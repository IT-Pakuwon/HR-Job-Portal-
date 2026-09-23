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
use App\Models\Vpladjustment;
use App\Models\Vpladjustmentdetail;
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

class VpladjustmentController extends Controller
{

    public function add_vpladjustment()
    {
        //add trx_voucher        
        $user = Auth::user();
        $usercpny = Usercpny::where('username', '=', $user->username)
            ->get();
        $usercpny2 = Usercpny::where('username', '=', $user->username)
            ->first();   
            
        $multicpny = explode(',', $user->companyid);                
               
        // $msproduct = Msproduct::where('status', 'A')
        //     ->whereIn('cpnyid',$multicpny)
        //     ->get();   

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
     

        return view('vpladjustment.add_vpladjustment', compact('usercpny','usercpny2','msproduct','mswhs','userdept','userdept2'));
    }

    public function getProductsByAdjustmentType(Request $request)
    {
        $user = Auth::user();
        $cpnyid = $request->input('cpnyid');           
    //    dd($cpnyid);
        // Query produk berdasarkan warehouse dan tipe adjustment
        $msproduct = Msproduct::join('vpl_ms_product_detail', 'vpl_ms_product.product_id', '=', 'vpl_ms_product_detail.product_id')
            ->select(
                'vpl_ms_product_detail.id',
                'vpl_ms_product.product_id', 
                // 'vpl_ms_product.product_name', 
                DB::raw("CONCAT(vpl_ms_product.product_name, ' / ', vpl_ms_product.product_value, ' / ', vpl_ms_product.product_uom) AS product_name"),
                'vpl_ms_product.cpnyid', 
                'vpl_ms_product_detail.expired_date', 
                'vpl_ms_product_detail.qty_available',
                'vpl_ms_product_detail.whs_id'
            )
            ->where('vpl_ms_product.cpnyid', $cpnyid)          
            ->orderby('vpl_ms_product_detail.expired_date', 'ASC')
            ->get();

        return response()->json($msproduct); // Kembalikan data produk sebagai JSON
    }


    // Controller
    public function getProducts(Request $request)
    {
        $products = MsProduct::where('cpnyid', $request->cpnyid)->get();
        return response()->json($products);
    }

    public function getToWhsOptionsAdjustment(Request $request)
    {
                
        $mswhs = Mswhsdept::where('cpnyid', $request->cpnyid)
            ->where('status', 'A')           
            ->get();

        return response()->json($mswhs);
    }


    public function saveVpladjustment(Request $request)
    {
        // dd($request->all());
        $user = Auth::user();
        $datenow = Carbon::now()->format('Y-m-d');
        $datestamp = Carbon::now()->toDateTimeString();
        $dt = Carbon::now();
        $year = $dt->year;
        $month =  $dt->month;       

        //cek ms Approval
        $count_approval = M_approval::where('status', 'A')
            ->where('aprvcpnyid', $request->cpnyid)
            ->where('aprvdeptid', $request->department)
            ->where('aprvdoctype', 'VPA')
            ->count();
       
        if ($count_approval == 0) {
            return response()->json(['error' => 'Approval Empty, Please contact IT!'], 422); // 422 Unprocessable Entity
        } else {
            $autonbr = Autonbr::where('doctype', '=', 'VPA')
                ->where('year', '=', $year)
                ->where('month', '=', $month)
                ->where('status', '=', 'A')
                ->first();

            $tglbln =  substr($dt->year, 2) . $autonbr->month;

            // $cek autonbr
            if ($autonbr->number == 0) {
                $urutan = 1;              
                $docid = 'VPA' . $tglbln . '00' . $urutan;
            } else {
                $urutan = $autonbr->number;
                $urutan++;               
                $docid = 'VPA' . $tglbln . sprintf("%03s", $urutan);
            }
            
            //update ms_autonbr
            $autonbr->number = $urutan;
            $autonbr->save();

            $adjustment = Vpladjustment::create([
                'adjustment_id' => $docid,
                'cpnyid' => $request->cpnyid,
                'department' => $request->department,                
                'adjustment_date' => $datenow,                       
                'adjustment_remark' => $request->adjustment_remark,  
                'user' => $user->username,             
                'status' => 'P',             
                'created_user' => $user->name
            ]);
            
            // Mengambil ID dari record yang baru saja dibuat
            $id = $adjustment->id; // Atau field yang menjadi primary key jika berbeda, misalnya 'adjustment_id'
            
            if ($request->has('addmore')) {
                $lineNumber = 1;
                foreach ($request->addmore as $detail) {
                    // Insert into Vpladjustmentdetail
                    Vpladjustmentdetail::create([
                        'adjustment_id' => $docid,
                        'linenbr' => $lineNumber,
                        'product_id' => $detail['product_id'],                       
                        'qty_adjustment' => $detail['qty_adjustment'],
                        'expired_date' => $detail['expired_date'],
                        'whs_id' => $detail['from_whs_id'],                     
                        'status' => 'P',
                        'created_user' => $user->username,
                        'created_at' => $datestamp,
                    ]);
            
                   
                    // Increment lineNumber for each new row
                    $lineNumber++;
                }
            }

            $m_approval = M_approval::where('aprvdoctype', 'VPA')             
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
            'info' => $request->adjustment_remark,           
            'url' => url('/showvpladjustment_') . $id

        );

        $multiapp = explode(',', $t_approval_next->aprvusername);

        $email_it = User::whereIN('username', $multiapp)
            ->where('status', 'A')
            ->get();

        foreach ($email_it as $emailsit) {
            Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

                $message->to($emailsit->test_email)->subject($data['docid'] . ' - Waiting Approval Adjustment');
                $message->from('digitalserver@pakuwon.com', 'Digital Approval System');
            });
        }       

            return response()->json(['success' => 'Adjustment saved successfully.']);
           
        }
        
    }
   
    public function approve($id, Request $request)
    {
        //update tr_vpladjustment
        $vpladjustment = Vpladjustment::find($id);
        $user = Auth::user();
        $datestamp = Carbon::now()->toDateTimeString();
        $ms_site = Site::where('id', $user->site)            
            ->first();
        
        //update status completed tr_vpladjustment
        $count_approval = T_approval::where('docid', '=', $vpladjustment->adjustment_id)
            ->where('status', '=', 'P')
            ->count();
      
        //read trx_approval
        $t_approval = T_approval::where('docid', $vpladjustment->adjustment_id)
            ->where('status', 'P')
            ->where('aprvusername', 'like', "%" . $user->username . "%")
            ->first();
        // dd($t_approval->status);    
        //update trx_approval 
        if ($t_approval == null){
            return redirect('/showvpladjustment_' . $id);
        }else{
            $t_approval->status = 'A';
            $t_approval->aprvdateafter = $datestamp;
            $t_approval->aprvusername = $user->username;
            $t_approval->name = $user->name;
            $t_approval->save();
        }   

        //jika approval terakhir
        if ($count_approval == 1) {
            $vpladjustment->status = 'C';
            $vpladjustment->completed_user = $user->username;
            $vpladjustment->completed_at = $datestamp;
            $vpladjustment->save();
            app('App\Http\Controllers\VpladjustmentController')->insert_msproduct_detail($id);
            app('App\Http\Controllers\VplledgerController')->insert_ledger_from_adjustment($id); 
            //call generate pdf and send email
            // app('App\Http\Controllers\VpladjustmentController')->generate_pdf($id);          
           
        }
        
        //read trx_approval next
        $t_approval_next = T_approval::where('docid', $vpladjustment->adjustment_id)
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
                'info' => $vpladjustment->adjustment_remark,               
                'url' => url('/showvpladjustment_') . $id

            );

            $multiapp = explode(',', $t_approval_next->aprvusername);

            $email_it = User::whereIN('username', $multiapp)
                ->where('status', 'A')
                ->get();

            foreach ($email_it as $emailsit) {
                Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

                    $message->to($emailsit->test_email)->subject($data['docid'] . ' - Waiting Approval Adjustment');
                    $message->from('digitalserver@pakuwon.com', 'Digital Approval System');
                });
            }
        }
        
        return redirect('/home')->with('message', 'Data Approved Successfully');
      
    }

    public function reject($id, Request $request)
    {
        if ($request->message == ''){
            return redirect('/showvpladjustment_' . $id)->with('error', 'Message Empty, Please Entry Message');
        }
        //update tr_vpladjustment
        $vpladjustment = Vpladjustment::find($id);
        $user = Auth::user();
        $datestamp = Carbon::now()->toDateTimeString();
        $ms_site = Site::where('id', $user->site)            
            ->first();

        //read trx_approval
        $t_approval = T_approval::where('docid', '=', $vpladjustment->adjustment_id)
            ->where('status', '=', 'P')
            ->orderby('aprvid','ASC')
            ->first();

        $vpladjustment->status = 'R';
        $vpladjustment->save();

        //update trx_approval 
        $t_approval->status = 'R';
        $t_approval->aprvdateafter = $datestamp;
        // $t_approval->aprvusername = $user->email;
        $t_approval->aprvusername = $user->username;
        $t_approval->name = $user->name;
        $t_approval->save();

        $t_aprv_sisa = T_approval::where('docid', '=', $vpladjustment->adjustment_id)
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
            'info' => $vpladjustment->adjustment_remark,            
            'url' => url('/showvpladjustment_') . $id

        );

        $email_it = User::where('username', $vpladjustment->user)
                ->where('status', 'A')
                ->get();

        foreach ($email_it as $emailsit) {
            Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {
                $message->to($emailsit->test_email)->subject($data['docid'] . ' - Rejected Adjustment');
                $message->from('digitalserver@pakuwon.com', 'Digital Approval System');
            });
        }

        app('App\Http\Controllers\VpladjustmentController')->sendmsg($id,$request);
       
        return redirect('/home')->with('message', 'Data Rejected Successfully');
    }

    public function revise($id, Request $request)
    {
        if ($request->message == ''){
            return redirect('/showvpladjustment_' . $id)->with('error', 'Message Empty, Please Entry Message');
        }
        //update tr_vpladjustment
        $vpladjustment = Vpladjustment::find($id);
        $user = Auth::user();
        $datestamp = Carbon::now()->toDateTimeString();
        $ms_site = Site::where('id', $user->site)            
            ->first();

        //update status tr_vpladjustment
        $vpladjustment->status = 'D';
        $vpladjustment->updated_user = $user->name;
        $vpladjustment->updated_at = $datestamp;
        $vpladjustment->save();

        //read trx_approval
        $t_approval = T_approval::where('docid', '=', $vpladjustment->adjustment_id)
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
        $t_aprv_sisa = T_approval::where('docid', '=', $vpladjustment->adjustment_id)
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
            'info' => $vpladjustment->adjustment_remark . ' (Silahkan Revisi dengan cara klik link dibawah ini lalu klik tombol Edit lalu Submit/Cancel Document, Thanks)',
            'url' => url('/showvpladjustment_') . $id

        );

        $email_it = User::where('username', $vpladjustment->user)
                ->where('status', 'A')
                ->get();

        foreach ($email_it as $emailsit) {
            Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {
                $message->to($emailsit->test_email, '-')->subject($data['docid'] . ' - Revise Adjustment');
                $message->from('digitalserver@pakuwon.com', 'Digital Approval System');
            });
        }

        app('App\Http\Controllers\VpladjustmentController')->sendmsg($id,$request);
                
        return redirect('/home')->with('message', 'Data Revised Successfully');
    }

    //show data Trouble Report and trx_Approval
    public function show_vpladjustment($id, Request $request)
    {
       
        $vpladjustment = Vpladjustment::find($id);
        $company = Company::where('status', 'A')->get();
        $user = Auth::user();
        $cek_role = User::where('name', $user->name)->first();
        //show all trx_approval
        $t_approval = T_approval::where('docid', $vpladjustment->adjustment_id)
            ->where('status','<>','X')      
            ->orderBy('created_at')
            ->orderBy('aprvid')      
            ->get();

        //read status
        if ($vpladjustment->status =='R'){
            $status_doc ='Rejected';
        } else if ($vpladjustment->status =='C'){
            $status_doc ='Completed';
        } else if ($vpladjustment->status =='D'){    
            $status_doc ='Hold';
        }else if($vpladjustment->status =='X'){    
            $status_doc ='Cancel';    
        } else {
            $status_doc ='On Progress';
        }
                

        //hidden button update,add, upload
      
        if($vpladjustment->status == 'D' and $vpladjustment->created_user == $user->name){
            $hidden = '';
        }else{
            // $hidden = 'hidden';
            $hidden = 'display:none';
        } 

        //cek for validasi button approval   
        if ($vpladjustment->status == 'P') {           
            
            $trx_cek_like = T_approval::where('docid', $vpladjustment->adjustment_id)
                ->where('status', 'P')
                ->where('aprvusername', 'like', "%" . $user->username . "%")                
                ->first();  
              
            if ($trx_cek_like == null or $trx_cek_like->aprvdatebefore == null) {
                $popup_approve = '#modal-warning';
                $popup_reject = '#modal-warning';
                $popup_revise = '#modal-warning';
                
            } else {
                $cek_approval = T_approval::where('docid', $vpladjustment->adjustment_id)
                    ->where('status', '=', 'P')                   
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
        $vpladjustmentdetail = Vpladjustmentdetail::join('vpl_ms_product','vpl_trx_adjustment_detail.product_id','=','vpl_ms_product.product_id')
            ->select('vpl_trx_adjustment_detail.*','vpl_ms_product.product_name')
            ->where('adjustment_id', $vpladjustment->adjustment_id)            
            ->get();
       
        //read attachment
        $t_attachment = Attachment::where('docid', $vpladjustment->adjustment_id)
            ->where('status', 'A')
            ->get();
        //read message
        $t_message = T_Message::where('docid', $vpladjustment->adjustment_id)
            ->where('status', 'A')
            ->get();
       
        $trx_cancel = T_approval::where('docid', $vpladjustment->adjustment_id)
            ->where('status', 'P')           
            ->where('aprvid',1)
            ->count();  
             
        $tr_vpladjustment = Vpladjustment::where('status', 'D')  
            ->where('adjustment_id', $vpladjustment->adjustment_id)
            ->count();    
       
        if (($trx_cancel == 1 || $tr_vpladjustment == 1) && $vpladjustment->created_user == $user->name) {
            // Show element if either condition matches and the created user is the same as the logged-in user
            $hiddenx = '';
        } else {
            // Hide the element
            $hiddenx = 'display:none';
        }
        $adjustmenttype = 'Adjustment';
        return view('vpladjustment.show_vpladjustment', compact('vpladjustment', 't_approval', 'vpladjustmentdetail', 'popup_approve', 'popup_reject', 'popup_revise', 't_attachment',  't_message', 'user', 'company','status_doc','hidden','hiddenx','adjustmenttype'));
    }


    
    public function vpladjustment_waiting(Request $request)
    {     
           
        $tittle = 'On Progress Adjustment';   
        $user = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $multidept = explode(',', $user->departmentid);

        if ($request->ajax()) {                   
            
            if ($user->role == 'admin') {                
                $data = Vpladjustment::leftjoin('trx_approval', 'vpl_trx_adjustment.adjustment_id', '=', 'trx_approval.docid')                                      
                    ->select('vpl_trx_adjustment.*', 'trx_approval.name as waiting')
                    ->where('trx_approval.status', 'P')
                    ->where('trx_approval.aprvdatebefore', '<>',null)                        
                    ->get();          
            }else{
                $data = Vpladjustment::leftjoin('trx_approval', 'vpl_trx_adjustment.adjustment_id', '=', 'trx_approval.docid')                                      
                    ->select('vpl_trx_adjustment.*', 'trx_approval.name as waiting')
                    ->where('trx_approval.status', 'P')
                    ->where('trx_approval.aprvdatebefore', '<>',null)   
                    ->whereIn('vpl_trx_adjustment.cpnyid', $multicpnyid)
                    ->whereIn('vpl_trx_adjustment.department', $multidept)                     
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

                ->addColumn('adjustmenttype', function($row) {
                    // Periksa nilai adjustmenttype
                    if ($row->adjustmenttype == 'Adjustment') {
                        return 'Adjustment';
                    } else if ($row->adjustmenttype == 'ReturnTf') {
                        return 'Return Adjustment';
                    } else {
                        return '';
                    }
                })
                
                ->addColumn('adjustment_id', function($row){         
                    $url = "/showvpladjustment_$row->id";                                                            
                    $btn = '<a href="'.$url.'" class="btn btn-block" style="background-color: #3c87e2; color:white">'.$row->adjustment_id.'</a>';    
                    return $btn;
                })
                ->rawColumns(['status','adjustment_id','adjustmenttype'])                                           
                ->make(true);
        }
        return view('vpladjustment.vpladjustment_waiting', compact('tittle','user'));
    }

    public function vpladjustment_completed(Request $request)
    {     
           
        $tittle = 'Completed Adjustment';   
        $user = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $multidept = explode(',', $user->departmentid);

        if ($request->ajax()) {                   
            
            if ($user->role == 'admin') {                
                $data = Vpladjustment::where('status', 'C')                    
                    ->get();           
            }else{
                $data = Vpladjustment::whereIn('cpnyid', $multicpnyid)
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

                ->addColumn('adjustmenttype', function($row) {
                    // Periksa nilai adjustmenttype
                    if ($row->adjustmenttype == 'Adjustment') {
                        return 'Adjustment';
                    } else if ($row->adjustmenttype == 'ReturnTf') {
                        return 'Return Adjustment';
                    } else {
                        return '';
                    }
                })
                    
                ->addColumn('adjustment_id', function($row){         
                    $url = "/showvpladjustment_$row->id";                                                            
                    $btn = '<a href="'.$url.'" class="btn btn-block" style="background-color: #3c87e2; color:white">'.$row->adjustment_id.'</a>';    
                    return $btn;
                })
                ->rawColumns(['status','adjustment_id','adjustmenttype'])                                           
                ->make(true);
            }
        return view('vpladjustment.vpladjustment_completed', compact('user','tittle'));
    }
    
    public function vpladjustment_rejected(Request $request)
    {     
           
        $tittle = 'Rejected Adjustment';   
        $user = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $multidept = explode(',', $user->departmentid);
       
        if ($request->ajax()) {                   
            
            if ($user->role == 'admin') {                
                $data = Vpladjustment::where('status', 'R')                    
                    ->get();                  
            }else{
                $data = Vpladjustment::whereIn('cpnyid', $multicpnyid)
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

                ->addColumn('adjustmenttype', function($row) {
                    // Periksa nilai adjustmenttype
                    if ($row->adjustmenttype == 'Adjustment') {
                        return 'Adjustment';
                    } else if ($row->adjustmenttype == 'ReturnTf') {
                        return 'Return Adjustment';
                    } else {
                        return '';
                    }
                })
                    
                ->addColumn('adjustment_id', function($row){         
                    $url = "/showvpladjustment_$row->id";                                                            
                    $btn = '<a href="'.$url.'" class="btn btn-block" style="background-color: #3c87e2; color:white">'.$row->adjustment_id.'</a>';    
                    return $btn;
                })
                ->rawColumns(['status','adjustment_id','adjustmenttype'])                                           
                ->make(true);
            }
        
        return view('vpladjustment.vpladjustment_rejected', compact('user','tittle'));
    }

    public function vpladjustment_all(Request $request)
    {     
           
        $tittle = 'All Adjustment';   
        $user = Auth::user();
        $multicpnyid = explode(',', $user->companyid);
        $multidept = explode(',', $user->departmentid);

        if ($request->ajax()) {                   
            
            if ($user->role == 'admin') {
                $data = Vpladjustment::get();            
            }else{
                $data = Vpladjustment::whereIn('cpnyid', $multicpnyid)
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

                ->addColumn('adjustmenttype', function($row) {
                    // Periksa nilai adjustmenttype
                    if ($row->adjustmenttype == 'Adjustment') {
                        return 'Adjustment';
                    } else if ($row->adjustmenttype == 'ReturnTf') {
                        return 'Return Adjustment';
                    } else {
                        return '';
                    }
                })
                    
                ->addColumn('adjustment_id', function($row){         
                    $url = "/showvpladjustment_$row->id";                                                            
                    $btn = '<a href="'.$url.'" class="btn btn-block" style="background-color: #3c87e2; color:white">'.$row->adjustment_id.'</a>';    
                    return $btn;
                })
                ->rawColumns(['status','adjustment_id','adjustmenttype'])                                           
                ->make(true);
            }
        
        return view('vpladjustment.vpladjustment_all', compact('tittle','user'));
    }
    public function sendmsg_ajax(Request $request, $id)
    {

        $user = Auth::user();
        $vpladjustment = Vpladjustment::find($id);
        $data = new T_Message();
        $data->docid = $vpladjustment->adjustment_id;
        $data->doctype = 'VPA';
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
        $vpladjustment = Vpladjustment::find($id);

        //save trx_message
        T_Message::create([
            'docid' => $vpladjustment->adjustment_id,
            'doctype' => 'VPA',
            'username' => $user->username,
            'name' => $user->name,
            'message' => $request->message,
            'created_user' => $user->name,
            'status' => 'A'
        ]);

        return redirect('/showvpladjustment_' . $id);
    }

    public function print_vpladjustment_pdf(int $id)
    {
        $vpladjustment = Vpladjustment::find($id);
     
        $vpladjustmentdetail = Vpladjustmentdetail::join('vpl_ms_product','vpl_trx_adjustment_detail.product_id','=','vpl_ms_product.product_id')
            ->select('vpl_trx_adjustment_detail.*','vpl_ms_product.product_name')
            ->where('adjustment_id', $vpladjustment->adjustment_id)            
            ->get();
       
        $t_approval = T_approval::where('docid', $vpladjustment->adjustment_id)
            ->where('status','<>','X')   
            ->orderBy('created_at')
            ->orderBy('aprvid')         
            ->get();
        $company = Company::where('cpnyid', $vpladjustment->cpnyid)->first();
        $date = $vpladjustment->created_at->format(' d F Y ');            

        $approve_count = T_approval::where('docid', $vpladjustment->adjustment_id) 
            ->where('status', '<>','X')           
            ->count();
      
        $data = [
            'cpnyid' => $company->cpnyname,
            'parent' => $company->parent,
            'project' => $company->project,
            'department' => $vpladjustment->department,
            'docid' => $vpladjustment->adjustment_id,
            'adjustmenttype' => $vpladjustment->adjustmenttype,            
            'created_at' => $date,
            'user' => $vpladjustment->created_user,                      
            'adjustment_remark' => $vpladjustment->adjustment_remark,               
            'req_date' => $vpladjustment->created_at,            
        ];


        $pdf = PDF::loadview('vpladjustment.show_vpladjustment_pdf', $data, ['vpladjustmentdetail' => $vpladjustmentdetail, 't_approval' => $t_approval, 'approve_count' => $approve_count]);
        return $pdf->stream("pdf_vpladjustment.pdf");


    }

    public function edit_vpladjustment(int $id)
    {
        $vpladjustment = Vpladjustment::find($id);
        $vpladjustmentdetail = Vpladjustmentdetail::join('vpl_ms_product','vpl_trx_adjustment_detail.product_id','=','vpl_ms_product.product_id')
            ->select('vpl_trx_adjustment_detail.*','vpl_ms_product.product_name')
            ->where('adjustment_id', $vpladjustment->adjustment_id)            
            ->get();
        $t_attachment = Attachment::where('docid', $vpladjustment->adjustment_id)
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
     

        return view('vpladjustment.edit_vpladjustment', compact('vpladjustment','vpladjustmentdetail','t_attachment','usercpny','usercpny2','msproduct','mswhs','userdept','userdept2'));
    }

    public function deleteVpladjustmentDetail(Request $request)
    {
        $detailId = $request->input('detail_id');

        // Find and delete the record
        $detail = Vpladjustmentdetail::find($detailId);

        if ($detail) {
            $detail->delete();
            return response()->json(['message' => 'Detail deleted successfully.']);
        } else {
            return response()->json(['message' => 'Record not found.'], 404);
        }
    }

    public function deleteVpladjustmentAttach(Request $request)
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
    public function updateVpladjustment(Request $request)
    {
        // dd($request->all());
        $user = Auth::user();
        $datenow = Carbon::now()->format('Y-m-d');
        $datestamp = Carbon::now()->toDateTimeString();
        $dt = Carbon::now();
        $year = $dt->year;
        $month =  $dt->month; 

        $vpladjustment = Vpladjustment::find($request->idx);
              
        if ($request->has('addmore')) {

            foreach ($request->addmore as $detail) {
                // Check if all the required fields are not null and not empty
                if (!is_null($detail['product_id']) && !is_null($detail['qty_adjustment']) &&
                    !empty($detail['product_id']) && !empty($detail['qty_adjustment'])) {
                    
                    // Check if the record already exists in Vpladjustmentdetail
                    $vplAdjustmentDetail = Vpladjustmentdetail::where('adjustment_id', $vpladjustment->adjustment_id)
                        ->where('product_id', $detail['product_id'])
                        ->where('expired_date', $detail['expired_date'])
                        ->where('whs_id', $detail['from_whs_id'])
                        ->first();
                    
                    if ($vplAdjustmentDetail) {
                        // If record exists, update qty_adjustment by adding the new qty
                        $vplAdjustmentDetail->qty_adjustment += $detail['qty_adjustment'];
                        $vplAdjustmentDetail->updated_user = $user->username;
                        $vplAdjustmentDetail->updated_at = $datestamp;
                        $vplAdjustmentDetail->save();
                    } else {
                        // If no matching record exists, insert a new record
                        Vpladjustmentdetail::create([
                            'adjustment_id' => $vpladjustment->adjustment_id,
                            'linenbr' => 0,
                            'product_id' => $detail['product_id'],                           
                            'qty_adjustment' => $detail['qty_adjustment'],
                            'expired_date' => $detail['expired_date'],
                            'whs_id' => $detail['from_whs_id'],                           
                            'status' => 'P',
                            'created_user' => $user->username,
                            'created_at' => $datestamp,
                        ]);
                    }
        
                   
                }
            }
        
            // Now update the linenbr in the correct sequence for all records of this adjustment_id
            $vplAdjustmentDetails = Vpladjustmentdetail::where('adjustment_id', $vpladjustment->adjustment_id)
                ->orderBy('created_at', 'asc') // Order by creation time to ensure correct ordering
                ->get();
        
            $lineNumber = 1; // Reset linenbr counter
        
            foreach ($vplAdjustmentDetails as $detail) {
                $detail->linenbr = $lineNumber;
                $detail->save(); // Save each updated line number
                $lineNumber++;
            }
        }       
        

        $m_approval = M_approval::where('aprvdoctype', 'VPA')             
            ->where('aprvcpnyid', $request->cpnyid)
            ->where('aprvdeptid', $request->department)
            ->where('status', 'A')
            ->get();
       

        //insert trx_approval
        foreach ($m_approval as $mp) {
            $aprvdatebefore = ($mp->aprvid == 1) ? $datestamp : null; 
            T_approval::create([
                'docid' => $vpladjustment->adjustment_id,
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
                $attach->docid = $vpladjustment->adjustment_id;
                $attach->name = $filename;
                $attach->attachfile = $attachfile;
                $attach->status = 'A';
                $attach->extention = $file->getClientOriginalExtension();
                $attach->created_user = $user->name;
                $attach->save();
            }
        }

        $vpladjustment->cpnyid = $request->cpnyid;
        $vpladjustment->department = $request->department;            
        $vpladjustment->adjustment_remark = $request->adjustment_remark;      
        $vpladjustment->status = 'P';        
        $vpladjustment->updated_user = $user->name;
        $vpladjustment->updated_at = $datestamp;
        $vpladjustment->save();

         //read trx_approval next
        $t_approval_next = T_approval::where('docid', $vpladjustment->adjustment_id)
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
            'info' => $request->adjustment_remark,           
            'url' => url('/showvpladjustment_') . $request->idx

        );

        $multiapp = explode(',', $t_approval_next->aprvusername);

        $email_it = User::whereIN('username', $multiapp)
            ->where('status', 'A')
            ->get();

        foreach ($email_it as $emailsit) {
            Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

                $message->to($emailsit->test_email)->subject($data['docid'] . ' - Waiting Approval Adjustment');
                $message->from('digitalserver@pakuwon.com', 'Digital Approval System');
            });
        }
   

        return response()->json(['success' => 'Adjustment saved successfully.']);
      
    }

    public function vpladjustment_cancel($id)
    {
        //process it checked        
        $user = Auth::user();        
        $vpladjustment = Vpladjustment::find($id);
       
        $vpladjustment->status = 'X';
        $vpladjustment->updated_user = $user->name;
        $vpladjustment->save();

        $approval = T_approval::where('docid', $vpladjustment->adjustment_id)
                ->where('status', 'P')                
                ->get();

        foreach ($approval as $t_approval) {            
            $t_approval->status = 'X';
            $t_approval->aprvdatebefore = null;
            $t_approval->save();
        }    
        return redirect('/vpladjustment_waiting')->with('message', 'Process Cancel Successfully');
    }

    public function insert_msproduct_detail(int $id)
    {
            
        $user = Auth::user();    
        $datestamp = Carbon::now()->toDateTimeString();
        $vpladjustment = Vpladjustment::find($id);    
        
        $vpladjustmentdetail = Vpladjustmentdetail::where('adjustment_id', $vpladjustment->adjustment_id)                            
            ->get();
        // dd($vpladjustmentdetail);
        foreach ($vpladjustmentdetail as $detail) {            
            $msProductDetail = Msproductdetail::where('product_id', $detail['product_id'])
                ->where('expired_date', $detail['expired_date'])
                ->where('whs_id', $detail['whs_id'])               
                ->first();           
    
                if ($msProductDetail) {
                    // If record exists, update qty_available by adding the new qty
                    $msProductDetail->qty_available += $detail['qty_adjustment'];                    
                    $msProductDetail->updated_at = $datestamp;
                    $msProductDetail->updated_user = $user->username;
                    $msProductDetail->save();                  
                } else {
                  
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
