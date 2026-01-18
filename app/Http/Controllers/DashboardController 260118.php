<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DataFeed;
use App\Models\ProjectTask;
use App\Models\TrApproval;
use App\Models\Viewtrxall;
use App\Models\Agenda;
use Illuminate\Support\Carbon;
use App\Models\News;
use App\Models\Users_talenta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use App\Models\MsCompany;
use App\Models\ViewJobApply;
use App\Models\ViewtrPurch;
use App\Models\ViewDasAll;
use Illuminate\Support\Facades\Log;
use Vinkla\Hashids\Facades\Hashids;
use App\Models\Autonbr;
use Illuminate\Support\Facades\Schema;



class DashboardController extends Controller
{
    
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

        $doctypes = Autonbr::on('pgsql2')
            ->where('status', 'A')
            ->select('doctype')
            ->distinct()
            ->orderBy('doctype')
            ->pluck('doctype')
            ->values()
            ->all();

        return view('pages.dashboard.waitingapproval', compact('user', 'doctypes'));
    }


    // DashboardController.php (potong ganti 3 method ini)

    public function index()
    {
        $dataFeed = new DataFeed();
        $user = request()->user();
        if (!$user) return redirect()->route('login');

        // Models
        $trxM   = new ViewTrxAll();   // iamsys (server A)
        $dasM   = new ViewDasAll();   // das_voucher (server A)  <-- TAMBAHAN
        $appM   = new ViewJobApply(); // jobportal (server A/B)
        $aprM   = new TrApproval();   // das_voucher (server A dengan ViewTrxAll)
        $purchM = new ViewtrPurch();  // purchasing (server B)

        // koneksi & table
        $trxConn   = $trxM->getConnectionName()   ?: config('database.default');
        $dasConn   = $dasM->getConnectionName()   ?: config('database.default'); // <-- TAMBAHAN
        $appConn   = $appM->getConnectionName()   ?: config('database.default');
        $aprConn   = $aprM->getConnectionName()   ?: config('database.default');
        $purchConn = $purchM->getConnectionName() ?: config('database.default');

        $tblTrx   = $trxM->getTable();
        $tblDas   = $dasM->getTable();   // <-- TAMBAHAN
        $tblApp   = $appM->getTable();
        $tblApr   = $aprM->getTable();
        $tblPurch = $purchM->getTable();

        // 1) ambil DOCID pending
        $docids = DB::connection($aprConn)->table($tblApr)
            ->where('aprv_username', 'ilike', "%{$user->username}%")
            ->where('status', 'P')
            ->whereNotNull('aprv_datebefore')
            ->pluck('refnbr')->unique()->values();

        if ($docids->isEmpty()) {
            $tr_approval = collect();
        } else {
            $selectCols = ['id','docdate','cpnyid','departementid','infohd','url','docid'];

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

            // 2) tarik dari masing-masing sumber
            $rowsTrx = $fetchByDocids($trxConn, $tblTrx);
            $rowsDas = $fetchByDocids($dasConn, $tblDas);   // <-- TAMBAHAN
            $rowsApp = $fetchByDocids($appConn, $tblApp);

            try {
                $rowsPurch = $fetchByDocids($purchConn, $tblPurch);
            } catch (\Throwable $e) {
                Log::warning('Fetch purchasing failed', ['err' => $e->getMessage()]);
                $rowsPurch = collect();
            }

            // 3) merge
            $tr_approval = $rowsTrx
                ->concat($rowsDas)   // <-- TAMBAHAN
                ->concat($rowsApp)
                ->concat($rowsPurch)
                ->values();

            Log::info('Dashboard approvals', [
                'user'          => $user->username,
                'docids_count'  => $docids->count(),
                'docids_sample' => $docids->take(5)->values(),
                'rows_trx'      => $rowsTrx->count(),
                'rows_das'      => $rowsDas->count(),   // <-- TAMBAHAN
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

        $doctypes = Autonbr::query()
            ->select('doctype')
            ->distinct()
            ->where('status', 'A')      // optional
            ->orderBy('doctype')
            ->pluck('doctype')
            ->values();


        return view('pages/dashboard/dashboard', compact('dataFeed','tr_approval','agendas','news', 'doctypes'));
    }


    public function Waitingjson(Request $request)
    {
        $user = request()->user();
        if (!$user) return response()->json(['data' => []], 401);

        $doctype = strtoupper(trim((string) $request->get('doctype', '')));
        $doctype = $doctype === 'ALL' ? '' : $doctype;


        $trxM   = new ViewTrxAll();
        // $dasM   = new ViewDasAll();  // <-- TAMBAHAN
        $appM   = new ViewJobApply();
        $aprM   = new TrApproval();
        $purchM = new ViewtrPurch();

        $trxConn   = $trxM->getConnectionName()   ?: config('database.default');
        // $dasConn   = $dasM->getConnectionName()   ?: config('database.default'); // <-- TAMBAHAN
        $appConn   = $appM->getConnectionName()   ?: config('database.default');
        $aprConn   = $aprM->getConnectionName()   ?: config('database.default');
        $purchConn = $purchM->getConnectionName() ?: config('database.default');

        $tblTrx   = $trxM->getTable();
        // $tblDas   = $dasM->getTable();  // <-- TAMBAHAN
        $tblApp   = $appM->getTable();
        $tblApr   = $aprM->getTable();
        $tblPurch = $purchM->getTable();

        $docids = DB::connection($aprConn)->table($tblApr)
            ->where('aprv_username', 'ilike', "%{$user->username}%")
            ->where('status', 'P')
            ->whereNotNull('aprv_datebefore')
            ->pluck('refnbr')->unique()->values();

        if ($docids->isEmpty()) return response()->json(['data' => []]);

        $selectCols = ['id','docdate','cpnyid','departementid','infohd','url','docid'];
        $fetch = function(string $conn, string $table) use ($docids, $selectCols, $doctype) {
            $out = collect();

            foreach ($docids->chunk(500) as $chunk) {
                $q = DB::connection($conn)->table($table)
                    ->whereIn('docid', $chunk->all())
                    ->select($selectCols);

                if ($doctype !== '') {
                    try {
                        if (Schema::connection($conn)->hasColumn($table, 'doctype')) {

                            $driver = DB::connection($conn)->getDriverName();

                            if ($driver === 'mysql') {
                                $q->whereRaw(
                                    "doctype COLLATE utf8mb4_unicode_ci = ?",
                                    [$doctype]
                                );
                            } elseif ($driver === 'pgsql') {
                                // PostgreSQL aman TANPA collate
                                $q->where('doctype', $doctype);
                            }
                        }
                    } catch (\Throwable $e) {
                        Log::warning('Doctype filter skipped', [
                            'conn'   => $conn,
                            'table'  => $table,
                            'driver' => DB::connection($conn)->getDriverName(),
                            'err'    => $e->getMessage(),
                        ]);
                    }
                }


                $out = $out->concat($q->get());
            }

            return $out;
        };


        // $fetch = function(string $conn, string $table) use ($docids, $selectCols) {
        //     $out = collect();
        //     foreach ($docids->chunk(500) as $chunk) {
        //         $out = $out->concat(
        //             DB::connection($conn)->table($table)
        //             ->whereIn('docid', $chunk->all())
        //             ->select($selectCols)
        //             ->get()
        //         );
        //     }
        //     return $out;
        // };

                Log::info('Waitingjson called', [
            'user' => $user->username ?? null,
            'doctype_param' => $request->get('doctype'),
            'doctype_normalized' => $doctype,
            'all_query' => $request->all(),
        ]);


        $data = collect();
        $data = $data->concat($fetch($trxConn, $tblTrx));
        // $data = $data->concat($fetch($dasConn, $tblDas)); // <-- TAMBAHAN
        $data = $data->concat($fetch($appConn, $tblApp));

        try { $data = $data->concat($fetch($purchConn, $tblPurch)); }
        catch (\Throwable $e) { Log::warning('Waitingjson: purchasing fetch failed', ['err'=>$e->getMessage()]); }

        $data = $data->map(function ($r) {
            return [
                'hid'          => Hashids::encode($r->id),
                'docid'        => $r->docid,
                'docdate'      => $r->docdate,
                'cpnyid'       => $r->cpnyid,
                'departementid'=> $r->departementid,
                'infohd'       => $r->infohd,
                'url'          => $r->url,
            ];
        });

        return response()->json(['data' => $data->values()]);
    }


    public function Approvejson(Request $request)
    {
        $user = request()->user();
        if (!$user) return response()->json(['data' => []], 401);

        $doctype = strtoupper(trim((string) $request->get('doctype', '')));
        $doctype = $doctype === 'ALL' ? '' : $doctype;


        $trxM   = new ViewTrxAll();
        // $dasM   = new ViewDasAll();  // <-- TAMBAHAN
        $appM   = new ViewJobApply();
        $aprM   = new TrApproval();
        $purchM = new ViewtrPurch();

        $trxConn   = $trxM->getConnectionName()   ?: config('database.default');
        // $dasConn   = $dasM->getConnectionName()   ?: config('database.default'); // <-- TAMBAHAN
        $appConn   = $appM->getConnectionName()   ?: config('database.default');
        $aprConn   = $aprM->getConnectionName()   ?: config('database.default');
        $purchConn = $purchM->getConnectionName() ?: config('database.default');

        $tblTrx   = $trxM->getTable();
        // $tblDas   = $dasM->getTable();  // <-- TAMBAHAN
        $tblApp   = $appM->getTable();
        $tblApr   = $aprM->getTable();
        $tblPurch = $purchM->getTable();

        $docids = DB::connection($aprConn)->table($tblApr)
            ->where('aprv_username', 'ilike', "%{$user->username}%")
            ->where('status', 'A')
            ->whereNotNull('aprv_datebefore')
            ->pluck('refnbr')->unique()->values();

        if ($docids->isEmpty()) return response()->json(['data' => []]);

        $selectCols = ['id','docdate','cpnyid','departementid','infohd','url','docid'];
        $fetch = function(string $conn, string $table) use ($docids, $selectCols, $doctype) {
            $out = collect();

            foreach ($docids->chunk(500) as $chunk) {
                $q = DB::connection($conn)->table($table)
                    ->whereIn('docid', $chunk->all())
                    ->select($selectCols);

                if ($doctype !== '') {
                    try {
                        if (Schema::connection($conn)->hasColumn($table, 'doctype')) {

                            $driver = DB::connection($conn)->getDriverName();

                            if ($driver === 'mysql') {
                                $q->whereRaw(
                                    "doctype COLLATE utf8mb4_unicode_ci = ?",
                                    [$doctype]
                                );
                            } elseif ($driver === 'pgsql') {
                                // PostgreSQL aman TANPA collate
                                $q->where('doctype', $doctype);
                            }
                        }
                    } catch (\Throwable $e) {
                        Log::warning('Doctype filter skipped', [
                            'conn'   => $conn,
                            'table'  => $table,
                            'driver' => DB::connection($conn)->getDriverName(),
                            'err'    => $e->getMessage(),
                        ]);
                    }
                }


                $out = $out->concat($q->get());
            }

            return $out;
        };
        // $fetch = function(string $conn, string $table) use ($docids, $selectCols) {
        //     $out = collect();
        //     foreach ($docids->chunk(500) as $chunk) {
        //         $out = $out->concat(
        //             DB::connection($conn)->table($table)
        //             ->whereIn('docid', $chunk->all())
        //             ->select($selectCols)
        //             ->get()
        //         );
        //     }
        //     return $out;
        // };

        $data = collect();
        $data = $data->concat($fetch($trxConn, $tblTrx));
        // $data = $data->concat($fetch($dasConn, $tblDas)); // <-- TAMBAHAN
        $data = $data->concat($fetch($appConn, $tblApp));

        try { $data = $data->concat($fetch($purchConn, $tblPurch)); }
        catch (\Throwable $e) { Log::warning('Approvejson: purchasing fetch failed', ['err'=>$e->getMessage()]); }

        $data = $data->map(function ($r) {
            return [
                'hid'          => Hashids::encode($r->id),
                'docid'        => $r->docid,
                'docdate'      => $r->docdate,
                'cpnyid'       => $r->cpnyid,
                'departementid'=> $r->departementid,
                'infohd'       => $r->infohd,
                'url'          => $r->url,
            ];
        });

        return response()->json(['data' => $data->values()]);
    }



  

}

