<?php

namespace App\Exports;

use App\Models\MsCompany;
use App\Models\MsDepartment;
use App\Models\MsLndTrainingFeedback;
use App\Models\TrLndTrainingFeedbackAnswer;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TrainingFeedbackExport implements
    FromCollection,
    WithHeadings,
    ShouldAutoSize
{
    protected string $scheduleId;

    protected $questions;

    public function __construct(string $scheduleId)
    {
        $this->scheduleId = $scheduleId;
        $this->questions = MsLndTrainingFeedback::active()->get();
    }

    public function headings(): array
    {
        return array_merge(
            ['Doc ID', 'Name', 'Company', 'Department'],
            $this->questions->map(fn ($q) => $q->question_text)->all()
        );
    }

    public function collection()
    {
        $answers = TrLndTrainingFeedbackAnswer::where('schedule_id', $this->scheduleId)->get();
        $byRespondent = $answers->groupBy('training_regist_id');

        $usernames = $answers->pluck('user_registration')->unique();
        $names = $usernames->isEmpty() ? collect() : User::whereIn('username', $usernames)->pluck('name', 'username');

        $cpnyIds = $answers->pluck('cpny_id')->filter()->unique();
        $cpnyNames = $cpnyIds->isEmpty() ? collect() : MsCompany::whereIn('cpny_id', $cpnyIds)->pluck('cpny_name', 'cpny_id');

        $deptIds = $answers->pluck('department_id')->filter()->unique();
        $deptNames = $deptIds->isEmpty() ? collect() : MsDepartment::whereIn('department_id', $deptIds)->pluck('department_name', 'department_id');

        return $byRespondent->map(function ($rows, $registId) use ($names, $cpnyNames, $deptNames) {
            $first = $rows->first();
            $byQuestion = $rows->keyBy('question_order');

            $row = [
                'docid' => $registId,
                'name' => $names[$first->user_registration] ?? $first->user_registration,
                'company' => $cpnyNames[$first->cpny_id] ?? $first->cpny_id,
                'department' => $deptNames[$first->department_id] ?? $first->department_id,
            ];

            foreach ($this->questions as $question) {
                $answer = $byQuestion->get($question->question_order);
                $row['q_' . $question->question_order] = $answer?->answer_number ?? $answer?->answer_text ?? '-';
            }

            return $row;
        })->values();
    }
}
