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
use App\Models\Vplreceive;
use App\Models\Vplreceivedetail;
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

class VplreceiveController extends Controller
{

    public function add_vplreceive()
    {
        //add trx_voucher        
        $user = Auth::user();
        $usercpny = Usercpny::where('username', '=', $user->username)
            ->get();
        $usercpny2 = Usercpny::where('username', '=', $user->username)
            ->first();   
            
        $multicpny = explode(',', $user->companyid);                
               
        $msproduct = Msproduct::select('product_source_tenant')
            ->where('status', 'A')
            ->whereIn('cpnyid',$multicpny)
            ->distinct()
            ->get();   
            
        $mswhs = Mswhsdept::whereIN('cpnyid',$multicpny)
            ->where('status','A')
            ->get(); 
        $userdept = Userdept::where('username', '=', $user->username)
            ->get();
        $userdept2 = Userdept::where('username', '=', $user->username)
            ->first();
     

        return view('vplreceive.add_vplreceive', compact('usercpny','usercpny2','msproduct','mswhs','userdept','userdept2'));
    }

    // public function getProductDetails(Request $request)
    // {

    //     // dd($request->all());   
    //     $product = Msproduct::where('product_id', $request->product_id)            
    //         ->first();  
      
    //     return response()->json($product);
    // }

    
    // public function getProducts(Request $request)
    // {
    //     // $products = MsProduct::where('cpnyid', $request->cpnyid)->get();
    //     $products = MsProduct::select(
    //         'id', 'product_id','product_check_exp',
    //         DB::raw("CONCAT(product_name, ' / ', product_value, ' / ', product_uom) AS product_name")
    //     )
    //     ->where('cpnyid', $request->cpnyid)
    //     ->where('product_source_tenant', $request->product_source_tenant)
    //     ->get();
    //     return response()->json($products);
    // }
    public function getProducts(Request $request)
    {
        $products = MsProduct::select('id', 'product_id', 'product_check_exp', 'product_name', 'product_value', 'product_uom')
            ->where('cpnyid', $request->cpnyid)
            ->where('product_type', $request->vp_type)
            ->where('product_source_tenant', $request->product_source_tenant)
            ->where('status', 'A')
        
            ->get();

        // Format product_value secara manual
        $products->transform(function ($product) {
            $product->product_name = $product->product_name . ' / ' . number_format($product->product_value, 0, '.', ',') . ' / ' . $product->product_uom;
            return $product;
        });

        return response()->json($products);
    }


    public function getTenantsByCpnyid(Request $request)
    {
       
        // Query untuk filter berdasarkan cpnyid
        $tenants = MsProduct::where('cpnyid', $request->cpnyid)
            ->select('product_source_tenant')
            ->where('status', 'A')
            ->distinct()
            ->get();

        return response()->json($tenants);
    }

    public function getProductDetails(Request $request)
    {
        $product = MsProduct::where('product_id', $request->product_id)
            ->select('product_id', 'product_check_exp')
            ->first();

        return response()->json($product);
    }



    public function getWarehouse(Request $request)
    {
        // dd($request->all());
        $warehouse = Mswhsdept::where('cpnyid', $request->cpnyid)
            // ->where('department_id',$request->department)
            ->whereRaw('FIND_IN_SET(?, department_id)', [$request->department])      
            ->where('whs_type','Parent')
            ->where('vp_type',$request->vp_type)
            ->get();
            // dd($warehouse);
        return response()->json($warehouse);
    }


    public function saveVplreceive(Request $request)
    {
        // dd($request->all());
        $user = Auth::user();
        $datenow = Carbon::now()->format('Y-m-d');
        $datestamp = Carbon::now()->toDateTimeString();
        $dt = Carbon::now();
        $year = $dt->year;
        $month =  $dt->month;
         
        // VLR	Voucher Loyalty Receive  bbbbbbbbb
        // PLR	Product Loyalty Receive
        if ($request->vp_type == 'P') {
            $doctype = 'PLR';
        } else if ($request->vp_type == 'V') {
            $doctype = 'VLR';
        } else {
            $doctype = '';
        }

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

            $receive = Vplreceive::create([
                'receive_id' => $docid,
                'cpnyid' => $request->cpnyid,
                'department' => $request->department,                
                'vp_type' => $request->vp_type,                
                'receive_date' => $datenow,
                'receive_type' => $request->receive_type,
                'receive_company' => $request->receive_company,
                'receive_tenant' => $request->product_source_tenant,
                'source_receive_id' => $request->source_receive_id,
                'source_receive_dept' => $request->source_receive_dept,
                'receive_remark' => $request->receive_remark,  
                'user' => $user->username,             
                'status' => 'P',             
                'created_user' => $user->name
            ]);
            
            // Mengambil ID dari record yang baru saja dibuat
            $id = $receive->id; // Atau field yang menjadi primary key jika berbeda, misalnya 'receive_id'
            
            if ($request->has('addmore')) {
                $lineNumber = 1;
                foreach ($request->addmore as $detail) {
                    // Periksa apakah expired_date kosong atau null
                    $expiredDate = !empty($detail['expired_date']) ? $detail['expired_date'] : '1900-01-01';
            
                    // Insert into Vplreceivedetail
                    Vplreceivedetail::create([
                        'receive_id' => $docid,
                        'linenbr' => $lineNumber,
                        'product_id' => $detail['product_name'],
                        'qty_receive' => $detail['qty'],
                        'expired_date' => $expiredDate, // Gunakan expiredDate
                        'whs_id' => $detail['whs_id'],
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
            'info' => $request->receive_remark,           
            'url' => url('/showvplreceive_') . $id

        );

        $multiapp = explode(',', $t_approval_next->aprvusername);

        $email_it = User::whereIN('username', $multiapp)
            ->where('status', 'A')
            ->get();

        foreach ($email_it as $emailsit) {
            Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

                $message->to($emailsit->test_email)->subject($data['docid'] . ' - Waiting Approval Receive Product Voucher');
                $message->from('digitalserver@pakuwon.com', 'Digital Approval System');
            });
        }
       

            return response()->json(['success' => 'Receive saved successfully.']);
           
        }
        
    }
   
    public function approve($id, Request $request)
    {
        //update tr_vplreceive
        $vplreceive = Vplreceive::find($id);
        $user = Auth::user();
        $datestamp = Carbon::now()->toDateTimeString();
        $ms_site = Site::where('id', $user->site)            
            ->first();
        
        //update status completed tr_vplreceive
        $count_approval = T_approval::where('docid', '=', $vplreceive->receive_id)
            ->where('status', '=', 'P')
            ->count();
      
        //read trx_approval
        $t_approval = T_approval::where('docid', $vplreceive->receive_id)
            ->where('status', 'P')
            ->where('aprvusername', 'like', "%" . $user->username . "%")
            ->first();
        // dd($t_approval->status);    
        //update trx_approval 
        if ($t_approval == null){
            return redirect('/showvplreceive_' . $id);
        }else{
            $t_approval->status = 'A';
            $t_approval->aprvdateafter = $datestamp;
            $t_approval->aprvusername = $user->username;
            $t_approval->name = $user->name;
            $t_approval->save();
        }   

        //jika approval terakhir
        if ($count_approval == 1) {
            $vplreceive->status = 'C';
            $vplreceive->completed_user = $user->username;
            $vplreceive->completed_at = $datestamp;
            $vplreceive->save();            
            app('App\Http\Controllers\VplreceiveController')->insert_msproduct_detail($id);
            app('App\Http\Controllers\VplledgerController')->insert_ledger_from_receive($id);  
            //call generate pdf and send email
            // app('App\Http\Controllers\VplreceiveController')->generate_pdf($id);          
           
        }
        
        //read trx_approval next
        $t_approval_next = T_approval::where('docid', $vplreceive->receive_id)
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
                'info' => $vplreceive->receive_remark,               
                'url' => url('/showvplreceive_') . $id

            );

            $multiapp = explode(',', $t_approval_next->aprvusername);

            $email_it = User::whereIN('username', $multiapp)
                ->where('status', 'A')
                ->get();

            foreach ($email_it as $emailsit) {
                Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

                    $message->to($emailsit->test_email)->subject($data['docid'] . ' - Waiting Approval Receive Product/Voucher');
                    $message->from('digitalserver@pakuwon.com', 'Digital Approval System');
                });
            }
        }
        
        return redirect('/home')->with('message', 'Data Approved Successfully');
      
    }

    public function reject($id, Request $request)
    {
        if ($request->message == ''){
            return redirect('/showvplreceive_' . $id)->with('error', 'Message Empty, Please Entry Message');
        }
        //update tr_vplreceive
        $vplreceive = Vplreceive::find($id);
        $user = Auth::user();
        $datestamp = Carbon::now()->toDateTimeString();
        $ms_site = Site::where('id', $user->site)            
            ->first();

        //read trx_approval
        $t_approval = T_approval::where('docid', '=', $vplreceive->receive_id)
            ->where('status', '=', 'P')
            ->orderby('aprvid','ASC')
            ->first();

        $vplreceive->status = 'R';
        $vplreceive->save();

        //update trx_approval 
        $t_approval->status = 'R';
        $t_approval->aprvdateafter = $datestamp;
        // $t_approval->aprvusername = $user->email;
        $t_approval->aprvusername = $user->username;
        $t_approval->name = $user->name;
        $t_approval->save();

        $t_aprv_sisa = T_approval::where('docid', '=', $vplreceive->receive_id)
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
            'info' => $vplreceive->receive_remark,            
            'url' => url('/showvplreceive_') . $id

        );

        $email_it = User::where('username', $vplreceive->user)
                ->where('status', 'A')
                ->get();

        foreach ($email_it as $emailsit) {
            Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {
                $message->to($emailsit->test_email)->subject($data['docid'] . ' - Rejected Receive Product/Voucher');
                $message->from('digitalserver@pakuwon.com', 'Digital Approval System');
            });
        }

        app('App\Http\Controllers\VplreceiveController')->sendmsg($id,$request);
       
        return redirect('/home')->with('message', 'Data Rejected Successfully');
    }

    public function revise($id, Request $request)
    {
        if ($request->message == ''){
            return redirect('/showvplreceive_' . $id)->with('error', 'Message Empty, Please Entry Message');
        }
        //update tr_vplreceive
        $vplreceive = Vplreceive::find($id);
        $user = Auth::user();
        $datestamp = Carbon::now()->toDateTimeString();
        $ms_site = Site::where('id', $user->site)            
            ->first();

        //update status tr_vplreceive
        $vplreceive->status = 'D';
        $vplreceive->updated_user = $user->name;
        $vplreceive->updated_at = $datestamp;
        $vplreceive->save();

        //read trx_approval
        $t_approval = T_approval::where('docid', '=', $vplreceive->receive_id)
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
        $t_aprv_sisa = T_approval::where('docid', '=', $vplreceive->receive_id)
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
            'info' => $vplreceive->receive_remark . ' (Silahkan Revisi dengan cara klik link dibawah ini lalu klik tombol Edit lalu Submit/Cancel Document, Thanks)',
            'url' => url('/showvplreceive_') . $id

        );

        $email_it = User::where('username', $vplreceive->user)
                ->where('status', 'A')
                ->get();

        foreach ($email_it as $emailsit) {
            Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {
                $message->to($emailsit->test_email, '-')->subject($data['docid'] . ' - Revise Receive Product/Voucher');
                $message->from('digitalserver@pakuwon.com', 'Digital Approval System');
            });
        }

        app('App\Http\Controllers\VplreceiveController')->sendmsg($id,$request);
                
        return redirect('/home')->with('message', 'Data Revised Successfully');
    }

    //show data Trouble Report and trx_Approval
    public function show_vplreceive($id, Request $request)
    {
        $vplreceive = Vplreceive::find($id);
        $company = Company::where('status', 'A')->get();
        $user = Auth::user();
        $cek_role = User::where('name', $user->name)->first();
        //show all trx_approval
        $t_approval = T_approval::where('docid', $vplreceive->receive_id)
            ->where('status','<>','X')      
            ->orderBy('created_at')
            ->orderBy('aprvid')      
            ->get();

        //read status
        if ($vplreceive->status =='R'){
            $status_doc ='Rejected';
        } else if ($vplreceive->status =='C'){
            $status_doc ='Completed';
        } else if ($vplreceive->status =='D'){    
            $status_doc ='Hold';
        }else if($vplreceive->status =='X'){    
            $status_doc ='Cancel';    
        } else {
            $status_doc ='On Progress';
        }    

        //hidden button update,add, upload
      
        if($vplreceive->status == 'D' and $vplreceive->created_user == $user->name){
            $hidden = '';
        }else{
            // $hidden = 'hidden';
            $hidden = 'display:none';
        } 

        //cek for validasi button approval   
        if ($vplreceive->status == 'P') {           
            
            $trx_cek_like = T_approval::where('docid', $vplreceive->receive_id)
                ->where('status', 'P')
                ->where('aprvusername', 'like', "%" . $user->username . "%")                
                ->first();  
              
            if ($trx_cek_like == null or $trx_cek_like->aprvdatebefore == null) {
                $popup_approve = '#modal-warning';
                $popup_reject = '#modal-warning';
                $popup_revise = '#modal-warning';
                
            } else {
                $cek_approval = T_approval::where('docid', $vplreceive->receive_id)
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
        $vplreceivedetail = Vplreceivedetail::join('vpl_ms_product','vpl_trx_receive_detail.product_id','=','vpl_ms_product.product_id')
            ->select('vpl_trx_receive_detail.*','vpl_ms_product.product_name','vpl_ms_product.product_uom')
            ->where('receive_id', $vplreceive->receive_id)            
            ->get();
        
        //read attachment
        $t_attachment = Attachment::where('docid', $vplreceive->receive_id)
            ->where('status', 'A')
            ->get();
        //read message
        $t_message = T_Message::where('docid', $vplreceive->receive_id)
            ->where('status', 'A')
            ->get();
       
        $trx_cancel = T_approval::where('docid', $vplreceive->receive_id)
            ->where('status', 'P')           
            ->where('aprvid',1)
            ->count();  
             
        $tr_vplreceive = Vplreceive::where('status', 'D')  
            ->where('receive_id', $vplreceive->receive_id)
            ->count();    
       
        if (($trx_cancel == 1 || $tr_vplreceive == 1) && $vplreceive->created_user == $user->name) {
            // Show element if either condition matches and the created user is the same as the logged-in user
            $hiddenx = '';
        } else {
            // Hide the element
            $hiddenx = 'display:none';
        }
        return view('vplreceive.show_vplreceive', compact('vplreceive', 't_approval', 'vplreceivedetail', 'popup_approve', 'popup_reject', 'popup_revise', 't_attachment',  't_message', 'user', 'company','status_doc','hidden','hiddenx'));
    }


    
    public function vplreceive_waiting(Request $request)
    {     
           
        $tittle = 'On Progress Receive Product/Voucher';   
        $user = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $multidept = explode(',', $user->departmentid);

        if ($request->ajax()) {                   
            
            if ($user->role == 'admin') {                
                $data = Vplreceive::leftjoin('trx_approval', 'vpl_trx_receive.receive_id', '=', 'trx_approval.docid')                                      
                    ->select('vpl_trx_receive.*', 'trx_approval.name as waiting')
                    ->where('trx_approval.status', 'P')
                    ->where('trx_approval.aprvdatebefore', '<>',null)                        
                    ->get();           
            }else{
                $data = Vplreceive::leftjoin('trx_approval', 'vpl_trx_receive.receive_id', '=', 'trx_approval.docid')                                      
                    ->select('vpl_trx_receive.*', 'trx_approval.name as waiting')
                    ->where('trx_approval.status', 'P')
                    ->where('trx_approval.aprvdatebefore', '<>',null)   
                    ->whereIn('vpl_trx_receive.cpnyid', $multicpnyid)
                    ->whereIn('vpl_trx_receive.department', $multidept)                     
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
                
                ->addColumn('receive_id', function($row){         
                    $url = "/showvplreceive_$row->id";                                                            
                    $btn = '<a href="'.$url.'" class="btn btn-block" style="background-color: #3c87e2; color:white">'.$row->receive_id.'</a>';    
                    return $btn;
                })
                ->rawColumns(['status','receive_id'])                                           
                ->make(true);
        }
        return view('vplreceive.vplreceive_waiting', compact('tittle','user'));
    }

    public function vplreceive_completed(Request $request)
    {     
           
        $tittle = 'Completed Receive';   
        $user = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $multidept = explode(',', $user->departmentid);

        if ($request->ajax()) {                   
            
            if ($user->role == 'admin') {                
                $data = Vplreceive::where('status', 'C')                    
                    ->get();           
            }else{
                $data = Vplreceive::whereIn('cpnyid', $multicpnyid)
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
                    
                    ->addColumn('receive_id', function($row){         
                        $url = "/showvplreceive_$row->id";                                                            
                        $btn = '<a href="'.$url.'" class="btn btn-block" style="background-color: #3c87e2; color:white">'.$row->receive_id.'</a>';    
                        return $btn;
                    })
                    ->rawColumns(['status','receive_id'])                                           
                    ->make(true);
            }
        return view('vplreceive.vplreceive_completed', compact('user','tittle'));
    }
    
    public function vplreceive_rejected(Request $request)
    {     
           
        $tittle = 'Rejected Receive';   
        $user = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $multidept = explode(',', $user->departmentid);
       
        if ($request->ajax()) {                   
            
            if ($user->role == 'admin') {                
                $data = Vplreceive::where('status', 'R')                    
                    ->get();             
            }else{
                $data = Vplreceive::whereIn('cpnyid', $multicpnyid)
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
                    
                    ->addColumn('receive_id', function($row){         
                        $url = "/showvplreceive_$row->id";                                                            
                        $btn = '<a href="'.$url.'" class="btn btn-block" style="background-color: #3c87e2; color:white">'.$row->receive_id.'</a>';    
                        return $btn;
                    })
                    ->rawColumns(['status','receive_id'])                                           
                    ->make(true);
            }
        
        return view('vplreceive.vplreceive_rejected', compact('user','tittle'));
    }

    public function vplreceive_all(Request $request)
    {     
           
        $tittle = 'All Receive';   
        $user = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $multidept = explode(',', $user->departmentid);

        if ($request->ajax()) {                   
            
            if ($user->role == 'admin') {
                $data = Vplreceive::get();            
            }else{
                $data = Vplreceive::whereIn('cpnyid', $multicpnyid)
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
                    
                    ->addColumn('receive_id', function($row){         
                        $url = "/showvplreceive_$row->id";                                                            
                        $btn = '<a href="'.$url.'" class="btn btn-block" style="background-color: #3c87e2; color:white">'.$row->receive_id.'</a>';    
                        return $btn;
                    })
                    ->rawColumns(['status','receive_id'])                                           
                    ->make(true);
            }
        
        return view('vplreceive.vplreceive_all', compact('tittle','user'));
    }
    public function sendmsg_ajax(Request $request, $id)
    {

        $user = Auth::user();
        $vplreceive = Vplreceive::find($id);

        // VLR	Voucher Loyalty Receive  bbbbbbbbb
        // PLR	Product Loyalty Receive
        if ($vplreceive->vp_type == 'P') {
            $doctype = 'PLR';
        } else if ($vplreceive->vp_type == 'V') {
            $doctype = 'VLR';
        } else {
            $doctype = '';
        }

        $data = new T_Message();
        $data->docid = $vplreceive->receive_id;
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
        $vplreceive = Vplreceive::find($id);

        // VLR	Voucher Loyalty Receive  bbbbbbbbb
        // PLR	Product Loyalty Receive
        if ($vplreceive->vp_type == 'P') {
            $doctype = 'PLR';
        } else if ($vplreceive->vp_type == 'V') {
            $doctype = 'VLR';
        } else {
            $doctype = '';
        }

        //save trx_message bbbb
        T_Message::create([
            'docid' => $vplreceive->receive_id,
            'doctype' => $doctype,
            'username' => $user->username,
            'name' => $user->name,
            'message' => $request->message,
            'created_user' => $user->name,
            'status' => 'A'
        ]);

        return redirect('/showvplreceive_' . $id);
    }

    public function print_vplreceive_pdf(int $id)
    {
        $vplreceive = Vplreceive::find($id);
     
        $vplreceivedetail = Vplreceivedetail::join('vpl_ms_product','vpl_trx_receive_detail.product_id','=','vpl_ms_product.product_id')
            ->select('vpl_trx_receive_detail.*','vpl_ms_product.product_name')
            ->where('receive_id', $vplreceive->receive_id)            
            ->get();
       
        $t_approval = T_approval::where('docid', $vplreceive->receive_id)
            ->where('status','<>','X')   
            ->orderBy('created_at')
            ->orderBy('aprvid')         
            ->get();
        $company = Company::where('cpnyid', $vplreceive->cpnyid)->first();
        $date = $vplreceive->created_at->format(' d F Y ');            

        $approve_count = T_approval::where('docid', $vplreceive->receive_id) 
            ->where('status', '<>','X')           
            ->count();
      
        $data = [
            'cpnyid' => $company->cpnyname,
            'parent' => $company->parent,
            'project' => $company->project,
            'department' => $vplreceive->department,
            'docid' => $vplreceive->receive_id,
            'receive_type' => $vplreceive->receive_type,
            'receive_tenant' => $vplreceive->receive_tenant,
            'source_receive_dept' => $vplreceive->source_receive_dept,
            'created_at' => $date,
            'user' => $vplreceive->created_user,                      
            'receive_remark' => $vplreceive->receive_remark,               
            'req_date' => $vplreceive->created_at,            
        ];


        $pdf = PDF::loadview('vplreceive.show_vplreceive_pdf', $data, ['vplreceivedetail' => $vplreceivedetail, 't_approval' => $t_approval, 'approve_count' => $approve_count]);
        return $pdf->stream("pdf_vplreceive.pdf");


    }

    public function edit_vplreceive(int $id)
    {
        $vplreceive = Vplreceive::find($id);
        $vplreceivedetail = Vplreceivedetail::join('vpl_ms_product','vpl_trx_receive_detail.product_id','=','vpl_ms_product.product_id')
            ->select('vpl_trx_receive_detail.*','vpl_ms_product.product_name')
            ->where('receive_id', $vplreceive->receive_id)            
            ->get();
        $t_attachment = Attachment::where('docid', $vplreceive->receive_id)
            ->where('status', 'A')
            ->get();
             
        $user = Auth::user();
        $usercpny = Usercpny::where('username', '=', $user->username)
            ->get();
        $usercpny2 = Usercpny::where('username', '=', $user->username)
            ->first();   
            
        $multicpny = explode(',', $user->companyid);                
               
        $msproduct = Msproduct::where('status', 'A')
            ->whereIn('cpnyid',$multicpny)
            ->get();   
        $mswhs = Mswhsdept::whereIN('cpnyid',$multicpny)
            ->where('status','A')
            ->get(); 
        $userdept = Userdept::where('username', '=', $user->username)
            ->get();
        $userdept2 = Userdept::where('username', '=', $user->username)
            ->first();
     

        return view('vplreceive.edit_vplreceive', compact('vplreceive','vplreceivedetail','t_attachment','usercpny','usercpny2','msproduct','mswhs','userdept','userdept2'));
    }

    public function deleteVplreceiveDetail(Request $request)
    {
        $detailId = $request->input('detail_id');

        // Find and delete the record
        $detail = Vplreceivedetail::find($detailId);

        if ($detail) {
            $detail->delete();
            return response()->json(['message' => 'Detail deleted successfully.']);
        } else {
            return response()->json(['message' => 'Record not found.'], 404);
        }
    }

    public function deleteVplreceiveAttach(Request $request)
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
    public function updateVplreceive(Request $request)
    {
        // dd($request->all());  bbbbbbbbb
        $user = Auth::user();
        $datenow = Carbon::now()->format('Y-m-d');
        $datestamp = Carbon::now()->toDateTimeString();
        $dt = Carbon::now();
        $year = $dt->year;
        $month =  $dt->month; 

        $vplreceive = Vplreceive::find($request->idx);
        
        // VLR	Voucher Loyalty Receive  bbbbbbbbb
        // PLR	Product Loyalty Receive
        if ($vplreceive->vp_type == 'P') {
            $doctype = 'PLR';
        } else if ($vplreceive->vp_type == 'V') {
            $doctype = 'VLR';
        } else {
            $doctype = '';
        }


        if ($request->has('addmore')) {
            
            foreach ($request->addmore as $detail) {
                $expiredDate = !empty($detail['expired_date']) ? $detail['expired_date'] : '1900-01-01';
                // Check if all the required fields are not null and not empty
                if (!is_null($detail['product_name']) && !is_null($detail['qty']) && !is_null($detail['whs_id']) &&
                    !empty($detail['product_name']) && !empty($detail['qty'])  && !empty($detail['whs_id'])) {
                    
                    

                    // Check if the record already exists in Vplreceivedetail
                    $vplReceiveDetail = Vplreceivedetail::where('receive_id', $vplreceive->receive_id)
                        ->where('product_id', $detail['product_name'])
                        ->where('expired_date', $expiredDate)
                        ->where('whs_id', $detail['whs_id'])
                        ->first();
                                           

                    if ($vplReceiveDetail) {
                        // If record exists, update qty_receive by adding the new qty
                        $vplReceiveDetail->qty_receive += $detail['qty'];
                        $vplReceiveDetail->updated_at = $datestamp;
                        $vplReceiveDetail->save();
                    } else {
                        // If no matching record exists, insert a new record
                        Vplreceivedetail::create([
                            'receive_id' => $vplreceive->receive_id,
                            'linenbr' => 0,
                            'product_id' => $detail['product_name'],
                            'qty_receive' => $detail['qty'],
                            'expired_date' => $expiredDate,
                            'whs_id' => $detail['whs_id'],
                            'status' => 'P',
                            'created_user' => $user->username,
                            'created_at' => $datestamp,
                        ]);
                    }
        
                   
                }
            }
        
            // Now update the linenbr in the correct sequence for all records of this receive_id
            $vplReceiveDetails = Vplreceivedetail::where('receive_id', $vplreceive->receive_id)
                ->orderBy('created_at', 'asc') // Order by creation time to ensure correct ordering
                ->get();
        
            $lineNumber = 1; // Reset linenbr counter
        
            foreach ($vplReceiveDetails as $detail) {
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
                'docid' => $vplreceive->receive_id,
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
                // $randomNumber = random_int(100000, 999999);
                // $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                // $attachfile = $randomNumber . '-' . $file->getClientOriginalName();
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
                $attach->docid = $vplreceive->receive_id;
                $attach->name = $filename;
                $attach->attachfile = $attachfile;
                $attach->status = 'A';
                $attach->extention = $file->getClientOriginalExtension();
                $attach->created_user = $user->name;
                $attach->save();
            }
        }

        $vplreceive->cpnyid = $request->cpnyid;
        $vplreceive->department = $request->department;
        $vplreceive->receive_type = $request->receive_type;
        $vplreceive->receive_tenant = $request->product_source_tenant;
        $vplreceive->source_receive_dept = $request->source_receive_dept;
        $vplreceive->receive_remark = $request->receive_remark;      
        $vplreceive->status = 'P';        
        $vplreceive->updated_user = $user->name;
        $vplreceive->updated_at = $datestamp;
        $vplreceive->save();

         //read trx_approval next
        $t_approval_next = T_approval::where('docid', $vplreceive->receive_id)
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
            'info' => $request->receive_remark,           
            'url' => url('/showvplreceive_') . $request->idx

        );

        $multiapp = explode(',', $t_approval_next->aprvusername);

        $email_it = User::whereIN('username', $multiapp)
            ->where('status', 'A')
            ->get();

        foreach ($email_it as $emailsit) {
            Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

                $message->to($emailsit->test_email)->subject($data['docid'] . ' - Waiting Approval Receive Product Voucher');
                $message->from('digitalserver@pakuwon.com', 'Digital Approval System');
            });
        }
   

        return response()->json(['success' => 'Receive saved successfully.']);
      
    }

    public function vplreceive_cancel($id)
    {
        //process it checked        
        $user = Auth::user();        
        $vplreceive = Vplreceive::find($id);
       
        $vplreceive->status = 'X';
        $vplreceive->updated_user = $user->name;
        $vplreceive->save();

        $approval = T_approval::where('docid', $vplreceive->receive_id)
                ->where('status', 'P')                
                ->get();

        foreach ($approval as $t_approval) {            
            $t_approval->status = 'X';
            $t_approval->aprvdatebefore = null;
            $t_approval->save();
        }    
        return redirect('/vplreceive_waiting')->with('message', 'Process Cancel Successfully');
    }

    
    public function insert_msproduct_detail(int $id)
    {
            
        $user = Auth::user();    
        $datestamp = Carbon::now()->toDateTimeString();
        $vplreceive = Vplreceive::find($id);    
        
        $vplreceivedetail = Vplreceivedetail::where('receive_id', $vplreceive->receive_id)                            
            ->get();
        
        foreach ($vplreceivedetail as $detail) {            
             $msProductDetail = Msproductdetail::where('product_id', $detail['product_id'])
                ->where('expired_date', $detail['expired_date'])
                ->first();
    
                if ($msProductDetail) {
                    // If record exists, update qty_available by adding the new qty
                    $msProductDetail->qty_available += $detail['qty_receive'];
                    $msProductDetail->updated_user = $user->username; 
                    $msProductDetail->updated_at = $datestamp;
                    $msProductDetail->save();
                } else {
                    // If record does not exist, create a new record
                    Msproductdetail::create([
                        'product_id' => $detail['product_id'],
                        'expired_date' => $detail['expired_date'],
                        'cpnyid' => $vplreceive->cpnyid, // Ambil company ID dari request
                        'qty_available' => $detail['qty_receive'], // Insert new qty
                        'qty_reserved' => 0,                        
                        'whs_id' => $detail['whs_id'],
                        'status' => 'A',
                        'created_user' => $vplreceive->created_user,
                        'created_at' => $vplreceive->created_at,
                        'updated_user' => $user->username,
                        'updated_at' => $datestamp,
                    ]);
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
