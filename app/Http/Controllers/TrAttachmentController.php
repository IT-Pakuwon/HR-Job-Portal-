<?php

namespace App\Http\Controllers;

use App\Models\TrAttachment;
use Carbon\Carbon;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

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

    public function uploadAttachments(Request $request, string $doctype, string $refnbr)
    {
        // dd($request->all());
        $user = $request->user();

        if (!$request->hasFile('attachments')) {
            return response()->json(['success' => false, 'message' => 'No files received.'], 422);
        }

        $meta = [
            'refnbr'        => (string) $refnbr,
            'doctype'       => strtoupper($doctype),
            'cpnyid'        => $request->input('cpnyid'),
            'departementid' => $request->input('departementid'),
            // opsional (bisa di-derive otomatis di uploadInternal)
            'base_folder'   => 'att-purchasing-app/'.strtolower($doctype),
            'created_by'    => $user->username ?? 'system',
        ];

        try {
            $uploader = app(self::class);
            $uploader->uploadInternal($meta, (array) $request->file('attachments'));
            // setelah sukses, kirim daftar terbaru
            return $this->listAttachments($doctype, $refnbr);
        } catch (\Throwable $e) {
            \Log::error('uploadAttachments error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Upload failed: '.$e->getMessage()], 500);
        }
    }

    // === API: List ===
    public function listAttachments(string $doctype, string $refnbr)
    {
        $rows = TrAttachment::where('refnbr', $refnbr)
            ->where('doctype', strtoupper($doctype))
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

        // Signed URL
        $config = config('filesystems.disks.gcs');
        $keyFilePath = $config['key_file'];
        if (!Str::startsWith($keyFilePath, ['/','C:\\','D:\\'])) {
            $keyFilePath = base_path($keyFilePath);
        }
        $storage = new StorageClient([
            'projectId'   => $config['project_id'],
            'keyFilePath' => $keyFilePath,
        ]);
        $bucket = $storage->bucket($config['bucket']);

        $attachments = $rows->map(function ($r) use ($bucket) {
            $objectPath = rtrim($r->folder, '/').'/'.$r->filename;
            $signedUrl  = null;
            try {
                $signedUrl = $bucket->object($objectPath)->signedUrl(
                    new \DateTimeImmutable('+10 minutes'),
                    ['version' => 'v4']
                );
            } catch (\Throwable $e) {
                \Log::warning('Signed URL gagal', ['path' => $objectPath, 'error' => $e->getMessage()]);
            }

            return [
                'id'           => $r->id,
                'name'         => $r->attachment_name,    // label untuk UI
                'display_name' => $r->attachment_name,
                'created_user' => $r->created_by,
                'created_by'   => $r->created_by,
                'created_at'   => optional($r->created_at)->toDateTimeString(),
                'extention'    => $r->extention,
                'size'         => $r->filesize,
                'url'          => $signedUrl,
                'folder'       => $r->folder,
                'filename'     => $r->filename,
            ];
        });

        return response()->json(['success' => true, 'attachments' => $attachments]);
    }

    // === API: Soft-delete ===
    public function deleteAttachment(int $id)
    {
        $row = TrAttachment::find($id);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Attachment not found.'], 404);
        }
        $row->status = 'X';
        $row->save();

        return response()->json(['success' => true]);
    }

    public function removeAttachment($id)
    {
        try {
            $attachment = TrAttachment::findOrFail($id);
            $attachment->update(['status' => 'X']); // Update status ke "D" (Deleted)

            return response()->json(['success' => true, 'message' => 'Attachment status updated']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update attachment status', 'error' => $e->getMessage()], 500);
        }
    }
}
