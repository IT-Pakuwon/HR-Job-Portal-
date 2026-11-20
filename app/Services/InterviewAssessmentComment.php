<?php

namespace App\Services;

class InterviewAssessmentComment
{
    /** Format the current reviewer result as an item in the career comment feed. */
    public static function make($result): ?array
    {
        $outcome = strtoupper(trim((string) $result->assessment_result));
        $type = strtolower(trim((string) $result->assessment_type));
        if (!in_array($outcome, ['PASS', 'FAILED'], true) || !in_array($type, ['hc', 'user'], true)) {
            return null;
        }

        $date = $result->updated_at ?: $result->created_at;
        if (!$date) {
            return null;
        }

        return [
            'id' => 'assessment-'.$result->id,
            'username' => $result->created_user,
            'name' => $result->created_user,
            'message' => trim((string) $result->assessment_comment),
            'created_at' => $date,
            'message_date' => $date,
            'is_reject' => false,
            'assessment_type' => $type,
            'assessment_result' => $outcome,
        ];
    }
}
