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

        switch ($doc) {
            case 'SPPB':                
                $header = TrSPPB::with([
                    'requestType:requesttypeid,requesttype_name',
                    'creator:username,name',
                    'purchaser:username,name' 
                ])
                ->findOrFail($src); 
                $detail = TrSPPBdetail::where('sppbid', $header->sppbid)->get();
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
                $detail = TrSPPJdetail::where('sppjid', $header->sppjid)->get();
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
                $detail = TrSPPKdetail::where('sppkid', $header->sppkid)->get();
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
                $detail = TrSPPTdetail::where('spptid', $header->spptid)->get();
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
        dd($request->all());
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
                $det->cs_no               = $lineNo;
                $det->sppj_no             = $srcRefNo; // isi dengan nomor baris sumber apapun namanya

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
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $randomNumber = random_int(10000000, 99999999);
                    $originalName = str_replace('%', '', $file->getClientOriginalName());
                    $attachfile   = md5($randomNumber) . '-' . $originalName;

                    $folderYear = public_path('attachments/' . $year);
                    if (!is_dir($folderYear)) {
                        @mkdir($folderYear, 0777, true);
                    }
                    $file->move($folderYear, $attachfile);

                    Attachment::create([
                        'docid'        => $csid,
                        'name'         => pathinfo($originalName, PATHINFO_FILENAME),
                        'attachfile'   => $attachfile,
                        'status'       => 'A',
                        'extention'    => $file->getClientOriginalExtension(),
                        'created_user' => $username,
                    ]);
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


    




}
