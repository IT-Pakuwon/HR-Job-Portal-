<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DataFeed;
use App\Models\TrApproval;
use App\Models\Viewtrxall;
use App\Models\ViewJobApply;
use App\Models\ViewtrPurch;
use App\Models\ViewDasAll;
use App\Models\Agenda;
use App\Models\News;
use App\Models\Users_talenta;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Vinkla\Hashids\Facades\Hashids;
use App\Models\Autonbr;

class DashboardController extends Controller
{
    public function showProfile()
    {
        
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $talenta = User::where('username', $user->username)->first();
        return view('profile.show', compact('talenta'));
    }

    public function index()
    {
        $dataFeed = new DataFeed();
        $user = request()->user();
        if (!$user) return redirect()->route('login');

        $datenow = now()->format('Y-m-d');

        // $agendas = Agenda::whereDate('startdate', $datenow)
        //     ->where(function ($q) use ($user) {
        //         $q->where('created_user', $user->username)
        //           ->orWhereRaw('FIND_IN_SET(?, participant)', [$user->username]);
        //     })
        //     ->orderBy('startdate', 'asc')
        //     ->get();

        // $news = News::where('status', 'C')
        //     ->orderBy('created_at', 'desc')
        //     ->get();

        // $doctypes = Autonbr::query()
        //     ->select('doctype')
        //     ->distinct()
        //     ->where('status', 'A')
        //     ->orderBy('doctype')
        //     ->pluck('doctype')
        //     ->values();
        $doctypes = Autonbr::query()
            ->select('doctype', 'doctype_descr')
            ->where('status', 'A')
            ->groupBy('doctype', 'doctype_descr') // aman di PostgreSQL
            ->orderBy('doctype')
            ->get();

        return view('pages/dashboard/dashboard', [
            'dataFeed'   => $dataFeed,
            'tr_approval'=> collect(), // data via AJAX
            // 'agendas'    => $agendas,
            // 'news'       => $news,
            'doctypes'   => $doctypes,
        ]);
    }

    public function Waitingjson(Request $request)
    {
        return $this->approvalJson($request, 'P');
    }

    public function Approvejson(Request $request)
    {
        return $this->approvalJson($request, 'A');
    }

    /**
     * Shared approval loader (FAST)
     */

    private function approvalJson(Request $request, string $status)
    {
        $user = $request->user();
        if (!$user) return response()->json(['data' => []], 401);

        $doctype = strtoupper(trim((string) $request->get('doctype', '')));
        $doctype = ($doctype === 'ALL') ? '' : $doctype;

        $trxM   = new Viewtrxall();
        $appM   = new ViewJobApply();
        $aprM   = new TrApproval();
        $purchM = new ViewtrPurch();
        $dasM   = new ViewDasAll();

        $trxConn   = $trxM->getConnectionName()   ?: config('database.default');
        $appConn   = $appM->getConnectionName()   ?: config('database.default');
        $aprConn   = $aprM->getConnectionName()   ?: config('database.default');
        $purchConn = $purchM->getConnectionName() ?: config('database.default');
        $dasConn   = $dasM->getConnectionName()   ?: config('database.default');

        $tblTrx   = $trxM->getTable();
        $tblApp   = $appM->getTable();
        $tblApr   = $aprM->getTable();
        $tblPurch = $purchM->getTable();
        $tblDas   = $dasM->getTable();

        $username = strtolower(trim((string) $user->username));

        // 1. ambil approval rows + aprv_datebefore
        $approvalRows = DB::connection($aprConn)->table($tblApr)
            ->select('refnbr', 'aprv_datebefore')
            ->whereRaw(
                "(',' || lower(regexp_replace(coalesce(aprv_username,''), '\s+', '', 'g')) || ',') like ?",
                ['%,' . $username . ',%']
            )
            ->where('status', $status)
            ->whereNotNull('aprv_datebefore')
            ->get();

        if ($approvalRows->isEmpty()) {
            return response()->json(['data' => []]);
        }

        // 2. group by refnbr, ambil aprv_datebefore terbaru per doc
        $approvalMap = $approvalRows
            ->groupBy(fn ($r) => strtoupper(trim($r->refnbr)))
            ->map(function ($rows, $refnbr) {
                $latest = collect($rows)
                    ->sortByDesc(fn ($r) => $r->aprv_datebefore)
                    ->first();

                return [
                    'refnbr' => $refnbr,
                    'aprv_datebefore' => $latest->aprv_datebefore,
                ];
            });

        $docids = $approvalMap->keys()->values();

        // 3. filter doctype jika dipilih
        if ($doctype !== '') {
            $docids = $docids->filter(function ($docid) use ($doctype) {
                if (!preg_match('/^[A-Z]+/', $docid, $m)) return false;
                return $m[0] === $doctype;
            })->values();
        }

        if ($docids->isEmpty()) {
            return response()->json(['data' => []]);
        }

        $selectCols = ['id', 'docdate', 'cpnyid', 'departementid', 'infohd', 'url', 'docid'];

        $fetch = function (string $conn, string $table) use ($docids, $selectCols) {
            $out = collect();

            foreach ($docids->chunk(1200) as $chunk) {
                $rows = DB::connection($conn)->table($table)
                    ->whereIn('docid', $chunk->all())
                    ->select($selectCols)
                    ->get();

                $out = $out->concat($rows);
            }

            return $out;
        };

        $t0 = microtime(true);

        $data = collect()
            ->concat($fetch($trxConn, $tblTrx))
            ->concat($fetch($appConn, $tblApp));

        try {
            $data = $data->concat($fetch($purchConn, $tblPurch));
        } catch (\Throwable $e) {
            Log::warning('approvalJson: purchasing fetch failed', [
                'err' => $e->getMessage()
            ]);
        }

        try {
            $data = $data->concat($fetch($dasConn, $tblDas));
        } catch (\Throwable $e) {
            Log::warning('approvalJson: DAS fetch failed', [
                'err' => $e->getMessage()
            ]);
        }

        // 4. pakai aprv_datebefore sebagai docdate
        $data = $data->map(function ($r) use ($approvalMap) {
            $docidKey = strtoupper(trim($r->docid));
            $approval = $approvalMap->get($docidKey);

            return [
                'hid'           => Hashids::encode($r->id),
                'docid'         => $r->docid,
                'docdate'       => $approval['aprv_datebefore'] ?? null,
                'cpnyid'        => $r->cpnyid,
                'departementid' => $r->departementid,
                'infohd'        => $r->infohd,
                'url'           => $r->url,
            ];
        })
        ->sortByDesc(function ($r) {
            return $r['docdate'] ?? '';
        })
        ->values();

        Log::info('approvalJson perf', [
            'user'    => $user->username,
            'status'  => $status,
            'doctype' => $doctype ?: 'ALL',
            'docids'  => $docids->count(),
            'rows'    => $data->count(),
            'ms'      => (int) ((microtime(true) - $t0) * 1000),
        ]);

        return response()->json(['data' => $data]);
    }

    private function approvalJson_old(Request $request, string $status)
    {
        $user = $request->user();
        if (!$user) return response()->json(['data' => []], 401);

        $doctype = strtoupper(trim((string) $request->get('doctype', '')));
        $doctype = ($doctype === 'ALL') ? '' : $doctype;

        $trxM   = new Viewtrxall();
        $appM   = new ViewJobApply();
        $aprM   = new TrApproval();
        $purchM = new ViewtrPurch();

        $trxConn   = $trxM->getConnectionName()   ?: config('database.default');
        $appConn   = $appM->getConnectionName()   ?: config('database.default');
        $aprConn   = $aprM->getConnectionName()   ?: config('database.default');
        $purchConn = $purchM->getConnectionName() ?: config('database.default');

        $tblTrx   = $trxM->getTable();
        $tblApp   = $appM->getTable();
        $tblApr   = $aprM->getTable();
        $tblPurch = $purchM->getTable();

        $username = strtolower(trim((string) $user->username));

        // 1. ambil approval rows + aprv_datebefore
        $approvalRows = DB::connection($aprConn)->table($tblApr)
            ->select('refnbr', 'aprv_datebefore')
            ->whereRaw(
                "(',' || lower(regexp_replace(coalesce(aprv_username,''), '\s+', '', 'g')) || ',') like ?",
                ['%,' . $username . ',%']
            )
            ->where('status', $status)
            ->whereNotNull('aprv_datebefore')
            ->get();

        if ($approvalRows->isEmpty()) {
            return response()->json(['data' => []]);
        }

        // 2. group by refnbr, ambil aprv_datebefore terbaru per doc
        $approvalMap = $approvalRows
            ->groupBy(fn ($r) => strtoupper(trim($r->refnbr)))
            ->map(function ($rows, $refnbr) {
                $latest = collect($rows)
                    ->sortByDesc(fn ($r) => $r->aprv_datebefore)
                    ->first();

                return [
                    'refnbr' => $refnbr,
                    'aprv_datebefore' => $latest->aprv_datebefore,
                ];
            });

        $docids = $approvalMap->keys()->values();

        // 3. filter doctype jika dipilih
        if ($doctype !== '') {
            $docids = $docids->filter(function ($docid) use ($doctype) {
                if (!preg_match('/^[A-Z]+/', $docid, $m)) return false;
                return $m[0] === $doctype;
            })->values();
        }

        if ($docids->isEmpty()) {
            return response()->json(['data' => []]);
        }

        $selectCols = ['id', 'docdate', 'cpnyid', 'departementid', 'infohd', 'url', 'docid'];

        $fetch = function (string $conn, string $table) use ($docids, $selectCols) {
            $out = collect();

            foreach ($docids->chunk(1200) as $chunk) {
                $rows = DB::connection($conn)->table($table)
                    ->whereIn('docid', $chunk->all())
                    ->select($selectCols)
                    ->get();

                $out = $out->concat($rows);
            }

            return $out;
        };

        $t0 = microtime(true);

        $data = collect()
            ->concat($fetch($trxConn, $tblTrx))
            ->concat($fetch($appConn, $tblApp));

        try {
            $data = $data->concat($fetch($purchConn, $tblPurch));
        } catch (\Throwable $e) {
            Log::warning('approvalJson: purchasing fetch failed', [
                'err' => $e->getMessage()
            ]);
        }

        // 4. pakai aprv_datebefore sebagai docdate
        $data = $data->map(function ($r) use ($approvalMap) {
            $docidKey = strtoupper(trim($r->docid));
            $approval = $approvalMap->get($docidKey);

            return [
                'hid'           => Hashids::encode($r->id),
                'docid'         => $r->docid,
                'docdate'       => $approval['aprv_datebefore'] ?? null, // ambil dari approval
                'cpnyid'        => $r->cpnyid,
                'departementid' => $r->departementid,
                'infohd'        => $r->infohd,
                'url'           => $r->url,
            ];
        })
        ->sortByDesc(function ($r) {
            return $r['docdate'] ?? '';
        })
        ->values();

        Log::info('approvalJson perf', [
            'user'    => $user->username,
            'status'  => $status,
            'doctype' => $doctype ?: 'ALL',
            'docids'  => $docids->count(),
            'rows'    => $data->count(),
            'ms'      => (int) ((microtime(true) - $t0) * 1000),
        ]);

        return response()->json(['data' => $data]);
    }

  
}
