<?php

return [
    'source_id' => (int) env('AI_SEARCH_SOURCE_ID', 3),
    'table_labels' => [
        'public.tr_cs' => 'Canvass Sheet / canvass sheet / canvassing sheet / CS: header dokumen; gunakan untuk jumlah dokumen canvass.',
        'public.tr_cs_detail' => 'Detail item/baris Canvass Sheet; bukan jumlah dokumen canvass.',
    ],
    // Unused backup tables in the career source; exclude exact names only.
    'excluded_tables' => [
        3 => [
            'hr_company_address_260729',
            'hr_company_address_260805',
            'hr_ms_sto_grading_260803',
            'hr_ms_sto_subgrading_260803',
            'hr_trx_prf_260729',
            'hr_trx_prf_260803',
            'hr_trx_prf_job_qualification_260803',
            'hr_trx_prf_job_responsiblities_260803',
            'hr_trx_prf_job_tags_260803',
            'ms_approval_payroll_260803',
            'ms_group_acc_specific_260803',
            'trx_agenda_260806',
        ],
    ],
    'openrouter' => [
        'api_key' => env('OPENROUTER_API_KEY'),
        'model' => env('OPENROUTER_MODEL'),
        'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
    ],
];
