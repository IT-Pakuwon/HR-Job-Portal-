<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\UserDas;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Autonbr;
use App\Models\Company;
use App\Models\Dept;
use App\Models\T_Message;
use App\Models\Attachment;
use App\Models\M_approval;
use App\Models\M_approval_other;
use App\Models\T_approval;
use Illuminate\Support\Facades\DB;

class AgendaController extends Controller
{
    /**
     * Menampilkan halaman utama dengan DataTables
     */
    public function index()
    {
        $participant = UserDas::where('status', 'A')          
            ->get();
        // dd($participant);
        // $email = request()->user()->email;
        // dd($email);
        return view('pages.agendas.index', compact('participant'));
        
        
    }

    /**
     * Mengambil data agenda untuk DataTables (JSON Response)
     */
    public function json()
    {
        $agendas = Agenda::select(['id', 'agendaid', 'title', 'startdate', 'participant', 'agendapriority', 'duedate', 'status'])
            ->latest()
            ->get();

        return response()->json(['data' => $agendas]);
    }

    /**
     * Mengambil daftar peserta untuk Select2
     */
    // public function getParticipants()
    // {
    //     return response()->json(UserDas::select('username', 'name')->get());
    // }
    public function getParticipants(Request $request)
    {
        $query = UserDas::query();

        // Tambahkan pencarian jika ada parameter `q`
        if ($request->has('q')) {
            $search = $request->q;
            $query->where('name', 'LIKE', "%{$search}%");
        }

        // Pastikan `id` diambil dari `username`, bukan dari database default
        $participants = $query->selectRaw("username AS idx, name AS text")->limit(10)->get();

        return response()->json($participants);
    }

    /**
     * Menyimpan agenda baru
     */
    public function store(Request $request)
    {
        dd($request->all()); // Debugging untuk cek data yang diterima

        // Validasi input
        $request->validate([
            'title' => 'required|string|max:255',
            'agendapriority' => 'required|string',
            'startdate' => 'nullable|date',
            'duedate' => 'nullable|date|after_or_equal:startdate',            
            'description' => 'nullable|string',
           
        ]);

        DB::beginTransaction();
        try {
            $datenow = Carbon::now()->format('Y-m-d');
            $dt = Carbon::now();
            $year = $dt->year;
            $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
            $doctype = 'AGD';

            // Generate agenda ID
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

            $userlist = UserDas::where('status','A')
                ->get(); 

            $participantlist = $request->input('participant');
            if($participantlist <> null){
                $userlist->appreance = implode(',', $participantlist);
            }else{
                $userlist->appreance = '';
            }

            // Simpan agenda dengan participant sebagai JSON
            $agenda = Agenda::create([
                'agendaid' => $docid,
                'agendadate' => $datenow,
                'agendatype' => $request->agendatype,
                'title' => $request->title,
                'agendapriority' => $request->agendapriority,
                'status' => $request->status ?? 'P',
                'startdate' => $request->startdate,
                'duedate' => $request->duedate,
                'description' => $request->description,
                'cpnyid' => $request->cpnyid,
                'departementid' => $request->departementid,
                'participant' => $userlist->appreance
            ]);

            //read ms_approval
            $m_approval = M_approval::where('aprvdoctype', $doctype)
                ->where('aprvcpnyid', $request->cpnyid)
                ->where('aprvdeptid', $request->departementid)
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
                    'created_user' => request()->user()->email
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
                    $attach->created_user = request()->user()->email;
                    $attach->save();
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'agenda' => $agenda]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan agenda', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Mengambil data agenda berdasarkan ID (untuk edit)
     */
    public function edit($id)
    {
        $agenda = Agenda::findOrFail($id);
        return response()->json($agenda);
    }

    /**
     * Mengupdate agenda berdasarkan ID
     */
    public function update(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'title' => 'required|string|max:255',
            'agendapriority' => 'required|string',
            'startdate' => 'nullable|date',
            'duedate' => 'nullable|date|after_or_equal:startdate',
            'status' => 'nullable|string|max:1',
            'description' => 'nullable|string',
            // 'participant' => 'nullable|string' 
        ]);

        DB::beginTransaction();
        try {
            $agenda = Agenda::findOrFail($id);
            $agenda->update([
                'title' => $request->title,
                'agendapriority' => $request->agendapriority,
                'agendatype' => $request->agendatype,
                'cpnyid' => $request->cpnyid,
                'departementid' => $request->departementid,
                'status' => $request->status,
                'startdate' => $request->startdate,
                'duedate' => $request->duedate,
                'description' => $request->description,
                'participant' => $userlist->appreance
            ]);

            DB::commit();
            return response()->json(['success' => true, 'agenda' => $agenda]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal memperbarui agenda', 'message' => $e->getMessage()], 500);
        }
    }


    public function getCompany()
    {
        $companies = Company::select('cpnyid')->get();
        return response()->json($companies);
    }

    public function getDepartement()
    {
        $departement = Dept::select('deptname')->get();       
        return response()->json($departement);
    }

    public function showagendas($id, Request $request)
    {
        $agenda = Agenda::find($id);
        
        $t_approval = T_approval::where('docid', $agenda->agendaid)
            ->where('status','<>','X')   
            ->orderBy('created_at')
            ->orderBy('aprvid')         
            ->get();
        //read attachment
        $t_attachment = Attachment::where('docid', $agenda->agendaid)
            ->where('status', 'A')
            ->get();
        //read message
        $t_message = T_Message::where('docid', $agenda->agendaid)
            ->where('status', 'A')
            ->get();
        return view('pages.agendas.show', compact('agenda','t_approval','t_attachment','t_message'));
        
        
    }

    public function show($agendaId)
    {
        $agenda = Agenda::findOrFail($agendaId);

        $t_approval = T_approval::where('docid', $agenda->agendaid)
            ->where('status', '<>', 'X')
            ->orderBy('created_at')
            ->orderBy('aprvid')
            ->get();

        $t_attachment = Attachment::where('docid', $agenda->agendaid)
            ->where('status', 'A')
            ->get();

        return response()->json([
            'agenda' => $agenda,
            'approvals' => $t_approval,
            'attachments' => $t_attachment,
        ]);
    }

 

    public function getApprovals($agendaId)
    {
        // Ambil data approval berdasarkan agendaId
        // $approvals = Approval::where('agenda_id', $agendaId)->get();
        $agenda = Agenda::find($agendaId);
        
        $approvals = T_approval::where('docid', $agenda->agendaid)
            ->where('status','<>','X')   
            ->orderBy('created_at')
            ->orderBy('aprvid')         
            ->get();
        
        return response()->json($approvals);
    }

    


}
