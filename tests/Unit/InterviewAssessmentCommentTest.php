<?php

namespace Tests\Unit;

use App\Services\InterviewAssessmentComment;
use PHPUnit\Framework\TestCase;

class InterviewAssessmentCommentTest extends TestCase
{
    public function test_updates_show_latest_result_and_comment_without_changing_reviewer_identity(): void
    {
        $result = (object) [
            'id' => 42, 'assessment_type' => 'user', 'assessment_result' => 'FAILED',
            'assessment_comment' => 'Tidak lanjut', 'created_user' => 'reviewer',
            'created_at' => '2026-11-19 09:00:00', 'updated_at' => '2026-11-19 09:00:00',
        ];
        $first = InterviewAssessmentComment::make($result);
        $this->assertSame('FAILED', $first['assessment_result']);
        $this->assertSame('Tidak lanjut', $first['message']);
        $result->assessment_result = 'PASS';
        $result->assessment_comment = 'Lanjut';
        $result->updated_at = '2026-11-20 10:00:00';
        $updated = InterviewAssessmentComment::make($result);
        $this->assertSame($first['id'], $updated['id']);
        $this->assertSame('PASS', $updated['assessment_result']);
        $this->assertSame('Lanjut', $updated['message']);
        $this->assertSame('reviewer', $updated['username']);
        $this->assertSame('2026-11-20 10:00:00', $updated['message_date']);
        $this->assertFalse($updated['is_reject']);
    }

    public function test_only_valid_hc_and_user_results_are_shown(): void
    {
        $result = (object) [
            'id' => 1, 'assessment_type' => 'hc', 'assessment_result' => 'PASS',
            'assessment_comment' => null, 'created_user' => 'hc',
            'created_at' => '2026-11-20 10:00:00', 'updated_at' => null,
        ];
        $this->assertSame('', InterviewAssessmentComment::make($result)['message']);
        $result->assessment_result = '';
        $this->assertNull(InterviewAssessmentComment::make($result));
        $result->assessment_result = 'PASS';
        $result->assessment_type = 'other';
        $this->assertNull(InterviewAssessmentComment::make($result));
    }
}
