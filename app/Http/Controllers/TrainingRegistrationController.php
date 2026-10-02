<?php

namespace App\Http\Controllers;

use App\Exports\TrainingAllRegistrationsExport;
use App\Http\Controllers\Traits\HasAttendanceWindow;
use App\Http\Controllers\Traits\HasAutonbr;
use App\Http\Controllers\Traits\UploadsToGcs;
use App\Models\CompanyAddress;
use App\Models\MsCategory;
use App\Models\MsCompany;
use App\Models\MsDepartment;
use App\Models\MsLndPlaces;
use App\Models\MsLndTrainingDetail;
use App\Models\MsLndTrainingQuota;
use App\Models\MsLndTrainingSchedule;
use App\Models\MsTrainingEvent;
use App\Models\StoGrading;
use App\Models\StoSubGradingJobLevel;
use App\Models\TrApproval;
use App\Models\TrLndTrainingFeedbackAnswer;
use App\Models\TrLndTrainingRegistration;
use App\Models\TrMessage;
use App\Models\User;
use App\Models\ViewUsersTalenta;
use App\Services\TrainingRegistrationService;
use App\Services\TrainingWaitlistNotifier;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Picqer\Barcode\BarcodeGeneratorPNG;
use Vinkla\Hashids\Facades\Hashids;

class TrainingRegistrationController extends Controller
{
    use HasAutonbr;
    use HasAttendanceWindow;
    use UploadsToGcs;

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
        return view('pages.training_list.index', ['initialEid' => null, 'initialMyEid' => null, 'initialAllRegsEid' => null, 'initialApprovalEid' => null]);
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

        return view('pages.training_list.index', ['initialEid' => $eid, 'initialMyEid' => null, 'initialAllRegsEid' => null, 'initialApprovalEid' => null]);
    }

    /**
     * Same browse page, but with a specific one of the caller's own
     * registrations' view modal auto-opened — same hash-id/shareable-URL
     * convention as show() above, scoped to the Registration List tab instead.
     *
     * Also viewable by any USERACCESS holder regardless of status, so an
     * approver can open a Pending registration to review/approve it (not
     * only after it's already Approved).
     *
     * ?tab=approvals (used by approval widget/notification links, since an
     * approver isn't the registrant and so wouldn't find this eid via the
     * plain myRegistrations() call) tells the page's Registration List tab
     * to also check pendingApprovals() to find and open this row.
     */
    public function showMy($eid, Request $request)
    {
        $id = Hashids::decode($eid)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();

        $query = TrLndTrainingRegistration::where('id', $id);

        if (!$user->hasRole('USERACCESS')) {
            $query->where('user_registration', $user->username);
        }

        $registration = $query->firstOrFail();

        // Determined from actual ownership rather than the ?tab=approvals
        // query flag alone — that flag gets stripped from the address bar
        // once the modal opens (see openMyViewModal()'s history.replaceState
        // call), so a plain refresh of the resulting clean URL would
        // otherwise fall back to "My Registration" and fail to find an
        // approver's (not the registrant's) row.
        $isApprovalTab = $request->query('tab') === 'approvals'
            || $registration->user_registration !== $user->username;

        return view('pages.training_list.index', [
            'initialEid' => null,
            'initialMyEid' => $isApprovalTab ? null : $eid,
            'initialAllRegsEid' => null,
            'initialApprovalEid' => $isApprovalTab ? $eid : null,
        ]);
    }

    /**
     * Same shareable-URL convention as showMy() above, but auto-opens the
     * Fill Feedback modal instead of the view modal — used by the "please
     * fill feedback" reminder link. Strictly the registrant's own eid, same
     * ownership rule TrainingFeedbackController::show()/submit() enforce.
     */
    public function showFeedback($eid)
    {
        $id = Hashids::decode($eid)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();

        TrLndTrainingRegistration::where('id', $id)
            ->where('user_registration', $user->username)
            ->firstOrFail();

        return view('pages.training_list.index', [
            'initialEid' => null,
            'initialMyEid' => null,
            'initialAllRegsEid' => null,
            'initialApprovalEid' => null,
            'initialFeedbackEid' => $eid,
        ]);
    }

    /**
     * Same shareable-URL convention as showMy() above, but for the
     * HCDEVACCESS-only List Registration tab — any employee's registration,
     * not just the caller's own (showMy()'s ownership check would 404 an HR
     * admin trying to open someone else's).
     */
    public function showAllRegs($eid)
    {
        if (!Auth::user()->hasRole('HCDEVACCESS') && !Auth::user()->hasRole('HCBPACCESS')) {
            abort(403, 'You do not have HCDEVACCESS or HCBPACCESS access');
        }

        $id = Hashids::decode($eid)[0] ?? null;
        abort_if(!$id, 404);

        TrLndTrainingRegistration::findOrFail($id);

        return view('pages.training_list.index', ['initialEid' => null, 'initialMyEid' => null, 'initialAllRegsEid' => $eid, 'initialApprovalEid' => null]);
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

        // Mandatory trainings only allow one schedule per participant (see
        // register()'s $mandatoryDuplicates check) — and that rule applies
        // across the whole training_id, not just within one "Add Schedule"
        // batch (docid). So a mandatory training split into two separate
        // batches/cards needs this looked up independently of $myRegs above,
        // which is keyed by schedule_id and only covers currently-published
        // schedules. Mirrors register()'s own duplicate definition exactly
        // (active = not cancelled, not rejected) rather than $myRegs' display
        // filter, and isn't limited to $scheduleIds since the blocking
        // registration may be on a schedule that's since closed.
        $myMandatoryRegsByTraining = TrLndTrainingRegistration::where('user_registration', $user->username)
            ->whereIn('training_id', MsTrainingEvent::where('is_mandatory', true)->pluck('training_id'))
            ->where(function ($q) {
                $q->whereNull('status_registration')
                    ->orWhere('status_registration', '!=', TrLndTrainingRegistration::REG_STATUS_CANCELLED);
            })
            ->where('status', '!=', TrLndTrainingRegistration::STATUS_REJECTED)
            ->get(['training_id', 'schedule_id', 'schedule_date', 'status', 'status_registration'])
            ->groupBy('training_id');

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

        // Grouped by registration_cpny_id — the quota pool a row actually
        // draws from — not cpny_id (the participant's fixed home company),
        // since a manually-reassigned row (see offerManually()/manualAccept())
        // consumes a different company's quota than its home company.
        $usage = TrLndTrainingRegistration::whereIn('schedule_id', $scheduleIds)
            ->where(function ($q) {
                $q->whereNull('status_registration')
                    ->orWhere('status_registration', TrLndTrainingRegistration::REG_STATUS_OFFERED);
            })
            ->where('status', '!=', TrLndTrainingRegistration::STATUS_REJECTED)
            ->select('schedule_id', 'registration_cpny_id', DB::raw('count(*) as cnt'))
            ->groupBy('schedule_id', 'registration_cpny_id')
            ->get()
            ->groupBy('schedule_id');

        $companyNames = MsCompany::whereIn('cpny_id', $userCpnyIds)->pluck('cpny_name', 'cpny_id');
        $myLevelGroup = $this->jobLevelGroupsFor(collect([$user]))->get($user->username);
        $levelLabels = StoGrading::labelsFor($details->pluck('schedule.job_level'));
        $speakerNames = User::whereIn('username', $details->pluck('training_speaker_username')->filter()->unique())
            ->pluck('name', 'username');
        $placeNames = MsLndPlaces::whereIn('places_id', $details->pluck('places_id')->filter()->unique())
            ->pluck('places_name', 'places_id');

        // One signed URL per distinct poster object, not per schedule row —
        // several dates in a batch share the same training_poster.
        $posterUrls = $details->pluck('schedule.training_poster')->filter()->unique()
            ->mapWithKeys(fn ($path) => [$path => $this->gcsSignedUrl($path)]);

        $scheduleOptions = $details->map(function ($d) use ($myRegs, $usage, $companyNames, $myLevelGroup, $levelLabels, $placeNames, $posterUrls) {
            $grouped = $usage->get($d->schedule_id, collect());

            $eligibleCompanies = $d->quota->map(function ($q) use ($grouped, $companyNames) {
                $seatCount = (int) $grouped->where('registration_cpny_id', $q->cpny_id)->sum('cnt');

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

            // Schedules created before the Level picker switched to
            // group_job_level still hold a legacy numeric grade_id — there's
            // no equivalent group to compare against, so the gate doesn't
            // apply to those. Unresolved caller level (null) is also treated
            // as a match — HR-maintained level mapping may not cover every
            // employee yet, and that gap shouldn't silently lock people out.
            // A batch created via the multi-select stores several levels
            // '|'-joined in that same field (not comma — group_job_level
            // labels contain commas themselves, e.g. "Sr. Officer, Officer,
            // Crew") — matching any one of them is enough, since they all
            // share this batch's dates/quota.
            $scheduleLevels = $this->splitJobLevels($d->schedule->job_level);
            $isLegacyLevel = $scheduleLevels->count() === 1 && ctype_digit($scheduleLevels->first());
            $levelMatch = $isLegacyLevel || $myLevelGroup === null || $scheduleLevels->contains($myLevelGroup);

            return [
                'id' => $d->schedule_id,
                'training_id' => $d->training_id,
                'training_name' => $d->schedule->training->training_name ?? null,
                'docid' => $d->training_detail_id,
                'schedule_date' => $d->schedule_date?->format('Y-m-d'),
                'start_time' => $d->schedule_start_time,
                'end_time' => $d->schedule_end_time,
                'mode' => $d->training_mode,
                'location' => $d->places_id ? ($placeNames[$d->places_id] ?? $d->places_id) : null,
                'platform' => $d->training_platform,
                'meeting_link' => $d->training_meeting_link,
                'poster_url' => $d->schedule->training_poster ? ($posterUrls[$d->schedule->training_poster] ?? null) : null,
                'grade_id' => $d->schedule->job_level,
                'grade_name' => $levelLabels[$d->schedule->job_level] ?? $d->schedule->job_level,
                'level_match' => $levelMatch,
                'speaker_name' => $d->training_speaker_name ?: $d->training_ext_speaker_name,
                'registration_deadline' => $d->registration_deadline,
                'is_open' => (!$d->registration_deadline || !Carbon::parse($d->registration_deadline)->endOfDay()->isPast()) && !$d->is_schedule_over,
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
        $rows = $scheduleOptions->groupBy('docid')->map(function ($schedules) use ($trainingsById, $categoryNames, $myMandatoryRegsByTraining) {
            $first = $schedules->first();
            $training = $trainingsById->get($first['training_id']);

            // Mandatory training, and the participant already holds an active
            // registration on a schedule that isn't one of THIS card's own
            // dates — registering themselves here would be rejected server
            // -side, so surface it instead of offering a button that fails.
            // A colleague isn't affected by this (it's a per-participant
            // rule), so this only ever disables the self-register path.
            $mandatoryBlock = null;
            if ($training->is_mandatory ?? false) {
                $scheduleIdsInCard = $schedules->pluck('id');
                $other = ($myMandatoryRegsByTraining->get($first['training_id']) ?? collect())
                    ->first(fn ($r) => !$scheduleIdsInCard->contains($r->schedule_id));

                if ($other) {
                    $mandatoryBlock = [
                        'schedule_date' => $other->schedule_date?->format('Y-m-d'),
                        'status' => $other->status_registration ?: $other->status,
                    ];
                }
            }

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
                'level_eligible' => $schedules->contains(fn ($s) => $s['level_match']),
                'eligible' => $schedules->contains(fn ($s) => count($s['eligible_companies']) > 0 && $s['level_match']),
                'my_mandatory_block' => $mandatoryBlock,
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
     * Same shape as splitMulti(), but for job_level specifically — that
     * field is joined with '|' rather than ',' because the group_job_level
     * labels it stores contain commas themselves (e.g. "Sr. Officer,
     * Officer, Crew"), which a comma split would incorrectly break apart.
     */
    private function splitJobLevels(?string $raw): \Illuminate\Support\Collection
    {
        return collect(explode('|', (string) $raw))
            ->map(fn ($v) => trim($v))
            ->filter()
            ->values();
    }

    /**
     * Each user's own level group (the same group_job_level bucket a
     * schedule's job_level is now set to) — resolved via ms_user.npk ->
     * view_users_talenta.employee_id -> job_level title -> matched against
     * hr_ms_sto_subgrading_joblevel.job_level_id.
     *
     * Both Talenta's title and job_level_id carry a " - N" disambiguation
     * suffix for duplicate titles (e.g. "Supervisor - 1"); stripped on both
     * sides before matching so "Supervisor" and "Supervisor - 1" line up
     * with the same group.
     *
     * A user who can't be resolved (no npk, no Talenta record, no matching
     * subgrade row) maps to null — callers treat that as "can't tell, don't
     * block" rather than a hard mismatch, since this is HR-maintained
     * reference data that may not cover every employee yet.
     *
     * @return \Illuminate\Support\Collection<string, ?string> username => group_job_level
     */
    private function jobLevelGroupsFor(\Illuminate\Support\Collection $users): \Illuminate\Support\Collection
    {
        $npks = $users->pluck('npk')->filter()->unique()->values();

        if ($npks->isEmpty()) {
            return $users->mapWithKeys(fn ($u) => [$u->username => null]);
        }

        $titlesByNpk = ViewUsersTalenta::whereIn('employee_id', $npks)->pluck('job_level', 'employee_id');

        $groupCpnyIds = $users->pluck('group_cpny_id')->filter()
            ->map(fn ($v) => strtoupper(trim($v)))->unique()->values();

        $stripSuffix = fn ($title) => strtolower(trim(preg_replace('/\s*-\s*\d+$/', '', (string) $title)));

        $groupByKey = [];
        StoSubGradingJobLevel::where('status', 'A')
            ->whereIn('group_cpny_id', $groupCpnyIds)
            ->whereNotNull('job_level_id')
            ->get(['group_cpny_id', 'job_level_id', 'group_job_level'])
            ->each(function ($row) use (&$groupByKey, $stripSuffix) {
                $key = strtoupper(trim($row->group_cpny_id)).'|'.$stripSuffix($row->job_level_id);
                $groupByKey[$key] ??= $row->group_job_level;
            });

        return $users->mapWithKeys(function ($user) use ($titlesByNpk, $groupByKey, $stripSuffix) {
            $title = $titlesByNpk[$user->npk] ?? null;

            if (!$title) {
                return [$user->username => null];
            }

            $key = strtoupper(trim($user->group_cpny_id)).'|'.$stripSuffix($title);

            return [$user->username => $groupByKey[$key] ?? null];
        });
    }

    /**
     * Colleagues eligible for batch registration: active ms_user rows in the
     * caller's own origin department. `scope=same` (default) also requires
     * the caller's exact origin company; `scope=diff` requires a DIFFERENT
     * company instead (still same department) for cross-company batches.
     */
    public function colleagues(Request $request)
    {
        $user = Auth::user();
        $originCpnyId = trim((string) $user->origin_cpny_id);
        $originDeptId = trim((string) $user->origin_department_id);

        if ($originCpnyId === '' || $originDeptId === '') {
            return response()->json(['data' => []]);
        }

        $scope = $request->get('scope', 'same');

        $query = User::query()
            ->where('status', 'A')
            ->where('origin_department_id', $originDeptId);

        if ($scope === 'diff') {
            $query->where('origin_cpny_id', '!=', $originCpnyId);
        } else {
            $query->where('origin_cpny_id', $originCpnyId);
        }

        $scheduleId = $request->get('schedule_id');
        if ($scheduleId) {
            $alreadyRegistered = TrLndTrainingRegistration::where('schedule_id', $scheduleId)
                ->where('status', '!=', TrLndTrainingRegistration::STATUS_REJECTED)
                ->where(function ($q) {
                    $q->whereNull('status_registration')
                        ->orWhere('status_registration', '!=', TrLndTrainingRegistration::REG_STATUS_CANCELLED);
                })
                ->pluck('user_registration');

            if ($alreadyRegistered->isNotEmpty()) {
                $query->whereNotIn('username', $alreadyRegistered);
            }
        }

        $search = trim((string) $request->get('q', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('username', 'ilike', "%{$search}%");
            });
        }

        $rows = $query->orderBy('name')->limit(50)->get(['username', 'name', 'origin_cpny_id', 'origin_department_id']);

        $cpnyNames = MsCompany::whereIn('cpny_id', $rows->pluck('origin_cpny_id')->filter()->unique())
            ->pluck('cpny_name', 'cpny_id');

        return response()->json([
            'data' => $rows->map(fn ($u) => [
                'username' => $u->username,
                'name' => $u->name ?: $u->username,
                'cpny_id' => $u->origin_cpny_id,
                'cpny_name' => $cpnyNames[$u->origin_cpny_id] ?? $u->origin_cpny_id,
                'department_id' => $u->origin_department_id,
            ])->values(),
        ]);
    }

    /**
     * Rows where you're the participant OR you're the one who submitted the
     * batch (registered a colleague) — each viewer only ever sees this same
     * union for themselves, so a colleague you registered still only shows
     * up on THEIR own "My Registration" via the user_registration half of
     * this OR, never anyone else's beyond that.
     */
    public function myRegistrations()
    {
        $user = Auth::user();

        $registrations = TrLndTrainingRegistration::where(function ($q) use ($user) {
                $q->where('user_registration', $user->username)
                    ->orWhere('created_by', $user->username);
            })
            ->with('schedule.schedule.training')
            ->orderByDesc('created_at')
            ->get();

        $answeredDocIds = TrLndTrainingFeedbackAnswer::whereIn('training_regist_id', $registrations->pluck('training_regist_id'))
            ->pluck('training_regist_id')
            ->unique();

        $placeIds = $registrations->pluck('schedule.places_id')->filter()->unique();
        $placeNames = $placeIds->isEmpty() ? collect() : MsLndPlaces::whereIn('places_id', $placeIds)->pluck('places_name', 'places_id');

        $levelLabels = StoGrading::labelsFor($registrations->pluck('schedule.schedule.job_level'));

        $participantUsernames = $registrations->pluck('user_registration')->filter()->unique();
        $participantNames = $participantUsernames->isEmpty() ? collect() : User::whereIn('username', $participantUsernames)->pluck('name', 'username');

        $rows = $registrations->map(function ($r) use ($user, $answeredDocIds, $placeNames, $levelLabels, $participantNames) {
            $isOwn = strcasecmp((string) $r->user_registration, (string) $user->username) === 0;
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
                'schedule_date' => ($r->schedule_date ?? $r->schedule?->schedule_date)?->format('Y-m-d'),
                'start_time' => $r->schedule?->schedule_start_time,
                'end_time' => $r->schedule?->schedule_end_time,
                'mode' => $r->schedule?->training_mode,
                'location' => $r->schedule?->places_id ? ($placeNames[$r->schedule->places_id] ?? $r->schedule->places_id) : null,
                'platform' => $r->schedule?->training_platform,
                'speaker_name' => $r->schedule?->training_speaker_name ?: $r->schedule?->training_ext_speaker_name,
                'grade_name' => $levelLabels[$r->schedule?->schedule?->job_level] ?? $r->schedule?->schedule?->job_level,
                'status' => $r->effective_status,
                'is_own' => $isOwn,
                'participant_username' => $r->user_registration,
                'participant_name' => $participantNames[$r->user_registration] ?? $r->user_registration,
                'offer_expires_at' => $isOwn && $r->status_registration === TrLndTrainingRegistration::REG_STATUS_OFFERED
                    ? $r->offer_expires_at
                    : null,
                'has_attended' => $hasAttended,
                'is_late_attendance' => $r->is_late_attendance,
                'feedback_open' => $feedbackOpen,
                'feedback_submitted' => $feedbackSubmitted,
                // Feedback/certificate/offer actions all require being the
                // actual participant server-side (see submit()/myCertificate()/
                // acceptOffer()/declineOffer()) — suppressed here too so a
                // registration you only submitted for a colleague never shows
                // an action that would just 403.
                'can_fill_feedback' => $isOwn && $hasAttended && $feedbackOpen,
                'can_view_certificate' => $isOwn && $hasAttended && $isApproved && $certificateReady,
                'stars' => $r->stars,
                'created_at' => $r->created_at,
            ];
        });

        return response()->json(['data' => $rows]);
    }

    /**
     * Lean feed for the "please fill feedback" dashboard reminder — the
     * caller's own attended registrations whose schedule has feedback open
     * and that don't have an answer yet. Deliberately not gated by
     * TRAININGLIST,VIEW (see route comment) so it works from any dashboard.
     */
    public function pendingFeedback()
    {
        $user = Auth::user();

        $registrations = TrLndTrainingRegistration::where('user_registration', $user->username)
            ->whereNotNull('completed_at')
            ->with('schedule.schedule.training')
            ->get();

        if ($registrations->isEmpty()) {
            return response()->json(['data' => []]);
        }

        $answeredDocIds = TrLndTrainingFeedbackAnswer::whereIn('training_regist_id', $registrations->pluck('training_regist_id'))
            ->pluck('training_regist_id')
            ->unique();

        $rows = $registrations
            ->filter(fn ($r) => (bool) $r->schedule?->is_feedback_open && !$answeredDocIds->contains($r->training_regist_id))
            ->map(fn ($r) => [
                'id' => $r->id,
                'eid' => Hashids::encode($r->id),
                'training_name' => $r->schedule?->schedule?->training?->training_name ?? null,
                'speaker_name' => $r->schedule?->training_speaker_name ?: $r->schedule?->training_ext_speaker_name,
            ])
            ->values();

        return response()->json(['data' => $rows]);
    }

    /**
     * The caller's own attended trainings + star breakdown, for the profile
     * page's "My Trainings & Stars" panel. Deliberately not gated by
     * TRAININGLIST,VIEW (see route comment) so every employee can see their
     * own record on their profile regardless of that module permission.
     */
    public function myTrainingStars()
    {
        $user = Auth::user();

        $registrations = TrLndTrainingRegistration::where('user_registration', $user->username)
            ->whereNotNull('completed_at')
            ->with('schedule.schedule.training')
            ->orderByDesc('completed_at')
            ->get();

        $rows = $registrations->map(fn ($r) => [
            'training_name' => $r->schedule?->schedule?->training?->training_name ?? null,
            'schedule_date' => ($r->schedule_date ?? $r->schedule?->schedule_date)?->format('Y-m-d'),
            'is_late_attendance' => $r->is_late_attendance,
            'attendance_stars' => $r->attendance_stars,
            'feedback_stars' => $r->feedback_stars,
            'stars' => $r->stars,
        ])->values();

        return response()->json([
            'data' => $rows,
            'total_stars' => $rows->sum('stars'),
        ]);
    }

    /**
     * Streams a certificate PDF rendered fresh from this registration's own
     * data (no stored file/row) — available once the registration is
     * Approved, the participant actually attended, and HR has closed the
     * feedback window for the schedule (is_certificate_ready). Self-service
     * only: scoped to the caller's own row.
     */
    public function myCertificate($id)
    {
        $user = Auth::user();

        $registration = TrLndTrainingRegistration::where('user_registration', $user->username)
            ->with('schedule.schedule.training')
            ->findOrFail($id);

        abort_unless($registration->status === TrLndTrainingRegistration::STATUS_APPROVED, 422, 'This registration has not been approved yet');
        abort_unless((bool) $registration->completed_at, 422, 'You have not been recorded as attending this training');

        $schedule = $registration->schedule;
        abort_unless($schedule && $schedule->is_certificate_ready, 422, 'The certificate for this training is not available yet');

        $trainingDetail = $schedule->schedule;
        $training = $trainingDetail?->training;
        abort_unless($trainingDetail && $training, 422, 'Training data is incomplete');

        $gradeName = StoGrading::labelsFor([$trainingDetail->job_level])->get($trainingDetail->job_level);

        $company = MsCompany::where('cpny_id', $registration->cpny_id)->first();
        $companyAddress = CompanyAddress::where('cpnyid', $registration->cpny_id)->first();
        $certificateNo = $registration->attendance_code ?: ('CERT-'.$registration->id);

        $pdf = Pdf::loadView('pages.training_attendance.certificate-pdf', [
            'participantName' => $user->name ?? $user->username,
            'trainingName' => $training->training_name,
            'gradeName' => $gradeName,
            'scheduleDate' => $schedule->schedule_date,
            'certificateNo' => $certificateNo,
            'issueDate' => now(),
            'companyName' => $companyAddress->cpnyname ?? $company->cpny_name ?? '-',
            'stars' => $registration->stars,
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
            return response()->json(['available' => false, 'message' => 'Registration has not been approved yet']);
        }

        if ($registration->status_registration) {
            return response()->json(['available' => false, 'message' => 'You do not have a slot for this event yet']);
        }

        if (!$registration->attendance_code) {
            $registration->attendance_code = 'TRN-'.strtoupper(Str::random(10));
            $registration->updated_by = $user->username;
            $registration->save();
        }

        $detail = MsLndTrainingSchedule::where('schedule_id', $registration->schedule_id)->first();

        if (!$detail) {
            return response()->json(['available' => false, 'message' => 'Schedule not found']);
        }

        $window = $this->attendanceWindow($detail);
        $now = now();

        if ($now->lessThan($window['from'])) {
            return response()->json([
                'available' => false,
                'message' => 'Barcode will be active on '.$window['from']->translatedFormat('d M Y H:i'),
            ]);
        }

        if ($now->greaterThan($window['until'])) {
            return response()->json(['available' => false, 'message' => 'Barcode has expired']);
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
     * registers that EXACT set of people (the submitter is NOT auto-included
     * — they must be listed explicitly to register themselves too). Every
     * participant must share the submitter's origin department; company may
     * differ. Each participant gets their own row AND their own
     * training_regist_id/approval chain, checked/consumed against their OWN
     * company's quota and routed to their OWN company's approval line — they
     * are submitted together but approved independently, not as a shared
     * batch document.
     */
    public function register(Request $request, string $scheduleId)
    {
        $detail = MsLndTrainingSchedule::where('schedule_id', $scheduleId)->firstOrFail();
        $user = Auth::user();

        if ($detail->status !== self::SCHEDULE_PUBLISHED) {
            return response()->json(['success' => false, 'message' => 'Registration for this schedule is already closed'], 422);
        }

        if ($detail->registration_deadline && Carbon::parse($detail->registration_deadline)->endOfDay()->isPast()) {
            return response()->json(['success' => false, 'message' => 'The registration deadline has passed'], 422);
        }

        if ($detail->is_schedule_over) {
            return response()->json(['success' => false, 'message' => 'This training schedule has already taken place'], 422);
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
                return response()->json(['success' => false, 'message' => "Participant {$username} not found"], 422);
            }

            // Colleagues must share the submitter's department — company may
            // differ (see colleagues() 'diff' scope for cross-company picks).
            if (strcasecmp($username, $user->username) !== 0) {
                $pDept = trim((string) $participant->origin_department_id);

                if ($pDept !== $originDeptId) {
                    return response()->json([
                        'success' => false,
                        'message' => "Participant {$username} is not in your department",
                    ], 422);
                }
            }

            $participants->push($participant);
        }

        // Level gate: a schedule's job_level is one-or-more group_job_level
        // buckets '|'-joined (see TrainingSessionController::levelSearch /
        // combineJobLevels) — every participant must resolve to one of those
        // buckets via their own npk. A participant who can't be resolved (no
        // npk/Talenta record/subgrade mapping) is let through rather than
        // blocked, since this is HR-maintained reference data that may not
        // cover everyone yet.
        $scheduleLevels = $this->splitJobLevels($detail->schedule?->job_level);
        $isLegacyLevel = $scheduleLevels->count() === 1 && ctype_digit($scheduleLevels->first());
        if ($scheduleLevels->isNotEmpty() && !$isLegacyLevel) {
            $levelGroups = $this->jobLevelGroupsFor($participants);

            foreach ($participants as $participant) {
                $participantLevel = $levelGroups->get($participant->username);

                if ($participantLevel !== null && !$scheduleLevels->contains($participantLevel)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Participant {$participant->username} is not at the appropriate level for this training",
                    ], 422);
                }
            }
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
                'message' => 'Already registered for this schedule: '.$duplicates->implode(', '),
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
                    'message' => 'This training is mandatory — already registered on another schedule: '.$mandatoryDuplicates->implode(', '),
                ], 422);
            }
        }

        DB::connection('pgsql5')->beginTransaction();

        try {
            // Colleagues no longer have to share the submitter's company (only
            // the department), so quota/seats are checked per PARTICIPANT'S
            // OWN company rather than once against the submitter's company —
            // each participant draws from and consumes their own company's
            // quota pool, independent of who submitted the batch.
            $participantCpnyIds = $participants->map(fn ($p) => trim((string) $p->origin_cpny_id))->unique()->values();

            $quotasByCpny = MsLndTrainingQuota::where('schedule_id', $scheduleId)
                ->whereIn('cpny_id', $participantCpnyIds)
                ->lockForUpdate()
                ->get()
                ->groupBy('cpny_id');

            $missingCpnyIds = $participantCpnyIds->filter(fn ($id) => !isset($quotasByCpny[$id]));

            if ($missingCpnyIds->isNotEmpty()) {
                DB::connection('pgsql5')->rollBack();

                $missingNames = MsCompany::whereIn('cpny_id', $missingCpnyIds)->pluck('cpny_name', 'cpny_id');
                $labels = $missingCpnyIds->map(fn ($id) => $missingNames[$id] ?? $id)->implode(', ');

                return response()->json(['success' => false, 'message' => "This training is not available for: {$labels}"], 422);
            }

            // Plain array, not a Collection: $collection[$key]-- below is a no-op
            // on Illuminate\Support\Collection (PHP can't do indirect
            // modification of an overloaded ArrayAccess element without a
            // by-reference offsetGet), which would silently leave every
            // company's seat count undecremented for the rest of the batch.
            $seatsRemainingByCpny = $participantCpnyIds->mapWithKeys(function ($cpnyId) use ($quotasByCpny, $scheduleId) {
                $quotaPax = $quotasByCpny[$cpnyId]->sum('quota_pax');
                $seatCount = $this->activeSeatCount($scheduleId, $cpnyId);

                return [$cpnyId => max(0, $quotaPax - $seatCount)];
            })->all();

            // Approval always starts at registration time, whether or not
            // there's a seat free. status_registration is the SEATING flag on
            // top of it: waitlisted people are still pending/approved in the
            // approval pipeline while they wait. Seats are handed out to the
            // submitted set in order (first-come within the batch, per their
            // own company) rather than all-or-nothing, so a batch can land
            // partly seated / partly waitlisted when fewer seats remain than
            // participants — each participant still gets their own
            // independent document/approval chain below.
            $status = TrLndTrainingRegistration::STATUS_PENDING;

            $now = now();
            $docIds = collect();
            $seatedCount = 0;
            $waitlistedCount = 0;

            // Each participant gets their OWN training_regist_id and their own
            // independent approval chain — a colleague batch is no longer one
            // shared document. This means the approver acts on each person
            // individually instead of approving the whole batch in one click,
            // but it makes training_regist_id a true 1:1 key for every row
            // (needed so attendance logging can unambiguously tell participants
            // in the same submission apart).
            foreach ($participants as $participant) {
                $pCpnyId = trim((string) $participant->origin_cpny_id);
                $pDeptId = trim((string) $participant->origin_department_id);

                $docId = $this->generateRegistrationCode($user->username);
                $docIds->push($docId);

                if ($seatsRemainingByCpny[$pCpnyId] > 0) {
                    $statusReg = null;
                    $seatsRemainingByCpny[$pCpnyId]--;
                    $seatedCount++;
                } else {
                    $statusReg = TrLndTrainingRegistration::REG_STATUS_WAITLISTED;
                    $waitlistedCount++;
                }

                TrLndTrainingRegistration::create([
                    'training_regist_id' => $docId,
                    'training_regist_date' => $now->toDateString(),
                    'training_id' => $detail->training_id,
                    'training_detail_id' => $detail->training_detail_id,
                    'schedule_id' => $detail->schedule_id,
                    'schedule_date' => $detail->schedule_date,
                    'cpny_id' => $pCpnyId,
                    'registration_cpny_id' => $pCpnyId,
                    'department_id' => $pDeptId,
                    'user_registration' => $participant->username,
                    'qty_registration' => 1,
                    'status' => $status,
                    'status_registration' => $statusReg,
                    'process_registration_user' => $statusReg ? $user->username : null,
                    'process_registration_date' => $statusReg ? $now : null,
                    'created_by' => $user->username,
                    'created_at' => $now,
                ]);

                $this->submitForApproval($docId, $pCpnyId, $pDeptId, $user, $now, $trainingName);
            }

            DB::connection('pgsql5')->commit();

            $message = 'Registration successful, awaiting approval';
            if ($waitlistedCount > 0 && $seatedCount > 0) {
                $message = "{$seatedCount} seats remaining, {$seatedCount} participants registered and {$waitlistedCount} placed on the waiting list — approval still proceeds";
            } elseif ($waitlistedCount > 0) {
                $message = 'Quota full, all participants placed on the waiting list — approval still proceeds';
            } elseif ($docIds->count() > 1) {
                $message = 'Registration successful ('.$docIds->count().' documents: '.$docIds->implode(', ').'), awaiting approval';
            }

            return response()->json([
                'success' => true,
                'training_regist_id' => $docIds->first(),
                'training_regist_ids' => $docIds->values(),
                'status' => $waitlistedCount > 0
                    ? TrLndTrainingRegistration::REG_STATUS_WAITLISTED
                    : $status,
                'seated_count' => $seatedCount,
                'waitlisted_count' => $waitlistedCount,
                'message' => $message,
            ]);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
            // abort()/abort_if() calls further down the stack (e.g. ApprovalController::loadLines()
            // aborting with "Approval line belum di-setup, Please contact IT!") carry a real,
            // actionable message — let those surface as-is instead of being masked below.
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Failed to complete registration',
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
                'message' => 'Failed to complete registration',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * HCDEVACCESS-only admin cancel — approval participants can only
     * approve/reject their own step (see approve()/reject()); cancelling a
     * registration outright (Approved, Waiting List, or Pending/Offered) is
     * an HR action. Blocked once the row is already terminal (Rejected or
     * already Cancelled), once attendance has been recorded, or once the
     * schedule date has passed (same-day is still cancellable; H+1 onward
     * is not).
     */
    public function cancel(Request $request, $id)
    {
        $registration = TrLndTrainingRegistration::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasRole('HCDEVACCESS') && !$user->hasRole('HCBPACCESS')) {
            abort(403);
        }

        if ($registration->status === TrLndTrainingRegistration::STATUS_REJECTED
            || $registration->status_registration === TrLndTrainingRegistration::REG_STATUS_CANCELLED) {
            return response()->json(['success' => false, 'message' => 'This registration is no longer active'], 422);
        }

        if ($registration->completed_at) {
            return response()->json(['success' => false, 'message' => 'This registration cannot be cancelled because attendance has already been recorded'], 422);
        }

        $scheduleDate = $registration->schedule_date ?? $registration->schedule?->schedule_date;
        if ($scheduleDate && $scheduleDate->lt(Carbon::today())) {
            return response()->json(['success' => false, 'message' => 'This registration cannot be cancelled because its training schedule has already passed'], 422);
        }

        DB::connection('pgsql5')->beginTransaction();

        try {
            $wasOffered = $registration->status_registration === TrLndTrainingRegistration::REG_STATUS_OFFERED;
            $heldSeat = !$registration->status_registration;

            $registration->status_registration = TrLndTrainingRegistration::REG_STATUS_CANCELLED;
            $registration->process_registration_user = $user->username;
            $registration->process_registration_date = now();
            $registration->updated_by = $user->username;
            $registration->updated_at = now();
            $registration->save();

            // Void any approval step still sitting at 'P' (same pattern as
            // BookingCarController::cancel()) — otherwise the approver's
            // Waiting Approval list keeps this doc forever, and they could
            // still approve/reject a registration HR already cancelled.
            TrApproval::where('refnbr', $registration->training_regist_id)
                ->where('aprv_doctype', self::DOCTYPE)
                ->where('status', 'P')
                ->update([
                    'status' => 'X',
                    'updated_by' => $user->username,
                    'updated_at' => now(),
                ]);

            if ($heldSeat) {
                TrainingRegistrationService::promoteWaitlistIfOpen($registration);
            } elseif ($wasOffered) {
                TrainingRegistrationService::cascadeToNextWaitlist($registration);
            }

            DB::connection('pgsql5')->commit();

            // Unlike approve()/reject(), cancel() previously notified nobody —
            // the participant (and whoever registered them, if different) only
            // found out by reopening My Registration. Sent after commit so a
            // notification never goes out for a change that then rolled back.
            $docUrl = url('/training-list/my/'.Hashids::encode($registration->id));

            app(ApprovalController::class)->notifyRequesterOnStatus(
                $registration->training_regist_id,
                'Training Registration',
                'X',
                $registration->created_by,
                $docUrl
            );

            $this->notifyParticipantOnStatus($registration, 'X', $docUrl);

            $this->notifyDocSystem(
                $registration->training_regist_id,
                $registration->cpny_id,
                $registration->department_id,
                'Your training registration has been cancelled.',
                'CANCEL'
            );

            return response()->json(['success' => true, 'message' => 'Registration cancelled successfully']);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel registration',
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
            return response()->json(['success' => false, 'message' => 'This offer is no longer valid'], 422);
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
                    ? 'Slot accepted, awaiting approval to finish'
                    : 'Slot accepted',
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to accept offer',
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
            return response()->json(['success' => false, 'message' => 'This offer is no longer valid'], 422);
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

            return response()->json(['success' => true, 'message' => 'Offer declined']);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to decline offer',
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

        $docUrl = url('/training-list/my/'.Hashids::encode($registration->id));

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

                $this->notifyParticipantOnStatus($registration, 'C', $docUrl);

                $this->notifyDocSystem(
                    $refnbr,
                    $registration->cpny_id,
                    $registration->department_id,
                    'Your training registration has been fully approved.',
                    'APPROVE'
                );
            },
            function ($next, Carbon $now) use ($registration, $docUrl) {
                if (!$next) {
                    return;
                }

                // Deliberately no notifyDocSystem() bell entry here — "awaiting your
                // approval" already has a dedicated home on the Approval Dashboard's
                // Waiting Approval list, and the approver already gets an email via
                // notifyFirstApprover() below. A bell notice on top of both was pure
                // duplication, unlike the other TRN system notices (offer, approved,
                // rejected, ...) which have nowhere else to surface.
                app(ApprovalController::class)->notifyFirstApprover(
                    $registration->training_regist_id,
                    self::DOCTYPE,
                    'P',
                    'Training Registration',
                    $docUrl,
                    ['createdby' => $registration->created_by, 'date' => $now->toDateTimeString()]
                );
            }
        );

        return response()->json($result, $result['ok'] ?? false ? 200 : 422);
    }

    public function reject(Request $request, $id)
    {
        $registration = TrLndTrainingRegistration::findOrFail($id);
        $user = Auth::user();

        $docUrl = url('/training-list/my/'.Hashids::encode($registration->id));

        $result = app(ApprovalController::class)->rejectStep(
            $registration->training_regist_id,
            self::DOCTYPE,
            $user->username,
            $user->name,
            function (string $refnbr, Carbon $now) use ($registration, $docUrl) {
                // A rejected row is already excluded from the seat-usage count
                // (see json()/waitlistForOffer()'s status != 'R' filter), so
                // the quota number frees up on its own — but nobody is
                // auto-promoted unless this row was actually holding/offered
                // that seat (a still-waitlisted row wasn't occupying one).
                $wasOffered = $registration->status_registration === TrLndTrainingRegistration::REG_STATUS_OFFERED;
                $heldSeat = !$registration->status_registration;

                // Rejection is terminal — clear the lifecycle flag (same pattern
                // as acceptOffer()/manualAccept() nulling it on acceptance) so
                // effective_status falls through to the raw 'R' status instead
                // of permanently showing a stale "Waiting List"/"Offered" chip
                // and blocking re-registration on the browse page.
                $registration->status_registration = null;
                $registration->status = TrLndTrainingRegistration::STATUS_REJECTED;
                $registration->updated_by = Auth::user()->username;
                $registration->updated_at = $now;
                $registration->save();

                if ($heldSeat) {
                    TrainingRegistrationService::promoteWaitlistIfOpen($registration);
                } elseif ($wasOffered) {
                    TrainingRegistrationService::cascadeToNextWaitlist($registration);
                }

                app(ApprovalController::class)->notifyRequesterOnStatus(
                    $refnbr,
                    'Training Registration',
                    'R',
                    $registration->created_by,
                    $docUrl
                );

                $this->notifyParticipantOnStatus($registration, 'R', $docUrl);

                $this->notifyDocSystem(
                    $refnbr,
                    $registration->cpny_id,
                    $registration->department_id,
                    'Your training registration has been rejected.',
                    'REJECT'
                );
            }
        );

        return response()->json($result, $result['ok'] ?? false ? 200 : 422);
    }

    /**
     * Every TRN document the caller is or was an approver on: rows still
     * 'P' (the caller is the current/active approver — same "active step"
     * definition as ApprovalController::assertUserCanAct(), so this matches
     * exactly what approve()/reject() would let them act on right now, via
     * the comma-separated aprv_username list), plus rows already 'A'/'R'
     * where the caller was the one who made that decision — approveStep()/
     * rejectStep() overwrite aprv_username with the actor's own username on
     * decision, so an exact match (not the comma-list one) is correct there.
     * Feeds the Approval sub-tab, which lets the caller filter by status.
     */
    public function pendingApprovals(Request $request)
    {
        $user = Auth::user();
        $username = strtolower(trim($user->username));

        $approvalRows = TrApproval::query()
            ->where('aprv_doctype', self::DOCTYPE)
            ->whereNotNull('aprv_datebefore')
            ->where(function ($q) use ($username) {
                $q->where(function ($q2) use ($username) {
                    $q2->where('status', 'P')
                        ->whereRaw(
                            "(',' || lower(regexp_replace(coalesce(aprv_username,''), '\s+', '', 'g')) || ',') like ?",
                            ['%,'.$username.',%']
                        );
                })->orWhere(function ($q2) use ($username) {
                    $q2->whereIn('status', ['A', 'R'])
                        ->whereRaw("lower(trim(coalesce(aprv_username, ''))) = ?", [$username]);
                });
            })
            ->orderByDesc(DB::raw('coalesce(aprv_dateafter, aprv_datebefore)'))
            ->get(['refnbr', 'aprv_datebefore', 'aprv_dateafter', 'status']);

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

        // Same place/level lookups as myRegistrations(), so a pending-approval
        // row carries everything openMyViewModal() needs (eid, schedule/mode/
        // speaker/level) — lets the Waiting Approval tab reuse that same
        // read-only detail modal instead of only exposing Approve/Reject.
        $placeIds = $registrations->pluck('schedule.places_id')->filter()->unique();
        $placeNames = $placeIds->isEmpty() ? collect() : MsLndPlaces::whereIn('places_id', $placeIds)->pluck('places_name', 'places_id');

        $levelLabels = StoGrading::labelsFor($registrations->pluck('schedule.schedule.job_level'));

        $data = $approvalRows
            ->map(function ($apr) use ($registrations, $names, $companyNames, $departmentNames, $placeNames, $levelLabels) {
                $r = $registrations->get($apr->refnbr);

                if (!$r) {
                    return null;
                }

                return [
                    'id' => $r->id,
                    'eid' => Hashids::encode($r->id),
                    'docid' => $r->training_regist_id,
                    'training_name' => $r->schedule?->schedule?->training?->training_name ?? null,
                    'username' => $r->user_registration,
                    'name' => $names[$r->user_registration] ?? $r->user_registration,
                    'cpny_id' => $r->cpny_id,
                    'cpny_name' => $companyNames[$r->cpny_id] ?? $r->cpny_id,
                    'department_id' => $r->department_id,
                    'department_name' => $departmentNames[$r->department_id] ?? $r->department_id,
                    'schedule_date' => ($r->schedule_date ?? $r->schedule?->schedule_date)?->format('Y-m-d'),
                    'start_time' => $r->schedule?->schedule_start_time,
                    'end_time' => $r->schedule?->schedule_end_time,
                    'mode' => $r->schedule?->training_mode,
                    'location' => $r->schedule?->places_id ? ($placeNames[$r->schedule->places_id] ?? $r->schedule->places_id) : null,
                    'platform' => $r->schedule?->training_platform,
                    'speaker_name' => $r->schedule?->training_speaker_name ?: $r->schedule?->training_ext_speaker_name,
                    'grade_name' => $levelLabels[$r->schedule?->schedule?->job_level] ?? $r->schedule?->schedule?->job_level,
                    'status' => $r->effective_status,
                    'approval_status' => $apr->status,
                    'action_date' => $apr->status === 'P' ? $apr->aprv_datebefore : $apr->aprv_dateafter,
                ];
            })
            ->filter()
            ->values();

        return response()->json(['data' => $data]);
    }

    /**
     * HCDEVACCESS-only: every training registration across all employees, so
     * HR can see who has registered and where each one currently stands
     * (waiting approval, approved, waitlisted, offered, rejected, cancelled)
     * without being limited to just what's actively pending their own
     * approval action (that's pendingApprovals() above).
     */
    public function allRegistrations(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasRole('HCDEVACCESS') && !$user->hasRole('HCBPACCESS')) {
            abort(403, 'You do not have HCDEVACCESS or HCBPACCESS access');
        }

        // Same "past Draft" scoping as registrationSummary()'s cards/filter
        // options — a still-Draft schedule isn't open for registration, so
        // no row here should ever be able to belong to one.
        $registrations = TrLndTrainingRegistration::query()
            ->with('schedule.schedule.training')
            ->whereHas('schedule', fn ($q) => $q->where('status', '!=', self::SCHEDULE_DRAFT))
            ->orderByDesc('created_at')
            ->get();

        $usernames = $registrations->pluck('user_registration')->unique();
        $names = $usernames->isEmpty() ? collect() : User::whereIn('username', $usernames)->pluck('name', 'username');

        // Includes registration_cpny_id too — once a row's been manually
        // reassigned to a different company's quota, that company may not
        // otherwise appear among the plain cpny_id values below.
        $cpnyIds = $registrations->pluck('cpny_id')
            ->merge($registrations->pluck('registration_cpny_id'))
            ->filter()->unique();
        $companyNames = $cpnyIds->isEmpty() ? collect() : MsCompany::whereIn('cpny_id', $cpnyIds)->pluck('cpny_name', 'cpny_id');

        $deptIds = $registrations->pluck('department_id')->filter()->unique();
        $departmentNames = $deptIds->isEmpty() ? collect() : MsDepartment::whereIn('department_id', $deptIds)->pluck('department_name', 'department_id');

        $placeIds = $registrations->pluck('schedule.places_id')->filter()->unique();
        $placeNames = $placeIds->isEmpty() ? collect() : MsLndPlaces::whereIn('places_id', $placeIds)->pluck('places_name', 'places_id');

        $levelLabels = StoGrading::labelsFor($registrations->pluck('schedule.schedule.job_level'));

        // Queue position within each schedule's waitlist, oldest-first — same
        // ordering waitlistForOffer()/the Waitlist Management tab uses, just
        // surfaced here as an actual number so this tab can double as that
        // queue view instead of a separate one.
        $queueNumbers = collect();
        $registrations
            ->filter(fn ($r) => $r->status_registration === TrLndTrainingRegistration::REG_STATUS_WAITLISTED
                && $r->status !== TrLndTrainingRegistration::STATUS_REJECTED)
            ->groupBy('schedule_id')
            ->each(function ($group) use ($queueNumbers) {
                // flatMap()->collapse() would silently drop these registration-id
                // keys (collapse() re-indexes numerically) — build the map by hand
                // instead so `$queueNumbers[$r->id]` lookups below actually hit.
                $group->sortBy('created_at')->values()->each(fn ($r, $i) => $queueNumbers->put($r->id, $i + 1));
            });

        // Accept (force-seat) applies to Waiting List rows on a schedule
        // that's already Closed (see manualAccept()); Offer applies to the
        // same rows but on a schedule that's still Published/open instead
        // (see offerManually()). Both allow approval to still be Pending —
        // the approval chain runs independently of seating (see those
        // methods' doc comments) — so only Rejected is excluded here.
        // offerManually()'s second case (already seated/offered, not
        // waitlisted, approval Pending) is Published-only, so it's added as
        // a separate OR branch rather than folded into the waitlisted check.
        // Quota/usage is only worth fetching for that combined subset, not
        // every registration on the page.
        $eligibleScheduleIds = $registrations->filter(function ($r) {
            $scheduleStatus = $r->schedule?->status;
            $notRejected = $r->status !== TrLndTrainingRegistration::STATUS_REJECTED;

            $waitlistCase = $r->status_registration === TrLndTrainingRegistration::REG_STATUS_WAITLISTED
                && $notRejected
                && in_array($scheduleStatus, [self::SCHEDULE_CLOSED, self::SCHEDULE_PUBLISHED], true);

            $activeReassignCase = in_array($r->status_registration, [null, TrLndTrainingRegistration::REG_STATUS_OFFERED], true)
                && $r->status === TrLndTrainingRegistration::STATUS_PENDING
                && $scheduleStatus === self::SCHEDULE_PUBLISHED;

            return $waitlistCase || $activeReassignCase;
        })->pluck('schedule_id')->unique();

        $quotas = $eligibleScheduleIds->isEmpty() ? collect() : MsLndTrainingQuota::whereIn('schedule_id', $eligibleScheduleIds)->get();
        $quotaCompanyNames = MsCompany::whereIn('cpny_id', $quotas->pluck('cpny_id')->unique())->pluck('cpny_name', 'cpny_id');

        // Grouped by registration_cpny_id — the quota pool a row actually
        // draws from — not cpny_id (see activeSeatCount()'s doc comment).
        $usage = $eligibleScheduleIds->isEmpty() ? collect() : TrLndTrainingRegistration::whereIn('schedule_id', $eligibleScheduleIds)
            ->where(function ($q) {
                $q->whereNull('status_registration')
                    ->orWhere('status_registration', TrLndTrainingRegistration::REG_STATUS_OFFERED);
            })
            ->where('status', '!=', TrLndTrainingRegistration::STATUS_REJECTED)
            ->select('schedule_id', 'registration_cpny_id', DB::raw('count(*) as cnt'))
            ->groupBy('schedule_id', 'registration_cpny_id')
            ->get()
            ->groupBy('schedule_id');

        $data = $registrations->map(function ($r) use ($names, $companyNames, $departmentNames, $placeNames, $levelLabels, $queueNumbers, $quotas, $quotaCompanyNames, $usage) {
            // Accept (force-seat, skipping the 24h offer step) stays closed-
            // schedule-only — it's the fallback for once nothing auto-
            // promotes anymore. Offer is the opposite: only on a still-
            // Published/open schedule, so HR can push a waitlisted person a
            // slot (e.g. under a different company's quota) without waiting
            // on a cancellation to trigger auto-promotion — once a schedule
            // is Closed, offering no longer makes sense since registration
            // itself is done. Waitlisted + not Rejected already implies not
            // cancelled and not already offered, since a row can only hold
            // one status_registration value at a time. Pending approval is
            // allowed through (see manualAccept()/offerManually() for why
            // that's safe) so HR isn't stuck waiting on approval to finish
            // before seating someone. A second, Published-only case covers
            // offerManually()'s pure-reassignment path: not waitlisted
            // (already seated or already offered) but still Pending.
            $isWaitlistedAndEligible = $r->status_registration === TrLndTrainingRegistration::REG_STATUS_WAITLISTED
                && $r->status !== TrLndTrainingRegistration::STATUS_REJECTED;

            $isActiveReassignEligible = in_array($r->status_registration, [null, TrLndTrainingRegistration::REG_STATUS_OFFERED], true)
                && $r->status === TrLndTrainingRegistration::STATUS_PENDING;

            $canAccept = $isWaitlistedAndEligible && $r->schedule?->status === self::SCHEDULE_CLOSED;
            $canOffer = ($isWaitlistedAndEligible || $isActiveReassignEligible) && $r->schedule?->status === self::SCHEDULE_PUBLISHED;

            $quotaOptions = collect();
            if ($canAccept || $canOffer) {
                $usedByCpny = collect($usage->get($r->schedule_id, collect()));
                // This row itself may already be counted in its own current
                // pool (the active-reassign case: status_registration is
                // null/'O' already) — exclude it so its own company doesn't
                // show as more full than it actually is, same reasoning as
                // activeSeatCount()'s $excludeId.
                $selfCountsToward = in_array($r->status_registration, [null, TrLndTrainingRegistration::REG_STATUS_OFFERED], true)
                    ? $r->registration_cpny_id
                    : null;

                $quotaOptions = $quotas->where('schedule_id', $r->schedule_id)
                    ->map(function ($q) use ($usedByCpny, $quotaCompanyNames, $selfCountsToward) {
                        $used = (int) $usedByCpny->where('registration_cpny_id', $q->cpny_id)->sum('cnt');

                        if ($selfCountsToward === $q->cpny_id) {
                            $used = max(0, $used - 1);
                        }

                        return [
                            'cpny_id' => $q->cpny_id,
                            'cpny_name' => $quotaCompanyNames[$q->cpny_id] ?? $q->cpny_id,
                            'quota_pax' => $q->quota_pax,
                            'used' => $used,
                            'available' => max(0, $q->quota_pax - $used),
                        ];
                    })->values();
            }

            return [
                'id' => $r->id,
                'eid' => Hashids::encode($r->id),
                'docid' => $r->training_regist_id,
                'training_id' => $r->training_id,
                'training_name' => $r->schedule?->schedule?->training?->training_name ?? null,
                'username' => $r->user_registration,
                'name' => $names[$r->user_registration] ?? $r->user_registration,
                'cpny_id' => $r->cpny_id,
                'cpny_name' => $companyNames[$r->cpny_id] ?? $r->cpny_id,
                'registration_cpny_id' => $r->registration_cpny_id,
                'registration_cpny_name' => $companyNames[$r->registration_cpny_id] ?? $r->registration_cpny_id,
                'department_id' => $r->department_id,
                'department_name' => $departmentNames[$r->department_id] ?? $r->department_id,
                'grade_name' => $levelLabels[$r->schedule?->schedule?->job_level] ?? $r->schedule?->schedule?->job_level,
                'schedule_date' => ($r->schedule_date ?? $r->schedule?->schedule_date)?->format('Y-m-d'),
                'start_time' => $r->schedule?->schedule_start_time,
                'end_time' => $r->schedule?->schedule_end_time,
                'mode' => $r->schedule?->training_mode,
                'location' => $r->schedule?->places_id ? ($placeNames[$r->schedule->places_id] ?? $r->schedule->places_id) : null,
                'platform' => $r->schedule?->training_platform,
                'speaker_name' => $r->schedule?->training_speaker_name ?: $r->schedule?->training_ext_speaker_name,
                'schedule_status' => $r->schedule?->status,
                'approval_status' => $r->status,
                'status_registration' => $r->status_registration,
                'queue_no' => $queueNumbers[$r->id] ?? null,
                'status' => $r->effective_status,
                'registered_at' => $r->created_at,
                'has_attended' => (bool) $r->completed_at,
                'can_accept' => $canAccept,
                'can_offer' => $canOffer,
                'quota_options' => $quotaOptions,
            ];
        })->values();

        return response()->json(['data' => $data]);
    }

    /**
     * HCDEVACCESS-only: Excel download of the List Registration tab, honoring
     * the same training/level/schedule_date/status/search filters currently
     * applied on screen (see TrainingAllRegistrationsExport, which mirrors
     * allRegistrations() above row-for-row).
     */
    public function exportAllRegistrations(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasRole('HCDEVACCESS') && !$user->hasRole('HCBPACCESS')) {
            abort(403, 'You do not have HCDEVACCESS or HCBPACCESS access');
        }

        return Excel::download(
            new TrainingAllRegistrationsExport(
                $request->query('training_id'),
                $request->query('status'),
                $request->query('search'),
                $request->query('level'),
                $request->query('schedule_date'),
                $request->query('company')
            ),
            'training-registrations-'.now()->format('Ymd_His').'.xlsx'
        );
    }

    /**
     * HCDEVACCESS-only: quota utilization + status-count overview for the
     * List Registration tab, optionally scoped to one training event
     * (?training_id=). The training filter list only offers events that
     * currently have a PUBLISHED schedule (what HR can still act on right
     * now); once picked, totals cover every schedule of that training
     * regardless of its own status — same when nothing is picked, just
     * across all trainings.
     */
    public function registrationSummary(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasRole('HCDEVACCESS') && !$user->hasRole('HCBPACCESS')) {
            abort(403, 'You do not have HCDEVACCESS or HCBPACCESS access');
        }

        $trainingId = $request->query('training_id');

        $publishedTrainingIds = MsLndTrainingSchedule::where('status', self::SCHEDULE_PUBLISHED)
            ->pluck('training_id')
            ->unique();

        $trainingOptions = $publishedTrainingIds->isEmpty()
            ? collect()
            : MsTrainingEvent::whereIn('training_id', $publishedTrainingIds)
                ->orderBy('training_name')
                ->get(['training_id', 'training_name']);

        $scheduleQuery = MsLndTrainingSchedule::query();
        if ($trainingId) {
            $scheduleQuery->where('training_id', $trainingId);
        }
        $scopedSchedules = $scheduleQuery->get(['schedule_id', 'training_detail_id', 'schedule_date', 'status']);

        // Everything below — quota/reserved/status cards and Level/Schedule
        // Date filter options — stays scoped to schedules that are past
        // Draft and not Cancelled: a Draft schedule isn't open for
        // registration yet, and a Cancelled schedule is dead, so neither
        // should contribute quota, counts, or filterable values anywhere on
        // screen (a level/date only reachable through a cancelled schedule
        // shouldn't show up as a pickable option). The registration table
        // itself (allRegistrations()) is scoped separately and still shows
        // rows under a cancelled schedule, since those are real historical
        // registrations.
        $liveSchedules = $scopedSchedules->whereNotIn('status', [self::SCHEDULE_DRAFT, self::SCHEDULE_CANCELLED]);

        // Level/Schedule Date options: not every ms_lnd_training_detail batch
        // (a batch can have zero schedules under it) — only ones with a live
        // schedule, matching what the Master Training page groups by. These
        // list every value available for the selected training regardless of
        // the current level/schedule_date pick below, so picking one doesn't
        // prune the other's options out from under the user.
        $detailIds = $liveSchedules->pluck('training_detail_id')->filter()->unique();
        $detailJobLevels = $detailIds->isEmpty()
            ? collect()
            : MsLndTrainingDetail::whereIn('training_detail_id', $detailIds)->pluck('job_level', 'training_detail_id');
        $detailLabels = StoGrading::labelsFor($detailJobLevels->values());

        $levelOptions = $detailJobLevels->values()
            ->map(fn ($jl) => $detailLabels[$jl] ?? $jl)
            ->unique()
            ->sort()
            ->values();

        $scheduleDateOptions = $liveSchedules->pluck('schedule_date')
            ->filter()
            ->map(fn ($d) => $d->format('Y-m-d'))
            ->unique()
            ->sort()
            ->values();

        // Cards (quota/reserved/status counts) narrow further to the
        // specific level/schedule_date currently picked in the filter bar —
        // otherwise picking one schedule date still totaled quota across
        // every live schedule of the training, which read as "not filtered".
        $level = $request->query('level');
        $scheduleDate = $request->query('schedule_date');

        $cardSchedules = $liveSchedules;

        if ($scheduleDate) {
            $cardSchedules = $cardSchedules->filter(fn ($s) => $s->schedule_date?->format('Y-m-d') === $scheduleDate);
        }

        if ($level) {
            $matchingDetailIds = $detailJobLevels
                ->filter(fn ($jl) => ($detailLabels[$jl] ?? $jl) === $level)
                ->keys();
            $cardSchedules = $cardSchedules->filter(fn ($s) => $matchingDetailIds->contains($s->training_detail_id));
        }

        $scheduleIds = $cardSchedules->pluck('schedule_id');

        $quotas = MsLndTrainingQuota::whereIn('schedule_id', $scheduleIds)->get();
        $companyNames = MsCompany::whereIn('cpny_id', $quotas->pluck('cpny_id')->unique())
            ->pluck('cpny_name', 'cpny_id');

        $totalByCpny = $quotas->groupBy('cpny_id')->map(fn ($g) => (int) $g->sum('quota_pax'));

        // Reserved = currently holding or offered a seat (not cancelled, not
        // rejected) — the same "used" definition as json()/waitlistForOffer().
        // Grouped by registration_cpny_id (the quota pool actually consumed),
        // not cpny_id — see activeSeatCount()'s doc comment.
        $reservedByCpny = TrLndTrainingRegistration::whereIn('schedule_id', $scheduleIds)
            ->where(function ($q) {
                $q->whereNull('status_registration')
                    ->orWhere('status_registration', TrLndTrainingRegistration::REG_STATUS_OFFERED);
            })
            ->where('status', '!=', TrLndTrainingRegistration::STATUS_REJECTED)
            ->select('registration_cpny_id', DB::raw('count(*) as cnt'))
            ->groupBy('registration_cpny_id')
            ->pluck('cnt', 'registration_cpny_id');

        $byCompany = $totalByCpny->keys()
            ->merge($reservedByCpny->keys())
            ->unique()
            ->map(fn ($cpnyId) => [
                'cpny_id' => $cpnyId,
                'cpny_name' => $companyNames[$cpnyId] ?? $cpnyId,
                'reserved' => (int) ($reservedByCpny[$cpnyId] ?? 0),
                'total_quota' => (int) ($totalByCpny[$cpnyId] ?? 0),
            ])
            ->sortByDesc('total_quota')
            ->values();

        $statusCounts = TrLndTrainingRegistration::whereIn('schedule_id', $scheduleIds)
            ->select('status', 'status_registration', DB::raw('count(*) as cnt'))
            ->groupBy('status', 'status_registration')
            ->get()
            ->reduce(function ($carry, $row) {
                $effective = $row->status_registration ?: $row->status;
                $carry[$effective] = ($carry[$effective] ?? 0) + (int) $row->cnt;

                return $carry;
            }, []);

        return response()->json([
            'trainings' => $trainingOptions->values(),
            'levels' => $levelOptions,
            'schedule_dates' => $scheduleDateOptions,
            'overall' => [
                'reserved' => (int) $byCompany->sum('reserved'),
                'total_quota' => (int) $byCompany->sum('total_quota'),
            ],
            'by_company' => $byCompany,
            'status_counts' => [
                'waiting_approval' => $statusCounts['P'] ?? 0,
                'approved' => $statusCounts['C'] ?? 0,
                'rejected' => $statusCounts['R'] ?? 0,
                'cancelled' => $statusCounts['X'] ?? 0,
                'waiting_list' => $statusCounts[TrLndTrainingRegistration::REG_STATUS_WAITLISTED] ?? 0,
                'waiting_offer' => $statusCounts[TrLndTrainingRegistration::REG_STATUS_OFFERED] ?? 0,
            ],
        ]);
    }

    /**
     * HCDEVACCESS-only: seat a waitlisted person on a closed schedule. The
     * person's approval chain was already started at registration time (see
     * submitForApproval()) and runs independently of this row's cpny_id/
     * status_registration — approveStep()/rejectStep() act purely off the
     * TrApproval rows keyed by refnbr, so reassigning the seat here never
     * disturbs an in-flight approval. HR may therefore accept a person whose
     * approval is still Pending ('P') as well as one already Approved ('C');
     * only Rejected is blocked. HR may also pick a different company's quota
     * than the person's origin company — that reassigns registration_cpny_id
     * (the quota pool this seat is counted against), NOT cpny_id, which stays
     * fixed as the participant's home company (used for scoping/approval/
     * reports — see activeSeatCount()'s doc comment for why these two fields
     * are kept separate).
     */
    public function manualAccept(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user->hasRole('HCDEVACCESS') && !$user->hasRole('HCBPACCESS')) {
            abort(403, 'You do not have HCDEVACCESS or HCBPACCESS access');
        }

        $registration = TrLndTrainingRegistration::findOrFail($id);

        if ($registration->status_registration !== TrLndTrainingRegistration::REG_STATUS_WAITLISTED) {
            return response()->json(['success' => false, 'message' => 'This registration is not on the waiting list'], 422);
        }

        if (!in_array($registration->status, [
            TrLndTrainingRegistration::STATUS_PENDING,
            TrLndTrainingRegistration::STATUS_APPROVED,
        ], true)) {
            return response()->json(['success' => false, 'message' => 'This registration has been rejected and can no longer be seated'], 422);
        }

        $detail = MsLndTrainingSchedule::where('schedule_id', $registration->schedule_id)->first();

        if (!$detail || $detail->status !== self::SCHEDULE_CLOSED) {
            return response()->json(['success' => false, 'message' => 'Manual acceptance is only for schedules that are already closed'], 422);
        }

        $cpnyId = trim((string) ($request->input('cpny_id') ?: $registration->registration_cpny_id ?: $registration->cpny_id));

        DB::connection('pgsql5')->beginTransaction();

        try {
            $quota = MsLndTrainingQuota::where('schedule_id', $registration->schedule_id)
                ->where('cpny_id', $cpnyId)
                ->lockForUpdate()
                ->first();

            if (!$quota) {
                DB::connection('pgsql5')->rollBack();

                return response()->json(['success' => false, 'message' => 'Quota for the selected company was not found on this schedule'], 422);
            }

            $seatCount = $this->activeSeatCount($registration->schedule_id, $cpnyId, $registration->id);

            if ($seatCount >= $quota->quota_pax) {
                DB::connection('pgsql5')->rollBack();

                return response()->json(['success' => false, 'message' => 'Quota is already full, no slots available for this company'], 422);
            }

            $companyName = MsCompany::where('cpny_id', $cpnyId)->value('cpny_name') ?? $cpnyId;
            $now = now();

            $registration->registration_cpny_id = $cpnyId;
            $registration->status_registration = null;
            $registration->process_registration_user = $user->username;
            $registration->process_registration_date = $now;
            $registration->updated_by = $user->username;
            $registration->updated_at = $now;
            $registration->save();

            TrainingWaitlistNotifier::notifyCreatorManualAccept($registration, $user->name ?: $user->username);

            DB::connection('pgsql5')->commit();

            $approvalPending = $registration->status === TrLndTrainingRegistration::STATUS_PENDING;

            return response()->json([
                'success' => true,
                'message' => $registration->user_registration.' accepted (quota '.$companyName.')'
                    .($approvalPending ? ' — approval still pending' : ''),
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to accept participant',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * HCDEVACCESS/HCBPACCESS-only: manually push a quota slot/reassignment
     * onto a participant while the schedule is still Published/open. Two
     * distinct cases are allowed, both gated to the schedule being Published
     * (manualAccept() is the Closed-schedule counterpart and does NOT cover
     * the second case below):
     *
     * 1. Waitlisted (status_registration='W'), approval Pending or Approved:
     *    sends the same 24h accept/decline offer that auto-promotion sends
     *    (see TrainingRegistrationService::offerSlot()), so HR can proactively
     *    push a slot to someone waitlisted (e.g. under a different company's
     *    quota that still has room) instead of waiting on a cancellation to
     *    trigger auto-promotion.
     * 2. NOT waitlisted — already holding an active seat (status_registration
     *    null) or already sitting on an outstanding offer ('O') — but
     *    approval is still Pending: this is a pure quota reassignment, not a
     *    fresh offer. The participant already has (or is already deciding on)
     *    a seat, so there's nothing to re-offer; this just moves which
     *    company's quota that seat/offer counts against, with no 24h clock
     *    restarted and no new offer notification sent. Approved rows are
     *    excluded from this case since once approval is done and they're not
     *    waitlisted, their seat is already final.
     *
     * Both cases rely on the approval chain running independently of this
     * row's cpny_id/status_registration — TrApproval rows are keyed by
     * refnbr and approveStep()/rejectStep() never re-derive company or seat
     * info (see those methods), so reassigning/offering the seat never
     * disturbs an in-flight approval. Rejected rows are excluded entirely.
     * The quota pool a row draws from is tracked in registration_cpny_id,
     * NOT cpny_id — cpny_id stays fixed as the participant's home company
     * (scoping/approval/reports), while registration_cpny_id is what moves
     * when HR reassigns to a different company (see activeSeatCount()).
     * Quota is a hard cap: a company (own or another) with no free seats is
     * never a valid target — this returns 'quota_full' => true and refuses,
     * same as manualAccept()'s closed-schedule equivalent. There is no
     * override; HR must pick a company that actually has room.
     */
    public function offerManually(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user->hasRole('HCDEVACCESS') && !$user->hasRole('HCBPACCESS')) {
            abort(403, 'You do not have HCDEVACCESS or HCBPACCESS access');
        }

        $registration = TrLndTrainingRegistration::findOrFail($id);

        $isWaitlistCase = $registration->status_registration === TrLndTrainingRegistration::REG_STATUS_WAITLISTED
            && in_array($registration->status, [
                TrLndTrainingRegistration::STATUS_PENDING,
                TrLndTrainingRegistration::STATUS_APPROVED,
            ], true);

        $isActiveReassignCase = in_array($registration->status_registration, [null, TrLndTrainingRegistration::REG_STATUS_OFFERED], true)
            && $registration->status === TrLndTrainingRegistration::STATUS_PENDING;

        if (!$isWaitlistCase && !$isActiveReassignCase) {
            return response()->json(['success' => false, 'message' => 'This registration is not eligible for a manual offer/reassignment'], 422);
        }

        $detail = MsLndTrainingSchedule::where('schedule_id', $registration->schedule_id)->first();

        if (!$detail || $detail->status !== self::SCHEDULE_PUBLISHED) {
            return response()->json(['success' => false, 'message' => 'Manual offer is only available while the schedule is still open (Published)'], 422);
        }

        $cpnyId = trim((string) ($request->input('cpny_id') ?: $registration->registration_cpny_id ?: $registration->cpny_id));

        DB::connection('pgsql5')->beginTransaction();

        try {
            $quota = MsLndTrainingQuota::where('schedule_id', $registration->schedule_id)
                ->where('cpny_id', $cpnyId)
                ->lockForUpdate()
                ->first();

            if (!$quota) {
                DB::connection('pgsql5')->rollBack();

                return response()->json(['success' => false, 'message' => 'Quota for the selected company was not found on this schedule'], 422);
            }

            $seatCount = $this->activeSeatCount($registration->schedule_id, $cpnyId, $registration->id);

            // Hard cap — a company (own or another) with no free seats simply
            // isn't a valid target; this never exceeds quota_pax (same rule
            // manualAccept() already enforces for the closed-schedule case).
            if ($seatCount >= $quota->quota_pax) {
                DB::connection('pgsql5')->rollBack();

                return response()->json([
                    'success' => false,
                    'quota_full' => true,
                    'used' => $seatCount,
                    'quota_pax' => $quota->quota_pax,
                    'message' => "Quota for {$cpnyId} is already full ({$seatCount}/{$quota->quota_pax}) — pick a company with available seats.",
                ], 422);
            }

            $registration->registration_cpny_id = $cpnyId;

            if ($isWaitlistCase) {
                TrainingRegistrationService::offerSlot($registration, $user->username);
            } else {
                // Pure reassignment — no 24h clock, no offer notification;
                // the seat/offer this row already holds just moves quota.
                $registration->updated_by = $user->username;
                $registration->updated_at = now();
                $registration->save();
            }

            DB::connection('pgsql5')->commit();

            $approvalPending = $registration->status === TrLndTrainingRegistration::STATUS_PENDING;
            $companyName = MsCompany::where('cpny_id', $cpnyId)->value('cpny_name') ?? $cpnyId;

            $message = $isWaitlistCase
                ? $registration->user_registration.' has been offered the slot'
                : $registration->user_registration.'\'s registration is now using '.$companyName.'\'s quota';

            return response()->json([
                'success' => true,
                'message' => $message.($approvalPending ? ' — approval still pending' : ''),
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to offer slot',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Rows currently holding a seat on a schedule+company quota pool:
     * pending approval, approved, or offered (waitlisted/cancelled/rejected
     * don't). Filtered by registration_cpny_id — the quota pool a row
     * actually draws from — not cpny_id (the participant's fixed home
     * company), since offerManually()/manualAccept() can reassign a row onto
     * a different company's quota without changing its home company.
     *
     * $excludeId drops a specific row from the count — needed when checking
     * room for a row that may already be counted in its OWN current pool
     * (offerManually()'s active-reassign case: status_registration is
     * already null/'O', so re-targeting the same company at exact capacity
     * would otherwise self-block a no-op reassignment).
     */
    private function activeSeatCount(string $scheduleId, string $cpnyId, ?int $excludeId = null): int
    {
        return TrLndTrainingRegistration::where('schedule_id', $scheduleId)
            ->where('registration_cpny_id', $cpnyId)
            ->where(function ($q) {
                $q->whereNull('status_registration')
                    ->orWhere('status_registration', TrLndTrainingRegistration::REG_STATUS_OFFERED);
            })
            ->where('status', '!=', TrLndTrainingRegistration::STATUS_REJECTED)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
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
     * $eventCode drives the bell's label/icon (see DocumentNotificationService's
     * trnSystemEventMeta()) instead of the generic "New Comment" styling.
     * Kept to <=8 chars: tr_message.message_type is varchar(10) and the 'S_'
     * prefix (see trnSystemEventMeta()'s detection) already takes 2.
     */
    /**
     * notifyRequesterOnStatus() above only reaches created_by — the person who
     * submitted the registration. When that's someone registering a colleague
     * (participants[] in register(), L743+), user_registration (the actual
     * attendee) never gets told their training was approved/rejected unless we
     * email them here too. The bell notice already covers this correctly via
     * trnSystemEventRecipients()'s APPROVE/REJECT case in
     * DocumentNotificationService — this closes the same gap for email.
     */
    private function notifyParticipantOnStatus(TrLndTrainingRegistration $registration, string $statusCode, string $docUrl): void
    {
        if (!$registration->user_registration || $registration->user_registration === $registration->created_by) {
            return;
        }

        $creator = User::where('username', $registration->created_by)->first();

        app(ApprovalController::class)->notifyRequesterOnStatus(
            $registration->training_regist_id,
            'Training Registration',
            $statusCode,
            $registration->user_registration,
            $docUrl,
            ['createdby' => $creator->name ?? $registration->created_by]
        );
    }

    private function notifyDocSystem(string $docId, string $cpnyId, string $deptId, string $message, string $eventCode): void
    {
        TrMessage::create([
            'refnbr' => $docId,
            'doctype' => self::DOCTYPE,
            'message_date' => now(),
            'message_type' => 'S_' . $eventCode,
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

        // See the matching comment in approve()'s notifyFirstApprover closure — no
        // notifyDocSystem() bell entry here either, same reasoning.
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
    }

    private function generateRegistrationCode(string $username): string
    {
        $year = (int) Carbon::now()->year;
        $month = Carbon::now()->format('m');

        $auto = $this->nextAutonbr(
            self::DOCTYPE,
            $year,
            $month,
            $username,
            'Training Registration'
        );

        $yy = substr((string) $year, 2, 2);

        return self::DOCTYPE.$yy.$month.sprintf('%04d', $auto['next']);
    }
}
