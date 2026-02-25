<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Autonbr;
use App\Models\MsCompany;
use App\Models\MsDepartment;
use App\Models\Usercpny;
use App\Models\Userdept;
use App\Models\User;
use App\Models\Site;
use App\Models\Division;
use App\Models\TrSPPB;
use App\Models\TrSPPBdetail;
use App\Models\MsLocation;
use App\Models\MsSubLocation;
use Mail;
use Illuminate\Support\Facades\Log;
use PDF;
use Vinkla\Hashids\Facades\Hashids;
use App\Http\Controllers\TrAttachmentController;
use Illuminate\Support\Facades\Response;
use App\Models\TrAttachment;
use Illuminate\Support\Str;
use Google\Cloud\Storage\StorageClient;
use App\Http\Controllers\ApprovalController;
use App\Models\TrApproval;
use App\Models\SysUserRole;
use App\Models\MsSite;

use App\Models\TrCS;
use App\Models\TrCSdetail;
use App\Models\TrPO;
use App\Models\TrPOdetail;
use App\Models\TrReceipt;
use App\Models\TrReceiptdetail;
use App\Models\VTrackingSppbFlow;
use App\Http\Controllers\Traits\HasAutonbr;


class SppbController extends Controller
{
    use HasAutonbr;
    public function index()
    {
        $user = Auth::user();       

        if (!$user) {
            return redirect()->route('login');
        }

        if (is_string($user->cpny_id)) {
            $cpnyIds = array_map('trim', explode(',', $user->cpny_id));
        } else {
            $cpnyIds = (array) $user->cpny_id;
        }

        // department_id juga bisa multi, tapi di debug sudah "IT"
        if (is_string($user->department_id)) {
            $deptIds = array_map('trim', explode(',', $user->department_id));
        } else {
            $deptIds = (array) $user->department_id;
        }

        $all = TrSPPB::whereIn('cpny_id', $cpnyIds)
                    ->whereIn('department_id', $deptIds)
                    ->count();

        $onProgress = TrSPPB::where('status', 'P')
                    ->whereIn('cpny_id', $cpnyIds)
                    ->whereIn('department_id', $deptIds)
                    ->count();

        $reject = TrSPPB::where('status', 'R')
                    ->whereIn('cpny_id', $cpnyIds)
                    ->whereIn('department_id', $deptIds)
                    ->count();

        $revise = TrSPPB::where('status', 'D')
                    ->whereIn('cpny_id', $cpnyIds)
                    ->whereIn('department_id', $deptIds)
                    ->count();

        $completed = TrSPPB::where('status', 'C')
                    ->whereIn('cpny_id', $cpnyIds)
                    ->whereIn('department_id', $deptIds)
                    ->count();

        return view('pages.sppbs.sppbs', compact('all', 'onProgress', 'reject', 'revise', 'completed'));
    }

    
    public function json(Request $request)
    {
        $user = Auth::user();

        if (is_string($user->cpny_id)) {
            $cpnyIds = array_map('trim', explode(',', $user->cpny_id));
        } else {
            $cpnyIds = (array) $user->cpny_id;
        }

        // department_id juga bisa multi, tapi di debug sudah "IT"
        if (is_string($user->department_id)) {
            $deptIds = array_map('trim', explode(',', $user->department_id));
        } else {
            $deptIds = (array) $user->department_id;
        }

        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $search = trim((string) $request->input('search.value', ''));
        $status = (string) $request->query('status', '');

        $baseTable = (new TrSPPB)->getTable();

        $columns = [
            0 => 'sppb.sppbid',
            1 => 'sppb.sppbdate',
            2 => 'sppb.cpny_id',
            3 => 'sppb.department_id',
            4 => 'rt.requesttype_name',
            5 => 'sppb.keperluan',
            6 => 'sppb.status',
        ];

        $orderIdx = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'asc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'sppb.sppbid';

        $base = TrSPPB::from($baseTable . ' as sppb')
            ->leftJoin('ms_request_type as rt', function ($join) {
                $join->on('rt.requesttypeid', '=', 'sppb.requesttypeid');
            })
            ->whereIn('sppb.cpny_id', $cpnyIds)            // ✔ filter tambahan
            ->whereIn('sppb.department_id', $deptIds);    // ✔ filter tambahan

        if ($status !== '') {
            $base->where('sppb.status', $status);
        }

        // Total sebelum search
        $recordsTotal = (clone $base)->distinct('sppb.sppbid')->count('sppb.sppbid');

        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('sppb.sppbid',          'ilike', "%{$search}%")
                ->orWhere('sppb.cpny_id',       'ilike', "%{$search}%")
                ->orWhere('sppb.department_id', 'ilike', "%{$search}%")
                ->orWhere('rt.requesttype_name','ilike', "%{$search}%")
                ->orWhere('sppb.keperluan',     'ilike', "%{$search}%")
                ->orWhere('sppb.status',        'ilike', "%{$search}%");
            });
        }

        // Total setelah search
        $recordsFiltered = (clone $base)->distinct('sppb.sppbid')->count('sppb.sppbid');

        $data = $base->select(
                    'sppb.id',
                    'sppb.sppbid',
                    'sppb.sppbdate',
                    'sppb.cpny_id',
                    'sppb.department_id',
                    'sppb.requesttypeid',
                    'rt.requesttype_name',
                    'sppb.keperluan',
                    'sppb.status',
                    'sppb.created_by'
                )
                ->orderBy($orderCol, $orderDir)
                ->orderBy('sppb.sppbid', 'desc')
                ->skip($start)
                ->take($length)
                ->get();

        // Add encrypted ID
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



    
    public function createSppb()
    {        
        $user = Auth::user();       

        if (!$user) {
            return redirect()->route('login');
        }

        $usercpny = Usercpny::where('username', $user->username)
            ->get();       
        
        $usercpny2 = Usercpny::where('username', $user->username)
            ->first();
        $userdept = Userdept::where('username', $user->username)
            ->get();
        $userdept2 = Userdept::where('username', $user->username)
            ->first();                   
       
        $akses_stock = SysUserRole::where('username', $user->username)
            ->where('role_id','WHSACCESS')
            ->first();

        return view('pages.sppbs.createsppbs', compact('usercpny','usercpny2','userdept','userdept2','akses_stock'));
    }

    
    
    public function storeSppb(Request $request)
    {
        // dd($request->all()); // Debugging: check request data
        // kumpulkan array dari form
        $inventoryIds  = $request->input('inventoryid',  $request->input('inventory_id', []));
        $productNames  = $request->input('product_name', []);
        $qtys          = $request->input('qty', []);
        $uoms          = $request->input('stock_unit',   $request->input('uom', [])); // <- penting
        $notes         = $request->input('note', []);
        $locations     = $request->input('location', []);
        $locationIds   = $request->input('location_id', $request->input('locationid', [])); // <- kalau perlu simpan
        $subLocIds     = $request->input('sub_location_id', $request->input('sublocationid', []));
        $subLocations  = $request->input('sub_location', []);      
        $activityIds   = $request->input('activity_id', []);
        $busUnitIds    = $request->input('business_unit_id', []);
        $deptFinIds    = $request->input('department_fin_id', []);
        $actDescrs     = $request->input('activity_descr', []);
        $coaIds        = $request->input('coa_id', []); // account_id
        $item_types    = $request->input('item_type', []);
        // $item_categories = $request->input('item_category', []);
        $siteids        = $request->input('siteid', []);        
        
        $purchaseUnits    = $request->input('purchase_unit', []);     // dari hidden purchase_unit[]
        $uomMultDivs      = $request->input('uom_unitmultdiv', []);   // 'M' atau 'D'
        $uomRates         = $request->input('uom_unitrate', []);      // bisa "12", "12,5", "12.000",

        $inventoryCategories = $request->input('item_category', []);      // baris pertama untuk Komputer
        $inventorySubTypes   = $request->input('item_sub_type', []); // untuk Fixed Asset subtype

        $doctype  = 'PB';
        $user     = $request->user();
        $username = $user->username ?? 'system';
        $fullname = $user->name ?? 'system';

        $dt        = Carbon::now();        
        // $month     = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        // $datestamp = $dt->toDateTimeString();
        $year  = (int) (int) $dt->year;
        $month = str_pad((string)$dt->month, 2, '0', STR_PAD_LEFT);


        // helper untuk normalisasi angka lokal (ID format)     
        $toFloat = function ($v): ?float {
            if ($v === null || $v === '') return null;
            $s = preg_replace('/\s+/', '', (string)$v);

            $hasComma = strpos($s, ',') !== false;
            $hasDot   = strpos($s, '.') !== false;

            if ($hasComma && $hasDot) {
                // Decimal = separator yang muncul paling akhir
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
                // hanya koma → koma = decimal
                $s = str_replace(',', '.', $s);
            } elseif ($hasDot) {
                // hanya titik → asumsikan titik = decimal
                // kalau ada >1 titik, anggap titik = ribuan → hapus semua titik
                if (substr_count($s, '.') > 1) {
                    $s = str_replace('.', '', $s);
                }
                // kalau 1 titik, biarkan sebagai decimal
            }
            return is_numeric($s) ? (float)$s : null;
        };


        // pastikan line approval ada
        // $approvalCount = M_approval::where([
        //     ['status', '=', 'A'],
        //     ['aprvcpnyid', '=', $request->cpnyid],
        //     ['aprvdeptid', '=', $request->departementid],
        //     ['aprvdoctype', '=', $doctype],
        // ])->count();

        // if ($approvalCount === 0) {
        //     return response()->json([
        //         'message' => 'Approval line belum di-setup, Please contact IT!',
        //     ], 422);
        // }
       
        // ===== generate TrApproval dari MsApproval sesuai context =====
        $approvalCtl = app(ApprovalController::class);

        // Pastikan line approval ada (kalau mau validasi awal sebelum simpan detail, panggil loadLines)
        $approvalCtl->loadLines($doctype, $request->cpnyid, $request->departementid);

        DB::beginTransaction();
        try {
            // === generate autonbr & docid (lock) ===
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
            //     $urutan = $autonbr->number + 1;
            //     $autonbr->update(['number' => $urutan]);
            // }

            // $tglbln = substr($year, 2) . $month;               // YYMM
            // $docid  = $doctype . $tglbln . sprintf("%04d", $urutan);
            // $sppbNo = $docid;                                   // atau 'SPPB-'.$docid
            $auto = $this->nextAutonbr(
                $doctype,
                $year,
                $month,
                $username,
                'SPPB'
            );
            $urutan = (int) $auto['next'];

            $tglbln = substr((string)$year, 2) . $month;   // YYMM
            $docid  = $doctype . $tglbln . sprintf("%04d", $urutan);
            $sppbNo = $docid;


            // === 1) header dulu (totalqty sementara 0) ===
            $header = new TrSPPB();
            $header->sppbid            = $docid;                // PK string
            $header->sppbdate          = $dt->toDateString();
            $header->cpny_id           = $request->input('cpnyid');
            $header->department_id     = $request->input('departementid');
            $header->requesttypeid     = $request->input('requesttypeid');
            $header->keperluan         = $request->input('keperluan');
            $header->budget_perpost    = $request->input('perpost');
            $header->woid              = $request->input('woid');
            $header->is_urgent         = $request->input('is_urgent');
            $header->spbid             = null;
            $header->totalopenordered  = 0;
            $header->totalqty          = 0;
            $header->totalordered      = 0;
            $header->totalrejectordered = 0;
            $header->totalcompleteordered = 0;
            $header->assignby          = null;
            $header->assigndate        = null;
            $header->assignpurchasing  = null;
            $header->csjobs            = null;
            $header->cs                = null;
            $header->status            = 'P';
            $header->created_by        = $username;
            $header->save();

            // === 2) detail ===
            $totalQty         = 0;
            $totalOpenOrdered = 0;
            $rowCount = max(count($inventoryIds), count($qtys));

            // ===== default site fallback (ambil sekali per header cpny) =====
            $defaultSiteId = null;
            try {
                $defaultSiteId = MsSite::query()
                    ->where('cpny_id', $request->cpnyid)
                    ->where(function($q){
                        $q->where('site_default', true)
                        ->orWhere('site_default', 'true')
                        ->orWhere('site_default', 1)
                        ->orWhere('site_default', '1');
                    })
                    ->value('siteid'); // langsung ambil siteid saja
            } catch (\Throwable $e) {
                // optional: log saja, jangan hentikan proses
                \Log::warning('Failed to get default site', [
                    'cpnyid' => $request->cpnyid,
                    'err' => $e->getMessage(),
                ]);
            }
           
           
            for ($i = 0; $i < $rowCount; $i++) {
                $invId = $inventoryIds[$i] ?? null;
                $productName = $productNames[$i] ?? null;
                // qty: sudah kamu konversi koma->titik di JS; tetap jaga-jaga:
                $qty   = (float) str_replace(',', '.', (string) ($qtys[$i] ?? 0));
                $uom   = $uoms[$i] ?? null;

                if (empty($invId) || $qty <= 0) continue;

                // ==== perhitungan base_* ====
                $baseUom        = $purchaseUnits[$i] ?? null;                   // WAJIB: purchase_unit
                $typeMultiplier = strtoupper(trim((string)($uomMultDivs[$i] ?? ''))); // 'M' / 'D' / ''
                $rateRaw        = $uomRates[$i] ?? null;
                $rate           = $toFloat($rateRaw) ?? 1.0;                     // default 1 kalau kosong/tidak valid
                if ($rate <= 0) {                                                // guard divide-by-zero & negatif
                    $rate = 1.0;
                    $typeMultiplier = '';                                        // anggap tidak ada konversi
                }

                // base_qty logic
                $baseQty = $qty;
                if ($typeMultiplier === 'M') {
                    $baseQty = $qty * $rate;
                } elseif ($typeMultiplier === 'D') {
                    $baseQty = $qty / $rate;
                }

                $siteFromForm = trim((string)($siteids[$i] ?? ''));
                $finalSiteId  = $siteFromForm !== '' ? $siteFromForm : $defaultSiteId;

                // optional: kalau wajib
                if (empty($finalSiteId)) {
                    throw new \Exception("SiteID kosong dan default site tidak ditemukan untuk Company {$request->cpnyid}.");
                }

                $detail = new TrSPPBdetail();
                $detail->sppbid                   = $docid;
                $detail->sppb_no                  = $i + 1;   // nomor urut detail
                $detail->inventoryid              = $invId;
                $detail->inventory_descr          = $productName;
                // $detail->siteid                   = $siteids[$i] ?? null;
                $detail->siteid                   = $finalSiteId;
                $detail->qty                      = $qty;
                $detail->uom                      = $uom;
                $detail->note                     = $notes[$i]   ?? null;
                $detail->inventory_type           = $item_types[$i] ?? null;
                $detail->inventory_sub_type       = $inventorySubTypes[$i] ?? null;
                $detail->inventory_category       = $inventoryCategories[$i] ?? null;
                $detail->base_uom                 = $baseUom;            // = purchase_unit
                $detail->base_multiplier          = $rate;               // = uom_unitrate (float)
                $detail->type_multiplier          = $typeMultiplier ?: null; // = 'M' / 'D' / null
                $detail->base_qty                 = $baseQty;            // hitungan M/D               
                $detail->budget_cpny_id           = $request->cpnyid;
                $detail->budget_business_unit_id  = $busUnitIds[$i]     ?? null;
                $detail->budget_department_fin_id = $deptFinIds[$i] ?? null;                
                $detail->budget_account_id        = $coaIds[$i]         ?? null;
                $detail->budget_activity_id       = $activityIds[$i]   ?? null;  
                $detail->budget_activity_descr    = $actDescrs[$i] ?? null;             
                $detail->location_id              = $locationIds[$i]  ?? null;
                $detail->sub_location_id          = $subLocIds[$i]    ?? null;
                $detail->budget_perpost           = $request->perpost;
                $detail->assignby                 = null;
                $detail->assigndate               = null;
                $detail->assignpurchasing         = null;
                $detail->openordered              = $qty;
                $detail->ordered                  = 0;
                $detail->rejectordered            = 0;
                $detail->completeordered          = 0;
                $detail->status                   = 'P';
                $detail->created_by               = $username;
                $detail->save();

                $totalQty += $qty;
            }

            // update totalqty di header
            $header->totalqty = $totalQty;
            $header->totalopenordered = $totalQty;
            $header->save();

            // // === 4) copy line approval (M_approval -> T_approval) ===
            // $approvals = M_approval::where([
            //     ['status', '=', 'A'],
            //     ['aprvcpnyid', '=', $request->cpnyid],
            //     ['aprvdeptid', '=', $request->departementid],
            //     ['aprvdoctype', '=', $doctype],
            // ])->get();

            // foreach ($approvals as $a) {
            //     T_approval::create([
            //         'docid'          => $docid,
            //         'aprvid'         => $a->aprvid,
            //         'aprvdoctype'    => $a->aprvdoctype,
            //         'aprvcpnyid'     => $a->aprvcpnyid,
            //         'aprvdeptid'     => $a->aprvdeptid,
            //         'aprvusername'   => $a->aprvusername,
            //         'name'           => $a->name,
            //         'aprvdatebefore' => $a->aprvid == 1 ? $datestamp : null,
            //         'aprvtotalday'   => 1,
            //         'status'         => 'P',
            //         'created_by'   => $username,
            //     ]);
            // }

            // $firstApprovalUsernames = optional($approvals->first())->aprvusername; // bisa comma-separated
            // if ($firstApprovalUsernames) {
            //     $header->completed_by = $firstApprovalUsernames;
            //     $header->completed_at = $dt; // atau Carbon::now()
            //     $header->save();
            // }

                  

            // 1) Urgent → dari header field is_urgent (boolean atau "1"/"true")
            $isUrgent = (bool) $request->input('is_urgent', false);

            // 2) Komputer → hanya kategori pada BARIS PERTAMA yang non-empty
            $firstCategory = null;
            if (!empty($inventoryCategories)) {
                foreach ($inventoryCategories as $c) {
                    if (!empty($c)) { $firstCategory = $c; break; }
                }
            }

            // 3) Fixed Asset → minimal ada SATU detail dengan inventory_sub_type = Fixed Asset / FA
            $hasFixedAssetSubtype = false;
            foreach ((array)$inventorySubTypes as $sub) {
                $s = mb_strtolower((string)$sub);
                if ($s === 'fixed asset' || $s === 'fa') { $hasFixedAssetSubtype = true; break; }
            }

            // 4) Build context untuk ApprovalController
            $ctx = [
                'is_urgent'                => $isUrgent,
                'first_inventory_category' => $firstCategory,
                'has_fixed_asset_subtype'  => $hasFixedAssetSubtype,
                'ignore_nominal'           => true,   // SPPB diminta tidak cek nominal
                // 'grand_total'           => ...     // tidak dipakai di SPPB
            ];

            // Generate TrApproval
            [$firstApprovalUsernames, $linesCount] = $approvalCtl->generateForDocument(
                $docid,
                $doctype,
                $request->cpnyid,
                $request->departementid,
                $username,
                $ctx,
                $dt
            );

            // (opsional) simpan hint approver pertama di header seperti sebelumnya
            if ($firstApprovalUsernames) {
                $header->completed_by = $firstApprovalUsernames;
                $header->completed_at = $dt;
                $header->save();
            }

            // === 5) attachments (opsional) ===
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
            //         $attach->docid = $docid;
            //         $attach->name = $filename;
            //         $attach->attachfile = $attachfile;
            //         $attach->status = 'A';
            //         $attach->extention = $file->getClientOriginalExtension();
            //         $attach->created_user = $user->username;
            //         $attach->save();
            //     }
            // }            

            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr'        => $docid,
                    'doctype'       => $doctype,
                    'cpnyid'        => $request->input('cpnyid'),
                    'departementid' => $request->input('departementid'),                    
                    'base_folder'   => 'att-purchasing-app/'.strtolower($doctype),
                    'created_by'    => $user->username,
                ];

                $files = (array) $request->file('attachments');

                try {
                    $uploader = app(TrAttachmentController::class);
                    $uploadResult = $uploader->uploadInternal($meta, $files);
                    // tidak return di sini!
                } catch (\Throwable $e) {
                    \DB::rollBack();
                    return response()->json([
                        'message' => 'Failed to create PB',
                        'error'   => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            } else {
                $uploadResult = null; // tidak ada attachment
            }

            $eid = Hashids::encode($header->id);

            $approvalCtl->notifyFirstApprover(
                    $docid,
                    $doctype,
                    $header->status,                 // 'P' | 'R' | 'D' | 'A' | 'C'
                    'SPPB',
                    url('/showsppbs/' . $eid),
                    [
                        'info'      => $request->keperluan,
                        'createdby' => $header->created_by,
                        'date'      => $dt->toDateTimeString(),
                    ]
                );

            // === 6) kirim email ke approver pertama ===
            // $firstApproval = T_approval::where('docid', $docid)
            //     ->where('status', 'P')
            //     ->orderBy('aprvid')
            //     ->first();

            // if ($firstApproval) {

            //     $status = $header->status; // 'P' | 'R' | 'D' | 'A' | 'C'
                
            //     $subjectMap = [
            //         'P' => 'Waiting Approval',
            //         'R' => 'Rejected Approval',
            //         'D' => 'Revise Approval',
            //         'A' => 'Approved',
            //         'C' => 'Completed',
            //     ];
            //     $subjectSuffix = $subjectMap[$status] ?? 'Notification';

            //     $eid = Hashids::encode($header->id);

                                
            //     $data = [
            //         'docid'    => $firstApproval->docid,
            //         'cpnyid'   => $firstApproval->aprvcpnyid,
            //         'deptname' => $firstApproval->aprvdeptid,
            //         'date'     => $firstApproval->aprvdatebefore,
            //         'name'     => $firstApproval->name,
            //         'createdby'=> $header->created_by,
            //         'info'     => $request->keperluan,
            //         'status'   => $status,
            //         'docname'  => 'SPPB',
            //         'url'      => url('/showsppbs/' . $eid),
            //     ];
                
            //     $approvers = array_filter(array_map('trim', explode(',', (string)$firstApproval->aprvusername)));
            //     $emails = User::whereIn('username', $approvers)
            //         ->where('status', 'A')
            //         ->pluck('notification_email');

            //     foreach ($emails as $email) {
            //         \Mail::send('emails.mailapprovenew', $data, function ($message) use ($email, $data) {
            //             $message->to($email)
            //                 ->subject($data['docid'].' - Waiting Approval SPPB')
            //                 ->from('digitalserver@pakuwon.com', 'Pakuwon System');
            //         });
            //     }
            // }

            DB::commit();

            return response()->json([
                'message'  => 'SPPB created successfully',
                'sppbid'   => $docid,
                'sppb_no'  => $sppbNo,
                'totalqty' => $totalQty,
                'attachments' => $uploadResult,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'message' => 'Failed to create SPPB',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
   
    public function editSppb($hash)
    {
        $user = Auth::user();       

        if (!$user) {
            return redirect()->route('login');
        }
        
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $sppb = TrSPPB::findOrFail($id);

        // Ambil detail + eager load relasi lokasi & sublokasi
        $sppbdetail = TrSPPBdetail::with([
                'location:location_id,location_name',
                'subLocation:sub_location_id,sub_location_name',
            ])
            ->where('sppbid', $sppb->sppbid)
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

        // $attachment = Attachment::where('docid', $sppb->sppbid)
        //     ->where('status', 'A')
        //     ->get();

        $rows = TrAttachment::where('refnbr', $sppb->sppbid)
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

        $akses_stock = SysUserRole::where('username', $user->username)
            ->where('role_id','WHSACCESS')
            ->first();

        return view('pages.sppbs.editsppbs', compact(
            'sppb','sppbdetail','usercpny','usercpny2','userdept','userdept2','attachments','hash','akses_stock'
        ));
    }



    public function updateSppb(Request $request, $hash)
    {
        // dd($request->all()); // matikan agar eksekusi lanjut

        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404, 'PB tidak ditemukan.');

        $user      = $request->user();   
        $dt        = Carbon::now();
        $year      = (int) $dt->year;
        $month     = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        $datestamp = $dt->toDateTimeString();   
        $doctype   = 'PB';
        $username  = $user->username ?? 'system';
        $fullname  = $user->name ?? 'system';

        // ===== generate TrApproval dari MsApproval sesuai context =====
        $approvalCtl = app(ApprovalController::class);

        // Pastikan line approval ada (kalau mau validasi awal sebelum simpan detail, panggil loadLines)
        $approvalCtl->loadLines($doctype, $request->cpnyid, $request->departementid);

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

        $header = TrSPPB::findOrFail($id);
        // update header
        $header->cpny_id        = $request->cpnyid;
        $header->department_id  = $request->departementid;
        $header->requesttypeid  = $request->requesttypeid;
        $header->keperluan      = $request->keperluan;
        $header->budget_perpost = $request->perpost;   
        $header->woid           = $request->woid;
        $header->is_urgent      = $request->is_urgent;
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
        $actDescrs    = array_values($request->input('activity_descr', []));        
        $coaIds       = array_values($request->input('coa_id', []));
        $itemTypes    = array_values($request->input('item_type', []));
        $itemCats     = array_values($request->input('item_category', []));
        $siteids     = array_values($request->input('siteid', []));
        
        $inventorySubTypes   = $request->input('item_sub_type', []);

        // arrays UoM tambahan
        $purchaseUnits = array_values($request->input('purchase_unit', []));      // hidden dari UI
        $uomMultDivs   = array_values($request->input('uom_unitmultdiv', []));    // 'M'/'D'
        $uomRates      = array_values($request->input('uom_unitrate', []));       // bisa "12.000"

        DB::beginTransaction();

        try {
            // hapus baris yang di-mark delete
            if ($request->filled('deleted_detail_ids')) {
                $idsToDelete = array_filter(array_map('trim', explode(',', $request->deleted_detail_ids)));
                if ($idsToDelete) TrSPPBdetail::whereIn('id', $idsToDelete)->delete();
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
                    'siteid'                   => $siteids[$i] ?? null,
                    'qty'                      => $qty,
                    'uom'                      => $displayUom,
                    'note'                     => $notes[$i] ?? null,
                    'inventory_type'                => $itemTypes[$i] ?? null,
                    'inventory_sub_type'            => $inventorySubTypes[$i] ?? null,
                    'inventory_category'            => $itemCats[$i] ?? null,

                    // >>> ini yang ditambahkan <<<
                    'base_uom'                 => $baseUom,                       // purchase_unit
                    'base_multiplier'          => $rate,                          // uom_unitrate (float)
                    'type_multiplier'          => $typeMultiplier ?: null,        // 'M'/'D'/null
                    'base_qty'                 => $baseQty,                        // hasil M/D

                    'budget_cpny_id'           => $request->cpnyid,
                    'budget_business_unit_id'  => $buIds[$i] ?? null,
                    'budget_department_fin_id' => $deptFinIds[$i] ?? null,
                    'budget_activity_descr'    => $actDescrs[$i] ?? null,
                    'budget_account_id'        => $coaIds[$i] ?? null,
                    'budget_activity_id'       => $actIds[$i] ?? null,
                    'openordered'              => $qty,
                    'ordered'                  => 0,
                    'rejectordered'            => 0,
                    'completeordered'          => 0,
                    'location_id'              => $locIds[$i] ?? null,
                    'sub_location_id'          => $subLocIds[$i] ?? null,
                    'budget_perpost'           => $request->perpost,
                    'status'                   => 'P',
                    'updated_by'               => $username,
                ];

                $idDetail = $detailIds[$i] ?? null;

                if ($idDetail) {
                    $detail = TrSPPBdetail::where('id', $idDetail)
                        ->where('sppbid', $header->sppbid)
                        ->first();
                    if ($detail) {
                        $detail->fill($data)->save();
                    } else {
                        $detail = new TrSPPBdetail($data);
                        $detail->sppbid = $header->sppbid;
                        $detail->save();
                    }
                } else {
                    $detail = new TrSPPBdetail($data);
                    $detail->sppbid = $header->sppbid;
                    $detail->save();
                }

                $savedDetails[] = $detail->id;
            }

            // Renumber sppb_no 1..N
            $n = 1;
            foreach ($savedDetails as $did) {
                TrSPPBdetail::where('id', $did)->update(['sppb_no' => $n++]);
            }

            // Hitung total qty (kalau mau pakai base_qty, ganti ke sum('base_qty'))
            $totalQty = TrSPPBdetail::where('sppbid', $header->sppbid)->sum('qty');
            $header->totalqty = $totalQty;
            $header->totalopenordered = $totalQty;
            $header->save();

            // // === regenerasi T_approval (opsional, ikuti logikamu) ===
            // $approvals = M_approval::where([
            //     ['status', '=', 'A'],
            //     ['aprvcpnyid', '=', $request->cpnyid],
            //     ['aprvdeptid', '=', $request->departementid],
            //     ['aprvdoctype', '=', $doctype],
            // ])->get();

            // foreach ($approvals as $a) {
            //     T_approval::create([
            //         'docid'          => $header->sppbid,
            //         'aprvid'         => $a->aprvid,
            //         'aprvdoctype'    => $a->aprvdoctype,
            //         'aprvcpnyid'     => $a->aprvcpnyid,
            //         'aprvdeptid'     => $a->aprvdeptid,
            //         'aprvusername'   => $a->aprvusername,
            //         'name'           => $a->name,
            //         'aprvdatebefore' => $a->aprvid == 1 ? $datestamp : null,
            //         'aprvtotalday'   => 1,
            //         'status'         => 'P',
            //         'created_by'   => $username,
            //     ]);
            // }

            // $firstApprovalUsernames = optional($approvals->first())->aprvusername;
            // if ($firstApprovalUsernames) {
            //     $header->completed_by = $firstApprovalUsernames;
            //     $header->completed_at = $dt;
            //     $header->save();
            // }

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
            //         $attach->docid = $header->sppbid;
            //         $attach->name = $filename;
            //         $attach->attachfile = $attachfile;
            //         $attach->status = 'A';
            //         $attach->extention = $file->getClientOriginalExtension();
            //         $attach->created_user = $user->username;
            //         $attach->save();
            //     }
            // }       

            // 1) Urgent → dari header field is_urgent (boolean atau "1"/"true")
            $isUrgent = (bool) $request->input('is_urgent', false);

            // 2) Komputer → hanya kategori pada BARIS PERTAMA yang non-empty
            $firstCategory = null;
            if (!empty($inventoryCategories)) {
                foreach ($inventoryCategories as $c) {
                    if (!empty($c)) { $firstCategory = $c; break; }
                }
            }

            // 3) Fixed Asset → minimal ada SATU detail dengan inventory_sub_type = Fixed Asset / FA
            $hasFixedAssetSubtype = false;
            foreach ((array)$inventorySubTypes as $sub) {
                $s = mb_strtolower((string)$sub);
                if ($s === 'fixed asset' || $s === 'fa') { $hasFixedAssetSubtype = true; break; }
            }

            // 4) Build context untuk ApprovalController
            $ctx = [
                'is_urgent'                => $isUrgent,
                'first_inventory_category' => $firstCategory,
                'has_fixed_asset_subtype'  => $hasFixedAssetSubtype,
                'ignore_nominal'           => true,   // SPPB diminta tidak cek nominal
                // 'grand_total'           => ...     // tidak dipakai di SPPB
            ];

            // Generate TrApproval
            [$firstApprovalUsernames, $linesCount] = $approvalCtl->generateForDocument(
                $header->sppbid,
                $doctype,
                $request->cpnyid,
                $request->departementid,
                $username,
                $ctx,
                $dt
            );

            // (opsional) simpan hint approver pertama di header seperti sebelumnya
            if ($firstApprovalUsernames) {
                $header->completed_by = $firstApprovalUsernames;
                $header->completed_at = $dt;
                $header->save();
            }

              
            $uploadResult = null;
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr'        => $header->sppbid,
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

            // // email approver pertama (tetap)
            // $firstApproval = T_approval::where('docid', $header->sppbid)
            //     ->where('status', 'P')
            //     ->orderBy('aprvid')
            //     ->first();

            // if ($firstApproval) {
            //     $status = $header->status; // 'P' | 'R' | 'D' | 'A' | 'C'

            //     $subjectMap = [
            //         'P' => 'Waiting Approval',
            //         'R' => 'Rejected Approval',
            //         'D' => 'Revise Approval',
            //         'A' => 'Approved',
            //         'C' => 'Completed',
            //     ];
            //     $subjectSuffix = $subjectMap[$status] ?? 'Notification';

            //     $eid = Hashids::encode($header->id);
                
            //     $data = [
            //         'docid'    => $firstApproval->docid,
            //         'cpnyid'   => $firstApproval->aprvcpnyid,
            //         'deptname' => $firstApproval->aprvdeptid,
            //         'date'     => $firstApproval->aprvdatebefore,
            //         'name'     => $firstApproval->name,
            //         'createdby'=> $header->created_by,
            //         'info'     => $request->keperluan,
            //         'status'   => $status,
            //         'docname'  => 'SPPB',
            //         'url'      => url('/showsppbs/' . $eid),
            //     ];

            //     $approvers = array_filter(array_map('trim', explode(',', (string)$firstApproval->aprvusername)));
            //     $emails = User::whereIn('username', $approvers)
            //         ->where('status', 'A')
            //         ->pluck('notification_email');

            //     foreach ($emails as $email) {
            //         \Mail::send('emails.mailapprovenew', $data, function ($message) use ($email, $data) {
            //             $message->to($email)
            //                 ->subject($data['docid'].' - Waiting Approval SPPB')
            //                 ->from('digitalserver@pakuwon.com', 'Pakuwon System');
            //         });
            //     }
            // }

            $eid = Hashids::encode($header->id);

            $approvalCtl->notifyFirstApprover(
                    $header->sppbid,
                    $doctype,
                    $header->status,                 // 'P' | 'R' | 'D' | 'A' | 'C'
                    'SPPB',
                    url('/showsppbs/' . $eid),
                    [
                        'info'      => $request->keperluan,
                        'createdby' => $header->created_by,
                        'date'      => $dt->toDateTimeString(),
                    ]
            );

            DB::commit();
            return response()->json(['message' => 'SPPB updated successfully']);

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
 

    public function showSppb($hash)
    {        
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();       

        if (!$user) {
            return redirect()->route('login');
        }

        // $sppb = TrSPPB::findOrFail($id);
        $sppb = TrSPPB::with([
            'requestType:requesttypeid,requesttype_name',
            'creator:username,name'
        ])
        ->findOrFail($id);        

        $sppbdetail = TrSPPBdetail::with([
            'location:location_id,location_name',
            'subLocation:sub_location_id,sub_location_name'
        ])
        ->where('sppbid', $sppb->sppbid)
        ->orderby('sppb_no', 'ASC')
        ->get();
        
        // $approval = T_approval::where('docid', $sppb->sppbid)
        //     ->where('status','<>','X')      
        //     ->orderBy('created_at')
        //     ->orderBy('aprvid')      
        //     ->get();
       
        // $attachment = Attachment::where('docid', $sppb->sppbid)    
        //     ->where('status','A')        
        //     ->get();    
        
        // ---------- ambil lampiran dari tr_attachment ----------
        $rows = TrAttachment::where('refnbr', $sppb->sppbid)
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
        
        $loginUsername = $user->username ?? $user->name ?? null;
        $canUpload     = $sppb->created_by === $loginUsername;
        $akses_cc = SysUserRole::where('username', $user->username)
            ->where('role_id','COSTCTRLACCESS')
            ->first();

        return view('pages.sppbs.showsppbs', compact('sppb','attachments','sppbdetail','hash','canUpload','akses_cc'));
    }
      
    public function approveSppb(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'PB';

        $sppb = TrSPPB::with('creator')->where('sppbid', $docid)->first();
        if (!$sppb) return response()->json(['success'=>false,'message'=>'SPPB not found'],404);

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($sppb->id);
        $docUrl   = url('/showsppbs/' . $eid);
        $fullname = data_get($sppb, 'creator.name') ?: $sppb->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->approveStep(
            $sppb->sppbid,
            $doctype,
            $user->username,
            $user->name,

            // complete: update header/detail + email creator complete
            function (string $refnbr, \Carbon\Carbon $now) use ($sppb, $fullname, $docUrl) {
                $sppb->status       = 'C';
                $sppb->completed_by = $sppb->completed_by ?: auth()->user()->username;
                $sppb->completed_at = $now;
                $sppb->save();

                TrSPPBdetail::where('sppbid', $sppb->sppbid)->update(['status' => 'C']);

                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $sppb->sppbid,
                    'SPPB',
                    'C',
                    $sppb->created_by,
                    $docUrl,
                    [
                        'cpnyid'   => $sppb->cpny_id ?? $sppb->cpnyid ?? '',
                        'deptname' => $sppb->department_id ?? $sppb->departementid ?? '',
                        'date'     => $sppb->sppbdate,
                        'info'     => $sppb->keperluan,
                        'fullname' => $fullname,
                        'name'     => $fullname,
                        'createdby'=> $fullname, 
                    ]
                );
            },

            // notify next approver
            function ($next, \Carbon\Carbon $now) use ($sppb, $docUrl) {
                app(\App\Http\Controllers\ApprovalController::class)->notifyFirstApprover(
                    $sppb->sppbid,
                    'PB',
                    'P',
                    'SPPB',
                    $docUrl,
                    [
                        'info'      => $sppb->keperluan,
                        'createdby' => $sppb->created_by,
                        'date'      => $now->toDateTimeString(),
                    ]
                );

                // jejak terakhir diproses (optional)
                $sppb->completed_by = auth()->user()->username;
                $sppb->completed_at = $now;
                $sppb->save();
            }
        );

        if (!$result['ok']) {
            return response()->json(['success'=>false,'message'=>$result['message'] ?? 'Approve failed'], 403);
        }

        return response()->json(['success'=>true,'message'=>'Task approved successfully']);
    }

    public function rejectSppb(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'PB';

        $sppb = \App\Models\TrSPPB::with('creator')->where('sppbid', $docid)->first();
        if (!$sppb) return response()->json(['success'=>false,'message'=>'SPPB not found'],404);

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($sppb->id);
        $docUrl   = url('/showsppbs/' . $eid);
        $fullname = data_get($sppb, 'creator.name') ?: $sppb->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->rejectStep(
            $sppb->sppbid,
            $doctype,
            $user->username,
            $user->name,

            function (string $refnbr, \Carbon\Carbon $now) use ($sppb, $fullname, $docUrl) {
                $sppb->status       = 'R';
                $sppb->completed_by = auth()->user()->username;
                $sppb->completed_at = $now;
                $sppb->save();

                // optional: tandai detail R
                // \App\Models\TrSPPBdetail::where('sppbid', $sppb->sppbid)->update(['status' => 'R']);

                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $sppb->sppbid,
                    'SPPB',
                    'R',
                    $sppb->created_by,
                    $docUrl,
                    [
                        'cpnyid'   => $sppb->cpny_id ?? $sppb->cpnyid ?? '',
                        'deptname' => $sppb->department_id ?? $sppb->departementid ?? '',
                        'date'     => $now->toDateString(),
                        'info'     => $sppb->keperluan,
                        'fullname' => $fullname,
                        'name'     => $fullname,
                        'createdby'=> $fullname, 
                    ]
                );

                // simpan komentar (jika ada)
                try {
                    app('App\Http\Controllers\SendCommentController')->sendmsg($sppb->id, 'PB', request());
                } catch (\Throwable $e) {}
            }
        );

        if (!$result['ok']) {
            return response()->json(['success'=>false,'message'=>$result['message'] ?? 'Reject failed'], 403);
        }

        return response()->json(['success'=>true,'message'=>'SPPB rejected successfully']);
    }

    public function reviseSppb(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'PB';

        $sppb = \App\Models\TrSPPB::with('creator')->where('sppbid', $docid)->first();
        if (!$sppb) return response()->json(['success'=>false,'message'=>'SPPB not found'],404);

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($sppb->id);
        $docUrl   = url('/showsppbs/' . $eid);
        $fullname = data_get($sppb, 'creator.name') ?: $sppb->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->reviseStep(
            $sppb->sppbid,            // refnbr
            $doctype,                 // PT
            $user->username,          // actor
            $user->name,              // actor
            function (string $refnbr, \Carbon\Carbon $now) use ($sppb, $fullname, $docUrl) {
                // === HEADER SPPB -> D ===
                $sppb->status       = 'D';
                $sppb->completed_by = auth()->user()->username;
                $sppb->completed_at = $now;
                $sppb->save();

                // (opsional) DETAIL -> D
                // \App\Models\TrSPPBdetail::where('sppbid', $sppb->sppbid)->update(['status' => 'D']);

                // === Email ke requester ===
                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $sppb->sppbid,
                    'SPPB',
                    'D',
                    $sppb->created_by,
                    $docUrl,
                    [
                        'cpnyid'   => $sppb->cpny_id ?? $sppb->cpnyid ?? '',
                        'deptname' => $sppb->department_id ?? $sppb->departementid ?? '',
                        'date'     => $now->toDateString(),
                        'info'     => $sppb->keperluan,
                        'fullname' => $fullname,
                        'name'     => $fullname,
                        'createdby'=> $fullname,   // <<< tambahkan ini
                    ]
                );


                // === Simpan komentar (jika ada) ===
                try {
                    app('App\Http\Controllers\SendCommentController')->sendmsg($sppb->id, 'PB', request());
                } catch (\Throwable $e) {}
            }
        );

        if (!$result['ok']) {
            return response()->json([
                'success'=>false,
                'message'=>$result['message'] ?? 'Revise failed'
            ], 403);
        }

        return response()->json(['success'=>true,'message'=>'SPPB revised successfully']);
    }

    // public function approveSppb(Request $request, $docid)
    // {
    //     $now  = Carbon::now();
    //     $user = $request->user();
    //     $doctype = 'PB';

    //     // Ambil header + creator
    //     $sppb = TrSPPB::with('creator')->where('sppbid', $docid)->first();
    //     if (!$sppb) {
    //         return response()->json(['success' => false, 'message' => 'SPPB not found'], 404);
    //     }
    //     $fullname = data_get($sppb, 'creator.name') ?: $sppb->created_by;

    //     // Cari row approval PENDING level terendah yang sudah "aktif" (aprv_datebefore != null)
    //     // Lalu pastikan user saat ini termasuk dalam daftar aprv_username (support ; atau ,)
    //     $currentPending = TrApproval::query()
    //         ->where('refnbr', $sppb->sppbid)
    //         ->where('aprv_doctype', $doctype)
    //         ->where('status', 'P')
    //         ->whereNotNull('aprv_datebefore')
    //         ->orderByRaw("CAST(aprv_leveling AS numeric) ASC")
    //         ->first();

    //     if (!$currentPending) {
    //         return response()->json(['success' => false, 'message' => "No active approval step."], 403);
    //     }

    //     // Apakah user berhak approve di step ini?
    //     $list = preg_split('/[;,]/', (string)$currentPending->aprv_username);
    //     $list = array_filter(array_map('trim', (array)$list));
    //     $canApprove = in_array(strtolower($user->username), array_map('strtolower', $list), true);

    //     if (!$canApprove) {
    //         return response()->json(['success' => false, 'message' => "You can't approve!"], 403);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         // 1) Set current approver -> Approved
    //         $currentPending->status        = 'A';
    //         $currentPending->aprv_dateafter= $now;
    //         // opsional: cap keberadaan approver aktual
    //         $currentPending->aprv_username = $user->username;
    //         $currentPending->aprv_name     = $user->name;
    //         $currentPending->save();

    //         // Update header informasi "terakhir diproses"
    //         $sppb->completed_by = $user->username;
    //         $sppb->completed_at = $now;
    //         $sppb->save();

    //         // 2) Masih ada pending lain?
    //         $pendingCount = TrApproval::query()
    //             ->where('refnbr', $sppb->sppbid)
    //             ->where('aprv_doctype', $doctype)
    //             ->where('status', 'P')
    //             ->count();

    //         $eid = Hashids::encode($sppb->id);
    //         $subjectMap = [
    //             'P' => 'Waiting Approval',
    //             'R' => 'Rejected Approval',
    //             'D' => 'Revise Approval',
    //             'A' => 'Approved',
    //             'C' => 'Completed',
    //         ];

    //         if ($pendingCount === 0) {
    //             // 3) Tidak ada approver lagi -> dokumen complete
    //             $sppb->status       = 'C';
    //             $sppb->completed_by = $user->username;
    //             $sppb->completed_at = $now;
    //             $sppb->save();

    //             // Close semua detail
    //             TrSPPBdetail::where('sppbid', $sppb->sppbid)->update(['status' => 'C']);

    //             // Kirim email ke requester (creator)
    //             $status        = 'C';
    //             $subjectSuffix = $subjectMap[$status] ?? 'Notification';

    //             $data = [
    //                 'docid'     => $sppb->sppbid,
    //                 'cpnyid'    => $sppb->cpny_id ?? $sppb->cpnyid ?? '',
    //                 'deptname'  => $sppb->department_id ?? $sppb->departementid ?? '',
    //                 'date'      => $sppb->sppbdate,
    //                 'fullname'  => $fullname,
    //                 'name'      => $fullname,
    //                 'createdby' => $fullname,
    //                 'docname'   => 'SPPB',
    //                 'info'      => $sppb->keperluan,
    //                 'status'    => $status,
    //                 'url'       => url('/showsppbs/' . $eid),
    //             ];

    //             $recipients = User::where('username', $sppb->created_by)
    //                 ->where('status', 'A')
    //                 ->get();

    //             foreach ($recipients as $rcp) {
    //                 try {
    //                     $to = $rcp->notification_email ?? $rcp->email;
    //                     Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
    //                         $message->to($to)
    //                             ->subject($data['docid'] . ' - ' . $subjectSuffix . ' SPPB')
    //                             ->from('digitalserver@pakuwon.com', 'Pakuwon System');
    //                     });
    //                 } catch (\Throwable $e) {
    //                     Log::error('Failed sending SPPB completion email', ['error' => $e->getMessage()]);
    //                 }
    //             }

    //         } else {
    //             // 4) Masih ada approver berikutnya -> aktifkan step berikutnya (level terendah)
    //             $next = TrApproval::query()
    //                 ->where('refnbr', $sppb->sppbid)
    //                 ->where('aprv_doctype', $doctype)
    //                 ->where('status', 'P')
    //                 ->orderByRaw("CAST(aprv_leveling AS numeric) ASC")
    //                 ->first();

    //             if ($next) {
    //                 // Stempel "datebefore" untuk approver berikutnya
    //                 if (empty($next->aprv_datebefore)) {
    //                     $next->aprv_datebefore = $now;
    //                     $next->save();
    //                 }

    //                 // Kirim email ke approver level berikutnya via ApprovalController (reusable)
    //                 app(ApprovalController::class)->notifyFirstApprover(
    //                     $sppb->sppbid,
    //                     $doctype,
    //                     'P',
    //                     'SPPB',
    //                     url('/showsppbs/' . $eid),
    //                     [
    //                         'info'      => $sppb->keperluan,
    //                         'createdby' => $sppb->created_by,
    //                         'date'      => $now->toDateTimeString(),
    //                     ]
    //                 );
    //             }
    //         }

    //         DB::commit();
    //         return response()->json(['success' => true, 'message' => 'Task approved successfully']);

    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         Log::error('Approve SPPB failed', ['error' => $e->getMessage()]);
    //         return response()->json(['success' => false, 'message' => 'Approve failed'], 500);
    //     }
    // }
    
    // public function rejectSppb(Request $request, $docid)
    // {
    //     $now     = Carbon::now();
    //     $user    = $request->user();
    //     $doctype = 'PB';

    //     // Header + creator
    //     $sppb = TrSPPB::with('creator')->where('sppbid', $docid)->first();
    //     if (!$sppb) {
    //         return response()->json(['success' => false, 'message' => 'Task not found'], 404);
    //     }
    //     $fullname = data_get($sppb, 'creator.name') ?: $sppb->created_by;

    //     // Row approval aktif (pending + sudah "dibuka" datebefore)
    //     $currentPending = TrApproval::query()
    //         ->where('refnbr', $sppb->sppbid)
    //         ->where('aprv_doctype', $doctype)
    //         ->where('status', 'P')
    //         ->whereNotNull('aprv_datebefore')
    //         ->orderByRaw("CAST(aprv_leveling AS numeric) ASC")
    //         ->first();

    //     if (!$currentPending) {
    //         return response()->json(['success' => false, 'message' => "No active approval step."], 403);
    //     }

    //     // Cek apakah user termasuk approver di step ini
    //     $list = preg_split('/[;,]/', (string)$currentPending->aprv_username);
    //     $list = array_filter(array_map('trim', (array)$list));
    //     $canReject = in_array(strtolower($user->username), array_map('strtolower', $list), true);

    //     if (!$canReject) {
    //         return response()->json(['success' => false, 'message' => "You can't reject!"], 403);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         // 1) Tandai approval saat ini sebagai Rejected
    //         $currentPending->status         = 'R';
    //         $currentPending->aprv_dateafter = $now;
    //         // catat siapa yang mengeksekusi
    //         $currentPending->aprv_username  = $user->username;
    //         $currentPending->aprv_name      = $user->name;
    //         $currentPending->save();

    //         // 2) Update header SPPB -> Rejected
    //         $sppb->status       = 'R';
    //         $sppb->completed_by = $user->username;
    //         $sppb->completed_at = $now;
    //         $sppb->save();

    //         // 3) Batalkan semua approval yang masih pending (status 'X')
    //         TrApproval::query()
    //             ->where('refnbr', $sppb->sppbid)
    //             ->where('aprv_doctype', $doctype)
    //             ->where('status', 'P')
    //             ->update(['status' => 'X']);

    //         DB::commit();
    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         Log::error('Reject SPPB failed', ['docid' => $docid, 'error' => $e->getMessage()]);
    //         return response()->json(['success' => false, 'message' => 'Reject failed'], 500);
    //     }

    //     // 4) Kirim Email ke requester (creator) -> Rejected
    //     try {
    //         $status       = 'R';
    //         $subjectMap   = [
    //             'P' => 'Waiting Approval',
    //             'R' => 'Rejected Approval',
    //             'D' => 'Revise Approval',
    //             'A' => 'Approved',
    //             'C' => 'Completed',
    //         ];
    //         $subjectSuffix = $subjectMap[$status] ?? 'Notification';
    //         $eid           = Hashids::encode($sppb->id);

    //         $data = [
    //             'docid'     => $sppb->sppbid,
    //             'cpnyid'    => $sppb->cpny_id ?? $sppb->cpnyid ?? '',
    //             'deptname'  => $sppb->department_id ?? $sppb->departementid ?? '',
    //             'date'      => $now->toDateString(),
    //             'fullname'  => $fullname,
    //             'name'      => $fullname,
    //             'createdby' => $fullname,
    //             'docname'   => 'SPPB',
    //             'info'      => $sppb->keperluan,
    //             'status'    => $status,
    //             'url'       => url('/showsppbs/' . $eid),
    //         ];

    //         $recipients = User::where('username', $sppb->created_by)
    //             ->where('status', 'A')
    //             ->get();

    //         foreach ($recipients as $rcp) {
    //             $to = $rcp->notification_email ?? $rcp->email;
    //             if (!$to) continue;

    //             Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
    //                 $message->to($to)
    //                     ->subject($data['docid'] . ' - ' . $subjectSuffix . ' SPPB')
    //                     ->from('digitalserver@pakuwon.com', 'Pakuwon System');
    //             });
    //         }
    //     } catch (\Throwable $e) {
    //         Log::error('Failed sending SPPB rejected email', [
    //             'docid' => $sppb->sppbid,
    //             'error' => $e->getMessage()
    //         ]);
    //     }

    //     // 5) Simpan komentar penolakan (jika ada)
    //     try {
    //         app('App\Http\Controllers\SendCommentController')->sendmsg($sppb->id, $doctype, $request);
    //     } catch (\Throwable $e) {
    //         Log::warning('SendComment after reject failed', [
    //             'docid' => $sppb->sppbid,
    //             'error' => $e->getMessage()
    //         ]);
    //     }

    //     return response()->json(['success' => true, 'message' => 'SPPB rejected successfully']);
    // }

    // public function reviseSppb(Request $request, $docid)
    // {
    //     $now     = Carbon::now();
    //     $user    = $request->user();
    //     $doctype = 'PB';

    //     // 1) Ambil header + creator
    //     $sppb = TrSPPB::with('creator')->where('sppbid', $docid)->first();
    //     if (!$sppb) {
    //         return response()->json(['success' => false, 'message' => 'SPPB not found'], 404);
    //     }
    //     $fullname = data_get($sppb, 'creator.name') ?: $sppb->created_by;

    //     // 2) Validasi: user harus approver aktif (status P) pada step terendah yang sudah "dibuka" (aprv_datebefore != null)
    //     $currentPending = TrApproval::query()
    //         ->where('refnbr', $sppb->sppbid)
    //         ->where('aprv_doctype', $doctype)
    //         ->where('status', 'P')
    //         ->whereNotNull('aprv_datebefore')
    //         ->orderByRaw("CAST(aprv_leveling AS numeric) ASC")
    //         ->first();

    //     if (!$currentPending) {
    //         return response()->json(['success' => false, 'message' => "No active approval step."], 403);
    //     }

    //     // 3) Cek user termasuk approver di step ini (mendukung ; atau ,)
    //     $list = preg_split('/[;,]/', (string)$currentPending->aprv_username);
    //     $list = array_filter(array_map('trim', (array)$list));
    //     $canRevise = in_array(strtolower($user->username), array_map('strtolower', $list), true);

    //     if (!$canRevise) {
    //         return response()->json(['success' => false, 'message' => "You can't revise!"], 403);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         // 4) Tandai approval saat ini sebagai Revise (D)
    //         $currentPending->status         = 'D';
    //         $currentPending->aprv_dateafter = $now;
    //         // catat eksekutor aktual
    //         $currentPending->aprv_username  = $user->username;
    //         $currentPending->aprv_name      = $user->name;
    //         $currentPending->save();

    //         // 5) Update header SPPB -> D (Revise)
    //         $sppb->status       = 'D';
    //         $sppb->completed_by = $user->username;
    //         $sppb->completed_at = $now;
    //         $sppb->save();

    //         // (opsional) tandai detail sebagai D juga kalau mau:
    //         // TrSPPBdetail::where('sppbid', $sppb->sppbid)->update(['status' => 'D']);

    //         // 6) Batalkan semua approval lain yang masih pending (status 'X')
    //         TrApproval::query()
    //             ->where('refnbr', $sppb->sppbid)
    //             ->where('aprv_doctype', $doctype)
    //             ->where('status', 'P')
    //             ->update(['status' => 'X']);

    //         DB::commit();
    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         Log::error('Revise SPPB failed', ['docid' => $docid, 'error' => $e->getMessage()]);
    //         return response()->json(['success' => false, 'message' => 'Revise failed'], 500);
    //     }

    //     // 7) Kirim email ke requester (creator) -> Revise
    //     try {
    //         $status        = 'D';
    //         $subjectMap    = ['P'=>'Waiting Approval','R'=>'Rejected Approval','D'=>'Revise Approval','A'=>'Approved','C'=>'Completed'];
    //         $subjectSuffix = $subjectMap[$status] ?? 'Notification';
    //         $eid           = Hashids::encode($sppb->id);

    //         $data = [
    //             'docid'     => $sppb->sppbid,
    //             'cpnyid'    => $sppb->cpny_id ?? $sppb->cpnyid ?? '',
    //             'deptname'  => $sppb->department_id ?? $sppb->departementid ?? '',
    //             'date'      => $now->toDateString(), // atau pakai $currentPending->aprv_dateafter
    //             'fullname'  => $fullname,
    //             'name'      => $fullname,
    //             'createdby' => $fullname,
    //             'docname'   => 'SPPB',
    //             'info'      => $sppb->keperluan,
    //             'status'    => $status,
    //             'url'       => url('/showsppbs/' . $eid),
    //         ];

    //         $recipients = User::where('username', $sppb->created_by)
    //             ->where('status', 'A')
    //             ->get();

    //         foreach ($recipients as $rcp) {
    //             $to = $rcp->notification_email ?? $rcp->email;
    //             if (!$to) continue;

    //             Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
    //                 $message->to($to)
    //                     ->subject($data['docid'] . ' - ' . $subjectSuffix . ' SPPB')
    //                     ->from('digitalserver@pakuwon.com', 'Pakuwon System');
    //             });
    //         }
    //     } catch (\Throwable $e) {
    //         Log::error('Failed sending SPPB revise email', [
    //             'docid' => $sppb->sppbid,
    //             'error' => $e->getMessage()
    //         ]);
    //     }

    //     // 8) Simpan komentar revisi (jika ada)
    //     try {
    //         app('App\Http\Controllers\SendCommentController')->sendmsg($sppb->id, $doctype, $request);
    //     } catch (\Throwable $e) {
    //         Log::warning('SendComment after revise failed', [
    //             'docid' => $sppb->sppbid,
    //             'error' => $e->getMessage()
    //         ]);
    //     }

    //     return response()->json(['success' => true, 'message' => 'SPPB revised successfully']);
    // }
    

    public function trackingDetail($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $sppb = TrSPPB::findOrFail($id);
        $sppbNo = $sppb->sppbid;

        $fmt = fn($dt) => $dt ? Carbon::parse($dt)->format('Y-m-d H:i') : null;
        $approved = fn($h) => $h ? (!empty($h->completed_by) || !empty($h->completed_at)) : false;

        // ===== SPPB =====
        $sppbDetails = TrSPPBdetail::query()
            ->where('sppbid', $sppbNo)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get();

        // ===== LIST CS (ALL) =====
        $csList = TrCS::query()
            ->where('sppbjktid', $sppbNo)
            ->whereNull('deleted_at')
            ->orderBy('csdate', 'desc')
            ->get(['csid','csdate','status','completed_by','completed_at']);

        // default selected CS: yang terbaru
        $selCsNo = optional($csList->first())->csid;

        // ===== LIST PO (ALL) =====
        // Jika kamu mau PO mengikuti CS terpilih, nanti kita filter dengan csid
        $poList = TrPO::query()
            ->where('sppbjktid', $sppbNo)
            ->where('cpny_id', $sppb->cpny_id)
            ->whereNull('deleted_at')
            ->orderBy('podate', 'desc')
            ->get(['ponbr','podate','status','csid','completed_by','completed_at']);

        $selPoNo = optional($poList->first())->ponbr;

        // ===== LIST RECEIPT (ALL) =====
        // Default: receipt paling baru untuk SPPB ini
        $receiptList = TrReceipt::query()
            ->where('sppbjktid', $sppbNo)
            ->whereNull('deleted_at')
            ->orderBy('receiptdate','desc')
            ->get(['receiptnbr','receiptdate','status','ponbr','csid','completed_by','completed_at']);

        // default selected receipt: kalau ada PO terpilih, ambil receipt terbaru by ponbr itu
        $selReceiptNo = optional(
            $receiptList->firstWhere('ponbr', $selPoNo) ?? $receiptList->first()
        )->receiptnbr;

        // ===== DETAIL DEFAULT SELECTED (CS/PO/RECEIPT) =====
        $csHeader = $selCsNo ? TrCS::where('csid',$selCsNo)->whereNull('deleted_at')->first() : null;
        // $csDetails = $selCsNo ? TrCSdetail::where('csid',$selCsNo)->whereNull('deleted_at')->orderBy('id')->get() : collect();
        $csDetails = collect();
        if ($selCsNo) {
            $csHeader = TrCS::where('csid', $selCsNo)->whereNull('deleted_at')->first();

            $isTrue = function ($v) {
                // handle boolean / int / string 't'/'f'
                if (is_bool($v)) return $v;
                $v = strtolower((string)$v);
                return in_array($v, ['1','true','t','yes','y'], true);
            };

            $csDetails = TrCSdetail::query()
                ->where('csid', $selCsNo)
                ->whereNull('deleted_at')
                ->orderBy('id')
                ->get()
                // ✅ hanya tampilkan yang vendor selected = true
                ->filter(function($d) use ($isTrue){
                    return $isTrue($d->vendor1selected)
                        || $isTrue($d->vendor2selected)
                        || $isTrue($d->vendor3selected)
                        || $isTrue($d->vendor4selected)
                        || $isTrue($d->vendor5selected)
                        || $isTrue($d->vendor6selected);
                })
                // ✅ map jadi array supaya field tambahan pasti ikut ke JSON
                ->map(function($d) use ($csHeader, $isTrue){

                    $vendorName  = null;
                    $vendorPrice = null;

                    if ($csHeader) {
                        if ($isTrue($d->vendor1selected)) { $vendorName = $csHeader->vendorname1; $vendorPrice = $d->vendorprice1; }
                        elseif ($isTrue($d->vendor2selected)) { $vendorName = $csHeader->vendorname2; $vendorPrice = $d->vendorprice2; }
                        elseif ($isTrue($d->vendor3selected)) { $vendorName = $csHeader->vendorname3; $vendorPrice = $d->vendorprice3; }
                        elseif ($isTrue($d->vendor4selected)) { $vendorName = $csHeader->vendorname4; $vendorPrice = $d->vendorprice4; }
                        elseif ($isTrue($d->vendor5selected)) { $vendorName = $csHeader->vendorname5; $vendorPrice = $d->vendorprice5; }
                        elseif ($isTrue($d->vendor6selected)) { $vendorName = $csHeader->vendorname6; $vendorPrice = $d->vendorprice6; }
                    }

                    return [
                        'id' => $d->id,
                        'inventoryid' => $d->inventoryid,
                        'inventory_descr' => $d->inventory_descr,
                        'qty' => $d->qty,
                        'uom' => $d->uom,

                        // ✅ ini yang kamu butuhin
                        'vendorname_selected' => $vendorName,
                        'vendorprice_selected' => $vendorPrice,

                        'status' => $d->status,
                    ];
                })
                ->values();
        }


        $poHeader = $selPoNo ? TrPO::where('ponbr',$selPoNo)
            ->where('cpny_id', $sppb->cpny_id)
            ->whereNull('deleted_at')
            ->first() : null;
        $poDetails = $selPoNo ? TrPOdetail::where('ponbr',$selPoNo)
            ->where('budget_cpny_id', $sppb->cpny_id)
            ->whereNull('deleted_at')->orderBy('id')->get() : collect();

        $receiptHeader = $selReceiptNo ? TrReceipt::where('receiptnbr',$selReceiptNo)->whereNull('deleted_at')->first() : null;
        $receiptDetails = $selReceiptNo ? TrReceiptdetail::where('receiptnbr',$selReceiptNo)->whereNull('deleted_at')->orderBy('id')->get() : collect();

        $lastApprSppb    = $this->getLastApprovalInfo($sppbNo);
        $lastApprCs      = $selCsNo ? $this->getLastApprovalInfo($selCsNo) : null;
        $lastApprReceipt = $selReceiptNo ? $this->getLastApprovalInfo($selReceiptNo) : null;

        return response()->json([
            'doc' => $sppbNo,

            'lists' => [
                'cs' => $csList->map(fn($x)=>[
                    'doc'=>$x->csid,
                    'date'=>$fmt($x->csdate),
                    'status'=>$x->status,
                    'is_approved'=>(!empty($x->completed_by) || !empty($x->completed_at)),
                ])->values(),
                'po' => $poList->map(fn($x)=>[
                    'doc'=>$x->ponbr,
                    'date'=>$fmt($x->podate),
                    'status'=>$x->status,
                    'csid'=>$x->csid,
                    'is_approved'=>(!empty($x->completed_by) || !empty($x->completed_at)),
                ])->values(),
                'receipt' => $receiptList->map(fn($x)=>[
                    'doc'=>$x->receiptnbr,
                    'date'=>$fmt($x->receiptdate),
                    'status'=>$x->status,
                    'ponbr'=>$x->ponbr,
                    'csid'=>$x->csid,
                    'is_approved'=>(!empty($x->completed_by) || !empty($x->completed_at)),
                ])->values(),
            ],

            'selected' => [
                'cs_no' => $selCsNo,
                'po_no' => $selPoNo,
                'receipt_no' => $selReceiptNo,
            ],

            'sppb' => [
                'header' => [
                    'doc' => $sppb->sppbid,
                    'date' => $fmt($sppb->sppbdate),
                    'cpny_id' => $sppb->cpny_id,
                    'department_id' => $sppb->department_id,
                    'keperluan' => $sppb->keperluan,
                    'status' => $sppb->status,
                    'created_by' => $sppb->created_by,
                    'created_at' => $fmt($sppb->created_at),
                    'completed_by' => $sppb->completed_by,
                    'completed_at' => $fmt($sppb->completed_at),
                    'is_approved' => $approved($sppb),
                    'last_approval' => $lastApprSppb,
                ],
                'details' => $sppbDetails,
            ],

            'cs' => [
                'header' => $csHeader ? [
                    'doc'=>$csHeader->csid,
                    'date'=>$fmt($csHeader->csdate),
                    'cpny_id'=>$csHeader->cpny_id,
                    'department_id'=>$csHeader->department_id,
                    'keperluan'=>$csHeader->keperluan,
                    'status'=>$csHeader->status,
                    'completed_by'=>$csHeader->completed_by,
                    'completed_at'=>$fmt($csHeader->completed_at),
                    'is_approved'=>$approved($csHeader),
                    'last_approval' => $lastApprCs,
                ] : null,
                'details' => $csDetails,
            ],

            'po' => [
                'header' => $poHeader ? [
                    'doc'=>$poHeader->ponbr,
                    'date'=>$fmt($poHeader->podate),
                    'cpny_id'=>$poHeader->cpny_id,
                    'department_id'=>$poHeader->department_id,
                    'vendorname'=>$poHeader->vendorname,
                    'status'=>$poHeader->status,
                    'completed_by'=>$poHeader->completed_by,
                    'completed_at'=>$fmt($poHeader->completed_at),
                    'is_approved'=>$approved($poHeader),                    
                ] : null,
                'details' => $poDetails,
            ],

            'receipt' => [
                'header' => $receiptHeader ? [
                    'doc'=>$receiptHeader->receiptnbr,
                    'date'=>$fmt($receiptHeader->receiptdate),
                    'cpny_id'=>$receiptHeader->cpny_id,
                    'department_id'=>$receiptHeader->department_id,
                    'vendorname'=>$receiptHeader->vendorname,
                    'status'=>$receiptHeader->status,
                    'completed_by'=>$receiptHeader->completed_by,
                    'completed_at'=>$fmt($receiptHeader->completed_at),
                    'is_approved'=>$approved($receiptHeader),
                    'last_approval' => $lastApprReceipt,
                ] : null,
                'details' => $receiptDetails,
            ],
        ]);
    }
    
    public function trackingDetailItem($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $sppb = TrSPPB::findOrFail($id);
        $sppbNo = $sppb->sppbid;

        $type = request('type');  // cs|po|receipt
        $doc  = request('doc');   // csid / ponbr / receiptnbr

        abort_if(!in_array($type, ['cs','po','receipt'], true), 400);
        abort_if(!$doc, 400);

        $fmt = fn($dt) => $dt ? \Carbon\Carbon::parse($dt)->format('Y-m-d H:i') : null;
        $approved = fn($h) => $h ? (!empty($h->completed_by) || !empty($h->completed_at)) : false;

        // ===== helper: ambil last approval per doc =====
        $getLastApproval = function(string $refnbr) use ($fmt) {
            $q = TrApproval::query()->where('refnbr', $refnbr);

            // 1) cari yang masih pending tapi sudah mulai proses (datebefore not null)
            $pending = (clone $q)
                ->where('status', 'P')
                ->whereNotNull('aprv_datebefore')
                ->orderByDesc('aprv_leveling')
                ->orderByDesc('created_at')
                ->first();

            $row = $pending;

            // 2) kalau tidak ada, ambil yang sudah approve
            if (!$row) {
                $row = (clone $q)
                    ->where('status', 'A')
                    ->orderByDesc('aprv_leveling')
                    ->orderByDesc('created_at')
                    ->first();
            }

            if (!$row) return null;

            return [
                'refnbr'        => $row->refnbr,
                'aprv_leveling' => $row->aprv_leveling,
                'status'        => $row->status,
                'username'      => $row->aprv_username,
                'name'          => $row->aprv_name,
                'date_before'   => $fmt($row->aprv_datebefore),
                'date_after'    => $fmt($row->aprv_dateafter),
                'type'          => $row->aprv_type,
                'condition'     => $row->aprv_condition,
            ];
        };

        if ($type === 'cs') {
            $h = TrCS::where('csid', $doc)
                ->where('sppbjktid', $sppbNo)
                ->whereNull('deleted_at')
                ->first();

            $d = $h ? TrCSdetail::where('csid', $doc)
                ->whereNull('deleted_at')
                ->orderBy('id')->get() : collect();
            // $tbl = (new TrCSdetail)->getTable();

            // $d = $h ? TrCSdetail::query()
            //     ->leftJoin('ms_vendor as v', 'v.vendor_id', '=', $tbl.'.vendor_id_selected') // ⬅ kolom sesuaikan
            //     ->where($tbl.'.csid', $doc)
            //     ->whereNull($tbl.'.deleted_at')
            //     ->select([
            //         $tbl.'.*',
            //         'v.vendor_name as vendorname_selected'
            //     ])
            //     ->orderBy($tbl.'.id')
            //     ->get()
            // : collect();
            // $d = $h ? TrCSdetail::where('csid',$doc)
            //     ->whereNull('deleted_at')
            //     ->orderBy('id')
            //     ->get()
            //     ->map(function($row){
            //         $vendor = \App\Models\MsVendor::where('vendor_id', $row->vendor_id_selected)->first();
            //         $row->vendorname_selected = $vendor->vendor_name ?? null;
            //         return $row;
            //     })
            // : collect();

            return response()->json([
                'header' => $h ? [
                    'doc'          => $h->csid,
                    'date'         => $fmt($h->csdate),
                    'cpny_id'       => $h->cpny_id,
                    'department_id' => $h->department_id,
                    'keperluan'     => $h->keperluan,
                    'status'        => $h->status,
                    'completed_by'  => $h->completed_by,
                    'completed_at'  => $fmt($h->completed_at),
                    'is_approved'   => $approved($h),

                    // ✅ tambah ini
                    'last_approval' => $getLastApproval($h->csid),
                ] : null,
                'details' => $d
            ]);
        }

        if ($type === 'po') {
            $h = TrPO::where('ponbr', $doc)
                ->where('sppbjktid', $sppbNo)
                ->whereNull('deleted_at')
                ->first();

            $d = $h ? TrPOdetail::where('ponbr', $doc)
                ->where('budget_cpny_id', $h->cpny_id)
                ->whereNull('deleted_at')
                ->orderBy('id')->get() : collect();

            return response()->json([
                'header' => $h ? [
                    'doc'          => $h->ponbr,
                    'date'         => $fmt($h->podate),
                    'cpny_id'       => $h->cpny_id,
                    'department_id' => $h->department_id,
                    'vendorname'    => $h->vendorname,
                    'status'        => $h->status,
                    'completed_by'  => $h->completed_by,
                    'completed_at'  => $fmt($h->completed_at),
                    'is_approved'   => $approved($h),

                    // ✅ tambah ini
                    'last_approval' => $getLastApproval($h->ponbr),
                ] : null,
                'details' => $d
            ]);
        }

        // receipt
        $h = TrReceipt::where('receiptnbr', $doc)
            ->where('sppbjktid', $sppbNo)
            ->whereNull('deleted_at')
            ->first();

        $d = $h ? TrReceiptdetail::where('receiptnbr', $doc)
            ->whereNull('deleted_at')
            ->orderBy('id')->get() : collect();

        return response()->json([
            'header' => $h ? [
                'doc'          => $h->receiptnbr,
                'date'         => $fmt($h->receiptdate),
                'cpny_id'       => $h->cpny_id,
                'department_id' => $h->department_id,
                'vendorname'    => $h->vendorname,
                'status'        => $h->status,
                'completed_by'  => $h->completed_by,
                'completed_at'  => $fmt($h->completed_at),
                'is_approved'   => $approved($h),

                // ✅ tambah ini
                'last_approval' => $getLastApproval($h->receiptnbr),
            ] : null,
            'details' => $d
        ]);
    }

    private function getLastApprovalInfo(string $refnbr): ?array
    {
        $refnbr = trim((string)$refnbr);
        if ($refnbr === '') return null;

        // 1) PRIORITY: status P & aprv_datebefore not null
        $row = TrApproval::query()
            ->where('refnbr', $refnbr)
            ->where('status', 'P')
            ->whereNotNull('aprv_datebefore')
            ->orderByDesc('aprv_leveling')
            ->orderByDesc('id')
            ->first();

        // 2) FALLBACK: status A (approved)
        if (!$row) {
            $row = TrApproval::query()
                ->where('refnbr', $refnbr)
                ->where('status', 'A')
                ->orderByDesc('aprv_leveling')
                ->orderByDesc('id')
                ->first();
        }

        if (!$row) return null;

        // Note: field "created_by" kamu ada di fillable, tapi juga ada aprv_username & aprv_name
        return [
            'status'        => $row->status,                 // P / A
            'aprv_leveling' => $row->aprv_leveling,
            'username'      => $row->aprv_username ?? $row->created_by,
            'name'          => $row->aprv_name,
            'date_before'   => $row->aprv_datebefore,
            'date_after'    => $row->aprv_dateafter,
            'doctype'       => $row->aprv_doctype,
            'condition'     => $row->aprv_condition,
        ];
    }


    public function tracking_xxx($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $sppb = TrSPPB::findOrFail($id);

        // Ambil 1 row terbaru dari view untuk sppb ini
        // Kalau view kamu menghasilkan banyak baris, ambil yang paling "akhir"
        $row = VTrackingSppbFlow::query()
            ->where('sppb_no', $sppb->sppbid)
            ->orderByRaw('cs_date DESC NULLS LAST')
            ->orderByRaw('po_date DESC NULLS LAST')
            ->orderByRaw('receipt_date DESC NULLS LAST')
            ->first();

        // Kalau tidak ketemu di view (harusnya minimal ada SPPB)
        if (!$row) {
            return response()->json([
                'doc' => $sppb->sppbid,
                'steps' => [[
                    'key' => 'sppb',
                    'title' => 'SPPB',
                    'doc' => $sppb->sppbid,
                    'status' => 'C',
                    'status_label' => 'Submitted',
                    'by' => $sppb->created_by,
                    'at' => optional($sppb->created_at)->format('Y-m-d H:i'),
                ]],
            ]);
        }

        $fmt = fn($dt) => $dt ? Carbon::parse($dt)->format('Y-m-d H:i') : null;

        $stepStatus = function (?string $docNo, $isApproved) {
            // belum dibuat
            if (!$docNo) return ['_', 'Not created yet'];
            // sudah dibuat tapi belum complete
            if (!$isApproved) return ['P', 'In progress / waiting approval'];
            // approved/complete
            return ['C', 'Approved / completed'];
        };

        // ====== BUILD STEPS ======
        // SPPB selalu ada
        [$sppbSt, $sppbLbl] = $stepStatus($row->sppb_no, $row->sppb_is_approved ?? false);
        // Tapi SPPB "Submitted" lebih jelas sebagai baseline
        $steps = [[
            'key' => 'sppb',
            'title' => 'SPPB',
            'doc' => $row->sppb_no,
            'status' => 'C',
            'status_label' => 'Submitted',
            'by' => $row->sppb_created_by ?? null,
            'at' => $fmt($row->sppb_created_at ?? null),
        ]];

        // CS
        [$csSt, $csLbl] = $stepStatus($row->cs_no ?? null, $row->cs_is_approved ?? false);
        $steps[] = [
            'key' => 'cs',
            'title' => 'CS',
            'doc' => $row->cs_no ?? null,
            'status' => $csSt,
            'status_label' => $csLbl,
            'by' => ($row->cs_is_approved ?? false) ? ($row->cs_completed_by ?? null) : null,
            'at' => ($row->cs_is_approved ?? false) ? $fmt($row->cs_completed_at ?? null) : null,
        ];

        // PO
        [$poSt, $poLbl] = $stepStatus($row->po_no ?? null, $row->po_is_approved ?? false);
        $steps[] = [
            'key' => 'po',
            'title' => 'PO',
            'doc' => $row->po_no ?? null,
            'status' => $poSt,
            'status_label' => $poLbl,
            'by' => ($row->po_is_approved ?? false) ? ($row->po_completed_by ?? null) : null,
            'at' => ($row->po_is_approved ?? false) ? $fmt($row->po_completed_at ?? null) : null,
        ];

        // Receipt
        [$rcSt, $rcLbl] = $stepStatus($row->receipt_no ?? null, $row->receipt_is_approved ?? false);
        $steps[] = [
            'key' => 'receipt',
            'title' => 'Receipt',
            'doc' => $row->receipt_no ?? null,
            'status' => $rcSt,
            'status_label' => $rcLbl,
            'by' => ($row->receipt_is_approved ?? false) ? ($row->receipt_completed_by ?? null) : null,
            'at' => ($row->receipt_is_approved ?? false) ? $fmt($row->receipt_completed_at ?? null) : null,
        ];

        return response()->json([
            'doc' => $row->sppb_no,
            'steps' => $steps,
        ]);
    }

    public function printSppb($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $authUser = Auth::user();
        if (!$authUser) {
            return redirect()->route('login');
        }

        // Ambil SPPB + relasi yang dibutuhkan
        $sppb = TrSPPB::with([
                'requestType:requesttypeid,requesttype_name',
                'creator:username,name',
            ])
            ->findOrFail($id);

        // Detail baris SPPB
        $sppbdetail = TrSPPBdetail::with([
                'location:location_id,location_name',
                'subLocation:sub_location_id,sub_location_name',
            ])
            ->where('sppbid', $sppb->sppbid)
            ->get();

        $refnbr    = $sppb->sppbid;
        $apprTable = (new TrApproval)->getTable(); // "tr_approval"

        $approval = TrApproval::query()
            ->where('refnbr', $refnbr)
            ->where('status', '<>', 'X')
            ->where('created_at', function ($q) use ($refnbr, $apprTable) {
                $q->selectRaw('MAX(created_at)')
                ->from($apprTable)
                ->where('refnbr', $refnbr)
                ->where('status', '<>', 'X');
            })
            ->orderByRaw("
                CASE
                    WHEN trim(coalesce(aprv_leveling::text, '')) ~ '^[0-9]+(\\.[0-9]+)?$'
                    THEN trim(aprv_leveling::text)::numeric
                    ELSE NULL
                END ASC
            ")
            ->orderBy('id', 'asc')
            ->get();


        $approve_count = $approval->count();

        // Company (handle null)
        $company = MsCompany::where('cpny_id', $sppb->cpny_id)->first();

        // Mapping status dokumen
        switch ($sppb->status) {
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
            'doc_type'            => 'SPPB',
            'docid'               => $sppb->sppbid,
            'department_id'       => $sppb->department_id,
            'cpnyname'            => optional($company)->cpny_name,
            'parent'              => optional($company)->parent,
            'project'             => optional($company)->project,
            // identitas & tanggal
            'created_by_username' => $sppb->created_by,
            'created_by_name'     => ucwords(strtolower(optional($sppb->creator)->name)),
            'created_at_fmt'      => optional($sppb->created_at)->format('d F Y'),
            'req_date_fmt'        => optional($sppb->created_at)->format('d M Y H:i'),
            'sppbdate'            => \Carbon\Carbon::parse($sppb->sppbdate)->format('d F Y'),
            // konten
            'keperluan'           => $sppb->keperluan,
            'status_doc'          => $status_doc,
            'requesttype_name'    => optional($sppb->requestType)->requesttype_name,
        ];

        // Kirim ke view
        $pdf = \PDF::loadView(
            'pages.sppbs.pdf_sppbs',
            array_merge($data, [
                'detail'         => $sppbdetail,
                'approval'       => $approval,
                'approve_count'  => $approve_count,
            ])
        );

        // Portrait jika <= 5 approver, else landscape
        $pdf->setPaper('A4', ($approve_count <= 5) ? 'portrait' : 'landscape');

        return $pdf->stream("pdf_sppbs_{$sppb->sppbid}.pdf");
    }

    public function cancelSppb(Request $request, string $hash)
    {
        
        // decode hash -> id (sesuaikan kalau tidak pakai Hashids)
        $decoded = Hashids::decode($hash);
        abort_if(empty($decoded), 404, 'Invalid document');

        $id = $decoded[0];
        
        // ambil doc
        $sppb = TrSPPB::query()->where('id', $id)->firstOrFail();        

        DB::beginTransaction();
        try {
            // update status header jadi X (Canceled)
            $sppb->status = 'X';
            $sppb->updated_by = Auth::user()->username ?? Auth::id(); // kalau kolom ada
            $sppb->updated_at = now(); // kalau kolom ada
            $sppb->save();          

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Document canceled (status X).',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel document.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }




    






}
