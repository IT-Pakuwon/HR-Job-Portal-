<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

class VendorEvaluationScoring
{
    public function calculate(array $details, array $ratings, array $scale): array
    {
        $expected = array_column($details, 'template_vpe_subaspect_id');
        $actual = array_keys($ratings);
        sort($expected);
        sort($actual);
        if ($expected !== $actual) {
            throw ValidationException::withMessages(['ratings' => 'Isi skor untuk seluruh subaspek sesuai template.']);
        }
        $total = 0;
        foreach ($details as &$detail) {
            $key = $detail['template_vpe_aspect_id'].':'.$ratings[$detail['template_vpe_subaspect_id']];
            if (!array_key_exists($key, $scale)) {
                throw ValidationException::withMessages(['ratings' => 'Skor tidak tersedia pada master penilaian.']);
            }
            $detail['template_vpe_subaspect_score'] = (int) $ratings[$detail['template_vpe_subaspect_id']];
            $detail['template_vpe_subaspect_nilai'] = round((float) $detail['template_vpe_subaspect_bobot'] * (float) $scale[$key] / 100, 2);
            $total += $detail['template_vpe_subaspect_nilai'];
        }
        unset($detail);
        $total = round($total, 2);
        $result = match (true) {
            $total >= 90 => 'Sangat Baik',
            $total >= 80 => 'Baik',
            $total >= 70 => 'Cukup',
            $total >= 60 => 'Kurang',
            default => 'Sangat Kurang/Perlu Evaluasi Khusus',
        };

        return ['details' => $details, 'total' => $total, 'result' => $result];
    }
}
