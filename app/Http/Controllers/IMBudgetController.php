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
use App\Models\TrIMBudget;
use App\Models\TrIMBudgetdetail;
use App\Models\TrCS;
use App\Models\TrCSdetail;
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

class IMBudgetController extends Controller
{
    public function index()
    {
        $all = TrIMBudget::count();
        $onProgress = TrIMBudget::where('status', 'P')->count();
        $reject = TrIMBudget::where('status', 'R')->count();
        $cancel = TrIMBudget::where('status', 'X')->count();
        $completed = TrIMBudget::where('status', 'C')->count();
       
        return view('pages.imbudgets.imbudgets', compact('all', 'onProgress', 'reject', 'cancel', 'completed'));
    }


    public function json(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $search = trim((string) $request->input('search.value', ''));
        $status = (string) $request->query('status', ''); // '' = all

        $baseTable = (new TrIMBudget)->getTable(); // "tr_imbudget"

        // Urutan kolom sesuai DataTables di view
        $columns = [
            0 => 'imb.imbudgetid',
            1 => 'imb.imbudgetdate',
            2 => 'imb.csid',
            3 => 'imb.sppbjktid',
            4 => 'imb.cpny_id',
            5 => 'imb.user_peminta',
            6 => 'imb.status',
        ];

        $orderIdx = (int) $request->input('order.0.column', 1); // default by date
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'imb.imbudgetdate';

        $base = TrIMBudget::from($baseTable.' as imb');

        if ($status !== '') {
            $base->where('imb.status', $status);
        }

        // total sebelum filter search
        $recordsTotal = (clone $base)->count();

        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('imb.imbudgetid', 'like', "%{$search}%")
                ->orWhere('imb.csid', 'like', "%{$search}%")
                ->orWhere('imb.sppbjktid', 'like', "%{$search}%")
                ->orWhere('imb.cpny_id', 'like', "%{$search}%")
                ->orWhere('imb.user_peminta', 'like', "%{$search}%")
                ->orWhere('imb.status', 'like', "%{$search}%");
            });
        }

        // total setelah filter search
        $recordsFiltered = (clone $base)->count();

        $rows = $base->select(
                    'imb.imbudgetid',
                    'imb.imbudgetdate',
                    'imb.csid',
                    'imb.sppbjktid',
                    'imb.cpny_id',
                    'imb.user_peminta',
                    'imb.status',
                    'imb.created_by'
                )
                ->orderBy($orderCol, $orderDir)
                ->orderBy('imb.imbudgetid', 'desc')
                ->skip($start)
                ->take($length)
                ->get();

        // Optional: format tanggal jadi Y-m-d (biar rapi konsisten)
        $rows->transform(function ($row) {
            $row->imbudgetdate = $row->imbudgetdate
                ? \Carbon\Carbon::parse($row->imbudgetdate)->format('Y-m-d')
                : null;

            // gunakan imbudgetid langsung untuk URL (di view pakai encodeURIComponent)
            $row->eid = $row->imbudgetid;

            return $row;
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $rows,
        ]);
    }

    
    
    public function GenerateIMBudget(Request $request) 
    {
        // --- Ambil CS header & detail ---
        $csid = $request->input('csid', 'CS25100015'); // default contoh
        $cs = TrCS::where('csid', $csid)->first();
        if (!$cs) {
            return response()->json([
                'message' => "CS dengan csid '{$csid}' tidak ditemukan."
            ], 404);
        }
        $csdetail = TrCSdetail::where('csid', $csid)->get();

        // --- Konfigurasi dasar ---
        $doctype  = 'IM';
        $user     = $request->user();
        $username = $user->username ?? 'system';

        $dt        = Carbon::now();
        $year      = $dt->year;
        $month     = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        $datestamp = $dt->toDateTimeString();

        // helper angka lokal
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
            } elseif ($hasDot && substr_count($s, '.') > 1) {
                $s = str_replace('.', '', $s);
            }
            return is_numeric($s) ? (float)$s : null;
        };

        // --- Mapping kolom dari CS header (pakai fallback beberapa nama yang mungkin ada) ---
        $cpnyid        = $cs->cpny_id         ?? $cs->cpnyid         ?? $request->input('cpnyid');
        $departementid = $cs->departementid   ?? $cs->department_id  ?? $request->input('departementid');
        $perpost       = $cs->budget_perpost  ?? $cs->perpost        ?? $request->input('perpost');
        $sppbjktid     = $cs->sppbjktid       ?? $request->input('sppbjktid');
        $user_peminta  = $cs->user_peminta    ?? $request->input('user_peminta', $username);
        $imbudgetnote  = $cs->imbudgetnote    ?? $cs->csnote         ?? $cs->note ?? $request->input('imbudgetnote');

        // --- Pastikan line approval ada (gunakan cpny & dept dari CS) ---
        $approvalCount = M_approval::where([
            ['status', '=', 'A'],
            ['aprvcpnyid', '=', $cpnyid],
            ['aprvdeptid', '=', $departementid],
            ['aprvdoctype', '=', $doctype],
        ])->count();

        if ($approvalCount === 0) {
            return response()->json([
                'message' => 'Approval line belum di-setup, Please contact IT!',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // === autonbr & docid ===
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

            $tglbln = substr($year, 2) . $month;   // YYMM
            $docid  = $doctype . $tglbln . sprintf("%04d", $urutan);

            // === 1) HEADER IMBudget ===
            $header = new TrIMBudget();
            $header->imbudgetid               = $docid;
            $header->imbudgetdate             = $dt->toDateString();
            $header->csid                     = $csid;
            $header->sppbjktid                = $sppbjktid;
            $header->cpny_id                  = $cpnyid;
            $header->user_peminta             = $user_peminta;
            $header->imbudgetnote             = $imbudgetnote;
            $header->budget_perpost           = $perpost;
            $header->total_budget_needed      = 0;
            $header->total_budget_requested   = 0;
            $header->status                   = 'P';
            $header->created_by               = $username;
            $header->save();

            // === 2) DETAIL dari CSdetail ===
            $totalNeeded = 0.0;
            $totalRequested = 0.0;

            foreach ($csdetail as $d) {
                // Map fleksibel dari berbagai kemungkinan nama kolom di CSdetail
                $detailBudgetCpny  = $d->budget_cpny_id          ?? $d->cpny_id          ?? $cpnyid;
                $detailBU          = $d->budget_business_unit_id ?? $d->business_unit_id ?? null;
                $detailDeptFin     = $d->budget_department_fin_id?? $d->department_fin_id?? null;
                $detailCoa         = $d->budget_account_id       ?? $d->coa_id           ?? null;
                $detailAct         = $d->budget_activity_id      ?? $d->activity_id      ?? null;
                $detailActDescr    = $d->budget_activity_descr   ?? $d->activity_descr   ?? null;

                $need              = $toFloat($d->budget_needed   ?? $d->needed   ?? $d->amount_needed   ?? 0) ?? 0.0;
                $req               = $toFloat($d->budget_requested?? $d->requested?? $d->amount_requested?? 0) ?? 0.0;

                $noteDetail        = $d->csnote_detail ?? null;

                // skip baris kosong
                if ($need <= 0 && $req <= 0) {
                    continue;
                }

                $detail = new TrIMBudgetdetail();
                $detail->imbudgetid                   = $docid;
                $detail->csid                         = $csid;
                $detail->sppbjktid                    = $sppbjktid;
                $detail->budget_perpost               = $perpost;

                $detail->budget_cpny_id               = $detailBudgetCpny;
                $detail->budget_business_unit_id      = $detailBU;
                $detail->budget_department_fin_id     = $detailDeptFin;
                $detail->budget_account_id            = $detailCoa;
                $detail->budget_activity_id           = $detailAct;
                $detail->budget_activity_descr        = $detailActDescr;

                $detail->budget_needed                = $need;
                $detail->budget_requested             = $req;
                $detail->note                         = $noteDetail;
                $detail->status                       = 'P';
                $detail->created_by                   = $username;
                $detail->save();

                $totalNeeded    += $need;
                $totalRequested += $req;
            }

            // Kalau sama sekali tidak ada detail terpakai, batalkan
            if ($totalNeeded <= 0 && $totalRequested <= 0) {
                DB::rollBack();
                return response()->json([
                    'message' => "Tidak ada baris detail CS ({$csid}) yang memiliki nilai budget_needed/requested > 0.",
                ], 422);
            }

            // === 3) update total header ===
            $header->total_budget_needed    = $totalNeeded;
            $header->total_budget_requested = $totalRequested;
            $header->save();

            // === 4) copy line approval (M_approval -> T_approval) ===
            $approvals = M_approval::where([
                ['status', '=', 'A'],
                ['aprvcpnyid', '=', $cpnyid],
                ['aprvdeptid', '=', $departementid],
                ['aprvdoctype', '=', $doctype],
            ])->get();

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

            // optional: catat approver pertama
            $firstApprovalUsernames = optional($approvals->first())->aprvusername;
            if ($firstApprovalUsernames) {
                $header->completed_by = $firstApprovalUsernames;
                $header->completed_at = $dt;
                $header->save();
            }

            // === 5) attachments (opsional) ===
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr'        => $docid,
                    'doctype'       => $doctype,
                    'cpnyid'        => $cpnyid,
                    'departementid' => $departementid,
                    'base_folder'   => 'att-purchasing-app/'.strtolower($doctype),
                    'created_by'    => $username,
                ];
                $files = (array) $request->file('attachments');

                try {
                    $uploader = app(TrAttachmentController::class);
                    $uploadResult = $uploader->uploadInternal($meta, $files);
                } catch (\Throwable $e) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Failed to create IMBudget',
                        'error'   => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            } else {
                $uploadResult = null;
            }

            // === 6) Email ke approver pertama ===
            $firstApproval = T_approval::where('docid', $docid)
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

                $eid = rawurlencode($header->imbudgetid);
                $data = [
                    'docid'     => $firstApproval->docid,
                    'cpnyid'    => $firstApproval->aprvcpnyid,
                    'deptname'  => $firstApproval->aprvdeptid,
                    'date'      => $firstApproval->aprvdatebefore,
                    'name'      => $firstApproval->name,
                    'createdby' => $header->created_by,
                    'info'      => $imbudgetnote,
                    'status'    => $status,
                    'docname'   => 'IMBudget',
                    'url'       => url('/showimbudgets/' . $eid),
                ];

                $approvers = array_filter(array_map('trim', explode(',', (string)$firstApproval->aprvusername)));
                $emails = User::whereIn('username', $approvers)
                    ->where('status', 'A')
                    ->pluck('test_email');

                foreach ($emails as $email) {
                    \Mail::send('emails.mailapprovenew', $data, function ($message) use ($email, $data, $subjectSuffix) {
                        $message->to($email)
                            ->subject($data['docid'].' - '.$subjectSuffix.' IMBudget')
                            ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                    });
                }
            }

            DB::commit();

            return response()->json([
                'message'                 => 'IMBudget created successfully',
                'imbudgetid'              => $docid,
                'total_budget_needed'     => $totalNeeded,
                'total_budget_requested'  => $totalRequested,
                'attachments'             => $uploadResult,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json([
                'message' => 'Failed to create IMBudget',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
   
    public function editIMBudget($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $imbudget = TrIMBudget::findOrFail($id);

        // Ambil detail + eager load relasi lokasi & sublokasi
        $imbudgetdetail = TrIMBudgetdetail::with([
                'location:location_id,location_name',
                'subLocation:sub_location_id,sub_location_name',
            ])
            ->where('imbudgetid', $imbudget->imbudgetid)
            ->get()
            ->map(function ($d) {
                // Sematkan nama ke attribute agar Blade lama tetap jalan
                $d->location_name      = optional($d->location)->location_name;
                $d->sub_location_name  = optional($d->subLocation)->sub_location_name;
                return $d;
            });

        $user   = request()->user();
        $usercpny  = Usercpny::where('username', $user->username)->get();
        $usercpny2 = Usercpny::where('username', $user->username)->first();
        $userdept  = Userdept::where('username', $user->username)->get();
        $userdept2 = Userdept::where('username', $user->username)->first();

        // $attachment = Attachment::where('docid', $imbudget->imbudgetid)
        //     ->where('status', 'A')
        //     ->get();

        $rows = TrAttachment::where('refnbr', $imbudget->imbudgetid)
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
                'id'          => $r->id,
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

        return view('pages.imbudgets.editimbudgets', compact(
            'imbudget','imbudgetdetail','usercpny','usercpny2','userdept','userdept2','attachments','hash'
        ));
    }



    public function updateIMBudget(Request $request, $hash)
    {
        // dd($request->all()); // matikan agar eksekusi lanjut

        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404, 'PB tidak ditemukan.');

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

        $header = TrIMBudget::findOrFail($id);
        // update header
        $header->cpny_id        = $request->cpnyid;
        $header->department_id  = $request->departementid;
        $header->requesttypeid  = $request->requesttypeid;
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
                if ($idsToDelete) TrIMBudgetdetail::whereIn('id', $idsToDelete)->delete();
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
                    'inventory_category'            => $itemCats[$i] ?? null,

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
                    $detail = TrIMBudgetdetail::where('id', $idDetail)
                        ->where('imbudgetid', $header->imbudgetid)
                        ->first();
                    if ($detail) {
                        $detail->fill($data)->save();
                    } else {
                        $detail = new TrIMBudgetdetail($data);
                        $detail->imbudgetid = $header->imbudgetid;
                        $detail->save();
                    }
                } else {
                    $detail = new TrIMBudgetdetail($data);
                    $detail->imbudgetid = $header->imbudgetid;
                    $detail->save();
                }

                $savedDetails[] = $detail->id;
            }

            // Renumber imbudget_no 1..N
            $n = 1;
            foreach ($savedDetails as $did) {
                TrIMBudgetdetail::where('id', $did)->update(['imbudget_no' => $n++]);
            }

            // Hitung total qty (kalau mau pakai base_qty, ganti ke sum('base_qty'))
            $totalQty = TrIMBudgetdetail::where('imbudgetid', $header->imbudgetid)->sum('qty');
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
                    'docid'          => $header->imbudgetid,
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
            // if ($request->hasfile('attachments')) {
            //     foreach ($request->file('attachments') as $file) {
            //         $randomNumber = random_int(10000000, 99999999);
            //         $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                   
            //         $originalName = str_replace('%', '', $file->getClientOriginalName());
            //         $ext        = $file->getClientOriginalExtension();
            //         $attachfile = md5($randomNumber) . '.' . $ext;

            //         //attach to folder
            //         $folder_attach = public_path() . '/attachments/'.$year;
            //         $config['upload_path'] = $folder_attach;                   
            //         if(!is_dir($folder_attach))
            //         {
            //             mkdir($folder_attach, 0777);
            //         }
                    
            //         $folder_upload = $folder_attach;
            //         // $folder_upload = public_path() . '/attachments';
            //         $file->move($folder_upload, $attachfile);

            //         //insert to table attachments
            //         $attach = new Attachment();
            //         $attach->docid = $header->imbudgetid;
            //         $attach->name = $filename;
            //         $attach->attachfile = $attachfile;
            //         $attach->status = 'A';
            //         $attach->extention = $file->getClientOriginalExtension();
            //         $attach->created_user = $user->username;
            //         $attach->save();
            //     }
            // }       

              
            $uploadResult = null;
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr'        => $header->imbudgetid,
                    'doctype'       => $doctype,
                    'cpnyid'        => $request->cpnyid,
                    'departementid' => $request->departementid,
                    'base_folder'   => 'att-purchasing-app/'.strtolower($doctype),
                    'created_by'    => $user->username,
                ];
                $files = (array) $request->file('attachments');

                try {
                    $uploader = app(TrAttachmentController::class);
                    $uploadResult = $uploader->uploadInternal($meta, $files);
                } catch (\Throwable $e) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Failed to update PB',
                        'error'   => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            }

            // email approver pertama (tetap)
            $firstApproval = T_approval::where('docid', $header->imbudgetid)
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
                    'docname'  => 'IMBudget',
                    'url'      => url('/showimbudgets/' . $eid),
                ];

                $approvers = array_filter(array_map('trim', explode(',', (string)$firstApproval->aprvusername)));
                $emails = User::whereIn('username', $approvers)
                    ->where('status', 'A')
                    ->pluck('test_email');

                foreach ($emails as $email) {
                    \Mail::send('emails.mailapprovenew', $data, function ($message) use ($email, $data) {
                        $message->to($email)
                            ->subject($data['docid'].' - Waiting Approval IMBudget')
                            ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                    });
                }
            }

            DB::commit();
            return response()->json(['message' => 'IMBudget updated successfully']);

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json(['message' => 'Update failed', 'error' => $e->getMessage()], 500);
        }
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
 

    public function showIMBudget($hash)
    {        
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();       

        if (!$user) {
            return redirect()->route('login');
        }

        // $imbudget = TrIMBudget::findOrFail($id);
        $imbudget = TrIMBudget::with([
            'requestType:requesttypeid,requesttype_name',
            'creator:username,name'
        ])
        ->findOrFail($id);        

        $imbudgetdetail = TrIMBudgetdetail::with([
            'location:location_id,location_name',
            'subLocation:sub_location_id,sub_location_name'
        ])
        ->where('imbudgetid', $imbudget->imbudgetid)
        ->get();
        
        $approval = T_approval::where('docid', $imbudget->imbudgetid)
            ->where('status','<>','X')      
            ->orderBy('created_at')
            ->orderBy('aprvid')      
            ->get();
       
        // $attachment = Attachment::where('docid', $imbudget->imbudgetid)    
        //     ->where('status','A')        
        //     ->get();    
        
        // ---------- ambil lampiran dari tr_attachment ----------
        $rows = TrAttachment::where('refnbr', $imbudget->imbudgetid)
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
        
       
        return view('pages.imbudgets.showimbudgets', compact('imbudget','approval','attachments','imbudgetdetail','hash'));
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

    public function approveIMBudget(Request $request, $docid)
    {
        $now  = Carbon::now();
        $user = $request->user();

        // $imbudget = TrIMBudget::where('imbudgetid', $docid)->first();
        $imbudget = TrIMBudget::with('creator')
            ->where('imbudgetid', $docid)
            ->first();
        $fullname = data_get($imbudget, 'creator.name') ?: $imbudget->created_by;

        if (!$imbudget) {
            return response()->json(['success' => false, 'message' => 'IMBudget not found'], 404);
        }

        // pastikan user memang approver aktif (status P) di doc ini
        $tApproval = T_approval::where('docid', $imbudget->imbudgetid)
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
            $imbudget->completed_by = $user->username;
            $imbudget->completed_at = $now;
            $imbudget->save();

            // Hitung sisa pending setelah approve ini
            $pendingCount = T_approval::where('docid', $imbudget->imbudgetid)
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

            $eid = Hashids::encode($imbudget->id);

            if ($pendingCount === 0) {
                // Tidak ada approver lagi -> dokumen complete
                $imbudget->status       = 'C';
                $imbudget->completed_by = $user->username;
                $imbudget->completed_at = $now;
                $imbudget->save();

                $imbudgetdetail = TrIMBudgetdetail::where('imbudgetid', $imbudget->imbudgetid)                
                    ->get();

                foreach ($imbudgetdetail as $d) {
                    $d->status = 'C'; 
                    $d->save();
                }

                // Kirim email ke requester (creator)
                $status        = 'C';
                $subjectSuffix = $subjectMap[$status] ?? 'Notification';                

                $data = [
                    'docid'     => $imbudget->imbudgetid,
                    'cpnyid'    => $imbudget->cpny_id ?? $imbudget->cpnyid ?? '',
                    'deptname'  => $imbudget->department_id ?? $imbudget->departementid ?? '',
                    'date'      => $imbudget->imbudgetdate,
                    'fullname'  => $fullname,  // nama penerima di email
                    'name'      => $fullname,  // fallback
                    'createdby' => $fullname,
                    'docname'   => 'IMBudget',
                    'info'      => $imbudget->keperluan,
                    'status'    => $status,
                    'url'       => url('/showimbudgets/' . $eid),
                ];

                $recipients = User::where('username', $imbudget->created_by)
                    ->where('status', 'A')
                    ->get();

                foreach ($recipients as $rcp) {
                    try {
                        Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $rcp, $subjectSuffix) {
                            $to = $rcp->test_email ?? $rcp->email; // pakai field yang memang ada
                            $message->to($to)
                                ->subject($data['docid'] . ' - ' . $subjectSuffix . ' IMBudget')
                                ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                        });
                    } catch (\Throwable $e) {
                        Log::error('Failed sending IMBudget completion email', ['error' => $e->getMessage()]);
                    }
                }
            } else {
                // Masih ada approver berikutnya -> cari level berikutnya (P terrendah aprvid)
                $next = T_approval::where('docid', $imbudget->imbudgetid)
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
                        'createdby' => $imbudget->created_by,
                        'docname'   => 'IMBudget',
                        'info'      => $imbudget->keperluan,
                        'status'    => $status,
                        'url'       => url('/showimbudgets/' . $eid),
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
                                        ->subject($data['docid'] . ' - ' . $subjectSuffix . ' IMBudget')
                                        ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                                });
                            } catch (\Throwable $e) {
                                Log::error('Failed sending IMBudget waiting-approval email', ['error' => $e->getMessage()]);
                            }
                        }
                    } else {
                        Log::warning('Next approver has empty aprvusername list', ['docid' => $imbudget->imbudgetid]);
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Task approved successfully']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Approve IMBudget failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Approve failed'], 500);
        }
    }
    
    public function rejectIMBudget(Request $request, $docid)
    {
        $now  = Carbon::now();
        $user = $request->user();

        // $imbudget = TrIMBudget::where('imbudgetid', $docid)->first();
        $imbudget = TrIMBudget::with('creator')
            ->where('imbudgetid', $docid)
            ->first();
        $fullname = data_get($imbudget, 'creator.name') ?: $imbudget->created_by;

        if (!$imbudget) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        // Validasi: user harus approver aktif (status P) pada dokumen ini
        $tApproval = T_approval::where('docid', $imbudget->imbudgetid)
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

            // Update header IMBudget
            $imbudget->status       = 'R';
            $imbudget->completed_by = $user->username;
            $imbudget->completed_at = $now;
            $imbudget->save();

            // Batalkan semua approval yang masih pending
            T_approval::where('docid', $imbudget->imbudgetid)
                ->where('status', 'P')
                ->update(['status' => 'X']);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Reject IMBudget failed', ['docid' => $docid, 'error' => $e->getMessage()]);
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
        $eid = Hashids::encode($imbudget->id);

        $data = [
            'docid'     => $imbudget->imbudgetid,
            'cpnyid'    => $imbudget->cpny_id ?? $imbudget->cpnyid ?? '',
            'deptname'  => $imbudget->department_id ?? $imbudget->departementid ?? '',
            'date'      => $now->toDateString(),            // bisa juga pakai $tApproval->aprvdateafter
            'fullname'  => $fullname,               // view email kita pakai $fullname
            'name'      => $fullname,               // fallback jika view pakai $name
            'createdby' => $fullname,
            'docname'   => 'IMBudget',
            'info'      => $imbudget->keperluan,
            'status'    => $status,
            'url'       => url('/showimbudgets/' . $eid),
        ];

        $recipients = User::where('username', $imbudget->created_by)
            ->where('status', 'A')
            ->get();

        foreach ($recipients as $rcp) {
            try {
                $to = $rcp->test_email ?? $rcp->email; // sesuaikan field yang tersedia
                Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
                    $message->to($to)
                        ->subject($data['docid'] . ' - ' . $subjectSuffix . ' IMBudget')
                        ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                });
            } catch (\Throwable $e) {
                Log::error('Failed sending IMBudget rejected email', [
                    'docid' => $data['docid'],
                    'to'    => $rcp->username,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Simpan komentar penolakan (jika ada)
        try {
            app('App\Http\Controllers\SendCommentController')
                ->sendmsg($imbudget->id, 'PB', $request);
        } catch (\Throwable $e) {
            Log::warning('SendComment after reject failed', [
                'docid' => $imbudget->imbudgetid,
                'error' => $e->getMessage()
            ]);
        }

        return response()->json(['success' => true, 'message' => 'IMBudget rejected successfully']);
    }

    public function reviseIMBudget(Request $request, $docid)
    {
        $now  = Carbon::now();
        $user = $request->user();

        // $imbudget = TrIMBudget::where('imbudgetid', $docid)->first();
        $imbudget = TrIMBudget::with('creator')
            ->where('imbudgetid', $docid)
            ->first();
        $fullname = data_get($imbudget, 'creator.name') ?: $imbudget->created_by;
            
        if (!$imbudget) {
            return response()->json(['success' => false, 'message' => 'IMBudget not found'], 404);
        }

        // Pastikan user adalah approver aktif (status P) dokumen ini
        $tApproval = T_approval::where('docid', $imbudget->imbudgetid)
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

            // Update header IMBudget
            $imbudget->status       = 'D';
            $imbudget->completed_by = $user->username;        // mengikuti pola existing
            $imbudget->completed_at = $now;
            $imbudget->save();

            // Batalkan approval lain yang masih pending
            T_approval::where('docid', $imbudget->imbudgetid)
                ->where('status', 'P')
                ->update(['status' => 'X']);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Revise IMBudget failed', ['docid' => $docid, 'error' => $e->getMessage()]);
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
        $eid = Hashids::encode($imbudget->id);

        $data = [
            'docid'     => $imbudget->imbudgetid,
            'cpnyid'    => $imbudget->cpny_id ?? $imbudget->cpnyid ?? '',
            'deptname'  => $imbudget->department_id ?? $imbudget->departementid ?? '',
            'date'      => $now->toDateString(),          // atau $tApproval->aprvdateafter
            'fullname'  => $fullname,             // template email pakai $fullname
            'name'      => $fullname,             // fallback jika view pakai $name
            'createdby' => $fullname,
            'docname'   => 'IMBudget',
            'info'      => $imbudget->keperluan,
            'status'    => $status,
            'url'       => url('/showimbudgets/' . $eid),
        ];

        $recipients = User::where('username', $imbudget->created_by)
            ->where('status', 'A')
            ->get();

        foreach ($recipients as $rcp) {
            try {
                $to = $rcp->test_email ?? $rcp->email; // sesuaikan dengan kolom yang ada
                Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
                    $message->to($to)
                        ->subject($data['docid'] . ' - ' . $subjectSuffix . ' IMBudget')
                        ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                });
            } catch (\Throwable $e) {
                Log::error('Failed sending IMBudget revise email', [
                    'docid' => $data['docid'],
                    'to'    => $rcp->username,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Simpan komentar revisi (jika ada)
        try {
            app('App\Http\Controllers\SendCommentController')
                ->sendmsg($imbudget->id, 'PB', $request);
        } catch (\Throwable $e) {
            Log::warning('SendComment after revise failed', [
                'docid' => $imbudget->imbudgetid,
                'error' => $e->getMessage()
            ]);
        }

        return response()->json(['success' => true, 'message' => 'IMBudget revised successfully']);
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

    public function tracking($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $imbudget = TrIMBudget::findOrFail($id);

        $getName = function (?string $username) {
            if (!$username) return null;
            $u = \App\Models\User::where('username', $username)->first();
            return $u->name ?? $username;
        };

        $createdByName = $getName($imbudget->created_by ?? null);
        $createdAt     = $imbudget->created_at ? \Carbon\Carbon::parse($imbudget->created_at)->format('Y-m-d H:i') : null;

        $completedByName = $getName($imbudget->completed_by ?? null);
        $completedAt     = $imbudget->completed_at ? \Carbon\Carbon::parse($imbudget->completed_at)->format('Y-m-d H:i') : null;

        // kolom opsional, kalau tidak ada biarkan null
        $rejectedByName  = $getName($imbudget->rejected_by ?? null);
        $rejectedAt      = isset($imbudget->rejected_at) ? \Carbon\Carbon::parse($imbudget->rejected_at)->format('Y-m-d H:i') : null;

        $revisedByName   = $getName($imbudget->revised_by ?? null);
        $revisedAt       = isset($imbudget->revised_at) ? \Carbon\Carbon::parse($imbudget->revised_at)->format('Y-m-d H:i') : null;

        $status = (string) ($imbudget->status ?? '');
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
            'title'        => 'IMBudget',
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
            'doc'   => $imbudget->imbudgetid ?? (string)$imbudget->id,
            'steps' => $steps,
            'status'=> $status,
            'status_label' => $statusLabel,
        ]);
    }

    public function printIMBudget($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $authUser = Auth::user();
        if (!$authUser) {
            return redirect()->route('login');
        }

        // Ambil IMBudget + relasi yang dibutuhkan
        $imbudget = TrIMBudget::with([
                'requestType:requesttypeid,requesttype_name',
                'creator:username,name',
            ])
            ->findOrFail($id);

        // Detail baris IMBudget
        $imbudgetdetail = TrIMBudgetdetail::with([
                'location:location_id,location_name',
                'subLocation:sub_location_id,sub_location_name',
            ])
            ->where('imbudgetid', $imbudget->imbudgetid)
            ->get();

        // Approval list (non-cancelled)
        $approval = T_approval::where('docid', $imbudget->imbudgetid)
            ->where('status', '<>', 'X')
            ->orderBy('aprvid')
            ->orderBy('created_at')
            ->get();

        $approve_count = $approval->count();

        // Company (handle null)
        $company = Company::where('cpnyid', $imbudget->cpny_id)->first();

        // Mapping status dokumen
        switch ($imbudget->status) {
            case 'R':
                $status_doc = 'Rejected';
                break;
            case 'C':
                $status_doc = 'Completed';
                break;
            case 'D':
                $status_doc = 'Hold';
                break;
            case 'X':
                $status_doc = 'Cancel';
                break;
            default:
                $status_doc = 'On Progress';
                break;
        }

        $data = [
            'title'               => 'Surat Permintaan Pembelian Barang',
            'doc_type'            => 'IMBudget',
            'docid'               => $imbudget->imbudgetid,
            'department_id'       => $imbudget->department_id,
            'cpnyname'            => optional($company)->cpnyname,
            'parent'              => optional($company)->parent,
            'project'             => optional($company)->project,
            // identitas & tanggal
            'created_by_username' => $imbudget->created_by,
            'created_by_name'     => ucwords(strtolower(optional($imbudget->creator)->name)),
            'created_at_fmt'      => optional($imbudget->created_at)->format('d F Y'),
            'req_date_fmt'        => optional($imbudget->created_at)->format('d M Y H:i'),
            'imbudgetdate'            => \Carbon\Carbon::parse($imbudget->imbudgetdate)->format('d F Y'),
            // konten
            'keperluan'           => $imbudget->keperluan,
            'status_doc'          => $status_doc,
            'requesttype_name'    => optional($imbudget->requestType)->requesttype_name,
        ];

        // Kirim ke view
        $pdf = \PDF::loadView(
            'pages.imbudgets.pdf_imbudgets',
            array_merge($data, [
                'detail'         => $imbudgetdetail,
                'approval'       => $approval,
                'approve_count'  => $approve_count,
            ])
        );

        // Portrait jika <= 5 approver, else landscape
        $pdf->setPaper('A4', ($approve_count <= 5) ? 'portrait' : 'landscape');

        return $pdf->stream("pdf_imbudgets_{$imbudget->imbudgetid}.pdf");
    }





    






}
