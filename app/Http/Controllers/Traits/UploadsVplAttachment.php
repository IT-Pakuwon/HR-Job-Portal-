<?php

namespace App\Http\Controllers\Traits;

use App\Models\Attachment;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Stores VPL (Receive/Transfer/Usage/Settlement) attachments in the
 * pkwjkt-attachment GCS bucket under att-vpl/{module}-attachment/{year}/
 * instead of the local public/attachment disk. Attachment::attachfile
 * holds the full GCS object path (relative to the bucket root).
 */
trait UploadsVplAttachment
{
    private function vplAttachmentBucket()
    {
        $config = config('filesystems.disks.gcs');
        $keyFilePath = $config['key_file'];
        if (!Str::startsWith($keyFilePath, ['/', 'C:\\', 'D:\\'])) {
            $keyFilePath = base_path($keyFilePath);
        }

        $storage = new StorageClient([
            'projectId'   => $config['project_id'],
            'keyFilePath' => $keyFilePath,
        ]);

        return $storage->bucket($config['bucket']);
    }

    /**
     * @param string $baseFolder e.g. 'att-vpl/vpr-attachment'
     */
    private function saveVplAttachments(Request $request, string $docid, string $baseFolder, int $year, $user): void
    {
        if (!$request->hasFile('attachment')) {
            return;
        }

        $bucket = $this->vplAttachmentBucket();
        $yearFolder = rtrim($baseFolder, '/')."/{$year}";

        foreach ($request->file('attachment') as $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            $rand = random_int(10000000, 99999999);
            $originalName = str_replace(['%', '\\', '/'], '', $file->getClientOriginalName());
            $filename = pathinfo($originalName, PATHINFO_FILENAME);
            $attachfile = md5((string) $rand).'-'.$originalName;
            $objectPath = "{$yearFolder}/{$attachfile}";

            $bucket->upload(
                fopen($file->getPathname(), 'r'),
                [
                    'name' => $objectPath,
                    'predefinedAcl' => 'private',
                    'metadata' => [
                        'contentType' => $file->getMimeType(),
                        'metadata' => ['original-name' => $originalName],
                    ],
                ]
            );

            // Attachment::$fillable only allows docid/created_user/status, so
            // mass-assignment via create() silently drops name/attachfile/extention.
            $attach = new Attachment();
            $attach->docid = $docid;
            $attach->name = $filename;
            $attach->attachfile = $objectPath;
            $attach->status = 'A';
            $attach->extention = $file->getClientOriginalExtension();
            $attach->created_user = $user->name;
            $attach->save();
        }
    }

    public function viewAttachment(int $id)
    {
        $attach = Attachment::where('id', $id)->where('status', 'A')->firstOrFail();

        $url = $this->vplAttachmentBucket()->object($attach->attachfile)->signedUrl(
            now()->addMinutes(15),
            ['version' => 'v4']
        );

        return redirect()->away($url);
    }
}
