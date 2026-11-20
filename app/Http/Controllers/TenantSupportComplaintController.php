<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\UploadsToGcs;
use App\Models\SysUserRole;
use App\Models\Usercpny;
use App\Support\TenantRelationScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;

/** Complaint Jobs: every complaint raised by tenant users in WPS (legacy tr_complaint), read-only, for Tenant Relation (ACCESSTR). */
class TenantSupportComplaintController extends Controller
{
    use UploadsToGcs;
    use \App\Http\Controllers\Traits\TenantAttachmentRules;

    /** Header (tr_complaint.status): C Created -> P On Progress -> D Completed, or X Cancelled. Transfer to WO keeps P; it shows as W in the activity log only. */
    private const STATUS = ['C' => 'Created', 'P' => 'On Progress', 'D' => 'Completed', 'X' => 'Cancelled'];

    /** action => [allowed current header statuses, new header status, note required, log label (activity type)]; activity status = header status */
    private const ACTIONS = [
        'process'  => [['C'], 'P', false, 'Process'],
        'complete' => [['P'], 'D', true, 'Complete'],
        'cancel'  => [['C', 'P'], 'X', true, 'Cancel'],
    ];

    private function authorizeLog(): void
    {
        $user = Auth::user();
        abort_unless($user && ($user->hasRole('ACCESSTR') || $user->isPrimaryAdmin()), 403);
    }

    private function base()
    {
        $q = DB::connection('mysql5')->table('tr_complaint as c')
            ->leftJoin('mssite as s', 's.siteid', '=', 'c.siteid')
            ->leftJoin('mstenant as t', 't.id', '=', 'c.tenantid')
            ->leftJoin('mstenantcompany as tc', 'tc.id', '=', 't.tenantcompanyid')
            ->leftJoin('msfloor as f', 'f.id', '=', 'c.floorid')
            ->leftJoin('mscategory as k', 'k.id', '=', 'c.categoryid');

        // Tenant Relation users only see sites of their own companies (admins / TRACCESS: all).
        if (($sites = TenantRelationScope::siteIds()) !== null) {
            $q->whereIn('c.siteid', $sites ?: ['']);
        }

        return $q;
    }

    public function index()
    {
        $this->authorizeLog();

        $years = $this->base()->selectRaw('distinct year(c.complaintdate) y')->orderByDesc('y')->pluck('y')->map(fn ($y) => (int) $y)->all();
        if (! in_array((int) now()->year, $years)) array_unshift($years, (int) now()->year);

        return view('pages.tenancy.complaint', [
            'years'      => $years,
            'statuses'   => self::STATUS,
            'sites'      => DB::connection('mysql5')->table('mssite')->orderBy('siteid')->get(['siteid', 'sitename']),
            'categories' => DB::connection('mysql5')->table('mscategory')->where('type', 'Complaint')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    private function filtered(Request $request)
    {
        $q = $this->base();
        if ($request->filled('year'))     $q->whereBetween('c.complaintdate', [$request->year . '-01-01 00:00:00', $request->year . '-12-31 23:59:59']);
        if ($request->filled('siteid'))   $q->where('c.siteid', $request->siteid);
        if ($request->filled('category')) $q->where('c.categoryid', (int) $request->category);
        if ($request->filled('search')) {
            $t = '%' . trim($request->search) . '%';
            $q->where(fn ($w) => $w->where('c.complaintnbr', 'like', $t)->orWhere('t.storename', 'like', $t)
                ->orWhere('c.description', 'like', $t)->orWhere('c.created_user', 'like', $t));
        }

        return $q;
    }

    private function cols(): array
    {
        return ['c.id', 'c.complaintnbr', 'c.complaintdate', 'c.siteid', 's.sitename', 't.storename', 'tc.tenantcompanyname', 't.unit',
            'f.floor', 'k.name as category', 'c.description', 'c.action', 'c.status', 'c.created_user', 'c.resolved_date'];
    }

    private function shape($r)
    {
        $r->eid = Hashids::encode($r->id);
        unset($r->id);
        $r->status_label = self::STATUS[$r->status] ?? $r->status;

        return $r;
    }

    public function data(Request $request)
    {
        $this->authorizeLog();
        $request->validate(['year' => 'nullable|digits:4']);

        $q = $this->filtered($request);
        $counts = (clone $q)->selectRaw('c.status, count(*) n')->groupBy('c.status')->pluck('n', 'status');
        $counts = ['ALL' => (int) $counts->sum()] + collect(self::STATUS)->mapWithKeys(fn ($l, $k) => [$k => (int) ($counts[$k] ?? 0)])->all();

        if ($request->filled('status') && $request->status !== 'ALL') $q->where('c.status', $request->status);

        $total   = (clone $q)->count();
        $perPage = min(max((int) $request->input('per_page', 15), 10), 100);
        $page    = max((int) $request->input('page', 1), 1);

        $sortable = ['complaintnbr' => 'c.complaintnbr', 'complaintdate' => 'c.complaintdate', 'sitename' => 's.sitename', 'storename' => 't.storename',
            'category' => 'k.name', 'description' => 'c.description', 'created_user' => 'c.created_user', 'status' => 'c.status'];
        if (isset($sortable[$request->sort])) {
            $q->orderBy($sortable[$request->sort], $request->dir === 'asc' ? 'asc' : 'desc')->orderByDesc('c.id');
        } else {
            $q->orderByDesc('c.complaintdate')->orderByDesc('c.id');
        }

        $rows = $q->forPage($page, $perPage)->get($this->cols())
            ->map(fn ($r) => $this->shape($r));

        return response()->json(['data' => $rows, 'counts' => $counts, 'total' => $total, 'page' => $page, 'per_page' => $perPage]);
    }

    public function show(string $eid)
    {
        $this->authorizeLog();
        $id = Hashids::decode($eid)[0] ?? null;
        abort_unless($id, 404);
        $row = $this->base()->where('c.id', $id)->first($this->cols());
        abort_unless($row, 404);

        $row = $this->shape($row);
        foreach ($this->payload($row, $id) as $k => $v) $row->$k = $v;

        return response()->json(['data' => $row]);
    }

    /** Process / Complete / Cancel. Complete and Cancel need a note; every step is appended to the complaint's action log. */
    public function act(Request $request, string $eid)
    {
        $this->authorizeLog();
        $in = $request->validate(['action' => 'required|in:' . implode(',', array_keys(self::ACTIONS)), 'description' => 'nullable|string|max:1000', 'activity_date' => 'nullable|date']);
        [$from, $to, $needNote, $label] = self::ACTIONS[$in['action']];
        $notes = trim($in['description'] ?? '');
        if ($needNote && $notes === '') {
            return response()->json(['message' => 'Please fill the description.'], 422);
        }

        // Only Process may carry a user-chosen date (empty = now); Complete / Cancel always take the submit time.
        $actDate = null;
        if ($in['action'] === 'process' && ! empty($in['activity_date'])) {
            $actDate = \Carbon\Carbon::parse($in['activity_date'], 'Asia/Jakarta');
            if ($actDate->gt(now('Asia/Jakarta')->addMinute())) {
                return response()->json(['message' => 'Process date cannot be in the future.'], 422);
            }
            $actDate = $actDate->format('Y-m-d H:i:s');
        }

        $id = Hashids::decode($eid)[0] ?? null;
        abort_unless($id, 404);
        abort_unless($this->base()->where('c.id', $id)->exists(), 404);

        $error = $this->transition($id, $from, $to, $label, $notes, null, $actDate);
        if ($error) return response()->json(['message' => $error], 409);

        return $this->show($eid);
    }

    // ---- Activity / Messages / Attachments (tr_complaint_activity; trmessage + msattachment with doctype CMP) ----

    private function db()
    {
        return DB::connection('mysql5');
    }

    private function nowJkt(): string
    {
        return now('Asia/Jakarta')->format('Y-m-d H:i:s');
    }

    private function findOrFail(string $eid)
    {
        $id = Hashids::decode($eid)[0] ?? null;
        abort_unless($id, 404);
        $row = $this->base()->where('c.id', $id)->first(['c.id', 'c.complaintnbr', 'c.complaintdate', 'c.siteid', 'c.created_user']);
        abort_unless($row, 404);

        return $row;
    }

    private const ACT_LABEL = ['Process' => 'Process Complaint', 'Complete' => 'Completed', 'Cancel' => 'Cancelled', 'Transfer to WO' => 'Transfer to WO', 'Upload Attachment' => 'Attachment uploaded'];

    /** One row per step in tr_complaint_activity (status = the complaint status it led to: C/P/W/D/X); newest first. */
    private function activities($c): array
    {
        $db = $this->db();
        // Status steps only (attachment uploads are not tracked here); messages are merged in below.
        $rows = $db->table('tr_complaint_activity')->where('complaintnbr', $c->complaintnbr)->where('activitytype', '!=', 'Upload Attachment')
            ->get(['activitytype', 'status', 'activity_date', 'activity_description', 'created_user', 'created_name', 'created_datetime']);

        $items = $rows->map(fn ($r) => [
            'type' => $r->activitytype, 'status' => $r->status, 'label' => self::ACT_LABEL[$r->activitytype] ?? $r->activitytype,
            'ts' => (string) ($r->activity_date ?: $r->created_datetime), 'time' => substr((string) ($r->activity_date ?: $r->created_datetime), 0, 16),
            'user' => $r->created_name ?: $r->created_user, 'note' => trim((string) $r->activity_description),
        ])->all();

        // Complaints raised by the tenant app before this log existed still show their Created step.
        if (! $rows->contains('activitytype', 'Created')) {
            $items[] = ['type' => 'Created', 'status' => 'C', 'label' => 'Created', 'ts' => (string) $c->complaintdate, 'time' => substr((string) $c->complaintdate, 0, 16), 'user' => $c->created_user, 'note' => ''];
        }

        foreach ($this->messages($c) as $m) {
            $items[] = ['type' => 'Message', 'status' => null, 'label' => 'Message', 'ts' => (string) $m->created_datetime,
                'time' => substr((string) $m->created_datetime, 0, 16), 'user' => $m->name ?: $m->created_user, 'note' => $m->message];
        }

        usort($items, fn ($x, $y) => strcmp($y['ts'], $x['ts']));

        return $items;
    }
    /** Append a step to the activity log. $status = complaint status after the step. */
    private function logActivity($db, string $nbr, ?string $siteid, string $type, ?string $status, string $activity, string $notes = '', ?string $now = null, ?string $actDate = null): void
    {
        $u = Auth::user();
        $db->table('tr_complaint_activity')->insert([
            'complaintnbr' => $nbr, 'siteid' => $siteid, 'activitytype' => $type, 'status' => $status, 'activity' => $activity, 'activity_description' => $notes, 'activity_date' => $actDate ?? $now ?? $this->nowJkt(),
            'created_user' => $u->username, 'created_name' => $u->name, 'created_datetime' => $now ?? $this->nowJkt(),
        ]);
    }

    /** Who can be @mentioned: the tenant user who raised it + ACCESSTR users of this site's company(ies). */
    private function mentionable($c): array
    {
        $me = strtolower((string) Auth::user()?->username);
        $out = [];
        $tenant = $this->db()->table('msusertenant')->where('username', $c->created_user)->value('name');
        $out[strtolower($c->created_user)] = ['username' => $c->created_user, 'name' => $tenant ?: $c->created_user, 'role' => 'Tenant'];

        $cpnies = TenantRelationScope::SITE_COMPANY[$c->siteid] ?? [];
        if ($cpnies) {
            $inCpny = Usercpny::where('status', 'A')->whereIn(DB::raw('upper(trim(cpny_id))'), $cpnies)->pluck('username')->map(fn ($u) => strtolower($u))->unique()->all();
            $tr = SysUserRole::where('role_id', 'ACCESSTR')->where('status', 'A')->pluck('username')->map(fn ($u) => strtolower($u))->unique()->all();
            $names = \App\Models\User::whereIn(DB::raw('lower(username)'), array_values(array_intersect($tr, $inCpny)))->pluck('name', 'username');
            foreach ($names as $u => $n) $out[strtolower($u)] ??= ['username' => $u, 'name' => $n, 'role' => 'Tenant Relation'];
        }
        unset($out[$me]);

        return array_values($out);
    }

    private function attachments($c)
    {
        return $this->db()->table('msattachment')->where('doctype', 'CMP')->where('refnbr', $c->complaintnbr)->where('status', 'A')
            ->orderBy('id')->get(['id', 'name', 'filename', 'fileextension', 'created_user', 'created_datetime'])
            ->map(function ($a) {
                $a->url = route('tenantsupport.complaint.attachment', Hashids::encode($a->id));
                unset($a->id);

                return $a;
            });
    }

    private function messages($c)
    {
        return $this->db()->table('trmessage')->where('doctype', 'CMP')->where('refnbr', $c->complaintnbr)->where('status', 'A')
            ->orderBy('id')->get(['name', 'messagetype', 'message', 'created_user', 'created_datetime']);
    }

    private function payload($row, int $id): array
    {
        $c = $this->base()->where('c.id', $id)->first(['c.complaintnbr', 'c.complaintdate', 'c.siteid', 'c.created_user']);

        return ['activities' => $this->activities($c), 'messages' => $this->messages($c), 'attachments' => $this->attachments($c), 'mentionable' => $this->mentionable($c)];
    }

    public function message(Request $request, string $eid)
    {
        $this->authorizeLog();
        $in = $request->validate(['message' => 'required|string|max:1000']);
        $c = $this->findOrFail($eid);
        $u = Auth::user();
        $now = $this->nowJkt();

        $this->db()->table('trmessage')->insert([
            'refnbr' => $c->complaintnbr, 'doctype' => 'CMP', 'siteid' => $c->siteid, 'name' => $u->name, 'messagetype' => 'Message',
            'message' => trim($in['message']), 'status' => 'A',
            'created_user' => $u->username, 'created_datetime' => $now, 'lastupdate_user' => $u->username, 'lastupdate_datetime' => $now,
        ]);

        return response()->json(['messages' => $this->messages($c), 'activities' => $this->activities($c)]);
    }

    /** att-wps/<site>/CMP/<year>/<complaint no>/<random>.<ext> (max 5 MB, image/video) */
    public function upload(Request $request, string $eid)
    {
        $this->authorizeLog();
        $request->validate(['file' => 'required|file']);
        $c = $this->findOrFail($eid);
        $user = Auth::user();
        $db = $this->db();

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        // Images (JPG/JPEG/PNG) or video only, max 5 MB per file.
        if ($err = $this->tenantUploadError($file)) {
            return response()->json(['message' => $err], 422);
        }

        $period = \Carbon\Carbon::parse($c->complaintdate)->year;
        $stored = \Illuminate\Support\Str::random(32) . '.' . $ext;
        $this->gcsClient()->bucket(config('filesystems.disks.gcs.bucket'))
            ->upload(fopen($file->getRealPath(), 'r'), ['name' => $this->tenantObjectPath($c->siteid, 'CMP', $period, $c->complaintnbr, $stored)]);

        $now = $this->nowJkt();
        $audit = ['created_user' => $user->username, 'created_datetime' => $now, 'lastupdate_user' => $user->username, 'lastupdate_datetime' => $now];
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $db->table('msattachment')->insert(['refnbr' => $c->complaintnbr, 'doctype' => 'CMP', 'siteid' => $c->siteid, 'period' => $period,
            'name' => $name, 'filename' => $stored, 'fileextension' => $ext, 'status' => 'A'] + $audit);

        return response()->json(['attachments' => $this->attachments($c)]);
    }

    public function attachment(string $aeid)
    {
        $this->authorizeLog();
        $id = Hashids::decode($aeid)[0] ?? null;
        abort_unless($id, 404);
        $att = $this->db()->table('msattachment')->where('id', $id)->where('doctype', 'CMP')->where('status', 'A')->first();
        abort_unless($att, 404);
        abort_unless($this->base()->where('c.complaintnbr', $att->refnbr)->exists(), 404);

        $object = $this->tenantAttachmentObject($att, 'CMP');
        $url = $this->gcsSignedUrl($object, 15);
        abort_unless($url, 404);

        return redirect()->away($url);
    }

    /** For the WO form: the complaint (by hashed id) if this user may transfer it, else null. */
    public static function forWo(string $eid): ?array
    {
        $user = Auth::user();
        $id = $eid !== '' ? (Hashids::decode($eid)[0] ?? null) : null;
        if (! $id || ! $user || ! ($user->hasRole('ACCESSTR') || $user->isPrimaryAdmin())) return null;

        $c = (new self)->base()->where('c.id', $id)->whereIn('c.status', ['C', 'P'])
            ->whereNotExists(fn ($q) => $q->from('tr_complaint_activity as a')->whereColumn('a.complaintnbr', 'c.complaintnbr')->where('a.status', 'W'))
            ->first(['c.complaintnbr', 'c.description', 'k.name as category', 't.storename', 's.sitename']);
        if (! $c) return null;

        $text = "Complaint {$c->complaintnbr}" . ($c->category ? " ({$c->category})" : '') . "\n"
            . trim(implode(' - ', array_filter([$c->sitename, $c->storename]))) . "\n" . $c->description;

        return ['eid' => $eid, 'nbr' => $c->complaintnbr, 'text' => trim($text)];
    }

    /** Called by the WO form once a WO was saved from a complaint: activity W + WO number in the log; header becomes/stays P. */
    public static function markTransferred(string $eid, string $woid): void
    {
        $id = Hashids::decode($eid)[0] ?? null;
        if ($id) (new self)->transition($id, ['C', 'P'], 'P', 'Transfer to WO', "WO $woid", 'W');
    }

    /** $to = new header status; $actStatus = status written to the activity log (defaults to $to). */
    private function transition(int $id, array $from, string $to, string $label, string $notes, ?string $actStatus = null, ?string $actDate = null): ?string
    {
        $db   = DB::connection('mysql5');
        $user = Auth::user()->username;
        $now  = now('Asia/Jakarta')->format('Y-m-d H:i:s');

        return $db->transaction(function () use ($db, $id, $from, $to, $label, $notes, $user, $now, $actStatus, $actDate) {
            $cur = $db->table('tr_complaint')->where('id', $id)->lockForUpdate()->first();
            if (! $cur || ! in_array($cur->status, $from, true)) return 'This complaint is no longer in a status that allows this action.';

            $line = "[$now $user] $label" . ($notes !== '' ? ": $notes" : '');
            $update = [
                'status' => $to, 'action' => trim(($cur->action ? $cur->action . "\n" : '') . $line),
                'lastupdate_user' => $user, 'lastupdate_datetime' => $now,
            ];
            if ($to === 'D') $update['resolved_date'] = $actDate ?? $now;   // Complete -> Resolved Date
            $db->table('tr_complaint')->where('id', $id)->update($update);
            $this->logActivity($db, $cur->complaintnbr, $cur->siteid, $label, $actStatus ?? $to, "Complaint {$cur->complaintnbr}: $label", $notes, $now, $actDate);

            return null;
        });
    }
}
