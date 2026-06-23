<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // baru
use App\Models\User;
use App\Models\Usercpny;
use App\Models\Vplreceive;
use App\Models\Vplreceivedetail;
use App\Models\Vplledger;
use App\Models\Site;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\Vpltransfer;
use App\Models\Vpltransferdetail;
use App\Models\Vplrequest;
use App\Models\Vplrequestdetail;
use App\Models\Vpladjustment;
use App\Models\Vpladjustmentdetail;
use App\Models\Msproduct;
use App\Models\Msproductdetail;
use App\Models\Vplperiode;
use App\Models\Viewvplproductmovement;
use App\Models\Viewvplproductmovementwhs;
use App\Models\Viewvpltrialbalancedet;
use App\Models\Viewvpltrialbalancesummary;
use App\Models\Viewvpltrialbalancesummarygroup;
use App\Models\Viewagingtarget;
use App\Models\Viewagingexpired;
use App\Models\Mswhsdept;
use App\Models\Viewproductwarehouseportion;
use DataTables;


class VplledgerController extends Controller
{    
    public function insert_ledger_from_receive($id)
    {
       
        $user = Auth::user();
        $datenow = Carbon::now()->format('Y-m-d');
        $datestamp = Carbon::now()->toDateTimeString();
                    
        $vplreceive = Vplreceive::find($id);
        $postdate = Carbon::parse($vplreceive->completed_at)->format('Y-m-d');
        $postdate2 = Carbon::parse($vplreceive->completed_at);
        $year = $postdate2->year;
        $month = sprintf("%02d", $postdate2->month);  // Menambahkan nol di depan jika bulan di bawah 10
        $perpost = $year.$month;

        $vplreceivedetail = Vplreceivedetail::where('receive_id', $vplreceive->receive_id)          
            ->get();        
         
        $lineNumber = 1;
        foreach ($vplreceivedetail as $detail) {
            // Insert into Vplreceive
            Vplledger::create([
                'refnbr' => $vplreceive->receive_id,
                'refdate'=>$datestamp,
                'cpnyid' => $vplreceive->cpnyid,
                'type' => 'Receive',
                'postdate' => $postdate,
                'perpost' => $perpost,
                'linenbr' => $lineNumber,
                'product_id' => $detail['product_id'],                
                'expired_date' => $detail['expired_date'],
                'whs_id' => $detail['whs_id'],
                'qty' => $detail['qty_receive'],
                'reference_refnbr' => $vplreceive->receive_id,                
                'status' => 'A',
                'created_user' => $vplreceive->user,
                'created_at' => $vplreceive->created_at,
                'updated_user' => $vplreceive->completed_user,
                'updated_at' => $vplreceive->completed_at,
            ]);    
    
            // Increment lineNumber for each new row
            $lineNumber++;
        }           
        
    }  

    public function insert_ledger_from_transfer($id)
    {
       
        $user = Auth::user();
        $datenow = Carbon::now()->format('Y-m-d');
        $datestamp = Carbon::now()->toDateTimeString();
      
        $vpltransfer = Vpltransfer::find($id);
        $postdate = Carbon::parse($vpltransfer->completed_at)->format('Y-m-d');
        $postdate2 = Carbon::parse($vpltransfer->completed_at);
        $year = $postdate2->year;
        $month = sprintf("%02d", $postdate2->month);  // Menambahkan nol di depan jika bulan di bawah 10
        $perpost = $year.$month;

        $vpltransferdetail = Vpltransferdetail::where('transfer_id', $vpltransfer->transfer_id)          
            ->get();        
         
        $lineNumber = 1;
        foreach ($vpltransferdetail as $detail) {
                    
            // Insert for adding quantity (Gudang tujuan)
            Vplledger::create([
                'refnbr' => $vpltransfer->transfer_id,
                'refdate' => $datestamp,
                'cpnyid' => $vpltransfer->cpnyid,
                'type' => $vpltransfer->transfertype,
                'postdate' => $postdate,
                'perpost' => $perpost,
                'linenbr' => $lineNumber,
                'product_id' => $detail['product_id'],
                'expired_date' => $detail['expired_date'],
                'whs_id' => $detail['to_whs_id'], // Gudang tujuan
                'qty' => abs($detail['qty_transfer']), // Positif untuk penambahan
                'reference_refnbr' => $vpltransfer->transfer_id,                
                'status' => 'A',
                'created_user' => $vpltransfer->user,
                'created_at' => $vpltransfer->created_at,
                'updated_user' => $vpltransfer->completed_user,
                'updated_at' => $vpltransfer->completed_at,
            ]);
        
            // Increment lineNumber for each new row
            $lineNumber++;

            // Insert for reducing quantity (Gudang asal)
            Vplledger::create([
                'refnbr' => $vpltransfer->transfer_id,
                'refdate' => $datestamp,
                'cpnyid' => $vpltransfer->cpnyid,
                'type' => $vpltransfer->transfertype,
                'postdate' => $postdate,
                'perpost' => $perpost,
                'linenbr' => $lineNumber,
                'product_id' => $detail['product_id'],
                'expired_date' => $detail['expired_date'],
                'whs_id' => $detail['from_whs_id'], // Gudang asal
                'qty' => -abs($detail['qty_transfer']), // Negatif untuk pengurangan
                'reference_refnbr' => $detail['ref_transfer_id'],    
                'status' => 'A',
                'created_user' => $vpltransfer->user,
                'created_at' => $vpltransfer->created_at,
                'updated_user' => $vpltransfer->completed_user,
                'updated_at' => $vpltransfer->completed_at,
            ]);
        
            // Increment lineNumber for the second entry
            $lineNumber++;
        }    
        
    }  
    public function insert_ledger_from_request($id)
    {
       
        $user = Auth::user();
        $datenow = Carbon::now()->format('Y-m-d');
        $datestamp = Carbon::now()->toDateTimeString();
       
        $vplrequest = Vplrequest::find($id);
        $count_vplrequestdetail = Vplrequestdetail::where('request_id', $vplrequest->request_id)  
            ->where('purpose_id','<>','Redeem PG Card')
            ->count();


        if ($count_vplrequestdetail == 0 && $vplrequest->department == 'CUSTOMERSERVICE'){
            $postdate = Carbon::parse($vplrequest->request_date);           
            $year = $postdate->year;
            $month = sprintf("%02d", $postdate->month);  // Menambahkan nol di depan jika bulan di bawah 10
            $perpost = $year.$month;
        }else{
            $postdate = Carbon::parse($vplrequest->completed_at)->format('Y-m-d');
            $postdate2 = Carbon::parse($vplrequest->completed_at);
            $year = $postdate2->year;
            $month = sprintf("%02d", $postdate2->month);  // Menambahkan nol di depan jika bulan di bawah 10
            $perpost = $year.$month;

        }
        

        $vplrequestdetail = Vplrequestdetail::where('request_id', $vplrequest->request_id)          
            ->get();        
         
        $lineNumber = 1;
        foreach ($vplrequestdetail as $detail) {
            if($vplrequest->requesttype == 'Usage'){
                
                Vplledger::create([
                    'refnbr' => $vplrequest->request_id,
                    'refdate' => $datestamp,
                    'cpnyid' => $vplrequest->cpnyid,
                    'type' => $vplrequest->requesttype,
                    'postdate' => $postdate,
                    'perpost' => $perpost,
                    'linenbr' => $lineNumber,
                    'product_id' => $detail['product_id'],
                    'expired_date' => $detail['expired_date'],
                    'whs_id' => $detail['whs_id'], // Gudang tujuan
                    'qty' => -abs($detail['qty_request']), // Positif untuk penambahan
                    'reference_refnbr' => $vplrequest->request_id,
                    'purpose_id' => $detail['purpose_id'],
                    'status' => 'A',
                    'created_user' => $vplrequest->user,
                    'created_at' => $vplrequest->created_at,
                    'updated_user' => $vplrequest->completed_user,
                    'updated_at' => $vplrequest->completed_at,
                ]);
            
                // Increment lineNumber for each new row
                $lineNumber++;

            }else{

                // Insert for adding quantity (Gudang tujuan)
                Vplledger::create([
                    'refnbr' => $vplrequest->request_id,
                    'refdate' => $datestamp,
                    'cpnyid' => $vplrequest->cpnyid,
                    'type' => $vplrequest->requesttype,
                    'postdate' => $postdate,
                    'perpost' => $perpost,
                    'linenbr' => $lineNumber,
                    'product_id' => $detail['product_id'],
                    'expired_date' => $detail['expired_date'],
                    'whs_id' => $detail['whs_id'], // Gudang tujuan
                    'qty' => abs($detail['qty_request']), // Positif untuk penambahan
                    'reference_refnbr' => $vplrequest->ref_request_id,
                    'purpose_id' => $detail['purpose_id'],
                    'status' => 'A',
                    'created_user' => $vplrequest->user,
                    'created_at' => $vplrequest->created_at,
                    'updated_user' => $vplrequest->completed_user,
                    'updated_at' => $vplrequest->completed_at,
                ]);
            
                // Increment lineNumber for each new row
                $lineNumber++;


            }         
           
        }    
        
    }  
    
    public function insert_ledger_from_adjustment($id)
    {
       
        $user = Auth::user();
        $datenow = Carbon::now()->format('Y-m-d');
        $datestamp = Carbon::now()->toDateTimeString();
        $dt = Carbon::now();
        $year = $dt->year;
        $month = sprintf("%02d", $dt->month);  // Menambahkan nol di depan jika bulan di bawah 10
        $perpost = $year.$month;
        $adjustmenttype ='Adjustment';

        $vpladjustment = Vpladjustment::find($id);
        $postdate = Carbon::parse($vpladjustment->completed_at)->format('Y-m-d');

        $vpladjustmentdetail = Vpladjustmentdetail::where('adjustment_id', $vpladjustment->adjustment_id)          
            ->get();        
         
        $lineNumber = 1;
        foreach ($vpladjustmentdetail as $detail) {
                          
            Vplledger::create([
                'refnbr' => $vpladjustment->adjustment_id,
                'refdate' => $datestamp,
                'cpnyid' => $vpladjustment->cpnyid,
                'type' => $adjustmenttype,
                'postdate' => $postdate,
                'perpost' => $perpost,
                'linenbr' => $lineNumber,
                'product_id' => $detail['product_id'],
                'expired_date' => $detail['expired_date'],
                'whs_id' => $detail['whs_id'],
                'qty' => $detail['qty_adjustment'], 
                'reference_refnbr' => $vpladjustment->adjustment_id,    
                'purpose_id' => $adjustmenttype,            
                'status' => 'A',
                'created_user' => $vpladjustment->user,
                'created_at' => $vpladjustment->created_at,
                'updated_user' => $vpladjustment->completed_user,
                'updated_at' => $vpladjustment->completed_at,
            ]);
        
            // Increment lineNumber for each new row
            $lineNumber++;
            
        }    
        
    }  

    public function vplledgerall(Request $request)
    {     
           
        $tittle = 'Ledger All';   
        $user = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $multidept = explode(',', $user->departmentid);

        if ($request->ajax()) {           
             
            $data = Vplledger::leftjoin('vpl_ms_product', 'vpl_trx_ledger.product_id', '=', 'vpl_ms_product.product_id')                                      
                    ->select('vpl_trx_ledger.*', 'vpl_ms_product.product_name')                                       
                    ->get();       
                       
            return Datatables::of($data)   
                                                   
                ->make(true);
        }
        return view('vplledger.vplledgerall', compact('tittle'));
    }

    public function msproduct_rpt(Request $request)
    {     
           
        $tittle = 'Report Voucher & Product';   
        $user = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $multidept = explode(',', $user->departmentid);

        if ($request->ajax()) {           
            
            if ($user->role == 'admin') { 
                $data = Msproductdetail::leftjoin('vpl_ms_product', 'vpl_ms_product_detail.product_id', '=', 'vpl_ms_product.product_id')                                      
                    ->select('vpl_ms_product_detail.*', 'vpl_ms_product.*')                                       
                    ->get();       
            }else{
                $data = Msproductdetail::leftjoin('vpl_ms_product', 'vpl_ms_product_detail.product_id', '=', 'vpl_ms_product.product_id')                                      
                    ->select('vpl_ms_product_detail.*', 'vpl_ms_product.*')        
                    ->whereIn('vpl_ms_product.cpnyid', $multicpnyid)                                                   
                    ->get(); 
            }           
            return Datatables::of($data)
                ->editColumn('expired_date', function($row) {
                    return \Carbon\Carbon::parse($row->expired_date)->format('Y-m-d') == '1900-01-01' ? 'No Expired' : $row->expired_date;
                })
                ->make(true);
        }
        return view('vplledger.msproduct_rpt', compact('tittle'));
    }

    public function inoutproduct_rpt(Request $request)
    {
        $tittle = 'Report In Out Product';
        $user = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $multidept = explode(',', $user->departmentid);
    
        if ($request->ajax()) {
            // Query data dengan join
            if ($user->role == 'admin') {
                $query = Vplledger::leftJoin('vpl_ms_product', 'vpl_trx_ledger.product_id', '=', 'vpl_ms_product.product_id')
                    ->select('vpl_trx_ledger.*', 'vpl_ms_product.product_name')
                    ->where('vpl_trx_ledger.status', '<>', 'X')
                    ->get();
            }else{
                $query = Vplledger::leftJoin('vpl_ms_product', 'vpl_trx_ledger.product_id', '=', 'vpl_ms_product.product_id')
                    ->select('vpl_trx_ledger.*', 'vpl_ms_product.product_name')
                    ->whereIn('vpl_trx_ledger.cpnyid', $multicpnyid)
                    ->where('vpl_trx_ledger.status', '<>', 'X')
                    ->get();
            }    
    
            return Datatables::of($query)
                ->editColumn('expired_date', function($row) {
                    return \Carbon\Carbon::parse($row->expired_date)->format('Y-m-d') === '1900-01-01'
                        ? 'No Expired'
                        : \Carbon\Carbon::parse($row->expired_date)->format('Y-m-d');
                })
                ->make(true);
        }
    
        return view('vplledger.inoutproduct_rpt', compact('tittle'));
    }

    public function rekapvoucher(Request $request)
    {
        $tittle = 'Rekap Stock';
        $user = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $company = Vplperiode::distinct()->get(['cpnyid']); 
        $perpost = Vplperiode::distinct()->get(['perpost_year', 'perpost_month']); 
        
        if ($request->ajax()) {
            // Ambil data dari tabel dengan filter jika ada
            $query = Viewvplproductmovement::query();

            if ($user->role !== 'admin') {
                $query->whereIn('cpnyid', $multicpnyid);
            }

            if ($request->has('cpnyid') && !empty($request->cpnyid)) {
                $query->where('cpnyid', $request->cpnyid);
            }

            if ($request->has('perpost') && !empty($request->perpost)) {
                $query->where('perpost', $request->perpost);
            }

            return Datatables::of($query)->make(true);
        }

        return view('vplledger.rekapvoucher', compact('tittle', 'company', 'perpost'));
    }

    public function stocktrialbalance(Request $request)
    {
        $tittle = 'Stock Trial Balance';
        $user = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $company = Vplperiode::distinct()->get(['cpnyid']); 
        $perpost = Vplperiode::distinct()->get(['perpost_year', 'perpost_month']); 
        
        if ($request->ajax()) {
            // Ambil data dari tabel dengan filter jika ada
            $query = Viewvplproductmovementwhs::query();

            if ($user->role !== 'admin') {
                $query->whereIn('cpnyid', $multicpnyid);
            }

            if ($request->has('cpnyid') && !empty($request->cpnyid)) {
                $query->where('cpnyid', $request->cpnyid);
            }

            if ($request->has('perpost') && !empty($request->perpost)) {
                $query->where('perpost', $request->perpost);
            }

            return Datatables::of($query)->make(true);
        }

        return view('vplledger.stocktrialbalance', compact('tittle', 'company', 'perpost'));
    }

    public function posting_periode(Request $request)
    {
        $tittle = 'Posting Periode';
        $user = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $company = Vplperiode::distinct()->get(['cpnyid']); 
        $year = Vplperiode::distinct()->get(['perpost_year']); 
        $month = Vplperiode::distinct()->get(['perpost_month']);
    
        if ($request->ajax()) {
            // Query dengan filter
            $query = Vplperiode::select('cpnyid', 'perpost_year', 'perpost_month', 'status', 'id');
           
            if ($user->role !== 'admin') {
                $query->whereIn('cpnyid', $multicpnyid);
            }

            if (!empty($request->cpnyid)) {
                $query->where('cpnyid', $request->cpnyid);
            }

            if (!empty($request->year)) {
                $query->where('perpost_year', $request->year);
            }

            if (!empty($request->month)) {
                $query->where('perpost_month', $request->month);
            }

            $query->orderBy('cpnyid', 'asc')->orderBy('perpost_year', 'asc')->orderBy('perpost_month', 'asc');

            return Datatables::of($query)
                ->addColumn('statusx', function ($row) {                                     
                    return $row->status == 'A' 
                        ? '<a href="javascript:void(0)" class="label label-success">Active</a>' 
                        : '<a href="javascript:void(0)" class="label label-danger">Inactive</a>';
                })
                ->addColumn('action', function ($row) {   
                    return '<a href="javascript:void(0)" data-toggle="tooltip" data-id="'.$row->id.'" data-original-title="Process" class="edit btn btn-sm editProcess btn-primary text-white">Process</a>';
                })
                ->rawColumns(['statusx', 'action'])
                ->make(true);
        }

        return view('vplledger.posting_periode', compact('tittle', 'company', 'year','month'));
    }

    public function getLatestActiveMonth()
    {
        $user = Auth::user();
        $multicpnyid = explode(',', $user->companyid);

        $latestActive = Vplperiode::where('status', 'A')
                        ->whereIn('cpnyid', $multicpnyid)
                        ->orderBy('perpost_year', 'desc')
                        ->orderBy('perpost_month', 'desc')
                        ->get(); // Ambil semua data dengan status 'A'
        // dd($latestActive);
        if ($latestActive->isNotEmpty()) {
            $previousMonths = [];

            foreach ($latestActive as $record) {
                $previousMonth = Carbon::createFromDate($record->perpost_year, $record->perpost_month, 1)
                                        ->subMonth(); // Kurangi 1 bulan
                
                $previousMonths[] = [
                    'cpnyid' => $record->cpnyid,
                    'year' => $previousMonth->year,
                    'month' => str_pad($previousMonth->month, 2, "0", STR_PAD_LEFT)
                ];
            }

            return response()->json($previousMonths);
        }

        return response()->json(['error' => 'No active month found'], 404);
    }



    public function postingProcess(Request $request)
    {
        // dd($request->all());
        try {
            $id = $request->id;
            $vplperiode = Vplperiode::find($id);
            
            $cpnyid = $vplperiode->cpnyid;
            $perpost = $vplperiode->perpost_year.$vplperiode->perpost_month;
            $nextMonth = Carbon::createFromFormat('Ym', $perpost)->addMonth()->format('Ym');
           
            DB::beginTransaction();
            
            // Memanggil Stored Procedure
            DB::statement("CALL InsertProductBalance(?, ?)", [$cpnyid, $perpost]);
            DB::statement("CALL UpdateProductBalance(?, ?)", [$cpnyid, $perpost]);

            DB::commit();

            // Update status periode yang diproses menjadi 'N'
            $vplperiode->status = 'N';
            $vplperiode->save();

            // Update status periode bulan berikutnya menjadi 'A'
            Vplperiode::where('cpnyid', $cpnyid)
                ->whereRaw("CONCAT(perpost_year, LPAD(perpost_month, 2, '0')) = ?", [$nextMonth])
                ->update(['status' => 'A']);

            return response()->json(['success' => 'Proses Posting berhasil dijalankan!']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Gagal memproses: ' . $e->getMessage()], 500);
        }
    }

    public function warehouse_portion(Request $request)
    {     
           
        $tittle = 'Report Warehouse Portion';   
        $user = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $multidept = explode(',', $user->departmentid);

        if ($request->ajax()) {           
            
            if ($user->role == 'admin') { 
                $data = Viewproductwarehouseportion::get();       
            }else{
                $data = Viewproductwarehouseportion::whereIn('cpnyid', $multicpnyid)                                                   
                    ->get(); 
            }           

            return Datatables::of($data)
                ->editColumn('expired_date', function($row) {
                    return \Carbon\Carbon::parse($row->expired_date)->format('Y-m-d') === '1900-01-01'
                        ? 'No Expired'
                        : \Carbon\Carbon::parse($row->expired_date)->format('Y-m-d');
                })
                ->make(true);          
        }
        return view('vplledger.warehouse_portion', compact('tittle'));
    }

    public function trialBalancedetail(Request $request)
    {
        $tittle = 'Trial Balance Detail';
        $user = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $company = Vplperiode::distinct()->get(['cpnyid']); 
        $perpost = Vplperiode::distinct()->get(['perpost_year', 'perpost_month']); 
        $warehouse = Mswhsdept::get(['whs_id']);
              
        if ($request->ajax()) {
            // Ambil data dari tabel dengan filter jika ada
            $query = Viewvpltrialbalancedet::query()
                ->orderBy('perpost', 'asc')
                ->orderBy('cpnyid', 'asc')
                ->orderBy('product_id', 'asc')
                ->orderBy('expired_date', 'asc')
                ->orderBy('whs_id', 'asc')
                ->orderBy('postdate', 'asc');

            if ($user->role !== 'admin') {
                $query->whereIn('cpnyid', $multicpnyid);
            }

            if ($request->has('cpnyid') && !empty($request->cpnyid)) {
                $query->where('cpnyid', $request->cpnyid);
            }

            if ($request->has('perpost') && !empty($request->perpost)) {
                $query->where('perpost', $request->perpost);
            }

            if ($request->has('whs_id') && !empty($request->whs_id)) {
                $query->where('whs_id', $request->whs_id);
            }

            return Datatables::of($query)
                ->editColumn('expired_date', function($row) {
                    return \Carbon\Carbon::parse($row->expired_date)->format('Y-m-d') === '1900-01-01'
                        ? 'No Expired'
                        : \Carbon\Carbon::parse($row->expired_date)->format('Y-m-d');
                })
                ->make(true);
            
        }

        return view('vplledger.trialbalancedetail', compact('tittle', 'company', 'perpost','warehouse'));
    }

    public function trialBalancesummary(Request $request)
    {
        $tittle = 'Trial Balance Summary';
        $user = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $company = Vplperiode::distinct()->get(['cpnyid']); 
        $perpost = Vplperiode::distinct()->get(['perpost_year', 'perpost_month']); 
        $warehouse = Mswhsdept::get(['whs_id']);
              
        if ($request->ajax()) {
            // Ambil data dari tabel dengan filter jika ada
            $query = Viewvpltrialbalancesummary::query()
                ->orderBy('perpost', 'asc')
                ->orderBy('cpnyid', 'asc')
                ->orderBy('product_id', 'asc')
                ->orderBy('expired_date', 'asc')
                ->orderBy('whs_id', 'asc');

            if ($user->role !== 'admin') {
                $query->whereIn('cpnyid', $multicpnyid);
            }

            if ($request->has('cpnyid') && !empty($request->cpnyid)) {
                $query->where('cpnyid', $request->cpnyid);
            }

            if ($request->has('perpost') && !empty($request->perpost)) {
                $query->where('perpost', $request->perpost);
            }

            if ($request->has('whs_id') && !empty($request->whs_id)) {
                $query->where('whs_id', $request->whs_id);
            }

            return Datatables::of($query)
                ->editColumn('expired_date', function($row) {
                    return \Carbon\Carbon::parse($row->expired_date)->format('Y-m-d') === '1900-01-01'
                        ? 'No Expired'
                        : \Carbon\Carbon::parse($row->expired_date)->format('Y-m-d');
                })
                ->make(true);          

        }

        return view('vplledger.trialbalancesummary', compact('tittle', 'company', 'perpost','warehouse'));
    }

    
    public function trialBalancesummarygroup(Request $request)
    {
        $tittle = 'Trial Balance Summary Group';
        $user = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $company = Vplperiode::distinct()->get(['cpnyid']); 
        $perpost = Vplperiode::distinct()->get(['perpost_year', 'perpost_month']); 
        // $warehouse = Mswhsdept::get(['whs_owner']);
              
        if ($request->ajax()) {
            // Ambil data dari tabel dengan filter jika ada
            $query = Viewvpltrialbalancesummarygroup::query()
            ->orderBy('perpost', 'asc')
            ->orderBy('cpnyid', 'asc')
            ->orderBy('product_id', 'asc')
            ->orderBy('expired_date', 'asc')
            ->orderBy('whs_owner', 'asc');

            if ($user->role !== 'admin') {
                $query->whereIn('cpnyid', $multicpnyid);
            }

            if ($request->has('cpnyid') && !empty($request->cpnyid)) {
                $query->where('cpnyid', $request->cpnyid);
            }

            if ($request->has('perpost') && !empty($request->perpost)) {
                $query->where('perpost', $request->perpost);
            }

            // if ($request->has('whs_owner') && !empty($request->whs_owner)) {
            //     $query->where('whs_owner', $request->whs_owner);
            // }
            
            return Datatables::of($query)
                ->editColumn('expired_date', function($row) {
                    return \Carbon\Carbon::parse($row->expired_date)->format('Y-m-d') === '1900-01-01'
                        ? 'No Expired'
                        : \Carbon\Carbon::parse($row->expired_date)->format('Y-m-d');
                })
                ->make(true);          

        }

        return view('vplledger.trialbalancesummarygroup', compact('tittle', 'company', 'perpost'));
    }

    public function agingtargetdatedate(Request $request)
    {
        $tittle = 'Aging Target Date';
        $user = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $company = Vplperiode::distinct()->get(['cpnyid']);         
        $warehouse = Mswhsdept::get(['whs_id']);
              
        if ($request->ajax()) {
            // Ambil data dari tabel dengan filter jika ada
            $query = Viewagingtarget::query();

            if ($user->role !== 'admin') {
                $query->whereIn('cpnyid', $multicpnyid);
            }

            if ($request->has('cpnyid') && !empty($request->cpnyid)) {
                $query->where('cpnyid', $request->cpnyid);
            }
           
            if ($request->has('whs_id') && !empty($request->whs_id)) {
                $query->where('whs_id', $request->whs_id);
            }

            return Datatables::of($query)
                ->editColumn('expired_date', function($row) {
                    return \Carbon\Carbon::parse($row->expired_date)->format('Y-m-d') === '1900-01-01'
                        ? 'No Expired'
                        : \Carbon\Carbon::parse($row->expired_date)->format('Y-m-d');
                })
                ->make(true);

        }

        return view('vplledger.agingtarget', compact('tittle', 'company', 'warehouse'));
    }

    public function agingexpireddatedate(Request $request)
    {
        $tittle = 'Aging Expired Date';
        $user = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $company = Vplperiode::distinct()->get(['cpnyid']);         
        $warehouse = Mswhsdept::get(['whs_id']);
              
        if ($request->ajax()) {
            // Ambil data dari tabel dengan filter jika ada
            $query = Viewagingexpired::query();

            if ($user->role !== 'admin') {
                $query->whereIn('cpnyid', $multicpnyid);
            }

            if ($request->has('cpnyid') && !empty($request->cpnyid)) {
                $query->where('cpnyid', $request->cpnyid);
            }
           
            if ($request->has('whs_id') && !empty($request->whs_id)) {
                $query->where('whs_id', $request->whs_id);
            }

            return Datatables::of($query)
                ->editColumn('expired_date', function($row) {
                    return \Carbon\Carbon::parse($row->expired_date)->format('Y-m-d') === '1900-01-01'
                        ? 'No Expired'
                        : \Carbon\Carbon::parse($row->expired_date)->format('Y-m-d');
                })
                ->make(true);

        }

        return view('vplledger.agingexpired', compact('tittle', 'company', 'warehouse'));
    }

    public function getWarehouseByCompany($cpnyid)
    {
        $warehouses = Mswhsdept::where('cpnyid', $cpnyid)->get(['whs_id']);
        return response()->json($warehouses);
    }

  
}
