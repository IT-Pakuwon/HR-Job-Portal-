<?php

namespace App\Http\Controllers;

use App\Exports\TrainingAttendanceExport;
use App\Http\Controllers\Traits\HasAttendanceWindow;
use App\Models\MsCompany;
use App\Models\MsDepartment;
use App\Models\MsLndTrainingFeedback;
use App\Models\StoGrading;
use App\Models\TrLndTrainingAttendance;
use App\Models\TrLndTrainingFeedbackAnswer;
use App\Models\TrLndTrainingRegistration;
use App\Models\MsLndTrainingSchedule;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

class TrainingAttendanceController extends Controller
{
    use HasAttendanceWindow;

    public function index()
    {
        return view('pages.training_attendance.index');
    }

    /**
     * Selectable "events" — one row per non-draft schedule date, newest
     * first. Not restricted to dates that already have an Approved
     * registrant — HR needs to be able to open an event and see "no one's
     * approved yet" rather than have it silently missing from the list.
     */
    public function events()
    {
        $details = MsLndTrainingSchedule::whereIn('status', ['P', 'C'])
            ->with('schedule.training')
            ->orderByDesc('schedule_date')
            ->get();

        $scheduleIds = $details->pluck('schedule_id');

        $approvedCounts = TrLndTrainingRegistration::whereIn('schedule_id', $scheduleIds)
            ->where('status', TrLndTrainingRegistration::STATUS_APPROVED)
            ->seated()
            ->select('schedule_id', DB::raw('count(*) as cnt'))
            ->groupBy('schedule_id')
            ->pluck('cnt', 'schedule_id');

        $gradeIds = $details->pluck('schedule.job_level')->filter()->unique();
        $gradeNames = $gradeIds->isEmpty()
            ? collect()
            : StoGrading::whereIn('grade_id', $gradeIds)->pluck('grade_name', 'grade_id');

        $rows = $details->map(function ($d) use ($gradeNames, $approvedCounts) {
            $schedule = $d->schedule;
            $training = $schedule?->training;

            return [
                'id' => $d->schedule_id,
                'docid' => $d->training_detail_id,
                'training_name' => $training->training_name ?? null,
                'grade_name' => $gradeNames[$schedule->job_level ?? null] ?? ($schedule->job_level ?? null),
                'schedule_date' => $d->schedule_date?->format('Y-m-d'),
                'start_time' => $d->schedule_start_time,
                'end_time' => $d->schedule_end_time,
                'mode' => $d->training_mode,
                'location' => $d->training_platform,
                'platform' => $d->training_platform,
                'status' => $d->status,
                'approved_count' => (int) ($approvedCounts[$d->schedule_id] ?? 0),
            ];
        })->values();

        return response()->json(['data' => $rows]);
    }

    private function decorateRegistrations($registrations, bool $withHistory = false)
    {
        $usernames = $registrations->pluck('user_registration')->unique();
        $names = $usernames->isEmpty() ? collect() : User::whereIn('username', $usernames)->pluck('name', 'username');

        $cpnyIds = $registrations->pluck('cpny_id')->filter()->unique();
        $cpnyNames = $cpnyIds->isEmpty() ? collect() : MsCompany::whereIn('cpny_id', $cpnyIds)->pluck('cpny_name', 'cpny_id');

        $deptIds = $registrations->pluck('department_id')->filter()->unique();
        $deptNames = $deptIds->isEmpty() ? collect() : MsDepartment::whereIn('department_id', $deptIds)->pluck('department_name', 'department_id');

        // Full scan/undo timeline, only when asked for (roster/scan don't need it —
        // just the after-event corrections view does). training_regist_id is a 1:1
        // key per row, so this can't pull in another participant's entries.
        $historyByDocid = collect();
        if ($withHistory) {
            $historyByDocid = TrLndTrainingAttendance::withTrashed()
                ->whereIn('training_regist_id', $registrations->pluck('training_regist_id'))
                ->orderBy('attendance_datetime')
                ->get()
                ->groupBy('training_regist_id');
        }

        return $registrations->map(function ($r) use ($names, $cpnyNames, $deptNames, $withHistory, $historyByDocid) {
            $row = [
                'id' => $r->id,
                'docid' => $r->training_regist_id,
                'username' => $r->user_registration,
                'name' => $names[$r->user_registration] ?? $r->user_registration,
                'cpny_name' => $cpnyNames[$r->cpny_id] ?? $r->cpny_id,
                'department_name' => $deptNames[$r->department_id] ?? $r->department_id,
                'attended_at' => $r->completed_at,
                'attended_by' => $r->completed_by,
            ];

            if ($withHistory) {
                $row['history'] = $historyByDocid->get($r->training_regist_id, collect())->map(fn ($h) => [
                    'attendance_datetime' => $h->attendance_datetime,
                    'status' => $h->status,
                    'created_by' => $h->created_by,
                    'voided' => $h->trashed(),
                ])->values();
            }

            return $row;
        })->values();
    }

    public function roster($scheduleId)
    {
        $detail = MsLndTrainingSchedule::where('schedule_id', $scheduleId)->firstOrFail();

        $registrations = TrLndTrainingRegistration::where('schedule_id', $scheduleId)
            ->where('status', TrLndTrainingRegistration::STATUS_APPROVED)
            ->seated()
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'data' => $this->decorateRegistrations($registrations),
        ]);
    }

    /**
     * Three barcode/QR formats can be scanned here:
     * - "TRN-xxxxxxxxxx" — per-registration code (User::attendance_code),
     *   good for exactly one event; looked up directly and cross-checked
     *   against the selected schedule.
     * - "USR-<username>" — a person's own permanent badge (User::barcode_code,
     *   shown on their account page), valid at any event. Since it isn't
     *   tied to one registration, it's resolved by finding *this* username's
     *   Approved/seated registration for the currently selected schedule.
     * - A vCard (from the account page's QR, ProfileController::qrImage()) —
     *   a phone camera reads this as a normal contact card, but it carries
     *   the same USR- code in a custom X-USERCODE field just for this
     *   scanner to find, so it doubles as valid check-in input.
     */
    public function scan(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|string',
            'code' => 'required|string',
        ]);

        $detail = MsLndTrainingSchedule::where('schedule_id', $request->schedule_id)->firstOrFail();
        $code = trim($request->code);

        if (stripos($code, 'BEGIN:VCARD') !== false && preg_match('/X-USERCODE:\s*(\S+)/i', $code, $m)) {
            $code = trim($m[1]);
        }

        if (stripos($code, 'USR-') === 0) {
            $username = substr($code, 4);

            $registration = TrLndTrainingRegistration::whereRaw('lower(user_registration) = ?', [strtolower($username)])
                ->where('schedule_id', $detail->schedule_id)
                ->where('status', TrLndTrainingRegistration::STATUS_APPROVED)
                ->seated()
                ->first();

            if (!$registration) {
                return response()->json(['success' => false, 'message' => 'Peserta ini tidak terdaftar/disetujui untuk event ini'], 404);
            }
        } else {
            $registration = TrLndTrainingRegistration::where('attendance_code', $code)
                ->where('status', TrLndTrainingRegistration::STATUS_APPROVED)
                ->seated()
                ->first();

            if (!$registration) {
                \Illuminate\Support\Facades\Log::warning('Unrecognized attendance scan', ['raw_code' => $request->code, 'resolved_code' => $code]);

                return response()->json(['success' => false, 'message' => 'Barcode tidak dikenali'], 404);
            }

            if (strcasecmp((string) $registration->schedule_id, (string) $detail->schedule_id) !== 0) {
                return response()->json(['success' => false, 'message' => 'Barcode ini bukan untuk event yang dipilih'], 422);
            }
        }

        if (!$this->isWithinAttendanceWindow($detail)) {
            return response()->json(['success' => false, 'message' => 'Barcode belum berlaku atau sudah kedaluwarsa'], 422);
        }

        return response()->json([
            'success' => true,
            'data' => $this->decorateRegistrations(collect([$registration]))->first(),
        ]);
    }

    public function markAttend($registrationId)
    {
        $registration = TrLndTrainingRegistration::findOrFail($registrationId);

        if ($registration->status_registration) {
            return response()->json(['success' => false, 'message' => 'Peserta belum memiliki slot pada event ini'], 422);
        }

        $detail = MsLndTrainingSchedule::where('schedule_id', $registration->schedule_id)->first();

        if (!$detail || !$this->isWithinAttendanceWindow($detail)) {
            return response()->json(['success' => false, 'message' => 'Barcode belum berlaku atau sudah kedaluwarsa'], 422);
        }

        if (!$registration->completed_at) {
            $user = Auth::user();
            $now = now();

            $registration->update([
                'completed_at' => $now,
                'completed_by' => $user->username ?? 'system',
                'updated_by' => $user->username ?? 'system',
            ]);

            TrLndTrainingAttendance::create([
                'training_regist_id' => $registration->training_regist_id,
                'attendance_datetime' => $now,
                'status' => 'A',
                'created_by' => $user->username ?? 'system',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $this->decorateRegistrations(collect([$registration->fresh()]))->first(),
        ]);
    }

    /**
     * HCDEV-only correction for a mis-scan: clears the registration's
     * completed_at/completed_by and voids (soft-deletes) its attendance log
     * row so the person can be re-scanned. training_regist_id is a 1:1 key
     * per row now, so this can't accidentally touch anyone else's log entry.
     */
    public function unmarkAttend($registrationId)
    {
        $user = Auth::user();
        abort_unless($user && $user->hasRole('HCDEVACCESS'), 403);

        $registration = TrLndTrainingRegistration::findOrFail($registrationId);

        if (!$registration->completed_at) {
            return response()->json(['success' => false, 'message' => 'Peserta ini belum ditandai hadir'], 422);
        }

        TrLndTrainingAttendance::where('training_regist_id', $registration->training_regist_id)
            ->where('status', 'A')
            ->get()
            ->each(fn ($row) => $row->delete());

        $registration->update([
            'completed_at' => null,
            'completed_by' => null,
            'updated_by' => $user->username,
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->decorateRegistrations(collect([$registration->fresh()]))->first(),
        ]);
    }

    public function afterEvent($scheduleId)
    {
        MsLndTrainingSchedule::where('schedule_id', $scheduleId)->firstOrFail();

        $registrations = TrLndTrainingRegistration::where('schedule_id', $scheduleId)
            ->where('status', TrLndTrainingRegistration::STATUS_APPROVED)
            ->whereNotNull('completed_at')
            ->orderBy('completed_at')
            ->get();

        $decorated = $this->decorateRegistrations($registrations, withHistory: true);

        $canManage = (bool) Auth::user()?->hasRole('HCDEVACCESS');

        return response()->json([
            'data' => $decorated,
            'can_undo' => $canManage,
        ]);
    }

    public function exportExcel($scheduleId)
    {
        return Excel::download(new TrainingAttendanceExport((string) $scheduleId), 'attendance.xlsx');
    }

    public function exportCsv($scheduleId)
    {
        return Excel::download(new TrainingAttendanceExport((string) $scheduleId), 'attendance.csv', ExcelFormat::CSV);
    }

    public function exportPdf($scheduleId)
    {
        $detail = MsLndTrainingSchedule::with('schedule.training')
            ->where('schedule_id', $scheduleId)
            ->firstOrFail();

        $registrations = TrLndTrainingRegistration::where('schedule_id', $scheduleId)
            ->where('status', TrLndTrainingRegistration::STATUS_APPROVED)
            ->seated()
            ->whereNotNull('completed_at')
            ->orderBy('completed_at')
            ->get();

        $pdf = Pdf::loadView('pages.training_attendance.export-pdf', [
            'rows' => $this->decorateRegistrations($registrations),
            'trainingName' => $detail->schedule?->training->training_name ?? '-',
            'scheduleDate' => $detail->schedule_date,
        ]);

        return $pdf->download('attendance.pdf');
    }

    /**
     * HCDEV-only: open the feedback window for this schedule. Re-opening
     * after a close clears feedback_closed_at so is_feedback_open flips back
     * to true.
     */
    public function openFeedback($scheduleId)
    {
        $user = Auth::user();
        abort_unless($user && $user->hasRole('HCDEVACCESS'), 403);

        $detail = MsLndTrainingSchedule::where('schedule_id', $scheduleId)->firstOrFail();

        $detail->update([
            'feedback_opened_at' => now(),
            'feedback_opened_by' => $user->username,
            'feedback_closed_at' => null,
            'feedback_closed_by' => null,
            'updated_by' => $user->username,
        ]);

        return response()->json(['success' => true, 'message' => 'Feedback dibuka untuk peserta']);
    }

    public function closeFeedback($scheduleId)
    {
        $user = Auth::user();
        abort_unless($user && $user->hasRole('HCDEVACCESS'), 403);

        $detail = MsLndTrainingSchedule::where('schedule_id', $scheduleId)->firstOrFail();

        if (!$detail->feedback_opened_at) {
            return response()->json(['success' => false, 'message' => 'Feedback belum pernah dibuka'], 422);
        }

        $detail->update([
            'feedback_closed_at' => now(),
            'feedback_closed_by' => $user->username,
            'updated_by' => $user->username,
        ]);

        return response()->json(['success' => true, 'message' => 'Feedback ditutup']);
    }

    /**
     * Aggregate results per question: average + distribution for Rating,
     * option counts for Single Choice, raw comment list for Long Text.
     * Also doubles as the feedback-window status view (is_open/opened/closed).
     */
    public function feedbackResults($scheduleId)
    {
        $detail = MsLndTrainingSchedule::where('schedule_id', $scheduleId)->firstOrFail();

        $attendedCount = TrLndTrainingRegistration::where('schedule_id', $scheduleId)
            ->where('status', TrLndTrainingRegistration::STATUS_APPROVED)
            ->whereNotNull('completed_at')
            ->count();

        $answers = TrLndTrainingFeedbackAnswer::where('schedule_id', $scheduleId)->get();
        $respondentCount = $answers->pluck('training_regist_id')->unique()->count();

        $usernames = $answers->pluck('user_registration')->unique();
        $names = $usernames->isEmpty() ? collect() : User::whereIn('username', $usernames)->pluck('name', 'username');

        $byQuestion = $answers->groupBy('question_order')->sortKeys()->map(function ($rows) use ($names) {
            $first = $rows->first();

            $result = [
                'question_order' => $first->question_order,
                'question_text' => $first->question_text,
                'question_type' => $first->question_type,
                'response_count' => $rows->count(),
            ];

            if ($first->question_type === MsLndTrainingFeedback::TYPE_RATING) {
                $numbers = $rows->pluck('answer_number')->filter(fn ($n) => $n !== null);
                $result['average'] = $numbers->isEmpty() ? null : round($numbers->avg(), 2);
                $result['distribution'] = $numbers->countBy();
            } elseif ($first->question_type === MsLndTrainingFeedback::TYPE_SINGLE_CHOICE) {
                $result['distribution'] = $rows->pluck('answer_text')->filter()->countBy();
            } else {
                $result['answers'] = $rows->map(fn ($r) => [
                    'name' => $names[$r->user_registration] ?? $r->user_registration,
                    'text' => $r->answer_text,
                ])->filter(fn ($a) => !empty($a['text']))->values();
            }

            return $result;
        })->values();

        return response()->json([
            'attended_count' => $attendedCount,
            'respondent_count' => $respondentCount,
            'is_open' => (bool) $detail->is_feedback_open,
            'opened_at' => $detail->feedback_opened_at,
            'opened_by' => $detail->feedback_opened_by,
            'closed_at' => $detail->feedback_closed_at,
            'can_manage' => (bool) Auth::user()?->hasRole('HCDEVACCESS'),
            'questions' => $byQuestion,
        ]);
    }
}
