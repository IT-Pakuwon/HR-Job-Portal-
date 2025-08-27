<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Sppb;
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
use App\Models\Jobposting;
use App\Models\JobpostingResponsiblities;
use App\Models\JobpostingQualification;
use App\Models\AutonbrJobportal;
use App\Models\MJobtag;
use App\Models\TrJobtag;
use App\Models\Jobpostingtag;
use App\Models\Site;
use App\Models\StoEmployee;
use App\Models\StoDepartement;
use App\Models\StoJobProfile;
use App\Models\StoJobSpec;
use App\Models\Division;
use App\Models\StoSubGrading;
use App\Models\TrSPPB;
use App\Models\TrSPPBdetail;
use App\Models\MsLocationPG;
use App\Models\MsSubLocationPG;
use Mail;


class SppbController extends Controller
{
    public function index()
    {
        $all = TrSPPB::count();
        $onProgress = TrSPPB::where('status', 'P')->count();
        $reject = TrSPPB::where('status', 'R')->count();
        $revise = TrSPPB::where('status', 'D')->count();
        $completed = TrSPPB::where('status', 'C')->count();
       
        return view('pages.sppbs.sppbs', compact('all', 'onProgress', 'reject', 'revise', 'completed'));
    }

    public function json(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $search = trim((string) $request->input('search.value', ''));
        $status = (string) $request->query('status', ''); // '' = all

        // Dapatkan nama tabel fisik dari model lalu alias ke "sppb"
        $baseTable = (new TrSPPB)->getTable(); // contoh: "tr_sppb"

        $columns = [
            0 => 'sppb.sppbid',
            1 => 'sppb.sppbdate',
            2 => 'sppb.cpny_id',
            3 => 'sppb.department_id',
            4 => 'rt.requesttype_name',
            5 => 'sppb.keperluan',
            6 => 'sppb.status',
        ];
        $orderIdx = (int) $request->input('order.0.column', 1);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'sppb.sppbdate';

        // FROM tr_sppb AS sppb
        $base = TrSPPB::from($baseTable.' as sppb')
            ->leftJoin('ms_request_type as rt', function ($join) {
                $join->on('rt.requesttypeid', '=', 'sppb.requesttypeid')
                    ->on('rt.cpny_id',       '=', 'sppb.cpny_id');
            });

        if ($status !== '') {
            $base->where('sppb.status', $status);
        }

        // total tanpa filter
        $recordsTotal = (clone $base)->distinct('sppb.sppbid')->count('sppb.sppbid');

        // filter global
        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('sppb.sppbid',       'like', "%{$search}%")
                ->orWhere('sppb.cpny_id',    'like', "%{$search}%")
                ->orWhere('sppb.department_id','like', "%{$search}%")
                ->orWhere('rt.requesttype_name','like', "%{$search}%")
                ->orWhere('sppb.keperluan',  'like', "%{$search}%")
                ->orWhere('sppb.status',     'like', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $base)->distinct('sppb.sppbid')->count('sppb.sppbid');

        $data = $base->select(
                    'sppb.id',
                    'sppb.sppbid',
                    'sppb.sppbdate',
                    'sppb.cpny_id',
                    'sppb.department_id',
                    'sppb.requesttypeid',
                    'rt.requesttype_name',
                    'sppb.keperluan',
                    'sppb.status',
                    'sppb.created_by'
                )
                ->orderBy($orderCol, $orderDir)
                ->skip($start)
                ->take($length)
                ->get();

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    
    public function createSppb()
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
       
        return view('pages.sppbs.createsppbs', compact('usercpny','usercpny2','userdept','userdept2'));
    }

    
    
    public function storeSppb(Request $request)
    {
        // dd($request->all()); // Debugging: check request data
        // kumpulkan array dari form
        $inventoryIds  = $request->input('inventoryid',  $request->input('inventory_id', []));
        $productNames  = $request->input('product_name', []);
        $qtys          = $request->input('qty', []);
        $uoms          = $request->input('stock_unit',   $request->input('uom', [])); // <- penting
        $notes         = $request->input('note', []);
        $locations     = $request->input('location', []);
        $locationIds   = $request->input('location_id', $request->input('locationid', [])); // <- kalau perlu simpan
        $subLocIds     = $request->input('sub_location_id', $request->input('sublocationid', []));
        $subLocations  = $request->input('sub_location', []);
        $activities    = $request->input('activity', []);
        $coas          = $request->input('coa_id', []);
        $busUnits      = $request->input('business_unit', []);
        $departements  = $request->input('departement', []);

        $doctype  = 'PB';
        $user     = $request->user();
        $username = $user->username ?? 'system';

        $dt        = Carbon::now();
        $year      = $dt->year;
        $month     = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        $datestamp = $dt->toDateTimeString();

        // pastikan line approval ada
        $approvalCount = M_approval::where([
            ['status', '=', 'A'],
            ['aprvcpnyid', '=', $request->cpnyid],
            ['aprvdeptid', '=', $request->departementid],
            ['aprvdoctype', '=', $doctype],
        ])->count();

        if ($approvalCount === 0) {
            return response()->json([
                'message' => 'Approval line belum di-setup, Please contact IT!',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // === generate autonbr & docid (lock) ===
            $autonbr = Autonbr::lockForUpdate()
                ->where('doctype', $doctype)
                ->where('year', $year)
                ->where('month', $month)
                ->first();

            if (!$autonbr) {
                $autonbr = Autonbr::create([
                    'doctype' => $doctype,
                    'year'    => $year,
                    'month'   => $month,
                    'status'  => 'A',
                    'number'  => 1,
                ]);
                $urutan = 1;
            } else {
                $urutan = $autonbr->number + 1;
                $autonbr->update(['number' => $urutan]);
            }

            $tglbln = substr($year, 2) . $month;               // YYMM
            $docid  = $doctype . $tglbln . sprintf("%03d", $urutan);
            $sppbNo = $docid;                                   // atau 'SPPB-'.$docid

            // === 1) header dulu (totalqty sementara 0) ===
            $header = new TrSPPB();
            $header->sppbid            = $docid;                // PK string
            $header->sppbdate          = $dt->toDateString();
            $header->cpny_id           = $request->input('cpnyid');
            $header->department_id     = $request->input('departementid');
            $header->requesttypeid     = $request->input('requesttypeid');
            $header->keperluan         = $request->input('keperluan');
            $header->woid              = $request->input('woid');
            $header->spbid             = null;
            $header->totalopenordered  = 0;
            $header->totalqty          = 0;
            $header->assignby          = null;
            $header->assigndate        = null;
            $header->assignpurchasing  = null;
            $header->csjobs            = null;
            $header->cs                = null;
            $header->status            = 'P';
            $header->created_by        = $username;
            $header->save();

            // === 2) detail ===
            $totalQty         = 0;
            $totalOpenOrdered = 0;
            $rowCount = max(count($inventoryIds), count($qtys));
           
            for ($i = 0; $i < $rowCount; $i++) {
                $invId = $inventoryIds[$i] ?? null;
                $productName = $productNames[$i] ?? null;
                // qty: sudah kamu konversi koma->titik di JS; tetap jaga-jaga:
                $qty   = (float) str_replace(',', '.', (string) ($qtys[$i] ?? 0));
                $uom   = $uoms[$i] ?? null;

                if (empty($invId) || $qty <= 0) continue;

                $detail = new TrSPPBdetail();
                $detail->sppbid                   = $header->sppbid;
                $detail->sppb_no                  = $i + 1;   // nomor urut detail
                $detail->inventoryid              = $invId;
                $detail->inventory_descr          = $productName;
                $detail->qty                      = $qty;
                $detail->uom                      = $uom;
                $detail->note                     = $notes[$i]   ?? null;
                $detail->base_multiplier          = 1;
                $detail->base_uom                 = $uom;
                $detail->base_qty                 = $qty;
                $detail->budget_cpny_id           = $request->cpnyid;
                $detail->budget_business_unit_id  = $busUnits[$i]     ?? $request->cpnyid;
                $detail->budget_department_fin_id = $request->departementid;
                $detail->budget_account_id        = $coas[$i]         ?? null;
                $detail->budget_activity_id       = $activities[$i]   ?? null;               
                $detail->location_id              = $locationIds[$i]  ?? null;
                $detail->sub_location_id          = $subLocIds[$i]    ?? null;
                $detail->assignby                 = null;
                $detail->assigndate               = null;
                $detail->assignpurchasing         = null;
                $detail->openordered              = 0;
                $detail->ordered                  = 0;
                $detail->status                   = 'P';
                $detail->created_by               = $username;
                $detail->save();

                $totalQty += $qty;
            }

            // update totalqty di header
            $header->totalqty = $totalQty;
            $header->totalopenordered = $totalQty;
            $header->save();

            // === 4) copy line approval (M_approval -> T_approval) ===
            $approvals = M_approval::where([
                ['status', '=', 'A'],
                ['aprvcpnyid', '=', $request->cpnyid],
                ['aprvdeptid', '=', $request->departementid],
                ['aprvdoctype', '=', $doctype],
            ])->get();

            foreach ($approvals as $a) {
                T_approval::create([
                    'docid'          => $docid,
                    'aprvid'         => $a->aprvid,
                    'aprvdoctype'    => $a->aprvdoctype,
                    'aprvcpnyid'     => $a->aprvcpnyid,
                    'aprvdeptid'     => $a->aprvdeptid,
                    'aprvusername'   => $a->aprvusername,
                    'name'           => $a->name,
                    'aprvdatebefore' => $a->aprvid == 1 ? $datestamp : null,
                    'aprvtotalday'   => 1,
                    'status'         => 'P',
                    'created_user'   => $username,
                ]);
            }

            // === 5) attachments (opsional) ===
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

            // === 6) kirim email ke approver pertama ===
            $firstApproval = T_approval::where('docid', $docid)
                ->where('status', 'P')
                ->orderBy('aprvid')
                ->first();

            if ($firstApproval) {
                $data = [
                    'docid'    => $firstApproval->docid,
                    'cpnyid'   => $firstApproval->aprvcpnyid,
                    'deptname' => $firstApproval->aprvdeptid,
                    'date'     => $firstApproval->aprvdatebefore,
                    'name'     => $username,
                    'info'     => $request->changerequest_note,
                    'url'      => url('/showsppbs/' . $header->id), // FIX: pakai sppbid header
                ];

                $approvers = array_filter(array_map('trim', explode(',', (string)$firstApproval->aprvusername)));
                $emails = User::whereIn('username', $approvers)
                    ->where('status', 'A')
                    ->pluck('test_email');

                foreach ($emails as $email) {
                    \Mail::send('emails.mailapprove', $data, function ($message) use ($email, $data) {
                        $message->to($email)
                            ->subject($data['docid'].' - Waiting Approval SPPB')
                            ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                    });
                }
            }

            DB::commit();

            return response()->json([
                'message'  => 'SPPB created successfully',
                'sppbid'   => $docid,
                'sppb_no'  => $sppbNo,
                'totalqty' => $totalQty,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'message' => 'Failed to create SPPB',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
   

    public function editSppb($id)
    {
        $sppb = TrSPPB::findOrFail($id);
        $user = request()->user();
        $usercpny = Usercpny::where('username', '=', $user->username)
            ->get();
        $usercpny2 = Usercpny::where('username', '=', $user->username)
            ->first();
        $userdept = Userdept::where('username', '=', $user->username)
            ->get();
        $userdept2 = Userdept::where('username', '=', $user->username)
            ->first();
        $subgrading = StoSubGrading::select('subgrade_id','subgrade_name')->get();   

        $attachment = Attachment::where('docid', $sppb->sppbid)  
            ->where('status','A')         
            ->get();
       
        return view('pages.sppbs.editsppbs', compact('subgrading','usercpny','usercpny2','userdept','userdept2','sppb','attachment'));
    }
    
    public function updateSppb(Request $request, $id)
    {
        // dd($request->all()); 
        
        // Validasi input
        $request->validate([
            'cpnyid' => 'required|string',
            'departementid' => 'required|string',
            'departement_name' => 'required|string',
            'subgrade_name' => 'required|string',            
            'changerequest_note' => 'required|string',
            'attachments.*' => 'file|max:2048'
        ]);

        DB::beginTransaction();
        try {
            $datenow = Carbon::now()->format('Y-m-d');
            $dt = Carbon::now();
            $year = $dt->year;
            $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
            $doctype = 'CSO';
            $datestamp = Carbon::now()->toDateTimeString();
            $user = request()->user();

            $sppb = TrSPPB::findOrFail($id);
                                   
            $sppb -> update([              
                'cpnyid' => $request->cpnyid,
                'departementid' => $request->departementid,
                'departement_name' => $request->departement_name,
                'subgrade_name' => $request->subgrade_name,
                'changerequest_note' => $request->changerequest_note,
                'changerequest_date' => $datenow,
                'user' => $user->username,
                'created_user' => $user->username,
                'status' => 'P'        
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
                    'docid' => $sppb->sppbid,
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
                    $attach->docid = $sppb->sppbid;
                    $attach->name = $filename;
                    $attach->attachfile = $attachfile;
                    $attach->status = 'A';
                    $attach->extention = $file->getClientOriginalExtension();
                    $attach->created_user = $user->username;
                    $attach->save();
                }
            }

            $t_approval_next = T_approval::where('docid', $sppb->sppbid)
                ->where('status', 'P')
                ->orderby('aprvid','ASC')
                ->first();
           
            $data = array(
                'docid' => $t_approval_next->docid,
                'cpnyid' => $t_approval_next->aprvcpnyid,
                'deptname' => $t_approval_next->aprvdeptid,                
                'date' => $t_approval_next->aprvdatebefore,
                'name' => $t_approval_next->created_user,                          
                'info' => $request->job_title,           
                'url' => url('/showsppbs/') . $sppb->id
    
            );
    
            $multiapp = explode(',', $t_approval_next->aprvusername);
    
            $email_it = User::whereIN('username', $multiapp)
                ->where('status', 'A')
                ->get();
    
            foreach ($email_it as $emailsit) {
                Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {
                    $message->to($emailsit->test_email)->subject($data['docid'] . ' - Waiting Approval SPPBs');
                    $message->from('digitalserver@pakuwon.com', 'Pakuwon System');
                });
            }

            DB::commit();
            return response()->json(['success' => true, 'sppb' => $sppb]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan sppb', 'message' => $e->getMessage()], 500);
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
 

    public function showSppb($id)
    {        
        $sppb = TrSPPB::findOrFail($id);
        $sppb = TrSPPB::with([
            'requestType:requesttypeid,requesttype_name',
            'creator:username,name'
        ])
        ->findOrFail($id);

        

        $sppbdetail = TrSPPBdetail::with([
            'location:location_id,location_name',
            'subLocation:sub_location_id,sub_location_name'
        ])
        ->where('sppbid', $sppb->sppbid)
        ->get();
        
        $approval = T_approval::where('docid', $sppb->sppbid)
            ->where('status','<>','X')      
            ->orderBy('created_at')
            ->orderBy('aprvid')      
            ->get();
       
        $attachment = Attachment::where('docid', $sppb->sppbid)    
            ->where('status','A')        
            ->get();
       
       
        return view('pages.sppbs.showsppbs', compact('sppb','approval','attachment','sppbdetail'));
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
        $comment->doctype = 'CSO';
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

    public function approveSppb(Request $request, $docid)
    {
        $datestamp = Carbon::now()->toDateTimeString();       
        $user = request()->user(); // Ambil user yang login
        
        $sppb = TrSPPB::where('sppbid', $docid)->first();   

        if (!$sppb) {
            return response()->json(['success' => false, 'message' => 'SPPB not found'], 404);
        }        

        $count_approval = T_approval::where('docid', '=', $sppb->sppbid)
            ->where('status', '=', 'P')
            ->count();
    
        // Cek apakah user memiliki akses untuk approve
        $t_approval = T_approval::where('docid', $sppb->sppbid)
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
            $sppb->status = 'C';
            $sppb->completed_user = $user->username;
            $sppb->completed_at = $datestamp;
            $sppb->save();
            app('App\Http\Controllers\SppbController')->insert_jobposting($docid);
        }

        $t_approval_next = T_approval::where('docid', $sppb->sppbid)
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
                'info' => $sppb->changerequest_note,               
                'url' => url('/showsppbs/') . $sppb->id

            );

            $multiapp = explode(',', $t_approval_next->aprvusername);

            $email_it = User::whereIN('username', $multiapp)
                ->where('status', 'A')
                ->get();

            foreach ($email_it as $emailsit) {
                Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

                    $message->to($emailsit->test_email)->subject($data['docid'] . ' - Waiting Approval SPPB');
                    $message->from('digitalserver@pakuwon.com', 'Pakuwon System');
                });
            }
        }

        return response()->json(['success' => true, 'message' => 'Task approved successfully']);
    }

    public function rejectSppb(Request $request, $docid)
    {
        
        // dd($request->all());         
        $datestamp = Carbon::now()->toDateTimeString();       
        $user = request()->user(); // Ambil user yang login

        $sppb = TrSPPB::where('sppbid', $docid)->first();  
        
        
        if (!$sppb) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        // Cek apakah user memiliki akses untuk approve
        $t_approval = T_approval::where('docid', $sppb->sppbid)
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

            $sppb->status = 'R';
            $sppb->save();
        }   
                       
        $t_aprv_sisa = T_approval::where('docid', '=', $sppb->sppbid)
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
            'info' => $sppb->changerequest_note,               
            'url' => url('/showsppbs/') . $sppb->id

        );

       
        $email_it = User::where('username', $sppb->created_user)
                ->where('status', 'A')
                ->get();

        foreach ($email_it as $emailsit) {
            Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

                $message->to($emailsit->test_email)->subject($data['docid'] . ' - Rejected Sppb');
                $message->from('digitalserver@pakuwon.com', 'Pakuwon System');
            });
        }

        $id = $sppb->id;
        $doctype ='CSO';
        app('App\Http\Controllers\SendCommentController')->sendmsg($id, $doctype, $request);

        return response()->json(['success' => true, 'message' => 'Sppb rejected successfully']);
    }

    public function reviseSppb(Request $request, $docid)
    {
        
        // dd($request->all());         
        $datestamp = Carbon::now()->toDateTimeString();       
        $user = request()->user(); // Ambil user yang login

        $sppb = TrSPPB::where('sppbid', $docid)->first();  
        
        
        if (!$sppb) {
            return response()->json(['success' => false, 'message' => 'Sppb not found'], 404);
        }

        // Cek apakah user memiliki akses untuk approve
        $t_approval = T_approval::where('docid', $sppb->sppbid)
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

            $sppb->status = 'D';
            $sppb->save();
        }   
                       
        $t_aprv_sisa = T_approval::where('docid', '=', $sppb->sppbid)
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
            'info' => $sppb->changerequest_note,               
            'url' => url('/showsppbs/') . $sppb->id

        );

       
        $email_it = User::where('username', $sppb->created_user)
                ->where('status', 'A')
                ->get();

        foreach ($email_it as $emailsit) {
            Mail::send('emails.mailapprove', $data, function ($message) use ($data, $emailsit) {

                $message->to($emailsit->test_email)->subject($data['docid'] . ' - Revise Sppb');
                $message->from('digitalserver@pakuwon.com', 'Pakuwon System');
            });
        }

        $id = $sppb->id;
        $doctype ='CSO';
        app('App\Http\Controllers\SendCommentController')->sendmsg($id, $doctype, $request);

        return response()->json(['success' => true, 'message' => 'Sppb revise successfully']);
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
