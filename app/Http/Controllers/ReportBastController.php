<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ReportBastController extends Controller
{
    public function index()
    {
        return view('pages.report-bast.index');
    }

    private function query()
    {
        return DB::connection('pgsql')
            ->table('tr_bast as b')

            ->select([
                'b.bastid',
                'b.bastdate',
                'b.ponbr',
                'b.cpny_id',
                'b.csid',
                'b.sppbjktid',
                'b.bqid',
                'b.department_id',
                'b.user_peminta',
                'b.vendorname',
                'b.location_id',
                'b.sub_location_id',
                'b.startdate',
                'b.enddate',
                'b.bast_amount',
                'b.realize_amount',
                'b.penalty',
                'b.progress_pct',
                'b.terms_id',
                'b.status',
                'b.keperluan',
                'b.created_by',

                // ⭐ rating avg
                DB::raw('(
                SELECT COALESCE(AVG(rating_score),0)
                FROM tr_bast_rating r
                WHERE r.bast_id = b.bastid
            ) as rating_avg'),
            ]);
    }

    private function applyFilters($query, Request $request)
    {
        // Date range
        if ($request->date_from) {
            $query->whereDate('b.bastdate', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('b.bastdate', '<=', $request->date_to);
        }

        // BAST ID
        if ($request->bastid) {
            $query->where('b.bastid', 'ilike', "%{$request->bastid}%");
        }

        // Vendor
        if ($request->vendor) {
            $query->where('b.vendorname', 'ilike', "%{$request->vendor}%");
        }

        return $query;
    }

    private function applyUserScope($query)
    {
        $user = auth()->user();

        $isCostCtrl = collect([
            'COSTCTRLACCESS',
            'FINACCESS'
        ])->contains(fn ($role) => $user->hasRole($role));
        // Company scope
        $companyIds = \App\Models\Usercpny::where('username', $user->username)
            ->pluck('cpny_id');

        $query->whereIn('b.cpny_id', $companyIds);

        // Department restriction (ONLY if not cost control)
        if (!$isCostCtrl) {
            $deptIds = \App\Models\Userdept::where('username', $user->username)
                ->pluck('department_id');

            $query->where(function ($q) use ($deptIds, $user) {
                $q->whereIn('b.department_id', $deptIds)
                  ->orWhere('b.user_peminta', $user->username)
                  ->orWhere('b.created_by', $user->username);
            });
        }

        return $query;
    }

    public function json(Request $request)
    {
        $query = $this->applyUserScope(
            $this->query()
        );

        $query = $this->applyFilters($query, $request);

        $users = User::select('username', 'name')
            ->pluck('name', 'username');

        $departments = DB::connection('pgsql2')
            ->table('ms_department')
            ->pluck('department_name', 'department_id');

        $locations = DB::connection('pgsql')
            ->table('ms_location')
            ->pluck('location_name', 'location_id');

        $terms = DB::connection('pgsql')
            ->table('ms_top_detail')
            ->pluck('terms_name', 'terms_id');

        $sublocations = DB::connection('pgsql')
            ->table('ms_sub_location')
            ->pluck('sub_location_name', 'sub_location_id');

        return DataTables::of($query)

            ->addColumn('date', fn ($row) => $row->bastdate ? Carbon::parse($row->bastdate)->format('d-M-Y') : ''
            )

            ->addColumn('department_name', function ($row) use ($departments) {
                return isset($departments[$row->department_id])
                    ? $departments[$row->department_id]
                    : $row->department_id;
            })

            ->addColumn('requester', fn ($row) => $users[$row->user_peminta] ?? $row->user_peminta
            )

            ->addColumn('duration', function ($row) {
                if (!$row->startdate || !$row->enddate) {
                    return '-';
                }

                return Carbon::parse($row->startdate)
                    ->diffInDays($row->enddate).' days';
            })

            ->addColumn('location_full', function ($row) use ($locations, $sublocations) {
                $loc = isset($locations[$row->location_id])
                    ? $locations[$row->location_id]
                    : null;

                $sub = isset($sublocations[$row->sub_location_id])
                    ? $sublocations[$row->sub_location_id]
                    : null;

                if ($loc && $sub) {
                    return ['main' => $loc, 'sub' => $sub];
                }

                if ($loc) {
                    return ['main' => $loc, 'sub' => null];
                }

                return null;
            })

            ->addColumn('terms_name', function ($row) use ($terms) {
                return $row->terms_id && isset($terms[$row->terms_id])
                    ? $terms[$row->terms_id]
                    : '-';
            })

            ->addColumn('progress_label', fn ($row) => ($row->progress_pct ?? 0).'%'
            )

            ->addColumn('penalty_format', fn ($row) => $row->penalty
                ? number_format($row->penalty, 2)
                : '-'
            )

            ->addColumn('rating', function ($row) {
                return $row->rating_avg
                    ? round($row->rating_avg, 1)
                    : null;
            })

            ->make(true);
    }

    public function export(Request $request)
    {
        $query = $this->applyUserScope(
            $this->query()
        );

        $query = $this->applyFilters($query, $request);

        $rows = $query->get();

        // MASTER DATA
        $users = User::select('username', 'name')
            ->pluck('name', 'username');

        $departments = DB::connection('pgsql2')
            ->table('ms_department')
            ->pluck('department_name', 'department_id');

        $locations = DB::connection('pgsql')
            ->table('ms_location')
            ->pluck('location_name', 'location_id');

        $sublocations = DB::connection('pgsql')
            ->table('ms_sub_location')
            ->pluck('sub_location_name', 'sub_location_id');

        $terms = DB::connection('pgsql')
            ->table('ms_top_detail')
            ->pluck('terms_name', 'terms_id');

        // ✅ FINAL DATA MAPPING
        $data = $rows->map(function ($row) use (
            $users,
            $departments,
            $locations,
            $sublocations,
            $terms
        ) {
            // 🔥 SPPBJKT (ONE COLUMN)
            $docs = collect(explode(',', $row->sppbjktid ?? ''))
                ->map(fn ($x) => trim($x))
                ->filter()
                ->implode(', ');

            // 🔥 LOCATION COMBINE
            $loc = $locations[$row->location_id] ?? null;
            $sub = $sublocations[$row->sub_location_id] ?? null;

            $locationFull = $loc && $sub
                ? "$loc - $sub"
                : ($loc ?? '-');

            return [
                'Date' => $row->bastdate
                    ? Carbon::parse($row->bastdate)->format('d-M-Y')
                    : '',

                'BAST No' => $row->bastid,
                'CS No' => $row->csid,

                // ✅ ONE COLUMN
                'SPPBJKT' => $docs ?: '-',

                'BQ No' => $row->bqid,

                'Terms' => $terms[$row->terms_id] ?? '-',

                // ✅ COMBINED
                'Location' => $locationFull,

                'Department' => $departments[$row->department_id] ?? '-',

                'Requester' => $users[$row->user_peminta] ?? $row->user_peminta,

                'Vendor' => $row->vendorname,

                'Duration (Days)' => $row->startdate && $row->enddate
                    ? Carbon::parse($row->startdate)->diffInDays($row->enddate)
                    : '-',

                'Progress (%)' => $row->progress_pct
                    ? $row->progress_pct.'%'
                    : '-',

                'Penalty' => $row->penalty
                    ? number_format($row->penalty, 2)
                    : '-',

                'Amount' => $row->bast_amount
                    ? number_format($row->bast_amount, 2)
                    : '-',

                'Status' => match ($row->status) {
                    'C' => 'Completed',
                    'P' => 'Pending',
                    'X' => 'Cancelled',
                    default => $row->status,
                },

                'Rating' => $row->rating_avg
                    ? round($row->rating_avg, 1)
                    : '-',

                'Description' => $row->keperluan,
            ];
        });

        // ✅ EXPORT CSV
        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');

            if ($data->isNotEmpty()) {
                fputcsv($handle, array_keys($data->first()));
            }

            foreach ($data as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 'BAST_Report_'.now()->format('Ymd_His').'.csv');
    }
}
