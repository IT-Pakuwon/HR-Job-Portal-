<?php

namespace App\Http\Controllers;

use App\Models\SysCalendar;
use App\Models\TodoItem;
use App\Services\ZoomApi;
use Carbon\Carbon;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;
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
    /** Active meetings the user organizes or is listed on (by login or notification email). */
    private function myMeetingsQuery()
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

        return DB::connection('pgsql5')->table('tr_meeting as m')
            ->where('m.status', 'P')
            ->where(fn ($q) => $q->whereIn('m.docid', $docIds)->orWhere('m.user_peminta', $user->username));
    }

    private function meetings(): array
    {
        $rows = $this->myMeetingsQuery()
            ->leftJoin('ms_meeting_room as r', DB::raw('r.room_id::text'), '=', 'm.room_id')
            ->whereBetween('m.meeting_date', [now()->subDays(30)->toDateString(), now()->addDays(180)->toDateString()])
            ->orderBy('m.start_meeting_time')
            ->get(['m.docid', 'm.meeting_date', 'm.meeting_title', 'm.start_meeting_time', 'm.end_meeting_time',
                'm.zoom_id', 'm.msteams_join_url', 'r.room_name']);

        return $rows->map(fn ($m) => [
            'join_url' => preg_match('~^https?://~i', (string) $m->msteams_join_url) ? trim($m->msteams_join_url) : null,
            'can_record' => (bool) $m->zoom_id && Carbon::parse($m->end_meeting_time)->isPast(),
            'docid'    => $m->docid,
            'title'    => $m->meeting_title,
            'date'     => substr((string) $m->meeting_date, 0, 10),
            'start'    => substr((string) $m->start_meeting_time, 11, 5),
            'end'      => substr((string) $m->end_meeting_time, 11, 5),
            'location' => trim((string) $m->room_name) ?: null,
            'online'   => ($m->zoom_id || str_contains((string) $m->msteams_join_url, 'zoom.us')) ? 'Zoom' : ($m->msteams_join_url ? 'Teams' : null),
        ])->values()->all();
    }

    /**
     * Rows of the "Zoom" dashboard panel: Zoom meetings the user organizes or was invited to by email,
     * the 90 days behind through the 30 days ahead, newest first. Recordings are fetched per meeting on click.
     */
    public function zoomJson()
    {
        $rows = $this->myMeetingsQuery()
            ->leftJoin('ms_meeting_room as r', DB::raw('r.room_id::text'), '=', 'm.room_id')
            ->where(fn ($q) => $q->whereNotNull('m.zoom_id')->orWhere('m.msteams_join_url', 'ilike', '%zoom.us%'))
            ->whereBetween('m.meeting_date', [now()->subDays(90)->toDateString(), now()->addDays(30)->toDateString()])
            ->orderByDesc('m.start_meeting_time')
            ->get(['m.docid', 'm.meeting_date', 'm.meeting_title', 'm.start_meeting_time', 'm.end_meeting_time',
                'm.user_peminta', 'm.zoom_id', 'm.msteams_join_url', 'r.room_name']);

        return response()->json(['data' => $rows->map(function ($m) {
            $ended = Carbon::parse($m->end_meeting_time)->isPast();

            return [
                'docid'     => $m->docid,
                'title'     => $m->meeting_title,
                'date'      => substr((string) $m->meeting_date, 0, 10),
                'start'     => substr((string) $m->start_meeting_time, 11, 5),
                'end'       => substr((string) $m->end_meeting_time, 11, 5),
                'location'  => trim((string) $m->room_name) ?: null,
                'organizer' => $m->user_peminta,
                'join_url'  => preg_match('~^https?://~i', (string) $m->msteams_join_url) ? trim($m->msteams_join_url) : null,
                'ended'     => $ended,
                'can_record' => (bool) $m->zoom_id && $ended,
            ];
        })->values()]);
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

    /** Zoom cloud recording of a meeting the user is part of; fetched live, nothing is stored. */
    public function recording(ZoomApi $zoom, string $docid)
    {
        $m = $this->myMeetingsQuery()->where('m.docid', $docid)->first(['m.docid', 'm.zoom_id', 'm.zoom_account']);
        if (!$m || !$m->zoom_id) {
            return response()->json(['message' => 'Meeting not found.'], 404);
        }

        try {
            $r = $zoom->getRecordings($m->zoom_id, $m->zoom_account ?: 'business');
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                return response()->json(['files' => [], 'message' => 'No recording yet. Zoom can take a while after the meeting ends, and the host must have cloud recording on.']);
            }
            Log::warning('zoom recording fetch failed', ['docid' => $docid, 'status' => $e->getResponse()->getStatusCode(), 'body' => (string) $e->getResponse()->getBody()]);

            return response()->json(['message' => 'Could not read the recording from Zoom.'], 502);
        } catch (\Throwable $e) {
            Log::warning('zoom recording fetch failed', ['docid' => $docid, 'message' => $e->getMessage()]);

            return response()->json(['message' => 'Could not read the recording from Zoom.'], 502);
        }

        $labels = ['shared_screen_with_speaker_view' => 'Video', 'shared_screen_with_gallery_view' => 'Video (gallery)', 'active_speaker' => 'Video (speaker)',
            'gallery_view' => 'Video (gallery)', 'shared_screen' => 'Shared screen', 'audio_only' => 'Audio', 'audio_transcript' => 'Transcript', 'chat_file' => 'Chat', 'timeline' => 'Timeline'];

        $files = collect($r['recording_files'] ?? [])
            ->filter(fn ($f) => ($f['status'] ?? 'completed') === 'completed' && !empty($f['play_url']) && ($f['file_type'] ?? '') !== 'TIMELINE')
            ->map(fn ($f) => [
                'label' => $labels[$f['recording_type'] ?? ''] ?? ucfirst(str_replace('_', ' ', (string) ($f['recording_type'] ?? 'Recording'))),
                'type'  => strtoupper((string) ($f['file_type'] ?? '')),
                'url'   => $f['play_url'],
            ])->values();

        return response()->json([
            'files'    => $files,
            'share'    => $r['share_url'] ?? null,
            'passcode' => $r['password'] ?? null,
            'message'  => $files->isEmpty() ? 'No recording yet. Zoom can take a while after the meeting ends, and the host must have cloud recording on.' : null,
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
