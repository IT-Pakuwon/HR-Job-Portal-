<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\MsCompany;
use App\Models\MsTrainingEvent;
use App\Models\StoGrading;
use App\Models\TrTrainingSchedule;
use App\Models\TrTrainingScheduleDetail;
use App\Models\TrTrainingScheduleQuota;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;

class TrainingSessionController extends Controller
{
    use HasAutonbr;

    protected const SCHEDULE_DOCTYPE = 'TSC';

    protected const ALLOWED_STATUS_TRANSITIONS = [
        'DRAFT' => ['PUBLISHED', 'CANCELLED'],
        'PUBLISHED' => ['CLOSED', 'CANCELLED'],
        'CLOSED' => [],
        'CANCELLED' => [],
    ];

    private function resolveTraining(string $hash): MsTrainingEvent
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        return MsTrainingEvent::findOrFail($id);
    }

    /**
     * Headers (one per Add-Schedule batch) for this training, with their
     * dates (details) and each date's own quota eager loaded.
     */
    private function scheduleQuery($trainingId)
    {
        return TrTrainingSchedule::where('training_id', $trainingId)
            ->with(['details' => fn ($q) => $q->orderBy('schedule_date'), 'details.quota'])
            ->orderBy('grade_id');
    }

    /**
     * Flatten header + details into one row per date — same shape the
     * frontend already groups by grade_id, just now `id` refers to the
     * date's own detail row (what Edit/Publish/Close/Cancel act on),
     * while `docid` is the shared batch code.
     */
    private function decorateSchedules($headers)
    {
        $gradeNames = StoGrading::whereIn('grade_id', $headers->pluck('grade_id')->unique())
            ->pluck('grade_name', 'grade_id');

        $speakerUsernames = $headers->pluck('speaker_username')->filter()->unique();
        $speakerNames = $speakerUsernames->isEmpty()
            ? collect()
            : User::whereIn('username', $speakerUsernames)->pluck('name', 'username');

        $cpnyIds = $headers->flatMap(fn ($h) => $h->details->flatMap(fn ($d) => $d->quota->pluck('cpny_id')))->unique();
        $cpnyNames = $cpnyIds->isEmpty()
            ? collect()
            : MsCompany::whereIn('cpny_id', $cpnyIds)->pluck('cpny_name', 'cpny_id');

        $rows = collect();

        foreach ($headers as $header) {
            foreach ($header->details as $detail) {
                $rows->push([
                    'id' => $detail->id,
                    'docid' => $header->docid,
                    'training_id' => $header->training_id,
                    'grade_id' => $header->grade_id,
                    'grade_name' => $gradeNames[$header->grade_id] ?? $header->grade_id,
                    'schedule_date' => $detail->schedule_date,
                    'start_time' => $detail->start_time,
                    'end_time' => $detail->end_time,
                    'mode' => $detail->mode,
                    'location' => $detail->location,
                    'platform' => $detail->platform,
                    'meeting_link' => $detail->meeting_link,
                    'poster_url' => $header->poster ? asset($header->poster) : null,
                    'speaker_username' => $header->speaker_username,
                    'speaker_name' => $header->speaker_username
                        ? ($speakerNames[$header->speaker_username] ?? $header->speaker_username)
                        : null,
                    'registration_deadline' => $detail->registration_deadline,
                    'status' => $detail->status,
                    'quota' => $detail->quota->map(fn ($q) => [
                        'cpny_id' => $q->cpny_id,
                        'cpny_name' => $cpnyNames[$q->cpny_id] ?? $q->cpny_id,
                        'quota_pax' => $q->quota_pax,
                    ]),
                    'quota_total' => $detail->quota->sum('quota_pax'),
                ]);
            }
        }

        return $rows->sortBy('schedule_date')->values();
    }

    public function show($hash)
    {
        $training = $this->resolveTraining($hash);

        $headers = $this->scheduleQuery($training->training_id)->get();
        $decorated = $this->decorateSchedules($headers);

        $levels = $decorated->groupBy('grade_id')->map(function ($rows, $gradeId) {
            return [
                'grade_id' => $gradeId,
                'grade_name' => $rows->first()['grade_name'],
                'schedule_count' => $rows->count(),
                'status_counts' => $rows->countBy('status'),
            ];
        })->values();

        return response()->json([
            'training' => $training,
            'levels' => $levels,
        ]);
    }

    public function manage($hash)
    {
        $training = $this->resolveTraining($hash);

        return view('pages.master_training.sessions', [
            'training' => $training,
            'hash' => $hash,
        ]);
    }

    public function schedules($hash)
    {
        $training = $this->resolveTraining($hash);

        $headers = $this->scheduleQuery($training->training_id)->get();

        return response()->json([
            'data' => $this->decorateSchedules($headers),
        ]);
    }

    /**
     * Rules for creating a batch: one level, one shared speaker/poster,
     * and one-or-more dates. Quota is entered once and applied to every
     * date in the batch (each date still tracks its own quota independently
     * from there on).
     */
    private function batchRules(): array
    {
        return [
            'grade_id' => 'required|string|max:20',
            'speaker_username' => 'nullable|string|max:50',
            'poster' => 'required|file|mimes:jpg,jpeg,png|max:5120',
            'dates' => 'required|array|min:1',
            'dates.*.schedule_date' => 'required|date|after_or_equal:today',
            'dates.*.start_time' => 'required|date_format:H:i',
            'dates.*.end_time' => 'required|date_format:H:i|after:dates.*.start_time',
            'dates.*.mode' => 'required|in:ONLINE,OFFLINE,HYBRID',
            'dates.*.location' => 'nullable|string|max:255',
            'dates.*.platform' => 'nullable|string|max:100',
            'dates.*.meeting_link' => 'nullable|string|max:255',
            'dates.*.registration_deadline' => 'nullable|date|after_or_equal:today',
            'quota' => 'nullable|array',
            'quota.*.cpny_id' => 'required_with:quota|string|max:10',
            'quota.*.quota_pax' => 'required_with:quota|integer|min:1',
        ];
    }

    /**
     * Rules for editing a single date. Level/speaker/poster are batch-level
     * (editing them here updates the shared header, affecting every other
     * date in the same batch too — that's intentional).
     */
    private function dateRules(): array
    {
        return [
            'grade_id' => 'required|string|max:20',
            'speaker_username' => 'nullable|string|max:50',
            'poster' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'schedule_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'mode' => 'required|in:ONLINE,OFFLINE,HYBRID',
            'location' => 'nullable|string|max:255',
            'platform' => 'nullable|string|max:100',
            'meeting_link' => 'nullable|string|max:255',
            'registration_deadline' => 'nullable|date|after_or_equal:today',
            'quota' => 'nullable|array',
            'quota.*.cpny_id' => 'required_with:quota|string|max:10',
            'quota.*.quota_pax' => 'required_with:quota|integer|min:1',
        ];
    }

    private function uploadPoster(Request $request): ?string
    {
        if (!$request->hasFile('poster')) {
            return null;
        }

        $file = $request->file('poster');
        $year = now()->year;
        $filename = md5(random_int(10000000, 99999999)) . '-' . str_replace('%', '', $file->getClientOriginalName());

        $folder = public_path('/training_posters/' . $year);
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        $file->move($folder, $filename);

        return 'training_posters/' . $year . '/' . $filename;
    }

    private function generateScheduleCode(string $username): string
    {
        $year = (int) Carbon::now()->year;

        $auto = $this->nextAutonbr(
            self::SCHEDULE_DOCTYPE,
            $year,
            '00',
            $username,
            'Training Schedule'
        );

        $yy = substr((string) $year, 2, 2);

        return self::SCHEDULE_DOCTYPE . $yy . sprintf('%04d', $auto['next']);
    }

    public function storeSchedule(Request $request, $hash)
    {
        $training = $this->resolveTraining($hash);

        $request->validate($this->batchRules());

        DB::connection('pgsql5')->beginTransaction();

        try {
            $user = Auth::user();
            $createdBy = $user->username ?? 'system';

            $docid = $this->generateScheduleCode($createdBy);

            $schedule = TrTrainingSchedule::create([
                'docid' => $docid,
                'training_id' => $training->training_id,
                'grade_id' => trim($request->grade_id),
                'poster' => $this->uploadPoster($request),
                'speaker_username' => $training->training_type === 'INTERNAL'
                    ? $request->speaker_username
                    : null,
                'created_by' => $createdBy,
            ]);

            $quotaRows = $request->input('quota', []);

            foreach ($request->dates as $line => $dateRow) {
                $deadline = !empty($dateRow['registration_deadline'])
                    ? $dateRow['registration_deadline']
                    : max(Carbon::parse($dateRow['schedule_date'])->subDays(3), Carbon::today())->toDateString();

                $detail = TrTrainingScheduleDetail::create([
                    'docid' => $docid,
                    'linenbr' => $line + 1,
                    'schedule_date' => $dateRow['schedule_date'],
                    'start_time' => $dateRow['start_time'],
                    'end_time' => $dateRow['end_time'],
                    'mode' => $dateRow['mode'],
                    'location' => $dateRow['location'] ?? null,
                    'platform' => $dateRow['platform'] ?? null,
                    'meeting_link' => $dateRow['meeting_link'] ?? null,
                    'registration_deadline' => $deadline,
                    'status' => 'DRAFT',
                    'created_by' => $createdBy,
                ]);

                $this->syncQuota($detail, $quotaRows, $createdBy);
            }

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'data' => $schedule,
                'message' => count($request->dates) > 1
                    ? count($request->dates) . ' schedules berhasil disimpan'
                    : 'Schedule berhasil disimpan',
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan schedule',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateSchedule(Request $request, $id)
    {
        $detail = TrTrainingScheduleDetail::findOrFail($id);

        if ($detail->status !== 'DRAFT') {
            return response()->json([
                'success' => false,
                'message' => "Schedule berstatus {$detail->status} tidak dapat diubah — hanya schedule berstatus DRAFT yang dapat diedit",
            ], 422);
        }

        $schedule = TrTrainingSchedule::where('docid', $detail->docid)->firstOrFail();
        $training = MsTrainingEvent::where('training_id', $schedule->training_id)->firstOrFail();

        $request->validate($this->dateRules());

        DB::connection('pgsql5')->beginTransaction();

        try {
            $user = Auth::user();
            $updatedBy = $user->username ?? 'system';

            $newPoster = $this->uploadPoster($request);

            $schedule->update([
                'grade_id' => trim($request->grade_id),
                'poster' => $newPoster ?? $schedule->poster,
                'speaker_username' => $training->training_type === 'INTERNAL'
                    ? $request->speaker_username
                    : null,
                'updated_by' => $updatedBy,
            ]);

            $deadline = $request->filled('registration_deadline')
                ? $request->registration_deadline
                : max(Carbon::parse($request->schedule_date)->subDays(3), Carbon::today())->toDateString();

            $detail->update([
                'schedule_date' => $request->schedule_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'mode' => $request->mode,
                'location' => $request->location,
                'platform' => $request->platform,
                'meeting_link' => $request->meeting_link,
                'registration_deadline' => $deadline,
                'updated_by' => $updatedBy,
            ]);

            $detail->quota()->delete();
            $this->syncQuota($detail, $request->input('quota', []), $updatedBy);

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Schedule berhasil diupdate',
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal update schedule',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function syncQuota(TrTrainingScheduleDetail $detail, array $quotaRows, string $username): void
    {
        foreach ($quotaRows as $row) {
            if (empty($row['cpny_id'])) {
                continue;
            }

            TrTrainingScheduleQuota::create([
                'schedule_detail_id' => $detail->id,
                'cpny_id' => trim($row['cpny_id']),
                'quota_pax' => (int) $row['quota_pax'],
                'created_by' => $username,
            ]);
        }
    }

    public function scheduleStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:DRAFT,PUBLISHED,CLOSED,CANCELLED',
        ]);

        $detail = TrTrainingScheduleDetail::findOrFail($id);

        $allowed = self::ALLOWED_STATUS_TRANSITIONS[$detail->status] ?? [];

        if (!in_array($request->status, $allowed, true)) {
            return response()->json([
                'success' => false,
                'message' => "Tidak bisa mengubah status dari {$detail->status} ke {$request->status}",
            ], 422);
        }

        $user = Auth::user();

        $detail->update([
            'status' => $request->status,
            'updated_by' => $user->username ?? 'system',
        ]);

        return response()->json([
            'success' => true,
            'status' => $request->status,
            'message' => 'Status schedule berhasil diupdate',
        ]);
    }

    public function gradeSearch(Request $request)
    {
        $search = trim((string) $request->get('q', ''));

        $query = StoGrading::query()->where('status', 'A');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('grade_id', 'ilike', "%{$search}%")
                    ->orWhere('grade_name', 'ilike', "%{$search}%");
            });
        }

        $rows = $query->orderBy('grade_name')->limit(50)->get();

        return response()->json([
            'results' => $rows->map(fn ($row) => [
                'id' => $row->grade_id,
                'text' => $row->grade_name,
            ]),
        ]);
    }

    public function speakerSearch(Request $request)
    {
        $search = trim((string) $request->get('q', ''));

        $query = User::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('username', 'ilike', "%{$search}%");
            });
        }

        $rows = $query->orderBy('name')->limit(50)->get(['username', 'name']);

        return response()->json([
            'results' => $rows->map(fn ($row) => [
                'id' => $row->username,
                'text' => $row->name ?: $row->username,
            ]),
        ]);
    }

    public function companySearch(Request $request)
    {
        $search = trim((string) $request->get('q', ''));

        $query = MsCompany::query()->where('status', 'A');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('cpny_id', 'ilike', "%{$search}%")
                    ->orWhere('cpny_name', 'ilike', "%{$search}%");
            });
        }

        $rows = $query->orderBy('cpny_name')->limit(50)->get();

        return response()->json([
            'results' => $rows->map(fn ($row) => [
                'id' => $row->cpny_id,
                'text' => $row->cpny_name,
            ]),
        ]);
    }
}
