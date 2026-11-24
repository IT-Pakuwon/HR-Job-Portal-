<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Http\UploadedFile;

/**
 * Attachment rules shared by the Tenant Support screens (Complaint, SIK, SIMKB):
 * images (JPG/JPEG/PNG) or video, max 5 MB per file, stored in the GCS bucket at
 * att-wps/<siteid>/<doctype>/<year>/<refnbr>/<file>. Needs UploadsToGcs on the same class.
 */
trait TenantAttachmentRules
{
    /** Error message, or null when the file is acceptable. */
    protected function tenantUploadError(UploadedFile $file): ?string
    {
        $ext = strtolower($file->getClientOriginalExtension());
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'mp4', 'mov', 'avi', 'mkv', 'webm', 'wmv', 'm4v', '3gp'], true)) {
            return 'Only image (JPG, JPEG, PNG) or video files are allowed.';
        }

        return $file->getSize() > 5 * 1024 * 1024 ? 'File size must not exceed 5 MB.' : null;
    }

    protected function tenantObjectPath(string $site, string $doctype, $year, string $nbr, string $file): string
    {
        return "att-wps/{$site}/{$doctype}/{$year}/{$nbr}/{$file}";
    }

    /** Object path of a stored attachment row; files saved before this rule live under the older "att-wps GPS" folder. */
    protected function tenantAttachmentObject($att, string $doctype): string
    {
        $object = $this->tenantObjectPath($att->siteid, $doctype, $att->period, $att->refnbr, $att->filename);
        try {
            if (! $this->gcsClient()->bucket(config('filesystems.disks.gcs.bucket'))->object($object)->exists()) {
                return "att-wps GPS/{$att->siteid}/{$doctype}/{$att->period}/{$att->refnbr}/{$att->filename}";
            }
        } catch (\Throwable $e) {
            // keep the new path
        }

        return $object;
    }
}
