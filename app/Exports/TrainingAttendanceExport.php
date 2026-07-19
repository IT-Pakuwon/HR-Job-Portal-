<?php

namespace App\Exports;

use App\Models\MsCompany;
use App\Models\MsDepartment;
use App\Models\TrTrainingRegistration;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TrainingAttendanceExport implements
    FromCollection,
    WithHeadings,
    ShouldAutoSize
{
    protected int $scheduleDetailId;

    public function __construct(int $scheduleDetailId)
    {
        $this->scheduleDetailId = $scheduleDetailId;
    }

    public function headings(): array
    {
        return ['Doc ID', 'Name', 'Company', 'Department', 'Attended At'];
    }

    public function collection()
    {
        $registrations = TrTrainingRegistration::where('schedule_detail_id', $this->scheduleDetailId)
            ->where('status', TrTrainingRegistration::STATUS_APPROVED)
            ->whereNotNull('attended_at')
            ->orderBy('attended_at')
            ->get();

        $usernames = $registrations->pluck('username')->unique();
        $names = $usernames->isEmpty() ? collect() : User::whereIn('username', $usernames)->pluck('name', 'username');

        $cpnyIds = $registrations->pluck('cpny_id')->filter()->unique();
        $cpnyNames = $cpnyIds->isEmpty() ? collect() : MsCompany::whereIn('cpny_id', $cpnyIds)->pluck('cpny_name', 'cpny_id');

        $deptIds = $registrations->pluck('department_id')->filter()->unique();
        $deptNames = $deptIds->isEmpty() ? collect() : MsDepartment::whereIn('department_id', $deptIds)->pluck('department_name', 'department_id');

        return $registrations->map(fn ($r) => [
            'docid' => $r->docid,
            'name' => $names[$r->username] ?? $r->username,
            'company' => $cpnyNames[$r->cpny_id] ?? $r->cpny_id,
            'department' => $deptNames[$r->department_id] ?? $r->department_id,
            'attended_at' => $r->attended_at ? Carbon::parse($r->attended_at)->format('d-M-Y H:i') : '-',
        ])->values();
    }
}
