<?php

namespace App\Http\Controllers;

use App\Models\TrAttachment;
use Carbon\Carbon;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MsKontrakDocument; 

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
                    'cpny_id'          => $meta['cpny_id']        ?? null,
                    'department_id'   => $meta['department_id'] ?? null,
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
    $user = Auth::user();
    $username = $user->username ?? 'system';

    $cpnyId = $request->input('cpny_id') ?? $request->input('cpnyid');
    $deptId = $request->input('department_id') ?? $request->input('departementid');

    // =========================================================
    // DEBUG: cek environment upload yg dipakai request WEB (FPM)
    // =========================================================
    \Log::info('UPLOAD DEBUG', [
        'doctype'            => $doctype,
        'refnbr'             => $refnbr,
        'content_type'       => $request->header('Content-Type'),
        'content_length'     => $request->server('CONTENT_LENGTH'),
        'post_max_size'      => ini_get('post_max_size'),
        'upload_max_filesize'=> ini_get('upload_max_filesize'),
        'max_file_uploads'   => ini_get('max_file_uploads'),
        'upload_tmp_dir'     => ini_get('upload_tmp_dir'),
        'sys_temp_dir'       => sys_get_temp_dir(),
        '_files_keys'        => array_keys($_FILES ?? []),
        '_files_attachments' => $_FILES['attachments'] ?? null,
    ]);

    // =========================================================
    // Ambil attachments (bisa 1 file atau array of files)
    // =========================================================
    $att = $request->file('attachments');

    $attList = [];
    if ($att instanceof \Illuminate\Http\UploadedFile) {
        $attList = [$att];
    } elseif (is_array($att)) {
        $attList = $att;
    }

    // =========================================================
    // Validasi file: kalau invalid / tmp kosong -> jangan proses
    // =========================================================
    $validFiles = [];
    $invalidInfo = [];

    foreach ($attList as $f) {
        if (!$f) {
            $invalidInfo[] = ['name' => null, 'size' => null, 'error' => null, 'isValid' => false, 'tmp' => null];
            continue;
        }

        $tmp = method_exists($f, 'getPathname') ? $f->getPathname() : null;

        $info = [
            'name'        => $f->getClientOriginalName(),
            'size'        => $f->getSize(),
            'error'       => $f->getError(),  // penting: ini kode error PHP upload
            'isValid'     => $f->isValid(),
            'tmp'         => $tmp,
            'tmp_exists'  => $tmp ? file_exists($tmp) : false,
            'tmp_readable'=> $tmp ? is_readable($tmp) : false,
            'client_mime' => $f->getClientMimeType(),
        ];

        // kalau upload gagal, biasanya isValid=false atau tmp kosong
        if (!$f->isValid() || empty($tmp) || !is_readable($tmp)) {
            $invalidInfo[] = $info;
            continue;
        }

        $validFiles[] = $f;
    }

    \Log::info('UPLOAD FILE CHECK', [
        'valid_count'   => count($validFiles),
        'invalid_count' => count($invalidInfo),
        'invalid'       => $invalidInfo,
    ]);

    // =========================================================
    // Kalau tidak ada file valid -> return 422 (bukan 500)
    // =========================================================
    if (count($validFiles) === 0) {
        return response()->json([
            'success' => false,
            'message' => 'No files received (upload failed before reaching server/tmp).',
            'invalid' => $invalidInfo, // lihat ini di response / log untuk tahu penyebabnya
        ], 422);
    }

    // =========================================================
    // Meta upload
    // =========================================================
    $meta = [
        'refnbr'        => (string) $refnbr,
        'doctype'       => strtoupper($doctype),
        'cpny_id'       => $cpnyId,
        'department_id' => $deptId,
        'base_folder'   => 'att-purchasing-app/' . strtolower($doctype),
        'created_by'    => $username,
    ];

    try {
        // Panggil uploadInternal langsung (tidak perlu app(self::class))
        $this->uploadInternal($meta, $validFiles);

        // setelah sukses, kirim daftar terbaru
        return $this->listAttachments($request, $doctype, $refnbr);

    } catch (\Throwable $e) {
        \Log::error('uploadAttachments error', [
            'doctype' => $doctype,
            'refnbr'  => $refnbr,
            'error'   => $e->getMessage(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Upload failed: ' . $e->getMessage(),
        ], 500);
    }
}
    public function uploadAttachments_xxx(Request $request, string $doctype, string $refnbr)
    {
        // dd($request->all());
        // $user = $request->user();
        $user = Auth::user();
        $username = $user ? $user->username : 'system';

        $cpnyId = $request->input('cpny_id') ?? $request->input('cpnyid');
        $deptId = $request->input('department_id') ?? $request->input('departementid');

        if (!$request->hasFile('attachments')) {
            return response()->json(['success' => false, 'message' => 'No files received.'], 422);
        }

        $meta = [
            'refnbr'        => (string) $refnbr,
            'doctype'       => strtoupper($doctype),
            'cpny_id'        => $cpnyId,
            'department_id' => $deptId,
            // opsional (bisa di-derive otomatis di uploadInternal)
            'base_folder'   => 'att-purchasing-app/'.strtolower($doctype),
            'created_by'    => $user->username ?? 'system',
        ];

        try {
            $uploader = app(self::class);
            $uploader->uploadInternal($meta, (array) $request->file('attachments'));
            // setelah sukses, kirim daftar terbaru
            return $this->listAttachments($request,$doctype, $refnbr);
        } catch (\Throwable $e) {
            \Log::error('uploadAttachments error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Upload failed: '.$e->getMessage()], 500);
        }
    }

    // === API: List ===
    public function listAttachments(Request $request, string $doctype, string $refnbr)
    {
        // dd($request->all());
        $cpnyId = $request->input('cpny_id') ?? $request->input('cpnyid');
        $query = TrAttachment::where('refnbr', $refnbr)
            ->where('doctype', strtoupper($doctype))
            ->where('status', 'A');

        if (!empty($cpnyId)) {
            $query->where('cpny_id', $cpnyId);
        }

        // $rows = $query->orderBy('created_at', 'desc')->get();
        $rows = $query
            ->orderBy('id', 'asc')
            ->orderBy('created_at', 'asc')
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

    public function uploadInternalKontrak(array $meta, array $filesWithDoc): array
    {
        foreach (['refnbr','doctype','base_folder','created_by'] as $k) {
            if (empty($meta[$k])) {
                throw new \InvalidArgumentException("Missing required meta: {$k}");
            }
        }

        $year       = now()->year;
        $yearFolder = rtrim($meta['base_folder'], '/')."/{$year}";

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

        // mapping kontrakdocument_id -> kontrakdocument_descr
        // (kalau sudah kamu supply dari meta, pakai itu. Kalau belum, kita build dari DB)
        $docMap = $meta['kontrak_doc_map'] ?? [];

        // auto build map jika kosong
        if (empty($docMap)) {
            $ids = collect($filesWithDoc)
                ->pluck('kontrakdocument_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (!empty($ids)) {
                $docMap = MsKontrakDocument::query()
                    ->whereIn('kontrakdocument_id', $ids)
                    ->pluck('kontrakdocument_descr', 'kontrakdocument_id')
                    ->toArray();
            }
        }

        foreach ($filesWithDoc as $row) {
            $file = $row['file'] ?? null;
            $kontrakDocId = $row['kontrakdocument_id'] ?? null;

            if (!$file instanceof UploadedFile || !$file->isValid()) {
                continue;
            }

            $originalName = str_replace(['%','\\','/'], '', $file->getClientOriginalName());
            $randomPrefix = md5(random_int(1, 99999999));
            $ext          = $file->getClientOriginalExtension();
            $filename     = $randomPrefix . '.' . $ext;
            $gcsPath      = "{$yearFolder}/{$filename}";

            // ✅ ambil label dari master kontrak document
            $attachmentLabel = null;
            if ($kontrakDocId !== null && $kontrakDocId !== '') {
                $attachmentLabel = $docMap[$kontrakDocId] ?? null;
            }
            // fallback kalau mapping tidak ketemu
            if (!$attachmentLabel) {
                $attachmentLabel = pathinfo($originalName, PATHINFO_FILENAME);
            }

            try {
                $bucket->upload(
                    fopen($file->getPathname(), 'r'),
                    [
                        'name'          => $gcsPath,
                        'predefinedAcl' => 'private',
                        'metadata'      => [
                            'contentType' => $file->getMimeType(),
                            'metadata'    => [
                                'original-name' => $originalName,
                                'kontrakdocument-id' => (string) $kontrakDocId,
                            ],
                        ],
                    ]
                );

                Log::info("Upload attachment kontrak sukses", ['gcsPath' => $gcsPath]);

                $rowDb = TrAttachment::create([
                    'refnbr'           => $meta['refnbr'],
                    'doctype'          => $meta['doctype'],
                    'attachment_date'  => Carbon::now(),
                    'cpny_id'          => $meta['cpny_id']        ?? null,
                    'department_id'    => $meta['department_id']  ?? null,
                    'attachment_name'  => $attachmentLabel, // 🔥 dari MsKontrakDocument
                    'folder'           => $yearFolder,
                    'filename'         => $filename,
                    'filesize'         => $file->getSize(),
                    'extention'        => $ext,
                    'status'           => 'A',
                    'created_by'       => $meta['created_by'],

                    // kalau kamu punya kolom ini di tr_attachment, bagus banget diisi:
                    // 'kontrakdocument_id' => $kontrakDocId,
                ]);

                $result['items'][] = [
                    'gcsPath'  => $gcsPath,
                    'id'       => $rowDb->id,
                    'filename' => $filename,
                    'kontrakdocument_id' => $kontrakDocId,
                ];
            } catch (\Throwable $e) {
                Log::error("Upload attachment kontrak gagal", [
                    'gcsPath' => $gcsPath,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        }

        return $result;
    }
}
