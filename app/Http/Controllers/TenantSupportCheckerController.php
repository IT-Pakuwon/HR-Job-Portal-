<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Vinkla\Hashids\Facades\Hashids;

/** Checker TS: scan the QR on a SIK / SIMKB / SIA permit PDF and log Check In / Out at Posko FCC / Security. */
class TenantSupportCheckerController extends Controller
{
    use Traits\UploadsToGcs;

    /** Security photos / videos live in msattachment under this doctype, so no permit or WPS attachment list ever shows them. */
    private const MEDIA_DOCTYPE = 'CHK';
    private const IMAGE_EXT = ['jpg', 'jpeg', 'png'];
    private const VIDEO_EXT = ['mp4', 'mov', 'webm', 'm4v', '3gp'];
    private const MEDIA_MAX_BYTES = 5 * 1024 * 1024;   // the browser shrinks photos / videos to fit; a video is at most 15 s (checked in the browser)

    /** Same bucket as the SIK / SIMKB / SIA attachments (GCS_BUCKET). */
    private function mediaBucket()
    {
        return $this->gcsClient()->bucket(config('filesystems.disks.gcs.bucket'));
    }

    /** Same layout as the other attachments, with its own CHK folder: att-wps/<siteid>/CHK/<year>/<permit number>/<file>. */
    private function mediaObject($att): string
    {
        return "att-wps/{$att->siteid}/" . self::MEDIA_DOCTYPE . "/{$att->period}/{$att->refnbr}/{$att->filename}";
    }

    private const TYPES = [
        'SIK'   => ['table' => 'viewtrsik',   'nbr' => 'siknbr',   'route' => 'tenantsupport.sik.verify'],
        'SIMKB' => ['table' => 'viewtrsimkb', 'nbr' => 'simkbnbr', 'route' => 'tenantsupport.simkb.verify'],
        'SIA'   => ['table' => 'viewtrsia',   'nbr' => 'sianbr',   'route' => 'tenantsupport.sia.verify'],
    ];

    /** Posts a Check In / Out is made at; each scan is stored with its post. */
    public const POSTS = ['POSKO' => 'Posko FCC', 'SECURITY' => 'Security'];

    /**
     * Posts the current user may work at.
     * POSKO: ACCESSCHECKERTS role, or a legacy tenancy user of type CHECKER (same username). SECURITY: ACCESSCHECKERSEC role. Admin: both.
     */
    private function allowedPosts(): array
    {
        $user = Auth::user();
        if (! $user) return [];
        if ($user->isPrimaryAdmin()) return array_keys(self::POSTS);

        $posts = [];
        if ($user->hasRole('ACCESSCHECKERTS')
            || DB::connection('mysql5')->table('user')->where('username', $user->username)->where('usertype', 'CHECKER')->where('status', 'A')->exists()) {
            $posts[] = 'POSKO';
        }
        if ($user->hasRole('ACCESSCHECKERSEC')) $posts[] = 'SECURITY';

        return $posts;
    }

    private function authorizeChecker(): void
    {
        abort_unless($this->allowedPosts(), 403);
    }

    /** The post this request acts as: the one asked for if the user may use it, else their first post. */
    private function postFor(Request $request): string
    {
        $allowed = $this->allowedPosts();
        $want = strtoupper((string) $request->input('post'));

        return in_array($want, $allowed, true) ? $want : $allowed[0];
    }

    public function index()
    {
        $this->authorizeChecker();

        return view('pages.tenancy.checker', ['posts' => collect($this->allowedPosts())->mapWithKeys(fn ($p) => [$p => self::POSTS[$p]])->all()]);
    }

    /** Completed SIK + SIMKB + SIA (all tenants), with each permit's current check state. */
    public function listing(Request $request)
    {
        $this->authorizeChecker();

        $db = DB::connection('mysql5');
        $sel = fn ($tbl, $type, $nbr, $date) => $db->table($tbl)->where('status', 'C')->selectRaw(
            "'{$type}' as type, id, tenantid, {$nbr} as number, {$date} as permit_date, sitename, companyname, storename, requestname, startdate, enddate, coalesce(extendenddate, enddate) as end_date");
        $q = $sel('viewtrsik', 'SIK', 'siknbr', 'sikdate')->unionAll($sel('viewtrsimkb', 'SIMKB', 'simkbnbr', 'simkbdate'))->unionAll($sel('viewtrsia', 'SIA', 'sianbr', 'siadate'));
        $u = $db->query()->fromSub($q, 'u');

        $years = (clone $u)->selectRaw('distinct year(permit_date) y')->orderByDesc('y')->pluck('y')->map(fn ($y) => (int) $y)->all();
        $cur = (int) now('Asia/Jakarta')->year;
        if (! in_array($cur, $years)) array_unshift($years, $cur);

        if ($request->filled('year')) $u->whereBetween('permit_date', [$request->year . '-01-01 00:00:00', $request->year . '-12-31 23:59:59']);
        if (in_array($request->type, ['SIK', 'SIMKB', 'SIA'])) $u->where('type', $request->type);
        if ($request->boolean('today')) { $d = now('Asia/Jakarta')->toDateString(); $u->where('startdate', '<=', $d)->where('end_date', '>=', $d); }
        if ($request->filled('search')) {
            $t = '%' . trim($request->search) . '%';
            $u->where(fn ($w) => $w->where('number', 'like', $t)->orWhere('storename', 'like', $t)->orWhere('companyname', 'like', $t)->orWhere('requestname', 'like', $t));
        }

        $total = (clone $u)->count();
        $rows  = $u->orderByDesc('permit_date')->orderByDesc('id')->forPage(max((int) $request->input('page', 1), 1), 10)->get();

        // Latest check action per permit on this page, at the user's current post.
        $last = [];
        $post = $this->postFor($request);
        foreach ($rows->groupBy('type') as $type => $g) {
            foreach (DB::connection('mysql5')->table('tr_ts_checkinout')->where('doctype', $type)->where('post', $post)->whereIn('ref_id', $g->pluck('id'))
                ->orderBy('checked_at')->orderBy('id')->get() as $l) $last[$type . $l->ref_id] = $l;
        }
        $today = now('Asia/Jakarta')->toDateString();
        // tenants on this page that are under warning
        $warned = DB::connection('mysql5')->table('mstenant')->whereIn('id', $rows->pluck('tenantid')->filter()->unique())->where('is_warned', 1)->pluck('id')->flip();

        return response()->json(['total' => $total, 'per_page' => 10, 'years' => $years, 'data' => $rows->map(function ($r) use ($last, $today, $warned) {
            $l = $last[$r->type . $r->id] ?? null;
            return [
                'warned' => $warned->has($r->tenantid),
                'type' => $r->type, 'eid' => Hashids::encode($r->id), 'number' => $r->number, 'site' => $r->sitename,
                'company' => $r->companyname, 'store' => $r->storename, 'requester' => $r->requestname,
                'start' => $r->startdate, 'end' => $r->end_date, 'in_period' => $today >= $r->startdate && $today <= $r->end_date,
                'state' => ! $l ? 'NONE' : ($l->action === 'OUT' ? 'OUT' : (substr($l->checked_at, 0, 10) === $today ? 'IN' : 'STALE')),
                'last_at' => $l ? substr($l->checked_at, 0, 16) : null,
            ];
        })->values()]);
    }

    /** Parse scanned QR text (signed verify URL), validate the signature and return the permit + its check log. */
    public function lookup(Request $request)
    {
        $this->authorizeChecker();
        $permit = $this->resolve($request);
        if (is_string($permit)) return response()->json(['message' => $permit], 422);

        return response()->json($this->payload($permit['type'], $permit['row'], $this->postFor($request)));
    }

    public function record(Request $request)
    {
        $this->authorizeChecker();
        $request->validate(['action' => 'required|in:IN,OUT', 'notes' => 'nullable|string|max:255']);

        $permit = $this->resolve($request);
        if (is_string($permit)) return response()->json(['message' => $permit], 422);

        $type = $permit['type'];
        $row  = $permit['row'];
        $nbr  = $row->{self::TYPES[$type]['nbr']};
        if ($row->status !== 'C') return response()->json(['message' => "Permit {$nbr} is not approved (status {$row->status})."], 422);

        // In / Out rules are per post: Security checking a visitor in does not check them in at Posko, and vice versa.
        $post  = $this->postFor($request);
        $last  = $this->log($type, $row->id)->where('post', $post)->last();
        $today = now('Asia/Jakarta')->toDateString();
        if ($request->action === 'IN') {
            if ($today < $row->startdate || $today > max($row->enddate, $row->extendenddate ?? '')) {
                return response()->json(['message' => 'Today is outside the permit period.'], 422);
            }
            // A forgotten Check Out from an earlier day does not block today's Check In (one visit per day).
            if ($last && $last->action === 'IN' && substr($last->checked_at, 0, 10) === $today) {
                return response()->json(['message' => 'Already checked in today. Check Out first.'], 422);
            }
        }
        if ($request->action === 'OUT' && (! $last || $last->action === 'OUT')) {
            return response()->json(['message' => 'Not checked in yet.'], 422);
        }

        DB::connection('mysql5')->table('tr_ts_checkinout')->insert([
            'doctype' => $type, 'ref_id' => $row->id, 'ref_nbr' => $nbr, 'action' => $request->action, 'post' => $post,
            'checked_by' => Auth::user()->username, 'checked_at' => now('Asia/Jakarta')->format('Y-m-d H:i:s'),
            'notes' => $request->input('notes') ?: null,
        ]);

        return response()->json($this->payload($type, $row, $post));
    }

    /** PIC name / number kept on the tenant master (Tenancy Support > Master Data). */
    private function tenantPic($tenantId): array
    {
        $t = $tenantId ? DB::connection('mysql5')->table('mstenant')->where('id', $tenantId)->first(['picname', 'picphone']) : null;

        return ['name' => trim((string) ($t->picname ?? '')) ?: null, 'phone' => trim((string) ($t->picphone ?? '')) ?: null];
    }

    /** The tenant's active warning (set under Tenancy Support > Master Data > Warning), or null. Shown to the checker; it does not block Check In / Out. */
    private function tenantWarning($tenantId): ?array
    {
        if (! $tenantId) return null;
        try {
            $db = DB::connection('mysql5');
            $t  = $db->table('mstenant')->where('id', $tenantId)->where('is_warned', 1)->first(['storename', 'warning_reason']);
            if (! $t) return null;
            $w = $db->table('trtenantwarning')->where('tenantid', $tenantId)->where('status', 'A')->orderByDesc('id')->first();

            return [
                'reason' => $t->warning_reason ?: ($w->reason ?? null) ?: 'This tenant is under warning.',
                'by'     => $w->issued_user ?? null,
                'at'     => isset($w->issued_datetime) ? substr($w->issued_datetime, 0, 16) : null,
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Security evidence for one permit: photos / videos taken at the gate. Only the Security post adds them; every checker sees them. */
    private function media(string $nbr): array
    {
        try {
            return DB::connection('mysql5')->table('msattachment')->where('doctype', self::MEDIA_DOCTYPE)->where('refnbr', $nbr)->where('status', 'A')
                ->orderByDesc('created_datetime')->orderByDesc('id')->get()->map(fn ($a) => [
                    'id'   => Hashids::encode($a->id),
                    'can_delete' => $this->canDeleteMedia($a),
                    'url'  => route('tenantsupport.checker.media', Hashids::encode($a->id), false),
                    'name' => $a->name . '.' . $a->fileextension,
                    'kind' => in_array($a->fileextension, self::VIDEO_EXT, true) ? 'video' : 'image',
                    'by'   => $a->created_user, 'at' => substr($a->created_datetime, 0, 16),
                ])->values()->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** Add a photo / video to a permit (Security post only). */
    public function uploadMedia(Request $request)
    {
        $this->authorizeChecker();
        if ($this->postFor($request) !== 'SECURITY') {
            return response()->json(['message' => 'Only the Security post can add photos or videos.'], 403);
        }
        $request->validate(['file' => 'required|file']);

        $permit = $this->resolve($request);
        if (is_string($permit)) return response()->json(['message' => $permit], 422);
        $row = $permit['row'];
        if ($row->status !== 'C') return response()->json(['message' => 'Permit is not approved.'], 422);

        $file = $request->file('file');
        $ext  = strtolower($file->getClientOriginalExtension());
        $isImage = in_array($ext, self::IMAGE_EXT, true);
        if (! $isImage && ! in_array($ext, self::VIDEO_EXT, true)) {
            return response()->json(['message' => 'Only image (JPG, JPEG, PNG) or video (MP4, MOV, WEBM) files are allowed.'], 422);
        }
        if ($file->getSize() > self::MEDIA_MAX_BYTES) {
            return response()->json(['message' => 'File is too large (max 5 MB).'], 422);
        }

        $type   = $permit['type'];
        $nbr    = $row->{self::TYPES[$type]['nbr']};
        $period = now('Asia/Jakarta')->year;
        $stored = Str::random(32) . '.' . $ext;
        try {
            $this->mediaBucket()->upload(fopen($file->getRealPath(), 'r'), ['name' => $this->mediaObject((object) ['siteid' => $row->siteid, 'period' => $period, 'refnbr' => $nbr, 'filename' => $stored])]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Could not save the file to the storage bucket. Please try again or contact IT.'], 500);
        }

        $user = Auth::user()->username;
        $now  = now('Asia/Jakarta')->format('Y-m-d H:i:s');
        DB::connection('mysql5')->table('msattachment')->insert([
            'refnbr' => $nbr, 'doctype' => self::MEDIA_DOCTYPE, 'siteid' => $row->siteid, 'period' => $period,
            'name' => Str::limit(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), 200, ''), 'filename' => $stored, 'fileextension' => $ext, 'status' => 'A',
            'created_user' => $user, 'created_datetime' => $now, 'lastupdate_user' => $user, 'lastupdate_datetime' => $now,
        ]);

        return response()->json($this->payload($type, $row, 'SECURITY'));
    }

    /** Short-lived signed link to one Security photo / video. */
    public function mediaFile(string $aeid)
    {
        $this->authorizeChecker();
        $id  = Hashids::decode($aeid)[0] ?? null;
        $att = $id ? DB::connection('mysql5')->table('msattachment')->where('id', $id)->where('doctype', self::MEDIA_DOCTYPE)->where('status', 'A')->first() : null;
        abort_unless($att, 404);

        try {
            $url = $this->mediaBucket()->object($this->mediaObject($att))->signedUrl(new \DateTimeImmutable('+15 minutes'), ['version' => 'v4']);
        } catch (\Throwable $e) {
            $url = null;
        }
        abort_unless($url, 404);

        return redirect()->away($url);
    }

    /** The uploader may delete their own photo / video; admin may delete any. */
    private function canDeleteMedia($att): bool
    {
        $user = Auth::user();

        return $user && ($att->created_user === $user->username || $user->isPrimaryAdmin());
    }

    /** Remove a Security photo / video: the row is switched off and the file is deleted from the bucket. */
    public function deleteMedia(Request $request, string $aeid)
    {
        $this->authorizeChecker();
        $id  = Hashids::decode($aeid)[0] ?? null;
        $db  = DB::connection('mysql5');
        $att = $id ? $db->table('msattachment')->where('id', $id)->where('doctype', self::MEDIA_DOCTYPE)->where('status', 'A')->first() : null;
        if (! $att) return response()->json(['message' => 'File not found.'], 404);
        if (! $this->canDeleteMedia($att)) return response()->json(['message' => 'Only the person who uploaded this can delete it.'], 403);

        $db->table('msattachment')->where('id', $id)->update([
            'status' => 'X', 'lastupdate_user' => Auth::user()->username, 'lastupdate_datetime' => now('Asia/Jakarta')->format('Y-m-d H:i:s'),
        ]);
        try {
            $this->mediaBucket()->object($this->mediaObject($att))->delete();
        } catch (\Throwable $e) {
            // already gone / bucket unreachable: the row is off, nothing is shown
        }

        $permit = $this->resolve($request);
        if (is_string($permit)) return response()->json(['message' => $permit], 422);

        return response()->json($this->payload($permit['type'], $permit['row'], $this->postFor($request)));
    }

    /** Returns ['type','row'] or an error message. */
    private function resolve(Request $request)
    {
        // Picked from the completed-permit list (checker is already authorised): no QR needed.
        if ($request->filled('type') && $request->filled('eid')) {
            $type = strtoupper((string) $request->type);
            $id   = Hashids::decode((string) $request->eid)[0] ?? null;
            $row  = ($id && isset(self::TYPES[$type])) ? DB::connection('mysql5')->table(self::TYPES[$type]['table'])->where('id', $id)->first() : null;

            return $row ? ['type' => $type, 'row' => $row] : 'Permit not found.';
        }

        $code = trim((string) $request->input('code'));

        // QR from the WPS tenant app: its own detail link, hashed with the WPS salt (no signature; checker is already authorised)
        if (preg_match('#/show(sik|simkb|sia)/([A-Za-z0-9]+)#i', $code, $m)) {
            $salt = config('services.wps.hashids_salt');
            if (! $salt) return 'WPS QR links are not configured (WPS_HASHIDS_SALT).';
            $type = strtoupper($m[1]);
            $id   = (new \Hashids\Hashids($salt, (int) config('services.wps.hashids_length')))->decode($m[2])[0] ?? null;
            $row  = $id ? DB::connection('mysql5')->table(self::TYPES[$type]['table'])->where('id', $id)->first() : null;

            return $row ? ['type' => $type, 'row' => $row] : 'Permit not found.';
        }

        if ($code === '' || ! preg_match('#/verify/tenant-(sik|simkb|sia)/([A-Za-z0-9]+)#i', $code, $m)) {
            return 'Not a permit QR code.';
        }
        if (! URL::hasValidSignature(Request::create($code))) return 'Invalid or tampered QR code.';

        $type = strtoupper($m[1]);
        $id   = Hashids::decode($m[2])[0] ?? null;
        $row  = $id ? DB::connection('mysql5')->table(self::TYPES[$type]['table'])->where('id', $id)->first() : null;

        return $row ? ['type' => $type, 'row' => $row] : 'Permit not found.';
    }

    private function log(string $type, $refId)
    {
        return DB::connection('mysql5')->table('tr_ts_checkinout')->where('doctype', $type)->where('ref_id', $refId)
            ->orderBy('checked_at')->orderBy('id')->get();
    }

    private function payload(string $type, $r, string $post): array
    {
        $nbr = $r->{self::TYPES[$type]['nbr']};
        $log = $this->log($type, $r->id);   // every post, so the log shows who scanned where
        $last = $log->where('post', $post)->last();   // but the state / rules follow the user's own post
        $today = now('Asia/Jakarta')->toDateString();
        $start = $r->startdate;
        $end   = max($r->enddate, $r->extendenddate ?? '');

        return [
            'type' => $type, 'number' => $nbr, 'status' => $r->status, 'approved' => $r->status === 'C',
            'in_period' => $today >= $start && $today <= $end,
            'site' => $r->sitename, 'company' => $r->companyname, 'store' => $r->storename,
            'requester' => $r->requestname, 'start' => $start, 'end' => $end, 'workhour' => $r->workhour ?? (isset($r->starttime) ? substr($r->starttime, 0, 5) . ' - ' . substr($r->endtime, 0, 5) : null),
            // IN = inside now; STALE = checked in on an earlier day and never checked out (new day may Check In again).
            'state' => ! $last ? 'NONE' : ($last->action === 'OUT' ? 'OUT' : (substr($last->checked_at, 0, 10) === $today ? 'IN' : 'STALE')),
            'post' => $post, 'post_label' => self::POSTS[$post],
            'can_upload' => $post === 'SECURITY' && $r->status === 'C', 'media' => $this->media($nbr),
            'warning' => $this->tenantWarning($r->tenantid ?? null),
            // PIC on the tenant master vs the PIC written on this request
            'pic_master'  => $this->tenantPic($r->tenantid ?? null),
            'pic_request' => ['name' => trim((string) ($r->requestpenanggungjawab ?? '')) ?: (trim((string) ($r->requestname ?? '')) ?: null), 'phone' => trim((string) ($r->requestphone ?? '')) ?: null],
            'log' => $log->map(fn ($l) => ['action' => $l->action, 'post' => $l->post, 'post_label' => self::POSTS[$l->post] ?? $l->post, 'at' => substr($l->checked_at, 0, 16), 'by' => $l->checked_by, 'notes' => $l->notes])->values(),
        ];
    }

    /** Check In / Out lines printed in the permit's Posko FCC box (Posko scans only; Security scans have their own post). */
    public static function permitLog(string $type, $refId)
    {
        try {
            return DB::connection('mysql5')->table('tr_ts_checkinout')->where('doctype', $type)->where('ref_id', $refId)->where('post', 'POSKO')
                ->orderBy('checked_at')->orderBy('id')->get();
        } catch (\Throwable $e) {
            return collect();
        }
    }
}
