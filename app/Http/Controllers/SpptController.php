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
use App\Models\TrSPPT;
use App\Models\TrSPPTdetail;
use App\Models\MsLocationPG;
use App\Models\MsSubLocationPG;
use Mail;
use Illuminate\Support\Facades\Log;
use App\Models\CompanyPG;
use App\Models\Bq;
use App\Models\BqDetail;
use App\Models\BqDetailTemp;
use PDF;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BqDetailTempImport; 
use Vinkla\Hashids\Facades\Hashids;
use App\Http\Controllers\TrAttachmentController;
use Illuminate\Support\Facades\Response;
use App\Models\TrAttachment;
use Google\Cloud\Storage\StorageClient;
use App\Models\MsTenant;
use App\Http\Controllers\ApprovalController;
use App\Models\TrApproval;


class SpptController extends Controller
{
    public function index()
    {
        $all = TrSPPT::count();
        $onProgress = TrSPPT::where('status', 'P')->count();
        $reject = TrSPPT::where('status', 'R')->count();
        $revise = TrSPPT::where('status', 'D')->count();
        $completed = TrSPPT::where('status', 'C')->count();
       
        return view('pages.sppts.sppts', compact('all', 'onProgress', 'reject', 'revise', 'completed'));
    }

    public function json(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $search = trim((string) $request->input('search.value', ''));
        $status = (string) $request->query('status', ''); // '' = all

        $baseTable = (new TrSPPT)->getTable(); // e.g. "tr_sppt"

        $columns = [
            0 => 'sppt.spptid',
            1 => 'sppt.spptdate',
            2 => 'sppt.cpny_id',
            3 => 'sppt.department_id',
            4 => 'rt.requesttype_name',
            5 => 'sppt.keperluan',
            6 => 'sppt.status',
        ];

        // ⬇️ default ke kolom 0 (sppt.spptid) dan desc
        $orderIdx = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'sppt.spptid';

        $base = TrSPPT::from($baseTable.' as sppt')
            ->leftJoin('ms_request_type as rt', function ($join) {
                $join->on('rt.requesttypeid', '=', 'sppt.requesttypeid');
            });

        if ($status !== '') {
            $base->where('sppt.status', $status);
        }

        $recordsTotal = (clone $base)->distinct('sppt.spptid')->count('sppt.spptid');

        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('sppt.spptid',          'like', "%{$search}%")
                ->orWhere('sppt.cpny_id',       'like', "%{$search}%")
                ->orWhere('sppt.department_id', 'like', "%{$search}%")
                ->orWhere('rt.requesttype_name','like', "%{$search}%")
                ->orWhere('sppt.keperluan',     'like', "%{$search}%")
                ->orWhere('sppt.status',        'like', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $base)->distinct('sppt.spptid')->count('sppt.spptid');

        $data = $base->select(
                'sppt.id',
                'sppt.spptid',
                'sppt.spptdate',
                'sppt.cpny_id',
                'sppt.department_id',
                'sppt.requesttypeid',
                'rt.requesttype_name',
                'sppt.keperluan',
                'sppt.status',
                'sppt.created_by'
            )
            ->orderBy($orderCol, $orderDir)                  // ← mengikuti request, default ke spptid desc
            ->orderBy('sppt.spptid', 'desc')                 // ← tie-breaker agar stabil
            ->skip($start)
            ->take($length)
            ->get();

        // Encode id dengan hashids → tambahkan field eid
        $data->transform(function ($row) {
            $row->eid = Hashids::encode($row->id);
            unset($row->id); // opsional: sembunyikan id asli
            return $row;
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }


    
    public function createSppt()
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
       
        return view('pages.sppts.createsppts', compact('usercpny','usercpny2','userdept','userdept2'));
    }

    
    
    public function storeSppt(Request $request)
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
        $inventorySubTypes   = $request->input('item_sub_type', []); // untuk Fixed Asset subtype

        $purchaseUnits    = $request->input('purchase_unit', []);     // dari hidden purchase_unit[]
        $uomMultDivs      = $request->input('uom_unitmultdiv', []);   // 'M' atau 'D'
        $uomRates         = $request->input('uom_unitrate', []);      // bisa "12", "12,5", "12.000",

        $doctype  = 'PT';
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


        // // pastikan line approval ada
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
            $spptNo = $docid;                                   // atau 'SPPT-'.$docid

            // === 1) header dulu (totalqty sementara 0) ===
            $header = new TrSPPT();
            $header->spptid            = $docid;                // PT string
            $header->spptdate          = $dt->toDateString();
            $header->cpny_id           = $request->input('cpnyid');
            $header->department_id     = $request->input('departementid');
            $header->requesttypeid     = $request->input('requesttypeid');
            $header->nama_tenant       = $request->input('tenant_id');
            $header->no_unit_tenant    = $request->input('unit_id');
            $header->pic_pengawas      = $request->input('pic_pengawas');
            $header->condition_unit    = $request->input('condition_unit');
            $header->beban             = $request->input('beban');
            $header->keperluan         = $request->input('keperluan');
            $header->budget_perpost    = $request->input('perpost');
            $header->woid              = $request->input('woid');
            $header->bqid              = '';
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

                $detail = new TrSPPTdetail();
                $detail->spptid                   = $docid;
                $detail->sppt_no                  = $i + 1;   // nomor urut detail
                $detail->inventoryid              = $invId;
                $detail->inventory_descr          = $productName;
                $detail->qty                      = $qty;
                $detail->uom                      = $uom;
                $detail->note                     = $notes[$i]   ?? null;
                $detail->inventory_type                = $item_types[$i] ?? null;
                $detail->inventory_sub_type       = $inventorySubTypes[$i] ?? null;
                $detail->inventory_category            = $item_categories[$i] ?? null;
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
            //         'created_user'   => $username,
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
                'ignore_nominal'           => true,   // SPPT diminta tidak cek nominal
                // 'grand_total'           => ...     // tidak dipakai di SPPT
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
                $header->updated_by = $firstApprovalUsernames;
                $header->updated_at = $dt;
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
                        'message' => 'Failed to create PT',
                        'error'   => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            } else {
                $uploadResult = null; // tidak ada attachment
            }

            // // === 6) kirim email ke approver pertama ===
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
            //         'docname'  => 'SPPT',
            //         'url'      => url('/showsppts/' . $eid),
            //     ];
                
            //     $approvers = array_filter(array_map('trim', explode(',', (string)$firstApproval->aprvusername)));
            //     $emails = User::whereIn('username', $approvers)
            //         ->where('status', 'A')
            //         ->pluck('test_email');

            //     foreach ($emails as $email) {
            //         \Mail::send('emails.mailapprovenew', $data, function ($message) use ($email, $data) {
            //             $message->to($email)
            //                 ->subject($data['docid'].' - Waiting Approval SPPT')
            //                 ->from('digitalserver@pakuwon.com', 'Pakuwon System');
            //         });
            //     }
            // }

            $eid = Hashids::encode($header->id);

            $approvalCtl->notifyFirstApprover(
                    $docid,
                    $doctype,
                    $header->status,                 // 'P' | 'R' | 'D' | 'A' | 'C'
                    'SPPT',
                    url('/showsppts/' . $eid),
                    [
                        'info'      => $request->keperluan,
                        'createdby' => $header->created_by,
                        'date'      => $dt->toDateTimeString(),
                    ]
            );


            DB::commit();

            return response()->json([
                'message'  => 'SPPT created successfully',
                'spptid'   => $docid,
                'sppt_no'  => $spptNo,
                'totalqty' => $totalQty,
                'attachments' => $uploadResult,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'message' => 'Failed to create SPPT',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
   
    public function editSppt($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $sppt = TrSPPT::findOrFail($id);

    // ===== Prefill TENANT: pakai MsTenant (unit_id, store_name, floor_id, store_no)
    if (!empty($sppt->nama_tenant)) {
        $tenant = \App\Models\MsTenant::select('unit_id','store_name','floor_id','store_no')
            ->where('unit_id', $sppt->nama_tenant)
            ->first();

        if ($tenant) {
            $sppt->tenant_name    = $tenant->store_name; // <-- label yg ditampilkan Select2
            $sppt->no_unit_tenant = trim(
                ($tenant->floor_id ? $tenant->floor_id : '') .
                ($tenant->store_no ? (' - '.$tenant->store_no) : '')
            );
        }
    }

    // ===== (Opsional) Prefill PIC: ambil nama lengkap utk label Select2 PIC
    if (!empty($sppt->pic_pengawas)) {
        $pic = \App\Models\User::where('username', $sppt->pic_pengawas)
            ->first(['username','name as full_name']);
        if ($pic) {
            $sppt->pic_name = $pic->full_name;
        }
    }


        // ===== Detail + eager load lokasi (sudah OK)
        $spptdetail = TrSPPTdetail::with([
                'location:location_id,location_name',
                'subLocation:sub_location_id,sub_location_name',
            ])
            ->where('spptid', $sppt->spptid)
            ->get()
            ->map(function ($d) {
                $d->location_name     = optional($d->location)->location_name;
                $d->sub_location_name = optional($d->subLocation)->sub_location_name;
                return $d;
            });

        $user      = request()->user();
        $usercpny  = Usercpny::where('username', $user->username)->get();
        $usercpny2 = Usercpny::where('username', $user->username)->first();
        $userdept  = Userdept::where('username', $user->username)->get();
        $userdept2 = Userdept::where('username', $user->username)->first();

        $rows = TrAttachment::where('refnbr', $sppt->spptid)
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
                'id'           => $r->id,
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

        return view('pages.sppts.editsppts', compact(
            'sppt','spptdetail','usercpny','usercpny2','userdept','userdept2','attachments','hash'
        ));
    }



    public function updateSppt(Request $request, $hash)
    {
        // dd($request->all()); // matikan agar eksekusi lanjut

        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404, 'PT tidak ditemukan.');

        $user      = $request->user();   
        $dt        = Carbon::now();
        $year      = $dt->year;
        $month     = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        $datestamp = $dt->toDateTimeString();   
        $doctype   = 'PT';
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

        $header = TrSPPT::findOrFail($id);
        // update header
        $header->cpny_id        = $request->cpnyid;
        $header->department_id  = $request->departementid;
        $header->requesttypeid  = $request->requesttypeid;
        $header->nama_tenant    = $request->nama_tenant;
        $header->no_unit_tenant = $request->no_unit_tenant;
        $header->pic_pengawas   = $request->pic_pengawas;
        $header->condition_unit = $request->condition_unit;
        $header->beban          = $request->beban;
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

        $inventorySubTypes   = array_values($request->input('item_sub_type', []));

        // arrays UoM tambahan
        $purchaseUnits = array_values($request->input('purchase_unit', []));      // hidden dari UI
        $uomMultDivs   = array_values($request->input('uom_unitmultdiv', []));    // 'M'/'D'
        $uomRates      = array_values($request->input('uom_unitrate', []));       // bisa "12.000"

        DB::beginTransaction();

        try {
            // hapus baris yang di-mark delete
            if ($request->filled('deleted_detail_ids')) {
                $idsToDelete = array_filter(array_map('trim', explode(',', $request->deleted_detail_ids)));
                if ($idsToDelete) TrSPPTdetail::whereIn('id', $idsToDelete)->delete();
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
                    $detail = TrSPPTdetail::where('id', $idDetail)
                        ->where('spptid', $header->spptid)
                        ->first();
                    if ($detail) {
                        $detail->fill($data)->save();
                    } else {
                        $detail = new TrSPPTdetail($data);
                        $detail->spptid = $header->spptid;
                        $detail->save();
                    }
                } else {
                    $detail = new TrSPPTdetail($data);
                    $detail->spptid = $header->spptid;
                    $detail->save();
                }

                $savedDetails[] = $detail->id;
            }

            // Renumber sppt_no 1..N
            $n = 1;
            foreach ($savedDetails as $did) {
                TrSPPTdetail::where('id', $did)->update(['sppt_no' => $n++]);
            }

            // Hitung total qty (kalau mau pakai base_qty, ganti ke sum('base_qty'))
            $totalQty = TrSPPTdetail::where('spptid', $header->spptid)->sum('qty');
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
            //         'docid'          => $header->spptid,
            //         'aprvid'         => $a->aprvid,
            //         'aprvdoctype'    => $a->aprvdoctype,
            //         'aprvcpnyid'     => $a->aprvcpnyid,
            //         'aprvdeptid'     => $a->aprvdeptid,
            //         'aprvusername'   => $a->aprvusername,
            //         'name'           => $a->name,
            //         'aprvdatebefore' => $a->aprvid == 1 ? $datestamp : null,
            //         'aprvtotalday'   => 1,
            //         'status'         => 'P',
            //         'created_user'   => $username,
            //     ]);
            // }

            // $firstApprovalUsernames = optional($approvals->first())->aprvusername;
            // if ($firstApprovalUsernames) {
            //     $header->completed_by = $firstApprovalUsernames;
            //     $header->completed_at = $dt;
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
                'ignore_nominal'           => true,   // SPPT diminta tidak cek nominal
                // 'grand_total'           => ...     // tidak dipakai di SPPT
            ];

            // Generate TrApproval
            [$firstApprovalUsernames, $linesCount] = $approvalCtl->generateForDocument(
                $header->spptid,
                $doctype,
                $request->cpnyid,
                $request->departementid,
                $username,
                $ctx,
                $dt
            );

            // (opsional) simpan hint approver pertama di header seperti sebelumnya
            if ($firstApprovalUsernames) {
                $header->updated_by = $firstApprovalUsernames;
                $header->updated_at = $dt;
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
            //         $attach->docid = $header->spptid;
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
                    'refnbr'        => $header->spptid,
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
                        'message' => 'Failed to update PT',
                        'error'   => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            }

            // // email approver pertama (tetap)
            // $firstApproval = T_approval::where('docid', $header->spptid)
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
            //         'docname'  => 'SPPT',
            //         'url'      => url('/showsppts/' . $eid),
            //     ];

            //     $approvers = array_filter(array_map('trim', explode(',', (string)$firstApproval->aprvusername)));
            //     $emails = User::whereIn('username', $approvers)
            //         ->where('status', 'A')
            //         ->pluck('test_email');

            //     foreach ($emails as $email) {
            //         \Mail::send('emails.mailapprovenew', $data, function ($message) use ($email, $data) {
            //             $message->to($email)
            //                 ->subject($data['docid'].' - Waiting Approval SPPT')
            //                 ->from('digitalserver@pakuwon.com', 'Pakuwon System');
            //         });
            //     }
            // }

            $eid = Hashids::encode($header->id);

            $approvalCtl->notifyFirstApprover(
                    $header->spptid,
                    $doctype,
                    $header->status,                 // 'P' | 'R' | 'D' | 'A' | 'C'
                    'SPPT',
                    url('/showsppts/' . $eid),
                    [
                        'info'      => $request->keperluan,
                        'createdby' => $header->created_by,
                        'date'      => $dt->toDateTimeString(),
                    ]
            );


            DB::commit();
            return response()->json(['message' => 'SPPT updated successfully']);

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
 

    public function showSppt($hash)
    {        
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);
        $user = Auth::user();       

        if (!$user) {
            return redirect()->route('login');
        }

        // $sppt = TrSPPT::findOrFail($id);
        $sppt = TrSPPT::with([
            'requestType:requesttypeid,requesttype_name',
            'creator:username,name',
            'tenantname:id,store_name',
            'pic:username,name',
        ])
        ->findOrFail($id);        

        $spptdetail = TrSPPTdetail::with([
            'location:location_id,location_name',
            'subLocation:sub_location_id,sub_location_name'
        ])
        ->where('spptid', $sppt->spptid)
        ->get();
        
        // $approval = T_approval::where('docid', $sppt->spptid)
        //     ->where('status','<>','X')      
        //     ->orderBy('created_at')
        //     ->orderBy('aprvid')      
        //     ->get();
       
        // $attachment = Attachment::where('docid', $sppt->spptid)    
        //     ->where('status','A')        
        //     ->get();    

        $rows = TrAttachment::where('refnbr', $sppt->spptid)
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
            
        $bq = Bq::where('bqid', $sppt->bqid)   
            ->first();          
            
        if ($bq) {
            $bq->eid = Hashids::encode($bq->id);
        }
       
        return view('pages.sppts.showsppts', compact('sppt','attachments','spptdetail','bq','hash'));
    }
   
    public function approveSppt(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'PT';

        $sppt = TrSPPT::with('creator')->where('spptid', $docid)->first();
        if (!$sppt) return response()->json(['success'=>false,'message'=>'SPPT not found'],404);

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($sppt->id);
        $docUrl   = url('/showsppts/' . $eid);
        $fullname = data_get($sppt, 'creator.name') ?: $sppt->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->approveStep(
            $sppt->spptid,
            $doctype,
            $user->username,
            $user->name,

            // complete: update header/detail + email creator complete
            function (string $refnbr, \Carbon\Carbon $now) use ($sppt, $fullname, $docUrl) {
                $sppt->status       = 'C';
                $sppt->completed_by = $sppt->completed_by ?: auth()->user()->username;
                $sppt->completed_at = $now;
                $sppt->save();

                TrSPPTdetail::where('spptid', $sppt->spptid)->update(['status' => 'C']);

                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $sppt->spptid,
                    'SPPT',
                    'C',
                    $sppt->created_by,
                    $docUrl,
                    [
                        'cpnyid'   => $sppt->cpny_id ?? $sppt->cpnyid ?? '',
                        'deptname' => $sppt->department_id ?? $sppt->departementid ?? '',
                        'date'     => $sppt->spptdate,
                        'info'     => $sppt->keperluan,
                        'fullname' => $fullname,
                        'name'     => $fullname,
                        'createdby'=> $fullname, 
                    ]
                );
            },

            // notify next approver
            function ($next, \Carbon\Carbon $now) use ($sppt, $docUrl) {
                app(\App\Http\Controllers\ApprovalController::class)->notifyFirstApprover(
                    $sppt->spptid,
                    'PT',
                    'P',
                    'SPPT',
                    $docUrl,
                    [
                        'info'      => $sppt->keperluan,
                        'createdby' => $sppt->created_by,
                        'date'      => $now->toDateTimeString(),
                    ]
                );

                // jejak terakhir diproses (optional)
                $sppt->completed_by = auth()->user()->username;
                $sppt->completed_at = $now;
                $sppt->save();
            }
        );

        if (!$result['ok']) {
            return response()->json(['success'=>false,'message'=>$result['message'] ?? 'Approve failed'], 403);
        }

        return response()->json(['success'=>true,'message'=>'Task approved successfully']);
    }

    public function rejectSppt(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'PT';

        $sppt = \App\Models\TrSPPT::with('creator')->where('spptid', $docid)->first();
        if (!$sppt) return response()->json(['success'=>false,'message'=>'SPPT not found'],404);

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($sppt->id);
        $docUrl   = url('/showsppts/' . $eid);
        $fullname = data_get($sppt, 'creator.name') ?: $sppt->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->rejectStep(
            $sppt->spptid,
            $doctype,
            $user->username,
            $user->name,

            function (string $refnbr, \Carbon\Carbon $now) use ($sppt, $fullname, $docUrl) {
                $sppt->status       = 'R';
                $sppt->completed_by = auth()->user()->username;
                $sppt->completed_at = $now;
                $sppt->save();

                // optional: tandai detail R
                // \App\Models\TrSPPTdetail::where('spptid', $sppt->spptid)->update(['status' => 'R']);

                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $sppt->spptid,
                    'SPPT',
                    'R',
                    $sppt->created_by,
                    $docUrl,
                    [
                        'cpnyid'   => $sppt->cpny_id ?? $sppt->cpnyid ?? '',
                        'deptname' => $sppt->department_id ?? $sppt->departementid ?? '',
                        'date'     => $now->toDateString(),
                        'info'     => $sppt->keperluan,
                        'fullname' => $fullname,
                        'name'     => $fullname,
                        'createdby'=> $fullname, 
                    ]
                );

                // simpan komentar (jika ada)
                try {
                    app('App\Http\Controllers\SendCommentController')->sendmsg($sppt->id, 'PT', request());
                } catch (\Throwable $e) {}
            }
        );

        if (!$result['ok']) {
            return response()->json(['success'=>false,'message'=>$result['message'] ?? 'Reject failed'], 403);
        }

        return response()->json(['success'=>true,'message'=>'SPPT rejected successfully']);
    }

    public function reviseSppt(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'PT';

        $sppt = \App\Models\TrSPPT::with('creator')->where('spptid', $docid)->first();
        if (!$sppt) return response()->json(['success'=>false,'message'=>'SPPT not found'],404);

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($sppt->id);
        $docUrl   = url('/showsppts/' . $eid);
        $fullname = data_get($sppt, 'creator.name') ?: $sppt->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->reviseStep(
            $sppt->spptid,            // refnbr
            $doctype,                 // PT
            $user->username,          // actor
            $user->name,              // actor
            function (string $refnbr, \Carbon\Carbon $now) use ($sppt, $fullname, $docUrl) {
                // === HEADER SPPT -> D ===
                $sppt->status       = 'D';
                $sppt->completed_by = auth()->user()->username;
                $sppt->completed_at = $now;
                $sppt->save();

                // (opsional) DETAIL -> D
                // \App\Models\TrSPPTdetail::where('spptid', $sppt->spptid)->update(['status' => 'D']);

                // === Email ke requester ===
                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $sppt->spptid,
                    'SPPT',
                    'D',
                    $sppt->created_by,
                    $docUrl,
                    [
                        'cpnyid'   => $sppt->cpny_id ?? $sppt->cpnyid ?? '',
                        'deptname' => $sppt->department_id ?? $sppt->departementid ?? '',
                        'date'     => $now->toDateString(),
                        'info'     => $sppt->keperluan,
                        'fullname' => $fullname,
                        'name'     => $fullname,
                        'createdby'=> $fullname,   // <<< tambahkan ini
                    ]
                );


                // === Simpan komentar (jika ada) ===
                try {
                    app('App\Http\Controllers\SendCommentController')->sendmsg($sppt->id, 'PT', request());
                } catch (\Throwable $e) {}
            }
        );

        if (!$result['ok']) {
            return response()->json([
                'success'=>false,
                'message'=>$result['message'] ?? 'Revise failed'
            ], 403);
        }

        return response()->json(['success'=>true,'message'=>'SPPT revised successfully']);
    }




    // public function approveSppt(Request $request, $docid)
    // {
    //     $now  = Carbon::now();
    //     $user = $request->user();

    //     // $sppt = TrSPPT::where('spptid', $docid)->first();
    //     $sppt = TrSPPT::with('creator')
    //         ->where('spptid', $docid)
    //         ->first();
    //     $fullname = data_get($sppt, 'creator.name') ?: $sppt->created_by;

    //     if (!$sppt) {
    //         return response()->json(['success' => false, 'message' => 'SPPT not found'], 404);
    //     }

    //     // pastikan user memang approver aktif (status P) di doc ini
    //     $tApproval = T_approval::where('docid', $sppt->spptid)
    //         ->where('status', 'P')
    //         ->where('aprvusername', 'like', "%{$user->username}%")
    //         ->whereNotNull('aprvdatebefore') 
    //         ->orderBy('aprvid', 'ASC')
    //         ->first();

    //     if (!$tApproval) {
    //         return response()->json(['success' => false, 'message' => "You can't approve!"], 403);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         // Set current approver -> Approved
    //         $tApproval->status         = 'A';
    //         $tApproval->aprvdateafter  = $now;
    //         $tApproval->aprvusername   = $user->username;
    //         $tApproval->name           = $user->name;
    //         $tApproval->save();

    //         // Update header informasi "terakhir diproses"
    //         $sppt->completed_by = $user->username;
    //         $sppt->completed_at = $now;
    //         $sppt->save();

    //         // Hitung sisa pending setelah approve ini
    //         $pendingCount = T_approval::where('docid', $sppt->spptid)
    //             ->where('status', 'P')
    //             ->count();

    //         // Pemetaan judul sesuai status
    //         $subjectMap = [
    //             'P' => 'Waiting Approval',
    //             'R' => 'Rejected Approval',
    //             'D' => 'Revise Approval',
    //             'A' => 'Approved',
    //             'C' => 'Completed',
    //         ];

    //         $eid = Hashids::encode($sppt->id);

    //         if ($pendingCount === 0) {
    //             // Tidak ada approver lagi -> dokumen complete
    //             $sppt->status       = 'C';
    //             $sppt->completed_by = $user->username;
    //             $sppt->completed_at = $now;
    //             $sppt->save();

    //             $spptdetail = TrSPPTdetail::where('spptid', $sppt->spptid)                
    //                 ->get();

    //             foreach ($spptdetail as $d) {
    //                 $d->status = 'C'; 
    //                 $d->save();
    //             }

    //             // Kirim email ke requester (creator)
    //             $status        = 'C';
    //             $subjectSuffix = $subjectMap[$status] ?? 'Notification';

    //             $data = [
    //                 'docid'     => $sppt->spptid,
    //                 'cpnyid'    => $sppt->cpny_id ?? $sppt->cpnyid ?? '',
    //                 'deptname'  => $sppt->department_id ?? $sppt->departementid ?? '',
    //                 'date'      => $sppt->spptdate,
    //                 'fullname'  => $fullname,  // nama penerima di email
    //                 'name'      => $fullname,  // fallback
    //                 'createdby' => $fullname,
    //                 'docname'   => 'SPPT',
    //                 'info'      => $sppt->keperluan,
    //                 'status'    => $status,
    //                 'url'       => url('/showsppts/' . $eid),
    //             ];

    //             $recipients = User::where('username', $sppt->created_by)
    //                 ->where('status', 'A')
    //                 ->get();

    //             foreach ($recipients as $rcp) {
    //                 try {
    //                     Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $rcp, $subjectSuffix) {
    //                         $to = $rcp->test_email ?? $rcp->email; // pakai field yang memang ada
    //                         $message->to($to)
    //                             ->subject($data['docid'] . ' - ' . $subjectSuffix . ' SPPT')
    //                             ->from('digitalserver@pakuwon.com', 'Pakuwon System');
    //                     });
    //                 } catch (\Throwable $e) {
    //                     Log::error('Failed sending SPPT completion email', ['error' => $e->getMessage()]);
    //                 }
    //             }
    //         } else {
    //             // Masih ada approver berikutnya -> cari level berikutnya (P terrendah aprvid)
    //             $next = T_approval::where('docid', $sppt->spptid)
    //                 ->where('status', 'P')
    //                 ->orderBy('aprvid', 'ASC')
    //                 ->first();

    //             if ($next) {
    //                 // Stempel "datebefore" untuk approver berikutnya
    //                 $next->aprvdatebefore = $now;
    //                 $next->save();

    //                 // Kirim email ke semua username yang ada di kolom aprvusername (dipisah koma)
    //                 $status        = 'P';
    //                 $subjectSuffix = $subjectMap[$status] ?? 'Notification';

    //                 $data = [
    //                     'docid'     => $next->docid,
    //                     'cpnyid'    => $next->aprvcpnyid,
    //                     'deptname'  => $next->aprvdeptid,
    //                     'date'      => $next->aprvdatebefore,
    //                     'fullname'  => $next->name,
    //                     'name'      => $next->name,
    //                     'createdby' => $sppt->created_by,
    //                     'docname'   => 'SPPT',
    //                     'info'      => $sppt->keperluan,
    //                     'status'    => $status,
    //                     'url'       => url('/showsppts/' . $eid),
    //                 ];

    //                 $usernames = array_filter(array_map('trim', explode(',', (string) $next->aprvusername)));
    //                 if (!empty($usernames)) {
    //                     $recipients = User::whereIn('username', $usernames)
    //                         ->where('status', 'A')
    //                         ->get();

    //                     foreach ($recipients as $rcp) {
    //                         try {
    //                             Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $rcp, $subjectSuffix) {
    //                                 $to = $rcp->test_email ?? $rcp->email;
    //                                 $message->to($to)
    //                                     ->subject($data['docid'] . ' - ' . $subjectSuffix . ' SPPT')
    //                                     ->from('digitalserver@pakuwon.com', 'Pakuwon System');
    //                             });
    //                         } catch (\Throwable $e) {
    //                             Log::error('Failed sending SPPT waiting-approval email', ['error' => $e->getMessage()]);
    //                         }
    //                     }
    //                 } else {
    //                     Log::warning('Next approver has empty aprvusername list', ['docid' => $sppt->spptid]);
    //                 }
    //             }
    //         }

    //         DB::commit();
    //         return response()->json(['success' => true, 'message' => 'Task approved successfully']);
    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         Log::error('Approve SPPT failed', ['error' => $e->getMessage()]);
    //         return response()->json(['success' => false, 'message' => 'Approve failed'], 500);
    //     }
    // }
    
    // public function rejectSppt(Request $request, $docid)
    // {
    //     $now  = Carbon::now();
    //     $user = $request->user();

    //     // $sppt = TrSPPT::where('spptid', $docid)->first();
    //     $sppt = TrSPPT::with('creator')
    //         ->where('spptid', $docid)
    //         ->first();
    //     $fullname = data_get($sppt, 'creator.name') ?: $sppt->created_by;

    //     if (!$sppt) {
    //         return response()->json(['success' => false, 'message' => 'Task not found'], 404);
    //     }

    //     // Validasi: user harus approver aktif (status P) pada dokumen ini
    //     $tApproval = T_approval::where('docid', $sppt->spptid)
    //         ->where('status', 'P')
    //         ->where('aprvusername', 'like', "%{$user->username}%")
    //         ->whereNotNull('aprvdatebefore') 
    //         ->orderBy('aprvid', 'ASC')
    //         ->first();

    //     if (!$tApproval) {
    //         return response()->json(['success' => false, 'message' => "You can't reject!"], 403);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         // Tandai approval saat ini sebagai Rejected
    //         $tApproval->status        = 'R';
    //         $tApproval->aprvdateafter = $now;
    //         $tApproval->aprvusername  = $user->username; // catat siapa yang reject
    //         $tApproval->name          = $user->name;
    //         $tApproval->save();

    //         // Update header SPPT
    //         $sppt->status       = 'R';
    //         $sppt->completed_by = $user->username;
    //         $sppt->completed_at = $now;
    //         $sppt->save();

    //         // Batalkan semua approval yang masih pending
    //         T_approval::where('docid', $sppt->spptid)
    //             ->where('status', 'P')
    //             ->update(['status' => 'X']);

    //         DB::commit();
    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         Log::error('Reject SPPT failed', ['docid' => $docid, 'error' => $e->getMessage()]);
    //         return response()->json(['success' => false, 'message' => 'Reject failed'], 500);
    //     }

    //     // === Kirim Email ke requester (creator) ===
    //     $status = 'R'; // Rejected
    //     $subjectMap = [
    //         'P' => 'Waiting Approval',
    //         'R' => 'Rejected Approval',
    //         'D' => 'Revise Approval',
    //         'A' => 'Approved',
    //         'C' => 'Completed',
    //     ];
    //     $subjectSuffix = $subjectMap[$status] ?? 'Notification';

    //     $eid = Hashids::encode($sppt->id);

    //     $data = [
    //         'docid'     => $sppt->spptid,
    //         'cpnyid'    => $sppt->cpny_id ?? $sppt->cpnyid ?? '',
    //         'deptname'  => $sppt->department_id ?? $sppt->departementid ?? '',
    //         'date'      => $now->toDateString(),            // bisa juga pakai $tApproval->aprvdateafter
    //         'fullname'  => $fullname,               // view email kita pakai $fullname
    //         'name'      => $fullname,               // fallback jika view pakai $name
    //         'createdby' => $fullname,
    //         'docname'   => 'SPPT',
    //         'info'      => $sppt->keperluan,
    //         'status'    => $status,
    //         'url'       => url('/showsppts/' . $eid),
    //     ];

    //     $recipients = User::where('username', $sppt->created_by)
    //         ->where('status', 'A')
    //         ->get();

    //     foreach ($recipients as $rcp) {
    //         try {
    //             $to = $rcp->test_email ?? $rcp->email; // sesuaikan field yang tersedia
    //             Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
    //                 $message->to($to)
    //                     ->subject($data['docid'] . ' - ' . $subjectSuffix . ' SPPT')
    //                     ->from('digitalserver@pakuwon.com', 'Pakuwon System');
    //             });
    //         } catch (\Throwable $e) {
    //             Log::error('Failed sending SPPT rejected email', [
    //                 'docid' => $data['docid'],
    //                 'to'    => $rcp->username,
    //                 'error' => $e->getMessage()
    //             ]);
    //         }
    //     }

    //     // Simpan komentar penolakan (jika ada)
    //     try {
    //         app('App\Http\Controllers\SendCommentController')
    //             ->sendmsg($sppt->id, 'PT', $request);
    //     } catch (\Throwable $e) {
    //         Log::warning('SendComment after reject failed', [
    //             'docid' => $sppt->spptid,
    //             'error' => $e->getMessage()
    //         ]);
    //     }

    //     return response()->json(['success' => true, 'message' => 'SPPT rejected successfully']);
    // }

    // public function reviseSppt(Request $request, $docid)
    // {
    //     $now  = Carbon::now();
    //     $user = $request->user();

    //     // $sppt = TrSPPT::where('spptid', $docid)->first();
    //     $sppt = TrSPPT::with('creator')
    //         ->where('spptid', $docid)
    //         ->first();
    //     $fullname = data_get($sppt, 'creator.name') ?: $sppt->created_by;
            
    //     if (!$sppt) {
    //         return response()->json(['success' => false, 'message' => 'SPPT not found'], 404);
    //     }

    //     // Pastikan user adalah approver aktif (status P) dokumen ini
    //     $tApproval = T_approval::where('docid', $sppt->spptid)
    //         ->where('status', 'P')
    //         ->where('aprvusername', 'like', "%{$user->username}%")
    //         ->whereNotNull('aprvdatebefore') 
    //         ->orderBy('aprvid', 'ASC')
    //         ->first();

    //     if (!$tApproval) {
    //         return response()->json(['success' => false, 'message' => "You can't revise!"], 403);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         // Tandai approval saat ini sebagai Revise (D)
    //         $tApproval->status        = 'D';
    //         $tApproval->aprvdateafter = $now;
    //         $tApproval->aprvusername  = $user->username;  // catat siapa yang revise
    //         $tApproval->name          = $user->name;
    //         $tApproval->save();

    //         // Update header SPPT
    //         $sppt->status       = 'D';
    //         $sppt->completed_by = $user->username;        // mengikuti pola existing
    //         $sppt->completed_at = $now;
    //         $sppt->save();

    //         // Batalkan approval lain yang masih pending
    //         T_approval::where('docid', $sppt->spptid)
    //             ->where('status', 'P')
    //             ->update(['status' => 'X']);

    //         DB::commit();
    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         Log::error('Revise SPPT failed', ['docid' => $docid, 'error' => $e->getMessage()]);
    //         return response()->json(['success' => false, 'message' => 'Revise failed'], 500);
    //     }

    //     // === Kirim email ke requester (creator) ===
    //     $status = 'D'; // Revise
    //     $subjectMap = [
    //         'P' => 'Waiting Approval',
    //         'R' => 'Rejected Approval',
    //         'D' => 'Revise Approval',
    //         'A' => 'Approved',
    //         'C' => 'Completed',
    //     ];
    //     $subjectSuffix = $subjectMap[$status] ?? 'Notification';

    //     $eid = Hashids::encode($sppt->id);

    //     $data = [
    //         'docid'     => $sppt->spptid,
    //         'cpnyid'    => $sppt->cpny_id ?? $sppt->cpnyid ?? '',
    //         'deptname'  => $sppt->department_id ?? $sppt->departementid ?? '',
    //         'date'      => $now->toDateString(),          // atau $tApproval->aprvdateafter
    //         'fullname'  => $fullname,             // template email pakai $fullname
    //         'name'      => $fullname,             // fallback jika view pakai $name
    //         'createdby' => $fullname,
    //         'docname'   => 'SPPT',
    //         'info'      => $sppt->keperluan,
    //         'status'    => $status,
    //         'url'       => url('/showsppts/' . $eid),
    //     ];

    //     $recipients = User::where('username', $sppt->created_by)
    //         ->where('status', 'A')
    //         ->get();

    //     foreach ($recipients as $rcp) {
    //         try {
    //             $to = $rcp->test_email ?? $rcp->email; // sesuaikan dengan kolom yang ada
    //             Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
    //                 $message->to($to)
    //                     ->subject($data['docid'] . ' - ' . $subjectSuffix . ' SPPT')
    //                     ->from('digitalserver@pakuwon.com', 'Pakuwon System');
    //             });
    //         } catch (\Throwable $e) {
    //             Log::error('Failed sending SPPT revise email', [
    //                 'docid' => $data['docid'],
    //                 'to'    => $rcp->username,
    //                 'error' => $e->getMessage()
    //             ]);
    //         }
    //     }

    //     // Simpan komentar revisi (jika ada)
    //     try {
    //         app('App\Http\Controllers\SendCommentController')
    //             ->sendmsg($sppt->id, 'PT', $request);
    //     } catch (\Throwable $e) {
    //         Log::warning('SendComment after revise failed', [
    //             'docid' => $sppt->spptid,
    //             'error' => $e->getMessage()
    //         ]);
    //     }

    //     return response()->json(['success' => true, 'message' => 'SPPT revised successfully']);
    // }
    

    // public function checkApproval($id, $action)
    // {
    //     $user = Auth::user(); // Ambil user yang login
    //     // dd($action);
    //     // Query dasar untuk pengecekan
    //     $query = T_approval::where('docid', $id)
    //                 ->where('aprvusername', 'like', '%' . $user->username . '%')
    //                 ->where('status', 'P');                 

    //     // Jika aksi adalah reject atau revise, pastikan aprvdatebefore tidak null
    //     if (in_array($action, ['reject', 'revise','approve'])) {
    //         $query->whereNotNull('aprvdatebefore');
    //     }

    //     // Cek apakah user bisa melakukan aksi
    //     $canPerformAction = $query->exists();

    //     return response()->json(['canPerformAction' => $canPerformAction]);
    // }

    public function tracking($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $sppt = TrSPPT::findOrFail($id);

        $getName = function (?string $username) {
            if (!$username) return null;
            $u = \App\Models\User::where('username', $username)->first();
            return $u->name ?? $username;
        };

        $createdByName = $getName($sppt->created_by ?? null);
        $createdAt     = $sppt->created_at ? \Carbon\Carbon::parse($sppt->created_at)->format('Y-m-d H:i') : null;

        $completedByName = $getName($sppt->completed_by ?? null);
        $completedAt     = $sppt->completed_at ? \Carbon\Carbon::parse($sppt->completed_at)->format('Y-m-d H:i') : null;

        // kolom opsional, kalau tidak ada biarkan null
        $rejectedByName  = $getName($sppt->rejected_by ?? null);
        $rejectedAt      = isset($sppt->rejected_at) ? \Carbon\Carbon::parse($sppt->rejected_at)->format('Y-m-d H:i') : null;

        $revisedByName   = $getName($sppt->revised_by ?? null);
        $revisedAt       = isset($sppt->revised_at) ? \Carbon\Carbon::parse($sppt->revised_at)->format('Y-m-d H:i') : null;

        $status = (string) ($sppt->status ?? '');
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
            'title'        => 'SPPT',
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
            'doc'   => $sppt->spptid ?? (string)$sppt->id,
            'steps' => $steps,
            'status'=> $status,
            'status_label' => $statusLabel,
        ]);
    }

    public function showBQ($hash)
    {        
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();       

        if (!$user) {
            return redirect()->route('login');
        }

        // $sppt = TrSPPT::findOrFail($id);
        $bq = Bq::with([            
            'creator:username,name'
        ])
        ->findOrFail($id);     
        
        $canEdit = T_approval::where('docid', $bq->sppjtid)
            ->where('status', 'P')
            ->whereNotNull('aprvdatebefore')
            ->where(function ($q) use ($user) {
                $q->where('created_user', $user->id)               // kalau stored id
                  ->orWhere('created_user', $user->name)            // kalau stored name
                  ->orWhere('created_user', $user->username ?? ''); // kalau stored username
            })
            ->exists();
        // dd( $canEdit);   
        $bqdetail = BqDetail::where('bqid', $bq->bqid)
            ->get();      
              
        $attachment = Attachment::where('docid', $bq->bqid)    
            ->where('status','A')        
            ->get();    
       
        return view('pages.sppts.showbqsppts', compact('bq','attachment','bqdetail','canEdit'));
    }

    public function editBQ($id)
    {
        // kalau $id adalah PRIMARY KEY tabel tr_bq:
        $bq = Bq::with(['creator:username,name'])->findOrFail($id);

        // kalau $id itu bqid (string) ganti ke:
        // $bq = Bq::with(['creator:username,name'])->where('bqid', $id)->firstOrFail();

        $bq_detail = BqDetail::where('bqid', $bq->bqid)
            ->orderBy('bq_no') // biar urut
            ->get();

        $temp_id  = session('import_temp_id');
        $tempData = $temp_id ? BqDetailTemp::where('temp_id', $temp_id)->get() : [];

        $attachment = Attachment::where('docid', $bq->bqid)
            ->where('status','A')
            ->get();

        return view('pages.sppts.editbqsppts', compact(
            'bq',
            'bq_detail',
            'temp_id',
            'tempData',
            'attachment'
        ));
    }
    
    public function printSppt($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);
        
        $authUser = Auth::user();
        if (!$authUser) {
            return redirect()->route('login');
        }

        // Ambil SPPT + relasi yang dibutuhkan
        $sppt = TrSPPT::with([
                'requestType:requesttypeid,requesttype_name',
                'creator:username,name',
            ])
            ->findOrFail($id);

        // Detail baris SPPT
        $spptdetail = TrSPPTdetail::with([
                'location:location_id,location_name',
                'subLocation:sub_location_id,sub_location_name',
            ])
            ->where('spptid', $sppt->spptid)
            ->get();

        // Approval list (non-cancelled)
        // $approval = T_approval::where('docid', $sppt->spptid)
        //     ->where('status', '<>', 'X')
        //     ->orderBy('aprvid')
        //     ->orderBy('created_at')
        //     ->get();
        $approval = TrApproval::query()
            ->where('refnbr', $sppt->spptid)          // dulu: docid
            ->where('status', '<>', 'X')           
            ->orderByRaw('CAST(aprv_leveling AS numeric) ASC')
            ->orderBy('created_at', 'ASC')            // tie-breaker kalau leveling sama
            ->get();

        $approve_count = $approval->count();

        // Company (handle null)
        $company = Company::where('cpnyid', $sppt->cpny_id)->first();

        // Mapping status dokumen
        switch ($sppt->status) {
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
            'title'               => ' Surat Permintaan Pekerjaan Tenant',
            'doc_type'            => 'SPPT',
            'docid'               => $sppt->spptid,
            'department_id'       => $sppt->department_id,
            'cpnyname'            => optional($company)->cpnyname,
            'parent'              => optional($company)->parent,
            'project'             => optional($company)->project,
            // identitas & tanggal
            'created_by_username' => $sppt->created_by,
            'created_by_name'     => ucwords(strtolower(optional($sppt->creator)->name)),
            'created_at_fmt'      => optional($sppt->created_at)->format('d F Y'),
            'req_date_fmt'        => optional($sppt->created_at)->format('d M Y H:i'),
            'spptdate'            => \Carbon\Carbon::parse($sppt->spptdate)->format('d F Y'),
            // konten
            'bqid'                => $sppt->bqid,
            'nama_tenant'         => optional($sppt->tenantname)->tenant,
            'no_unit_tenant'      => $sppt->no_unit_tenant,
            'pic_pengawas'        => ucwords(strtolower(optional($sppt->pic)->name)),
            'condition_unit'      => $sppt->condition_unit,
            'beban'               => $sppt->beban,
            'keperluan'           => $sppt->keperluan,
            'status_doc'          => $status_doc,
            'requesttype_name'    => optional($sppt->requestType)->requesttype_name,
        ];

        // Kirim ke view
        $pdf = \PDF::loadView(
            'pages.sppts.pdf_sppts',
            array_merge($data, [
                'detail'         => $spptdetail,
                'approval'       => $approval,
                'approve_count'  => $approve_count,
            ])
        );

        // Portrait jika <= 5 approver, else landscape
        $pdf->setPaper('A4', ($approve_count <= 5) ? 'portrait' : 'landscape');

        return $pdf->stream("pdf_sppts_{$sppt->spptid}.pdf");
    }

    public function createBQ($id)
    {       
        $user = request()->user();     
        $sppt = TrSPPT::findOrFail($id);  
       
        $temp_id = session('import_temp_id'); // ambil dari session

        $tempData = [];
        if ($temp_id) {
            $tempData = BqDetailTemp::where('temp_id', $temp_id)->get();
        }

       
        return view('pages.sppts.createbqsppt', compact('sppt','tempData','temp_id'));
    }

    public function importCreate(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'file'    => 'required|mimes:xlsx,xls,csv',
            'sppjtid' => 'required',
        ]);

        try {
            $username = Auth::user()->username ?? 'system';
            $temp_id  = (string) Str::uuid();

            // Bersihkan temp milik user agar batch tidak tercampur
            BqDetailTemp::where('created_by', $username)->delete();

            $idx = $request->input('idx');
            $sppjtid = $request->input('sppjtid');

            // Import Excel ke tr_bq_detail_temp
            Excel::import(
                new BqDetailTempImport($temp_id, $sppjtid),
                $request->file('file')
            );

            // Simpan temp_id ke session untuk dipakai di halaman create
            session(['import_temp_id' => $temp_id]);

            // ⬇️ Selalu redirect ke create
            return redirect()
                ->route('bqsppt.create', $idx)
                ->with('success', 'Data berhasil di-import.');
        } catch (\Throwable $e) {
            // opsional: report($e);
            return back()
                ->withInput()
                ->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }


    public function importEdit(Request $request)
    {
        $request->validate([
            'file'     => 'required|mimes:xlsx,xls,csv',
            'sppjtid'  => 'required', // dari hidden input di form
            // 'bqid'   => 'nullable'  // kalau suatu saat kamu kirim bqid juga
        ]);

        try {
            $username = Auth::user()->username ?? 'system';
            $temp_id  = (string) Str::uuid();
            
            // Bersihkan temp milik user agar tidak tercampur batch sebelumnya
            BqDetailTemp::where('created_by', $username)->delete();

            $idx = $request->input('idx');
            $sppjtid = $request->input('sppjtid');
            // $bqid    = $request->input('bqid'); // opsional
            
            // Import Excel ke tr_bq_detail_temp
            Excel::import(
                new BqDetailTempImport($temp_id, $sppjtid),
                $request->file('file')
            );

            // Simpan temp_id ke session untuk dipakai di createBQ()
            session(['import_temp_id' => $temp_id]);

           return redirect()
                ->route('bqsppt.edit', $idx)
                ->with('success', 'Data berhasil di‑import (edit mode).');
            //  return $idx
            //     ? redirect()->route('bqsppt.edit', $idx)
            //                 ->with('success', 'Data berhasil di‑import (edit mode).')
            //     : redirect()->route('bqs.create')
            //                 ->with('success', 'Data berhasil di‑import.');
            
            return back()->with('success', 'Data BQ berhasil di-import.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal import: '.$e->getMessage());
        }
    }

    public function storeBQ(Request $request)
    {
        $request->validate([
            'temp_id' => 'required',
            // 'bq_type' => 'nullable|string|max:20',
        ]);

        $temp_id = $request->input('temp_id');

        // Ambil batch temp
        $tempData = BqDetailTemp::where('temp_id', $temp_id)
            ->orderBy('bq_line_no', 'asc')
            ->get();
        if ($tempData->isEmpty()) {
            return response()->json(['message' => 'Tidak ada data BQ import ditemukan!'], 422);
        }
        $tempHead  = $tempData->first();

        $dt       = Carbon::now();
        $datenow  = $dt->format('Y-m-d');
        $year     = $dt->year;
        $month    = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        $username = Auth::user()->username ?? 'system';

        // Kebutuhan header
        $doctype  = 'BQ';
        $sppjtid  = $tempHead->sppjtid ?? $request->input('sppjtid'); // string SPPTID (mis. SPPT-xxxxx)
        $bq_type  = $request->input('bq_type', 'SPPT'); // default

        // Ambil cpny_id dari SPPT (kalau kolom BQ wajib)
        $cpny_id = null;
        if ($sppjtid) {
            $sppt = TrSPPT::where('spptid', $sppjtid)
                        ->orWhere('id', $request->input('idx')) // kalau kamu kirim idx juga
                        ->first();
            $cpny_id = $sppt->cpny_id ?? $sppt->cpnyid ?? null;
        }

        // Grand total header
        $grandMat  = $tempData->sum(fn($r) => (float) ($r->total_est_material_price ?? 0));
        $grandJasa = $tempData->sum(fn($r) => (float) ($r->total_est_jasa_price ?? 0));

        DB::beginTransaction(); // kalau semua di PG, bisa pakai DB::connection('pgsql')->beginTransaction();
        try {
            // ===== Autonumber untuk BQID =====
            $autonbr = Autonbr::lockForUpdate()
                ->where('doctype', $doctype)
                ->where('year', $year)
                ->where('month', $month)
                ->where('status', 'A')
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
                $autonbr->number = $urutan;
                $autonbr->save();
            }

            $tglbln = substr($year, 2) . $month;
            $bqid   = $doctype . $tglbln . sprintf('%03d', $urutan);

            $sppt->bqid = $bqid;
            $sppt->save();

            // ===== Insert HEADER: tr_bq =====
            $bq = Bq::create([
                'bqid'                           => $bqid,
                'sppjtid'                        => $sppjtid,
                'cpny_id'                        => $cpny_id,
                'bq_type'                        => $bq_type,
                'grand_total_est_material_price' => $grandMat,
                'grand_total_est_jasa_price'     => $grandJasa,
                'status'                         => 'P',
                'created_by'                     => $username,
                'updated_by'                     => $username,
            ]);

            // ===== Insert DETAIL: tr_bq_detail =====
            $seq = 1; // nomor urut dimulai dari 1
            foreach ($tempData as $row) {
                BqDetail::create([
                    'bqid'                     => $bqid,
                    'sppjtid'                  => $row->sppjtid,
                    'bq_no'                    => $seq++,            // <<=== no urut
                    'bq_line_no'               => $row->bq_line_no,  // tetap simpan line no asli jika diperlukan
                    'bq_descr'                 => $row->bq_descr,
                    'qty'                      => $row->qty,
                    'uom'                      => $row->uom,
                    'est_material_price'       => $row->est_material_price,
                    'total_est_material_price' => $row->total_est_material_price,
                    'est_jasa_price'           => $row->est_jasa_price,
                    'total_est_jasa_price'     => $row->total_est_jasa_price,
                    'status'                   => 'P',
                    'created_by'               => $username,
                    'updated_by'               => $username,
                ]);
            }


            // ===== Hapus temp batch =====
            BqDetailTemp::where('temp_id', $temp_id)->delete();

            // ===== Attachments (optional): simpan ke /public/attachments/{year} =====
           
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    if (!$file || !$file->isValid()) continue;

                    // ambil nama asli dan extension
                    $original   = $file->getClientOriginalName();
                    $baseName   = pathinfo($original, PATHINFO_FILENAME);
                    $ext        = strtolower($file->getClientOriginalExtension());

                    // sanitasi nama untuk disimpan di kolom "name"
                    $safeName   = Str::limit(preg_replace('/[^A-Za-z0-9_\- ]/', '', $baseName), 120, '');

                    // buat nama file unik untuk disimpan di folder & kolom "attachfile"
                    $unique     = md5(uniqid('', true));
                    $storedName = $unique . '-' . ($safeName !== '' ? Str::slug($safeName, '_') : 'photo') . '.' . $ext;

                    // pastikan folder ada
                    $folder = public_path('attachments/' . $year);
                    if (!is_dir($folder)) {
                        @mkdir($folder, 0755, true);
                    }

                    // pindahkan file
                    $file->move($folder, $storedName);

                    // simpan ke DB tanpa mass assignment
                    $attach = new Attachment();
                    $attach->docid        = $bqid;      // relasi ke dokumen BQ
                    $attach->name         = $safeName;  // nama (tanpa ext)
                    $attach->attachfile   = $storedName; // nama file di server (dengan ext)
                    $attach->status       = 'A';
                    $attach->extention    = $ext;        // kolommu memang "extention"
                    $attach->created_user = $username;
                    $attach->save();
                }
            }


            DB::commit();
            return response()->json(['success' => true, 'bq' => $bq]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan BQ', 'message' => $e->getMessage()], 500);
        }
    }

    public function updateBQ(Request $request, int $id)
    {
        // temp_id boleh kosong → artinya tidak ada import baru (hanya simpan &/atau tambah lampiran)
        $request->validate([
            'temp_id' => 'nullable|string',
            'bq_type' => 'nullable|string|max:20',
            // 'attachments.*' => 'file|mimes:jpg,jpeg,png,webp,gif,bmp,svg|max:5120', // opsional validasi file
        ]);

        $username = Auth::user()->username ?? 'system';
        $now      = Carbon::now();

        $bq = Bq::findOrFail($id);
        $bqid    = $bq->bqid;                 // <-- dipertahankan (tidak generate baru)
        $sppjtid = $bq->sppjtid ?? $request->input('sppjtid');

        // Ambil temp data jika ada
        $tempId   = $request->input('temp_id');
        $tempData = collect();
        if ($tempId) {
            $tempData = BqDetailTemp::where('temp_id', $tempId)
                        ->orderBy('bq_line_no', 'asc')
                        ->get();
        }

        DB::beginTransaction();
        try {
            // ===================== HEADER =====================
            // Hitung grand total:
            //  - jika ada tempData → pakai tempData
            //  - jika tidak ada → hitung dari detail existing agar tetap konsisten
            if ($tempData->isNotEmpty()) {
                $grandMat  = $tempData->sum(fn($r) => (float) ($r->total_est_material_price ?? 0));
                $grandJasa = $tempData->sum(fn($r) => (float) ($r->total_est_jasa_price ?? 0));
            } else {
                $grandMat  = (float) BqDetail::where('bqid', $bqid)->sum('total_est_material_price');
                $grandJasa = (float) BqDetail::where('bqid', $bqid)->sum('total_est_jasa_price');
            }

            // Optional: update cpny_id dari SPPT jika ingin sinkron lagi (bisa di-skip)
            // $cpny_id = $bq->cpny_id;
            // if ($sppjtid) {
            //     $sppj    = TrSPPT::where('sppjid', $sppjtid)->first();
            //     $cpny_id = $sppj->cpny_id ?? $cpny_id;
            // }

            $bq->grand_total_est_material_price = $grandMat;
            $bq->grand_total_est_jasa_price     = $grandJasa;
            if ($request->filled('bq_type')) {
                $bq->bq_type = $request->input('bq_type');
            }
            $bq->updated_by = $username;
            $bq->updated_at = $now;
            $bq->save();

            // ===================== DETAIL (replace jika ada temp) =====================
            if ($tempData->isNotEmpty()) {
                // hapus semua detail lama bqid ini
                BqDetail::where('bqid', $bqid)->delete();

                // insert ulang dari temp (nomor urut bq_no dimulai 1)
                $seq = 1;
                foreach ($tempData as $row) {
                    BqDetail::create([
                        'bqid'                     => $bqid,
                        'sppjtid'                  => $row->sppjtid,
                        'bq_no'                    => $seq++,
                        'bq_line_no'               => $row->bq_line_no,
                        'bq_descr'                 => $row->bq_descr,
                        'qty'                      => $row->qty,
                        'uom'                      => $row->uom,
                        'est_material_price'       => $row->est_material_price,
                        'total_est_material_price' => $row->total_est_material_price,
                        'est_jasa_price'           => $row->est_jasa_price,
                        'total_est_jasa_price'     => $row->total_est_jasa_price,
                        'status'                   => 'P',
                        'created_by'               => $username,
                        'updated_by'               => $username,
                    ]);
                }

                // bersihkan temp batch setelah dipakai
                BqDetailTemp::where('temp_id', $tempId)->delete();
            }

            // ===================== ATTACHMENTS (tambahan) =====================
            if ($request->hasFile('attachments')) {
                $year = $now->year;
                $folder = public_path('attachments/' . $year);
                if (!is_dir($folder)) {
                    @mkdir($folder, 0755, true);
                }

                foreach ($request->file('attachments') as $file) {
                    if (!$file || !$file->isValid()) continue;

                    $original   = $file->getClientOriginalName();
                    $baseName   = pathinfo($original, PATHINFO_FILENAME);
                    $ext        = strtolower($file->getClientOriginalExtension());

                    $safeName   = Str::limit(preg_replace('/[^A-Za-z0-9_\- ]/', '', $baseName), 120, '');
                    $unique     = md5(uniqid('', true));
                    $storedName = $unique . '-' . ($safeName !== '' ? Str::slug($safeName, '_') : 'photo') . '.' . $ext;

                    $file->move($folder, $storedName);

                    $attach = new Attachment();
                    $attach->docid        = $bqid;
                    $attach->name         = $safeName;
                    $attach->attachfile   = $storedName;
                    $attach->status       = 'A';
                    $attach->extention    = $ext;
                    $attach->created_user = $username;
                    $attach->save();
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'bq' => $bq]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'error'   => 'Gagal mengupdate BQ',
                'message' => $e->getMessage()
            ], 500);
        }
    }








}
