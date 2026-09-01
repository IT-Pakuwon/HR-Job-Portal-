<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\MsCompany;
use App\Models\MsLndPlaces;
use App\Models\MsTrainingEvent;
use App\Models\StoGrading;
use App\Models\MsLndTrainingDetail;
use App\Models\MsLndTrainingSchedule;
use App\Models\MsLndTrainingQuota;
use App\Models\TrLndTrainingRegistration;
use App\Models\User;
use App\Services\TrainingWaitlistNotifier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Vinkla\Hashids\Facades\Hashids;

class TrainingSessionController extends Controller
{
    use HasAutonbr;

    protected const DETAIL_DOCTYPE = 'TSC';
    protected const SCHEDULE_DOCTYPE = 'TSD';

    protected const ALLOWED_STATUS_TRANSITIONS = [
        'DRAFT' => ['PUBLISHED', 'CANCELLED'],
        'PUBLISHED' => ['CLOSED', 'CANCELLED'],
        'CLOSED' => [],
        'CANCELLED' => [],
    ];

    /**
     * ms_lnd_training_schedule.status is varchar(1) — the DRAFT/PUBLISHED/
     * CLOSED/CANCELLED lifecycle is stored as single-letter codes and
     * translated back to full words at this controller's boundary so the
     * rest of the code (validation, JS, badges) keeps speaking full words.
     */
    protected const STATUS_CODE_MAP = [
        'DRAFT' => 'D',
        'PUBLISHED' => 'P',
        'CLOSED' => 'C',
        'CANCELLED' => 'X',
    ];

    protected const STATUS_LABEL_MAP = [
        'D' => 'DRAFT',
        'P' => 'PUBLISHED',
        'C' => 'CLOSED',
        'X' => 'CANCELLED',
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
        return MsLndTrainingDetail::where('training_id', $trainingId)
            ->with(['details' => fn ($q) => $q->orderBy('schedule_date'), 'details.quota'])
            ->orderBy('job_level');
    }

    /**
     * Flatten header + details into one row per date — same shape the
     * frontend already groups by job_level, just now `id` refers to the
     * date's own detail row (what Edit/Publish/Close/Cancel act on),
     * while `training_detail_id` is the shared batch code.
     */
    private function decorateSchedules($headers)
    {
        $gradeNames = StoGrading::whereIn('grade_id', $headers->pluck('job_level')->unique())
            ->pluck('grade_name', 'grade_id');

        $allDetails = $headers->flatMap(fn ($h) => $h->details);

        $speakerUsernames = $allDetails->pluck('training_speaker_username')->filter()->unique();
        $speakerNames = $speakerUsernames->isEmpty()
            ? collect()
            : User::whereIn('username', $speakerUsernames)->pluck('name', 'username');

        $placeIds = $allDetails->pluck('places_id')->filter()->unique();
        $placeNames = $placeIds->isEmpty()
            ? collect()
            : MsLndPlaces::whereIn('places_id', $placeIds)->pluck('places_name', 'places_id');

        $cpnyIds = $allDetails->flatMap(fn ($d) => $d->quota->pluck('cpny_id'))->unique();
        $cpnyNames = $cpnyIds->isEmpty()
            ? collect()
            : MsCompany::whereIn('cpny_id', $cpnyIds)->pluck('cpny_name', 'cpny_id');

        $rows = collect();

        foreach ($headers as $header) {
            foreach ($header->details as $detail) {
                $rows->push([
                    'id' => $detail->id,
                    'schedule_id' => $detail->schedule_id,
                    'training_detail_id' => $header->training_detail_id,
                    'training_id' => $header->training_id,
                    'job_level' => $header->job_level,
                    'grade_name' => $gradeNames[$header->job_level] ?? $header->job_level,
                    'training_detail_name' => $header->training_detail_name,
                    'training_poster' => $header->training_poster,
                    'training_poster_url' => $header->training_poster ? Storage::disk('public')->url($header->training_poster) : null,
                    'is_ext_speaker' => (bool) $header->is_ext_speaker,
                    'schedule_date' => $detail->schedule_date?->format('Y-m-d'),
                    'start_time' => $detail->schedule_start_time,
                    'end_time' => $detail->schedule_end_time,
                    'mode' => $detail->training_mode,
                    'places_id' => $detail->places_id,
                    'places_name' => $detail->places_id ? ($placeNames[$detail->places_id] ?? $detail->places_id) : null,
                    'platform' => $detail->training_platform,
                    'meeting_link' => $detail->training_meeting_link,
                    'training_speaker_username' => $detail->training_speaker_username,
                    'training_speaker_name' => $detail->training_speaker_username
                        ? ($speakerNames[$detail->training_speaker_username] ?? $detail->training_speaker_name)
                        : $detail->training_speaker_name,
                    'training_ext_speaker_name' => $detail->training_ext_speaker_name,
                    'registration_deadline' => $detail->registration_deadline,
                    'status' => self::STATUS_LABEL_MAP[$detail->status] ?? $detail->status,
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

        $levels = $decorated->groupBy('job_level')->map(function ($rows, $jobLevel) {
            return [
                'job_level' => $jobLevel,
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
     * Rules for creating a batch: one level, one batch name, one shared
     * speaker-source toggle, and one-or-more dates. Quota is entered once
     * and applied to every date in the batch (each date still tracks its
     * own quota independently from there on).
     */
    private function batchRules(): array
    {
        return [
            'job_level' => 'required|string|max:20',
            'training_detail_name' => 'required|string|max:255',
            'training_poster' => 'nullable|image|max:5120',
            'is_ext_speaker' => 'required|boolean',
            'dates' => 'required|array|min:1',
            'dates.*.schedule_date' => 'required|date|after_or_equal:today',
            'dates.*.start_time' => 'required|date_format:H:i',
            'dates.*.end_time' => 'required|date_format:H:i|after:dates.*.start_time',
            'dates.*.mode' => 'required|in:ONLINE,OFFLINE,HYBRID',
            'dates.*.places_id' => 'required_if:dates.*.mode,OFFLINE,HYBRID|nullable|string|max:20',
            'dates.*.platform' => 'nullable|string|max:100',
            'dates.*.meeting_link' => 'nullable|string|max:255',
            'dates.*.registration_deadline' => 'nullable|date|after_or_equal:today',
            'dates.*.speaker_username' => 'nullable|string|max:50',
            'dates.*.speaker_name' => 'nullable|string|max:255',
            'dates.*.ext_speaker_name' => 'required_if:is_ext_speaker,1|nullable|string|max:255',
            'quota' => 'nullable|array',
            'quota.*.cpny_id' => 'required_with:quota|string|max:10',
            'quota.*.quota_pax' => 'required_with:quota|integer|min:1',
        ];
    }

    /**
     * Rules for editing a single date. Level/batch name/speaker-source are
     * batch-level (editing them here updates the shared header, affecting
     * every other date in the same batch too — that's intentional).
     */
    private function dateRules(): array
    {
        return [
            'job_level' => 'required|string|max:20',
            'training_detail_name' => 'required|string|max:255',
            'training_poster' => 'nullable|image|max:5120',
            'is_ext_speaker' => 'required|boolean',
            'schedule_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'mode' => 'required|in:ONLINE,OFFLINE,HYBRID',
            'places_id' => 'required_if:mode,OFFLINE,HYBRID|nullable|string|max:20',
            'platform' => 'nullable|string|max:100',
            'meeting_link' => 'nullable|string|max:255',
            'registration_deadline' => 'nullable|date|after_or_equal:today',
            'speaker_username' => 'nullable|string|max:50',
            'speaker_name' => 'nullable|string|max:255',
            'ext_speaker_name' => 'required_if:is_ext_speaker,1|nullable|string|max:255',
            'quota' => 'nullable|array',
            'quota.*.cpny_id' => 'required_with:quota|string|max:10',
            'quota.*.quota_pax' => 'required_with:quota|integer|min:1',
        ];
    }

    private function generateTrainingDetailCode(string $username): string
    {
        $year = (int) Carbon::now()->year;

        $auto = $this->nextAutonbr(
            self::DETAIL_DOCTYPE,
            $year,
            '00',
            $username,
            'Training Schedule Batch'
        );

        $yy = substr((string) $year, 2, 2);

        return self::DETAIL_DOCTYPE . $yy . sprintf('%04d', $auto['next']);
    }

    private function generateScheduleDateCode(string $username): string
    {
        $year = (int) Carbon::now()->year;

        $auto = $this->nextAutonbr(
            self::SCHEDULE_DOCTYPE,
            $year,
            '00',
            $username,
            'Training Schedule Date'
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

            $trainingDetailId = $this->generateTrainingDetailCode($createdBy);
            $isExtSpeaker = $request->boolean('is_ext_speaker');

            $posterPath = $request->hasFile('training_poster')
                ? $request->file('training_poster')->store('training_posters', 'public')
                : null;

            $schedule = MsLndTrainingDetail::create([
                'training_detail_id' => $trainingDetailId,
                'training_id' => $training->training_id,
                'training_detail_name' => trim($request->training_detail_name),
                'training_poster' => $posterPath,
                'job_level' => trim($request->job_level),
                'is_ext_speaker' => $isExtSpeaker,
                'status' => 'A',
                'created_by' => $createdBy,
            ]);

            $quotaRows = $request->input('quota', []);

            foreach ($request->dates as $line => $dateRow) {
                $deadline = !empty($dateRow['registration_deadline'])
                    ? $dateRow['registration_deadline']
                    : max(Carbon::parse($dateRow['schedule_date'])->subDays(3), Carbon::today())->toDateString();

                $isOffsite = in_array($dateRow['mode'], ['OFFLINE', 'HYBRID'], true);

                $detail = MsLndTrainingSchedule::create([
                    'schedule_id' => $this->generateScheduleDateCode($createdBy),
                    'training_id' => $training->training_id,
                    'training_detail_id' => $trainingDetailId,
                    'schedule_date' => $dateRow['schedule_date'],
                    'schedule_start_time' => $dateRow['start_time'],
                    'schedule_end_time' => $dateRow['end_time'],
                    'places_id' => $isOffsite ? ($dateRow['places_id'] ?? null) : null,
                    'training_mode' => $dateRow['mode'],
                    'training_platform' => $dateRow['platform'] ?? null,
                    'training_meeting_link' => $dateRow['meeting_link'] ?? null,
                    'registration_deadline' => $deadline,
                    'training_speaker_username' => $isExtSpeaker ? null : ($dateRow['speaker_username'] ?? null),
                    'training_speaker_name' => $isExtSpeaker ? null : ($dateRow['speaker_name'] ?? null),
                    'training_ext_speaker_name' => $isExtSpeaker ? trim($dateRow['ext_speaker_name']) : null,
                    'status' => self::STATUS_CODE_MAP['DRAFT'],
                    'created_by' => $createdBy,
                ]);

                $this->syncQuota($detail, $quotaRows, $training->training_id, $trainingDetailId, $createdBy);
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
        $detail = MsLndTrainingSchedule::findOrFail($id);

        if ($detail->status !== self::STATUS_CODE_MAP['DRAFT']) {
            $currentLabel = self::STATUS_LABEL_MAP[$detail->status] ?? $detail->status;

            return response()->json([
                'success' => false,
                'message' => "Schedule berstatus {$currentLabel} tidak dapat diubah — hanya schedule berstatus DRAFT yang dapat diedit",
            ], 422);
        }

        $schedule = MsLndTrainingDetail::where('training_detail_id', $detail->training_detail_id)->firstOrFail();

        $request->validate($this->dateRules());

        DB::connection('pgsql5')->beginTransaction();

        try {
            $user = Auth::user();
            $updatedBy = $user->username ?? 'system';

            $isExtSpeaker = $request->boolean('is_ext_speaker');

            $scheduleUpdate = [
                'job_level' => trim($request->job_level),
                'training_detail_name' => trim($request->training_detail_name),
                'is_ext_speaker' => $isExtSpeaker,
                'updated_by' => $updatedBy,
            ];

            if ($request->hasFile('training_poster')) {
                if ($schedule->training_poster) {
                    Storage::disk('public')->delete($schedule->training_poster);
                }

                $scheduleUpdate['training_poster'] = $request->file('training_poster')->store('training_posters', 'public');
            }

            $schedule->update($scheduleUpdate);

            $deadline = $request->filled('registration_deadline')
                ? $request->registration_deadline
                : max(Carbon::parse($request->schedule_date)->subDays(3), Carbon::today())->toDateString();

            $isOffsite = in_array($request->mode, ['OFFLINE', 'HYBRID'], true);

            $detail->update([
                'schedule_date' => $request->schedule_date,
                'schedule_start_time' => $request->start_time,
                'schedule_end_time' => $request->end_time,
                'places_id' => $isOffsite ? $request->places_id : null,
                'training_mode' => $request->mode,
                'training_platform' => $request->platform,
                'training_meeting_link' => $request->meeting_link,
                'registration_deadline' => $deadline,
                'training_speaker_username' => $isExtSpeaker ? null : $request->speaker_username,
                'training_speaker_name' => $isExtSpeaker ? null : $request->speaker_name,
                'training_ext_speaker_name' => $isExtSpeaker ? trim($request->ext_speaker_name) : null,
                'updated_by' => $updatedBy,
            ]);

            $detail->quota()->delete();
            $this->syncQuota($detail, $request->input('quota', []), $schedule->training_id, $schedule->training_detail_id, $updatedBy);

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

    /**
     * Move the date/time of a schedule that's already PUBLISHED or CLOSED —
     * unlike updateSchedule() this doesn't touch level/batch/quota, cascades
     * schedule_date onto every still-active registration (it's denormalized
     * on tr_lnd_training_registration), and notifies those registrants so a
     * silently-kept seat can't strand someone who can't make the new date.
     */
    public function reschedule(Request $request, $id)
    {
        $detail = MsLndTrainingSchedule::findOrFail($id);
        $currentLabel = self::STATUS_LABEL_MAP[$detail->status] ?? $detail->status;

        if (!in_array($currentLabel, ['PUBLISHED', 'CLOSED'], true)) {
            $message = $currentLabel === 'DRAFT'
                ? 'Schedule berstatus DRAFT belum perlu di-reschedule — gunakan Edit'
                : "Schedule berstatus {$currentLabel} tidak dapat di-reschedule";

            return response()->json(['success' => false, 'message' => $message], 422);
        }

        $request->validate([
            'schedule_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'registration_deadline' => 'nullable|date|after_or_equal:today',
            'reason' => 'required|string|max:500',
        ]);

        $oldDate = $detail->schedule_date?->toDateString();
        $newDate = $request->schedule_date;
        $reason = trim($request->reason);

        DB::connection('pgsql5')->beginTransaction();

        try {
            $user = Auth::user();
            $updatedBy = $user->username ?? 'system';

            $detail->update([
                'schedule_date' => $newDate,
                'schedule_start_time' => $request->start_time,
                'schedule_end_time' => $request->end_time,
                'registration_deadline' => $request->filled('registration_deadline')
                    ? $request->registration_deadline
                    : $detail->registration_deadline,
                // Moving a CLOSED schedule's date only makes sense if the
                // intent is to accept registrations again — the validated
                // schedule_date is already required to be >= today.
                'status' => $currentLabel === 'CLOSED' ? self::STATUS_CODE_MAP['PUBLISHED'] : $detail->status,
                'updated_by' => $updatedBy,
            ]);

            // Same "active registration" filter used by register()'s duplicate
            // check: seated (null), waitlisted ('W') or offered ('O') — never
            // cancelled, never a rejected approval.
            $registrations = TrLndTrainingRegistration::where('schedule_id', $detail->schedule_id)
                ->where(function ($q) {
                    $q->whereNull('status_registration')
                        ->orWhere('status_registration', '!=', TrLndTrainingRegistration::REG_STATUS_CANCELLED);
                })
                ->where('status', '!=', TrLndTrainingRegistration::STATUS_REJECTED)
                ->get();

            foreach ($registrations as $registration) {
                $registration->schedule_date = $newDate;
                $registration->updated_by = $updatedBy;
                $registration->save();
            }

            DB::connection('pgsql5')->commit();
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal reschedule',
                'error' => $e->getMessage(),
            ], 500);
        }

        foreach ($registrations as $registration) {
            TrainingWaitlistNotifier::notifyReschedule($registration->fresh(['schedule.schedule.training']), $oldDate, $newDate, $reason);
        }

        return response()->json([
            'success' => true,
            'message' => count($registrations) > 0
                ? 'Schedule berhasil di-reschedule, ' . count($registrations) . ' peserta telah diberi notifikasi'
                : 'Schedule berhasil di-reschedule',
        ]);
    }

    private function syncQuota(
        MsLndTrainingSchedule $detail,
        array $quotaRows,
        string $trainingId,
        string $trainingDetailId,
        string $username
    ): void {
        foreach ($quotaRows as $row) {
            if (empty($row['cpny_id'])) {
                continue;
            }

            MsLndTrainingQuota::create([
                'schedule_id' => $detail->schedule_id,
                'training_id' => $trainingId,
                'training_detail_id' => $trainingDetailId,
                'cpny_id' => trim($row['cpny_id']),
                'quota_pax' => (int) $row['quota_pax'],
                'status' => 'A',
                'created_by' => $username,
            ]);
        }
    }

    public function scheduleStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:DRAFT,PUBLISHED,CLOSED,CANCELLED',
        ]);

        $detail = MsLndTrainingSchedule::findOrFail($id);

        $currentLabel = self::STATUS_LABEL_MAP[$detail->status] ?? $detail->status;
        $allowed = self::ALLOWED_STATUS_TRANSITIONS[$currentLabel] ?? [];

        if (!in_array($request->status, $allowed, true)) {
            return response()->json([
                'success' => false,
                'message' => "Tidak bisa mengubah status dari {$currentLabel} ke {$request->status}",
            ], 422);
        }

        $user = Auth::user();

        $detail->update([
            'status' => self::STATUS_CODE_MAP[$request->status],
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

    public function placeSearch(Request $request)
    {
        $search = trim((string) $request->get('q', ''));

        $query = MsLndPlaces::query()->where('status', 'A');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('places_id', 'ilike', "%{$search}%")
                    ->orWhere('places_name', 'ilike', "%{$search}%");
            });
        }

        $rows = $query->orderBy('places_name')->limit(50)->get();

        return response()->json([
            'results' => $rows->map(fn ($row) => [
                'id' => $row->places_id,
                'text' => $row->places_name,
            ]),
        ]);
    }
}
