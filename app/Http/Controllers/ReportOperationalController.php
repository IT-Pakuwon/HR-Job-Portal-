<?php

namespace App\Http\Controllers;

use App\Models\MsDepartment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Vinkla\Hashids\Facades\Hashids;

class ReportOperationalController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        abort_unless(
            $user->hasRole('COSTCTRLACCESS') || $user->hasRole('OPRACCESS'),
            403
        );

        return view('pages.report-operational.index');
    }

    /*
    |------------------------------------------------
    | Base Query
    |------------------------------------------------
    */
    private function operationalQuery()
    {
        return DB::connection('pgsql')
            ->table('tr_wo as w')
            ->where('w.status', 'C')

            ->select([
            DB::raw('id'),
                'w.woid',
                'w.wodate',
                'w.department_id',
                'w.picrequester',
                'w.pic_wo',
                'w.created_by',
                'w.status',
                'w.status_pekerjaan',
                'w.keperluan',
                'w.biaya_wo',
                'w.completed_at',
                'w.created_at',
                'w.cpny_id',

                'w.pic_department',
                'w.budget_department_fin_id',
                'w.budget_account_id',
                'w.budget_activity_id',
                'w.budget_activity_descr',
                'w.budget_use',

                DB::raw('(SELECT COUNT(*) FROM tr_spb WHERE woid = w.woid) as spb_count'),

                DB::raw('(
                    SELECT COUNT(*) FROM (
                        SELECT woid FROM tr_sppb
                        UNION ALL
                        SELECT woid FROM tr_sppj
                        UNION ALL
                        SELECT woid FROM tr_sppt
                    ) x WHERE x.woid = w.woid
                ) as sppbjkt_count'),

                DB::raw("(
                    SELECT STRING_AGG(spbid, ', ')
                    FROM tr_spb
                    WHERE woid = w.woid
                ) as spb_list"),

                DB::raw("(
                    SELECT STRING_AGG(doc, ', ')
                    FROM (
                        SELECT sppbid as doc FROM tr_sppb WHERE woid = w.woid
                        UNION ALL
                        SELECT sppjid as doc FROM tr_sppj WHERE woid = w.woid
                        UNION ALL
                        SELECT spptid as doc FROM tr_sppt WHERE woid = w.woid
                    ) x
                ) as sppbjkt_list"),
            ]);
    }

    /*
    |------------------------------------------------
    | Filters
    |------------------------------------------------
    */
    private function applyFilters($query, Request $request)
    {
        if ($request->date_from) {
            $query->whereDate('w.wodate', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('w.wodate', '<=', $request->date_to);
        }

        if ($request->woid) {
            $query->where('w.woid', 'ilike', "%{$request->woid}%");
        }

        if ($request->department) {
            $query->where('w.department_id', $request->department); // ✅ FIXED
        }

        return $query;
    }

    /*
    |------------------------------------------------
    | User Scope (COSTCTRL vs OPR)
    |------------------------------------------------
    */
    private function applyUserScope($query)
    {
        $user = auth()->user();

        $isCostCtrl = $user->hasRole('COSTCTRLACCESS');
        $isOpr = $user->hasRole('OPRACCESS');

        /*
        |--------------------------------------------
        | Company Scope (always applied)
        |--------------------------------------------
        */
        $companyIds = \App\Models\Usercpny::where('username', $user->username)
            ->pluck('cpny_id');

        $query->whereIn('w.cpny_id', $companyIds);

        /*
        |--------------------------------------------
        | COST CONTROL → FULL ACCESS
        |--------------------------------------------
        */
        if ($isCostCtrl) {
            return $query;
        }

        /*
        |--------------------------------------------
        | OPERATION → ONLY INVOLVED WO
        |--------------------------------------------
        */
        if ($isOpr) {
            $query->where(function ($q) use ($user) {
                $q->where('w.pic_wo', $user->username)
                  ->orWhere('w.picrequester', $user->username)
                  ->orWhere('w.created_by', $user->username); // optional but recommended
            });
        }

        return $query;
    }

    /*
    |------------------------------------------------
    | DataTables JSON
    |------------------------------------------------
    */
    public function json(Request $request)
    {
        $user = Auth::user();

        abort_unless(
            $user->hasRole('COSTCTRLACCESS') || $user->hasRole('OPRACCESS'),
            403
        );

        $query = $this->applyFilters(
            $this->operationalQuery(),
            $request
        );

        // ✅ IMPORTANT
        $query = $this->applyUserScope($query);

        $users = User::pluck('name', 'username');

        $departments = MsDepartment::pluck('department_name', 'department_id');

        $budgetDepartments = DB::connection('pgsql2')
            ->table('ms_department')
            ->pluck('department_name', 'department_fin_id');

        $budgetDetails = \App\Models\BudgetDetail::select(
            'department_fin_id',
            'account_id',
            'activity_id',
            'activity_descr'
        )->get();

        return DataTables::of($query)

            ->addColumn('date', fn ($row) => $row->wodate
                    ? Carbon::parse($row->wodate)->format('d-M-Y')
                    : ''
            )

            ->addColumn('woid_eid', function ($row) {
                return Hashids::encode($row->id);
            })

            ->addColumn('department_name', fn ($row) => $departments[$row->department_id] ?? $row->department_id
            )

            ->addColumn('pic_department_name', fn ($row) => $users[$row->pic_department] ?? $row->pic_department
            )

            ->addColumn('requester', fn ($row) => $users[$row->picrequester] ?? $row->picrequester
            )

            ->addColumn('pic_wo_name', fn ($row) => $users[$row->pic_wo] ?? $row->pic_wo
            )

            ->addColumn('duration', function ($row) {
                return Carbon::parse($row->created_at)
                    ->diffInDays($row->completed_at ?? now()); // ✅ FIXED
            })

            ->addColumn('budget_info', function ($row) {
                return [
                    'dept' => $row->budget_department_fin_id ?? '-',
                    'account' => $row->budget_account_id ?? '-',
                    'activity' => $row->budget_activity_descr ?? $row->budget_activity_id ?? '-',
                ];
            })

            ->addColumn('budget_user', function ($row) use ($users) {
                return $users[$row->budget_use] ?? $row->budget_use ?? '-';
            })

            ->addColumn('doc_status', function ($row) {
                return match ($row->status) {
                    'P' => 'Pending',
                    'C' => 'Completed',
                    'X' => 'Cancelled',
                    'R' => 'Rejected',
                    default => $row->status,
                };
            })

            ->addColumn('work_status', function ($row) {
                return match ($row->status_pekerjaan) {
                    'P' => 'Progress',
                    'H' => 'On Hold',
                    'C' => 'Done',
                    'X' => 'Cancelled',
                    default => $row->status_pekerjaan,
                };
            })

            ->make(true);
    }

    public function export(Request $request)
    {
        $query = $this->applyFilters(
            $this->operationalQuery(),
            $request
        );

        $query = $this->applyUserScope($query);

        $rows = $query->get();

        $users = User::pluck('name', 'username');
        $departments = MsDepartment::pluck('department_name', 'department_id');

        $rows = $rows->map(function ($row) use ($users, $departments) {
            return [
                'WO No' => $row->woid,

                'Date' => $row->wodate
                    ? Carbon::parse($row->wodate)->format('Y-m-d')
                    : '',

                'Department' => $departments[$row->department_id] ?? $row->department_id,

                'Requester' => $users[$row->picrequester] ?? $row->picrequester,

                'PIC WO' => $users[$row->pic_wo] ?? $row->pic_wo,

                'PIC Department' => $users[$row->pic_department] ?? $row->pic_department,

                'Document Status' => match ($row->status) {
                    'P' => 'Pending',
                    'C' => 'Completed',
                    'X' => 'Cancelled',
                    'R' => 'Rejected',
                    default => $row->status,
                },

                'WO Status' => match ($row->status_pekerjaan) {
                    'P' => 'Progress',
                    'H' => 'On Hold',
                    'C' => 'Done',
                    'X' => 'Cancelled',
                    default => $row->status_pekerjaan,
                },

                // ✅ SPB LIST
                'SPB' => $row->spb_list ?? '',

                'SPPB' => collect(explode(',', $row->sppbjkt_list ?? ''))
                    ->map(fn ($x) => trim($x))
                    ->filter(fn ($x) => str_starts_with($x, 'SPPB'))
                    ->implode(', '),

                'SPPJ' => collect(explode(',', $row->sppbjkt_list ?? ''))
                    ->map(fn ($x) => trim($x))
                    ->filter(fn ($x) => str_starts_with($x, 'SPPJ'))
                    ->implode(', '),

                'SPPT' => collect(explode(',', $row->sppbjkt_list ?? ''))
                    ->map(fn ($x) => trim($x))
                    ->filter(fn ($x) => str_starts_with($x, 'SPPT'))
                    ->implode(', '),

                // ✅ SPLIT BUDGET
                'Budget Department' => $row->budget_department_fin_id ?? '',
                'Account' => $row->budget_account_id ?? '',
                'Activity' => $row->budget_activity_descr ?? $row->budget_activity_id ?? '',

                // // OPTIONAL
                // 'Cost' => number_format($row->biaya_wo ?? 0, 2, '.', ''),

                'Description' => $row->keperluan ?? '',
            ];
        });

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ArrayExport($rows),
            'report_operational.xlsx'
        );
    }
}
