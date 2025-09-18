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
use App\Models\TrSPPB;
use App\Models\TrSPPBdetail;
use App\Models\MsLocationPG;
use App\Models\MsSubLocationPG;
use Mail;
use Illuminate\Support\Facades\Log;
use PDF;

class SppbController extends Controller
{
    public function index()
    {
        $all = TrSPPB::count();
        $onProgress = TrSPPB::where('status', 'P')->count();
        $reject = TrSPPB::where('status', 'R')->count();
        $revise = TrSPPB::where('status', 'D')->count();
        $completed = TrSPPB::where('status', 'C')->count();
       
        return view('pages.sppbs.sppbs', compact('all', 'onProgress', 'reject', 'revise', 'completed'));
    }

    public function json(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $search = trim((string) $request->input('search.value', ''));
        $status = (string) $request->query('status', ''); // '' = all

        $baseTable = (new TrSPPB)->getTable(); // e.g. "tr_sppb"

        $columns = [
            0 => 'sppb.sppbid',
            1 => 'sppb.sppbdate',
            2 => 'sppb.cpny_id',
            3 => 'sppb.department_id',
            4 => 'rt.requesttype_name',
            5 => 'sppb.keperluan',
            6 => 'sppb.status',
        ];

        // ⬇️ default ke kolom 0 (sppb.sppbid) dan desc
        $orderIdx = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'sppb.sppbid';

        $base = TrSPPB::from($baseTable.' as sppb')
            ->leftJoin('ms_request_type as rt', function ($join) {
                $join->on('rt.requesttypeid', '=', 'sppb.requesttypeid');
            });

        if ($status !== '') {
            $base->where('sppb.status', $status);
        }

        $recordsTotal = (clone $base)->distinct('sppb.sppbid')->count('sppb.sppbid');

        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('sppb.sppbid',          'like', "%{$search}%")
                ->orWhere('sppb.cpny_id',       'like', "%{$search}%")
                ->orWhere('sppb.department_id', 'like', "%{$search}%")
                ->orWhere('rt.requesttype_name','like', "%{$search}%")
                ->orWhere('sppb.keperluan',     'like', "%{$search}%")
                ->orWhere('sppb.status',        'like', "%{$search}%");
            });
        }

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
                ->orderBy($orderCol, $orderDir)                  // ← mengikuti request, default ke sppbid desc
                ->orderBy('sppb.sppbid', 'desc')                 // ← tie-breaker agar stabil
                ->skip($start)
                ->take($length)
                ->get();

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }


    
    public function createSppb()
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
       
        return view('pages.sppbs.createsppbs', compact('usercpny','usercpny2','userdept','userdept2'));
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
        $coaIds        = $request->input('coa_id', []); // account_id
        $item_types    = $request->input('item_type', []);
        $item_categories = $request->input('item_category', []);

        $purchaseUnits    = $request->input('purchase_unit', []);     // dari hidden purchase_unit[]
        $uomMultDivs      = $request->input('uom_unitmultdiv', []);   // 'M' atau 'D'
        $uomRates         = $request->input('uom_unitrate', []);      // bisa "12", "12,5", "12.000",

        $doctype  = 'PB';
        $user     = $request->user();
        $username = $user->username ?? 'system';
        $fullname = $user->name ?? 'system';

        $dt        = Carbon::now();
        $year      = $dt->year;
        $month     = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        $datestamp = $dt->toDateTimeString();


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
        $approvalCount = M_approval::where([
            ['status', '=', 'A'],
            ['aprvcpnyid', '=', $request->cpnyid],
            ['aprvdeptid', '=', $request->departementid],
            ['aprvdoctype', '=', $doctype],
        ])->count();

        if ($approvalCount === 0) {
            return response()->json([
                'message' => 'Approval line belum di-setup, Please contact IT!',
            ], 422);
        }

        DB::beginTransaction();
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
            $docid  = $doctype . $tglbln . sprintf("%03d", $urutan);
            $sppbNo = $docid;                                   // atau 'SPPB-'.$docid

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
            $header->spbid             = null;
            $header->totalopenordered  = 0;
            $header->totalqty          = 0;
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

                $detail = new TrSPPBdetail();
                $detail->sppbid                   = $docid;
                $detail->sppb_no                  = $i + 1;   // nomor urut detail
                $detail->inventoryid              = $invId;
                $detail->inventory_descr          = $productName;
                $detail->qty                      = $qty;
                $detail->uom                      = $uom;
                $detail->note                     = $notes[$i]   ?? null;
                $detail->sppb_type                = $item_types[$i] ?? null;
                $detail->sppb_category            = $item_categories[$i] ?? null;
                $detail->base_uom                 = $baseUom;            // = purchase_unit
                $detail->base_multiplier          = $rate;               // = uom_unitrate (float)
                $detail->type_multiplier          = $typeMultiplier ?: null; // = 'M' / 'D' / null
                $detail->base_qty                 = $baseQty;            // hitungan M/D               
                $detail->budget_cpny_id           = $request->cpnyid;
                $detail->budget_business_unit_id  = $busUnitIds[$i]     ?? null;
                $detail->budget_department_fin_id = $deptFinIds[$i] ?? null;
                $detail->budget_account_id        = $coaIds[$i]         ?? null;
                $detail->budget_activity_id       = $activityIds[$i]   ?? null;               
                $detail->location_id              = $locationIds[$i]  ?? null;
                $detail->sub_location_id          = $subLocIds[$i]    ?? null;
                $detail->budget_perpost           = $request->perpost;
                $detail->assignby                 = null;
                $detail->assigndate               = null;
                $detail->assignpurchasing         = null;
                $detail->openordered              = 0;
                $detail->ordered                  = 0;
                $detail->status                   = 'P';
                $detail->created_by               = $username;
                $detail->save();

                $totalQty += $qty;
            }

            // update totalqty di header
            $header->totalqty = $totalQty;
            // $header->totalopenordered = $totalQty;
            $header->save();

            // === 4) copy line approval (M_approval -> T_approval) ===
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
                    'created_by'   => $username,
                ]);
            }

            $firstApprovalUsernames = optional($approvals->first())->aprvusername; // bisa comma-separated
            if ($firstApprovalUsernames) {
                $header->completed_by = $firstApprovalUsernames;
                $header->completed_at = $dt; // atau Carbon::now()
                $header->save();
            }

            // === 5) attachments (opsional) ===
            if ($request->hasfile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $randomNumber = random_int(10000000, 99999999);
                    $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                   
                    $originalName = str_replace('%', '', $file->getClientOriginalName());
                    $attachfile = md5($randomNumber) . '-' . $originalName;

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
                    $attach->docid = $docid;
                    $attach->name = $filename;
                    $attach->attachfile = $attachfile;
                    $attach->status = 'A';
                    $attach->extention = $file->getClientOriginalExtension();
                    $attach->created_user = $user->username;
                    $attach->save();
                }
            }            

            // === 6) kirim email ke approver pertama ===
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
                
                $data = [
                    'docid'    => $firstApproval->docid,
                    'cpnyid'   => $firstApproval->aprvcpnyid,
                    'deptname' => $firstApproval->aprvdeptid,
                    'date'     => $firstApproval->aprvdatebefore,
                    'name'     => $firstApproval->name,
                    'createdby'=> $header->created_by,
                    'info'     => $request->keperluan,
                    'status'   => $status,
                    'docname'  => 'SPPB',
                    'url'      => url('/showsppbs/' . $header->id),
                ];
                
                $approvers = array_filter(array_map('trim', explode(',', (string)$firstApproval->aprvusername)));
                $emails = User::whereIn('username', $approvers)
                    ->where('status', 'A')
                    ->pluck('test_email');

                foreach ($emails as $email) {
                    \Mail::send('emails.mailapprovenew', $data, function ($message) use ($email, $data) {
                        $message->to($email)
                            ->subject($data['docid'].' - Waiting Approval SPPB')
                            ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                    });
                }
            }

            DB::commit();

            return response()->json([
                'message'  => 'SPPB created successfully',
                'sppbid'   => $docid,
                'sppb_no'  => $sppbNo,
                'totalqty' => $totalQty,
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
   
    public function editSppb($id)
    {
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

        $attachment = Attachment::where('docid', $sppb->sppbid)
            ->where('status', 'A')
            ->get();

        return view('pages.sppbs.editsppbs', compact(
            'sppb','sppbdetail','usercpny','usercpny2','userdept','userdept2','attachment'
        ));
    }



    public function updateSppb(Request $request, $id)
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

        $header = TrSPPB::findOrFail($id);
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
                    'qty'                      => $qty,
                    'uom'                      => $displayUom,
                    'note'                     => $notes[$i] ?? null,
                    'sppb_type'                => $itemTypes[$i] ?? null,
                    'sppb_category'            => $itemCats[$i] ?? null,

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
                    'openordered'              => 0,
                    'ordered'                  => 0,
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
            // $header->totalopenordered = $totalQty;
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
                    'docid'          => $header->sppbid,
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
                    $attachfile = md5($randomNumber) . '-' . $originalName;

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
                    $attach->docid = $header->sppbid;
                    $attach->name = $filename;
                    $attach->attachfile = $attachfile;
                    $attach->status = 'A';
                    $attach->extention = $file->getClientOriginalExtension();
                    $attach->created_user = $user->username;
                    $attach->save();
                }
            }       

            // email approver pertama (tetap)
            $firstApproval = T_approval::where('docid', $header->sppbid)
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
                
                $data = [
                    'docid'    => $firstApproval->docid,
                    'cpnyid'   => $firstApproval->aprvcpnyid,
                    'deptname' => $firstApproval->aprvdeptid,
                    'date'     => $firstApproval->aprvdatebefore,
                    'name'     => $firstApproval->name,
                    'createdby'=> $header->created_by,
                    'info'     => $request->keperluan,
                    'status'   => $status,
                    'docname'  => 'SPPB',
                    'url'      => url('/showsppbs/' . $header->id),
                ];

                $approvers = array_filter(array_map('trim', explode(',', (string)$firstApproval->aprvusername)));
                $emails = User::whereIn('username', $approvers)
                    ->where('status', 'A')
                    ->pluck('test_email');

                foreach ($emails as $email) {
                    \Mail::send('emails.mailapprovenew', $data, function ($message) use ($email, $data) {
                        $message->to($email)
                            ->subject($data['docid'].' - Waiting Approval SPPB')
                            ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                    });
                }
            }

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
            $attachment = Attachment::findOrFail($id);
            $attachment->update(['status' => 'X']); // Update status ke "D" (Deleted)

            return response()->json(['success' => true, 'message' => 'Attachment status updated']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update attachment status', 'error' => $e->getMessage()], 500);
        }
    }
 

    public function showSppb($id)
    {        
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
        ->get();
        
        $approval = T_approval::where('docid', $sppb->sppbid)
            ->where('status','<>','X')      
            ->orderBy('created_at')
            ->orderBy('aprvid')      
            ->get();
       
        $attachment = Attachment::where('docid', $sppb->sppbid)    
            ->where('status','A')        
            ->get();       
       
        return view('pages.sppbs.showsppbs', compact('sppb','approval','attachment','sppbdetail'));
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

    public function approveSppb(Request $request, $docid)
    {
        $now  = Carbon::now();
        $user = $request->user();

        // $sppb = TrSPPB::where('sppbid', $docid)->first();
        $sppb = TrSPPB::with('creator')
            ->where('sppbid', $docid)
            ->first();
        $fullname = data_get($sppb, 'creator.name') ?: $sppb->created_by;

        if (!$sppb) {
            return response()->json(['success' => false, 'message' => 'SPPB not found'], 404);
        }

        // pastikan user memang approver aktif (status P) di doc ini
        $tApproval = T_approval::where('docid', $sppb->sppbid)
            ->where('status', 'P')
            ->where('aprvusername', 'like', "%{$user->username}%")
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
            $sppb->completed_by = $user->username;
            $sppb->completed_at = $now;
            $sppb->save();

            // Hitung sisa pending setelah approve ini
            $pendingCount = T_approval::where('docid', $sppb->sppbid)
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

            if ($pendingCount === 0) {
                // Tidak ada approver lagi -> dokumen complete
                $sppb->status       = 'C';
                $sppb->completed_by = $user->username;
                $sppb->completed_at = $now;
                $sppb->save();

                $sppbdetail = TrSPPBdetail::where('sppbid', $sppb->sppbid)                
                    ->get();

                foreach ($sppbdetail as $d) {
                    $d->status = 'C'; 
                    $d->save();
                }

                // Kirim email ke requester (creator)
                $status        = 'C';
                $subjectSuffix = $subjectMap[$status] ?? 'Notification';

                $data = [
                    'docid'     => $sppb->sppbid,
                    'cpnyid'    => $sppb->cpny_id ?? $sppb->cpnyid ?? '',
                    'deptname'  => $sppb->department_id ?? $sppb->departementid ?? '',
                    'date'      => $sppb->sppbdate,
                    'fullname'  => $fullname,  // nama penerima di email
                    'name'      => $fullname,  // fallback
                    'createdby' => $fullname,
                    'docname'   => 'SPPB',
                    'info'      => $sppb->keperluan,
                    'status'    => $status,
                    'url'       => url('/showsppbs/' . $sppb->id),
                ];

                $recipients = User::where('username', $sppb->created_by)
                    ->where('status', 'A')
                    ->get();

                foreach ($recipients as $rcp) {
                    try {
                        Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $rcp, $subjectSuffix) {
                            $to = $rcp->test_email ?? $rcp->email; // pakai field yang memang ada
                            $message->to($to)
                                ->subject($data['docid'] . ' - ' . $subjectSuffix . ' SPPB')
                                ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                        });
                    } catch (\Throwable $e) {
                        Log::error('Failed sending SPPB completion email', ['error' => $e->getMessage()]);
                    }
                }
            } else {
                // Masih ada approver berikutnya -> cari level berikutnya (P terrendah aprvid)
                $next = T_approval::where('docid', $sppb->sppbid)
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
                        'createdby' => $sppb->created_by,
                        'docname'   => 'SPPB',
                        'info'      => $sppb->keperluan,
                        'status'    => $status,
                        'url'       => url('/showsppbs/' . $sppb->id),
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
                                        ->subject($data['docid'] . ' - ' . $subjectSuffix . ' SPPB')
                                        ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                                });
                            } catch (\Throwable $e) {
                                Log::error('Failed sending SPPB waiting-approval email', ['error' => $e->getMessage()]);
                            }
                        }
                    } else {
                        Log::warning('Next approver has empty aprvusername list', ['docid' => $sppb->sppbid]);
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Task approved successfully']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Approve SPPB failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Approve failed'], 500);
        }
    }
    
    public function rejectSppb(Request $request, $docid)
    {
        $now  = Carbon::now();
        $user = $request->user();

        // $sppb = TrSPPB::where('sppbid', $docid)->first();
        $sppb = TrSPPB::with('creator')
            ->where('sppbid', $docid)
            ->first();
        $fullname = data_get($sppb, 'creator.name') ?: $sppb->created_by;

        if (!$sppb) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        // Validasi: user harus approver aktif (status P) pada dokumen ini
        $tApproval = T_approval::where('docid', $sppb->sppbid)
            ->where('status', 'P')
            ->where('aprvusername', 'like', "%{$user->username}%")
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

            // Update header SPPB
            $sppb->status       = 'R';
            $sppb->completed_by = $user->username;
            $sppb->completed_at = $now;
            $sppb->save();

            // Batalkan semua approval yang masih pending
            T_approval::where('docid', $sppb->sppbid)
                ->where('status', 'P')
                ->update(['status' => 'X']);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Reject SPPB failed', ['docid' => $docid, 'error' => $e->getMessage()]);
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

        $data = [
            'docid'     => $sppb->sppbid,
            'cpnyid'    => $sppb->cpny_id ?? $sppb->cpnyid ?? '',
            'deptname'  => $sppb->department_id ?? $sppb->departementid ?? '',
            'date'      => $now->toDateString(),            // bisa juga pakai $tApproval->aprvdateafter
            'fullname'  => $fullname,               // view email kita pakai $fullname
            'name'      => $fullname,               // fallback jika view pakai $name
            'createdby' => $fullname,
            'docname'   => 'SPPB',
            'info'      => $sppb->keperluan,
            'status'    => $status,
            'url'       => url('/showsppbs/' . $sppb->id),
        ];

        $recipients = User::where('username', $sppb->created_by)
            ->where('status', 'A')
            ->get();

        foreach ($recipients as $rcp) {
            try {
                $to = $rcp->test_email ?? $rcp->email; // sesuaikan field yang tersedia
                Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
                    $message->to($to)
                        ->subject($data['docid'] . ' - ' . $subjectSuffix . ' SPPB')
                        ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                });
            } catch (\Throwable $e) {
                Log::error('Failed sending SPPB rejected email', [
                    'docid' => $data['docid'],
                    'to'    => $rcp->username,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Simpan komentar penolakan (jika ada)
        try {
            app('App\Http\Controllers\SendCommentController')
                ->sendmsg($sppb->id, 'PB', $request);
        } catch (\Throwable $e) {
            Log::warning('SendComment after reject failed', [
                'docid' => $sppb->sppbid,
                'error' => $e->getMessage()
            ]);
        }

        return response()->json(['success' => true, 'message' => 'SPPB rejected successfully']);
    }

    public function reviseSppb(Request $request, $docid)
    {
        $now  = Carbon::now();
        $user = $request->user();

        // $sppb = TrSPPB::where('sppbid', $docid)->first();
        $sppb = TrSPPB::with('creator')
            ->where('sppbid', $docid)
            ->first();
        $fullname = data_get($sppb, 'creator.name') ?: $sppb->created_by;
            
        if (!$sppb) {
            return response()->json(['success' => false, 'message' => 'SPPB not found'], 404);
        }

        // Pastikan user adalah approver aktif (status P) dokumen ini
        $tApproval = T_approval::where('docid', $sppb->sppbid)
            ->where('status', 'P')
            ->where('aprvusername', 'like', "%{$user->username}%")
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

            // Update header SPPB
            $sppb->status       = 'D';
            $sppb->completed_by = $user->username;        // mengikuti pola existing
            $sppb->completed_at = $now;
            $sppb->save();

            // Batalkan approval lain yang masih pending
            T_approval::where('docid', $sppb->sppbid)
                ->where('status', 'P')
                ->update(['status' => 'X']);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Revise SPPB failed', ['docid' => $docid, 'error' => $e->getMessage()]);
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

        $data = [
            'docid'     => $sppb->sppbid,
            'cpnyid'    => $sppb->cpny_id ?? $sppb->cpnyid ?? '',
            'deptname'  => $sppb->department_id ?? $sppb->departementid ?? '',
            'date'      => $now->toDateString(),          // atau $tApproval->aprvdateafter
            'fullname'  => $fullname,             // template email pakai $fullname
            'name'      => $fullname,             // fallback jika view pakai $name
            'createdby' => $fullname,
            'docname'   => 'SPPB',
            'info'      => $sppb->keperluan,
            'status'    => $status,
            'url'       => url('/showsppbs/' . $sppb->id),
        ];

        $recipients = User::where('username', $sppb->created_by)
            ->where('status', 'A')
            ->get();

        foreach ($recipients as $rcp) {
            try {
                $to = $rcp->test_email ?? $rcp->email; // sesuaikan dengan kolom yang ada
                Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
                    $message->to($to)
                        ->subject($data['docid'] . ' - ' . $subjectSuffix . ' SPPB')
                        ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                });
            } catch (\Throwable $e) {
                Log::error('Failed sending SPPB revise email', [
                    'docid' => $data['docid'],
                    'to'    => $rcp->username,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Simpan komentar revisi (jika ada)
        try {
            app('App\Http\Controllers\SendCommentController')
                ->sendmsg($sppb->id, 'PB', $request);
        } catch (\Throwable $e) {
            Log::warning('SendComment after revise failed', [
                'docid' => $sppb->sppbid,
                'error' => $e->getMessage()
            ]);
        }

        return response()->json(['success' => true, 'message' => 'SPPB revised successfully']);
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
        $sppb = TrSPPB::findOrFail($id);

        $getName = function (?string $username) {
            if (!$username) return null;
            $u = \App\Models\User::where('username', $username)->first();
            return $u->name ?? $username;
        };

        $createdByName = $getName($sppb->created_by ?? null);
        $createdAt     = $sppb->created_at ? \Carbon\Carbon::parse($sppb->created_at)->format('Y-m-d H:i') : null;

        $completedByName = $getName($sppb->completed_by ?? null);
        $completedAt     = $sppb->completed_at ? \Carbon\Carbon::parse($sppb->completed_at)->format('Y-m-d H:i') : null;

        // kolom opsional, kalau tidak ada biarkan null
        $rejectedByName  = $getName($sppb->rejected_by ?? null);
        $rejectedAt      = isset($sppb->rejected_at) ? \Carbon\Carbon::parse($sppb->rejected_at)->format('Y-m-d H:i') : null;

        $revisedByName   = $getName($sppb->revised_by ?? null);
        $revisedAt       = isset($sppb->revised_at) ? \Carbon\Carbon::parse($sppb->revised_at)->format('Y-m-d H:i') : null;

        $status = (string) ($sppb->status ?? '');
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
            'title'        => 'SPPB',
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
            'doc'   => $sppb->sppbid ?? (string)$sppb->id,
            'steps' => $steps,
            'status'=> $status,
            'status_label' => $statusLabel,
        ]);
    }

    public function printSppb(int $id)
    {
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

        // Approval list (non-cancelled)
        $approval = T_approval::where('docid', $sppb->sppbid)
            ->where('status', '<>', 'X')
            ->orderBy('aprvid')
            ->orderBy('created_at')
            ->get();

        $approve_count = $approval->count();

        // Company (handle null)
        $company = Company::where('cpnyid', $sppb->cpny_id)->first();

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
            'cpnyname'            => optional($company)->cpnyname,
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





    






}
