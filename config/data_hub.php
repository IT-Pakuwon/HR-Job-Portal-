<?php

return [
    'om' => [
        'all_mall_users' => array_values(array_unique(array_merge(
            ['habsaribanoewati@pakuwon.com'],
            array_filter(array_map('trim', explode(',', (string) env('DATA_HUB_OM_ALL_MALL_USERS', ''))))
        ))),
    ],
    /*
    |--------------------------------------------------------------------------
    | Loyalty Power BI links
    |--------------------------------------------------------------------------
    |
    | Paste the complete Power BI report URL between the quotes. Empty links
    | are shown as "Link belum diisi" and cannot be opened.
    |
    */
    'loyalty' => [
        'pgcard' => 'https://app.powerbi.com/view?r=eyJrIjoiNTRjZjg2NzItOGVmNC00YjlmLThhODgtNGQ5OGZiZjE1ODY4IiwidCI6ImE2MGEzZWRmLTgzOTktNGQ5ZS1hMGEwLWQ3NGRjOTg1NDI2NCIsImMiOjEwfQ%3D%3D',
        'styw_2026' => 'https://app.powerbi.com/view?r=eyJrIjoiZWE4NTM3OGEtNTlkOS00Njc5LTgyMjYtYThiYjM3NDhlNTA0IiwidCI6ImE2MGEzZWRmLTgzOTktNGQ5ZS1hMGEwLWQ3NGRjOTg1NDI2NCIsImMiOjEwfQ%3D%3D',
        'styw_2025' => 'https://app.powerbi.com/view?r=eyJrIjoiMzI1M2ViNTctZTQzYS00ZDU5LTkwNWUtZGI3MjMxNDkzOTI0IiwidCI6ImE2MGEzZWRmLTgzOTktNGQ5ZS1hMGEwLWQ3NGRjOTg1NDI2NCIsImMiOjEwfQ%3D%3D',
        'cny_hktb_2026' => 'https://app.powerbi.com/view?r=eyJrIjoiNjE3NmQyM2EtZTc3ZC00ZDRjLTgxNmItZDJhNDEwN2EyZTk0IiwidCI6ImE2MGEzZWRmLTgzOTktNGQ5ZS1hMGEwLWQ3NGRjOTg1NDI2NCIsImMiOjEwfQ%3D%3D',
        'pgcard_styw_comparison' => '',
    ],

    'hrd' => [
        'job_portal' => 'https://app.powerbi.com/view?r=eyJrIjoiMTcyNGZlODctMDY1YS00YzZmLThkNzUtOWY1ZjczMzFjMDcxIiwidCI6ImE2MGEzZWRmLTgzOTktNGQ5ZS1hMGEwLWQ3NGRjOTg1NDI2NCIsImMiOjEwfQ%3D%3D',
    ],

    'sport' => [
        'padel' => 'https://app.powerbi.com/view?r=eyJrIjoiMTllM2RmYjQtNGNlMy00Y2FhLTgyODctYjhjMzg0MzY0YmI0IiwidCI6ImE2MGEzZWRmLTgzOTktNGQ5ZS1hMGEwLWQ3NGRjOTg1NDI2NCIsImMiOjEwfQ%3D%3D',
        'tennis' => 'https://app.powerbi.com/view?r=eyJrIjoiNDk5ZDY2OWQtNTg1Ny00ZDM5LTgyNTMtODVjNzU0MmI3YWZkIiwidCI6ImE2MGEzZWRmLTgzOTktNGQ5ZS1hMGEwLWQ3NGRjOTg1NDI2NCIsImMiOjEwfQ%3D%3D',
        'asia_racket' => 'https://app.powerbi.com/view?r=eyJrIjoiZjMzZGQ4ZGQtODQ1Yy00NDNjLTg4NDQtNmE4MjQ3Yzk2NTE0IiwidCI6ImE2MGEzZWRmLTgzOTktNGQ5ZS1hMGEwLWQ3NGRjOTg1NDI2NCIsImMiOjEwfQ%3D%3D',
    ],
];
