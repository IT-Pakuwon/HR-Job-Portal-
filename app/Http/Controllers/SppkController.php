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
use App\Models\TrSPPK;
use App\Models\TrSPPKdetail;
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

class SppkController extends Controller
{
    public function index()
    {
        $all = TrSPPK::count();
        $onProgress = TrSPPK::where('status', 'P')->count();
        $reject = TrSPPK::where('status', 'R')->count();
        $revise = TrSPPK::where('status', 'D')->count();
        $completed = TrSPPK::where('status', 'C')->count();
       
        return view('pages.sppks.sppks', compact('all', 'onProgress', 'reject', 'revise', 'completed'));
    }

    public function json(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $search = trim((string) $request->input('search.value', ''));
        $status = (string) $request->query('status', ''); // '' = all

        $baseTable = (new TrSPPK)->getTable(); // e.g. "tr_sppk"

        $columns = [
            0 => 'sppk.sppkid',
            1 => 'sppk.sppkdate',
            2 => 'sppk.cpny_id',
            3 => 'sppk.department_id',
            4 => 'rt.requesttype_name',
            5 => 'sppk.keperluan',
            6 => 'sppk.status',
        ];

        // ⬇️ default ke kolom 0 (sppk.sppkid) dan desc
        $orderIdx = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'sppk.sppkid';

        $base = TrSPPK::from($baseTable.' as sppk')
            ->leftJoin('ms_request_type as rt', function ($join) {
                $join->on('rt.requesttypeid', '=', 'sppk.requesttypeid');
            });

        if ($status !== '') {
            $base->where('sppk.status', $status);
        }

        $recordsTotal = (clone $base)->distinct('sppk.sppkid')->count('sppk.sppkid');

        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('sppk.sppkid',          'like', "%{$search}%")
                ->orWhere('sppk.cpny_id',       'like', "%{$search}%")
                ->orWhere('sppk.department_id', 'like', "%{$search}%")
                ->orWhere('rt.requesttype_name','like', "%{$search}%")
                ->orWhere('sppk.keperluan',     'like', "%{$search}%")
                ->orWhere('sppk.status',        'like', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $base)->distinct('sppk.sppkid')->count('sppk.sppkid');

        $data = $base->select(
                'sppk.id',
                'sppk.sppkid',
                'sppk.sppkdate',
                'sppk.cpny_id',
                'sppk.department_id',
                'sppk.requesttypeid',
                'rt.requesttype_name',
                'sppk.keperluan',
                'sppk.status',
                'sppk.created_by'
            )
            ->orderBy($orderCol, $orderDir)                  // ← mengikuti request, default ke sppkid desc
            ->orderBy('sppk.sppkid', 'desc')                 // ← tie-breaker agar stabil
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
    
    public function createSppk()
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
       
        return view('pages.sppks.createsppks', compact('usercpny','usercpny2','userdept','userdept2'));
    }

        
    public function storeSppk(Request $request)
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

        $doctype  = 'PK';
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
            $docid  = $doctype . $tglbln . sprintf("%04d", $urutan);
            $sppkNo = $docid;                                   // atau 'SPPK-'.$docid

            // === 1) header dulu (totalqty sementara 0) ===
            $header = new TrSPPK();
            $header->sppkid            = $docid;                // PK string
            $header->sppkdate          = $dt->toDateString();
            $header->cpny_id           = $request->input('cpnyid');
            $header->department_id     = $request->input('departementid');
            $header->requesttypeid     = $request->input('requesttypeid');
            $header->keperluan         = $request->input('keperluan');
            $header->budget_perpost    = $request->input('perpost');
            $header->no_polisi         = $request->input('no_polisi');
            $header->namakendaraan     = $request->input('namakendaraan');
            $header->pemilikkendaraan  = $request->input('pemilikkendaraan');
            $header->km_kendaraan      = $request->input('km_kendaraan');
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

                $detail = new TrSPPKdetail();
                $detail->sppkid                   = $docid;
                $detail->sppk_no                  = $i + 1;   // nomor urut detail
                $detail->inventoryid              = $invId;
                $detail->inventory_descr          = $productName;
                $detail->qty                      = $qty;
                $detail->uom                      = $uom;
                $detail->note                     = $notes[$i]   ?? null;
                $detail->inventory_type                = $item_types[$i] ?? null;
                $detail->sppk_category            = $item_categories[$i] ?? null;
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
                        'message' => 'Failed to create PK',
                        'error'   => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            } else {
                $uploadResult = null; // tidak ada attachment
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
                    'docname'  => 'SPPK',
                    'url'      => url('/showsppks/' . $eid),
                ];
                
                $approvers = array_filter(array_map('trim', explode(',', (string)$firstApproval->aprvusername)));
                $emails = User::whereIn('username', $approvers)
                    ->where('status', 'A')
                    ->pluck('test_email');

                foreach ($emails as $email) {
                    \Mail::send('emails.mailapprovenew', $data, function ($message) use ($email, $data) {
                        $message->to($email)
                            ->subject($data['docid'].' - Waiting Approval SPPK')
                            ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                    });
                }
            }

            DB::commit();

            return response()->json([
                'message'  => 'SPPK created successfully',
                'sppkid'   => $docid,
                'sppk_no'  => $sppkNo,
                'totalqty' => $totalQty,
                'attachments' => $uploadResult,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'message' => 'Failed to create SPPK',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
   
    public function editSppk($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $sppk = TrSPPK::findOrFail($id);

        // Ambil detail + eager load relasi lokasi & sublokasi
        $sppkdetail = TrSPPKdetail::with([
                'location:location_id,location_name',
                'subLocation:sub_location_id,sub_location_name',
            ])
            ->where('sppkid', $sppk->sppkid)
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

        // $attachment = Attachment::where('docid', $sppk->sppkid)
        //     ->where('status', 'A')
        //     ->get();

        $rows = TrAttachment::where('refnbr', $sppk->sppkid)
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

        return view('pages.sppks.editsppks', compact(
            'sppk','sppkdetail','usercpny','usercpny2','userdept','userdept2','attachments','hash'
        ));
    }



    public function updateSppk(Request $request, $hash)
    {
        // dd($request->all()); // matikan agar eksekusi lanjut

        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404, 'PK tidak ditemukan.');
        
        $user      = $request->user();   
        $dt        = Carbon::now();
        $year      = $dt->year;
        $month     = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        $datestamp = $dt->toDateTimeString();   
        $doctype   = 'PK';
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

        $header = TrSPPK::findOrFail($id);
        // update header
        $header->cpny_id        = $request->cpnyid;
        $header->department_id  = $request->departementid;
        $header->requesttypeid  = $request->requesttypeid;
        $header->no_polisi      = $request->no_polisi;
        $header->namakendaraan      = $request->namakendaraan;
        $header->pemilikkendaraan      = $request->pemilikkendaraan;
        $header->km_kendaraan      = $request->km_kendaraan;
        $header->keperluan      = $request->keperluan;
        $header->budget_perpost = $request->perpost;  
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
                if ($idsToDelete) TrSPPKdetail::whereIn('id', $idsToDelete)->delete();
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
                    'sppk_category'            => $itemCats[$i] ?? null,

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
                    $detail = TrSPPKdetail::where('id', $idDetail)
                        ->where('sppkid', $header->sppkid)
                        ->first();
                    if ($detail) {
                        $detail->fill($data)->save();
                    } else {
                        $detail = new TrSPPKdetail($data);
                        $detail->sppkid = $header->sppkid;
                        $detail->save();
                    }
                } else {
                    $detail = new TrSPPKdetail($data);
                    $detail->sppkid = $header->sppkid;
                    $detail->save();
                }

                $savedDetails[] = $detail->id;
            }

            // Renumber sppk_no 1..N
            $n = 1;
            foreach ($savedDetails as $did) {
                TrSPPKdetail::where('id', $did)->update(['sppk_no' => $n++]);
            }

            // Hitung total qty (kalau mau pakai base_qty, ganti ke sum('base_qty'))
            $totalQty = TrSPPKdetail::where('sppkid', $header->sppkid)->sum('qty');
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
                    'docid'          => $header->sppkid,
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
            //         $attach->docid = $header->sppkid;
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
                    'refnbr'        => $header->sppkid,
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
                        'message' => 'Failed to update PK',
                        'error'   => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            }

            // email approver pertama (tetap)
            $firstApproval = T_approval::where('docid', $header->sppkid)
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
                    'docname'  => 'SPPK',
                    'url'      => url('/showsppks/' . $eid),
                ];

                $approvers = array_filter(array_map('trim', explode(',', (string)$firstApproval->aprvusername)));
                $emails = User::whereIn('username', $approvers)
                    ->where('status', 'A')
                    ->pluck('test_email');

                foreach ($emails as $email) {
                    \Mail::send('emails.mailapprovenew', $data, function ($message) use ($email, $data) {
                        $message->to($email)
                            ->subject($data['docid'].' - Waiting Approval SPPK')
                            ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                    });
                }
            }

            DB::commit();
            return response()->json(['message' => 'SPPK updated successfully']);

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
 

    public function showSppk($hash)
    {        
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();       

        if (!$user) {
            return redirect()->route('login');
        }

        // $sppk = TrSPPK::findOrFail($id);
        $sppk = TrSPPK::with([
            'requestType:requesttypeid,requesttype_name',
            'creator:username,name'
        ])
        ->findOrFail($id);        

        $sppkdetail = TrSPPKdetail::with([
            'location:location_id,location_name',
            'subLocation:sub_location_id,sub_location_name'
        ])
        ->where('sppkid', $sppk->sppkid)
        ->get();
        
        $approval = T_approval::where('docid', $sppk->sppkid)
            ->where('status','<>','X')      
            ->orderBy('created_at')
            ->orderBy('aprvid')      
            ->get();
       
        // $attachment = Attachment::where('docid', $sppk->sppkid)    
        //     ->where('status','A')        
        //     ->get();     
        
        // ---------- ambil lampiran dari tr_attachment ----------
        $rows = TrAttachment::where('refnbr', $sppk->sppkid)
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
        
       
        return view('pages.sppks.showsppks', compact('sppk','approval','attachments','sppkdetail','hash'));
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
        $comment->doctype = 'PK';
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

    public function approveSppk(Request $request, $docid)
    {
        $now  = Carbon::now();
        $user = $request->user();

        // $sppk = TrSPPK::where('sppkid', $docid)->first();
        $sppk = TrSPPK::with('creator')
            ->where('sppkid', $docid)
            ->first();
        $fullname = data_get($sppk, 'creator.name') ?: $sppk->created_by;

        if (!$sppk) {
            return response()->json(['success' => false, 'message' => 'SPPK not found'], 404);
        }

        // pastikan user memang approver aktif (status P) di doc ini
        $tApproval = T_approval::where('docid', $sppk->sppkid)
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
            $sppk->completed_by = $user->username;
            $sppk->completed_at = $now;
            $sppk->save();

            // Hitung sisa pending setelah approve ini
            $pendingCount = T_approval::where('docid', $sppk->sppkid)
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

            $eid = Hashids::encode($sppk->id);

            if ($pendingCount === 0) {
                // Tidak ada approver lagi -> dokumen complete
                $sppk->status       = 'C';
                $sppk->completed_by = $user->username;
                $sppk->completed_at = $now;
                $sppk->save();

                $sppkdetail = TrSPPKdetail::where('sppkid', $sppk->sppkid)                
                    ->get();

                foreach ($sppkdetail as $d) {
                    $d->status = 'C'; 
                    $d->save();
                }

                // Kirim email ke requester (creator)
                $status        = 'C';
                $subjectSuffix = $subjectMap[$status] ?? 'Notification';

                $data = [
                    'docid'     => $sppk->sppkid,
                    'cpnyid'    => $sppk->cpny_id ?? $sppk->cpnyid ?? '',
                    'deptname'  => $sppk->department_id ?? $sppk->departementid ?? '',
                    'date'      => $sppk->sppkdate,
                    'fullname'  => $fullname,  // nama penerima di email
                    'name'      => $fullname,  // fallback
                    'createdby' => $fullname,
                    'docname'   => 'SPPK',
                    'info'      => $sppk->keperluan,
                    'status'    => $status,
                    'url'       => url('/showsppks/' . $eid),
                ];

                $recipients = User::where('username', $sppk->created_by)
                    ->where('status', 'A')
                    ->get();

                foreach ($recipients as $rcp) {
                    try {
                        Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $rcp, $subjectSuffix) {
                            $to = $rcp->test_email ?? $rcp->email; // pakai field yang memang ada
                            $message->to($to)
                                ->subject($data['docid'] . ' - ' . $subjectSuffix . ' SPPK')
                                ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                        });
                    } catch (\Throwable $e) {
                        Log::error('Failed sending SPPK completion email', ['error' => $e->getMessage()]);
                    }
                }
            } else {
                // Masih ada approver berikutnya -> cari level berikutnya (P terrendah aprvid)
                $next = T_approval::where('docid', $sppk->sppkid)
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
                        'createdby' => $sppk->created_by,
                        'docname'   => 'SPPK',
                        'info'      => $sppk->keperluan,
                        'status'    => $status,
                        'url'       => url('/showsppks/' . $eid),
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
                                        ->subject($data['docid'] . ' - ' . $subjectSuffix . ' SPPK')
                                        ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                                });
                            } catch (\Throwable $e) {
                                Log::error('Failed sending SPPK waiting-approval email', ['error' => $e->getMessage()]);
                            }
                        }
                    } else {
                        Log::warning('Next approver has empty aprvusername list', ['docid' => $sppk->sppkid]);
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Task approved successfully']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Approve SPPK failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Approve failed'], 500);
        }
    }
    
    public function rejectSppk(Request $request, $docid)
    {
        $now  = Carbon::now();
        $user = $request->user();

        // $sppk = TrSPPK::where('sppkid', $docid)->first();
        $sppk = TrSPPK::with('creator')
            ->where('sppkid', $docid)
            ->first();
        $fullname = data_get($sppk, 'creator.name') ?: $sppk->created_by;

        if (!$sppk) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        // Validasi: user harus approver aktif (status P) pada dokumen ini
        $tApproval = T_approval::where('docid', $sppk->sppkid)
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

            // Update header SPPK
            $sppk->status       = 'R';
            $sppk->completed_by = $user->username;
            $sppk->completed_at = $now;
            $sppk->save();

            // Batalkan semua approval yang masih pending
            T_approval::where('docid', $sppk->sppkid)
                ->where('status', 'P')
                ->update(['status' => 'X']);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Reject SPPK failed', ['docid' => $docid, 'error' => $e->getMessage()]);
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

        $eid = Hashids::encode($sppk->id);

        $data = [
            'docid'     => $sppk->sppkid,
            'cpnyid'    => $sppk->cpny_id ?? $sppk->cpnyid ?? '',
            'deptname'  => $sppk->department_id ?? $sppk->departementid ?? '',
            'date'      => $now->toDateString(),            // bisa juga pakai $tApproval->aprvdateafter
            'fullname'  => $fullname,               // view email kita pakai $fullname
            'name'      => $fullname,               // fallback jika view pakai $name
            'createdby' => $fullname,
            'docname'   => 'SPPK',
            'info'      => $sppk->keperluan,
            'status'    => $status,
            'url'       => url('/showsppks/' . $eid),
        ];

        $recipients = User::where('username', $sppk->created_by)
            ->where('status', 'A')
            ->get();

        foreach ($recipients as $rcp) {
            try {
                $to = $rcp->test_email ?? $rcp->email; // sesuaikan field yang tersedia
                Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
                    $message->to($to)
                        ->subject($data['docid'] . ' - ' . $subjectSuffix . ' SPPK')
                        ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                });
            } catch (\Throwable $e) {
                Log::error('Failed sending SPPK rejected email', [
                    'docid' => $data['docid'],
                    'to'    => $rcp->username,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Simpan komentar penolakan (jika ada)
        try {
            app('App\Http\Controllers\SendCommentController')
                ->sendmsg($sppk->id, 'PK', $request);
        } catch (\Throwable $e) {
            Log::warning('SendComment after reject failed', [
                'docid' => $sppk->sppkid,
                'error' => $e->getMessage()
            ]);
        }

        return response()->json(['success' => true, 'message' => 'SPPK rejected successfully']);
    }

    public function reviseSppk(Request $request, $docid)
    {
        $now  = Carbon::now();
        $user = $request->user();

        // $sppk = TrSPPK::where('sppkid', $docid)->first();
        $sppk = TrSPPK::with('creator')
            ->where('sppkid', $docid)
            ->first();
        $fullname = data_get($sppk, 'creator.name') ?: $sppk->created_by;
            
        if (!$sppk) {
            return response()->json(['success' => false, 'message' => 'SPPK not found'], 404);
        }

        // Pastikan user adalah approver aktif (status P) dokumen ini
        $tApproval = T_approval::where('docid', $sppk->sppkid)
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

            // Update header SPPK
            $sppk->status       = 'D';
            $sppk->completed_by = $user->username;        // mengikuti pola existing
            $sppk->completed_at = $now;
            $sppk->save();

            // Batalkan approval lain yang masih pending
            T_approval::where('docid', $sppk->sppkid)
                ->where('status', 'P')
                ->update(['status' => 'X']);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Revise SPPK failed', ['docid' => $docid, 'error' => $e->getMessage()]);
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

        $eid = Hashids::encode($sppk->id);

        $data = [
            'docid'     => $sppk->sppkid,
            'cpnyid'    => $sppk->cpny_id ?? $sppk->cpnyid ?? '',
            'deptname'  => $sppk->department_id ?? $sppk->departementid ?? '',
            'date'      => $now->toDateString(),          // atau $tApproval->aprvdateafter
            'fullname'  => $fullname,             // template email pakai $fullname
            'name'      => $fullname,             // fallback jika view pakai $name
            'createdby' => $fullname,
            'docname'   => 'SPPK',
            'info'      => $sppk->keperluan,
            'status'    => $status,
            'url'       => url('/showsppks/' . $eid),
        ];

        $recipients = User::where('username', $sppk->created_by)
            ->where('status', 'A')
            ->get();

        foreach ($recipients as $rcp) {
            try {
                $to = $rcp->test_email ?? $rcp->email; // sesuaikan dengan kolom yang ada
                Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
                    $message->to($to)
                        ->subject($data['docid'] . ' - ' . $subjectSuffix . ' SPPK')
                        ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                });
            } catch (\Throwable $e) {
                Log::error('Failed sending SPPK revise email', [
                    'docid' => $data['docid'],
                    'to'    => $rcp->username,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Simpan komentar revisi (jika ada)
        try {
            app('App\Http\Controllers\SendCommentController')
                ->sendmsg($sppk->id, 'PK', $request);
        } catch (\Throwable $e) {
            Log::warning('SendComment after revise failed', [
                'docid' => $sppk->sppkid,
                'error' => $e->getMessage()
            ]);
        }

        return response()->json(['success' => true, 'message' => 'SPPK revised successfully']);
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
        $sppk = TrSPPK::findOrFail($id);

        $getName = function (?string $username) {
            if (!$username) return null;
            $u = \App\Models\User::where('username', $username)->first();
            return $u->name ?? $username;
        };

        $createdByName = $getName($sppk->created_by ?? null);
        $createdAt     = $sppk->created_at ? \Carbon\Carbon::parse($sppk->created_at)->format('Y-m-d H:i') : null;

        $completedByName = $getName($sppk->completed_by ?? null);
        $completedAt     = $sppk->completed_at ? \Carbon\Carbon::parse($sppk->completed_at)->format('Y-m-d H:i') : null;

        // kolom opsional, kalau tidak ada biarkan null
        $rejectedByName  = $getName($sppk->rejected_by ?? null);
        $rejectedAt      = isset($sppk->rejected_at) ? \Carbon\Carbon::parse($sppk->rejected_at)->format('Y-m-d H:i') : null;

        $revisedByName   = $getName($sppk->revised_by ?? null);
        $revisedAt       = isset($sppk->revised_at) ? \Carbon\Carbon::parse($sppk->revised_at)->format('Y-m-d H:i') : null;

        $status = (string) ($sppk->status ?? '');
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
            'title'        => 'SPPK',
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
            'doc'   => $sppk->sppkid ?? (string)$sppk->id,
            'steps' => $steps,
            'status'=> $status,
            'status_label' => $statusLabel,
        ]);
    }

    public function printSppk($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $authUser = Auth::user();
        if (!$authUser) {
            return redirect()->route('login');
        }

        // Ambil SPPK + relasi yang dibutuhkan
        $sppk = TrSPPK::with([
                'requestType:requesttypeid,requesttype_name',
                'creator:username,name',
            ])
            ->findOrFail($id);

        // Detail baris SPPK
        $sppkdetail = TrSPPKdetail::with([
                'location:location_id,location_name',
                'subLocation:sub_location_id,sub_location_name',
            ])
            ->where('sppkid', $sppk->sppkid)
            ->get();

        // Approval list (non-cancelled)
        $approval = T_approval::where('docid', $sppk->sppkid)
            ->where('status', '<>', 'X')
            ->orderBy('aprvid')
            ->orderBy('created_at')
            ->get();

        $approve_count = $approval->count();

        // Company (handle null)
        $company = Company::where('cpnyid', $sppk->cpny_id)->first();

        // Mapping status dokumen
        switch ($sppk->status) {
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
            'title'               => 'Surat Permintaan Perbaikan Kendaraan',
            'doc_type'            => 'SPPK',
            'docid'               => $sppk->sppkid,
            'department_id'       => $sppk->department_id,
            'cpnyname'            => optional($company)->cpnyname,
            'parent'              => optional($company)->parent,
            'project'             => optional($company)->project,
            // identitas & tanggal
            'created_by_username' => $sppk->created_by,
            'created_by_name'     => ucwords(strtolower(optional($sppk->creator)->name)),
            'created_at_fmt'      => optional($sppk->created_at)->format('d F Y'),
            'req_date_fmt'        => optional($sppk->created_at)->format('d M Y H:i'),
            'sppkdate'            => \Carbon\Carbon::parse($sppk->sppkdate)->format('d F Y'),
            // konten
            'no_polisi'           => $sppk->no_polisi,
            'namakendaraan'       => $sppk->namakendaraan,
            'pemilikkendaraan'    => $sppk->pemilikkendaraan,
            'km_kendaraan'        => number_format($sppk->km_kendaraan, 0, ',', '.'),
            'keperluan'           => $sppk->keperluan,
            'status_doc'          => $status_doc,
            'requesttype_name'    => optional($sppk->requestType)->requesttype_name,
        ];

        // Kirim ke view
        $pdf = \PDF::loadView(
            'pages.sppks.pdf_sppks',
            array_merge($data, [
                'detail'         => $sppkdetail,
                'approval'       => $approval,
                'approve_count'  => $approve_count,
            ])
        );

        // Portrait jika <= 5 approver, else landscape
        $pdf->setPaper('A4', ($approve_count <= 5) ? 'portrait' : 'landscape');

        return $pdf->stream("pdf_sppks_{$sppk->sppkid}.pdf");
    }





    






}
