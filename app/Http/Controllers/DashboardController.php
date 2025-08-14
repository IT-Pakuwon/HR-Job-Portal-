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

class DashboardController extends Controller
{
    public function index_xxx()
    {
        $dataFeed = new DataFeed();   
        $user = request()->user();     
        if (!$user) {
            return redirect()->route('login');
        }
               
        // Step 1: Ambil data dari database kedua (ViewTrxAll)
        $viewTrxAll = ViewTrxAll::get(); // Ambil semua atau tambahkan kondisi jika diperlukan

        // Step 2: Ambil semua docid dari ViewTrxAll untuk mencocokkan di T_approval
        $docIds = $viewTrxAll->pluck('docid')->toArray(); // Convert ke array
        
        // Step 2: Ambil data dari T_approval berdasarkan kondisi yang diberikan
        $trxApproval = T_approval::whereIn('docid', $docIds)
            ->where('aprvusername', 'like', "%" . $user->username . "%")
            ->where('status', 'P')
            ->whereNotNull('aprvdatebefore')
            ->pluck('docid')
            ->toArray(); // Ambil hanya docid yang memenuhi kondisi

        // Step 3: Ambil data yang cocok dari ViewTrxAll berdasarkan hasil query T_approval
        $tr_approval = ViewTrxAll::whereIn('docid', $trxApproval)
            ->select('id', 'docdate', 'cpnyid', 'departementid', 'infohd', 'url', 'docid')
            ->get();
            // dd($tr_approval);

        $datenow = Carbon::now()->format('Y-m-d');

        // $agendas = Agenda::whereDate('startdate', $datenow)
        //     ->where('created_user',$user->username)
        //     ->orderBy('startdate', 'asc')
        //     ->get();
        $agendas = Agenda::whereDate('startdate', $datenow)
            ->where(function($query) use ($user) {
                $query->where('created_user', $user->username)
                    ->orWhereRaw('FIND_IN_SET(?, participant)', [$user->username]);
            })
            ->orderBy('startdate', 'asc')
            ->get();


        $news = News::where('status','C')
            ->orderBy('created_at', 'Desc')
            ->get();

        return view('pages/dashboard/dashboard', compact('dataFeed','tr_approval','agendas','news'));
    }

    public function index()
    {
        $dataFeed = new DataFeed();   
        $user = request()->user();     
        if (!$user) return redirect()->route('login');

        // Models
        $trxM = new ViewTrxAll();     // contoh: DB iamsys
        $appM = new ViewJobApply();   // contoh: DB hrdb
        $aprM = new T_approval();     // lokasi t_approval (bisa beda DB)

        // Conn + DB names
        $trxConn = $trxM->getConnectionName() ?: config('database.default');
        $appConn = $appM->getConnectionName() ?: config('database.default');
        $aprConn = $aprM->getConnectionName() ?: config('database.default');

        $trxDb = DB::connection($trxConn)->getDatabaseName();
        $appDb = DB::connection($appConn)->getDatabaseName();
        $aprDb = DB::connection($aprConn)->getDatabaseName();

        // Table names
        $tblTrx = $trxM->getTable();   // ex: view_trx_all
        $tblApp = $appM->getTable();   // ex: v_job_apply_with_posting
        $tblApr = $aprM->getTable();   // ex: t_approval

        // Fully-qualified
        $tblTrxFQ = "`{$trxDb}`.`{$tblTrx}`";
        $tblAppFQ = "`{$appDb}`.`{$tblApp}`";
        $tblAprFQ = "`{$aprDb}`.`{$tblApr}`";

        // --- UNION ALL: kolom harus identik di kedua view ---
        // kalau kamu punya kolom lain, tinggal tambahkan di SELECT keduanya.
        $unionSql = "
            SELECT id, docdate, cpnyid, departementid, infohd, url, docid
            FROM {$tblTrxFQ}
            UNION ALL
            SELECT id, docdate, cpnyid, departementid, infohd, url, docid
            FROM {$tblAppFQ}
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






    // public function analytics()
    // {
    //     $dataFeed = new DataFeed();

    //     return view('pages/dashboard/mastercard', compact('dataFeed'));
    // }

    // public function fintech()
    // {
    //     return view('pages/dashboard/fintech');
    // }

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

    public function Waitingjson_1_db(Request $request)
    {
        $user = request()->user();
        if (!$user) {
            return response()->json(['data' => []], 401);
        }
        $viewTrxAll = ViewTrxAll::get();
        $docIds = $viewTrxAll->pluck('docid')->toArray();
        $trxApproval = T_approval::whereIn('docid', $docIds)
            ->where('aprvusername', 'like', "%" . $user->username . "%")
            ->where('status', 'P')
            ->whereNotNull('aprvdatebefore')
            ->pluck('docid')
            ->toArray();
        $tr_approval = ViewTrxAll::whereIn('docid', $trxApproval)
            ->select('id', 'docdate', 'cpnyid', 'departementid', 'infohd', 'url', 'docid','status')
            ->get();
        return response()->json(['data' => $tr_approval]);
    }

    public function Waitingjson(Request $request)
    {
        $user = request()->user();
        if (!$user) {
            return response()->json(['data' => []], 401);
        }

       // Models
        $trxM = new ViewTrxAll();     // contoh: DB iamsys
        $appM = new ViewJobApply();   // contoh: DB hrdb
        $aprM = new T_approval();     // lokasi t_approval (bisa beda DB)

        // Conn + DB names
        $trxConn = $trxM->getConnectionName() ?: config('database.default');
        $appConn = $appM->getConnectionName() ?: config('database.default');
        $aprConn = $aprM->getConnectionName() ?: config('database.default');

        $trxDb = DB::connection($trxConn)->getDatabaseName();
        $appDb = DB::connection($appConn)->getDatabaseName();
        $aprDb = DB::connection($aprConn)->getDatabaseName();

        // Table names
        $tblTrx = $trxM->getTable();   // ex: view_trx_all
        $tblApp = $appM->getTable();   // ex: v_job_apply_with_posting
        $tblApr = $aprM->getTable();   // ex: t_approval

        // Fully-qualified
        $tblTrxFQ = "`{$trxDb}`.`{$tblTrx}`";
        $tblAppFQ = "`{$appDb}`.`{$tblApp}`";
        $tblAprFQ = "`{$aprDb}`.`{$tblApr}`";

        // --- UNION ALL: kolom harus identik di kedua view ---
        // kalau kamu punya kolom lain, tinggal tambahkan di SELECT keduanya.
        $unionSql = "
            SELECT id, docdate, cpnyid, departementid, infohd, url, docid
            FROM {$tblTrxFQ}
            UNION ALL
            SELECT id, docdate, cpnyid, departementid, infohd, url, docid
            FROM {$tblAppFQ}
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

    public function Approvejson_1_db(Request $request)
    {
        $user = request()->user();
        if (!$user) {
            return response()->json(['data' => []], 401);
        }
        $viewTrxAll = ViewTrxAll::get();
        $docIds = $viewTrxAll->pluck('docid')->toArray();
        $trxApproval = T_approval::whereIn('docid', $docIds)
            ->where('aprvusername', 'like', "%" . $user->username . "%")
            ->where('status', 'A')
            ->pluck('docid')
            ->toArray();
        $tr_approval = ViewTrxAll::whereIn('docid', $trxApproval)
            ->select('id', 'docdate', 'cpnyid', 'departementid', 'infohd', 'url', 'docid','status')
            ->get();
        return response()->json(['data' => $tr_approval]);
    }
    public function Approvejson(Request $request)
    {

        $user = request()->user();
        if (!$user) {
            return response()->json(['data' => []], 401);
        }

       // Models
        $trxM = new ViewTrxAll();     // contoh: DB iamsys
        $appM = new ViewJobApply();   // contoh: DB hrdb
        $aprM = new T_approval();     // lokasi t_approval (bisa beda DB)

        // Conn + DB names
        $trxConn = $trxM->getConnectionName() ?: config('database.default');
        $appConn = $appM->getConnectionName() ?: config('database.default');
        $aprConn = $aprM->getConnectionName() ?: config('database.default');

        $trxDb = DB::connection($trxConn)->getDatabaseName();
        $appDb = DB::connection($appConn)->getDatabaseName();
        $aprDb = DB::connection($aprConn)->getDatabaseName();

        // Table names
        $tblTrx = $trxM->getTable();   // ex: view_trx_all
        $tblApp = $appM->getTable();   // ex: v_job_apply_with_posting
        $tblApr = $aprM->getTable();   // ex: t_approval

        // Fully-qualified
        $tblTrxFQ = "`{$trxDb}`.`{$tblTrx}`";
        $tblAppFQ = "`{$appDb}`.`{$tblApp}`";
        $tblAprFQ = "`{$aprDb}`.`{$tblApr}`";

        // --- UNION ALL: kolom harus identik di kedua view ---
        // kalau kamu punya kolom lain, tinggal tambahkan di SELECT keduanya.
        $unionSql = "
            SELECT id, docdate, cpnyid, departementid, infohd, url, docid
            FROM {$tblTrxFQ}
            UNION ALL
            SELECT id, docdate, cpnyid, departementid, infohd, url, docid
            FROM {$tblAppFQ}
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

    // public function test()
    // {
    //     $company = CompanyPG::get();
    //     dd($company);
    // }

}

