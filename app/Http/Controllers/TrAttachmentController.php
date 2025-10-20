<?php

namespace App\Http\Controllers;

use App\Models\TrAttachment;
use Carbon\Carbon;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TrAttachmentController extends Controller
{
    
    public function uploadInternal(array $meta, array $files): array
    {
        // validasi ringan meta
        foreach (['refnbr','doctype','base_folder','created_by'] as $k) {
            if (empty($meta[$k])) {
                throw new \InvalidArgumentException("Missing required meta: {$k}");
            }
        }

        $year      = now()->year;
        $yearFolder= rtrim($meta['base_folder'], '/')."/{$year}";

        // --- Ambil config GCS
        $config      = config('filesystems.disks.gcs');
        $keyFilePath = $config['key_file'];
        if (!Str::startsWith($keyFilePath, ['/','C:\\','D:\\'])) {
            $keyFilePath = base_path($keyFilePath);
        }

        // --- Inisialisasi GCS
        $storage = new StorageClient([
            'projectId'   => $config['project_id'],
            'keyFilePath' => $keyFilePath,
        ]);
        $bucket = $storage->bucket($config['bucket']);

        $result = ['folder' => $yearFolder, 'items' => []];

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile || !$file->isValid()) {
                continue;
            }

            $originalName = str_replace(['%','\\','/'], '', $file->getClientOriginalName());
            $randomPrefix = md5(random_int(1, 99999999));
            $ext           = $file->getClientOriginalExtension();
            $filename     = $randomPrefix  . '.' . $ext;
            $gcsPath      = "{$yearFolder}/{$filename}";

            try {
                $bucket->upload(
                    fopen($file->getPathname(), 'r'),
                    [
                        'name'          => $gcsPath,
                        'predefinedAcl' => 'private', // ganti 'publicRead' jika ingin publik
                        // 'resumable'     => true,    // aktifkan kalau perlu stabilitas ekstra
                        'metadata'      => [
                            'contentType' => $file->getMimeType(),
                            'metadata'    => ['original-name' => $originalName],
                        ],
                    ]
                );

                Log::info("Upload attachment sukses", ['gcsPath' => $gcsPath]);

                $row = TrAttachment::create([
                    'refnbr'          => $meta['refnbr'],
                    'doctype'         => $meta['doctype'],
                    'attachment_date' => Carbon::now(),
                    'cpnyid'          => $meta['cpnyid']        ?? null,
                    'departementid'   => $meta['departementid'] ?? null,
                    'attachment_name' => pathinfo($originalName, PATHINFO_FILENAME),
                    'folder'          => $yearFolder,    // hanya sampai tahun
                    'filename'        => $filename,      // randomprefix-nama.ext
                    'filesize'        => $file->getSize(),
                    'extention'       => $ext,
                    'status'          => 'A',
                    'created_by'      => $meta['created_by'],
                ]);

                $result['items'][] = [
                    'gcsPath'  => $gcsPath,
                    'id'       => $row->id,
                    'filename' => $filename,
                ];
            } catch (\Throwable $e) {
                Log::error("Upload attachment gagal", ['gcsPath' => $gcsPath, 'error' => $e->getMessage()]);
                throw $e; // biar pemanggil bisa tangkap & balas 500 sesuai kebijakan mereka
            }
        }

        return $result;
    }
}
