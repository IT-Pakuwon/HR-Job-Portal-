<?php

namespace App\Http\Controllers;

use App\Models\TrFinding;
use App\Models\Usercpny;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FindingController extends Controller
{
    public function index()
    {
        $username = Auth::user()->username;
        $companies = $this->companies($username);
        $baseQuery = TrFinding::query()->whereIn('cpny_id', $companies);

        $allFinding = (clone $baseQuery)->count();
        $myFinding = (clone $baseQuery)->where('created_by', $username)->count();
        $openFinding = (clone $baseQuery)->where(fn (Builder $query) => $this->open($query))->count();
        $closeFinding = (clone $baseQuery)->where(fn (Builder $query) => $this->closed($query))->count();

        return view('pages.finding.finding', compact(
            'companies',
            'allFinding',
            'myFinding',
            'openFinding',
            'closeFinding'
        ));
    }

    public function json(Request $request)
    {
        $username = Auth::user()->username;
        $companies = $this->companies($username);
        $companyId = trim((string) $request->input('cpny_id', ''));
        $filter = strtolower((string) $request->input('filter', 'my'));
        $draw = (int) $request->input('draw', 1);
        $start = max((int) $request->input('start', 0), 0);
        $length = min(max((int) $request->input('length', 25), 1), 100);
        $search = trim((string) $request->input('search.value', ''));

        $query = TrFinding::query()->whereIn('cpny_id', $companies);

        if ($companyId !== '') {
            abort_unless($companies->contains($companyId), 403);
            $query->where('cpny_id', $companyId);
        }

        if ($filter === 'my') {
            $query->where('created_by', $username);
        } elseif ($filter === 'open') {
            $query->where(fn (Builder $subQuery) => $this->open($subQuery));
        } elseif (in_array($filter, ['close', 'closed'], true)) {
            $query->where(fn (Builder $subQuery) => $this->closed($subQuery));
        }

        $recordsTotal = (clone $query)->count();

        if ($search !== '') {
            $query->where(function (Builder $subQuery) use ($search) {
                $subQuery->where('finding_id', 'ilike', "%{$search}%")
                    ->orWhere('cpny_id', 'ilike', "%{$search}%")
                    ->orWhere('department_id', 'ilike', "%{$search}%")
                    ->orWhere('location_id', 'ilike', "%{$search}%")
                    ->orWhere('finding_category', 'ilike', "%{$search}%")
                    ->orWhere('finding_item', 'ilike', "%{$search}%")
                    ->orWhere('finding_subitem', 'ilike', "%{$search}%")
                    ->orWhere('issue', 'ilike', "%{$search}%")
                    ->orWhere('solution', 'ilike', "%{$search}%")
                    ->orWhere('created_by', 'ilike', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $query)->count();
        $columns = [
            0 => 'finding_date',
            1 => 'finding_id',
            2 => 'cpny_id',
            3 => 'department_id',
            4 => 'location_id',
            5 => 'finding_category',
            6 => 'finding_item',
            7 => 'issue',
            8 => 'status',
            9 => 'created_by',
        ];
        $orderColumn = $columns[(int) $request->input('order.0.column', 0)] ?? 'finding_date';
        $orderDirection = strtolower((string) $request->input('order.0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $data = $query->orderBy($orderColumn, $orderDirection)
            ->orderByDesc('id')
            ->skip($start)
            ->take($length)
            ->get([
                'id', 'finding_id', 'finding_date', 'cpny_id', 'department_id',
                'location_id', 'sub_location_id', 'finding_category', 'finding_item',
                'finding_subitem', 'issue', 'solution', 'user_solution', 'status',
                'created_by', 'completed_by', 'completed_at',
            ])
            ->map(function (TrFinding $finding) use ($username) {
                $finding->finding_date_label = $finding->finding_date?->format('d M Y');
                $finding->status_label = $finding->completed_at || strtoupper((string) $finding->status) === 'C'
                    ? 'Close'
                    : 'Open';
                $finding->is_mine = $finding->created_by === $username;

                return $finding;
            });

        return response()->json(compact('draw', 'recordsTotal', 'recordsFiltered', 'data'));
    }

    private function companies(string $username)
    {
        return Usercpny::query()
            ->where('username', $username)
            ->where('status', 'A')
            ->orderBy('cpny_id')
            ->pluck('cpny_id')
            ->unique()
            ->values();
    }

    private function closed(Builder $query): Builder
    {
        return $query->where(function (Builder $subQuery) {
            $subQuery->where('status', 'C')->orWhereNotNull('completed_at');
        });
    }

    private function open(Builder $query): Builder
    {
        return $query->whereNull('completed_at')
            ->where(function (Builder $subQuery) {
                $subQuery->whereNull('status')->orWhere('status', '<>', 'C');
            });
    }
}
