<?php

namespace App\Exports;

use App\Models\MsCategory;
use App\Models\MsCompany;
use App\Models\MsDepartment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FreeParkingExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function headings(): array
    {
        return [
            'Doc ID',
            'Registration Date',
            'Name',
            'Company',
            'Department',
            'License Plate',
            'Vehicle Type',
            'Parking Type',
            'Worker Type',
            'Start Date',
            'End Date',
            'Status',
        ];
    }

    public function collection()
    {
        $request = $this->request;

        $companies = MsCompany::pluck('cpny_name', 'cpny_id');

        $departments = MsDepartment::pluck('department_name', 'department_id');

        $parkingTypeMap = MsCategory::where('doctype', 'PKR')
            ->where('type', 'TYPE')
            ->pluck('category_name', 'categoryid');

        $workerTypeMap = MsCategory::where('doctype', 'PKR')
            ->where('type', 'WORKER')
            ->pluck('category_name', 'categoryid');

        $user = Auth::user();

        $companyIds = collect(explode(',', (string) $user->cpny_id))
            ->map(fn ($x) => trim($x))
            ->filter()
            ->values()
            ->toArray();

        $query = DB::connection('pgsql5')
            ->table('tr_parking_registration_detail as pd')
            ->join('tr_parking_registration as pr', 'pr.docid', '=', 'pd.docid')
            ->whereIn('pr.cpny_id', $companyIds)
            ->select([
                'pr.docid',
                'pr.parking_regist_date',
                'pd.nama',
                'pd.cpny_id',
                'pd.department_id',
                'pd.nopol',
                'pd.jenis_kendaraan',
                'pd.parking_type',
                'pd.worker_type',
                'pd.startdate',
                'pd.enddate',
                'pd.status',
            ]);

        if ($request->date_from) {
            $query->whereDate('pr.parking_regist_date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('pr.parking_regist_date', '<=', $request->date_to);
        }

        if ($request->name) {
            $query->where(function ($q) use ($request) {
                $q->where('pd.nama', 'ilike', "%{$request->name}%")
                    ->orWhere('pd.username', 'ilike', "%{$request->name}%");
            });
        }

        if ($request->parking_type) {
            $query->where('pd.parking_type', $request->parking_type);
        }

        if ($request->worker_type) {
            $query->where('pd.worker_type', $request->worker_type);
        }

        if ($request->status) {
            $query->where('pd.status', $request->status);
        }

        return $query
            ->orderBy('pr.parking_regist_date', 'desc')
            ->get()
            ->map(function ($row) use ($companies, $departments, $parkingTypeMap, $workerTypeMap) {
                return [
                    'docid'               => $row->docid,
                    'parking_regist_date' => $row->parking_regist_date
                        ? Carbon::parse($row->parking_regist_date)->format('d-M-Y')
                        : '-',
                    'nama'                => $row->nama ?: '-',
                    'company'             => $companies[$row->cpny_id] ?? '-',
                    'department'          => $departments[$row->department_id] ?? '-',
                    'nopol'               => $row->nopol ?: '-',
                    'jenis_kendaraan'     => $row->jenis_kendaraan ?: '-',
                    'parking_type'        => $parkingTypeMap[$row->parking_type] ?? $row->parking_type ?? '-',
                    'worker_type'         => $workerTypeMap[$row->worker_type] ?? $row->worker_type ?? '-',
                    'startdate'           => $row->startdate
                        ? Carbon::parse($row->startdate)->format('d-M-Y')
                        : '-',
                    'enddate'             => $row->enddate
                        ? Carbon::parse($row->enddate)->format('d-M-Y')
                        : '-',
                    'status'              => match ($row->status) {
                        'P' => 'On Progress',
                        'C' => 'Completed',
                        'R' => 'Rejected',
                        'D' => 'Revise',
                        'X' => 'Cancelled',
                        'A' => 'Active',
                        default => $row->status ?? '-',
                    },
                ];
            });
    }
}
