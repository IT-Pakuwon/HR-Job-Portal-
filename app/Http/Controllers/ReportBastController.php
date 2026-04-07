<?php

namespace App\Http\Controllers;

// ← ADD THIS

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
                'b.department_id',
                'b.user_peminta',
                'b.vendorname',
                'b.location_id',
                'b.sub_location_id',
                'b.startdate',
                'b.enddate',
                'b.bast_amount',
                'b.realize_amount',
                'b.status',
                'b.keperluan',

                // ⭐ rating avg
                DB::raw('(
                    SELECT AVG(rating_score)
                    FROM tr_bast_rating r
                    WHERE r.bast_id = b.bastid
                ) as rating_avg'),
            ]);
    }

    public function json(Request $request)
    {
        $query = $this->query();

        $users = User::pluck('name', 'username');
        $departments = MsDepartment::pluck('department_name', 'department_id');

        return DataTables::of($query)

            ->addColumn('date', fn ($row) => $row->bastdate ? Carbon::parse($row->bastdate)->format('d-M-Y') : ''
            )

            ->addColumn('department_name', fn ($row) => $departments[$row->department_id] ?? $row->department_id
            )

            ->addColumn('requester', fn ($row) => $users[$row->user_peminta] ?? $row->user_peminta
            )

            ->addColumn('duration', function ($row) {
                if (!$row->startdate || !$row->enddate) {
                    return '-';
                }

                return Carbon::parse($row->startdate)
                    ->diffInDays($row->enddate).' days';
            })

            ->addColumn('rating', function ($row) {
                return $row->rating_avg
                    ? round($row->rating_avg, 1)
                    : null;
            })

            ->make(true);
    }
}
