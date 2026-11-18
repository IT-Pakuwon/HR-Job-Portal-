<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Vinkla\Hashids\Facades\Hashids;

/** Checker TS: scan the QR on a SIK / SIMKB permit PDF and log Check In / Out at Posko FCC. */
class TenantSupportCheckerController extends Controller
{
    private const TYPES = [
        'SIK'   => ['table' => 'viewtrsik',   'nbr' => 'siknbr',   'route' => 'tenantsupport.sik.verify'],
        'SIMKB' => ['table' => 'viewtrsimkb', 'nbr' => 'simkbnbr', 'route' => 'tenantsupport.simkb.verify'],
    ];

    private function authorizeChecker(): void
    {
        $user = Auth::user();
        // ACCESSCHECKERTS role, or a legacy tenancy user of type CHECKER (same username), or admin.
        $ok = $user && ($user->hasRole('ACCESSCHECKERTS') || $user->isPrimaryAdmin()
            || DB::connection('mysql5')->table('user')->where('username', $user->username)->where('usertype', 'CHECKER')->where('status', 'A')->exists());
        abort_unless($ok, 403);
    }

    public function index()
    {
        $this->authorizeChecker();

        return view('pages.tenancy.checker');
    }

    /** Completed SIK + SIMKB (all tenants), with each permit's current check state. */
    public function listing(Request $request)
    {
        $this->authorizeChecker();

        $db = DB::connection('mysql5');
        $sel = fn ($tbl, $type, $nbr, $date) => $db->table($tbl)->where('status', 'C')->selectRaw(
            "'{$type}' as type, id, {$nbr} as number, {$date} as permit_date, sitename, companyname, storename, requestname, startdate, enddate, coalesce(extendenddate, enddate) as end_date");
        $q = $sel('viewtrsik', 'SIK', 'siknbr', 'sikdate')->unionAll($sel('viewtrsimkb', 'SIMKB', 'simkbnbr', 'simkbdate'));
        $u = $db->query()->fromSub($q, 'u');

        $years = (clone $u)->selectRaw('distinct year(permit_date) y')->orderByDesc('y')->pluck('y')->map(fn ($y) => (int) $y)->all();
        $cur = (int) now('Asia/Jakarta')->year;
        if (! in_array($cur, $years)) array_unshift($years, $cur);

        if ($request->filled('year')) $u->whereBetween('permit_date', [$request->year . '-01-01 00:00:00', $request->year . '-12-31 23:59:59']);
        if (in_array($request->type, ['SIK', 'SIMKB'])) $u->where('type', $request->type);
        if ($request->boolean('today')) { $d = now('Asia/Jakarta')->toDateString(); $u->where('startdate', '<=', $d)->where('end_date', '>=', $d); }
        if ($request->filled('search')) {
            $t = '%' . trim($request->search) . '%';
            $u->where(fn ($w) => $w->where('number', 'like', $t)->orWhere('storename', 'like', $t)->orWhere('companyname', 'like', $t)->orWhere('requestname', 'like', $t));
        }

        $total = (clone $u)->count();
        $rows  = $u->orderByDesc('permit_date')->orderByDesc('id')->forPage(max((int) $request->input('page', 1), 1), 10)->get();

        // Latest check action per permit on this page.
        $last = [];
        foreach ($rows->groupBy('type') as $type => $g) {
            foreach (DB::connection('mysql5')->table('tr_ts_checkinout')->where('doctype', $type)->whereIn('ref_id', $g->pluck('id'))
                ->orderBy('checked_at')->orderBy('id')->get() as $l) $last[$type . $l->ref_id] = $l;
        }
        $today = now('Asia/Jakarta')->toDateString();

        return response()->json(['total' => $total, 'per_page' => 10, 'years' => $years, 'data' => $rows->map(function ($r) use ($last, $today) {
            $l = $last[$r->type . $r->id] ?? null;
            return [
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

        return response()->json($this->payload($permit['type'], $permit['row']));
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

        $last  = $this->log($type, $row->id)->last();
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
            'doctype' => $type, 'ref_id' => $row->id, 'ref_nbr' => $nbr, 'action' => $request->action,
            'checked_by' => Auth::user()->username, 'checked_at' => now('Asia/Jakarta')->format('Y-m-d H:i:s'),
            'notes' => $request->input('notes') ?: null,
        ]);

        return response()->json($this->payload($type, $row));
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
        if ($code === '' || ! preg_match('#/verify/tenant-(sik|simkb)/([A-Za-z0-9]+)#i', $code, $m)) {
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

    private function payload(string $type, $r): array
    {
        $nbr = $r->{self::TYPES[$type]['nbr']};
        $log = $this->log($type, $r->id);
        $last = $log->last();
        $today = now('Asia/Jakarta')->toDateString();
        $start = $r->startdate;
        $end   = max($r->enddate, $r->extendenddate ?? '');

        return [
            'type' => $type, 'number' => $nbr, 'status' => $r->status, 'approved' => $r->status === 'C',
            'in_period' => $today >= $start && $today <= $end,
            'site' => $r->sitename, 'company' => $r->companyname, 'store' => $r->storename,
            'requester' => $r->requestname, 'start' => $start, 'end' => $end, 'workhour' => $r->workhour,
            // IN = inside now; STALE = checked in on an earlier day and never checked out (new day may Check In again).
            'state' => ! $last ? 'NONE' : ($last->action === 'OUT' ? 'OUT' : (substr($last->checked_at, 0, 10) === $today ? 'IN' : 'STALE')),
            'log' => $log->map(fn ($l) => ['action' => $l->action, 'at' => substr($l->checked_at, 0, 16), 'by' => $l->checked_by, 'notes' => $l->notes])->values(),
        ];
    }

    /** Check In / Out lines printed in the permit's Posko FCC box. */
    public static function permitLog(string $type, $refId)
    {
        try {
            return DB::connection('mysql5')->table('tr_ts_checkinout')->where('doctype', $type)->where('ref_id', $refId)
                ->orderBy('checked_at')->orderBy('id')->get();
        } catch (\Throwable $e) {
            return collect();
        }
    }
}
