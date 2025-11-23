<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\TrCalr;
use App\Models\TrPOterm;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Str;
use App\Http\Controllers\TrAttachmentController;
use Illuminate\Support\Facades\Response;
use App\Models\TrAttachment;
use Google\Cloud\Storage\StorageClient;
use App\Http\Controllers\ApprovalController;
use App\Models\TrApproval;
use App\Models\Autonbr;
use App\Models\TrSPPB;
use App\Models\TrSPPJ;
use App\Models\TrSPPK;
use App\Models\TrSPPT;
use App\Models\TrCS;
use App\Models\Company;
use App\Models\Dept;
use App\Models\TrPo;
use App\Models\TrPOdetail;
use App\Models\TrRfca;


class CalrController extends Controller
{
    public function createCalr(Request $request)
    {
        // Expect: /calr/create?rfca=<hashid-of-TrRfca.id>
        $rfcaHash = (string) $request->query('rfca', '');
        if (!$rfcaHash) {
            abort(404, 'Parameter rfca tidak ditemukan.');
        }

        $decoded = Hashids::decode($rfcaHash);
        if (empty($decoded)) {
            abort(404, 'Parameter rfca tidak valid.');
        }

        $rfcaId = (int) $decoded[0];

        // Header dari TrRfca
        $rfca = TrRfca::findOrFail($rfcaId);

        // Detail dari TrPOdetail, relasi lewat PONBR
        // (sesuaikan nama kolom jika berbeda)
        $details = TrPOdetail::where('ponbr', $rfca->ponbr)
            ->select(['inventory_descr', 'qty', 'uom', 'totalcost'])
            ->get();

        // kirim ke view
        return view('pages.calr.createcalr', [
            'rfca'      => $rfca,
            'rfca_eid'  => $rfcaHash,
            'details'   => $details,
        ]);
    }

    public function storeCalr(Request $request)
    {
        $request->validate([
            'rfca_eid'         => 'required|string',
            'calr_amount'      => 'required|numeric|min:0',
            'attachments.*'    => 'file|max:10240',     // 10MB/file
            'attachments_ba.*' => 'file|max:10240',     // 10MB/file
        ]);

        // decode RFCA
        $decoded = Hashids::decode($request->input('rfca_eid'));
        if (empty($decoded)) {
            return response()->json(['message' => 'RFCA hash tidak valid.'], 422);
        }
        $rfcaPkId = (int) $decoded[0];

        /** @var \App\Models\TrRfca|null $rfca */
        $rfca = TrRfca::find($rfcaPkId);
        if (!$rfca) {
            return response()->json(['message' => 'Data RFCA tidak ditemukan.'], 404);
        }

        // nilai RFCA & CALR
        $rfcaAmount  = (float) ($rfca->rfca_amount ?? 0);
        $calrAmount  = (float) $request->input('calr_amount', 0); // hidden input dari view
        $balance     = $rfcaAmount - $calrAmount;                  // boleh minus

        $doctype  = 'CA'; // kode dokumen CALR
        $user     = $request->user();
        $username = $user->username ?? 'system';

        $dt        = Carbon::now('Asia/Jakarta');
        $year      = (int) $dt->year;
        $month     = str_pad((string) $dt->month, 2, '0', STR_PAD_LEFT);
        $datestamp = $dt->toDateTimeString();

        /** @var \App\Http\Controllers\ApprovalController $approvalCtl */
        $approvalCtl = app(ApprovalController::class);

        // Pastikan line approval ada (pakai company & dept dari RFCA)
        $approvalCtl->loadLines($doctype, $rfca->cpny_id, $rfca->department_id);

        DB::beginTransaction();
        try {
            // === autonumber (lock) ===
            /** @var \App\Models\Autonbr|null $autonbr */
            $autonbr = Autonbr::lockForUpdate()
                ->where('doctype', $doctype)
                ->where('year', $year)
                ->where('month', $month)
                ->first();

            if (!$autonbr) {
                $autonbr = Autonbr::create([
                    'doctype' => $doctype,
                    'year'    => $year,
                    'month'   => $month,
                    'status'  => 'A',
                    'number'  => 1,
                ]);
                $urutan = 1;
            } else {
                $urutan = (int) $autonbr->number + 1;
                $autonbr->update(['number' => $urutan]);
            }

            $tglbln = substr((string) $year, 2) . $month; // YYMM
            $docid  = $doctype . $tglbln . sprintf('%04d', $urutan);
            $calrid = $docid;

            // === create header TrCalr (pakai model baru) ===
            /** @var \App\Models\TrCalr $header */
            $header = TrCalr::create([
                'calrid'        => $calrid,
                'calrdate'      => $dt->toDateString(),

                'rfcaid'        => $rfca->rfcaid,
                'rfca_type'     => $rfca->rfca_type ?? null,
                'ponbr'         => $rfca->ponbr,
                'cpny_id'       => $rfca->cpny_id,
                'csid'          => $rfca->csid,
                'sppbjktid'     => $rfca->sppbjktid ?? null,
                'department_id' => $rfca->department_id,
                'user_peminta'  => $rfca->user_peminta ?? null,
                'keperluan'     => $rfca->keperluan,
                'vendorid'      => $rfca->vendorid ?? null,
                'vendorname'    => $rfca->vendorname,

                'rfca_amount'   => $rfcaAmount,
                'calr_amount'   => $calrAmount,
                'balance_amount'=> $balance,

                'status'        => 'P', // Pending / On Progress
                'created_by'    => $username,
                'updated_by'    => $username,
            ]);

            // Flag di RFCA supaya tidak muncul lagi di jobs (kalau memang pakai kolom ini)
            // if (property_exists($rfca, 'calrid') || $rfca->getAttribute('calrid') !== null || $rfca->getAttribute('calrid') === null) {
            //     $rfca->calrid     = $calrid;
            //     $rfca->updated_by = $username;
            //     $rfca->updated_at = $datestamp;
            //     $rfca->save();
            // }

            // === generate TrApproval ===
            $ctx = [
                'ignore_nominal' => true,
            ];

            [$firstApprovalUsernames, $linesCount] = $approvalCtl->generateForDocument(
                $docid,
                $doctype,
                $rfca->cpny_id,
                $rfca->department_id,
                $username,
                $ctx,
                $dt
            );

            // (tidak set completed_by di TrCalr karena field-nya tidak ada di model baru)

            // === upload attachments (doctype CA) jika ada ===
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr'        => $docid,
                    'doctype'       => 'CA',
                    'cpnyid'        => $rfca->cpny_id,
                    'departementid' => $rfca->department_id,
                    'base_folder'   => 'att-purchasing-app/' . strtolower($doctype),
                    'created_by'    => $username,
                ];

                $files = (array) $request->file('attachments');

                try {
                    /** @var \App\Http\Controllers\TrAttachmentController $uploader */
                    $uploader = app(TrAttachmentController::class);
                    $uploader->uploadInternal($meta, $files);
                } catch (\Throwable $e) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Gagal membuat CALR',
                        'error'   => 'Gagal upload attachment: ' . $e->getMessage(),
                    ], 500);
                }
            }

            
            $eid = Hashids::encode((string) $header->id);

            // === notifikasi approver pertama ===
            $approvalCtl->notifyFirstApprover(
                $docid,
                $doctype,
                $header->status, // 'P'
                'CALR',
                url('/showcalr/' . $eid),
                [
                    'info'      => $header->keperluan,
                    'createdby' => $header->created_by,
                    'date'      => $dt->toDateTimeString(),
                ]
            );

            DB::commit();

            return response()->json([
                'ok'      => true,
                'message' => 'Calr created successfully.',
                'calrid'  => $header->calrid,
                'eid'     => $eid,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json(['message' => 'Gagal membuat CALR.'], 500);
        }
    }


    public function showCalr($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        // ===== Header Calr
        $calr = TrCalr::findOrFail($id);
              
        // ===== Link ke PO (opsional)
        $poUrl = null;
        if (!empty($calr->ponbr)) {
            $poId = TrPO::where('ponbr', $calr->ponbr)->value('id');
            if ($poId) {
                $poHash = Hashids::encode($poId);
                $poUrl  = url("/showpo/{$poHash}");
            }
        }

       
        // ===== Link ke SPPB/J/K/T (opsional)
        $sppbUrl = null;
        $sppbjktid = (string)($calr->sppbjktid ?? '');
        $prefix = strtoupper(substr($sppbjktid, 0, 2));

        $routeMap = [
            'PB' => 'showsppbs',
            'PJ' => 'showsppjs',
            'PK' => 'showsppks',
            'PT' => 'showsppts',
        ];

        if ($sppbjktid !== '' && isset($routeMap[$prefix])) {
            $docId = null;

            if ($prefix === 'PB') {                
                $docId = TrSPPB::where('sppbid', $sppbjktid)->value('id');
            } elseif ($prefix === 'PJ') {
                $docId = TrSPPJ::where('sppjid', $sppbjktid)->value('id');
            } elseif ($prefix === 'PK') {
                $docId = TrSPPK::where('sppkid', $sppbjktid)->value('id');
            } elseif ($prefix === 'PT') {
                $docId = TrSPPT::where('spptid', $sppbjktid)->value('id');
            }

            if (!empty($docId)) {
                $sppbHash = Hashids::encode($docId);
                $sppbUrl  = url('/' . $routeMap[$prefix] . '/' . $sppbHash);
            }
        }

        // ===== Link ke CS (opsional)
        $csUrl = null;
        if (!empty($calr->csid)) {
            $csId = TrCS::where('csid', $calr->csid)->value('id');
            if ($csId) {
                $csHash = Hashids::encode($csId);
                $csUrl  = url("/showcs/{$csHash}");
            }
        }

        // Untuk convenience (mis. kirim email dsb)
        $eid_calrid = Hashids::encode($calr->calrid);

        $ratingAvg = is_null($calr->rating_vendor) ? null : (float)$calr->rating_vendor;
        $ratingLegendName = null;

        if (!is_null($ratingAvg)) {
            $legend = MsCALRRatingLegend::where('status', 'A')
                ->where('rating_legend_from', '<=', $ratingAvg)
                ->where('rating_legend_to', '>=', $ratingAvg)
                ->orderBy('rating_legend_from', 'asc')
                ->first();

            $ratingLegendName = $legend->rating_legend_name ?? null;
        }

        // --- detail rows TrCALRRating + legend name per baris ---
        $calrRatingRows = TrCALRRating::from('tr_calr_rating as t')
            ->leftJoin('ms_calr_rating_legend as l', function($join){
                // Postgres: cocokkan score ke rentang legend; batasi legend aktif
                // Jika rating_score bertipe integer/decimal, cast tidak wajib;
                // kalau kolom text, pakai cast numeric.
                $join->on(DB::raw('t.rating_score::numeric'), '>=', DB::raw('l.rating_legend_from::numeric'))
                    ->on(DB::raw('t.rating_score::numeric'), '<=', DB::raw('l.rating_legend_to::numeric'))
                    ->where('l.status', 'A');
            })
            ->where('t.calr_id', $calr->calrid)
            ->orderBy('t.rating_no')
            ->get([
                't.id',
                't.rating_no',
                't.rating_name',
                't.rating_score',
                'l.rating_legend_name'
            ]);

        return view('pages.calr.showcalr', [
            'calr'            => $calr,    
            'hash'           => $hash,
            'eid_calrid' => $eid_calrid,
            'poUrl'          => $poUrl,
            'sppbUrl'        => $sppbUrl,
            'csUrl'          => $csUrl,    
            'ratingLegendName'  => $ratingLegendName,        
            'calrRatingRows'    => $calrRatingRows,
        ]);
    }

    public function approveCalr_xxx(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'BA';

        $calr = \App\Models\TrCALR::with('creator')->where('calrid', $docid)->first();
        if (!$calr) {
            return response()->json(['success'=>false,'message'=>'CALR not found'],404);
        }

        $rating = (int) $request->input('rating_vendor', 0);

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($calr->id);
        $docUrl   = url('/showcalr/' . $eid);
        $fullname = data_get($calr, 'creator.name') ?: $calr->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->approveStep(
            $calr->calrid,
            $doctype,
            $user->username,
            $user->name,

            // ✅ FINAL APPROVAL (C = Completed)
            function (string $refnbr, \Carbon\Carbon $now) use ($calr, $fullname, $docUrl, $rating) {

                // ✅ APPLY SIDE EFFECTS AGAIN (agar final tetap sync)
                $this->applyCalrApprovalSideEffects($calr, $rating, $now);

                $calr->status       = 'C';
                $calr->completed_by = auth()->user()->username;
                $calr->completed_at = $now;
                $calr->save();

                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $calr->calrid,
                    'CALR',
                    'C',
                    $calr->created_by,
                    $docUrl,
                    [
                        'cpnyid'   => $calr->cpny_id ?? '',
                        'deptname' => $calr->department_id ?? '',
                        'date'     => $calr->calrdate,
                        'info'     => $calr->keperluan,
                        'fullname' => $fullname,
                        'createdby'=> $fullname,
                    ]
                );
            },

            // ✅ NEXT APPROVER (P = Pending next approver)
            function ($next, \Carbon\Carbon $now) use ($calr, $docUrl, $rating) {

                /**
                 * ✅ ONLY LEVEL 1.00 -> simpan rating & penalty!
                 */
                if (isset($next['aprv_leveling']) && $next['aprv_leveling'] === "2.00") {
                    // berarti sekarang masih di approve level 1 (yang baru approve)
                    $this->applyCalrApprovalSideEffects($calr, $rating, $now);
                }

                app(\App\Http\Controllers\ApprovalController::class)->notifyFirstApprover(
                    $calr->calrid,
                    'BA',
                    'P',
                    'CALR',
                    $docUrl,
                    [
                        'info'      => $calr->keperluan,
                        'createdby' => $calr->created_by,
                        'date'      => $now->toDateTimeString(),
                    ]
                );

                $calr->completed_by = auth()->user()->username;
                $calr->completed_at = $now;
                $calr->save();
            }
        );

        if (!$result['ok']) {
            return response()->json(['success'=>false,'message'=>$result['message'] ?? 'Approve failed'], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Task approved successfully'
        ]);
    }

    public function approveCalr(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'BA';

        $calr = \App\Models\TrCALR::with('creator')->where('calrid', $docid)->first();
        if (!$calr) {
            return response()->json(['success'=>false,'message'=>'CALR not found'],404);
        }

        // 🔽 inilah kuncinya
        $ratingScores = $this->extractRatings($request);

        // (opsional) log untuk debugging
        \Log::info('[approveCalr] payload', [
            'all'           => $request->all(),
            'rating_scores' => $ratingScores,
        ]);

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($calr->id);
        $docUrl   = url('/showcalr/' . $eid);
        $fullname = data_get($calr, 'creator.name') ?: $calr->created_by;

        return \DB::transaction(function () use ($user, $doctype, $calr, $ratingScores, $docUrl, $fullname) {

            $result = app(\App\Http\Controllers\ApprovalController::class)->approveStep(
                $calr->calrid,
                $doctype,
                $user->username,
                $user->name,

                // FINAL
                function (string $refnbr, \Carbon\Carbon $now) use ($calr, $fullname, $docUrl, $ratingScores) {
                    $this->applyCalrApprovalSideEffects($calr, $now, $ratingScores);

                    $calr->status       = 'C';
                    $calr->completed_by = auth()->user()->username;
                    $calr->completed_at = $now;
                    $calr->save();

                    app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                        $calr->calrid, 'CALR', 'C', $calr->created_by, $docUrl, [
                            'cpnyid'   => $calr->cpny_id ?? '',
                            'deptname' => $calr->department_id ?? '',
                            'date'     => $calr->calrdate,
                            'info'     => $calr->keperluan,
                            'fullname' => $fullname,
                            'createdby'=> $fullname,
                        ]
                    );
                },

                // NEXT APPROVER
                function ($next, \Carbon\Carbon $now) use ($calr, $docUrl, $ratingScores) {
                    if (isset($next['aprv_leveling']) && $next['aprv_leveling'] === "2.00") {
                        // efek samping setelah lolos level 1
                        $this->applyCalrApprovalSideEffects($calr, $now, $ratingScores);
                    }

                    app(\App\Http\Controllers\ApprovalController::class)->notifyFirstApprover(
                        $calr->calrid, 'BA', 'P', 'CALR', $docUrl, [
                            'info'      => $calr->keperluan,
                            'createdby' => $calr->created_by,
                            'date'      => $now->toDateTimeString(),
                        ]
                    );

                    $calr->completed_by = auth()->user()->username;
                    $calr->completed_at = $now;
                    $calr->save();
                }
            );

            if (!$result['ok']) {
                \DB::rollBack();
                return response()->json(['success'=>false,'message'=>$result['message'] ?? 'Approve failed'], 403);
            }

            return response()->json([
                'success'       => true,
                'message'       => 'Task approved successfully',
                'rating_vendor' => $calr->rating_vendor,
            ]);
        });
    }

    public function rejectCalr(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'BA';

        $calr = \App\Models\TrCALR::with('creator')->where('calrid', $docid)->first();
        if (!$calr) return response()->json(['success'=>false,'message'=>'CALR not found'],404);

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($calr->id);
        $docUrl   = url('/showcalr/' . $eid);
        $fullname = data_get($calr, 'creator.name') ?: $calr->created_by;
        $term = TrPOterm::where('calrid', $calr->calrid)                
            ->first();

        $result = app(\App\Http\Controllers\ApprovalController::class)->rejectStep(
            $calr->calrid,
            $doctype,
            $user->username,
            $user->name,

            function (string $refnbr, \Carbon\Carbon $now) use ($calr, $fullname, $docUrl,$term) {
                $calr->status       = 'R';
                $calr->completed_by = auth()->user()->username;
                $calr->completed_at = $now;
                $calr->save();                

                $term->calrid = '';
                $term->updated_by = auth()->user()->username;
                $term->updated_at = $now;
                $term->save();

                // optional: tandai detail R
                // \App\Models\TrCALRdetail::where('calrid', $calr->calrid)->update(['status' => 'R']);

                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $calr->calrid,
                    'CALR',
                    'R',
                    $calr->created_by,
                    $docUrl,
                    [
                        'cpnyid'   => $calr->cpny_id ?? $calr->cpnyid ?? '',
                        'deptname' => $calr->department_id ?? $calr->departementid ?? '',
                        'date'     => $now->toDateString(),
                        'info'     => $calr->keperluan,
                        'fullname' => $fullname,
                        'name'     => $fullname,
                        'createdby'=> $fullname, 
                    ]
                );

                // simpan komentar (jika ada)
                try {
                    app('App\Http\Controllers\SendCommentController')->sendmsg($calr->id, 'BA', request());
                } catch (\Throwable $e) {}
            }
        );

        if (!$result['ok']) {
            return response()->json(['success'=>false,'message'=>$result['message'] ?? 'Reject failed'], 403);
        }

        return response()->json(['success'=>true,'message'=>'CALR rejected successfully']);
    }

    public function reviseCalr(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'BA';

        $calr = \App\Models\TrCALR::with('creator')->where('calrid', $docid)->first();
        if (!$calr) return response()->json(['success'=>false,'message'=>'CALR not found'],404);

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($calr->id);
        $docUrl   = url('/showcalr/' . $eid);
        $fullname = data_get($calr, 'creator.name') ?: $calr->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->reviseStep(
            $calr->calrid,            // refnbr
            $doctype,                 // PT
            $user->username,          // actor
            $user->name,              // actor
            function (string $refnbr, \Carbon\Carbon $now) use ($calr, $fullname, $docUrl) {
                // === HEADER CALR -> D ===
                $calr->status       = 'D';
                $calr->completed_by = auth()->user()->username;
                $calr->completed_at = $now;
                $calr->save();

                // (opsional) DETAIL -> D
                // \App\Models\TrCALRdetail::where('calrid', $calr->calrid)->update(['status' => 'D']);

                // === Email ke requester ===
                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $calr->calrid,
                    'CALR',
                    'D',
                    $calr->created_by,
                    $docUrl,
                    [
                        'cpnyid'   => $calr->cpny_id ?? $calr->cpnyid ?? '',
                        'deptname' => $calr->department_id ?? $calr->departementid ?? '',
                        'date'     => $now->toDateString(),
                        'info'     => $calr->keperluan,
                        'fullname' => $fullname,
                        'name'     => $fullname,
                        'createdby'=> $fullname,   // <<< tambahkan ini
                    ]
                );


                // === Simpan komentar (jika ada) ===
                try {
                    app('App\Http\Controllers\SendCommentController')->sendmsg($calr->id, 'BA', request());
                } catch (\Throwable $e) {}
            }
        );

        if (!$result['ok']) {
            return response()->json([
                'success'=>false,
                'message'=>$result['message'] ?? 'Revise failed'
            ], 403);
        }

        return response()->json(['success'=>true,'message'=>'CALR revised successfully']);
    }

    public function printCalr($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $authUser = Auth::user();
        if (!$authUser) {
            return redirect()->route('login');
        }

        // Ambil CALR + relasi
        $calr = TrCalr::with(['creator', 'userpeminta', 'location', 'subLocation'])
            ->findOrFail($id);

        // Approval list
        $approval = TrApproval::query()
            ->where('refnbr', $calr->calrid)
            ->where('status', '<>', 'X')
            ->orderByRaw('CAST(aprv_leveling AS numeric) ASC')
            ->orderBy('created_at', 'ASC')
            ->get();

        $approve_count = $approval->count();

        // Company
        $company = Company::where('cpnyid', $calr->cpny_id)->first();

        // Mapping status dokumen
        switch ($calr->status) {
            case 'R':
                $status_doc = 'Rejected';
                break;
            case 'C':
                $status_doc = 'Completed';
                break;
            case 'D':
                $status_doc = 'Hold';
                break;
            case 'X':
                $status_doc = 'Cancel';
                break;
            default:
                $status_doc = 'On Progress';
                break;
        }

        $data = [
            'title'               => 'Berita Acara Serah Terima',
            'doc_type'            => 'CALR',
            'docid'               => $calr->calrid,
            'department_id'       => $calr->department_id,
            'cpnyname'            => optional($company)->cpnyname,
            'parent'              => optional($company)->parent,
            'project'             => optional($company)->project,

            // identitas & tanggal
            'created_by_username' => $calr->created_by,
            'created_by_name'     => ucwords(strtolower(optional($calr->creator)->name ?? $calr->created_by)),
            'created_at_fmt'      => optional($calr->created_at)->format('d F Y'),
            'req_date_fmt'        => optional($calr->created_at)->format('d M Y H:i'),
            'calrdate'            => $calr->calrdate
                                        ? Carbon::parse($calr->calrdate)->format('d F Y')
                                        : '',

            // konten utama
            'keperluan'           => $calr->keperluan,
            'status_doc'          => $status_doc,
            // kalau nanti ada relasi requestType, tetap aman
            'requesttype_name'    => optional($calr->requestType ?? null)->requesttype_name,

            // tanggal pekerjaan
            'startdate_fmt'       => $calr->startdate
                                        ? Carbon::parse($calr->startdate)->format('d/m/Y')
                                        : '',
            'enddate_fmt'         => $calr->enddate
                                        ? Carbon::parse($calr->enddate)->format('d/m/Y')
                                        : '',
            'handoverdate_fmt'    => $calr->handoverdate
                                        ? Carbon::parse($calr->handoverdate)->format('d/m/Y H:i')
                                        : '',

            // lokasi
            'location_name'       => optional($calr->location)->location_name ?? $calr->location_id,
            'sub_location_name'   => optional($calr->subLocation)->sub_location_name ?? $calr->sub_location_id,

            // angka2
            'penalty_per_day'     => $calr->penalty,
            'days_penalty'        => $calr->days_penalty,
            'total_penalty'       => $calr->total_penalty,
            'calr_amount'         => $calr->calr_amount,
            'realize_amount'      => $calr->realize_amount,
            'spkpic'              => $calr->spkpic,
            'spkwarranty'         => $calr->spkwarranty,
        ];

        $pdf = \PDF::loadView(
            'pages.calr.pdf_calr',
            array_merge($data, [
                'calr'          => $calr,
                'approval'      => $approval,
                'approve_count' => $approve_count,
            ])
        );

        // $pdf->setPaper('A4', ($approve_count <= 5) ? 'portrait' : 'landscape');

        return $pdf->stream("pdf_calr_{$calr->calrid}.pdf");
    }


    public function printCalrVendor($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $authUser = Auth::user();
        if (!$authUser) {
            return redirect()->route('login');
        }

        // Ambil CALR + relasi
        $calr = TrCalr::with(['creator', 'userpeminta', 'location', 'subLocation'])
            ->findOrFail($id);

        // Approval list
        $approval = TrApproval::query()
            ->where('refnbr', $calr->calrid)
            ->where('status', '<>', 'X')
            ->orderByRaw('CAST(aprv_leveling AS numeric) ASC')
            ->orderBy('created_at', 'ASC')
            ->get();

        $approve_count = $approval->count();

        // Company
        $company = Company::where('cpnyid', $calr->cpny_id)->first();

        // Mapping status dokumen
        switch ($calr->status) {
            case 'R':
                $status_doc = 'Rejected';
                break;
            case 'C':
                $status_doc = 'Completed';
                break;
            case 'D':
                $status_doc = 'Hold';
                break;
            case 'X':
                $status_doc = 'Cancel';
                break;
            default:
                $status_doc = 'On Progress';
                break;
        }

        $data = [
            'title'               => 'Berita Acara Serah Terima',
            'doc_type'            => 'CALR',
            'docid'               => $calr->calrid,
            'department_id'       => $calr->department_id,
            'cpnyname'            => optional($company)->cpnyname,
            'parent'              => optional($company)->parent,
            'project'             => optional($company)->project,

            // identitas & tanggal
            'created_by_username' => $calr->created_by,
            'created_by_name'     => ucwords(strtolower(optional($calr->creator)->name ?? $calr->created_by)),
            'created_at_fmt'      => optional($calr->created_at)->format('d F Y'),
            'req_date_fmt'        => optional($calr->created_at)->format('d M Y H:i'),
            'calrdate'            => $calr->calrdate
                                        ? Carbon::parse($calr->calrdate)->format('d F Y')
                                        : '',

            // konten utama
            'keperluan'           => $calr->keperluan,
            'status_doc'          => $status_doc,
            // kalau nanti ada relasi requestType, tetap aman
            'requesttype_name'    => optional($calr->requestType ?? null)->requesttype_name,

            // tanggal pekerjaan
            'startdate_fmt'       => $calr->startdate
                                        ? Carbon::parse($calr->startdate)->format('d/m/Y')
                                        : '',
            'enddate_fmt'         => $calr->enddate
                                        ? Carbon::parse($calr->enddate)->format('d/m/Y')
                                        : '',
            'handoverdate_fmt'    => $calr->handoverdate
                                        ? Carbon::parse($calr->handoverdate)->format('d/m/Y H:i')
                                        : '',

            // lokasi
            'location_name'       => optional($calr->location)->location_name ?? $calr->location_id,
            'sub_location_name'   => optional($calr->subLocation)->sub_location_name ?? $calr->sub_location_id,

            // angka2
            'penalty_per_day'     => $calr->penalty,
            'days_penalty'        => $calr->days_penalty,
            'total_penalty'       => $calr->total_penalty,
            'calr_amount'         => $calr->calr_amount,
            'realize_amount'      => $calr->realize_amount,
            'spkpic'              => $calr->spkpic,
            'spkwarranty'         => $calr->spkwarranty,
        ];

        // Kirim ke view
        $pdf = \PDF::loadView(
            'pages.calr.pdf_calr_vendor',
            array_merge($data, [                
                'calr'          => $calr,
                'approval'       => $approval,
                'approve_count'  => $approve_count,
            ])
        );

        // Portrait jika <= 5 approver, else landscape
        // $pdf->setPaper('A4', ($approve_count <= 5) ? 'portrait' : 'landscape');

        return $pdf->stream("pdf_calr_vendor_{$calr->calrid}.pdf");
    }

    private function extractRatings(Request $request): array
    {
        // 1) bentuk map langsung: rating_scores = { "<id>": <score>, "RATING01": <score>, "no:1": <score> }
        $ratingScores = $request->input('rating_scores', []);
        if (!is_array($ratingScores)) $ratingScores = [];

        // 2) bentuk array: ratings = [ {id, rating_id, rating_no, rating_score/score}, ... ]
        $ratingsItems = $request->input('ratings', []);
        if (!is_array($ratingsItems)) $ratingsItems = [];

        // 3) bentuk string json: ratings_json = "[{...}, {...}]"
        if (empty($ratingsItems)) {
            $ratingsJsonStr = $request->input('ratings_json');
            if (is_string($ratingsJsonStr) && $ratingsJsonStr !== '') {
                $decoded = json_decode($ratingsJsonStr, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $ratingsItems = $decoded;
                }
            }
        }

        // Normalisasi -> jadikan associative map unified
        // Kunci yang didukung: baris id, rating_id, dan "no:<rating_no>"
        foreach ($ratingsItems as $it) {
            if (!is_array($it)) continue;

            $score = $it['rating_score'] ?? $it['score'] ?? null;
            if ($score === null) continue;

            $score = (float)$score;
            if ($score <= 0) continue;

            if (!empty($it['id'])) {
                $ratingScores[(string)$it['id']] = $score;
            }
            if (!empty($it['rating_id'])) {
                $ratingScores[(string)$it['rating_id']] = $score;
            }
            if (!empty($it['rating_no'])) {
                $ratingScores['no:'.(string)$it['rating_no']] = $score;
            }
        }

        return $ratingScores; // unified map
    }

    

    private function applyCalrApprovalSideEffects(TrCALR $calr, Carbon $approveAt, array $ratingScores = []): TrCALR
    {
        // === 1) Update skor per-baris TrCALRRating dari payload slider (1-10)
        //      Terima kunci berupa row->id ATAU row->rating_id.
        if (!empty($ratingScores)) {
            $rows = TrCALRRating::where('calr_id', $calr->calrid)->get();

            foreach ($rows as $row) {
                // cari score by id
                $score = null;

                if (array_key_exists($row->id, $ratingScores)) {
                    $score = $ratingScores[$row->id];
                } elseif (!is_null($row->rating_id) && array_key_exists($row->rating_id, $ratingScores)) {
                    $score = $ratingScores[$row->rating_id];
                }

                if (!is_null($score)) {
                    // clamp 1..10
                    $clamped = max(1, min(10, (float)$score));
                    $row->rating_score = $clamped;
                    $row->updated_by   = auth()->user()->username ?? 'system';
                    $row->updated_at   = now('Asia/Jakarta');
                    $row->save();
                }
            }
        }

        // === 2) Hitung rata-rata terbaru (abaikan null/0)
        $agg = TrCALRRating::where('calr_id', $calr->calrid)
            ->whereNotNull('rating_score')
            ->where('rating_score', '>', 0)
            ->selectRaw('AVG(rating_score)::numeric as avg_score, COUNT(*) as cnt')
            ->first();

        $avgScore = $agg && $agg->cnt > 0 ? (float) $agg->avg_score : 0.0;

        // Simpan ke header. (Tetap pakai skala 1-10; kalau mau 1-5 bintang, tinggal dibagi 2.)
        $calr->rating_vendor = $avgScore > 0 ? round($avgScore, 1) : null;

        // === 3) Handover date = tanggal approve
        $calr->handoverdate = $approveAt->toDateString();

        // === 4) Days penalty (telat jika approve > enddate)
        $daysPenalty = 0;
        if (!empty($calr->enddate)) {
            $end  = Carbon::parse($calr->enddate)->startOfDay();
            $appr = $approveAt->copy()->startOfDay();
            $diff = $end->diffInDays($appr, false);
            $daysPenalty = $diff > 0 ? $diff : 0;
        }
        $calr->days_penalty = $daysPenalty;

        // === 5) Total penalty = days * penalty_per_day
        $perDay = (float) ($calr->penalty ?? 0); // kolom penalty dianggap tarif/hari
        $calr->total_penalty = $daysPenalty > 0 ? ($daysPenalty * $perDay) : 0.0;

        $calr->save();

        return $calr;
    }
    
    private function applyCalrApprovalSideEffects_xxx(TrCALR $calr, ?int $ratingFromReq, Carbon $approveAt): TrCALR
    {
        // 1) Rating
        if (!is_null($ratingFromReq) && $ratingFromReq > 0 && $ratingFromReq <= 5) {
            $calr->rating_vendor = $ratingFromReq;
        }

        // 2) Handover date = tanggal approve (YYYY-MM-DD)
        $calr->handoverdate = $approveAt->toDateString();

        // 3) Days penalty (telat jika approve > enddate)
        $daysPenalty = 0;
        if (!empty($calr->enddate)) {
            $end  = Carbon::parse($calr->enddate)->startOfDay();
            $appr = $approveAt->copy()->startOfDay();

            // Selisih hari (positif jika approve setelah enddate)
            $diff = $end->diffInDays($appr, false);
            $daysPenalty = $diff > 0 ? $diff : 0;
        }
        $calr->days_penalty = $daysPenalty;

        // 4) Total penalty = days * penalty_per_day
        $perDay = (float) ($calr->penalty ?? 0); // kolom penalty dianggap tarif/hari
        $calr->total_penalty = $daysPenalty > 0 ? ($daysPenalty * $perDay) : 0.0;

        $calr->save();

        return $calr;
    }

    public function getCalrRatings(string $calrid)
    {
        // Optional: validasi hak akses lihat CALR di sini

        $rows = TrCALRRating::where('calr_id', $calrid)
            ->orderBy('rating_no')
            ->get([
                'id',
                'rating_id',
                'rating_no',
                'rating_name',
                'rating_descr',
                'rating_score',
            ]);

        return response()->json([
            'success' => true,
            'data'    => $rows,
        ]);
    }


}
