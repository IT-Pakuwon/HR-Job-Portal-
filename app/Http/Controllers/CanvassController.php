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
use App\Models\TrSPPJ;
use App\Models\TrSPPJdetail;
use App\Models\TrSPPK;
use App\Models\TrSPPKdetail;
use App\Models\TrSPPT;
use App\Models\TrSPPTdetail;
use App\Models\MsLocationPG;
use App\Models\MsSubLocationPG;
use App\Models\vReceivedList;
use App\Models\vSppbjktOnProgress;
use App\Models\vCsJobs;
use App\Models\vCsRevision;
use App\Models\TrCS;
use App\Models\TrCSdetail;
use App\Models\BudgetDetail;
use Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class CanvassController extends Controller
{
    public function createCS(string $doc, string $src)
    {
        $doc = strtoupper($doc);
        abort_unless(in_array($doc, ['SPPB','SPPJ','SPPK','SPPT']), 404, 'Invalid doc type');

        $header = null;
        $detail = collect();
        $docno  = null;
        $srcLineKey = null;

        switch ($doc) {
            case 'SPPB':                
                $header = TrSPPB::with([
                    'requestType:requesttypeid,requesttype_name',
                    'creator:username,name',
                    'purchaser:username,name' 
                ])
                ->findOrFail($src); 
                $detail = TrSPPBdetail::where('sppbid', $header->sppbid)
                    ->orderBy('sppb_no', 'asc')
                    ->get();
                $attachment = Attachment::where('docid', $header->sppbid)    
                    ->where('status','A')        
                    ->get();
                $docno  = $header->sppbno ?? $header->doc_no ?? $header->sppbid;
                break;

            case 'SPPJ':                
                $header = TrSPPJ::with([
                    'requestType:requesttypeid,requesttype_name',
                    'creator:username,name',
                    'purchaser:username,name' 
                ])
                ->findOrFail($src);
                $detail = TrSPPJdetail::where('sppjid', $header->sppjid)
                    ->orderBy('sppj_no', 'asc')
                    ->get();
                $attachment = Attachment::where('docid', $header->sppjid)    
                    ->where('status','A')        
                    ->get();
                $docno  = $header->sppjno ?? $header->doc_no ?? $header->sppjid;
                break;

            case 'SPPK':
                $header = TrSPPK::with([
                    'requestType:requesttypeid,requesttype_name',
                    'creator:username,name',
                    'purchaser:username,name' 
                ])
                ->findOrFail($src);                
                $detail = TrSPPKdetail::where('sppkid', $header->sppkid)    
                    ->orderBy('sppk_no', 'asc')
                    ->get();
                $attachment = Attachment::where('docid', $header->sppkid)    
                    ->where('status','A')        
                    ->get();
                $docno  = $header->sppkno ?? $header->doc_no ?? $header->sppkid;
                break;

            case 'SPPT':                
                $header = TrSPPT::with([
                    'requestType:requesttypeid,requesttype_name',
                    'creator:username,name',
                    'purchaser:username,name' 
                ])
                ->findOrFail($src);
                $detail = TrSPPTdetail::where('spptid', $header->spptid)
                    ->orderBy('sppt_no', 'asc')
                    ->get();
                $attachment = Attachment::where('docid', $header->spptid)    
                    ->where('status','A')        
                    ->get();
                $docno  = $header->spptno ?? $header->doc_no ?? $header->spptid;
                break;
        }
     
        $items = $detail;

        return view('pages.canvass.createcs', [
            'doc'     => $doc,
            'src_id'  => $src,
            'docno'   => $docno,
            'header'  => $header,
            'attachment'  => $attachment,
            'items'   => $items,  
        ]);
    }

    public function storeCS(Request $request)
    {
        // dd($request->all());       
        // ==== Ambil input dasar dari form (hidden + payload JSON) ====
        $doc          = strtoupper($request->input('doc'));          // SPPB|SPPJ|SPPK|SPPT
        $srcId        = $request->input('src_id');                   // id sumber doc
        $sppbjktid    = $request->input('sppbjktid');                // docno ditaruh ke sini
        $cpnyId       = $request->input('cpny_id');
        $deptId       = $request->input('department_id');
        $bqid         = $request->input('bqid');
        $userPeminta  = $request->input('user_peminta');
        $csnote       = $request->input('keperluan');                // textarea #keperluan

        // Dari JS: vendors[] + details[]
        $vendors = json_decode($request->input('vendors', '[]'), true) ?: [];
        $details = json_decode($request->input('details', '[]'), true) ?: [];

        $user     = $request->user();
        $username = $user->username ?? 'system';

        $dt        = Carbon::now();
        $year      = $dt->year;
        $month     = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        $datestamp = $dt->toDateTimeString();

        $round2 = fn($n) => round((float)$n, 2);
        $safeSet = function ($model, string $table, string $column, $value) {
            if (Schema::connection('pgsql')->hasColumn($table, $column)) {
                $model->{$column} = $value;
            }
        };

        // ==== 1) Approval line check (doctype CS) ====
        $doctype = 'CS';
        $approvalCount = M_approval::where([
            ['status', '=', 'A'],
            ['aprvcpnyid', '=', $cpnyId],
            ['aprvdeptid', '=', $deptId],
            ['aprvdoctype', '=', $doctype],
        ])->count();

        if ($approvalCount === 0) {
            return response()->json([
                'message' => 'Approval line CS belum di-setup, please contact IT!',
            ], 422);
        }

        DB::connection('pgsql')->beginTransaction();
        try {
            // ==== 2) Ambil header & detail sumber (SPPB/J/K/T) ====
            $srcHeader = null;
            $srcDetails = collect();
            $srcLineKey = null; // nama kolom nomor urut detail di sumber

            switch ($doc) {
                case 'SPPB':
                    $srcHeader  = TrSPPB::with(['requestType', 'creator', 'purchaser'])->findOrFail($srcId);
                    $srcLineKey = 'sppb_no';
                    $srcDetails = TrSPPBdetail::where('sppbid', $srcHeader->sppbid)
                                ->orderBy($srcLineKey, 'asc')
                                ->get();
                    break;

                case 'SPPJ':
                    $srcHeader  = TrSPPJ::with(['requestType', 'creator', 'purchaser'])->findOrFail($srcId);
                    $srcLineKey = 'sppj_no';
                    $srcDetails = TrSPPJdetail::where('sppjid', $srcHeader->sppjid)
                                ->orderBy($srcLineKey, 'asc')
                                ->get();
                    break;

                case 'SPPK':
                    $srcHeader  = TrSPPK::with(['requestType', 'creator', 'purchaser'])->findOrFail($srcId);
                    $srcLineKey = 'sppk_no';
                    $srcDetails = TrSPPKdetail::where('sppkid', $srcHeader->sppkid)
                                ->orderBy($srcLineKey, 'asc')
                                ->get();
                    break;

                case 'SPPT':
                    $srcHeader  = TrSPPT::with(['requestType', 'creator', 'purchaser'])->findOrFail($srcId);
                    $srcLineKey = 'sppt_no';
                    $srcDetails = TrSPPTdetail::where('spptid', $srcHeader->spptid)
                                ->orderBy($srcLineKey, 'asc')
                                ->get();
                    break;

                default:
                    abort(422, 'Invalid doc type');
            }


            // Index-kan detail sumber untuk memudahkan pencocokan
            // Kunci: inventoryid|uom|inventory_descr
            $srcIndex = [];
            foreach ($srcDetails as $sd) {
                $key = strtoupper(trim(($sd->inventoryid ?? ''))) . '|' .
                    strtoupper(trim(($sd->uom ?? ''))) . '|' .
                    strtoupper(trim(($sd->inventory_descr ?? '')));
                $srcIndex[$key] = $sd;
            }

            // ==== 3) Generate autonbr CS (lock for update) ====
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

            $tglbln = substr($year, 2) . $month; // YYMM
            $csid   = $doctype . $tglbln . sprintf("%04d", $urutan);

            // ==== 4) Simpan header TrCS (lengkapi dari header sumber) ====
            $cs = new TrCS();
            $cs->setConnection('pgsql');
            $cs->csid          = $csid;
            $cs->csdate        = $dt->toDateString();
            $cs->cpny_id       = $cpnyId;
            $cs->sppbjktid     = $sppbjktid;                          // referensi SPPB/J/K/T ID
            $cs->bqid          = $bqid ?: ($srcHeader->bqid ?? null); // fallback dari sumber
            $cs->department_id = $deptId ?: ($srcHeader->department_id ?? null);
            $cs->user_peminta  = $userPeminta ?: (optional($srcHeader->creator)->name ?? null);
            $cs->csnote        = $csnote ?: null;

            // Lengkapi dari header sumber kalau kolom ada di tr_cs
            $csTable = $cs->getTable();
            $safeSet($cs, $csTable, 'budget_perpost', $srcHeader->budget_perpost ?? null);
            $safeSet($cs, $csTable, 'woid',           $srcHeader->woid           ?? null);
            $safeSet($cs, $csTable, 'spbid',          $srcHeader->spbid          ?? null);

            $cs->status     = 'P';
            $cs->created_by = $username;

            // Map maksimal 6 vendor (sudah dari view kamu urut sesuai kolom)
            for ($i = 0; $i < min(count($vendors), 6); $i++) {
                $idx = $i + 1;
                $v   = $vendors[$i];

                $safeSet($cs, $csTable, "vendorid{$idx}",      $v['vendorid']        ?? null);
                $safeSet($cs, $csTable, "vendorname{$idx}",    $v['vendorname']      ?? null);
                $safeSet($cs, $csTable, "vendoralamat{$idx}",  $v['vendoralamat']    ?? null);
                $safeSet($cs, $csTable, "vendortelp{$idx}",    $v['vendortelp']      ?? null);
                $safeSet($cs, $csTable, "vendorcp{$idx}",      $v['vendorcp']        ?? null);
                $safeSet($cs, $csTable, "vendortop{$idx}",     $v['vendortop']       ?? null);
                $safeSet($cs, $csTable, "vendornote{$idx}",    $v['vendornote']      ?? null);

                $safeSet($cs, $csTable, "totalvendor{$idx}",              $round2($v['total']          ?? 0));
                $safeSet($cs, $csTable, "taxcodevendor{$idx}",            $v['taxcode']                ?? null);
                $safeSet($cs, $csTable, "ppnvendor{$idx}",                $round2($v['ppn']            ?? 0));
                $safeSet($cs, $csTable, "pphvendor{$idx}",                $round2($v['pph']            ?? 0));
                $safeSet($cs, $csTable, "taxvendor{$idx}",                $round2($v['tax']            ?? 0));
                $safeSet($cs, $csTable, "grandtotalvendor{$idx}",         $round2($v['grand']          ?? 0));
                $safeSet($cs, $csTable, "totalselectedvendor{$idx}",      $round2($v['selected_total'] ?? 0));
                $safeSet($cs, $csTable, "taxselectedvendor{$idx}",        $round2($v['selected_tax']   ?? 0));
                $safeSet($cs, $csTable, "grandtotalselectedvendor{$idx}", $round2($v['selected_grand'] ?? 0));
            }
            $cs->save();

            // ==== 5) Simpan detail TrCSdetail (lengkapi dari detail sumber) ====
            $lineNo = 0;
            foreach ($details as $d) {
                $lineNo++;

                // Cari pasangannya di detail sumber:
                $matchKey = strtoupper(trim(($d['inventoryid'] ?? ''))) . '|' .
                            strtoupper(trim(($d['uom'] ?? ''))) . '|' .
                            strtoupper(trim(($d['inventory_descr'] ?? '')));
                $src = $srcIndex[$matchKey] ?? null;

                // fallback jika tidak ketemu cocokannya: pakai by index (aman kalau urutannya sama)
                if (!$src && isset($srcDetails[$lineNo - 1])) {
                    $src = $srcDetails[$lineNo - 1];
                }

                // Nomor urut sumber (untuk diisi ke kolom csdetail->sppj_no sebagai "ref line")
                $srcRefNo = $src ? ($src->{$srcLineKey} ?? null) : null;

                $det = new TrCSdetail();
                $det->setConnection('pgsql');
                $det->csid                = $csid;
                $det->sppbjktid = $sppbjktid;
                $det->cs_no               = $lineNo;
                $det->sppbjkt_no             = $srcRefNo; // isi dengan nomor baris sumber apapun namanya

                $det->inventoryid         = $d['inventoryid']        ?? ($src->inventoryid ?? null);
                $det->inventory_descr     = $d['inventory_descr']    ?? ($src->inventory_descr ?? null);
                $det->qty                 = $round2($d['qty']        ?? ($src->qty ?? 0));
                $det->uom                 = $d['uom']                ?? ($src->uom ?? null);

                // Lengkapi konversi UOM dari sumber bila ada
                $det->type_multiplier     = $src->type_multiplier     ?? null;
                $det->base_multiplier     = isset($src->base_multiplier) ? $round2($src->base_multiplier) : null;
                $det->base_qty            = isset($src->base_qty)        ? $round2($src->base_qty)        : null;
                $det->base_uom            = $src->base_uom ?? null;

                // Harga terakhir & note detail
                $det->inventory_last_price= isset($d['inventory_last_price']) ? $round2($d['inventory_last_price']) :
                                            (isset($src->inventory_last_price) ? $round2($src->inventory_last_price) : 0);
                $det->csnote_detail       = $d['csnote_detail']      ?? ($src->note ?? null);

                // Lokasi & budgeting (ambil dari sumber bila ada)
                $det->location_id               = $src->location_id               ?? null;
                $det->sub_location_id           = $src->sub_location_id           ?? null;
                $det->budget_perpost            = $src->budget_perpost            ?? null;
                $det->budget_cpny_id            = $cpnyId; // tetap perusahaan CS
                $det->budget_business_unit_id   = $src->budget_business_unit_id   ?? null;
                $det->budget_department_fin_id  = $src->budget_department_fin_id  ?? null;
                $det->budget_account_id         = $src->budget_account_id         ?? null;
                $det->budget_activity_id        = $src->budget_activity_id        ?? null;

                // Map harga per vendor (maks 6)
                for ($i = 0; $i < min(count($d['vendor'] ?? []), 6); $i++) {
                    $idx   = $i + 1;
                    $vrow  = $d['vendor'][$i];
                    $vid   = $vrow['vendorid'] ?? null;
                    $price = $round2($vrow['price'] ?? 0);
                    $total = $round2($vrow['total'] ?? 0);
                    $sel   = !empty($vrow['selected']);

                    $det->{"vendorid{$idx}"}         = $vid;
                    $det->{"vendorprice{$idx}"}      = $price;
                    $det->{"vendortotalprice{$idx}"} = $total;
                    $det->{"vendor{$idx}selected"}   = (bool)$sel;
                }

                $det->status      = 'P';
                $det->created_by  = $username;
                $det->save();
            }

            // ==== 5b) Update ordered/openordered pada dokumen sumber (partial allowed) ====
            // Hitung qty yang benar-benar diorder (hanya baris yg punya vendor selected)
            $addedTotalOrdered = 0.0;

            foreach ($details as $i => $d) {
                // cari apakah ada vendor yg dipilih utk baris ini
                $isSelected = false;
                foreach (($d['vendor'] ?? []) as $vrow) {
                    if (!empty($vrow['selected'])) { $isSelected = true; break; }
                }
                if (!$isSelected) continue; // baris ini belum diorder

                $orderedQty = (float) ($d['qty'] ?? 0);
                if ($orderedQty <= 0) continue;

                // Temukan detail sumber yg matching
                $matchKey = strtoupper(trim(($d['inventoryid'] ?? ''))) . '|' .
                            strtoupper(trim(($d['uom'] ?? ''))) . '|' .
                            strtoupper(trim(($d['inventory_descr'] ?? '')));
                $srcDet = $srcIndex[$matchKey] ?? ($srcDetails[$i] ?? null);
                if (!$srcDet) continue;

                $detTable = $srcDet->getTable();
                // ordered += orderedQty (jika kolom ada)
                if (Schema::connection('pgsql')->hasColumn($detTable, 'ordered')) {
                    $srcDet->ordered = (float)($srcDet->ordered ?? 0) + $orderedQty;
                }
                // openordered -= orderedQty (jika kolom ada)
                if (Schema::connection('pgsql')->hasColumn($detTable, 'openordered')) {
                    $srcDet->openordered = max(0, (float)($srcDet->openordered ?? 0) - $orderedQty);
                }
                $srcDet->save();

                $addedTotalOrdered += $orderedQty;
            }

            // Update header sumber: totalordered & totalopenordered bila tersedia
            $hdrTable = $srcHeader->getTable();
            if (Schema::connection('pgsql')->hasColumn($hdrTable, 'totalordered')) {
                $srcHeader->totalordered = (float)($srcHeader->totalordered ?? 0) + $addedTotalOrdered;
            }
            if (Schema::connection('pgsql')->hasColumn($hdrTable, 'totalopenordered')) {
                $srcHeader->totalopenordered = max(0, (float)($srcHeader->totalopenordered ?? 0) - $addedTotalOrdered);
            }
            $srcHeader->save();

            // ==== 5c) Update Budget Reserve (pakai tahun dari csdate & bulan dari csdate) ====
            // csdate sudah Anda set di atas saat buat header CS
            $csDate   = Carbon::parse($cs->csdate);
            $yearStr  = $csDate->format('Y');      // <-- perpost = YYYY
            $monthIdx = (int) $csDate->format('m'); // 1..12
            $periodColBase = 'period' . str_pad($monthIdx, 2, '0', STR_PAD_LEFT); // period05, period12, dst.
            $periodReserveCol = $periodColBase . '_reserve';

            // Agregasi nilai reserve per kombinasi budget
            $budgetBuckets = []; // key: json_encode([...]), value: total uang (float)

            // Ambil nilai total yang DIPILIH (selected) per baris detail dari payload $details
            foreach ($details as $d) {
                // cari vendor selected
                $selectedTotal = 0.0;
                if (!empty($d['vendor']) && is_array($d['vendor'])) {
                    foreach ($d['vendor'] as $vrow) {
                        if (!empty($vrow['selected'])) {
                            $selectedTotal = (float) ($vrow['total'] ?? 0);
                            break;
                        }
                    }
                }
                if ($selectedTotal <= 0) continue;

                // dimensi budget per baris → jika null, biarkan null (filter pakai when)
                $bCpny = $d['budget_cpny_id'] ?? $cpnyId;                 // fallback ke header
                $bBU   = $d['budget_business_unit_id'] ?? null;
                $bDept = $d['budget_department_fin_id'] ?? null;
                $bAcc  = $d['budget_account_id'] ?? null;
                $bAct  = $d['budget_activity_id'] ?? null;

                // perpost = TAHUN dari csdate (bukan YYYYMM)
                $key = json_encode([
                    'perpost'           => $yearStr,   // <-- hanya tahun
                    'cpny_id'           => $bCpny,
                    'business_unit_id'  => $bBU,
                    'department_fin_id' => $bDept,
                    'account_id'        => $bAcc,
                    'activity_id'       => $bAct,
                ]);

                $budgetBuckets[$key] = ($budgetBuckets[$key] ?? 0) + (float)$selectedTotal;
            }

            // Update ms_budget sekali per bucket
            foreach ($budgetBuckets as $keyJson => $amount) {
                $crit = json_decode($keyJson, true);

                // Lock baris budget yang sesuai (perpost=YYYY)
                $bd = BudgetDetail::where([
                    ['perpost', '=', $crit['perpost']],           // YYYY
                    ['cpny_id', '=', $crit['cpny_id']],
                ])
                ->when($crit['business_unit_id'],  fn($q,$v)=>$q->where('business_unit_id', $v))
                ->when($crit['department_fin_id'], fn($q,$v)=>$q->where('department_fin_id', $v))
                ->when($crit['account_id'],        fn($q,$v)=>$q->where('account_id', $v))
                ->when($crit['activity_id'],       fn($q,$v)=>$q->where('activity_id', $v))
                ->lockForUpdate()
                ->first();

                if (!$bd) {
                    // buat baris ms_budget baru jika belum ada
                    $bd = new BudgetDetail();
                    $bd->setConnection('pgsql');
                    $bd->perpost            = $crit['perpost'];  // YYYY
                    $bd->cpny_id            = $crit['cpny_id'];
                    $bd->business_unit_id   = $crit['business_unit_id'];
                    $bd->department_fin_id  = $crit['department_fin_id'];
                    $bd->account_id         = $crit['account_id'];
                    $bd->activity_id        = $crit['activity_id'];
                    $bd->status             = 'A';
                    $bd->created_by         = $username;

                    // inisialisasi kolom periodXX_*
                    for ($m = 1; $m <= 12; $m++) {
                        $p = 'period' . str_pad($m, 2, '0', STR_PAD_LEFT);
                        $bd->{$p . '_budget'}  = $bd->{$p . '_budget'}  ?? 0;
                        $bd->{$p . '_reserve'} = $bd->{$p . '_reserve'} ?? 0;
                        $bd->{$p . '_used'}    = $bd->{$p . '_used'}    ?? 0;
                    }
                }

                // Tambahkan ke kolom reserve bulan dari csdate
                $bd->{$periodReserveCol} = (float) ($bd->{$periodReserveCol} ?? 0) + (float) $amount;
                $bd->updated_by = $username;
                $bd->save();
            }



            // ==== 6) Copy line approval ke T_approval ====
            $approvals = M_approval::where([
                ['status', '=', 'A'],
                ['aprvcpnyid', '=', $cpnyId],
                ['aprvdeptid', '=', $deptId],
                ['aprvdoctype', '=', $doctype],
            ])->orderBy('aprvid')->get();

            foreach ($approvals as $a) {
                T_approval::create([
                    'docid'          => $csid,
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

            // ==== 7) Attachments (opsional) ====
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
                    $file->move($folder_upload, $attachfile);

                    //insert to table attachments
                    $attach = new Attachment();
                    $attach->docid = $csid;
                    $attach->name = $filename;
                    $attach->attachfile = $attachfile;
                    $attach->status = 'A';
                    $attach->extention = $file->getClientOriginalExtension();
                    $attach->created_user = $user->username;
                    $attach->save();
                }
            }            

            // ==== 8) Email ke approver pertama ====
            $firstApproval = T_approval::where('docid', $csid)
                ->where('status', 'P')
                ->orderBy('aprvid')
                ->first();

            if ($firstApproval) {
                $status = $cs->status; // 'P'|'R'|'D'|'A'|'C'
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
                    'createdby'=> $cs->created_by,
                    'info'     => $csnote,
                    'status'   => $status,
                    'docname'  => 'CS',
                    'url'      => url('/showcs/' . $cs->id), // sesuaikan route "show"
                ];

                $approvers = array_filter(array_map('trim', explode(',', (string)$firstApproval->aprvusername)));
                $emails = User::whereIn('username', $approvers)
                    ->where('status', 'A')
                    ->pluck('test_email');

                foreach ($emails as $email) {
                    Mail::send('emails.mailapprovenew', $data, function ($message) use ($email, $data, $subjectSuffix) {
                        $message->to($email)
                            ->subject($data['docid'] . ' - ' . $subjectSuffix . ' CS')
                            ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                    });
                }
            }

            DB::connection('pgsql')->commit();

            return response()->json([
                'message' => 'CS created successfully',
                'csid'    => $csid,
            ]);

        } catch (\Throwable $e) {
            DB::connection('pgsql')->rollBack();
            report($e);

            return response()->json([
                'message' => 'Failed to create CS',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function saveCS(Request $request)
    {
    //    dd($request->all());
        // ==== Ambil input dasar dari form (hidden + payload JSON) ====
        $doc          = strtoupper($request->input('doc'));          // SPPB|SPPJ|SPPK|SPPT
        $srcId        = $request->input('src_id');                   // id sumber doc
        $sppbjktid    = $request->input('sppbjktid');                // docno ditaruh ke sini
        $cpnyId       = $request->input('cpny_id');
        $deptId       = $request->input('department_id');
        $bqid         = $request->input('bqid');
        $userPeminta  = $request->input('user_peminta');
        $csnote       = $request->input('keperluan');                // textarea #keperluan

        // Dari JS: vendors[] + details[]
        $vendors = json_decode($request->input('vendors', '[]'), true) ?: [];
        $details = json_decode($request->input('details', '[]'), true) ?: [];

        $user     = $request->user();
        $username = $user->username ?? 'system';

        $dt        = Carbon::now();
        $year      = $dt->year;
        $month     = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        $datestamp = $dt->toDateTimeString();

        $round2 = fn($n) => round((float)$n, 2);
        $safeSet = function ($model, string $table, string $column, $value) {
            if (Schema::connection('pgsql')->hasColumn($table, $column)) {
                $model->{$column} = $value;
            }
        };

        // ==== 1) Approval line check (doctype CS) ====
        $doctype = 'CS';
        $approvalCount = M_approval::where([
            ['status', '=', 'A'],
            ['aprvcpnyid', '=', $cpnyId],
            ['aprvdeptid', '=', $deptId],
            ['aprvdoctype', '=', $doctype],
        ])->count();

        if ($approvalCount === 0) {
            return response()->json([
                'message' => 'Approval line CS belum di-setup, please contact IT!',
            ], 422);
        }

        DB::connection('pgsql')->beginTransaction();
        try {
            // ==== 2) Ambil header & detail sumber (SPPB/J/K/T) ====
            $srcHeader = null;
            $srcDetails = collect();
            $srcLineKey = null; // nama kolom nomor urut detail di sumber
            switch ($doc) {
                case 'SPPB':
                    $srcHeader = TrSPPB::with(['requestType', 'creator', 'purchaser'])->findOrFail($srcId);
                    $srcDetails = TrSPPBdetail::where('sppbid', $srcHeader->sppbid)->get();
                    $srcLineKey = 'sppb_no';
                    break;
                case 'SPPJ':
                    $srcHeader = TrSPPJ::with(['requestType', 'creator', 'purchaser'])->findOrFail($srcId);
                    $srcDetails = TrSPPJdetail::where('sppjid', $srcHeader->sppjid)->get();
                    $srcLineKey = 'sppj_no';
                    break;
                case 'SPPK':
                    $srcHeader = TrSPPK::with(['requestType', 'creator', 'purchaser'])->findOrFail($srcId);
                    $srcDetails = TrSPPKdetail::where('sppkid', $srcHeader->sppkid)->get();
                    $srcLineKey = 'sppk_no';
                    break;
                case 'SPPT':
                    $srcHeader = TrSPPT::with(['requestType', 'creator', 'purchaser'])->findOrFail($srcId);
                    $srcDetails = TrSPPTdetail::where('spptid', $srcHeader->spptid)->get();
                    $srcLineKey = 'sppt_no';
                    break;
                default:
                    abort(422, 'Invalid doc type');
            }

            // Index-kan detail sumber untuk memudahkan pencocokan
            // Kunci: inventoryid|uom|inventory_descr
            $srcIndex = [];
            foreach ($srcDetails as $sd) {
                $key = strtoupper(trim(($sd->inventoryid ?? ''))) . '|' .
                    strtoupper(trim(($sd->uom ?? ''))) . '|' .
                    strtoupper(trim(($sd->inventory_descr ?? '')));
                $srcIndex[$key] = $sd;
            }

            // ==== 3) Generate autonbr CS (lock for update) ====
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

            $tglbln = substr($year, 2) . $month; // YYMM
            $csid   = $doctype . $tglbln . sprintf("%04d", $urutan);

            // ==== 4) Simpan header TrCS (lengkapi dari header sumber) ====
            $cs = new TrCS();
            $cs->setConnection('pgsql');
            $cs->csid          = $csid;
            $cs->csdate        = $dt->toDateString();
            $cs->cpny_id       = $cpnyId;
            $cs->sppbjktid     = $sppbjktid;                          // referensi SPPB/J/K/T ID
            $cs->bqid          = $bqid ?: ($srcHeader->bqid ?? null); // fallback dari sumber
            $cs->department_id = $deptId ?: ($srcHeader->department_id ?? null);
            $cs->user_peminta  = $userPeminta ?: (optional($srcHeader->creator)->name ?? null);
            $cs->csnote        = $csnote ?: null;

            // Lengkapi dari header sumber kalau kolom ada di tr_cs
            $csTable = $cs->getTable();
            $safeSet($cs, $csTable, 'budget_perpost', $srcHeader->budget_perpost ?? null);
            $safeSet($cs, $csTable, 'woid',           $srcHeader->woid           ?? null);
            $safeSet($cs, $csTable, 'spbid',          $srcHeader->spbid          ?? null);

            $cs->status     = 'H';
            $cs->created_by = $username;

            // Map maksimal 6 vendor (sudah dari view kamu urut sesuai kolom)
            for ($i = 0; $i < min(count($vendors), 6); $i++) {
                $idx = $i + 1;
                $v   = $vendors[$i];

                $safeSet($cs, $csTable, "vendorid{$idx}",      $v['vendorid']        ?? null);
                $safeSet($cs, $csTable, "vendorname{$idx}",    $v['vendorname']      ?? null);
                $safeSet($cs, $csTable, "vendoralamat{$idx}",  $v['vendoralamat']    ?? null);
                $safeSet($cs, $csTable, "vendortelp{$idx}",    $v['vendortelp']      ?? null);
                $safeSet($cs, $csTable, "vendorcp{$idx}",      $v['vendorcp']        ?? null);
                $safeSet($cs, $csTable, "vendortop{$idx}",     $v['vendortop']       ?? null);
                $safeSet($cs, $csTable, "vendornote{$idx}",    $v['vendornote']      ?? null);

                $safeSet($cs, $csTable, "totalvendor{$idx}",              $round2($v['total']          ?? 0));
                $safeSet($cs, $csTable, "taxcodevendor{$idx}",            $v['taxcode']                ?? null);
                $safeSet($cs, $csTable, "ppnvendor{$idx}",                $round2($v['ppn']            ?? 0));
                $safeSet($cs, $csTable, "pphvendor{$idx}",                $round2($v['pph']            ?? 0));
                $safeSet($cs, $csTable, "taxvendor{$idx}",                $round2($v['tax']            ?? 0));
                $safeSet($cs, $csTable, "grandtotalvendor{$idx}",         $round2($v['grand']          ?? 0));
                $safeSet($cs, $csTable, "totalselectedvendor{$idx}",      $round2($v['selected_total'] ?? 0));
                $safeSet($cs, $csTable, "taxselectedvendor{$idx}",        $round2($v['selected_tax']   ?? 0));
                $safeSet($cs, $csTable, "grandtotalselectedvendor{$idx}", $round2($v['selected_grand'] ?? 0));
            }
            $cs->save();

            // ==== 5) Simpan detail TrCSdetail (lengkapi dari detail sumber) ====
            $lineNo = 0;
            foreach ($details as $d) {
                $lineNo++;

                // Cari pasangannya di detail sumber:
                $matchKey = strtoupper(trim(($d['inventoryid'] ?? ''))) . '|' .
                            strtoupper(trim(($d['uom'] ?? ''))) . '|' .
                            strtoupper(trim(($d['inventory_descr'] ?? '')));
                $src = $srcIndex[$matchKey] ?? null;

                // fallback jika tidak ketemu cocokannya: pakai by index (aman kalau urutannya sama)
                if (!$src && isset($srcDetails[$lineNo - 1])) {
                    $src = $srcDetails[$lineNo - 1];
                }

                // Nomor urut sumber (untuk diisi ke kolom csdetail->sppj_no sebagai "ref line")
                $srcRefNo = $src ? ($src->{$srcLineKey} ?? null) : null;

                $det = new TrCSdetail();
                $det->setConnection('pgsql');
                $det->csid                = $csid;
                $det->cs_no               = $lineNo;
                $det->sppbjkt_no             = $srcRefNo; // isi dengan nomor baris sumber apapun namanya

                $det->inventoryid         = $d['inventoryid']        ?? ($src->inventoryid ?? null);
                $det->inventory_descr     = $d['inventory_descr']    ?? ($src->inventory_descr ?? null);
                $det->qty                 = $round2($d['qty']        ?? ($src->qty ?? 0));
                $det->uom                 = $d['uom']                ?? ($src->uom ?? null);

                // Lengkapi konversi UOM dari sumber bila ada
                $det->type_multiplier     = $src->type_multiplier     ?? null;
                $det->base_multiplier     = isset($src->base_multiplier) ? $round2($src->base_multiplier) : null;
                $det->base_qty            = isset($src->base_qty)        ? $round2($src->base_qty)        : null;
                $det->base_uom            = $src->base_uom ?? null;

                // Harga terakhir & note detail
                $det->inventory_last_price= isset($d['inventory_last_price']) ? $round2($d['inventory_last_price']) :
                                            (isset($src->inventory_last_price) ? $round2($src->inventory_last_price) : 0);
                $det->csnote_detail       = $d['csnote_detail']      ?? ($src->note ?? null);

                // Lokasi & budgeting (ambil dari sumber bila ada)
                $det->location_id               = $src->location_id               ?? null;
                $det->sub_location_id           = $src->sub_location_id           ?? null;
                $det->budget_perpost            = $src->budget_perpost            ?? null;
                $det->budget_cpny_id            = $cpnyId; // tetap perusahaan CS
                $det->budget_business_unit_id   = $src->budget_business_unit_id   ?? null;
                $det->budget_department_fin_id  = $src->budget_department_fin_id  ?? null;
                $det->budget_account_id         = $src->budget_account_id         ?? null;
                $det->budget_activity_id        = $src->budget_activity_id        ?? null;

                // Map harga per vendor (maks 6)
                for ($i = 0; $i < min(count($d['vendor'] ?? []), 6); $i++) {
                    $idx   = $i + 1;
                    $vrow  = $d['vendor'][$i];
                    $vid   = $vrow['vendorid'] ?? null;
                    $price = $round2($vrow['price'] ?? 0);
                    $total = $round2($vrow['total'] ?? 0);
                    $sel   = !empty($vrow['selected']);

                    $det->{"vendorid{$idx}"}         = $vid;
                    $det->{"vendorprice{$idx}"}      = $price;
                    $det->{"vendortotalprice{$idx}"} = $total;
                    $det->{"vendor{$idx}selected"}   = (bool)$sel;
                }

                $det->status      = 'H';
                $det->created_by  = $username;
                $det->save();
            }
          
            // ==== 7) Attachments (opsional) ====
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
                    $file->move($folder_upload, $attachfile);

                    //insert to table attachments
                    $attach = new Attachment();
                    $attach->docid = $csid;
                    $attach->name = $filename;
                    $attach->attachfile = $attachfile;
                    $attach->status = 'A';
                    $attach->extention = $file->getClientOriginalExtension();
                    $attach->created_user = $user->username;
                    $attach->save();
                }
            }   
           
            DB::connection('pgsql')->commit();

            return response()->json([
                'message' => 'CS created successfully',
                'csid'    => $csid,
            ]);

        } catch (\Throwable $e) {
            DB::connection('pgsql')->rollBack();
            report($e);

            return response()->json([
                'message' => 'Failed to create CS',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function showCS($id)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $cs = TrCS::with([
            'creator:username,name',
            'updater:username,name',
            'completer:username,name',
        ])->findOrFail($id);

        $csdetail = TrCSdetail::with([
            'location:location_id,location_name',
            'subLocation:sub_location_id,sub_location_name'
        ])->where('csid', $cs->csid)
        ->orderBy('cs_no')
        ->get();

        $approval = T_approval::where('docid', $cs->csid)
            ->where('status','<>','X')
            ->orderBy('created_at')
            ->orderBy('aprvid')
            ->get();

        $attachmentCS = Attachment::where('docid', $cs->csid)
            ->where('status','A')
            ->get();

        $attachmentBJKT = Attachment::where('docid', $cs->sppbjktid)
            ->where('status','A')
            ->get();

        // =========================
        // Ambil header sumber (SPPB/J/K/T) dari 2 huruf depan sppbjktid
        // =========================
        $prefix = strtoupper(substr((string)$cs->sppbjktid, 0, 2));

        if ($prefix == 'PB') {
                $srcHeader = TrSPPB::with(['requestType', 'creator', 'purchaser'])->where('sppbid', $cs->sppbjktid)->first();
                $srcDetails = TrSPPBdetail::where('sppbid', $cs->sppbjktid)->get();
                $docid = $srcHeader ? $srcHeader->sppbid : null;
        } else if ($prefix == 'PJ') {
                $srcHeader = TrSPPJ::with(['requestType', 'creator', 'purchaser'])->where('sppjid', $cs->sppbjktid)->first();
                $srcDetails = TrSPPJdetail::where('sppjid', $cs->sppbjktid)->get();
                $docid = $srcHeader ? $srcHeader->sppjid : null;
        } else if ($prefix == 'PK') {
                $srcHeader = TrSPPK::with(['requestType', 'creator', 'purchaser'])->where('sppkid', $cs->sppbjktid)->first();
                $srcDetails = TrSPPKdetail::where('sppkid', $cs->sppbjktid)->get();
                $docid = $srcHeader ? $srcHeader->sppkid : null;
        } else if ($prefix == 'PT') {
                $srcHeader = TrSPPT::with(['requestType', 'creator', 'purchaser'])->where('spptid', $cs->sppbjktid)->first();
                $srcDetails = TrSPPTdetail::where('spptid', $cs->sppbjktid)->get();
                $docid = $srcHeader ? $srcHeader->spptid : null;
        } else {
            abort(422, 'Invalid doc type');
        }   
       
            

        // ---- susun vendor header: maksimal 6 kolom ----
        $vendors = [];
        for ($i = 1; $i <= 6; $i++) {
            $vid = $cs->{"vendorid{$i}"} ?? null;
            if (!$vid) continue;
            $vendors[] = [
                'i'              => $i,
                'vendorid'       => $vid,
                'vendorname'     => $cs->{"vendorname{$i}"}       ?? '',
                'vendoralamat'   => $cs->{"vendoralamat{$i}"}     ?? '',
                'vendortelp'     => $cs->{"vendortelp{$i}"}       ?? '',
                'vendorcp'       => $cs->{"vendorcp{$i}"}         ?? '',
                'vendortop'      => $cs->{"vendortop{$i}"}        ?? '',
                'ppn'            => (float)($cs->{"ppnvendor{$i}"} ?? 11.00),
                'pph'            => (float)($cs->{"pphvendor{$i}"} ?? 0.00),
                'total'          => (float)($cs->{"totalvendor{$i}"} ?? 0),
                'grand'          => (float)($cs->{"grandtotalvendor{$i}"} ?? 0),
                'selected_total' => (float)($cs->{"totalselectedvendor{$i}"} ?? 0),
                'selected_grand' => (float)($cs->{"grandtotalselectedvendor{$i}"} ?? 0),
                'taxcode'        => $cs->{"taxcodevendor{$i}"}    ?? '',
            ];
        }

        return view('pages.canvass.showcs', [
            'cs'         => $cs,
            'approval'   => $approval,
            'attachmentCS' => $attachmentCS,
            'attachmentBJKT' => $attachmentBJKT,
            'csdetail'   => $csdetail,
            'vendors'    => $vendors,          
            'srcHeader'     => $srcHeader,
            'docid'     => $docid,
            'prefix'    => $prefix,
        ]);
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
        $comment->doctype = 'CS';
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

    public function approveCS(Request $request, $docid)
    {
        $now  = Carbon::now();
        $user = $request->user();

        // $cs = TrCS::where('csid', $docid)->first();
        $cs = TrCS::with('creator')
            ->where('csid', $docid)
            ->first();
        $fullname = data_get($cs, 'creator.name') ?: $cs->created_by;

        if (!$cs) {
            return response()->json(['success' => false, 'message' => 'CS not found'], 404);
        }

        // pastikan user memang approver aktif (status P) di doc ini
        $tApproval = T_approval::where('docid', $cs->csid)
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
            $cs->completed_by = $user->username;
            $cs->completed_at = $now;
            $cs->save();

            // Hitung sisa pending setelah approve ini
            $pendingCount = T_approval::where('docid', $cs->csid)
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
                $cs->status       = 'C';
                $cs->completed_by = $user->username;
                $cs->completed_at = $now;
                $cs->save();

                $csdetail = TrCSdetail::where('csid', $cs->csid)                
                    ->get();

                foreach ($csdetail as $d) {
                    $d->status = 'C'; 
                    $d->save();
                }

                // Kirim email ke requester (creator)
                $status        = 'C';
                $subjectSuffix = $subjectMap[$status] ?? 'Notification';

                $data = [
                    'docid'     => $cs->csid,
                    'cpnyid'    => $cs->cpny_id ?? $cs->cpnyid ?? '',
                    'deptname'  => $cs->department_id ?? $cs->departementid ?? '',
                    'date'      => $cs->csdate,
                    'fullname'  => $fullname,  // nama penerima di email
                    'name'      => $fullname,  // fallback
                    'createdby' => $fullname,
                    'docname'   => 'CS',
                    'info'      => $cs->keperluan,
                    'status'    => $status,
                    'url'       => url('/showcs/' . $cs->id),
                ];

                $recipients = User::where('username', $cs->created_by)
                    ->where('status', 'A')
                    ->get();

                foreach ($recipients as $rcp) {
                    try {
                        Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $rcp, $subjectSuffix) {
                            $to = $rcp->test_email ?? $rcp->email; // pakai field yang memang ada
                            $message->to($to)
                                ->subject($data['docid'] . ' - ' . $subjectSuffix . ' CS')
                                ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                        });
                    } catch (\Throwable $e) {
                        Log::error('Failed sending CS completion email', ['error' => $e->getMessage()]);
                    }
                }
            } else {
                // Masih ada approver berikutnya -> cari level berikutnya (P terrendah aprvid)
                $next = T_approval::where('docid', $cs->csid)
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
                        'createdby' => $cs->created_by,
                        'docname'   => 'CS',
                        'info'      => $cs->keperluan,
                        'status'    => $status,
                        'url'       => url('/showcs/' . $cs->id),
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
                                        ->subject($data['docid'] . ' - ' . $subjectSuffix . ' CS')
                                        ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                                });
                            } catch (\Throwable $e) {
                                Log::error('Failed sending CS waiting-approval email', ['error' => $e->getMessage()]);
                            }
                        }
                    } else {
                        Log::warning('Next approver has empty aprvusername list', ['docid' => $cs->csid]);
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Task approved successfully']);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Approve CS failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Approve failed'], 500);
        }
    }
    
    public function rejectCS(Request $request, $docid)
    {
        $now  = Carbon::now();
        $user = $request->user();

        // $cs = TrCS::where('csid', $docid)->first();
        $cs = TrCS::with('creator')
            ->where('csid', $docid)
            ->first();
        $fullname = data_get($cs, 'creator.name') ?: $cs->created_by;

        if (!$cs) {
            return response()->json(['success' => false, 'message' => 'Task not found'], 404);
        }

        // Validasi: user harus approver aktif (status P) pada dokumen ini
        $tApproval = T_approval::where('docid', $cs->csid)
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

            // Update header CS
            $cs->status       = 'R';
            $cs->completed_by = $user->username;
            $cs->completed_at = $now;
            $cs->save();

            // Batalkan semua approval yang masih pending
            T_approval::where('docid', $cs->csid)
                ->where('status', 'P')
                ->update(['status' => 'X']);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Reject CS failed', ['docid' => $docid, 'error' => $e->getMessage()]);
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
            'docid'     => $cs->csid,
            'cpnyid'    => $cs->cpny_id ?? $cs->cpnyid ?? '',
            'deptname'  => $cs->department_id ?? $cs->departementid ?? '',
            'date'      => $now->toDateString(),            // bisa juga pakai $tApproval->aprvdateafter
            'fullname'  => $fullname,               // view email kita pakai $fullname
            'name'      => $fullname,               // fallback jika view pakai $name
            'createdby' => $fullname,
            'docname'   => 'CS',
            'info'      => $cs->keperluan,
            'status'    => $status,
            'url'       => url('/showcs/' . $cs->id),
        ];

        $recipients = User::where('username', $cs->created_by)
            ->where('status', 'A')
            ->get();

        foreach ($recipients as $rcp) {
            try {
                $to = $rcp->test_email ?? $rcp->email; // sesuaikan field yang tersedia
                Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
                    $message->to($to)
                        ->subject($data['docid'] . ' - ' . $subjectSuffix . ' CS')
                        ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                });
            } catch (\Throwable $e) {
                Log::error('Failed sending CS rejected email', [
                    'docid' => $data['docid'],
                    'to'    => $rcp->username,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Simpan komentar penolakan (jika ada)
        try {
            app('App\Http\Controllers\SendCommentController')
                ->sendmsg($cs->id, 'CS', $request);
        } catch (\Throwable $e) {
            Log::warning('SendComment after reject failed', [
                'docid' => $cs->csid,
                'error' => $e->getMessage()
            ]);
        }

        return response()->json(['success' => true, 'message' => 'CS rejected successfully']);
    }

    public function reviseCS(Request $request, $docid)
    {
        $now  = Carbon::now();
        $user = $request->user();

        // $cs = TrCS::where('csid', $docid)->first();
        $cs = TrCS::with('creator')
            ->where('csid', $docid)
            ->first();
        $fullname = data_get($cs, 'creator.name') ?: $cs->created_by;
            
        if (!$cs) {
            return response()->json(['success' => false, 'message' => 'CS not found'], 404);
        }

        // Pastikan user adalah approver aktif (status P) dokumen ini
        $tApproval = T_approval::where('docid', $cs->csid)
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

            // Update header CS
            $cs->status       = 'D';
            $cs->completed_by = $user->username;        // mengikuti pola existing
            $cs->completed_at = $now;
            $cs->save();

            // Batalkan approval lain yang masih pending
            T_approval::where('docid', $cs->csid)
                ->where('status', 'P')
                ->update(['status' => 'X']);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Revise CS failed', ['docid' => $docid, 'error' => $e->getMessage()]);
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
            'docid'     => $cs->csid,
            'cpnyid'    => $cs->cpny_id ?? $cs->cpnyid ?? '',
            'deptname'  => $cs->department_id ?? $cs->departementid ?? '',
            'date'      => $now->toDateString(),          // atau $tApproval->aprvdateafter
            'fullname'  => $fullname,             // template email pakai $fullname
            'name'      => $fullname,             // fallback jika view pakai $name
            'createdby' => $fullname,
            'docname'   => 'CS',
            'info'      => $cs->keperluan,
            'status'    => $status,
            'url'       => url('/showcs/' . $cs->id),
        ];

        $recipients = User::where('username', $cs->created_by)
            ->where('status', 'A')
            ->get();

        foreach ($recipients as $rcp) {
            try {
                $to = $rcp->test_email ?? $rcp->email; // sesuaikan dengan kolom yang ada
                Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
                    $message->to($to)
                        ->subject($data['docid'] . ' - ' . $subjectSuffix . ' CS')
                        ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                });
            } catch (\Throwable $e) {
                Log::error('Failed sending CS revise email', [
                    'docid' => $data['docid'],
                    'to'    => $rcp->username,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Simpan komentar revisi (jika ada)
        try {
            app('App\Http\Controllers\SendCommentController')
                ->sendmsg($cs->id, 'CS', $request);
        } catch (\Throwable $e) {
            Log::warning('SendComment after revise failed', [
                'docid' => $cs->csid,
                'error' => $e->getMessage()
            ]);
        }

        return response()->json(['success' => true, 'message' => 'CS revised successfully']);
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
        $cs = TrCS::findOrFail($id);

        $getName = function (?string $username) {
            if (!$username) return null;
            $u = \App\Models\User::where('username', $username)->first();
            return $u->name ?? $username;
        };

        $createdByName = $getName($cs->created_by ?? null);
        $createdAt     = $cs->created_at ? \Carbon\Carbon::parse($cs->created_at)->format('Y-m-d H:i') : null;

        $completedByName = $getName($cs->completed_by ?? null);
        $completedAt     = $cs->completed_at ? \Carbon\Carbon::parse($cs->completed_at)->format('Y-m-d H:i') : null;

        // kolom opsional, kalau tidak ada biarkan null
        $rejectedByName  = $getName($cs->rejected_by ?? null);
        $rejectedAt      = isset($cs->rejected_at) ? \Carbon\Carbon::parse($cs->rejected_at)->format('Y-m-d H:i') : null;

        $revisedByName   = $getName($cs->revised_by ?? null);
        $revisedAt       = isset($cs->revised_at) ? \Carbon\Carbon::parse($cs->revised_at)->format('Y-m-d H:i') : null;

        $status = (string) ($cs->status ?? '');
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
            'title'        => 'CS',
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
            'doc'   => $cs->csid ?? (string)$cs->id,
            'steps' => $steps,
            'status'=> $status,
            'status_label' => $statusLabel,
        ]);
    }

    public function printCS(int $id)
    {
        $authUser = Auth::user();
        if (!$authUser) {
            return redirect()->route('login');
        }

        // Ambil CS + relasi yang dibutuhkan
        $cs = TrCS::with([
                'requestType:requesttypeid,requesttype_name',
                'creator:username,name',
            ])
            ->findOrFail($id);

        // Detail baris CS
        $csdetail = TrCSdetail::with([
                'location:location_id,location_name',
                'subLocation:sub_location_id,sub_location_name',
            ])
            ->where('csid', $cs->csid)
            ->get();

        // Approval list (non-cancelled)
        $approval = T_approval::where('docid', $cs->csid)
            ->where('status', '<>', 'X')
            ->orderBy('aprvid')
            ->orderBy('created_at')
            ->get();

        $approve_count = $approval->count();

        // Company (handle null)
        $company = Company::where('cpnyid', $cs->cpny_id)->first();

        // Mapping status dokumen
        switch ($cs->status) {
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
            'doc_type'            => 'CS',
            'docid'               => $cs->csid,
            'department_id'       => $cs->department_id,
            'cpnyname'            => optional($company)->cpnyname,
            'parent'              => optional($company)->parent,
            'project'             => optional($company)->project,
            // identitas & tanggal
            'created_by_username' => $cs->created_by,
            'created_by_name'     => ucwords(strtolower(optional($cs->creator)->name)),
            'created_at_fmt'      => optional($cs->created_at)->format('d F Y'),
            'req_date_fmt'        => optional($cs->created_at)->format('d M Y H:i'),
            'csdate'            => \Carbon\Carbon::parse($cs->csdate)->format('d F Y'),
            // konten
            'keperluan'           => $cs->keperluan,
            'status_doc'          => $status_doc,
            'requesttype_name'    => optional($cs->requestType)->requesttype_name,
        ];

        // Kirim ke view
        $pdf = \PDF::loadView(
            'pages.cs.pdf_cs',
            array_merge($data, [
                'detail'         => $csdetail,
                'approval'       => $approval,
                'approve_count'  => $approve_count,
            ])
        );

        // Portrait jika <= 5 approver, else landscape
        $pdf->setPaper('A4', ($approve_count <= 5) ? 'portrait' : 'landscape');

        return $pdf->stream("pdf_cs_{$cs->csid}.pdf");
    }

    




}
