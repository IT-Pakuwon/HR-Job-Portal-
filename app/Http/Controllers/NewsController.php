<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Http\Request;
use App\Models\News;
use Illuminate\Support\Carbon;
use App\Models\Autonbr;
use App\Models\T_Message;
use App\Models\Attachment;
use App\Models\M_approval;
use App\Models\M_approval_other;
use App\Models\T_approval;
use App\Models\Company;
use App\Models\Dept;
use App\Models\JobLevel;
use App\Models\JobResponsiblities;
use App\Models\JobQualification;
use App\Models\Usercpny;
use App\Models\Userdept;
use App\Models\User;
use Mail;


class NewsController extends Controller
{
    public function index()
    {
        $all = News::count();
        $onProgress = News::where('status', 'P')->count();
        $reject = News::where('status', 'R')->count();
        $revise = News::where('status', 'D')->count();
        $completed = News::where('status', 'C')->count();
       
        return view('pages.news.news', compact('all', 'onProgress', 'reject', 'revise', 'completed'));
    }
    
    public function json(Request $request)
    {
        // $status = $request->query('status', 'P');
        $status = $request->has('status') ? $request->query('status') : 'P';

        $query = News::query();

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $news = $query->orderBy('id', 'desc')->get();

        return response()->json(['data' => $news]);
    }


    public function createNews()
    {
        $user = request()->user();
        $usercpny = Usercpny::where('username', '=', $user->username)
            ->get();
        $usercpny2 = Usercpny::where('username', '=', $user->username)
            ->first();
        $userdept = Userdept::where('username', '=', $user->username)
            ->get();
        $userdept2 = Userdept::where('username', '=', $user->username)
            ->first();       
       
        return view('pages.news.createnews', compact('usercpny','usercpny2','userdept','userdept2'));
    }


    public function storeNews(Request $request)
    {
        // dd($request->all()); 
        
        // Validasi input
        $request->validate([
            'cpnyid' => 'required|string',
            'departementid' => 'required|string',
            'title' => 'required|string',
            'description' => 'required|string',                  
            'attachments.*' => 'file|max:2048' // Validasi file, max 2MB
        ]);

        $doctype = 'NEW';
        $count_approval = M_approval::where('status', 'A')
            ->where('aprvcpnyid', $request->cpnyid)
            ->where('aprvdeptid', $request->departementid)
            ->where('aprvdoctype', $doctype)
            ->count();
        
        if ($count_approval === 0) {
            return response()->json([
                'message' => 'Approval line belum di-setup, Please contact IT!'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $datenow = Carbon::now()->format('Y-m-d');
            $dt = Carbon::now();
            $year = $dt->year;
            $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);            
            $datestamp = Carbon::now()->toDateTimeString();
            $user = request()->user();

            // Generate task ID
            $autonbr = Autonbr::lockForUpdate()
                ->where('doctype', $doctype)
                ->where('year', $year)
                ->where('month', $month)
                ->where('status', 'A')
                ->first();

            if (!$autonbr) {
                $autonbr = Autonbr::create([
                    'doctype' => $doctype,
                    'year' => $year,
                    'month' => $month,
                    'status' => 'A',
                    'number' => 1
                ]);
                $urutan = 1;
            } else {
                $urutan = $autonbr->number + 1;
                $autonbr->number = $urutan;
                $autonbr->save();
            }

            $tglbln = substr($year, 2) . $month;
            $docid = $doctype . $tglbln . sprintf("%03d", $urutan);
                       
            $news = News::create([
                'docid' => $docid,
                'cpnyid' => $request->cpnyid,
                'departementid' => $request->departementid,
                'newsdate' => $datenow,
                'title' => $request->title,
                'description' => $request->description,    
                'created_user' => $user->username,
                'status' => $request->status ?? 'P'                
            ]);

           
            //read ms_approval
            $m_approval = M_approval::where('aprvdoctype', $doctype)
                ->where('aprvcpnyid', $request->cpnyid)
                ->where('aprvdeptid', $request->departementid)
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
                    'created_user' => $user->username
                ]);
            }            
         
            // Simpan Attachments ke attachments          
            if ($request->hasfile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $randomNumber = random_int(10000000, 99999999);
                    $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                   
                    $originalName = str_replace('%', '', $file->getClientOriginalName());
                    $attachfile = md5($randomNumber) . '-' . $originalName;

                    //attach to folder
                    $folder_attach = public_path() . '/attachments/'.$year;
                    $config['upload_path'] = $folder_attach;                   
                    if(!is_dir($folder_attach))
                    {
                        mkdir($folder_attach, 0777);
                    }
                    
                    $folder_upload = $folder_attach;
                    // $folder_upload = public_path() . '/attachments';
                    $file->move($folder_upload, $attachfile);

                    //insert to table attachments
                    $attach = new Attachment();
                    $attach->docid = $docid;
                    $attach->name = $filename;
                    $attach->attachfile = $attachfile;
                    $attach->status = 'A';
                    $attach->extention = $file->getClientOriginalExtension();
                    $attach->created_user = $user->username;
                    $attach->save();
                }
            }
            

            $t_approval_next = T_approval::where('docid', $docid)
                ->where('status', 'P')
                ->orderby('aprvid','ASC')
                ->first();
            $id = $news->id;
            $data = array(
                'docid' => $t_approval_next->docid,
                'cpnyid' => $t_approval_next->aprvcpnyid,
                'deptname' => $t_approval_next->aprvdeptid,                
                'date' => $t_approval_next->aprvdatebefore,
                'name' => $t_approval_next->created_user,                          
                'info' => $request->title,           
                'url' => url('/shownews/') . $id
    
            );
    
            $multiapp = explode(',', $t_approval_next->aprvusername);
    
            $email_it = User::whereIN('username', $multiapp)
                ->where('status', 'A')
                ->get();
    
            foreach ($email_it as $emailsit) {
                Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {
                    $message->to($emailsit->test_email)->subject($data['docid'] . ' - Waiting Approval News');
                    $message->from('digitalserver@pakuwon.com', 'Pakuwon Smart System');
                });
            }       

            DB::commit();
            return response()->json(['success' => true, 'news' => $news]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan news', 'message' => $e->getMessage()], 500);
        }
    }

    public function editNews($id)
    {
        $user = request()->user();
        $usercpny = Usercpny::where('username', '=', $user->username)
            ->get();
        $usercpny2 = Usercpny::where('username', '=', $user->username)
            ->first();
        $userdept = Userdept::where('username', '=', $user->username)
            ->get();
        $userdept2 = Userdept::where('username', '=', $user->username)
            ->first();
        $news = News::findOrFail($id);    
        $attachment = Attachment::where('docid', $news->docid)  
            ->where('status','A')         
            ->get();

        return view('pages.news.editnews', compact('news', 'attachment','usercpny','usercpny2','userdept','userdept2'));
    }
    
    public function updateNews(Request $request, $id)
    {
        // dd($request->all()); 
        
        // Validasi input
        $request->validate([
            'cpnyid' => 'required|string',
            'departementid' => 'required|string',
            'title' => 'required|string',
            'description' => 'required|string',         
            
        ]);

        DB::beginTransaction();
        try {
            $datenow = Carbon::now()->format('Y-m-d');
            $dt = Carbon::now();
            $year = $dt->year;
            $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
            $doctype = 'NEW';
            $datestamp = Carbon::now()->toDateTimeString();
            $user = request()->user();

            $news = News::findOrFail($id);
                       
            $news -> update([              
                'cpnyid' => $request->cpnyid,
                'departementid' => $request->departementid,
                'newsdate' => $datenow,
                'title' => $request->title,
                'description' => $request->description,             
                'created_user' => $user->username,
                'status' => $request->status ?? 'P'                
            ]);

            //read ms_approval
            $m_approval = M_approval::where('aprvdoctype', $doctype)
                ->where('aprvcpnyid', $request->cpnyid)
                ->where('aprvdeptid', $request->departementid)
                ->where('status', 'A')
                ->get();

            //insert trx_approval
            foreach ($m_approval as $mp) {
                $aprvdatebefore = ($mp->aprvid == 1) ? $datestamp : null; 
                T_approval::create([
                    'docid' => $news->docid,
                    'aprvid' => $mp->aprvid,
                    'aprvdoctype' => $mp->aprvdoctype,
                    'aprvcpnyid' => $mp->aprvcpnyid,
                    'aprvdeptid' => $mp->aprvdeptid,
                    'aprvusername' => $mp->aprvusername,
                    'name' => $mp->name,
                    'aprvdatebefore' => $aprvdatebefore,
                    'aprvtotalday' => 1,
                    'status' => 'P',
                    'created_user' => $user->username
                ]);
            }            
                        
            // Simpan Attachments ke attachments          
            if ($request->hasfile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $randomNumber = random_int(10000000, 99999999);
                    $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                   
                    $originalName = str_replace('%', '', $file->getClientOriginalName());
                    $attachfile = md5($randomNumber) . '-' . $originalName;

                    //attach to folder
                    $folder_attach = public_path() . '/attachments/'.$year;
                    $config['upload_path'] = $folder_attach;                   
                    if(!is_dir($folder_attach))
                    {
                        mkdir($folder_attach, 0777);
                    }
                    
                    $folder_upload = $folder_attach;
                    // $folder_upload = public_path() . '/attachments';
                    $file->move($folder_upload, $attachfile);

                    //insert to table attachments
                    $attach = new Attachment();
                    $attach->docid = $news->docid;
                    $attach->name = $filename;
                    $attach->attachfile = $attachfile;
                    $attach->status = 'A';
                    $attach->extention = $file->getClientOriginalExtension();
                    $attach->created_user = $user->username;
                    $attach->save();
                }
            }

            $t_approval_next = T_approval::where('docid', $news->docid)
                ->where('status', 'P')
                ->orderby('aprvid','ASC')
                ->first();
           
            $data = array(
                'docid' => $t_approval_next->docid,
                'cpnyid' => $t_approval_next->aprvcpnyid,
                'deptname' => $t_approval_next->aprvdeptid,                
                'date' => $t_approval_next->aprvdatebefore,
                'name' => $t_approval_next->created_user,                          
                'info' => $request->title,           
                'url' => url('/shownews/') . $news->id
    
            );
    
            $multiapp = explode(',', $t_approval_next->aprvusername);
    
            $email_it = User::whereIN('username', $multiapp)
                ->where('status', 'A')
                ->get();
    
            foreach ($email_it as $emailsit) {
                Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {
                    $message->to($emailsit->test_email)->subject($data['docid'] . ' - Waiting Approval News');
                    $message->from('digitalserver@pakuwon.com', 'Pakuwon Smart System');
                });
            }

            DB::commit();
            return response()->json(['success' => true, 'news' => $news]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan news', 'message' => $e->getMessage()], 500);
        }
    }

    public function removeAttachment($id)
    {
        try {
            $attachment = Attachment::findOrFail($id);
            $attachment->update(['status' => 'X']); // Update status ke "D" (Deleted)

            return response()->json(['success' => true, 'message' => 'Attachment status updated']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update attachment status', 'error' => $e->getMessage()], 500);
        }
    }
 

    public function showNews($id)
    {        
        $news = News::findOrFail($id);
        $approval = T_approval::where('docid', $news->docid)
            ->where('status','<>','X')      
            ->orderBy('created_at')
            ->orderBy('aprvid')      
            ->get();
        
        $attachment = Attachment::where('docid', $news->docid)    
            ->where('status','A')        
            ->get();
       
        return view('pages.news.shownews', compact('news','approval','attachment'));
    }

    
    public function fetchComments($id)
    {
    
        $comments = T_Message::where('docid', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'comments' => $comments
        ]);
    }
    public function storeComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:500',
        ]);
        // dd($id);
        $user = request()->user();
        $comment = new T_Message();
        $comment->docid = $id;
        $comment->doctype = 'NEW';
        $comment->username = $user->username; 
        $comment->name = $user->name; 
        $comment->message = $request->comment;
        $comment->status = 'A';
        $comment->created_at = now();
        $comment->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Comment added successfully!',
            'comment' => $comment
        ]);
    }

    public function approveNews(Request $request, $docid)
    {
        $datestamp = Carbon::now()->toDateTimeString();       
        $user = request()->user(); // Ambil user yang login
        
        $news = News::where('docid', $docid)->first();   

        if (!$news) {
            return response()->json(['success' => false, 'message' => 'Prf not found'], 404);
        }        

        $count_approval = T_approval::where('docid', '=', $news->docid)
            ->where('status', '=', 'P')
            ->count();
    
        // Cek apakah user memiliki akses untuk approve
        $t_approval = T_approval::where('docid', $news->docid)
            ->where('status', 'P')
            ->where('aprvusername', 'like', "%" . $user->username . "%")
            ->first();
        // dd($t_approval);
        if ($t_approval == null) {
            return response()->json(['success' => false, 'message' => "You Can't Approve!"], 403);
        } else {
            $t_approval->status = 'A';
            $t_approval->aprvdateafter = $datestamp;
            $t_approval->aprvusername = $user->username;
            $t_approval->name = $user->name;
            $t_approval->save();
        }   

        if ($count_approval == 1) {
            $news->status = 'C';
            $news->completed_user = $user->username;
            $news->completed_at = $datestamp;
            $news->save();
        }

        $t_approval_next = T_approval::where('docid', $news->docid)
            ->where('status', 'P')
            ->orderby('aprvid','ASC')
            ->first();

        if ($count_approval <> 1) {
            //update datebefore
            $t_approval_next->aprvdatebefore = $datestamp;
            $t_approval_next->save();

            //send email 
            $data = array(
                'docid' => $t_approval_next->docid,
                'cpnyid' => $t_approval_next->aprvcpnyid,
                'deptname' => $t_approval_next->aprvdeptid,               
                'date' => $t_approval_next->aprvdatebefore,
                'name' => $t_approval_next->created_user,
                'info' => $news->title,               
                'url' => url('/showvpersonels/') . $news->id

            );

            $multiapp = explode(',', $t_approval_next->aprvusername);

            $email_it = User::whereIN('username', $multiapp)
                ->where('status', 'A')
                ->get();

            foreach ($email_it as $emailsit) {
                Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

                    $message->to($emailsit->test_email)->subject($data['docid'] . ' - Waiting Approval News');
                    $message->from('digitalserver@pakuwon.com', 'Pakuwon Smart System');
                });
            }
        }

        return response()->json(['success' => true, 'message' => 'News approved successfully']);
    }

    public function rejectNews(Request $request, $docid)
    {
        
        // dd($request->all());         
        $datestamp = Carbon::now()->toDateTimeString();       
        $user = request()->user(); // Ambil user yang login

        $news = News::where('docid', $docid)->first();  
        
        
        if (!$news) {
            return response()->json(['success' => false, 'message' => 'News not found'], 404);
        }

        // Cek apakah user memiliki akses untuk approve
        $t_approval = T_approval::where('docid', $news->docid)
            ->where('status', 'P')
            ->where('aprvusername', 'like', "%" . $user->username . "%")
            ->first();
        // dd($t_approval);
        if ($t_approval == null) {
            return response()->json(['success' => false, 'message' => "You Can't Rejected!"], 403);
        } else {
            $t_approval->status = 'R';
            $t_approval->aprvdateafter = $datestamp;           
            $t_approval->save();

            $news->status = 'R';
            $news->save();
        }   
                       
        $t_aprv_sisa = T_approval::where('docid', '=', $news->docid)
            ->where('status', '=', 'P')
            ->get();

        foreach ($t_aprv_sisa as $t_aprv) {
            $t_aprv->status = 'X';
            $t_aprv->save();
        }

        //send email 
        $data = array(
            'docid' => $t_approval->docid,
            'cpnyid' => $t_approval->aprvcpnyid,
            'deptname' => $t_approval->aprvdeptid,
            // 'locationname' => $ms_site->site,
            'date' => $t_approval->aprvdatebefore,
            'name' => $t_approval->created_user,
            'info' => $news->title,               
            'url' => url('/showvpersonels/') . $news->id

        );

       
        $email_it = User::where('username', $news->created_user)
                ->where('status', 'A')
                ->get();

        foreach ($email_it as $emailsit) {
            Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

                $message->to($emailsit->test_email)->subject($data['docid'] . ' - Rejected News');
                $message->from('digitalserver@pakuwon.com', 'Pakuwon Smart System');
            });
        }

        $id = $news->id;
        $doctype ='NEW';
        app('App\Http\Controllers\SendCommentController')->sendmsg($id, $doctype, $request);

        return response()->json(['success' => true, 'message' => 'News rejected successfully']);
    }

    public function reviseNews(Request $request, $docid)
    {
        
        // dd($request->all());         
        $datestamp = Carbon::now()->toDateTimeString();       
        $user = request()->user(); // Ambil user yang login

        $news = News::where('docid', $docid)->first();  
        
        
        if (!$news) {
            return response()->json(['success' => false, 'message' => 'News not found'], 404);
        }

        // Cek apakah user memiliki akses untuk approve
        $t_approval = T_approval::where('docid', $news->docid)
            ->where('status', 'P')
            ->where('aprvusername', 'like', "%" . $user->username . "%")
            ->first();
        // dd($t_approval);
        if ($t_approval == null) {
            return response()->json(['success' => false, 'message' => "You Can't Revise!"], 403);
        } else {
            $t_approval->status = 'D';
            $t_approval->aprvdateafter = $datestamp;           
            $t_approval->save();

            $news->status = 'D';
            $news->save();
        }   
                       
        $t_aprv_sisa = T_approval::where('docid', '=', $news->docid)
            ->where('status', '=', 'P')
            ->get();

        foreach ($t_aprv_sisa as $t_aprv) {
            $t_aprv->status = 'X';
            $t_aprv->save();
        }

        //send email 
        $data = array(
            'docid' => $t_approval->docid,
            'cpnyid' => $t_approval->aprvcpnyid,
            'deptname' => $t_approval->aprvdeptid,
            // 'locationname' => $ms_site->site,
            'date' => $t_approval->aprvdatebefore,
            'name' => $t_approval->created_user,
            'info' => $news->title,               
            'url' => url('/showvpersonels/') . $news->id

        );

       
        $email_it = User::where('username', $news->created_user)
                ->where('status', 'A')
                ->get();

        foreach ($email_it as $emailsit) {
            Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

                $message->to($emailsit->test_email)->subject($data['docid'] . ' - Revise News');
                $message->from('digitalserver@pakuwon.com', 'Pakuwon Smart System');
            });
        }

        $id = $news->id;
        $doctype ='NEW';
        app('App\Http\Controllers\SendCommentController')->sendmsg($id, $doctype, $request);

        return response()->json(['success' => true, 'message' => 'News revise successfully']);
    }

    
    public function checkApproval($id, $action)
    {
        $user = Auth::user(); // Ambil user yang login
        // dd($action);
        // Query dasar untuk pengecekan
        $query = T_approval::where('docid', $id)
                    ->where('aprvusername', 'like', '%' . $user->username . '%')
                    ->where('status', 'P');                 

        // Jika aksi adalah reject atau revise, pastikan aprvdatebefore tidak null
        if (in_array($action, ['reject', 'revise','approve'])) {
            $query->whereNotNull('aprvdatebefore');
        }

        // Cek apakah user bisa melakukan aksi
        $canPerformAction = $query->exists();

        return response()->json(['canPerformAction' => $canPerformAction]);
    }





}
