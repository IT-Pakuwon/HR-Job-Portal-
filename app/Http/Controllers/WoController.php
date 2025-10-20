<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Autonbr;
use App\Models\T_Message;
use App\Models\Attachment;
use App\Models\M_approval;
use App\Models\M_approval_other;
use App\Models\T_approval;
use App\Models\Company;
use App\Models\Dept;
use App\Models\Usercpny;
use App\Models\Userdept;
use App\Models\User;
use App\Models\Site;
use App\Models\Division;
use App\Models\TrWO;
use App\Models\TrWOdetail;
use App\Models\MsLocationPG;
use App\Models\MsSubLocationPG;
use Mail;
use Illuminate\Support\Facades\Log;
use PDF;
use Vinkla\Hashids\Facades\Hashids;
use App\Http\Controllers\TrAttachmentController;
use Illuminate\Support\Facades\Response;
use App\Models\TrAttachment;
use Illuminate\Support\Str;
use Google\Cloud\Storage\StorageClient;


class WoController extends Controller
{
    public function index()
    {
        $all = TrWO::count();
        $onProgress = TrWO::where('status', 'P')->count();
        $reject = TrWO::where('status', 'R')->count();
        $revise = TrWO::where('status', 'D')->count();
        $completed = TrWO::where('status', 'C')->count();
       
        return view('pages.wos.wos', compact('all', 'onProgress', 'reject', 'revise', 'completed'));
    }

    
   public function json(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $search = trim((string) $request->input('search.value', ''));
        $status = (string) $request->query('status', ''); // '' = all

        $baseTable = (new TrWO)->getTable(); // "tr_wo"

        // urutan kolom untuk sorting server-side (disesuaikan dengan kolom yang ditampilkan)
        $columns = [
            0 => 'wo.woid',
            1 => 'wo.wodate',
            2 => 'wo.cpny_id',
            3 => 'wo.department_id',
            4 => 'wt.worktype_name', // << dari ms_worktype
            5 => 'wo.worequest',
            6 => 'wo.keperluan',
            7 => 'wo.status',
        ];

        $orderIdx = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'asc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'wo.woid';

        $base = TrWO::from($baseTable.' as wo')
            ->leftJoin('ms_worktype as wt', function ($join) {
                $join->on('wt.worktypeid', '=', 'wo.worktypeid'); // << relasi baru
            });

        if ($status !== '') {
            $base->where('wo.status', $status);
        }

        $recordsTotal = (clone $base)->distinct('wo.woid')->count('wo.woid');

        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('wo.woid',            'like', "%{$search}%")
                ->orWhere('wo.cpny_id',       'like', "%{$search}%")
                ->orWhere('wo.department_id', 'like', "%{$search}%")
                ->orWhere('wt.worktype_name', 'like', "%{$search}%") // << cari di worktype_name
                ->orWhere('wo.worequest',     'like', "%{$search}%")
                ->orWhere('wo.keperluan',     'like', "%{$search}%")
                ->orWhere('wo.status',        'like', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $base)->distinct('wo.woid')->count('wo.woid');

        $data = $base->select(
                    'wo.id',                // untuk hashids -> eid
                    'wo.woid',
                    'wo.wodate',
                    'wo.cpny_id',
                    'wo.department_id',
                    'wt.worktype_name',     // << ditampilkan ke tabel
                    'wo.worequest',
                    'wo.keperluan',
                    'wo.status',
                    'wo.created_by'
                )
                ->orderBy($orderCol, $orderDir)
                ->orderBy('wo.woid', 'desc')
                ->skip($start)
                ->take($length)
                ->get();

        // tambahkan eid, sembunyikan id asli
        $data->transform(function ($row) {
            $row->eid = Hashids::encode($row->id);
            unset($row->id);
            return $row;
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }



    public function createWo()
    {        
        $user = request()->user();
        $usercpny = Usercpny::where('username', '=', $user->username)
            ->get();
        $usercpny2 = Usercpny::where('username', '=', $user->username)
            ->first();
        $userdept = Userdept::where('username', '=', $user->username)
            ->get();
        $userdept2 = Userdept::where('username', '=', $user->username)
            ->first();                     
       
        return view('pages.wos.createwos', compact('usercpny','usercpny2','userdept','userdept2'));
    }

    
    
    public function storeWo(Request $request)
    {
        $validated = $request->validate([
            'cpnyid'          => ['required','string','max:20'],
            'departementid'   => ['required','string','max:100'],
            'wotype'          => ['required','string','max:100'],
            'worequest'       => ['required','string','max:100'],
            'worktypeid'      => ['required','string','max:50'],
            'subworktypeid'   => ['required','string','max:50'],
            'picrequester'    => ['required','string','max:100'],
            'biaya_wo'        => ['nullable','string','max:50'], // string dulu, kita normalisasi manual
            'location_id'     => ['required','string','max:50'],
            'sub_location_id' => ['required','string','max:50'],
            'keperluan'       => ['nullable','string','max:1000'],
            // 'attachments.*' => ['file','mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,zip','max:5120'], // opsional
        ], [
            'cpnyid.required' => 'Company wajib.',
            'departementid.required' => 'Department wajib.',
            'wotype.required' => 'WO Type wajib.',
            'worequest.required' => 'WO Request wajib.',
            'worktypeid.required' => 'Worktype wajib.',
            'subworktypeid.required' => 'Sub Worktype wajib.',
            'location_id.required' => 'Location wajib.',
            'sub_location_id.required' => 'Sub Location wajib.',
            'picrequester.required' => 'PIC Requester wajib.',
        ]);

        $doctype  = 'WO';
        $user     = $request->user();
        $username = $user->username ?? 'system';
        $fullname = $user->name ?? 'system';

        $dt        = \Carbon\Carbon::now();
        $year      = $dt->year;
        $month     = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        $datestamp = $dt->toDateTimeString();

        // normalisasi angka lokal → float
        $toFloat = function ($v): ?float {
            if ($v === null || $v === '') return null;
            $s = preg_replace('/\s+/', '', (string)$v);
            $hasComma = strpos($s, ',') !== false;
            $hasDot   = strpos($s, '.') !== false;

            if ($hasComma && $hasDot) {
                $lastComma = strrpos($s, ',');
                $lastDot   = strrpos($s, '.');
                if ($lastComma > $lastDot) {
                    $s = str_replace('.', '', $s);
                    $s = str_replace(',', '.', $s);
                } else {
                    $s = str_replace(',', '', $s);
                }
            } elseif ($hasComma) {
                $s = str_replace(',', '.', $s);
            } elseif ($hasDot) {
                if (substr_count($s, '.') > 1) {
                    $s = str_replace('.', '', $s);
                }
            }
            return is_numeric($s) ? (float)$s : null;
        };

        // pastikan line approval ada
        $approvalCount = M_approval::where([
            ['status', '=', 'A'],
            ['aprvcpnyid', '=', $validated['cpnyid']],
            ['aprvdeptid', '=', $validated['departementid']],
            ['aprvdoctype', '=', $doctype],
        ])->count();

        if ($approvalCount === 0) {
            return response()->json([
                'message' => 'Approval line belum di-setup, Please contact IT!',
            ], 422);
        }

        \DB::beginTransaction();
        try {
            // === generate autonbr & docid (lock) ===
            $autonbr = Autonbr::lockForUpdate()
                ->where('doctype', $doctype)
                ->where('year', $year)
                ->where('month', $month)
                ->first();

            if (!$autonbr) {
                $autonbr = Autonbr::create([
                    'doctype' => $doctype,
                    'year'    => $year,
                    'month'   => $month,
                    'status'  => 'A',
                    'number'  => 1,
                ]);
                $urutan = 1;
            } else {
                $urutan = $autonbr->number + 1;
                $autonbr->update(['number' => $urutan]);
            }

            $tglbln = substr($year, 2) . $month;               // YYMM
            $docid  = $doctype . $tglbln . sprintf("%04d", $urutan);

            // === header ===
            $wo = new TrWO();
            $wo->woid           = $docid; // <<< PENTING: simpan nomor dokumen
            $wo->cpny_id        = $validated['cpnyid'];
            $wo->department_id  = $validated['departementid'];
            $wo->wotype         = $validated['wotype'];
            $wo->worequest      = $validated['worequest'];
            $wo->worktypeid     = $validated['worktypeid'];
            $wo->subworktypeid  = $validated['subworktypeid'];
            $wo->picrequester   = $validated['picrequester'];
            $wo->biaya_wo       = $toFloat($validated['biaya_wo'] ?? null) ?? 0; // <<< pakai normalisasi
            $wo->location_id    = $validated['location_id'];
            $wo->sub_location_id= $validated['sub_location_id'];
            $wo->keperluan      = $validated['keperluan'] ?? null;

            $wo->wodate         = $dt;     // pakai $dt biar konsisten
            $wo->status         = 'P';     // default
            $wo->created_by     = $username;
            $wo->save();

            // === copy line approval (M_approval -> T_approval) ===
            $approvals = M_approval::where([
                ['status', '=', 'A'],
                ['aprvcpnyid', '=', $validated['cpnyid']],
                ['aprvdeptid', '=', $validated['departementid']],
                ['aprvdoctype', '=', $doctype],
            ])->orderBy('aprvid')->get();

            foreach ($approvals as $a) {
                T_approval::create([
                    'docid'          => $docid,
                    'aprvid'         => $a->aprvid,
                    'aprvdoctype'    => $a->aprvdoctype,
                    'aprvcpnyid'     => $a->aprvcpnyid,
                    'aprvdeptid'     => $a->aprvdeptid,
                    'aprvusername'   => $a->aprvusername,
                    'name'           => $a->name,
                    'aprvdatebefore' => $a->aprvid == 1 ? $datestamp : null,
                    'aprvtotalday'   => 1,
                    'status'         => 'P',
                    'created_by'     => $username,
                ]);
            }
            
            // === attachments (opsional) ===
            // if ($request->hasFile('attachments')) {
            //     foreach ($request->file('attachments') as $file) {
            //         if (!$file->isValid()) continue;

            //         $randomNumber = random_int(10000000, 99999999);
            //         $filenameNoExt = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            //         $ext           = $file->getClientOriginalExtension();
            //         $attachfile    = md5($randomNumber) . '.' . $ext;

            //         $folder_attach = public_path('attachments/'.$year);
            //         if (!is_dir($folder_attach)) {
            //             mkdir($folder_attach, 0777, true); // <<< recursive
            //         }
            //         $file->move($folder_attach, $attachfile);

            //         $attach = new Attachment();
            //         $attach->docid        = $docid;
            //         $attach->name         = str_replace('%','', $filenameNoExt);
            //         $attach->attachfile   = $attachfile;
            //         $attach->status       = 'A';
            //         $attach->extention    = $ext;
            //         $attach->created_user = $username; // <<< pakai $username
            //         $attach->save();
            //     }
            // }

     

            // $meta = [
            //     'refnbr'        => $docid,                       // biasanya = docid WO
            //     'doctype'       => $doctype,                         // bedakan per modul: 'WO', 'PR', dll
            //     'cpnyid'        => $request->input('cpnyid'),    // opsional
            //     'departementid' => $request->input('departementid'), // opsional
            //     'base_folder'   => 'att-purchasing-app/wo',      // beda modul → beda base_folder
            //     'created_by'    => auth()->user()->username ?? auth()->id(),
            // ];

            // // ambil file dari request (field: attachments[])
            // $files = (array) $request->file('attachments');

            // try {
              
            //     $uploader = app(TrAttachmentController::class);
            //     $uploadResult = $uploader->uploadInternal($meta, $files);
               
            //     return Response::json([
            //         'message'       => 'WO created & attachments uploaded',
            //         'attachments'   => $uploadResult, // folder + daftar item
            //     ]);
            // } catch (\Throwable $e) {
            //     return Response::json([
            //         'message' => 'Failed to create WO',
            //         'error'   => $e->getMessage(),
            //     ], 500);
            // }

            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr'        => $docid,
                    'doctype'       => $doctype,
                    'cpnyid'        => $request->input('cpnyid'),
                    'departementid' => $request->input('departementid'),
                    'base_folder'   => 'att-purchasing-app/wo',
                    'created_by'    => $username,
                ];

                $files = (array) $request->file('attachments');

                try {
                    $uploader = app(TrAttachmentController::class);
                    $uploadResult = $uploader->uploadInternal($meta, $files);
                    // tidak return di sini!
                } catch (\Throwable $e) {
                    \DB::rollBack();
                    return response()->json([
                        'message' => 'Failed to create WO',
                        'error'   => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            } else {
                $uploadResult = null; // tidak ada attachment
            }

          
            // === kirim email ke approver pertama ===
            $firstApproval = T_approval::where('docid', $docid)
                ->where('status', 'P')
                ->orderBy('aprvid')
                ->first();

            if ($firstApproval) {
                $status = $wo->status; // <<< pakai $wo, bukan $header
                $subjectMap = [
                    'P' => 'Waiting Approval',
                    'R' => 'Rejected Approval',
                    'D' => 'Revise Approval',
                    'A' => 'Approved',
                    'C' => 'Completed',
                ];
                $subjectSuffix = $subjectMap[$status] ?? 'Notification';

                $eid = \Hashids::encode($wo->id); // <<< pakai $wo

                $data = [
                    'docid'    => $firstApproval->docid,
                    'cpnyid'   => $firstApproval->aprvcpnyid,
                    'deptname' => $firstApproval->aprvdeptid,
                    'date'     => $firstApproval->aprvdatebefore,
                    'name'     => $firstApproval->name,
                    'createdby'=> $wo->created_by,
                    'info'     => $request->keperluan,
                    'status'   => $status,
                    'docname'  => 'WO',
                    'url'      => url('/showwos/' . $eid),
                ];

                $approvers = array_filter(array_map('trim', explode(',', (string)$firstApproval->aprvusername)));
                $emails = User::whereIn('username', $approvers)
                    ->where('status', 'A')
                    ->pluck('test_email');

                foreach ($emails as $email) {
                    \Mail::send('emails.mailapprovenew', $data, function ($message) use ($email, $data, $subjectSuffix) {
                        $message->to($email)
                            ->subject($data['docid'].' - '.$subjectSuffix.' WO')
                            ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                    });
                }
            }

            \DB::commit();

            return response()->json([
                'ok' => true,
                'message' => 'WO created successfully',
                'id' => $wo->id,
                'docid'    => $docid,
                'attachments' => $uploadResult, // opsional
            ]);

        } catch (\Throwable $e) {
            \DB::rollBack();
            report($e);

            return response()->json([
                'message' => 'Failed to create WO',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

   
    public function editWo($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $wo = TrWO::with([
            'worktype',       // MsWorktype
            'subworktype',    // MsSubworktype
            'location',       // MsLocationPG
            'sublocation',    // MsSubLocationPG
            'creator:username,name',
        ])->findOrFail($id);

        $user      = request()->user();
        $usercpny  = Usercpny::where('username', $user->username)->get();
        $usercpny2 = Usercpny::where('username', $user->username)->first();
        $userdept  = Userdept::where('username', $user->username)->get();
        $userdept2 = Userdept::where('username', $user->username)->first();

        // attachments
        $rows = TrAttachment::where('refnbr', $wo->woid)
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

        $config      = config('filesystems.disks.gcs');
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
            $signedUrl  = null;
            try {
                $signedUrl = $object->signedUrl(
                    new \DateTimeImmutable('+10 minutes'),
                    ['version' => 'v4']
                );
            } catch (\Throwable $e) {
                \Log::warning('Signed URL gagal', ['path' => $objectPath, 'error' => $e->getMessage()]);
            }
            return (object) [
                'display_name' => $r->attachment_name,
                'created_by'   => $r->created_by,
                'created_at'   => $r->created_at,
                'url'          => $signedUrl,
                'folder'       => $r->folder,
                'filename'     => $r->filename,
                'extention'    => $r->extention,
                'size'         => $r->filesize,
            ];
        });

        // ==== nilai awal untuk prefill (aman jika relasi null) ====
        $prefill = [
            'cpnyid'          => $wo->cpny_id ?? '',
            'departementid'   => $wo->department_id ?? '',
            'wotype'          => $wo->wotype ?? '',            // (string nama kategori, sama seperti create)
            'worequest'       => $wo->worequest ?? '',         // (string nama kategori)
            'location_id'     => $wo->location_id ?? '',
            'location_name'   => optional($wo->location)->location_name ?? ($wo->location_id ?? ''),
            'sub_location_id' => $wo->sub_location_id ?? '',
            'sub_location_name'=> optional($wo->sublocation)->sub_location_name ?? ($wo->sub_location_id ?? ''),
            'worktypeid'      => $wo->worktypeid ?? '',
            'worktype_name'   => optional($wo->worktype)->worktype_name ?? ($wo->worktypeid ?? ''),
            'subworktypeid'   => $wo->subworktypeid ?? '',
            'subworktype_name'=> optional($wo->subworktype)->subworktype_name ?? ($wo->subworktypeid ?? ''),
            'picrequester'    => $wo->picrequester ?? ($wo->created_by ?? ''),
            'biaya_wo'        => $wo->biaya_wo ?? null,
            'keperluan'       => $wo->keperluan ?? '',
            'woid'            => $wo->woid ?? '',
            'hash'            => request()->route('hash') ?? '',
        ];

        return view('pages.wos.editwos', compact(
            'wo','usercpny','usercpny2','userdept','userdept2','attachments','prefill'
        ));
    }




    public function updateWo(Request $request, $id)
    {
        // dd($request->all()); // matikan agar eksekusi lanjut

        $user      = $request->user();   
        $dt        = Carbon::now();
        $year      = $dt->year;
        $month     = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        $datestamp = $dt->toDateTimeString();   
        $doctype   = 'PB';
        $username  = $user->username ?? 'system';
        $fullname  = $user->name ?? 'system';

        // helper: normalisasi angka (tahan "12.000", "1.234,56", "12,5")
        $toFloat = function ($v): ?float {
            if ($v === null || $v === '') return null;
            $s = preg_replace('/\s+/', '', (string)$v);
            $hasComma = strpos($s, ',') !== false;
            $hasDot   = strpos($s, '.') !== false;

            if ($hasComma && $hasDot) {
                $lastComma = strrpos($s, ',');
                $lastDot   = strrpos($s, '.');
                if ($lastComma > $lastDot) {
                    // koma = decimal, titik = ribuan
                    $s = str_replace('.', '', $s);
                    $s = str_replace(',', '.', $s);
                } else {
                    // titik = decimal, koma = ribuan
                    $s = str_replace(',', '', $s);
                }
            } elseif ($hasComma) {
                $s = str_replace(',', '.', $s);
            } elseif ($hasDot) {
                if (substr_count($s, '.') > 1) $s = str_replace('.', '', $s);
            }
            return is_numeric($s) ? (float)$s : null;
        };

        $header = TrWO::findOrFail($id);
        // update header
        $header->cpny_id        = $request->cpnyid;
        $header->department_id  = $request->departementid;
        $header->worktypeid  = $request->worktypeid;
        $header->keperluan      = $request->keperluan;
        $header->budget_perpost = $request->perpost;   
        $header->woid           = $request->woid;
        $header->status         = 'P';
        $header->updated_by     = $username;
        $header->save();

        // arrays utama
        $detailIds    = array_values($request->input('detail_id', []));
        $inventoryIds = array_values($request->input('inventoryid', []));
        $productNames = array_values($request->input('product_name', []));
        $qtys         = array_values($request->input('qty', []));
        $uoms         = array_values($request->input('stock_unit', []));
        $notes        = array_values($request->input('note', []));
        $locIds       = array_values($request->input('location_id', []));
        $subLocIds    = array_values($request->input('sub_location_id', []));
        $actIds       = array_values($request->input('activity_id', []));
        $buIds        = array_values($request->input('business_unit_id', []));
        $deptFinIds   = array_values($request->input('department_fin_id', []));
        $coaIds       = array_values($request->input('coa_id', []));
        $itemTypes    = array_values($request->input('item_type', []));
        $itemCats     = array_values($request->input('item_category', []));

        // arrays UoM tambahan
        $purchaseUnits = array_values($request->input('purchase_unit', []));      // hidden dari UI
        $uomMultDivs   = array_values($request->input('uom_unitmultdiv', []));    // 'M'/'D'
        $uomRates      = array_values($request->input('uom_unitrate', []));       // bisa "12.000"

        DB::beginTransaction();

        try {
            // hapus baris yang di-mark delete
            if ($request->filled('deleted_detail_ids')) {
                $idsToDelete = array_filter(array_map('trim', explode(',', $request->deleted_detail_ids)));
                if ($idsToDelete) TrWOdetail::whereIn('id', $idsToDelete)->delete();
            }

            $rowCount = max(count($inventoryIds), count($qtys));
            $savedDetails = [];

            for ($i = 0; $i < $rowCount; $i++) {
                $invId = $inventoryIds[$i] ?? null;
                $qty   = (float) str_replace(',', '.', (string)($qtys[$i] ?? 0));
                if (empty($invId) || $qty <= 0) continue;

                // === konversi base_* seperti di store ===
                $displayUom     = $uoms[$i] ?? null;
                $baseUom        = $purchaseUnits[$i] ?? null;                        // purchase_unit
                $typeMultiplier = strtoupper(trim((string)($uomMultDivs[$i] ?? ''))); // 'M'/'D'
                $rate           = $toFloat($uomRates[$i] ?? null) ?? 1.0;             // 12.000 -> 12.0
                if ($rate <= 0) { $rate = 1.0; $typeMultiplier = ''; }

                $baseQty = $qty;
                if ($typeMultiplier === 'M') {
                    $baseQty = $qty * $rate;
                } elseif ($typeMultiplier === 'D') {
                    $baseQty = $qty / $rate;
                }

                $data = [
                    'inventoryid'              => $invId,
                    'inventory_descr'          => $productNames[$i] ?? null,
                    'qty'                      => $qty,
                    'uom'                      => $displayUom,
                    'note'                     => $notes[$i] ?? null,
                    'inventory_type'                => $itemTypes[$i] ?? null,
                    'wo_category'            => $itemCats[$i] ?? null,

                    // >>> ini yang ditambahkan <<<
                    'base_uom'                 => $baseUom,                       // purchase_unit
                    'base_multiplier'          => $rate,                          // uom_unitrate (float)
                    'type_multiplier'          => $typeMultiplier ?: null,        // 'M'/'D'/null
                    'base_qty'                 => $baseQty,                        // hasil M/D

                    'budget_cpny_id'           => $request->cpnyid,
                    'budget_business_unit_id'  => $buIds[$i] ?? null,
                    'budget_department_fin_id' => $deptFinIds[$i] ?? null,
                    'budget_account_id'        => $coaIds[$i] ?? null,
                    'budget_activity_id'       => $actIds[$i] ?? null,
                    'openordered'              => $qty,
                    'ordered'                  => 0,
                    'location_id'              => $locIds[$i] ?? null,
                    'sub_location_id'          => $subLocIds[$i] ?? null,
                    'budget_perpost'           => $request->perpost,
                    'status'                   => 'P',
                    'updated_by'               => $username,
                ];

                $idDetail = $detailIds[$i] ?? null;

                if ($idDetail) {
                    $detail = TrWOdetail::where('id', $idDetail)
                        ->where('woid', $header->woid)
                        ->first();
                    if ($detail) {
                        $detail->fill($data)->save();
                    } else {
                        $detail = new TrWOdetail($data);
                        $detail->woid = $header->woid;
                        $detail->save();
                    }
                } else {
                    $detail = new TrWOdetail($data);
                    $detail->woid = $header->woid;
                    $detail->save();
                }

                $savedDetails[] = $detail->id;
            }

            // Renumber wo_no 1..N
            $n = 1;
            foreach ($savedDetails as $did) {
                TrWOdetail::where('id', $did)->update(['wo_no' => $n++]);
            }

            // Hitung total qty (kalau mau pakai base_qty, ganti ke sum('base_qty'))
            $totalQty = TrWOdetail::where('woid', $header->woid)->sum('qty');
            $header->totalqty = $totalQty;
            $header->totalopenordered = $totalQty;
            $header->save();

            // === regenerasi T_approval (opsional, ikuti logikamu) ===
            $approvals = M_approval::where([
                ['status', '=', 'A'],
                ['aprvcpnyid', '=', $request->cpnyid],
                ['aprvdeptid', '=', $request->departementid],
                ['aprvdoctype', '=', $doctype],
            ])->get();

            foreach ($approvals as $a) {
                T_approval::create([
                    'docid'          => $header->woid,
                    'aprvid'         => $a->aprvid,
                    'aprvdoctype'    => $a->aprvdoctype,
                    'aprvcpnyid'     => $a->aprvcpnyid,
                    'aprvdeptid'     => $a->aprvdeptid,
                    'aprvusername'   => $a->aprvusername,
                    'name'           => $a->name,
                    'aprvdatebefore' => $a->aprvid == 1 ? $datestamp : null,
                    'aprvtotalday'   => 1,
                    'status'         => 'P',
                    'created_by'   => $username,
                ]);
            }

            $firstApprovalUsernames = optional($approvals->first())->aprvusername;
            if ($firstApprovalUsernames) {
                $header->completed_by = $firstApprovalUsernames;
                $header->completed_at = $dt;
                $header->save();
            }

            // attachments (tetap)
            if ($request->hasfile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $randomNumber = random_int(10000000, 99999999);
                    $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                   
                    $originalName = str_replace('%', '', $file->getClientOriginalName());
                    $ext        = $file->getClientOriginalExtension();
                    $attachfile = md5($randomNumber) . '.' . $ext;

                    //attach to folder
                    $folder_attach = public_path() . '/attachments/'.$year;
                    $config['upload_path'] = $folder_attach;                   
                    if(!is_dir($folder_attach))
                    {
                        mkdir($folder_attach, 0777);
                    }
                    
                    $folder_upload = $folder_attach;
                    // $folder_upload = public_path() . '/attachments';
                    $file->move($folder_upload, $attachfile);

                    //insert to table attachments
                    $attach = new Attachment();
                    $attach->docid = $header->woid;
                    $attach->name = $filename;
                    $attach->attachfile = $attachfile;
                    $attach->status = 'A';
                    $attach->extention = $file->getClientOriginalExtension();
                    $attach->created_user = $user->username;
                    $attach->save();
                }
            }       

            // email approver pertama (tetap)
            $firstApproval = T_approval::where('docid', $header->woid)
                ->where('status', 'P')
                ->orderBy('aprvid')
                ->first();

            if ($firstApproval) {
                $status = $header->status; // 'P' | 'R' | 'D' | 'A' | 'C'

                $subjectMap = [
                    'P' => 'Waiting Approval',
                    'R' => 'Rejected Approval',
                    'D' => 'Revise Approval',
                    'A' => 'Approved',
                    'C' => 'Completed',
                ];
                $subjectSuffix = $subjectMap[$status] ?? 'Notification';

                $eid = Hashids::encode($header->id);
                
                $data = [
                    'docid'    => $firstApproval->docid,
                    'cpnyid'   => $firstApproval->aprvcpnyid,
                    'deptname' => $firstApproval->aprvdeptid,
                    'date'     => $firstApproval->aprvdatebefore,
                    'name'     => $firstApproval->name,
                    'createdby'=> $header->created_by,
                    'info'     => $request->keperluan,
                    'status'   => $status,
                    'docname'  => 'WO',
                    'url'      => url('/showwos/' . $eid),
                ];

                $approvers = array_filter(array_map('trim', explode(',', (string)$firstApproval->aprvusername)));
                $emails = User::whereIn('username', $approvers)
                    ->where('status', 'A')
                    ->pluck('test_email');

                foreach ($emails as $email) {
                    \Mail::send('emails.mailapprovenew', $data, function ($message) use ($email, $data) {
                        $message->to($email)
                            ->subject($data['docid'].' - Waiting Approval WO')
                            ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                    });
                }
            }

            DB::commit();
            return response()->json(['message' => 'WO updated successfully']);

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json(['message' => 'Update failed', 'error' => $e->getMessage()], 500);
        }
    }

   
   
    public function removeAttachment($id)
    {
        try {
            $attachment = Attachment::findOrFail($id);
            $attachment->update(['status' => 'X']); // Update status ke "D" (Deleted)

            return response()->json(['success' => true, 'message' => 'Attachment status updated']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update attachment status', 'error' => $e->getMessage()], 500);
        }
    }
 

    public function showWo($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $wo = TrWO::with([
            'worktype',       // MsWorktype
            'subworktype',    // MsSubworktype
            'location',       // MsLocationPG
            'sublocation',    // MsSubLocationPG
            'creator:username,name',
        ])->findOrFail($id);

        $approval = T_approval::where('docid', $wo->woid)
            ->where('status', '<>', 'X')
            ->orderBy('created_at')
            ->orderBy('aprvid')
            ->get();

        // $attachment = Attachment::where('docid', $wo->woid)
        //     ->where('status', 'A')
        //     ->get();
        // ---------- ambil lampiran dari tr_attachment ----------
        $rows = TrAttachment::where('refnbr', $wo->woid)
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

        // siapkan Signed URL dari GCS
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

        // map jadi data siap pakai di view
        $attachments = $rows->map(function ($r) use ($bucket) {
            $objectPath = rtrim($r->folder, '/').'/'.$r->filename;   // ex: att-purchasing-app/wo/2025/xxxx-file.pdf
            $object     = $bucket->object($objectPath);

            // Signed URL 10 menit
            $signedUrl = null;
            try {
                $signedUrl = $object->signedUrl(
                    new \DateTimeImmutable('+10 minutes'),
                    ['version' => 'v4']
                );
            } catch (\Throwable $e) {
                // kalau gagal signed URL, biarkan null; di UI tampilkan nama saja
                \Log::warning('Signed URL gagal', ['path' => $objectPath, 'error' => $e->getMessage()]);
            }

            return (object) [
                'display_name' => $r->attachment_name,         // nama yang enak dibaca
                'created_by'   => $r->created_by,
                'created_at'   => $r->created_at,
                'url'          => $signedUrl,                  // bisa null jika gagal
                'folder'       => $r->folder,
                'filename'     => $r->filename,
                'extention'    => $r->extention,
                'size'         => $r->filesize,
            ];
        });

        return view('pages.wos.showwos', compact('wo', 'approval', 'attachments', 'hash'));
    }


    
    public function fetchComments($id)
    {
    
        $comments = T_Message::where('docid', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'comments' => $comments
        ]);
    }
    public function storeComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:500',
        ]);
        // dd($id);
        $user = request()->user();
        $comment = new T_Message();
        $comment->docid = $id;
        $comment->doctype = 'PB';
        $comment->username = $user->username; 
        $comment->name = $user->name; 
        $comment->message = $request->comment;
        $comment->status = 'A';
        $comment->created_at = now();
        $comment->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Comment added successfully!',
            'comment' => $comment
        ]);
    }

    public function approveWo(Request $request, $docid)
    {
        $now  = Carbon::now();
        $user = $request->user();

        // $wo = TrWO::where('woid', $docid)->first();
        $wo = TrWO::with('creator')
            ->where('woid', $docid)
            ->first();
        $fullname = data_get($wo, 'creator.name') ?: $wo->created_by;

        if (!$wo) {
            return response()->json(['success' => false, 'message' => 'WO not found'], 404);
        }

        // pastikan user memang approver aktif (status P) di doc ini
        $tApproval = T_approval::where('docid', $wo->woid)
            ->where('status', 'P')
            ->where('aprvusername', 'like', "%{$user->username}%")
            ->whereNotNull('aprvdatebefore') 
            ->orderBy('aprvid', 'ASC')
            ->first();

        if (!$tApproval) {
            return response()->json(['success' => false, 'message' => "You can't approve!"], 403);
        }

        DB::beginTransaction();
        try {
            // Set current approver -> Approved
            $tApproval->status         = 'A';
            $tApproval->aprvdateafter  = $now;
            $tApproval->aprvusername   = $user->username;
            $tApproval->name           = $user->name;
            $tApproval->save();

            // Update header informasi "terakhir diproses"
            $wo->completed_by = $user->username;
            $wo->completed_at = $now;
            $wo->save();

            // Hitung sisa pending setelah approve ini
            $pendingCount = T_approval::where('docid', $wo->woid)
                ->where('status', 'P')
                ->count();

            // Pemetaan judul sesuai status
            $subjectMap = [
                'P' => 'Waiting Approval',
                'R' => 'Rejected Approval',
                'D' => 'Revise Approval',
                'A' => 'Approved',
                'C' => 'Completed',
            ];

            $eid = Hashids::encode($wo->id);

            if ($pendingCount === 0) {
                // Tidak ada approver lagi -> dokumen complete
                $wo->status       = 'C';
                $wo->completed_by = $user->username;
                $wo->completed_at = $now;
                $wo->save();

                $wodetail = TrWOdetail::where('woid', $wo->woid)                
                    ->get();

                foreach ($wodetail as $d) {
                    $d->status = 'C'; 
                    $d->save();
                }

                // Kirim email ke requester (creator)
                $status        = 'C';
                $subjectSuffix = $subjectMap[$status] ?? 'Notification';                

                $data = [
                    'docid'     => $wo->woid,
                    'cpnyid'    => $wo->cpny_id ?? $wo->cpnyid ?? '',
                    'deptname'  => $wo->department_id ?? $wo->departementid ?? '',
                    'date'      => $wo->wodate,
                    'fullname'  => $fullname,  // nama penerima di email
                    'name'      => $fullname,  // fallback
                    'createdby' => $fullname,
                    'docname'   => 'WO',
                    'info'      => $wo->keperluan,
                    'status'    => $status,
                    'url'       => url('/showwos/' . $eid),
                ];

                $recipients = User::where('username', $wo->created_by)
                    ->where('status', 'A')
                    ->get();

                foreach ($recipients as $rcp) {
                    try {
                        Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $rcp, $subjectSuffix) {
                            $to = $rcp->test_email ?? $rcp->email; // pakai field yang memang ada
                            $message->to($to)
                                ->subject($data['docid'] . ' - ' . $subjectSuffix . ' WO')
                                ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                        });
                    } catch (\Throwable $e) {
                        Log::error('Failed sending WO completion email', ['error' => $e->getMessage()]);
                    }
                }
            } else {
                // Masih ada approver berikutnya -> cari level berikutnya (P terrendah aprvid)
                $next = T_approval::where('docid', $wo->woid)
                    ->where('status', 'P')
                    ->orderBy('aprvid', 'ASC')
                    ->first();

                if ($next) {
                    // Stempel "datebefore" untuk approver berikutnya
                    $next->aprvdatebefore = $now;
                    $next->save();

                    // Kirim email ke semua username yang ada di kolom aprvusername (dipisah koma)
                    $status        = 'P';
                    $subjectSuffix = $subjectMap[$status] ?? 'Notification';

                    $data = [
                        'docid'     => $next->docid,
                        'cpnyid'    => $next->aprvcpnyid,
                        'deptname'  => $next->aprvdeptid,
                        'date'      => $next->aprvdatebefore,
                        'fullname'  => $next->name,
                        'name'      => $next->name,
                        'createdby' => $wo->created_by,
                        'docname'   => 'WO',
                        'info'      => $wo->keperluan,
                        'status'    => $status,
                        'url'       => url('/showwos/' . $eid),
                    ];

                    $usernames = array_filter(array_map('trim', explode(',', (string) $next->aprvusername)));
                    if (!empty($usernames)) {
                        $recipients = User::whereIn('username', $usernames)
                            ->where('status', 'A')
                            ->get();

                        foreach ($recipients as $rcp) {
                            try {
                                Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $rcp, $subjectSuffix) {
                                    $to = $rcp->test_email ?? $rcp->email;
                                    $message->to($to)
                                        ->subject($data['docid'] . ' - ' . $subjectSuffix . ' WO')
                                        ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                                });
                            } catch (\Throwable $e) {
                                Log::error('Failed sending WO waiting-approval email', ['error' => $e->getMessage()]);
                            }
                        }
                    } else {
                        Log::warning('Next approver has empty aprvusername list', ['docid' => $wo->woid]);
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Task approved successfully']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Approve WO failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Approve failed'], 500);
        }
    }
    
    public function rejectWo(Request $request, $docid)
    {
        $now  = Carbon::now();
        $user = $request->user();

        // $wo = TrWO::where('woid', $docid)->first();
        $wo = TrWO::with('creator')
            ->where('woid', $docid)
            ->first();
        $fullname = data_get($wo, 'creator.name') ?: $wo->created_by;

        if (!$wo) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        // Validasi: user harus approver aktif (status P) pada dokumen ini
        $tApproval = T_approval::where('docid', $wo->woid)
            ->where('status', 'P')
            ->where('aprvusername', 'like', "%{$user->username}%")
            ->whereNotNull('aprvdatebefore') 
            ->orderBy('aprvid', 'ASC')
            ->first();

        if (!$tApproval) {
            return response()->json(['success' => false, 'message' => "You can't reject!"], 403);
        }

        DB::beginTransaction();
        try {
            // Tandai approval saat ini sebagai Rejected
            $tApproval->status        = 'R';
            $tApproval->aprvdateafter = $now;
            $tApproval->aprvusername  = $user->username; // catat siapa yang reject
            $tApproval->name          = $user->name;
            $tApproval->save();

            // Update header WO
            $wo->status       = 'R';
            $wo->completed_by = $user->username;
            $wo->completed_at = $now;
            $wo->save();

            // Batalkan semua approval yang masih pending
            T_approval::where('docid', $wo->woid)
                ->where('status', 'P')
                ->update(['status' => 'X']);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Reject WO failed', ['docid' => $docid, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Reject failed'], 500);
        }

        // === Kirim Email ke requester (creator) ===
        $status = 'R'; // Rejected
        $subjectMap = [
            'P' => 'Waiting Approval',
            'R' => 'Rejected Approval',
            'D' => 'Revise Approval',
            'A' => 'Approved',
            'C' => 'Completed',
        ];
        $subjectSuffix = $subjectMap[$status] ?? 'Notification';
        $eid = Hashids::encode($wo->id);

        $data = [
            'docid'     => $wo->woid,
            'cpnyid'    => $wo->cpny_id ?? $wo->cpnyid ?? '',
            'deptname'  => $wo->department_id ?? $wo->departementid ?? '',
            'date'      => $now->toDateString(),            // bisa juga pakai $tApproval->aprvdateafter
            'fullname'  => $fullname,               // view email kita pakai $fullname
            'name'      => $fullname,               // fallback jika view pakai $name
            'createdby' => $fullname,
            'docname'   => 'WO',
            'info'      => $wo->keperluan,
            'status'    => $status,
            'url'       => url('/showwos/' . $eid),
        ];

        $recipients = User::where('username', $wo->created_by)
            ->where('status', 'A')
            ->get();

        foreach ($recipients as $rcp) {
            try {
                $to = $rcp->test_email ?? $rcp->email; // sesuaikan field yang tersedia
                Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
                    $message->to($to)
                        ->subject($data['docid'] . ' - ' . $subjectSuffix . ' WO')
                        ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                });
            } catch (\Throwable $e) {
                Log::error('Failed sending WO rejected email', [
                    'docid' => $data['docid'],
                    'to'    => $rcp->username,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Simpan komentar penolakan (jika ada)
        try {
            app('App\Http\Controllers\SendCommentController')
                ->sendmsg($wo->id, 'PB', $request);
        } catch (\Throwable $e) {
            Log::warning('SendComment after reject failed', [
                'docid' => $wo->woid,
                'error' => $e->getMessage()
            ]);
        }

        return response()->json(['success' => true, 'message' => 'WO rejected successfully']);
    }

    public function reviseWo(Request $request, $docid)
    {
        $now  = Carbon::now();
        $user = $request->user();

        // $wo = TrWO::where('woid', $docid)->first();
        $wo = TrWO::with('creator')
            ->where('woid', $docid)
            ->first();
        $fullname = data_get($wo, 'creator.name') ?: $wo->created_by;
            
        if (!$wo) {
            return response()->json(['success' => false, 'message' => 'WO not found'], 404);
        }

        // Pastikan user adalah approver aktif (status P) dokumen ini
        $tApproval = T_approval::where('docid', $wo->woid)
            ->where('status', 'P')
            ->where('aprvusername', 'like', "%{$user->username}%")
            ->whereNotNull('aprvdatebefore')
            ->orderBy('aprvid', 'ASC')
            ->first();

        if (!$tApproval) {
            return response()->json(['success' => false, 'message' => "You can't revise!"], 403);
        }

        DB::beginTransaction();
        try {
            // Tandai approval saat ini sebagai Revise (D)
            $tApproval->status        = 'D';
            $tApproval->aprvdateafter = $now;
            $tApproval->aprvusername  = $user->username;  // catat siapa yang revise
            $tApproval->name          = $user->name;
            $tApproval->save();

            // Update header WO
            $wo->status       = 'D';
            $wo->completed_by = $user->username;        // mengikuti pola existing
            $wo->completed_at = $now;
            $wo->save();

            // Batalkan approval lain yang masih pending
            T_approval::where('docid', $wo->woid)
                ->where('status', 'P')
                ->update(['status' => 'X']);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Revise WO failed', ['docid' => $docid, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Revise failed'], 500);
        }

        // === Kirim email ke requester (creator) ===
        $status = 'D'; // Revise
        $subjectMap = [
            'P' => 'Waiting Approval',
            'R' => 'Rejected Approval',
            'D' => 'Revise Approval',
            'A' => 'Approved',
            'C' => 'Completed',
        ];
        $subjectSuffix = $subjectMap[$status] ?? 'Notification';
        $eid = Hashids::encode($wo->id);

        $data = [
            'docid'     => $wo->woid,
            'cpnyid'    => $wo->cpny_id ?? $wo->cpnyid ?? '',
            'deptname'  => $wo->department_id ?? $wo->departementid ?? '',
            'date'      => $now->toDateString(),          // atau $tApproval->aprvdateafter
            'fullname'  => $fullname,             // template email pakai $fullname
            'name'      => $fullname,             // fallback jika view pakai $name
            'createdby' => $fullname,
            'docname'   => 'WO',
            'info'      => $wo->keperluan,
            'status'    => $status,
            'url'       => url('/showwos/' . $eid),
        ];

        $recipients = User::where('username', $wo->created_by)
            ->where('status', 'A')
            ->get();

        foreach ($recipients as $rcp) {
            try {
                $to = $rcp->test_email ?? $rcp->email; // sesuaikan dengan kolom yang ada
                Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
                    $message->to($to)
                        ->subject($data['docid'] . ' - ' . $subjectSuffix . ' WO')
                        ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                });
            } catch (\Throwable $e) {
                Log::error('Failed sending WO revise email', [
                    'docid' => $data['docid'],
                    'to'    => $rcp->username,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Simpan komentar revisi (jika ada)
        try {
            app('App\Http\Controllers\SendCommentController')
                ->sendmsg($wo->id, 'PB', $request);
        } catch (\Throwable $e) {
            Log::warning('SendComment after revise failed', [
                'docid' => $wo->woid,
                'error' => $e->getMessage()
            ]);
        }

        return response()->json(['success' => true, 'message' => 'WO revised successfully']);
    }
    

    public function checkApproval($id, $action)
    {
        $user = Auth::user(); // Ambil user yang login
        // dd($action);
        // Query dasar untuk pengecekan
        $query = T_approval::where('docid', $id)
                    ->where('aprvusername', 'like', '%' . $user->username . '%')
                    ->where('status', 'P');                 

        // Jika aksi adalah reject atau revise, pastikan aprvdatebefore tidak null
        if (in_array($action, ['reject', 'revise','approve'])) {
            $query->whereNotNull('aprvdatebefore');
        }

        // Cek apakah user bisa melakukan aksi
        $canPerformAction = $query->exists();

        return response()->json(['canPerformAction' => $canPerformAction]);
    }

    public function tracking($id)
    {
        $wo = TrWO::findOrFail($id);

        $getName = function (?string $username) {
            if (!$username) return null;
            $u = \App\Models\User::where('username', $username)->first();
            return $u->name ?? $username;
        };

        $createdByName = $getName($wo->created_by ?? null);
        $createdAt     = $wo->created_at ? \Carbon\Carbon::parse($wo->created_at)->format('Y-m-d H:i') : null;

        $completedByName = $getName($wo->completed_by ?? null);
        $completedAt     = $wo->completed_at ? \Carbon\Carbon::parse($wo->completed_at)->format('Y-m-d H:i') : null;

        // kolom opsional, kalau tidak ada biarkan null
        $rejectedByName  = $getName($wo->rejected_by ?? null);
        $rejectedAt      = isset($wo->rejected_at) ? \Carbon\Carbon::parse($wo->rejected_at)->format('Y-m-d H:i') : null;

        $revisedByName   = $getName($wo->revised_by ?? null);
        $revisedAt       = isset($wo->revised_at) ? \Carbon\Carbon::parse($wo->revised_at)->format('Y-m-d H:i') : null;

        $status = (string) ($wo->status ?? '');
        $labelMap = [
            'P' => 'Waiting approval',
            'R' => 'Rejected',
            'D' => 'Revise',
            'C' => 'Completed',
        ];
        $statusLabel = $labelMap[$status] ?? $status;

        // selalu mulai dari Submitted
        $steps = [[
            'key'          => 'submitted',
            'title'        => 'WO',
            'status'       => 'C',              // dibuat = completed
            'status_label' => 'Submitted',
            'by'           => $createdByName,
            'at'           => $createdAt,
        ]];

        switch ($status) {
            case 'P':
                // masih menunggu/berjalan → tampilkan Approval saja
                $steps[] = [
                    'key'          => 'approval',
                    'title'        => 'Approval',
                    'status'       => 'P',
                    'status_label' => 'Waiting approval',
                    'by'           => $completedByName,
                    'at'           => $completedAt,
                ];
                break;

            case 'R':
                // DITOLAK → langsung Submitted → Rejected (tanpa Approval)
                $steps[] = [
                    'key'          => 'rejected',
                    'title'        => 'Rejected',
                    'status'       => 'R',
                    'status_label' => 'Rejected',
                    'by'           => $completedByName,
                    'at'           => $completedAt,
                ];
                break;

            case 'D':
                // REVISE → Submitted → Revise
                $steps[] = [
                    'key'          => 'revise',
                    'title'        => 'Revise',
                    'status'       => 'D',
                    'status_label' => 'Revise',
                    'by'           => $completedByName,
                    'at'           => $completedAt,
                ];
                break;

            case 'C':
                // SELESAI → bisa langsung Submitted → Completed
                // (kalau kamu ingin menampilkan Approval yang sudah dilalui,
                // tambahkan step 'approval' sebelum 'completed')
                $steps[] = [
                    'key'          => 'completed',
                    'title'        => 'Completed',
                    'status'       => 'C',
                    'status_label' => 'Completed',
                    'by'           => $completedByName,
                    'at'           => $completedAt,
                ];
                break;

            default:
                // status tidak dikenal → biarkan hanya Submitted
                break;
        }

        return response()->json([
            'doc'   => $wo->woid ?? (string)$wo->id,
            'steps' => $steps,
            'status'=> $status,
            'status_label' => $statusLabel,
        ]);
    }

    public function printWo(Request $request, $hash)
    {
        $id = \Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        if (!\Auth::check()) {
            return redirect()->route('login');
        }

        $wo = TrWO::with([
            'worktype',      // MsWorktype
            'subworktype',   // MsSubworktype
            'location',      // MsLocationPG
            'sublocation',   // MsSubLocationPG
            'creator:username,name',
        ])->findOrFail($id);

        $approval = T_approval::where('docid', $wo->woid)
            ->where('status', '<>', 'X')
            ->orderBy('aprvid')
            ->orderBy('created_at')
            ->get();

        $approve_count = $approval->count();

        $company = Company::where('cpnyid', $wo->cpny_id)->first();

        // mapping status
        $status_map = [
            'R' => 'Rejected',
            'C' => 'Completed',
            'D' => 'Hold',
            'X' => 'Cancel',
            'P' => 'On Progress',
        ];
        $status_doc = $status_map[$wo->status] ?? 'On Progress';

        // pilih varian tampilan
        $variant = $request->query('variant', 'default'); // default | tenant
        $view = $variant === 'tenant'
            ? 'pages.wos.pdf_wos_tenant'
            : 'pages.wos.pdf_wos';

        $data = [
            'title'               => $variant === 'tenant' ? 'Work Order (Tenant)' : 'Work Order (WO)',
            'doc_type'            => 'WO',
            'docid'               => $wo->woid,
            'department_id'       => $wo->department_id,
            'cpnyname'            => optional($company)->cpnyname,
            'cpnyid'              => $wo->cpny_id,           
            'created_by_username' => $wo->created_by,
            'created_by_name'     => ucwords(strtolower(optional($wo->creator)->name)),
            'created_at_fmt'      => optional($wo->created_at)->format('d F Y'),
            'req_date_fmt'        => optional($wo->created_at)->format('d M Y H:i'),
            'wodate'              => \Carbon\Carbon::parse($wo->wodate)->format('d F Y'),
            'keperluan'           => $wo->keperluan,
            'status_doc'          => $status_doc,

            // info tambahan yang sering dipakai di template
            'wotype'              => $wo->wotype,                      // disimpan string category_name
            'worequest'           => $wo->worequest,                   // disimpan string category_name
            'worktype_name'       => optional($wo->worktype)->worktype_name,
            'subworktype_name'    => optional($wo->subworktype)->subworktype_name,
            'location_name'       => optional($wo->location)->location_name,
            'sub_location_name'   => optional($wo->sublocation)->sub_location_name,
            'picrequester'        => $wo->picrequester,
            'biaya_wo'            => number_format($wo->biaya_wo, 0, ',', '.'),            
        ];

        $pdf = \PDF::loadView($view, array_merge($data, [
            'approval'      => $approval,
            'approve_count' => $approve_count,
        ]));

        // Portrait jika <= 5 approver, else landscape
        $pdf->setPaper('A4', ($approve_count <= 5) ? 'portrait' : 'landscape');

        $suffix = $variant === 'tenant' ? '_tenant' : '';
        return $pdf->stream("pdf_wos{$suffix}_{$wo->woid}.pdf");
    }






    






}
