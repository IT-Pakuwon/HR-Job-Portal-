<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAttendanceWindow;
use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\CompanyAddress;
use App\Models\MsCategory;
use App\Models\MsCompany;
use App\Models\MsDepartment;
use App\Models\MsLndPlaces;
use App\Models\MsLndTrainingSchedule;
use App\Models\MsLndTrainingQuota;
use App\Models\MsTrainingEvent;
use App\Models\StoGrading;
use App\Models\TrApproval;
use App\Models\TrLndTrainingFeedbackAnswer;
use App\Models\TrLndTrainingRegistration;
use App\Models\TrMessage;
use App\Models\User;
use App\Services\TrainingRegistrationService;
use App\Services\TrainingWaitlistNotifier;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Vinkla\Hashids\Facades\Hashids;

class TrainingRegistrationController extends Controller
{
    use HasAutonbr;
    use HasAttendanceWindow;

    protected const DOCTYPE = 'TRN';

    /**
     * ms_lnd_training_schedule.status is varchar(1) — single-letter codes
     * (DRAFT/PUBLISHED/CLOSED/CANCELLED). The legacy registration code
     * compared against full words, which never matched anything.
     */
    protected const SCHEDULE_DRAFT = 'D';
    protected const SCHEDULE_PUBLISHED = 'P';
    protected const SCHEDULE_CLOSED = 'C';
    protected const SCHEDULE_CANCELLED = 'X';

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
     * availability, grouped one row per training batch (training_detail_id).
     *
     * Company/department for the whole registration flow come from the
     * participant's origin org (ms_user.origin_cpny_id / origin_department_id),
     * so every published schedule's quota pool is filtered to the caller's own
     * origin company(s).
     *
     * Optional ?training_id= narrows to a single training (used by the
     * dedicated event page instead of the full browse list).
     */
    public function json(Request $request)
    {
        $user = Auth::user();
        $userCpnyIds = $this->splitMulti($user->origin_cpny_id);
        $userDeptIds = $this->splitMulti($user->origin_department_id);
        $onlyTrainingId = $request->query('training_id');

        $details = MsLndTrainingSchedule::query()
            ->where('status', self::SCHEDULE_PUBLISHED)
            ->when($onlyTrainingId, fn ($q) => $q->where('training_id', $onlyTrainingId))
            ->with([
                'schedule.training',
                'quota' => fn ($q) => $q->whereIn('cpny_id', $userCpnyIds),
            ])
            ->orderBy('schedule_date')
            ->get();

        $scheduleIds = $details->pluck('schedule_id');

        $myRegs = TrLndTrainingRegistration::whereIn('schedule_id', $scheduleIds)
            ->where('user_registration', $user->username)
            ->where(function ($q) {
                $q->where(fn ($q2) => $q2->whereNull('status_registration')->whereNotNull('status'))
                    ->orWhereIn('status_registration', [
                        TrLndTrainingRegistration::REG_STATUS_WAITLISTED,
                        TrLndTrainingRegistration::REG_STATUS_OFFERED,
                    ]);
            })
            ->orderBy('created_at')
            ->get()
            ->keyBy('schedule_id');

        $usage = TrLndTrainingRegistration::whereIn('schedule_id', $scheduleIds)
            ->where(function ($q) {
                $q->whereNull('status_registration')
                    ->orWhere('status_registration', TrLndTrainingRegistration::REG_STATUS_OFFERED);
            })
            ->where('status', '!=', TrLndTrainingRegistration::STATUS_REJECTED)
            ->select('schedule_id', 'cpny_id', DB::raw('count(*) as cnt'))
            ->groupBy('schedule_id', 'cpny_id')
            ->get()
            ->groupBy('schedule_id');

        $companyNames = MsCompany::whereIn('cpny_id', $userCpnyIds)->pluck('cpny_name', 'cpny_id');
        $gradeNames = StoGrading::whereIn('grade_id', $details->pluck('schedule.job_level')->filter()->unique())
            ->pluck('grade_name', 'grade_id');
        $speakerNames = User::whereIn('username', $details->pluck('training_speaker_username')->filter()->unique())
            ->pluck('name', 'username');
        $placeNames = MsLndPlaces::whereIn('places_id', $details->pluck('places_id')->filter()->unique())
            ->pluck('places_name', 'places_id');

        $scheduleOptions = $details->map(function ($d) use ($myRegs, $usage, $companyNames, $gradeNames, $speakerNames, $placeNames) {
            $grouped = $usage->get($d->schedule_id, collect());

            $eligibleCompanies = $d->quota->map(function ($q) use ($grouped, $companyNames) {
                $seatCount = (int) $grouped->where('cpny_id', $q->cpny_id)->sum('cnt');

                return [
                    'cpny_id' => $q->cpny_id,
                    'cpny_name' => $companyNames[$q->cpny_id] ?? $q->cpny_id,
                    'quota_pax' => $q->quota_pax,
                    'reserved' => 0,
                    'used' => $seatCount,
                    'available' => max(0, $q->quota_pax - $seatCount),
                ];
            })->values();

            $mine = $myRegs->get($d->schedule_id);

            return [
                'id' => $d->schedule_id,
                'training_id' => $d->training_id,
                'training_name' => $d->schedule->training->training_name ?? null,
                'docid' => $d->training_detail_id,
                'schedule_date' => $d->schedule_date,
                'start_time' => $d->schedule_start_time,
                'end_time' => $d->schedule_end_time,
                'mode' => $d->training_mode,
                'location' => $d->places_id ? ($placeNames[$d->places_id] ?? $d->places_id) : null,
                'platform' => $d->training_platform,
                'meeting_link' => $d->training_meeting_link,
                'poster_url' => $d->schedule->training_poster ? Storage::disk('public')->url($d->schedule->training_poster) : null,
                'grade_id' => $d->schedule->job_level,
                'grade_name' => $gradeNames[$d->schedule->job_level] ?? $d->schedule->job_level,
                'speaker_name' => $d->training_speaker_name ?: $d->training_ext_speaker_name,
                'registration_deadline' => $d->registration_deadline,
                'is_open' => !$d->registration_deadline || !Carbon::parse($d->registration_deadline)->isPast(),
                'eligible_companies' => $eligibleCompanies,
                'my_status' => $mine ? $mine->effective_status : null,
                'my_registration_id' => $mine->id ?? null,
            ];
        });

        $trainingsById = $details->pluck('schedule.training')->filter()->unique('training_id')->keyBy('training_id');
        $categoryNames = MsCategory::where('doctype', 'TE')
            ->whereIn('categoryid', $trainingsById->pluck('category_id')->filter()->unique())
            ->pluck('category_name', 'categoryid');

        // One card per batch (training_detail_id — a distinct HR "Add
        // Schedule" batch with one level/speaker/poster). Different batches
        // are different cards even when they share a training name.
        $rows = $scheduleOptions->groupBy('docid')->map(function ($schedules) use ($trainingsById, $categoryNames) {
            $first = $schedules->first();
            $training = $trainingsById->get($first['training_id']);

            return [
                'docid' => $first['docid'],
                'training_id' => $first['training_id'],
                'eid' => $training ? Hashids::encode($training->id) : null,
                'training_name' => $first['training_name'],
                'poster_url' => $first['poster_url'],
                'description' => $training->training_description ?? null,
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
        $companyNamesAll = MsCompany::whereIn('cpny_id', $userCpnyIds)->pluck('cpny_name', 'cpny_id');

        return response()->json([
            'data' => $rows,
            'department_options' => $userDeptIds->map(fn ($id) => [
                'id' => $id,
                'name' => $departmentNames[$id] ?? $id,
            ])->values(),
            'my_company_name' => $companyNamesAll[$user->origin_cpny_id] ?? $user->origin_cpny_id,
            'my_department_name' => $departmentNames[$user->origin_department_id] ?? $user->origin_department_id,
        ]);
    }

    private function splitMulti(?string $raw): \Illuminate\Support\Collection
    {
        return collect(explode(',', (string) $raw))
            ->map(fn ($v) => trim($v))
            ->filter()
            ->values();
    }

    /**
     * Colleagues eligible for batch registration: active ms_user rows sharing
     * the caller's exact origin company AND origin department.
     */
    public function colleagues(Request $request)
    {
        $user = Auth::user();
        $originCpnyId = trim((string) $user->origin_cpny_id);
        $originDeptId = trim((string) $user->origin_department_id);

        if ($originCpnyId === '' || $originDeptId === '') {
            return response()->json(['data' => []]);
        }

        $query = User::query()
            ->where('status', 'A')
            ->where('origin_cpny_id', $originCpnyId)
            ->where('origin_department_id', $originDeptId);

        $search = trim((string) $request->get('q', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('username', 'ilike', "%{$search}%");
            });
        }

        $rows = $query->orderBy('name')->limit(50)->get(['username', 'name', 'origin_cpny_id', 'origin_department_id']);

        return response()->json([
            'data' => $rows->map(fn ($u) => [
                'username' => $u->username,
                'name' => $u->name ?: $u->username,
                'cpny_id' => $u->origin_cpny_id,
                'department_id' => $u->origin_department_id,
            ])->values(),
        ]);
    }

    public function myRegistrations()
    {
        $user = Auth::user();

        $registrations = TrLndTrainingRegistration::where('user_registration', $user->username)
            ->with('schedule.schedule.training')
            ->orderByDesc('created_at')
            ->get();

        $answeredDocIds = TrLndTrainingFeedbackAnswer::whereIn('training_regist_id', $registrations->pluck('training_regist_id'))
            ->pluck('training_regist_id')
            ->unique();

        $rows = $registrations->map(function ($r) use ($answeredDocIds) {
            $hasAttended = (bool) $r->completed_at;
            $feedbackOpen = (bool) $r->schedule?->is_feedback_open;
            $feedbackSubmitted = $answeredDocIds->contains($r->training_regist_id);
            $isApproved = $r->status === TrLndTrainingRegistration::STATUS_APPROVED;
            $certificateReady = (bool) $r->schedule?->is_certificate_ready;

            return [
                'id' => $r->id,
                'eid' => Hashids::encode($r->id),
                'schedule_id' => $r->schedule_id,
                'docid' => $r->training_regist_id,
                'training_name' => $r->schedule?->schedule?->training?->training_name ?? null,
                'schedule_date' => $r->schedule_date ?? $r->schedule?->schedule_date,
                'status' => $r->effective_status,
                'offer_expires_at' => $r->status_registration === TrLndTrainingRegistration::REG_STATUS_OFFERED
                    ? $r->offer_expires_at
                    : null,
                'has_attended' => $hasAttended,
                'feedback_open' => $feedbackOpen,
                'feedback_submitted' => $feedbackSubmitted,
                'can_fill_feedback' => $hasAttended && $feedbackOpen,
                'can_view_certificate' => $hasAttended && $isApproved && $certificateReady,
                'created_at' => $r->created_at,
            ];
        });

        return response()->json(['data' => $rows]);
    }

    /**
     * Streams a certificate PDF rendered fresh from this registration's own
     * data (no stored file/row) — available once the registration is
     * Approved, the participant actually attended, and the event is at
     * least a day past (is_certificate_ready; H+1, so a same-day mis-scan
     * can still be corrected via unmarkAttend before a certificate could be
     * pulled for it). Self-service only: scoped to the caller's own row.
     */
    public function myCertificate($id)
    {
        $user = Auth::user();

        $registration = TrLndTrainingRegistration::where('user_registration', $user->username)
            ->with('schedule.schedule.training')
            ->findOrFail($id);

        abort_unless($registration->status === TrLndTrainingRegistration::STATUS_APPROVED, 422, 'Registrasi ini belum disetujui');
        abort_unless((bool) $registration->completed_at, 422, 'Anda belum tercatat hadir pada training ini');

        $schedule = $registration->schedule;
        abort_unless($schedule && $schedule->is_certificate_ready, 422, 'Sertifikat untuk training ini belum tersedia');

        $trainingDetail = $schedule->schedule;
        $training = $trainingDetail?->training;
        abort_unless($trainingDetail && $training, 422, 'Data training tidak lengkap');

        $gradeName = $trainingDetail->job_level;
        if ($trainingDetail->job_level) {
            $grade = StoGrading::where('grade_id', $trainingDetail->job_level)->first();
            $gradeName = $grade->grade_name ?? $trainingDetail->job_level;
        }

        $company = MsCompany::where('cpny_id', $registration->cpny_id)->first();
        $companyAddress = CompanyAddress::where('cpnyid', $registration->cpny_id)->first();
        $certificateNo = $registration->attendance_code ?: ('CERT-' . $registration->id);

        $pdf = Pdf::loadView('pages.training_attendance.certificate-pdf', [
            'participantName' => $user->name ?? $user->username,
            'trainingName' => $training->training_name,
            'gradeName' => $gradeName,
            'scheduleDate' => $schedule->schedule_date,
            'certificateNo' => $certificateNo,
            'issueDate' => now(),
            'logo' => $company->cpny_id ?? null,
            'companyName' => $companyAddress->cpnyname ?? $company->cpny_name ?? '-',
        ])->setPaper('a4', 'landscape');

        return $pdf->stream("certificate-{$certificateNo}.pdf");
    }

    /**
     * Whether/when the caller can see their own check-in barcode for one of
     * their Approved registrations. The unique attendance_code is generated
     * by the model observer at approval time; this endpoint only reports it.
     */
    public function barcodeStatus($id)
    {
        $registration = TrLndTrainingRegistration::findOrFail($id);
        $user = Auth::user();

        abort_unless(strcasecmp((string) $registration->user_registration, (string) $user->username) === 0, 403);

        if ($registration->status !== TrLndTrainingRegistration::STATUS_APPROVED) {
            return response()->json(['available' => false, 'message' => 'Registrasi belum disetujui']);
        }

        if ($registration->status_registration) {
            return response()->json(['available' => false, 'message' => 'Anda belum memiliki slot pada event ini']);
        }

        if (!$registration->attendance_code) {
            $registration->attendance_code = 'TRN-' . strtoupper(Str::random(10));
            $registration->updated_by = $user->username;
            $registration->save();
        }

        $detail = MsLndTrainingSchedule::where('schedule_id', $registration->schedule_id)->first();

        if (!$detail) {
            return response()->json(['available' => false, 'message' => 'Schedule tidak ditemukan']);
        }

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
        $registration = TrLndTrainingRegistration::findOrFail($id);
        $user = Auth::user();

        abort_unless(strcasecmp((string) $registration->user_registration, (string) $user->username) === 0, 403);
        abort_unless($registration->status === TrLndTrainingRegistration::STATUS_APPROVED, 403);
        abort_unless(!$registration->status_registration, 403);
        abort_unless($registration->attendance_code, 404);

        $detail = MsLndTrainingSchedule::where('schedule_id', $registration->schedule_id)->firstOrFail();
        abort_unless($this->isWithinAttendanceWindow($detail), 403);

        $generator = new BarcodeGeneratorPNG();
        $png = $generator->getBarcode($registration->attendance_code, $generator::TYPE_CODE_128, 2, 60);

        return response($png, 200)->header('Content-Type', 'image/png');
    }

    /**
     * Multi-participant registration. $scheduleId is the TSDxxxxx schedule
     * code. Defaults to self-registration; a non-empty `participants[]` list
     * registers that exact set of colleagues (submitter may include
     * themselves). Each participant gets their own row AND their own
     * training_regist_id/approval chain — they are submitted together
     * (one seat-availability check for the whole set) but approved
     * independently, not as a shared batch document.
     */
    public function register(Request $request, string $scheduleId)
    {
        $detail = MsLndTrainingSchedule::where('schedule_id', $scheduleId)->firstOrFail();
        $user = Auth::user();

        if ($detail->status !== self::SCHEDULE_PUBLISHED) {
            return response()->json(['success' => false, 'message' => 'Registrasi untuk jadwal ini sudah ditutup'], 422);
        }

        if ($detail->registration_deadline && Carbon::parse($detail->registration_deadline)->isPast()) {
            return response()->json(['success' => false, 'message' => 'Batas waktu registrasi sudah lewat'], 422);
        }

        $originCpnyId = trim((string) $user->origin_cpny_id);
        $originDeptId = trim((string) $user->origin_department_id);

        $requested = $request->input('participants', []);
        if (!is_array($requested) || empty($requested)) {
            $requested = [$user->username];
        }

        $requested = collect($requested)
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->values();

        $participants = collect();
        foreach ($requested as $username) {
            $participant = User::where('username', $username)->where('status', 'A')->first();

            if (!$participant) {
                return response()->json(['success' => false, 'message' => "Peserta {$username} tidak ditemukan"], 422);
            }

            // Colleagues must belong to the exact same origin org as the submitter.
            if (strcasecmp($username, $user->username) !== 0) {
                $pCpny = trim((string) $participant->origin_cpny_id);
                $pDept = trim((string) $participant->origin_department_id);

                if ($pCpny !== $originCpnyId || $pDept !== $originDeptId) {
                    return response()->json([
                        'success' => false,
                        'message' => "Peserta {$username} bukan rekan sekantor Anda",
                    ], 422);
                }
            }

            $participants->push($participant);
        }

        // Duplicate prevention guardrail: no active (non-cancelled, non-rejected)
        // registration for any selected participant on this schedule.
        $duplicates = TrLndTrainingRegistration::where('schedule_id', $scheduleId)
            ->whereIn('user_registration', $participants->pluck('username'))
            ->where(function ($q) {
                $q->whereNull('status_registration')
                    ->orWhere('status_registration', '!=', TrLndTrainingRegistration::REG_STATUS_CANCELLED);
            })
            ->where('status', '!=', TrLndTrainingRegistration::STATUS_REJECTED)
            ->pluck('user_registration');

        if ($duplicates->isNotEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Sudah terdaftar pada jadwal ini: ' . $duplicates->implode(', '),
            ], 422);
        }

        // Mandatory trainings only allow one schedule per participant: block if
        // any selected participant already has an active (non-cancelled,
        // non-rejected) registration on a *different* schedule of this same
        // training_id.
        $isMandatory = (bool) MsTrainingEvent::where('training_id', $detail->training_id)->value('is_mandatory');
        $trainingName = (string) MsTrainingEvent::where('training_id', $detail->training_id)->value('training_name');

        if ($isMandatory) {
            $mandatoryDuplicates = TrLndTrainingRegistration::where('training_id', $detail->training_id)
                ->where('schedule_id', '!=', $scheduleId)
                ->whereIn('user_registration', $participants->pluck('username'))
                ->where(function ($q) {
                    $q->whereNull('status_registration')
                        ->orWhere('status_registration', '!=', TrLndTrainingRegistration::REG_STATUS_CANCELLED);
                })
                ->where('status', '!=', TrLndTrainingRegistration::STATUS_REJECTED)
                ->pluck('user_registration');

            if ($mandatoryDuplicates->isNotEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Training ini wajib (mandatory) — sudah terdaftar di jadwal lain: ' . $mandatoryDuplicates->implode(', '),
                ], 422);
            }
        }

        DB::connection('pgsql5')->beginTransaction();

        try {
            $quotas = MsLndTrainingQuota::where('schedule_id', $scheduleId)
                ->where('cpny_id', $originCpnyId)
                ->lockForUpdate()
                ->get();

            if ($quotas->isEmpty()) {
                DB::connection('pgsql5')->rollBack();

                return response()->json(['success' => false, 'message' => 'Training ini tidak tersedia untuk perusahaan Anda'], 422);
            }

            $quotaPax = $quotas->sum('quota_pax');
            $seatCount = $this->activeSeatCount($scheduleId, $originCpnyId);
            $available = $quotaPax - $seatCount;
            $batchCount = $participants->count();

            // Approval always starts at registration time, whether or not
            // there's a seat free. status_registration is the SEATING flag on
            // top of it: waitlisted people are still pending/approved in the
            // approval pipeline while they wait. Seat availability is still
            // evaluated once for the whole submitted set (all-or-nothing: a
            // colleague batch is either fully seated or fully waitlisted
            // together), even though each participant now gets their own
            // independent document/approval chain below.
            $status = TrLndTrainingRegistration::STATUS_PENDING;
            $statusReg = $available >= $batchCount
                ? null
                : TrLndTrainingRegistration::REG_STATUS_WAITLISTED;

            $now = now();
            $docIds = collect();

            // Each participant gets their OWN training_regist_id and their own
            // independent approval chain — a colleague batch is no longer one
            // shared document. This means the approver acts on each person
            // individually instead of approving the whole batch in one click,
            // but it makes training_regist_id a true 1:1 key for every row
            // (needed so attendance logging can unambiguously tell participants
            // in the same submission apart).
            foreach ($participants as $participant) {
                $docId = $this->generateRegistrationCode($user->username);
                $docIds->push($docId);

                TrLndTrainingRegistration::create([
                    'training_regist_id' => $docId,
                    'training_regist_date' => $now->toDateString(),
                    'training_id' => $detail->training_id,
                    'training_detail_id' => $detail->training_detail_id,
                    'schedule_id' => $detail->schedule_id,
                    'schedule_date' => $detail->schedule_date,
                    'cpny_id' => trim((string) $participant->origin_cpny_id),
                    'department_id' => trim((string) $participant->origin_department_id),
                    'user_registration' => $participant->username,
                    'qty_registration' => 1,
                    'status' => $status,
                    'status_registration' => $statusReg,
                    'process_registration_user' => $statusReg ? $user->username : null,
                    'process_registration_date' => $statusReg ? $now : null,
                    'created_by' => $user->username,
                    'created_at' => $now,
                ]);

                $this->submitForApproval($docId, $originCpnyId, $originDeptId, $user, $now, $trainingName);
            }

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'training_regist_id' => $docIds->first(),
                'training_regist_ids' => $docIds->values(),
                'status' => $statusReg ?: $status,
                'message' => $statusReg
                    ? 'Kuota penuh, seluruh peserta masuk waiting list — approval tetap berjalan'
                    : ($docIds->count() > 1
                        ? 'Registrasi berhasil (' . $docIds->count() . ' dokumen: ' . $docIds->implode(', ') . '), menunggu approval'
                        : 'Registrasi berhasil, menunggu approval'),
            ]);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
            // abort()/abort_if() calls further down the stack (e.g. ApprovalController::loadLines()
            // aborting with "Approval line belum di-setup, Please contact IT!") carry a real,
            // actionable message — let those surface as-is instead of being masked below.
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Gagal melakukan registrasi',
            ], $e->getStatusCode());
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            \Illuminate\Support\Facades\Log::error('Training registration failed', [
                'schedule_id' => $scheduleId,
                'user' => $user->username,
                'participants' => $requested->all(),
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan registrasi',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function cancel(Request $request, $id)
    {
        $registration = TrLndTrainingRegistration::findOrFail($id);
        $user = Auth::user();

        if (strcasecmp((string) $registration->user_registration, (string) $user->username) !== 0) {
            abort(403);
        }

        if ($registration->status !== TrLndTrainingRegistration::STATUS_APPROVED) {
            return response()->json(['success' => false, 'message' => 'Hanya registrasi berstatus Approved yang dapat dibatalkan'], 422);
        }

        DB::connection('pgsql5')->beginTransaction();

        try {
            $heldSeat = !$registration->status_registration;

            $registration->status_registration = TrLndTrainingRegistration::REG_STATUS_CANCELLED;
            $registration->process_registration_user = $user->username;
            $registration->process_registration_date = now();
            $registration->updated_by = $user->username;
            $registration->updated_at = now();
            $registration->save();

            if ($heldSeat) {
                TrainingRegistrationService::promoteWaitlistIfOpen($registration);
            }

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
        $registration = TrLndTrainingRegistration::findOrFail($id);
        $user = Auth::user();

        if (strcasecmp((string) $registration->user_registration, (string) $user->username) !== 0) {
            abort(403);
        }

        if (!$this->offerStillValid($registration)) {
            return response()->json(['success' => false, 'message' => 'Offer ini sudah tidak berlaku'], 422);
        }

        DB::connection('pgsql5')->beginTransaction();

        try {
            $now = now();

            // Approval was already started at registration time, so accepting
            // the offer only removes the seating flag — the approval chain
            // keeps running to completion on its own.
            $registration->status_registration = null;
            $registration->process_registration_user = null;
            $registration->process_registration_date = null;
            $registration->updated_by = $user->username;
            $registration->updated_at = $now;
            $registration->save();

            TrainingWaitlistNotifier::notifyHcdevOfferResponse($registration, true);

            DB::connection('pgsql5')->commit();

            $approvalPending = $registration->status === TrLndTrainingRegistration::STATUS_PENDING;

            return response()->json([
                'success' => true,
                'message' => $approvalPending
                    ? 'Slot diterima, menunggu approval selesai'
                    : 'Slot diterima',
            ]);
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
        $registration = TrLndTrainingRegistration::findOrFail($id);
        $user = Auth::user();

        if (strcasecmp((string) $registration->user_registration, (string) $user->username) !== 0) {
            abort(403);
        }

        if ($registration->status_registration !== TrLndTrainingRegistration::REG_STATUS_OFFERED) {
            return response()->json(['success' => false, 'message' => 'Offer ini sudah tidak berlaku'], 422);
        }

        DB::connection('pgsql5')->beginTransaction();

        try {
            $registration->status_registration = TrLndTrainingRegistration::REG_STATUS_CANCELLED;
            $registration->process_registration_user = $user->username;
            $registration->process_registration_date = now();
            $registration->updated_by = $user->username;
            $registration->updated_at = now();
            $registration->save();

            TrainingWaitlistNotifier::notifyHcdevOfferResponse($registration, false);

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

    /**
     * Approve a single participant's registration document (training_regist_id
     * is a 1:1 key per row now, not a shared batch code — the model observer
     * mints that row's unique barcode once status hits 'C').
     */
    public function approve(Request $request, $id)
    {
        $registration = TrLndTrainingRegistration::findOrFail($id);
        $user = Auth::user();

        $docUrl = url('/training-list/my/' . Hashids::encode($registration->id));

        $result = app(ApprovalController::class)->approveStep(
            $registration->training_regist_id,
            self::DOCTYPE,
            $user->username,
            $user->name,
            function (string $refnbr, Carbon $now) use ($registration, $docUrl) {
                $registration->status = TrLndTrainingRegistration::STATUS_APPROVED;
                $registration->updated_by = Auth::user()->username;
                $registration->updated_at = $now;
                $registration->save();

                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $refnbr,
                    'Training Registration',
                    'C',
                    $registration->created_by,
                    $docUrl
                );

                $this->notifyDocSystem(
                    $refnbr,
                    $registration->cpny_id,
                    $registration->department_id,
                    'Registrasi training Anda telah disetujui sepenuhnya.'
                );
            },
            function ($next, Carbon $now) use ($registration, $docUrl) {
                if (!$next) {
                    return;
                }

                app(ApprovalController::class)->notifyFirstApprover(
                    $registration->training_regist_id,
                    self::DOCTYPE,
                    'P',
                    'Training Registration',
                    $docUrl,
                    ['createdby' => $registration->created_by, 'date' => $now->toDateTimeString()]
                );

                $this->notifyDocSystem(
                    $registration->training_regist_id,
                    $registration->cpny_id,
                    $registration->department_id,
                    'Registrasi training menunggu persetujuan Anda.'
                );
            }
        );

        return response()->json($result, $result['ok'] ?? false ? 200 : 422);
    }

    public function reject(Request $request, $id)
    {
        $registration = TrLndTrainingRegistration::findOrFail($id);
        $user = Auth::user();

        $docUrl = url('/training-list/my/' . Hashids::encode($registration->id));

        $result = app(ApprovalController::class)->rejectStep(
            $registration->training_regist_id,
            self::DOCTYPE,
            $user->username,
            $user->name,
            function (string $refnbr, Carbon $now) use ($registration, $docUrl) {
                $registration->status = TrLndTrainingRegistration::STATUS_REJECTED;
                $registration->updated_by = Auth::user()->username;
                $registration->updated_at = $now;
                $registration->save();

                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $refnbr,
                    'Training Registration',
                    'R',
                    $registration->created_by,
                    $docUrl
                );

                $this->notifyDocSystem(
                    $refnbr,
                    $registration->cpny_id,
                    $registration->department_id,
                    'Registrasi training Anda ditolak.'
                );
            }
        );

        return response()->json($result, $result['ok'] ?? false ? 200 : 422);
    }

    /**
     * TRN documents where the caller is the current (active) approver —
     * same "active step" definition as ApprovalController::assertUserCanAct()
     * (earliest P-status line for this refnbr with aprv_datebefore set, and
     * the caller's username in that line's comma-separated aprv_username
     * list), so this list matches exactly what approve()/reject() would
     * actually let them act on right now.
     */
    public function pendingApprovals(Request $request)
    {
        $user = Auth::user();
        $username = strtolower(trim($user->username));

        $approvalRows = TrApproval::query()
            ->where('aprv_doctype', self::DOCTYPE)
            ->where('status', 'P')
            ->whereNotNull('aprv_datebefore')
            ->whereRaw(
                "(',' || lower(regexp_replace(coalesce(aprv_username,''), '\s+', '', 'g')) || ',') like ?",
                ['%,' . $username . ',%']
            )
            ->orderBy('aprv_datebefore')
            ->get(['refnbr', 'aprv_datebefore']);

        if ($approvalRows->isEmpty()) {
            return response()->json(['data' => []]);
        }

        $docIds = $approvalRows->pluck('refnbr')->unique()->values();

        $registrations = TrLndTrainingRegistration::whereIn('training_regist_id', $docIds->all())
            ->with('schedule.schedule.training')
            ->get()
            ->keyBy('training_regist_id');

        $usernames = $registrations->pluck('user_registration')->unique();
        $names = $usernames->isEmpty() ? collect() : User::whereIn('username', $usernames)->pluck('name', 'username');

        $cpnyIds = $registrations->pluck('cpny_id')->filter()->unique();
        $companyNames = $cpnyIds->isEmpty() ? collect() : MsCompany::whereIn('cpny_id', $cpnyIds)->pluck('cpny_name', 'cpny_id');

        $deptIds = $registrations->pluck('department_id')->filter()->unique();
        $departmentNames = $deptIds->isEmpty() ? collect() : MsDepartment::whereIn('department_id', $deptIds)->pluck('department_name', 'department_id');

        $data = $approvalRows
            ->map(function ($apr) use ($registrations, $names, $companyNames, $departmentNames) {
                $r = $registrations->get($apr->refnbr);

                if (!$r) {
                    return null;
                }

                return [
                    'id' => $r->id,
                    'docid' => $r->training_regist_id,
                    'training_name' => $r->schedule?->schedule?->training?->training_name ?? null,
                    'username' => $r->user_registration,
                    'name' => $names[$r->user_registration] ?? $r->user_registration,
                    'cpny_id' => $r->cpny_id,
                    'cpny_name' => $companyNames[$r->cpny_id] ?? $r->cpny_id,
                    'department_id' => $r->department_id,
                    'department_name' => $departmentNames[$r->department_id] ?? $r->department_id,
                    'schedule_date' => $r->schedule_date ?? $r->schedule?->schedule_date,
                    'waiting_since' => $apr->aprv_datebefore,
                ];
            })
            ->filter()
            ->values();

        return response()->json(['data' => $data]);
    }

    /**
     * HCDEVACCESS-only: waitlisted people, with every company quota row for
     * their schedule (available seats) so HR can also reassign which
     * company's quota a person consumes when accepting post-close.
     */
    public function waitlistForOffer(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasRole('HCDEVACCESS')) {
            abort(403, 'Anda tidak memiliki akses HCDEVACCESS');
        }

        $scheduleId = $request->query('schedule_id');

        $query = TrLndTrainingRegistration::where('status_registration', TrLndTrainingRegistration::REG_STATUS_WAITLISTED)
            ->where('status', '!=', TrLndTrainingRegistration::STATUS_REJECTED)
            ->with('schedule.schedule.training');

        if ($scheduleId) {
            $query->where('schedule_id', $scheduleId);
        }

        $rows = $query->orderBy('created_at')->get();

        $scheduleIds = $rows->pluck('schedule_id')->unique();

        $quotas = MsLndTrainingQuota::whereIn('schedule_id', $scheduleIds)->get();

        $usage = TrLndTrainingRegistration::whereIn('schedule_id', $scheduleIds)
            ->where(function ($q) {
                $q->whereNull('status_registration')
                    ->orWhere('status_registration', TrLndTrainingRegistration::REG_STATUS_OFFERED);
            })
            ->where('status', '!=', TrLndTrainingRegistration::STATUS_REJECTED)
            ->select('schedule_id', 'cpny_id', DB::raw('count(*) as cnt'))
            ->groupBy('schedule_id', 'cpny_id')
            ->get()
            ->groupBy('schedule_id');

        $companyNames = MsCompany::whereIn('cpny_id', $quotas->pluck('cpny_id')->unique())
            ->pluck('cpny_name', 'cpny_id');

        $usernames = $rows->pluck('user_registration')->unique();
        $names = $usernames->isEmpty() ? collect() : User::whereIn('username', $usernames)->pluck('name', 'username');

        return response()->json([
            'data' => $rows->map(function ($r) use ($quotas, $usage, $companyNames, $names) {
                $usedByCpny = collect($usage->get($r->schedule_id, collect()));

                return [
                    'id' => $r->id,
                    'docid' => $r->training_regist_id,
                    'username' => $r->user_registration,
                    'name' => $names[$r->user_registration] ?? $r->user_registration,
                    'cpny_id' => $r->cpny_id,
                    'approval_status' => $r->status,
                    'training_name' => $r->schedule?->schedule?->training?->training_name ?? null,
                    'schedule_date' => $r->schedule_date ?? $r->schedule?->schedule_date,
                    'schedule_status' => $r->schedule?->status ?? null,
                    'quota_options' => $quotas
                        ->where('schedule_id', $r->schedule_id)
                        ->map(function ($q) use ($usedByCpny, $companyNames) {
                            $used = (int) $usedByCpny->where('cpny_id', $q->cpny_id)->sum('cnt');

                            return [
                                'cpny_id' => $q->cpny_id,
                                'cpny_name' => $companyNames[$q->cpny_id] ?? $q->cpny_id,
                                'quota_pax' => $q->quota_pax,
                                'used' => $used,
                                'available' => max(0, $q->quota_pax - $used),
                            ];
                        })
                        ->values(),
                    'created_at' => $r->created_at,
                ];
            }),
        ]);
    }

    /**
     * HCDEVACCESS-only: seat a waitlisted person on a closed schedule. The
     * person's approval chain already ran at registration time, so HR can
     * only accept people whose approval has COMPLETED (status 'C'). HR may
     * pick a different company's quota than the person's origin company —
     * that reassigns the row's cpny_id (the seat is then counted against the
     * chosen company's quota).
     */
    public function manualAccept(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user->hasRole('HCDEVACCESS')) {
            abort(403, 'Anda tidak memiliki akses HCDEVACCESS');
        }

        $registration = TrLndTrainingRegistration::findOrFail($id);

        if ($registration->status_registration !== TrLndTrainingRegistration::REG_STATUS_WAITLISTED) {
            return response()->json(['success' => false, 'message' => 'Registrasi ini bukan waiting list'], 422);
        }

        if ($registration->status !== TrLndTrainingRegistration::STATUS_APPROVED) {
            return response()->json(['success' => false, 'message' => 'Approval untuk peserta ini belum selesai — tunggu hingga approval selesai'], 422);
        }

        $detail = MsLndTrainingSchedule::where('schedule_id', $registration->schedule_id)->first();

        if (!$detail || $detail->status !== self::SCHEDULE_CLOSED) {
            return response()->json(['success' => false, 'message' => 'Penerimaan manual hanya untuk jadwal yang sudah closed'], 422);
        }

        $cpnyId = trim((string) ($request->input('cpny_id') ?: $registration->cpny_id));

        DB::connection('pgsql5')->beginTransaction();

        try {
            $quota = MsLndTrainingQuota::where('schedule_id', $registration->schedule_id)
                ->where('cpny_id', $cpnyId)
                ->lockForUpdate()
                ->first();

            if (!$quota) {
                DB::connection('pgsql5')->rollBack();

                return response()->json(['success' => false, 'message' => 'Kuota untuk perusahaan terpilih tidak ditemukan pada jadwal ini'], 422);
            }

            $seatCount = $this->activeSeatCount($registration->schedule_id, $cpnyId);

            if ($seatCount >= $quota->quota_pax) {
                DB::connection('pgsql5')->rollBack();

                return response()->json(['success' => false, 'message' => 'Kuota sudah penuh, tidak ada slot yang tersedia untuk perusahaan ini'], 422);
            }

            $companyName = MsCompany::where('cpny_id', $cpnyId)->value('cpny_name') ?? $cpnyId;
            $now = now();

            $registration->cpny_id = $cpnyId;
            $registration->status_registration = null;
            $registration->process_registration_user = $user->username;
            $registration->process_registration_date = $now;
            $registration->updated_by = $user->username;
            $registration->updated_at = $now;
            $registration->save();

            TrainingWaitlistNotifier::notifyCreatorManualAccept($registration, $user->name ?: $user->username);

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => $registration->user_registration . ' diterima (kuota ' . $companyName . ')',
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menerima peserta',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Rows currently holding a seat on a schedule+company pool: pending
     * approval, approved, or offered (waitlisted/cancelled/rejected don't).
     */
    private function activeSeatCount(string $scheduleId, string $cpnyId): int
    {
        return TrLndTrainingRegistration::where('schedule_id', $scheduleId)
            ->where('cpny_id', $cpnyId)
            ->where(function ($q) {
                $q->whereNull('status_registration')
                    ->orWhere('status_registration', TrLndTrainingRegistration::REG_STATUS_OFFERED);
            })
            ->where('status', '!=', TrLndTrainingRegistration::STATUS_REJECTED)
            ->count();
    }

    private function offerStillValid(TrLndTrainingRegistration $registration): bool
    {
        if ($registration->status_registration !== TrLndTrainingRegistration::REG_STATUS_OFFERED) {
            return false;
        }

        $expiresAt = $registration->offer_expires_at;

        if ($expiresAt && $expiresAt->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * TrMessage doctype 'TRN' is registered in DocumentNotificationService's
     * extendedDocTypeConfig(), so writing this row also surfaces in the bell
     * for the creator, current approval line, and HCDEVACCESS holders —
     * alongside whatever targeted email already went out for the same event.
     */
    private function notifyDocSystem(string $docId, string $cpnyId, string $deptId, string $message): void
    {
        TrMessage::create([
            'refnbr' => $docId,
            'doctype' => self::DOCTYPE,
            'message_date' => now(),
            'message_type' => 'SYSTEM',
            'cpny_id' => $cpnyId,
            'department_id' => $deptId,
            'username' => 'system',
            'name' => 'System',
            'message' => $message,
            'status' => 'A',
            'created_by' => 'system',
        ]);
    }

    private function submitForApproval(string $docId, string $cpnyId, string $deptId, User $user, Carbon $now, string $trainingName = ''): void
    {
        $approvalCtl = app(ApprovalController::class);

        $approvalCtl->loadLines(self::DOCTYPE, $cpnyId, $deptId);

        $approvalCtl->generateForDocument(
            $docId,
            self::DOCTYPE,
            $cpnyId,
            $deptId,
            $user->username,
            [],
            $now
        );

        $docUrl = url('/training-list/my');

        $approvalCtl->notifyFirstApprover(
            $docId,
            self::DOCTYPE,
            'P',
            'Training Registration',
            $docUrl,
            [
                'info' => $trainingName,
                'createdby' => $user->name ?? $user->username,
                'date' => $now->toDateTimeString(),
            ]
        );

        $this->notifyDocSystem($docId, $cpnyId, $deptId, 'Registrasi training baru menunggu persetujuan Anda.');
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
