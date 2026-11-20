<?php

namespace App\Http\Controllers;

use App\Support\TenantRelationScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;

/** Complaint Log: every complaint raised by tenant users in WPS (legacy trcomplaint), read-only, for Tenant Relation (ACCESSTR). */
class TenantSupportComplaintController extends Controller
{
    private const STATUS = ['P' => 'Pending', 'O' => 'On Progress', 'C' => 'Completed', 'X' => 'Cancelled'];

    private function authorizeLog(): void
    {
        $user = Auth::user();
        abort_unless($user && ($user->hasRole('ACCESSTR') || $user->isPrimaryAdmin()), 403);
    }

    private function base()
    {
        $q = DB::connection('mysql5')->table('trcomplaint as c')
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
            'f.floor', 'k.name as category', 'c.description', 'c.action', 'c.status', 'c.created_user'];
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

        $rows = $q->orderByDesc('c.complaintdate')->orderByDesc('c.id')->forPage($page, $perPage)->get($this->cols())
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

        return response()->json(['data' => $this->shape($row)]);
    }
}
