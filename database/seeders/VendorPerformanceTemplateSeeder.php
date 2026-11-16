<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class VendorPerformanceTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = json_decode(file_get_contents(database_path('data/vendor_evaluation_templates.json')), true, 512, JSON_THROW_ON_ERROR);
        $db = DB::connection('pgsql');
        $counts = ['inserted' => 0, 'existing' => 0];
        $db->transaction(function () use ($db, $templates, &$counts) {
            // Serialize this importer, including repeated runs with nullable scope keys.
            $db->select('select pg_advisory_xact_lock(?)', [716202611]);
            $audit = ['status' => 'A', 'created_by' => 'vendor_template_import',
                'created_at' => now(), 'updated_by' => 'vendor_template_import', 'updated_at' => now()];
            $scope = ['cpny_id' => null, 'department_id' => null];
            $insert = function ($table, $keys, $values) use ($db, $audit, &$counts) {
                $existing = $db->table($table)->where($keys)->get();
                if ($existing->count() > 1) {
                    throw new RuntimeException('Duplicate template key in '.$table);
                }
                if ($existing->isNotEmpty()) {
                    foreach ($values as $column => $value) {
                        $stored = $existing->first()->{$column};
                        $matches = is_numeric($value) && is_numeric($stored)
                            ? (string) $stored === (string) $value || (float) $stored === (float) $value
                            : $stored === $value;
                        if (!$matches) {
                            throw new RuntimeException('Existing template differs: '.$table.'.'.$column);
                        }
                    }
                    $counts['existing']++;
                    return;
                }
                $db->table($table)->insert(array_merge($keys, $values, $audit));
                $counts['inserted']++;
            };
            foreach ($templates as $template) {
                foreach ($template['aspects'] as $aspect) {
                    $keys = array_merge($scope, ['template_vpe_id' => $template['id'], 'template_vpe_aspect_id' => $aspect['id']]);
                    $insert('ms_template_vendor_perfomance', $keys, [
                        'template_vpe_descr' => $template['description'],
                        'template_vpe_aspect_descr' => $aspect['description'],
                        'template_vpe_aspect_total_bobot' => $aspect['weight'],
                    ]);
                    foreach ($aspect['details'] as $detail) {
                        $insert('ms_template_vendor_perfomance_detail', array_merge($keys, ['template_vpe_subaspect_id' => $detail['id']]), [
                            'template_vpe_aspect_descr' => $aspect['description'],
                            'template_vpe_subaspect_descr' => $detail['description'],
                            'template_vpe_subaspect_kriteria' => $detail['criteria'],
                            'template_vpe_subaspect_note' => $detail['note'],
                            'template_vpe_aspect_bobot' => $detail['weight'],
                        ]);
                    }
                    foreach ($template['scores'] as $score) {
                        $insert('ms_template_vendor_perfomance_score', array_merge($keys, ['template_vpe_aspect_score' => $score['score']]), [
                            'template_vpe_aspect_nilai' => $score['value'],
                        ]);
                    }
                }
            }
        });
        $this->command?->info('Vendor evaluation master: '.$counts['inserted'].' inserted, '.$counts['existing'].' already present.');
    }
}
