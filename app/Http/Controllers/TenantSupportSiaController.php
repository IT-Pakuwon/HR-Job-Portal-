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

/**
 * Tenancy Support > SIA Request: review / approve the Surat Izin Acara (event permit) that tenants submit in WPS.
 * Cloned from TenantSupportController (SIK); keep the two in sync when the approval flow changes.
 */
class TenantSupportSiaController extends Controller
{
    use UploadsToGcs;
    use \App\Http\Controllers\Traits\TenantAttachmentRules;

    /** SIA status letters in trsia.status */
    private const SIA_STATUS = ['P' => 'On Progress', 'C' => 'Completed', 'H' => 'Hold / Revise', 'E' => 'Extend Pending', 'R' => 'Rejected', 'X' => 'Cancelled'];

    private function authorizeSia(): void
    {
        $user = Auth::user();
        abort_unless($user && ($user->hasRole('ACCESSTS') || $user->isPrimaryAdmin()), 403);
    }

    /** SIA created by tenant users (msusertenant). */
    private function siaQuery()
    {
        return DB::connection('mysql5')->table('viewtrsia as s')
            ->whereExists(fn ($q) => $q->select(DB::raw(1))->from('msusertenant as u')
                ->whereColumn('u.username', 's.created_user'));
    }

    /**
     * /tenant-support/sia/{eid}: every SIA has its own link (same hashed-id pattern as the other modules).
     * A browser visit opens the list with that SIA's popup already open; the popup's own fetch asks for JSON.
     */
    public function siaPage(Request $request, string $eid)
    {
        if ($request->expectsJson()) {
            return $this->siaShow($eid);
        }

        $this->authorizeSia();
        $this->siaFindOrFail($eid);   // 404 for an unknown / non-tenant SIA

        return $this->sia($eid);
    }

    public function sia(?string $openEid = null)
    {
        $this->authorizeSia();

        $years = $this->siaQuery()->selectRaw('distinct year(s.siadate) y')->orderByDesc('y')->pluck('y')->all();
        if (! in_array((int) now()->year, $years)) array_unshift($years, (int) now()->year);

        return view('pages.tenancy.sia', [
            'openEid'  => $openEid,
            'years'    => $years,
            'statuses' => self::SIA_STATUS,
            'sites'    => DB::connection('mysql5')->table('mssite')->orderBy('siteid')->get(['siteid', 'sitename']),
        ]);
    }

    /** Export the current filtered list (all pages) as xlsx or csv. */
    public function siaExport(Request $request)
    {
        $this->authorizeSia();

        $q = $this->siaFiltered($request);
        if ($request->filled('status') && $request->status !== 'ALL') $q->where('s.status', $request->status);

        $rows = $this->siaSorted($q, $request)->limit(50000)->get()->map(fn ($r) => [
            $r->sianbr, substr($r->siadate, 0, 10), $r->sitename, $r->companyname, $r->locationname, $r->storename,
            trim($r->floor . ' ' . $r->unit), $r->requestpenanggungjawab, $r->requestphone, $r->requestemail,
            $r->startdate, $r->enddate, $r->starttime, $r->endtime, (int) $r->totalextend,
            $r->eventtype, $r->eventlocation, $r->eventguests, $r->coordinatorname, $r->picsecurity, $r->picengineering,
            $r->organizername, $r->organizerpic, $r->created_user, self::SIA_STATUS[$r->status] ?? $r->status,
        ]);

        $export = new class($rows) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            public function __construct(private $rows) {}
            public function collection() { return $this->rows; }
            public function headings(): array
            {
                return ['SIA No', 'Date', 'Site', 'Company', 'Location', 'Tenant', 'Floor / Unit', 'Person in Charge',
                    'Phone', 'Email', 'Start Date', 'End Date', 'Start Time', 'End Time', 'Total Extend',
                    'Event Type', 'Event Location', 'Guests / Media', 'Event Coordinator', 'PIC Security', 'PIC Engineering',
                    'Event Organizer', 'Event Organizer PIC', 'Created By', 'Status'];
            }
        };

        $csv = $request->input('format') === 'csv';

        return \Maatwebsite\Excel\Facades\Excel::download(
            $export,
            'SIA_' . now()->format('Ymd_His') . ($csv ? '.csv' : '.xlsx'),
            $csv ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX
        );
    }

    /** Whitelisted column sort; ties broken by newest first. */
    private function siaSorted($q, Request $request)
    {
        $map = ['sianbr' => 's.sianbr', 'siadate' => 's.siadate', 'sitename' => 's.sitename', 'storename' => 's.storename',
            'requestpenanggungjawab' => 's.requestpenanggungjawab', 'eventtype' => 's.eventtype', 'startdate' => 's.startdate', 'created_user' => 's.created_user', 'status' => 's.status'];
        $col = $map[$request->input('sort')] ?? null;
        if ($col) $q->orderBy($col, $request->input('dir') === 'asc' ? 'asc' : 'desc');

        return $q->orderByDesc('s.id');
    }

    private function siaFiltered(Request $request)
    {
        $q = $this->siaQuery();
        if ($request->filled('year'))   $q->whereBetween('s.siadate', [$request->year . '-01-01 00:00:00', $request->year . '-12-31 23:59:59']);
        if ($request->filled('siteid')) $q->where('s.siteid', $request->siteid);
        if ($request->filled('from'))   $q->whereDate('s.siadate', '>=', $request->from);
        if ($request->filled('to'))     $q->whereDate('s.siadate', '<=', $request->to);
        if ($request->filled('search')) {
            $t = '%' . trim($request->search) . '%';
            $q->where(fn ($w) => $w->where('s.sianbr', 'like', $t)->orWhere('s.storename', 'like', $t)
                ->orWhere('s.requestpenanggungjawab', 'like', $t)->orWhere('s.eventtype', 'like', $t)->orWhere('s.created_user', 'like', $t));
        }

        return $q;
    }

    public function siaJson(Request $request)
    {
        $this->authorizeSia();

        $request->validate(['year' => 'nullable|digits:4']);
        $q = $this->siaFiltered($request);

        // Card counts ignore the status filter itself but respect scope, site, dates and search.
        $counts = (clone $q)->selectRaw('s.status, count(*) c')->groupBy('s.status')->pluck('c', 'status');
        $counts = ['ALL' => (int) $counts->sum()] + array_map(fn ($k) => (int) ($counts[$k] ?? 0), array_combine(array_keys(self::SIA_STATUS), array_keys(self::SIA_STATUS)));

        if ($request->filled('status') && $request->status !== 'ALL') $q->where('s.status', $request->status);

        $total   = (clone $q)->count();
        $perPage = min(max((int) $request->input('per_page', 15), 10), 100);
        $page    = max((int) $request->input('page', 1), 1);

        $rows = $this->siaSorted($q, $request)->forPage($page, $perPage)->get([
            's.id', 's.sianbr', 's.siadate', 's.sitename', 's.companyname', 's.locationname', 's.storename', 's.floor', 's.unit',
            's.requestpenanggungjawab', 's.eventtype', 's.startdate', 's.enddate', 's.totalextend', 's.status', 's.created_user',
        ])->map(function ($r) {
            $r->eid = Hashids::encode($r->id);
            unset($r->id);
            $r->status_label = self::SIA_STATUS[$r->status] ?? $r->status;
            return $r;
        });

        return response()->json(['data' => $rows, 'counts' => $counts, 'total' => $total, 'page' => $page, 'per_page' => $perPage]);
    }


    // =====================================================================
    // SIA detail + actions (ported from the legacy tenant app's SIAController)
    // =====================================================================

    private function siaDb()
    {
        return DB::connection('mysql5');
    }

    /** Legacy stores local (Jakarta) time. */
    private function siaNow(): string
    {
        return now('Asia/Jakarta')->format('Y-m-d H:i:s');
    }

    /** Tenant-created SIA (from the view) by hashed id, or 404. */
    private function siaFindOrFail(string $eid)
    {
        $id = Hashids::decode($eid)[0] ?? null;
        abort_unless($id, 404);
        $sia = $this->siaQuery()->where('s.id', $id)->first();
        abort_unless($sia, 404);

        return $sia;
    }

    /** The pending "Approve" step where $username is (one of) the current approver(s). */
    private function siaPendingRow($sia, string $username)
    {
        return $this->siaDb()->table('trapproval')
            ->where('doctype', 'SIA')->where('refnbr', $sia->sianbr)->where('siteid', $sia->siteid)
            ->where('accesstype', 'Approve')->where('progressapproval', 1)->where('aprvresult', 'P')->where('status', 'A')
            ->whereRaw("FIND_IN_SET(?, REPLACE(username, ' ', '')) > 0", [$username])
            ->orderBy('nomor')->first();
    }

    /** Active users who can be added as an extra approver: internal users not already approvers on this SIA. */
    private function siaApproverCandidates($sia)
    {
        $taken = $this->siaDb()->table('trapproval')->where('doctype', 'SIA')->where('refnbr', $sia->sianbr)->where('siteid', $sia->siteid)
            ->where('status', 'A')->where('progressapproval', '!=', 3)->pluck('username')
            ->flatMap(fn ($u) => array_map(fn ($x) => strtolower(trim($x)), explode(',', (string) $u)))->all();

        // Department name (msdepartment has one row per site, so collapse to one name per departmentid); the SIA's own department comes first.
        $depts = $this->siaDb()->table('msdepartment')->where('status', 'A')->selectRaw('departmentid, min(departmentname) as departmentname')->groupBy('departmentid');

        return $this->siaDb()->table('user as u')->leftJoinSub($depts, 'd', 'd.departmentid', '=', 'u.departmentid')
            ->where('u.status', 'A')->whereIn('u.usertype', ['SUPERADMIN', 'ADMIN', 'USER'])->where('u.is_admin', '!=', 0)
            // Active users whose own site is this SIA's site (not just people who have access to it).
            ->where('u.siteid', $sia->siteid)
            ->orderByRaw('(u.departmentid = ?) desc', [$sia->departmentid])->orderByRaw("coalesce(d.departmentname, u.departmentid, 'zzz')")->orderBy('u.name')
            ->get(['u.username', 'u.name', DB::raw("coalesce(d.departmentname, nullif(u.departmentid, '')) as department"), DB::raw('(u.departmentid = ' . $this->siaDb()->getPdo()->quote($sia->departmentid) . ') as same_dept')])
            ->reject(fn ($u) => in_array(strtolower($u->username), $taken, true))->values();
    }

    /** Only the user who created the SIA may add attachments (legacy status rules kept; approvers no longer can). */
    private function siaCanUpload($sia, string $username): bool
    {
        if ($sia->created_user !== $username) {
            return false;
        }

        $steps = fn () => $this->siaDb()->table('trapproval')->where('doctype', 'SIA')
            ->where('refnbr', $sia->sianbr)->where('siteid', $sia->siteid);

        $pending = $steps()->where('aprvresult', 'P')->where('progressapproval', 1)->count() > 0;
        $decided = $steps()->whereIn('aprvresult', ['A', 'R'])->where('progressapproval', 2)->count() > 0;

        return in_array($sia->status, ['H', 'E'])
            || ($sia->status === 'P' && $pending && ! $decided);
    }

    /** People who can be @mentioned on a SIA: the creator (tenant user) and every approver in the flow, minus the current user. */
    private function siaMentionable($sia, $approvals): array
    {
        $me = strtolower((string) Auth::user()?->username);

        $names = [];
        $tenantName = $this->siaDb()->table('msusertenant')->where('username', $sia->created_user)->value('name');
        $names[strtolower($sia->created_user)] = ['username' => $sia->created_user, 'name' => $tenantName ?: $sia->created_user, 'role' => 'Creator'];

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

    /** approvals / attachments / messages / reason for one SIA. */
    private function siaPayload($sia): array
    {
        $db = $this->siaDb();
        $steps = fn () => $db->table('trapproval')->where('doctype', 'SIA')
            ->where('refnbr', $sia->sianbr)->where('siteid', $sia->siteid)->where('status', 'A')->where('progressapproval', '!=', 3);
        $cols = ['username', 'accesstype', 'conditiontype', 'created_user', 'urutan', 'aprvresult', 'progressapproval', 'aprvdateafter'];

        // Same selection as the legacy print: latest Submit + the live approval steps (no cancelled "L" rows).
        $submit = $steps()->where('aprvresult', 'S')->orderByDesc('id')->first($cols);
        $approvals = collect($submit ? [$submit] : [])->concat(
            $steps()->whereNotIn('aprvresult', ['L', 'S'])->orderBy('nomor')->orderBy('urutan')->get($cols)
        )->values();

        $attachments = $db->table('msattachment')->where('doctype', 'SIA')->where('refnbr', $sia->sianbr)->where('status', 'A')
            ->orderBy('id')->get(['id', 'name', 'filename', 'fileextension', 'created_user', 'created_datetime'])
            ->map(function ($a) {
                $a->url = route('tenantsupport.sia.attachment', Hashids::encode($a->id));
                unset($a->id);

                return $a;
            });

        $messages = $db->table('trmessage')->where('doctype', 'SIA')->where('refnbr', $sia->sianbr)->where('status', 'A')
            ->orderBy('id')->get(['name', 'messagetype', 'message', 'created_user', 'created_datetime']);

        // Reject / Revise reason only matters while the SIA is in that state.
        $reason = in_array($sia->status, ['H', 'R'])
            ? $messages->whereIn('messagetype', ['Reject', 'Revise'])->last()?->message
            : null;

        return ['approvals' => $approvals, 'attachments' => $attachments, 'messages' => $messages, 'reason' => $reason, 'mentionable' => $this->siaMentionable($sia, $approvals)];
    }

    public function siaShow(string $eid)
    {
        $this->authorizeSia();
        $sia = $this->siaFindOrFail($eid);
        $me  = Auth::user()->username;

        $payload = $this->siaPayload($sia);
        $can = [
            'approve' => in_array($sia->status, ['P', 'E']) && $this->siaPendingRow($sia, $me) !== null,
            'upload'  => $this->siaCanUpload($sia, $me),
            // Only the approver whose turn it is may add another approver (as a sub-level), and only while the SIA is On Progress.
            'extend'  => $sia->status === 'P' && $this->siaPendingRow($sia, $me) !== null,
        ];

        unset($sia->id);
        $sia->status_label = self::SIA_STATUS[$sia->status] ?? $sia->status;

        return response()->json(['data' => $sia, 'can' => $can] + $payload);
    }

    /** PDF of one SIA (same data as the detail popup). */
    public function siaPrint(string $eid)
    {
        $d = $this->siaShow($eid)->getData(true);

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.tenancy.pdf.sia', ['d' => $d])
            ->setPaper('a4')
            ->stream($d['data']['sianbr'] . '.pdf');
    }

    /**
     * The formal "Permohonan Ijin Acara" permit (same layout as the WPS print): letterhead per site, event details,
     * the terms, signature blocks for Tenant / Tenant Relation / General Manager, and a QR that opens the public verify page.
     */
    public function siaPermit(string $eid)
    {
        $this->authorizeSia();
        $sia = $this->siaFindOrFail($eid);
        $db  = $this->siaDb();

        // Live approval steps only (cancelled "L" rows and rounds voided by a revise are left out).
        $trail = $db->table('trapproval')->where('doctype', 'SIA')->where('refnbr', $sia->sianbr)->where('siteid', $sia->siteid)
            ->where('status', 'A')->where('progressapproval', '!=', 3)->orderBy('nomor')->get();

        $verifyUrl = URL::signedRoute('tenantsupport.sia.verify', ['eid' => $eid]);
        $qrSvg = (new \BaconQrCode\Writer(new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(120, 0), new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        )))->writeString($verifyUrl);

        $notes = $db->table('msprintnote')->where('doctype', 'SIA')->where('siteid', $sia->siteid)
            ->whereIn('departmentid', ['ALL', $sia->departmentid])->where('status', 'A')->orderBy('nomor')->get();

        $usernames = $trail->pluck('username')->flatMap(fn ($u) => explode(',', (string) $u))
            ->merge($trail->pluck('lastupdate_user'))->push($sia->created_user)
            ->map(fn ($u) => trim((string) $u))->filter()->unique()->values()->all();

        return Pdf::loadView('pages.tenancy.pdf.sia-permit', [
            'sia'             => $sia,
            'trail'           => $trail,
            'site'            => $db->table('mssite')->where('siteid', $sia->siteid)->first(),
            'notes'           => $notes,
            'users'           => $db->table('user')->whereIn('username', $usernames)->get(['username', 'name', 'departmentid'])->keyBy('username'),
            'departmentNames' => $db->table('msdepartment')->where('siteid', $sia->siteid)->pluck('departmentname', 'departmentid'),
            'floorUnit'       => collect([$sia->floor, $sia->unit])->filter()->implode(' / '),
            'verifyUrl'       => $verifyUrl,
            'qrSvg'           => $qrSvg,
            'checkLog'        => TenantSupportCheckerController::permitLog('SIA', $sia->id),
        ])->setPaper('a4')->stream("SIA-{$sia->sianbr}.pdf");
    }

    /** Public page behind the permit's QR code (signed link, no login) so the gate can confirm the print matches the system. */
    public function siaVerify(string $eid)
    {
        $id  = Hashids::decode($eid)[0] ?? null;
        $sia = $id ? $this->siaQuery()->where('s.id', $id)->first() : null;
        abort_unless($sia, 404);

        return view('pages.tenancy.verify-sia', ['sia' => $sia, 'statusLabel' => self::SIA_STATUS[$sia->status] ?? $sia->status]);
    }

    /** Approve (A) / Reject (R) / Revise (D). Reject and Revise need a reason. */
    public function siaAction(Request $request, string $eid)
    {
        $this->authorizeSia();
        $in = $request->validate(['action' => 'required|in:A,R,D', 'notes' => 'nullable|string|max:1000']);
        $notes = trim($in['notes'] ?? '');
        if (in_array($in['action'], ['R', 'D']) && $notes === '') {
            return response()->json(['message' => 'Please fill the reason.'], 422);
        }

        $sia  = $this->siaFindOrFail($eid);
        $user = Auth::user();
        $now  = $this->siaNow();
        $db   = $this->siaDb();
        $mails = [];

        $error = $db->transaction(function () use ($in, $notes, $sia, $user, $now, $db, &$mails) {
            $cur = $db->table('trsia')->where('sianbr', $sia->sianbr)->where('siteid', $sia->siteid)
                ->whereIn('status', ['P', 'E'])->lockForUpdate()->first();
            $mine = $cur ? $this->siaPendingRow($sia, $user->username) : null;
            if (! $cur || ! $mine) return 'This SIA is not waiting for your approval.';

            $steps = fn () => $db->table('trapproval')->where('doctype', 'SIA')
                ->where('refnbr', $sia->sianbr)->where('siteid', $sia->siteid)->where('status', 'A');
            $audit = ['lastupdate_user' => $user->username, 'lastupdate_datetime' => $now];

            $db->table('trapproval')->where('id', $mine->id)->update([
                'progressapproval' => 2, 'aprvresult' => $in['action'], 'username' => $user->username, 'aprvdateafter' => $now,
            ] + $audit);

            $log = fn (string $type, string $text, string $n) => $db->table('tractivity')->insert([
                'refnbr' => $sia->sianbr, 'doctype' => 'SIA', 'siteid' => $sia->siteid, 'name' => $user->name,
                'activitytype' => $type, 'activity' => "$type Surat Izin Acara {$sia->sianbr}", 'notes' => $n, 'status' => 'A',
                'created_user' => $user->username, 'created_datetime' => $now,
            ] + $audit);
            $chat = fn (string $type, string $text) => $db->table('trmessage')->insert([
                'refnbr' => $sia->sianbr, 'doctype' => 'SIA', 'siteid' => $sia->siteid, 'name' => $user->name,
                'messagetype' => $type, 'message' => $text, 'status' => 'A',
                'created_user' => $user->username, 'created_datetime' => $now,
            ] + $audit);

            if ($in['action'] === 'A') {
                $next = $steps()->where('aprvresult', 'P')->orderBy('nomor')->first();
                if (! $next) {
                    $db->table('trsia')->where('id', $cur->id)->update(['status' => 'C'] + $audit);
                    $mails[] = ['complete', $cur->created_user];
                } else {
                    $db->table('trapproval')->where('id', $next->id)->update(['progressapproval' => 1, 'aprvdatebefore' => $now]);
                    $mails[] = ['waiting', $next->username];
                }
                $log('Approve', '', '');
            } elseif ($in['action'] === 'R') {
                $db->table('trsia')->where('id', $cur->id)->update(['status' => 'R'] + $audit);
                $steps()->where('aprvresult', 'P')->where('progressapproval', 0)
                    ->update(['nomor' => 0, 'progressapproval' => 3, 'aprvresult' => 'L'] + $audit);
                $log('Reject', '', $notes);
                $chat('Reject', "{$sia->sianbr} Rejected - $notes");
                $mails[] = ['reject', $cur->created_user];
            } else {
                $db->table('trsia')->where('id', $cur->id)->update(['status' => $cur->status === 'E' ? 'C' : 'H'] + $audit);
                $steps()->where('aprvresult', 'P')->update(['nomor' => 0, 'aprvresult' => 'L'] + $audit);
                $steps()->update(['progressapproval' => 3] + $audit);
                $log('Revise', '', $notes);
                $chat('Revise', "{$sia->sianbr} Revised - $notes");
                $mails[] = ['revise', $cur->created_user];
            }

            return null;
        });

        if ($error) return response()->json(['message' => $error], 409);

        foreach ($mails as [$kind, $to]) $this->siaSendMail($kind, $to, $sia, $notes);

        return $this->siaShow($eid);
    }

    /** Users who can still be added as an extra approver (only for the approver whose turn it is, SIA On Progress). */
    public function siaApproverOptions(string $eid)
    {
        $this->authorizeSia();
        $sia = $this->siaFindOrFail($eid);
        abort_unless($sia->status === 'P' && $this->siaPendingRow($sia, Auth::user()->username), 403);

        return response()->json(['data' => $this->siaApproverCandidates($sia)]);
    }

    /**
     * Extend the approval of an On Progress SIA: the approver who currently holds the step (level N) adds a user as
     * sub-level N.1, N.2, ... placed right after level N and before level N+1.
     */
    public function siaAddApprover(Request $request, string $eid)
    {
        $this->authorizeSia();
        $in   = $request->validate(['username' => 'required|string|max:50', 'notes' => 'required|string|max:1000']);
        $sia  = $this->siaFindOrFail($eid);
        $user = Auth::user();

        if (! $this->siaPendingRow($sia, $user->username)) {
            return response()->json(['message' => 'Only the approver whose turn it is can add an approver.'], 403);
        }
        $new = $this->siaApproverCandidates($sia)->firstWhere('username', $in['username']);
        if (! $new) {
            return response()->json(['message' => 'That user cannot be added (not an active internal user, or already an approver on this SIA).'], 422);
        }

        $notes = trim($in['notes']);
        $now   = $this->siaNow();
        $db    = $this->siaDb();

        $error = $db->transaction(function () use ($new, $notes, $sia, $user, $now, $db) {
            // Same lock the approve action takes, so a concurrent last approval can't slip past this check.
            $cur = $db->table('trsia')->where('sianbr', $sia->sianbr)->where('siteid', $sia->siteid)->where('status', 'P')->lockForUpdate()->first();
            if (! $cur) return 'This SIA is no longer On Progress, so an approver can not be added.';

            // Re-check under the lock that it is still this user's turn (they may have just approved).
            $mine = $this->siaPendingRow($sia, $user->username);
            if (! $mine) return 'It is no longer your turn to approve, so an approver can not be added.';

            $steps = fn () => $db->table('trapproval')->where('doctype', 'SIA')->where('refnbr', $sia->sianbr)->where('siteid', $sia->siteid)
                ->where('status', 'A')->where('progressapproval', '!=', 3);

            // Level N = the level of the step being held; its sub-levels are N.1 .. N.9 (urutan is decimal(3,1)).
            $level  = (int) floor((float) $mine->urutan);
            $inLvl  = fn () => $steps()->where('urutan', '>=', $level)->where('urutan', '<', $level + 1);
            $urutan = round((float) $inLvl()->max('urutan') + 0.1, 1);
            if ($urutan >= $level + 1) return "Level $level already has 9 added approvers.";

            // Place it after the last step of this level, shifting later steps down one slot (approval order follows nomor).
            $after = (int) $inLvl()->max('nomor');
            $db->table('trapproval')->where('doctype', 'SIA')->where('refnbr', $sia->sianbr)->where('siteid', $sia->siteid)
                ->where('nomor', '>', $after)->increment('nomor');

            $audit = ['lastupdate_user' => $user->username, 'lastupdate_datetime' => $now];
            $base  = ['refnbr' => $sia->sianbr, 'doctype' => 'SIA', 'siteid' => $sia->siteid, 'created_user' => $user->username, 'created_datetime' => $now] + $audit;
            $label = rtrim(rtrim(number_format($urutan, 1, '.', ''), '0'), '.');

            $db->table('trapproval')->insert($base + [
                'departmentid' => $cur->departmentid, 'nomor' => $after + 1, 'urutan' => $urutan, 'username' => $new->username,
                'accesstype' => 'Approve', 'conditiontype' => 'Extend', 'progressapproval' => 0, 'aprvresult' => 'P', 'aprvtotalday' => 0, 'status' => 'A',
            ]);
            $db->table('tractivity')->insert($base + [
                'name' => $user->name, 'activitytype' => 'Add Approver', 'activity' => "Add Approver Surat Izin Acara {$sia->sianbr}",
                'notes' => "Level $label: {$new->name} ({$new->username}) - $notes", 'status' => 'A',
            ]);
            $db->table('trmessage')->insert($base + [
                'name' => $user->name, 'messagetype' => 'Message', 'message' => "{$sia->sianbr} Approver added at level $label: {$new->name} ({$new->username}) - $notes", 'status' => 'A',
            ]);

            return null;
        });

        if ($error) return response()->json(['message' => $error], 422);

        return $this->siaShow($eid);
    }

    public function siaMessage(Request $request, string $eid)
    {
        $this->authorizeSia();
        $in  = $request->validate(['message' => 'required|string|max:1000']);
        $sia = $this->siaFindOrFail($eid);
        $u   = Auth::user();
        $now = $this->siaNow();

        $this->siaDb()->table('trmessage')->insert([
            'refnbr' => $sia->sianbr, 'doctype' => 'SIA', 'siteid' => $sia->siteid, 'name' => $u->name,
            'messagetype' => 'Message', 'message' => trim($in['message']), 'status' => 'A',
            'created_user' => $u->username, 'created_datetime' => $now, 'lastupdate_user' => $u->username, 'lastupdate_datetime' => $now,
        ]);

        return response()->json(['messages' => $this->siaPayload($sia)['messages']]);
    }

    /** Upload to the same bucket path the legacy app uses: att-wps/<site>/SIA/<year>/<SIA no>/<random>.<ext> (max 5 MB, image/video) */
    public function siaUpload(Request $request, string $eid)
    {
        $this->authorizeSia();
        $request->validate(['file' => 'required|file']);
        $sia  = $this->siaFindOrFail($eid);
        $user = Auth::user();

        if (! $this->siaCanUpload($sia, $user->username)) {
            return response()->json(['message' => 'Only the user who created this SIA can add an attachment, and only before approval has started.'], 422);
        }

        $file = $request->file('file');
        $ext  = strtolower($file->getClientOriginalExtension());
        $db   = $this->siaDb();
        if ($err = $this->tenantUploadError($file)) {
            return response()->json(['message' => $err], 422);
        }

        $period = \Carbon\Carbon::parse($sia->siadate)->year;
        $stored = Str::random(32) . '.' . $ext;
        $object = $this->tenantObjectPath($sia->siteid, 'SIA', $period, $sia->sianbr, $stored);

        $this->gcsClient()->bucket(config('filesystems.disks.gcs.bucket'))->upload(fopen($file->getRealPath(), 'r'), ['name' => $object]);

        $now = $this->siaNow();
        $audit = ['created_user' => $user->username, 'created_datetime' => $now, 'lastupdate_user' => $user->username, 'lastupdate_datetime' => $now];
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $db->table('msattachment')->insert([
            'refnbr' => $sia->sianbr, 'doctype' => 'SIA', 'siteid' => $sia->siteid, 'period' => $period,
            'name' => $name, 'filename' => $stored, 'fileextension' => $ext, 'status' => 'A',
        ] + $audit);
        $db->table('tractivity')->insert([
            'refnbr' => $sia->sianbr, 'doctype' => 'SIA', 'siteid' => $sia->siteid, 'name' => $user->name,
            'activitytype' => 'Upload Attachment', 'activity' => "Upload Attachment Surat Izin Acara {$sia->sianbr}",
            'notes' => "$name.$ext", 'status' => 'A',
        ] + $audit);

        return response()->json(['attachments' => $this->siaPayload($sia)['attachments']]);
    }

    /** Signed, short-lived link to one SIA attachment. */
    public function siaAttachment(string $aeid)
    {
        $this->authorizeSia();
        $id = Hashids::decode($aeid)[0] ?? null;
        abort_unless($id, 404);
        $att = $this->siaDb()->table('msattachment')->where('id', $id)->where('doctype', 'SIA')->where('status', 'A')->first();
        abort_unless($att, 404);
        abort_unless($this->siaQuery()->where('s.sianbr', $att->refnbr)->exists(), 404);

        $object = $this->tenantAttachmentObject($att, 'SIA');
        $url = $this->gcsSignedUrl($object, 15);
        abort_unless($url, 404);

        return redirect()->away($url);
    }

    /** Same notifications as the legacy app. Never blocks the approval if mail fails. */
    private function siaSendMail(string $kind, string $usernames, $sia, string $notes = ''): void
    {
        try {
            $subjects = ['waiting' => 'Waiting Your Approval', 'revise' => 'is Revised, Please Review', 'reject' => 'is Rejected', 'complete' => 'is Completed'];
            $users = $this->siaDb()->table('user')->whereIn('username', array_map('trim', explode(',', $usernames)))
                ->where('status', 'A')->get(['name', 'email']);
            if ($users->isEmpty()) return;

            $pdf = null;
            if ($kind === 'complete') {
                $d = $this->siaShow(Hashids::encode($sia->id))->getData(true);
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.tenancy.pdf.sia', ['d' => $d])->setPaper('a4')->output();
            }

            foreach ($users as $u) {
                if (! $u->email) continue;
                Mail::send('pages.tenancy.mail.sia', [
                    'kind' => $kind, 'name' => $u->name, 'sianbr' => $sia->sianbr, 'date' => $this->siaNow(),
                    'site' => $sia->siteid, 'notes' => $notes, 'link' => route('tenantsupport.sia.show', Hashids::encode($sia->id)),
                ], function ($m) use ($u, $sia, $kind, $subjects, $pdf) {
                    $m->to($u->email, $u->name)->subject("{$sia->siteid} - {$sia->sianbr} - {$subjects[$kind]}");
                    if ($pdf) $m->attachData($pdf, $sia->sianbr . '.pdf');
                });
            }
        } catch (\Throwable $e) {
            Log::warning('SIA mail failed: ' . $e->getMessage());
        }
    }
}
