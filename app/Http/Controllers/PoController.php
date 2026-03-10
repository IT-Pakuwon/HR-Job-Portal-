<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Autonbr;
use App\Models\User;
use App\Models\TrPO;
use App\Models\TrPOdetail;
use App\Models\MsVendor;
use App\Models\MsCompany;
use App\Models\TrSPPB;
use App\Models\TrSPPJ;
use App\Models\TrSPPK;
use App\Models\TrSPPT;
use App\Models\TrCS;
use Vinkla\Hashids\Facades\Hashids;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\TrAttachmentController;
use Illuminate\Support\Facades\Response;
use App\Models\TrAttachment;
use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Str;
use App\Models\MsTopdetail;
use App\Models\TrPOterm;
use App\Models\MsTop;
use App\Models\TrRfca;
use App\Models\TrPOReuse;
use App\Models\Bq;
use App\Models\TrPoLastPrice;
use App\Models\MsInventory;
use App\Models\TrReceipt;
use App\Models\MsEmailCcRule;
use App\Models\TrBQCS;
use App\Models\TrBQCSDetail;
use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\BusinessUnit;
use App\Models\SysCalendar;
use App\Models\TrCSDetail;   // add this

class PoController extends Controller
{
    use HasAutonbr;

    public function showPo($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        // Header PO
        $po = TrPO::findOrFail($id);

        // Detail PO
        $podetail = TrPOdetail::where('ponbr', $po->ponbr)
            ->where('budget_cpny_id', $po->cpny_id)
            ->orderBy('cs_no')
            ->get();

        $poTerms = MsTop::where('top_type', $po->potype)
        ->where('topid', $po->vendortop)
        ->first();

        // -------- Ambil lampiran dari tr_attachment & buat Signed URL --------
        $rows = TrAttachment::where('refnbr', $po->ponbr)
            ->where('cpny_id', $po->cpny_id)
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

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

        // map untuk view
        $attachment = $rows->map(function ($r) use ($bucket) {
            $objectPath = rtrim($r->folder, '/').'/'.$r->filename; // ex: att-purchasing-app/po/2025/xxx.pdf
            $object     = $bucket->object($objectPath);

            $signedUrl = null;
            try {
                $signedUrl = $object->signedUrl(
                    new \DateTimeImmutable('+10 minutes'),
                    ['version' => 'v4']
                );
            } catch (\Throwable $e) {
                \Log::warning('Signed URL gagal', ['path' => $objectPath, 'error' => $e->getMessage()]);
            }

            return (object) [
                'id'           => $r->id,
                'display_name' => $r->attachment_name,
                'created_by'   => $r->created_by,
                'created_at'   => $r->created_at,
                'url'          => $signedUrl,      // bisa null jika gagal
                'folder'       => $r->folder,
                'filename'     => $r->filename,
                'extention'    => $r->extention,
                'size'         => $r->filesize,
            ];
        });

        $eid_ponbr = Hashids::encode($po->ponbr);

        $prefix = strtoupper(substr((string) $po->sppbjktid, 0, 2));
        if ($prefix === 'PB') {
            $id = TrSPPB::where('sppbid', $po->sppbjktid)->value('id');
        } elseif ($prefix === 'PJ') {
            $id = TrSPPJ::where('sppjid', $po->sppbjktid)->value('id');
        } elseif ($prefix === 'PK') {
            $id = TrSPPK::where('sppkid', $po->sppbjktid)->value('id');
        } elseif ($prefix === 'PT') {
            $id = TrSPPT::where('spptid', $po->sppbjktid)->value('id');
        } else {
            abort(422, 'Invalid doc type');
        }
        $routeMap = [
            'PB' => 'showsppbs',
            'PJ' => 'showsppjs',
            'PK' => 'showsppks',
            'PT' => 'showsppts',
        ];

        $sppbUrl = null;
        if (!empty($po->sppbjktid) && isset($routeMap[$prefix])) {
            $sppbHash = Hashids::encode($id);
            $sppbUrl  = url("/{$routeMap[$prefix]}/{$sppbHash}");
        }

        $id = TrCS::where('csid', $po->csid)->value('id');

        $csUrl = null;
        if (!empty($po->csid)) {
            $csHash = Hashids::encode($id);
            $csUrl  = url("/showcs/{$csHash}");
        }

        $hasReceiptCompleted = TrReceipt::where('ponbr', $po->ponbr)
            ->where('vendorid', $po->vendorid)
            ->where('status', 'C')
            ->exists();

        $poHistory = TrReceipt::query()
            ->where('ponbr', $po->ponbr)
            ->when(!empty($po->vendorid), fn($q) => $q->where('vendorid', $po->vendorid))
            ->orderByDesc('receiptdate')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($r) {
                $r->receipt_eid = \Hashids::encode($r->id);

                $map = [
                    'P' => 'Pending',
                    'A' => 'Approved',
                    'R' => 'Rejected',
                    'C' => 'Completed',
                    'X' => 'Canceled',
                ];

                $st = $r->status ?? null;
                $r->status_text = $st && isset($map[$st]) ? $map[$st] : ($st ?? '-');

                return $r;
            });

        $holidayDates = SysCalendar::query()
            ->where('status', 'A') // active only
            ->whereIn('date_calendar_type', ['LIBUR_NASIONAL', 'CUTI_BERSAMA'])
            ->whereNull('deleted_at')
            ->pluck('date_calendar')
            ->values();

        return view('pages.purchase.showpo', [
            'po'          => $po,
            'podetail'    => $podetail,
            'attachment'  => $attachment,   // <- sudah dalam format siap pakai
            'hash'        => $hash,
            'eid_ponbr'   => $eid_ponbr,
            'sppbUrl'     => $sppbUrl,
            'csUrl'       => $csUrl,
            'hasReceiptCompleted' => $hasReceiptCompleted,
            'poHistory'   => $poHistory,
            'hash'        => $hash,
            'holidayDates'          => $holidayDates,
             'poTerms'     => $poTerms,
        ]);
    }

    public function submitPO(Request $req, $ponbr)
    {
        // dd($req->all());
        $po = TrPO::where('ponbr', $ponbr)
            ->where('cpny_id', $req->input('cpny_id'))
            ->firstOrFail();

        if ($po->status !== 'H') {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen hanya bisa di-Submit jika status = HOLD (H).'
            ], 422);
        }

        // Terima kedua nama field: po_deliverydate (dari view lama) atau podeliverydate (kolom DB)
        $deliveryDate = $req->input('podeliverydate') ?? $req->input('po_deliverydate');

        // Validasi dinamis sesuai po type
        if (strtoupper($po->potype ?? '') === 'PO') {
            $req->validate([
                'podeliverydate' => ['nullable','date'],
            ]);

            if (empty($deliveryDate)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The podeliverydate field is required.'
                ], 422);
            }
        } else {
            $req->validate([
                'work_date_from' => ['required','date'],
                'work_date_to'   => ['required','date','after_or_equal:work_date_from'],
                'work_days'      => ['required','integer','min:0'],
                'work_day_from'  => ['required','string'],
                'work_day_to'    => ['required','string'],
                'work_time_from' => ['required','date_format:H:i'],
                'work_time_to'   => ['required','date_format:H:i'],
                'manpower_total' => ['required','integer','min:0'],

                // Internal Pakuwon
                'spkpic'         => ['required','string'],
                'spkpicphone'    => ['required','string'],

                // Vendor
                'spkvendor'      => ['required','string'],
                'spkvendorphone' => ['required','string'],

                'warranty'       => ['required','string'],
            ]);
        }

        $start = Carbon::parse($req->input('work_date_from'));
        $end   = Carbon::parse($req->input('work_date_to'));

        $holidays = SysCalendar::query()
            ->where('status', 'A')
            ->whereIn('date_calendar_type', ['LIBUR_NASIONAL', 'CUTI_BERSAMA'])
            ->whereNull('deleted_at')
            ->pluck('date_calendar')
            ->toArray();

        $calculatedWorkingDays = 0;
        $current = $start->copy();

        $type = $req->input('work_day_type', 'EXCLUDE');


        while ($current <= $end) {

            $isWeekend = $current->isWeekend();
            $isHoliday = in_array($current->format('Y-m-d'), $holidays);

            if ($type === 'INCLUDE') {
                $calculatedWorkingDays++;
            } else {
                if (!$isWeekend && !$isHoliday) {
                    $calculatedWorkingDays++;
                }
            }

            $current->addDay();
        }
        if (strtoupper($po->potype ?? '') === 'SPK') {
            // Compare with input
            if ((int)$req->input('work_days') !== $calculatedWorkingDays) {
                return response()->json([
                    'success' => false,
                    'message' => 'Working days mismatch. Please recheck selected dates.'
                ], 422);
            }
        }


        DB::transaction(function () use ($po, $req, $deliveryDate) {
            $now = Carbon::now();
            $po->submitdate = $now;
            $po->updated_by = Auth::user()->username ?? 'system';

            if (strtoupper($po->potype ?? '') === 'PO') {
                // hanya simpan tanggal delivery
                $po->podeliverydate = $deliveryDate ? Carbon::parse($deliveryDate) : null;

                // (opsional) catat sedikit ringkasan di ponote
                // if ($deliveryDate) {
                //     $po->ponote = trim(($po->ponote ? $po->ponote."\n" : '') .
                //         'Delivery Date: '.Carbon::parse($deliveryDate)->format('d/m/Y'));
                // }
            } else {
                // simpan field SPK ke kolom yang tersedia di model
                $po->spkstartworkingdate = $req->input('work_date_from');
                $po->spkendtworkingdate  = $req->input('work_date_to');
                $po->spktotalday         = $req->input('work_days');
                $po->spkcarabayar        = 'Transfer';

                // schedule: "Hari X s/d Y Pukul a s/d b WIB"
                $schedule = sprintf(
                    'Hari %s s/d %s Pukul %s s/d %s WIB',
                    $req->input('work_day_from'),
                    $req->input('work_day_to'),
                    $req->input('work_time_from'),
                    $req->input('work_time_to')
                );
                $po->spkworkschedule = $schedule;

                // manpower & PIC
                $po->spkmanpower = $req->input('manpower_total');

                // Internal Pakuwon
                $po->spkpic         = $req->input('spkpic');
                $po->spkpicjabatan  = $req->input('spkpicjabatan');
                $po->spkpicphone    = $req->input('spkpicphone');
                $po->spkpicemail    = $req->input('spkpicemail');

                // Vendor
                $po->spkvendor         = $req->input('spkvendor');
                $po->spkvendorjabatan  = $req->input('spkvendorjabatan');
                $po->spkvendorphone    = $req->input('spkvendorphone');
                $po->spkvendoremail    = $req->input('spkvendoremail');

                $po->spkwarranty = $req->input('warranty');

                // simpan "cara pembayaran" ke ponote (kolom yang ada) — gunakan variabel berbeda!
                $pm = strtoupper($req->input('payment_method', '')); // <- TIDAK menimpa $po
                if (!empty($pm)) {
                    $po->ponote = trim(($po->ponote ? $po->ponote."\n" : '')."Cara Pembayaran: {$pm}");
                }
            }

            // 1. Cek Detail PO ada sebelum Used Budget
            $detailCount = TrPODetail::where('ponbr', $po->ponbr)
                ->where('budget_cpny_id', $po->cpny_id)
                ->count();
            if ($detailCount <= 0) {
                throw new \Exception("PO Detail kosong. Tidak bisa proses budget untuk PO {$po->ponbr}");
            }


            // ✅ INSERT/UPDATE last price
            if ($po->potype == 'PO'){
                $this->insertPoLastPrice($po);
            }

            // 3. Sync term dari TOP
            $this->syncPoTermsFromTop($po);

            // 4. Generate RFCA dari term DP
            $this->generateRfcaFromPo($po);

            // Used budget via SP (Submit)
            DB::connection('pgsql')->statement(
                'CALL public.sp_process_budget(?, ?, ?, ?)',
                ['PO', $po->ponbr, 'Submit', Auth::user()->username]
            );

            // 5. Update status ke Purchase Order
            $po->status = 'P';
            $po->send_email = false; // reset flag email
            $po->save();



        });

        return response()->json([
            'success' => true,
            'message' => 'Submit berhasil. Status berubah menjadi Purchase Order.'
        ]);
    }


    /** POST /po/{ponbr}/cancel-reuse */
    public function ReusePO(Request $req, $hash)
    {
        $decoded = Hashids::decode($hash);
        abort_if(empty($decoded), 404, 'Dokumen tidak ditemukan.');
        $id = $decoded[0];


        // $po = TrPO::where('ponbr', $ponbr)->firstOrFail();
        $po = TrPO::findOrFail($id);

        $data = $req->validate([
            'reason' => ['required','string']
        ]);

        $po->status     = 'D';
        $po->updated_by = Auth::user()->username ?? 'system';
        $po->updated_at = Carbon::now();

        // simpan reason ke ponote (append)
        $stamp = Carbon::now();
        $who   = Auth::user()->username ?? 'user';
        $reasonLine = "CANCEL REUSE: ".$data['reason'];
        $po->reuse = true;
        $po->reuse_at = $stamp;
        $po->save();

        // Insert detail ke tabel Reuse
        $this->insertPOReuse($po);

        $fakeReq = new \Illuminate\Http\Request([
            'docid'  => $po->ponbr,
            'reason' => $reasonLine,
        ]);

        app('App\Http\Controllers\SendCommentController')
                ->sendmsg($po->ponbr, 'PO', $fakeReq);

        // Used budget via SP (Reuse)
        DB::connection('pgsql')->statement(
            'CALL public.sp_process_budget(?, ?, ?, ?)',
            ['PO', $po->ponbr, 'Reuse', Auth::user()->username]
        );

        return response()->json([
            'success' => true,
            'message' => 'PO telah di-REUSE (C).'
        ]);
    }

    /** POST /po/{ponbr}/cancel */
    public function cancelPO(Request $req, $hash)
    {
        $decoded = Hashids::decode($hash);
        abort_if(empty($decoded), 404, 'Dokumen tidak ditemukan.');
        $id = $decoded[0];

        // $po = TrPO::where('ponbr', $ponbr)->firstOrFail();
        $po = TrPO::findOrFail($id);


        $data = $req->validate([
            'reason' => ['required','string']
        ]);

        $po->status     = 'X';
        $po->updated_by = Auth::user()->username ?? 'system';
        $po->updated_at = Carbon::now();

        // simpan reason ke ponote (append)
        $stamp = Carbon::now()->format('d/m/Y H:i');
        $who   = Auth::user()->username ?? 'user';
        $reasonLine = "CANCEL: ".$data['reason'];
        $po->save();

        $fakeReq = new \Illuminate\Http\Request([
            'docid'  => $po->ponbr,
            'reason' => $reasonLine,
        ]);

        app('App\Http\Controllers\SendCommentController')
                ->sendmsg($po->ponbr, 'PO', $fakeReq);

        // Used budget via SP (Cancel)
        DB::connection('pgsql')->statement(
            'CALL public.sp_process_budget(?, ?, ?, ?)',
            ['PO', $po->ponbr, 'Cancel', Auth::user()->username]
        );

        return response()->json([
            'success' => true,
            'message' => 'Status diubah menjadi CANCEL (X).'
        ]);
    }



    public function uploadAttachments_xxx(Request $request, $poid)
    {
        try {
            // $user = $request->user();
            $user = Auth::user();
            $username = $user ? $user->username : 'system';
            $year = (int) ($request->input('year') ?? now()->year);

            $created = [];

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $randomNumber = random_int(10000000, 99999999);
                    $filename     = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                    // bersihkan nama original dari %
                    $originalName = str_replace('%', '', $file->getClientOriginalName());
                    $ext        = $file->getClientOriginalExtension();
                    $attachfile = md5($randomNumber) . '.' . $ext;

                    // folder tujuan
                    $folder_attach = public_path('attachments/'.$year);
                    if (!is_dir($folder_attach)) {
                        @mkdir($folder_attach, 0777, true);
                    }

                    // pindahkan file (tanpa ekstensi di nama file, sesuai contoh kamu)
                    $file->move($folder_attach, $attachfile);

                    // simpan DB
                    $attach = new Attachment();
                    $attach->docid       = $poid;
                    $attach->name        = $filename; // tampilkan nama tanpa ekstensi
                    $attach->attachfile  = $attachfile;
                    $attach->status      = 'A';
                    $attach->extention   = $file->getClientOriginalExtension();
                    $attach->created_user= $user->username ?? 'system';
                    $attach->save();

                    $created[] = [
                        'id'         => $attach->id,
                        'name'       => $attach->name,
                        'attachfile' => $attach->attachfile,
                        'ext'        => $attach->extention,
                        'year'       => $year,
                    ];
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No files received.'
                ], 422);
            }

            return response()->json([
                'success'     => true,
                'attachments' => $created
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function uploadAttachments(Request $request, $hash)
    {
        try {
            $decoded = Hashids::decode($hash);
            abort_if(empty($decoded), 404, 'Dokumen tidak ditemukan.');
            $id = $decoded[0];

            $po = TrPO::findOrFail($id);
            $user       = $request->user();
            $year       = (int) ($request->input('year') ?? now()->year);
            $refnbr     = (string) $po->ponbr;     // PO => pakai ponbr sebagai refnbr
            $doctype    = 'PO';
            $cpnyid     = $po->cpny_id;
            $deptId     = $po->department_id;
            $createdBy  = $user->username ?? $user->id ?? 'system';

            if (!$request->hasFile('attachments')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No files received.',
                ], 422);
            }

            // === Delegasikan upload ke TrAttachmentController ===
            $meta = [
                'refnbr'        => $refnbr,
                'doctype'       => $doctype,
                'cpnyid'        => $cpnyid,
                'departementid' => $deptId,
                'base_folder'   => 'att-purchasing-app/'.strtolower($doctype), // <= PO
                'created_by'    => $createdBy,
            ];
            $files = (array) $request->file('attachments');

            try {
                /** @var \App\Http\Controllers\TrAttachmentController $uploader */
                $uploader     = app(TrAttachmentController::class);
                $uploadResult = $uploader->uploadInternal($meta, $files); // array paths (opsional)
            } catch (\Throwable $e) {
                \Log::error('PO uploadInternal gagal', ['error' => $e->getMessage()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal upload attachment: '.$e->getMessage(),
                ], 500);
            }

            // === Ambil ulang daftar attachment untuk dikembalikan ke FE (dengan Signed URL) ===
            $rows = TrAttachment::where('refnbr', $refnbr)
                ->where('cpnyid', $cpnyid)
                ->where('doctype', $doctype)
                ->where('status', 'A')
                ->orderBy('created_at', 'desc')
                ->get();

            // Siapkan Signed URL via GCS (biar FE bisa langsung klik)
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
                $objectPath = rtrim($r->folder, '/').'/'.$r->filename;   // ex: att-purchasing-app/po/2025/xxxx-file.pdf
                $object     = $bucket->object($objectPath);

                $signedUrl = null;
                try {
                    $signedUrl = $object->signedUrl(
                        new \DateTimeImmutable('+10 minutes'),
                        ['version' => 'v4']
                    );
                } catch (\Throwable $e) {
                    \Log::warning('Signed URL gagal', ['path' => $objectPath, 'error' => $e->getMessage()]);
                }

                return [
                    'display_name' => $r->attachment_name,
                    'created_by'   => $r->created_by,
                    'created_at'   => optional($r->created_at)->toDateTimeString(),
                    'extention'    => $r->extention,
                    'size'         => $r->filesize,
                    'url'          => $signedUrl,   // bisa null jika gagal
                    'folder'       => $r->folder,
                    'filename'     => $r->filename,
                ];
            });

            return response()->json([
                'success'     => true,
                'message'     => 'Files uploaded.',
                // kembalikan daftar terbaru agar view (yang “pakai yg atas”) bisa langsung render
                'attachments' => $attachments,
            ]);
        } catch (\Throwable $e) {
            \Log::error('PO uploadAttachments error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    Public function listAttachment($hash)
    {
        $decoded = Hashids::decode($hash);
        abort_if(empty($decoded), 404, 'Dokumen tidak ditemukan.');
        $id = $decoded[0];
        $po = TrPO::findOrFail($id);
        $ponbr = $po->ponbr;
        $doctype = 'PO';

        $rows = TrAttachment::where('refnbr', $ponbr)
            ->where('cpnyid', $po->cpny_id)
            ->where('doctype', $doctype)
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

        // GCS signed URL
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
            $object     = $bucket->object($objectPath);

            $signedUrl = null;
            try {
                $signedUrl = $object->signedUrl(
                    new \DateTimeImmutable('+10 minutes'),
                    ['version' => 'v4']
                );
            } catch (\Throwable $e) {
                \Log::warning('Signed URL gagal', ['path' => $objectPath, 'error' => $e->getMessage()]);
            }

            return [
                'id'            => $r->id,
                // alias supaya kompatibel dengan JS lama & baru
                'name'          => $r->attachment_name,   // << digunakan oleh JS kamu
                'display_name'  => $r->attachment_name,
                'created_user'  => $r->created_by,        // << digunakan oleh JS kamu
                'created_by'    => $r->created_by,
                'created_at'    => optional($r->created_at)->toDateTimeString(),
                'extention'     => $r->extention,
                'size'          => $r->filesize,
                'url'           => $signedUrl,            // bisa null jika gagal
                'folder'        => $r->folder,
                'filename'      => $r->filename,
            ];
        });

        return response()->json([
            'success'     => true,
            'attachments' => $attachments,
        ]);
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

        return response()->json(['success'=>true]);
    }

    public function printPO(string $hash)
    {
        $decoded = Hashids::decode($hash);
        abort_if(empty($decoded), 404, 'Dokumen tidak ditemukan.');
        $id = $decoded[0];

        $authUser = Auth::user();
        if (!$authUser) {
            return redirect()->route('login');
        }

        // =========================
        // PO HEADER
        // =========================
        $po = TrPO::findOrFail($id);

        // =========================
        // PO DETAIL
        // =========================
    $podetail = TrPOdetail::where('ponbr', $po->ponbr)
        ->where('budget_cpny_id', $po->cpny_id)
        ->orderBy('cs_no')
        ->get();

        // =========================
        // COMPANY
        // =========================
        $company = MsCompany::where('cpny_id', $po->cpny_id)->first();

        // =========================
        // TERM OF PAYMENT (TOP)
        // =========================
        $poTerms = MsTop::where('top_type', $po->potype)
            ->where('topid', $po->vendortop)
            ->first();

        // =========================
        // PO TERMS (DP / PAYMENT)
        // =========================
        $poTermDetails = TrPOterm::where('ponbr', $po->ponbr)
            ->where('cpny_id', $po->cpny_id)
            ->orderBy('order_term')
            ->get();

        $dpTerm = $poTermDetails->firstWhere('term_type', 'DP');

        $paymentTerms = $poTermDetails->where('term_type', '<>', 'DP');

        // =========================
        // AMOUNT
        // =========================
        $dpp   = (float) ($po->totalamt ?? 0);
        $ppn   = (float) ($po->taxamt ?? 0);
        $grand = (float) ($po->grandtotalamt ?? 0);

        // =========================
        // DP AMOUNT
        // =========================
        $dpAmount = 0;

        if ($dpTerm) {
            $dpAmount = ($dpTerm->payment_pct / 100) * $grand;
        }

        // =========================
        // RETENTION
        // =========================
        $retensi = MsTopdetail::where('topid', $po->vendortop)
            ->where('top_type', $po->potype)
            ->where('terms_type', 'RET')
            ->first();

        $retentionValue = null;

        if ($retensi) {
            $retentionValue = ($retensi->payment_pct / 100) * $grand;
        }
        // =========================
        // LOCATION FROM CS DETAIL
        // =========================
        $location = '-';

        if ($po->csid) {

            $csDetail = \App\Models\TrCSdetail::with(['location','subLocation'])
                ->where('csid', $po->csid)
                ->first();

            if ($csDetail) {

                $loc = optional($csDetail->location)->location_name;
                $sub = optional($csDetail->subLocation)->sub_location_name;

                if ($loc) {
                    $location = $loc;
                }

                if ($sub) {
                    $location .= ' - ' . $sub;
                }
            }
        }
        // =========================
        // TERBILANG
        // =========================
        $terbilang = ucfirst($this->terbilang($grand)) . ' rupiah';

        // =========================
        // PURCHASER
        // =========================
        $purchaser = ucwords(strtolower($po->created_by ?? 'System'));

        // =========================
        // DATA FOR VIEW
        // =========================
        $data = [
            'po'            => $po,
            'podetail'      => $podetail,
            'poTerms'       => $poTerms,
            'poTermDetails' => $poTermDetails,
            'dpTerm'        => $dpTerm,
            'dpAmount'      => $dpAmount,
            'paymentTerms'  => $paymentTerms,
            'retensi'       => $retentionValue,
            'location'      => $location,
            'dpp'           => $dpp,
            'ppn'           => $ppn,
            'grand'         => $grand,
            'terbilang'     => $terbilang,
            'company'       => $company,
            'now'           => Carbon::now(),
            'purchaser'     => $purchaser,
        ];

        // =========================
        // SELECT VIEW
        // =========================
        $potype = strtoupper((string) ($po->potype ?? ''));

        if ($potype === 'PO') {
            $view = 'pages.purchase.pdf_po';
        } else {
            $view = ($grand > 1_000_000_000)
                ? 'pages.purchase.pdf_spk'
                : 'pages.purchase.pdf_spk';
        }

        // Render view -> Dompdf
        $pdf = Pdf::loadView($view, $data)->setPaper('A4', 'portrait');

        /** @var \Dompdf\Dompdf $dompdf */
        $pdf = Pdf::loadView($view, $data)->setPaper('A4', 'portrait');

        /** @var \Dompdf\Dompdf $dompdf */
        $dompdf = $pdf->getDomPDF();
        $dompdf->render();

   $canvas  = $dompdf->get_canvas();
$w       = $canvas->get_width();
$h       = $canvas->get_height();

$metrics = $dompdf->getFontMetrics();
$font    = $metrics->get_font('sans-serif', 'normal');
$size    = 7;

$now = $data['now'];

$leftTxt  = "Created by: {$purchaser}, Sent by: {$purchaser}, On: " . $now->format('d/m/Y H:i');
$pageTpl  = "Page {PAGE_NUM} of {PAGE_COUNT}";
$parafTxt = "Paraf PIHAK KEDUA";

$margin = 20;

/* ============================= */
/* LEFT TEXT */
/* ============================= */

$canvas->page_text(
    $margin,
    $h - 22,
    $leftTxt,
    $font,
    $size,
    [0,0,0]
);

/* ============================= */
/* RIGHT BLOCK (justify-end) */
/* ============================= */

$pageWidth  = $metrics->getTextWidth($pageTpl, $font, $size);
$parafWidth = $metrics->getTextWidth($parafTxt, $font, $size);

$xPage  = $w - $pageWidth  - $margin;
$xParaf = $w - $parafWidth - $margin;

$canvas->page_text($xParaf, $h - 36, $parafTxt, $font, $size, [0,0,0]);
$canvas->page_text($xParaf,  $h - 23, $pageTpl,  $font, $size, [0,0,0]);
        $basename = ($potype === 'PO') ? 'PO' : 'SPK';
        return $dompdf->stream("{$basename}_{$po->ponbr}.pdf", ['Attachment' => false]);
    }

    public function printSpkBq(string $hash)
    {
        $ids = Hashids::decode($hash);
        abort_if(empty($ids), 404);
        $poId = $ids[0];

        $po = TrPO::findOrFail($poId);

        abort_if(strtoupper((string) $po->potype) !== 'SPK', 403);

        abort_if(empty($po->csid), 404);
        $cs = TrCS::where('csid', $po->csid)->firstOrFail();

        abort_if(empty($cs->bqid), 404);
        $bq = TrBQCS::where('bqid', $cs->bqid)->firstOrFail();

        $details = TrBQCSDetail::where('bqid', $bq->bqid)
            ->orderBy('bq_no')
            ->orderBy('bq_line_no')
            ->get();

        // =====================================================
        // (FIX) Ambil Business Unit dari TrPODetail (bukan TrBQCSDetail)
        // =====================================================
        $buId = TrPODetail::where('ponbr', $po->ponbr)
            ->where('budget_cpny_id', $po->cpny_id)
            ->whereNotNull('budget_business_unit_id')
            ->value('budget_business_unit_id'); // <-- ambil 1 saja

        $businessUnit = null;
        if ($buId) {
            $businessUnit = BusinessUnit::query()
                ->where('cpny_id', $po->cpny_id) // safety
                ->where('business_unit_id', $buId)
                ->select('business_unit_id', 'business_unit_name')
                ->first();
        }

        // =====================================================
        // Vendors (tetap)
        // =====================================================
        $vendors = [];
        for ($i = 1; $i <= 6; $i++) {
            $vid = $cs->{"vendorid{$i}"} ?? null;
            if (!$vid) continue;

            $vendors[] = [
                'idx'        => $i,
                'vendorid'   => $vid,
                'vendorname' => $cs->{"vendorname{$i}"} ?? '',
                'vendoraddr' => $cs->{"vendoralamat{$i}"} ?? '',
                'vendortelp' => $cs->{"vendortelp{$i}"} ?? '',
                'vendorcp'   => $cs->{"vendorcp{$i}"} ?? '',
                'mat_total'  => (float) ($bq->{"grandtotalmaterialvendor{$i}"} ?? 0),
                'jsa_total'  => (float) ($bq->{"grandtotaljasavendor{$i}"} ?? 0),
            ];
        }

        return Pdf::loadView('pages.purchase.pdf_bqspk', [
                'po'               => $po,
                'cs'               => $cs,
                'bq'               => $bq,
                'details'          => $details,
                'vendors'          => $vendors,
                'businessUnit'  => $businessUnit,
                'now'              => Carbon::now(),
            ])
            ->setPaper('A4', 'portrait')
            ->stream("BQ_SPK_{$po->ponbr}.pdf", ['Attachment' => false]);
    }


    public function printSpkBq_aca(string $hash)
    {
        $ids = Hashids::decode($hash);
        abort_if(empty($ids), 404);
        $poId = $ids[0];

        $po = TrPO::findOrFail($poId);

        abort_if(strtoupper((string) $po->potype) !== 'SPK', 403);

        abort_if(empty($po->csid), 404);
        $cs = TrCS::where('csid', $po->csid)->firstOrFail();

        abort_if(empty($cs->bqid), 404);
        $bq = TrBQCS::where('bqid', $cs->bqid)->firstOrFail();

        $details = TrBQCSDetail::where('bqid', $bq->bqid)
            ->orderBy('bq_no')
            ->orderBy('bq_line_no')
            ->get();


        $vendors = [];
        for ($i = 1; $i <= 6; $i++) {
            $vid = $cs->{"vendorid{$i}"} ?? null;
            if (!$vid) continue;

            $vendors[] = [
                'idx'        => $i,
                'vendorid'   => $vid,
                'vendorname' => $cs->{"vendorname{$i}"} ?? '',
                'vendoraddr' => $cs->{"vendoralamat{$i}"} ?? '',
                'vendortelp' => $cs->{"vendortelp{$i}"} ?? '',
                'vendorcp'   => $cs->{"vendorcp{$i}"} ?? '',
                'mat_total'  => (float) ($bq->{"grandtotalmaterialvendor{$i}"} ?? 0),
                'jsa_total'  => (float) ($bq->{"grandtotaljasavendor{$i}"} ?? 0),
            ];
        }

        return Pdf::loadView('pages.purchase.pdf_bqspk', [
                'po'      => $po,
                'cs'      => $cs,
                'bq'      => $bq,
                'details' => $details,
                'vendors' => $vendors,
                'now'     => Carbon::now(),
            ])
            ->setPaper('A4', 'portrait')
            ->stream("BQ_SPK_{$po->ponbr}.pdf", ['Attachment' => false]);
    }




    private function terbilang($angka): string
    {
        if (is_string($angka)) {
            $angka = str_replace([',', ' '], '', $angka);
        }
        if (!is_numeric($angka)) return '';

        $isMinus = $angka < 0;
        $angka = (int) abs((float) $angka);

        $bil = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        $fn = function ($n) use (&$fn, $bil): string {
            if ($n < 12)                  return ' '.$bil[$n];
            if ($n < 20)                  return $fn($n - 10).' belas';
            if ($n < 100)                 return $fn(intval($n / 10)).' puluh'.$fn($n % 10);
            if ($n < 200)                 return ' seratus'.$fn($n - 100);
            if ($n < 1000)                return $fn(intval($n / 100)).' ratus'.$fn($n % 100);
            if ($n < 2000)                return ' seribu'.$fn($n - 1000);
            if ($n < 1_000_000)           return $fn(intval($n / 1000)).' ribu'.$fn($n % 1000);
            if ($n < 1_000_000_000)       return $fn(intval($n / 1_000_000)).' juta'.$fn($n % 1_000_000);
            if ($n < 1_000_000_000_000)   return $fn(intval($n / 1_000_000_000)).' miliar'.$fn($n % 1_000_000_000);
            return $fn(intval($n / 1_000_000_000_000)).' triliun'.$fn($n % 1_000_000_000_000);
        };

        $hasil = trim(preg_replace('/\s+/', ' ', $fn($angka)));
        return ($isMinus ? 'minus ' : '').$hasil;
    }



    private function extractBodyHtml(string $fullHtml): string
    {
        try {
            $dom = new \DOMDocument();
            // suppress warning HTML tidak sempurna
            @$dom->loadHTML($fullHtml, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET);
            $body = $dom->getElementsByTagName('body')->item(0);
            if (!$body) return $fullHtml;

            $innerHTML = '';
            foreach ($body->childNodes as $child) {
                $innerHTML .= $dom->saveHTML($child);
            }
            return $innerHTML;
        } catch (\Throwable $e) {
            return $fullHtml;
        }
    }

    public function viewEmailPO(string $hash, Request $request)
    {
        $ponbr = Hashids::decode($hash)[0] ?? null;
        abort_if(!$ponbr, 404);

        $cpnyId = $request->query('cpny_id');

        $po = TrPO::where('ponbr', $ponbr)
            ->where('cpny_id', $cpnyId)
            ->firstOrFail();

        $eid = Hashids::encode($po->id);
        // $emailfrom = User::where('username', $po->created_by)->value('notification_email');
        $user = User::where('username', $po->created_by)
            ->first(['name', 'notification_email','email']);

        // $fromEmail = $user->notification_email;
        $fromEmail = $user->email;

        $purchaser = ucwords(strtolower($user->name));

        $emailto   = MsVendor::where('vendor_id', $po->vendorid)->value('email');
        // $emailto ='bedriamaail@pakuwon.com ; rikiparahat@pakuwon.com';

        $subject_email = $po->potype == 'PO'
            ? 'Purchase Order Nomor '.$ponbr.' untuk '.trim($po->vendorname).' - '.$po->keperluan
            : 'Surat Perintah Kerja Nomor '.$ponbr.' untuk '.trim($po->vendorname).' - '.$po->keperluan;

        $html = file_get_contents(public_path('template/email_templates.html'));

        // URL absolut ke gambar di public/template/po_footer.jpg
        $footerUrl = asset('template/po_footer.jpg');

        $map = [
            '${POTYPE}'      => $po->potype,
            '${PONBR}'       => $po->ponbr,
            '${VENDORNAME}'  => $po->vendorname,
            '${CSKEPERLUAN}' => $po->keperluan,
            '${CONTACTNAME}' => $po->vendorcp,
            '${PURCHASER}'   => $purchaser,
            '${FOOTER_URL}'  => $footerUrl, // <- ini penting
        ];

        $initial_html = strtr($html, $map);

        return view('pages.purchase.sendemailpo', [
            'ponbr'         => $ponbr,
            'po'            => $po,
            'vendor'        => $po->vendorname,
            'template'      => strtoupper($po->potype ?? 'PO'),
            'subject_email' => $subject_email,
            'from_email'    => $fromEmail,
            'purchaser'    => $purchaser,
            'to_email'      => $emailto,
            'initial_html'  => $initial_html,
            'eid'           => $eid,
        ]);
    }




    public function sendNowPO(Request $req, string $ponbr)
    {

        $authUser = Auth::user();
        $stamp = Carbon::now();

        $data = $req->validate([
            'from'    => ['required','email'],
            'to'      => ['required'],
            'cc'      => ['nullable'],
            'bcc'     => ['nullable'],
            'subject' => ['required','string','max:200'],
            'html'    => ['required','string'],
            'bq_vendor_idx' => ['nullable','integer','min:1','max:6'],

        ]);

        $cpnyId = $req->input('cpny_id') ?? $req->query('cpny_id');

        $po  = TrPO::where('ponbr', $ponbr)
            ->where('cpny_id', $cpnyId)
            ->firstOrFail();

        $podetail = TrPOdetail::where('ponbr', $po->ponbr)
            ->where('budget_cpny_id', $po->cpny_id)
            ->orderBy('cs_no')->get();

        $dpp       = $po->totalamt;
        $ppn       = $po->taxamt;
        $grand     = $po->grandtotalamt;
        $terbilang = ucfirst($this->terbilang($grand)) . ' rupiah';
        $company   = MsCompany::where('cpny_id', $po->cpny_id)->first();

        $purchaser = ucwords(strtolower($authUser->name ?? 'System'));

        $viewData = [
            'po'        => $po,
            'podetail'  => $podetail,
            'dpp'       => $dpp,
            'ppn'       => $ppn,
            'grand'     => $grand,
            'terbilang' => $terbilang,
            'company'   => $company,
            'now'       => Carbon::now(),
            'purchaser' => $purchaser,
        ];

        $senderName = User::where('notification_email', $data['from'])->value('name')
            ?: User::where('username', $po->created_by)->value('name')
            ?: (Auth::check() ? (Auth::user()->name ?? Auth::user()->fullname) : null)
            ?: 'Pakuwon System';

        // Normalisasi + validasi email
        $norm = function ($v) {
            if (!$v) return [];
            $arr = is_array($v)
                ? $v
                : preg_split('/[,;]+/', (string)$v);

            $arr = array_values(array_unique(array_filter(array_map('trim', $arr))));
            // filter valid email
            $arr = array_values(array_filter($arr, fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL)));
            return $arr;
        };

        $to  = $norm($data['to']);
        if (empty($to)) {
            return response()->json(['success'=>false,'message'=>'Field "To" wajib diisi.'], 422);
        }

        // =========================
        // CC dari table (default cc rules)
        // =========================
        $ccFromTable = MsEmailCcRule::query()
            ->where('status', 'A')
            ->where('cpny_id', $po->cpny_id)
            // ->where('department_id', $po->department_id) // pastikan kolom PO = department_id
            ->pluck('email')
            ->map(fn($e) => trim((string)$e))
            ->filter(fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL))
            ->values()
            ->all();

        // // CC dari request (kalau ada)
        // $ccFromRequest = $norm($data['cc'] ?? []);

        // // merge + unique
        // $cc  = array_values(array_unique(array_merge($ccFromTable, $ccFromRequest)));

        // CC dari request (kalau ada)
        $ccFromRequest = $norm($data['cc'] ?? []);

        // (NEW) CC otomatis ke email pengirim (from)
        $fromEmail = $data['from']; // ini pasti email valid karena sudah validate 'from' => email
        $ccFromSender = filter_var($fromEmail, FILTER_VALIDATE_EMAIL) ? [$fromEmail] : [];

        // merge + unique
        $cc = array_values(array_unique(array_merge($ccFromTable, $ccFromRequest, $ccFromSender)));

        // optional: jangan sampai from masuk juga ke TO (biar ga double)
        $cc = array_values(array_diff($cc, $to));

        $bcc = $norm($data['bcc'] ?? []);

        // ===== lampiran GCS (bagian kamu tetap) =====
        $rows = TrAttachment::where('refnbr', $po->ponbr)
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

        $config      = config('filesystems.disks.gcs');
        $keyFilePath = $config['key_file'];
        if (!Str::startsWith($keyFilePath, ['/', 'C:\\', 'D:\\'])) {
            $keyFilePath = base_path($keyFilePath);
        }
        $storage = new StorageClient([
            'projectId'   => $config['project_id'],
            'keyFilePath' => $keyFilePath,
        ]);
        $bucket = $storage->bucket($config['bucket']);

        $gcsAttachments = [];
        foreach ($rows as $r) {
            $objectPath = rtrim((string)$r->folder, '/').'/'.(string)$r->filename;
            try {
                $object = $bucket->object($objectPath);
                if ($object->exists()) {
                    $binary = $object->downloadAsString();
                    $name = $r->attachment_name ?: basename($objectPath);
                    $mime = $r->mimetype ?? ($object->info()['contentType'] ?? 'application/octet-stream');

                    $gcsAttachments[] = [
                        'data' => $binary,
                        'name' => $name,
                        'mime' => $mime,
                    ];
                } else {
                    \Log::warning('GCS object not found', ['path' => $objectPath]);
                }
            } catch (\Throwable $e) {
                \Log::warning('GCS download failed', ['path' => $objectPath, 'error' => $e->getMessage()]);
            }
        }

        // ===== generate PDF (bagian kamu tetap) =====
        $view = $po->potype === 'PO' ? 'pages.purchase.pdf_po' : 'pages.purchase.pdf_spk';
        $pdf  = PDF::loadView($view, $viewData)->setPaper('A4', 'portrait');

        $dompdf = $pdf->getDomPDF();
        $dompdf->render();

        $canvas  = $dompdf->get_canvas();
        $w       = $canvas->get_width();
        $h       = $canvas->get_height();

        $metrics = $dompdf->getFontMetrics();
        $font    = $metrics->get_font('sans-serif', 'normal');
        $size    = 9;

        $now       = $viewData['now'];
        $leftTxt   = "Created by: {$purchaser}, Sent by: {$purchaser}, On: " . $now->format('d/m/Y H:i');
        $rightTpl  = "Page {PAGE_NUM} of {PAGE_COUNT}";
        $rightW    = $metrics->getTextWidth($rightTpl, $font, $size);

        $y = $h - 28;
        $x = $canvas->get_width() - $w - 75;

        $canvas->page_text(20, $y, $leftTxt, $font, $size, [0,0,0]);
        $canvas->page_text($w - $x - $rightW - 20, $y, $rightTpl, $font, $size, [0,0,0]);

        $pdfBinary = $dompdf->output();
        $pdfName   = ($po->potype === 'PO' ? 'PO' : 'SPK') . "_{$po->ponbr}.pdf";

        // =========================
        // (FIX) Generate PDF BQCS Vendor berdasarkan PO->vendorid (ONLY)
        // =========================

        // =========================
        // (FIX) Generate PDF BQ SPK (template printSpkBq) ONLY vendor PO->vendorid
        // =========================
        $bqPdfBinary = null;
        $bqPdfName   = null;

        try {
            // PO harus punya csid
            $cs = TrCS::on('pgsql')
                ->where('csid', $po->csid)
                ->first();

            if ($cs && !empty($cs->bqid)) {
                $bq = TrBQCS::on('pgsql')
                    ->where('bqid', $cs->bqid)
                    ->first();

                if ($bq) {
                    $details = TrBQCSDetail::on('pgsql')
                        ->where('bqid', $bq->bqid)
                        ->orderBy('bq_no')
                        ->orderBy('bq_line_no')
                        ->get();

                    // =====================================================
                    // Business Unit (ambil 1) dari TrPODetail
                    // =====================================================
                    $buId = TrPODetail::where('ponbr', $po->ponbr)
                        ->where('budget_cpny_id', $po->cpny_id)
                        ->whereNotNull('budget_business_unit_id')
                        ->value('budget_business_unit_id');

                    $businessUnit = null;
                    if ($buId) {
                        $businessUnit = BusinessUnit::query()
                            ->where('cpny_id', $po->cpny_id)
                            ->where('business_unit_id', $buId)
                            ->select('business_unit_id', 'business_unit_name')
                            ->first();
                    }

                    // =====================================================
                    // Vendors ONLY vendor PO->vendorid
                    // =====================================================
                    $poVendorId = (string) ($po->vendorid ?? '');
                    $vendors = [];

                    if ($poVendorId !== '') {
                        for ($i = 1; $i <= 6; $i++) {
                            $vid = (string) ($cs->{"vendorid{$i}"} ?? '');
                            if ($vid === '') continue;

                            if (strcasecmp($vid, $poVendorId) !== 0) {
                                continue; // ✅ ONLY vendor PO
                            }

                            $vendors[] = [
                                'idx'        => $i,
                                'vendorid'   => $vid,
                                'vendorname' => $cs->{"vendorname{$i}"} ?? '',
                                'vendoraddr' => $cs->{"vendoralamat{$i}"} ?? '',
                                'vendortelp' => $cs->{"vendortelp{$i}"} ?? '',
                                'vendorcp'   => $cs->{"vendorcp{$i}"} ?? '',
                                'mat_total'  => (float) ($bq->{"grandtotalmaterialvendor{$i}"} ?? 0),
                                'jsa_total'  => (float) ($bq->{"grandtotaljasavendor{$i}"} ?? 0),
                            ];

                            break; // ✅ ketemu 1 vendor, stop loop
                        }
                    }

                    // kalau vendor PO tidak ada di CS vendor1..6, jangan buat PDF biar tidak salah
                    if (empty($vendors)) {
                        \Log::warning('BQ SPK PDF: vendor PO not found in CS vendor1..6', [
                            'ponbr'     => $po->ponbr,
                            'po_vendor' => $poVendorId,
                            'csid'      => $po->csid,
                            'bqid'      => $cs->bqid,
                        ]);
                    } else {
                        // =====================================================
                        // Render PDF pakai template yang sama seperti printSpkBq
                        // =====================================================
                        $bqPdf = \PDF::loadView('pages.purchase.pdf_bqspk', [
                            'po'           => $po,
                            'cs'           => $cs,
                            'bq'           => $bq,
                            'details'      => $details,
                            'vendors'      => $vendors,       // ✅ cuma 1 vendor
                            'businessUnit' => $businessUnit,
                            'now'          => Carbon::now(),
                        ])->setPaper('A4', 'portrait');

                        $bqDompdf = $bqPdf->getDomPDF();
                        $bqDompdf->render();

                        $bqPdfBinary = $bqDompdf->output();
                        $bqPdfName   = "BQ_SPK_{$po->ponbr}.pdf";
                    }
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('Generate BQ SPK PDF failed', [
                'ponbr' => $po->ponbr,
                'error' => $e->getMessage(),
            ]);
        }


        // $bqPdfBinary = null;
        // $bqPdfName   = null;

        // try {
        //     // ambil CS dari po->csid untuk dapat bqid (sesuaikan jika kolom beda)
        //     $cs = TrCS::on('pgsql')
        //         ->where('csid', $po->csid)
        //         ->first();

        //     if ($cs) {
        //         $bqid = $cs->bqid ?? null;

        //         if ($bqid) {
        //             // cari BQCS header berdasarkan bqid + csid
        //             $bq = TrBQCS::on('pgsql')
        //                 ->where('bqid', $bqid)
        //                 ->where('csid', $po->csid)
        //                 ->first();

        //             if ($bq) {

        //                 // =========================
        //                 // 1) Tentukan vendor idx dari PO->vendorid
        //                 // =========================
        //                 $poVendorId = (string) ($po->vendorid ?? '');
        //                 $idx = null;

        //                 if ($poVendorId !== '') {
        //                     for ($i = 1; $i <= 6; $i++) {
        //                         $csVendorId = (string) ($cs->{"vendorid{$i}"} ?? '');
        //                         if ($csVendorId !== '' && strcasecmp($csVendorId, $poVendorId) === 0) {
        //                             $idx = $i;
        //                             break;
        //                         }
        //                     }
        //                 }

        //                 // Jika vendor PO tidak ditemukan pada CS vendor1..6, STOP (jangan generate PDF salah vendor)
        //                 if (!$idx) {
        //                     \Log::warning('BQCS vendor idx not found by PO vendorid', [
        //                         'ponbr'     => $po->ponbr,
        //                         'po_vendor' => $poVendorId,
        //                         'csid'      => $po->csid,
        //                         'bqid'      => $bqid,
        //                     ]);
        //                 } else {

        //                     // =========================
        //                     // 2) Ambil vendor data sesuai idx tersebut (ONLY)
        //                     // =========================
        //                     $vendor = [
        //                         'id'   => $cs->{"vendorid{$idx}"} ?? null,
        //                         'name' => $cs->{"vendorname{$idx}"} ?? null,
        //                         'addr' => $cs->{"vendoralamat{$idx}"} ?? null,
        //                         'cp'   => $cs->{"vendorcp{$idx}"} ?? null,
        //                         'telp' => $cs->{"vendortelp{$idx}"} ?? null,
        //                         'top'  => $cs->{"vendortop{$idx}"} ?? null,
        //                     ];

        //                     // =========================
        //                     // 3) Ambil detail BQ
        //                     // =========================
        //                     $bqdetail = TrBQCSDetail::on('pgsql')
        //                         ->where('bqid', $bq->bqid)
        //                         ->orderBy('bq_no')
        //                         ->orderBy('bq_line_no')
        //                         ->get();

        //                     // =========================
        //                     // 4) Hitung total khusus vendor idx itu
        //                     // =========================
        //                     $grandTotalMaterial = 0;
        //                     $grandTotalJasa     = 0;

        //                     foreach ($bqdetail as $item) {
        //                         $qty  = (float) ($item->qty ?? 0);
        //                         $mat  = (float) ($item->{"vendorproductprice{$idx}"} ?? 0);
        //                         $jasa = (float) ($item->{"vendorjasaprice{$idx}"} ?? 0);

        //                         $grandTotalMaterial += $qty * $mat;
        //                         $grandTotalJasa     += $qty * $jasa;
        //                     }

        //                     $companyBq = MsCompany::where('cpny_id', $bq->cpny_id)->first();

        //                     $bqData = [
        //                         'title'     => 'CS Bills of Quantities (BQ)',
        //                         'doc_type'  => 'BQ',
        //                         'cpny_id'   => $companyBq->cpny_id ?? $bq->cpny_id,
        //                         'cpny_name' => $companyBq->cpny_name ?? '',
        //                         'vendor'    => $vendor,
        //                         'idx'       => $idx,
        //                         'grandTotalMaterial' => $grandTotalMaterial,
        //                         'grandTotalJasa'     => $grandTotalJasa,
        //                         'bq'        => $bq,
        //                         'bqdetail'  => $bqdetail,
        //                     ];

        //                     // =========================
        //                     // 5) Generate PDF
        //                     // =========================
        //                     $bqPdf = \PDF::loadView('pages.canvass.pdfbq_cs_vendor', $bqData)->setPaper('A4');

        //                     $bqDompdf = $bqPdf->getDomPDF();
        //                     $bqDompdf->render();

        //                     $bqPdfBinary = $bqDompdf->output();
        //                     $bqPdfName   = "BQCS_{$bq->bqid}_VENDOR_{$poVendorId}.pdf";
        //                 }
        //             }
        //         }
        //     }
        // } catch (\Throwable $e) {
        //     \Log::warning('Generate BQCS PDF failed', [
        //         'ponbr' => $po->ponbr,
        //         'error' => $e->getMessage(),
        //     ]);
        // }



        // ===== kirim email =====
        Mail::html($data['html'], function ($message) use ($data, $to, $cc, $bcc, $pdfBinary, $pdfName, $gcsAttachments, $senderName,$bqPdfBinary, $bqPdfName) {
            $message->from($data['from'], $senderName);
            $message->to($to);

            // ✅ cc hasil merge table + request
            if (!empty($cc))  $message->cc($cc);
            if (!empty($bcc)) $message->bcc($bcc);

            $message->subject($data['subject']);

            $message->attachData($pdfBinary, $pdfName, ['mime' => 'application/pdf']);

            foreach ($gcsAttachments as $att) {
                $message->attachData($att['data'], $att['name'], ['mime' => $att['mime'] ?? 'application/octet-stream']);
            }

            // attach BQCS vendor pdf kalau berhasil dibuat
            if (!empty($bqPdfBinary) && !empty($bqPdfName)) {
                $message->attachData($bqPdfBinary, $bqPdfName, ['mime' => 'application/pdf']);
            }

        });

        $po->send_email = true;
        $po->send_email_at = $stamp;
        $po->save();

        return response()->json([
            'success' => true,
            'message' => 'Email sudah dikirim beserta lampiran PDF & file dari GCS.',
            'cc_used' => $cc, // opsional untuk debugging
        ]);
    }



    public function sendNowPO_tanpa_cc(Request $req, string $ponbr)
    {
        $authUser = Auth::user();

        // 1) Validasi payload
        $data = $req->validate([
            'from'    => ['required','email'],
            'to'      => ['required'],
            'cc'      => ['nullable'],
            'bcc'     => ['nullable'],
            'subject' => ['required','string','max:200'],
            'html'    => ['required','string'],
        ]);

        // 2) Ambil PO + detail + data untuk view
        $po       = TrPO::where('ponbr', $ponbr)->firstOrFail();
        $podetail = TrPOdetail::where('ponbr', $po->ponbr)->orderBy('cs_no')->get();

        $dpp       = $po->totalamt;
        $ppn       = $po->taxamt;
        $grand     = $po->grandtotalamt;
        $terbilang = ucfirst($this->terbilang($grand)) . ' rupiah';
        $company   = MsCompany::where('cpny_id', $po->cpny_id)->first();

        $purchaser = ucwords(strtolower($authUser->name));

        $viewData = [
            'po'        => $po,
            'podetail'  => $podetail,
            'dpp'       => $dpp,
            'ppn'       => $ppn,
            'grand'     => $grand,
            'terbilang' => $terbilang,
            'company'   => $company,
            'now'       => Carbon::now(),
            'purchaser' => $purchaser,
        ];

        // 3) Tentukan display name pengirim
        $senderName = User::where('notification_email', $data['from'])->value('name')
            ?: User::where('username', $po->created_by)->value('name')
            ?: (Auth::check() ? (Auth::user()->name ?? Auth::user()->fullname) : null)
            ?: 'Pakuwon System';

        // 4) Normalisasi daftar email
        $norm = function ($v) {
            if (!$v) return [];
            if (is_array($v)) return array_values(array_unique(array_filter(array_map('trim',$v))));
            return array_values(array_unique(array_filter(array_map('trim', preg_split('/[,;]+/', $v)))));
        };
        $to  = $norm($data['to']);
        $cc  = $norm($data['cc'] ?? []);
        $bcc = $norm($data['bcc'] ?? []);
        if (empty($to)) {
            return response()->json(['success'=>false,'message'=>'Field "To" wajib diisi.'], 422);
        }

        // 5) Ambil lampiran dari TrAttachment (GCS)
        //    refnbr = nomor dokumen (pakai ponbr untuk PO), doctype opsional kalau dipakai
        $rows = TrAttachment::where('refnbr', $po->ponbr)
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

        // init GCS client sesuai config kamu
        $config      = config('filesystems.disks.gcs');
        $keyFilePath = $config['key_file'];
        if (!Str::startsWith($keyFilePath, ['/', 'C:\\', 'D:\\'])) {
            $keyFilePath = base_path($keyFilePath);
        }
        $storage = new StorageClient([
            'projectId'   => $config['project_id'],
            'keyFilePath' => $keyFilePath,
        ]);
        $bucket = $storage->bucket($config['bucket']);

        // download lampiran untuk di-attach
        $gcsAttachments = []; // [ ['data' => binary, 'name' => 'file.ext', 'mime' => 'application/octet-stream'], ... ]
        foreach ($rows as $r) {
            $objectPath = rtrim((string)$r->folder, '/').'/'.(string)$r->filename;
            try {
                $object = $bucket->object($objectPath);
                if ($object->exists()) {
                    // ambil biner
                    $binary = $object->downloadAsString();

                    // tentukan nama file & mime
                    $name = $r->attachment_name ?: basename($objectPath);
                    $mime = $r->mimetype ?? $object->info()['contentType'] ?? 'application/octet-stream';

                    $gcsAttachments[] = [
                        'data' => $binary,
                        'name' => $name,
                        'mime' => $mime,
                    ];
                } else {
                    \Log::warning('GCS object not found', ['path' => $objectPath]);
                }
            } catch (\Throwable $e) {
                \Log::warning('GCS download failed', ['path' => $objectPath, 'error' => $e->getMessage()]);
            }
        }

        // 6) Generate PDF + footer
        $view = $po->potype === 'PO' ? 'pages.purchase.pdf_po' : 'pages.purchase.pdf_spk';
        $pdf  = PDF::loadView($view, $viewData)->setPaper('A4', 'portrait');

        /** @var \Dompdf\Dompdf $dompdf */
        $dompdf = $pdf->getDomPDF();
        $dompdf->render();

        $canvas  = $dompdf->get_canvas();
        $w       = $canvas->get_width();
        $h       = $canvas->get_height();

        $metrics = $dompdf->getFontMetrics();
        $font    = $metrics->get_font('sans-serif', 'normal');
        $size    = 9;

        $now       = $viewData['now'];
        $leftTxt   = "Created by: {$purchaser}, Sent by: {$purchaser}, On: " . $now->format('d/m/Y H:i');
        $rightTpl  = "Page {PAGE_NUM} of {PAGE_COUNT}";
        $rightW    = $metrics->getTextWidth($rightTpl, $font, $size);

        $y = $h - 28;
        $x = $canvas->get_width() - $w - 75;

        $canvas->page_text(20, $y, $leftTxt, $font, $size, [0,0,0]);
        $canvas->page_text($w - $x - $rightW - 20, $y, $rightTpl, $font, $size, [0,0,0]);

        $pdfBinary = $dompdf->output();
        $pdfName   = ($po->potype === 'PO' ? 'PO' : 'SPK') . "_{$po->ponbr}.pdf";

        // 7) Kirim email
        Mail::html($data['html'], function ($message) use ($data, $to, $cc, $bcc, $pdfBinary, $pdfName, $gcsAttachments, $senderName) {
            $message->from($data['from'], $senderName);
            $message->to($to);
            if (!empty($cc))  $message->cc($cc);
            if (!empty($bcc)) $message->bcc($bcc);
            $message->subject($data['subject']);

            // attach PDF hasil render + footer
            $message->attachData($pdfBinary, $pdfName, ['mime' => 'application/pdf']);

            // attach file-file dari GCS
            foreach ($gcsAttachments as $att) {
                $message->attachData($att['data'], $att['name'], ['mime' => $att['mime'] ?? 'application/octet-stream']);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Email sudah dikirim beserta lampiran PDF & file dari GCS.'
        ]);
    }

    private function syncPoTermsFromTop(TrPO $po): void
    {
        $topId = $po->vendortop ?? null;

        $topDetails = MsTopdetail::where('topid', $topId)
            ->where('status', 'A')
            ->orderBy('order_term')
            ->get();

        if ($topDetails->isEmpty()) {
            return;
        }

        $bq = Bq::where('sppjtid', $po->sppbjktid)
            ->first();

        $username = Auth::user()->username ?? 'system';

        foreach ($topDetails as $detail) {

            // hitung bastamount = payment_pct% * total PO
            $poTotal    = $po->grandtotalamt ?? 0;
            $pct        = floatval($detail->payment_pct ?? 0);
            $bastAmount = $poTotal * ($pct / 100);

            TrPOterm::create([
                // ===== Header PO =====
                'ponbr'         => $po->ponbr,
                'cpny_id'       => $po->cpny_id,
                'csid'          => $po->csid ?? null,
                'sppbjktid'     => $po->sppbjktid ?? null,
                'bqid'          => $bq->bqid ?? null,
                'department_id' => $po->department_id ?? null,
                'user_peminta'  => $po->user_peminta ?? null,
                'keperluan'     => $po->keperluan ?? null,
                'vendorid'      => $po->vendorid ?? null,
                'vendorname'    => $po->vendorname ?? null,

                // ===== Term dari MsTopdetail =====
                'order_term'    => $detail->order_term,
                'terms_id'      => $detail->terms_id,
                'topid'         => $detail->topid,
                'top_type'      => $detail->top_type,
                'terms_name'    => $detail->terms_name,
                'payment_pct'   => $pct,
                'progress_pct'  => $detail->progress_pct ?? 0,
                'terms_type'    => $detail->terms_type,
                'flag_bast'     => $detail->flag_bast,

                // ===== Nominal =====
                'poamount'      => $poTotal,
                'bastamount'    => $bastAmount,
                'penalty'       => 0,
                'dayslate'      => 0,
                'realizeamount' => 0,

                // ===== Status & audit =====
                'rfcaid'        => null,
                'calrid'        => null,
                'bastid'        => null,
                'status'        => 'A',
                'created_by'    => $username,
                'updated_by'    => $username,
            ]);
        }
    }

    private function generateRfcaFromPo(TrPO $po): void
    {
        // Ambil semua term DP pada PO ini
        $dpTerms = TrPOterm::where('ponbr', $po->ponbr)
            ->where('cpny_id', $po->cpny_id)
            ->where('terms_type', 'DP')
            ->orderBy('order_term')
            ->get();

        if ($dpTerms->isEmpty()) {
            return;
        }

        $now      = Carbon::now();
        $year     = (int) $now->format('Y');
        $month    = $now->format('m');
        $doctype  = 'RC'; // kode dokumen RFCA (mengikuti konvensi yang sudah ada)
        $user     = Auth::user();
        $username = $user->username ?? 'system';

        foreach ($dpTerms as $term) {

            // === Generate nomor RFCA (rfcaid) pakai tabel autonbr, lockForUpdate ===
            // $autonbr = Autonbr::lockForUpdate()
            //     ->where('doctype', $doctype)
            //     ->where('year', $year)
            //     ->where('month', $month)
            //     ->first();

            // if (!$autonbr) {
            //     $autonbr = Autonbr::create([
            //         'doctype' => $doctype,
            //         'year'    => $year,
            //         'month'   => $month,
            //         'status'  => 'A',
            //         'number'  => 1,
            //     ]);
            //     $urutan = 1;
            // } else {
            //     $urutan = (int) $autonbr->number + 1;
            //     $autonbr->update(['number' => $urutan]);
            // }

            // $tglbln = substr((string) $year, 2) . $month;      // YYMM
            // $docid  = $doctype . $tglbln . sprintf("%04d", $urutan);
            // $rfcaid = $docid;

            $auto = $this->nextAutonbr(
                $doctype,
                $year,
                $month,
                $username,
                'RFCA'
            );
            $urutan = (int) $auto['next'];

            $tglbln = substr((string)$year, 2) . $month;   // YYMM
            $rfcaid  = $doctype . $tglbln . sprintf("%04d", $urutan);

            // Nominal: pakai data dari term PO
            $poAmount    = $term->poamount ?? ($po->grandtotalamt ?? 0);
            $rfcaAmount  = $term->bastamount ?? 0;  // utk DP biasanya = payment_pct * total PO

            TrRfca::create([
                'rfcaid'          => $rfcaid,
                'rfcadate'        => $now,                 // tanggal RFCA = sekarang
                'ponbr'           => $po->ponbr,
                'cpny_id'         => $po->cpny_id,
                'csid'            => $po->csid,
                'sppbjktid'       => $po->sppbjktid,
                'department_id'   => $po->department_id,
                'user_peminta'    => $po->user_peminta,
                'keperluan'       => $po->keperluan,

                'order_term'      => $term->order_term,
                'terms_id'        => $term->terms_id,
                'topid'           => $term->topid,
                'payment_pct'     => $term->payment_pct,

                'vendorid'        => $po->vendorid,
                'vendorname'      => $po->vendorname,

                'po_amount'       => $poAmount,
                'rfca_amount'     => $rfcaAmount,

                // Untuk RFCA pertama, previous info dikosongkan dulu
                'prev_rfcaid'       => null,
                'prev_ponbr'        => null,
                'prev_csid'         => null,
                'prev_rfca_amount'  => 0,
                'add_rfca_amount'   => 0,

                'required_date'   => $now->copy()->addDays(9),
                'calr_date'       => null,

                'status'          => 'A',
                'rfca_type'       => '',
                'rfca_step_order' => null,
                'rfca_step_id'    => null,
                'status_rfca'     => null,

                'created_by'      => $username,
                'updated_by'      => $username,
            ]);
        }
    }

    private function insertPOReuse($po)
    {
        $user     = Auth::user();
        $username = $user->username ?? 'system';

        // Ambil semua detail PO
        $details = \App\Models\TrPOdetail::where('ponbr', $po->ponbr)
            ->where('budget_cpny_id', $po->cpny_id)
            ->get();

        if ($details->isEmpty()) {
            return;
        }

        foreach ($details as $d) {

            // =========================
            // Hitung sisa qty (yang boleh di-reuse)
            // =========================
            $qty          = (float) ($d->qty ?? 0);
            $qtyReceived  = (float) ($d->qty_received ?? 0);
            $qtyReturn    = (float) ($d->qty_return ?? 0);
            $qtyCompleted = (float) ($d->qty_completed ?? 0);

            /**
             * Definisi "sisa" yang aman:
             * - qty_completed = sudah selesai (tidak boleh reuse)
             * - qty_return = balik/retur (anggap tidak boleh reuse)
             * - qty_received biasanya tidak mengurangi sisa order (karena received belum tentu completed),
             *   tapi user minta ikut dicek -> kita jadikan guard supaya sisa tidak lebih kecil dari yang sudah received.
             *
             * Jadi baseline: qty - completed - return
             */
            $remaining = $qty - $qtyCompleted - $qtyReturn;

            // Guard: tidak boleh kurang dari 0
            if ($remaining < 0) {
                $remaining = 0;
            }

            // Guard tambahan sesuai request: kalau sudah received melebihi remaining, remaining jangan lebih kecil dari (qty - received - return - completed)
            // (ini mencegah reuse qty yang sudah fisik diterima, jika itu memang aturan bisnis kamu)
            $remainingByReceived = $qty - $qtyReceived - $qtyReturn - $qtyCompleted;
            if ($remainingByReceived < 0) {
                $remainingByReceived = 0;
            }

            // pilih yang paling konservatif (paling kecil) supaya aman
            $remaining = min($remaining, $remainingByReceived);

            // Kalau tidak ada sisa → skip
            if ($remaining <= 0) {
                continue;
            }

            // =========================
            // Hitung sisa base qty (kalau kamu pakai base_qty)
            // =========================
            $baseQty          = (float) ($d->base_qty ?? 0);
            $baseQtyReceived  = (float) ($d->base_qty_received ?? 0);
            $baseQtyReturn    = (float) ($d->base_qty_return ?? 0);
            $baseQtyCompleted = (float) ($d->base_qty_completed ?? 0);

            $baseRemaining = $baseQty - $baseQtyCompleted - $baseQtyReturn;
            if ($baseRemaining < 0) $baseRemaining = 0;

            $baseRemainingByReceived = $baseQty - $baseQtyReceived - $baseQtyReturn - $baseQtyCompleted;
            if ($baseRemainingByReceived < 0) $baseRemainingByReceived = 0;

            $baseRemaining = min($baseRemaining, $baseRemainingByReceived);

            // kalau base_qty tidak dipakai/0, fallback proporsional dari remaining
            if ($baseRemaining <= 0 && $baseQty > 0 && $qty > 0) {
                $baseRemaining = ($baseQty / $qty) * $remaining;
            }

            // =========================
            // Insert TrPOReuse (qty = sisa)
            // =========================
            \App\Models\TrPOReuse::create([
                'cpny_id'                  => $po->cpny_id ?? null,

                'ponbr'                    => $d->ponbr,
                'po_no'                    => $d->po_no,
                'csid'                     => $d->csid,
                'cs_no'                    => $d->cs_no,
                'sppbjktid'                => $d->sppbjktid,

                // ⚠️ ini field di model kamu namanya sppbjktid_no (bukan sppbjktid_no?)
                // di code lama kamu pakai $d->sppbjktid_no tapi key insert pakai 'sppbjkt_no'
                // aku samakan jadi sppbjktid_no -> sppbjktid_no (sesuaikan kolom TrPOReuse kamu)
                'sppbjkt_no'               => $d->sppbjktid_no,

                'inventory_type'           => $d->inventory_type,
                'inventory_sub_type'       => $d->inventory_sub_type,
                'inventory_category'       => $d->inventory_category,
                'inventoryid'              => $d->inventoryid,
                'inventory_descr'          => $d->inventory_descr,

                // ✅ pakai sisa
                'qty'                      => $remaining,
                'uom'                      => $d->uom,
                'siteid'                   => $d->siteid,

                'type_multiplier'          => $d->type_multiplier,
                'base_multiplier'          => $d->base_multiplier,
                'base_qty'                 => $baseRemaining,
                'base_uom'                 => $d->base_uom,

                'budget_perpost'           => $d->budget_perpost,
                'budget_cpny_id'           => $d->budget_cpny_id,
                'budget_business_unit_id'  => $d->budget_business_unit_id,
                'budget_department_fin_id' => $d->budget_department_fin_id,
                'budget_account_id'        => $d->budget_account_id,
                'budget_activity_id'       => $d->budget_activity_id,
                'budget_activity_descr'    => $d->budget_activity_descr,

                // default openordered/ordered dkk
                'openordered'              => $remaining,
                'ordered'                  => 0,
                'rejectordered'            => 0,
                'completeordered'          => 0,

                'status'                   => 'D', // reuse
                'created_by'               => $username,
                // created_at otomatis kalau TrPOReuse pakai timestamps=true,
                // tapi kalau tidak, ini aman:
                'created_at'               => now(),
            ]);
        }
    }




    private function insertPoLastPrice(TrPO $po): void
    {
        $username = Auth::user()->username ?? 'system';
        $now = Carbon::now();

        $details = TrPODetail::where('ponbr', $po->ponbr)
            ->where('budget_cpny_id', $po->cpny_id)
            ->get();

        foreach ($details as $d) {
            // optional: ambil info inventory untuk type/subtype/category
            $inv = null;
            if (!empty($d->inventoryid)) {
                $inv = MsInventory::query()
                    ->where('inventoryid', $d->inventoryid)
                    ->first();
            }

            // Kalau kamu mau “last price” per vendor+inventory, biasanya lebih aman pakai updateOrCreate
            // key bisa kamu sesuaikan (ini contoh)
            TrPoLastPrice::updateOrCreate(
                [
                    'cpny_id'     => $po->cpny_id,
                    'vendorid'    => $po->vendorid,
                    'inventoryid' => $d->inventoryid,
                ],
                [
                    'ponbr'              => $po->ponbr,
                    'podate'             => $po->podate ?? $po->submitdate ?? $now, // sesuaikan field tanggal PO kamu
                    'csid'               => $po->csid ?? null,
                    'sppbjktid'          => $po->sppbjktid ?? null,

                    'vendorname'         => $po->vendorname ?? null,

                    'inventory_type'     => $inv->inventory_type ?? ($d->inventory_type ?? null),
                    'inventory_sub_type' => $inv->item_sub_type ?? ($d->inventory_sub_type ?? null),
                    'inventory_category' => $inv->item_category ?? ($d->inventory_category ?? null),

                    'inventory_descr'    => $d->inventory_descr ?? ($inv->inventory_descr ?? null),

                    'qty'                => $d->qty ?? 0,
                    'uom'                => $d->uom ?? null,
                    'siteid'             => $d->siteid ?? null,

                    'unitcost'           => $d->unitcost ?? 0,
                    'taxcodeid'          => $d->taxcodeid ?? null,
                    'taxamt'             => $d->taxamt ?? 0,
                    'totalcost'          => $d->totalcost ?? (($d->qty ?? 0) * ($d->unitcost ?? 0) + ($d->taxamt ?? 0)),

                    'status'             => 'A',
                    'purchaser'          => $username,
                    'updated_by'         => $username,
                    'updated_at'         => $now,

                    // kalau record baru, fill created
                    'created_by'         => $username,
                    'created_at'         => $now,
                ]
            );
        }
    }

    public function completePartial(Request $request, $ponbr)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:3'],
        ]);

        // ✅ inject reason supaya bisa dibaca SendCommentController lewat $request->input('message') / 'comment'
        // sesuaikan key yang dipakai di SendCommentController (lihat catatan di bawah)
        $request->merge([
            'message' => $data['reason'],      // untuk controller yang pakai 'message'
            'comment' => $data['reason'],      // untuk controller yang pakai 'comment'
            'doctype' => 'PO',
            'refnbr'  => $ponbr,
        ]);

        return DB::transaction(function () use ($ponbr, $data, $user, $request) {

            $po = TrPO::where('ponbr', $ponbr)
                ->where('cpny_id', $user->cpny_id)
                ->lockForUpdate()->firstOrFail();

            // guard status
            if (in_array($po->status, ['H','X','R','C'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status PO tidak valid untuk completed.'
                ], 422);
            }

            // lock all details
            $details = TrPOdetail::where('ponbr', $ponbr)
                ->where('budget_cpny_id', $user->cpny_id)
                ->lockForUpdate()
                ->orderBy('id')
                ->get();

            if ($details->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'PO detail tidak ditemukan.'], 404);
            }

            $hasAnyReceived = $details->contains(fn($d) => (float)($d->qty_received ?? 0) > 0);
            if (!$hasAnyReceived) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak bisa completed: belum ada item yang diterima (qty_received masih 0 semua).'
                ], 422);
            }

            $affected = 0;

            foreach ($details as $d) {
                $qty       = (float)($d->qty ?? 0);
                $received  = (float)($d->qty_received ?? 0);
                $ret       = (float)($d->qty_return ?? 0);
                $completed = (float)($d->qty_completed ?? 0);

                // qty_sisa = qty - qty_received - qty_completed + qty_return
                $sisa = max($qty - $received - $completed + $ret, 0);

                if ($sisa <= 0.00001) {
                    continue;
                }

                $newCompleted = $completed + $sisa;

                $d->qty_completed = $newCompleted;
                $d->base_qty_completed = $newCompleted; // kalau ada multiplier, sesuaikan
                $d->completed = true;
                $d->updated_by = $user->username ?? $user->name ?? 'system';
                $d->updated_at = Carbon::now();
                $d->save();

                $affected++;
            }

            if ($affected < 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada sisa qty yang bisa di-complete.'
                ], 422);
            }

            // ✅ header status jadi Completed
            $po->status = 'C';

            // ✅ kirim reason ke SendCommentController
            try {
                app(\App\Http\Controllers\SendCommentController::class)
                    ->sendmsg($ponbr, 'PO', $request);
            } catch (\Throwable $e) {
                // optional: log
                // \Log::warning('SendComment failed', ['err' => $e->getMessage()]);
            }

            // Used budget via SP (Completed)
            DB::connection('pgsql')->statement(
                'CALL public.sp_process_budget(?, ?, ?, ?)',
                ['PO', $po->ponbr, 'Completed', Auth::user()->username]
            );

            $po->updated_by = $user->username ?? $user->name ?? 'system';
            $po->updated_at = Carbon::now();
            $po->save();

            return response()->json([
                'success' => true,
                'message' => "Partial completed berhasil. {$affected} item detail di-update (qty_completed ditambah sisa).",
            ]);
        });
    }

}
