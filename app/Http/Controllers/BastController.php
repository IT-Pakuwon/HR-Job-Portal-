<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\Autonbr;
use App\Models\MsBASTRating;
use App\Models\MsBASTRatingLegend;
use App\Models\MsCompany;
use App\Models\MsPenalty;
use App\Models\SysCalendar;
use App\Models\TrApproval;
use App\Models\TrBast;
use App\Models\TrBASTRating;
use App\Models\TrCS;
use App\Models\TrPO;
use App\Models\TrPOterm;
use App\Models\TrSPPB;
use App\Models\TrSPPJ;
use App\Models\TrSPPK;
use App\Models\TrSPPT;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;

class BastController extends Controller
{
    use HasAutonbr;

    public function createBast(Request $request)
    {
        // Expect: /bast/create?term=<hashid-of-TrPOterm.id>
        $termHash = (string) $request->query('term', '');
        if (!$termHash) {
            abort(404, 'Parameter term tidak ditemukan.');
        }

        $decoded = Hashids::decode($termHash);
        if (empty($decoded)) {
            abort(404, 'Parameter term tidak valid.');
        }

        $termId = (int) $decoded[0];

        // ambil term
        $term = TrPOterm::findOrFail($termId);

        // cek 2 digit kiri sppbjktid
        $docPrefix = strtoupper(substr(trim((string) $term->sppbjktid), 0, 2));
        $isPkRequest = $docPrefix === 'PK';

        // ambil PO by ponbr + cpny_id
        $po = TrPO::query()
            ->where('ponbr', $term->ponbr)
            ->where('cpny_id', $term->cpny_id)
            ->select(['ponbr', 'cpny_id', 'spkstartworkingdate', 'spkendtworkingdate'])
            ->first();

        return view('pages.bast.createbast', [
            'term' => $term,
            'term_eid' => $termHash,
            'po' => $po,
            'docPrefix' => $docPrefix,
            'isPkRequest' => $isPkRequest,
        ]);
    }

    public function createBast_xxx(Request $request)
    {
        // Expect: /bast/create?term=<hashid-of-TrPOterm.id>
        $termHash = (string) $request->query('term', '');
        if (!$termHash) {
            abort(404, 'Parameter term tidak ditemukan.');
        }

        $decoded = Hashids::decode($termHash);
        if (empty($decoded)) {
            abort(404, 'Parameter term tidak valid.');
        }

        $termId = (int) $decoded[0];

        // ambil term
        $term = TrPOterm::findOrFail($termId);

        // ✅ ambil PO by ponbr + cpny_id (penting!)
        $po = TrPO::query()
            ->where('ponbr', $term->ponbr)
            ->where('cpny_id', $term->cpny_id)
            ->select(['ponbr', 'cpny_id', 'spkstartworkingdate', 'spkendtworkingdate'])
            ->first();

        // kirim ke view
        return view('pages.bast.createbast', [
            'term' => $term,
            'term_eid' => $termHash,
            'po' => $po, // ✅ tambahan
        ]);
    }

    public function storeBast(Request $request)
    {
        //    dd($request->all());
        $request->validate([
            'term_eid' => 'required|string',
            'location_id' => 'required', 'string',
            'sub_location_id' => 'required', 'string',
            'attachments.*' => 'file|max:10240', // 10MB/file
        ]);

        // decode term
        $decoded = Hashids::decode($request->input('term_eid'));
        if (empty($decoded)) {
            return response()->json(['message' => 'Term hash tidak valid.'], 422);
        }
        $termId = (int) $decoded[0];

        /** @var TrPOterm|null $term */
        $term = TrPOterm::find($termId);
        if (!$term) {
            return response()->json(['message' => 'Data TrPOterm tidak ditemukan.'], 404);
        }

        $po = TrPO::where('ponbr', $term->ponbr)
                ->where('cpny_id', $term->cpny_id)
                ->first();

        $doctype = 'BA'; // kode dokumen BAST (ikuti konvensimu)
        $user = $request->user();
        $username = $user->username ?? 'system';
        $fullname = $user->name ?? 'system';

        // waktu
        $dt = Carbon::now('Asia/Jakarta');
        $year = (int) $dt->year;
        $month = str_pad((string) $dt->month, 2, '0', STR_PAD_LEFT);
        $datestamp = $dt->toDateTimeString();

        // Controller Approval
        /** @var ApprovalController $approvalCtl */
        $approvalCtl = app(ApprovalController::class);

        // Pastikan line approval ada
        $approvalCtl->loadLines($doctype, $term->cpny_id, $term->department_id);

        DB::beginTransaction();
        try {
            // === autonumber (lock) ===
            /** @var Autonbr|null $autonbr */         

            $auto = $this->nextAutonbr(
                $doctype,
                $year,
                $month,
                $username,
                'BAST'
            );
            $urutan = (int) $auto['next'];

            $tglbln = substr((string) $year, 2).$month;   // YYMM
            $docid = $doctype.$tglbln.sprintf('%04d', $urutan);
            $bastid = $docid;

            // === create header TrBast ===
            /** @var TrBast $header */
            $header = TrBast::create([
                'bastid' => $bastid,
                'bastdate' => $dt->toDateString(),
                'ponbr' => $term->ponbr,
                'cpny_id' => $term->cpny_id,
                'csid' => $term->csid,
                'sppbjktid' => $term->sppbjktid,
                'bqid' => $term->bqid,
                'department_id' => $term->department_id,
                'user_peminta' => $term->user_peminta,
                'keperluan' => $term->keperluan,
                'order_term' => $term->order_term,
                'terms_id' => $term->terms_id,
                'topid' => $term->topid,
                'progress_pct' => $term->progress_pct,
                'payment_pct' => $term->payment_pct,
                'vendorid' => $term->vendorid,
                'vendorname' => $term->vendorname,

                'location_id' => $request->location_id,
                'sub_location_id' => $request->sub_location_id,

                'startdate' => $po->spkstartworkingdate,
                'enddate' => $po->spkendtworkingdate,
                'bast_amount' => $term->bastamount,
                'spkpic' => $po->spkpic,
                'spkwarranty' => $po->spkwarranty,

                'status' => 'P', // pending/On Progress
                'created_by' => $username,
            ]);

            $term->bastid = $bastid;
            $term->updated_by = $username;
            $term->updated_at = $datestamp;
            $term->save();

            // === ms_rating ===
            $ms_bastrating = MsBASTRating::where('status', 'A')
                ->orderBy('rating_no', 'ASC')
                ->get();

            foreach ($ms_bastrating as $mrating) {
                TrBASTRating::create([
                    'bast_id' => $header->bastid,
                    'rating_id' => $mrating->rating_id,
                    'rating_no' => $mrating->rating_no,
                    'rating_name' => $mrating->rating_name,
                    'rating_descr' => $mrating->rating_descr,
                    'rating_score' => 0,
                    'status' => $mrating->status ?? 'A',
                    'created_by' => $username,
                    'created_at' => $dt,    // Carbon yang sudah kamu definisikan
                    'updated_by' => $username,
                    'updated_at' => $dt,
                ]);
            }

            // === generate TrApproval ===
            $ctx = [
                'ignore_nominal' => true,
            ];

            [$firstApprovalUsernames, $linesCount] = $approvalCtl->generateForDocument(
                $docid,
                $doctype,
                $term->cpny_id,
                $term->department_id,
                $username,
                $ctx,
                $dt
            );

            // (opsional) simpan hint approver pertama jika ingin
            if (!empty($firstApprovalUsernames)) {
                // jika kolom completed_by memang ada di TrBast
                $header->completed_by = is_array($firstApprovalUsernames)
                    ? implode(',', $firstApprovalUsernames)
                    : (string) $firstApprovalUsernames;
                // simpan tanpa assumed completed_at jika kolomnya tidak ada
                $header->save();
            }

            // === upload Photo After
            $uploadResult = null;
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr' => $docid,
                    'doctype' => 'BQ',
                    'cpnyid' => $term->cpny_id,
                    'departementid' => $term->department_id,
                    'base_folder' => 'att-purchasing-app/'.strtolower($doctype),
                    'created_by' => $username,
                ];

                $files = (array) $request->file('attachments');

                try {
                    /** @var TrAttachmentController $uploader */
                    $uploader = app(TrAttachmentController::class);
                    $uploadResult = $uploader->uploadInternal($meta, $files);
                } catch (\Throwable $e) {
                    DB::rollBack();

                    return response()->json([
                        'message' => 'Gagal membuat BAST',
                        'error' => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            }

            // === upload attachments_ba
            $uploadResult = null;
            if ($request->hasFile('attachments_ba')) {
                $meta = [
                    'refnbr' => $docid,
                    'doctype' => $doctype,
                    'cpnyid' => $term->cpny_id,
                    'departementid' => $term->department_id,
                    'base_folder' => 'att-purchasing-app/'.strtolower($doctype),
                    'created_by' => $username,
                ];

                $files = (array) $request->file('attachments_ba');

                try {
                    /** @var TrAttachmentController $uploader */
                    $uploader = app(TrAttachmentController::class);
                    $uploadResult = $uploader->uploadInternal($meta, $files);
                } catch (\Throwable $e) {
                    DB::rollBack();

                    return response()->json([
                        'message' => 'Gagal membuat BAST',
                        'error' => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            }

            $eid = Hashids::encode((string) $header->id);

            // === notifikasi approver pertama ===
            $approvalCtl->notifyFirstApprover(
                $docid,
                $doctype,
                $header->status, // 'P' | ...
                'BAST',
                url('/showbast/'.$eid),
                [
                    'info' => $header->keperluan,
                    'createdby' => $header->created_by,
                    'date' => $dt->toDateTimeString(),
                ]
            );

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'Bast created successfully.',
                'bastid' => $header->bastid,
                'eid' => $eid,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json(['message' => 'Gagal membuat BAST.'], 500);
        }
    }

    public function showBast($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // ===== Header Bast
        $bast = TrBast::findOrFail($id);

        // ===== Link ke PO (opsional)
        $poUrl = null;
        if (!empty($bast->ponbr)) {
            $poId = TrPO::where('ponbr', $bast->ponbr)->value('id');
            if ($poId) {
                $poHash = Hashids::encode($poId);
                $poUrl = url("/showpo/{$poHash}");
            }
        }

        // ===== Link ke SPPB/J/K/T (opsional)
        $sppbUrl = null;
        $sppbjktid = (string) ($bast->sppbjktid ?? '');
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
                $sppbUrl = url('/'.$routeMap[$prefix].'/'.$sppbHash);
            }
        }

        // ===== Link ke CS (opsional)
        $csUrl = null;
        if (!empty($bast->csid)) {
            $csId = TrCS::where('csid', $bast->csid)->value('id');
            if ($csId) {
                $csHash = Hashids::encode($csId);
                $csUrl = url("/showcs/{$csHash}");
            }
        }

        // Untuk convenience (mis. kirim email dsb)
        $eid_bastid = Hashids::encode($bast->bastid);

        $ratingAvg = is_null($bast->rating_vendor) ? null : (float) $bast->rating_vendor;
        $ratingLegendName = null;

        if (!is_null($ratingAvg)) {
            $legend = MsBASTRatingLegend::where('status', 'A')
                ->where('rating_legend_from', '<=', $ratingAvg)
                ->where('rating_legend_to', '>=', $ratingAvg)
                ->orderBy('rating_legend_from', 'asc')
                ->first();

            $ratingLegendName = $legend->rating_legend_name ?? null;
        }

        // --- detail rows TrBASTRating + legend name per baris ---
        $bastRatingRows = TrBASTRating::from('tr_bast_rating as t')
            ->leftJoin('ms_bast_rating_legend as l', function ($join) {
                // Postgres: cocokkan score ke rentang legend; batasi legend aktif
                // Jika rating_score bertipe integer/decimal, cast tidak wajib;
                // kalau kolom text, pakai cast numeric.
                $join->on(DB::raw('t.rating_score::numeric'), '>=', DB::raw('l.rating_legend_from::numeric'))
                    ->on(DB::raw('t.rating_score::numeric'), '<=', DB::raw('l.rating_legend_to::numeric'))
                    ->where('l.status', 'A');
            })
            ->where('t.bast_id', $bast->bastid)
            ->orderBy('t.rating_no')
            ->get([
                't.id',
                't.rating_no',
                't.rating_name',
                't.rating_score',
                'l.rating_legend_name',
            ]);

        $loginUsername = $user->username ?? $user->name ?? null;
        $canUpload = $bast->created_by === $loginUsername;

        return view('pages.bast.showbast', [
            'bast' => $bast,
            'hash' => $hash,
            'eid_bastid' => $eid_bastid,
            'poUrl' => $poUrl,
            'sppbUrl' => $sppbUrl,
            'csUrl' => $csUrl,
            'ratingLegendName' => $ratingLegendName,
            'bastRatingRows' => $bastRatingRows,
            'canUpload' => $canUpload,
        ]);
    }

    public function approveBast(Request $request, $docid)
    {
        $user = $request->user();
        $doctype = 'BA';

        $bast = TrBast::with('creator')->where('bastid', $docid)->first();
        if (!$bast) {
            return response()->json(['success' => false, 'message' => 'BAST not found'], 404);
        }

        // Ambil rating dari request
        $ratingScores = $this->extractRatings($request);

        \Log::info('[approveBast] payload', [
            'all' => $request->all(),
            'rating_scores' => $ratingScores,
        ]);

        $eid = Hashids::encode($bast->id);
        $docUrl = url('/showbast/' . $eid);
        $fullname = data_get($bast, 'creator.name') ?: $bast->created_by;

        return \DB::transaction(function () use ($user, $doctype, $bast, $ratingScores, $docUrl, $fullname) {

            $result = app(ApprovalController::class)->approveStep(
                $bast->bastid,
                $doctype,
                $user->username,
                $user->name,

                // FINAL APPROVER
                function (string $refnbr, Carbon $now) use ($bast, $fullname, $docUrl) {
                    // JANGAN jalankan applyBastApprovalSideEffects di final
                    // karena requirement: hanya untuk approval level 1.00 saja

                    $bast->status = 'C';
                    $bast->completed_by = auth()->user()->username;
                    $bast->completed_at = $now;
                    $bast->save();

                    app(ApprovalController::class)->notifyRequesterOnStatus(
                        $bast->bastid,
                        'BAST',
                        'C',
                        $bast->created_by,
                        $docUrl,
                        [
                            'cpnyid' => $bast->cpny_id ?? '',
                            'deptname' => $bast->department_id ?? '',
                            'date' => $bast->bastdate,
                            'info' => $bast->keperluan,
                            'fullname' => $fullname,
                            'createdby' => $fullname,
                        ]
                    );
                },

                // NEXT APPROVER
                function ($next, Carbon $now) use ($bast, $docUrl, $ratingScores) {

                    /*
                    * Jika setelah approve current step, next approver adalah level 2.00,
                    * berarti current approver yang baru saja approve adalah level 1.00.
                    *
                    * Jadi applyBastApprovalSideEffects hanya jalan sekali,
                    * yaitu setelah approval level 1.00.
                    */
                    if ((float) ($next['aprv_leveling'] ?? 0) > 1.00) {
                        $this->applyBastApprovalSideEffects($bast, $now, $ratingScores);
                    }

                    app(ApprovalController::class)->notifyFirstApprover(
                        $bast->bastid,
                        'BA',
                        'P',
                        'BAST',
                        $docUrl,
                        [
                            'info' => $bast->keperluan,
                            'createdby' => $bast->created_by,
                            'date' => $now->toDateTimeString(),
                        ]
                    );

                    $bast->completed_by = auth()->user()->username;
                    $bast->completed_at = $now;
                    $bast->save();
                }
            );

            if (!$result['ok']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Approve failed',
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'Task approved successfully',
                'rating_vendor' => $bast->rating_vendor,
            ]);
        });
    }

    public function approveBast_zzz(Request $request, $docid)
    {
        $user = $request->user();
        $doctype = 'BA';

        $bast = TrBast::with('creator')->where('bastid', $docid)->first();
        if (!$bast) {
            return response()->json(['success' => false, 'message' => 'BAST not found'], 404);
        }

        // 🔽 inilah kuncinya
        $ratingScores = $this->extractRatings($request);

        // (opsional) log untuk debugging
        \Log::info('[approveBast] payload', [
            'all' => $request->all(),
            'rating_scores' => $ratingScores,
        ]);

        $eid = Hashids::encode($bast->id);
        $docUrl = url('/showbast/'.$eid);
        $fullname = data_get($bast, 'creator.name') ?: $bast->created_by;

        return \DB::transaction(function () use ($user, $doctype, $bast, $ratingScores, $docUrl, $fullname) {
            $result = app(ApprovalController::class)->approveStep(
                $bast->bastid,
                $doctype,
                $user->username,
                $user->name,

                // FINAL
                function (string $refnbr, Carbon $now) use ($bast, $fullname, $docUrl, $ratingScores) {
                    $this->applyBastApprovalSideEffects($bast, $now, $ratingScores);

                    $bast->status = 'C';
                    $bast->completed_by = auth()->user()->username;
                    $bast->completed_at = $now;
                    $bast->save();

                    app(ApprovalController::class)->notifyRequesterOnStatus(
                        $bast->bastid, 'BAST', 'C', $bast->created_by, $docUrl, [
                            'cpnyid' => $bast->cpny_id ?? '',
                            'deptname' => $bast->department_id ?? '',
                            'date' => $bast->bastdate,
                            'info' => $bast->keperluan,
                            'fullname' => $fullname,
                            'createdby' => $fullname,
                        ]
                    );
                },

                // NEXT APPROVER
                function ($next, Carbon $now) use ($bast, $docUrl, $ratingScores) {
                    // if (isset($next['aprv_leveling']) && $next['aprv_leveling'] === '2.00') {
                    //     // efek samping setelah lolos level 1
                    //     $this->applyBastApprovalSideEffects($bast, $now, $ratingScores);
                    // }
                    if ((float) ($next['aprv_leveling'] ?? 0) >= 2) {
                        $this->applyBastApprovalSideEffects($bast, $now, $ratingScores);
                    }

                    app(ApprovalController::class)->notifyFirstApprover(
                        $bast->bastid, 'BA', 'P', 'BAST', $docUrl, [
                            'info' => $bast->keperluan,
                            'createdby' => $bast->created_by,
                            'date' => $now->toDateTimeString(),
                        ]
                    );

                    $bast->completed_by = auth()->user()->username;
                    $bast->completed_at = $now;
                    $bast->save();
                }
            );

            if (!$result['ok']) {
                \DB::rollBack();

                return response()->json(['success' => false, 'message' => $result['message'] ?? 'Approve failed'], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'Task approved successfully',
                'rating_vendor' => $bast->rating_vendor,
            ]);
        });
    }

    public function approveBast_xxx(Request $request, $docid)
    {
        $user = $request->user();
        $doctype = 'BA';

        $bast = TrBast::with('creator')->where('bastid', $docid)->first();
        if (!$bast) {
            return response()->json(['success' => false, 'message' => 'BAST not found'], 404);
        }

        $rating = (int) $request->input('rating_vendor', 0);

        $eid = Hashids::encode($bast->id);
        $docUrl = url('/showbast/'.$eid);
        $fullname = data_get($bast, 'creator.name') ?: $bast->created_by;

        $result = app(ApprovalController::class)->approveStep(
            $bast->bastid,
            $doctype,
            $user->username,
            $user->name,

            // ✅ FINAL APPROVAL (C = Completed)
            function (string $refnbr, Carbon $now) use ($bast, $fullname, $docUrl, $rating) {
                // ✅ APPLY SIDE EFFECTS AGAIN (agar final tetap sync)
                $this->applyBastApprovalSideEffects($bast, $rating, $now);

                $bast->status = 'C';
                $bast->completed_by = auth()->user()->username;
                $bast->completed_at = $now;
                $bast->save();

                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $bast->bastid,
                    'BAST',
                    'C',
                    $bast->created_by,
                    $docUrl,
                    [
                        'cpnyid' => $bast->cpny_id ?? '',
                        'deptname' => $bast->department_id ?? '',
                        'date' => $bast->bastdate,
                        'info' => $bast->keperluan,
                        'fullname' => $fullname,
                        'createdby' => $fullname,
                    ]
                );
            },

            // ✅ NEXT APPROVER (P = Pending next approver)
            function ($next, Carbon $now) use ($bast, $docUrl, $rating) {
                /*
                 * ✅ ONLY LEVEL 1.00 -> simpan rating & penalty!
                 */
                if (isset($next['aprv_leveling']) && $next['aprv_leveling'] === '2.00') {
                    // berarti sekarang masih di approve level 1 (yang baru approve)
                    $this->applyBastApprovalSideEffects($bast, $rating, $now);
                }

                app(ApprovalController::class)->notifyFirstApprover(
                    $bast->bastid,
                    'BA',
                    'P',
                    'BAST',
                    $docUrl,
                    [
                        'info' => $bast->keperluan,
                        'createdby' => $bast->created_by,
                        'date' => $now->toDateTimeString(),
                    ]
                );

                $bast->completed_by = auth()->user()->username;
                $bast->completed_at = $now;
                $bast->save();
            }
        );

        if (!$result['ok']) {
            return response()->json(['success' => false, 'message' => $result['message'] ?? 'Approve failed'], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Task approved successfully',
        ]);
    }

    public function rejectBast(Request $request, $docid)
    {
        $user = $request->user();
        $doctype = 'BA';

        $bast = TrBast::with('creator')->where('bastid', $docid)->first();

        if (!$bast) {
            return response()->json([
                'success' => false,
                'message' => 'BAST not found'
            ], 404);
        }

        $eid = Hashids::encode($bast->id);
        $docUrl = url('/showbast/' . $eid);
        $fullname = data_get($bast, 'creator.name') ?: $bast->created_by;

        $result = app(ApprovalController::class)->rejectStep(
            $bast->bastid,
            $doctype,
            $user->username,
            $user->name,

            function (string $refnbr, Carbon $now) use ($bast, $fullname, $docUrl, $user) {
                // update status BAST
                $bast->status       = 'R';
                $bast->completed_by = $user->username;
                $bast->completed_at = $now;
                $bast->updated_by   = $user->username;
                $bast->updated_at   = $now;
                $bast->save();

                // release PO Term supaya BAST bisa dibuat ulang
                $this->releasePoTermBast($bast, $user->username, $now);

                // optional: tandai detail R
                // \App\Models\TrBastdetail::where('bastid', $bast->bastid)->update(['status' => 'R']);

                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $bast->bastid,
                    'BAST',
                    'R',
                    $bast->created_by,
                    $docUrl,
                    [
                        'cpnyid'    => $bast->cpny_id ?? $bast->cpnyid ?? '',
                        'deptname'  => $bast->department_id ?? $bast->departementid ?? '',
                        'date'      => $now->toDateString(),
                        'info'      => $bast->keperluan,
                        'fullname'  => $fullname,
                        'name'      => $fullname,
                        'createdby' => $fullname,
                    ]
                );

                // simpan komentar jika ada
                try {
                    app('App\Http\Controllers\SendCommentController')
                        ->sendmsg($bast->id, 'BA', request());
                } catch (\Throwable $e) {
                    // sengaja diabaikan
                }
            }
        );

        if (!$result['ok']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Reject failed'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'BAST rejected successfully'
        ]);
    }

    public function reviseBast(Request $request, $docid)
    {
        $user = $request->user();
        $doctype = 'BA';

        $bast = TrBast::with('creator')->where('bastid', $docid)->first();
        if (!$bast) {
            return response()->json(['success' => false, 'message' => 'BAST not found'], 404);
        }

        $eid = Hashids::encode($bast->id);
        $docUrl = url('/showbast/'.$eid);
        $fullname = data_get($bast, 'creator.name') ?: $bast->created_by;

        $result = app(ApprovalController::class)->reviseStep(
            $bast->bastid,            // refnbr
            $doctype,                 // PT
            $user->username,          // actor
            $user->name,              // actor
            function (string $refnbr, Carbon $now) use ($bast, $fullname, $docUrl) {
                // === HEADER BAST -> D ===
                $bast->status = 'D';
                $bast->completed_by = auth()->user()->username;
                $bast->completed_at = $now;
                $bast->save();

                // (opsional) DETAIL -> D
                // \App\Models\TrBastdetail::where('bastid', $bast->bastid)->update(['status' => 'D']);

                // === Email ke requester ===
                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $bast->bastid,
                    'BAST',
                    'D',
                    $bast->created_by,
                    $docUrl,
                    [
                        'cpnyid' => $bast->cpny_id ?? $bast->cpnyid ?? '',
                        'deptname' => $bast->department_id ?? $bast->departementid ?? '',
                        'date' => $now->toDateString(),
                        'info' => $bast->keperluan,
                        'fullname' => $fullname,
                        'name' => $fullname,
                        'createdby' => $fullname,   // <<< tambahkan ini
                    ]
                );

                // === Simpan komentar (jika ada) ===
                try {
                    app('App\Http\Controllers\SendCommentController')->sendmsg($bast->id, 'BA', request());
                } catch (\Throwable $e) {
                }
            }
        );

        if (!$result['ok']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Revise failed',
            ], 403);
        }

        return response()->json(['success' => true, 'message' => 'BAST revised successfully']);
    }

    public function printBast($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $authUser = Auth::user();
        if (!$authUser) {
            return redirect()->route('login');
        }

        // Ambil BAST + relasi
        $bast = TrBast::with(['creator', 'userpeminta', 'location', 'subLocation'])
            ->findOrFail($id);

        $level1Approved = TrApproval::query()
            ->where('refnbr', $bast->bastid)
            ->where('status', 'A')
            ->whereRaw('CAST(aprv_leveling AS numeric) = 1.00')
            ->exists();

        abort_if(!$level1Approved, 403, 'BAST belum dapat dicetak. Approval level 1 belum disetujui.');

        // Approval list
        // $approval = TrApproval::query()
        //     ->where('refnbr', $bast->bastid)
        //     ->where('status', '<>', 'X')
        //     ->orderByRaw('CAST(aprv_leveling AS numeric) ASC')
        //     ->orderBy('created_at', 'ASC')
        //     ->get();
        $refnbr = $bast->bastid;
        $apprTable = (new TrApproval())->getTable(); // "tr_approval"

        $approval = TrApproval::query()
            ->where('refnbr', $refnbr)
            ->where('status', '<>', 'X')
            ->reorder()
            ->orderBy('created_at', 'asc')
            ->orderBy('aprv_leveling', 'asc')
            ->orderBy('id', 'asc')
            ->get([
                'aprv_leveling',
                'aprv_name',
                'aprv_datebefore',
                'aprv_dateafter',
                'status',
                'aprv_type',
                'aprv_condition',
            ]);

        $approve_count = $approval->count();

        // Company
        $company = MsCompany::where('cpny_id', $bast->cpny_id)->first();

        // Mapping status dokumen
        switch ($bast->status) {
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
            'title' => 'Berita Acara Serah Terima',
            'doc_type' => 'BAST',
            'docid' => $bast->bastid,
            'department_id' => $bast->department_id,
            'cpnyname' => optional($company)->cpny_name,
            'parent' => optional($company)->parent,
            'project' => optional($company)->project,

            // identitas & tanggal
            'created_by_username' => $bast->created_by,
            'created_by_name' => ucwords(strtolower(optional($bast->creator)->name ?? $bast->created_by)),
            'created_at_fmt' => optional($bast->created_at)->format('d F Y'),
            'req_date_fmt' => optional($bast->created_at)->format('d M Y H:i'),
            'bastdate' => $bast->bastdate
                                        ? Carbon::parse($bast->bastdate)->format('d F Y')
                                        : '',

            // konten utama
            'keperluan' => $bast->keperluan,
            'status_doc' => $status_doc,
            // kalau nanti ada relasi requestType, tetap aman
            'requesttype_name' => optional($bast->requestType ?? null)->requesttype_name,

            // tanggal pekerjaan
            'startdate_fmt' => $bast->startdate
                                        ? Carbon::parse($bast->startdate)->format('d/m/Y')
                                        : '',
            'enddate_fmt' => $bast->enddate
                                        ? Carbon::parse($bast->enddate)->format('d/m/Y')
                                        : '',
            'handoverdate_fmt' => $bast->handoverdate
                                        ? Carbon::parse($bast->handoverdate)->format('d/m/Y H:i')
                                        : '',

            // lokasi
            'location_name' => optional($bast->location)->location_name ?? $bast->location_id,
            'sub_location_name' => optional($bast->subLocation)->sub_location_name ?? $bast->sub_location_id,

            // angka2
            'penalty_per_day' => $bast->penalty,
            'days_penalty' => $bast->days_penalty,
            'total_penalty' => $bast->total_penalty,
            'bast_amount' => $bast->bast_amount,
            'realize_amount' => $bast->realize_amount,
            'spkpic' => $bast->spkpic,
            'spkwarranty' => $bast->spkwarranty,
        ];

        $pdf = \PDF::loadView(
            'pages.bast.pdf_bast',
            array_merge($data, [
                'bast' => $bast,
                'approval' => $approval,
                'approve_count' => $approve_count,
            ])
        );

        // $pdf->setPaper('A4', ($approve_count <= 5) ? 'portrait' : 'landscape');

        return $pdf->stream("pdf_bast_{$bast->bastid}.pdf");
    }

    public function printBastVendor($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $authUser = Auth::user();
        if (!$authUser) {
            return redirect()->route('login');
        }

        // Ambil BAST + relasi
        $bast = TrBast::with(['creator', 'userpeminta', 'location', 'subLocation'])
            ->findOrFail($id);

        $level1Approved = TrApproval::query()
            ->where('refnbr', $bast->bastid)
            ->where('status', 'A')
            ->whereRaw('CAST(aprv_leveling AS numeric) = 1.00')
            ->exists();

        abort_if(!$level1Approved, 403, 'BAST belum dapat dicetak. Approval level 1 belum disetujui.');

        // Approval list
        $approval = TrApproval::query()
            ->where('refnbr', $bast->bastid)
            ->where('status', '<>', 'X')
            ->orderByRaw('CAST(aprv_leveling AS numeric) ASC')
            ->orderBy('created_at', 'ASC')
            ->get();

        $approvalLevel1 = $approval->first(function ($a) {
            return (float) $a->aprv_leveling === 1.00;
        });

        $approve_count = $approval->count();

        // Company
        $company = MsCompany::where('cpny_id', $bast->cpny_id)->first();

        // Mapping status dokumen
        switch ($bast->status) {
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
            'title' => 'Berita Acara Serah Terima',
            'doc_type' => 'BAST',
            'docid' => $bast->bastid,
            'department_id' => $bast->department_id,
            'cpnyname' => optional($company)->cpny_name,
            'parent' => optional($company)->parent,
            'project' => optional($company)->project,

            // identitas & tanggal
            'created_by_username' => $bast->created_by,
            'created_by_name' => ucwords(strtolower(optional($bast->creator)->name ?? $bast->created_by)),
            'created_at_fmt' => optional($bast->created_at)->format('d F Y'),
            'req_date_fmt' => optional($bast->created_at)->format('d M Y H:i'),
            'bastdate' => $bast->bastdate
                                        ? Carbon::parse($bast->bastdate)->format('d F Y')

                                        : '',

            'approval_level1_name' => $approvalLevel1->aprv_name ?? null,
            // konten utama

            'keperluan' => $bast->keperluan,
            'status_doc' => $status_doc,
            // kalau nanti ada relasi requestType, tetap aman
            'requesttype_name' => optional($bast->requestType ?? null)->requesttype_name,

            // tanggal pekerjaan
            'startdate_fmt' => $bast->startdate
                                        ? Carbon::parse($bast->startdate)->format('d/m/Y')
                                        : '',
            'enddate_fmt' => $bast->enddate
                                        ? Carbon::parse($bast->enddate)->format('d/m/Y')
                                        : '',
            'handoverdate_fmt' => $bast->handoverdate
                                        ? Carbon::parse($bast->handoverdate)->format('d/m/Y H:i')
                                        : '',

            // lokasi
            'location_name' => optional($bast->location)->location_name ?? $bast->location_id,
            'sub_location_name' => optional($bast->subLocation)->sub_location_name ?? $bast->sub_location_id,

            // angka2
            'penalty_per_day' => $bast->penalty,
            'days_penalty' => $bast->days_penalty,
            'total_penalty' => $bast->total_penalty,
            'bast_amount' => $bast->bast_amount,
            'realize_amount' => $bast->realize_amount,
            'spkpic' => $bast->spkpic,
            'spkwarranty' => $bast->spkwarranty,
        ];

        // Kirim ke view
        $pdf = \PDF::loadView(
            'pages.bast.pdf_bast_vendor',
            array_merge($data, [
                'bast' => $bast,
                'approval' => $approval,
                'approve_count' => $approve_count,
            ])
        );

        // Portrait jika <= 5 approver, else landscape
        // $pdf->setPaper('A4', ($approve_count <= 5) ? 'portrait' : 'landscape');

        return $pdf->stream("pdf_bast_vendor_{$bast->bastid}.pdf");
    }

    private function extractRatings(Request $request): array
    {
        // 1) bentuk map langsung: rating_scores = { "<id>": <score>, "RATING01": <score>, "no:1": <score> }
        $ratingScores = $request->input('rating_scores', []);
        if (!is_array($ratingScores)) {
            $ratingScores = [];
        }

        // 2) bentuk array: ratings = [ {id, rating_id, rating_no, rating_score/score}, ... ]
        $ratingsItems = $request->input('ratings', []);
        if (!is_array($ratingsItems)) {
            $ratingsItems = [];
        }

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
            if (!is_array($it)) {
                continue;
            }

            $score = $it['rating_score'] ?? $it['score'] ?? null;
            if ($score === null) {
                continue;
            }

            $score = (float) $score;
            if ($score <= 0) {
                continue;
            }

            if (!empty($it['id'])) {
                $ratingScores[(string) $it['id']] = $score;
            }
            if (!empty($it['rating_id'])) {
                $ratingScores[(string) $it['rating_id']] = $score;
            }
            if (!empty($it['rating_no'])) {
                $ratingScores['no:'.(string) $it['rating_no']] = $score;
            }
        }

        return $ratingScores; // unified map
    }

    private function applyBastApprovalSideEffects(TrBast $bast, Carbon $approveAt, array $ratingScores = []): TrBast
    {
        // 1) rating
        $this->applyBastVendorRating($bast, $ratingScores);

        // 2) penalty + handoverdate
        $this->applyBastPenalty($bast, $approveAt);

        // simpan sekali
        $bast->save();

        return $bast;
    }

    private function applyBastPenalty(TrBast $bast, Carbon $approveAt): void
    {
        // handoverdate = tanggal approve
        $bast->handoverdate = $approveAt->toDateString();

        $daysPenalty = 0;
        $penaltyDaily = 0.0;
        $totalPenaltyRaw = 0.0;
        $totalPenalty = 0.0;
        $maxPenalty = 0.0;
        $maxPercentAmount = 0.0;

        if (!empty($bast->enddate)) {
            $end = Carbon::parse($bast->enddate)->startOfDay();
            $ho = $approveAt->copy()->startOfDay();

            if ($ho->gt($end)) {
                $lateStart = $end->copy()->addDay(); // mulai telat = enddate + 1
                $lateEnd = $ho->copy();              // sampai tanggal approve / handover

                // Ambil hari libur dari sys_calendar_exception / sys_calendar
                $holidayDates = SysCalendar::query()
                    ->where('status', 'A')
                    ->whereBetween('date_calendar', [
                        $lateStart->toDateString(),
                        $lateEnd->toDateString()
                    ])
                    ->pluck('date_calendar')
                    ->map(fn ($d) => Carbon::parse($d)->toDateString())
                    ->all();

                // jadikan set agar lookup cepat
                $holidaySet = array_fill_keys($holidayDates, true);

                // Hitung business day manual: exclude weekend + exclude holiday
                $cursor = $lateStart->copy();

                while ($cursor->lte($lateEnd)) {
                    $isWeekend = $cursor->isSaturday() || $cursor->isSunday();
                    $isHoliday = isset($holidaySet[$cursor->toDateString()]);

                    if (!$isWeekend && !$isHoliday) {
                        ++$daysPenalty;
                    }

                    $cursor->addDay();
                }

                // Nilai BAST / SPK
                $amount = (float) ($bast->bast_amount ?? 0);

                // Lookup penalty per hari dari MsPenalty sesuai bast_amount
                $penRow = MsPenalty::query()
                    ->where('status', 'A')
                    ->where('min_amount', '<=', $amount)
                    ->where('max_amount', '>=', $amount)
                    ->orderBy('min_amount')
                    ->first();

                if ($penRow) {
                    $penaltyDaily = (float) ($penRow->penalty ?? 0);

                    // Ambil maksimal persen dari ms_penalty.max_percent_amount
                    // Contoh isi max_percent_amount = 20, artinya 20%
                    $maxPercentAmount = (float) ($penRow->max_percent_amount ?? 0);

                    // Total penalti sebelum dibatasi maksimal
                    $totalPenaltyRaw = $daysPenalty * $penaltyDaily;

                    // Maksimal penalti = max_percent_amount % x nilai BAST / SPK
                    $maxPenalty = $amount * ($maxPercentAmount / 100);

                    // Total penalti final tidak boleh lebih dari batas maksimal
                    if ($maxPenalty > 0) {
                        $totalPenalty = min($totalPenaltyRaw, $maxPenalty);
                    } else {
                        // kalau max_percent_amount kosong / 0, tidak dibatasi
                        $totalPenalty = $totalPenaltyRaw;
                    }
                }

                \Log::info('[BAST][Penalty] calc', [
                    'bastid' => $bast->bastid,
                    'enddate' => $end->toDateString(),
                    'approve' => $ho->toDateString(),
                    'late_start' => $lateStart->toDateString(),
                    'late_end' => $lateEnd->toDateString(),
                    'holidays' => $holidayDates,
                    'daysPenalty' => $daysPenalty,
                    'amount' => $amount,
                    'penaltyDaily' => $penaltyDaily,
                    'maxPercentAmount' => $maxPercentAmount,
                    'totalPenaltyRaw' => $totalPenaltyRaw,
                    'maxPenalty' => $maxPenalty,
                    'totalPenaltyFinal' => $totalPenalty,
                    'isCapped' => $maxPenalty > 0 && $totalPenaltyRaw > $maxPenalty,
                    'penalty_id' => $penRow->penalty_id ?? null,
                ]);
            }
        }

        $bast->days_penalty = $daysPenalty;
        $bast->penalty = $penaltyDaily; // tarif/hari dari MsPenalty
        $bast->total_penalty = $totalPenalty;

        // Realisasi setelah dikurangi penalti final
        $bastAmount = (float) ($bast->bast_amount ?? 0);
        $bast->realize_amount = max(0, $bastAmount - $totalPenalty);

        \Log::info('[BAST][Penalty] applied', [
            'bastid' => $bast->bastid,
            'handoverdate' => $bast->handoverdate,
            'days_penalty' => $bast->days_penalty,
            'penalty_daily' => $bast->penalty,
            'max_percent_amount' => $maxPercentAmount,
            'total_penalty_raw' => $totalPenaltyRaw,
            'max_penalty' => $maxPenalty,
            'total_penalty_final' => $bast->total_penalty,
            'realize_amount' => $bast->realize_amount,
        ]);
    }

    private function applyBastPenalty_old(TrBast $bast, Carbon $approveAt): void
    {
        // handoverdate = tanggal approve
        $bast->handoverdate = $approveAt->toDateString();

        $daysPenalty = 0;
        $penaltyDaily = 0.0;
        $totalPenalty = 0.0;

        if (!empty($bast->enddate)) {
            $end = Carbon::parse($bast->enddate)->startOfDay();
            $ho = $approveAt->copy()->startOfDay();

            if ($ho->gt($end)) {
                $lateStart = $end->copy()->addDay(); // mulai telat = enddate + 1
                $lateEnd = $ho->copy();            // sampai tanggal approve (handover)

                // Ambil hari libur dari sys_calendar_exception (pgsql2)
                $holidayDates = SysCalendar::query()
                    ->where('status', 'A') // sesuaikan kalau status beda
                    ->whereBetween('date_calendar', [$lateStart->toDateString(), $lateEnd->toDateString()])
                    ->pluck('date_calendar')
                    ->map(fn ($d) => Carbon::parse($d)->toDateString())
                    ->all();

                // jadikan set biar lookup cepat
                $holidaySet = array_fill_keys($holidayDates, true);

                // Hitung business day manual (exclude weekend + exclude holiday)
                $cursor = $lateStart->copy();
                while ($cursor->lte($lateEnd)) {
                    $isWeekend = $cursor->isSaturday() || $cursor->isSunday();
                    $isHoliday = isset($holidaySet[$cursor->toDateString()]);

                    if (!$isWeekend && !$isHoliday) {
                        ++$daysPenalty;
                    }
                    $cursor->addDay();
                }

                // Lookup penalty per hari dari MsPenalty sesuai bast_amount
                $amount = (float) ($bast->bast_amount ?? 0);

                $penRow = MsPenalty::query()
                    ->where('status', 'A')
                    ->where('min_amount', '<=', $amount)
                    ->where('max_amount', '>=', $amount)
                    ->orderBy('min_amount')
                    ->first();

                $penaltyDaily = (float) ($penRow->penalty ?? 0);
                $totalPenalty = $daysPenalty * $penaltyDaily;

                \Log::info('[BAST][Penalty] calc', [
                    'bastid' => $bast->bastid,
                    'enddate' => $end->toDateString(),
                    'approve' => $ho->toDateString(),
                    'late_start' => $lateStart->toDateString(),
                    'late_end' => $lateEnd->toDateString(),
                    'holidays' => $holidayDates,
                    'daysPenalty' => $daysPenalty,
                    'amount' => $amount,
                    'penaltyDaily' => $penaltyDaily,
                    'totalPenalty' => $totalPenalty,
                    'penalty_id' => $penRow->penalty_id ?? null,
                ]);
            }
        }

        $bast->days_penalty = $daysPenalty;
        $bast->penalty = $penaltyDaily; // tarif/hari dari MsPenalty
        $bast->total_penalty = $totalPenalty;

        // optional: realize_amount
        $bastAmount = (float) ($bast->bast_amount ?? 0);
        $bast->realize_amount = max(0, $bastAmount - $totalPenalty);

        \Log::info('[BAST][Penalty] applied', [
            'bastid' => $bast->bastid,
            'handoverdate' => $bast->handoverdate,
            'days_penalty' => $bast->days_penalty,
            'penalty_daily' => $bast->penalty,
            'total_penalty' => $bast->total_penalty,
            'realize_amount' => $bast->realize_amount,
        ]);
    }

    private function applyBastVendorRating(TrBast $bast, array $ratingScores = []): void
    {
        // === Update skor per baris (1-10)
        if (!empty($ratingScores)) {
            $rows = TrBASTRating::where('bast_id', $bast->bastid)->get();

            foreach ($rows as $row) {
                $score = null;

                if (array_key_exists($row->id, $ratingScores)) {
                    $score = $ratingScores[$row->id];
                } elseif (!is_null($row->rating_id) && array_key_exists($row->rating_id, $ratingScores)) {
                    $score = $ratingScores[$row->rating_id];
                }

                if (!is_null($score)) {
                    $clamped = max(1, min(10, (float) $score));
                    $row->rating_score = $clamped;
                    $row->updated_by = auth()->user()->username ?? 'system';
                    $row->updated_at = now('Asia/Jakarta');
                    $row->save();
                }
            }
        }

        // === Hitung rata-rata terbaru (abaikan null/0)
        $agg = TrBASTRating::where('bast_id', $bast->bastid)
            ->whereNotNull('rating_score')
            ->where('rating_score', '>', 0)
            ->selectRaw('AVG(rating_score)::numeric as avg_score, COUNT(*) as cnt')
            ->first();

        $avgScore = $agg && $agg->cnt > 0 ? (float) $agg->avg_score : 0.0;

        // Simpan ke header
        $bast->rating_vendor = $avgScore > 0 ? round($avgScore, 1) : null;

        \Log::info('[BAST][Rating] applied', [
            'bastid' => $bast->bastid,
            'avg' => $bast->rating_vendor,
            'cnt' => $agg->cnt ?? 0,
        ]);
    }


    public function getBastRatings(string $bastid)
    {
        // Optional: validasi hak akses lihat BAST di sini

        $rows = TrBASTRating::where('bast_id', $bastid)
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
            'data' => $rows,
        ]);
    }

    public function editBast($hash)
    {
        $bastId = Hashids::decode($hash)[0] ?? null;
        abort_if(!$bastId, 404);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $bast = TrBast::query()
            ->with(['location', 'subLocation', 'creator', 'userpeminta'])
            ->findOrFail($bastId);

        return view('pages.bast.editbast', [
            'bast' => $bast,
            'hash' => $hash,
        ]);
    }

    public function updateBast(Request $request, string $hash)
    {
        $request->validate([
            'location_id' => 'required|string',
            'sub_location_id' => 'required|string',
            'attachments.*' => 'file|max:10240', // Photo After (10MB/file)
            'attachments_ba.*' => 'file|max:10240', // Attachments BA (10MB/file)
        ]);

        // decode hash dari route param
        $decoded = Hashids::decode($hash);
        if (empty($decoded)) {
            return response()->json(['message' => 'Bast hash tidak valid.'], 422);
        }
        $bastPkId = (int) $decoded[0];

        /** @var TrBast|null $bast */
        $bast = TrBast::find($bastPkId);
        if (!$bast) {
            return response()->json(['message' => 'Data TrBast tidak ditemukan.'], 404);
        }

        $doctype = 'BA'; // BAST
        $user = $request->user();
        $username = $user->username ?? 'system';
        $dt = Carbon::now('Asia/Jakarta');

        /** @var ApprovalController $approvalCtl */
        $approvalCtl = app(ApprovalController::class);
        $approvalCtl->loadLines($doctype, $bast->cpny_id, $bast->department_id);

        DB::beginTransaction();
        try {
            // 1) update header
            $bast->location_id = $request->location_id;
            $bast->sub_location_id = $request->sub_location_id;

            // jika memang setiap edit harus balik ke pending
            $bast->status = 'P';
            $bast->updated_by = $username;
            $bast->updated_at = $dt;
            $bast->save();

            // 2) upload Photo After (doctype BQ) -> folder BQ
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr' => $bast->bastid,
                    'doctype' => 'BQ',
                    'cpnyid' => $bast->cpny_id,
                    'departementid' => $bast->department_id,
                    'base_folder' => 'att-purchasing-app/bq',   // ✅ FIX
                    'created_by' => $username,
                ];

                $files = (array) $request->file('attachments');

                /** @var TrAttachmentController $uploader */
                $uploader = app(TrAttachmentController::class);
                $uploader->uploadInternal($meta, $files);
            }

            // 3) upload Attachments BA (doctype BA) -> folder BA
            if ($request->hasFile('attachments_ba')) {
                $meta = [
                    'refnbr' => $bast->bastid,
                    'doctype' => $doctype,
                    'cpnyid' => $bast->cpny_id,
                    'departementid' => $bast->department_id,
                    'base_folder' => 'att-purchasing-app/ba',
                    'created_by' => $username,
                ];

                $files = (array) $request->file('attachments_ba');

                /** @var TrAttachmentController $uploader */
                $uploader = app(TrAttachmentController::class);
                $uploader->uploadInternal($meta, $files);
            }

            // 4) regenerate approval (hindari dobel)
            // HAPUS approval lama dulu (sesuaikan nama tabel/model kamu)
            // TrApproval::where('docid', $bast->bastid)
            //     ->where('doctype', $doctype)
            //     ->delete();

            $ctx = ['ignore_nominal' => true];

            [$firstApprovalUsernames, $linesCount] = $approvalCtl->generateForDocument(
                $bast->bastid,
                $doctype,
                $bast->cpny_id,
                $bast->department_id,
                $username,
                $ctx,
                $dt
            );

            if (!empty($firstApprovalUsernames)) {
                $bast->completed_by = is_array($firstApprovalUsernames)
                    ? implode(',', $firstApprovalUsernames)
                    : (string) $firstApprovalUsernames;
                $bast->save();
            }

            DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'Bast updated successfully.',
                'bastid' => $bast->bastid,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'message' => 'Gagal update BAST.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function releasePoTermBast(TrBast $bast, string $username, Carbon $dt): void
    {
        TrPOterm::query()
            ->where('bastid', $bast->bastid)
            ->update([
                'bastid'     => null,
                'updated_by' => $username,
                'updated_at' => $dt,
            ]);
    }

    public function cancelBast(Request $request, string $hash)
    {
        $decoded = Hashids::decode($hash);

        if (empty($decoded)) {
            return response()->json([
                'success' => false,
                'message' => 'Hash BAST tidak valid.',
            ], 422);
        }

        $bastId = (int) $decoded[0];

        $user = $request->user() ?: Auth::user();
        $username = $user->username ?? 'system';
        $dt = Carbon::now('Asia/Jakarta');

        DB::beginTransaction();

        try {
            $bast = TrBast::query()
                ->where('id', $bastId)
                ->lockForUpdate()
                ->first();

            if (!$bast) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Data BAST tidak ditemukan.',
                ], 404);
            }

            if (in_array($bast->status, ['X', 'C'], true)) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => $bast->status === 'X'
                        ? 'BAST ini sudah di-cancel.'
                        : 'BAST yang sudah completed tidak bisa di-cancel.',
                ], 422);
            }

            // 1) Update status BAST menjadi Cancel
            $bast->status = 'X';
            $bast->updated_by = $username;
            $bast->updated_at = $dt;

            // kalau kolom ini memang ada di tr_bast, boleh diisi
            if (isset($bast->completed_by)) {
                $bast->completed_by = $username;
            }

            if (isset($bast->completed_at)) {
                $bast->completed_at = $dt;
            }

            $bast->save();

            // 2) Release PO Term supaya BAST bisa dibuat ulang
            $this->releasePoTermBast($bast, $username, $dt);

            // 3) Tutup approval BAST yang masih pending
            TrApproval::query()
                ->where('refnbr', $bast->bastid)
                ->where('status', 'P')
                ->update([
                    'status'     => 'X',
                    'updated_by' => $username,
                    'updated_at' => $dt,
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'BAST berhasil di-cancel.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Gagal cancel BAST.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
