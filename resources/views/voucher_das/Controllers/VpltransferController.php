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
use App\Models\Vpltransfer;
use App\Models\Vpltransferdetail;
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

class VpltransferController extends Controller
{

    public function add_vpltransfer()
    {
        //add trx_voucher        
        $user = Auth::user();
        $usercpny = Usercpny::where('username', '=', $user->username)
            ->get();
        $usercpny2 = Usercpny::where('username', '=', $user->username)
            ->first();   
            
        $multicpny = explode(',', $user->companyid);         

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
            ->whereIn('vpl_ms_product.cpnyid',$multicpny)
            ->orderby('vpl_ms_product_detail.expired_date','ASC')           
            ->get();
        $mswhs = Mswhsdept::whereIN('cpnyid',$multicpny)
            ->where('whs_type','Child') 
            ->where('status','A')
            ->get(); 
        $userdept = Userdept::where('username', '=', $user->username)
            ->get();
        $userdept2 = Userdept::where('username', '=', $user->username)
            ->first();
        // $vpltransfer = Vpltransfer::where('status', 'C')
        //     ->get();
        $vpltransfer = collect(); // ✅ kosong dulu

        return view('vpltransfer.add_vpltransfer', compact('usercpny','usercpny2','msproduct','mswhs','userdept','userdept2','vpltransfer'));
    }

    // VpltransferController.php
    public function getRefTransferOptions(Request $request)
    {
        $cpnyid      = $request->cpnyid;
        $department  = $request->department;
        $vp_type     = $request->vp_type;       // 'V' atau 'P'
        $transfertype= $request->transfertype;  // harus ReturnTf

        if ($transfertype !== 'ReturnTf' || !$vp_type || !$cpnyid) {
            return response()->json([]);
        }

        // Ambil transfer yg Completed yg di dalamnya ada product dengan product_type = $vp_type
        $refs = Vpltransfer::where('status', 'C')
                ->where('cpnyid', $cpnyid)
                ->where('department', $department)
                ->where('vp_type', $vp_type)
                ->pluck('transfer_id');
        return response()->json($refs);
    }


    public function getProductsByTransferType(Request $request)
    {
        // dd($request->all());
        $user = Auth::user();
        $usercpny = Usercpny::where('username', '=', $user->username)
            ->get();
        $usercpny2 = Usercpny::where('username', '=', $user->username)
            ->first();   
          
        $cpnyid = $request->input('cpnyid');            
        $vp_type = $request->input('vp_type'); 
        $transfertype = $request->input('transfertype'); // Ambil tipe transfer dari request
        $warehouseId = $request->input('warehouseId'); 
        $refTtransfer = $request->input('refTtransfer');
              
        if($transfertype == 'Transfer'){
            // Query produk berdasarkan warehouse dan tipe transfer
            $msproduct = Msproduct::join('vpl_ms_product_detail', 'vpl_ms_product.product_id', '=', 'vpl_ms_product_detail.product_id')
                ->select(
                    'vpl_ms_product_detail.id',
                    'vpl_ms_product.product_id', 
                    DB::raw("CONCAT(vpl_ms_product.product_name, ' / ', vpl_ms_product.product_value, ' / ', vpl_ms_product.product_uom) AS product_name"),
                    'vpl_ms_product.cpnyid', 
                    'vpl_ms_product_detail.expired_date', 
                    'vpl_ms_product_detail.qty_available',
                    DB::raw("0 AS qty_transfer"), // Set qty_transfer sebagai dummy bernilai 0
                    'vpl_ms_product_detail.whs_id'
                )
                ->where('vpl_ms_product.cpnyid', $cpnyid)
                ->where('vpl_ms_product_detail.whs_id', $warehouseId) // Kondisi warehouse
                ->where('vpl_ms_product.product_type', $vp_type) // Type Voucher Product
                ->orderBy('vpl_ms_product_detail.expired_date', 'ASC')
                ->get();


        }else{
            $msproduct = Vpltransferdetail::join('vpl_trx_transfer', 'vpl_trx_transfer_detail.transfer_id', '=', 'vpl_trx_transfer.transfer_id')
                ->join('vpl_ms_product', 'vpl_trx_transfer_detail.product_id', '=', 'vpl_ms_product.product_id')    
                ->leftjoin('vpl_ms_product_detail', function($join) {
                    $join->on('vpl_trx_transfer_detail.product_id', '=', 'vpl_ms_product_detail.product_id')
                        ->on('vpl_trx_transfer_detail.expired_date', '=', 'vpl_ms_product_detail.expired_date') // Join dengan expired_date juga
                        ->on('vpl_trx_transfer_detail.to_whs_id', '=', 'vpl_ms_product_detail.whs_id'); 
                })
                ->select(
                    'vpl_trx_transfer_detail.id',
                    'vpl_trx_transfer_detail.product_id',                     
                    DB::raw("CONCAT(vpl_ms_product.product_name, ' / ', vpl_ms_product.product_value, ' / ', vpl_ms_product.product_uom) AS product_name"),
                    'vpl_trx_transfer.cpnyid', 
                    'vpl_trx_transfer_detail.expired_date', 
                    'vpl_ms_product_detail.qty_available',
                    'vpl_trx_transfer_detail.qty_transfer',
                    'vpl_trx_transfer_detail.to_whs_id AS whs_id'
                )
                ->where('vpl_trx_transfer.transfer_id', $refTtransfer)
                ->where('vpl_trx_transfer.cpnyid', $cpnyid)
                // ->where('vpl_ms_product_detail.whs_id', $warehouseId) // Kondisi warehouse
                ->where('vpl_ms_product.product_type', $vp_type) // Type Voucher Product
                ->orderBy('vpl_trx_transfer_detail.expired_date', 'ASC')
                ->get();
        }

        return response()->json($msproduct); // Kembalikan data produk sebagai JSON
    }


    // Controller
    public function getProducts(Request $request)
    {
        $products = MsProduct::where('cpnyid', $request->cpnyid)->get();
        return response()->json($products);
    }

    public function getToWhsOptionsTransfer(Request $request)
    {        
        $cpnyid = $request->cpnyid;
        $department = $request->department;
        $vp_type = $request->vp_type;
        $transfertype = $request->transfertype;
        $warehouseId = $request->warehouseId;

        if (!$cpnyid || !$department || !$vp_type || !$transfertype) {
            return response()->json(['error' => 'Missing parameters'], 400);
        }

        if($transfertype == 'Transfer' ){       
            // dd($request->all());         
            $mswhs = Mswhsdept::where('cpnyid', $cpnyid)
                ->where('status', 'A')
                ->where('whs_id','<>',$warehouseId)    
                ->where('vp_type', $vp_type)
                // ->where('department_id_transfer', $department) 
                ->whereRaw('FIND_IN_SET(?, department_id_transfer)', [$department])      
                ->get();
        }else if($transfertype == 'ReturnTf' ){
            $whs_type ='Parent';
            // dd($request->all());         
            $mswhs = Mswhsdept::where('cpnyid', $cpnyid)
                ->where('whs_type', $whs_type)
                ->where('vp_type', $vp_type)
                ->where('status', 'A')
                // ->where('whs_id','<>',$warehouseId)    
                // ->where('department_id_transfer', $department)       
                ->get();
            }
        return response()->json($mswhs);
    }

    public function getFromWhsOptionsTransfer(Request $request)
    {
        // dd($request->all());

        // if($request->transfertype == 'Transfer'){
        //     $whs_type ='Parent';
        // }else{
        //     $whs_type ='Child';
        // }
                
        // $mswhs = Mswhsdept::where('cpnyid', $request->cpnyid)
        //     ->where('whs_type', $whs_type)
        //     ->where('status', 'A')           
        //     ->first();
        // // dd($mswhs);
        // return response()->json($mswhs);

        $cpnyid = $request->cpnyid;
        $department = $request->department;
        $vp_type = $request->vp_type;
        $transfertype = $request->transfertype;
    
        if (!$cpnyid || !$department || !$vp_type || !$transfertype) {
            return response()->json(['error' => 'Missing parameters'], 400);
        }

        if($transfertype == 'Transfer' ){
            $whs_type ='Parent';
            
            $mswhs = Mswhsdept::where('cpnyid', $cpnyid)
            ->where('whs_type', $whs_type)
            ->where('vp_type', $vp_type)
            ->where('status', 'A')           
            ->first();        
        }else if($transfertype == 'ReturnTf' ){
             $whs_type ='Child';

            $mswhs = Mswhsdept::where('cpnyid', $cpnyid)
            ->where('whs_type', $whs_type)
            ->where('vp_type', $vp_type)
            // ->where('department_id_transfer', $department) 
            ->whereRaw('FIND_IN_SET(?, department_id_transfer)', [$department])      
            ->where('status', 'A')           
            ->first();
        }
                
        // dd($request->department);
        return response()->json($mswhs);
    }


    public function saveVpltransfer(Request $request)
    {
        // dd($request->all());
        $user = Auth::user();
        $datenow = Carbon::now()->format('Y-m-d');
        $datestamp = Carbon::now()->toDateTimeString();
        $dt = Carbon::now();
        $year = $dt->year;
        $month =  $dt->month;
        
        // VLT	Voucher Loyalty Transfer
        // PLT	Product Loyalty Transfer
        // VRT	Voucher Loyalty Transfer (RETURN)
        // PRT	Product Loyalty Transfer (RETURN)
        $doctype = '';
        if($request->transfertype == 'Transfer' && $request->vp_type == 'V') {
            $doctype = 'VLT';
        } else if  ($request->transfertype == 'ReturnTf' && $request->vp_type == 'V') {
            $doctype = 'VRT';
        } else if  ($request->transfertype == 'Transfer' && $request->vp_type == 'P') {
            $doctype = 'PLT';
        } else if  ($request->transfertype == 'ReturnTf' && $request->vp_type == 'P') {
            $doctype = 'PRT';
        } else {$doctype = '';}

        //cek ms Approval
        $count_approval = M_approval::where('status', 'A')
            ->where('aprvcpnyid', $request->cpnyid)
            ->where('aprvdeptid', $request->department)
            ->where('aprvdoctype', $doctype)
            ->count();
       
        if ($count_approval == 0) {
            return response()->json(['error' => 'Approval Empty, Please contact IT!'], 422); // 422 Unprocessable Entity
        } else {
            $autonbr = Autonbr::where('doctype', '=', $doctype)
                ->where('year', '=', $year)
                ->where('month', '=', $month)
                ->where('status', '=', 'A')
                ->first();
                // dd($autonbr);
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

            $transfer = Vpltransfer::create([
                'transfer_id' => $docid,
                'cpnyid' => $request->cpnyid,
                'department' => $request->department,       
                'vp_type' => $request->vp_type,                     
                'transfer_date' => $datenow,
                'transfertype' => $request->transfertype,               
                'transfer_remark' => $request->transfer_remark,  
                'ref_transfer_id' => $request->ref_transfer_id,
                'user' => $user->username,             
                'status' => 'P',             
                'created_user' => $user->name
            ]);
            
            // Mengambil ID dari record yang baru saja dibuat
            $id = $transfer->id; // Atau field yang menjadi primary key jika berbeda, misalnya 'transfer_id'
            
            if ($request->has('addmore')) {
                $lineNumber = 1;
                foreach ($request->addmore as $detail) {
                    // Insert into Vpltransferdetail
                    Vpltransferdetail::create([
                        'transfer_id' => $docid,
                        'linenbr' => $lineNumber,
                        'product_id' => $detail['product_id'],
                        'qty_available' => $detail['qty_available'],
                        'qty_transfer' => $detail['qty_transfer'],
                        'expired_date' => $detail['expired_date'],
                        'from_whs_id' => $detail['from_whs_id'],
                        'to_whs_id' => $detail['to_whs_id'],
                        'ref_transfer_id' => $request->ref_transfer_id,
                        'status' => 'P',
                        'created_user' => $user->username,
                        'created_at' => $datestamp,
                    ]);            
                   
                    // Increment lineNumber for each new row
                    $lineNumber++;
                }
            }

            $m_approval = M_approval::where('aprvdoctype', $doctype)             
                ->where('aprvcpnyid', $request->cpnyid)
                ->where('aprvdeptid', $request->department)
                ->where('status', 'A')
                ->get();
           

            //insert trx_approval
            foreach ($m_approval as $mp) {
                $aprvdatebefore = ($mp->aprvid == 1) ? $datestamp : null; 
                T_approval::create([
                    'docid' => $docid,
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
                    $attach->docid = $docid;
                    $attach->name = $filename;
                    $attach->attachfile = $attachfile;
                    $attach->status = 'A';
                    $attach->extention = $file->getClientOriginalExtension();
                    $attach->created_user = $user->name;
                    $attach->save();
                }
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
            'docid' => $t_approval_next->docid,
            'cpnyid' => $t_approval_next->aprvcpnyid,
            'deptname' => $t_approval_next->aprvdeptid,
            'locationname' => $ms_site->site,
            'date' => $t_approval_next->aprvdatebefore,
            'name' => $t_approval_next->created_user,
            'info' => $request->transfer_remark,           
            'url' => url('/showvpltransfer_') . $id

        );

        $multiapp = explode(',', $t_approval_next->aprvusername);

        $email_it = User::whereIN('username', $multiapp)
            ->where('status', 'A')
            ->get();

        foreach ($email_it as $emailsit) {
            Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {
                $message->to($emailsit->test_email)->subject($data['docid'] . ' - Waiting Approval Transfer');
                $message->from('digitalserver@pakuwon.com', 'Digital Approval System');
            });
        }       

            return response()->json(['success' => 'Transfer saved successfully.']);
           
        }
        
    }
   
    public function approve($id, Request $request)
    {
        //update tr_vpltransfer
        $vpltransfer = Vpltransfer::find($id);
        $user = Auth::user();
        $datestamp = Carbon::now()->toDateTimeString();
        $ms_site = Site::where('id', $user->site)            
            ->first();

        // Ambil daftar produk dari vpl_trx_request_detail
        $request_details = Vpltransferdetail::where('transfer_id', $vpltransfer->transfer_id)
            ->get();

        // Validasi ketersediaan stok di vpl_ms_product_detail
        foreach ($request_details as $detail) {          
            $product_stock = Msproductdetail::join('vpl_ms_product', 'vpl_ms_product_detail.product_id', '=', 'vpl_ms_product.product_id')
            ->where('vpl_ms_product_detail.product_id', $detail->product_id)
            ->where('vpl_ms_product_detail.expired_date', $detail->expired_date)
            ->where('vpl_ms_product_detail.whs_id', $detail->from_whs_id)
            ->select('vpl_ms_product_detail.qty_available', 'vpl_ms_product_detail.product_id','vpl_ms_product.product_name')
            ->first();
           
            if (!$product_stock || $product_stock->qty_available == 0) {
                return redirect()->back()->with('error', 'Approval gagal! Karena ' . $product_stock->product_name . ' dan Expired Date: ' . $detail->expired_date . ' tidak memiliki stok tersedia. Silahkan Reject / Revise');
            }
        }
        
        //update status completed tr_vpltransfer
        $count_approval = T_approval::where('docid', '=', $vpltransfer->transfer_id)
            ->where('status', '=', 'P')
            ->count();
      
        //read trx_approval
        $t_approval = T_approval::where('docid', $vpltransfer->transfer_id)
            ->where('status', 'P')
            ->where('aprvusername', 'like', "%" . $user->username . "%")
            ->first();
        // dd($t_approval->status);    
        //update trx_approval 
        if ($t_approval == null){
            return redirect('/showvpltransfer_' . $id);
        }else{
            $t_approval->status = 'A';
            $t_approval->aprvdateafter = $datestamp;
            $t_approval->aprvusername = $user->username;
            $t_approval->name = $user->name;
            $t_approval->save();
        }   

        //jika approval terakhir
        if ($count_approval == 1) {
            $vpltransfer->status = 'C';
            $vpltransfer->completed_user = $user->username;
            $vpltransfer->completed_at = $datestamp;
            $vpltransfer->save();
            app('App\Http\Controllers\VpltransferController')->insert_msproduct_detail($id);
            app('App\Http\Controllers\VplledgerController')->insert_ledger_from_transfer($id); 
            //call generate pdf and send email
            // app('App\Http\Controllers\VpltransferController')->generate_pdf($id);          
           
        }
        
        //read trx_approval next
        $t_approval_next = T_approval::where('docid', $vpltransfer->transfer_id)
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
                'info' => $vpltransfer->transfer_remark,               
                'url' => url('/showvpltransfer_') . $id

            );

            $multiapp = explode(',', $t_approval_next->aprvusername);

            $email_it = User::whereIN('username', $multiapp)
                ->where('status', 'A')
                ->get();

            foreach ($email_it as $emailsit) {
                Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

                    $message->to($emailsit->test_email)->subject($data['docid'] . ' - Waiting Approval Transfer');
                    $message->from('digitalserver@pakuwon.com', 'Digital Approval System');
                });
            }
        }
        
        return redirect('/home')->with('message', 'Data Approved Successfully');
      
    }

    public function reject($id, Request $request)
    {
        if ($request->message == ''){
            return redirect('/showvpltransfer_' . $id)->with('error', 'Message Empty, Please Entry Message');
        }
        //update tr_vpltransfer
        $vpltransfer = Vpltransfer::find($id);
        $user = Auth::user();
        $datestamp = Carbon::now()->toDateTimeString();
        $ms_site = Site::where('id', $user->site)            
            ->first();

        //read trx_approval
        $t_approval = T_approval::where('docid', '=', $vpltransfer->transfer_id)
            ->where('status', '=', 'P')
            ->orderby('aprvid','ASC')
            ->first();

        $vpltransfer->status = 'R';
        $vpltransfer->save();

        //update trx_approval 
        $t_approval->status = 'R';
        $t_approval->aprvdateafter = $datestamp;
        // $t_approval->aprvusername = $user->email;
        $t_approval->aprvusername = $user->username;
        $t_approval->name = $user->name;
        $t_approval->save();

        $t_aprv_sisa = T_approval::where('docid', '=', $vpltransfer->transfer_id)
            ->where('status', '=', 'P')
            ->get();

        foreach ($t_aprv_sisa as $t_aprv) {
            $t_aprv->status = 'X';
            $t_aprv->save();
        }

        //send email to it advice
        $data = array(
            'docid' => $t_approval->docid,
            'cpnyid' => $t_approval->aprvcpnyid,
            'deptname' => $t_approval->aprvdeptid,
            'locationname' => $ms_site->site,
            'date' => $t_approval->aprvdatebefore,
            'name' => $t_approval->created_user,
            'info' => $vpltransfer->transfer_remark,            
            'url' => url('/showvpltransfer_') . $id

        );

        $email_it = User::where('username', $vpltransfer->user)
                ->where('status', 'A')
                ->get();

        foreach ($email_it as $emailsit) {
            Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {
                $message->to($emailsit->test_email)->subject($data['docid'] . ' - Rejected Transfer');
                $message->from('digitalserver@pakuwon.com', 'Digital Approval System');
            });
        }

        app('App\Http\Controllers\VpltransferController')->sendmsg($id,$request);
       
        return redirect('/home')->with('message', 'Data Rejected Successfully');
    }

    public function revise($id, Request $request)
    {
        if ($request->message == ''){
            return redirect('/showvpltransfer_' . $id)->with('error', 'Message Empty, Please Entry Message');
        }
        //update tr_vpltransfer
        $vpltransfer = Vpltransfer::find($id);
        $user = Auth::user();
        $datestamp = Carbon::now()->toDateTimeString();
        $ms_site = Site::where('id', $user->site)            
            ->first();

        //update status tr_vpltransfer
        $vpltransfer->status = 'D';
        $vpltransfer->updated_user = $user->name;
        $vpltransfer->updated_at = $datestamp;
        $vpltransfer->save();

        //read trx_approval
        $t_approval = T_approval::where('docid', '=', $vpltransfer->transfer_id)
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
        $t_aprv_sisa = T_approval::where('docid', '=', $vpltransfer->transfer_id)
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
            'info' => $vpltransfer->transfer_remark . ' (Silahkan Revisi dengan cara klik link dibawah ini lalu klik tombol Edit lalu Submit/Cancel Document, Thanks)',
            'url' => url('/showvpltransfer_') . $id

        );

        $email_it = User::where('username', $vpltransfer->user)
                ->where('status', 'A')
                ->get();

        foreach ($email_it as $emailsit) {
            Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {
                $message->to($emailsit->test_email, '-')->subject($data['docid'] . ' - Revise Transfer');
                $message->from('digitalserver@pakuwon.com', 'Digital Approval System');
            });
        }

        app('App\Http\Controllers\VpltransferController')->sendmsg($id,$request);
                
        return redirect('/home')->with('message', 'Data Revised Successfully');
    }

    //show data Trouble Report and trx_Approval
    public function show_vpltransfer($id, Request $request)
    {
       
        $vpltransfer = Vpltransfer::find($id);
        $company = Company::where('status', 'A')->get();
        $user = Auth::user();
        $cek_role = User::where('name', $user->name)->first();
        //show all trx_approval
        $t_approval = T_approval::where('docid', $vpltransfer->transfer_id)
            ->where('status','<>','X')      
            ->orderBy('created_at')
            ->orderBy('aprvid')      
            ->get();

        //read status
        if ($vpltransfer->status =='R'){
            $status_doc ='Rejected';
        } else if ($vpltransfer->status =='C'){
            $status_doc ='Completed';
        } else if ($vpltransfer->status =='D'){    
            $status_doc ='Hold';
        }else if($vpltransfer->status =='X'){    
            $status_doc ='Cancel';    
        } else {
            $status_doc ='On Progress';
        }
        
        if ($vpltransfer->transfertype =='Transfer'){
            $transfertype ='Transfer';
        } else if ($vpltransfer->transfertype =='ReturnTf'){
            $transfertype ='Return Transfer';         
        } else {
            $transfertype ='';
        }    

        //hidden button update,add, upload
      
        if($vpltransfer->status == 'D' and $vpltransfer->created_user == $user->name){
            $hidden = '';
        }else{
            // $hidden = 'hidden';
            $hidden = 'display:none';
        } 

        //cek for validasi button approval   
        if ($vpltransfer->status == 'P') {           
            
            $trx_cek_like = T_approval::where('docid', $vpltransfer->transfer_id)
                ->where('status', 'P')
                ->where('aprvusername', 'like', "%" . $user->username . "%")                
                ->first();  
              
            if ($trx_cek_like == null or $trx_cek_like->aprvdatebefore == null) {
                $popup_approve = '#modal-warning';
                $popup_reject = '#modal-warning';
                $popup_revise = '#modal-warning';
                
            } else {
                $cek_approval = T_approval::where('docid', $vpltransfer->transfer_id)
                    ->where('status', '=', 'P')      
                    ->whereNotNull('aprvdatebefore')             
                    ->first();
                      
                $trx_cek_like2 = T_approval::where('aprvid', $cek_approval->aprvid)
                    ->where('aprvusername', 'like', "%" . $user->username . "%")
                    ->first();
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
        $vpltransferdetail = Vpltransferdetail::join('vpl_ms_product','vpl_trx_transfer_detail.product_id','=','vpl_ms_product.product_id')
            ->select('vpl_trx_transfer_detail.*','vpl_ms_product.product_name')
            ->where('transfer_id', $vpltransfer->transfer_id)            
            ->get();
         

        //read attachment
        $t_attachment = Attachment::where('docid', $vpltransfer->transfer_id)
            ->where('status', 'A')
            ->get();
        //read message
        $t_message = T_Message::where('docid', $vpltransfer->transfer_id)
            ->where('status', 'A')
            ->get();
       
        $trx_cancel = T_approval::where('docid', $vpltransfer->transfer_id)
            ->where('status', 'P')           
            ->where('aprvid',1)
            ->count();  
             
        $tr_vpltransfer = Vpltransfer::where('status', 'D')  
            ->where('transfer_id', $vpltransfer->transfer_id)
            ->count();    
       
        if (($trx_cancel == 1 || $tr_vpltransfer == 1) && $vpltransfer->created_user == $user->name) {
            // Show element if either condition matches and the created user is the same as the logged-in user
            $hiddenx = '';
        } else {
            // Hide the element
            $hiddenx = 'display:none';
        }
        return view('vpltransfer.show_vpltransfer', compact('vpltransfer', 't_approval', 'vpltransferdetail', 'popup_approve', 'popup_reject', 'popup_revise', 't_attachment',  't_message', 'user', 'company','status_doc','hidden','hiddenx','transfertype'));
    }
    
    public function vpltransfer_waiting(Request $request)
    {     
           
        $tittle = 'On Progress Transfer / Return';   
        $user = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $multidept = explode(',', $user->departmentid);

        if ($request->ajax()) {                   
            
            if ($user->role == 'admin') {                
                $data = Vpltransfer::leftjoin('trx_approval', 'vpl_trx_transfer.transfer_id', '=', 'trx_approval.docid')                                      
                    ->select('vpl_trx_transfer.*', 'trx_approval.name as waiting')
                    ->where('trx_approval.status', 'P')
                    ->where('trx_approval.aprvdatebefore', '<>',null)                        
                    ->get();     
            }else if ($user->groups == 18 || $user->groups == 6 || $user->groups == 19) {                
                $data = Vpltransfer::leftjoin('trx_approval', 'vpl_trx_transfer.transfer_id', '=', 'trx_approval.docid')                                      
                    ->select('vpl_trx_transfer.*', 'trx_approval.name as waiting')
                    ->where('trx_approval.status', 'P')
                    ->where('trx_approval.aprvdatebefore', '<>',null)    
                    ->whereIn('vpl_trx_transfer.cpnyid', $multicpnyid)                    
                    ->get();      
            }else{
                $data = Vpltransfer::leftjoin('trx_approval', 'vpl_trx_transfer.transfer_id', '=', 'trx_approval.docid')                                      
                    ->select('vpl_trx_transfer.*', 'trx_approval.name as waiting')
                    ->where('trx_approval.status', 'P')
                    ->where('trx_approval.aprvdatebefore', '<>',null)   
                    ->whereIn('vpl_trx_transfer.cpnyid', $multicpnyid)
                    ->whereIn('vpl_trx_transfer.department', $multidept)                     
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

                ->addColumn('transfertype', function($row) {
                    // Periksa nilai transfertype
                    if ($row->transfertype == 'Transfer') {
                        return 'Transfer';
                    } else if ($row->transfertype == 'ReturnTf') {
                        return 'Return Transfer';
                    } else {
                        return '';
                    }
                })
                
                ->addColumn('transfer_id', function($row){         
                    $url = "/showvpltransfer_$row->id";                                                            
                    $btn = '<a href="'.$url.'" class="btn btn-block" style="background-color: #3c87e2; color:white">'.$row->transfer_id.'</a>';    
                    return $btn;
                })
                ->rawColumns(['status','transfer_id','transfertype'])                                           
                ->make(true);
        }
        return view('vpltransfer.vpltransfer_waiting', compact('tittle','user'));
    }

    public function vpltransfer_completed(Request $request)
    {     
           
        $tittle = 'Completed Transfer / Return';   
        $user = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $multidept = explode(',', $user->departmentid);

        if ($request->ajax()) {                   
            
            if ($user->role == 'admin') {                
                $data = Vpltransfer::where('status', 'C')                    
                    ->get();      
            }else if ($user->groups == 18 || $user->groups == 6 || $user->groups == 19){
                $data = Vpltransfer::whereIn('cpnyid', $multicpnyid)                     
                    ->where('status', 'C')                 
                    ->get();       
            }else{
                $data = Vpltransfer::whereIn('cpnyid', $multicpnyid)
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

                ->addColumn('transfertype', function($row) {
                    // Periksa nilai transfertype
                    if ($row->transfertype == 'Transfer') {
                        return 'Transfer';
                    } else if ($row->transfertype == 'ReturnTf') {
                        return 'Return Transfer';
                    } else {
                        return '';
                    }
                })
                    
                ->addColumn('transfer_id', function($row){         
                    $url = "/showvpltransfer_$row->id";                                                            
                    $btn = '<a href="'.$url.'" class="btn btn-block" style="background-color: #3c87e2; color:white">'.$row->transfer_id.'</a>';    
                    return $btn;
                })
                ->rawColumns(['status','transfer_id','transfertype'])                                           
                ->make(true);
            }
        return view('vpltransfer.vpltransfer_completed', compact('user','tittle'));
    }
    
    public function vpltransfer_rejected(Request $request)
    {     
           
        $tittle = 'Rejected Transfer / Return';   
        $user = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $multidept = explode(',', $user->departmentid);
       
        if ($request->ajax()) {                   
            
            if ($user->role == 'admin') {                
                $data = Vpltransfer::where('status', 'R')                    
                    ->get();   
            }else if ($user->groups == 18 || $user->groups == 6 || $user->groups == 19){
                $data = Vpltransfer::whereIn('cpnyid', $multicpnyid)                    
                    ->where('status', 'R')                 
                    ->get();              
            }else{
                $data = Vpltransfer::whereIn('cpnyid', $multicpnyid)
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

                ->addColumn('transfertype', function($row) {
                    // Periksa nilai transfertype
                    if ($row->transfertype == 'Transfer') {
                        return 'Transfer';
                    } else if ($row->transfertype == 'ReturnTf') {
                        return 'Return Transfer';
                    } else {
                        return '';
                    }
                })
                    
                ->addColumn('transfer_id', function($row){         
                    $url = "/showvpltransfer_$row->id";                                                            
                    $btn = '<a href="'.$url.'" class="btn btn-block" style="background-color: #3c87e2; color:white">'.$row->transfer_id.'</a>';    
                    return $btn;
                })
                ->rawColumns(['status','transfer_id','transfertype'])                                           
                ->make(true);
            }
        
        return view('vpltransfer.vpltransfer_rejected', compact('user','tittle'));
    }

    public function vpltransfer_all(Request $request)
    {     
           
        $tittle = 'All Transfer / Return';   
        $user = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $multidept = explode(',', $user->departmentid);

        if ($request->ajax()) {                   
            
            if ($user->role == 'admin') {
                $data = Vpltransfer::get();      
            }else if ($user->groups == 18 || $user->groups == 6 || $user->groups == 19){
                $data = Vpltransfer::whereIn('cpnyid', $multicpnyid)                                    
                    ->get();        
            }else{
                $data = Vpltransfer::whereIn('cpnyid', $multicpnyid)
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

                ->addColumn('transfertype', function($row) {
                    // Periksa nilai transfertype
                    if ($row->transfertype == 'Transfer') {
                        return 'Transfer';
                    } else if ($row->transfertype == 'ReturnTf') {
                        return 'Return Transfer';
                    } else {
                        return '';
                    }
                })
                    
                ->addColumn('transfer_id', function($row){         
                    $url = "/showvpltransfer_$row->id";                                                            
                    $btn = '<a href="'.$url.'" class="btn btn-block" style="background-color: #3c87e2; color:white">'.$row->transfer_id.'</a>';    
                    return $btn;
                })
                ->rawColumns(['status','transfer_id','transfertype'])                                           
                ->make(true);
            }
        
        return view('vpltransfer.vpltransfer_all', compact('tittle','user'));
    }
    public function sendmsg_ajax(Request $request, $id)
    {
        $user = Auth::user();
        $vpltransfer = Vpltransfer::find($id);

        // VLT	Voucher Loyalty Transfer
        // PLT	Product Loyalty Transfer
        // VRT	Voucher Loyalty Transfer (RETURN)
        // PRT	Product Loyalty Transfer (RETURN)
        $doctype = '';
        if($vpltransfer->transfertype == 'Transfer' && $vpltransfer->vp_type == 'V') {
            $doctype = 'VLT';
        } else if  ($vpltransfer->transfertype == 'ReturnTf' && $vpltransfer->vp_type == 'V') {
            $doctype = 'VRT';
        } else if  ($vpltransfer->transfertype == 'Transfer' && $vpltransfer->vp_type == 'P') {
            $doctype = 'PLT';
        } else if  ($vpltransfer->transfertype == 'ReturnTf' && $vpltransfer->vp_type == 'P') {
            $doctype = 'PRT';
        } else {$doctype = '';}

        $data = new T_Message();
        $data->docid = $vpltransfer->transfer_id;
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
        $vpltransfer = Vpltransfer::find($id);
        
        $doctype = '';
        if($vpltransfer->transfertype == 'Transfer' && $vpltransfer->vp_type == 'V') {
            $doctype = 'VLT';
        } else if  ($vpltransfer->transfertype == 'ReturnTf' && $vpltransfer->vp_type == 'V') {
            $doctype = 'VRT';
        } else if  ($vpltransfer->transfertype == 'Transfer' && $vpltransfer->vp_type == 'P') {
            $doctype = 'PLT';
        } else if  ($vpltransfer->transfertype == 'ReturnTf' && $vpltransfer->vp_type == 'P') {
            $doctype = 'PRT';
        } else {$doctype = '';}
        
        //save trx_message
        T_Message::create([
            'docid' => $vpltransfer->transfer_id,
            'doctype' => $doctype,
            'username' => $user->username,
            'name' => $user->name,
            'message' => $request->message,
            'created_user' => $user->name,
            'status' => 'A'
        ]);

        return redirect('/showvpltransfer_' . $id);
    }

    public function print_vpltransfer_pdf(int $id)
    {
        $vpltransfer = Vpltransfer::find($id);
     
        $vpltransferdetail = Vpltransferdetail::join('vpl_ms_product','vpl_trx_transfer_detail.product_id','=','vpl_ms_product.product_id')
            ->select('vpl_trx_transfer_detail.*','vpl_ms_product.product_name')
            ->where('transfer_id', $vpltransfer->transfer_id)            
            ->get();
       
        $t_approval = T_approval::where('docid', $vpltransfer->transfer_id)
            ->where('status','<>','X')   
            ->orderBy('created_at')
            ->orderBy('aprvid')         
            ->get();
        $company = Company::where('cpnyid', $vpltransfer->cpnyid)->first();
        $date = $vpltransfer->created_at->format(' d F Y ');            

        $approve_count = T_approval::where('docid', $vpltransfer->transfer_id) 
            ->where('status', '<>','X')           
            ->count();
      
        $data = [
            'cpnyid' => $company->cpnyname,
            'parent' => $company->parent,
            'project' => $company->project,
            'department' => $vpltransfer->department,
            'docid' => $vpltransfer->transfer_id,
            'transfertype' => $vpltransfer->transfertype,            
            'created_at' => $date,
            'user' => $vpltransfer->created_user,                      
            'transfer_remark' => $vpltransfer->transfer_remark,               
            'req_date' => $vpltransfer->created_at,            
        ];

        $pdf = PDF::loadview('vpltransfer.show_vpltransfer_pdf', $data, ['vpltransferdetail' => $vpltransferdetail, 't_approval' => $t_approval, 'approve_count' => $approve_count]);
        return $pdf->stream("pdf_vpltransfer.pdf");

    }

    public function edit_vpltransfer(int $id)
    {
        $vpltransfer = Vpltransfer::find($id);
        $vpltransferdetail = Vpltransferdetail::join('vpl_ms_product','vpl_trx_transfer_detail.product_id','=','vpl_ms_product.product_id')
            ->select('vpl_trx_transfer_detail.*','vpl_ms_product.product_name')
            ->where('transfer_id', $vpltransfer->transfer_id)            
            ->get();
        $t_attachment = Attachment::where('docid', $vpltransfer->transfer_id)
            ->where('status', 'A')
            ->get();
             
        $user = Auth::user();
        $usercpny = Usercpny::where('username', '=', $user->username)
            ->get();
        $usercpny2 = Usercpny::where('username', '=', $user->username)
            ->first();   
            
        $multicpny = explode(',', $user->companyid);                
               
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
            ->whereIn('vpl_ms_product.cpnyid',$multicpny)
            ->orderby('vpl_ms_product_detail.expired_date','ASC')           
            ->get();   
        $mswhs = Mswhsdept::whereIN('cpnyid',$multicpny)
            ->where('status','A')
            ->get(); 
        $userdept = Userdept::where('username', '=', $user->username)
            ->get();
        $userdept2 = Userdept::where('username', '=', $user->username)
            ->first();

        $vpltransfer_completed = Vpltransfer::where('status', 'C')
            ->get();
     

        return view('vpltransfer.edit_vpltransfer', compact('vpltransfer','vpltransferdetail','t_attachment','usercpny','usercpny2','msproduct','mswhs','userdept','userdept2','vpltransfer_completed'));
    }

    public function deleteVpltransferDetail(Request $request)
    {
        $detailId = $request->input('detail_id');

        // Find and delete the record
        $detail = Vpltransferdetail::find($detailId);

        if ($detail) {
            $detail->delete();
            return response()->json(['message' => 'Detail deleted successfully.']);
        } else {
            return response()->json(['message' => 'Record not found.'], 404);
        }
    }

    public function deleteVpltransferAttach(Request $request)
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
    public function updateVpltransfer(Request $request)
    {
        // dd($request->all());
        $user = Auth::user();
        $datenow = Carbon::now()->format('Y-m-d');
        $datestamp = Carbon::now()->toDateTimeString();
        $dt = Carbon::now();
        $year = $dt->year;
        $month =  $dt->month; 

        $vpltransfer = Vpltransfer::find($request->idx);
        
        // VLT	Voucher Loyalty Transfer
        // PLT	Product Loyalty Transfer
        // VRT	Voucher Loyalty Transfer (RETURN)
        // PRT	Product Loyalty Transfer (RETURN)
        $doctype = '';
        if($vpltransfer->transfertype == 'Transfer' && $vpltransfer->vp_type == 'V') {
            $doctype = 'VLT';
        } else if  ($vpltransfer->transfertype == 'ReturnTf' && $vpltransfer->vp_type == 'V') {
            $doctype = 'VRT';
        } else if  ($vpltransfer->transfertype == 'Transfer' && $vpltransfer->vp_type == 'P') {
            $doctype = 'PLT';
        } else if  ($vpltransfer->transfertype == 'ReturnTf' && $vpltransfer->vp_type == 'P') {
            $doctype = 'PRT';
        } else {$doctype = '';}
              
        if ($request->has('addmore')) {

            foreach ($request->addmore as $detail) {
                // Check if all the required fields are not null and not empty
                if (!is_null($detail['product_id']) && !is_null($detail['qty_transfer']) && !is_null($detail['to_whs_id']) &&
                    !empty($detail['product_id']) && !empty($detail['qty_transfer']) && !empty($detail['to_whs_id'])) {
                    
                    // Check if the record already exists in Vpltransferdetail
                    $vplTransferDetail = Vpltransferdetail::where('transfer_id', $vpltransfer->transfer_id)
                        ->where('product_id', $detail['product_id'])
                        ->where('expired_date', $detail['expired_date'])
                        ->where('to_whs_id', $detail['to_whs_id'])
                        ->first();
                    
                    if ($vplTransferDetail) {
                        // If record exists, update qty_transfer by adding the new qty
                        $vplTransferDetail->qty_transfer += $detail['qty_transfer'];
                        $vplTransferDetail->updated_user = $user->username;
                        $vplTransferDetail->updated_at = $datestamp;
                        $vplTransferDetail->save();
                    } else {
                        // If no matching record exists, insert a new record
                        Vpltransferdetail::create([
                            'transfer_id' => $vpltransfer->transfer_id,
                            'linenbr' => 0,
                            'product_id' => $detail['product_id'],
                            'qty_available' => $detail['qty_available'],
                            'qty_transfer' => $detail['qty_transfer'],
                            'expired_date' => $detail['expired_date'],
                            'from_whs_id' => $detail['from_whs_id'],
                            'to_whs_id' => $detail['to_whs_id'],
                            'ref_transfer_id' => $request->ref_transfer_id,                            
                            'status' => 'P',
                            'created_user' => $user->username,
                            'created_at' => $datestamp,
                        ]);
                    }
        
                   
                }
            }
        
            // Now update the linenbr in the correct sequence for all records of this transfer_id
            $vplTransferDetails = Vpltransferdetail::where('transfer_id', $vpltransfer->transfer_id)
                ->orderBy('created_at', 'asc') // Order by creation time to ensure correct ordering
                ->get();
        
            $lineNumber = 1; // Reset linenbr counter
        
            foreach ($vplTransferDetails as $detail) {
                $detail->linenbr = $lineNumber;
                $detail->save(); // Save each updated line number
                $lineNumber++;
            }
        }
        
        $m_approval = M_approval::where('aprvdoctype', $doctype)             
            ->where('aprvcpnyid', $request->cpnyid)
            ->where('aprvdeptid', $request->department)
            ->where('status', 'A')
            ->get();
       
        //insert trx_approval
        foreach ($m_approval as $mp) {
            $aprvdatebefore = ($mp->aprvid == 1) ? $datestamp : null; 
            T_approval::create([
                'docid' => $vpltransfer->transfer_id,
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
                $randomNumber = random_int(10000000, 99999999);
                $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                // $attachfile = md5($randomNumber) . '-' . $file->getClientOriginalName();
                $originalName = str_replace('%', '', $file->getClientOriginalName());
                $attachfile = md5($randomNumber) . '-' . $originalName;

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
                $attach->docid = $vpltransfer->transfer_id;
                $attach->name = $filename;
                $attach->attachfile = $attachfile;
                $attach->status = 'A';
                $attach->extention = $file->getClientOriginalExtension();
                $attach->created_user = $user->name;
                $attach->save();
            }
        }

        $vpltransfer->cpnyid = $request->cpnyid;
        $vpltransfer->department = $request->department;
        $vpltransfer->vp_type = $request->vp_type;
        $vpltransfer->transfertype = $request->transfertype;     
        $vpltransfer->ref_transfer_id = $request->ref_transfer_id;  
        $vpltransfer->transfer_remark = $request->transfer_remark;     
        $vpltransfer->status = 'P';        
        $vpltransfer->updated_user = $user->name;
        $vpltransfer->updated_at = $datestamp;
        $vpltransfer->save();

         //read trx_approval next
        $t_approval_next = T_approval::where('docid', $vpltransfer->transfer_id)
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
            'info' => $request->transfer_remark,           
            'url' => url('/showvpltransfer_') . $request->idx

        );

        $multiapp = explode(',', $t_approval_next->aprvusername);

        $email_it = User::whereIN('username', $multiapp)
            ->where('status', 'A')
            ->get();

        foreach ($email_it as $emailsit) {
            Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

                $message->to($emailsit->test_email)->subject($data['docid'] . ' - Waiting Approval Transfer');
                $message->from('digitalserver@pakuwon.com', 'Digital Approval System');
            });
        }
   

        return response()->json(['success' => 'Transfer saved successfully.']);
      
    }

    public function vpltransfer_cancel($id)
    {
        //process it checked        
        $user = Auth::user();        
        $vpltransfer = Vpltransfer::find($id);
       
        $vpltransfer->status = 'X';
        $vpltransfer->updated_user = $user->name;
        $vpltransfer->save();

        $approval = T_approval::where('docid', $vpltransfer->transfer_id)
                ->where('status', 'P')                
                ->get();

        foreach ($approval as $t_approval) {            
            $t_approval->status = 'X';
            $t_approval->aprvdatebefore = null;
            $t_approval->save();
        }    
        return redirect('/vpltransfer_waiting')->with('message', 'Process Cancel Successfully');
    }

    public function insert_msproduct_detail(int $id)
    {
            
        $user = Auth::user();    
        $datestamp = Carbon::now()->toDateTimeString();
        $vpltransfer = Vpltransfer::find($id);    
        
        $vpltransferdetail = Vpltransferdetail::where('transfer_id', $vpltransfer->transfer_id)                            
            ->get();
        // dd($vpltransferdetail);
        foreach ($vpltransferdetail as $detail) {            
            $msProductDetail = Msproductdetail::where('product_id', $detail['product_id'])
                ->where('expired_date', $detail['expired_date'])
                ->where('whs_id', $detail['to_whs_id'])               
                ->first();
            $msProductDetail2 = Msproductdetail::where('product_id', $detail['product_id'])
                ->where('expired_date', $detail['expired_date'])
                ->where('whs_id', $detail['from_whs_id'])               
                ->first();
    
                if ($msProductDetail) {
                    // If record exists, update qty_available by adding the new qty
                    $msProductDetail->qty_available += $detail['qty_transfer'];                    
                    $msProductDetail->updated_at = $datestamp;
                    $msProductDetail->updated_user = $user->username;
                    $msProductDetail->save();
                    $msProductDetail2->qty_available -= $detail['qty_transfer'];                   
                    $msProductDetail2->updated_at = $datestamp;
                    $msProductDetail2->updated_user = $user->username;
                    $msProductDetail2->save();
                } else {
                    // If record does not exist, create a new record
                    Msproductdetail::create([
                        'product_id' => $detail['product_id'],
                        'expired_date' => $detail['expired_date'],
                        'cpnyid' => $vpltransfer->cpnyid, // Ambil company ID dari request
                        'qty_available' => $detail['qty_transfer'], // Insert new qty
                        'whs_id' => $detail['to_whs_id'],                       
                        'status' => 'A',
                        'created_user' => $vpltransfer->created_user,
                        'created_at' => $vpltransfer->created_at,
                        'updated_user' => $user->username,
                        'updated_at' => $datestamp,
                    ]);

                    $msProductDetail2->qty_available -= $detail['qty_transfer'];                   
                    $msProductDetail2->updated_at = $datestamp;
                    $msProductDetail2->save();
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
