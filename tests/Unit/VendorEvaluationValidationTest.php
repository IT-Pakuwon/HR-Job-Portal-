<?php

namespace Tests\Unit;

use App\Http\Controllers\EvaluasiVendorController;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class VendorEvaluationValidationTest extends TestCase
{
    public function test_evaluation_note_is_required_for_creation_and_revision(): void
    {
        foreach ([true, false] as $creating) {
            try {
                $this->validateEvaluation(['vendor_perfomance_note' => ''], $creating);
                $this->fail('Catatan Evaluasi harus diisi.');
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('vendor_perfomance_note', $e->errors());
            }
        }
    }

    public function test_criteria_and_recommendation_from_request_are_not_accepted(): void
    {
        $result = $this->validateEvaluation([
            'vendor_perfomance_note' => 'Evaluasi selesai',
            'criteria' => ['S1' => 'Kriteria yang diubah'],
            'template_vpe_aspect_comment' => 'Komentar yang diubah',
        ], false);

        $this->assertSame('Evaluasi selesai', $result['vendor_perfomance_note']);
        $this->assertArrayNotHasKey('criteria', $result);
        $this->assertArrayNotHasKey('template_vpe_aspect_comment', $result);
    }

    private function validateEvaluation(array $overrides, bool $creating): array
    {
        $data = array_merge([
            'source_type' => 'SPK', 'source_id' => 1,
            'vendor_perfomance_date' => '2026-11-26', 'vendor_perfomance_start_date' => '2026-01-01',
            'vendor_perfomance_end_date' => '2026-12-31', 'template_vpe_id' => 'VPE', 'ratings' => ['S1' => 4],
        ], $overrides);
        $request = Request::create('/evaluasivendor', 'POST', $data);
        $method = new \ReflectionMethod(EvaluasiVendorController::class, 'validated');
        return $method->invoke(new EvaluasiVendorController(), $request, $creating);
    }
}
