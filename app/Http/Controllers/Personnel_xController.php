<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // baru
use App\Models\Tr;
use App\Models\M_approval;
use App\Models\T_approval;
use App\Models\Dept;
use App\Models\Location;
use App\Models\Autonbr;
use App\Models\Attachment;
use App\Models\Company;
use App\Models\T_Message;
use App\Models\User;
use App\Models\Usercpny;
use App\Models\Personnel;
use App\Models\Joblevel;
use App\Models\Viewtrxpersonnel;
use App\Models\Site;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Jobtype;
use App\Models\Msmanpower;

use PDF;
use Mail;
use App\Mail\NotifyMail;
use App\Models\Budgetprf;


class PersonnelController extends Controller
{

    public function index()
    {
        $tittle = '';
        //view trx_personnel
        $user = Auth::user();
        if ($user->role == 'admin') {
            $personnel = Personnel::all();
        } else {
            $personnel = Personnel::where('cpnyid', $user->companyid)
                ->where('deptname', $user->departmentid)
                ->orderBy('id', 'DESC')
                ->get();
        }

        return view('personnel.personnel', ['personnel' => $personnel, 'tittle' => $tittle]);
    }

    public function add()
    {
        //add trx_personnel        
        $user = Auth::user();
        $usercpny = Usercpny::where('username', '=', $user->username)
            ->get();
        $usercpny2 = Usercpny::where('username', '=', $user->username)
            ->first();          
                
        $joblevel = Joblevel::where('status', 'A')            
            ->get();
        $jobtype = Jobtype::where('status', 'A')
            ->where('doctype','PRF')            
            ->get();
        $msmanpower = Msmanpower::where('status', 'A') 
            ->where('cpnyid',$usercpny2->cpnyid) 
            ->where('deptname',$user->departmentid)          
            ->first();

        $user_list = User::where('status', 'A')
            ->where('departmentid',$user->departmentid)
            ->get();
     
        // dd($joblevel);
        return view('personnel.add', compact('usercpny', 'joblevel','usercpny2','jobtype','msmanpower','user_list'));
    }

    public function edit($id, Request $request)
    {
        //edit trx_personnel       
        $personnel = Personnel::find($id); 
        $user = Auth::user();
        $usercpny = Usercpny::where('username', '=', $user->username)
            ->get();
        $usercpny2 = Usercpny::where('username', '=', $user->username)
            ->first();          
                
        $joblevel = Joblevel::where('status', 'A')            
            ->get();
        
        if($personnel->job_type == 'Replacement'){
            $ceklist = 'checked';
            $ceklist2 = '';        
        }else{
            $ceklist2 = 'checked';
            $ceklist = '';
        }    

        $t_attachment = Attachment::where('docid', $personnel->docid)
            ->where('status', 'A')
            ->get();

        $user_list = User::where('status', 'A')
            ->where('departmentid',$user->departmentid)
            ->get();
        $jobtype = Jobtype::where('status', 'A')
            ->where('doctype','PRF')            
            ->get();    

        return view('personnel.edit', compact('personnel','usercpny', 'joblevel','usercpny2','ceklist','ceklist2','t_attachment','user_list','jobtype'));
    }

    public function save(Request $request)
    {

        $this->validate($request, [
            'job_title' => 'required',
            'cpnyid' => 'required',
            'job_level' => 'required',
            'immediate_superior' => 'required',
            'state_position' => 'required',
            'job_type' => 'required',
            'reason_vacancy' => 'required',
            'required' => 'required',
            'actual' => 'required',    
            'total_actual' => 'required',
            'job_responsibilities' => 'required',
            'job_qualification' => 'required',        
        ]);
        
       
        //create autonbr
        $user = Auth::user();
        $datenow = Carbon::now()->format('Y-m-d');
        $datestamp = Carbon::now()->toDateTimeString();
        $dt = Carbon::now();
        $year = $dt->year;
        $month =  $dt->month;
        // $tglbln =  substr($dt->year, 2) . $dt->month;

        //cek ms Approval
        $count_approval = M_approval::where('status', 'A')
            ->where('aprvcpnyid', $request->cpnyid)
            ->where('aprvdeptid', $user->departmentid)
            ->where('aprvdoctype', 'PRF')
            ->count();
        if ($count_approval == 0) {
            return redirect('/addpersonnel')->with('error', 'Approval Empty, Please contact IT!');
        } else {

            //read autonbr
            $autonbr = Autonbr::where('doctype', '=', 'PRF')
                ->where('year', '=', $year)
                ->where('month', '=', $month)
                ->where('status', '=', 'A')
                ->first();

            $tglbln =  substr($dt->year, 2) . $autonbr->month;

            // $cek autonbr
            if ($autonbr->number == 0) {
                $urutan = 1;                
                $docid = 'PRF' . $tglbln . '00' . $urutan;
            } else {
                $urutan = $autonbr->number;
                $urutan++;                
                $docid = 'PRF' . $tglbln . sprintf("%03s", $urutan);
            }

            //update ms_autonbr
            $autonbr->number = $urutan;
            $autonbr->save();
            
            //read site
            $m_site = Site::where('id', $user->site)
                ->where('status', 'A')
                ->first();

            if ($request->name_job <> null){
                $name_job = $request->name_job;
            }else{
                $name_job = $request->name_job2;
            }

            //save trx_personnel
            Personnel::create([
                'docid' => $docid,
                'cpnyid' => $request->cpnyid,
                'deptname' => $user->departmentid,
                'locationname' => $m_site->site,
                'date' => $datenow,
                'user' => $user->username,
                'job_title' => $request->job_title,
                'job_level' => $request->job_level,
                'immediate_superior' => $request->immediate_superior,
                'state_position' => $request->state_position,
                'job_type' => $request->job_type,
                'reason_vacancy' => $request->reason_vacancy,
                'name_job' => $name_job,
                'required' => $request->required,
                'actual' => $request->actual,
                'total_actual' => $request->total_actual,
                'job_responsibilities' => $request->job_responsibilities,
                'job_qualification' => $request->job_qualification,                               
                'status' => 'P',
                'site' => $m_site->id,
                'cpnyid_site' => $m_site->cpnyid,
                'created_user' => $user->name
            ]);

            //process attachment
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
                    
                    $folder_upload = $folder_attach;
                    // $folder_upload = public_path() . '/attachment';
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

            if ($request->required + $request->actual > $request->total_actual){

                $personnel = Personnel::where('docid', $docid)->first();
                $personnel->status = 'H';
                $personnel->save();
                return redirect('/personnel_all')->with('error', 'More than actual !, Please Create IM PRF');
                
            }else{

                //read ms_approval for approval site
                $m_approval = M_approval::where('aprvdoctype', 'PRF')
                    ->where('aprvcpnyid', $request->cpnyid)
                    ->where('aprvdeptid', $user->departmentid)
                    ->where('status', 'A')
                    ->get();

                //insert trx_approval
                foreach ($m_approval as $mp) {
                    T_approval::create([
                        'docid' => $docid,
                        'aprvid' => $mp->aprvid,
                        'aprvdoctype' => $mp->aprvdoctype,
                        'aprvcpnyid' => $mp->aprvcpnyid,
                        'aprvdeptid' => $mp->aprvdeptid,
                        'aprvusername' => $mp->aprvusername,                    
                        'name' => $mp->name,
                        'aprvtotalday' => 1,
                        'status' => 'P',
                        'created_user' => $user->name
                    ]);
                }
            
                //update datebefore for show approval in dasboard
                $t_approval_date = T_approval::where('docid', $docid)
                    ->where('aprvid', 1)
                    ->first();
                $t_approval_date->aprvdatebefore = $datestamp;
                $t_approval_date->save();

                
                //read ms_approval for email
                $email_approval = M_approval::where('aprvdoctype', 'PRF')
                    ->where('aprvcpnyid', $request->cpnyid)
                    ->where('aprvdeptid', $user->departmentid)
                    ->where('aprvid', 1)
                    ->where('status', 'A')
                    ->first();
                $personnel = Viewtrxpersonnel::where('docid', $docid)->first();
                //send email to approval 1
                $data = array(
                    'docid' => $docid,
                    'cpnyid' => $request->cpnyid,
                    'deptname' => $user->departmentid,
                    'location' => $personnel->locationname,
                    'date' => $datenow,
                    'name' => $user->name,
                    'email' => $user->notification_email,             
                    'job_title' => $request->job_title,
                    'job_level' => $personnel->title_level,
                    'url' => url('/showpersonnel_') . $personnel->id,
                );


                $multiapp = explode(',', $email_approval->aprvusername);

                $email_it = User::whereIN('username', $multiapp)
                    ->where('status', 'A')
                    ->get();
                
                foreach ($email_it as $emailsit) {               

                    Mail::send('emails.mailpersonnel', $data, function ($message) use ($data, $emailsit) {

                        $message->to($emailsit->notification_email, '-')->subject($data['docid'] . ' - Waiting Approval Personnel Requisition ');                    
                        $message->from('digitalserver@pakuwon.com', 'Digital Approval System');
                    });
                }

                return redirect('/personnel_waiting')->with('message', 'Data sent Successfully');
            }
        }
    }

    public function updateuser($id, Request $request)
    {
        //update personnel user
        $personnel = Personnel::find($id);
        $user = Auth::user();
        $datestamp = Carbon::now()->toDateTimeString();
        $datenow = Carbon::now()->format('Y-m-d');
        $dt = Carbon::now();
        $year = $dt->year;

        $date_first = $request->date_used;
        $date_used = date("Y-m-d", strtotime($date_first));

        //read site
        $m_site = Site::where('id', $user->site)
        ->where('status', 'A')
        ->first();

        //cek ms Approval
        $count_approval = M_approval::where('status', 'A')
            ->where('aprvcpnyid', $request->cpnyid)
            ->where('aprvdeptid', $user->departmentid)
            ->where('aprvdoctype', 'PRF')
            ->count();
        if ($count_approval == 0) {
            return redirect('/editpersonnel_'.$id)->with('error', 'Approval Empty, Please contact IT!');
        } else {
        
            $personnel->cpnyid = $request->cpnyid;  
            $personnel->job_title = $request->job_title;
            $personnel->job_level = $request->job_level;
            $personnel->immediate_superior = $request->immediate_superior;
            $personnel->state_position = $request->state_position;
            $personnel->job_type = $request->job_type;
            $personnel->name_job = $request->name_job;
            $personnel->reason_vacancy = $request->reason_vacancy;
            $personnel->required = $request->required;
            $personnel->actual = $request->actual;
            $personnel->total_actual = $request->total_actual;
            $personnel->job_responsibilities = $request->job_responsibilities;
            $personnel->job_qualification = $request->job_qualification;            
            $personnel->status = 'P';
            $personnel->updated_user = $user->name;
            $personnel->updated_at = $datestamp;
            $personnel->checked = '';
            // $personnel->cpnyid_site = $request->cpnyid_site;          
            $personnel->save();
           
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
                    
                    $folder_upload = $folder_attach;
                    // $folder_upload = public_path() . '/attachment';
                    $file->move($folder_upload, $attachfile);
    
                    //insert to table attachment
                    $attach = new Attachment();
                    $attach->docid = $personnel->docid;
                    $attach->name = $filename;
                    $attach->attachfile = $attachfile;
                    $attach->status = 'A';
                    $attach->extention = $file->getClientOriginalExtension();
                    $attach->created_user = $user->name;
                    $attach->save();
                }
            }  
            //read ms_approval
            $m_approval = M_approval::where('aprvdoctype', 'PRF')
                ->where('aprvcpnyid', $request->cpnyid)
                ->where('aprvdeptid', $user->departmentid)
                ->where('status', 'A')
                ->get();

            //insert trx_approval
            foreach ($m_approval as $mp) {
                T_approval::create([
                    'docid' => $personnel->docid,
                    'aprvid' => $mp->aprvid,
                    'aprvdoctype' => $mp->aprvdoctype,
                    'aprvcpnyid' => $mp->aprvcpnyid,
                    'aprvdeptid' => $mp->aprvdeptid,
                    'aprvusername' => $mp->aprvusername,
                    'name' => $mp->name,
                    'aprvtotalday' => 1,
                    'status' => 'P',
                    'created_user' => $user->name
                ]);
            }

            //update datebefore for show approval in dasboard
            $t_approval_date = T_approval::where('docid', $personnel->docid)
                ->where('aprvid', 1)
                ->where('status', 'P')
                ->first();
            $t_approval_date->aprvdatebefore = $datestamp;
            $t_approval_date->save();

                       
            //read ms_approval for email
            $email_approval = M_approval::where('aprvdoctype', 'PRF')
                ->where('aprvcpnyid', $request->cpnyid)
                ->where('aprvdeptid', $user->departmentid)
                ->where('aprvid', 1)
                ->where('status', 'A')
                ->first();
            $personnel = Personnel::where('docid', $personnel->docid)->first();

            //send email to GA groups
          
            $data = array(
                'docid' => $personnel->docid,
                'cpnyid' => $request->cpnyid,
                'deptname' => $user->departmentid,
                'location' => $personnel->locationname,
                'date' => $datenow,
                'name' => $user->name,
                'email' => $user->notification_email,             
                'job_title' => $request->job_title,
                'job_level' => $personnel->title_level,
                'url' => url('/showpersonnel_') . $personnel->id,
            );


            $multiapp = explode(',', $email_approval->aprvusername);

            $email_it = User::whereIN('username', $multiapp)
                ->where('status', 'A')
                ->get();
            
            foreach ($email_it as $emailsit) {               

                Mail::send('emails.mailpersonnel', $data, function ($message) use ($data, $emailsit) {

                    $message->to($emailsit->notification_email, '-')->subject($data['docid'] . ' - Waiting Approval Personnel  ');                    
                    $message->from('digitalserver@pakuwon.com', 'Digital Approval System');
                });
            }
        
            return redirect('/showpersonnel_' . $id)->with('message', 'Data Updated Successfully');
        }
    }

    public function attach($id, Request $request)
    {
        $personnel = Personnel::find($id);
        $user = Auth::user();
        //process attachment
        if ($request->hasfile('attachment')) {

            foreach ($request->file('attachment') as $file) {

                $randomNumber = random_int(100000, 999999);

                $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $attachfile = $randomNumber . '-' . $file->getClientOriginalName();

                //attach to folder
                $folder_upload = public_path() . '/attachment';
                $file->move($folder_upload, $attachfile);

                //insert to table attachment
                $attach = new Attachment();
                $attach->docid = $personnel->docid;
                $attach->name = $filename;
                $attach->attachfile = $attachfile;
                $attach->status = 'A';
                $attach->extention = $file->getClientOriginalExtension();
                $attach->created_user = $user->name;
                $attach->save();
            }
        }

        return redirect('/showpersonnel_' . $id)->with('message', 'Data Attach Successfully');
    }

   

    public function approve($id)
    {
        //update trx_personnel
        $personnel = Personnel::find($id);
        $user = Auth::user();
        $datestamp = Carbon::now()->toDateTimeString();

        //update status completed trx_personnel
        $count_approval = T_approval::where('docid', $personnel->docid)
            ->where('status', 'P')
            ->count();
     
            if ($count_approval == 1) {
                $personnel->status = 'C';
                $personnel->save();
                app('App\Http\Controllers\PersonnelController')->sendemail($id);
            }

            //read trx_approval
            $t_approval = T_approval::where('docid', $personnel->docid)
                ->where('status', 'P')
                ->where('aprvusername', 'like', "%" . $user->username . "%")
                ->first();
            
            //update trx_approval 
            $t_approval->status = 'A';
            $t_approval->aprvdateafter = $datestamp;
            $t_approval->aprvusername = $user->username;
            $t_approval->name = $user->name;
            $t_approval->save();    
            
            $personnel->updated_user = $user->name;
            $personnel->save();
            //read trx_approval next
            $t_approval_next = T_approval::where('docid', $personnel->docid)
                ->where('status', 'P')                
                ->first();
            
            if ($count_approval <> 1) {
                //update datebefore
                $t_approval_next->aprvdatebefore = $datestamp;
                $t_approval_next->save();

                $personnelx = Viewtrxpersonnel::find($id);

                //send email to it advice
                
                $data = array(
                    'docid' => $t_approval_next->docid,
                    'cpnyid' => $t_approval_next->aprvcpnyid,
                    'deptname' => $t_approval_next->aprvdeptid,
                    'location' => $personnelx->locationname,
                    'date' => $t_approval_next->aprvdatebefore,
                    'name' => $t_approval_next->created_user,
                    'email' => $user->notification_email,             
                    'job_title' => $personnelx->job_title,
                    'job_level' => $personnelx->title_level,                    
                    'url' => url('/showpersonnel_') . $personnel->id,
                );
         
                    $multiapp = explode(',', $t_approval_next->aprvusername);
                    $email_it = User::whereIN('username', $multiapp)
                        ->where('status', 'A')
                        ->get();

                    foreach ($email_it as $emailsit) {
                        Mail::send('emails.mailpersonnel', $data, function ($message) use ($data, $emailsit) {
                            $message->to($emailsit->notification_email, '-')->subject($data['docid'] . ' - Waiting Approval Personnel  ');
                            $message->from('digitalserver@pakuwon.com', 'Digital Approval System');
                        });
                    }
                // }

                
            }

            // return redirect('/showpersonnel_' . $id)->with('message', 'Data Approved Successfully');
            return redirect('/home')->with('message', 'Data Approved Successfully');
        
    }

    public function reject($id, Request $request)
    {
        
        if ($request->message == ''){
            return redirect('/showpersonnel_' . $id)->with('error', 'Message Empty, Please Entry Message');
        }
        

        //update trx_personnel
        $personnel = Personnel::find($id);
        $user = Auth::user();
        $datestamp = Carbon::now()->toDateTimeString();

        //read trx_approval
        $t_approval = T_approval::where('docid', '=', $personnel->docid)
            ->where('status', '=', 'P')
            ->first();

        $personnel->status = 'R';
        $personnel->save();

        //update trx_approval 
        $t_approval->status = 'R';
        $t_approval->aprvdateafter = $datestamp;
        // $t_approval->aprvusername = $user->email;
        $t_approval->aprvusername = $user->username;
        $t_approval->name = $user->name;
        $t_approval->save();

        $t_aprv_sisa = T_approval::where('docid', '=', $personnel->docid)
            ->where('status', '=', 'P')
            ->get();

        foreach ($t_aprv_sisa as $t_aprv) {
            $t_aprv->status = 'X';
            $t_aprv->save();
        }

        $personnelx = Viewtrxpersonnel::find($id);

                //send email to it advice
                $data = array(
                    'docid' => $personnelx->docid,
                    'cpnyid' => $personnelx->cpnyid,
                    'deptname' => $personnelx->deptname,
                    'location' => $personnelx->locationname,
                    'date' => $t_approval->aprvdatebefore,
                    'name' => $personnelx->created_user,
                    'email' => $user->notification_email,             
                    'job_title' => $personnelx->job_title,
                    'job_level' => $personnelx->title_level,                    
                    'url' => url('/showpersonnel_') . $personnel->id,
                );

                // $multiapp = explode(',', $personnel->user);

                $email_it = User::where('username', $personnel->user)
                    ->where('status', 'A')
                    ->get();

                foreach ($email_it as $emailsit) {
                    Mail::send('emails.mailpersonnel', $data, function ($message) use ($data, $emailsit) {
                        $message->to($emailsit->notification_email, '-')->subject($data['docid'] . ' - Rejected Personnel ');
                        $message->from('digitalserver@pakuwon.com', 'Digital Approval System');
                    });
                }

                app('App\Http\Controllers\PersonnelController')->sendmsg($id,$request);

        // return redirect('/showpersonnel_' . $id)->with('message', 'Data Rejected Successfully');
        return redirect('/home')->with('message', 'Data Rejected Successfully');
    }

    public function revise($id, Request $request)
    {
        if ($request->message == ''){
            return redirect('/showpersonnel_' . $id)->with('error', 'Message Empty, Please Entry Message');
        }
        
        //update trx_personnel
        $personnel = Personnel::find($id);
        $user = Auth::user();
        $datestamp = Carbon::now()->toDateTimeString();

        //read trx_approval
        $t_approval = T_approval::where('docid', '=', $personnel->docid)
            ->where('status', '=', 'P')
            ->first();

        //update trx_approval 
        $t_approval->status = 'D';
        $t_approval->aprvdateafter = $datestamp;
        // $t_approval->aprvusername = $user->email;
        $t_approval->aprvusername = $user->username;
        $t_approval->name = $user->name;
        $t_approval->save();

        $personnel->status = 'D';
        $personnel->updated_user = $user->name;
        $personnel->updated_at = $datestamp;
        $personnel->save();

        //read trx_approval sisa
        $t_aprv_sisa = T_approval::where('docid', '=', $personnel->docid)
            ->where('status', '=', 'P')
            ->get();

        //update trx_approval sisa
        foreach ($t_aprv_sisa as $t_aprv) {
            $t_aprv->status = 'X';
            $t_aprv->save();
        }

        $personnelx = Viewtrxpersonnel::find($id);

        //send email to it advice
        $data = array(
            'docid' => $personnelx->docid,
            'cpnyid' => $personnelx->cpnyid,
            'deptname' => $personnelx->deptname,
            'location' => $personnelx->locationname,
            'date' => $t_approval->aprvdatebefore,
            'name' => $personnelx->created_user,
            'email' => $user->notification_email,             
            'job_title' => $personnelx->job_title,
            'job_level' => $personnelx->title_level . ' (Silahkan Revisi dengan cara klik link dibawah ini lalu klik tombol Edit lalu Submit/Cancel Document, Thanks)',                    
            'url' => url('/showpersonnel_') . $personnel->id,
        );


        $email_it = User::where('username', $personnel->user)
                ->where('status', 'A')
                ->get();

                foreach ($email_it as $emailsit) {
                    Mail::send('emails.mailpersonnel', $data, function ($message) use ($data, $emailsit) {
                        $message->to($emailsit->notification_email, '-')->subject($data['docid'] . ' - Revise Personnel');
                        $message->from('digitalserver@pakuwon.com', 'Digital Approval System');
                    });
                }

        app('App\Http\Controllers\PersonnelController')->sendmsg($id,$request);

        
        return redirect('/home')->with('message', 'Data Revised Successfully');
    }

    //show data Trouble Report and trx_Approval
    public function show($id, Request $request)
    {
        $personnel = Viewtrxpersonnel::find($id);
        $company = Company::where('status', 'A')->get();
        $user = Auth::user();
        $cek_role = User::where('name', $user->name)->first();
        //show all trx_approval
        $t_approval = T_approval::where('docid', $personnel->docid)
            ->where('status','<>','X')            
            ->get();

        //read status
        if ($personnel->status =='R'){
            $status_doc ='Rejected';
            $bg_status = 'red';
        } else if ($personnel->status =='C'){
            $status_doc ='Completed';
            $bg_status = 'green';
        } else if ($personnel->status =='D'){    
            $status_doc ='Hold';
            $bg_status = 'aqua';
        }else if($personnel->status =='X'){    
            $status_doc ='Cancel';    
            $bg_status = 'red';
        } else {
            $status_doc ='On Progress';
            $bg_status = 'yellow';
        }    

        //hidden button update,add, upload
        if ($cek_role->role == 'user' and $cek_role->groups == '5' and $personnel->pic == $user->username) {
            if ($personnel->status == 'C' or $personnel->status == 'R') {
                $validasi = 'disabled';                 
                $hidden = 'hidden';  
                $hidden2 = '';     
                $hidden3 = 'hidden';         
            } else {
                $validasi = 'required';                 
                $hidden = '';  
                $hidden2 = 'hidden';   
                $hidden3 = 'hidden';         
            }
        } else {           
            $validasi = 'disabled';   
            $hidden = 'hidden'; 
            $hidden2 = 'hidden'; 

            if($personnel->status == 'D' and $personnel->created_user == $user->name){
                $hidden3 = '';
            }else{
                $hidden3 = 'display:none';
            } 
        }

        //cek for validasi button approval   
        if ($personnel->status == 'P') {           
            
            $trx_cek_like = T_approval::where('docid', $personnel->docid)
                ->where('status', 'P')
                ->where('aprvusername', 'like', "%" . $user->username . "%")                
                ->first();  
              
            if ($trx_cek_like == null or $trx_cek_like->aprvdatebefore == null) {
                $popup_approve = '#modal-warning';
                $popup_reject = '#modal-warning';
                $popup_revise = '#modal-warning';
                
            } else {
                $cek_approval = T_approval::where('docid', $personnel->docid)
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

   

        //read attachment
        $t_attachment = Attachment::where('docid', $personnel->docid)
            ->where('status', 'A')
            ->get();
        //read message
        $t_message = T_Message::where('docid', $personnel->docid)
            ->where('status', 'A')
            ->get();
        $requester = User::where('username',$personnel->user)->first();    
        // $show_personnel = Category::where('id', $personnel->type_personnel)
        //     ->where('status', 'A')
        //     ->first();
        // $usersite = Site::where('id', '=', $user->site)->first();

        $trx_cancel = T_approval::where('docid', $personnel->docid)
            ->where('status', 'P')
            // ->where('aprvdatebefore','<>',null) 
            ->where('aprvid',1)
            ->count();            
        if ($trx_cancel == 1 and $personnel->created_user == $user->name){
            $hiddenx = '';
        }else{
            $hiddenx = 'display:none';
            // $hiddenx = '';
        }  
        
        
        $budgetprf = Budgetprf::where('docid', $personnel->refid)            
            ->first();
        
        // dd($budgetprf);
        
        return view('personnel.show', compact('personnel', 't_approval', 'validasi', 'popup_approve', 'popup_reject', 'popup_revise', 't_attachment', 't_message', 'user', 'company','status_doc','requester','hidden','hidden2','hidden3','hiddenx','bg_status','budgetprf'));
    }


    public function personnel_waiting()
    {
        //show trx_personnel waiting
        $tittle = 'On Progress Personnel Requisition';
        $user = Auth::user();

        if ($user->role == 'admin') {
            // $personnel = Personnel::where('status', 'P')
            //     ->orderBy('id', 'desc')
            //     ->get();
            $personnel = Viewtrxpersonnel::leftjoin('trx_approval', 'viewtrxpersonnel.docid', '=', 'trx_approval.docid')                                      
                ->select('viewtrxpersonnel.*', 'trx_approval.name')
                ->where('trx_approval.status', 'P')
                ->where('trx_approval.aprvdatebefore', '<>',null)
                ->orderBy('viewtrxpersonnel.id', 'desc')
                ->get(); 
        } elseif ($user->role == 'user' and $user->groups == 5) {          
           
            $personnel = Viewtrxpersonnel::where('status', 'P')               
                ->orderBy('id', 'desc')
                ->get();              
        } else {        

            $multicpnyid = explode(',', $user->companyid);         
            $personnel = Viewtrxpersonnel::leftjoin('trx_approval', 'viewtrxpersonnel.docid', '=', 'trx_approval.docid')                                      
                ->select('viewtrxpersonnel.*', 'trx_approval.name')
                ->whereIn('viewtrxpersonnel.cpnyid', $multicpnyid)
                ->where('viewtrxpersonnel.deptname', $user->departmentid)
                ->where('trx_approval.status', 'P')
                ->where('trx_approval.aprvdatebefore', '<>',null)
                ->orderBy('viewtrxpersonnel.id', 'desc')
                ->get();
        }

        return view('personnel.personnel_checked', compact('personnel', 'user', 'tittle'));
    }

    public function personnel_completed()
    {
        // $date = '2023-01-24';
        // $year = date('Y', strtotime($date));
        // dd($year);
        //show trx_personnel completed      
        $tittle = 'Completed Personnel ';
        $user = Auth::user();
        if ($user->role == 'admin') {
            $personnel = Viewtrxpersonnel::where('status', 'C')
                ->orderBy('id', 'DESC')
                ->get();
        } elseif ($user->role == 'user' and $user->groups == 5) {          
          
            $personnel = Viewtrxpersonnel::where('status', 'C')               
                ->orderBy('id', 'desc')
                ->get();
        } else {
            
            $multicpnyid = explode(',', $user->companyid);
            $personnel = Viewtrxpersonnel::where('deptname', $user->departmentid)
                ->whereIn('cpnyid', $multicpnyid)
                ->where('status', 'C')
                ->orderBy('id', 'desc')
                ->get();
        }
        return view('personnel.personnel_completed', compact('personnel', 'user','tittle'));
    }

    public function personnel_reject()
    {
        //show trx_personnel reject
        $tittle = 'Rejected Personnel ';
        $user = Auth::user();
        if ($user->role == 'admin') {
            $personnel = Viewtrxpersonnel::where('status', 'R')
                ->get();
        } elseif ($user->role == 'user' and $user->groups == 5) {
            
            // $site = Site::where('id', $user->site)->first();
            $personnel = Viewtrxpersonnel::where('status', 'R')
                // ->where('cpnyid_site', $site->cpnyid)
                ->orderBy('id', 'desc')
                ->get();
        } else {
           
            $multicpnyid = explode(',', $user->companyid);
            $personnel = Viewtrxpersonnel::where('deptname', $user->departmentid)
                ->whereIn('cpnyid', $multicpnyid)
                ->where('status', 'R')
                ->orderBy('id', 'desc')
                ->get();
        }
        return view('personnel.personnel', compact('personnel', 'tittle'));
    }

    public function personnel_all()
    {
        //show trx_personnel all
        $tittle = 'All Personnel ';

        $user = Auth::user();
        if ($user->role == 'admin') {
            
            $personnel = Viewtrxpersonnel::all();
        } elseif ($user->role == 'user' and $user->groups == 5) {
            $personnel = Viewtrxpersonnel::all();
        } else {           
            $multicpnyid = explode(',', $user->companyid);
            $personnel = Viewtrxpersonnel::where('deptname', $user->departmentid)
                ->whereIn('cpnyid', $multicpnyid)
                ->orderBy('id', 'desc')
                ->get();
        }
        return view('personnel.personnel', compact('personnel', 'tittle'));
    }

    public function personnel_myjob()
    {
        //show trx_personnel myjob
        $tittle = 'My Job Personnel ';
        $user = Auth::user();
        if ($user->role == 'admin') {            
            $personnel = Viewtrxpersonnel::where('checked', $user->username)
                ->get();
        } elseif ($user->role == 'user' and $user->groups == 5) {
            // $site = Site::where('id', $user->site)->first();           
            $personnel = Viewtrxpersonnel::where('checked', $user->username)                
                ->orderBy('id', 'desc')
                ->get();
        } else {            
            $multicpnyid = explode(',', $user->companyid);
            $personnel = Viewtrxpersonnel::where('deptname', $user->departmentid)
                ->where('checked', $user->username)
                ->whereIn('cpnyid', $multicpnyid)
                ->orderBy('id', 'desc')
                ->get();
        }
        return view('personnel.personnel', compact('personnel', 'tittle'));
    }

    public function personnel_approval()
    {
        $tittle = 'MyApproval Personnel ';    
        $user = Auth::user();  
        $personnel = T_approval::join('trx_personnel', 'trx_approval.docid', '=', 'trx_personnel.docid')
            ->select('trx_personnel.*', 'trx_approval.name', 'trx_personnel.status')
            ->where('trx_approval.aprvusername', 'like', "%" . $user->username . "%")
            ->whereIN('trx_approval.status', ['A','R','D'])                              
            ->get();
        
        return view('personnel.personnel', compact('personnel', 'tittle'));
    }

    public function sendmsg(int $id, Request $request)
    {
        //send message
        $this->validate($request, [
            'message' => 'required'
        ]);


        $user = Auth::user();
        $personnel = Personnel::find($id);

        
        //save trx_message
        T_Message::create([
            'docid' => $personnel->docid,
            'doctype' => 'PRF',
            'username' => $user->email,
            'name' => $user->name,
            'message' => $request->message,
            'created_user' => $user->name,
            'status' => 'A'
        ]);

        return redirect('/showpersonnel_' . $id);
    }

    public function sendemail(int $id)
    {
        $personnelx = Viewtrxpersonnel::find($id);
        $user = Auth::user();

        $email_to = User::where('username', $personnelx->user)
            ->where('status', 'A')
            ->first();
        //send email to it advice
        $data = array(
            'docid' => $personnelx->docid,
            'cpnyid' => $personnelx->cpnyid,
            'deptname' => $personnelx->deptname,
            'location' => $personnelx->locationname,
            'date' => $personnelx->date,
            'name' => $personnelx->created_user,
            'email' => $email_to->notification_email,             
            'job_title' => $personnelx->job_title,
            'job_level' => $personnelx->title_level,                    
            'url' => url('/showpersonnel_') . $personnelx->id,
        );


        $email_cc = User::where('status', 'A')
            ->where('groups', '5')            
            // ->where('companyid', 'like', "%" . $budget->cpnyid . "%")
            ->get();

        foreach ($email_cc as $emailcc) {
            Mail::send('emails.mailpersonnel', $data, function ($message) use ($data, $emailcc) {
                $message->to($data['email'], '-')->subject($data['docid'] . ' - Completed Personnel');
                $message->cc($emailcc->notification_email);
                $message->from('digitalserver@pakuwon.com', 'Digital Approval System');
            });
        }


    
    }


    public function delattach($id)
    {
        //hapus attachment       
        $personnel_attachment = Attachment::find($id);
        $personnel_attachment->delete();
        //show trx_personnel
        $personnel = Personnel::where('docid',  $personnel_attachment->docid)
            ->first();

        return redirect('/editpersonnel_' . $personnel->id)->with('message', 'Data Deleted Successfully');
        
    }


    //print pdf
    public function print_pdf(int $id)
    {
        $personnel = Viewtrxpersonnel::find($id);


        // $t_approval = T_approval::where('docid', $personnel->docid)->get();
        $t_approval = T_approval::where('docid', $personnel->docid)
            ->where('status', '<>','X')
            ->get();
        $approve_count = T_approval::where('docid', $personnel->docid) 
            ->where('status', '<>','X')           
            ->count();    
        $company = Company::where('cpnyid', $personnel->cpnyid)->first();
        $date = $personnel->created_at->format(' d F Y ');
        $date_used = date("d-m-Y", strtotime($personnel->date_used));
        
        $data = [
            'cpnyid' => $company->cpnyname,
            'deptname' => $personnel->deptname,
            'docid' => $personnel->docid,
            'date_used' => $date_used,
            'created_at' => $date,
            'user' => $personnel->created_user,
            'job_title' => $personnel->job_title,
            'immediate_superior' => $personnel->immediate_superior,
            'state_position' => $personnel->state_position,
            'job_type' => $personnel->job_type,
            'name_job' => $personnel->name_job,
            'reason_vacancy' => $personnel->reason_vacancy,
            'required' => $personnel->required,
            'actual' => $personnel->actual,
            'total_actual' => $personnel->total_actual,
            'job_responsibilities' => $personnel->job_responsibilities,
            'job_qualification' => $personnel->job_qualification,
            'title_level' => $personnel->title_level,     
            'req_date' => $personnel->created_at,       
            
        ];


        $pdf = PDF::loadview('personnel.showpersonnel_pdf', $data, ['t_approval' => $t_approval, 'approve_count' => $approve_count]);
        return $pdf->stream("showpersonnel.pdf");
    }

    public function cancel_doc($id)
    {
        //process it checked        
        $user = Auth::user();
        $personnel = Personnel::find($id);
        //update trx_personnel
        $personnel->status = 'X';
        $personnel->updated_user = $user->name;
        $personnel->save();

        $approval = T_approval::where('docid', $personnel->docid)
                ->where('status', 'P')
                // ->where('aprvdatebefore','<>',null)
                ->get();
        foreach ($approval as $t_approval) {            
            $t_approval->status = 'X';
            $t_approval->aprvdatebefore = null;
            $t_approval->save();
        }    
        return redirect('/personnel_waiting')->with('message', 'Process Cancel Successfully');
    }

    public function rollback($id)
    {
        //process it checked        
        $user = Auth::user();
        $personnel = Personnel::find($id);
        //update trx_personnel
        $personnel->pic = '';
        $personnel->updated_user = $user->name;
        $personnel->save();


        return redirect('/personnel_waiting')->with('message', 'Process Rollback Successfully');
    }

    public function update_actual($id, Request $request)
    {
        //update trx_personnel pada saat it checked
        $personnel = Personnel::find($id);
        $user = Auth::user();
        $datestamp = Carbon::now()->toDateTimeString();
        $actual_budget= str_replace(".", "", $request->actual_budget);
        
        //update trx_personnel      
        $personnel->status_trip = $request->status_trip;
        $personnel->type_trip_done = $request->type_trip_done;       
        $personnel->actual_budget = $actual_budget;       
        $personnel->updated_user = $user->name;
        $personnel->updated_at = $datestamp;
        $personnel->save();         

        return redirect('/personnel_completed')->with('message', 'Data Updated Successfully');
    }

    public function sendmsg_ajax(Request $request, $id)
    {

        $user = Auth::user();
        $voucher = Personnel::find($id);
        $data = new T_Message();
        $data->docid = $voucher->docid;
        $data->doctype = 'PRF';
        $data->username = $user->username;
        $data->name = $user->name;
        $data->message = $request->msg;
        $data->status = 'A';
        $data->save();
        // return response()->json($data);

    }

    public function show_employee($id)
    {
        $user = Auth::user();
        $dt = Carbon::now();
        $year = $dt->year;
        $usercpny2 = Usercpny::where('username', '=', $user->username)
            ->first();
        // $joblevel = Joblevel::where('id', $id)            
        //     ->first();
       
        $data = Msmanpower::where('status', 'A') 
            ->where('level',$id)
            ->where('cpnyid',$usercpny2->cpnyid) 
            ->where('deptname',$user->departmentid)   
            ->where('year',$year)        
            ->first();

        return response()->json(['data' => $data]);
    }

    public function update_required($id)
    {
        //find prf
        $personnel = Personnel::find($id);
        $user = Auth::user();
        $datestamp = Carbon::now()->toDateTimeString();         
        $year = date('Y', strtotime($personnel->date));
        
        
        $msmanpower = Msmanpower::where('status', 'A') 
            ->where('cpnyid',$personnel->cpnyid) 
            ->where('deptname',$personnel->deptname) 
            ->where('year',$year) 
            ->where('level',$personnel->job_level)        
            ->first();
        if ($personnel->job_type == 'Replacement'){
            //update master manpower      
            $msmanpower->actual_manpower = $msmanpower->actual_manpower + $personnel->required; 
            $msmanpower->temp_manpower = $msmanpower->temp_manpower - $personnel->required;         
            $msmanpower->updated_user = $user->name;
            $msmanpower->updated_at = $datestamp;
            $msmanpower->save();  
            
            $personnel->checked = $user->username;
            $personnel->save();  
        }else{
            //update master manpower      
            $msmanpower->actual_manpower = $msmanpower->actual_manpower + $personnel->required;          
            $msmanpower->updated_user = $user->name;
            $msmanpower->updated_at = $datestamp;
            $msmanpower->save();  
            
            $personnel->checked = $user->username;
            $personnel->save();  
        }
        

        return redirect('/msmanpower')->with('message', 'Data Updated Successfully');
    }
}
