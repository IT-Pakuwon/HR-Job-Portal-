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
use App\Models\TrApproval;
use App\Models\MsCompany;
use App\Models\TrCalr;
use App\Models\vMatchingRfca;

class RfcaListController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $username = $user->username ?? '';
        if (!$user) return redirect()->route('login');

        // bisa berisi "AW" atau "AW,GPS"
        $cpnyRaw  = $user->cpny_id ?? '';
        $cpnyList = $cpnyRaw !== '' ? array_map('trim', explode(',', $cpnyRaw)) : [];

        /**
         * Rfca Jobs:
         * - rfca_type masih kosong/null
         * - BELUM ada satupun step dengan progress_approval = 't'
         */
        $rfcajobs = TrRfca::when(!empty($cpnyList), function ($q) use ($cpnyList) {
                    $q->whereIn('cpny_id', $cpnyList);
                })
                ->where('created_by', $username)
                ->where('status', 'H')
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
         * - rfca_step_id = 'FR'
         */
        $financeReceived = TrRfca::when(!empty($cpnyList), function ($q) use ($cpnyList) {
                    $q->whereIn('cpny_id', $cpnyList);
                })
                ->where('status', 'P')
                ->whereExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('tr_rfca_step as step')
                        ->whereColumn('step.rfcaid', 'tr_rfca.rfcaid')
                        ->where('step.progress_approval', 't')
                        ->where('step.rfca_step_id', 'FR');
                })
                ->count();

        /**
         * Treasury Payment:
         * - step.progress_approval = 't'
         * - step.rfca_step_id = 'TP'
         */
        $treasuryPayment = TrRfca::when(!empty($cpnyList), function ($q) use ($cpnyList) {
                    $q->whereIn('cpny_id', $cpnyList);
                })
                ->where('status', 'P')
                ->whereExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('tr_rfca_step as step')
                        ->whereColumn('step.rfcaid', 'tr_rfca.rfcaid')
                        ->where('step.progress_approval', 't')
                        ->where('step.rfca_step_id', 'TP');
                })
                ->count();

        /**
         * Completed:
         * - step.progress_approval = 't'
         * - step.rfca_step_id = 'PC'
         */
        $completed = TrRfca::when(!empty($cpnyList), function ($q) use ($cpnyList) {
                    $q->whereIn('cpny_id', $cpnyList);
                })
                ->where('status', 'C')
                ->whereExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('tr_rfca_step as step')
                        ->whereColumn('step.rfcaid', 'tr_rfca.rfcaid')
                        ->where('step.progress_approval', 't')
                        ->where('step.rfca_step_id', 'PC');
                })
                ->count();

        // All RFCA tanpa filter progress_approval
        $all = TrRfca::when(!empty($cpnyList), function ($q) use ($cpnyList) {
                    $q->whereIn('cpny_id', $cpnyList);
                })
                ->whereNot('status', 'X')
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

        $cpnyRaw  = $user->cpny_id ?? '';
        $cpnyList = $cpnyRaw !== '' ? array_map('trim', explode(',', $cpnyRaw)) : [];

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
            ->when(!empty($cpnyList), function ($q) use ($cpnyList) {
                $q->whereIn('tr_rfca.cpny_id', $cpnyList);
            });

        // Scope filter
        switch ($scope) {
            case 'rfcajobs':
                $base->where('tr_rfca.status', 'H')
                    ->where(function ($q) {
                        $q->whereNull('tr_rfca.rfca_type')
                            ->orWhere('tr_rfca.rfca_type', '');
                    })
                    ->whereNull('step.id')
                    ->where('tr_rfca.created_by', $u);
                break;

            case 'financereceived':
                $base->where('tr_rfca.status', 'P')
                    ->where('step.rfca_step_id', 'FR');
                break;

            case 'treasurypayment':
                $base->where('tr_rfca.status', 'P')
                    ->where('step.rfca_step_id', 'TP');
                break;

            case 'completed':
                $base->where('tr_rfca.status', 'C')
                    ->where('step.rfca_step_id', 'PC');
                break;

            case 'all':
            default:
                $base->whereNot('tr_rfca.status', 'X');
                break;
        }

        $baseForCount = clone $base;

        $orderColumns = [
            0 => 'tr_rfca.rfcaid',
            1 => 'tr_rfca.rfcaid',
            2 => 'tr_rfca.rfcadate',
            3 => 'tr_rfca.ponbr',
            4 => 'tr_rfca.sppbjktid',
            5 => 'tr_rfca.csid',
            6 => 'tr_rfca.cpny_id',
            7 => 'tr_rfca.vendorname',
            8 => 'tr_rfca.created_by',
            9 => 'step.rfca_step_id',
        ];

        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('tr_rfca.rfcaid', 'ilike', "%{$search}%")
                    ->orWhere('tr_rfca.ponbr', 'ilike', "%{$search}%")
                    ->orWhere('tr_rfca.sppbjktid', 'ilike', "%{$search}%")
                    ->orWhere('tr_rfca.csid', 'ilike', "%{$search}%")
                    ->orWhere('tr_rfca.cpny_id', 'ilike', "%{$search}%")
                    ->orWhere('tr_rfca.vendorid', 'ilike', "%{$search}%")
                    ->orWhere('tr_rfca.vendorname', 'ilike', "%{$search}%")
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
            'tr_rfca.csid',
            'tr_rfca.cpny_id',
            'tr_rfca.vendorid',
            'tr_rfca.vendorname',
            'tr_rfca.created_by',
            'tr_rfca.status',
            'tr_rfca.rfca_type',
            'step.rfca_step_id as current_step_id',
            'step.progress_approval',
        ])
        ->orderBy($orderCol, $orderDir)
        ->orderBy('tr_rfca.rfcaid', 'desc')
        ->skip($start)
        ->take($length)
        ->get();

        // map PO id untuk link, dll… (bagian bawah tetap sama seperti punya kamu)
        // ...

        // (biarkan bagian transform & return response apa adanya dari kode kamu sebelumnya)
        // ========= ENRICH / FORMAT =========
        $poIdMap = [];
        $ponbrsForMap = $rows->pluck('ponbr')->filter()->unique()->values()->all();
        if (!empty($ponbrsForMap)) {
            $poIdMap = TrPO::whereIn('ponbr', $ponbrsForMap)->pluck('id', 'ponbr')->toArray();
        }

        $rows->transform(function ($r) use ($poIdMap) {
            $r->rfcadate_fmt = $r->rfcadate ? Carbon::parse($r->rfcadate)->format('Y-m-d') : null;
            $r->rfcaid_eid   = Hashids::encode((string) $r->id);

            $poId = $poIdMap[$r->ponbr] ?? null;
            $r->ponbr_eid = $poId ? Hashids::encode((string) $poId) : null;

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
        if (!$user) {
            return redirect()->route('login');
        }

        // ===== Header RFCA
        $rfca = TrRfca::findOrFail($id);

        $ponbr    = trim((string) ($rfca->ponbr ?? ''));
        $cpnyId   = trim((string) ($rfca->cpny_id ?? ''));
        $csid     = trim((string) ($rfca->csid ?? ''));
        $calrid   = trim((string) ($rfca->calrid ?? ''));
        $sppbjktid = trim((string) ($rfca->sppbjktid ?? ''));

        // ===== Link ke PO (lebih fleksibel)
        $poUrl = null;
        if ($ponbr !== '') {
            $poQuery = TrPO::query()
                ->whereRaw('TRIM(ponbr) = ?', [$ponbr]);

            if ($cpnyId !== '') {
                $poId = (clone $poQuery)
                    ->whereRaw('TRIM(cpny_id) = ?', [$cpnyId])
                    ->orderByDesc('id')
                    ->value('id');

                // fallback kalau tidak ketemu dengan company
                if (!$poId) {
                    $poId = (clone $poQuery)
                        ->orderByDesc('id')
                        ->value('id');
                }
            } else {
                $poId = (clone $poQuery)
                    ->orderByDesc('id')
                    ->value('id');
            }

            if ($poId) {
                $poHash = Hashids::encode($poId);
                $poUrl  = url("/showpo/{$poHash}");
            }
        }

        // ===== Link ke SPPB/J/K/T
        $sppbUrl = null;
        $prefix  = strtoupper(substr($sppbjktid, 0, 2));

        $routeMap = [
            'PB' => 'showsppbs',
            'PJ' => 'showsppjs',
            'PK' => 'showsppks',
            'PT' => 'showsppts',
        ];

        if ($sppbjktid !== '' && isset($routeMap[$prefix])) {
            $docId = null;

            if ($prefix === 'PB') {
                $docId = TrSPPB::whereRaw('TRIM(sppbid) = ?', [$sppbjktid])->value('id');
            } elseif ($prefix === 'PJ') {
                $docId = TrSPPJ::whereRaw('TRIM(sppjid) = ?', [$sppbjktid])->value('id');
            } elseif ($prefix === 'PK') {
                $docId = TrSPPK::whereRaw('TRIM(sppkid) = ?', [$sppbjktid])->value('id');
            } elseif ($prefix === 'PT') {
                $docId = TrSPPT::whereRaw('TRIM(spptid) = ?', [$sppbjktid])->value('id');
            }

            if (!empty($docId)) {
                $sppbHash = Hashids::encode($docId);
                $sppbUrl  = url('/' . $routeMap[$prefix] . '/' . $sppbHash);
            }
        }

        // ===== Link ke CS (lebih fleksibel)
        $csUrl = null;
        if ($csid !== '') {
            $csQuery = TrCS::query()
                ->whereRaw('TRIM(csid) = ?', [$csid]);

            $csId = null;

            if ($cpnyId !== '') {
                $csId = (clone $csQuery)
                    ->whereRaw('TRIM(cpny_id) = ?', [$cpnyId])
                    ->orderByDesc('id')
                    ->value('id');

                // fallback kalau tidak ketemu dengan company
                if (!$csId) {
                    $csId = (clone $csQuery)
                        ->orderByDesc('id')
                        ->value('id');
                }
            } else {
                $csId = (clone $csQuery)
                    ->orderByDesc('id')
                    ->value('id');
            }

            if ($csId) {
                $csHash = Hashids::encode($csId);
                $csUrl  = url("/showcs/{$csHash}");
            }
        }

        $prevRfcaid = trim((string) ($rfca->prev_rfcaid ?? ''));

        // ===== Link ke Previous RFCA
        $prevRfcaUrl = null;

        if ($prevRfcaid !== '') {
            $prevRfcaQuery = TrRfca::query()
                ->whereRaw('TRIM(rfcaid) = ?', [$prevRfcaid]);

            $prevRfcaId = null;

            if ($cpnyId !== '') {
                $prevRfcaId = (clone $prevRfcaQuery)
                    ->whereRaw('TRIM(cpny_id) = ?', [$cpnyId])
                    ->orderByDesc('id')
                    ->value('id');

                // fallback kalau tidak ketemu dengan company
                if (!$prevRfcaId) {
                    $prevRfcaId = (clone $prevRfcaQuery)
                        ->orderByDesc('id')
                        ->value('id');
                }
            } else {
                $prevRfcaId = (clone $prevRfcaQuery)
                    ->orderByDesc('id')
                    ->value('id');
            }

            if ($prevRfcaId) {
                $prevRfcaHash = Hashids::encode($prevRfcaId);
                $prevRfcaUrl  = url("/showrfca/{$prevRfcaHash}");
            }
        }

        // ===== Link ke CALR
        $calrUrl = null;
        if ($calrid !== '') {
            $calrQuery = TrCalr::query()
                ->whereRaw('TRIM(calrid) = ?', [$calrid]);

            $calrId = null;

            if ($cpnyId !== '') {
                $calrId = (clone $calrQuery)
                    ->whereRaw('TRIM(cpny_id) = ?', [$cpnyId])
                    ->orderByDesc('id')
                    ->value('id');

                if (!$calrId) {
                    $calrId = (clone $calrQuery)
                        ->orderByDesc('id')
                        ->value('id');
                }
            } else {
                $calrId = (clone $calrQuery)
                    ->orderByDesc('id')
                    ->value('id');
            }

            if ($calrId) {
                $calrHash = Hashids::encode($calrId);
                $calrUrl  = url("/showcalr/{$calrHash}");
            }
        }

        $eid_rfcaid = Hashids::encode($rfca->rfcaid);

        $rfcaSteps = TrRfcaStep::where('rfcaid', $rfca->rfcaid)
            ->orderBy('rfca_step_order')
            ->get();

        $currentStep = TrRfcaStep::where('rfcaid', $rfca->rfcaid)
            ->where('progress_approval', true)
            ->orderBy('rfca_step_order')
            ->first();

        $loginUsername = $user->username ?? $user->name ?? null;
        $hasSteps = $rfcaSteps->isNotEmpty();
        $canSubmit = ($rfca->created_by === $loginUsername) && !$hasSteps;

        $hasMatchingRfca = false;

        if ($canSubmit) {
            $hasMatchingRfca = vMatchingRfca::query()
                ->where('ponbr', $ponbr)
                ->where('cpny_id', $cpnyId)
                ->where('csid', $csid)
                ->where('sppbjktid', $sppbjktid)
                ->where('prev_status', 'P')
                ->exists();
        }

        $canShowMatchingRfca = $canSubmit && $hasMatchingRfca;
        $canShowSubmit = $canSubmit && !$hasMatchingRfca;

        $loginDept = $user->department_id ?? '';
        $loginDepartments = array_map('trim', explode(',', $loginDept));

        $canProcessStepDept = false;
        if ($currentStep && in_array($currentStep->rfca_step_department_id, $loginDepartments)) {
            $canProcessStepDept = true;
        }

        return view('pages.rfca.showrfca', [
            'rfca'               => $rfca,
            'hash'               => $hash,
            'eid_rfcaid'         => $eid_rfcaid,
            'poUrl'              => $poUrl,
            'sppbUrl'            => $sppbUrl,
            'csUrl'              => $csUrl,
            'calrUrl'            => $calrUrl,
            'prevRfcaUrl'        => $prevRfcaUrl,
            'rfcaSteps'          => $rfcaSteps,
            'currentStep'        => $currentStep,
            'canSubmit'          => $canSubmit,
            'hasMatchingRfca'     => $hasMatchingRfca,
            'canShowMatchingRfca' => $canShowMatchingRfca,
            'canShowSubmit'       => $canShowSubmit,
            'canProcessStepDept' => $canProcessStepDept,
            'docPrefix'          => $prefix,
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
                $rfca->status            = 'P';
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

    public function printRfca($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $authUser = Auth::user();
        if (!$authUser) {
            return redirect()->route('login');
        }

        // Ambil BAST + relasi
        $rfca = TrRfca::with(['creator', 'userpeminta'])
            ->findOrFail($id);

        // Approval list     
        $refnbr    = $rfca->csid;
        $apprTable = (new TrApproval)->getTable(); // "tr_approval"

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

        // $approval = TrRfcaStep::where('rfcaid', $rfca->rfcaid)                
        //         ->orderBy('rfca_step_order')
        //         ->get();


        $approve_count = $approval->count();

        // Company
        $company = MsCompany::where('cpny_id', $rfca->cpny_id)->first();

        // Mapping status dokumen
        // switch ($rfca->status_rfca) {
        //     case 'R':
        //         $status_doc = 'Rejected';
        //         break;
        //     case 'C':
        //         $status_doc = 'Completed';
        //         break;
        //     case 'D':
        //         $status_doc = 'Hold';
        //         break;
        //     case 'X':
        //         $status_doc = 'Cancel';
        //         break;
        //     default:
        //         $status_doc = 'On Progress';
        //         break;
        // }

        $status_doc = 'Completed';

        if ($rfca->rfca_type === 'RFP') {
            $doctype ='RFCA - RFP';
        } else {
            $doctype ='RFCA - CALR';
        }

        $data = [
            'title'               => 'REQUEST FOR CASH ADVANCE',
            'doc_type'            => $doctype,
            'docid'               => $rfca->rfcaid,
            'department_id'       => $rfca->department_id,
            'cpny_id'             => $company->cpny_id,
            'cpny_name'           => $company->cpny_name,
           
            // identitas & tanggal
            'created_by_username' => $rfca->created_by,
            'created_by_name'     => ucwords(strtolower(optional($rfca->creator)->name ?? $rfca->created_by)),
            'created_at_fmt'      => optional($rfca->created_at)->format('d F Y'),
            'req_date_fmt'        => optional($rfca->created_at)->format('d M Y H:i'),
            'rfcadate'            => $rfca->rfcadate
                                        ? Carbon::parse($rfca->rfcadate)->format('d F Y')
                                        : '',
            'required_date'       => $rfca->required_date
                                        ? Carbon::parse($rfca->required_date)->format('d/m/Y')
                                        : '',
            'calr_date'           => $rfca->calr_date
                                        ? Carbon::parse($rfca->calr_date)->format('d/m/Y')
                                        : '',

            // konten utama
            'rfca_amount'         => $rfca->rfca_amount,
            'terbilang'           => $terbilang = ucfirst($this->terbilang($rfca->rfca_amount)) . ' rupiah',
            'keperluan'           => $rfca->keperluan,
            'vendorname'          => $rfca->vendorname,
            'status_doc'          => $status_doc,
      
        ];

        $pdf = \PDF::loadView(
            'pages.rfca.pdf_rfca',
            array_merge($data, [
                'rfca'          => $rfca,
                'approval'      => $approval,
                'approve_count' => $approve_count,
            ])
        );

        // $pdf->setPaper('A4', ($approve_count <= 5) ? 'portrait' : 'landscape');

        return $pdf->stream("pdf_rfca_{$rfca->rfcaid}.pdf");
    }

    private function terbilang($angka): string
    {
        if (is_string($angka)) {
            $angka = str_replace([',', ' '], '', $angka);
        }
        if (!is_numeric($angka)) return '';

        $isMinus = $angka < 0;
        $angka = (int) abs((float) $angka);

        $bil = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        $fn = function ($n) use (&$fn, $bil): string {
            if ($n < 12)                  return ' '.$bil[$n];
            if ($n < 20)                  return $fn($n - 10).' belas';
            if ($n < 100)                 return $fn(intval($n / 10)).' puluh'.$fn($n % 10);
            if ($n < 200)                 return ' seratus'.$fn($n - 100);
            if ($n < 1000)                return $fn(intval($n / 100)).' ratus'.$fn($n % 100);
            if ($n < 2000)                return ' seribu'.$fn($n - 1000);
            if ($n < 1_000_000)           return $fn(intval($n / 1000)).' ribu'.$fn($n % 1000);
            if ($n < 1_000_000_000)       return $fn(intval($n / 1_000_000)).' juta'.$fn($n % 1_000_000);
            if ($n < 1_000_000_000_000)   return $fn(intval($n / 1_000_000_000)).' miliar'.$fn($n % 1_000_000_000);
            return $fn(intval($n / 1_000_000_000_000)).' triliun'.$fn($n % 1_000_000_000_000);
        };

        $hasil = trim(preg_replace('/\s+/', ' ', $fn($angka)));
        return ($isMinus ? 'minus ' : '').$hasil;
    }

    public function getMatchingRfcaList(Request $request)
    {
        // dd($request->all());
        $search   = trim((string) $request->get('search', ''));
        $ponbr    = trim((string) $request->get('ponbr', ''));
        $cpnyId   = trim((string) $request->get('cpny_id', ''));
        $csid     = trim((string) $request->get('csid', ''));
        $sppbjktid = trim((string) $request->get('sppbjktid', ''));

        $query = vMatchingRfca::query()
            ->select([
                // current document
                'ponbr',
                'cpny_id',
                'csid',
                'sppbjktid',

                // previous RFCA data, alias supaya response tetap cocok dengan view lama
                'prev_id as id',
                'prev_rfcaid as rfcaid',
                'prev_rfcadate as rfcadate',
                'prev_ponbr as prev_ponbr',
                'prev_cpnyid as prev_cpnyid',
                'prev_department_id as department_id',
                'prev_vendorid as vendorid',
                'prev_vendorname as vendorname',
                'prev_po_amount as po_amount',
                'prev_rfca_amount as rfca_amount',
                'prev_payment_pct as payment_pct',
                'prev_status as status',
                \DB::raw("'D' as po_status"),
            ])
            ->where('prev_status', 'P');

        // filter berdasarkan dokumen saat ini
        if ($ponbr !== '') {
            $query->where('ponbr', $ponbr);
        }

        if ($cpnyId !== '') {
            $query->where('cpny_id', $cpnyId);
        }

        if ($csid !== '') {
            $query->where('csid', $csid);
        }

        if ($sppbjktid !== '') {
            $query->where('sppbjktid', $sppbjktid);
        }

        // search global
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('prev_rfcaid', 'ILIKE', "%{$search}%")
                    ->orWhere('prev_ponbr', 'ILIKE', "%{$search}%")
                    ->orWhere('prev_cpnyid', 'ILIKE', "%{$search}%")
                    ->orWhere('prev_vendorid', 'ILIKE', "%{$search}%")
                    ->orWhere('prev_vendorname', 'ILIKE', "%{$search}%")
                    ->orWhere('prev_department_id', 'ILIKE', "%{$search}%")
                    ->orWhere('ponbr', 'ILIKE', "%{$search}%")
                    ->orWhere('cpny_id', 'ILIKE', "%{$search}%")
                    ->orWhere('csid', 'ILIKE', "%{$search}%")
                    ->orWhere('sppbjktid', 'ILIKE', "%{$search}%");
            });
        }

        $rows = $query
            ->orderByDesc('prev_rfcadate')
            ->orderByDesc('prev_id')
            ->limit(100)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rows,
        ]);
    }

    public function getMatchingRfcaList_xxx(Request $request)
    {
        $search = trim($request->get('search', ''));

        $query = TrRfca::query()
            ->from('tr_rfca as rfca')
            ->join('tr_po as po', function ($join) {
                $join->on('po.ponbr', '=', 'rfca.ponbr')
                    ->on('po.cpny_id', '=', 'rfca.cpny_id');
            })
            ->where('rfca.status', 'P')
            ->where('po.status', 'D')
            ->select([
                'rfca.id',
                'rfca.rfcaid',
                'rfca.rfcadate',
                'rfca.ponbr',
                'rfca.cpny_id',
                'rfca.department_id',
                'rfca.vendorid',
                'rfca.vendorname',
                'rfca.po_amount',
                'rfca.rfca_amount',
                'rfca.payment_pct',
                'rfca.status',
                'po.status as po_status',
            ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('rfca.rfcaid', 'ILIKE', "%{$search}%")
                    ->orWhere('rfca.ponbr', 'ILIKE', "%{$search}%")
                    ->orWhere('rfca.cpny_id', 'ILIKE', "%{$search}%")
                    ->orWhere('rfca.vendorid', 'ILIKE', "%{$search}%")
                    ->orWhere('rfca.vendorname', 'ILIKE', "%{$search}%")
                    ->orWhere('rfca.department_id', 'ILIKE', "%{$search}%");
            });
        }

        $rows = $query
            ->orderByDesc('rfca.rfcadate')
            ->orderByDesc('rfca.id')
            ->limit(100)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rows,
        ]);
    }

    public function selectMatchingRfca(Request $request, $hash)
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
            'selected_rfca_id' => ['required', 'integer'],
        ]);

        $username = $user->username ?? 'system';
        $now = Carbon::now();

        try {
            DB::connection('pgsql')->transaction(function () use ($id, $request, $username, $now) {

                /*
                |--------------------------------------------------------------------------
                | 1) Current RFCA yang sedang dibuka
                |--------------------------------------------------------------------------
                */
                $currentRfca = TrRfca::where('id', $id)
                    ->lockForUpdate()
                    ->firstOrFail();

                /*
                |--------------------------------------------------------------------------
                | 2) RFCA selected dari modal
                |    Syarat:
                |    - TrRfca selected status = P
                |    - PO dari selected status = D
                |--------------------------------------------------------------------------
                */
                $selectedRfca = TrRfca::where('id', $request->selected_rfca_id)
                    ->where('status', 'P')
                    ->lockForUpdate()
                    ->first();

                if (!$selectedRfca) {
                    throw ValidationException::withMessages([
                        'selected_rfca_id' => ['RFCA selected tidak ditemukan atau status RFCA bukan P.'],
                    ]);
                }

                if ((int) $selectedRfca->id === (int) $currentRfca->id) {
                    throw ValidationException::withMessages([
                        'selected_rfca_id' => ['RFCA tidak boleh matching dengan dirinya sendiri.'],
                    ]);
                }

                $selectedPo = TrPO::where('ponbr', $selectedRfca->ponbr)
                    ->where('cpny_id', $selectedRfca->cpny_id)
                    ->where('status', 'D')
                    ->lockForUpdate()
                    ->first();

                if (!$selectedPo) {
                    throw ValidationException::withMessages([
                        'selected_rfca_id' => ['PO dari RFCA selected tidak ditemukan atau status PO bukan D.'],
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | 3) Tentukan RFCA Type
                |--------------------------------------------------------------------------
                | Ambil dari RFCA selected.
                | Jika RFCA selected belum punya rfca_type, fallback ke RFCA.
                |--------------------------------------------------------------------------
                */
                $rfcaType = $selectedRfca->rfca_type ?: 'RFCA';

                /*
                |--------------------------------------------------------------------------
                | 4) Ambil master step seperti submitType()
                |--------------------------------------------------------------------------
                */
                $steps = MsRfcaStep::where('rfca_type', $rfcaType)
                    ->where('status', 'A')
                    ->orderBy('rfca_step_order')
                    ->get();

                if ($steps->isEmpty()) {
                    throw ValidationException::withMessages([
                        'rfca_type' => ['Master RFCA Step tidak ditemukan untuk tipe: ' . $rfcaType],
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | 5) Hindari duplicate step current RFCA
                |--------------------------------------------------------------------------
                */
                TrRfcaStep::where('rfcaid', $currentRfca->rfcaid)->delete();

                /*
                |--------------------------------------------------------------------------
                | 6) Insert RFCA Step untuk current RFCA
                |    Polanya sama seperti submitType()
                |--------------------------------------------------------------------------
                */
                foreach ($steps as $step) {
                    TrRfcaStep::create([
                        'rfcaid'                  => $currentRfca->rfcaid,
                        'ponbr'                   => $currentRfca->ponbr,
                        'cpny_id'                 => $currentRfca->cpny_id,
                        'rfca_step_order'         => $step->rfca_step_order,
                        'rfca_step_id'            => $step->rfca_step_id,
                        'rfca_step_descr'         => $step->rfca_step_descr,
                        'rfca_step_department_id' => $step->rfca_step_department_id,
                        'rfca_type'               => $step->rfca_type,
                        'calr_gen'                => $step->calr_gen,
                        'rfca_step_user'          => null,
                        'rfca_step_date'          => null,
                        'progress_approval'       => false,
                        'status_rfca'             => 'P',
                        'created_by'              => $username,
                        'updated_by'              => null,
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | 7) First step PS otomatis completed
                |--------------------------------------------------------------------------
                */
                $firstStep = TrRfcaStep::where('rfcaid', $currentRfca->rfcaid)
                    ->where('rfca_step_id', 'PS')
                    ->first();

                if (!$firstStep) {
                    throw new \RuntimeException('RFCA first step PS tidak ditemukan.');
                }

                $firstStep->rfca_step_user    = $username;
                $firstStep->rfca_step_date    = $now;
                $firstStep->progress_approval = false;
                $firstStep->status_rfca       = 'C';
                $firstStep->updated_by        = $username;
                $firstStep->save();

                /*
                |--------------------------------------------------------------------------
                | 8) Next step menjadi active
                |--------------------------------------------------------------------------
                */
                $nextStep = TrRfcaStep::where('rfcaid', $currentRfca->rfcaid)
                    ->where('rfca_step_order', '>', $firstStep->rfca_step_order)
                    ->orderBy('rfca_step_order')
                    ->first();

                if ($nextStep) {
                    $nextStep->progress_approval = true;
                    $nextStep->status_rfca       = 'P';
                    $nextStep->updated_by        = $username;
                    $nextStep->save();
                }

                /*
                |--------------------------------------------------------------------------
                | 9) Update current RFCA dari data selected RFCA
                |--------------------------------------------------------------------------
                | Field model Anda:
                | - prev_rfcaid
                | - prev_ponbr
                | - prev_csid
                | - prev_rfca_amount
                |--------------------------------------------------------------------------
                */
                $prevRfcaAmount = (float) ($selectedRfca->rfca_amount ?? 0);
                $currentRfcaAmount = (float) ($currentRfca->rfca_amount ?? 0);

                $currentRfca->status           = 'P';
                $currentRfca->rfca_type        = $rfcaType;
                $currentRfca->rfca_step_order  = $firstStep->rfca_step_order;
                $currentRfca->rfca_step_id     = $firstStep->rfca_step_id;
                $currentRfca->status_rfca      = $firstStep->status_rfca;

                $currentRfca->prev_rfcaid      = $selectedRfca->rfcaid;
                $currentRfca->prev_ponbr       = $selectedRfca->ponbr;
                $currentRfca->prev_csid        = $selectedRfca->csid;
                $currentRfca->prev_rfca_amount = $prevRfcaAmount;
                $currentRfca->add_rfca_amount  = $prevRfcaAmount - $currentRfcaAmount;

                $currentRfca->updated_by       = $username;
                $currentRfca->updated_at       = $now;
                $currentRfca->save();

                /*
                |--------------------------------------------------------------------------
                | 10) Update RFCA selected menjadi L
                |--------------------------------------------------------------------------
                */
                $selectedRfca->status     = 'L';
                $selectedRfca->updated_by = $username;
                $selectedRfca->updated_at = $now;
                $selectedRfca->save();
            });

            return response()->json([
                'success' => true,
                'message' => 'Matching RFCA berhasil diproses.',
            ]);

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal proses Matching RFCA: ' . $e->getMessage(),
            ], 500);
        }
    }

}
