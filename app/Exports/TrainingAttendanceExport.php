<?php

namespace App\Exports;

use App\Models\MsCompany;
use App\Models\MsDepartment;
use App\Models\TrLndTrainingRegistration;
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
    protected string $scheduleId;

    public function __construct(string $scheduleId)
    {
        $this->scheduleId = $scheduleId;
    }

    public function headings(): array
    {
        return ['Doc ID', 'Name', 'Company', 'Department', 'Attended At'];
    }

    public function collection()
    {
        $registrations = TrLndTrainingRegistration::where('schedule_id', $this->scheduleId)
            ->where('status', TrLndTrainingRegistration::STATUS_APPROVED)
            ->seated()
            ->whereNotNull('completed_at')
            ->orderBy('completed_at')
            ->get();

        $usernames = $registrations->pluck('user_registration')->unique();
        $names = $usernames->isEmpty() ? collect() : User::whereIn('username', $usernames)->pluck('name', 'username');

        $cpnyIds = $registrations->pluck('cpny_id')->filter()->unique();
        $cpnyNames = $cpnyIds->isEmpty() ? collect() : MsCompany::whereIn('cpny_id', $cpnyIds)->pluck('cpny_name', 'cpny_id');

        $deptIds = $registrations->pluck('department_id')->filter()->unique();
        $deptNames = $deptIds->isEmpty() ? collect() : MsDepartment::whereIn('department_id', $deptIds)->pluck('department_name', 'department_id');

        return $registrations->map(fn ($r) => [
            'docid' => $r->training_regist_id,
            'name' => $names[$r->user_registration] ?? $r->user_registration,
            'company' => $cpnyNames[$r->cpny_id] ?? $r->cpny_id,
            'department' => $deptNames[$r->department_id] ?? $r->department_id,
            'attended_at' => $r->completed_at ? Carbon::parse($r->completed_at)->format('d-M-Y H:i') : '-',
        ])->values();
    }
}
