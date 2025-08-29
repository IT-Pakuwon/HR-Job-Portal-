<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DataFeed;
use App\Models\ProjectTask;
use App\Models\T_approval;
use App\Models\Viewtrxall;
use App\Models\Agenda;
use Illuminate\Support\Carbon;
use App\Models\News;
use App\Models\Users_talenta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use App\Models\CompanyPG;
use App\Models\ViewJobApply;
use App\Models\ViewtrPurch;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    
    public function index_xxx()
    {
        $dataFeed = new DataFeed();   
        $user = request()->user();     
        if (!$user) return redirect()->route('login');

        // Models
        $trxM = new ViewTrxAll();     // contoh: DB iamsys
        $appM = new ViewJobApply();   // contoh: DB hrdb
        $aprM = new T_approval();     // lokasi t_approval (bisa beda DB)
        $purchM = new ViewtrPurch();

        // Conn + DB names
        $trxConn = $trxM->getConnectionName() ?: config('database.default');
        $appConn = $appM->getConnectionName() ?: config('database.default');
        $aprConn = $aprM->getConnectionName() ?: config('database.default');
        $purchConn = $purchM->getConnectionName() ?: config('database.default');

        $trxDb = DB::connection($trxConn)->getDatabaseName();
        $appDb = DB::connection($appConn)->getDatabaseName();
        $aprDb = DB::connection($aprConn)->getDatabaseName();
        $purchDb = DB::connection($purchConn)->getDatabaseName();

        // Table names
        $tblTrx = $trxM->getTable();   // ex: view_trx_all
        $tblApp = $appM->getTable();   // ex: v_job_apply_with_posting
        $tblApr = $aprM->getTable();   // ex: t_approval
        $tblPurch = $purchM->getTable(); 

        // Fully-qualified
        $tblTrxFQ = "`{$trxDb}`.`{$tblTrx}`";
        $tblAppFQ = "`{$appDb}`.`{$tblApp}`";
        $tblAprFQ = "`{$aprDb}`.`{$tblApr}`";
        $tblPurchFQ = "`{$purchDb}`.`{$tblPurch}`";

        // --- UNION ALL: kolom harus identik di kedua view ---
        // kalau kamu punya kolom lain, tinggal tambahkan di SELECT keduanya.
        $unionSql = "
            SELECT id, docdate, cpnyid, departementid, infohd, url, docid
            FROM {$tblTrxFQ}
            UNION ALL
            SELECT id, docdate, cpnyid, departementid, infohd, url, docid
            FROM {$tblAppFQ}
            UNION ALL
            SELECT id, docdate, cpnyid, departementid, infohd, url, docid
            FROM {$tblPurchFQ}
        ";
        // dd($unionSql);
        // Jalankan query di koneksi ViewTrxAll (asumsi kedua DB ada di server yang sama)
        $tr_approval = DB::connection($trxConn)
            ->table(DB::raw("({$unionSql}) as allv"))
            ->whereExists(function ($q) use ($user, $tblAprFQ) {
                $q->select(DB::raw(1))
                ->from(DB::raw("{$tblAprFQ} as ta"))
                ->whereColumn('ta.docid', 'allv.docid')
                ->where('ta.aprvusername', 'like', "%{$user->username}%")
                ->where('ta.status', 'P')
                ->whereNotNull('ta.aprvdatebefore');
            })
            ->select('allv.id','allv.docdate','allv.cpnyid','allv.departementid','allv.infohd','allv.url','allv.docid')
            ->get();
            // dd($tr_approval);
        $datenow = Carbon::now()->format('Y-m-d');

        $agendas = Agenda::whereDate('startdate', $datenow)
            ->where(function($q) use ($user) {
                $q->where('created_user', $user->username)
                ->orWhereRaw('FIND_IN_SET(?, participant)', [$user->username]);
            })
            ->orderBy('startdate', 'asc')
            ->get();

        $news = News::where('status','C')->orderBy('created_at','desc')->get();

        return view('pages/dashboard/dashboard', compact('dataFeed','tr_approval','agendas','news'));
    }


    public function showProfile()
    {
        $user = Auth::user();
        $talenta = Users_talenta::where('employee_id', $user->npk)->first();
        // dd($talenta);
        return view('profile.show', compact('talenta'));
    }

    public function waitingApproval()
    {
        $user = request()->user();              

        return view('pages.dashboard.waitingapproval', compact('user'));
    }
   
    public function Waitingjson_xx(Request $request)
    {
        $user = request()->user();
        if (!$user) {
            return response()->json(['data' => []], 401);
        }

       // Models
        $trxM = new ViewTrxAll();     // contoh: DB iamsys
        $appM = new ViewJobApply();   // contoh: DB hrdb
        $aprM = new T_approval();     // lokasi t_approval (bisa beda DB)
        $purchM = new ViewtrPurch();


        // Conn + DB names
        $trxConn = $trxM->getConnectionName() ?: config('database.default');
        $appConn = $appM->getConnectionName() ?: config('database.default');
        $aprConn = $aprM->getConnectionName() ?: config('database.default');
        $purchConn = $purchM->getConnectionName() ?: config('database.default');

        $trxDb = DB::connection($trxConn)->getDatabaseName();
        $appDb = DB::connection($appConn)->getDatabaseName();
        $aprDb = DB::connection($aprConn)->getDatabaseName();
        $purchDb = DB::connection($purchConn)->getDatabaseName();

        // Table names
        $tblTrx = $trxM->getTable();   // ex: view_trx_all
        $tblApp = $appM->getTable();   // ex: v_job_apply_with_posting
        $tblApr = $aprM->getTable();   // ex: t_approval
        $tblPurch = $purchM->getTable();

        // Fully-qualified
        $tblTrxFQ = "`{$trxDb}`.`{$tblTrx}`";
        $tblAppFQ = "`{$appDb}`.`{$tblApp}`";
        $tblAprFQ = "`{$aprDb}`.`{$tblApr}`";
        $tblPurchFQ = "`{$purchDb}`.`{$tblPurch}`";

        // --- UNION ALL: kolom harus identik di kedua view ---
        // kalau kamu punya kolom lain, tinggal tambahkan di SELECT keduanya.
        $unionSql = "
            SELECT id, docdate, cpnyid, departementid, infohd, url, docid
            FROM {$tblTrxFQ}
            UNION ALL
            SELECT id, docdate, cpnyid, departementid, infohd, url, docid
            FROM {$tblAppFQ}
            UNION ALL
            SELECT id, docdate, cpnyid, departementid, infohd, url, docid
            FROM {$tblPurchFQ}
        ";
        // dd($unionSql);
        // Jalankan query di koneksi ViewTrxAll (asumsi kedua DB ada di server yang sama)
        $tr_approval = DB::connection($trxConn)
            ->table(DB::raw("({$unionSql}) as allv"))
            ->whereExists(function ($q) use ($user, $tblAprFQ) {
                $q->select(DB::raw(1))
                ->from(DB::raw("{$tblAprFQ} as ta"))
                ->whereColumn('ta.docid', 'allv.docid')
                ->where('ta.aprvusername', 'like', "%{$user->username}%")
                ->where('ta.status', 'P')
                ->whereNotNull('ta.aprvdatebefore');
            })
            ->select('allv.id','allv.docdate','allv.cpnyid','allv.departementid','allv.infohd','allv.url','allv.docid')
            ->get();

        return response()->json(['data' => $tr_approval]);

    }
    
    public function Approvejson_xxx(Request $request)
    {

        $user = request()->user();
        if (!$user) {
            return response()->json(['data' => []], 401);
        }

       // Models
        $trxM = new ViewTrxAll();     // contoh: DB iamsys
        $appM = new ViewJobApply();   // contoh: DB hrdb
        $aprM = new T_approval();     // lokasi t_approval (bisa beda DB)
        $purchM = new ViewtrPurch();

        // Conn + DB names
        $trxConn = $trxM->getConnectionName() ?: config('database.default');
        $appConn = $appM->getConnectionName() ?: config('database.default');
        $aprConn = $aprM->getConnectionName() ?: config('database.default');
        $purchConn = $purchM->getConnectionName() ?: config('database.default');

        $trxDb = DB::connection($trxConn)->getDatabaseName();
        $appDb = DB::connection($appConn)->getDatabaseName();
        $aprDb = DB::connection($aprConn)->getDatabaseName();
        $purchDb = DB::connection($purchConn)->getDatabaseName();

        // Table names
        $tblTrx = $trxM->getTable();   // ex: view_trx_all
        $tblApp = $appM->getTable();   // ex: v_job_apply_with_posting
        $tblApr = $aprM->getTable();   // ex: t_approval
        $tblPurch = $purchM->getTable();

        // Fully-qualified
        $tblTrxFQ = "`{$trxDb}`.`{$tblTrx}`";
        $tblAppFQ = "`{$appDb}`.`{$tblApp}`";
        $tblAprFQ = "`{$aprDb}`.`{$tblApr}`";
        $tblPurchFQ = "`{$purchDb}`.`{$tblPurch}`";

        // --- UNION ALL: kolom harus identik di kedua view ---
        // kalau kamu punya kolom lain, tinggal tambahkan di SELECT keduanya.
        $unionSql = "
            SELECT id, docdate, cpnyid, departementid, infohd, url, docid
            FROM {$tblTrxFQ}
            UNION ALL
            SELECT id, docdate, cpnyid, departementid, infohd, url, docid
            FROM {$tblAppFQ}
            UNION ALL
            SELECT id, docdate, cpnyid, departementid, infohd, url, docid
            FROM {$tblPurchFQ}
        ";
        // dd($unionSql);
        // Jalankan query di koneksi ViewTrxAll (asumsi kedua DB ada di server yang sama)
        $tr_approval = DB::connection($trxConn)
            ->table(DB::raw("({$unionSql}) as allv"))
            ->whereExists(function ($q) use ($user, $tblAprFQ) {
                $q->select(DB::raw(1))
                ->from(DB::raw("{$tblAprFQ} as ta"))
                ->whereColumn('ta.docid', 'allv.docid')
                ->where('ta.aprvusername', 'like', "%{$user->username}%")
                ->where('ta.status', 'A')
                ->whereNotNull('ta.aprvdatebefore');
            })
            ->select('allv.id','allv.docdate','allv.cpnyid','allv.departementid','allv.infohd','allv.url','allv.docid')
            ->get();

        return response()->json(['data' => $tr_approval]);

    }

    // DashboardController.php (potong ganti 3 method ini)

    public function index()
    {
        $dataFeed = new DataFeed();
        $user = request()->user();
        if (!$user) return redirect()->route('login');

        // Models
        $trxM   = new ViewTrxAll();   // iamsys (server A)
        $appM   = new ViewJobApply(); // jobportal (server A/B)
        $aprM   = new T_approval();   // das_voucher (server A dengan ViewTrxAll)
        $purchM = new ViewtrPurch();  // purchasing (server B)

        // koneksi & table
        $trxConn   = $trxM->getConnectionName()   ?: config('database.default');
        $appConn   = $appM->getConnectionName()   ?: config('database.default');
        $aprConn   = $aprM->getConnectionName()   ?: config('database.default');
        $purchConn = $purchM->getConnectionName() ?: config('database.default');

        $tblTrx   = $trxM->getTable();
        $tblApp   = $appM->getTable();
        $tblApr   = $aprM->getTable();
        $tblPurch = $purchM->getTable();

        // 1) ambil DOCID yang perlu user approve (P = Pending)
        $docids = DB::connection($aprConn)->table($tblApr)
            ->where('aprvusername', 'like', "%{$user->username}%")
            ->where('status', 'P')
            ->whereNotNull('aprvdatebefore')
            ->pluck('docid')
            ->unique()
            ->values();

        if ($docids->isEmpty()) {
            $tr_approval = collect();
        } else {
            // helper ambil data per sumber + chunk
            $selectCols = [
                'id', 'docdate', 'cpnyid', 'departementid', 'infohd', 'url', 'docid'
            ];

            $fetchByDocids = function (string $conn, string $table) use ($docids, $selectCols) {
                $out = collect();
                foreach ($docids->chunk(500) as $chunk) {
                    $out = $out->concat(
                        DB::connection($conn)->table($table)
                            ->whereIn('docid', $chunk->all())
                            ->select($selectCols)
                            ->get()
                    );
                }
                return $out;
            };

            // 2) tarik dari masing-masing server
            $rowsTrx   = $fetchByDocids($trxConn,   $tblTrx);
            $rowsApp   = $fetchByDocids($appConn,   $tblApp);

            // purchasing bisa gagal (beda server), tangkap & log
            try {
                $rowsPurch = $fetchByDocids($purchConn, $tblPurch);
            } catch (\Throwable $e) {
                Log::warning('Fetch purchasing failed', ['err' => $e->getMessage()]);
                $rowsPurch = collect();
            }

            // 3) merge
            $tr_approval = $rowsTrx->concat($rowsApp)->concat($rowsPurch)->values();

            Log::info('Dashboard approvals', [
                'user'          => $user->username,
                'docids_count'  => $docids->count(),
                'docids_sample' => $docids->take(5)->values(),
                'rows_trx'      => $rowsTrx->count(),
                'rows_app'      => $rowsApp->count(),
                'rows_purch'    => $rowsPurch->count(),
            ]);
        }

        $datenow = \Illuminate\Support\Carbon::now()->format('Y-m-d');

        $agendas = Agenda::whereDate('startdate', $datenow)
            ->where(function($q) use ($user) {
                $q->where('created_user', $user->username)
                ->orWhereRaw('FIND_IN_SET(?, participant)', [$user->username]);
            })
            ->orderBy('startdate', 'asc')
            ->get();

        $news = News::where('status','C')->orderBy('created_at','desc')->get();

        return view('pages/dashboard/dashboard', compact('dataFeed','tr_approval','agendas','news'));
    }

    public function Waitingjson(Request $request)
    {
        $user = request()->user();
        if (!$user) return response()->json(['data' => []], 401);

        $trxM   = new ViewTrxAll();
        $appM   = new ViewJobApply();
        $aprM   = new T_approval();
        $purchM = new ViewtrPurch();

        $trxConn   = $trxM->getConnectionName()   ?: config('database.default');
        $appConn   = $appM->getConnectionName()   ?: config('database.default');
        $aprConn   = $aprM->getConnectionName()   ?: config('database.default');
        $purchConn = $purchM->getConnectionName() ?: config('database.default');

        $tblTrx   = $trxM->getTable();
        $tblApp   = $appM->getTable();
        $tblApr   = $aprM->getTable();
        $tblPurch = $purchM->getTable();

        $docids = DB::connection($aprConn)->table($tblApr)
            ->where('aprvusername', 'like', "%{$user->username}%")
            ->where('status', 'P')
            ->whereNotNull('aprvdatebefore')
            ->pluck('docid')->unique()->values();

        if ($docids->isEmpty()) return response()->json(['data' => []]);

        $selectCols = ['id','docdate','cpnyid','departementid','infohd','url','docid'];
        $fetch = function(string $conn, string $table) use ($docids, $selectCols) {
            $out = collect();
            foreach ($docids->chunk(500) as $chunk) {
                $out = $out->concat(
                    DB::connection($conn)->table($table)
                    ->whereIn('docid', $chunk->all())
                    ->select($selectCols)
                    ->get()
                );
            }
            return $out;
        };

        $data = collect();
        $data = $data->concat($fetch($trxConn, $tblTrx));
        $data = $data->concat($fetch($appConn, $tblApp));

        try { $data = $data->concat($fetch($purchConn, $tblPurch)); }
        catch (\Throwable $e) { Log::warning('Waitingjson: purchasing fetch failed', ['err'=>$e->getMessage()]); }

        return response()->json(['data' => $data->values()]);
    }

    public function Approvejson(Request $request)
    {
        $user = request()->user();
        if (!$user) return response()->json(['data' => []], 401);

        $trxM   = new ViewTrxAll();
        $appM   = new ViewJobApply();
        $aprM   = new T_approval();
        $purchM = new ViewtrPurch();

        $trxConn   = $trxM->getConnectionName()   ?: config('database.default');
        $appConn   = $appM->getConnectionName()   ?: config('database.default');
        $aprConn   = $aprM->getConnectionName()   ?: config('database.default');
        $purchConn = $purchM->getConnectionName() ?: config('database.default');

        $tblTrx   = $trxM->getTable();
        $tblApp   = $appM->getTable();
        $tblApr   = $aprM->getTable();
        $tblPurch = $purchM->getTable();

        $docids = DB::connection($aprConn)->table($tblApr)
            ->where('aprvusername', 'like', "%{$user->username}%")
            ->where('status', 'A')
            ->whereNotNull('aprvdatebefore')
            ->pluck('docid')->unique()->values();

        if ($docids->isEmpty()) return response()->json(['data' => []]);

        $selectCols = ['id','docdate','cpnyid','departementid','infohd','url','docid'];
        $fetch = function(string $conn, string $table) use ($docids, $selectCols) {
            $out = collect();
            foreach ($docids->chunk(500) as $chunk) {
                $out = $out->concat(
                    DB::connection($conn)->table($table)
                    ->whereIn('docid', $chunk->all())
                    ->select($selectCols)
                    ->get()
                );
            }
            return $out;
        };

        $data = collect();
        $data = $data->concat($fetch($trxConn, $tblTrx));
        $data = $data->concat($fetch($appConn, $tblApp));
        try { $data = $data->concat($fetch($purchConn, $tblPurch)); }
        catch (\Throwable $e) { Log::warning('Approvejson: purchasing fetch failed', ['err'=>$e->getMessage()]); }

        return response()->json(['data' => $data->values()]);
    }


  

}

