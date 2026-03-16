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
                $base->where(function ($q) {
                        $q->whereNull('tr_rfca.rfca_type')
                        ->orWhere('tr_rfca.rfca_type', '');
                    })
                    ->whereNull('step.id')
                    ->where('tr_rfca.created_by', $u);
                break;

            case 'financereceived':
                $base->where('step.rfca_step_id', 'FR');
                break;

            case 'treasurypayment':
                $base->where('step.rfca_step_id', 'TP');
                break;

            case 'completed':
                $base->where('step.rfca_step_id', 'PC');
                break;

            case 'all':
            default:
                // no extra filter
                break;
        }

        $baseForCount = clone $base;

        $orderColumns = [
            0 => 'tr_rfca.rfcaid',
            1 => 'tr_rfca.rfcadate',
            2 => 'tr_rfca.ponbr',
            3 => 'tr_rfca.sppbjktid',
            4 => 'tr_rfca.cpny_id',
            5 => 'tr_rfca.created_by',
        ];

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
                    'tr_rfca.status',
                    'tr_rfca.rfca_type',
                    'step.rfca_step_id as current_step_id',
                    'step.progress_approval',
                ])
                ->orderBy($orderCol, $orderDir)
                ->orderBy('tr_rfca.rfcaid', 'desc')
                ->skip($start)->take($length)
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
        if (!$user) return redirect()->route('login');

        // ===== Header Rfca
        $rfca = TrRfca::findOrFail($id);

        // ===== Link ke PO (opsional)
        $poUrl = null;
        if (!empty($rfca->ponbr)) {
            $poId = TrPO::where('ponbr', $rfca->ponbr)
                ->where('cpny_id', $rfca->cpny_id)
                ->value('id');
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

        // Detail step RFCA
        $rfcaSteps = TrRfcaStep::where('rfcaid', $rfca->rfcaid)
            ->orderBy('rfca_step_order')
            ->get();

        // STEP yang sedang aktif (progress_approval = true)
        $currentStep = TrRfcaStep::where('rfcaid', $rfca->rfcaid)
            ->where('progress_approval', true)
            ->orderBy('rfca_step_order')
            ->first();

        // === Flag: hanya creator yang boleh lihat tombol Submit ===
        $loginUsername = $user->username ?? $user->name ?? null;
        $canSubmit     = $rfca->created_by === $loginUsername;
       
       // === Cek apakah user berhak memproses step ini (department bisa multi) ===
        $loginDept = $user->department_id ?? ''; 
        $loginDepartments = array_map('trim', explode(',', $loginDept)); // contoh: ACCOUNTING,COLLECTION → ['ACCOUNTING','COLLECTION']

        $canProcessStepDept = false;

        if ($currentStep && in_array($currentStep->rfca_step_department_id, $loginDepartments)) {
            $canProcessStepDept = true;
        }



        return view('pages.rfca.showrfca', [
            'rfca'        => $rfca,
            'hash'        => $hash,
            'eid_rfcaid'  => $eid_rfcaid,
            'poUrl'       => $poUrl,
            'sppbUrl'     => $sppbUrl,
            'csUrl'       => $csUrl,
            'rfcaSteps'   => $rfcaSteps,
            'currentStep' => $currentStep,
            'canSubmit'   => $canSubmit, 
            'canProcessStepDept' => $canProcessStepDept,
            'docPrefix'   => $prefix,
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
        // $approval = TrApproval::query()
        //     ->where('refnbr', $rfca->csid)
        //     ->where('status', '<>', 'X')
        //     ->orderByRaw('CAST(aprv_leveling AS numeric) ASC')
        //     ->orderBy('created_at', 'ASC')
        //     ->get();
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

        $approve_count = $approval->count();

        // Company
        $company = MsCompany::where('cpny_id', $rfca->cpny_id)->first();

        // Mapping status dokumen
        switch ($rfca->status) {
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
            'title'               => 'REQUEST FOR CASH ADVANCE',
            'doc_type'            => 'RFCA',
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

}
