<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\UserDas;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Autonbr;
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
        return view('pages.agendas.index', compact('participant'));
        
        
    }

    /**
     * Mengambil data agenda untuk DataTables (JSON Response)
     */
    public function json()
    {
        $agenda = Agenda::select(['agenda_id', 'title', 'description','meeting_date','location','status'])
            ->latest()
            ->get();

        return response()->json(['data' => $agenda]);
    }

    /**
     * Mengambil daftar peserta untuk Select2
     */
    public function getParticipants()
    {
        return response()->json(UserDas::select('username', 'name')->get());
    }
    

    /**
     * Menyimpan agenda baru
     */
    public function store(Request $request)
    {
        // dd($request->all());
        // Validasi input
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'meeting_date' => 'nullable|date',
            'location' => 'nullable|string'            
        ]);

        DB::beginTransaction();
        try {
            $datenow = Carbon::now()->format('Y-m-d');
            $datestamp = Carbon::now()->toDateTimeString();
          
            // Buat agenda baru
            $agenda = Agenda::create([              
                'title' => $request->title,
                'description' => $request->description,
                'meeting_date' => $request->meetingdate,
                'location' => $request->location,
                'status' => $request->status ?? 'A'                
            ]);

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
        // dd($id);
        $agenda = Agenda::findOrFail($id);
        return response()->json($agenda);
    }

    /**
     * Mengupdate agenda berdasarkan ID
     */
    public function update(Request $request, $id)
    {
        // dd($request->all());
        // Validasi input
        $request->validate([
           'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'meeting_date' => 'nullable|date',
            'location' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $agenda = Agenda::findOrFail($id);
            $agenda->update([
                'title' => $request->title,
                'description' => $request->description,
                'meeting_date' => $request->meetingdate,
                'location' => $request->location,
                'status' => $request->status ?? 'A' 
            ]);

            DB::commit();
            return response()->json(['success' => true, 'agenda' => $agenda]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal memperbarui agenda', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Menghapus agenda berdasarkan ID
     */
    public function destroy($id)
    {
        $agenda = Agenda::findOrFail($id);
        $agenda->delete();

        return response()->json(['success' => true, 'message' => 'Data berhasil dihapus']);
    }
}
