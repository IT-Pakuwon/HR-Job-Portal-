<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Traits\UploadsToGcs;
use Vinkla\Hashids\Facades\Hashids;

class TenantSupportController extends Controller
{
    use UploadsToGcs;

    /**
     * Master Data for Tenant Relation (ACCESSTR): Tenant list + User Tenant (usertype TENANT only).
     * Reuses the Tenancy Master page without the Location / Floor tabs.
     */
    public function master()
    {
        $user = Auth::user();
        abort_unless($user && ($user->hasRole('ACCESSTR') || $user->isPrimaryAdmin()), 403);

        return view('pages.tenancy.master', ['tenantRelation' => true]);
    }

    /** SIK status letters in trsik.status */
    private const SIK_STATUS = ['P' => 'On Progress', 'C' => 'Completed', 'H' => 'Hold / Revise', 'R' => 'Rejected', 'X' => 'Cancelled'];

    private function authorizeSik(): void
    {
        $user = Auth::user();
        abort_unless($user && ($user->hasRole('ACCESSTS') || $user->isPrimaryAdmin()), 403);
    }

    /** SIK created by tenant users (msusertenant). */
    private function sikQuery()
    {
        return DB::connection('mysql5')->table('viewtrsik as s')
            ->whereExists(fn ($q) => $q->select(DB::raw(1))->from('msusertenant as u')
                ->whereColumn('u.username', 's.created_user'));
    }

    /**
     * /tenant-support/sik/{eid}: every SIK has its own link (same hashed-id pattern as the other modules).
     * A browser visit opens the list with that SIK's popup already open; the popup's own fetch asks for JSON.
     */
    public function sikPage(Request $request, string $eid)
    {
        if ($request->expectsJson()) {
            return $this->sikShow($eid);
        }

        $this->authorizeSik();
        $this->sikFindOrFail($eid);   // 404 for an unknown / non-tenant SIK

        return $this->sik($eid);
    }

    public function sik(?string $openEid = null)
    {
        $this->authorizeSik();

        $years = $this->sikQuery()->selectRaw('distinct year(s.sikdate) y')->orderByDesc('y')->pluck('y')->all();
        if (! in_array((int) now()->year, $years)) array_unshift($years, (int) now()->year);

        return view('pages.tenancy.sik', [
            'openEid'  => $openEid,
            'years'    => $years,
            'statuses' => self::SIK_STATUS,
            'sites'    => DB::connection('mysql5')->table('mssite')->orderBy('siteid')->get(['siteid', 'sitename']),
        ]);
    }

    /** Export the current filtered list (all pages) as xlsx or csv. */
    public function sikExport(Request $request)
    {
        $this->authorizeSik();

        $q = $this->sikFiltered($request);
        if ($request->filled('status') && $request->status !== 'ALL') $q->where('s.status', $request->status);

        $rows = $this->sikSorted($q, $request)->limit(50000)->get()->map(fn ($r) => [
            $r->siknbr, substr($r->sikdate, 0, 10), $r->sitename, $r->companyname, $r->locationname, $r->storename,
            trim($r->floor . ' ' . $r->unit), $r->requestname, $r->requestpenanggungjawab, $r->requestphone, $r->requestemail,
            $r->startdate, $r->enddate, (int) $r->totalextend, $r->workhour, $r->workdescription, $r->workerdescription,
            $r->created_user, self::SIK_STATUS[$r->status] ?? $r->status,
        ]);

        $export = new class($rows) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            public function __construct(private $rows) {}
            public function collection() { return $this->rows; }
            public function headings(): array
            {
                return ['SIK No', 'Date', 'Site', 'Company', 'Location', 'Tenant', 'Floor / Unit', 'Requester', 'Person in Charge',
                    'Phone', 'Email', 'Start Date', 'End Date', 'Total Extend', 'Work Hour', 'Work Description', 'Workers', 'Created By', 'Status'];
            }
        };

        $csv = $request->input('format') === 'csv';

        return \Maatwebsite\Excel\Facades\Excel::download(
            $export,
            'SIK_' . now()->format('Ymd_His') . ($csv ? '.csv' : '.xlsx'),
            $csv ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX
        );
    }

    /** Whitelisted column sort; ties broken by newest first. */
    private function sikSorted($q, Request $request)
    {
        $map = ['siknbr' => 's.siknbr', 'sikdate' => 's.sikdate', 'sitename' => 's.sitename', 'storename' => 's.storename',
            'requestname' => 's.requestname', 'startdate' => 's.startdate', 'created_user' => 's.created_user', 'status' => 's.status'];
        $col = $map[$request->input('sort')] ?? null;
        if ($col) $q->orderBy($col, $request->input('dir') === 'asc' ? 'asc' : 'desc');

        return $q->orderByDesc('s.id');
    }

    private function sikFiltered(Request $request)
    {
        $q = $this->sikQuery();
        if ($request->filled('year'))   $q->whereBetween('s.sikdate', [$request->year . '-01-01 00:00:00', $request->year . '-12-31 23:59:59']);
        if ($request->filled('siteid')) $q->where('s.siteid', $request->siteid);
        if ($request->filled('from'))   $q->whereDate('s.sikdate', '>=', $request->from);
        if ($request->filled('to'))     $q->whereDate('s.sikdate', '<=', $request->to);
        if ($request->filled('search')) {
            $t = '%' . trim($request->search) . '%';
            $q->where(fn ($w) => $w->where('s.siknbr', 'like', $t)->orWhere('s.storename', 'like', $t)
                ->orWhere('s.requestname', 'like', $t)->orWhere('s.created_user', 'like', $t));
        }

        return $q;
    }

    public function sikJson(Request $request)
    {
        $this->authorizeSik();

        $request->validate(['year' => 'nullable|digits:4']);
        $q = $this->sikFiltered($request);

        // Card counts ignore the status filter itself but respect scope, site, dates and search.
        $counts = (clone $q)->selectRaw('s.status, count(*) c')->groupBy('s.status')->pluck('c', 'status');
        $counts = ['ALL' => (int) $counts->sum()] + array_map(fn ($k) => (int) ($counts[$k] ?? 0), array_combine(array_keys(self::SIK_STATUS), array_keys(self::SIK_STATUS)));

        if ($request->filled('status') && $request->status !== 'ALL') $q->where('s.status', $request->status);

        $total   = (clone $q)->count();
        $perPage = min(max((int) $request->input('per_page', 15), 10), 100);
        $page    = max((int) $request->input('page', 1), 1);

        $rows = $this->sikSorted($q, $request)->forPage($page, $perPage)->get([
            's.id', 's.siknbr', 's.sikdate', 's.sitename', 's.companyname', 's.locationname', 's.storename', 's.floor', 's.unit',
            's.requestname', 's.startdate', 's.enddate', 's.totalextend', 's.status', 's.created_user',
        ])->map(function ($r) {
            $r->eid = Hashids::encode($r->id);
            unset($r->id);
            $r->status_label = self::SIK_STATUS[$r->status] ?? $r->status;
            return $r;
        });

        return response()->json(['data' => $rows, 'counts' => $counts, 'total' => $total, 'page' => $page, 'per_page' => $perPage]);
    }


    // =====================================================================
    // SIK detail + actions (ported from the legacy tenant app's SIKController)
    // =====================================================================

    private function sikDb()
    {
        return DB::connection('mysql5');
    }

    /** Legacy stores local (Jakarta) time. */
    private function sikNow(): string
    {
        return now('Asia/Jakarta')->format('Y-m-d H:i:s');
    }

    /** Tenant-created SIK (from the view) by hashed id, or 404. */
    private function sikFindOrFail(string $eid)
    {
        $id = Hashids::decode($eid)[0] ?? null;
        abort_unless($id, 404);
        $sik = $this->sikQuery()->where('s.id', $id)->first();
        abort_unless($sik, 404);

        return $sik;
    }

    /** The pending "Approve" step where $username is (one of) the current approver(s). */
    private function sikPendingRow($sik, string $username)
    {
        return $this->sikDb()->table('trapproval')
            ->where('doctype', 'SIK')->where('refnbr', $sik->siknbr)->where('siteid', $sik->siteid)
            ->where('accesstype', 'Approve')->where('progressapproval', 1)->where('aprvresult', 'P')->where('status', 'A')
            ->whereRaw("FIND_IN_SET(?, REPLACE(username, ' ', '')) > 0", [$username])
            ->orderBy('nomor')->first();
    }

    /** Active users who can be added as an extra approver: internal users not already approvers on this SIK. */
    private function sikApproverCandidates($sik)
    {
        $taken = $this->sikDb()->table('trapproval')->where('doctype', 'SIK')->where('refnbr', $sik->siknbr)->where('siteid', $sik->siteid)
            ->where('status', 'A')->where('progressapproval', '!=', 3)->pluck('username')
            ->flatMap(fn ($u) => array_map(fn ($x) => strtolower(trim($x)), explode(',', (string) $u)))->all();

        // Department name (msdepartment has one row per site, so collapse to one name per departmentid); the SIK's own department comes first.
        $depts = $this->sikDb()->table('msdepartment')->where('status', 'A')->selectRaw('departmentid, min(departmentname) as departmentname')->groupBy('departmentid');

        return $this->sikDb()->table('user as u')->leftJoinSub($depts, 'd', 'd.departmentid', '=', 'u.departmentid')
            ->where('u.status', 'A')->whereIn('u.usertype', ['SUPERADMIN', 'ADMIN', 'USER'])->where('u.is_admin', '!=', 0)
            // Active users whose own site is this SIK's site (not just people who have access to it).
            ->where('u.siteid', $sik->siteid)
            ->orderByRaw('(u.departmentid = ?) desc', [$sik->departmentid])->orderByRaw("coalesce(d.departmentname, u.departmentid, 'zzz')")->orderBy('u.name')
            ->get(['u.username', 'u.name', DB::raw("coalesce(d.departmentname, nullif(u.departmentid, '')) as department"), DB::raw('(u.departmentid = ' . $this->sikDb()->getPdo()->quote($sik->departmentid) . ') as same_dept')])
            ->reject(fn ($u) => in_array(strtolower($u->username), $taken, true))->values();
    }

    /** Only the user who created the SIK may add attachments (legacy status rules kept; approvers no longer can). */
    private function sikCanUpload($sik, string $username): bool
    {
        if ($sik->created_user !== $username) {
            return false;
        }

        $steps = fn () => $this->sikDb()->table('trapproval')->where('doctype', 'SIK')
            ->where('refnbr', $sik->siknbr)->where('siteid', $sik->siteid);

        $pending = $steps()->where('aprvresult', 'P')->where('progressapproval', 1)->count() > 0;
        $decided = $steps()->whereIn('aprvresult', ['A', 'R'])->where('progressapproval', 2)->count() > 0;

        return in_array($sik->status, ['H', 'E'])
            || ($sik->status === 'P' && $pending && ! $decided);
    }

    /** People who can be @mentioned on a SIK: the creator (tenant user) and every approver in the flow, minus the current user. */
    private function sikMentionable($sik, $approvals): array
    {
        $me = strtolower((string) Auth::user()?->username);

        $names = [];
        $tenantName = $this->sikDb()->table('msusertenant')->where('username', $sik->created_user)->value('name');
        $names[strtolower($sik->created_user)] = ['username' => $sik->created_user, 'name' => $tenantName ?: $sik->created_user, 'role' => 'Creator'];

        $approverNames = collect($approvals)->where('accesstype', '!=', 'Submit')
            ->flatMap(fn ($a) => array_filter(array_map('trim', explode(',', (string) $a->username))))
            ->unique()->values();
        $found = $approverNames->isEmpty() ? collect() : \App\Models\User::whereIn(DB::raw('lower(username)'), $approverNames->map(fn ($u) => strtolower($u))->all())
            ->pluck('name', 'username')->mapWithKeys(fn ($n, $u) => [strtolower($u) => $n]);
        foreach ($approverNames as $u) {
            $names[strtolower($u)] ??= ['username' => $u, 'name' => $found[strtolower($u)] ?? $u, 'role' => 'Approver'];
        }

        unset($names[$me]);

        return array_values($names);
    }

    /** approvals / attachments / messages / reason for one SIK. */
    private function sikPayload($sik): array
    {
        $db = $this->sikDb();
        $steps = fn () => $db->table('trapproval')->where('doctype', 'SIK')
            ->where('refnbr', $sik->siknbr)->where('siteid', $sik->siteid)->where('status', 'A')->where('progressapproval', '!=', 3);
        $cols = ['username', 'accesstype', 'conditiontype', 'created_user', 'urutan', 'aprvresult', 'progressapproval', 'aprvdateafter'];

        // Same selection as the legacy print: latest Submit + the live approval steps (no cancelled "L" rows).
        $submit = $steps()->where('aprvresult', 'S')->orderByDesc('id')->first($cols);
        $approvals = collect($submit ? [$submit] : [])->concat(
            $steps()->whereNotIn('aprvresult', ['L', 'S'])->orderBy('nomor')->orderBy('urutan')->get($cols)
        )->values();

        $attachments = $db->table('msattachment')->where('doctype', 'SIK')->where('refnbr', $sik->siknbr)->where('status', 'A')
            ->orderBy('id')->get(['id', 'name', 'filename', 'fileextension', 'created_user', 'created_datetime'])
            ->map(function ($a) {
                $a->url = route('tenantsupport.sik.attachment', Hashids::encode($a->id));
                unset($a->id);

                return $a;
            });

        $messages = $db->table('trmessage')->where('doctype', 'SIK')->where('refnbr', $sik->siknbr)->where('status', 'A')
            ->orderBy('id')->get(['name', 'messagetype', 'message', 'created_user', 'created_datetime']);

        // Reject / Revise reason only matters while the SIK is in that state.
        $reason = in_array($sik->status, ['H', 'R'])
            ? $messages->whereIn('messagetype', ['Reject', 'Revise'])->last()?->message
            : null;

        return ['approvals' => $approvals, 'attachments' => $attachments, 'messages' => $messages, 'reason' => $reason, 'mentionable' => $this->sikMentionable($sik, $approvals)];
    }

    public function sikShow(string $eid)
    {
        $this->authorizeSik();
        $sik = $this->sikFindOrFail($eid);
        $me  = Auth::user()->username;

        $payload = $this->sikPayload($sik);
        $can = [
            'approve' => in_array($sik->status, ['P', 'E']) && $this->sikPendingRow($sik, $me) !== null,
            'upload'  => $this->sikCanUpload($sik, $me),
            // Only the approver whose turn it is may add another approver (as a sub-level), and only while the SIK is On Progress.
            'extend'  => $sik->status === 'P' && $this->sikPendingRow($sik, $me) !== null,
        ];

        unset($sik->id);
        $sik->status_label = self::SIK_STATUS[$sik->status] ?? $sik->status;

        return response()->json(['data' => $sik, 'can' => $can] + $payload);
    }

    /** PDF of one SIK (same data as the detail popup). */
    public function sikPrint(string $eid)
    {
        $d = $this->sikShow($eid)->getData(true);

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.tenancy.pdf.sik', ['d' => $d])
            ->setPaper('a4')
            ->stream($d['data']['siknbr'] . '.pdf');
    }

    /**
     * The formal "Surat Ijin Kerja" permit (same layout as the WPS print): letterhead per site, work details,
     * site notes, signature blocks for every approval step, and a QR that opens the public verify page.
     */
    public function sikPermit(string $eid)
    {
        $this->authorizeSik();
        $sik = $this->sikFindOrFail($eid);
        $db  = $this->sikDb();

        // Live approval steps only (cancelled "L" rows and rounds voided by a revise are left out).
        $trail = $db->table('trapproval')->where('doctype', 'SIK')->where('refnbr', $sik->siknbr)->where('siteid', $sik->siteid)
            ->where('status', 'A')->where('progressapproval', '!=', 3)->orderBy('nomor')->get();

        $verifyUrl = URL::signedRoute('tenantsupport.sik.verify', ['eid' => $eid]);
        $qrSvg = (new \BaconQrCode\Writer(new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(120, 0), new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        )))->writeString($verifyUrl);

        $notes = $db->table('msprintnote')->where('doctype', 'SIK')->where('siteid', $sik->siteid)
            ->whereIn('departmentid', ['ALL', $sik->departmentid])->where('status', 'A')->orderBy('nomor')->get();

        $usernames = $trail->pluck('username')->flatMap(fn ($u) => explode(',', (string) $u))
            ->merge($trail->pluck('lastupdate_user'))->push($sik->created_user)
            ->map(fn ($u) => trim((string) $u))->filter()->unique()->values()->all();

        return Pdf::loadView('pages.tenancy.pdf.sik-permit', [
            'sik'             => $sik,
            'trail'           => $trail,
            'site'            => $db->table('mssite')->where('siteid', $sik->siteid)->first(),
            'notes'           => $notes,
            'workHourNote'    => $db->table('msworkhour')->where('doctype', 'SIK')->where('siteid', $sik->siteid)->where('workhour', $sik->workhour)->value('workhournote'),
            'users'           => $db->table('user')->whereIn('username', $usernames)->get(['username', 'name', 'departmentid'])->keyBy('username'),
            'departmentNames' => $db->table('msdepartment')->where('siteid', $sik->siteid)->pluck('departmentname', 'departmentid'),
            'floorUnit'       => collect([$sik->storename, $sik->floor, $sik->unit])->filter()->implode(' / '),
            'verifyUrl'       => $verifyUrl,
            'qrSvg'           => $qrSvg,
        ])->setPaper('a4')->stream("SIK-{$sik->siknbr}.pdf");
    }

    /** Public page behind the permit's QR code (signed link, no login) so the gate can confirm the print matches the system. */
    public function sikVerify(string $eid)
    {
        $id  = Hashids::decode($eid)[0] ?? null;
        $sik = $id ? $this->sikQuery()->where('s.id', $id)->first() : null;
        abort_unless($sik, 404);

        return view('pages.tenancy.verify-sik', ['sik' => $sik, 'statusLabel' => self::SIK_STATUS[$sik->status] ?? $sik->status]);
    }

    /** Approve (A) / Reject (R) / Revise (D). Reject and Revise need a reason. */
    public function sikAction(Request $request, string $eid)
    {
        $this->authorizeSik();
        $in = $request->validate(['action' => 'required|in:A,R,D', 'notes' => 'nullable|string|max:1000']);
        $notes = trim($in['notes'] ?? '');
        if (in_array($in['action'], ['R', 'D']) && $notes === '') {
            return response()->json(['message' => 'Please fill the reason.'], 422);
        }

        $sik  = $this->sikFindOrFail($eid);
        $user = Auth::user();
        $now  = $this->sikNow();
        $db   = $this->sikDb();
        $mails = [];

        $error = $db->transaction(function () use ($in, $notes, $sik, $user, $now, $db, &$mails) {
            $cur = $db->table('trsik')->where('siknbr', $sik->siknbr)->where('siteid', $sik->siteid)
                ->whereIn('status', ['P', 'E'])->lockForUpdate()->first();
            $mine = $cur ? $this->sikPendingRow($sik, $user->username) : null;
            if (! $cur || ! $mine) return 'This SIK is not waiting for your approval.';

            $steps = fn () => $db->table('trapproval')->where('doctype', 'SIK')
                ->where('refnbr', $sik->siknbr)->where('siteid', $sik->siteid)->where('status', 'A');
            $audit = ['lastupdate_user' => $user->username, 'lastupdate_datetime' => $now];

            $db->table('trapproval')->where('id', $mine->id)->update([
                'progressapproval' => 2, 'aprvresult' => $in['action'], 'username' => $user->username, 'aprvdateafter' => $now,
            ] + $audit);

            $log = fn (string $type, string $text, string $n) => $db->table('tractivity')->insert([
                'refnbr' => $sik->siknbr, 'doctype' => 'SIK', 'siteid' => $sik->siteid, 'name' => $user->name,
                'activitytype' => $type, 'activity' => "$type Surat Izin Kerja {$sik->siknbr}", 'notes' => $n, 'status' => 'A',
                'created_user' => $user->username, 'created_datetime' => $now,
            ] + $audit);
            $chat = fn (string $type, string $text) => $db->table('trmessage')->insert([
                'refnbr' => $sik->siknbr, 'doctype' => 'SIK', 'siteid' => $sik->siteid, 'name' => $user->name,
                'messagetype' => $type, 'message' => $text, 'status' => 'A',
                'created_user' => $user->username, 'created_datetime' => $now,
            ] + $audit);

            if ($in['action'] === 'A') {
                $next = $steps()->where('aprvresult', 'P')->orderBy('nomor')->first();
                if (! $next) {
                    $db->table('trsik')->where('id', $cur->id)->update(['status' => 'C'] + $audit);
                    $mails[] = ['complete', $cur->created_user];
                } else {
                    $db->table('trapproval')->where('id', $next->id)->update(['progressapproval' => 1, 'aprvdatebefore' => $now]);
                    $mails[] = ['waiting', $next->username];
                }
                $log('Approve', '', '');
            } elseif ($in['action'] === 'R') {
                $db->table('trsik')->where('id', $cur->id)->update(['status' => 'R'] + $audit);
                $steps()->where('aprvresult', 'P')->where('progressapproval', 0)
                    ->update(['nomor' => 0, 'progressapproval' => 3, 'aprvresult' => 'L'] + $audit);
                $log('Reject', '', $notes);
                $chat('Reject', "{$sik->siknbr} Rejected - $notes");
                $mails[] = ['reject', $cur->created_user];
            } else {
                $db->table('trsik')->where('id', $cur->id)->update(['status' => $cur->status === 'E' ? 'C' : 'H'] + $audit);
                $steps()->where('aprvresult', 'P')->update(['nomor' => 0, 'aprvresult' => 'L'] + $audit);
                $steps()->update(['progressapproval' => 3] + $audit);
                $log('Revise', '', $notes);
                $chat('Revise', "{$sik->siknbr} Revised - $notes");
                $mails[] = ['revise', $cur->created_user];
            }

            return null;
        });

        if ($error) return response()->json(['message' => $error], 409);

        foreach ($mails as [$kind, $to]) $this->sikSendMail($kind, $to, $sik, $notes);

        return $this->sikShow($eid);
    }

    /** Users who can still be added as an extra approver (only for the approver whose turn it is, SIK On Progress). */
    public function sikApproverOptions(string $eid)
    {
        $this->authorizeSik();
        $sik = $this->sikFindOrFail($eid);
        abort_unless($sik->status === 'P' && $this->sikPendingRow($sik, Auth::user()->username), 403);

        return response()->json(['data' => $this->sikApproverCandidates($sik)]);
    }

    /**
     * Extend the approval of an On Progress SIK: the approver who currently holds the step (level N) adds a user as
     * sub-level N.1, N.2, ... placed right after level N and before level N+1.
     */
    public function sikAddApprover(Request $request, string $eid)
    {
        $this->authorizeSik();
        $in   = $request->validate(['username' => 'required|string|max:50', 'notes' => 'required|string|max:1000']);
        $sik  = $this->sikFindOrFail($eid);
        $user = Auth::user();

        if (! $this->sikPendingRow($sik, $user->username)) {
            return response()->json(['message' => 'Only the approver whose turn it is can add an approver.'], 403);
        }
        $new = $this->sikApproverCandidates($sik)->firstWhere('username', $in['username']);
        if (! $new) {
            return response()->json(['message' => 'That user cannot be added (not an active internal user, or already an approver on this SIK).'], 422);
        }

        $notes = trim($in['notes']);
        $now   = $this->sikNow();
        $db    = $this->sikDb();

        $error = $db->transaction(function () use ($new, $notes, $sik, $user, $now, $db) {
            // Same lock the approve action takes, so a concurrent last approval can't slip past this check.
            $cur = $db->table('trsik')->where('siknbr', $sik->siknbr)->where('siteid', $sik->siteid)->where('status', 'P')->lockForUpdate()->first();
            if (! $cur) return 'This SIK is no longer On Progress, so an approver can not be added.';

            // Re-check under the lock that it is still this user's turn (they may have just approved).
            $mine = $this->sikPendingRow($sik, $user->username);
            if (! $mine) return 'It is no longer your turn to approve, so an approver can not be added.';

            $steps = fn () => $db->table('trapproval')->where('doctype', 'SIK')->where('refnbr', $sik->siknbr)->where('siteid', $sik->siteid)
                ->where('status', 'A')->where('progressapproval', '!=', 3);

            // Level N = the level of the step being held; its sub-levels are N.1 .. N.9 (urutan is decimal(3,1)).
            $level  = (int) floor((float) $mine->urutan);
            $inLvl  = fn () => $steps()->where('urutan', '>=', $level)->where('urutan', '<', $level + 1);
            $urutan = round((float) $inLvl()->max('urutan') + 0.1, 1);
            if ($urutan >= $level + 1) return "Level $level already has 9 added approvers.";

            // Place it after the last step of this level, shifting later steps down one slot (approval order follows nomor).
            $after = (int) $inLvl()->max('nomor');
            $db->table('trapproval')->where('doctype', 'SIK')->where('refnbr', $sik->siknbr)->where('siteid', $sik->siteid)
                ->where('nomor', '>', $after)->increment('nomor');

            $audit = ['lastupdate_user' => $user->username, 'lastupdate_datetime' => $now];
            $base  = ['refnbr' => $sik->siknbr, 'doctype' => 'SIK', 'siteid' => $sik->siteid, 'created_user' => $user->username, 'created_datetime' => $now] + $audit;
            $label = rtrim(rtrim(number_format($urutan, 1, '.', ''), '0'), '.');

            $db->table('trapproval')->insert($base + [
                'departmentid' => $cur->departmentid, 'nomor' => $after + 1, 'urutan' => $urutan, 'username' => $new->username,
                'accesstype' => 'Approve', 'conditiontype' => 'Extend', 'progressapproval' => 0, 'aprvresult' => 'P', 'aprvtotalday' => 0, 'status' => 'A',
            ]);
            $db->table('tractivity')->insert($base + [
                'name' => $user->name, 'activitytype' => 'Add Approver', 'activity' => "Add Approver Surat Izin Kerja {$sik->siknbr}",
                'notes' => "Level $label: {$new->name} ({$new->username}) - $notes", 'status' => 'A',
            ]);
            $db->table('trmessage')->insert($base + [
                'name' => $user->name, 'messagetype' => 'Message', 'message' => "{$sik->siknbr} Approver added at level $label: {$new->name} ({$new->username}) - $notes", 'status' => 'A',
            ]);

            return null;
        });

        if ($error) return response()->json(['message' => $error], 422);

        return $this->sikShow($eid);
    }

    public function sikMessage(Request $request, string $eid)
    {
        $this->authorizeSik();
        $in  = $request->validate(['message' => 'required|string|max:1000']);
        $sik = $this->sikFindOrFail($eid);
        $u   = Auth::user();
        $now = $this->sikNow();

        $this->sikDb()->table('trmessage')->insert([
            'refnbr' => $sik->siknbr, 'doctype' => 'SIK', 'siteid' => $sik->siteid, 'name' => $u->name,
            'messagetype' => 'Message', 'message' => trim($in['message']), 'status' => 'A',
            'created_user' => $u->username, 'created_datetime' => $now, 'lastupdate_user' => $u->username, 'lastupdate_datetime' => $now,
        ]);

        return response()->json(['messages' => $this->sikPayload($sik)['messages']]);
    }

    /** Upload to the same bucket path the legacy app uses: att-wps GPS/<site>/SIK/<year>/<SIK no>/<random>.<ext> */
    public function sikUpload(Request $request, string $eid)
    {
        $this->authorizeSik();
        $request->validate(['file' => 'required|file']);
        $sik  = $this->sikFindOrFail($eid);
        $user = Auth::user();

        if (! $this->sikCanUpload($sik, $user->username)) {
            return response()->json(['message' => 'Only the user who created this SIK can add an attachment, and only before approval has started.'], 422);
        }

        $file = $request->file('file');
        $ext  = strtolower($file->getClientOriginalExtension());
        $db   = $this->sikDb();
        $setting = fn (string $p) => $db->table('msglobalsetting')->where('idparameter', $p)->where('status', 'A')->value('parameter');

        if ($maxMb = (float) $setting('FILESIZE')) {
            if ($file->getSize() / 1048576 > $maxMb) return response()->json(['message' => "File size must not exceed {$maxMb} MB."], 422);
        }
        if ($allowed = $setting('FILEEXTENSION')) {
            $list = array_filter(array_map(fn ($e) => strtolower(trim($e)), explode(',', $allowed)));
            if ($list && ! in_array($ext, $list, true)) return response()->json(['message' => "File type not allowed. Allowed: {$allowed}."], 422);
        }

        $period = \Carbon\Carbon::parse($sik->sikdate)->year;
        $stored = Str::random(32) . '.' . $ext;
        $object = "att-wps GPS/{$sik->siteid}/SIK/{$period}/{$sik->siknbr}/{$stored}";

        $this->gcsClient()->bucket(config('filesystems.disks.gcs.bucket'))->upload(fopen($file->getRealPath(), 'r'), ['name' => $object]);

        $now = $this->sikNow();
        $audit = ['created_user' => $user->username, 'created_datetime' => $now, 'lastupdate_user' => $user->username, 'lastupdate_datetime' => $now];
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $db->table('msattachment')->insert([
            'refnbr' => $sik->siknbr, 'doctype' => 'SIK', 'siteid' => $sik->siteid, 'period' => $period,
            'name' => $name, 'filename' => $stored, 'fileextension' => $ext, 'status' => 'A',
        ] + $audit);
        $db->table('tractivity')->insert([
            'refnbr' => $sik->siknbr, 'doctype' => 'SIK', 'siteid' => $sik->siteid, 'name' => $user->name,
            'activitytype' => 'Upload Attachment', 'activity' => "Upload Attachment Surat Izin Kerja {$sik->siknbr}",
            'notes' => "$name.$ext", 'status' => 'A',
        ] + $audit);

        return response()->json(['attachments' => $this->sikPayload($sik)['attachments']]);
    }

    /** Signed, short-lived link to one SIK attachment. */
    public function sikAttachment(string $aeid)
    {
        $this->authorizeSik();
        $id = Hashids::decode($aeid)[0] ?? null;
        abort_unless($id, 404);
        $att = $this->sikDb()->table('msattachment')->where('id', $id)->where('doctype', 'SIK')->where('status', 'A')->first();
        abort_unless($att, 404);
        abort_unless($this->sikQuery()->where('s.siknbr', $att->refnbr)->exists(), 404);

        $object = "att-wps GPS/{$att->siteid}/SIK/{$att->period}/{$att->refnbr}/{$att->filename}";
        $url = $this->gcsSignedUrl($object, 15);
        abort_unless($url, 404);

        return redirect()->away($url);
    }

    /** Same notifications as the legacy app. Never blocks the approval if mail fails. */
    private function sikSendMail(string $kind, string $usernames, $sik, string $notes = ''): void
    {
        try {
            $subjects = ['waiting' => 'Waiting Your Approval', 'revise' => 'is Revised, Please Review', 'reject' => 'is Rejected', 'complete' => 'is Completed'];
            $users = $this->sikDb()->table('user')->whereIn('username', array_map('trim', explode(',', $usernames)))
                ->where('status', 'A')->get(['name', 'email']);
            if ($users->isEmpty()) return;

            $pdf = null;
            if ($kind === 'complete') {
                $d = $this->sikShow(Hashids::encode($sik->id))->getData(true);
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.tenancy.pdf.sik', ['d' => $d])->setPaper('a4')->output();
            }

            foreach ($users as $u) {
                if (! $u->email) continue;
                Mail::send('pages.tenancy.mail.sik', [
                    'kind' => $kind, 'name' => $u->name, 'siknbr' => $sik->siknbr, 'date' => $this->sikNow(),
                    'site' => $sik->siteid, 'notes' => $notes, 'link' => route('tenantsupport.sik.show', Hashids::encode($sik->id)),
                ], function ($m) use ($u, $sik, $kind, $subjects, $pdf) {
                    $m->to($u->email, $u->name)->subject("{$sik->siteid} - {$sik->siknbr} - {$subjects[$kind]}");
                    if ($pdf) $m->attachData($pdf, $sik->siknbr . '.pdf');
                });
            }
        } catch (\Throwable $e) {
            Log::warning('SIK mail failed: ' . $e->getMessage());
        }
    }
}
