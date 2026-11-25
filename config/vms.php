<?php

return [
    'portal_url' => env('VMS_PORTAL_URL', 'https://vms.pakuwon.com'),
    'attachments_url' => env('VMS_ATTACHMENTS_URL', 'https://s3-ap-southeast-1.amazonaws.com/vendorportal-attachment'),
    'legacy_aws_bucket' => env('LEGACY_AWS_BUCKET', 'vendorportal-attachment'),
    'legacy_aws_region' => env('LEGACY_AWS_REGION', 'ap-southeast-1'),
    'legacy_aws_key' => env('LEGACY_AWS_ACCESS_KEY_ID'),
    'legacy_aws_secret' => env('LEGACY_AWS_SECRET_ACCESS_KEY'),
    'legacy_aws_token' => env('LEGACY_AWS_SESSION_TOKEN'),
];
