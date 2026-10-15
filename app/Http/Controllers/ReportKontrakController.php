<?php

namespace App\Http\Controllers;

use App\Exports\KontrakReportExport;
use App\Models\TrKontrak;
use App\Models\TrRfp;
use App\Models\Usercpny;
use App\Models\Userdept;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;

class ReportKontrakController extends Controller
{
    public const MONTHS = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];

    public function index(Request $request)
    {
        $user = $request->user();
        $companies = $user->hasFullDataScope()
            ? TrKontrak::query()
            : Usercpny::where('username', $user->username);
        $companyIds = $companies->whereNotNull('cpny_id')
            ->where('cpny_id', '<>', '')
            ->distinct()
            ->orderBy('cpny_id')
            ->pluck('cpny_id');

        return view('pages.report-kontrak.index', [
            'months' => self::MONTHS,
            'companyIds' => $companyIds,
        ]);
    }

    private function query(Request $request)
    {
        $request->validate([
            'kontrakid' => ['nullable', 'string', 'max:100'],
            'cpny_id' => ['nullable', 'string', 'max:50'],
            'vendor' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'between:1900,9999'],
        ]);

        // Normalize both YYYYMM and YYYY-MM payment periods before pivoting.
        $rfps = TrRfp::query()->select([
            'id', 'kontrak_id', 'cpny_id', 'rfp_id', 'status',
        ])->selectRaw("regexp_replace(COALESCE(period_payment, ''), '[^0-9]', '', 'g') as period");

        $query = TrKontrak::query()->from('tr_kontrak as k')
            ->leftJoinSub($rfps, 'r', function ($join) {
                $join->on('r.kontrak_id', '=', 'k.kontrakid')
                    ->on('r.cpny_id', '=', 'k.cpny_id')
                    ->whereRaw("r.period ~ '^[0-9]{4}(0[1-9]|1[0-2])$'");
            })
            ->select(['k.id', 'k.kontrakid', 'k.cpny_id', 'k.vendorname', 'k.keperluan', 'k.startdate', 'k.enddate'])
            ->selectRaw("substring(r.period from 1 for 4) as year")
            ->selectRaw('json_object_agg(r.rfp_id, r.id) FILTER (WHERE r.rfp_id IS NOT NULL) as rfp_ids')
            ->groupBy('k.id', 'k.kontrakid', 'k.cpny_id', 'k.vendorname', 'k.keperluan', 'k.startdate', 'k.enddate')
            ->groupByRaw('substring(r.period from 1 for 4)');

        $statusSql = "CASE r.status WHEN 'D' THEN 'Revise / Draft' WHEN 'P' THEN 'On Progress'
            WHEN 'C' THEN 'Completed' WHEN 'X' THEN 'Cancelled' WHEN 'R' THEN 'Rejected'
            WHEN 'H' THEN 'Hold' ELSE COALESCE(r.status, 'Unknown') END";

        for ($month = 1; $month <= 12; $month++) {
            $query->selectRaw("string_agg(r.rfp_id || ' - ' || ($statusSql), E'\\n' ORDER BY r.rfp_id)
                FILTER (WHERE substring(r.period from 5 for 2) = ?) as month_$month", [sprintf('%02d', $month)]);
        }

        $user = $request->user();
        if (!$user->hasFullDataScope()) {
            $companyIds = Usercpny::where('username', $user->username)->pluck('cpny_id');
            $query->whereIn('k.cpny_id', $companyIds);

            if (!$user->hasRole('COSTCTRLACCESS') && !$user->hasRole('FINACCESS')) {
                $departments = Userdept::where('username', $user->username)->pluck('department_id');
                $query->where(function ($scope) use ($departments, $user) {
                    $scope->whereIn('k.department_id', $departments)
                        ->orWhere('k.user_peminta', $user->username)
                        ->orWhere('k.created_by', $user->username);
                });
            }
        }

        foreach (['kontrakid' => 'k.kontrakid', 'vendor' => 'k.vendorname'] as $input => $column) {
            if ($request->filled($input)) {
                $query->where($column, 'ilike', '%' . trim($request->input($input)) . '%');
            }
        }
        if ($request->filled('cpny_id')) {
            $query->where('k.cpny_id', trim($request->input('cpny_id')));
        }
        if ($request->filled('year')) {
            $query->whereRaw('substring(r.period from 1 for 4) = ?', [(string) $request->input('year')]);
        }

        return $query->toBase();
    }

    private function contractPeriod($row): string
    {
        $format = fn ($date) => $date ? Carbon::parse($date)->locale('id')->translatedFormat('j F Y') : '-';

        return $format($row->startdate) . ' - ' . $format($row->enddate);
    }

    public function json(Request $request)
    {
        return DataTables::of($this->query($request))
            ->editColumn('rfp_ids', function ($row) {
                $ids = json_decode($row->rfp_ids ?? '{}', true);

                return collect($ids)->map(fn ($id) => Hashids::encode($id))->all();
            })
            ->addColumn('period', fn ($row) => $this->contractPeriod($row))
            ->addColumn('kontrak_hash', fn ($row) => Hashids::encode($row->id))
            ->make(true);
    }

    public function export(Request $request)
    {
        $rows = $this->query($request)->orderBy('k.kontrakid')->orderBy('year')->get()
            ->map(function ($row) {
                $values = [$row->kontrakid, $row->cpny_id, $row->vendorname, $row->keperluan,
                    $this->contractPeriod($row), $row->year ?? '-'];
                for ($month = 1; $month <= 12; $month++) {
                    $values[] = $row->{'month_' . $month} ?? '';
                }

                return $values;
            });

        return Excel::download(new KontrakReportExport($rows), 'Kontrak_Report_' . now()->format('Ymd_His') . '.xlsx');
    }
}
