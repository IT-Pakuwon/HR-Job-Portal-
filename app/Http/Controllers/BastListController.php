<?php

namespace App\Http\Controllers;

use App\Models\TrBast;
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
use Illuminate\Support\Str;
use Vinkla\Hashids\Facades\Hashids;

class BastListController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // user->cpny_id bisa "AW" atau "AW,GPS"
        $cpnyRaw = $user->cpny_id ?? '';
        $cpnyList = $cpnyRaw !== '' ? array_map('trim', explode(',', $cpnyRaw)) : [];

        // user->department_id bisa "IT" atau "IT,ENG"
        $deptRaw = $user->department_id ?? '';
        $deptList = $deptRaw !== '' ? array_map('trim', explode(',', $deptRaw)) : [];

        // Jobs berasal dari TrPOterm
        $bastjobs = TrPOterm::query()
            ->when(!empty($cpnyList), fn ($q) => $q->whereIn('cpny_id', $cpnyList))
            ->when(!empty($deptList), fn ($q) => $q->whereIn('department_id', $deptList))
            ->where('flag_bast', true)
            ->whereNull('bastid')
            ->where('status', 'A')
            ->count();

        // BAST stats (tanpa basttype, tanpa returnjobs, TANPA filter created_by)
        $onProgress = TrBast::query()
            ->when(!empty($cpnyList), fn ($q) => $q->whereIn('cpny_id', $cpnyList))
            ->when(!empty($deptList), fn ($q) => $q->whereIn('department_id', $deptList))
            ->where('status', 'P')
            ->count();

        $completed = TrBast::query()
            ->when(!empty($cpnyList), fn ($q) => $q->whereIn('cpny_id', $cpnyList))
            ->when(!empty($deptList), fn ($q) => $q->whereIn('department_id', $deptList))
            ->where('status', 'C')
            ->count();

        $rejected = TrBast::query()
            ->when(!empty($cpnyList), fn ($q) => $q->whereIn('cpny_id', $cpnyList))
            ->when(!empty($deptList), fn ($q) => $q->whereIn('department_id', $deptList))
            ->where('status', 'R')
            ->count();

        $revise = TrBast::query()
            ->when(!empty($cpnyList), fn ($q) => $q->whereIn('cpny_id', $cpnyList))
            ->when(!empty($deptList), fn ($q) => $q->whereIn('department_id', $deptList))
            ->where('status', 'D')
            ->count();

        $all = TrBast::query()
            ->when(!empty($cpnyList), fn ($q) => $q->whereIn('cpny_id', $cpnyList))
            ->when(!empty($deptList), fn ($q) => $q->whereIn('department_id', $deptList))
            ->count();

        $allActive = TrBast::query()
            ->when(!empty($cpnyList), fn ($q) => $q->whereIn('cpny_id', $cpnyList))
            ->when(!empty($deptList), fn ($q) => $q->whereIn('department_id', $deptList))
            ->whereIn('status', ['P', 'C']) // Only On Progress + Completed
            ->count();

        return view('pages.bast.bastlist', compact(
            'bastjobs', 'onProgress', 'completed', 'all', 'rejected', 'revise', 'allActive'
        ));
    }

    public function json(Request $req)
    {
        $scope = strtolower((string) $req->query('scope', 'bastjobs'));
        $user = Auth::user();

        // parse cpny_id & department_id multiple
        $cpnyRaw = $user->cpny_id ?? '';
        $cpnyList = $cpnyRaw !== '' ? array_map('trim', explode(',', $cpnyRaw)) : [];

        $deptRaw = $user->department_id ?? '';
        $deptList = $deptRaw !== '' ? array_map('trim', explode(',', $deptRaw)) : [];

        $draw = (int) $req->input('draw', 1);
        $start = (int) $req->input('start', 0);
        $length = (int) $req->input('length', 25);
        $search = trim((string) $req->input('search.value', ''));

        $vendor = trim((string) $req->query('vendor', ''));
        $terms = trim((string) $req->query('terms', ''));
        $startDate = $req->query('start_date');
        $endDate = $req->query('end_date');

        if ($scope === 'bastjobs') {
            // Sumber list “jobs” dari TrPOterm
            // $base = TrPOterm::query()
            //     ->when(!empty($cpnyList), fn($q) => $q->whereIn('cpny_id', $cpnyList))
            //     ->when(!empty($deptList), fn($q) => $q->whereIn('department_id', $deptList))
            //     ->where('flag_bast', true)
            //     ->whereNull('bastid')
            //     ->select([
            //         'id', 'ponbr', 'cpny_id', 'vendorname', 'created_by',
            //         'terms_name', 'progress_pct', 'payment_pct',
            //         DB::raw("'HOLD' as status") // <= status dummy utk jobs
            //     ]);
            $base = TrPOterm::query()
                ->from('tr_po_term as t') // sesuaikan kalau table name TrPOterm beda
                ->when(!empty($cpnyList), fn ($q) => $q->whereIn('t.cpny_id', $cpnyList))
                ->when(!empty($deptList), fn ($q) => $q->whereIn('t.department_id', $deptList))
                ->where('t.flag_bast', true)
                ->whereNull('t.bastid')
                ->where('t.status', 'A')
                ->leftJoin('tr_po as p', function ($j) {
                    $j->on('p.ponbr', '=', 't.ponbr')
                    ->on('p.cpny_id', '=', 't.cpny_id'); // penting untuk multi company
                })
                ->select([
                    't.id',
                    't.ponbr',
                    't.cpny_id',
                    't.vendorname',
                    't.created_by',
                    't.terms_name',
                    't.progress_pct',
                    't.payment_pct',

                    // ✅ ambil dari TrPO
                    'p.spkstartworkingdate',
                    'p.spkendtworkingdate',

                    DB::raw("'HOLD' as status"),
                ])
                ->orderBy('t.order_term', 'asc');

            if ($vendor !== '') {
                $base->where('t.vendorname', 'ilike', "%{$vendor}%");
            }

            if ($terms !== '') {
                $base->where('t.terms_name', 'ilike', "%{$terms}%");
            }

            if ($startDate) {
                $base->whereDate('p.spkstartworkingdate', '>=', $startDate);
            }

            if ($endDate) {
                $base->whereDate('p.spkendtworkingdate', '<=', $endDate);
            }
            // $orderColumns = [
            //     0=>'ponbr', 1=>'ponbr', 2=>'cpny_id', 3=>'vendorname',
            //     4=>'terms_name', 5=>'progress_pct', 6=>'payment_pct', 7=>'created_by',
            //     8=>'status',
            // ];
            $orderColumns = [
                0 => 'ponbr',       // dtr-control (abaikan)
                1 => 'ponbr',       // action (abaikan)
                2 => 'ponbr',
                3 => 'cpny_id',
                4 => 'vendorname',
                5 => 'spkstartworkingdate',
                6 => 'spkendtworkingdate',
                7 => 'terms_name',
                8 => 'progress_pct',
                9 => 'payment_pct',
                10 => 'created_by',
                11 => 'status',
            ];

            if ($search !== '') {
                $base->where(function ($q) use ($search) {
                    $q->where('ponbr', 'ilike', "%{$search}%")
                    ->orWhere('cpny_id', 'ilike', "%{$search}%")
                    ->orWhere('vendorname', 'ilike', "%{$search}%")
                    ->orWhere('created_by', 'ilike', "%{$search}%")
                    ->orWhere('terms_name', 'ilike', "%{$search}%");
                });
            }
        } else {
            $base = TrBast::query()
                ->when(!empty($cpnyList), fn ($q) => $q->whereIn('cpny_id', $cpnyList))
                ->when(
                    !empty($deptList) && $scope !== 'allactive',
                    fn ($q) => $q->whereIn('department_id', $deptList)
                )
                ->when($scope === 'onprogress', fn ($q) => $q->where('status', 'P'))
                ->when($scope === 'completed', fn ($q) => $q->where('status', 'C'))
                ->when($scope === 'rejected', fn ($q) => $q->where('status', 'R'))
                ->when($scope === 'revise', fn ($q) => $q->where('status', 'D'))
                ->when($scope === 'allactive', fn ($q) => $q->whereIn('status', ['P', 'C']))
                ->select([
                    'id', 'bastid', 'bastdate', 'ponbr',
                    'sppbjktid', 'cpny_id', 'created_by', 'status',
                    'vendorname', 'startdate', 'enddate', 'terms_id',
                ]);

            $orderColumns = [
                0 => 'bastid',
                1 => 'bastdate',
                2 => 'ponbr',
                3 => 'sppbjktid',
                4 => 'cpny_id',
                5 => 'created_by',
                6 => 'status',
            ];

            if ($search !== '') {
                $base->where(function ($q) use ($search) {
                    $q->where('bastid', 'ilike', "%{$search}%")
                    ->orWhere('ponbr', 'ilike', "%{$search}%")
                    ->orWhere('sppbjktid', 'ilike', "%{$search}%")
                    ->orWhere('cpny_id', 'ilike', "%{$search}%")
                    ->orWhere('created_by', 'ilike', "%{$search}%")
                    ->orWhereRaw("TO_CHAR(bastdate,'YYYY-MM-DD') ILIKE ?", ["%{$search}%"]);
                });
            }
            // 🔥 FILTERS (BAST)
            if ($vendor !== '') {
                $base->where('vendorname', 'ilike', "%{$vendor}%");
            }

            // optional (if you want terms_id filter)
            if ($terms !== '') {
                $base->where('terms_id', 'ilike', "%{$terms}%");
            }

            // 🔥 use BAST dates (NOT PO anymore)
            if ($startDate) {
                $base->whereDate('startdate', '>=', $startDate);
            }

            if ($endDate) {
                $base->whereDate('enddate', '<=', $endDate);
            }
        }

        $recordsTotal = (clone $base)->count();
        $recordsFiltered = (clone $base)->count();

        $orderIdx = (int) $req->input('order.0.column', $scope === 'bastjobs' ? 1 : 1);
        $orderDir = $req->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $orderColumns[$orderIdx] ?? ($scope === 'bastjobs' ? 'ponbr' : 'bastdate');

        $rows = $base->orderBy($orderCol, $orderDir)
                    ->orderBy($scope === 'bastjobs' ? 'ponbr' : 'bastid', 'desc')
                    ->skip($start)->take($length)
                    ->get();

        // ========= ENRICH / FORMAT =========
        // Map PONBR -> id (TrPo) supaya link PO bisa dipakai
        // $poIdMap = [];
        // $ponbrsForMap = $rows->pluck('ponbr')->filter()->unique()->values()->all();
        // if (!empty($ponbrsForMap)) {
        //     // $poIdMap = TrPo::whereIn('ponbr', $ponbrsForMap)
        //     //     ->pluck('id', 'ponbr')
        //     //     ->toArray();
        //     $poIdMap = TrPO::whereIn('ponbr', $ponbrsForMap)
        //         ->when(!empty($cpnyList), fn ($q) => $q->where('cpny_id', $cpnyList))
        //         ->pluck('id', 'ponbr')
        //         ->toArray();
        // }
        $poIdMap = [];
        $ponbrsForMap = $rows->pluck('ponbr')->filter()->unique()->values()->all();

        if (!empty($ponbrsForMap)) {
            $poRows = TrPO::query()
                ->select('id', 'ponbr', 'cpny_id')
                ->whereIn('ponbr', $ponbrsForMap)
                ->when(!empty($cpnyList), fn ($q) => $q->whereIn('cpny_id', $cpnyList))
                ->get();

            foreach ($poRows as $po) {
                $poIdMap[$po->cpny_id . '||' . $po->ponbr] = $po->id;
            }
        }

        $rows->transform(function ($r) use ($scope, $poIdMap) {
            if ($scope === 'bastjobs') {
                // // Hash untuk ID dari table TrPOterm (id term)
                // $r->term_eid = Hashids::encode((string) $r->id);

                // // tetap mapping PO untuk link show PO
                // $poId = $poIdMap[$r->ponbr] ?? null;
                // $r->ponbr_eid = $poId ? Hashids::encode((string) $poId) : null;
                $r->term_eid = Hashids::encode((string) $r->id);

                // PO link mapping (tetap)
                // $poId = $poIdMap[$r->ponbr] ?? null;
                // $r->ponbr_eid = $poId ? Hashids::encode((string) $poId) : null;
                $poKey = ($r->cpny_id ?? '') . '||' . ($r->ponbr ?? '');
                $poId = $poIdMap[$poKey] ?? null;
                $r->ponbr_eid = $poId ? Hashids::encode((string) $poId) : null;

                // ✅ format tanggal spk
                $r->spkstartworkingdate_fmt = $r->spkstartworkingdate
                    ? Carbon::parse($r->spkstartworkingdate)->format('Y-m-d')
                    : null;

                $r->spkendtworkingdate_fmt = $r->spkendtworkingdate
                    ? Carbon::parse($r->spkendtworkingdate)->format('Y-m-d')
                    : null;
            } else {
                $r->bastdate_fmt = $r->bastdate
                    ? Carbon::parse($r->bastdate)->format('Y-m-d')
                    : null;
                $r->bastid_eid = Hashids::encode((string) $r->id);

                // 🔗 PO link via PONBR
                // $poId = $poIdMap[$r->ponbr] ?? null;
                // $r->ponbr_eid = $poId ? Hashids::encode((string) $poId) : null;
                $poKey = ($r->cpny_id ?? '') . '||' . ($r->ponbr ?? '');
                $poId = $poIdMap[$poKey] ?? null;
                $r->ponbr_eid = $poId ? Hashids::encode((string) $poId) : null;

                // 🔗 SPPB/J/K/T link (berdasar prefix)
                $r->sppb_route = null;
                $r->sppb_eid = null;
                if (!empty($r->sppbjktid)) {
                    $prefix = Str::upper(Str::substr($r->sppbjktid, 0, 2));
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
                        } else { /* PT */
                            $id = TrSPPT::where('spptid', $r->sppbjktid)->value('id');
                        }

                        if ($id) {
                            $r->sppb_route = $routeMap[$prefix];
                            $r->sppb_eid = Hashids::encode((string) $id);
                        }
                    }
                }
            }

            // ===== status badge (untuk UI) =====
            $st = strtoupper((string) ($r->status ?? ''));

            // default
            $statusText = 'Unknown';
            $statusClass = 'bg-gray-200/60 text-gray-700 border border-gray-500/40';

            // mapping BAST (TrBast)
            switch ($st) {
                case 'P':
                    $statusText = 'On Progress';
                    $statusClass = 'bg-orange-200/60 text-orange-800 border border-orange-600/40';
                    break;
                case 'A':
                    $statusText = 'Approved';
                    $statusClass = 'bg-green-200/60 text-green-800 border border-green-600/40';
                    break;
                case 'C':
                    $statusText = 'Completed';
                    $statusClass = 'bg-emerald-100 text-emerald-700 border-emerald-200';
                    break;
                case 'R':
                    $statusText = 'Rejected';
                    $statusClass = 'bg-red-200/60 text-red-800 border border-red-600/40';
                    break;
                case 'D':
                    $statusText = 'Revise';
                    $statusClass = 'bg-yellow-200/60 text-yellow-800 border border-yellow-600/40';
                    break;
                case 'X':
                    $statusText = 'Canceled';
                    $statusClass = 'bg-gray-200/60 text-gray-700 border border-gray-500/40';
                    break;

                    // status dummy jobs
                case 'HOLD':
                    $statusText = 'Hold';
                    $statusClass = 'bg-purple-200/60 text-purple-800 border border-purple-600/40';
                    break;

                default:
                    $statusText = $st !== '' ? $st : 'Unknown';
                    $statusClass = 'bg-gray-200/60 text-gray-700 border border-gray-500/40';
                    break;
            }

            $r->status_label = $statusText;
            $r->status_class = $statusClass;
            // ================================

            return $r;
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows,
        ]);
    }
}
