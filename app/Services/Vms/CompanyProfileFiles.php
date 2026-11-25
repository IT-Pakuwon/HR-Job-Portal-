<?php

namespace App\Services\Vms;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CompanyProfileFiles
{
    public function url(?string $filename): ?string
    {
        if (! $filename || str_contains($filename, '..') || str_contains($filename, '/') || str_contains($filename, '\\') || preg_match('/[\x00-\x1f]/', $filename)) {
            return null;
        }

        $key = config('vms.legacy_aws_key');
        $secret = config('vms.legacy_aws_secret');
        if (! $key || ! $secret) {
            $record = Schema::connection('mysql7')->hasTable('credential') ? DB::connection('mysql7')->table('credential')->first() : null;
            if ($record) {
                $key = openssl_decrypt($record->env_key, $record->ciphering, $record->encryption_key, (int) $record->options, $record->encryption_iv);
                $secret = openssl_decrypt($record->env_secret, $record->ciphering, $record->encryption_key, (int) $record->options, $record->encryption_iv);
                abort_unless($key && $secret, 503, 'Konfigurasi akses company profile belum dapat digunakan.');
            }
        }

        // Older public objects remain accessible if signing credentials are not configured.
        if (! $key || ! $secret) {
            return rtrim(config('vms.attachments_url'), '/').'/company_profile/'.rawurlencode($filename);
        }

        $bucket = (string) config('vms.legacy_aws_bucket');
        $region = (string) config('vms.legacy_aws_region');
        abort_unless(preg_match('/\A[a-z0-9][a-z0-9.-]+\z/D', $bucket) && preg_match('/\A[a-z0-9-]+\z/D', $region), 503, 'Konfigurasi penyimpanan company profile tidak valid.');
        $host = 's3-'.$region.'.amazonaws.com';
        $uri = '/'.rawurlencode($bucket).'/company_profile/'.rawurlencode($filename);
        $time = now('UTC');
        $date = $time->format('Ymd');
        $scope = $date.'/'.$region.'/s3/aws4_request';
        $query = ['X-Amz-Algorithm' => 'AWS4-HMAC-SHA256', 'X-Amz-Credential' => $key.'/'.$scope, 'X-Amz-Date' => $time->format('Ymd\THis\Z'), 'X-Amz-Expires' => '600', 'X-Amz-SignedHeaders' => 'host'];
        if ($token = config('vms.legacy_aws_token')) {
            $query['X-Amz-Security-Token'] = $token;
        }
        ksort($query);
        $canonicalQuery = http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        $canonical = "GET\n".$uri."\n".$canonicalQuery."\nhost:".$host."\n\nhost\nUNSIGNED-PAYLOAD";
        $toSign = "AWS4-HMAC-SHA256\n".$query['X-Amz-Date']."\n".$scope."\n".hash('sha256', $canonical);
        $signing = hash_hmac('sha256', $date, 'AWS4'.$secret, true);
        foreach ([$region, 's3', 'aws4_request'] as $part) {
            $signing = hash_hmac('sha256', $part, $signing, true);
        }

        return 'https://'.$host.$uri.'?'.$canonicalQuery.'&X-Amz-Signature='.hash_hmac('sha256', $toSign, $signing);
    }
}
