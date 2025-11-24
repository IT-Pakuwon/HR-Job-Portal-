<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use App\Models\TrRfca;
use App\Models\TrSPPB;
use App\Models\TrSPPJ;
use App\Models\TrSPPK;
use App\Models\TrSPPT;
use App\Models\TrPO;
use App\Models\TrCS;
use App\Models\MsRfcaStep;
use App\Models\TrRfcaStep;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Str;


class RfcaListController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $cpny_id = $user->cpny_id ?? '';

        /**
         * Rfca Jobs:
         * - rfca_type masih kosong/null
         * - BELUM ada satupun step dengan progress_approval = 't'
         */
        $rfcajobs = TrRfca::when($cpny_id, function ($q) use ($cpny_id) {
                $q->where('cpny_id', $cpny_id);
            })
            ->where(function ($q) {
                $q->whereNull('rfca_type')
                  ->orWhere('rfca_type', '');
            })
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('tr_rfca_step as step')
                    ->whereColumn('step.rfcaid', 'tr_rfca.rfcaid')
                    ->where('step.progress_approval', 't');
            })
            ->count();

        /**
         * Finance Received:
         * - ada step dengan progress_approval = 't'
         * - rfca_step_id = 'FR' (sesuaikan dengan master)
         */
        $financeReceived = TrRfca::when($cpny_id, function ($q) use ($cpny_id) {
                $q->where('cpny_id', $cpny_id);
            })
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('tr_rfca_step as step')
                    ->whereColumn('step.rfcaid', 'tr_rfca.rfcaid')
                    ->where('step.progress_approval', 't')
                    ->where('step.rfca_step_id', 'FR'); // <- sesuaikan code step Finance
            })
            ->count();

        /**
         * Treasury Payment:
         * - step.progress_approval = 't'
         * - step.rfca_step_id = 'TP' (sesuaikan)
         */
        $treasuryPayment = TrRfca::when($cpny_id, function ($q) use ($cpny_id) {
                $q->where('cpny_id', $cpny_id);
            })
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('tr_rfca_step as step')
                    ->whereColumn('step.rfcaid', 'tr_rfca.rfcaid')
                    ->where('step.progress_approval', 't')
                    ->where('step.rfca_step_id', 'TP'); // <- sesuaikan code step Treasury
            })
            ->count();

        /**
         * Completed:
         * - step.progress_approval = 't'
         * - step.rfca_step_id = 'PC' (atau sesuai master)
         */
        $completed = TrRfca::when($cpny_id, function ($q) use ($cpny_id) {
                $q->where('cpny_id', $cpny_id);
            })
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('tr_rfca_step as step')
                    ->whereColumn('step.rfcaid', 'tr_rfca.rfcaid')
                    ->where('step.progress_approval', 't')
                    ->where('step.rfca_step_id', 'PC'); // <- sesuaikan code step Completed
            })
            ->count();

        // All RFCA tanpa filter progress_approval
        $all = TrRfca::when($cpny_id, function ($q) use ($cpny_id) {
                $q->where('cpny_id', $cpny_id);
            })
            ->count();

        return view('pages.rfca.rfcalist', compact(
            'rfcajobs',
            'financeReceived',
            'treasuryPayment',
            'completed',
            'all'
        ));
    }

    public function json(Request $req)
    {
        $scope   = strtolower((string) $req->query('scope', 'rfcajobs'));
        $user    = Auth::user();
        $u       = $user->username ?? '';
        $cpny_id = $user->cpny_id ?? '';

        $draw   = (int) $req->input('draw', 1);
        $start  = (int) $req->input('start', 0);
        $length = (int) $req->input('length', 25);
        $search = trim((string) $req->input('search.value', ''));

        // ====== BASE QUERY: TrRfca + LEFT JOIN current step (progress_approval = 't') ======
        $base = TrRfca::query()
            ->from('tr_rfca')
            ->leftJoin('tr_rfca_step as step', function ($join) {
                $join->on('step.rfcaid', '=', 'tr_rfca.rfcaid')
                    ->where('step.progress_approval', 't');
            })
            ->when($cpny_id, fn($q) => $q->where('tr_rfca.cpny_id', $cpny_id));

        // Scope filter (pakai step.rfca_step_id, progress_approval = 't')
        switch ($scope) {
            case 'rfcajobs':
                // Rfca Jobs: rfca_type kosong + belum ada step active (progress_approval='t')
                $base->where(function ($q) {
                        $q->whereNull('tr_rfca.rfca_type')
                          ->orWhere('tr_rfca.rfca_type', '');
                    })
                    ->whereNull('step.id');
                break;

            case 'financereceived':
                $base->where('step.rfca_step_id', 'FR'); // sesuaikan code Finance Received
                break;

            case 'treasurypayment':
                $base->where('step.rfca_step_id', 'TP'); // sesuaikan code Treasury
                break;

            case 'completed':
                $base->where('step.rfca_step_id', 'PC');  // sesuaikan code Completed
                break;

            case 'all':
            default:
                // All RFCA: tidak tambah filter step
                break;
        }

        // Simpan base untuk total sebelum search
        $baseForCount = clone $base;

        // Kolom untuk order
        $orderColumns = [
            0 => 'tr_rfca.rfcaid',
            1 => 'tr_rfca.rfcadate',
            2 => 'tr_rfca.ponbr',
            3 => 'tr_rfca.sppbjktid',
            4 => 'tr_rfca.cpny_id',
            5 => 'tr_rfca.created_by',
        ];

        // Search
        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('tr_rfca.rfcaid', 'ilike', "%{$search}%")
                    ->orWhere('tr_rfca.ponbr', 'ilike', "%{$search}%")
                    ->orWhere('tr_rfca.sppbjktid', 'ilike', "%{$search}%")
                    ->orWhere('tr_rfca.cpny_id', 'ilike', "%{$search}%")
                    ->orWhere('tr_rfca.created_by', 'ilike', "%{$search}%")
                    ->orWhereRaw("TO_CHAR(tr_rfca.rfcadate,'YYYY-MM-DD') ILIKE ?", ["%{$search}%"]);
            });
        }

        $recordsTotal    = $baseForCount->count();
        $recordsFiltered = (clone $base)->count();

        $orderIdx = (int) $req->input('order.0.column', 1);
        $orderDir = $req->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $orderColumns[$orderIdx] ?? 'tr_rfca.rfcadate';

        $rows = $base->select([
                    'tr_rfca.id',
                    'tr_rfca.rfcaid',
                    'tr_rfca.rfcadate',
                    'tr_rfca.ponbr',
                    'tr_rfca.sppbjktid',
                    'tr_rfca.cpny_id',
                    'tr_rfca.created_by',
                    'tr_rfca.status',           // status lama (P/C/R/D) masih dipakai untuk logic revise
                    'tr_rfca.rfca_type',
                    'step.rfca_step_id as current_step_id',
                    'step.progress_approval',
                ])
                ->orderBy($orderCol, $orderDir)
                ->orderBy('tr_rfca.rfcaid', 'desc')
                ->skip($start)->take($length)
                ->get();

        // ========= ENRICH / FORMAT =========
        // Map PONBR -> id (TrPO) supaya link PO bisa dipakai
        $poIdMap = [];
        $ponbrsForMap = $rows->pluck('ponbr')->filter()->unique()->values()->all();
        if (!empty($ponbrsForMap)) {
            $poIdMap = TrPO::whereIn('ponbr', $ponbrsForMap)->pluck('id', 'ponbr')->toArray();
        }

        $rows->transform(function ($r) use ($poIdMap) {
            $r->rfcadate_fmt = $r->rfcadate ? Carbon::parse($r->rfcadate)->format('Y-m-d') : null;
            $r->rfcaid_eid   = Hashids::encode((string) $r->id);

            // 🔗 PO link via PONBR
            $poId = $poIdMap[$r->ponbr] ?? null;
            $r->ponbr_eid = $poId ? Hashids::encode((string) $poId) : null;

            // 🔗 SPPB/J/K/T link (berdasar prefix)
            $r->sppb_route = null;
            $r->sppb_eid   = null;
            if (!empty($r->sppbjktid)) {
                $prefix   = Str::upper(Str::substr($r->sppbjktid, 0, 2));
                $routeMap = [
                    'PB' => 'showsppbs',
                    'PJ' => 'showsppjs',
                    'PK' => 'showsppks',
                    'PT' => 'showsppts',
                ];

                if (isset($routeMap[$prefix])) {
                    if ($prefix === 'PB') {
                        $id = TrSPPB::where('sppbid', $r->sppbjktid)->value('id');
                    } elseif ($prefix === 'PJ') {
                        $id = TrSPPJ::where('sppjid', $r->sppbjktid)->value('id');
                    } elseif ($prefix === 'PK') {
                        $id = TrSPPK::where('sppkid', $r->sppbjktid)->value('id');
                    } else { // PT
                        $id = TrSPPT::where('spptid', $r->sppbjktid)->value('id');
                    }

                    if ($id) {
                        $r->sppb_route = $routeMap[$prefix];
                        $r->sppb_eid   = Hashids::encode((string) $id);
                    }
                }
            }

            return $r;
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $rows,
        ]);
    }

    public function showRfca($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        // ===== Header Rfca
        $rfca = TrRfca::findOrFail($id);

        // ===== Link ke PO (opsional)
        $poUrl = null;
        if (!empty($rfca->ponbr)) {
            $poId = TrPO::where('ponbr', $rfca->ponbr)->value('id');
            if ($poId) {
                $poHash = Hashids::encode($poId);
                $poUrl  = url("/showpo/{$poHash}");
            }
        }

        // ===== Link ke SPPB/J/K/T (opsional)
        $sppbUrl   = null;
        $sppbjktid = (string) ($rfca->sppbjktid ?? '');
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
        if (!empty($rfca->csid)) {
            $csId = TrCS::where('csid', $rfca->csid)->value('id');
            if ($csId) {
                $csHash = Hashids::encode($csId);
                $csUrl  = url("/showcs/{$csHash}");
            }
        }

        // Untuk convenience (mis. kirim email dsb) – encrypt rfcaid
        $eid_rfcaid = Hashids::encode($rfca->rfcaid);

        // Detail step RFCA (boleh tetap pakai rfca_step_order untuk urutan tampilan)
        $rfcaSteps = TrRfcaStep::where('rfcaid', $rfca->rfcaid)
            ->orderBy('rfca_step_order')
            ->get();

        // STEP yang sedang aktif (progress_approval = true)
        $currentStep = TrRfcaStep::where('rfcaid', $rfca->rfcaid)
            ->where('progress_approval', true)
            ->orderBy('rfca_step_order')
            ->first();

        return view('pages.rfca.showrfca', [
            'rfca'       => $rfca,
            'hash'       => $hash,
            'eid_rfcaid' => $eid_rfcaid,
            'poUrl'      => $poUrl,
            'sppbUrl'    => $sppbUrl,
            'csUrl'      => $csUrl,
            'rfcaSteps'  => $rfcaSteps,
            'currentStep' => $currentStep,
        ]);
    }

    public function submitType(Request $request, $hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $request->validate([
            'rfca_type' => 'required|in:RFCA,RFP',
        ]);

        $rfcaType = $request->input('rfca_type');
        $now      = Carbon::now();
        $rfca     = TrRfca::findOrFail($id);

        try {
            DB::transaction(function () use ($rfca, $rfcaType, $user, $now) {
                $username = $user->username ?? 'system';

                // ambil master step sesuai rfca_type
                $steps = MsRfcaStep::where('rfca_type', $rfcaType)
                    ->where('status', 'A')
                    ->orderBy('rfca_step_order')
                    ->get();

                if ($steps->isEmpty()) {
                    throw ValidationException::withMessages([
                        'rfca_type' => ['Master RFCA Step tidak ditemukan untuk tipe: ' . $rfcaType],
                    ]);
                }

                // optional: hapus step lama kalau perlu
                // TrRfcaStep::where('rfcaid', $rfca->rfcaid)->delete();

                foreach ($steps as $step) {
                    TrRfcaStep::create([
                        'rfcaid'                  => $rfca->rfcaid,
                        'ponbr'                   => $rfca->ponbr,
                        'cpny_id'                 => $rfca->cpny_id,
                        'rfca_step_order'         => $step->rfca_step_order,
                        'rfca_step_id'            => $step->rfca_step_id,
                        'rfca_step_descr'         => $step->rfca_step_descr,
                        'rfca_step_department_id' => $step->rfca_step_department_id,
                        'rfca_type'               => $step->rfca_type,
                        'calr_gen'                => $step->calr_gen,
                        'rfca_step_user'          => null,
                        'rfca_step_date'          => null,
                        'progress_approval'       => false,   // boolean false
                        'status_rfca'             => 'P',     // default pending
                        'created_by'              => $username,
                        'updated_by'              => null,
                    ]);
                }

                // === step awal yang dianggap sudah "approve" (misal: PS) ===
                $firstStep = TrRfcaStep::where('rfcaid', $rfca->rfcaid)
                    ->where('rfca_step_id', 'PS')      // ganti kalau kodenya beda
                    ->first();

                if (!$firstStep) {
                    throw new \RuntimeException('RFCA first step (PS) tidak ditemukan.');
                }

                // first step: sudah selesai, bukan progress_approval
                $firstStep->rfca_step_user    = $username;
                $firstStep->rfca_step_date    = $now;
                $firstStep->progress_approval = false;  // tetap false (sudah lewat)
                $firstStep->status_rfca       = 'C';    // Approved
                $firstStep->updated_by        = $username;
                $firstStep->save();

                // === NEXT STEP: jadikan "active" → progress_approval = true ===
                $nextStep = TrRfcaStep::where('rfcaid', $rfca->rfcaid)
                    ->where('rfca_step_order', '>', $firstStep->rfca_step_order)
                    ->orderBy('rfca_step_order')
                    ->first();

                if ($nextStep) {
                    $nextStep->progress_approval = true; // step aktif berikutnya
                    $nextStep->status_rfca       = 'P';  // tetap Pending
                    $nextStep->updated_by        = $username;
                    $nextStep->save();
                }

                // simpan info di header RFCA (posisi terakhir yang sudah approve = firstStep)
                $rfca->rfca_type       = $rfcaType;
                $rfca->rfca_step_order = $firstStep->rfca_step_order;
                $rfca->rfca_step_id    = $firstStep->rfca_step_id;
                $rfca->status_rfca     = $firstStep->status_rfca; // 'A'
                $rfca->updated_by      = $username;
                $rfca->save();
            });

            return response()->json([
                'success' => true,
                'message' => 'RFCA Steps berhasil digenerate untuk tipe ' . $rfcaType,
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate RFCA Steps: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function approveStep(Request $request, $hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $rfca = TrRfca::findOrFail($id);

        try {
            DB::transaction(function () use ($rfca, $user) {
                $username = $user->username ?? 'system';

                // 1. Cari step AKTIF (progress_approval = true, status_rfca = 'P')
                $activeStep = TrRfcaStep::where('rfcaid', $rfca->rfcaid)
                    ->where('progress_approval', true)
                    ->where('status_rfca', 'P')
                    ->orderBy('rfca_step_order')
                    ->first();

                if (!$activeStep) {
                    throw new \RuntimeException('No active RFCA Step to approve (no step with progress_approval = true).');
                }

                // 2. Step aktif saat ini → Done (C) dan progress_approval = false
                $activeStep->rfca_step_user    = $username;
                $activeStep->rfca_step_date    = Carbon::now();
                $activeStep->status_rfca       = 'C';   // Done
                $activeStep->progress_approval = false; // sudah tidak aktif
                $activeStep->updated_by        = $username;
                $activeStep->save();

                // 3. Cari NEXT STEP berdasarkan rfca_step_order → jadikan aktif
                $nextStep = TrRfcaStep::where('rfcaid', $rfca->rfcaid)
                    ->where('rfca_step_order', '>', $activeStep->rfca_step_order)
                    ->orderBy('rfca_step_order')
                    ->first();

                if ($nextStep) {
                    $nextStep->progress_approval = true;  // step aktif berikutnya
                    // pastikan status pending
                    $nextStep->status_rfca       = $nextStep->status_rfca ?: 'P';
                    $nextStep->updated_by        = $username;
                    $nextStep->save();
                }

                // 4. Update header TrRfca → posisi terakhir yang sudah "Done"
                $rfca->rfca_step_order = $activeStep->rfca_step_order;
                $rfca->rfca_step_id    = $activeStep->rfca_step_id;
                $rfca->status_rfca     = $activeStep->status_rfca; // 'C'
                $rfca->updated_by      = $username;
                $rfca->save();
            });

            return response()->json([
                'success' => true,
                'message' => 'Active RFCA Step approved.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve RFCA Step: ' . $e->getMessage(),
            ], 500);
        }
    }

}
