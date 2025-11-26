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
use App\Models\TrRfcaStep;



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

            // === update TrRfca & TrRfcaStep terkait ===
            $rfcastep = TrRfcaStep::where('rfcaid', $rfca->rfcaid)
                    ->where('ponbr', $rfca->ponbr)
                    ->where('rfca_step_id', 'PC')
                    ->first();

            $rfcastep->rfca_step_user   = $username;      
            $rfcastep->rfca_step_date   = $datestamp; 
            $rfcastep->status_rfca      = 'C'; 
            $rfcastep->updated_by = $username;
            $rfcastep->save();

            $rfca->calrid     = $calrid;
            $rfca->rfca_step_order = $rfcastep->rfca_step_order; 
            $rfca->rfca_step_id    = $rfcastep->rfca_step_id;
            $rfca->updated_by = $username;
            $rfca->updated_at = $datestamp;
            $rfca->save(); 

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
                    'doctype'       => $doctype,
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

        // ===== Header Calr (pakai model baru)
        /** @var \App\Models\TrCalr $calr */
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

        // ===== Link ke RFCA (baru)
        $rfcaUrl = null;
        if (!empty($calr->rfcaid)) {
            $rfcaId = TrRfca::where('rfcaid', $calr->rfcaid)->value('id');
            if ($rfcaId) {
                $rfcaHash = Hashids::encode($rfcaId);
                $rfcaUrl  = url("/showrfca/{$rfcaHash}");
            }
        }

        // ===== Link ke SPPB/J/K/T (opsional)
        $sppbUrl   = null;
        $sppbjktid = (string) ($calr->sppbjktid ?? '');
        $prefix    = strtoupper(substr($sppbjktid, 0, 2));

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

        // ===== Detail PO (TrPOdetail) berdasarkan PONBR
        $details = collect();
        if (!empty($calr->ponbr)) {
            $details = TrPOdetail::where('ponbr', $calr->ponbr)
                ->orderBy('po_no')
                ->get();
        }

        // Convenience: encode ID untuk email/dll
        $eid_calrid = Hashids::encode((string) $calr->id);

        return view('pages.calr.showcalr', [
            'calr'        => $calr,
            'hash'        => $hash,
            'eid_calrid'  => $eid_calrid,
            'poUrl'       => $poUrl,
            'rfcaUrl'     => $rfcaUrl,
            'sppbUrl'     => $sppbUrl,
            'csUrl'       => $csUrl,
            'details'     => $details,
        ]);
    }


   
    public function approveCalr(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'CA';

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
                        $calr->calrid, 'CA', 'P', 'CALR', $docUrl, [
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
        $doctype = 'CA';

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
                    app('App\Http\Controllers\SendCommentController')->sendmsg($calr->id, 'CA', request());
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
        $doctype = 'CA';

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
                    app('App\Http\Controllers\SendCommentController')->sendmsg($calr->id, 'CA', request());
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

    public function editCalr($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        /** @var \App\Models\TrCalr $calr */
        $calr = TrCalr::findOrFail($id);

        // Detail PO tetap dari TrPOdetail
        $details = TrPOdetail::where('ponbr', $calr->ponbr)->get();

        // hash untuk passing balik ke view (dipakai di route update)
        $calr_eid = Hashids::encode((string) $calr->id);

        $rows = TrAttachment::where('refnbr', $calr->calrid)
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

        $config      = config('filesystems.disks.gcs');
        $keyFilePath = $config['key_file'];
        if (!Str::startsWith($keyFilePath, ['/','C:\\','D:\\'])) {
            $keyFilePath = base_path($keyFilePath);
        }
        $storage = new StorageClient([
            'projectId'   => $config['project_id'],
            'keyFilePath' => $keyFilePath,
        ]);
        $bucket = $storage->bucket($config['bucket']);

        $attachments = $rows->map(function ($r) use ($bucket) {
            $objectPath = rtrim($r->folder, '/').'/'.$r->filename;
            $object     = $bucket->object($objectPath);
            $signedUrl  = null;
            try {
                $signedUrl = $object->signedUrl(
                    new \DateTimeImmutable('+10 minutes'),
                    ['version' => 'v4']
                );
            } catch (\Throwable $e) {
                \Log::warning('Signed URL gagal', ['path' => $objectPath, 'error' => $e->getMessage()]);
            }
            return (object) [
                'id'          => $r->id,
                'display_name' => $r->attachment_name,
                'created_by'   => $r->created_by,
                'created_at'   => $r->created_at,
                'url'          => $signedUrl,
                'folder'       => $r->folder,
                'filename'     => $r->filename,
                'extention'    => $r->extention,
                'size'         => $r->filesize,
            ];
        });

        return view('pages.calr.editcalr', [
            'calr'      => $calr,
            'calr_eid'  => $calr_eid,
            'hash'      => $hash,
            'details'   => $details,
            'attachments' => $attachments,
        ]);
    }

    public function updateCalr(Request $request, $hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        /** @var \App\Models\TrCalr $calr */
        $calr = TrCalr::findOrFail($id);

        $request->validate([
            'calr_amount'   => 'required|numeric|min:0',
            'attachments.*' => 'file|max:10240',
        ]);

        $doctype  = 'CA'; 
        $user     = $request->user();
        $username = $user->username ?? 'system';
        $dt        = Carbon::now('Asia/Jakarta');

        $rfcaAmount = (float) ($calr->rfca_amount ?? 0);
        $calrAmount = (float) $request->input('calr_amount', 0);
        $balance    = $rfcaAmount - $calrAmount;

        /** @var \App\Http\Controllers\ApprovalController $approvalCtl */
        $approvalCtl = app(ApprovalController::class);

        // Pastikan line approval ada (pakai company & dept dari RFCA)
        $approvalCtl->loadLines($doctype, $calr->cpny_id, $calr->department_id);

        DB::beginTransaction();
        try {
            // === update header TrCalr ===
            $calr->calr_amount    = $calrAmount;
            $calr->balance_amount = $balance;
            $calr->status    ='P';           
            $calr->updated_by     = $username;
            $calr->updated_at     = $dt;
            $calr->save();

            // === generate TrApproval ===
            $ctx = [
                'ignore_nominal' => true,
            ];

            [$firstApprovalUsernames, $linesCount] = $approvalCtl->generateForDocument(
                $calr->calrid,
                $doctype,
                $calr->cpny_id,
                $calr->department_id,
                $username,
                $ctx,
                $dt
            );

            // === upload attachment baru (opsional) ===
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr'        => $calr->calrid,          // pakai CALR ID yg sudah ada
                    'doctype'       => $doctype,
                    'cpnyid'        => $calr->cpny_id,
                    'departementid' => $calr->department_id,
                    'base_folder'   => 'att-purchasing-app/' . strtolower($doctype),
                    'created_by'    => $username,
                ];

                $files = (array) $request->file('attachments');

                /** @var \App\Http\Controllers\TrAttachmentController $uploader */
                $uploader = app(TrAttachmentController::class);
                $uploader->uploadInternal($meta, $files);
            }

            DB::commit();

            return response()->json([
                'ok'      => true,
                'message' => 'CALR updated successfully.',
                'calrid'  => $calr->calrid,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'message' => 'Gagal mengupdate CALR.',
            ], 500);
        }
    }

    

    

    
    
    


}
