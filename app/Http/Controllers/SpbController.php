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
use App\Models\TrSPB;
use App\Models\TrSPBdetail;
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

class SpbController extends Controller
{
    public function index()
    {
        $all = TrSPB::count();
        $onProgress = TrSPB::where('status', 'P')->count();
        $reject = TrSPB::where('status', 'R')->count();
        $revise = TrSPB::where('status', 'D')->count();
        $completed = TrSPB::where('status', 'C')->count();
       
        return view('pages.spbs.spbs', compact('all', 'onProgress', 'reject', 'revise', 'completed'));
    }

    
    public function json(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $search = trim((string) $request->input('search.value', ''));
        $status = (string) $request->query('status', ''); // '' = all

        $baseTable = (new TrSPB)->getTable(); // "tr_spb"

        // urutan index harus sinkron dengan <th> di view
        $columns = [
            0 => 'spb.spbid',
            1 => 'spb.spbdate',
            2 => 'spb.cpny_id',
            3 => 'spb.department_id',
            4 => 'wt.worktype_name',
            5 => 'swt.subworktype_name',
            6 => 'spb.keperluan',
            7 => 'spb.status',
        ];

        $orderIdx = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'asc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'spb.spbid';

        $base = TrSPB::from($baseTable.' as spb')
            ->leftJoin('ms_worktype as wt', function ($join) {
                $join->on('wt.worktypeid', '=', 'spb.worktypeid');
            })
            ->leftJoin('ms_subworktype as swt', function ($join) {
                $join->on('swt.subworktypeid', '=', 'spb.subworktypeid');
            });

        if ($status !== '') {
            $base->where('spb.status', $status);
        }

        $recordsTotal = (clone $base)->distinct('spb.spbid')->count('spb.spbid');

        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('spb.spbid', 'like', "%{$search}%")
                ->orWhere('spb.cpny_id', 'like', "%{$search}%")
                ->orWhere('spb.department_id', 'like', "%{$search}%")
                ->orWhere('wt.worktype_name', 'like', "%{$search}%")
                ->orWhere('swt.subworktype_name', 'like', "%{$search}%")
                ->orWhere('spb.keperluan', 'like', "%{$search}%")
                ->orWhere('spb.status', 'like', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $base)->distinct('spb.spbid')->count('spb.spbid');

        $data = $base->select(
                    'spb.id',
                    'spb.spbid',
                    'spb.spbdate',
                    'spb.cpny_id',
                    'spb.department_id',
                    'spb.worktypeid',
                    'spb.subworktypeid',
                    'wt.worktype_name',
                    'swt.subworktype_name',
                    'spb.keperluan',
                    'spb.status',
                    'spb.created_by'
                )
                ->orderBy($orderCol, $orderDir)
                ->orderBy('spb.spbid', 'desc')
                ->skip($start)
                ->take($length)
                ->get();

        // Encode id dengan hashids → JANGAN hapus id, karena dipakai untuk tombol tracking
        $data->transform(function ($row) {
            $row->eid = \Hashids::encode($row->id);
            return $row;
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    
    public function createSpb()
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
       
        return view('pages.spbs.createspbs', compact('usercpny','usercpny2','userdept','userdept2'));
    }

        
    public function storeSpb(Request $request)
    {
        $doctype  = 'RB';
        $user     = $request->user();
        $username = $user->username ?? 'system';
        $dt       = Carbon::now();
        $year     = (int) $dt->format('Y');
        $month    = $dt->format('m');
        $datestamp= $dt->toDateTimeString();

        // === Kumpulkan array dari form ===
        $inventoryIds   = $request->input('inventoryid',  $request->input('inventory_id', []));
        $productNames   = $request->input('product_name', []);
        $qtys           = $request->input('qty', []);
        $uoms           = $request->input('stock_unit',   $request->input('uom', []));
        $notes          = $request->input('note', []);

        // lokasi (gabungan modal)
        $locationIds    = $request->input('location_id', $request->input('locationid', []));
        $subLocIds      = $request->input('sub_location_id', $request->input('sublocationid', []));

        // COA chain
        $activityIds    = $request->input('activity_id', []);
        $busUnitIds     = $request->input('business_unit_id', []);
        $deptFinIds     = $request->input('department_fin_id', []);
        $coaIds         = $request->input('coa_id', []);

        // konversi UoM
        $purchaseUnits  = $request->input('purchase_unit', []);     // base_uom
        $uomMultDivs    = $request->input('uom_unitmultdiv', []);   // 'M' | 'D'
        $uomRates       = $request->input('uom_unitrate', []);      // bisa format lokal

        // helper angka lokal → float
        $toFloat = function ($v): ?float {
            if ($v === null || $v === '') return null;
            $s = preg_replace('/\s+/', '', (string)$v);
            $hasComma = strpos($s, ',') !== false;
            $hasDot   = strpos($s, '.') !== false;
            if ($hasComma && $hasDot) {
                $lastComma = strrpos($s, ','); $lastDot = strrpos($s, '.');
                if ($lastComma > $lastDot) { $s = str_replace('.', '', $s); $s = str_replace(',', '.', $s); }
                else { $s = str_replace(',', '', $s); }
            } elseif ($hasComma) {
                $s = str_replace(',', '.', $s);
            } elseif ($hasDot && substr_count($s, '.') > 1) {
                $s = str_replace('.', '', $s);
            }
            return is_numeric($s) ? (float)$s : null;
        };

        // === Cek line approval ===
        $approvalCount = M_approval::where([
            ['status', '=', 'A'],
            ['aprvcpnyid', '=', $request->cpnyid],
            ['aprvdeptid', '=', $request->departementid],
            ['aprvdoctype', '=', $doctype],
        ])->count();

        if ($approvalCount === 0) {
            return response()->json(['message' => 'Approval line belum di-setup, Please contact IT!'], 422);
        }

        DB::beginTransaction();
        try {
            // === Nomor otomatis ===
            $autonbr = Autonbr::lockForUpdate()
                ->where('doctype', $doctype)->where('year', $year)->where('month', $month)->first();

            if (!$autonbr) {
                $autonbr = Autonbr::create([
                    'doctype' => $doctype, 'year' => $year, 'month' => $month, 'status' => 'A', 'number' => 1,
                ]);
                $urutan = 1;
            } else {
                $urutan = $autonbr->number + 1;
                $autonbr->update(['number' => $urutan]);
            }

            $tglbln = substr((string)$year, 2) . $month; // YYMM
            $docid  = $doctype . $tglbln . sprintf("%04d", $urutan);

            // === Guard minimal 1 detail valid ===
            $rowCount = max(count($inventoryIds), count($qtys));
            $hasValid = false;
            for ($i=0; $i<$rowCount; $i++) {
                $inv = $inventoryIds[$i] ?? null;
                $qty = (float) str_replace(',', '.', (string)($qtys[$i] ?? 0));
                if (!empty($inv) && $qty > 0) { $hasValid = true; break; }
            }
            if (!$hasValid) {
                return response()->json(['message' => 'Minimal 1 baris detail dengan Product & Qty > 0.'], 422);
            }

            // === Header (pakai kolom TrSPB::fillable) ===
            $header = new TrSPB();
            $header->spbid             = $docid;
            $header->spbdate           = $dt->toDateString();
            $header->cpny_id           = $request->input('cpnyid');
            $header->department_id     = $request->input('departementid');
            $header->worktypeid        = $request->input('worktypeid');
            $header->subworktypeid     = $request->input('subworktypeid');
            $header->keperluan         = $request->input('keperluan');
            $header->budget_perpost    = $request->input('perpost');
            $header->woid              = $request->input('woid');
            $header->totalspbqty       = 0;
            $header->totalspbopenqty   = 0;
            $header->totalissueqty     = 0;
            $header->totalcompleteqty  = 0;
            $header->status            = 'P';
            $header->created_by        = $username;
            $header->save();

            // === Detail (pakai kolom TrSPBdetail::fillable) ===
            $totalQty = 0;

            for ($i=0; $i<$rowCount; $i++) {
                $invId       = $inventoryIds[$i] ?? null;
                $productName = $productNames[$i] ?? null;
                $qtyRaw      = $qtys[$i] ?? 0;
                $qty         = (float) str_replace(',', '.', (string)$qtyRaw);
                $uom         = $uoms[$i] ?? null;

                if (empty($invId) || $qty <= 0) continue;

                // konversi base
                $baseUom        = $purchaseUnits[$i] ?? null;
                $typeMultiplier = strtoupper(trim((string)($uomMultDivs[$i] ?? '')));
                $rate           = $toFloat($uomRates[$i] ?? null) ?? 1.0;
                if ($rate <= 0) { $rate = 1.0; $typeMultiplier = ''; }
                $baseQty = $qty;
                if ($typeMultiplier === 'M')      $baseQty = $qty * $rate;
                elseif ($typeMultiplier === 'D')  $baseQty = $qty / $rate;

                $detail = new TrSPBdetail();
                $detail->spbid                      = $docid;
                $detail->spb_no                     = $i + 1;
                $detail->inventoryid                = $invId;
                $detail->inventory_descr            = $productName;
                $detail->siteid                     = null;                 // kalau ada, isi sesuai kebutuhanmu
                $detail->qty                        = $qty;
                $detail->uom                        = $uom;
                $detail->type_multiplier            = $typeMultiplier ?: null;
                $detail->base_multiplier            = $rate;
                $detail->base_qty                   = $baseQty;
                $detail->base_uom                   = $baseUom;
                $detail->note                       = $notes[$i]           ?? null;

                $detail->location_id                = $locationIds[$i]     ?? null;
                $detail->sub_location_id            = $subLocIds[$i]       ?? null;

                $detail->budget_perpost             = $request->perpost;
                $detail->budget_cpny_id             = $request->cpnyid;
                $detail->budget_business_unit_id    = $busUnitIds[$i]      ?? null;
                $detail->budget_department_fin_id   = $deptFinIds[$i]      ?? null;
                $detail->budget_account_id          = $coaIds[$i]          ?? null;
                $detail->budget_activity_id         = $activityIds[$i]     ?? null;

                $detail->stock_qty                  = 0;                    // isi jika punya info stok
                $detail->spb_openqty                = $qty;                 // open = awalnya = qty
                $detail->issue_qty                  = 0;
                $detail->spb_completeqty            = 0;

                $detail->status                     = 'P';
                $detail->created_by                 = $username;
                $detail->save();

                $totalQty += $qty;
            }

            if ($totalQty <= 0) {
                DB::rollBack();
                return response()->json(['message' => 'Tidak ada detail valid untuk disimpan.'], 422);
            }

            // === Update total header (pakai kolom TrSPB) ===
            $header->totalspbqty      = $totalQty;
            $header->totalspbopenqty  = $totalQty;
            $header->totalissueqty    = 0;
            $header->totalcompleteqty = 0;
            $header->save();

            // === Copy line approval ===
            $approvals = M_approval::where([
                ['status', '=', 'A'],
                ['aprvcpnyid', '=', $request->cpnyid],
                ['aprvdeptid', '=', $request->departementid],
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

            // === Attachments (opsional) ===
            $uploadResult = null;
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr'        => $docid,
                    'doctype'       => $doctype,
                    'cpnyid'        => $request->input('cpnyid'),
                    'departementid' => $request->input('departementid'),
                    'base_folder'   => 'att-purchasing-app/'.strtolower($doctype),
                    'created_by'    => $username,
                ];
                $files = (array) $request->file('attachments');
                try {
                    $uploader     = app(TrAttachmentController::class);
                    $uploadResult = $uploader->uploadInternal($meta, $files);
                } catch (\Throwable $e) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Failed to create SPB',
                        'error'   => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            }

            // === Notifikasi approver pertama (opsional) ===
            $firstApproval = T_approval::where('docid', $docid)
                ->where('status', 'P')->orderBy('aprvid')->first();

            if ($firstApproval) {
                $status     = $header->status;
                $subjectMap = ['P'=>'Waiting Approval','R'=>'Rejected Approval','D'=>'Revise Approval','A'=>'Approved','C'=>'Completed'];
                $eid        = Hashids::encode($header->id ?? $docid); // fallback kalau tidak ada id auto

                $data = [
                    'docid'    => $firstApproval->docid,
                    'cpnyid'   => $firstApproval->aprvcpnyid,
                    'deptname' => $firstApproval->aprvdeptid,
                    'date'     => $firstApproval->aprvdatebefore,
                    'name'     => $firstApproval->name,
                    'createdby'=> $header->created_by,
                    'info'     => $request->keperluan,
                    'status'   => $status,
                    'docname'  => 'SPB',
                    'url'      => url('/showspbs/' . $eid),
                ];

                $approvers = array_filter(array_map('trim', explode(',', (string)$firstApproval->aprvusername)));
                $emails = User::whereIn('username', $approvers)->where('status', 'A')->pluck('test_email');

                foreach ($emails as $email) {
                    Mail::send('emails.mailapprovenew', $data, function ($message) use ($email, $data, $subjectMap, $status) {
                        $message->to($email)
                            ->subject($data['docid'].' - '.($subjectMap[$status] ?? 'Notification').' SPB')
                            ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                    });
                }
            }

            DB::commit();

            return response()->json([
                'message'     => 'SPB created successfully',
                'spbid'       => $docid,               
                'totalqty'    => $totalQty,
                'attachments' => $uploadResult,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json([
                'message' => 'Failed to create SPB',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

   
    public function editSpb($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $spb = TrSPB::findOrFail($id);

        // Ambil detail + eager load relasi lokasi & sublokasi
        $spbdetail = TrSPBdetail::with([
                'location:location_id,location_name',
                'subLocation:sub_location_id,sub_location_name',
            ])
            ->where('spbid', $spb->spbid)
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

        // $attachment = Attachment::where('docid', $spb->spbid)
        //     ->where('status', 'A')
        //     ->get();

        $rows = TrAttachment::where('refnbr', $spb->spbid)
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

        return view('pages.spbs.editspbs', compact(
            'spb','spbdetail','usercpny','usercpny2','userdept','userdept2','attachments','hash'
        ));
    }



    public function updateSpb(Request $request, $hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404, 'SPB tidak ditemukan.');

        $user      = $request->user();
        $dt        = \Carbon\Carbon::now();
        $datestamp = $dt->toDateTimeString();
        $doctype   = 'RB';
        $username  = $user->username ?? 'system';

        // helper normalisasi angka lokal
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
                if (substr_count($s, '.') > 1) $s = str_replace('.', '', $s);
            }
            return is_numeric($s) ? (float)$s : null;
        };

        /** @var TrSPB $header */
        $header = TrSPB::findOrFail($id);

        // === Ambil arrays dari form ===
        $detailIds      = array_values($request->input('detail_id', [])); // optional dari UI edit
        $inventoryIds   = array_values($request->input('inventoryid', []));
        $productNames   = array_values($request->input('product_name', []));
        $qtys           = array_values($request->input('qty', []));
        $uoms           = array_values($request->input('stock_unit', $request->input('uom', []))); // display uom
        $notes          = array_values($request->input('note', []));
        $locIds         = array_values($request->input('location_id', []));
        $subLocIds      = array_values($request->input('sub_location_id', []));
        $actIds         = array_values($request->input('activity_id', []));
        $buIds          = array_values($request->input('business_unit_id', []));
        $deptFinIds     = array_values($request->input('department_fin_id', []));
        $coaIds         = array_values($request->input('coa_id', []));
        $itemTypes      = array_values($request->input('item_type', []));
        $itemCats       = array_values($request->input('item_category', []));

        // UoM konversi
        $purchaseUnits  = array_values($request->input('purchase_unit', []));
        $uomMultDivs    = array_values($request->input('uom_unitmultdiv', []));
        $uomRates       = array_values($request->input('uom_unitrate', []));

        DB::beginTransaction();
        try {
            // === Update header (sesuai model) ===
            $header->fill([
                'cpny_id'        => $request->cpnyid,
                'department_id'  => $request->departementid,
                'worktypeid'     => $request->worktypeid,
                'subworktypeid'  => $request->subworktypeid,
                'keperluan'      => $request->keperluan,
                'budget_perpost' => $request->perpost,
                'woid'           => $request->woid,
                'status'         => 'P',
                'updated_by'     => $username,
            ])->save();

            // === Hapus detail yang di-mark delete (jika ada) ===
            if ($request->filled('deleted_detail_ids')) {
                $idsToDelete = array_filter(array_map('trim', explode(',', $request->deleted_detail_ids)));
                if (!empty($idsToDelete)) {
                    TrSPBdetail::where('spbid', $header->spbid)
                        ->whereIn('id', $idsToDelete)
                        ->delete();
                }
            }

            // === Simpan / update detail ===
            $rowCount     = max(count($inventoryIds), count($qtys));
            $savedIds     = [];
            $runningNo    = 1;

            for ($i = 0; $i < $rowCount; $i++) {
                $invId = $inventoryIds[$i] ?? null;
                $qty   = (float) str_replace(',', '.', (string)($qtys[$i] ?? 0));
                if (empty($invId) || $qty <= 0) continue;

                // Konversi base_* (match store)
                $displayUom     = $uoms[$i] ?? null;
                $baseUom        = $purchaseUnits[$i] ?? null;
                $typeMultiplier = strtoupper(trim((string)($uomMultDivs[$i] ?? ''))); // 'M'/'D'/''
                $rate           = $toFloat($uomRates[$i] ?? null) ?? 1.0;
                if ($rate <= 0) { $rate = 1.0; $typeMultiplier = ''; }

                $baseQty = $qty;
                if ($typeMultiplier === 'M') {
                    $baseQty = $qty * $rate;
                } elseif ($typeMultiplier === 'D') {
                    $baseQty = $qty / $rate;
                }

                $payload = [
                    'spbid'                    => $header->spbid,
                    'spb_no'                   => $runningNo,                 // direnumber tiap loop
                    'inventoryid'              => $invId,
                    'inventory_descr'          => $productNames[$i] ?? null,
                    'qty'                      => $qty,
                    'uom'                      => $displayUom,
                    'type_multiplier'          => $typeMultiplier ?: null,
                    'base_multiplier'          => $rate,
                    'base_qty'                 => $baseQty,
                    'base_uom'                 => $baseUom,
                    'note'                     => $notes[$i] ?? null,
                    'location_id'              => $locIds[$i] ?? null,
                    'sub_location_id'          => $subLocIds[$i] ?? null,

                    // budget mapping
                    'budget_perpost'           => $request->perpost,
                    'budget_cpny_id'           => $request->cpnyid,
                    'budget_business_unit_id'  => $buIds[$i] ?? null,
                    'budget_department_fin_id' => $deptFinIds[$i] ?? null,
                    'budget_account_id'        => $coaIds[$i] ?? null,
                    'budget_activity_id'       => $actIds[$i] ?? null,

                    // extra (ikuti model)
                    'stock_qty'                => null,
                    'spb_openqty'              => $qty,  // open = qty saat submit/edit
                    'issue_qty'                => 0,
                    'spb_completeqty'          => 0,

                    'status'                   => 'P',
                    'updated_by'               => $username,
                ];

                $idDetail = $detailIds[$i] ?? null;

                if ($idDetail) {
                    $detail = TrSPBdetail::where('id', $idDetail)
                        ->where('spbid', $header->spbid)
                        ->first();

                    if ($detail) {
                        $detail->fill($payload)->save();
                    } else {
                        $detail = TrSPBdetail::create(array_merge($payload, [
                            'created_by' => $username,
                        ]));
                    }
                } else {
                    $detail = TrSPBdetail::create(array_merge($payload, [
                        'created_by' => $username,
                    ]));
                }

                $savedIds[] = $detail->id;
                $runningNo++;
            }

            // === Recalculate header totals (mengikuti store) ===
            $totalQty = TrSPBdetail::where('spbid', $header->spbid)->sum('qty');

            $header->fill([
                'totalspbqty'       => $totalQty,
                'totalspbopenqty'   => $totalQty,  // sama dengan qty saat baru/diupdate
                'totalissueqty'     => 0,
                'totalcompleteqty'  => 0,
                'updated_by'        => $username,
            ])->save();

            // === (Opsional) regenerate approval lines seperti store ===
            // Hapus approval lama pending untuk doc ini agar tidak dobel (opsional; sesuaikan kebijakanmu)
            // T_approval::where('docid', $header->spbid)->where('status','P')->delete();

            $approvals = M_approval::where([
                ['status', '=', 'A'],
                ['aprvcpnyid', '=', $request->cpnyid],
                ['aprvdeptid', '=', $request->departementid],
                ['aprvdoctype', '=', $doctype],
            ])->get();

            foreach ($approvals as $a) {
                T_approval::create([
                    'docid'          => $header->spbid,
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

            if ($first = $approvals->first()) {
                $header->completed_by = $first->aprvusername;
                $header->save();
            }

            // === Upload attachments (tetap seperti store) ===
            $uploadResult = null;
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr'        => $header->spbid,
                    'doctype'       => $doctype,
                    'cpnyid'        => $request->cpnyid,
                    'departementid' => $request->departementid,
                    'base_folder'   => 'att-purchasing-app/'.strtolower($doctype),
                    'created_by'    => $user->username,
                ];
                $files = (array) $request->file('attachments');

                try {
                    $uploader      = app(TrAttachmentController::class);
                    $uploadResult  = $uploader->uploadInternal($meta, $files);
                } catch (\Throwable $e) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Failed to update SPB',
                        'error'   => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            }

            // === (Opsional) Email ke approver pertama (ikuti logika store) ===
            if ($firstPending = T_approval::where('docid', $header->spbid)->where('status','P')->orderBy('aprvid')->first()) {
                $eid = Hashids::encode($header->id);
                $data = [
                    'docid'    => $firstPending->docid,
                    'cpnyid'   => $firstPending->aprvcpnyid,
                    'deptname' => $firstPending->aprvdeptid,
                    'date'     => $firstPending->aprvdatebefore,
                    'name'     => $firstPending->name,
                    'createdby'=> $header->created_by,
                    'info'     => $request->keperluan,
                    'status'   => $header->status, // 'P'
                    'docname'  => 'SPB',
                    'url'      => url('/showspbs/' . $eid),
                ];

                $approverUsernames = array_filter(array_map('trim', explode(',', (string)$firstPending->aprvusername)));
                $emails = User::whereIn('username', $approverUsernames)
                    ->where('status', 'A')
                    ->pluck('test_email');

                foreach ($emails as $email) {
                    \Mail::send('emails.mailapprovenew', $data, function ($message) use ($email, $data) {
                        $message->to($email)
                            ->subject($data['docid'].' - Waiting Approval SPB')
                            ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                    });
                }
            }

            DB::commit();

            return response()->json([
                'message'      => 'SPB updated successfully',
                'spbid'        => $header->spbid,
                'totalspbqty'  => $header->totalspbqty,
                'attachments'  => $uploadResult,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json([
                'message' => 'Update failed',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
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
 

    public function showSpb($hash)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        // --- Normalisasi hash -> spbid ---
        $spbid = null;

        // 1) hash lama: angka (id)
        $decoded = Hashids::decode($hash);
        if (isset($decoded[0]) && !empty($decoded[0])) {
            $byId = TrSPB::select('spbid')->where('id', $decoded[0])->first();
            if ($byId) {
                $spbid = $byId->spbid;
            }
        }

        // 2) hash baru: encodeHex(spbid) ATAU langsung kirim spbid
        if (!$spbid) {
            try {
                $maybeSpbid = Hashids::decodeHex($hash);
                $spbid = $maybeSpbid ?: $hash; // kalau decodeHex gagal, anggap hash = spbid asli
            } catch (\Throwable $e) {
                $spbid = $hash;
            }
        }

        abort_if(!$spbid, 404);

        // --- Header SPB + relasi sesuai model ---
        $spb = TrSPB::with([
            'worktype:worktypeid,worktype_name',
            'subworktype:subworktypeid,subworktype_name',
            'creator:username,name',
        ])->where('spbid', $spbid)->firstOrFail();

        // --- Detail + relasi lokasi ---
        $spbdetail = TrSPBdetail::with([
            'location:location_id,location_name',
            'subLocation:sub_location_id,sub_location_name',
        ])->where('spbid', $spb->spbid)->get();

        // --- Approval trail ---
        $approval = T_approval::where('docid', $spb->spbid)
            ->where('status', '<>', 'X')
            ->orderBy('created_at')
            ->orderBy('aprvid')
            ->get();

        // --- Attachments (GCS signed URL) ---
        $rows = TrAttachment::where('refnbr', $spb->spbid)
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

        $config = config('filesystems.disks.gcs');
        $keyFilePath = $config['key_file'];
        if (!Str::startsWith($keyFilePath, ['/', 'C:\\', 'D:\\'])) {
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

        // untuk konsistensi link detail, kirim balik hash apa adanya
        return view('pages.spbs.showspbs', compact('spb', 'approval', 'attachments', 'spbdetail', 'hash'));
    }

    
   
    public function approveSpb(Request $request, $docid)
    {
        $now  = Carbon::now();
        $user = $request->user();

        // $spb = TrSPB::where('spbid', $docid)->first();
        $spb = TrSPB::with('creator')
            ->where('spbid', $docid)
            ->first();
        $fullname = data_get($spb, 'creator.name') ?: $spb->created_by;

        if (!$spb) {
            return response()->json(['success' => false, 'message' => 'SPB not found'], 404);
        }

        // pastikan user memang approver aktif (status P) di doc ini
        $tApproval = T_approval::where('docid', $spb->spbid)
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
            $spb->completed_by = $user->username;
            $spb->completed_at = $now;
            $spb->save();

            // Hitung sisa pending setelah approve ini
            $pendingCount = T_approval::where('docid', $spb->spbid)
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

            $eid = Hashids::encode($spb->id);

            if ($pendingCount === 0) {
                // Tidak ada approver lagi -> dokumen complete
                $spb->status       = 'C';
                $spb->completed_by = $user->username;
                $spb->completed_at = $now;
                $spb->save();

                $spbdetail = TrSPBdetail::where('spbid', $spb->spbid)                
                    ->get();

                foreach ($spbdetail as $d) {
                    $d->status = 'C'; 
                    $d->save();
                }

                // Kirim email ke requester (creator)
                $status        = 'C';
                $subjectSuffix = $subjectMap[$status] ?? 'Notification';                

                $data = [
                    'docid'     => $spb->spbid,
                    'cpnyid'    => $spb->cpny_id ?? $spb->cpnyid ?? '',
                    'deptname'  => $spb->department_id ?? $spb->departementid ?? '',
                    'date'      => $spb->spbdate,
                    'fullname'  => $fullname,  // nama penerima di email
                    'name'      => $fullname,  // fallback
                    'createdby' => $fullname,
                    'docname'   => 'SPB',
                    'info'      => $spb->keperluan,
                    'status'    => $status,
                    'url'       => url('/showspbs/' . $eid),
                ];

                $recipients = User::where('username', $spb->created_by)
                    ->where('status', 'A')
                    ->get();

                foreach ($recipients as $rcp) {
                    try {
                        Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $rcp, $subjectSuffix) {
                            $to = $rcp->test_email ?? $rcp->email; // pakai field yang memang ada
                            $message->to($to)
                                ->subject($data['docid'] . ' - ' . $subjectSuffix . ' SPB')
                                ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                        });
                    } catch (\Throwable $e) {
                        Log::error('Failed sending SPB completion email', ['error' => $e->getMessage()]);
                    }
                }
            } else {
                // Masih ada approver berikutnya -> cari level berikutnya (P terrendah aprvid)
                $next = T_approval::where('docid', $spb->spbid)
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
                        'createdby' => $spb->created_by,
                        'docname'   => 'SPB',
                        'info'      => $spb->keperluan,
                        'status'    => $status,
                        'url'       => url('/showspbs/' . $eid),
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
                                        ->subject($data['docid'] . ' - ' . $subjectSuffix . ' SPB')
                                        ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                                });
                            } catch (\Throwable $e) {
                                Log::error('Failed sending SPB waiting-approval email', ['error' => $e->getMessage()]);
                            }
                        }
                    } else {
                        Log::warning('Next approver has empty aprvusername list', ['docid' => $spb->spbid]);
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Task approved successfully']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Approve SPB failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Approve failed'], 500);
        }
    }
    
    public function rejectSpb(Request $request, $docid)
    {
        $now  = Carbon::now();
        $user = $request->user();

        // $spb = TrSPB::where('spbid', $docid)->first();
        $spb = TrSPB::with('creator')
            ->where('spbid', $docid)
            ->first();
        $fullname = data_get($spb, 'creator.name') ?: $spb->created_by;

        if (!$spb) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        // Validasi: user harus approver aktif (status P) pada dokumen ini
        $tApproval = T_approval::where('docid', $spb->spbid)
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

            // Update header SPB
            $spb->status       = 'R';
            $spb->completed_by = $user->username;
            $spb->completed_at = $now;
            $spb->save();

            // Batalkan semua approval yang masih pending
            T_approval::where('docid', $spb->spbid)
                ->where('status', 'P')
                ->update(['status' => 'X']);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Reject SPB failed', ['docid' => $docid, 'error' => $e->getMessage()]);
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
        $eid = Hashids::encode($spb->id);

        $data = [
            'docid'     => $spb->spbid,
            'cpnyid'    => $spb->cpny_id ?? $spb->cpnyid ?? '',
            'deptname'  => $spb->department_id ?? $spb->departementid ?? '',
            'date'      => $now->toDateString(),            // bisa juga pakai $tApproval->aprvdateafter
            'fullname'  => $fullname,               // view email kita pakai $fullname
            'name'      => $fullname,               // fallback jika view pakai $name
            'createdby' => $fullname,
            'docname'   => 'SPB',
            'info'      => $spb->keperluan,
            'status'    => $status,
            'url'       => url('/showspbs/' . $eid),
        ];

        $recipients = User::where('username', $spb->created_by)
            ->where('status', 'A')
            ->get();

        foreach ($recipients as $rcp) {
            try {
                $to = $rcp->test_email ?? $rcp->email; // sesuaikan field yang tersedia
                Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
                    $message->to($to)
                        ->subject($data['docid'] . ' - ' . $subjectSuffix . ' SPB')
                        ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                });
            } catch (\Throwable $e) {
                Log::error('Failed sending SPB rejected email', [
                    'docid' => $data['docid'],
                    'to'    => $rcp->username,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Simpan komentar penolakan (jika ada)
        try {
            app('App\Http\Controllers\SendCommentController')
                ->sendmsg($spb->id, 'RB', $request);
        } catch (\Throwable $e) {
            Log::warning('SendComment after reject failed', [
                'docid' => $spb->spbid,
                'error' => $e->getMessage()
            ]);
        }

        return response()->json(['success' => true, 'message' => 'SPB rejected successfully']);
    }

    public function reviseSpb(Request $request, $docid)
    {
        $now  = Carbon::now();
        $user = $request->user();

        // $spb = TrSPB::where('spbid', $docid)->first();
        $spb = TrSPB::with('creator')
            ->where('spbid', $docid)
            ->first();
        $fullname = data_get($spb, 'creator.name') ?: $spb->created_by;
            
        if (!$spb) {
            return response()->json(['success' => false, 'message' => 'SPB not found'], 404);
        }

        // Pastikan user adalah approver aktif (status P) dokumen ini
        $tApproval = T_approval::where('docid', $spb->spbid)
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

            // Update header SPB
            $spb->status       = 'D';
            $spb->completed_by = $user->username;        // mengikuti pola existing
            $spb->completed_at = $now;
            $spb->save();

            // Batalkan approval lain yang masih pending
            T_approval::where('docid', $spb->spbid)
                ->where('status', 'P')
                ->update(['status' => 'X']);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Revise SPB failed', ['docid' => $docid, 'error' => $e->getMessage()]);
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
        $eid = Hashids::encode($spb->id);

        $data = [
            'docid'     => $spb->spbid,
            'cpnyid'    => $spb->cpny_id ?? $spb->cpnyid ?? '',
            'deptname'  => $spb->department_id ?? $spb->departementid ?? '',
            'date'      => $now->toDateString(),          // atau $tApproval->aprvdateafter
            'fullname'  => $fullname,             // template email pakai $fullname
            'name'      => $fullname,             // fallback jika view pakai $name
            'createdby' => $fullname,
            'docname'   => 'SPB',
            'info'      => $spb->keperluan,
            'status'    => $status,
            'url'       => url('/showspbs/' . $eid),
        ];

        $recipients = User::where('username', $spb->created_by)
            ->where('status', 'A')
            ->get();

        foreach ($recipients as $rcp) {
            try {
                $to = $rcp->test_email ?? $rcp->email; // sesuaikan dengan kolom yang ada
                Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
                    $message->to($to)
                        ->subject($data['docid'] . ' - ' . $subjectSuffix . ' SPB')
                        ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                });
            } catch (\Throwable $e) {
                Log::error('Failed sending SPB revise email', [
                    'docid' => $data['docid'],
                    'to'    => $rcp->username,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Simpan komentar revisi (jika ada)
        try {
            app('App\Http\Controllers\SendCommentController')
                ->sendmsg($spb->id, 'RB', $request);
        } catch (\Throwable $e) {
            Log::warning('SendComment after revise failed', [
                'docid' => $spb->spbid,
                'error' => $e->getMessage()
            ]);
        }

        return response()->json(['success' => true, 'message' => 'SPB revised successfully']);
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

        $spb = TrSPB::findOrFail($id);

        $getName = function (?string $username) {
            if (!$username) return null;
            $u = \App\Models\User::where('username', $username)->first();
            return $u->name ?? $username;
        };

        $createdByName = $getName($spb->created_by ?? null);
        $createdAt     = $spb->created_at ? \Carbon\Carbon::parse($spb->created_at)->format('Y-m-d H:i') : null;

        $completedByName = $getName($spb->completed_by ?? null);
        $completedAt     = $spb->completed_at ? \Carbon\Carbon::parse($spb->completed_at)->format('Y-m-d H:i') : null;

        // kolom opsional, kalau tidak ada biarkan null
        $rejectedByName  = $getName($spb->rejected_by ?? null);
        $rejectedAt      = isset($spb->rejected_at) ? \Carbon\Carbon::parse($spb->rejected_at)->format('Y-m-d H:i') : null;

        $revisedByName   = $getName($spb->revised_by ?? null);
        $revisedAt       = isset($spb->revised_at) ? \Carbon\Carbon::parse($spb->revised_at)->format('Y-m-d H:i') : null;

        $status = (string) ($spb->status ?? '');
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
            'title'        => 'SPB',
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
            'doc'   => $spb->spbid ?? (string)$spb->id,
            'steps' => $steps,
            'status'=> $status,
            'status_label' => $statusLabel,
        ]);
    }

    public function printSpb($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $authUser = Auth::user();
        if (!$authUser) {
            return redirect()->route('login');
        }


        // Ambil SPB + relasi yang dibutuhkan
         $spb = TrSPB::with([
            'worktype:worktypeid,worktype_name',
            'subworktype:subworktypeid,subworktype_name',
            'creator:username,name',
        ])->where('id', $id)->firstOrFail();

        // --- Detail + relasi lokasi ---
        $spbdetail = TrSPBdetail::with([
            'location:location_id,location_name',
            'subLocation:sub_location_id,sub_location_name',
        ])->where('spbid', $spb->spbid)->get();

        // Approval list (non-cancelled)
        $approval = T_approval::where('docid', $spb->spbid)
            ->where('status', '<>', 'X')
            ->orderBy('aprvid')
            ->orderBy('created_at')
            ->get();

        $approve_count = $approval->count();

        // Company (handle null)
        $company = Company::where('cpnyid', $spb->cpny_id)->first();

        // Mapping status dokumen
        switch ($spb->status) {
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
            'title'               => 'Surat Permintaan Barang',
            'doc_type'            => 'SPB',
            'docid'               => $spb->spbid,
            'department_id'       => $spb->department_id,
            'cpnyname'            => optional($company)->cpnyname,
            'parent'              => optional($company)->parent,
            'project'             => optional($company)->project,
            // identitas & tanggal
            'created_by_username' => $spb->created_by,
            'created_by_name'     => ucwords(strtolower(optional($spb->creator)->name)),
            'created_at_fmt'      => optional($spb->created_at)->format('d F Y'),
            'req_date_fmt'        => optional($spb->created_at)->format('d M Y H:i'),
            'spbdate'            => \Carbon\Carbon::parse($spb->spbdate)->format('d F Y'),
            // konten
            'keperluan'           => $spb->keperluan,
            'status_doc'          => $status_doc,
            'requesttype_name'    => optional($spb->requestType)->requesttype_name,
        ];

        // Kirim ke view
        $pdf = \PDF::loadView(
            'pages.spbs.pdf_spbs',
            array_merge($data, [
                'detail'         => $spbdetail,
                'approval'       => $approval,
                'approve_count'  => $approve_count,
            ])
        );

        // Portrait jika <= 5 approver, else landscape
        $pdf->setPaper('A4', ($approve_count <= 5) ? 'portrait' : 'landscape');

        return $pdf->stream("pdf_spbs_{$spb->spbid}.pdf");
    }





    






}
