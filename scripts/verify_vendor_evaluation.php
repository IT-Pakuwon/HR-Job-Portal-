<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$db = Illuminate\Support\Facades\DB::connection('pgsql');
$templates = json_decode(file_get_contents(__DIR__.'/../database/data/vendor_evaluation_templates.json'), true, 512, JSON_THROW_ON_ERROR);
$ids = array_column($templates, 'id');
foreach (['ms_template_vendor_perfomance' => 45, 'ms_template_vendor_perfomance_detail' => 118, 'ms_template_vendor_perfomance_score' => 225] as $table => $expected) {
    $count = $db->table($table)->whereIn('template_vpe_id', $ids)->whereNull('cpny_id')->whereNull('department_id')->count();
    if ($count !== $expected) {
        throw new RuntimeException($table.': expected '.$expected.', got '.$count);
    }
    echo $table.': '.$count.' OK'.PHP_EOL;
}
foreach ($db->table('ms_template_vendor_perfomance')->whereIn('template_vpe_id', $ids)
    ->selectRaw('template_vpe_id, SUM(template_vpe_aspect_total_bobot) AS weight')
    ->groupBy('template_vpe_id')->orderBy('template_vpe_id')->get() as $row) {
    if ((float) $row->weight !== 100.0) {
        throw new RuntimeException('Invalid weight: '.$row->template_vpe_id);
    }
    echo $row->template_vpe_id.': '.$row->weight.'% OK'.PHP_EOL;
}
