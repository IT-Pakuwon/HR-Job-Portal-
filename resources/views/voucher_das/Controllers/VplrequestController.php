<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // baru
use App\Models\M_approval;
use App\Models\T_approval;
use App\Models\Dept;
use App\Models\Location;
use App\Models\Autonbr;
use App\Models\Category;
use App\Models\Attachment;
use App\Models\Company;
use App\Models\T_Message;
use App\Models\User;
use App\Models\Usercpny;
use App\Models\Vplrequest;
use App\Models\Vplrequestdetail;
use App\Models\Vplrequestdetailtemp;
use App\Models\Site;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use DataTables;

use PDF;
use Mail;
use App\Mail\NotifyMail;
use App\Models\Msproduct;
use App\Models\Msproductdetail;
use App\Models\Mswhsdept;
use App\Models\Userdept;
use App\Models\Mswhsusage;

class VplrequestController extends Controller
{

    public function add_vplrequest()
    {
        //add trx_voucher        
        $user = Auth::user();
        $usercpny = Usercpny::where('username', '=', $user->username)
            ->get();
        $usercpny2 = Usercpny::where('username', '=', $user->username)
            ->first();   
            
        $multicpny = explode(',', $user->companyid);     
        
        $randomNumber = random_int(10000000, 99999999);
        $refid = md5($randomNumber);            
        
        // $msproduct = Msproduct::join('vpl_ms_product_detail', 'vpl_ms_product.product_id', '=', 'vpl_ms_product_detail.product_id') 
        //     ->select(
        //         'vpl_ms_product_detail.id',
        //         'vpl_ms_product.product_id', 
        //         'vpl_ms_product.product_name', 
        //         'vpl_ms_product.cpnyid', 
        //         'vpl_ms_product_detail.expired_date', 
        //         'vpl_ms_product_detail.qty_available',
        //         'vpl_ms_product_detail.whs_id'
        //     )
        //     ->whereIn('vpl_ms_product.cpnyid',$multicpny)
        //     ->orderby('vpl_ms_product_detail.expired_date','ASC')           
        //     ->get();
        $mswhs = Mswhsdept::whereIN('cpnyid',$multicpny)
            ->where('whs_type','Child') 
            ->where('status','A')
            ->get(); 
        $userdept = Userdept::where('username', '=', $user->username)
            ->get();
        $userdept2 = Userdept::where('username', '=', $user->username)
            ->first();
        $detailtemp = Vplrequestdetailtemp::where('created_user',$user->username)
            // ->where('refid',$refid)
            ->get();
        // dd($detailtemp);        

        // return view('vplrequest.add_vplrequest', compact('usercpny','usercpny2','msproduct','mswhs','userdept','userdept2','detailtemp','refid'));
        return view('vplrequest.add_vplrequest', compact('usercpny','usercpny2','mswhs','userdept','userdept2','detailtemp','refid'));
    }

    public function getProductsRequesttemp($cpnyid, request $request)
    {   
        // dd($request->all());            
        $mswhs = Mswhsusage::where('cpnyid',$cpnyid)
            ->where('department_id',$request->department) 
            ->where('status','A')
            ->first(); 
            // dd($mswhs); 
        if($mswhs == null){
            $warehouse = Mswhsdept::where('cpnyid',$cpnyid)               
                ->where('whs_type','Child')
                ->where('vp_type',$request->vp_type)
                ->where('status','A')
                ->first();             
            $warehouse = $warehouse->whs_id;
        }else{
            $warehouse = $mswhs->whs_id;
        }

        $products = Msproduct::join('vpl_ms_product_detail', 'vpl_ms_product.product_id', '=', 'vpl_ms_product_detail.product_id')
            ->select(
                'vpl_ms_product.product_id',
                'vpl_ms_product.product_name',
                'vpl_ms_product.cpnyid'
            )
            ->where('vpl_ms_product.cpnyid', $cpnyid)
            ->where('vpl_ms_product.product_type', $request->vp_type)
            ->where('vpl_ms_product_detail.whs_id', $warehouse)
            ->distinct('vpl_ms_product.product_id') // Ensures unique product_id
            ->get(['product_id', 'product_name']);

        return response()->json($products);
    }
    
    public function getProductDetailsRequesttemp($cpnyid, $product_id, request $request)
    {
        $mswhs = Mswhsusage::where('cpnyid',$cpnyid)
            ->where('department_id',$request->department) 
            ->where('status','A')
            ->first(); 
        
        if($mswhs == null){
            $warehouse = Mswhsdept::where('cpnyid',$cpnyid)               
                ->where('whs_type','Child')
                ->where('vp_type',$request->vp_type)
                ->where('status','A')
                ->first();             
            $warehouse = $warehouse->whs_id;
        }else{
            $warehouse = $mswhs->whs_id;
        }

        $productsdetails = Msproduct::join('vpl_ms_product_detail', 'vpl_ms_product.product_id', '=', 'vpl_ms_product_detail.product_id') 
            ->select(
                'vpl_ms_product_detail.id',
                'vpl_ms_product.product_id', 
                'vpl_ms_product.product_name', 
                'vpl_ms_product.cpnyid', 
                'vpl_ms_product_detail.expired_date', 
                DB::raw('vpl_ms_product_detail.qty_available - vpl_ms_product_detail.qty_reserved AS qty_available'),
                'vpl_ms_product_detail.whs_id'
            )
            ->where('vpl_ms_product.product_id', $product_id)            
            ->where('vpl_ms_product.cpnyid', $cpnyid)
            // ->where('vpl_ms_product.product_type', $request->vp_type)
            ->where('vpl_ms_product_detail.whs_id', $warehouse)
            ->where('vpl_ms_product.product_type', $request->vp_type)
            ->orderby('vpl_ms_product_detail.expired_date', 'ASC')           
            ->get();
        
        return response()->json($productsdetails);
    }

    public function getProductsReturn($cpnyid, request $request)
    {   
        $user = Auth::user();        
            
        $multidept= explode(',', $user->departmentid);  
        $vplrequest = Vplrequest::where('cpnyid', $cpnyid)
            // ->whereIn('department', $multidept)
            ->where('department', $request->department)
            ->where('vp_type', $request->vp_type)
            ->where('requesttype', 'Usage')
            ->where('status', 'C')       
            ->get(['request_id', 'user','request_remark']);
        // dd($vplrequest);
        return response()->json($vplrequest);
    }

    public function getProductDetailsReturn($cpnyid, $request_id,request $request)
    {
        
        $mswhs = Mswhsusage::where('cpnyid',$cpnyid)
            ->where('department_id',$request->department) 
            ->where('status','A')
            ->first(); 
        
        if($mswhs == null){
            $warehouse = Mswhsdept::where('cpnyid',$cpnyid)               
                ->where('whs_type','Child')
                ->where('vp_type',$request->vp_type)
                ->where('status','A')
                ->first();             
            $warehouse = $warehouse->whs_id;
            // dd($warehouse);
        }else{
            $warehouse = $mswhs->whs_id;
        }
       
        // Ambil detail request berdasarkan product_id dan cpnyid
        $vplrequestdetail = Vplrequest::join('vpl_trx_request_detail', 'vpl_trx_request.request_id', '=', 'vpl_trx_request_detail.request_id') 
            ->join('vpl_ms_product', 'vpl_ms_product.product_id', '=', 'vpl_trx_request_detail.product_id') 
            ->select(
                'vpl_trx_request_detail.id',
                'vpl_trx_request_detail.product_id', 
                'vpl_ms_product.product_name', 
                'vpl_trx_request_detail.qty_request', 
                'vpl_trx_request_detail.expired_date',
                'vpl_trx_request_detail.purpose_id'
            )
            ->where('vpl_trx_request_detail.request_id', $request_id)            
            ->where('vpl_trx_request.cpnyid', $cpnyid)
            ->where('vpl_trx_request.vp_type', $request->vp_type)
            ->where('vpl_trx_request_detail.whs_id', $warehouse)                 
            ->get();

            // Kembalikan response dalam bentuk JSON
        return response()->json($vplrequestdetail);
    }
   

    public function validateStock(Request $request, $productId)
    {
        // $whsId = $request->input('whs_id'); // Kirim whs_id dari request
        $expiredDate = $request->input('expired_date'); // Kirim expired_date dari request
        // dd($request->all());
        $mswhs = Mswhsusage::where('cpnyid',$request->cpnyid)
            ->where('department_id',$request->department) 
            ->where('status','A')
            ->first(); 
        
        if($mswhs == null){
            $warehouse = Mswhsdept::where('cpnyid',$request->cpnyid)               
                ->where('whs_type','Child')
                ->where('vp_type',$request->vp_type)
                ->where('status','A')
                ->first();             
            $warehouse = $warehouse->whs_id;
            // dd($warehouse);
        }else{
            $warehouse = $mswhs->whs_id;
        }
        // dd($warehouse);
        $query = Msproductdetail::select('qty_available', 'qty_reserved')
                    ->where('product_id', $productId)
                    ->where('whs_id', $warehouse);

        // Jika expired_date dikirim, tambahkan kondisi untuk itu
        if ($expiredDate) {
            $query->where('expired_date', $expiredDate);
        }

        $product = $query->first();

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        return response()->json([
            'qty_available' => $product->qty_available,
            'qty_reserved' => $product->qty_reserved,
        ]);
    }

    public function validateReturn(Request $request, $productId)
    {
        $request->validate([
            'expired_date' => 'required|date',
            'qty_return' => 'required|numeric|min:0',
        ]);

        $expiredDate = $request->input('expired_date');       

        // Lakukan query dengan parameter yang diterima
        $data = Vplrequestdetail::selectRaw('SUM(qty_request) as qty_usage')
            ->where('product_id', $productId)
            ->where('expired_date', $expiredDate)
            ->first();

        if (!$data || !$data->qty_usage) {
            return response()->json([
                'message' => 'No data found for the given product ID and expiration date.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'message' => 'Validation successful.',
            'data' => [
                'qty_usage' => $data->qty_usage,                
            ],
        ]);
    }



    public function getProductsByRequestTypexxx(Request $request)
    {
        // dd($request->all());
        $user = Auth::user();
        $usercpny = Usercpny::where('username', '=', $user->username)
            ->get();
        $usercpny2 = Usercpny::where('username', '=', $user->username)
            ->first();   
            
        $multicpny = explode(',', $user->companyid);                
        $requesttype = $request->input('requesttype'); // Ambil tipe request dari request
   
        // Query produk berdasarkan warehouse dan tipe request
        $msproduct = Msproduct::join('vpl_ms_product_detail', 'vpl_ms_product.product_id', '=', 'vpl_ms_product_detail.product_id')
            ->select(
                'vpl_ms_product_detail.id',
                'vpl_ms_product.product_id', 
                'vpl_ms_product.product_name', 
                'vpl_ms_product.cpnyid', 
                'vpl_ms_product_detail.expired_date', 
                'vpl_ms_product_detail.qty_available',
                'vpl_ms_product_detail.whs_id'
            )
            // ->whereIn('vpl_ms_product.cpnyid', $multicpny)
            ->where('vpl_ms_product_detail.whs_id', 'WHS_PROMOTION') // Kondisi warehouse
            ->orderby('vpl_ms_product_detail.expired_date', 'ASC')
            ->get();

        return response()->json($msproduct); // Kembalikan data produk sebagai JSON
    }


    // Controller
    public function getProductsxxx(Request $request)
    {
        $products = MsProduct::where('cpnyid', $request->cpnyid)->get();
        return response()->json($products);
    }

       
    public function saveAddvplrequesttemp(Request $request)
    {
        // dd($request->all());
        $user = Auth::user();
        $qty = $request->input('qty_request');
        $productId = $request->input('product_id');
        $cpnyid = $request->input('cpnyid');
        $refid = $request->input('refid');
        $requesttype = $request->input('requesttype');

        // Find the appropriate warehouse for the company
        // $mswhs = Mswhsdept::where('cpnyid', $cpnyid)
        //     ->where('whs_type', 'Child')
        //     ->where('status', 'A')
        //     ->first();

        // if (!$mswhs) {
        //     return response()->json(['error' => 'Warehouse not found.'], 404);
        // }

        $mswhs = Mswhsusage::where('cpnyid',$cpnyid)
            ->where('department_id',$request->department) 
            ->where('status','A')
            ->first(); 
        
        if($mswhs == null){
            $warehouse = Mswhsdept::where('cpnyid',$cpnyid)               
                ->where('whs_type','Child')
                ->where('vp_type',$request->vp_type)
                ->where('status','A')
                ->first();             
            $warehouse = $warehouse->whs_id;
            // dd($warehouse);
        }else{
            $warehouse = $mswhs->whs_id;
        }

        // Get product details sorted by the nearest expiration date
        $productsdetail = Msproductdetail::where('cpnyid', $cpnyid)
            ->where('product_id', $productId)
            ->where('whs_id', $warehouse)
            ->orderBy('expired_date', 'ASC')
            ->get();

        $remainingQuantity = $qty;
        $insertData = [];

        foreach ($productsdetail as $detail) {
            if ($remainingQuantity <= 0) {
                break;
            }

            // Calculate available quantity considering both Msproductdetail and Vplrequestdetailtemp
            $existingTempQty = Vplrequestdetailtemp::where('refid', $refid)
                ->where('product_id', $detail->product_id)
                ->where('expired_date', $detail->expired_date)
                ->sum('qty_request');

            $availableQuantity = $detail->qty_available - $detail->qty_reserved - $existingTempQty;

            // Calculate quantity to take from this batch
            $takeQuantity = min($availableQuantity, $remainingQuantity);

            // Skip if takeQuantity is 0 or negative
            if ($takeQuantity <= 0) {
                continue;
            }

            // Prepare data for insertion
            $insertData[] = [
                'refid' => $refid,
                'product_id' => $detail->product_id,
                'expired_date' => $detail->expired_date,
                'whs_id' => $detail->whs_id,
                'requesttype' => $requesttype,
                'qty_request' => $takeQuantity,
                'created_user' => $user->username,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Deduct the taken quantity from remaining
            $remainingQuantity -= $takeQuantity;
        }

        if ($remainingQuantity > 0) {
            return response()->json(['error' => 'Stock tidak mencukupi.'], 400);
        }

        // Insert the prepared data into vpl_trx_request_detail_temp
        if (!empty($insertData)) {
            Vplrequestdetailtemp::insert($insertData);
        }

        return response()->json($insertData);
    }


    public function saveAddvplreturn(Request $request)
    {
        // dd($request->all());
        $user = Auth::user();
        $qty = $request->input('qty_request');
        $requestId = $request->input('request_id');
        $cpnyid = $request->input('cpnyid');
        $refid = $request->input('refid');
        $requesttype = $request->input('requesttype');

        Vplrequestdetailtemp::where('refid', $refid)->delete();
       
        // Get product details sorted by the nearest expiration date
        $vplrequestdetail = Vplrequestdetail::where('request_id', $requestId)                   
            ->get();
        // dd($vplrequestdetail);
        $insertData = [];

        foreach ($vplrequestdetail as $detail) {             
            // Prepare data for insertion
            $insertData[] = [
                'request_id' => $detail->request_id,
                'refid' => $refid,
                'product_id' => $detail->product_id,
                'expired_date' => $detail->expired_date,                
                'whs_id' => $detail->whs_id,
                'requesttype' => $requesttype,
                'qty_request' => $detail->qty_request,
                'purpose_id' => $detail->purpose_id,
                'created_user' => $user->username,
                'created_at' => now(),
                'updated_at' => now(),
            ];
           
        }

       
        // Insert the prepared data into vpl_trx_request_detail_temp
        if (!empty($insertData)) {
            Vplrequestdetailtemp::insert($insertData);
        }

        return response()->json($insertData);
    }


    public function getExistingEntries(Request $request, $refid)
    {
        // dd($request->all());
        $requesttype = $request->selectedRequestType;
        // dd($requesttype);
        // $entries = Vplrequestdetailtemp::where('refid', $refid)->get(['id', 'product_id', 'qty_request', 'expired_date']);
        $entries = Vplrequestdetailtemp::join('vpl_ms_product', 'vpl_trx_request_detail_temp.product_id', '=', 'vpl_ms_product.product_id')
            ->select(
                'vpl_trx_request_detail_temp.id',
                'vpl_trx_request_detail_temp.product_id',
                'vpl_ms_product.product_name',
                'vpl_trx_request_detail_temp.qty_request',
                'vpl_trx_request_detail_temp.expired_date',
                'vpl_trx_request_detail_temp.expired_date',
                'vpl_trx_request_detail_temp.purpose_id',
            )
            ->where('vpl_trx_request_detail_temp.refid', $refid)  
            ->where('vpl_trx_request_detail_temp.requesttype', $requesttype)       
            ->get(['id','product_id', 'product_name', 'qty_request', 'expired_date']);
               
        return response()->json($entries);
    }


    public function deleteEntry($id)
    {
        // dd($id);
        $entry = Vplrequestdetailtemp::find($id);
        
        if ($entry) {
            $entry->delete();
            return response()->json(['success' => 'Entry deleted successfully.']);
        }
        return response()->json(['error' => 'Entry not found.'], 404);
    }
    
    public function saveVplrequest(Request $request)
    {
        // dd($request->all());
        // $qty_return = $request->input('qty_return');
        // dd($qty_return);
        $user = Auth::user();
        $datenow = Carbon::now()->format('Y-m-d');
        $datestamp = Carbon::now()->toDateTimeString();
        $dt = Carbon::now();
        $year = $dt->year;
        $month =  $dt->month;      
        $refid = $request->refid; 
        
        // VLU	Voucher Loyalty Usage
        // PLU	Product Loyalty Usage
        // VRU	Voucher Loyalty Usage (RETURN)
        // PRU	Product Loyalty Usage (RETURN)
        $doctype = '';
        if($request->requesttype == 'Usage' && $request->vp_type == 'V' ) {
            $doctype = 'VLU';
        } else if  ($request->requesttype == 'Return' && $request->vp_type == 'V') {
            $doctype = 'VRU';
        } else if  ($request->requesttype == 'Usage' && $request->vp_type == 'P') {
            $doctype = 'PLU';
        } else if  ($request->requesttype == 'Return' && $request->vp_type == 'P') {
            $doctype = 'PRU';
        } else {$doctype = '';}
        
        $docdate = $request->doc_date;
        $docdate = date("Y-m-d", strtotime($docdate));
        
        //cek ms Approval
        $count_approval = M_approval::where('status', 'A')
            ->where('aprvcpnyid', $request->cpnyid)
            ->where('aprvdeptid', $request->department)
            ->where('aprvdoctype', $doctype)
            ->count();
        
        if ($count_approval == 0) {
            return response()->json(['error' => 'Approval Empty, Please contact IT!'], 422); // 422 Unprocessable Entity
        } else {
            $autonbr = Autonbr::where('doctype', $doctype)
                ->where('year', '=', $year)
                ->where('month', '=', $month)
                ->where('status', '=', 'A')
                ->first();

            $tglbln =  substr($dt->year, 2) . $autonbr->month;

            // $cek autonbr
            if ($autonbr->number == 0) {
                $urutan = 1;              
                $docid = $doctype . $tglbln . '00' . $urutan;
            } else {
                $urutan = $autonbr->number;
                $urutan++;               
                $docid = $doctype . $tglbln . sprintf("%03s", $urutan);
            }
            
            //update ms_autonbr
            $autonbr->number = $urutan;
            $autonbr->save();

            
            $vplrequestdetail_temp = Vplrequestdetailtemp::where('refid', $refid)  
                ->get();

            $requesttype = $request->requesttype;
            $qty_return = $request->qty_return ?? [];
            $purpose_id = $request->purpose_id ?? [];
            $purpose_remark = $request->purpose_remark ?? [];
            $ref_request_id = $request->request_id;
            
            if ($refid) {
                $lineNumber = 1;
                foreach ($vplrequestdetail_temp as $detail) {
                    
                    if ($requesttype === 'Return'){
                        $qtyToInsert = $qty_return[$detail['id']] ?? 0;
                        $currentPurposeId = $detail['purpose_id'] ?? '';
                        $currentPurposeRemark = $detail['purpose_remark'] ?? '';
                    }else{
                        $qtyToInsert = $detail['qty_request'] ?? 0;                       
                        $currentPurposeId = $purpose_id[$detail['id']] ?? '';
                        $currentPurposeRemark = $purpose_remark[$detail['id']] ?? '';

                    }
                               
                    // Insert into Vplrequestdetail
                    Vplrequestdetail::create([
                        'request_id' => $docid,
                        'linenbr' => $lineNumber,
                        'product_id' => $detail['product_id'],                      
                        'qty_request' => $qtyToInsert,
                        'expired_date' => $detail['expired_date'],
                        'whs_id' => $detail['whs_id'],           
                        'purpose_id' => $currentPurposeId,    
                        'purpose_remark' => $currentPurposeRemark,   
                        'ref_request_id' => $ref_request_id, 
                        'status' => 'P',
                        'created_user' => $user->username,
                        'created_at' => $datestamp,
                    ]);
            
                    // Check if the record already exists in msproductdetail
                    $msProductDetail = Msproductdetail::where('product_id', $detail['product_id'])
                        ->where('expired_date', $detail['expired_date'])
                        ->where('whs_id', $detail['whs_id'])
                        ->first();
            
                    if ($msProductDetail) {
                        // Update qty_reserved based on requesttype
                        if ($requesttype === 'Usage') {
                            $msProductDetail->qty_reserved += $qtyToInsert;
                        } else {
                            $msProductDetail->qty_reserved -= $qtyToInsert;
                        }
            
                        $msProductDetail->updated_at = $datestamp;
                        $msProductDetail->save();
                    }
            
                    // Increment lineNumber for each new row
                    $lineNumber++;
                }
            }    
            
            $count_vplrequestdetail = Vplrequestdetail::where('request_id', $docid)  
                ->where('purpose_id','<>','Redeem PG Card')
                ->count();
            //  dd($count_vplrequestdetail);

            
            $m_approval = M_approval::where('aprvdoctype', $doctype)             
                ->where('aprvcpnyid', $request->cpnyid)
                ->where('aprvdeptid', $request->department)
                ->where('status', 'A')
                ->get();

            $deptname =$request->department;

            // Jika count_vplrequestdetail == 0 dan department adalah CUSTOMERSERVICE, lakukan sekali saja
            if ($count_vplrequestdetail == 0 && $deptname == 'CUSTOMERSERVICE') {
                T_approval::create([
                    'docid' => $docid,
                    'aprvid' => 1, // Level approval pertama
                    'aprvdoctype' => $doctype,
                    'aprvcpnyid' => $request->cpnyid,
                    'aprvdeptid' => $request->department,
                    'aprvdatebefore' => $datestamp, 
                    'aprvdateafter' => $datestamp, // Disetujui langsung
                    'aprvtotalday' => 1,
                    'aprvusername' => $user->username,
                    'name' => $user->name,
                    'status' => 'A', // Langsung disetujui
                    'created_user' => $user->name,
                ]);
            } else {
                // Normal loop untuk approval lain
                foreach ($m_approval as $mp) {
                    $aprvdatebefore = ($mp->aprvid == 1) ? $datestamp : null; 
                    $aprvusername = $mp->aprvusername;
                    $name = $mp->name;
                    $status = 'P';
                    $aprvdateafter = null;

                    T_approval::create([
                        'docid' => $docid,
                        'aprvid' => $mp->aprvid,
                        'aprvdoctype' => $mp->aprvdoctype,
                        'aprvcpnyid' => $mp->aprvcpnyid,
                        'aprvdeptid' => $mp->aprvdeptid,
                        'aprvdatebefore' => $aprvdatebefore,
                        'aprvdateafter' => $aprvdateafter,
                        'aprvtotalday' => 1,
                        'aprvusername' => $aprvusername,
                        'name' => $name,
                        'status' => $status,
                        'created_user' => $user->name,
                    ]);
                }
            }

            //insert trx_approval
            // foreach ($m_approval as $mp) {
            //     $aprvdatebefore = ($mp->aprvid == 1) ? $datestamp : null; 
            //     $aprvusername = $mp->aprvusername;
            //     $name = $mp->name;
            //     $status = 'P';
            //     $aprvdateafter = NULL;
    
            //     if ($count_vplrequestdetail == 0 && $deptname == 'CUSTOMERSERVICE') {
            //         $aprvusername = $user->username;
            //         $name = $user->name;
            //         $aprvdateafter = $datestamp;
            //         $status = 'A';
            //     }
    
            //     T_approval::create([
            //         'docid' => $docid,
            //         'aprvid' => $mp->aprvid,
            //         'aprvdoctype' => $mp->aprvdoctype,
            //         'aprvcpnyid' => $mp->aprvcpnyid,
            //         'aprvdeptid' => $mp->aprvdeptid,
            //         'aprvdatebefore' => $aprvdatebefore,
            //         'aprvdateafter' => $aprvdateafter,
            //         'aprvtotalday' => 1,
            //         'aprvusername' => $aprvusername,
            //         'name' => $name,
            //         'status' => $status,
            //         'created_user' => $user->name,
            //     ]);
            // }
            
            

            if ($request->hasfile('attachment')) {
                foreach ($request->file('attachment') as $file) {
                    $randomNumber = random_int(100000, 999999);
                    $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $attachfile = $randomNumber . '-' . $file->getClientOriginalName();

                    //attach to folder                    
                    $folder_attach = public_path() . '/attachment/'.$year;
                    $config['upload_path'] = $folder_attach;                   
                    if(!is_dir($folder_attach))
                    {
                        mkdir($folder_attach, 0777);
                    }
                    // $folder_upload = public_path() . '/attachment';
                    $folder_upload = $folder_attach;
                    $file->move($folder_upload, $attachfile);

                    //insert to table attachment
                    $attach = new Attachment();
                    $attach->docid = $docid;
                    $attach->name = $filename;
                    $attach->attachfile = $attachfile;
                    $attach->status = 'A';
                    $attach->extention = $file->getClientOriginalExtension();
                    $attach->created_user = $user->name;
                    $attach->save();
                }
            }

            $status = 'P';
            $completed_user = NULL;
            $completed_at = NULL;
            if ($count_vplrequestdetail == 0 && $deptname == 'CUSTOMERSERVICE') {
                $status = 'C';
                $completed_user = $user->username;
                $completed_at = $datestamp;                
            }

            $request = Vplrequest::create([
                'request_id' => $docid,
                'cpnyid' => $request->cpnyid,
                'department' => $request->department,                
                'vp_type' => $request->vp_type,
                'request_date' => $docdate,
                'requesttype' => $request->requesttype,               
                'request_remark' => $request->request_remark,  
                'user' => $user->username,         
                'ref_request_id' => $ref_request_id,    
                'status' => $status,      
                'completed_user' => $completed_user,
                'completed_at' => $completed_at,      
                'created_user' => $user->name
            ]);
            
            // Mengambil ID dari record yang baru saja dibuat
            $id = $request->id; // Atau field yang menjadi primary key jika berbeda, misalnya 'request_id'
            if ($count_vplrequestdetail == 0 && $deptname == 'CUSTOMERSERVICE') {              
                app('App\Http\Controllers\VplrequestController')->insert_msproduct_detail($id);
                app('App\Http\Controllers\VplledgerController')->insert_ledger_from_request($id); 
            }


             //read trx_approval next
        $t_approval_next = T_approval::where('docid', $docid)
            ->where('status', 'P')
            ->orderby('aprvid','ASC')
            ->first();
        // dd($t_approval_next);    
        $ms_site = Site::where('id', $user->site)            
            ->first();
        
        //send email to it advice
        $data = array(
            'docid' => $docid,
            'cpnyid' => $request->cpnyid,
            'deptname' => $request->department,
            'locationname' => $ms_site->site,
            'date' => $datestamp,
            'name' => $user->name,
            'info' => $request->request_remark,           
            'url' => url('/showvplrequest_') . $id

        );

        if ($t_approval_next === null) {
            $multiapp = [$user->username];
        } else {
            $multiapp = explode(',', $t_approval_next->aprvusername);
        }
        

        $email_it = User::whereIN('username', $multiapp)
            ->where('status', 'A')
            ->get();

        foreach ($email_it as $emailsit) {
            Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {
                $message->to($emailsit->test_email)->subject($data['docid'] . ' - Waiting Approval Usage');
                $message->from('digitalserver@pakuwon.com', 'Digital Approval System');
            });
        }
            return response()->json(['success' => 'Request saved successfully.']);  
        }
        
    }
   
    public function approve($id, Request $request)
    {
        //update tr_vplrequest
        $vplrequest = Vplrequest::find($id);
        $user = Auth::user();
        $datestamp = Carbon::now()->toDateTimeString();
        $ms_site = Site::where('id', $user->site)            
            ->first();
        
        //update status completed tr_vplrequest
        $count_approval = T_approval::where('docid', '=', $vplrequest->request_id)
            ->where('status', '=', 'P')
            ->count();
      
        //read trx_approval
        $t_approval = T_approval::where('docid', $vplrequest->request_id)
            ->where('status', 'P')
            ->where('aprvusername', 'like', "%" . $user->username . "%")
            ->first();
        // dd($t_approval->status);    
        //update trx_approval 
        if ($t_approval == null){
            return redirect('/showvplrequest_' . $id);
        }else{
            $t_approval->status = 'A';
            $t_approval->aprvdateafter = $datestamp;
            $t_approval->aprvusername = $user->username;
            $t_approval->name = $user->name;
            $t_approval->save();
        }   

        //jika approval terakhir
        if ($count_approval == 1) {
            $vplrequest->status = 'C';
            $vplrequest->completed_user = $user->username;
            $vplrequest->completed_at = $datestamp;
            $vplrequest->save();
            app('App\Http\Controllers\VplrequestController')->insert_msproduct_detail($id);
            app('App\Http\Controllers\VplledgerController')->insert_ledger_from_request($id); 
            //call generate pdf and send email
            // app('App\Http\Controllers\VplrequestController')->generate_pdf($id);          
           
        }
        
        //read trx_approval next
        $t_approval_next = T_approval::where('docid', $vplrequest->request_id)
            ->where('status', 'P')
            ->orderby('aprvid','ASC')
            ->first();

        if ($count_approval <> 1) {
            //update datebefore
            $t_approval_next->aprvdatebefore = $datestamp;
            $t_approval_next->save();

            //send email to it advice
            $data = array(
                'docid' => $t_approval_next->docid,
                'cpnyid' => $t_approval_next->aprvcpnyid,
                'deptname' => $t_approval_next->aprvdeptid,
                'locationname' => $ms_site->site,
                'date' => $t_approval_next->aprvdatebefore,
                'name' => $t_approval_next->created_user,
                'info' => $vplrequest->request_remark,               
                'url' => url('/showvplrequest_') . $id

            );

            $multiapp = explode(',', $t_approval_next->aprvusername);

            $email_it = User::whereIN('username', $multiapp)
                ->where('status', 'A')
                ->get();

            foreach ($email_it as $emailsit) {
                Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

                    $message->to($emailsit->test_email)->subject($data['docid'] . ' - Waiting Approval Usage');
                    $message->from('digitalserver@pakuwon.com', 'Digital Approval System');
                });
            }
        }
        
        return redirect('/home')->with('message', 'Data Approved Successfully');
      
    }

    public function reject($id, Request $request)
    {
        if ($request->message == ''){
            return redirect('/showvplrequest_' . $id)->with('error', 'Message Empty, Please Entry Message');
        }
        //update tr_vplrequest
        $vplrequest = Vplrequest::find($id);
        $user = Auth::user();
        $datestamp = Carbon::now()->toDateTimeString();
        $ms_site = Site::where('id', $user->site)            
            ->first();

        //read trx_approval
        $t_approval = T_approval::where('docid', '=', $vplrequest->request_id)
            ->where('status', '=', 'P')
            ->orderby('aprvid','ASC')
            ->first();

        $vplrequest->status = 'R';
        $vplrequest->save();

        //update trx_approval 
        $t_approval->status = 'R';
        $t_approval->aprvdateafter = $datestamp;
        // $t_approval->aprvusername = $user->email;
        $t_approval->aprvusername = $user->username;
        $t_approval->name = $user->name;
        $t_approval->save();

        $t_aprv_sisa = T_approval::where('docid', '=', $vplrequest->request_id)
            ->where('status', '=', 'P')
            ->get();

        foreach ($t_aprv_sisa as $t_aprv) {
            $t_aprv->status = 'X';
            $t_aprv->save();
        }

        app('App\Http\Controllers\VplrequestController')->cancel_msproduct_detail($id);

        //send email to it advice
        $data = array(
            'docid' => $t_approval->docid,
            'cpnyid' => $t_approval->aprvcpnyid,
            'deptname' => $t_approval->aprvdeptid,
            'locationname' => $ms_site->site,
            'date' => $t_approval->aprvdatebefore,
            'name' => $t_approval->created_user,
            'info' => $vplrequest->request_remark,            
            'url' => url('/showvplrequest_') . $id

        );

        $email_it = User::where('username', $vplrequest->user)
                ->where('status', 'A')
                ->get();

        foreach ($email_it as $emailsit) {
            Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {
                $message->to($emailsit->test_email)->subject($data['docid'] . ' - Rejected Usage');
                $message->from('digitalserver@pakuwon.com', 'Digital Approval System');
            });
        }

        app('App\Http\Controllers\VplrequestController')->sendmsg($id,$request);
       
        return redirect('/home')->with('message', 'Data Rejected Successfully');
    }

    public function revise($id, Request $request)
    {
        if ($request->message == ''){
            return redirect('/showvplrequest_' . $id)->with('error', 'Message Empty, Please Entry Message');
        }
        //update tr_vplrequest
        $vplrequest = Vplrequest::find($id);
        $user = Auth::user();
        $datestamp = Carbon::now()->toDateTimeString();
        $ms_site = Site::where('id', $user->site)            
            ->first();

        //update status tr_vplrequest
        $vplrequest->status = 'D';
        $vplrequest->updated_user = $user->name;
        $vplrequest->updated_at = $datestamp;
        $vplrequest->save();

        //read trx_approval
        $t_approval = T_approval::where('docid', '=', $vplrequest->request_id)
            ->where('status', '=', 'P')
            ->orderby('aprvid','ASC')
            ->first();

        //update trx_approval 
        $t_approval->status = 'D';
        $t_approval->aprvdateafter = $datestamp;
        // $t_approval->aprvusername = $user->email;
        $t_approval->aprvusername = $user->username;
        $t_approval->name = $user->name;
        $t_approval->save();

        //read trx_approval sisa
        $t_aprv_sisa = T_approval::where('docid', '=', $vplrequest->request_id)
            ->where('status', '=', 'P')
            ->get();

        //update trx_approval sisa
        foreach ($t_aprv_sisa as $t_aprv) {
            $t_aprv->status = 'X';
            $t_aprv->save();
        }

        //send email to created
        $data = array(
            'docid' => $t_approval->docid,
            'cpnyid' => $t_approval->aprvcpnyid,
            'deptname' => $t_approval->aprvdeptid,
            'locationname' => $ms_site->site,
            'date' => $t_approval->aprvdatebefore,
            'name' => $t_approval->created_user,            
            'info' => $vplrequest->request_remark . ' (Silahkan Revisi dengan cara klik link dibawah ini lalu klik tombol Edit lalu Submit/Cancel Document, Thanks)',
            'url' => url('/showvplrequest_') . $id

        );

        $email_it = User::where('username', $vplrequest->user)
                ->where('status', 'A')
                ->get();

        foreach ($email_it as $emailsit) {
            Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {
                $message->to($emailsit->test_email, '-')->subject($data['docid'] . ' - Revise Usage');
                $message->from('digitalserver@pakuwon.com', 'Digital Approval System');
            });
        }

        app('App\Http\Controllers\VplrequestController')->sendmsg($id,$request);
        app('App\Http\Controllers\VplrequestController')->cancel_msproduct_detail($id);
                
        return redirect('/home')->with('message', 'Data Revised Successfully');
    }

    //show data Trouble Report and trx_Approval
    public function show_vplrequest($id, Request $request)
    {
       
        $vplrequest = Vplrequest::find($id);
        $company = Company::where('status', 'A')->get();
        $user = Auth::user();
        $cek_role = User::where('name', $user->name)->first();
        //show all trx_approval
        $t_approval = T_approval::where('docid', $vplrequest->request_id)
            ->where('status','<>','X')      
            ->orderBy('created_at')
            ->orderBy('aprvid')      
            ->get();
            // dd($t_approval);
        $vplrequest_ref = Vplrequest::where('request_id', $vplrequest->ref_request_id)->first();
        //read status
        if ($vplrequest->status =='R'){
            $status_doc ='Rejected';
        } else if ($vplrequest->status =='C'){
            $status_doc ='Completed';
        } else if ($vplrequest->status =='D'){    
            $status_doc ='Hold';
        }else if($vplrequest->status =='X'){    
            $status_doc ='Cancel';    
        } else {
            $status_doc ='On Progress';
        }    

        if ($vplrequest->requesttype =='Usage'){
            $requesttype ='Usage';
        } else if ($vplrequest->requesttype =='Return'){
            $requesttype ='Return';         
        } else {
            $requesttype ='Adjusment';
        }    


        //hidden button update,add, upload
      
        if($vplrequest->status == 'D' and $vplrequest->created_user == $user->name){
            $hidden = '';
        }else{
            // $hidden = 'hidden';
            $hidden = 'display:none';
        } 

        //cek for validasi button approval   
        if ($vplrequest->status == 'P') {           
            
            $trx_cek_like = T_approval::where('docid', $vplrequest->request_id)
                ->where('status', 'P')
                ->where('aprvusername', 'like', "%" . $user->username . "%")                
                ->first();  
            
            if ($trx_cek_like == null or $trx_cek_like->aprvdatebefore == null) {
                $popup_approve = '#modal-warning';
                $popup_reject = '#modal-warning';
                $popup_revise = '#modal-warning';
                
            } else {
                $cek_approval = T_approval::where('docid', $vplrequest->request_id)
                    ->where('status', '=', 'P')
                    ->whereNotNull('aprvdatebefore')
                    ->first();
                    //    dd($cek_approval); 
                $trx_cek_like2 = T_approval::where('aprvid', $cek_approval->aprvid)
                    ->where('aprvusername', 'like', "%" . $user->username . "%")
                    ->first();
                    // dd($trx_cek_like2);  
                if ($cek_approval->aprvusername == $user->username or $trx_cek_like2) {
                    
                    $popup_approve = '#modal-info';
                    $popup_reject = '#modal-danger';
                    $popup_revise = '#modal-success';
                } else {
                    
                    $popup_approve = '#modal-warning';
                    $popup_reject = '#modal-warning';
                    $popup_revise = '#modal-warning';
                }
            }
        } else {
           
            $popup_approve = '#modal-warning';
            $popup_reject = '#modal-warning';
            $popup_revise = '#modal-warning';
        }

        //read detail
        $vplrequestdetail = Vplrequestdetail::join('vpl_ms_product','vpl_trx_request_detail.product_id','=','vpl_ms_product.product_id')
            ->select('vpl_trx_request_detail.*','vpl_ms_product.product_name')
            ->where('request_id', $vplrequest->request_id)            
            ->get();
         

        //read attachment
        $t_attachment = Attachment::where('docid', $vplrequest->request_id)
            ->where('status', 'A')
            ->get();
        //read message
        $t_message = T_Message::where('docid', $vplrequest->request_id)
            ->where('status', 'A')
            ->get();
       
        $trx_cancel = T_approval::where('docid', $vplrequest->request_id)
            ->where('status', 'P')           
            ->where('aprvid',1)
            ->count();  
             
        $tr_vplrequest = Vplrequest::where('status', 'D')  
            ->where('request_id', $vplrequest->request_id)
            ->count();    
       
        if (($trx_cancel == 1 || $tr_vplrequest == 1) && $vplrequest->created_user == $user->name) {
            // Show element if either condition matches and the created user is the same as the logged-in user
            $hiddenx = '';
        } else {
            // Hide the element
            $hiddenx = 'display:none';
        }
        return view('vplrequest.show_vplrequest', compact('vplrequest', 't_approval', 'vplrequestdetail', 'popup_approve', 'popup_reject', 'popup_revise', 't_attachment',  't_message', 'user', 'company','status_doc','hidden','hiddenx','requesttype','vplrequest_ref'));
    }


    
    public function vplrequest_waiting(Request $request)
    {     
           
        $tittle = 'On Progress Usage / Return';   
        $user = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $multidept = explode(',', $user->departmentid);

        if ($request->ajax()) {                   
            
            if ($user->role == 'admin') {                
                $data = Vplrequest::leftjoin('trx_approval', 'vpl_trx_request.request_id', '=', 'trx_approval.docid')                                      
                    ->select('vpl_trx_request.*', 'trx_approval.name as waiting')
                    ->where('trx_approval.status', 'P')
                    ->where('trx_approval.aprvdatebefore', '<>',null)                        
                    ->get();           
            }else if ($user->groups == 18 || $user->groups == 6 || $user->groups == 19){
                $data = Vplrequest::leftjoin('trx_approval', 'vpl_trx_request.request_id', '=', 'trx_approval.docid')                                      
                    ->select('vpl_trx_request.*', 'trx_approval.name as waiting')
                    ->where('trx_approval.status', 'P')
                    ->where('trx_approval.aprvdatebefore', '<>',null)   
                    ->whereIn('vpl_trx_request.cpnyid', $multicpnyid)                                        
                    ->get(); 
            }else{
                $data = Vplrequest::leftjoin('trx_approval', 'vpl_trx_request.request_id', '=', 'trx_approval.docid')                                      
                    ->select('vpl_trx_request.*', 'trx_approval.name as waiting')
                    ->where('trx_approval.status', 'P')
                    ->where('trx_approval.aprvdatebefore', '<>',null)   
                    ->whereIn('vpl_trx_request.cpnyid', $multicpnyid)
                    ->whereIn('vpl_trx_request.department', $multidept)                     
                    ->get();                
            }
                       
            return Datatables::of($data)   
                ->addColumn('status', function($row){                                        
                    if ($row->status == 'P') {                                        
                        $btn = '<a href="javascript:void(0)" class="label" style="background-color: #FFCD05">On Progress</a>';
                    }else if ($row->status == 'C'){
                        $btn = '<a href="javascript:void(0)" class="label" style="background-color: #05A801">Completed</a>';
                    }else if ($row->status == 'R'){
                        $btn = '<a href="javascript:void(0)" class="label" style="background-color: #EA002F">Rejected</a>';
                    }else if ($row->status == 'X'){
                        $btn = '<a href="javascript:void(0)" class="label" style="background-color: #EA002F">Cancel</a>';
                    }else{                                      
                        $btn = '<a href="javascript:void(0)" class="label label-info">Revise</a>';
                    }      
                    return $btn;
                })

                ->addColumn('requesttype', function($row) {
                    // Periksa nilai requesttype
                    if ($row->requesttype == 'Usage') {
                        return 'Usage';
                    } else if ($row->requesttype == 'Return') {
                        return 'Return';
                    } else {
                        return '';
                    }
                })
                
                ->addColumn('request_id', function($row){         
                    $url = "/showvplrequest_$row->id";                                                            
                    $btn = '<a href="'.$url.'" class="btn btn-block" style="background-color: #3c87e2; color:white">'.$row->request_id.'</a>';    
                    return $btn;
                })
                ->rawColumns(['status','request_id','requesttype'])                                           
                ->make(true);
        }
        return view('vplrequest.vplrequest_waiting', compact('tittle','user'));
    }

    public function vplrequest_completed(Request $request)
    {     
           
        $tittle = 'Completed Usage / Return';   
        $user = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $multidept = explode(',', $user->departmentid);

        if ($request->ajax()) {                   
            
            if ($user->role == 'admin') {                
                $data = Vplrequest::where('status', 'C')                    
                    ->get();
            }else if ($user->groups == 18 || $user->groups == 6 || $user->groups == 19){
                $data = Vplrequest::whereIn('cpnyid', $multicpnyid)                    
                    ->where('status', 'C')                 
                    ->get();           
            }else{
                $data = Vplrequest::whereIn('cpnyid', $multicpnyid)
                    ->whereIn('department', $multidept)  
                    ->where('status', 'C')                 
                    ->get();                
            }
                       
            return Datatables::of($data)                                           
                ->addColumn('status', function($row){                                        
                    if ($row->status == 'P') {                                        
                        $btn = '<a href="javascript:void(0)" class="label" style="background-color: #FFCD05">On Progress</a>';
                    }else if ($row->status == 'C'){
                        $btn = '<a href="javascript:void(0)" class="label" style="background-color: #05A801">Completed</a>';
                    }else if ($row->status == 'R'){
                        $btn = '<a href="javascript:void(0)" class="label" style="background-color: #EA002F">Rejected</a>';
                    }else if ($row->status == 'X'){
                        $btn = '<a href="javascript:void(0)" class="label" style="background-color: #EA002F">Cancel</a>';
                    }else{                                      
                        $btn = '<a href="javascript:void(0)" class="label label-info">Revise</a>';
                    }      
                    return $btn;
                })

                ->addColumn('requesttype', function($row) {
                    // Periksa nilai requesttype
                    if ($row->requesttype == 'Usage') {
                        return 'Usage';
                    } else if ($row->requesttype == 'Return') {
                        return 'Return';
                    } else {
                        return '';
                    }
                })
                    
                ->addColumn('request_id', function($row){         
                    $url = "/showvplrequest_$row->id";                                                            
                    $btn = '<a href="'.$url.'" class="btn btn-block" style="background-color: #3c87e2; color:white">'.$row->request_id.'</a>';    
                    return $btn;
                })
                ->rawColumns(['status','request_id','requesttype'])                                           
                ->make(true);
            }
        return view('vplrequest.vplrequest_completed', compact('user','tittle'));
    }
    
    public function vplrequest_rejected(Request $request)
    {     
           
        $tittle = 'Rejected Usage / Return';   
        $user = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $multidept = explode(',', $user->departmentid);
       
        if ($request->ajax()) {                   
            
            if ($user->role == 'admin') {                
                $data = Vplrequest::where('status', 'R')                    
                    ->get();    
            }else if ($user->groups == 18 || $user->groups == 6 || $user->groups == 19){
                $data = Vplrequest::whereIn('cpnyid', $multicpnyid)                     
                    ->where('status', 'R')                 
                    ->get();               
            }else{
                $data = Vplrequest::whereIn('cpnyid', $multicpnyid)
                    ->whereIn('department', $multidept)  
                    ->where('status', 'R')                 
                    ->get();                
            }
                      
            return Datatables::of($data)                                           
                ->addColumn('status', function($row){                                        
                    if ($row->status == 'P') {                                        
                        $btn = '<a href="javascript:void(0)" class="label" style="background-color: #FFCD05">On Progress</a>';
                    }else if ($row->status == 'C'){
                        $btn = '<a href="javascript:void(0)" class="label" style="background-color: #05A801">Completed</a>';
                    }else if ($row->status == 'R'){
                        $btn = '<a href="javascript:void(0)" class="label" style="background-color: #EA002F">Rejected</a>';
                    }else if ($row->status == 'X'){
                        $btn = '<a href="javascript:void(0)" class="label" style="background-color: #EA002F">Cancel</a>';
                    }else{                                      
                        $btn = '<a href="javascript:void(0)" class="label label-info">Revise</a>';
                    }      
                    return $btn;
                })

                ->addColumn('requesttype', function($row) {
                    // Periksa nilai requesttype
                    if ($row->requesttype == 'Usage') {
                        return 'Usage';
                    } else if ($row->requesttype == 'Return') {
                        return 'Return';
                    } else {
                        return '';
                    }
                })
                    
                ->addColumn('request_id', function($row){         
                    $url = "/showvplrequest_$row->id";                                                            
                    $btn = '<a href="'.$url.'" class="btn btn-block" style="background-color: #3c87e2; color:white">'.$row->request_id.'</a>';    
                    return $btn;
                })
                ->rawColumns(['status','request_id','requesttype'])                                           
                ->make(true);
            }
        
        return view('vplrequest.vplrequest_rejected', compact('user','tittle'));
    }

    public function vplrequest_all(Request $request)
    {     
           
        $tittle = 'All Usage / Return';   
        $user = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $multidept = explode(',', $user->departmentid);

        if ($request->ajax()) {                   
            
            if ($user->role == 'admin') {
                $data = Vplrequest::get(); 
            }else if ($user->groups == 18 || $user->groups == 6 || $user->groups == 19){
                $data = Vplrequest::whereIn('cpnyid', $multicpnyid)                                      
                    ->get();           
            }else{
                $data = Vplrequest::whereIn('cpnyid', $multicpnyid)
                    ->whereIn('department', $multidept)                   
                    ->get();                
            }
                       
            return Datatables::of($data)                                           
                ->addColumn('status', function($row){                                        
                    if ($row->status == 'P') {                                        
                        $btn = '<a href="javascript:void(0)" class="label" style="background-color: #FFCD05">On Progress</a>';
                    }else if ($row->status == 'C'){
                        $btn = '<a href="javascript:void(0)" class="label" style="background-color: #05A801">Completed</a>';
                    }else if ($row->status == 'R'){
                        $btn = '<a href="javascript:void(0)" class="label" style="background-color: #EA002F">Rejected</a>';
                    }else if ($row->status == 'X'){
                        $btn = '<a href="javascript:void(0)" class="label" style="background-color: #EA002F">Cancel</a>';
                    }else{                                      
                        $btn = '<a href="javascript:void(0)" class="label label-info">Revise</a>';
                    }      
                    return $btn;
                })

                ->addColumn('requesttype', function($row) {
                    // Periksa nilai requesttype
                    if ($row->requesttype == 'Usage') {
                        return 'Usage';
                    } else if ($row->requesttype == 'Return') {
                        return 'Return';
                    } else {
                        return '';
                    }
                })
                    
                ->addColumn('request_id', function($row){         
                    $url = "/showvplrequest_$row->id";                                                            
                    $btn = '<a href="'.$url.'" class="btn btn-block" style="background-color: #3c87e2; color:white">'.$row->request_id.'</a>';    
                    return $btn;
                })
                ->rawColumns(['status','request_id','requesttype'])                                           
                ->make(true);
            }
        
        return view('vplrequest.vplrequest_all', compact('tittle','user'));
    }
    public function sendmsg_ajax(Request $request, $id)
    {      

        $user = Auth::user();
        $vplrequest = Vplrequest::find($id);

        // VLU	Voucher Loyalty Usage
        // PLU	Product Loyalty Usage
        // VRU	Voucher Loyalty Usage (RETURN)
        // PRU	Product Loyalty Usage (RETURN)
        $doctype = '';
        if($vplrequest->requesttype == 'Usage' && $vplrequest->vp_type == 'V' ) {
            $doctype = 'VLU';
        } else if  ($vplrequest->requesttype == 'Return' && $vplrequest->vp_type == 'V') {
            $doctype = 'VRU';
        } else if  ($vplrequest->requesttype == 'Usage' && $vplrequest->vp_type == 'P') {
            $doctype = 'PLU';
        } else if  ($vplrequest->requesttype == 'Return' && $vplrequest->vp_type == 'P') {
            $doctype = 'PRU';
        } else {$doctype = '';}

        $data = new T_Message();
        $data->docid = $vplrequest->request_id;
        $data->doctype = $doctype;
        $data->username = $user->username;
        $data->name = $user->name;
        $data->message = $request->msg;
        $data->status = 'A';
        $data->created_user = $user->username;
        $data->save();       

    }
    public function sendmsg(int $id, Request $request)
    {
        //send message
        $this->validate($request, [
            'message' => 'required'
        ]);

        $user = Auth::user();
        $vplrequest = Vplrequest::find($id);

        // VLU	Voucher Loyalty Usage
        // PLU	Product Loyalty Usage
        // VRU	Voucher Loyalty Usage (RETURN)
        // PRU	Product Loyalty Usage (RETURN)
        $doctype = '';
        if($vplrequest->requesttype == 'Usage' && $vplrequest->vp_type == 'V' ) {
            $doctype = 'VLU';
        } else if  ($vplrequest->requesttype == 'Return' && $vplrequest->vp_type == 'V') {
            $doctype = 'VRU';
        } else if  ($vplrequest->requesttype == 'Usage' && $vplrequest->vp_type == 'P') {
            $doctype = 'PLU';
        } else if  ($vplrequest->requesttype == 'Return' && $vplrequest->vp_type == 'P') {
            $doctype = 'PRU';
        } else {$doctype = '';}

        //save trx_message
        T_Message::create([
            'docid' => $vplrequest->request_id,
            'doctype' => $doctype,
            'username' => $user->username,
            'name' => $user->name,
            'message' => $request->message,
            'created_user' => $user->name,
            'status' => 'A'
        ]);

        return redirect('/showvplrequest_' . $id);
    }

    public function print_vplrequest_pdf(int $id)
    {
        $vplrequest = Vplrequest::find($id);
     
        $vplrequestdetail = Vplrequestdetail::join('vpl_ms_product','vpl_trx_request_detail.product_id','=','vpl_ms_product.product_id')
            ->select('vpl_trx_request_detail.*','vpl_ms_product.product_name')
            ->where('request_id', $vplrequest->request_id)            
            ->get();
       
        $t_approval = T_approval::where('docid', $vplrequest->request_id)
            ->where('status','<>','X')   
            ->orderBy('created_at')
            ->orderBy('aprvid')         
            ->get();
        $company = Company::where('cpnyid', $vplrequest->cpnyid)->first();
        $date = $vplrequest->created_at->format(' d F Y ');            

        $approve_count = T_approval::where('docid', $vplrequest->request_id) 
            ->where('status', '<>','X')           
            ->count();
      
        $data = [
            'cpnyid' => $company->cpnyname,
            'parent' => $company->parent,
            'project' => $company->project,
            'department' => $vplrequest->department,
            'docid' => $vplrequest->request_id,
            'requesttype' => $vplrequest->requesttype,            
            'created_at' => $date,
            'user' => $vplrequest->created_user,                      
            'request_remark' => $vplrequest->request_remark,               
            'req_date' => $vplrequest->created_at,            
        ];


        $pdf = PDF::loadview('vplrequest.show_vplrequest_pdf', $data, ['vplrequestdetail' => $vplrequestdetail, 't_approval' => $t_approval, 'approve_count' => $approve_count]);
        return $pdf->stream("pdf_vplrequest.pdf");


    }

    public function edit_vplrequest(int $id)
    {
        $vplrequest = Vplrequest::find($id);
        $vplrequestdetail = Vplrequestdetail::join('vpl_ms_product','vpl_trx_request_detail.product_id','=','vpl_ms_product.product_id')
            ->select('vpl_trx_request_detail.*','vpl_ms_product.product_name')
            ->where('request_id', $vplrequest->request_id)            
            ->get();
        $t_attachment = Attachment::where('docid', $vplrequest->request_id)
            ->where('status', 'A')
            ->get();
             
        $user = Auth::user();
        $usercpny = Usercpny::where('username', '=', $user->username)
            ->get();
        $usercpny2 = Usercpny::where('username', '=', $user->username)
            ->first();   

        $randomNumber = random_int(10000000, 99999999);
        $refid = md5($randomNumber); 
            
        // $multicpny = explode(',', $user->companyid);                
               
        // $msproduct = Msproduct::join('vpl_ms_product_detail', 'vpl_ms_product.product_id', '=', 'vpl_ms_product_detail.product_id') 
        //     ->select(
        //         'vpl_ms_product_detail.id',
        //         'vpl_ms_product.product_id', 
        //         'vpl_ms_product.product_name', 
        //         'vpl_ms_product.cpnyid', 
        //         'vpl_ms_product_detail.expired_date', 
        //         'vpl_ms_product_detail.qty_available',
        //         'vpl_ms_product_detail.whs_id'
        //     )
        //     ->whereIn('vpl_ms_product.cpnyid',$multicpny)
        //     ->orderby('vpl_ms_product_detail.expired_date','ASC')           
        //     ->get();   
        // $mswhs = Mswhsdept::whereIN('cpnyid',$multicpny)
        //     ->where('status','A')
        //     ->get(); 
        $userdept = Userdept::where('username', '=', $user->username)
            ->get();
        $userdept2 = Userdept::where('username', '=', $user->username)
            ->first();

        if ($vplrequest->requesttype =='Usage'){
            $requesttype ='Usage';
        } else if ($vplrequest->requesttype =='Return'){
            $requesttype ='Return';         
        } else {
            $requesttype ='Adjusment';
        }   
     

        return view('vplrequest.edit_vplrequest', compact('vplrequest','vplrequestdetail','t_attachment','usercpny','usercpny2','userdept','userdept2','requesttype','refid'));
    }

    public function deleteVplrequestDetail(Request $request)
    {
        $detailId = $request->input('detail_id');

        // Find and delete the record
        $detail = Vplrequestdetail::find($detailId);

        if ($detail) {
            $detail->delete();
            return response()->json(['message' => 'Detail deleted successfully.']);
        } else {
            return response()->json(['message' => 'Record not found.'], 404);
        }
    }

    public function deleteVplrequestAttach(Request $request)
    {
        $detailId = $request->input('detail_id');

        // Find and delete the record
        $detail = Attachment::find($detailId);

        if ($detail) {
            $detail->delete();
            return response()->json(['message' => 'Attachment deleted successfully.']);
        } else {
            return response()->json(['message' => 'Record not found.'], 404);
        }
    }
    public function updateVplrequest(Request $request)
    {
        // dd($request->all());
        $user = Auth::user();
        $datenow = Carbon::now()->format('Y-m-d');
        $datestamp = Carbon::now()->toDateTimeString();
        $dt = Carbon::now();
        $year = $dt->year;
        $month =  $dt->month; 

        $vplrequest = Vplrequest::find($request->idx);
        $vplrequestdetail = Vplrequestdetail::where('request_id', $vplrequest->request_id)  
            ->get();
        $refid = $request->refid; 
        $vplrequestdetail_temp = Vplrequestdetailtemp::where('refid', $refid)  
            ->get();
        $requesttype= $request->requesttype;  
        $qty_return = $request->qty_return ?? [];
        $purpose_id = $request->purpose_id ?? [];
        $purpose_remark = $request->purpose_remark ?? [];  
        
        // VLU	Voucher Loyalty Usage
        // PLU	Product Loyalty Usage
        // VRU	Voucher Loyalty Usage (RETURN)
        // PRU	Product Loyalty Usage (RETURN)
        $doctype = '';
        if($vplrequest->requesttype == 'Usage' && $vplrequest->vp_type == 'V' ) {
            $doctype = 'VLU';
        } else if  ($vplrequest->requesttype == 'Return' && $vplrequest->vp_type == 'V') {
            $doctype = 'VRU';
        } else if  ($vplrequest->requesttype == 'Usage' && $vplrequest->vp_type == 'P') {
            $doctype = 'PLU';
        } else if  ($vplrequest->requesttype == 'Return' && $vplrequest->vp_type == 'P') {
            $doctype = 'PRU';
        } else {$doctype = '';}

        if ($refid) {
            $lineNumber = 1;
            foreach ($vplrequestdetail_temp as $detail) {

                if ($requesttype === 'Return'){
                    $qtyToInsert = $qty_return[$detail['id']] ?? 0;
                    $currentPurposeId = $detail['purpose_id'] ?? '';
                    $currentPurposeRemark = $detail['purpose_remark'] ?? '';
                }else{
                    $qtyToInsert = $detail['qty_request'] ?? 0;                       
                    $currentPurposeId = $purpose_id[$detail['id']] ?? '';
                    $currentPurposeRemark = $purpose_remark[$detail['id']] ?? '';

                }
           
                // Insert into Vplrequestdetail
                // Vplrequestdetail::create([
                //     'request_id' => $vplrequest->request_id,
                //     'linenbr' => $lineNumber,
                //     'product_id' => $detail['product_id'],                      
                //     'qty_request' => $qtyToInsert,
                //     'expired_date' => $detail['expired_date'],
                //     'whs_id' => $detail['whs_id'],             
                //     'purpose_id' => $currentPurposeId,    
                //     'purpose_remark' => $currentPurposeRemark,     
                //     'ref_request_id' => $request->request_id,
                //     'status' => 'P',
                //     'created_user' => $user->username,
                //     'created_at' => $datestamp,
                // ]);
                $existingDetail = Vplrequestdetail::where('request_id', $vplrequest->request_id)
                    ->where('product_id', $detail['product_id'])
                    ->where('expired_date', $detail['expired_date'])
                    ->where('purpose_id', $currentPurposeId)
                    ->first();
                
                if ($existingDetail) {
                    // Update the existing record
                    $existingDetail->update([
                        'qty_request' => $existingDetail->qty_request + $qtyToInsert,
                        'purpose_remark' => $currentPurposeRemark,
                        'updated_user' => $user->username,
                        'updated_at' => $datestamp,
                    ]);
                } else {
                    // Create a new record
                    Vplrequestdetail::create([
                        'request_id' => $vplrequest->request_id,
                        'linenbr' => $lineNumber,
                        'product_id' => $detail['product_id'],
                        'qty_request' => $qtyToInsert,
                        'expired_date' => $detail['expired_date'],
                        'whs_id' => $detail['whs_id'],
                        'purpose_id' => $currentPurposeId,
                        'purpose_remark' => $currentPurposeRemark,
                        'ref_request_id' => $request->request_id,
                        'status' => 'P',
                        'created_user' => $user->username,
                        'created_at' => $datestamp,
                    ]);
                }
                
                $lineNumber++;
            }
        }
        $id = $request->idx;
        app('App\Http\Controllers\VplrequestController')->update_qty_reserved($id);    

        $m_approval = M_approval::where('aprvdoctype', $doctype)             
            ->where('aprvcpnyid', $request->cpnyid)
            ->where('aprvdeptid', $request->department)
            ->where('status', 'A')
            ->get();
           

        //insert trx_approval
        foreach ($m_approval as $mp) {
            $aprvdatebefore = ($mp->aprvid == 1) ? $datestamp : null; 
            T_approval::create([
                'docid' => $vplrequest->request_id,
                'aprvid' => $mp->aprvid,
                'aprvdoctype' => $mp->aprvdoctype,
                'aprvcpnyid' => $mp->aprvcpnyid,
                'aprvdeptid' => $mp->aprvdeptid,
                'aprvusername' => $mp->aprvusername,                  
                'name' => $mp->name,
                'aprvdatebefore' => $aprvdatebefore,
                'aprvtotalday' => 1,
                'status' => 'P',
                'created_user' => $user->name
            ]);
        }
            
        if ($request->hasfile('attachment')) {
            foreach ($request->file('attachment') as $file) {
                $randomNumber = random_int(100000, 999999);
                $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $attachfile = $randomNumber . '-' . $file->getClientOriginalName();

                //attach to folder                    
                $folder_attach = public_path() . '/attachment/'.$year;
                $config['upload_path'] = $folder_attach;                   
                if(!is_dir($folder_attach))
                {
                    mkdir($folder_attach, 0777);
                }
                // $folder_upload = public_path() . '/attachment';
                $folder_upload = $folder_attach;
                $file->move($folder_upload, $attachfile);

                //insert to table attachment
                $attach = new Attachment();
                $attach->docid = $vplrequest->request_id;
                $attach->name = $filename;
                $attach->attachfile = $attachfile;
                $attach->status = 'A';
                $attach->extention = $file->getClientOriginalExtension();
                $attach->created_user = $user->name;
                $attach->save();
            }
        }
      
        $vplrequest->cpnyid = $request->cpnyid;
        $vplrequest->department = $request->department;
        $vplrequest->vp_type = $request->vp_type;
        $vplrequest->requesttype = $request->requesttype;       
        $vplrequest->request_remark = $request->request_remark;      
        $vplrequest->status = 'P';        
        $vplrequest->updated_user = $user->name;
        $vplrequest->updated_at = $datestamp;
        $vplrequest->save();

         //read trx_approval next
        $t_approval_next = T_approval::where('docid', $vplrequest->request_id)
            ->where('status', 'P')
            ->orderby('aprvid','ASC')
            ->first();
        // dd($t_approval_next);    
        $ms_site = Site::where('id', $user->site)            
            ->first();
        
        //send email to it advice
        $data = array(
            'docid' => $t_approval_next->docid,
            'cpnyid' => $t_approval_next->aprvcpnyid,
            'deptname' => $t_approval_next->aprvdeptid,
            'locationname' => $ms_site->site,
            'date' => $t_approval_next->aprvdatebefore,
            'name' => $t_approval_next->created_user,
            'info' => $request->request_remark,           
            'url' => url('/showvplrequest_') . $request->idx

        );

        $multiapp = explode(',', $t_approval_next->aprvusername);

        $email_it = User::whereIN('username', $multiapp)
            ->where('status', 'A')
            ->get();

        foreach ($email_it as $emailsit) {
            Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

                $message->to($emailsit->test_email)->subject($data['docid'] . ' - Waiting Approval Usage');
                $message->from('digitalserver@pakuwon.com', 'Digital Approval System');
            });
        }
   

        return response()->json(['success' => 'Request saved successfully.']);
      
    }

    public function vplrequest_cancel($id)
    {
        //process it checked        
        $user = Auth::user();        
        $vplrequest = Vplrequest::find($id);
       
        $vplrequest->status = 'X';
        $vplrequest->updated_user = $user->name;
        $vplrequest->save();

        $approval = T_approval::where('docid', $vplrequest->request_id)
                ->where('status', 'P')                
                ->get();

        foreach ($approval as $t_approval) {            
            $t_approval->status = 'X';
            $t_approval->aprvdatebefore = null;
            $t_approval->save();
        }    

        app('App\Http\Controllers\VplrequestController')->cancel_msproduct_detail($id);

        return redirect('/vplrequest_waiting')->with('message', 'Process Cancel Successfully');
    }

    public function insert_msproduct_detail(int $id)
    {
            
        $user = Auth::user();    
        $datestamp = Carbon::now()->toDateTimeString();
        $vplrequest = Vplrequest::find($id);    
        
        $vplrequestdetail = Vplrequestdetail::where('request_id', $vplrequest->request_id)                            
            ->get();
        // dd($vplrequestdetail);
        foreach ($vplrequestdetail as $detail) {            
            $msProductDetail = Msproductdetail::where('product_id', $detail['product_id'])
                ->where('expired_date', $detail['expired_date'])
                ->where('whs_id', $detail['whs_id'])               
                ->first();               
                
            // If record exists, update qty_available by adding the new qty
            if($vplrequest->requesttype == 'Usage'){
                $msProductDetail->qty_available -= $detail['qty_request'];  
                $msProductDetail->qty_reserved -= $detail['qty_request'];  
            }else{
                $msProductDetail->qty_available += $detail['qty_request'];
                $msProductDetail->qty_reserved += $detail['qty_request'];
            }
                
            $msProductDetail->updated_user = $user->username;              
            $msProductDetail->updated_at = $datestamp;
            $msProductDetail->save();
                    
               
        }    
        
    }

    public function cancel_msproduct_detail(int $id)
    {
            
        $user = Auth::user();    
        $datestamp = Carbon::now()->toDateTimeString();
        $vplrequest = Vplrequest::find($id);    
        
        $vplrequestdetail = Vplrequestdetail::where('request_id', $vplrequest->request_id)                            
            ->get();
        // dd($vplrequestdetail);
        foreach ($vplrequestdetail as $detail) {            
            $msProductDetail = Msproductdetail::where('product_id', $detail['product_id'])
                ->where('expired_date', $detail['expired_date'])
                ->where('whs_id', $detail['whs_id'])               
                ->first();           
                 
                if($vplrequest->requesttype == 'Usage'){
                    // $msProductDetail->qty_available -= $detail['qty_request'];  
                    $msProductDetail->qty_reserved -= $detail['qty_request'];  
                }else{
                    // $msProductDetail->qty_available += $detail['qty_request'];
                    $msProductDetail->qty_reserved += $detail['qty_request'];
                }
                    
                $msProductDetail->updated_user = $user->username;              
                $msProductDetail->updated_at = $datestamp;
                $msProductDetail->save();
        }    
        
    }

    public function update_qty_reserved(int $id)
    {
        
        $user = Auth::user();    
        $datestamp = Carbon::now()->toDateTimeString();
        $vplrequest = Vplrequest::find($id);    
        
        $vplrequestdetail = Vplrequestdetail::where('request_id', $vplrequest->request_id)                            
            ->get();
        // dd($vplrequestdetail);
        foreach ($vplrequestdetail as $detail) {            
            
            // Check if the record already exists in msproductdetail
            $msProductDetail = Msproductdetail::where('product_id', $detail['product_id'])
                ->where('expired_date', $detail['expired_date'])
                ->where('whs_id', $detail['whs_id'])
                ->first();

            if($vplrequest->requesttype=='Usage'){                                
                
                // If record exists, update qty_available by adding the new qty
                $msProductDetail->qty_reserved += $detail['qty_request'];
                $msProductDetail->updated_at = $datestamp;
                $msProductDetail->save();
                
            }else{                   
                // If record exists, update qty_available by adding the new qty
                $msProductDetail->qty_reserved -= $detail['qty_request'];
                $msProductDetail->updated_at = $datestamp;
                $msProductDetail->save();

            }   
                
        }    
        
    }

    public function generate_pdf(int $id)    
    {   
        // $id= 6171;
       
        $payment = Payment::join('ms_payment_groupbiaya', 'trx_payment.groupbiaya', '=', 'ms_payment_groupbiaya.id')
            ->select('trx_payment.*', 'ms_payment_groupbiaya.groupbiayadescr')      
            ->where('trx_payment.id',$id)                
            ->first();
            // dd($payment);

        $trdetail = Payment_detail::where('docid', $payment->docid)
            ->where('status', 'A')
            ->get();

        $t_approval = T_approval::where('docid', $payment->docid)
            ->where('status', '<>','X')
            ->get();
        $company = Company::where('cpnyid', $payment->cpnyid)->first();
        $date = $payment->created_at->format(' d F Y ');
        $datediperlukan = date('d F Y', strtotime($payment->datediperlukan));  
        $paymentdate = date('d F Y', strtotime($payment->paymentdate));      
        $dt = Carbon::now();
        $year = $dt->year;
        $dept_prefix = Dept::where('deptname', $payment->deptname)           
            ->first();

        $approve_count = T_approval::where('docid', $payment->docid) 
            ->where('status', '<>','X')           
            ->count();
        if($payment->typepayment == 'RFP'){
            $typepayment = 'REQUEST FOR PAYMENT (RFP)';
        }else{
            $typepayment = 'REQUEST FOR CASH ADVANCE (RFCA)';
        }

        $email_to = User::where('username', $payment->user)
            ->where('status', 'A')
            ->first();
        // dd($email_to->test_email);
        $kepada = explode(',', $payment->imkepada);      
        $user_listx = User::where('status','A')
            ->whereIN('email',$kepada)
            ->get();                                          
        
        $data_user = [];
            foreach ($user_listx as $datauser) {
                foreach (explode(',', $datauser->name) as $value) {
                    $data_user[] = trim($value);
                }
            }
        $imkepada = implode(',', $data_user);

        $tembusan = explode(',', $payment->imtembusan);      
        $user_listx = User::where('status','A')
            ->whereIN('email',$tembusan)
            ->get();                                          
        
        $data_user = [];
            foreach ($user_listx as $datauser) {
                foreach (explode(',', $datauser->name) as $value) {
                    $data_user[] = trim($value);
                }
            }
        $imtembusan = implode(',', $data_user);

        $email_cc = User::where('status', 'A')
            ->where('groups', '10')            
            ->where('companyid', 'like', "%" . $payment->cpnyid . "%")
            ->get(); 
        
        $data_user = [];
        foreach ($email_cc as $datauser) {
            foreach (explode(',', $datauser->test_email) as $value) {
                $data_user[] = trim($value);
            }
        }
        $email_ccx = implode(',', $data_user);
        // dd($email_ccx);
        $data = [
            'dept_prefix'=> $dept_prefix->dept_prefix,
            'year'=> $year,
            'cpnyidx' => $payment->cpnyid,
            'cpnyid' => $company->cpnyname,
            'deptname' => $payment->deptname,
            'docid' => $payment->docid,
            'created_at' => $date,
            'user' => $payment->created_user,
            'locationname' => $payment->locationname,
            'typepayment' => $typepayment,            
            'datediperlukan' => $datediperlukan,
            'keperluan' => $payment->keperluan,
            'groupbiayadescr' => $payment->groupbiayadescr,    
            'pleasepayto' => $payment->pleasepayto,  
            'paymenttype' => $payment->paymenttype,    
            'paymentdate' =>  $paymentdate,
            'terbilang'=> terbilang($payment->amountrequestpayment),
            'amountrequestpayment' => number_format($payment->amountrequestpayment),       
            'req_date' => $payment->created_at,         
            'imkepada' => $imkepada,
            'imtembusan' => $imtembusan,
            'email_imkepada' => explode(',', $payment->imkepada),            
            'email_imtembusan' => explode(',', $payment->imtembusan),
            'email' => explode(',', $email_ccx.','.$email_to->test_email),
            'type' => $payment->typepayment,
            'url' => url('/showpayment_') . $id
        ];

        $pdf = PDF::loadview('payment.pdf_internalmemo', $data, ['trdetail' => $trdetail, 't_approval' => $t_approval, 'approve_count' => $approve_count]);
                
        // $email_cc = User::where('status', 'A')
        //     ->where('groups', '10')
        //     // ->where('companyid', $payment->cpnyid)
        //     ->where('companyid', 'like', "%" . $payment->cpnyid . "%")
        //     ->get();     
            
        // foreach ($email_cc as $emailcc) {
            Mail::send('emails.mailpaymentdone', $data, function ($message) use ($data, $pdf) {
                // Mail::send('emails.mailpayment', $data, function ($message) use ($data, $emailcc, $pdf) {
                $message->to($data['email'])->subject($data['docid'] . ' - Request Payment Done ');
                // $message->cc($emailcc->test_email);
                $message->from('digitalserver@pakuwon.com', 'Digital Approval System');
                $message->attachData($pdf->output(), $data['docid'] . ".pdf");
            });
        // }
    }
    
}
