<?php

namespace App\Http\Controllers\Traits;

use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Raw Google\Cloud\Storage client uploads/reads — the app has no Flysystem
 * adapter registered for the 'gcs' filesystem driver (config/filesystems.php
 * only declares its credentials), so Storage::disk('gcs') is not usable.
 * Mirrors the pattern already used by User::profilePhotoUrl() for the
 * (private) pkwjkt-attachment bucket: objects are read back via a
 * freshly-signed URL rather than a stored public path.
 */
trait UploadsToGcs
{
    private function gcsClient(): StorageClient
    {
        $config = config('filesystems.disks.gcs');
        $keyFilePath = $config['key_file'];

        if (!str_starts_with($keyFilePath, '/') && !preg_match('/^[A-Za-z]:\\\\/', $keyFilePath)) {
            $keyFilePath = base_path($keyFilePath);
        }

        return new StorageClient([
            'projectId' => $config['project_id'],
            'keyFilePath' => $keyFilePath,
        ]);
    }

    /**
     * Upload a file under $folder and return its object path
     * (e.g. "att-training/abc123.jpg") for storage in the DB.
     */
    protected function gcsUpload(UploadedFile $file, string $folder): string
    {
        $config = config('filesystems.disks.gcs');
        $objectName = trim($folder, '/') . '/' . Str::random(40) . '.' . $file->getClientOriginalExtension();

        $this->gcsClient()->bucket($config['bucket'])->upload(
            fopen($file->getRealPath(), 'r'),
            ['name' => $objectName]
        );

        return $objectName;
    }

    protected function gcsDelete(?string $objectPath): void
    {
        if (!$objectPath) {
            return;
        }

        try {
            $config = config('filesystems.disks.gcs');
            $this->gcsClient()->bucket($config['bucket'])->object($objectPath)->delete();
        } catch (\Throwable $e) {
            // Object already gone or bucket unreachable — nothing to clean up.
        }
    }

    protected function gcsSignedUrl(?string $objectPath, int $minutes = 10): ?string
    {
        if (!$objectPath) {
            return null;
        }

        try {
            $config = config('filesystems.disks.gcs');

            return $this->gcsClient()->bucket($config['bucket'])->object($objectPath)->signedUrl(
                new \DateTimeImmutable("+{$minutes} minutes"),
                ['version' => 'v4']
            );
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function gcsDownload(?string $objectPath): ?string
    {
        if (!$objectPath) {
            return null;
        }

        try {
            $config = config('filesystems.disks.gcs');

            return $this->gcsClient()->bucket($config['bucket'])->object($objectPath)->downloadAsString();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
