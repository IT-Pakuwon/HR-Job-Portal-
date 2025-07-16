<?php
namespace App\Http\Controllers;

use App\Models\Agenda;
use Illuminate\Http\Request;
use App\Models\Usercpny;
use App\Models\Userdept;
use App\Models\User;
use App\Models\T_approval;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Autonbr;

class AgendaController extends Controller
{
    public function index()
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
        $userlist = User::where('status','A')
            ->get();

        // return view('pages.agendas.agendas');
        return view('pages.agendas.agendas', compact('userlist','usercpny','usercpny2','userdept','userdept2'));
    }

    public function json()
    {
        $tasks = Agenda::select(['screen_id', 'screen_code', 'screen_name', 'status'])
            ->latest()
            ->get();

        return response()->json(['data' => $tasks]);
    }

  
    public function store(Request $request)
    {
        // dd($request->all()); // Debugging untuk cek data yang diterima

        // Validasi input
        $request->validate([
            'cpnyid' => 'required|string',
            'departementid' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'participant' => 'required|array', // Pastikan ini array
            'startdate' => 'required|date_format:Y-m-d\TH:i',
            'enddate' => 'required|date_format:Y-m-d\TH:i',

        ]);

        DB::beginTransaction();
        try {
            $datenow = Carbon::now()->format('Y-m-d');
            $dt = Carbon::now();
            $year = $dt->year;
            $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
            $doctype = 'AGD';
            $user = request()->user();

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

            $userlist = User::where('status','A')
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
                'startdate' => Carbon::parse($request->startdate),
                'enddate' => Carbon::parse($request->enddate),
                'description' => $request->description,
                'cpnyid' => $request->cpnyid,
                'departementid' => $request->departementid,
                'created_user' => $user->username,
                'participant' => $userlist->appreance
            ]);

            // //read ms_approval
            // $m_approval = M_approval::where('aprvdoctype', $doctype)
            //     ->where('aprvcpnyid', $request->cpnyid)
            //     ->where('aprvdeptid', $request->departementid)
            //     ->where('status', 'A')
            //     ->get();

            // //insert trx_approval
            // foreach ($m_approval as $mp) {
            //     T_approval::create([
            //         'docid' => $docid,
            //         'aprvid' => $mp->aprvid,
            //         'aprvdoctype' => $mp->aprvdoctype,
            //         'aprvcpnyid' => $mp->aprvcpnyid,
            //         'aprvdeptid' => $mp->aprvdeptid,
            //         'aprvusername' => $mp->aprvusername,
            //         'name' => $mp->name,
            //         'aprvtotalday' => 1,
            //         'status' => 'P',
            //         'created_user' => request()->user()->email
            //     ]);
            // }

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

    public function getAllAgendas()
    {
        return response()->json(Agenda::all());
    }

    public function getTodayAgendas()
    {
        $today = Carbon::now()->format('Y-m-d'); // Format tanggal hari ini
        
        $agendas = Agenda::whereDate('startdate', '=', $today) // Filter hanya agenda hari ini
            ->orderBy('startdate', 'asc')
            ->get();       
        return response()->json($agendas);
    }
 

    public function getAgendas(Request $request)
    {
        $date = $request->query('date', Carbon::now()->format('Y-m-d')); // Default ke hari ini jika tidak ada tanggal

        if (!$date) {
            return response()->json(['error' => 'Date parameter is required'], 400);
        }

        $agendas = Agenda::whereDate('startdate', $date)
            ->orderBy('startdate', 'asc')
            ->get();

        return response()->json($agendas);
    }

    public function show($id)
    {
        $agenda = Agenda::find($id);

        if (!$agenda) {
            return response()->json(['message' => 'Agenda not found'], 404);
        }

        return response()->json($agenda);
    }




    // public function edit($id)
    // {
    //     $post = Agenda::findOrFail($id);
    //     return response()->json($post);
    // }

    public function update(Request $request, $id)
    {
        // dd($request->all());
        $agenda = Agenda::find($id);

        if (!$agenda) {
            return response()->json(['message' => 'Agenda not found'], 404);
        }

        // Validasi input
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'startdate' => 'required|date_format:Y-m-d\TH:i',
            'enddate' => 'required|date_format:Y-m-d\TH:i',
            'participant' => 'array'
        ]);

        // Update agenda
        $agenda->update([
            'title' => $request->title,
            'description' => $request->description,
            'startdate' => $request->startdate,
            'enddate' => $request->enddate,
            'participant' => implode(',', $request->participant ?? [])
        ]);

        return response()->json(['message' => 'Agenda updated successfully', 'agenda' => $agenda]);
    }


    public function getMonthlyAgendas(Request $request)
{
    $year = $request->input('year');
    $month = $request->input('month');

    $agendas = Agenda::whereYear('startdate', $year)
                     ->whereMonth('startdate', $month)
                     ->get();

    return response()->json($agendas);
}

    




}
