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

class TenantSupportSimkbController extends Controller
{
    use UploadsToGcs;
    use \App\Http\Controllers\Traits\TenantAttachmentRules;

    /** SIMKB status letters in trsimkb.status */
    private const SIMKB_STATUS = ['P' => 'On Progress', 'C' => 'Completed', 'H' => 'Hold / Revise', 'E' => 'Extend Pending', 'R' => 'Rejected', 'X' => 'Cancelled'];

    private function authorizeSimkb(): void
    {
        $user = Auth::user();
        abort_unless($user && ($user->hasRole('ACCESSTS') || $user->isPrimaryAdmin()), 403);
    }

    /** SIMKB created by tenant users (msusertenant). */
    private function simkbQuery()
    {
        return DB::connection('mysql5')->table('viewtrsimkb as s')
            ->whereExists(fn ($q) => $q->select(DB::raw(1))->from('msusertenant as u')
                ->whereColumn('u.username', 's.created_user'));
    }

    /**
     * /tenant-support/simkb/{eid}: every SIMKB has its own link (same hashed-id pattern as the other modules).
     * A browser visit opens the list with that SIMKB's popup already open; the popup's own fetch asks for JSON.
     */
    public function simkbPage(Request $request, string $eid)
    {
        if ($request->expectsJson()) {
            return $this->simkbShow($eid);
        }

        $this->authorizeSimkb();
        $this->simkbFindOrFail($eid);   // 404 for an unknown / non-tenant SIMKB

        return $this->simkb($eid);
    }

    public function simkb(?string $openEid = null)
    {
        $this->authorizeSimkb();

        $years = $this->simkbQuery()->selectRaw('distinct year(s.simkbdate) y')->orderByDesc('y')->pluck('y')->all();
        if (! in_array((int) now()->year, $years)) array_unshift($years, (int) now()->year);

        return view('pages.tenancy.simkb', [
            'openEid'  => $openEid,
            'years'    => $years,
            'statuses' => self::SIMKB_STATUS,
            'sites'    => DB::connection('mysql5')->table('mssite')->orderBy('siteid')->get(['siteid', 'sitename']),
        ]);
    }

    /** Export the current filtered list (all pages) as xlsx or csv. */
    public function simkbExport(Request $request)
    {
        $this->authorizeSimkb();

        $q = $this->simkbFiltered($request);
        if ($request->filled('status') && $request->status !== 'ALL') $q->where('s.status', $request->status);

        $rows = $this->simkbSorted($q, $request)->limit(50000)->get()->map(fn ($r) => [
            $r->simkbnbr, substr($r->simkbdate, 0, 10), $r->sitename, $r->companyname, $r->locationname, $r->storename,
            trim($r->floor . ' ' . $r->unit), $r->requestname, $r->simkbtypeid, $r->entranceexitgate, $r->requestpenanggungjawab, $r->requestphone, $r->requestemail,
            $r->startdate, $r->enddate, (int) $r->totalextend, $r->workhour, $r->workdescription, $r->workerdescription,
            $r->created_user, self::SIMKB_STATUS[$r->status] ?? $r->status,
        ]);

        $export = new class($rows) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            public function __construct(private $rows) {}
            public function collection() { return $this->rows; }
            public function headings(): array
            {
                return ['SIMKB No', 'Date', 'Site', 'Company', 'Location', 'Tenant', 'Floor / Unit', 'Requester', 'Type', 'Gate', 'Person in Charge',
                    'Phone', 'Email', 'Start Date', 'End Date', 'Total Extend', 'Work Hour', 'Work Description', 'Workers', 'Created By', 'Status'];
            }
        };

        $csv = $request->input('format') === 'csv';

        return \Maatwebsite\Excel\Facades\Excel::download(
            $export,
            'SIMKB_' . now()->format('Ymd_His') . ($csv ? '.csv' : '.xlsx'),
            $csv ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX
        );
    }

    /** Whitelisted column sort; ties broken by newest first. */
    private function simkbSorted($q, Request $request)
    {
        $map = ['simkbnbr' => 's.simkbnbr', 'simkbdate' => 's.simkbdate', 'sitename' => 's.sitename', 'storename' => 's.storename',
            'requestname' => 's.requestname', 'startdate' => 's.startdate', 'created_user' => 's.created_user', 'status' => 's.status'];
        $col = $map[$request->input('sort')] ?? null;
        if ($col) $q->orderBy($col, $request->input('dir') === 'asc' ? 'asc' : 'desc');

        return $q->orderByDesc('s.id');
    }

    private function simkbFiltered(Request $request)
    {
        $q = $this->simkbQuery();
        if ($request->filled('year'))   $q->whereBetween('s.simkbdate', [$request->year . '-01-01 00:00:00', $request->year . '-12-31 23:59:59']);
        if ($request->filled('siteid')) $q->where('s.siteid', $request->siteid);
        if ($request->filled('from'))   $q->whereDate('s.simkbdate', '>=', $request->from);
        if ($request->filled('to'))     $q->whereDate('s.simkbdate', '<=', $request->to);
        if ($request->filled('search')) {
            $t = '%' . trim($request->search) . '%';
            $q->where(fn ($w) => $w->where('s.simkbnbr', 'like', $t)->orWhere('s.storename', 'like', $t)
                ->orWhere('s.requestname', 'like', $t)->orWhere('s.created_user', 'like', $t));
        }

        return $q;
    }

    public function simkbJson(Request $request)
    {
        $this->authorizeSimkb();

        $request->validate(['year' => 'nullable|digits:4']);
        $q = $this->simkbFiltered($request);

        // Card counts ignore the status filter itself but respect scope, site, dates and search.
        $counts = (clone $q)->selectRaw('s.status, count(*) c')->groupBy('s.status')->pluck('c', 'status');
        $counts = ['ALL' => (int) $counts->sum()] + array_map(fn ($k) => (int) ($counts[$k] ?? 0), array_combine(array_keys(self::SIMKB_STATUS), array_keys(self::SIMKB_STATUS)));

        if ($request->filled('status') && $request->status !== 'ALL') $q->where('s.status', $request->status);

        $total   = (clone $q)->count();
        $perPage = min(max((int) $request->input('per_page', 15), 10), 100);
        $page    = max((int) $request->input('page', 1), 1);

        $rows = $this->simkbSorted($q, $request)->forPage($page, $perPage)->get([
            's.id', 's.simkbnbr', 's.simkbdate', 's.sitename', 's.companyname', 's.locationname', 's.storename', 's.floor', 's.unit',
            's.requestname', 's.simkbtypeid', 's.startdate', 's.enddate', 's.totalextend', 's.status', 's.created_user',
        ])->map(function ($r) {
            $r->eid = Hashids::encode($r->id);
            unset($r->id);
            $r->status_label = self::SIMKB_STATUS[$r->status] ?? $r->status;
            return $r;
        });

        return response()->json(['data' => $rows, 'counts' => $counts, 'total' => $total, 'page' => $page, 'per_page' => $perPage]);
    }


    // =====================================================================
    // SIMKB detail + actions (ported from the legacy tenant app's SIMKBController)
    // =====================================================================

    private function simkbDb()
    {
        return DB::connection('mysql5');
    }

    /** Legacy stores local (Jakarta) time. */
    private function simkbNow(): string
    {
        return now('Asia/Jakarta')->format('Y-m-d H:i:s');
    }

    /** Tenant-created SIMKB (from the view) by hashed id, or 404. */
    private function simkbFindOrFail(string $eid)
    {
        $id = Hashids::decode($eid)[0] ?? null;
        abort_unless($id, 404);
        $simkb = $this->simkbQuery()->where('s.id', $id)->first();
        abort_unless($simkb, 404);

        return $simkb;
    }

    /** The pending "Approve" step where $username is (one of) the current approver(s). */
    private function simkbPendingRow($simkb, string $username)
    {
        return $this->simkbDb()->table('trapproval')
            ->where('doctype', 'SIMKB')->where('refnbr', $simkb->simkbnbr)->where('siteid', $simkb->siteid)
            ->where('accesstype', 'Approve')->where('progressapproval', 1)->where('aprvresult', 'P')->where('status', 'A')
            ->whereRaw("FIND_IN_SET(?, REPLACE(username, ' ', '')) > 0", [$username])
            ->orderBy('nomor')->first();
    }

    /** Active users who can be added as an extra approver: internal users not already approvers on this SIMKB. */
    private function simkbApproverCandidates($simkb)
    {
        $taken = $this->simkbDb()->table('trapproval')->where('doctype', 'SIMKB')->where('refnbr', $simkb->simkbnbr)->where('siteid', $simkb->siteid)
            ->where('status', 'A')->where('progressapproval', '!=', 3)->pluck('username')
            ->flatMap(fn ($u) => array_map(fn ($x) => strtolower(trim($x)), explode(',', (string) $u)))->all();

        // Department name (msdepartment has one row per site, so collapse to one name per departmentid); the SIMKB's own department comes first.
        $depts = $this->simkbDb()->table('msdepartment')->where('status', 'A')->selectRaw('departmentid, min(departmentname) as departmentname')->groupBy('departmentid');

        return $this->simkbDb()->table('user as u')->leftJoinSub($depts, 'd', 'd.departmentid', '=', 'u.departmentid')
            ->where('u.status', 'A')->whereIn('u.usertype', ['SUPERADMIN', 'ADMIN', 'USER'])->where('u.is_admin', '!=', 0)
            // Active users whose own site is this SIMKB's site (not just people who have access to it).
            ->where('u.siteid', $simkb->siteid)
            ->orderByRaw('(u.departmentid = ?) desc', [$simkb->departmentid])->orderByRaw("coalesce(d.departmentname, u.departmentid, 'zzz')")->orderBy('u.name')
            ->get(['u.username', 'u.name', DB::raw("coalesce(d.departmentname, nullif(u.departmentid, '')) as department"), DB::raw('(u.departmentid = ' . $this->simkbDb()->getPdo()->quote($simkb->departmentid) . ') as same_dept')])
            ->reject(fn ($u) => in_array(strtolower($u->username), $taken, true))->values();
    }

    /** Only the user who created the SIMKB may add attachments (legacy status rules kept; approvers no longer can). */
    private function simkbCanUpload($simkb, string $username): bool
    {
        if ($simkb->created_user !== $username) {
            return false;
        }

        $steps = fn () => $this->simkbDb()->table('trapproval')->where('doctype', 'SIMKB')
            ->where('refnbr', $simkb->simkbnbr)->where('siteid', $simkb->siteid);

        $pending = $steps()->where('aprvresult', 'P')->where('progressapproval', 1)->count() > 0;
        $decided = $steps()->whereIn('aprvresult', ['A', 'R'])->where('progressapproval', 2)->count() > 0;

        return in_array($simkb->status, ['H', 'E'])
            || ($simkb->status === 'P' && $pending && ! $decided);
    }

    /** People who can be @mentioned on a SIMKB: the creator (tenant user) and every approver in the flow, minus the current user. */
    private function simkbMentionable($simkb, $approvals): array
    {
        $me = strtolower((string) Auth::user()?->username);

        $names = [];
        $tenantName = $this->simkbDb()->table('msusertenant')->where('username', $simkb->created_user)->value('name');
        $names[strtolower($simkb->created_user)] = ['username' => $simkb->created_user, 'name' => $tenantName ?: $simkb->created_user, 'role' => 'Creator'];

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

    /** approvals / attachments / messages / reason for one SIMKB. */
    private function simkbPayload($simkb): array
    {
        $db = $this->simkbDb();
        $steps = fn () => $db->table('trapproval')->where('doctype', 'SIMKB')
            ->where('refnbr', $simkb->simkbnbr)->where('siteid', $simkb->siteid)->where('status', 'A')->where('progressapproval', '!=', 3);
        $cols = ['username', 'accesstype', 'conditiontype', 'created_user', 'urutan', 'aprvresult', 'progressapproval', 'aprvdateafter'];

        // Same selection as the legacy print: latest Submit + the live approval steps (no cancelled "L" rows).
        $submit = $steps()->where('aprvresult', 'S')->orderByDesc('id')->first($cols);
        $approvals = collect($submit ? [$submit] : [])->concat(
            $steps()->whereNotIn('aprvresult', ['L', 'S'])->orderBy('nomor')->orderBy('urutan')->get($cols)
        )->values();

        $attachments = $db->table('msattachment')->where('doctype', 'SIMKB')->where('refnbr', $simkb->simkbnbr)->where('status', 'A')
            ->orderBy('id')->get(['id', 'name', 'filename', 'fileextension', 'created_user', 'created_datetime'])
            ->map(function ($a) {
                $a->url = route('tenantsupport.simkb.attachment', Hashids::encode($a->id));
                unset($a->id);

                return $a;
            });

        $messages = $db->table('trmessage')->where('doctype', 'SIMKB')->where('refnbr', $simkb->simkbnbr)->where('status', 'A')
            ->orderBy('id')->get(['name', 'messagetype', 'message', 'created_user', 'created_datetime']);

        // Reject / Revise reason only matters while the SIMKB is in that state.
        $reason = in_array($simkb->status, ['H', 'R'])
            ? $messages->whereIn('messagetype', ['Reject', 'Revise'])->last()?->message
            : null;

        return ['approvals' => $approvals, 'attachments' => $attachments, 'messages' => $messages, 'reason' => $reason, 'mentionable' => $this->simkbMentionable($simkb, $approvals)];
    }

    public function simkbShow(string $eid)
    {
        $this->authorizeSimkb();
        $simkb = $this->simkbFindOrFail($eid);
        $me  = Auth::user()->username;

        $payload = $this->simkbPayload($simkb);
        $can = [
            'approve' => in_array($simkb->status, ['P', 'E']) && $this->simkbPendingRow($simkb, $me) !== null,
            'upload'  => $this->simkbCanUpload($simkb, $me),
            // Only the approver whose turn it is may add another approver (as a sub-level), and only while the SIMKB is On Progress.
            'extend'  => $simkb->status === 'P' && $this->simkbPendingRow($simkb, $me) !== null,
        ];

        unset($simkb->id);
        $simkb->status_label = self::SIMKB_STATUS[$simkb->status] ?? $simkb->status;

        return response()->json(['data' => $simkb, 'can' => $can] + $payload);
    }

    /** SIMKB Permit (Surat Ijin Masuk Barang) with signatures and a QR to the public verify page; ported from WPS SimkbController::print. */
    public function simkbPermit(string $eid)
    {
        $this->authorizeSimkb();
        $simkb = $this->simkbFindOrFail($eid);
        $db    = $this->simkbDb();

        // the view has no workdescription_keluar column
        $simkb->workdescription_keluar = $db->table('trsimkb')->where('id', $simkb->id)->value('workdescription_keluar');

        // Live approval steps only (cancelled "L" rows and rounds voided by a revise are left out).
        $trail = $db->table('trapproval')->where('doctype', 'SIMKB')->where('refnbr', $simkb->simkbnbr)->where('siteid', $simkb->siteid)
            ->where('status', 'A')->where('progressapproval', '!=', 3)->orderBy('nomor')->get();

        $verifyUrl = URL::signedRoute('tenantsupport.simkb.verify', ['eid' => $eid]);
        $qrSvg = (new \BaconQrCode\Writer(new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(120, 0), new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        )))->writeString($verifyUrl);

        $notes = $db->table('msprintnote')->where('doctype', 'SIMKB')->where('siteid', $simkb->siteid)
            ->whereIn('departmentid', ['ALL', $simkb->departmentid])->where('status', 'A')->orderBy('nomor')->get();

        $usernames = $trail->pluck('username')->flatMap(fn ($u) => explode(',', (string) $u))
            ->merge($trail->pluck('lastupdate_user'))->push($simkb->created_user)
            ->map(fn ($u) => trim((string) $u))->filter()->unique()->values()->all();

        $gateName = $db->table('msentranceexitgate')->where('doctype', 'SIMKB')->where('siteid', $simkb->siteid)
            ->where('entranceexitgateid', $simkb->gateid)->value('entranceexitgate');

        return Pdf::loadView('pages.tenancy.pdf.simkb-permit', [
            'simkb'           => $simkb,
            'trail'           => $trail,
            'site'            => $db->table('mssite')->where('siteid', $simkb->siteid)->first(),
            'notes'           => $notes,
            'workHourNote'    => $db->table('msworkhour')->where('doctype', 'SIMKB')->where('siteid', $simkb->siteid)->where('workhour', $simkb->workhour)->value('workhournote'),
            'gateName'        => $gateName ?: ($simkb->entranceexitgate ?: $simkb->gateid),
            'users'           => $db->table('user')->whereIn('username', $usernames)->get(['username', 'name', 'departmentid'])->keyBy('username'),
            'departmentNames' => $db->table('msdepartment')->where('siteid', $simkb->siteid)->pluck('departmentname', 'departmentid'),
            'floorUnit'       => collect([$simkb->storename, $simkb->floor, $simkb->unit])->filter()->implode(' / '),
            'verifyUrl'       => $verifyUrl,
            'qrSvg'           => $qrSvg,
            'checkLog'        => TenantSupportCheckerController::permitLog('SIMKB', $simkb->id),
        ])->setPaper('a4')->stream("SIMKB-{$simkb->simkbnbr}.pdf");
    }

    /** Public page behind the permit's QR code (signed link, no login). */
    public function simkbVerify(string $eid)
    {
        $id    = Hashids::decode($eid)[0] ?? null;
        $simkb = $id ? $this->simkbQuery()->where('s.id', $id)->first() : null;
        abort_unless($simkb, 404);

        return view('pages.tenancy.verify-simkb', ['simkb' => $simkb, 'statusLabel' => self::SIMKB_STATUS[$simkb->status] ?? $simkb->status]);
    }

    /** PDF of one SIMKB (same data as the detail popup). */
    public function simkbPrint(string $eid)
    {
        $d = $this->simkbShow($eid)->getData(true);

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.tenancy.pdf.simkb', ['d' => $d])
            ->setPaper('a4')
            ->stream($d['data']['simkbnbr'] . '.pdf');
    }

    /** Approve (A) / Reject (R) / Revise (D). Reject and Revise need a reason. */
    public function simkbAction(Request $request, string $eid)
    {
        $this->authorizeSimkb();
        $in = $request->validate(['action' => 'required|in:A,R,D', 'notes' => 'nullable|string|max:1000']);
        $notes = trim($in['notes'] ?? '');
        if (in_array($in['action'], ['R', 'D']) && $notes === '') {
            return response()->json(['message' => 'Please fill the reason.'], 422);
        }

        $simkb  = $this->simkbFindOrFail($eid);
        $user = Auth::user();
        $now  = $this->simkbNow();
        $db   = $this->simkbDb();
        $mails = [];

        $error = $db->transaction(function () use ($in, $notes, $simkb, $user, $now, $db, &$mails) {
            $cur = $db->table('trsimkb')->where('simkbnbr', $simkb->simkbnbr)->where('siteid', $simkb->siteid)
                ->whereIn('status', ['P', 'E'])->lockForUpdate()->first();
            $mine = $cur ? $this->simkbPendingRow($simkb, $user->username) : null;
            if (! $cur || ! $mine) return 'This SIMKB is not waiting for your approval.';

            $steps = fn () => $db->table('trapproval')->where('doctype', 'SIMKB')
                ->where('refnbr', $simkb->simkbnbr)->where('siteid', $simkb->siteid)->where('status', 'A');
            $audit = ['lastupdate_user' => $user->username, 'lastupdate_datetime' => $now];

            $db->table('trapproval')->where('id', $mine->id)->update([
                'progressapproval' => 2, 'aprvresult' => $in['action'], 'username' => $user->username, 'aprvdateafter' => $now,
            ] + $audit);

            $log = fn (string $type, string $text, string $n) => $db->table('tractivity')->insert([
                'refnbr' => $simkb->simkbnbr, 'doctype' => 'SIMKB', 'siteid' => $simkb->siteid, 'name' => $user->name,
                'activitytype' => $type, 'activity' => "$type Surat Izin Masuk Keluar Barang {$simkb->simkbnbr}", 'notes' => $n, 'status' => 'A',
                'created_user' => $user->username, 'created_datetime' => $now,
            ] + $audit);
            $chat = fn (string $type, string $text) => $db->table('trmessage')->insert([
                'refnbr' => $simkb->simkbnbr, 'doctype' => 'SIMKB', 'siteid' => $simkb->siteid, 'name' => $user->name,
                'messagetype' => $type, 'message' => $text, 'status' => 'A',
                'created_user' => $user->username, 'created_datetime' => $now,
            ] + $audit);

            if ($in['action'] === 'A') {
                $next = $steps()->where('aprvresult', 'P')->orderBy('nomor')->first();
                if (! $next) {
                    $db->table('trsimkb')->where('id', $cur->id)->update(['status' => 'C'] + $audit);
                    $mails[] = ['complete', $cur->created_user];
                } else {
                    $db->table('trapproval')->where('id', $next->id)->update(['progressapproval' => 1, 'aprvdatebefore' => $now]);
                    $mails[] = ['waiting', $next->username];
                }
                $log('Approve', '', '');
            } elseif ($in['action'] === 'R') {
                $db->table('trsimkb')->where('id', $cur->id)->update(['status' => 'R'] + $audit);
                $steps()->where('aprvresult', 'P')->where('progressapproval', 0)
                    ->update(['nomor' => 0, 'progressapproval' => 3, 'aprvresult' => 'L'] + $audit);
                $log('Reject', '', $notes);
                $chat('Reject', "{$simkb->simkbnbr} Rejected - $notes");
                $mails[] = ['reject', $cur->created_user];
            } else {
                $db->table('trsimkb')->where('id', $cur->id)->update(['status' => $cur->status === 'E' ? 'C' : 'H'] + $audit);
                $steps()->where('aprvresult', 'P')->update(['nomor' => 0, 'aprvresult' => 'L'] + $audit);
                $steps()->update(['progressapproval' => 3] + $audit);
                $log('Revise', '', $notes);
                $chat('Revise', "{$simkb->simkbnbr} Revised - $notes");
                $mails[] = ['revise', $cur->created_user];
            }

            return null;
        });

        if ($error) return response()->json(['message' => $error], 409);

        foreach ($mails as [$kind, $to]) $this->simkbSendMail($kind, $to, $simkb, $notes);

        return $this->simkbShow($eid);
    }

    /** Users who can still be added as an extra approver (only for the approver whose turn it is, SIMKB On Progress). */
    public function simkbApproverOptions(string $eid)
    {
        $this->authorizeSimkb();
        $simkb = $this->simkbFindOrFail($eid);
        abort_unless($simkb->status === 'P' && $this->simkbPendingRow($simkb, Auth::user()->username), 403);

        return response()->json(['data' => $this->simkbApproverCandidates($simkb)]);
    }

    /**
     * Extend the approval of an On Progress SIMKB: the approver who currently holds the step (level N) adds a user as
     * sub-level N.1, N.2, ... placed right after level N and before level N+1.
     */
    public function simkbAddApprover(Request $request, string $eid)
    {
        $this->authorizeSimkb();
        $in   = $request->validate(['username' => 'required|string|max:50', 'notes' => 'required|string|max:1000']);
        $simkb  = $this->simkbFindOrFail($eid);
        $user = Auth::user();

        if (! $this->simkbPendingRow($simkb, $user->username)) {
            return response()->json(['message' => 'Only the approver whose turn it is can add an approver.'], 403);
        }
        $new = $this->simkbApproverCandidates($simkb)->firstWhere('username', $in['username']);
        if (! $new) {
            return response()->json(['message' => 'That user cannot be added (not an active internal user, or already an approver on this SIMKB).'], 422);
        }

        $notes = trim($in['notes']);
        $now   = $this->simkbNow();
        $db    = $this->simkbDb();

        $error = $db->transaction(function () use ($new, $notes, $simkb, $user, $now, $db) {
            // Same lock the approve action takes, so a concurrent last approval can't slip past this check.
            $cur = $db->table('trsimkb')->where('simkbnbr', $simkb->simkbnbr)->where('siteid', $simkb->siteid)->where('status', 'P')->lockForUpdate()->first();
            if (! $cur) return 'This SIMKB is no longer On Progress, so an approver can not be added.';

            // Re-check under the lock that it is still this user's turn (they may have just approved).
            $mine = $this->simkbPendingRow($simkb, $user->username);
            if (! $mine) return 'It is no longer your turn to approve, so an approver can not be added.';

            $steps = fn () => $db->table('trapproval')->where('doctype', 'SIMKB')->where('refnbr', $simkb->simkbnbr)->where('siteid', $simkb->siteid)
                ->where('status', 'A')->where('progressapproval', '!=', 3);

            // Level N = the level of the step being held; its sub-levels are N.1 .. N.9 (urutan is decimal(3,1)).
            $level  = (int) floor((float) $mine->urutan);
            $inLvl  = fn () => $steps()->where('urutan', '>=', $level)->where('urutan', '<', $level + 1);
            $urutan = round((float) $inLvl()->max('urutan') + 0.1, 1);
            if ($urutan >= $level + 1) return "Level $level already has 9 added approvers.";

            // Place it after the last step of this level, shifting later steps down one slot (approval order follows nomor).
            $after = (int) $inLvl()->max('nomor');
            $db->table('trapproval')->where('doctype', 'SIMKB')->where('refnbr', $simkb->simkbnbr)->where('siteid', $simkb->siteid)
                ->where('nomor', '>', $after)->increment('nomor');

            $audit = ['lastupdate_user' => $user->username, 'lastupdate_datetime' => $now];
            $base  = ['refnbr' => $simkb->simkbnbr, 'doctype' => 'SIMKB', 'siteid' => $simkb->siteid, 'created_user' => $user->username, 'created_datetime' => $now] + $audit;
            $label = rtrim(rtrim(number_format($urutan, 1, '.', ''), '0'), '.');

            $db->table('trapproval')->insert($base + [
                'departmentid' => $cur->departmentid, 'nomor' => $after + 1, 'urutan' => $urutan, 'username' => $new->username,
                'accesstype' => 'Approve', 'conditiontype' => 'Extend', 'progressapproval' => 0, 'aprvresult' => 'P', 'aprvtotalday' => 0, 'status' => 'A',
            ]);
            $db->table('tractivity')->insert($base + [
                'name' => $user->name, 'activitytype' => 'Add Approver', 'activity' => "Add Approver Surat Izin Masuk Keluar Barang {$simkb->simkbnbr}",
                'notes' => "Level $label: {$new->name} ({$new->username}) - $notes", 'status' => 'A',
            ]);
            $db->table('trmessage')->insert($base + [
                'name' => $user->name, 'messagetype' => 'Message', 'message' => "{$simkb->simkbnbr} Approver added at level $label: {$new->name} ({$new->username}) - $notes", 'status' => 'A',
            ]);

            return null;
        });

        if ($error) return response()->json(['message' => $error], 422);

        return $this->simkbShow($eid);
    }

    public function simkbMessage(Request $request, string $eid)
    {
        $this->authorizeSimkb();
        $in  = $request->validate(['message' => 'required|string|max:1000']);
        $simkb = $this->simkbFindOrFail($eid);
        $u   = Auth::user();
        $now = $this->simkbNow();

        $this->simkbDb()->table('trmessage')->insert([
            'refnbr' => $simkb->simkbnbr, 'doctype' => 'SIMKB', 'siteid' => $simkb->siteid, 'name' => $u->name,
            'messagetype' => 'Message', 'message' => trim($in['message']), 'status' => 'A',
            'created_user' => $u->username, 'created_datetime' => $now, 'lastupdate_user' => $u->username, 'lastupdate_datetime' => $now,
        ]);

        return response()->json(['messages' => $this->simkbPayload($simkb)['messages']]);
    }

    /** Upload to the same bucket path the legacy app uses: att-wps/<site>/SIMKB/<year>/<SIMKB no>/<random>.<ext> (max 5 MB, image/video) */
    public function simkbUpload(Request $request, string $eid)
    {
        $this->authorizeSimkb();
        $request->validate(['file' => 'required|file']);
        $simkb  = $this->simkbFindOrFail($eid);
        $user = Auth::user();

        if (! $this->simkbCanUpload($simkb, $user->username)) {
            return response()->json(['message' => 'Only the user who created this SIMKB can add an attachment, and only before approval has started.'], 422);
        }

        $file = $request->file('file');
        $ext  = strtolower($file->getClientOriginalExtension());
        $db   = $this->simkbDb();
        if ($err = $this->tenantUploadError($file)) {
            return response()->json(['message' => $err], 422);
        }

        $period = \Carbon\Carbon::parse($simkb->simkbdate)->year;
        $stored = Str::random(32) . '.' . $ext;
        $object = $this->tenantObjectPath($simkb->siteid, 'SIMKB', $period, $simkb->simkbnbr, $stored);

        $this->gcsClient()->bucket(config('filesystems.disks.gcs.bucket'))->upload(fopen($file->getRealPath(), 'r'), ['name' => $object]);

        $now = $this->simkbNow();
        $audit = ['created_user' => $user->username, 'created_datetime' => $now, 'lastupdate_user' => $user->username, 'lastupdate_datetime' => $now];
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $db->table('msattachment')->insert([
            'refnbr' => $simkb->simkbnbr, 'doctype' => 'SIMKB', 'siteid' => $simkb->siteid, 'period' => $period,
            'name' => $name, 'filename' => $stored, 'fileextension' => $ext, 'status' => 'A',
        ] + $audit);
        $db->table('tractivity')->insert([
            'refnbr' => $simkb->simkbnbr, 'doctype' => 'SIMKB', 'siteid' => $simkb->siteid, 'name' => $user->name,
            'activitytype' => 'Upload Attachment', 'activity' => "Upload Attachment Surat Izin Masuk Keluar Barang {$simkb->simkbnbr}",
            'notes' => "$name.$ext", 'status' => 'A',
        ] + $audit);

        return response()->json(['attachments' => $this->simkbPayload($simkb)['attachments']]);
    }

    /** Signed, short-lived link to one SIMKB attachment. */
    public function simkbAttachment(string $aeid)
    {
        $this->authorizeSimkb();
        $id = Hashids::decode($aeid)[0] ?? null;
        abort_unless($id, 404);
        $att = $this->simkbDb()->table('msattachment')->where('id', $id)->where('doctype', 'SIMKB')->where('status', 'A')->first();
        abort_unless($att, 404);
        abort_unless($this->simkbQuery()->where('s.simkbnbr', $att->refnbr)->exists(), 404);

        $object = $this->tenantAttachmentObject($att, 'SIMKB');
        $url = $this->gcsSignedUrl($object, 15);
        abort_unless($url, 404);

        return redirect()->away($url);
    }

    /** Same notifications as the legacy app. Never blocks the approval if mail fails. */
    private function simkbSendMail(string $kind, string $usernames, $simkb, string $notes = ''): void
    {
        try {
            $subjects = ['waiting' => 'Waiting Your Approval', 'revise' => 'is Revised, Please Review', 'reject' => 'is Rejected', 'complete' => 'is Completed'];
            $users = $this->simkbDb()->table('user')->whereIn('username', array_map('trim', explode(',', $usernames)))
                ->where('status', 'A')->get(['name', 'email']);
            if ($users->isEmpty()) return;

            $pdf = null;
            if ($kind === 'complete') {
                $d = $this->simkbShow(Hashids::encode($simkb->id))->getData(true);
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pages.tenancy.pdf.simkb', ['d' => $d])->setPaper('a4')->output();
            }

            foreach ($users as $u) {
                if (! $u->email) continue;
                Mail::send('pages.tenancy.mail.simkb', [
                    'kind' => $kind, 'name' => $u->name, 'simkbnbr' => $simkb->simkbnbr, 'date' => $this->simkbNow(),
                    'site' => $simkb->siteid, 'notes' => $notes, 'link' => route('tenantsupport.simkb.show', Hashids::encode($simkb->id)),
                ], function ($m) use ($u, $simkb, $kind, $subjects, $pdf) {
                    $m->to($u->email, $u->name)->subject("{$simkb->siteid} - {$simkb->simkbnbr} - {$subjects[$kind]}");
                    if ($pdf) $m->attachData($pdf, $simkb->simkbnbr . '.pdf');
                });
            }
        } catch (\Throwable $e) {
            Log::warning('SIMKB mail failed: ' . $e->getMessage());
        }
    }
}
