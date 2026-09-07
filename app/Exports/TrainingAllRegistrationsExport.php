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

/**
 * Mirrors TrainingRegistrationController::allRegistrations() — same rows,
 * same optional training_id/status/search filters — so the download always
 * matches whatever the List Registration tab is currently showing.
 */
class TrainingAllRegistrationsExport implements
    FromCollection,
    WithHeadings,
    ShouldAutoSize
{
    protected const STATUS_LABELS = [
        'P' => 'Waiting Approval',
        'C' => 'Approved',
        'R' => 'Rejected',
        'W' => 'Waiting List',
        'O' => 'Slot Offered',
        'X' => 'Cancelled',
    ];

    protected ?string $trainingId;
    protected ?string $status;
    protected ?string $search;

    public function __construct(?string $trainingId = null, ?string $status = null, ?string $search = null)
    {
        $this->trainingId = $trainingId ?: null;
        $this->status = $status ?: null;
        $this->search = $search ?: null;
    }

    public function headings(): array
    {
        return [
            'Doc ID',
            'Employee',
            'Username',
            'Company',
            'Department',
            'Training',
            'Schedule Date',
            'Registered On',
            'Status',
        ];
    }

    public function collection()
    {
        $registrations = TrLndTrainingRegistration::query()
            ->with('schedule.schedule.training')
            ->when($this->trainingId, fn ($q) => $q->where('training_id', $this->trainingId))
            ->orderByDesc('created_at')
            ->get();

        $usernames = $registrations->pluck('user_registration')->unique();
        $names = $usernames->isEmpty() ? collect() : User::whereIn('username', $usernames)->pluck('name', 'username');

        $cpnyIds = $registrations->pluck('cpny_id')->filter()->unique();
        $companyNames = $cpnyIds->isEmpty() ? collect() : MsCompany::whereIn('cpny_id', $cpnyIds)->pluck('cpny_name', 'cpny_id');

        $deptIds = $registrations->pluck('department_id')->filter()->unique();
        $departmentNames = $deptIds->isEmpty() ? collect() : MsDepartment::whereIn('department_id', $deptIds)->pluck('department_name', 'department_id');

        $search = $this->search ? mb_strtolower($this->search) : null;

        return $registrations
            ->map(function ($r) use ($names, $companyNames, $departmentNames) {
                $effectiveStatus = $r->effective_status;

                return [
                    'docid' => $r->training_regist_id,
                    'name' => $names[$r->user_registration] ?? $r->user_registration,
                    'username' => $r->user_registration,
                    'company' => $companyNames[$r->cpny_id] ?? $r->cpny_id,
                    'department' => $departmentNames[$r->department_id] ?? $r->department_id,
                    'training_name' => $r->schedule?->schedule?->training?->training_name ?? '-',
                    'schedule_date' => ($r->schedule_date ?? $r->schedule?->schedule_date)
                        ? Carbon::parse($r->schedule_date ?? $r->schedule?->schedule_date)->format('d-M-Y')
                        : '-',
                    'registered_at' => $r->created_at ? Carbon::parse($r->created_at)->format('d-M-Y H:i') : '-',
                    'status' => self::STATUS_LABELS[$effectiveStatus] ?? $effectiveStatus,
                    '_effective_status' => $effectiveStatus,
                ];
            })
            ->filter(function ($row) use ($search) {
                if ($this->status && $row['_effective_status'] !== $this->status) {
                    return false;
                }

                if ($search) {
                    $haystack = mb_strtolower($row['docid'] . ' ' . $row['name'] . ' ' . $row['username'] . ' ' . $row['training_name']);
                    if (!str_contains($haystack, $search)) {
                        return false;
                    }
                }

                return true;
            })
            ->map(fn ($row) => collect($row)->except('_effective_status')->all())
            ->values();
    }
}
