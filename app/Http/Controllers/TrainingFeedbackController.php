<?php

namespace App\Http\Controllers;

use App\Models\MsLndTrainingFeedback;
use App\Models\MsLndTrainingSchedule;
use App\Models\TrLndTrainingFeedbackAnswer;
use App\Models\TrLndTrainingRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TrainingFeedbackController extends Controller
{
    /**
     * Question list + this participant's own existing answers (if any) +
     * whether the schedule's feedback window is currently open.
     * training_regist_id is a 1:1 key per participant, so ownership scoping
     * here can never leak another person's answers.
     */
    public function show($id)
    {
        $registration = TrLndTrainingRegistration::findOrFail($id);
        $user = Auth::user();

        abort_unless(strcasecmp((string) $registration->user_registration, (string) $user->username) === 0, 403);

        $detail = MsLndTrainingSchedule::where('schedule_id', $registration->schedule_id)->first();

        $questions = MsLndTrainingFeedback::active()->get();

        $existing = TrLndTrainingFeedbackAnswer::where('training_regist_id', $registration->training_regist_id)
            ->get()
            ->keyBy('question_order');

        return response()->json([
            'is_open' => (bool) $detail?->is_feedback_open,
            'has_attended' => (bool) $registration->completed_at,
            'already_submitted' => $existing->isNotEmpty(),
            'questions' => $questions->map(fn ($q) => [
                'question_order' => $q->question_order,
                'question_text' => $q->question_text,
                'question_type' => $q->question_type,
                'options' => $q->options,
                'answer_text' => $existing->get($q->question_order)?->answer_text,
                'answer_number' => $existing->get($q->question_order)?->answer_number,
            ])->values(),
        ]);
    }

    /**
     * Upsert one answer row per active question. Re-derives question_text/
     * type/value from MsLndTrainingFeedback server-side (never trusts the
     * client for those) and snapshots them onto the answer row, same
     * philosophy as the rest of this module.
     */
    public function submit(Request $request, $id)
    {
        $registration = TrLndTrainingRegistration::findOrFail($id);
        $user = Auth::user();

        abort_unless(strcasecmp((string) $registration->user_registration, (string) $user->username) === 0, 403);

        if (!$registration->completed_at) {
            return response()->json(['success' => false, 'message' => 'Anda belum tercatat hadir pada training ini'], 422);
        }

        $detail = MsLndTrainingSchedule::where('schedule_id', $registration->schedule_id)->first();

        if (!$detail || !$detail->is_feedback_open) {
            return response()->json(['success' => false, 'message' => 'Feedback untuk training ini belum/tidak lagi dibuka'], 422);
        }

        $request->validate([
            'answers' => 'required|array|min:1',
            'answers.*.question_order' => 'required|integer',
            'answers.*.value' => 'nullable',
        ]);

        $questions = MsLndTrainingFeedback::active()->get()->keyBy('question_order');
        $submitted = collect($request->input('answers'))->keyBy('question_order');

        DB::connection('pgsql5')->beginTransaction();

        try {
            foreach ($questions as $order => $question) {
                $value = $submitted->get($order)['value'] ?? null;

                $answerText = null;
                $answerNumber = null;

                if ($question->question_type === MsLndTrainingFeedback::TYPE_RATING) {
                    $answerNumber = is_numeric($value) ? (int) $value : null;
                } else {
                    $answerText = $value !== null && $value !== '' ? trim((string) $value) : null;
                }

                TrLndTrainingFeedbackAnswer::updateOrCreate(
                    [
                        'training_regist_id' => $registration->training_regist_id,
                        'question_order' => $order,
                    ],
                    [
                        'training_id' => $registration->training_id,
                        'training_detail_id' => $registration->training_detail_id,
                        'schedule_id' => $registration->schedule_id,
                        'schedule_date' => $registration->schedule_date,
                        'cpny_id' => $registration->cpny_id,
                        'department_id' => $registration->department_id,
                        'user_registration' => $registration->user_registration,
                        'question_text' => $question->question_text,
                        'question_type' => $question->question_type,
                        'question_value' => $question->question_value,
                        'answer_text' => $answerText,
                        'answer_number' => $answerNumber,
                        'status' => 'A',
                        'created_by' => $user->username,
                        'updated_by' => $user->username,
                    ]
                );
            }

            DB::connection('pgsql5')->commit();

            return response()->json(['success' => true, 'message' => 'Terima kasih atas feedback Anda']);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan feedback',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
