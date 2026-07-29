<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\TrFinding;
use App\Models\TrFindingActivity;
use App\Models\MsDepartmentOpr;
use App\Models\TrMessage;
use App\Models\TrWeeklyMeeting;
use App\Models\TrWeeklyMeetingFinding;
use App\Models\TrWeeklyMeetingMom;
use App\Models\TrWeeklyMeetingParticipant;
use App\Models\User;
use App\Models\Usercpny;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WeeklyMeetingController extends Controller
{
    use HasAutonbr;

    public function index()
    {
        return view('pages.weekly-meeting.weekly');
    }

    public function json(Request $request)
    {
        $companyIds = $this->companyIds($request->user()->username);
        $draw = (int) $request->input('draw', 1);
        $start = max((int) $request->input('start', 0), 0);
        $length = min(max((int) $request->input('length', 25), 1), 100);
        $search = trim((string) $request->input('search.value', ''));

        $query = TrWeeklyMeeting::query()->whereIn('cpny_id', $companyIds);
        $recordsTotal = (clone $query)->count();

        if ($search !== '') {
            $query->where(function (Builder $subQuery) use ($search) {
                $subQuery->where('weeklymeeting_id', 'ilike', "%{$search}%")
                    ->orWhere('weeklymeeting_topic', 'ilike', "%{$search}%")
                    ->orWhere('cpny_id', 'ilike', "%{$search}%")
                    ->orWhere('status', 'ilike', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $query)->count();
        $columns = [
            1 => 'weeklymeeting_date',
            2 => 'weeklymeeting_topic',
            4 => 'status',
        ];
        $orderColumn = $columns[(int) $request->input('order.0.column', 1)] ?? 'weeklymeeting_date';
        $orderDirection = strtolower((string) $request->input('order.0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $meetings = $query->orderBy($orderColumn, $orderDirection)
            ->orderByDesc('id')
            ->skip($start)
            ->take($length)
            ->get([
                'id', 'weeklymeeting_id', 'weeklymeeting_date', 'cpny_id',
                'weeklymeeting_startdate', 'weeklymeeting_enddate',
                'weeklymeeting_topic', 'status', 'created_by', 'created_at', 'completed_at',
            ]);
        $commentCounts = TrMessage::query()
            ->where('doctype', 'WOM')
            ->whereIn('refnbr', $meetings->pluck('weeklymeeting_id'))
            ->where(fn ($commentQuery) => $commentQuery
                ->whereNull('message_type')
                ->orWhere('message_type', '<>', 'Private'))
            ->where(fn ($statusQuery) => $statusQuery
                ->whereNull('status')
                ->orWhere('status', '<>', 'X'))
            ->selectRaw('refnbr, COUNT(*) AS total')
            ->groupBy('refnbr')
            ->pluck('total', 'refnbr');

        $data = $meetings->map(function (TrWeeklyMeeting $meeting) use ($commentCounts) {
            return [
                'weeklymeeting_id' => $meeting->weeklymeeting_id,
                'weeklymeeting_date' => $meeting->created_at
                    ? Carbon::parse($meeting->created_at)->locale('id')->translatedFormat('l, d F Y H:i')
                    : '-',
                'weeklymeeting_topic' => $meeting->weeklymeeting_topic,
                'comments_count' => (int) ($commentCounts[$meeting->weeklymeeting_id] ?? 0),
                'status' => $meeting->status,
                'status_label' => $meeting->completed_at || strtoupper((string) $meeting->status) === 'C'
                    ? 'Completed'
                    : 'Open',
            ];
        });

        return response()->json(compact('draw', 'recordsTotal', 'recordsFiltered', 'data'));
    }

    public function createWeeklyMeeting(Request $request)
    {
        $companyIds = $this->companyIds($request->user()->username);
        $preferredCompanyId = $request->user()->origin_cpny_id ?: $request->user()->cpny_id;
        $companyId = $companyIds->contains($preferredCompanyId)
            ? $preferredCompanyId
            : $companyIds->first();
        abort_if(!$companyId, 403, 'No active company access was found.');

        $users = $this->companyUsers($companyId);
        $lastMeeting = TrWeeklyMeeting::query()
            ->where('cpny_id', $companyId)
            ->where(fn (Builder $query) => $query->whereNull('status')->orWhere('status', '<>', 'X'))
            ->latest('weeklymeeting_date')
            ->latest('id')
            ->first();
        $participants = $lastMeeting
            ? TrWeeklyMeetingParticipant::query()
                ->where('weeklymeeting_id', $lastMeeting->weeklymeeting_id)
                ->where(fn (Builder $query) => $query->whereNull('status')->orWhere('status', '<>', 'X'))
                ->orderBy('order_participant')
                ->pluck('user_participant')
                ->filter()
                ->values()
            : collect();

        return view('pages.weekly-meeting.createweekly', [
            'companyId' => $companyId,
            'users' => $users,
            'participants' => $participants,
            'defaultDate' => now()->toDateString(),
            'defaultTime' => now()->format('H:i'),
        ]);
    }

    public function storeWeeklyMeeting(Request $request)
    {
        $companyIds = $this->companyIds($request->user()->username);
        $user = $request->user();
        $meetingCompanyId = trim((string) ($user->origin_cpny_id ?: $user->cpny_id));
        abort_unless(
            $meetingCompanyId !== '' && $companyIds->contains($meetingCompanyId),
            403,
            'Origin company or user company is not available in your active company access.'
        );
        $validated = $request->validate([
            'cpny_id' => ['required', Rule::in($companyIds->all())],
            'weeklymeeting_topic' => ['required', 'string', 'max:500'],
            'weeklymeeting_date' => ['required', 'date'],
            'meeting_time' => ['required', 'date_format:H:i'],
            'participants' => ['nullable', 'array'],
            'participants.*' => ['required', 'string', 'distinct'],
        ]);
        $validated['cpny_id'] = $meetingCompanyId;
        $allowedUsers = $this->companyUsers($meetingCompanyId)->keyBy('username');
        foreach ($validated['participants'] ?? [] as $username) {
            abort_unless($allowedUsers->has($username), 422, "Participant {$username} is not available.");
        }

        $meetingDate = Carbon::parse($validated['weeklymeeting_date']);
        $periodStart = $meetingDate->copy()->subWeek()->format('Y-m-d').' '.$validated['meeting_time'].':00';
        $periodEnd = $meetingDate->copy()->subDay()->format('Y-m-d').' '.$validated['meeting_time'].':00';
        $autonbrCompanyId = trim((string) ($user->origin_cpny_id ?: $user->cpny_id));
        abort_if($autonbrCompanyId === '', 422, 'Origin company or user company is required for autonumber.');
        $auto = $this->nextAutonbrByCpnyid(
            'WOM',
            (int) $meetingDate->year,
            $meetingDate->format('m'),
            $autonbrCompanyId,
            $user->username,
            'Weekly Meeting'
        );
        $meetingId = 'WOM'.$meetingDate->format('ym').sprintf('%04d', (int) $auto['next']);

        DB::connection('pgsql7')->transaction(function () use (
            $validated, $user, $meetingDate, $meetingId, $allowedUsers, $periodStart, $periodEnd
        ) {
            $meeting = TrWeeklyMeeting::query()->create([
                'weeklymeeting_id' => $meetingId,
                'weeklymeeting_date' => $meetingDate->toDateString(),
                'cpny_id' => $validated['cpny_id'],
                'department_id' => 'OPERATION',
                'weeklymeeting_startdate' => $periodStart,
                'weeklymeeting_enddate' => $periodEnd,
                'weeklymeeting_topic' => $validated['weeklymeeting_topic'],
                'status' => 'O',
                'created_by' => $user->username,
                'updated_by' => $user->username,
            ]);

            foreach (array_values($validated['participants'] ?? []) as $index => $username) {
                TrWeeklyMeetingParticipant::query()->create([
                    'weeklymeeting_id' => $meetingId,
                    'cpny_id' => $validated['cpny_id'],
                    'order_participant' => $index + 1,
                    'user_participant' => $username,
                    'name_participant' => $allowedUsers[$username]->name,
                    'status' => 'A',
                    'created_by' => $user->username,
                    'updated_by' => $user->username,
                ]);
            }

            $findings = TrFinding::query()
                ->where('cpny_id', $meeting->cpny_id)
                ->whereDate('finding_date', '>=', $meeting->weeklymeeting_startdate->toDateString())
                ->whereDate('finding_date', '<=', $meeting->weeklymeeting_enddate->toDateString())
                ->whereIn('status', ['O', 'P', 'C'])
                ->get(['finding_id', 'status']);

            foreach ($findings as $finding) {
                TrWeeklyMeetingFinding::query()->create([
                    'weeklymeeting_id' => $meetingId,
                    'cpny_id' => $meeting->cpny_id,
                    'finding_id' => $finding->finding_id,
                    'finding_status' => $finding->status,
                    'status' => 'A',
                    'created_by' => $user->username,
                    'updated_by' => $user->username,
                ]);
            }
        });

        return redirect()->route('weekly-meeting.show', $meetingId);
    }

    public function findings(Request $request, string $weeklyMeetingId)
    {
        $meeting = $this->accessibleMeeting($request, $weeklyMeetingId);
        $fromDate = $meeting->weeklymeeting_startdate->copy()->startOfDay();
        $toDate = $meeting->weeklymeeting_enddate->copy()->endOfDay();
        $findingBase = TrFinding::query()
            ->where('cpny_id', $meeting->cpny_id)
            ->whereBetween('finding_date', [$fromDate, $toDate]);
        $openFindings = (clone $findingBase)->whereIn('status', ['O', 'P'])
            ->orderByDesc('finding_date')->get();
        $closeFindings = (clone $findingBase)->where('status', 'C')
            ->orderByDesc('finding_date')->get();
        $meetingFindingIds = TrWeeklyMeetingFinding::query()
            ->where('weeklymeeting_id', $weeklyMeetingId)
            ->where(fn (Builder $query) => $query->whereNull('status')->orWhere('status', '<>', 'X'))
            ->pluck('finding_id');
        $openFindings = $openFindings->whereIn('finding_id', $meetingFindingIds)->values();
        $closeFindings = $closeFindings->whereIn('finding_id', $meetingFindingIds)->values();
        $findingActivities = TrFindingActivity::query()
            ->whereIn('finding_id', $openFindings->pluck('finding_id')->merge($closeFindings->pluck('finding_id')))
            ->where('status_activity', 'COMMENT')
            ->where(fn (Builder $query) => $query->whereNull('status')->orWhere('status', '<>', 'X'))
            ->get(['finding_id', 'created_by'])
            ->groupBy('finding_id');
        $addCommentInformation = function (TrFinding $finding) use ($findingActivities) {
            $activities = $findingActivities->get($finding->finding_id, collect());
            $finding->comments_count = $activities->count();
            $finding->comment_pics = $activities->pluck('created_by')->filter()->unique()->values()->implode(', ');

            return $finding;
        };
        $openFindings->transform($addCommentInformation);
        $closeFindings->transform($addCommentInformation);
        $masterDepartments = MsDepartmentOpr::query()
            ->where('cpny_id', $meeting->cpny_id)
            ->where(fn (Builder $query) => $query->whereNull('status')->orWhere('status', 'A'))
            ->orderBy('department_name')
            ->get(['department_opr_id', 'department_name']);
        $buildDepartmentCards = function ($findings) use ($masterDepartments) {
            $counts = $findings->countBy('department_id');

            return $masterDepartments
                ->map(fn ($department) => [
                    'id' => $department->department_opr_id,
                    'name' => $department->department_name ?: $department->department_opr_id,
                    'count' => (int) ($counts->get($department->department_opr_id) ?? 0),
                ])
                ->values();
        };
        $openDepartmentCards = $buildDepartmentCards($openFindings);
        $closeDepartmentCards = $buildDepartmentCards($closeFindings);
        $participants = TrWeeklyMeetingParticipant::query()
            ->where('weeklymeeting_id', $weeklyMeetingId)
            ->where(fn (Builder $query) => $query->whereNull('status')->orWhere('status', '<>', 'X'))
            ->orderBy('order_participant')
            ->get(['order_participant', 'user_participant', 'name_participant']);
        $minutes = TrWeeklyMeetingMom::query()
            ->where('weeklymeeting_id', $weeklyMeetingId)
            ->where('cpny_id', $meeting->cpny_id)
            ->where(fn (Builder $query) => $query->whereNull('status')->orWhere('status', '<>', 'X'))
            ->orderBy('order_mom')
            ->orderBy('id')
            ->get();
        $momContent = $minutes->pluck('mom_descr')->filter()->implode('<hr>');

        return view('pages.weekly-meeting.findingweekly', compact(
            'meeting',
            'openFindings',
            'closeFindings',
            'openDepartmentCards',
            'closeDepartmentCards',
            'participants',
            'minutes',
            'momContent',
            'fromDate',
            'toDate'
        ));
    }

    public function storeMom(Request $request, string $weeklyMeetingId)
    {
        $meeting = $this->accessibleMeeting($request, $weeklyMeetingId);
        $validated = $request->validate([
            'mom_descr' => ['required', 'string', 'max:15000000'],
        ]);
        $minutes = TrWeeklyMeetingMom::query()
            ->where('weeklymeeting_id', $weeklyMeetingId)
            ->where('cpny_id', $meeting->cpny_id)
            ->where(fn (Builder $query) => $query->whereNull('status')->orWhere('status', '<>', 'X'))
            ->orderBy('order_mom')
            ->orderBy('id')
            ->get();
        $minute = $minutes->first();

        if ($minute) {
            $minute->update([
                'mom_descr' => $validated['mom_descr'],
                'updated_by' => $request->user()->username,
            ]);
            TrWeeklyMeetingMom::query()
                ->whereIn('id', $minutes->skip(1)->pluck('id'))
                ->update([
                    'status' => 'X',
                    'deleted_by' => $request->user()->username,
                    'deleted_at' => now(),
                    'updated_by' => $request->user()->username,
                    'updated_at' => now(),
                ]);
        } else {
            TrWeeklyMeetingMom::query()->create([
                'weeklymeeting_id' => $weeklyMeetingId,
                'cpny_id' => $meeting->cpny_id,
                'order_mom' => 1,
                'mom_descr' => $validated['mom_descr'],
                'status' => 'A',
                'created_by' => $request->user()->username,
                'updated_by' => $request->user()->username,
            ]);
        }

        return redirect()->route('weekly-meeting.show', $weeklyMeetingId)
            ->with('success', 'Minute of Meeting saved.');
    }

    public function mom(Request $request, string $weeklyMeetingId)
    {
        $meeting = $this->accessibleMeeting($request, $weeklyMeetingId);
        $minutes = TrWeeklyMeetingMom::query()
            ->where('weeklymeeting_id', $weeklyMeetingId)
            ->where('cpny_id', $meeting->cpny_id)
            ->where(fn (Builder $query) => $query->whereNull('status')->orWhere('status', '<>', 'X'))
            ->orderBy('order_mom')
            ->orderBy('id')
            ->get(['mom_descr']);

        return response()->json([
            'weeklymeeting_id' => $meeting->weeklymeeting_id,
            'topic' => $meeting->weeklymeeting_topic,
            'date' => $meeting->weeklymeeting_date?->format('d M Y'),
            'content' => $minutes->pluck('mom_descr')->filter()->implode('<hr>'),
        ]);
    }

    public function show(Request $request, string $weeklyMeetingId)
    {
        if (!$request->boolean('export') && $request->query('view') !== 'mom') {
            return $this->findings($request, $weeklyMeetingId);
        }

        $meeting = $this->accessibleMeeting($request, $weeklyMeetingId)
            ->load(['participants', 'findings.finding']);
        $momContent = TrWeeklyMeetingMom::query()
            ->where('weeklymeeting_id', $weeklyMeetingId)
            ->where('cpny_id', $meeting->cpny_id)
            ->where(fn (Builder $query) => $query->whereNull('status')->orWhere('status', '<>', 'X'))
            ->orderBy('order_mom')
            ->orderBy('id')
            ->pluck('mom_descr')
            ->filter()
            ->implode('<hr>');

        return view('pages.weekly-meeting.showweekly', compact('meeting', 'momContent'));
    }

    private function accessibleMeeting(Request $request, string $weeklyMeetingId): TrWeeklyMeeting
    {
        return TrWeeklyMeeting::query()
            ->where('weeklymeeting_id', $weeklyMeetingId)
            ->whereIn('cpny_id', $this->companyIds($request->user()->username))
            ->firstOrFail();
    }

    private function companyIds(string $username)
    {
        return Usercpny::query()
            ->where('username', $username)
            ->where('status', 'A')
            ->pluck('cpny_id')
            ->unique()
            ->values();
    }

    private function companyUsers(string $companyId)
    {
        $usernames = Usercpny::query()
            ->where('cpny_id', $companyId)
            ->where('status', 'A')
            ->pluck('username');

        return User::query()
            ->whereIn('username', $usernames)
            ->where('status', 'A')
            ->orderBy('name')
            ->get(['username', 'name']);
    }
}
