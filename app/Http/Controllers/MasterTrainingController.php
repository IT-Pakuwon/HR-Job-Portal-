<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\MsCategory;
use App\Models\MsTrainingEvent;
use App\Models\MsLndTrainingSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;

class MasterTrainingController extends Controller
{
    use HasAutonbr;

    protected const DOCTYPE = 'TE';

    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        return view('pages.master_training.master');
    }

    public function json()
    {
        $rows = MsTrainingEvent::select([
                'id',
                'training_id',
                'training_name',
                'category_id',
                'is_mandatory',
                'training_description',
                'training_type',
                'status',
            ])
            ->orderByDesc('id')
            ->get();

        // One row per dated schedule (ms_lnd_training_schedule), aggregated
        // per training via its header table — avoids an N+1 query per row.
        $scheduleCounts = DB::connection('pgsql5')
            ->table('ms_lnd_training_detail as h')
            ->join('ms_lnd_training_schedule as d', 'd.training_detail_id', '=', 'h.training_detail_id')
            ->select('h.training_id', DB::raw('count(*) as cnt'))
            ->groupBy('h.training_id')
            ->pluck('cnt', 'training_id');

        $rows->each(function ($row) use ($scheduleCounts) {
            $row->eid = Hashids::encode($row->id);
            $row->schedule_count = (int) ($scheduleCounts[$row->training_id] ?? 0);
        });

        return response()->json([
            'data' => $rows,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'training_name'         => 'required|string|max:255',
            'category_id'           => 'required|string|max:50',
            'is_mandatory'          => 'nullable|boolean',
            'training_description'  => 'nullable|string',
            'training_type'         => 'required|in:INTERNAL,EXTERNAL',
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {
            $user = Auth::user();
            $now = now();
            $createdBy = $user->username ?? 'system';

            $trainingId = $this->generateTrainingCode($createdBy);

            $row = MsTrainingEvent::create([
                'training_id'           => $trainingId,
                'training_name'         => trim($request->training_name),
                'category_id'           => trim($request->category_id),
                'is_mandatory'          => $request->boolean('is_mandatory'),
                'training_description'  => $request->filled('training_description') ? trim($request->training_description) : null,
                'training_type'         => $request->training_type,
                'status'                => 'A',
                'created_by'            => $createdBy,
                'created_at'            => $now,
            ]);

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'data'    => $row,
                'message' => 'Training berhasil disimpan',
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan training',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        $row = MsTrainingEvent::findOrFail($id);

        return response()->json([
            'id'                     => $row->id,
            'training_id'            => $row->training_id,
            'training_name'          => $row->training_name,
            'category_id'            => $row->category_id,
            'is_mandatory'           => $row->is_mandatory,
            'training_description'   => $row->training_description,
            'training_type'          => $row->training_type,
            'status'                 => $row->status,
        ]);
    }

    public function update(Request $request, $id)
    {
        $row = MsTrainingEvent::findOrFail($id);

        $request->validate([
            'training_name'         => 'required|string|max:255',
            'category_id'           => 'required|string|max:50',
            'is_mandatory'          => 'nullable|boolean',
            'training_description'  => 'nullable|string',
            'training_type'         => 'required|in:INTERNAL,EXTERNAL',
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {
            $user = Auth::user();
            $now = now();
            $updatedBy = $user->username ?? 'system';

            $row->update([
                'training_name'         => trim($request->training_name),
                'category_id'           => trim($request->category_id),
                'is_mandatory'          => $request->boolean('is_mandatory'),
                'training_description'  => $request->filled('training_description') ? trim($request->training_description) : null,
                'training_type'         => $request->training_type,
                'updated_by'            => $updatedBy,
                'updated_at'            => $now,
            ]);

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Training berhasil diupdate',
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal update training',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:A,X',
        ]);

        $row = MsTrainingEvent::findOrFail($id);
        $user = Auth::user();

        if ($request->status === 'X') {
            // ms_lnd_training_schedule.status is stored as a single-letter code
            // (see TrainingSessionController::STATUS_CODE_MAP) — 'C' = Closed,
            // 'X' = Cancelled. Anything else (Draft/Published) counts as open.
            $hasOpenSchedule = MsLndTrainingSchedule::whereHas('schedule', function ($q) use ($row) {
                    $q->where('training_id', $row->training_id);
                })
                ->whereNotIn('status', ['C', 'X'])
                ->exists();

            if ($hasOpenSchedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'Training tidak dapat dinonaktifkan karena masih memiliki schedule yang belum berstatus Closed',
                ], 422);
            }
        }

        $row->update([
            'status'     => $request->status,
            'updated_by' => $user->username ?? 'system',
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'status'  => $request->status,
            'message' => 'Status berhasil diupdate',
        ]);
    }

    public function categorySearch(Request $request)
    {
        $search = trim((string) $request->get('q', ''));

        $query = MsCategory::query()
            ->where('doctype', self::DOCTYPE)
            ->where('status', 'A');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('categoryid', 'ilike', "%{$search}%")
                    ->orWhere('category_name', 'ilike', "%{$search}%");
            });
        }

        $rows = $query->orderBy('category_name')->limit(50)->get();

        return response()->json([
            'results' => $rows->map(fn ($row) => [
                'id'   => $row->categoryid,
                'text' => $row->category_name,
            ]),
        ]);
    }

    private function generateTrainingCode(string $username): string
    {
        $year = (int) Carbon::now()->year;

        $auto = $this->nextAutonbr(
            self::DOCTYPE,
            $year,
            '00',
            $username,
            'Training Event'
        );

        $yy = substr((string) $year, 2, 2);

        return self::DOCTYPE . $yy . sprintf('%04d', $auto['next']);
    }
}
