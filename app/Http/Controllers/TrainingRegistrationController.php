<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAttendanceWindow;
use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\MsCategory;
use App\Models\MsCompany;
use App\Models\MsDepartment;
use App\Models\MsTrainingEvent;
use App\Models\StoGrading;
use App\Models\TrTrainingRegistration;
use App\Models\TrTrainingScheduleDetail;
use App\Models\TrTrainingScheduleQuota;
use App\Models\User;
use App\Services\TrainingRegistrationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Vinkla\Hashids\Facades\Hashids;

class TrainingRegistrationController extends Controller
{
    use HasAutonbr;
    use HasAttendanceWindow;

    protected const DOCTYPE = 'TRN';

    public function index()
    {
        return view('pages.training_list.index', ['initialEid' => null]);
    }

    /**
     * Same browse page, but with a specific training's detail modal
     * auto-opened on load — gives the modal a real, shareable/bookmarkable
     * URL (same hash-id convention as mastertraining.view) without making
     * the detail view its own full page navigation.
     */
    public function show($eid)
    {
        $id = Hashids::decode($eid)[0] ?? null;
        abort_if(!$id, 404);

        MsTrainingEvent::findOrFail($id);

        return view('pages.training_list.index', ['initialEid' => $eid]);
    }

    /**
     * Open (PUBLISHED, not-yet-deadline) schedules with per-company quota
     * availability, grouped one row per training — a training with several
     * open dates/levels is a single card, and the specific date is picked at
     * register time rather than showing one duplicate-looking card per date.
     *
     * ms_user.cpny_id/department_id can each hold a comma-separated list (a
     * person can belong to several companies and/or departments) — every
     * matching company's quota is surfaced so the frontend can offer a
     * picker instead of silently guessing one.
     *
     * Optional ?training_id= narrows to a single training (used by the
     * dedicated event page instead of the full browse list).
     */
    public function json(Request $request)
    {
        $user = Auth::user();
        $userCpnyIds = $this->splitMulti($user->cpny_id);
        $userDeptIds = $this->splitMulti($user->department_id);
        $onlyTrainingId = $request->query('training_id');

        $details = TrTrainingScheduleDetail::query()
            ->where('status', 'PUBLISHED')
            ->when($onlyTrainingId, fn ($q) => $q->whereHas('schedule', fn ($q2) => $q2->where('training_id', $onlyTrainingId)))
            ->with([
                'schedule.training',
                'quota' => fn ($q) => $q->whereIn('cpny_id', $userCpnyIds),
            ])
            ->orderBy('schedule_date')
            ->get();

        $detailIds = $details->pluck('id');

        $myRegs = TrTrainingRegistration::whereIn('schedule_detail_id', $detailIds)
            ->where('username', $user->username)
            ->whereIn('status', [
                TrTrainingRegistration::STATUS_PENDING,
                TrTrainingRegistration::STATUS_APPROVED,
                TrTrainingRegistration::STATUS_WAITLISTED,
                TrTrainingRegistration::STATUS_OFFERED,
            ])
            ->get()
            ->keyBy('schedule_detail_id');

        $counts = TrTrainingRegistration::whereIn('schedule_detail_id', $detailIds)
            ->whereIn('status', [TrTrainingRegistration::STATUS_PENDING, TrTrainingRegistration::STATUS_APPROVED])
            ->select('schedule_detail_id', 'cpny_id', 'status', DB::raw('count(*) as cnt'))
            ->groupBy('schedule_detail_id', 'cpny_id', 'status')
            ->get()
            ->groupBy('schedule_detail_id');

        $companyNames = MsCompany::whereIn('cpny_id', $userCpnyIds)->pluck('cpny_name', 'cpny_id');
        $gradeNames = StoGrading::whereIn('grade_id', $details->pluck('schedule.grade_id')->filter()->unique())
            ->pluck('grade_name', 'grade_id');
        $speakerNames = User::whereIn('username', $details->pluck('schedule.speaker_username')->filter()->unique())
            ->pluck('name', 'username');

        $scheduleOptions = $details->map(function ($d) use ($myRegs, $counts, $companyNames, $gradeNames, $speakerNames) {
            $grouped = $counts->get($d->id, collect());

            $eligibleCompanies = $d->quota->map(function ($q) use ($grouped, $companyNames) {
                $reserved = (int) $grouped->where('cpny_id', $q->cpny_id)->where('status', 'P')->sum('cnt');
                $used = (int) $grouped->where('cpny_id', $q->cpny_id)->where('status', TrTrainingRegistration::STATUS_APPROVED)->sum('cnt');

                return [
                    'cpny_id' => $q->cpny_id,
                    'cpny_name' => $companyNames[$q->cpny_id] ?? $q->cpny_id,
                    'quota_pax' => $q->quota_pax,
                    'reserved' => $reserved,
                    'used' => $used,
                    'available' => max(0, $q->quota_pax - $reserved - $used),
                ];
            })->values();

            $mine = $myRegs->get($d->id);

            return [
                'id' => $d->id,
                'training_id' => $d->schedule->training_id,
                'training_name' => $d->schedule->training->training_name ?? null,
                'docid' => $d->docid,
                'schedule_date' => $d->schedule_date,
                'start_time' => $d->start_time,
                'end_time' => $d->end_time,
                'mode' => $d->mode,
                'location' => $d->location,
                'platform' => $d->platform,
                'meeting_link' => $d->meeting_link,
                'poster_url' => $d->schedule->poster ? asset($d->schedule->poster) : null,
                'grade_id' => $d->schedule->grade_id,
                'grade_name' => $gradeNames[$d->schedule->grade_id] ?? $d->schedule->grade_id,
                'speaker_name' => $d->schedule->speaker_username
                    ? ($speakerNames[$d->schedule->speaker_username] ?? $d->schedule->speaker_username)
                    : null,
                'registration_deadline' => $d->registration_deadline,
                'is_open' => !$d->registration_deadline || Carbon::parse($d->registration_deadline)->isFuture() || Carbon::parse($d->registration_deadline)->isToday(),
                'eligible_companies' => $eligibleCompanies,
                'my_status' => $mine->status ?? null,
                'my_registration_id' => $mine->id ?? null,
            ];
        });

        $trainingsById = $details->pluck('schedule.training')->filter()->unique('training_id')->keyBy('training_id');
        $categoryNames = MsCategory::where('doctype', 'TE')
            ->whereIn('categoryid', $trainingsById->pluck('category_id')->filter()->unique())
            ->pluck('category_name', 'categoryid');

        // One card per docid — a distinct HR "Add Schedule" batch (one level,
        // one speaker/poster). Different levels are different batches even
        // when they share a training name, so they must NOT be merged into
        // one card; only same-docid dates (already one batch) consolidate.
        $rows = $scheduleOptions->groupBy('docid')->map(function ($schedules) use ($trainingsById, $categoryNames) {
            $first = $schedules->first();
            $training = $trainingsById->get($first['training_id']);

            return [
                'docid' => $first['docid'],
                'training_id' => $first['training_id'],
                'eid' => $training ? Hashids::encode($training->id) : null,
                'training_name' => $first['training_name'],
                'poster_url' => $first['poster_url'],
                'description' => $training->description ?? null,
                'category_name' => $categoryNames[$training->category_id ?? null] ?? null,
                'training_type' => $training->training_type ?? null,
                'is_mandatory' => (bool) ($training->is_mandatory ?? false),
                'levels' => $schedules->pluck('grade_name')->filter()->unique()->values(),
                'speakers' => $schedules->pluck('speaker_name')->filter()->unique()->values(),
                'schedule_count' => $schedules->count(),
                'eligible' => $schedules->contains(fn ($s) => count($s['eligible_companies']) > 0),
                'schedules' => $schedules->values(),
            ];
        })->values();

        $departmentNames = MsDepartment::whereIn('department_id', $userDeptIds)->pluck('department_name', 'department_id');

        return response()->json([
            'data' => $rows,
            'department_options' => $userDeptIds->map(fn ($id) => [
                'id' => $id,
                'name' => $departmentNames[$id] ?? $id,
            ])->values(),
        ]);
    }

    private function splitMulti(?string $raw): \Illuminate\Support\Collection
    {
        return collect(explode(',', (string) $raw))
            ->map(fn ($v) => trim($v))
            ->filter()
            ->values();
    }

    public function myRegistrations()
    {
        $user = Auth::user();

        $rows = TrTrainingRegistration::where('username', $user->username)
            ->with('scheduleDetail.schedule.training')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'eid' => Hashids::encode($r->id),
                'docid' => $r->docid,
                'training_name' => $r->scheduleDetail->schedule->training->training_name ?? null,
                'schedule_date' => $r->scheduleDetail->schedule_date ?? null,
                'status' => $r->status,
                'offer_expires_at' => $r->offer_expires_at,
                'created_at' => $r->created_at,
            ]);

        return response()->json(['data' => $rows]);
    }

    /**
     * Whether/when the caller can see their own check-in barcode for one of
     * their Approved registrations — generates the code lazily on first
     * request rather than at approval time, so approving a registration
     * doesn't need to know anything about attendance.
     */
    public function barcodeStatus($id)
    {
        $registration = TrTrainingRegistration::findOrFail($id);
        $user = Auth::user();

        abort_unless($registration->username === $user->username, 403);

        if ($registration->status !== TrTrainingRegistration::STATUS_APPROVED) {
            return response()->json(['available' => false, 'message' => 'Registrasi belum disetujui']);
        }

        if (!$registration->attendance_code) {
            $registration->update(['attendance_code' => strtoupper(Str::random(20))]);
        }

        $detail = TrTrainingScheduleDetail::findOrFail($registration->schedule_detail_id);
        $window = $this->attendanceWindow($detail);
        $now = now();

        if ($now->lessThan($window['from'])) {
            return response()->json([
                'available' => false,
                'message' => 'Barcode akan aktif pada ' . Carbon::parse($detail->schedule_date)->translatedFormat('d M Y'),
            ]);
        }

        if ($now->greaterThan($window['until'])) {
            return response()->json(['available' => false, 'message' => 'Barcode sudah kedaluwarsa']);
        }

        return response()->json([
            'available' => true,
            'code' => $registration->attendance_code,
            'valid_until' => $window['until'],
        ]);
    }

    public function barcodeImage($id)
    {
        $registration = TrTrainingRegistration::findOrFail($id);
        $user = Auth::user();

        abort_unless($registration->username === $user->username, 403);
        abort_unless($registration->status === TrTrainingRegistration::STATUS_APPROVED, 403);
        abort_unless($registration->attendance_code, 404);

        $detail = TrTrainingScheduleDetail::findOrFail($registration->schedule_detail_id);
        abort_unless($this->isWithinAttendanceWindow($detail), 403);

        $generator = new BarcodeGeneratorPNG();
        $png = $generator->getBarcode($registration->attendance_code, $generator::TYPE_CODE_128, 2, 60);

        return response($png, 200)->header('Content-Type', 'image/png');
    }

    public function register(Request $request, $id)
    {
        $detail = TrTrainingScheduleDetail::findOrFail($id);
        $user = Auth::user();

        if ($detail->status !== 'PUBLISHED') {
            return response()->json(['success' => false, 'message' => 'Registrasi untuk jadwal ini sudah ditutup'], 422);
        }

        if ($detail->registration_deadline && Carbon::parse($detail->registration_deadline)->isPast()) {
            return response()->json(['success' => false, 'message' => 'Batas waktu registrasi sudah lewat'], 422);
        }

        $existing = TrTrainingRegistration::where('schedule_detail_id', $id)
            ->where('username', $user->username)
            ->whereIn('status', [
                TrTrainingRegistration::STATUS_PENDING,
                TrTrainingRegistration::STATUS_APPROVED,
                TrTrainingRegistration::STATUS_WAITLISTED,
                TrTrainingRegistration::STATUS_OFFERED,
            ])
            ->exists();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Anda sudah terdaftar pada jadwal ini'], 422);
        }

        // A person can belong to several companies and/or departments
        // (ms_user.cpny_id/department_id are comma-separated) — company
        // decides which quota pool they draw from, department decides which
        // ms_approval chain reviews them. Both must be picked explicitly
        // whenever more than one option exists; never guessed.
        $userCpnyIds = $this->splitMulti($user->cpny_id);
        $userDeptIds = $this->splitMulti($user->department_id);

        $requestedCpnyId = trim((string) $request->input('cpny_id', ''));
        $requestedDeptId = trim((string) $request->input('department_id', ''));

        if ($requestedCpnyId !== '' && !$userCpnyIds->contains($requestedCpnyId)) {
            return response()->json(['success' => false, 'message' => 'Perusahaan tidak valid untuk akun Anda'], 422);
        }

        if ($userDeptIds->count() > 1 && $requestedDeptId === '') {
            return response()->json(['success' => false, 'message' => 'Pilih departemen terlebih dahulu', 'require_department' => true], 422);
        }

        if ($requestedDeptId !== '' && !$userDeptIds->contains($requestedDeptId)) {
            return response()->json(['success' => false, 'message' => 'Departemen tidak valid untuk akun Anda'], 422);
        }

        $departmentId = $requestedDeptId !== '' ? $requestedDeptId : ($userDeptIds->first() ?? null);
        $cpnyIdsToTry = $requestedCpnyId !== '' ? collect([$requestedCpnyId]) : $userCpnyIds;

        DB::connection('pgsql5')->beginTransaction();

        try {
            $quotas = TrTrainingScheduleQuota::where('schedule_detail_id', $id)
                ->whereIn('cpny_id', $cpnyIdsToTry)
                ->lockForUpdate()
                ->get();

            if ($quotas->isEmpty()) {
                DB::connection('pgsql5')->rollBack();

                return response()->json(['success' => false, 'message' => 'Training ini tidak tersedia untuk perusahaan Anda'], 422);
            }

            if ($quotas->count() > 1) {
                DB::connection('pgsql5')->rollBack();

                return response()->json(['success' => false, 'message' => 'Pilih perusahaan terlebih dahulu', 'require_company' => true], 422);
            }

            $quota = $quotas->first();
            $cpnyId = $quota->cpny_id;

            $reserved = TrTrainingRegistration::where('schedule_detail_id', $id)
                ->where('cpny_id', $cpnyId)
                ->whereIn('status', [TrTrainingRegistration::STATUS_PENDING, TrTrainingRegistration::STATUS_APPROVED])
                ->count();

            $now = now();
            $docid = $this->generateRegistrationCode($user->username);

            $status = $reserved < $quota->quota_pax
                ? TrTrainingRegistration::STATUS_PENDING
                : TrTrainingRegistration::STATUS_WAITLISTED;

            $registration = TrTrainingRegistration::create([
                'docid' => $docid,
                'schedule_detail_id' => $id,
                'username' => $user->username,
                'cpny_id' => $cpnyId,
                'department_id' => $departmentId,
                'status' => $status,
                'created_by' => $user->username,
                'created_at' => $now,
            ]);

            if ($status === TrTrainingRegistration::STATUS_PENDING) {
                $this->submitForApproval($registration, $user, $now);
            }

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'status' => $registration->status,
                'message' => $status === TrTrainingRegistration::STATUS_PENDING
                    ? 'Registrasi berhasil, menunggu approval'
                    : 'Kuota penuh, Anda dimasukkan ke waiting list',
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan registrasi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function cancel(Request $request, $id)
    {
        $registration = TrTrainingRegistration::findOrFail($id);
        $user = Auth::user();

        if (strcasecmp((string) $registration->username, (string) $user->username) !== 0) {
            abort(403);
        }

        if ($registration->status !== TrTrainingRegistration::STATUS_APPROVED) {
            return response()->json(['success' => false, 'message' => 'Hanya registrasi berstatus Approved yang dapat dibatalkan'], 422);
        }

        DB::connection('pgsql5')->beginTransaction();

        try {
            $registration->status = TrTrainingRegistration::STATUS_CANCELLED;
            $registration->updated_by = $user->username;
            $registration->updated_at = now();
            $registration->save();

            TrainingRegistrationService::promoteWaitlistIfOpen($registration);

            DB::connection('pgsql5')->commit();

            return response()->json(['success' => true, 'message' => 'Registrasi berhasil dibatalkan']);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan registrasi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function acceptOffer(Request $request, $id)
    {
        $registration = TrTrainingRegistration::findOrFail($id);
        $user = Auth::user();

        if (strcasecmp((string) $registration->username, (string) $user->username) !== 0) {
            abort(403);
        }

        if (!$this->offerStillValid($registration)) {
            return response()->json(['success' => false, 'message' => 'Offer ini sudah tidak berlaku'], 422);
        }

        DB::connection('pgsql5')->beginTransaction();

        try {
            $now = now();

            $registration->status = TrTrainingRegistration::STATUS_PENDING;
            $registration->offered_at = null;
            $registration->offer_expires_at = null;
            $registration->updated_by = $user->username;
            $registration->updated_at = $now;
            $registration->save();

            $this->submitForApproval($registration, $user, $now);

            DB::connection('pgsql5')->commit();

            return response()->json(['success' => true, 'message' => 'Slot diterima, menunggu approval']);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menerima offer',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function declineOffer(Request $request, $id)
    {
        $registration = TrTrainingRegistration::findOrFail($id);
        $user = Auth::user();

        if (strcasecmp((string) $registration->username, (string) $user->username) !== 0) {
            abort(403);
        }

        if ($registration->status !== TrTrainingRegistration::STATUS_OFFERED) {
            return response()->json(['success' => false, 'message' => 'Offer ini sudah tidak berlaku'], 422);
        }

        DB::connection('pgsql5')->beginTransaction();

        try {
            $registration->status = TrTrainingRegistration::STATUS_CANCELLED;
            $registration->updated_by = $user->username;
            $registration->updated_at = now();
            $registration->save();

            TrainingRegistrationService::cascadeToNextWaitlist($registration);

            DB::connection('pgsql5')->commit();

            return response()->json(['success' => true, 'message' => 'Offer ditolak']);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menolak offer',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function approve(Request $request, $id)
    {
        $registration = TrTrainingRegistration::findOrFail($id);
        $user = Auth::user();
        $docUrl = url('/training-list/my/' . Hashids::encode($registration->id));

        $result = app(ApprovalController::class)->approveStep(
            $registration->docid,
            self::DOCTYPE,
            $user->username,
            $user->name,
            function (string $refnbr, Carbon $now) use ($registration, $docUrl) {
                $registration->status = TrTrainingRegistration::STATUS_APPROVED;
                $registration->updated_by = Auth::user()->username;
                $registration->updated_at = $now;
                $registration->save();

                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $registration->docid,
                    'Training Registration',
                    'C',
                    $registration->username,
                    $docUrl
                );
            },
            function ($next, Carbon $now) use ($registration, $docUrl) {
                if (!$next) {
                    return;
                }

                app(ApprovalController::class)->notifyFirstApprover(
                    $registration->docid,
                    self::DOCTYPE,
                    'P',
                    'Training Registration',
                    $docUrl,
                    ['createdby' => $registration->username, 'date' => $now->toDateTimeString()]
                );
            }
        );

        return response()->json($result, $result['ok'] ?? false ? 200 : 422);
    }

    public function reject(Request $request, $id)
    {
        $registration = TrTrainingRegistration::findOrFail($id);
        $user = Auth::user();
        $docUrl = url('/training-list/my/' . Hashids::encode($registration->id));

        $result = app(ApprovalController::class)->rejectStep(
            $registration->docid,
            self::DOCTYPE,
            $user->username,
            $user->name,
            function (string $refnbr, Carbon $now) use ($registration, $docUrl) {
                $registration->status = TrTrainingRegistration::STATUS_REJECTED;
                $registration->updated_by = Auth::user()->username;
                $registration->updated_at = $now;
                $registration->save();

                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $registration->docid,
                    'Training Registration',
                    'R',
                    $registration->username,
                    $docUrl
                );
            }
        );

        return response()->json($result, $result['ok'] ?? false ? 200 : 422);
    }

    /**
     * HCDEVACCESS-only: pick a specific waitlisted person to offer a slot that
     * was forfeited by a post-D-3 (CLOSED schedule) cancellation.
     */
    public function waitlistForOffer(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasRole('HCDEVACCESS')) {
            abort(403, 'Anda tidak memiliki akses HCDEVACCESS');
        }

        $scheduleDetailId = $request->query('schedule_detail_id');

        $query = TrTrainingRegistration::where('status', TrTrainingRegistration::STATUS_WAITLISTED)
            ->with('scheduleDetail.schedule.training');

        if ($scheduleDetailId) {
            $query->where('schedule_detail_id', $scheduleDetailId);
        }

        $rows = $query->orderBy('created_at')->get()->map(fn ($r) => [
            'id' => $r->id,
            'docid' => $r->docid,
            'username' => $r->username,
            'cpny_id' => $r->cpny_id,
            'training_name' => $r->scheduleDetail->schedule->training->training_name ?? null,
            'schedule_date' => $r->scheduleDetail->schedule_date ?? null,
            'schedule_status' => $r->scheduleDetail->status ?? null,
            'created_at' => $r->created_at,
        ]);

        return response()->json(['data' => $rows]);
    }

    public function manualOffer(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user->hasRole('HCDEVACCESS')) {
            abort(403, 'Anda tidak memiliki akses HCDEVACCESS');
        }

        $registration = TrTrainingRegistration::findOrFail($id);

        if ($registration->status !== TrTrainingRegistration::STATUS_WAITLISTED) {
            return response()->json(['success' => false, 'message' => 'Registrasi ini bukan waiting list'], 422);
        }

        $detail = TrTrainingScheduleDetail::find($registration->schedule_detail_id);

        if (!$detail || $detail->status !== 'CLOSED') {
            return response()->json(['success' => false, 'message' => 'Manual offer hanya untuk jadwal yang sudah closed'], 422);
        }

        DB::connection('pgsql5')->beginTransaction();

        try {
            $quota = TrTrainingScheduleQuota::where('schedule_detail_id', $registration->schedule_detail_id)
                ->where('cpny_id', $registration->cpny_id)
                ->lockForUpdate()
                ->first();

            $reserved = TrTrainingRegistration::where('schedule_detail_id', $registration->schedule_detail_id)
                ->where('cpny_id', $registration->cpny_id)
                ->whereIn('status', [TrTrainingRegistration::STATUS_PENDING, TrTrainingRegistration::STATUS_APPROVED, TrTrainingRegistration::STATUS_OFFERED])
                ->count();

            if (!$quota || $reserved >= $quota->quota_pax) {
                DB::connection('pgsql5')->rollBack();

                return response()->json(['success' => false, 'message' => 'Kuota sudah penuh, tidak ada slot yang tersedia untuk ditawarkan'], 422);
            }

            TrainingRegistrationService::offerSlot($registration);

            DB::connection('pgsql5')->commit();

            return response()->json(['success' => true, 'message' => 'Slot berhasil ditawarkan ke ' . $registration->username]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menawarkan slot',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function offerStillValid(TrTrainingRegistration $registration): bool
    {
        if ($registration->status !== TrTrainingRegistration::STATUS_OFFERED) {
            return false;
        }

        if ($registration->offer_expires_at && Carbon::parse($registration->offer_expires_at)->isPast()) {
            return false;
        }

        return true;
    }

    private function submitForApproval(TrTrainingRegistration $registration, User $user, Carbon $now): void
    {
        $approvalCtl = app(ApprovalController::class);

        $approvalCtl->loadLines(self::DOCTYPE, $registration->cpny_id, $registration->department_id);

        $approvalCtl->generateForDocument(
            $registration->docid,
            self::DOCTYPE,
            $registration->cpny_id,
            $registration->department_id,
            $user->username,
            [],
            $now
        );

        $docUrl = url('/training-list/my/' . Hashids::encode($registration->id));

        $approvalCtl->notifyFirstApprover(
            $registration->docid,
            self::DOCTYPE,
            'P',
            'Training Registration',
            $docUrl,
            [
                'createdby' => $user->name ?? $user->username,
                'date' => $now->toDateTimeString(),
            ]
        );
    }

    private function generateRegistrationCode(string $username): string
    {
        $year = (int) Carbon::now()->year;

        $auto = $this->nextAutonbr(
            self::DOCTYPE,
            $year,
            '00',
            $username,
            'Training Registration'
        );

        $yy = substr((string) $year, 2, 2);

        return self::DOCTYPE . $yy . sprintf('%04d', $auto['next']);
    }
}
