<?php

namespace Tests\Feature;

use Barryvdh\DomPDF\Facade\Pdf;
use Tests\TestCase;

class InterviewAssessmentPdfTest extends TestCase
{
    public function test_pdf_template_renders_scores_and_all_reviewers_for_each_interview_type(): void
    {
        $career = (object) ['docid' => 'JAP001', 'cpnyid' => 'TP', 'fullname' => 'Test Candidate', 'job_title' => 'IT Support', 'job_level' => 'Staff'];
        $assessment = (object) ['assessment_date' => '2026-11-24 09:30:00', 'user' => 'reviewer', 'total_assessment_score_value' => 3];
        $groups = collect([
            ['label' => 'Appearance', 'selected' => 0, 'options' => collect(['0' => (object) ['assessment_descr' => 'Poor'], '3' => (object) ['assessment_descr' => 'Good']])],
            ['label' => 'Communication', 'selected' => 3, 'options' => collect(['3' => (object) ['assessment_descr' => 'Clear']])],
            ['label' => 'Experience', 'selected' => null, 'options' => collect([])],
        ]);
        $results = collect([
            (object) ['created_user' => 'reviewer', 'assessment_result' => 'PASS', 'assessment_strengths' => 'Strong communication', 'assessment_weaknesses' => '', 'assessment_comment' => 'Lanjut'],
            (object) ['created_user' => 'other', 'assessment_result' => 'FAILED', 'assessment_strengths' => '', 'assessment_weaknesses' => 'Experience', 'assessment_comment' => 'Tidak lanjut'],
        ]);
        $names = collect(['reviewer' => 'Reviewer One', 'other' => 'Reviewer Two']);
        $companyName = 'Example Company';

        foreach (['hc', 'user'] as $type) {
            $data = compact('career', 'assessment', 'groups', 'results', 'names', 'companyName', 'type');
            $html = view('pages.careers.pdfinterviewassessment', $data)->render();
            $this->assertStringContainsString('Test Candidate', $html);
            $this->assertStringContainsString('Reviewer One', $html);
            $this->assertStringContainsString('Reviewer Two', $html);
            $this->assertStringContainsString('Tidak lanjut', $html);
            $this->assertSame(2, substr_count($html, '[ X ]'));
            $this->assertStringContainsString('09:30', $html);
            $this->assertStringStartsWith('%PDF-', Pdf::loadView('pages.careers.pdfinterviewassessment', $data)->output());
        }
    }
}
