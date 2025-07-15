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

class DashboardController extends Controller
{
    public function index()
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


    public function analytics()
    {
        $dataFeed = new DataFeed();

        return view('pages/dashboard/mastercard', compact('dataFeed'));
    }

    public function fintech()
    {
        return view('pages/dashboard/fintech');
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

    public function Waitingjson(Request $request)
    {     
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
            ->select('id', 'docdate', 'cpnyid', 'departementid', 'infohd', 'url', 'docid','status')
            ->get();

        return response()->json(['data' => $tr_approval]);
    }

    public function Approvejson(Request $request)
    {   
        
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
            ->where('status', 'A')
            // ->whereNotNull('aprvdatebefore')
            ->pluck('docid')
            ->toArray(); // Ambil hanya docid yang memenuhi kondisi

        // Step 3: Ambil data yang cocok dari ViewTrxAll berdasarkan hasil query T_approval
        $tr_approval = ViewTrxAll::whereIn('docid', $trxApproval)
            ->select('id', 'docdate', 'cpnyid', 'departementid', 'infohd', 'url', 'docid','status')
            ->get();

        return response()->json(['data' => $tr_approval]);
    }

    public function test()
    {
        $company = CompanyPG::get();
        dd($company);
    }

}

