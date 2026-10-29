<?php

namespace App\Http\Controllers;

use App\Models\SysCalendar;
use App\Models\TodoItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TodoController extends Controller
{
    private function mine()
    {
        return TodoItem::where('username', Auth::user()->username);
    }

    private function row(TodoItem $t): array
    {
        return [
            'id'       => $t->id,
            'title'    => $t->title,
            'due_date' => $t->due_date ? substr((string) $t->due_date, 0, 10) : null,
            'is_done'  => (bool) $t->is_done,
        ];
    }

    /**
     * Meetings the user is part of: booked by them, or listed as a participant by email
     * (login email or notification email). Cancelled meetings (status X) are skipped.
     */
    private function meetings(): array
    {
        $user = Auth::user();
        $emails = collect([$user->email ?? null, $user->notification_email ?? null])
            ->filter()->map(fn ($e) => strtolower(trim($e)))->unique()->values()->all();

        $docIds = $emails
            ? DB::connection('pgsql5')->table('tr_meeting_participant')
                ->where('status', 'A')
                ->whereIn(DB::raw('lower(trim(email_participant))'), $emails)
                ->pluck('docid')->all()
            : [];

        $rows = DB::connection('pgsql5')->table('tr_meeting as m')
            ->leftJoin('ms_meeting_room as r', DB::raw('r.room_id::text'), '=', 'm.room_id')
            ->where('m.status', 'P')
            ->whereBetween('m.meeting_date', [now()->subDays(30)->toDateString(), now()->addDays(180)->toDateString()])
            ->where(fn ($q) => $q->whereIn('m.docid', $docIds)->orWhere('m.user_peminta', $user->username))
            ->orderBy('m.start_meeting_time')
            ->get(['m.docid', 'm.meeting_date', 'm.meeting_title', 'm.start_meeting_time', 'm.end_meeting_time',
                'm.zoom_id', 'm.msteams_join_url', 'r.room_name']);

        return $rows->map(fn ($m) => [
            'join_url' => preg_match('~^https?://~i', (string) $m->msteams_join_url) ? trim($m->msteams_join_url) : null,
            'docid'    => $m->docid,
            'title'    => $m->meeting_title,
            'date'     => substr((string) $m->meeting_date, 0, 10),
            'start'    => substr((string) $m->start_meeting_time, 11, 5),
            'end'      => substr((string) $m->end_meeting_time, 11, 5),
            'location' => trim((string) $m->room_name) ?: null,
            'online'   => ($m->zoom_id || str_contains((string) $m->msteams_join_url, 'zoom.us')) ? 'Zoom' : ($m->msteams_join_url ? 'Teams' : null),
        ])->values()->all();
    }

    public function index()
    {
        // Open tasks plus anything finished in the last 60 days.
        $rows = $this->mine()
            ->where(fn ($q) => $q->where('is_done', false)->orWhere('done_at', '>=', now()->subDays(60)))
            ->orderBy('is_done')->orderByRaw('due_date asc nulls last')->orderBy('id')
            ->limit(500)->get();

        // Days off (national holidays / collective leave) from sys_calendar_exception, last year through next year.
        $holidays = SysCalendar::where('status', 'A')->whereNull('deleted_at')->where('internal_date_exception', true)
            ->whereBetween('date_calendar', [now()->subYear()->startOfYear()->toDateString(), now()->addYear()->endOfYear()->toDateString()])
            ->pluck('date_calendar_descr', 'date_calendar')
            ->mapWithKeys(fn ($d, $k) => [substr((string) $k, 0, 10) => $d]);

        return response()->json([
            'data'     => $rows->map(fn ($t) => $this->row($t))->values(),
            'holidays' => $holidays,
            'meetings' => $this->meetings(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'    => 'required|string|max:255',
            'due_date' => 'nullable|date',
        ]);

        $t = TodoItem::create([
            'username'   => Auth::user()->username,
            'title'      => trim($data['title']),
            'due_date'   => $data['due_date'] ?? null,
            'is_done'    => false,
            'created_at' => now(),
        ]);

        return response()->json(['data' => $this->row($t)]);
    }

    public function update(Request $request, $id)
    {
        $t = $this->mine()->findOrFail($id);

        $data = $request->validate([
            'title'    => 'sometimes|required|string|max:255',
            'due_date' => 'sometimes|nullable|date',
            'is_done'  => 'sometimes|boolean',
        ]);

        if (array_key_exists('title', $data)) {
            $t->title = trim($data['title']);
        }
        if (array_key_exists('due_date', $data)) {
            $t->due_date = $data['due_date'];
        }
        if (array_key_exists('is_done', $data)) {
            $t->is_done = $data['is_done'];
            $t->done_at = $data['is_done'] ? now() : null;
        }
        $t->updated_at = now();
        $t->save();

        return response()->json(['data' => $this->row($t)]);
    }

    public function destroy($id)
    {
        $this->mine()->findOrFail($id)->delete();

        return response()->json(['ok' => true]);
    }
}
