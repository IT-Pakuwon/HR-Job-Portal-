<?php

namespace App\Http\Controllers;

use App\Models\ProjectTask;
use App\Models\UserDas;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Autonbr;
use Illuminate\Support\Facades\DB;

class ProjectTaskController extends Controller
{
    public function index()
    {
        // Ambil semua task
        $tasks = ProjectTask::all();     
        $participants = UserDas::select('username', 'name')->get();
        
        return view('pages.tasks.index', compact('tasks', 'participants'));
    }

    public function getParticipants()
{
    return response()->json(UserDas::select('username', 'name')->get());
}



    // Mengambil data dalam format JSON
    public function json()
    {
        return response()->json(ProjectTask::latest()->get());
    }

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'summary' => 'required|string|max:255',
            'taskpriority' => 'required|string',
            'startdate' => 'nullable|date',
            'duedate' => 'nullable|date|after_or_equal:startdate',
            'status' => 'nullable|string|max:1',
            'description' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $datenow = Carbon::now()->format('Y-m-d');
            $datestamp = Carbon::now()->toDateTimeString();
            $dt = Carbon::now();
            $year = $dt->year;
            $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
            $doctype = 'TSK';

            // Ambil nomor terakhir dari tabel Autonbr
            $autonbr = Autonbr::lockForUpdate()
                ->where('doctype', $doctype)
                ->where('year', $year)
                ->where('month', $month)
                ->where('status', 'A')
                ->first();

            if (!$autonbr) {
                // Jika belum ada nomor di database, buat entri baru
                $autonbr = Autonbr::create([
                    'doctype' => $doctype,
                    'year' => $year,
                    'month' => $month,
                    'status' => 'A',
                    'number' => 1
                ]);
                $urutan = 1;
            } else {
                // Ambil nomor terakhir dan tambahkan 1
                $urutan = $autonbr->number + 1;
                $autonbr->number = $urutan;
                $autonbr->save();
            }

            // Buat task ID
            $tglbln = substr($year, 2) . $month;
            $docid = $doctype . $tglbln . sprintf("%03d", $urutan);

            // Buat task baru
            $task = ProjectTask::create([
                'taskid' => $docid,
                'taskdate' => $datenow,
                'summary' => $request->summary,
                'taskpriority' => $request->taskpriority,
                'status' => $request->status ?? 'A', // Default ke 'A' jika null
                'startdate' => $request->startdate ? Carbon::parse($request->startdate)->format('Y-m-d') : null,
                'duedate' => $request->duedate ? Carbon::parse($request->duedate)->format('Y-m-d') : null,
                'description' => $request->description
            ]);

            DB::commit();
            return response()->json($task);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan task', 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'summary' => 'required|string|max:255',
            'taskpriority' => 'required|string',
            'startdate' => 'nullable|date',
            'duedate' => 'nullable|date|after_or_equal:startdate',
            'status' => 'nullable|string|max:1',
            'description' => 'nullable|string'
        ]);

        $task = ProjectTask::findOrFail($id);

        // Update task
        $task->update([
            'summary' => $request->summary,
            'taskpriority' => $request->taskpriority,
            'status' => $request->status,
            'startdate' => $request->startdate ? Carbon::parse($request->startdate)->format('Y-m-d') : null,
            'duedate' => $request->duedate ? Carbon::parse($request->duedate)->format('Y-m-d') : null,
            'description' => $request->description
        ]);

        return response()->json($task);
    }

    public function destroy($id)
    {
        $task = ProjectTask::findOrFail($id);
        $task->delete();

        return response()->json(['message' => 'Data berhasil dihapus']);
    }
}
