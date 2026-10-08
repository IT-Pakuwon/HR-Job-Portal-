<?php

namespace App\Exports;

use App\Models\MsCompany;
use App\Models\MsDepartment;
use App\Models\MsTrainingEvent;
use App\Models\TrLndTrainingRegistration;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Same rows as the Training Report tab's employee table (one row per
 * employee, per training attended) — mirrors
 * TrainingAttendanceController::reportEmployees()'s query/scoping/filters
 * so the export always matches what's on screen.
 */
class TrainingReportEmployeesExport implements
    FromCollection,
    WithHeadings,
    ShouldAutoSize
{
    protected array $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function headings(): array
    {
        return ['Name', 'Company', 'Department', 'Training', 'Date', 'Stars'];
    }

    public function collection()
    {
        $query = TrLndTrainingRegistration::where('status', TrLndTrainingRegistration::STATUS_APPROVED)
            ->whereNotNull('completed_at');

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('schedule_date', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('schedule_date', '<=', $this->filters['date_to']);
        }

        if (!empty($this->filters['training_id'])) {
            $query->where('training_id', $this->filters['training_id']);
        }

        if (!empty($this->filters['cpny_id'])) {
            $query->where('cpny_id', $this->filters['cpny_id']);
        }

        if (!empty($this->filters['department_id'])) {
            $query->where('department_id', $this->filters['department_id']);
        }

        $attended = $query->orderByDesc('schedule_date')->get();

        $usernames = $attended->pluck('user_registration')->unique();
        $names = $usernames->isEmpty() ? collect() : User::whereIn('username', $usernames)->pluck('name', 'username');

        if (!empty($this->filters['search'])) {
            $term = strtolower($this->filters['search']);
            $matching = $usernames->filter(function ($username) use ($names, $term) {
                return str_contains(strtolower($username), $term) || str_contains(strtolower($names[$username] ?? ''), $term);
            });
            $attended = $attended->whereIn('user_registration', $matching);
        }

        $trainingIds = $attended->pluck('training_id')->unique();
        $trainingNames = $trainingIds->isEmpty() ? collect() : MsTrainingEvent::whereIn('training_id', $trainingIds)->pluck('training_name', 'training_id');

        $cpnyNames = MsCompany::whereIn('cpny_id', $attended->pluck('cpny_id')->unique())->pluck('cpny_name', 'cpny_id');
        $deptNames = MsDepartment::whereIn('department_id', $attended->pluck('department_id')->unique())->pluck('department_name', 'department_id');

        return $attended->sortBy('user_registration')->map(fn ($r) => [
            'name' => $names[$r->user_registration] ?? $r->user_registration,
            'company' => $cpnyNames[$r->cpny_id] ?? $r->cpny_id,
            'department' => $deptNames[$r->department_id] ?? $r->department_id,
            'training' => $trainingNames[$r->training_id] ?? $r->training_id,
            'date' => $r->schedule_date,
            'stars' => $r->stars,
        ])->values();
    }
}
