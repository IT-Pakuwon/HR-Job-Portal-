<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\TrRfca;
use App\Models\TrPOterm;
use App\Models\TrSPPB;
use App\Models\TrSPPJ;
use App\Models\TrSPPK;
use App\Models\TrSPPT;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Str;
use App\Models\TrPO;
use App\Models\TrCS;
use App\Models\MsRfcaStep;
use App\Models\TrRfcaStep;

class RfcaListController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $cpny_id = $user->cpny_id ?? '';

        // Rfca Jobs: rfca_type = '' dan rfca_step_order IS NULL
        $rfcajobs = TrRfca::when($cpny_id, fn($q) => $q->where('cpny_id', $cpny_id))
            ->where('rfca_type', '')
            ->whereNull('rfca_step_order')
            ->count();

        // Finance Received: rfca_step_order = '1'
        $financeReceived = TrRfca::when($cpny_id, fn($q) => $q->where('cpny_id', $cpny_id))
            ->where('rfca_step_order', '1')
            ->count();

        // Treasury Payment: rfca_step_order = '2'
        $treasuryPayment = TrRfca::when($cpny_id, fn($q) => $q->where('cpny_id', $cpny_id))
            ->where('rfca_step_order', '2')
            ->count();

        // Rfca Completed: rfca_step_order = '3'
        $completed = TrRfca::when($cpny_id, fn($q) => $q->where('cpny_id', $cpny_id))
            ->where('rfca_step_order', '3')
            ->count();

        // All Rfca (tanpa where rfca_step_order)
        $all = TrRfca::when($cpny_id, fn($q) => $q->where('cpny_id', $cpny_id))
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

        // ====== BASE QUERY: TrRfca ======
        $base = TrRfca::query()
            ->when($cpny_id, fn($q) => $q->where('cpny_id', $cpny_id));

        // Scope filter
        switch ($scope) {
            case 'rfcajobs':
                // Rfca Jobs: rfca_type = '' dan rfca_step_order IS NULL
                $base->where('rfca_type', '')
                    ->whereNull('rfca_step_order');
                break;

            case 'financereceived':
                // Finance Received
                $base->where('rfca_step_order', '1');
                break;

            case 'treasurypayment':
                // Treasury Payment
                $base->where('rfca_step_order', '2');
                break;

            case 'completed':
                // Rfca Completed
                $base->where('rfca_step_order', '3');
                break;

            case 'all':
            default:
                // All Rfca: tidak ada filter rfca_step_order tambahan
                break;
        }

        // Kolom untuk order
        $orderColumns = [
            0 => 'rfcaid',
            1 => 'rfcadate',
            2 => 'ponbr',
            3 => 'sppbjktid',
            4 => 'cpny_id',
            5 => 'created_by',
        ];

        // Search
        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('rfcaid', 'ilike', "%{$search}%")
                ->orWhere('ponbr', 'ilike', "%{$search}%")
                ->orWhere('sppbjktid', 'ilike', "%{$search}%")
                ->orWhere('cpny_id', 'ilike', "%{$search}%")
                ->orWhere('created_by', 'ilike', "%{$search}%")
                ->orWhereRaw("TO_CHAR(rfcadate,'YYYY-MM-DD') ILIKE ?", ["%{$search}%"]);
            });
        }

        $recordsTotal    = (clone $base)->count();
        $recordsFiltered = (clone $base)->count();

        $orderIdx = (int) $req->input('order.0.column', 1);
        $orderDir = $req->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $orderColumns[$orderIdx] ?? 'rfcadate';

        $rows = $base->select([
                    'id',
                    'rfcaid',
                    'rfcadate',
                    'ponbr',
                    'sppbjktid',
                    'cpny_id',
                    'created_by',
                    'status',       // status lama (P/C/R/D) masih dipakai untuk logic revise
                    'rfca_step_id',  // status pipeline baru (FR/TP/C/...)
                    'rfca_type',
                ])
                ->orderBy($orderCol, $orderDir)
                ->orderBy('rfcaid', 'desc')
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
                $prefix   = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($r->sppbjktid, 0, 2));
                $routeMap = ['PB' => 'showsppbs', 'PJ' => 'showsppjs', 'PK' => 'showsppks', 'PT' => 'showsppts'];
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
        $sppbUrl = null;
        $sppbjktid = (string)($rfca->sppbjktid ?? '');
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
        if (!empty($rfca->csid)) {
            $csId = TrCS::where('csid', $rfca->csid)->value('id');
            if ($csId) {
                $csHash = Hashids::encode($csId);
                $csUrl  = url("/showcs/{$csHash}");
            }
        }

        // Untuk convenience (mis. kirim email dsb)
        $eid_rfcaid = Hashids::encode($rfca->rfcaid);

        $rfcaSteps = TrRfcaStep::where('rfcaid', $rfca->rfcaid)
            ->orderBy('rfca_step_order')
            ->get();
                

        return view('pages.rfca.showrfca', [
            'rfca'            => $rfca,    
            'hash'           => $hash,
            'eid_rfcaid' => $eid_rfcaid,
            'poUrl'          => $poUrl,
            'sppbUrl'        => $sppbUrl,
            'csUrl'          => $csUrl,        
            'rfcaSteps'   => $rfcaSteps,       
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

        $rfca = TrRfca::findOrFail($id);

        try {
            DB::transaction(function () use ($rfca, $rfcaType, $user) {
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

                // optional: hapus step lama dulu kalau perlu
                // TrRfcaStep::where('rfcaid', $rfca->rfcaid)->delete();

                foreach ($steps as $step) {
                    TrRfcaStep::create([
                        'rfcaid'                   => $rfca->rfcaid,
                        'ponbr'                    => $rfca->ponbr,
                        'cpny_id'                  => $rfca->cpny_id,
                        'rfca_step_order'          => $step->rfca_step_order,
                        'rfca_step_id'             => $step->rfca_step_id,
                        'rfca_step_descr'          => $step->rfca_step_descr,
                        'rfca_step_department_id'  => $step->rfca_step_department_id,
                        'rfca_type'                => $step->rfca_type,
                        'calr_gen'                 => $step->calr_gen,
                        'rfca_step_user'           => $username,
                        'rfca_step_date'           => $now,
                        'status_rfca'              => 'A',
                        'created_by'               => $username,
                        'updated_by'               => null,
                    ]);
                }

                // simpan rfca_type di header RFCA
                $rfca->rfca_type = $rfcaType;
                $rfca->updated_by = $username;
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

                // Cari "active step": step pertama yang belum Approved/Rejected
                $activeStep = TrRfcaStep::where('rfcaid', $rfca->rfcaid)
                    ->orderBy('rfca_step_order')
                    ->whereNull('status_rfca')
                    ->first();

                if (!$activeStep) {
                    throw new \RuntimeException('No active RFCA Step to approve.');
                }

                // Update detail step
                $activeStep->rfca_step_user = $username;
                $activeStep->rfca_step_date = Carbon::now();
                $activeStep->status_rfca    = 'A';
                $activeStep->updated_by     = $username;
                $activeStep->save();

                // 🔼 Update header TrRfca mengikuti step yang baru di-approve
                $rfca->rfca_step_order = $activeStep->rfca_step_order;
                $rfca->rfca_step_id    = $activeStep->rfca_step_id;
                $rfca->status_rfca     = $activeStep->status_rfca; // 'A'
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
