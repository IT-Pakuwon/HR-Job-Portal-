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
use App\Models\vAssignList;
use App\Models\vSppbjktOnProgress;
use App\Models\vCsJobs;
use App\Models\vCsRevision;
use App\Models\TrCS;
use App\Models\TrCSdetail;
use App\Models\BudgetDetail;
use App\Models\TrBudget;
use Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Vinkla\Hashids\Facades\Hashids;
use App\Models\TrPO;
use App\Models\TrPOdetail;
use App\Http\Controllers\TrAttachmentController;
use Illuminate\Support\Facades\Response;
use App\Models\TrAttachment;
use Google\Cloud\Storage\StorageClient;
use App\Http\Controllers\ApprovalController;
use App\Models\TrApproval;   
use App\Models\MsPurchSetting; 
use App\Models\TrIMBudget;
use App\Http\Controllers\IMBudgetController; 
use App\Models\TrBQCS;
use App\Models\TrBQCSDetail;
use App\Models\MsTop;


class CanvassController extends Controller
{


    public function createCS(string $doc, string $hash)
    {
        $src = \Hashids::decode($hash)[0] ?? null;
        abort_if(!$src, 404);

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
                ])->findOrFail($src);
                $detail = TrSPPBdetail::where('sppbid', $header->sppbid)
                            ->orderBy('sppb_no','asc')->get();
                $refnbr = $header->sppbid;          // <-- pakai sebagai refnbr di TrAttachment
                $docno  = $header->sppbno ?? $header->doc_no ?? $header->sppbid;
                $top_type = 'PO';
                break;

            case 'SPPJ':
                $header = TrSPPJ::with([
                    'requestType:requesttypeid,requesttype_name',
                    'creator:username,name',
                    'purchaser:username,name'
                ])->findOrFail($src);
                $detail = TrSPPJdetail::where('sppjid', $header->sppjid)
                            ->orderBy('sppj_no','asc')->get();
                $refnbr = $header->sppjid;
                $docno  = $header->sppjno ?? $header->doc_no ?? $header->sppjid;
                $top_type = 'SPK';
                break;

            case 'SPPK':
                $header = TrSPPK::with([
                    'requestType:requesttypeid,requesttype_name',
                    'creator:username,name',
                    'purchaser:username,name'
                ])->findOrFail($src);
                $detail = TrSPPKdetail::where('sppkid', $header->sppkid)
                            ->orderBy('sppk_no','asc')->get();
                $refnbr = $header->sppkid;
                $docno  = $header->sppkno ?? $header->doc_no ?? $header->sppkid;
                $top_type = 'SPK';
                break;

            case 'SPPT':
                $header = TrSPPT::with([
                    'requestType:requesttypeid,requesttype_name',
                    'creator:username,name',
                    'purchaser:username,name'
                ])->findOrFail($src);
                $detail = TrSPPTdetail::where('spptid', $header->spptid)
                            ->orderBy('sppt_no','asc')->get();
                $refnbr = $header->spptid;
                $docno  = $header->spptno ?? $header->doc_no ?? $header->spptid;
                $top_type = 'SPK';
                break;
        }

        // ===== Ambil lampiran dari TrAttachment (berdasarkan refnbr) =====
        $rows = TrAttachment::where('refnbr', $refnbr)
            ->where('status', 'A')
            ->orderBy('created_at', 'desc')
            ->get();

        // ===== Siapkan Signed URL dari GCS (private) =====
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

        // Map ke bentuk siap pakai di view "createcs"
        $attachments = $rows->map(function ($r) use ($bucket) {
            $objectPath = rtrim($r->folder, '/').'/'.$r->filename; // contoh: att-purchasing-app/wo/2025/xxx.pdf
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
                'url'          => $signedUrl, // bisa null jika gagal
                'folder'       => $r->folder,
                'filename'     => $r->filename,
                'extention'    => $r->extention,
                'size'         => $r->filesize,
            ];
        });

        // Map detail (logika kamu sendiri)
        $items = $this->mapRemainingLines($detail);

        $tops = MsTop::where('status','A')
            ->where('top_type',$top_type) 
            ->orderByRaw('COALESCE(top_days, 9999), top_name') 
            ->get(['topid','top_name','top_days','top_type']);

        return view('pages.canvass.createcs', [
            'doc'        => $doc,
            'src_id'     => $src,
            'docno'      => $docno,
            'header'     => $header,
            'attachment' => $attachments, // tetap pakai key 'attachment' agar Blade lama aman
            'items'      => $items,
            'tops'       => $tops,
        ]);
    }


    private function mapRemainingLines($detail)
    {
        return $detail->map(function ($row) {
            // Cast angka
            $qty       = (float) ($row->qty ?? 0);
            $ordered   = (float) ($row->ordered ?? 0);
            $rejected  = (float) ($row->rejectordered ?? 0);
            $completed = (float) ($row->completeordered ?? 0);

            // Jika kolom openordered sudah ada & valid → pakai itu.
            // Jika tidak, hitung manual dari komponen yang tersedia.
            if (isset($row->openordered) && $row->openordered !== null) {
                $remaining = (float) $row->openordered;
            } else {
                $remaining = max($qty - $ordered - $rejected - $completed, 0);
            }

            // Skip nanti bila remaining <= 0
            $row->qty = $remaining;

            // Sinkronkan base_qty jika ada base_multiplier
            if (isset($row->base_multiplier) && is_numeric($row->base_multiplier)) {
                $row->base_qty = round($remaining * (float) $row->base_multiplier, 3);
            }

            return $row;
        })->filter(function ($row) {
            return (float) $row->qty > 0;
        })->values();
    }

    public function storeCS(Request $request)
    {
        
        // ==== Ambil input dasar dari form ====
        $doc          = strtoupper($request->input('doc'));          // SPPB|SPPJ|SPPK|SPPT
        $srcId        = $request->input('src_id');                   // id sumber doc (numeric PK table sumber)
        $sppbjktid    = $request->input('sppbjktid');                // nomor doc sumber yg ditaruh di field ini
        $cpnyId       = $request->input('cpny_id');
        $deptId       = $request->input('department_id');
        $bqid         = $request->input('bqid');
        $userPeminta  = $request->input('user_peminta');
        $csnote       = $request->input('csnote');
        $assigndate   = $request->input('assigndate');

        // Dari JS: vendors[] + details[]
        $vendors = json_decode($request->input('vendors', '[]'), true) ?: [];
        $details = json_decode($request->input('details', '[]'), true) ?: [];

        $user     = $request->user();
        $username = $user->username ?? 'system';

        $dt      = \Carbon\Carbon::now();
        $year    = $dt->year;
        $month   = str_pad($dt->month, 2, '0', STR_PAD_LEFT);

        $round2 = fn($n) => round((float)$n, 2);
        $safeSet = function ($model, string $table, string $column, $value) {
            if (\Illuminate\Support\Facades\Schema::connection('pgsql')->hasColumn($table, $column)) {
                $model->{$column} = $value;
            }
        };

        // ==== Approval (CS hanya cek NOMINAL) ====
        $doctype       = 'CS';
        $approvalCtl   = app(\App\Http\Controllers\ApprovalController::class);

        // Validasi line approval tersedia
        $approvalCtl->loadLines($doctype, $cpnyId, $deptId);

        \DB::connection('pgsql')->beginTransaction();
        try {
            // ==== 1) Ambil header & detail sumber (SPPB/J/K/T) ====
            $srcHeader  = null;
            $srcDetails = collect();
            $srcLineKey = null; // nama kolom nomor urut detail di sumber

            switch ($doc) {
                case 'SPPB':
                    $srcHeader  = \App\Models\TrSPPB::with(['requestType', 'creator', 'purchaser'])->findOrFail($srcId);
                    $srcLineKey = 'sppb_no';
                    $srcDetails = \App\Models\TrSPPBdetail::where('sppbid', $srcHeader->sppbid)->orderBy($srcLineKey)->get();
                    break;
                case 'SPPJ':
                    $srcHeader  = \App\Models\TrSPPJ::with(['requestType', 'creator', 'purchaser'])->findOrFail($srcId);
                    $srcLineKey = 'sppj_no';
                    $srcDetails = \App\Models\TrSPPJdetail::where('sppjid', $srcHeader->sppjid)->orderBy($srcLineKey)->get();
                    break;
                case 'SPPK':
                    $srcHeader  = \App\Models\TrSPPK::with(['requestType', 'creator', 'purchaser'])->findOrFail($srcId);
                    $srcLineKey = 'sppk_no';
                    $srcDetails = \App\Models\TrSPPKdetail::where('sppkid', $srcHeader->sppkid)->orderBy($srcLineKey)->get();
                    break;
                case 'SPPT':
                    $srcHeader  = \App\Models\TrSPPT::with(['requestType', 'creator', 'purchaser'])->findOrFail($srcId);
                    $srcLineKey = 'sppt_no';
                    $srcDetails = \App\Models\TrSPPTdetail::where('spptid', $srcHeader->spptid)->orderBy($srcLineKey)->get();
                    break;
                default:
                    abort(422, 'Invalid doc type');
            }

            // Index-kan detail sumber untuk matching
            $srcIndex = [];
            foreach ($srcDetails as $sd) {
                $key = strtoupper(trim(($sd->inventoryid ?? ''))) . '|' .
                    strtoupper(trim(($sd->uom ?? ''))) . '|' .
                    strtoupper(trim(($sd->inventory_descr ?? '')));
                $srcIndex[$key] = $sd;
            }

            // ==== 2) Generate autonbr CS (lock) ====
            $autonbr = \App\Models\Autonbr::lockForUpdate()
                ->where('doctype', $doctype)
                ->where('year',   $year)
                ->where('month',  $month)
                ->first();

            if (!$autonbr) {
                $autonbr = \App\Models\Autonbr::create([
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

            // ==== 3) Simpan header TrCS ====
            $cs = new \App\Models\TrCS();
            $cs->setConnection('pgsql');
            $cs->csid          = $csid;
            $cs->csdate        = $dt->toDateString();
            $cs->cpny_id       = $cpnyId;
            $cs->sppbjktid     = $sppbjktid;                          // referensi dok sumber
            $cs->bqid          = $bqid ?: ($srcHeader->bqid ?? null);
            $cs->department_id = $deptId ?: ($srcHeader->department_id ?? null);
            $cs->user_peminta  = $userPeminta ?: (optional($srcHeader->creator)->name ?? null);
            $cs->csnote        = $csnote ?: null;
            $cs->assigndate    = $assigndate ?: null;
            $cs->submitdate    = $dt;
            $cs->status        = 'P';
            $cs->created_by    = $username;

            // Lengkapi dari header sumber bila kolomnya ada
            $csTable = $cs->getTable();
            $safeSet($cs, $csTable, 'budget_perpost', $srcHeader->budget_perpost ?? null);
            $safeSet($cs, $csTable, 'woid',           $srcHeader->woid           ?? null);
            $safeSet($cs, $csTable, 'spbid',          $srcHeader->spbid          ?? null);

            // Map maksimal 6 vendor untuk header (basic info & total vendor versi "display")
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

                // angka ini opsional dari UI; nanti kita hitung ulang dari detail di bawah (selectedByVendor)
                $safeSet($cs, $csTable, "totalvendor{$idx}",              $round2($v['total']          ?? 0));
                $safeSet($cs, $csTable, "taxcodevendor{$idx}",            $v['taxcode']                ?? null);
                $safeSet($cs, $csTable, "ppnvendor{$idx}",                $round2($v['ppn']            ?? 0));
                $safeSet($cs, $csTable, "pphvendor{$idx}",                $round2($v['pph']            ?? 0));
                $safeSet($cs, $csTable, "taxvendor{$idx}",                $round2($v['tax']            ?? 0));
                $safeSet($cs, $csTable, "grandtotalvendor{$idx}",         $round2($v['grand']          ?? 0));

                // kolom “selected” akan DI-ISI ULANG dari akumulasi detail
                $safeSet($cs, $csTable, "totalselectedvendor{$idx}",      0);
                $safeSet($cs, $csTable, "taxselectedvendor{$idx}",        0);
                $safeSet($cs, $csTable, "grandtotalselectedvendor{$idx}", 0);
            }

            $cs->save();

            // ==== 4) Simpan detail TrCSdetail & hitung grand total selected (untuk approval nominal) ====
            $lineNo           = 0;
            $docSelectedGrand = 0.0; // untuk approval nominal
            // akumulator per vendor slot (1..6) – diisi dari detail yg selected
            $selectedByVendor = [];
            for ($i = 1; $i <= 6; $i++) {
                $selectedByVendor[$i] = ['total' => 0.0, 'tax' => 0.0, 'grand' => 0.0];
            }

            foreach ($details as $d) {
                $lineNo++;

                // Cari pasangan di detail sumber (exact match) → fallback by index
                $matchKey = strtoupper(trim(($d['inventoryid'] ?? ''))) . '|' .
                            strtoupper(trim(($d['uom'] ?? ''))) . '|' .
                            strtoupper(trim(($d['inventory_descr'] ?? '')));
                $src = $srcIndex[$matchKey] ?? ($srcDetails[$lineNo - 1] ?? null);

                $srcRefNo = $src ? ($src->{$srcLineKey} ?? null) : null;

                $det = new \App\Models\TrCSdetail();
                $det->setConnection('pgsql');
                $det->csid                 = $csid;
                $det->sppbjktid            = $sppbjktid;
                $det->cs_no                = $lineNo;
                $det->sppbjkt_no           = $srcRefNo;

                // ==== inventory fields (payload > sumber) ====
                $det->inventory_type       = $d['inventory_type']        ?? ($src->inventory_type ?? null);
                $det->inventoryid          = $d['inventoryid']           ?? ($src->inventoryid ?? null);
                $det->inventory_descr      = $d['inventory_descr']       ?? ($src->inventory_descr ?? null);

                // >>> tambah ini supaya tidak null <<<
                $det->inventory_sub_type   = $d['inventory_sub_type']    ?? ($src->inventory_sub_type ?? null);
                $det->inventory_category   = $d['inventory_category']    ?? ($src->inventory_category ?? null);

                $det->qty                  = $round2($d['qty']           ?? ($src->qty ?? 0));
                $det->uom                  = $d['uom']                   ?? ($src->uom ?? null);

                // Konversi UOM dari sumber (jika ada)
                $det->type_multiplier      = $src->type_multiplier       ?? null;
                $det->base_multiplier      = isset($src->base_multiplier) ? $round2($src->base_multiplier) : null;
                $det->base_qty             = isset($src->base_qty)        ? $round2($src->base_qty)        : null;
                $det->base_uom             = $src->base_uom ?? null;

                // Harga terakhir & note detail
                $det->inventory_last_price = isset($d['inventory_last_price']) ? $round2($d['inventory_last_price'])
                                            : (isset($src->inventory_last_price) ? $round2($src->inventory_last_price) : 0);
                $det->csnote_detail        = $d['csnote_detail'] ?? ($src->note ?? null);

                // Lokasi & budgeting (ambil dari sumber bila ada)
                $det->location_id               = $src->location_id               ?? null;
                $det->sub_location_id           = $src->sub_location_id           ?? null;
                $det->budget_perpost            = $src->budget_perpost            ?? null;
                $det->budget_cpny_id            = $cpnyId; // tetap perusahaan CS
                $det->budget_business_unit_id   = $src->budget_business_unit_id   ?? null;
                $det->budget_department_fin_id  = $src->budget_department_fin_id  ?? null;
                $det->budget_account_id         = $src->budget_account_id         ?? null;
                $det->budget_activity_id        = $src->budget_activity_id        ?? null;

                // Map harga per vendor (maks 6) + akumulasi selected per vendor
                $selectedTotalThisRow = 0.0;
                $selectedTaxThisRow   = 0.0;
                $selectedGrandThisRow = 0.0;

                for ($i = 0; $i < min(count($d['vendor'] ?? []), 6); $i++) {
                    $idx   = $i + 1;
                    $vrow  = $d['vendor'][$i];
                    $vid   = $vrow['vendorid'] ?? null;
                    $price = $round2($vrow['price'] ?? 0);
                    $total = $round2($vrow['total'] ?? 0);
                    // tax bisa datang sebagai ppn/pph atau tax; grand bisa datang sebagai grand atau total+tax
                    $ppn   = $round2($vrow['ppn']   ?? 0);
                    $pph   = $round2($vrow['pph']   ?? 0);
                    $tax   = $round2($vrow['tax']   ?? ($ppn + $pph));
                    $grand = $round2($vrow['grand'] ?? ($total + $tax));
                    $sel   = !empty($vrow['selected']);

                    $det->{"vendorid{$idx}"}         = $vid;
                    $det->{"vendorprice{$idx}"}      = $price;
                    $det->{"vendortotalprice{$idx}"} = $total;
                    $det->{"vendor{$idx}selected"}   = (bool)$sel;

                    if ($sel) {
                        $selectedTotalThisRow = $total;
                        $selectedTaxThisRow   = $tax;
                        $selectedGrandThisRow = $grand;

                        // akumulasi ke header per vendor slot
                        $selectedByVendor[$idx]['total'] += $total;
                        $selectedByVendor[$idx]['tax']   += $tax;
                        $selectedByVendor[$idx]['grand'] += $grand;
                    }
                }

                $docSelectedGrand += $selectedGrandThisRow;

                $det->status     = 'P';
                $det->created_by = $username;
                $det->save();
            }

            // ==== 5) Update ordered/openordered pada dokumen sumber (untuk baris yang selected) ====
            $addedTotalOrdered = 0.0;
            foreach ($details as $i => $d) {
                // ada vendor dipilih?
                $isSelected = false;
                foreach (($d['vendor'] ?? []) as $vrow) {
                    if (!empty($vrow['selected'])) { $isSelected = true; break; }
                }
                if (!$isSelected) continue;

                $orderedQty = (float) ($d['qty'] ?? 0);
                if ($orderedQty <= 0) continue;

                // Temukan detail sumber yg matching
                $matchKey = strtoupper(trim(($d['inventoryid'] ?? ''))) . '|' .
                            strtoupper(trim(($d['uom'] ?? ''))) . '|' .
                            strtoupper(trim(($d['inventory_descr'] ?? '')));
                $srcDet = $srcIndex[$matchKey] ?? ($srcDetails[$i] ?? null);
                if (!$srcDet) continue;

                $detTable = $srcDet->getTable();
                if (\Illuminate\Support\Facades\Schema::connection('pgsql')->hasColumn($detTable, 'ordered')) {
                    $srcDet->ordered = (float)($srcDet->ordered ?? 0) + $orderedQty;
                }
                if (\Illuminate\Support\Facades\Schema::connection('pgsql')->hasColumn($detTable, 'openordered')) {
                    $srcDet->openordered = max(0, (float)($srcDet->openordered ?? 0) - $orderedQty);
                }
                $srcDet->save();

                $addedTotalOrdered += $orderedQty;
            }

            // Update header sumber
            $hdrTable = $srcHeader->getTable();
            if (\Illuminate\Support\Facades\Schema::connection('pgsql')->hasColumn($hdrTable, 'totalordered')) {
                $srcHeader->totalordered = (float)($srcHeader->totalordered ?? 0) + $addedTotalOrdered;
            }
            if (\Illuminate\Support\Facades\Schema::connection('pgsql')->hasColumn($hdrTable, 'totalopenordered')) {
                $srcHeader->totalopenordered = max(0, (float)($srcHeader->totalopenordered ?? 0) - $addedTotalOrdered);
            }
            $srcHeader->save();

            // ==== 6) Budget reserve (per bulan csdate) ====
            $csDate   = \Carbon\Carbon::parse($cs->csdate);
            $yearStr  = $csDate->format('Y');     // perpost = YYYY
            $monthIdx = (int) $csDate->format('m');
            $periodColBase     = 'period' . str_pad($monthIdx, 2, '0', STR_PAD_LEFT);
            $periodReserveCol  = $periodColBase . '_reserve';

            // Aggregate budget berdasarkan selected vendor di tiap detail
            $budgetBuckets = [];
            foreach ($details as $d) {
                // total grand/total/tax – gunakan grand jika tersedia
                $selectedTotal = 0.0;
                $selectedTax   = 0.0;
                $selectedGrand = 0.0;

                if (!empty($d['vendor']) && is_array($d['vendor'])) {
                    foreach ($d['vendor'] as $vrow) {
                        if (!empty($vrow['selected'])) {
                            $t  = (float)($vrow['total'] ?? 0);
                            $ppn= (float)($vrow['ppn']   ?? 0);
                            $pph= (float)($vrow['pph']   ?? 0);
                            $tx = (float)($vrow['tax']   ?? ($ppn+$pph));
                            $gr = (float)($vrow['grand'] ?? ($t+$tx));
                            $selectedTotal = $t;
                            $selectedTax   = $tx;
                            $selectedGrand = $gr;
                            break;
                        }
                    }
                }
                if ($selectedGrand <= 0 && $selectedTotal <= 0) continue;

                $bCpny = $d['budget_cpny_id']            ?? $cpnyId;
                $bBU   = $d['budget_business_unit_id']   ?? null;
                $bDept = $d['budget_department_fin_id']  ?? null;
                $bAcc  = $d['budget_account_id']         ?? null;
                $bAct  = $d['budget_activity_id']        ?? null;

                $key = json_encode([
                    'perpost'           => $yearStr,
                    'cpny_id'           => $bCpny,
                    'business_unit_id'  => $bBU,
                    'department_fin_id' => $bDept,
                    'account_id'        => $bAcc,
                    'activity_id'       => $bAct,
                ]);
                $amount = ($selectedGrand > 0) ? $selectedGrand : ($selectedTotal + $selectedTax);
                $budgetBuckets[$key] = ($budgetBuckets[$key] ?? 0) + (float)$amount;
            }

            foreach ($budgetBuckets as $keyJson => $amount) {
                $crit = json_decode($keyJson, true);

                $bd = \App\Models\BudgetDetail::where([
                        ['perpost', '=', $crit['perpost']],
                        ['cpny_id', '=', $crit['cpny_id']],
                    ])
                    ->when($crit['business_unit_id'],  fn($q,$v)=>$q->where('business_unit_id', $v))
                    ->when($crit['department_fin_id'], fn($q,$v)=>$q->where('department_fin_id', $v))
                    ->when($crit['account_id'],        fn($q,$v)=>$q->where('account_id', $v))
                    ->when($crit['activity_id'],       fn($q,$v)=>$q->where('activity_id', $v))
                    ->lockForUpdate()
                    ->first();

                if (!$bd) {
                    $bd = new \App\Models\BudgetDetail();
                    $bd->setConnection('pgsql');
                    $bd->perpost            = $crit['perpost'];
                    $bd->cpny_id            = $crit['cpny_id'];
                    $bd->business_unit_id   = $crit['business_unit_id'];
                    $bd->department_fin_id  = $crit['department_fin_id'];
                    $bd->account_id         = $crit['account_id'];
                    $bd->activity_id        = $crit['activity_id'];
                    $bd->status             = 'A';
                    $bd->created_by         = $username;

                    for ($m = 1; $m <= 12; $m++) {
                        $p = 'period' . str_pad($m, 2, '0', STR_PAD_LEFT);
                        $bd->{$p . '_budget'}  = $bd->{$p . '_budget'}  ?? 0;
                        $bd->{$p . '_reserve'} = $bd->{$p . '_reserve'} ?? 0;
                        $bd->{$p . '_used'}    = $bd->{$p . '_used'}    ?? 0;
                    }
                }

                $bd->{$periodReserveCol} = (float) ($bd->{$periodReserveCol} ?? 0) + (float) $amount;
                $bd->updated_by = $username;
                $bd->save();
            }

            // ==== 7) Tulis ulang kolom header selected per vendor dari akumulasi detail ====
            for ($i = 1; $i <= 6; $i++) {
                $safeSet($cs, $csTable, "totalselectedvendor{$i}",      $round2($selectedByVendor[$i]['total']));
                $safeSet($cs, $csTable, "taxselectedvendor{$i}",        $round2($selectedByVendor[$i]['tax']));
                $safeSet($cs, $csTable, "grandtotalselectedvendor{$i}", $round2($selectedByVendor[$i]['grand']));
            }
            $cs->save();

            // ==== 8) Generate TrApproval (CS hanya cek NOMINAL) ====
            // Gunakan grand total TERPILIH pada detail sebagai dasar "Nominal"
            $ctx = [
                'ignore_nominal' => false,                 // aktifkan checkNominal()
                'grand_total'    => (float)$docSelectedGrand,
            ];

            [$firstApprovalUsernames, $linesCount] = $approvalCtl->generateForDocument(
                $cs->csid,     // refnbr
                $doctype,      // CS
                $cpnyId,
                $deptId,
                $username,
                $ctx,
                $dt
            );

            // opsional: simpan hint approver pertama di header
            if ($firstApprovalUsernames) {
                $cs->completed_by = $firstApprovalUsernames;
                $cs->completed_at = $dt;
                $cs->save();
            }

            // ==== 9) Attachments (opsional) ====
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr'        => $csid,
                    'doctype'       => $doctype,
                    'cpnyid'        => $cpnyId,
                    'departementid' => $deptId,
                    'base_folder'   => 'att-purchasing-app/'.strtolower($doctype),
                    'created_by'    => $user->username,
                ];
                $files = (array) $request->file('attachments');
                try {
                    $uploader     = app(\App\Http\Controllers\TrAttachmentController::class);
                    $uploadResult = $uploader->uploadInternal($meta, $files);
                } catch (\Throwable $e) {
                    \DB::connection('pgsql')->rollBack();
                    return response()->json([
                        'message' => 'Failed to create CS',
                        'error'   => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            } else {
                $uploadResult = null;
            }

            // ==== 10) Email ke approver pertama (status P) ====
            $eid = \Vinkla\Hashids\Facades\Hashids::encode($cs->id);
            $approvalCtl->notifyFirstApprover(
                $cs->csid,
                $doctype,
                $cs->status,                 // 'P'
                'CS',
                url('/showcs/' . $eid),
                [
                    'info'      => $csnote ?: ($srcHeader->keperluan ?? ''),
                    'createdby' => $cs->created_by,
                    'date'      => $dt->toDateTimeString(),
                ]
            );

            \DB::connection('pgsql')->commit();

            return response()->json([
                'message'     => 'CS created successfully',
                'csid'        => $csid,
                'grand_total' => $round2($docSelectedGrand), // dasar approval nominal
                'attachments' => $uploadResult,
            ]);

        } catch (\Throwable $e) {
            \DB::connection('pgsql')->rollBack();
            report($e);
            return response()->json([
                'message' => 'Failed to create CS',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
   

    public function saveCS(Request $request)
    {
            
        // ==== Ambil input dasar dari form (hidden + payload JSON) ====
        $doc          = strtoupper($request->input('doc'));          // SPPB|SPPJ|SPPK|SPPT
        $srcId        = $request->input('src_id');                   // id sumber doc
        $sppbjktid    = $request->input('sppbjktid');                // docno ditaruh ke sini
        $cpnyId       = $request->input('cpny_id');
        $deptId       = $request->input('department_id');
        $bqid         = $request->input('bqid');
        $userPeminta  = $request->input('user_peminta');
        $csnote       = $request->input('csnote');                // textarea #keperluan
        $assigndate   = $request->input('assigndate');             // hidden #assigndate
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
            $cs->assigndate    = $assigndate ?: null;

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
                $det->sppbjktid = $sppbjktid;
                $det->cs_no               = $lineNo;
                $det->sppbjkt_no          = $srcRefNo; // isi dengan nomor baris sumber apapun namanya
                $det->inventory_type      = $d['inventory_type']        ?? ($src->inventory_type ?? null);
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
            //         $file->move($folder_upload, $attachfile);

            //         //insert to table attachments
            //         $attach = new Attachment();
            //         $attach->docid = $csid;
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
                     'refnbr'        => $csid,
                    'doctype'       => $doctype,
                    'cpnyid'        => $cpnyId,
                    'departementid' => $deptId,                    
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
                        'message' => 'Failed to create CS',
                        'error'   => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            } else {
                $uploadResult = null; // tidak ada attachment
            }
           
            DB::connection('pgsql')->commit();

            return response()->json([
                'message' => 'CS created successfully',
                'csid'    => $csid,
                'attachments' => $uploadResult,
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

    public function editCS(string $eid)
    {
        $ids = Hashids::decode($eid);
        abort_if(empty($ids), 404);
        $id = $ids[0];

        $cs = TrCS::findOrFail($id);

        $docno   = (string) $cs->sppbjktid;
        $prefix2 = strtoupper(substr($docno, 0, 2));
        $map     = ['PB'=>'SPPB','PJ'=>'SPPJ','PK'=>'SPPK','PT'=>'SPPT'];
        $doc     = $map[$prefix2] ?? 'SPPB';

        // header dokumen sumber (untuk tampilan readonly di header)
        $header = null;
        switch ($doc) {
            case 'SPPB': $header = TrSPPB::with(['creator','purchaser'])->where('sppbid',$docno)->first(); $top_type='PO'; break;
            case 'SPPJ': $header = TrSPPJ::with(['creator','purchaser'])->where('sppjid',$docno)->first(); $top_type='SPK'; break;
            case 'SPPK': $header = TrSPPK::with(['creator','purchaser'])->where('sppkid',$docno)->first(); $top_type='SPK'; break;
            case 'SPPT': $header = TrSPPT::with(['creator','purchaser'])->where('spptid',$docno)->first(); $top_type='SPK'; break;
        }

        // Detail baris CS
        $items = TrCSdetail::where('csid', $cs->csid)
            ->orderBy(DB::raw("COALESCE(sppbjkt_no, cs_no)"))
            ->get();

        // === Bentuk vendor summary dari kolom TrCS vendor1..6 ===
        // kita pakai vendoridX sebagai "kode vendor" (key utama),
        // dan jadikan juga sebagai "id" kolom agar konsisten di atribut data-vendor-id.
        $vendorsUsed = [];
        for ($i = 1; $i <= 6; $i++) {
            $vid = $cs->{"vendorid{$i}"} ?? null; // KODE vendor (string)
            if (!$vid) continue;

            $vendorsUsed[] = [
                'id'           => $vid, // pakai kode sebagai id kolom
                'vendor_id'    => $vid, // kode (untuk dicocokkan di detail)
                'vendor_name'  => $cs->{"vendorname{$i}"}  ?? '',
                'vendor_addr1' => $cs->{"vendoralamat{$i}"} ?? '',
                'phone_number' => $cs->{"vendortelp{$i}"}   ?? '',
                'contact_person'=> $cs->{"vendorcp{$i}"}    ?? '',
                'top'          => $cs->{"vendortop{$i}"}    ?? '30D',
                // pajak & ringkasan
                'taxcode'      => $cs->{"taxcodevendor{$i}"} ?? '',
                'ppn'          => (float)($cs->{"ppnvendor{$i}"} ?? 11),
                'pph'          => (float)($cs->{"pphvendor{$i}"} ?? 0),
                'total'        => (float)($cs->{"totalvendor{$i}"} ?? 0),
                'tax'          => (float)($cs->{"taxvendor{$i}"} ?? 0),
                'grand'        => (float)($cs->{"grandtotalvendor{$i}"} ?? 0),
                'sel_total'    => (float)($cs->{"totalselectedvendor{$i}"} ?? 0),
                'sel_tax'      => (float)($cs->{"taxselectedvendor{$i}"} ?? 0),
                'sel_grand'    => (float)($cs->{"grandtotalselectedvendor{$i}"} ?? 0),
                // optional: jika kamu simpan tax id terpisah, isi di sini (sekarang tidak ada)
                'ppn_id'       => null,
                'pph_id'       => null,
            ];
        }

        // === Matriks detail per baris-per vendor dari TrCSdetail ===
        // DETAIL_MATRIX[rowIndex][vendor_code] = ['price'=>..., 'total'=>..., 'selected'=>bool]
        $detailVendorMatrix = [];
        foreach ($items as $idx => $row) {
            $detailVendorMatrix[$idx] = [];
            for ($i = 1; $i <= 6; $i++) {
                $code = $row->{"vendorid{$i}"} ?? null;  // KODE vendor
                if (!$code) continue;

                $detailVendorMatrix[$idx][$code] = [
                    'price'    => (float)($row->{"vendorprice{$i}"} ?? 0),
                    'total'    => (float)($row->{"vendortotalprice{$i}"} ?? 0),
                    'selected' => (bool)($row->{"vendor{$i}selected"} ?? false),
                ];
            }
        }

        // $attachment = Attachment::where('docid', $cs->sppbjktid)->where('status','A')->orderBy('created_at')->get();
        // $attachmentCS = Attachment::where('docid', $cs->csid)->where('status','A')->orderBy('created_at')->get();

        // --- helper: ambil daftar attachment TrAttachment + signed URL GCS ---
        $fetchGcsAttachments = function (string $refnbr) {
            $rows = \App\Models\TrAttachment::where('refnbr', $refnbr)
                ->where('status', 'A')
                ->orderBy('created_at', 'asc')   // sesuai permintaan: ASC
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

            return $rows->map(function ($r) use ($bucket) {
                $objectPath = rtrim($r->folder ?? '', '/') . '/' . ltrim($r->filename ?? '', '/');
                $object     = $bucket->object($objectPath);

                $signedUrl = null;
                try {
                    $signedUrl = $object->signedUrl(
                        new \DateTimeImmutable('+10 minutes'),
                        ['version' => 'v4']
                    );
                } catch (\Throwable $e) {
                    \Log::warning('Signed URL gagal', [
                        'path'  => $objectPath,
                        'error' => $e->getMessage()
                    ]);
                }

                return (object) [
                    'id'            => $r->id,
                    'display_name'  => $r->attachment_name ?? $r->filename,
                    'created_by'    => $r->created_by,
                    'created_at'    => $r->created_at,
                    'url'           => $signedUrl,     // dipakai di view
                    'folder'        => $r->folder,
                    'filename'      => $r->filename,
                    'extention'     => $r->extention,
                    'size'          => $r->filesize,
                ];
            });
        };

        // --- ambil attachment sumber dokumen & CS (GCS) ---
        $attachment   = $fetchGcsAttachments($cs->sppbjktid); // SPPB/J/K/T
        $attachmentCS = $fetchGcsAttachments($cs->csid);      // CS

        $eid = Hashids::encode($cs->id);

        $bq = null;
        $bq_eid = null;
        if (!empty($cs->bqid)) {
            $bq = TrBQCS::where('bqid', $cs->bqid)->first();   // <— sesuai permintaan
            if ($bq) {
                $bq_eid = Hashids::encode($bq->id);
            }
        }

        // --- siapkan pembanding: total per vendor dari CS & BQ
        $csVendorTotals = [];  // [idx => ['vendorid','vendorname','total_cs']]
        $bqVendorTotals = [];  // [idx => ['grand_mat','grand_jsa','sum_bq']]

        for ($i = 1; $i <= 6; $i++) {
            $vid = $cs->{"vendorid{$i}"} ?? null;
            $vnm = $cs->{"vendorname{$i}"} ?? null;

            // total dari CS (kolom totalvendor{i})
            $totalCS = (float) ($cs->{"totalvendor{$i}"} ?? 0);

            if ($vid || $vnm || $totalCS > 0) {
                $csVendorTotals[$i] = [
                    'vendorid'   => $vid,
                    'vendorname' => $vnm,
                    'total_cs'   => $totalCS,
                ];
            }

            // total dari BQ: grandtotalmaterialvendor{i} + grandtotaljasavendor{i}
            if ($bq) {
                $gmat = (float) ($bq->{"grandtotalmaterialvendor{$i}"} ?? 0);
                $gjsa = (float) ($bq->{"grandtotaljasavendor{$i}"} ?? 0);
                $bqVendorTotals[$i] = [
                    'grand_mat' => $gmat,
                    'grand_jsa' => $gjsa,
                    'sum_bq'    => $gmat + $gjsa,
                ];
            }
        }

        $tops = MsTop::where('status','A')
            ->where('top_type',$top_type) 
            ->orderByRaw('COALESCE(top_days, 9999), top_name') 
            ->get(['topid','top_name','top_days','top_type']);

        return view('pages.canvass.editcs', [
            'eid'        => $eid,
            'doc'        => $doc,
            'src_id'     => $header->id,
            'docno'      => $docno,
            'header'     => $header ?? $cs,
            'items'      => $items,
            'attachment' => $attachment,
            'attachmentCS' => $attachmentCS,
            'cs'            => $cs,
            'tops'         => $tops,
            // payload untuk preload JS
            'vendorsUsed'         => $vendorsUsed,
            'detailVendorMatrix'  => $detailVendorMatrix,
            'bq'         => $bq,
            'bq_eid'     => $bq_eid,
            'csVendorTotals'  => $csVendorTotals,
            'bqVendorTotals'  => $bqVendorTotals,
        ]);
    }

    public function updateCS(Request $request, $csid)
    {
        // 1) Validasi payload dasar
        $request->validate([
            'doc'             => 'required|string',     // SPPB|SPPJ|SPPK|SPPT
            'src_id'          => 'nullable',            // penting saat submit (untuk ordered/budget)
            'sppbjktid'       => 'nullable|string',
            'cpny_id'         => 'required|string',
            'department_id'   => 'required|string',
            'bqid'            => 'nullable|string',
            'user_peminta'    => 'nullable|string',
            'csnote'          => 'nullable|string',
            'assigndate'      => 'nullable|string',
            'vendors'         => 'required|string', // JSON array
            'details'         => 'required|string', // JSON array
            'action'          => 'nullable|in:save,submit',
        ]);

        // 2) Decode JSON dari form
        $vendors = json_decode($request->input('vendors', '[]'), true) ?: [];
        $details = json_decode($request->input('details', '[]'), true) ?: [];

        // 3) Context user & waktu
        $user      = $request->user();
        $username  = $user->username ?? 'system';
        $dt        = \Carbon\Carbon::now();

        $round2 = fn($n) => round((float)$n, 2);
        $safeSet = function ($model, string $table, string $column, $value) {
            if (\Illuminate\Support\Facades\Schema::connection('pgsql')->hasColumn($table, $column)) {
                $model->{$column} = $value;
            }
        };

        $doctype = 'CS';
        $doc     = strtoupper($request->input('doc'));
        $srcId   = $request->input('src_id');
        $cpnyId  = $request->input('cpny_id');
        $deptId  = $request->input('department_id');

        // 4) Pastikan line approval tersedia
        $approvalCtl = app(\App\Http\Controllers\ApprovalController::class);
        $approvalCtl->loadLines($doctype, $cpnyId, $deptId);

        \DB::connection('pgsql')->beginTransaction();
        try {
            // 5) Lock header CS
            /** @var \App\Models\TrCS $cs */
            $cs = \App\Models\TrCS::on('pgsql')->lockForUpdate()->where('csid', $csid)->firstOrFail();
            $csTable = $cs->getTable();

            // 6) Ambil sumber (header+detail) untuk fallback field tampilan
            // (Tetap lakukan di sini agar header terisi wajar meski action=save)
            $srcHeader  = null; $srcDetails = collect(); $srcLineKey = null;
            switch ($doc) {
                case 'SPPB':
                    $srcHeader  = \App\Models\TrSPPB::with(['requestType','creator','purchaser'])->find($srcId);
                    $srcLineKey = 'sppb_no';
                    $srcDetails = $srcHeader ? \App\Models\TrSPPBdetail::where('sppbid', $srcHeader->sppbid)->orderBy($srcLineKey)->get() : collect();
                    break;
                case 'SPPJ':
                    $srcHeader  = \App\Models\TrSPPJ::with(['requestType','creator','purchaser'])->find($srcId);
                    $srcLineKey = 'sppj_no';
                    $srcDetails = $srcHeader ? \App\Models\TrSPPJdetail::where('sppjid', $srcHeader->sppjid)->orderBy($srcLineKey)->get() : collect();
                    break;
                case 'SPPK':
                    $srcHeader  = \App\Models\TrSPPK::with(['requestType','creator','purchaser'])->find($srcId);
                    $srcLineKey = 'sppk_no';
                    $srcDetails = $srcHeader ? \App\Models\TrSPPKdetail::where('sppkid', $srcHeader->sppkid)->orderBy($srcLineKey)->get() : collect();
                    break;
                case 'SPPT':
                    $srcHeader  = \App\Models\TrSPPT::with(['requestType','creator','purchaser'])->find($srcId);
                    $srcLineKey = 'sppt_no';
                    $srcDetails = $srcHeader ? \App\Models\TrSPPTdetail::where('spptid', $srcHeader->spptid)->orderBy($srcLineKey)->get() : collect();
                    break;
                default:
                    abort(422, 'Invalid doc type');
            }

            // index detail sumber → untuk fallback field detail
            $srcIndex = [];
            foreach ($srcDetails as $sd) {
                $key = strtoupper(trim(($sd->inventoryid ?? ''))) . '|' .
                    strtoupper(trim(($sd->uom ?? ''))) . '|' .
                    strtoupper(trim(($sd->inventory_descr ?? '')));
                $srcIndex[$key] = $sd;
            }

            // 7) Update HEADER TrCS (termasuk kolom vendor*)
            $cs->sppbjktid     = $request->input('sppbjktid');
            $cs->cpny_id       = $cpnyId;
            $cs->bqid          = $request->input('bqid') ?: ($srcHeader->bqid ?? $cs->bqid);
            $cs->department_id = $deptId ?: ($srcHeader->department_id ?? $cs->department_id);
            $cs->user_peminta  = $request->input('user_peminta') ?: (optional($srcHeader->creator)->name ?? $cs->user_peminta);
            $cs->csnote        = $request->input('csnote') ?: null;
            $cs->assigndate    = $request->input('assigndate') ?: null;

            // lengkapi dari sumber jika kolom ada
            $safeSet($cs, $csTable, 'budget_perpost', $srcHeader->budget_perpost ?? null);
            $safeSet($cs, $csTable, 'woid',           $srcHeader->woid           ?? null);
            $safeSet($cs, $csTable, 'spbid',          $srcHeader->spbid          ?? null);

            // Tulis ulang vendor header (display) & reset kolom selected; juga clear slot yang tidak terpakai
            for ($slot = 1; $slot <= 6; $slot++) {
                $v = $vendors[$slot-1] ?? null;

                $safeSet($cs, $csTable, "vendorid{$slot}",      $v['vendorid']     ?? null);
                $safeSet($cs, $csTable, "vendorname{$slot}",    $v['vendorname']   ?? null);
                $safeSet($cs, $csTable, "vendoralamat{$slot}",  $v['vendoralamat'] ?? null);
                $safeSet($cs, $csTable, "vendortelp{$slot}",    $v['vendortelp']   ?? null);
                $safeSet($cs, $csTable, "vendorcp{$slot}",      $v['vendorcp']     ?? null);
                $safeSet($cs, $csTable, "vendortop{$slot}",     $v['vendortop']    ?? null);
                $safeSet($cs, $csTable, "vendornote{$slot}",    $v['vendornote']   ?? null);

                $safeSet($cs, $csTable, "totalvendor{$slot}",              $round2($v['total'] ?? 0));
                $safeSet($cs, $csTable, "taxcodevendor{$slot}",            $v['taxcode']   ?? null);
                $safeSet($cs, $csTable, "ppnvendor{$slot}",                $round2($v['ppn']   ?? 0));
                $safeSet($cs, $csTable, "pphvendor{$slot}",                $round2($v['pph']   ?? 0));
                $safeSet($cs, $csTable, "taxvendor{$slot}",                $round2($v['tax']   ?? 0));
                $safeSet($cs, $csTable, "grandtotalvendor{$slot}",         $round2($v['grand'] ?? 0));

                // reset kolom selected; akan diisi ulang setelah replace detail
                $safeSet($cs, $csTable, "totalselectedvendor{$slot}",      0);
                $safeSet($cs, $csTable, "taxselectedvendor{$slot}",        0);
                $safeSet($cs, $csTable, "grandtotalselectedvendor{$slot}", 0);
            }

            // status & audit (biarkan status lama jika belum submit)
            if (\Illuminate\Support\Facades\Schema::connection('pgsql')->hasColumn($csTable, 'updated_by')) {
                $cs->updated_by = $username;
            }
            $cs->save();

            // 8) Replace DETAIL TrCSdetail & akumulasi ke header
            \App\Models\TrCSdetail::on('pgsql')->where('csid', $csid)->delete();

            $lineNo           = 0;
            $docSelectedGrand = 0.0;
            $selectedByVendor = [];
            for ($i = 1; $i <= 6; $i++) $selectedByVendor[$i] = ['total'=>0.0,'tax'=>0.0,'grand'=>0.0];

            foreach ($details as $d) {
                $lineNo++;

                $matchKey = strtoupper(trim(($d['inventoryid'] ?? ''))) . '|' .
                            strtoupper(trim(($d['uom'] ?? ''))) . '|' .
                            strtoupper(trim(($d['inventory_descr'] ?? '')));
                $src = $srcIndex[$matchKey] ?? ($srcDetails[$lineNo - 1] ?? null);
                $srcRefNo = $src ? ($src->{$srcLineKey} ?? null) : null;

                $det = new \App\Models\TrCSdetail();
                $det->setConnection('pgsql');

                $det->csid          = $csid;
                $det->sppbjktid     = $request->input('sppbjktid');
                $det->cs_no         = $lineNo;
                $det->sppbjkt_no    = $srcRefNo;

                // inventory fields (payload > sumber) – jangan biarkan null critical fields
                $det->inventory_type     = $d['inventory_type']     ?? ($src->inventory_type ?? null);
                $det->inventoryid        = $d['inventoryid']        ?? ($src->inventoryid ?? null);
                $det->inventory_descr    = $d['inventory_descr']    ?? ($src->inventory_descr ?? null);
                $det->inventory_sub_type = $d['inventory_sub_type'] ?? ($src->inventory_sub_type ?? null);
                $det->inventory_category = $d['inventory_category'] ?? ($src->inventory_category ?? null);

                $det->qty   = $round2($d['qty'] ?? ($src->qty ?? 0));
                $det->uom   = $d['uom'] ?? ($src->uom ?? null);

                // konversi dari sumber (jika ada)
                $det->type_multiplier = $src->type_multiplier ?? null;
                $det->base_multiplier = isset($src->base_multiplier) ? $round2($src->base_multiplier) : null;
                $det->base_qty        = isset($src->base_qty)        ? $round2($src->base_qty)        : null;
                $det->base_uom        = $src->base_uom ?? null;

                // harga terakhir & note
                $det->inventory_last_price = isset($d['inventory_last_price']) ? $round2($d['inventory_last_price'])
                                        : (isset($src->inventory_last_price) ? $round2($src->inventory_last_price) : 0);
                $det->csnote_detail        = $d['csnote_detail'] ?? ($src->note ?? null);

                // lokasi & budgeting
                $det->location_id               = $src->location_id               ?? null;
                $det->sub_location_id           = $src->sub_location_id           ?? null;
                $det->budget_perpost            = $src->budget_perpost            ?? null;
                $det->budget_cpny_id            = $cpnyId;
                $det->budget_business_unit_id   = $src->budget_business_unit_id   ?? null;
                $det->budget_department_fin_id  = $src->budget_department_fin_id  ?? null;
                $det->budget_account_id         = $src->budget_account_id         ?? null;
                $det->budget_activity_id        = $src->budget_activity_id        ?? null;

                // harga vendor + akumulasi selected
                $selectedGrandThisRow = 0.0;
                for ($i = 0; $i < min(count($d['vendor'] ?? []), 6); $i++) {
                    $slot  = $i + 1;
                    $vrow  = $d['vendor'][$i];
                    $vid   = $vrow['vendorid'] ?? null;
                    $price = $round2($vrow['price'] ?? 0);
                    $total = $round2($vrow['total'] ?? 0);
                    $ppn   = $round2($vrow['ppn']   ?? 0);
                    $pph   = $round2($vrow['pph']   ?? 0);
                    $tax   = $round2($vrow['tax']   ?? ($ppn + $pph));
                    $grand = $round2($vrow['grand'] ?? ($total + $tax));
                    $sel   = !empty($vrow['selected']);

                    $det->{"vendorid{$slot}"}         = $vid;
                    $det->{"vendorprice{$slot}"}      = $price;
                    $det->{"vendortotalprice{$slot}"} = $total;
                    $det->{"vendor{$slot}selected"}   = (bool)$sel;

                    if ($sel) {
                        $selectedGrandThisRow = $grand;
                        $selectedByVendor[$slot]['total'] += $total;
                        $selectedByVendor[$slot]['tax']   += $tax;
                        $selectedByVendor[$slot]['grand'] += $grand;
                    }
                }

                $docSelectedGrand += $selectedGrandThisRow;

                // status detail: draft saat save, pending saat submit (akan di-set di bawah bila submit)
                $det->status     = 'H';
                $det->created_by = $username;
                $det->save();
            }

            // 9) Tulis ulang kolom header selected per vendor dari akumulasi detail
            for ($slot = 1; $slot <= 6; $slot++) {
                $safeSet($cs, $csTable, "totalselectedvendor{$slot}",      $round2($selectedByVendor[$slot]['total']));
                $safeSet($cs, $csTable, "taxselectedvendor{$slot}",        $round2($selectedByVendor[$slot]['tax']));
                $safeSet($cs, $csTable, "grandtotalselectedvendor{$slot}", $round2($selectedByVendor[$slot]['grand']));
            }
            $cs->save();

            // 10) Attachments BARU (GCS)
            if ($request->hasFile('attachments')) {
                $meta = [
                    'refnbr'        => $cs->csid,
                    'doctype'       => $doctype,
                    'cpnyid'        => $cpnyId,
                    'departementid' => $deptId,
                    'base_folder'   => 'att-purchasing-app/'.strtolower($doctype),
                    'created_by'    => $username,
                ];
                $files = (array) $request->file('attachments');
                try {
                    $uploader = app(\App\Http\Controllers\TrAttachmentController::class);
                    $uploader->uploadInternal($meta, $files);
                } catch (\Throwable $e) {
                    \DB::connection('pgsql')->rollBack();
                    return response()->json([
                        'ok'      => false,
                        'message' => 'Gagal upload attachment: '.$e->getMessage(),
                    ], 500);
                }
            }

            // 11) SAVE vs SUBMIT
            $action = strtolower($request->input('action', 'save'));
            if (!in_array($action, ['save','submit'], true)) $action = 'save';

            if ($action === 'submit') {
                // (a) Validasi submit server-side
                $this->validateSubmitServerSide($details);

                // (b) Ambil sumber (helper private milikmu), dan update ordered
                [$srcHeader2, $srcDetails2, $srcLineKey2, $srcIndex2] = $this->buildSourceForDoc($doc, $srcId);
                $this->updateOrderedOnSource($details, $srcHeader2, $srcDetails2, $srcIndex2, $cpnyId);

                // (c) Reserve budget
                $this->reserveBudget($details, $cpnyId, $cs, $username);

                // (d) Set status header & detail = Pending, set submitdate
                $cs->status = 'P';
                if (\Illuminate\Support\Facades\Schema::connection('pgsql')->hasColumn($csTable, 'submitdate')) {
                    $cs->submitdate = $dt;
                }
                if (\Illuminate\Support\Facades\Schema::connection('pgsql')->hasColumn($csTable, 'updated_by')) {
                    $cs->updated_by = $username;
                }
                $cs->save();

                \App\Models\TrCSdetail::on('pgsql')->where('csid', $csid)->update(['status' => 'P']);

                // (e) Generate TrApproval (cek nominal) + email approver pertama
                $ctx = [
                    'ignore_nominal' => false,
                    'grand_total'    => (float) $docSelectedGrand,
                ];

                [$firstApprovalUsernames, $linesCount] = $approvalCtl->generateForDocument(
                    $cs->csid,
                    $doctype,
                    $cpnyId,
                    $deptId,
                    $username,
                    $ctx,
                    $dt
                );

                if ($firstApprovalUsernames) {
                    $cs->completed_by = $firstApprovalUsernames;
                    $cs->completed_at = $dt;
                    $cs->save();
                }

                $eid = \Vinkla\Hashids\Facades\Hashids::encode($cs->id);
                $approvalCtl->notifyFirstApprover(
                    $cs->csid,
                    $doctype,
                    $cs->status,  // 'P'
                    'CS',
                    url('/showcs/' . $eid),
                    [
                        'info'      => $cs->csnote ?: ($srcHeader2->keperluan ?? $srcHeader->keperluan ?? ''),
                        'createdby' => $cs->created_by,
                        'date'      => $dt->toDateTimeString(),
                    ]
                );
            } else {
                // Pastikan status minimal draft jika belum pernah di-set
                if (!$cs->status || $cs->status === 'H') {
                    $cs->status = 'H';
                    if (\Illuminate\Support\Facades\Schema::connection('pgsql')->hasColumn($csTable, 'updated_by')) {
                        $cs->updated_by = $username;
                    }
                    $cs->save();
                }
            }

            \DB::connection('pgsql')->commit();

            return response()->json([
                'ok'           => true,
                'message'      => $action === 'submit' ? 'CS berhasil diupdate & diajukan' : 'CS berhasil diupdate',
                'csid'         => $cs->csid,
                'grand_total'  => $round2($docSelectedGrand), // dasar approval nominal
                'status'       => $cs->status,
                'submitdate'   => optional($cs->submitdate)->toDateTimeString(),
            ]);

        } catch (\Throwable $e) {
            \DB::connection('pgsql')->rollBack();
            report($e);
            return response()->json([
                'ok'      => false,
                'message' => 'Gagal update CS: '.(config('app.debug') ? $e->getMessage() : 'Terjadi kesalahan'),
            ], 500);
        }
    }



    public function showCS($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $cs = TrCS::with([
            'creator:username,name',
            'updater:username,name',
            'completer:username,name',
        ])->findOrFail($id);

        // $csdetail = TrCSdetail::with([
        //     'location:location_id,location_name',
        //     'subLocation:sub_location_id,sub_location_name'
        // ])->where('csid', $cs->csid)
        // ->orderBy('cs_no')
        // ->get();
        $csdetail = TrCSdetail::with([
            'location:location_id,location_name',
            'subLocation:sub_location_id,sub_location_name'
        ])
        ->where('csid', $cs->csid)
        ->whereNotNull('qty')           // aman kalau kolom bisa null
        ->where('qty', '!=', 0)         // terjemah ke SQL: qty <> 0
        ->orderBy('cs_no')
        ->get();


        $approval = T_approval::where('docid', $cs->csid)
            ->where('status','<>','X')
            ->orderBy('created_at')
            ->orderBy('aprvid')
            ->get();

        // $attachmentCS = Attachment::where('docid', $cs->csid)
        //     ->where('status','A')
        //     ->get();

        // ---------- ambil lampiran dari tr_attachment ----------
        $rows = TrAttachment::where('refnbr', $cs->csid)
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
        $attachmentCS = $rows->map(function ($r) use ($bucket) {
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

        // $attachmentBJKT = Attachment::where('docid', $cs->sppbjktid)
        //     ->where('status','A')
        //     ->get();

        // ---------- ambil lampiran dari tr_attachment ----------
        $rows = TrAttachment::where('refnbr', $cs->sppbjktid)
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
        $attachmentBJKT = $rows->map(function ($r) use ($bucket) {
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
       
        $eid_sppbjkt = Hashids::encode($srcHeader->id);
            

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
            'hash'      => $hash,
            'eid_sppbjkt' => $eid_sppbjkt,
        ]);
    }


    public function approveCS(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'CS';

        $cs = TrCS::with('creator')->where('csid', $docid)->first();
        if (!$cs) {
            return response()->json(['success' => false, 'message' => 'CS not found'], 404);
        }

        // Sumber header asal (PB/PJ/PK/PT) → tetap seperti semula
        $prefix  = strtoupper(substr((string)$cs->sppbjktid, 0, 2));
        $srcHeader = null;
        $potype   = null;

        if ($prefix === 'PB') {
            $srcHeader = TrSPPB::with(['requestType','creator','purchaser'])
                ->where('sppbid', $cs->sppbjktid)->first();
            $potype = 'PO';
        } elseif ($prefix === 'PJ') {
            $srcHeader = TrSPPJ::with(['requestType','creator','purchaser'])
                ->where('sppjid', $cs->sppbjktid)->first();
            $potype = 'SPK';
        } elseif ($prefix === 'PK') {
            $srcHeader = TrSPPK::with(['requestType','creator','purchaser'])
                ->where('sppkid', $cs->sppbjktid)->first();
            $potype = 'SPK';
        } elseif ($prefix === 'PT') {
            $srcHeader = TrSPPT::with(['requestType','creator','purchaser'])
                ->where('spptid', $cs->sppbjktid)->first();
            $potype = 'SPK';
        } else {
            return response()->json(['success' => false, 'message' => 'Invalid doc type'], 422);
        }

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($cs->id);
        $docUrl   = url('/showcs/' . $eid);
        $fullname = data_get($cs, 'creator.name') ?: $cs->created_by;
       
        // ======== LOGIKA IMBUDGET ========
        // Ambil level approver saat ini
        $pending = TrApproval::where('refnbr', $cs->csid)
            ->where('status', 'P')           
            ->whereNotNull('aprv_datebefore')           
            ->orderByRaw("CAST(aprv_leveling AS numeric) ASC")
            ->first();
           
        $currentLevel = (int)($pending->aprv_leveling ?? 0);

        // Threshold setting
        $threshold = (int) (MsPurchSetting::where('setting_id', 'IMGEN')->value('setting_value_int') ?? 0);

        $flagIM          = (bool) ($cs->flag_imbudget ?? false);     // kolom di TrCS
        $existingIM      = $cs->imbudgetid ?? null;                  // kolom di TrCS
        $statusIM        = $cs->status_imbudget ?? null;             // kolom di TrCS
        $needGenerateNow = $flagIM && empty($existingIM) && ($currentLevel >= $threshold);

        // 1) flag=true & sudah punya IM tapi belum Complete → STOP approve
        if ($flagIM && !empty($existingIM) && $statusIM !== 'C') {
            return response()->json([
                'success' => false,
                'code'    => 'IM_IN_PROGRESS',
                'message' => 'Tidak bisa approve. Masih On Progress IM.'
            ], 409);
        }

        // 2) flag=true & belum punya IM & level >= threshold → perlu konfirmasi SweetAlert
        if ($needGenerateNow) {
            if (!$request->boolean('confirm_generate_im')) {
                // Minta konfirmasi dulu (frontend munculin SweetAlert)
                return response()->json([
                    'success' => true,
                    'need_confirm_generate_im' => true,
                    'message' => 'Generate IMBudget sekarang?'
                ]);
            }

            // User sudah konfirmasi → Generate IM, status H; update CS (imbudgetid + status_imbudget)
            try {
                // panggil controller generateIMBudget dengan sumber data dari CS
                $payload = new Request([
                    'csid'          => $cs->csid,
                    'cpnyid'        => $cs->cpny_id ?? $cs->cpnyid,
                    'departementid' => $cs->department_id ?? $cs->departementid,
                    'perpost'       => $cs->budget_perpost ?? null,
                    'user_peminta'  => $cs->user_peminta ?? $user->username,
                    'sppbjktid'     => $cs->sppbjktid,
                    'imbudgetnote'  => $cs->csnote ?? $cs->keperluan,
                ]);

                /** @var IMBudgetController $imCtrl */
                $imCtrl = app(IMBudgetController::class);
           

                $resp = $imCtrl->generateIMBudget($payload);

                // Jika generateIMBudget mengembalikan status error, teruskan apa adanya ke frontend
                if (method_exists($resp, 'getStatusCode') && $resp->getStatusCode() >= 400) {
                    return $resp; // berisi message/error detail dari generateIMBudget
                }

                // Ambil data JSON (array) dengan aman
                $data = $resp->getData(true) ?? [];

                // Robust extract (kalau suatu saat dibungkus di 'data')
                $imbudgetid = $data['imbudgetid'] ?? ($data['data']['imbudgetid'] ?? null);

                // Kalau tetap tidak ada, lempar pesan yang lebih informatif
                if (!$imbudgetid) {
                    $detail = $data['error'] ?? $data['message'] ?? 'Tidak diketahui';
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal generate IMBudget: ' . $detail,
                    ], 500);
                }


                $imbudgetid = $data['imbudgetid'];

                // set status IM → H (Hold)
                TrIMBudget::where('imbudgetid', $imbudgetid)->update(['status' => 'H']);

                // update CS: nomor im + status_imbudget = H
                $cs->imbudgetid     = $imbudgetid;
                $cs->status_imbudget= 'H';
                $cs->save();
                
                $imb = TrIMBudget::where('imbudgetid', $imbudgetid)->first();
                // $hash = $imb ? \Vinkla\Hashids\Facades\Hashids::encode($imb->id) : null;
                $eidCs = \Vinkla\Hashids\Facades\Hashids::encode($cs->id);

                return response()->json([
                    'success'           => true,
                    'code'              => 'IM_CREATED_HOLD',
                    'message'           => "IMBudget berhasil dibuat ($imbudgetid) dan di-HOLD.",
                    'imbudgetid'        => $imbudgetid,
                    // 'imbudget_show_url' => $hash ? url('/showimbudgets/' . $hash) : null,
                    'imbudget_show_url'  => url('/showcs/'.$eidCs), 
                ]);
            } catch (\Throwable $e) {
                \Log::error('Generate IM from approveCS failed', [
                    'csid' => $cs->csid,
                    'err'  => $e->getMessage()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal generate IMBudget: '.$e->getMessage()
                ], 500);
            }
        }

        // 3) flag=true & sudah punya IM & status_imbudget = C → lanjut approve CS
        // 4) flag=false → lanjut approve CS
        $result = app(\App\Http\Controllers\ApprovalController::class)->approveStep(
            $cs->csid,
            $doctype,
            $user->username,
            $user->name,

            // COMPLETE CALLBACK
            function (string $refnbr, \Carbon\Carbon $now) use ($cs, $fullname, $docUrl, $srcHeader, $potype) {
                $cs->status       = 'C';
                $cs->completed_by = $cs->completed_by ?: auth()->user()->username;
                $cs->completed_at = $now;
                $cs->save();

                TrCSdetail::where('csid', $cs->csid)->update(['status' => 'C']);

                try {
                    $this->generatePOFromCS($cs, auth()->user(), $potype);
                } catch (\Throwable $e) {
                    \Log::error('generatePOFromCS failed', [
                        'csid' => $cs->csid,
                        'error' => $e->getMessage(),
                    ]);
                }

                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $cs->csid, 'CS', 'C', $cs->created_by, $docUrl, [
                        'cpnyid'    => $cs->cpny_id ?? $cs->cpnyid ?? '',
                        'deptname'  => $cs->department_id ?? $cs->departementid ?? '',
                        'date'      => $cs->csdate,
                        'info'      => optional($srcHeader)->keperluan ?? $cs->keperluan,
                        'fullname'  => $fullname,
                        'name'      => $fullname,
                        'createdby' => $fullname,
                    ]
                );
            },

            // NEXT APPROVER CALLBACK
            function ($next, \Carbon\Carbon $now) use ($cs, $docUrl) {
                app(\App\Http\Controllers\ApprovalController::class)->notifyFirstApprover(
                    $cs->csid, 'CS', 'P', 'CS', $docUrl, [
                        'info'      => $cs->keperluan,
                        'createdby' => $cs->created_by,
                        'date'      => $now->toDateTimeString(),
                    ]
                );
                $cs->completed_by = auth()->user()->username;
                $cs->completed_at = $now;
                $cs->save();
            }
        );

        if (!($result['ok'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Approve failed'
            ], 403);
        }

        return response()->json(['success' => true, 'message' => 'Task approved successfully']);
    }


    public function approveCS_xxx(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'CS';

        // Ambil header + relasi creator
        $cs = TrCS::with('creator')->where('csid', $docid)->first();
        if (!$cs) {
            return response()->json(['success' => false, 'message' => 'CS not found'], 404);
        }

        // Tentukan sumber header asal & tipe dokumen yang akan di-generate (PO/SPK)
        $prefix  = strtoupper(substr((string)$cs->sppbjktid, 0, 2));
        $srcHeader = null;
        $potype   = null;

        if ($prefix === 'PB') {
            $srcHeader = TrSPPB::with(['requestType','creator','purchaser'])
                ->where('sppbid', $cs->sppbjktid)->first();
            $potype = 'PO';
        } elseif ($prefix === 'PJ') {
            $srcHeader = TrSPPJ::with(['requestType','creator','purchaser'])
                ->where('sppjid', $cs->sppbjktid)->first();
            $potype = 'SPK';
        } elseif ($prefix === 'PK') {
            $srcHeader = TrSPPK::with(['requestType','creator','purchaser'])
                ->where('sppkid', $cs->sppbjktid)->first();
            $potype = 'SPK';
        } elseif ($prefix === 'PT') {
            $srcHeader = TrSPPT::with(['requestType','creator','purchaser'])
                ->where('spptid', $cs->sppbjktid)->first();
            $potype = 'SPK';
        } else {
            return response()->json(['success' => false, 'message' => 'Invalid doc type'], 422);
        }

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($cs->id);
        $docUrl   = url('/showcs/' . $eid);
        $fullname = data_get($cs, 'creator.name') ?: $cs->created_by;

        // Gunakan engine approval terpusat
        $result = app(\App\Http\Controllers\ApprovalController::class)->approveStep(
            $cs->csid,            // refnbr
            $doctype,             // doctype 'CS'
            $user->username,      // current approver username
            $user->name,          // current approver name

            // === COMPLETE CALLBACK ===
            function (string $refnbr, \Carbon\Carbon $now) use ($cs, $fullname, $docUrl, $srcHeader, $potype) {

                // Finalize header
                $cs->status       = 'C';
                $cs->completed_by = $cs->completed_by ?: auth()->user()->username;
                $cs->completed_at = $now;
                $cs->save();

                // Finalize detail
                TrCSdetail::where('csid', $cs->csid)->update(['status' => 'C']);

                // Generate PO/SPK dari CS (sesuai prefix)
                try {
                    // $potype: 'PO' atau 'SPK'
                    $this->generatePOFromCS($cs, auth()->user(), $potype);
                } catch (\Throwable $e) {
                    \Log::error('generatePOFromCS failed', [
                        'csid' => $cs->csid,
                        'error' => $e->getMessage(),
                    ]);
                }

                // Notifikasi ke requester (creator) - status Complete
                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $cs->csid,
                    'CS',    // docname untuk email
                    'C',     // status Complete
                    $cs->created_by,
                    $docUrl,
                    [
                        'cpnyid'    => $cs->cpny_id ?? $cs->cpnyid ?? '',
                        'deptname'  => $cs->department_id ?? $cs->departementid ?? '',
                        'date'      => $cs->csdate,
                        // Info: ambil dari sumber header bila ada, fallback ke CS
                        'info'      => optional($srcHeader)->keperluan ?? $cs->keperluan,
                        'fullname'  => $fullname,
                        'name'      => $fullname,
                        'createdby' => $fullname,
                    ]
                );
            },

            // === NEXT APPROVER CALLBACK ===
            function ($next, \Carbon\Carbon $now) use ($cs, $docUrl) {

                // Notifikasi ke approver berikutnya (status Pending)
                app(\App\Http\Controllers\ApprovalController::class)->notifyFirstApprover(
                    $cs->csid, // refnbr
                    'CS',      // doctype code
                    'P',       // status Waiting Approval
                    'CS',      // docname untuk email
                    $docUrl,
                    [
                        'info'      => $cs->keperluan,
                        'createdby' => $cs->created_by,
                        'date'      => $now->toDateTimeString(),
                    ]
                );

                // Jejak "terakhir diproses" (optional)
                $cs->completed_by = auth()->user()->username;
                $cs->completed_at = $now;
                $cs->save();
            }
        );

        if (!($result['ok'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Approve failed'
            ], 403);
        }

        return response()->json(['success' => true, 'message' => 'Task approved successfully']);
    }

    public function rejectCS(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'CS';

        $cs = \App\Models\TrCS::with('creator')->where('csid', $docid)->first();
        if (!$cs) return response()->json(['success'=>false,'message'=>'CS not found'],404);

        // (opsional) ambil sumber header untuk info keperluan
        $srcHeader = null;
        $prefix = strtoupper(substr((string) $cs->sppbjktid, 0, 2));
        if ($prefix === 'PB') {
            $srcHeader = \App\Models\TrSPPB::with(['requestType','creator','purchaser'])
                ->where('sppbid', $cs->sppbjktid)->first();
        } elseif ($prefix === 'PJ') {
            $srcHeader = \App\Models\TrSPPJ::with(['requestType','creator','purchaser'])
                ->where('sppjid', $cs->sppbjktid)->first();
        } elseif ($prefix === 'PK') {
            $srcHeader = \App\Models\TrSPPK::with(['requestType','creator','purchaser'])
                ->where('sppkid', $cs->sppbjktid)->first();
        } elseif ($prefix === 'PT') {
            $srcHeader = \App\Models\TrSPPT::with(['requestType','creator','purchaser'])
                ->where('spptid', $cs->sppbjktid)->first();
        }

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($cs->id);
        $docUrl   = url('/showcs/' . $eid);
        $fullname = data_get($cs, 'creator.name') ?: $cs->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->rejectStep(
            $cs->csid,           // refnbr
            $doctype,            // CS
            $user->username,     // actor
            $user->name,         // actor

            // CALLBACK saat reject benar-benar dieksekusi
            function (string $refnbr, \Carbon\Carbon $now) use ($cs, $fullname, $docUrl, $srcHeader) {
                // Header -> R
                $cs->status       = 'R';
                $cs->completed_by = auth()->user()->username;
                $cs->completed_at = $now;
                $cs->save();

                // (opsional) detail -> R
                // \App\Models\TrCSdetail::where('csid', $cs->csid)->update(['status' => 'R']);

                // Email requester
                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $cs->csid,
                    'CS',
                    'R',
                    $cs->created_by,
                    $docUrl,
                    [
                        'cpnyid'    => $cs->cpny_id ?? $cs->cpnyid ?? '',
                        'deptname'  => $cs->department_id ?? $cs->departementid ?? '',
                        'date'      => $now->toDateString(),
                        'info'      => optional($srcHeader)->keperluan ?? $cs->keperluan,
                        'fullname'  => $fullname,
                        'name'      => $fullname,
                        'createdby' => $fullname,
                    ]
                );

                // Simpan komentar (jika ada)
                try {
                    app('App\Http\Controllers\SendCommentController')->sendmsg($cs->id, 'CS', request());
                } catch (\Throwable $e) {}
            }
        );

        if (!($result['ok'] ?? false)) {
            return response()->json(['success'=>false,'message'=>$result['message'] ?? 'Reject failed'], 403);
        }

        return response()->json(['success'=>true,'message'=>'CS rejected successfully']);
    }

    public function reviseCS(Request $request, $docid)
    {
        $user    = $request->user();
        $doctype = 'CS';

        $cs = \App\Models\TrCS::with('creator')->where('csid', $docid)->first();
        if (!$cs) return response()->json(['success'=>false,'message'=>'CS not found'],404);

        // (opsional) ambil sumber header untuk info keperluan
        $srcHeader = null;
        $prefix = strtoupper(substr((string) $cs->sppbjktid, 0, 2));
        if ($prefix === 'PB') {
            $srcHeader = \App\Models\TrSPPB::with(['requestType','creator','purchaser'])
                ->where('sppbid', $cs->sppbjktid)->first();
        } elseif ($prefix === 'PJ') {
            $srcHeader = \App\Models\TrSPPJ::with(['requestType','creator','purchaser'])
                ->where('sppjid', $cs->sppbjktid)->first();
        } elseif ($prefix === 'PK') {
            $srcHeader = \App\Models\TrSPPK::with(['requestType','creator','purchaser'])
                ->where('sppkid', $cs->sppbjktid)->first();
        } elseif ($prefix === 'PT') {
            $srcHeader = \App\Models\TrSPPT::with(['requestType','creator','purchaser'])
                ->where('spptid', $cs->sppbjktid)->first();
        }

        $eid      = \Vinkla\Hashids\Facades\Hashids::encode($cs->id);
        $docUrl   = url('/showcs/' . $eid);
        $fullname = data_get($cs, 'creator.name') ?: $cs->created_by;

        $result = app(\App\Http\Controllers\ApprovalController::class)->reviseStep(
            $cs->csid,           // refnbr
            $doctype,            // CS
            $user->username,     // actor
            $user->name,         // actor

            // CALLBACK saat revise benar-benar dieksekusi
            function (string $refnbr, \Carbon\Carbon $now) use ($cs, $fullname, $docUrl, $srcHeader) {
                // Header -> D
                $cs->status       = 'D';
                $cs->completed_by = auth()->user()->username;
                $cs->completed_at = $now;
                $cs->save();

                // (opsional) detail -> D
                // \App\Models\TrCSdetail::where('csid', $cs->csid)->update(['status' => 'D']);

                // Email requester
                app(\App\Http\Controllers\ApprovalController::class)->notifyRequesterOnStatus(
                    $cs->csid,
                    'CS',
                    'D',
                    $cs->created_by,
                    $docUrl,
                    [
                        'cpnyid'    => $cs->cpny_id ?? $cs->cpnyid ?? '',
                        'deptname'  => $cs->department_id ?? $cs->departementid ?? '',
                        'date'      => $now->toDateString(),
                        'info'      => optional($srcHeader)->keperluan ?? $cs->keperluan,
                        'fullname'  => $fullname,
                        'name'      => $fullname,
                        'createdby' => $fullname,
                    ]
                );

                // Simpan komentar (jika ada)
                try {
                    app('App\Http\Controllers\SendCommentController')->sendmsg($cs->id, 'CS', request());
                } catch (\Throwable $e) {}
            }
        );

        if (!($result['ok'] ?? false)) {
            return response()->json(['success'=>false,'message'=>$result['message'] ?? 'Revise failed'], 403);
        }

        return response()->json(['success'=>true,'message'=>'CS revised successfully']);
    }
    

    // public function approveCS_xxx(Request $request, $docid)
    // {
    //     $now  = Carbon::now();
    //     $user = $request->user();

    //     // $cs = TrCS::where('csid', $docid)->first();
    //     $cs = TrCS::with('creator')
    //         ->where('csid', $docid)
    //         ->first();
    //     $fullname = data_get($cs, 'creator.name') ?: $cs->created_by;

    //     $prefix = strtoupper(substr((string)$cs->sppbjktid, 0, 2));

    //     if ($prefix == 'PB') {
    //             $srcHeader = TrSPPB::with(['requestType', 'creator', 'purchaser'])->where('sppbid', $cs->sppbjktid)->first();   
    //             $potype = 'PO';           
    //     } else if ($prefix == 'PJ') {
    //             $srcHeader = TrSPPJ::with(['requestType', 'creator', 'purchaser'])->where('sppjid', $cs->sppbjktid)->first();   
    //             $potype = 'SPK';            
    //     } else if ($prefix == 'PK') {
    //             $srcHeader = TrSPPK::with(['requestType', 'creator', 'purchaser'])->where('sppkid', $cs->sppbjktid)->first();     
    //             $potype = 'SPK';           
    //     } else if ($prefix == 'PT') {
    //             $srcHeader = TrSPPT::with(['requestType', 'creator', 'purchaser'])->where('spptid', $cs->sppbjktid)->first();  
    //             $potype = 'SPK';            
    //     } else {
    //         abort(422, 'Invalid doc type');
    //     }   

    //     if (!$cs) {
    //         return response()->json(['success' => false, 'message' => 'CS not found'], 404);
    //     }

    //     // pastikan user memang approver aktif (status P) di doc ini
    //     $tApproval = T_approval::where('docid', $cs->csid)
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
    //         $cs->completed_by = $user->username;
    //         $cs->completed_at = $now;
    //         $cs->save();

    //         // Hitung sisa pending setelah approve ini
    //         $pendingCount = T_approval::where('docid', $cs->csid)
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

    //         $eid = Hashids::encode($cs->id);
    //         // dd($pendingCount);
    //         if ($pendingCount === 0) {
    //             // Tidak ada approver lagi -> dokumen complete
    //             $cs->status       = 'C';
    //             $cs->completed_by = $user->username;
    //             $cs->completed_at = $now;
    //             $cs->save();

    //             $csdetail = TrCSdetail::where('csid', $cs->csid)                
    //                 ->get();

    //             foreach ($csdetail as $d) {
    //                 $d->status = 'C'; 
    //                 $d->save();
    //             }

    //             // Kirim email ke requester (creator)
    //             $status        = 'C';
    //             $subjectSuffix = $subjectMap[$status] ?? 'Notification';

    //             $this->generatePOFromCS($cs, $user, $potype);
                

    //             $data = [
    //                 'docid'     => $cs->csid,
    //                 'cpnyid'    => $cs->cpny_id ?? $cs->cpnyid ?? '',
    //                 'deptname'  => $cs->department_id ?? $cs->departementid ?? '',
    //                 'date'      => $cs->csdate,
    //                 'fullname'  => $fullname,  // nama penerima di email
    //                 'name'      => $fullname,  // fallback
    //                 'createdby' => $fullname,
    //                 'docname'   => 'CS',
    //                 'info'      => $srcHeader->keperluan,
    //                 'status'    => $status,
    //                 'url'       => url('/showcs/' . $eid),
    //             ];

    //             $recipients = User::where('username', $cs->created_by)
    //                 ->where('status', 'A')
    //                 ->get();

    //             foreach ($recipients as $rcp) {
    //                 try {
    //                     Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $rcp, $subjectSuffix) {
    //                         $to = $rcp->test_email ?? $rcp->email; // pakai field yang memang ada
    //                         $message->to($to)
    //                             ->subject($data['docid'] . ' - ' . $subjectSuffix . ' CS')
    //                             ->from('digitalserver@pakuwon.com', 'Pakuwon System');
    //                     });
    //                 } catch (\Throwable $e) {
    //                     Log::error('Failed sending CS completion email', ['error' => $e->getMessage()]);
    //                 }
    //             }
    //         } else {
    //             // Masih ada approver berikutnya -> cari level berikutnya (P terrendah aprvid)
    //             $next = T_approval::where('docid', $cs->csid)
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
    //                     'createdby' => $cs->created_by,
    //                     'docname'   => 'CS',
    //                     'info'      => $cs->keperluan,
    //                     'status'    => $status,
    //                     'url'       => url('/showcs/' . $eid),
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
    //                                     ->subject($data['docid'] . ' - ' . $subjectSuffix . ' CS')
    //                                     ->from('digitalserver@pakuwon.com', 'Pakuwon System');
    //                             });
    //                         } catch (\Throwable $e) {
    //                             Log::error('Failed sending CS waiting-approval email', ['error' => $e->getMessage()]);
    //                         }
    //                     }
    //                 } else {
    //                     Log::warning('Next approver has empty aprvusername list', ['docid' => $cs->csid]);
    //                 }
    //             }
    //         }

    //         DB::commit();
    //         return response()->json(['success' => true, 'message' => 'Task approved successfully']);
    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         Log::error('Approve CS failed', ['error' => $e->getMessage()]);
    //         return response()->json(['success' => false, 'message' => 'Approve failed'], 500);
    //     }
    // }
    
    // public function rejectCS_xxx(Request $request, $docid)
    // {
    //     $now  = Carbon::now();
    //     $user = $request->user();

    //     // $cs = TrCS::where('csid', $docid)->first();
    //     $cs = TrCS::with('creator')
    //         ->where('csid', $docid)
    //         ->first();
    //     $fullname = data_get($cs, 'creator.name') ?: $cs->created_by;

    //     $prefix = strtoupper(substr((string)$cs->sppbjktid, 0, 2));

    //     if ($prefix == 'PB') {
    //             $srcHeader = TrSPPB::with(['requestType', 'creator', 'purchaser'])->where('sppbid', $cs->sppbjktid)->first();              
    //     } else if ($prefix == 'PJ') {
    //             $srcHeader = TrSPPJ::with(['requestType', 'creator', 'purchaser'])->where('sppjid', $cs->sppbjktid)->first();               
    //     } else if ($prefix == 'PK') {
    //             $srcHeader = TrSPPK::with(['requestType', 'creator', 'purchaser'])->where('sppkid', $cs->sppbjktid)->first();                
    //     } else if ($prefix == 'PT') {
    //             $srcHeader = TrSPPT::with(['requestType', 'creator', 'purchaser'])->where('spptid', $cs->sppbjktid)->first();              
    //     } else {
    //         abort(422, 'Invalid doc type');
    //     }   

    //     if (!$cs) {
    //         return response()->json(['success' => false, 'message' => 'Task not found'], 404);
    //     }

    //     // Validasi: user harus approver aktif (status P) pada dokumen ini
    //     $tApproval = T_approval::where('docid', $cs->csid)
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

    //         // Update header CS
    //         $cs->status       = 'R';
    //         $cs->completed_by = $user->username;
    //         $cs->completed_at = $now;
    //         $cs->save();

    //         // Batalkan semua approval yang masih pending
    //         T_approval::where('docid', $cs->csid)
    //             ->where('status', 'P')
    //             ->update(['status' => 'X']);

    //         DB::commit();
    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         Log::error('Reject CS failed', ['docid' => $docid, 'error' => $e->getMessage()]);
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

    //     $eid = Hashids::encode($cs->id);

    //     $data = [
    //         'docid'     => $cs->csid,
    //         'cpnyid'    => $cs->cpny_id ?? $cs->cpnyid ?? '',
    //         'deptname'  => $cs->department_id ?? $cs->departementid ?? '',
    //         'date'      => $now->toDateString(),            // bisa juga pakai $tApproval->aprvdateafter
    //         'fullname'  => $fullname,               // view email kita pakai $fullname
    //         'name'      => $fullname,               // fallback jika view pakai $name
    //         'createdby' => $fullname,
    //         'docname'   => 'CS',
    //         'info'      => $srcHeader->keperluan,
    //         'status'    => $status,
    //         'url'       => url('/showcs/' . $eid),
    //     ];

    //     $recipients = User::where('username', $cs->created_by)
    //         ->where('status', 'A')
    //         ->get();

    //     foreach ($recipients as $rcp) {
    //         try {
    //             $to = $rcp->test_email ?? $rcp->email; // sesuaikan field yang tersedia
    //             Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
    //                 $message->to($to)
    //                     ->subject($data['docid'] . ' - ' . $subjectSuffix . ' CS')
    //                     ->from('digitalserver@pakuwon.com', 'Pakuwon System');
    //             });
    //         } catch (\Throwable $e) {
    //             Log::error('Failed sending CS rejected email', [
    //                 'docid' => $data['docid'],
    //                 'to'    => $rcp->username,
    //                 'error' => $e->getMessage()
    //             ]);
    //         }
    //     }

    //     // Simpan komentar penolakan (jika ada)
    //     try {
    //         app('App\Http\Controllers\SendCommentController')
    //             ->sendmsg($cs->id, 'CS', $request);
    //     } catch (\Throwable $e) {
    //         Log::warning('SendComment after reject failed', [
    //             'docid' => $cs->csid,
    //             'error' => $e->getMessage()
    //         ]);
    //     }

    //     return response()->json(['success' => true, 'message' => 'CS rejected successfully']);
    // }

    // public function reviseCS_xxx(Request $request, $docid)
    // {
    //     $now  = Carbon::now();
    //     $user = $request->user();

    //     // $cs = TrCS::where('csid', $docid)->first();
    //     $cs = TrCS::with('creator')
    //         ->where('csid', $docid)
    //         ->first();

    //     $fullname = data_get($cs, 'creator.name') ?: $cs->created_by;

    //     $prefix = strtoupper(substr((string)$cs->sppbjktid, 0, 2));

    //     if ($prefix == 'PB') {
    //             $srcHeader = TrSPPB::with(['requestType', 'creator', 'purchaser'])->where('sppbid', $cs->sppbjktid)->first();              
    //     } else if ($prefix == 'PJ') {
    //             $srcHeader = TrSPPJ::with(['requestType', 'creator', 'purchaser'])->where('sppjid', $cs->sppbjktid)->first();               
    //     } else if ($prefix == 'PK') {
    //             $srcHeader = TrSPPK::with(['requestType', 'creator', 'purchaser'])->where('sppkid', $cs->sppbjktid)->first();                
    //     } else if ($prefix == 'PT') {
    //             $srcHeader = TrSPPT::with(['requestType', 'creator', 'purchaser'])->where('spptid', $cs->sppbjktid)->first();              
    //     } else {
    //         abort(422, 'Invalid doc type');
    //     }           
            
    //     if (!$cs) {
    //         return response()->json(['success' => false, 'message' => 'CS not found'], 404);
    //     }

    //     // Pastikan user adalah approver aktif (status P) dokumen ini
    //     $tApproval = T_approval::where('docid', $cs->csid)
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

    //         // Update header CS
    //         $cs->status       = 'D';
    //         $cs->completed_by = $user->username;        // mengikuti pola existing
    //         $cs->completed_at = $now;
    //         $cs->save();

    //         // Batalkan approval lain yang masih pending
    //         T_approval::where('docid', $cs->csid)
    //             ->where('status', 'P')
    //             ->update(['status' => 'X']);

    //         DB::commit();
    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         Log::error('Revise CS failed', ['docid' => $docid, 'error' => $e->getMessage()]);
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

    //     $eid = Hashids::encode($cs->id);

    //     $data = [
    //         'docid'     => $cs->csid,
    //         'cpnyid'    => $cs->cpny_id ?? $cs->cpnyid ?? '',
    //         'deptname'  => $cs->department_id ?? $cs->departementid ?? '',
    //         'date'      => $now->toDateString(),          // atau $tApproval->aprvdateafter
    //         'fullname'  => $fullname,             // template email pakai $fullname
    //         'name'      => $fullname,             // fallback jika view pakai $name
    //         'createdby' => $fullname,
    //         'docname'   => 'CS',
    //         'info'      => $srcHeader->keperluan,
    //         'status'    => $status,
    //         'url'       => url('/showcs/' . $eid),
    //     ];

    //     $recipients = User::where('username', $cs->created_by)
    //         ->where('status', 'A')
    //         ->get();

    //     foreach ($recipients as $rcp) {
    //         try {
    //             $to = $rcp->test_email ?? $rcp->email; // sesuaikan dengan kolom yang ada
    //             Mail::send('emails.mailapprovenew', $data, function ($message) use ($data, $to, $subjectSuffix) {
    //                 $message->to($to)
    //                     ->subject($data['docid'] . ' - ' . $subjectSuffix . ' CS')
    //                     ->from('digitalserver@pakuwon.com', 'Pakuwon System');
    //             });
    //         } catch (\Throwable $e) {
    //             Log::error('Failed sending CS revise email', [
    //                 'docid' => $data['docid'],
    //                 'to'    => $rcp->username,
    //                 'error' => $e->getMessage()
    //             ]);
    //         }
    //     }

    //     // Simpan komentar revisi (jika ada)
    //     try {
    //         app('App\Http\Controllers\SendCommentController')
    //             ->sendmsg($cs->id, 'CS', $request);
    //     } catch (\Throwable $e) {
    //         Log::warning('SendComment after revise failed', [
    //             'docid' => $cs->csid,
    //             'error' => $e->getMessage()
    //         ]);
    //     }

    //     return response()->json(['success' => true, 'message' => 'CS revised successfully']);
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

    public function printCS($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $authUser = Auth::user();
        if (!$authUser) {
            return redirect()->route('login');
        }

        // Header CS + relasi
        $cs = TrCS::with([
            'creator:username,name',
            'updater:username,name',
            'completer:username,name',                      
        ])->findOrFail($id);

        // Detail
        $csdetail = TrCSdetail::with([
            'location:location_id,location_name',
            'subLocation:sub_location_id,sub_location_name',
        ])->where('csid', $cs->csid)->orderBy('cs_no')->get();

        // Approval
        $approval = T_approval::where('docid', $cs->csid)
            ->where('status', '<>', 'X')
            ->orderBy('aprvid')->orderBy('created_at')->get();
        $approve_count = $approval->count();

        // Company
        $company = Company::where('cpnyid', $cs->cpny_id)->first();

        // Map status
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

        // --- susun daftar vendor dinamis dari kolom vendor1..vendor6 di header CS
        $vendors = [];
        for ($i = 1; $i <= 6; $i++) {
            $idCol   = "vendorid{$i}";
            $nameCol = "vendorname{$i}";
            if (!filled($cs->{$idCol}) && !filled($cs->{$nameCol})) continue;

            $vendors[] = [
                'idx'         => $i,
                'id'          => $cs->{$idCol},
                'name'        => $cs->{$nameCol},
                'addr'        => $cs->{"vendoralamat{$i}"} ?? null,
                'cp'          => $cs->{"vendorcp{$i}"} ?? null,
                'telp'        => $cs->{"vendortelp{$i}"} ?? null,
                'top'         => $cs->{"vendortop{$i}"} ?? null,
                // ringkasan
                'total'       => (float) ($cs->{"totalvendor{$i}"} ?? 0),
                'tax'         => (float) ($cs->{"taxvendor{$i}"} ?? 0),
                'grand'       => (float) ($cs->{"grandtotalvendor{$i}"} ?? 0),
                'grandselected'       => (float) ($cs->{"grandtotalselectedvendor{$i}"} ?? 0),
            ];
        }
        $vendorCount = count($vendors); // dipakai view untuk lebar kolom dll.

        $data = [
            'title'               => 'Canvass Sheet',
            'doc_type'            => 'CS',
            'docid'               => $cs->csid,
            'department_id'       => $cs->department_id,
            'cpnyname'            => optional($company)->cpnyname,
            'parent'              => optional($company)->parent,
            'project'             => optional($company)->project,
            'created_by_username' => $cs->created_by,
            'created_by_name'     => ucwords(strtolower(optional($cs->creator)->name)),
            'created_at_fmt'      => optional($cs->created_at)->format('d F Y'),
            'req_date_fmt'        => optional($cs->created_at)->format('d M Y H:i'),
            'csdate'              => \Carbon\Carbon::parse($cs->csdate)->format('d F Y'),
            'keperluan'           => $cs->csnote ?? $cs->keperluan, // pilih yang tersedia
            'status_doc'          => $status_doc,
            'requesttype_name'    => optional($cs->requestType)->requesttype_name,
            'vendors'             => $vendors,
            'vendorCount'         => $vendorCount,
        ];

        $pdf = \PDF::loadView('pages.canvass.pdf_cs', array_merge($data, [
            'detail'        => $csdetail,
            'approval'      => $approval,
            'approve_count' => $approve_count,
        ]));

        // SELALU landscape (sesuai permintaan)
        $pdf->setPaper('A4', 'landscape');

        return $pdf->stream("pdf_cs_{$cs->csid}.pdf");
    }

   

    private function generatePOFromCS_xxx(TrCS $cs, $user, $potype): void
    {
        // dd('hai');
        // Idempotent: kalau sudah ada PO untuk CS ini, jangan bikin lagi
        $already = TrPO::where('csid', $cs->csid)->exists();       
        if ($already) return;
        
        // Ambil semua detail CS
        $details = TrCSdetail::where('csid', $cs->csid)->get();
        if ($details->isEmpty()) return;

        // Kelompokkan baris per vendor yg DIPILIH (vendor1..vendor6)
        // map: vendorIndex(1..6) => [rows...]
        $pickedByVendorIdx = collect([1,2,3,4,5,6])->mapWithKeys(function($i){
            return [$i => collect()];
        });

        foreach ($details as $row) {
            for ($i = 1; $i <= 6; $i++) {
                $sel = (bool) ($row->{"vendor{$i}selected"} ?? false);
                $vid = $row->{"vendorid{$i}"} ?? null;
                if ($sel && $vid) {
                    $pickedByVendorIdx[$i] = $pickedByVendorIdx[$i]->push($row);
                    break; // satu baris hanya boleh 1 vendor terpilih
                }
            }
        }

        // Tidak ada vendor terpilih? stop
        $nonEmptyGroups = $pickedByVendorIdx->filter(fn($g)=>$g->isNotEmpty());
        if ($nonEmptyGroups->isEmpty()) return;

        // nomor otomatis (pakai tabel autonbr doctype=PO, format: POYYMM####)
        $now   = Carbon::now();
       
        // nomor otomatis 10 digit per company (tanpa tahun/bulan)
        $mkPonbr = function() use ($cs) {
            $company = strtoupper((string)$cs->cpny_id);
            $digits  = 10;

            // base counter per company
            $base = ($company === 'GPS') ? 0 : 8000000000;

            // lock row autonbr untuk company ini, tanpa year/month
            $autonbr = Autonbr::lockForUpdate()
                ->where('doctype', $company)               
                ->first();

            // ambil nilai saat ini (kalau belum ada, mulai dari base)
            $current = $autonbr ? (int)$autonbr->number : (int)$base;

            // nomor berikutnya
            $next = $current + 1;

            if (!$autonbr) {
                $autonbr = Autonbr::create([
                    'doctype' => $company,                   
                    'status'  => 'A',
                    'number'  => $next,   // simpan posisi counter setelah dipakai
                ]);
            } else {
                $autonbr->update(['number' => $next]);
            }

            // format 10 digit (tanpa prefix)
            $ponbr = str_pad((string)$next, $digits, '0', STR_PAD_LEFT);

            // kalau Anda masih ingin menyimpan "poautonbr" sebagai angka mentah, return keduanya
            return [$ponbr, $next];
        };

        
        DB::connection('pgsql')->beginTransaction();
        try {

            foreach ($nonEmptyGroups as $i => $rows) {

                // Ambil info vendor dari header CS (vendorid{i}, vendorname{i}, dst.)
                $vendorId   = $cs->{"vendorid{$i}"}      ?? null;
                $vendorName = $cs->{"vendorname{$i}"}    ?? null;
                $vendorAddr = $cs->{"vendoralamat{$i}"}  ?? null;
                $vendorTelp = $cs->{"vendortelp{$i}"}    ?? null;
                $vendorCP   = $cs->{"vendorcp{$i}"}      ?? null;
                $vendorTOP  = $cs->{"vendortop{$i}"}     ?? null;

                if (!$vendorId) continue; // skip kalau header kosong

                // Totals ambil dari kolom "selected" versi header CS (sudah dihitung saat submit)
                $ppnPct      = (float) ($cs->{"ppnvendor{$i}"} ?? 0);   // %
                $pphPct      = (float) ($cs->{"pphvendor{$i}"} ?? 0);   // %
                $totalSel    = (float) ($cs->{"totalselectedvendor{$i}"}      ?? 0);
                $taxSel      = (float) ($cs->{"taxselectedvendor{$i}"}        ?? 0);
                $grandSel    = (float) ($cs->{"grandtotalselectedvendor{$i}"} ?? 0);
                $taxCodeId   = $cs->{"taxcodevendor{$i}"} ?? null;

                // Generate nomor PO
                [$ponbr, $poautonbr] = $mkPonbr();

                // === PO HEADER ===
                $po = new TrPO();
                $po->setConnection('pgsql');

                $po->ponbr           = $ponbr;
                $po->poautonbr       = $poautonbr;
                $po->podate          = $now->toDateString();
                $po->potype          = $potype; 
                $po->cpny_id         = $cs->cpny_id;
                $po->csid            = $cs->csid;
                $po->sppbjktid       = $cs->sppbjktid;
                $po->department_id   = $cs->department_id;
                $po->user_peminta    = $cs->user_peminta;
                $po->keperluan       = $cs->csnote ?? $cs->keperluan;

                $po->ponote          = null;

                $po->vendorid        = $vendorId;
                $po->vendorname      = $vendorName;
                $po->vendoralamat    = $vendorAddr;
                $po->vendortelp      = $vendorTelp;
                $po->vendorcp        = $vendorCP;
                $po->vendortop       = $vendorTOP;

                $po->totalamt        = $totalSel;
                $po->taxcodeid       = $taxCodeId;
                $po->taxamt          = $taxSel;
                $po->grandtotalamt   = $grandSel;
                $po->totalqty        = 0;
                $po->totalqtyreceived = 0;

                $po->submitdate      = $now;
                // field tanggal pengiriman/kontrak bisa diisi belakangan
                $po->status          = 'H'; // draft/hold; sesuaikan workflow-mu
                $po->created_by      = $cs->created_by ?? 'system';

                $po->save();
                
                $totalQty = 0;                
                // === PO DETAIL untuk vendor ini ===
                foreach ($rows as $row) {
                    $unitCost  = (float) ($row->{"vendorprice{$i}"}      ?? 0);
                    $totalCost = (float) ($row->{"vendortotalprice{$i}"} ?? 0);
                    $lineTax   = $totalCost * (($ppnPct + $pphPct) / 100);

                    $pd = new TrPOdetail();

                    $pd->ponbr              = $ponbr;
                    $pd->csid               = $cs->csid;
                    $pd->cs_no              = $row->cs_no ?? null;
                    $pd->sppbjktid          = $row->sppbjktid ?? $cs->sppbjktid;
                    $pd->sppbjktid_no       = $row->sppbjkt_no ?? null;

                    $pd->inventory_type     = $row->inventory_type ?? null;
                    $pd->inventoryid        = $row->inventoryid;
                    $pd->inventory_descr    = $row->inventory_descr;
                    $pd->ponote_detail      = $row->csnote_detail ?? null;

                    $pd->qty                = (float) $row->qty;
                    $pd->uom                = $row->uom;

                    $pd->type_multiplier    = $row->type_multiplier ?? null;
                    $pd->base_multiplier    = $row->base_multiplier ?? null;
                    $pd->base_qty           = $row->base_qty ?? null;
                    $pd->base_uom           = $row->base_uom ?? null;

                    $pd->unitcost           = $unitCost;
                    $pd->taxcodeid          = $taxCodeId; // boleh null
                    $pd->taxamt             = $lineTax;
                    $pd->totalcost          = $totalCost;

                    // qty states (pastikan semuanya diisi agar kolom insert konsisten)
                    $pd->qty_received       = 0;
                    $pd->base_qty_received  = 0;
                    $pd->qty_return         = 0;
                    $pd->base_qty_return    = 0;
                    $pd->qty_completed      = 0;
                    $pd->base_qty_completed = 0;

                    $pd->received           = false;
                    $pd->completed          = false;
                    $pd->canceled           = false;

                    // ⬇️ gunakan kolom yang benar (BUKAN account_id/activity_id)
                    $pd->budget_cpny_id           = $row->budget_cpny_id           ?? null;
                    $pd->budget_business_unit_id  = $row->budget_business_unit_id  ?? null;
                    $pd->budget_department_fin_id = $row->budget_department_fin_id ?? null;
                    $pd->budget_account_id        = $row->budget_account_id        ?? null;
                    $pd->budget_activity_id       = $row->budget_activity_id       ?? null;
                    $pd->budget_activity_descr    = $row->budget_activity_descr    ?? null;
                    $pd->budget_perpost           = $row->budget_perpost           ?? null;

                    $pd->status             = 'H';
                    $pd->created_by         = $user->username ?? 'system';

                    $pd->save();

                    $totalQty += (float) $row->qty;
                }

                // update totalqty header
                $po->totalqty = $totalQty;                
                $po->save();

            }

            DB::connection('pgsql')->commit();

        } catch (\Throwable $e) {
            DB::connection('pgsql')->rollBack();
            // catat error tapi jangan batalkan approve yg sudah C
            Log::error('Generate PO from CS failed', [
                'csid' => $cs->csid,
                'error'=> $e->getMessage()
            ]);
        }
    }

    private function generatePOFromCS(TrCS $cs, $user, $potype): void
    {
        // Idempotent: kalau sudah ada PO untuk CS ini, jangan bikin lagi
        $already = TrPO::where('csid', $cs->csid)->exists();
        if ($already) return;

        $details = TrCSdetail::where('csid', $cs->csid)->get();
        if ($details->isEmpty()) return;

        // Kelompokkan baris per vendor terpilih
        $pickedByVendorIdx = collect([1,2,3,4,5,6])->mapWithKeys(fn($i) => [$i => collect()]);
        foreach ($details as $row) {
            for ($i = 1; $i <= 6; $i++) {
                $sel = (bool) ($row->{"vendor{$i}selected"} ?? false);
                $vid = $row->{"vendorid{$i}"} ?? null;
                if ($sel && $vid) {
                    $pickedByVendorIdx[$i] = $pickedByVendorIdx[$i]->push($row);
                    break; // satu baris hanya 1 vendor terpilih
                }
            }
        }
        $nonEmptyGroups = $pickedByVendorIdx->filter(fn($g) => $g->isNotEmpty());
        if ($nonEmptyGroups->isEmpty()) return;

        $now = Carbon::now();

        // Generator nomor 10 digit per company (tanpa prefix)
        $mkPonbr = function() use ($cs) {
            $company = strtoupper((string)$cs->cpny_id);
            $digits  = 10;
            $base    = ($company === 'GPS') ? 0 : 8000000000;

            $autonbr = Autonbr::lockForUpdate()
                ->where('doctype', $company)
                ->first();

            $current = $autonbr ? (int)$autonbr->number : (int)$base;
            $next    = $current + 1;

            if (!$autonbr) {
                $autonbr = Autonbr::create([
                    'doctype' => $company,
                    'status'  => 'A',
                    'number'  => $next,
                ]);
            } else {
                $autonbr->update(['number' => $next]);
            }

            $ponbr = str_pad((string)$next, $digits, '0', STR_PAD_LEFT);
            return [$ponbr, $next];
        };

        DB::connection('pgsql')->beginTransaction();
        try {
            foreach ($nonEmptyGroups as $i => $rows) {
                // Info vendor dari header CS
                $vendorId   = $cs->{"vendorid{$i}"}     ?? null;
                $vendorName = $cs->{"vendorname{$i}"}   ?? null;
                $vendorAddr = $cs->{"vendoralamat{$i}"} ?? null;
                $vendorTelp = $cs->{"vendortelp{$i}"}   ?? null;
                $vendorCP   = $cs->{"vendorcp{$i}"}     ?? null;
                $vendorTOP  = $cs->{"vendortop{$i}"}    ?? null;

                if (!$vendorId) continue;

                // Pajak & tax code (dipakai untuk kalkulasi tiap detail)
                $ppnPct    = (float) ($cs->{"ppnvendor{$i}"} ?? 0);
                $pphPct    = (float) ($cs->{"pphvendor{$i}"} ?? 0);
                $taxCodeId = $cs->{"taxcodevendor{$i}"} ?? null;

                // Nomor PO
                [$ponbr, $poautonbr] = $mkPonbr();

                // ===== PO HEADER (sementara total=0, akan di-update setelah loop detail) =====
                $po = new TrPO();
                $po->setConnection('pgsql');

                $po->ponbr             = $ponbr;
                $po->poautonbr         = $poautonbr;
                $po->podate            = $now->toDateString();
                $po->potype            = $potype;
                $po->cpny_id           = $cs->cpny_id;
                $po->csid              = $cs->csid;
                $po->sppbjktid         = $cs->sppbjktid;
                $po->department_id     = $cs->department_id;
                $po->user_peminta      = $cs->user_peminta;
                $po->keperluan         = $cs->csnote ?? $cs->keperluan;
                $po->ponote            = null;

                $po->vendorid          = $vendorId;
                $po->vendorname        = $vendorName;
                $po->vendoralamat      = $vendorAddr;
                $po->vendortelp        = $vendorTelp;
                $po->vendorcp          = $vendorCP;
                $po->vendortop         = $vendorTOP;

                // total akan dihitung dari detail:
                $po->totalamt          = 0;
                $po->taxcodeid         = $taxCodeId;
                $po->taxamt            = 0;
                $po->grandtotalamt     = 0;
                $po->totalqty          = 0;
                $po->totalqtyreceived  = 0;

                $po->submitdate        = $now;
                $po->status            = 'H';
                $po->created_by        = $cs->created_by ?? 'system';
                $po->save();

                // ===== PO DETAIL =====
                $totalQty  = 0.0;
                $sumTotal  = 0.0; // jumlah totalcost detail
                $sumTax    = 0.0; // jumlah tax detail
                $lineNo    = 0;   // penomoran po_no per vendor

                foreach ($rows as $row) {
                    $lineNo++;

                    $unitCost  = (float) ($row->{"vendorprice{$i}"}      ?? 0);
                    $totalCost = (float) ($row->{"vendortotalprice{$i}"} ?? 0);

                    // jika model pajak: tax = total * (PPN+PPH)
                    $lineTax   = $totalCost * (($ppnPct + $pphPct) / 100);

                    $pd = new TrPOdetail();
                    $pd->ponbr                 = $ponbr;
                    $pd->po_no                 = $lineNo; // ← nomor urut 1,2,3,...

                    $pd->csid                  = $cs->csid;
                    $pd->cs_no                 = $row->cs_no ?? null;
                    $pd->sppbjktid             = $row->sppbjktid ?? $cs->sppbjktid;
                    $pd->sppbjktid_no          = $row->sppbjkt_no ?? null;

                    $pd->inventory_type        = $row->inventory_type ?? null;
                    $pd->inventoryid           = $row->inventoryid;
                    $pd->inventory_descr       = $row->inventory_descr;
                    $pd->ponote_detail         = $row->csnote_detail ?? null;

                    $pd->qty                   = (float) $row->qty;
                    $pd->uom                   = $row->uom;

                    $pd->type_multiplier       = $row->type_multiplier ?? null;
                    $pd->base_multiplier       = $row->base_multiplier ?? null;
                    $pd->base_qty              = $row->base_qty ?? null;
                    $pd->base_uom              = $row->base_uom ?? null;

                    $pd->unitcost              = $unitCost;
                    $pd->taxcodeid             = $taxCodeId;
                    $pd->taxamt                = $lineTax;
                    $pd->totalcost             = $totalCost;

                    $pd->qty_received          = 0;
                    $pd->base_qty_received     = 0;
                    $pd->qty_return            = 0;
                    $pd->base_qty_return       = 0;
                    $pd->qty_completed         = 0;
                    $pd->base_qty_completed    = 0;

                    $pd->received              = false;
                    $pd->completed             = false;
                    $pd->canceled              = false;

                    $pd->budget_cpny_id           = $row->budget_cpny_id           ?? null;
                    $pd->budget_business_unit_id  = $row->budget_business_unit_id  ?? null;
                    $pd->budget_department_fin_id = $row->budget_department_fin_id ?? null;
                    $pd->budget_account_id        = $row->budget_account_id        ?? null;
                    $pd->budget_activity_id       = $row->budget_activity_id       ?? null;
                    $pd->budget_activity_descr    = $row->budget_activity_descr    ?? null;
                    $pd->budget_perpost           = $row->budget_perpost           ?? null;

                    $pd->status                = 'H';
                    $pd->created_by            = $user->username ?? 'system';
                    $pd->save();

                    $totalQty += (float) $row->qty;
                    $sumTotal += $totalCost;
                    $sumTax   += $lineTax;
                }

                // ===== Update totals header dari akumulasi detail =====
                $po->totalqty        = $totalQty;
                $po->totalamt        = $sumTotal;
                $po->taxamt          = $sumTax;
                $po->grandtotalamt   = $sumTotal + $sumTax;
                $po->save();
            }

            DB::connection('pgsql')->commit();
        } catch (\Throwable $e) {
            DB::connection('pgsql')->rollBack();
            Log::error('Generate PO from CS failed', [
                'csid' => $cs->csid,
                'error'=> $e->getMessage()
            ]);
        }
    }

    private function validateSubmitServerSide(array $details): void
    {
        // Minimal 1 baris ada vendor selected kalau ada harga
        $hasAnyPrice = false;
        $everyPricedRowHasPick = true;

        foreach ($details as $row) {
            $rowHasPrice = false;
            $rowHasPick  = false;
            foreach (($row['vendor'] ?? []) as $v) {
                $price = (float)($v['price'] ?? 0);
                if ($price > 0) {
                    $rowHasPrice = true;
                    $hasAnyPrice = true;
                }
                if (!empty($v['selected'])) $rowHasPick = true;
            }
            if ($rowHasPrice && !$rowHasPick) {
                $everyPricedRowHasPick = false;
                break;
            }
        }

        if (!$hasAnyPrice) {
            abort(422, 'Total tidak boleh 0. Isi harga minimal pada salah satu vendor.');
        }
        if (!$everyPricedRowHasPick) {
            abort(422, 'Ada baris yang memiliki harga tetapi belum memilih vendor.');
        }

        // Qty tidak boleh > qty kiriman (front-end sudah batasi; ini redundansi aman)
        foreach ($details as $row) {
            $qty = (float)($row['qty'] ?? 0);
            if ($qty < 0) abort(422, 'Qty tidak valid.');
        }
    }

    private function buildSourceForDoc(string $doc, ?string $srcId): array {
        switch ($doc) {
            case 'SPPB':
                $h = TrSPPB::with(['requestType','creator','purchaser'])->findOrFail($srcId);
                $k = 'sppb_no';
                $d = TrSPPBdetail::where('sppbid', $h->sppbid)->orderBy($k)->get();
                break;
            case 'SPPJ':
                $h = TrSPPJ::with(['requestType','creator','purchaser'])->findOrFail($srcId);
                $k = 'sppj_no';
                $d = TrSPPJdetail::where('sppjid', $h->sppjid)->orderBy($k)->get();
                break;
            case 'SPPK':
                $h = TrSPPK::with(['requestType','creator','purchaser'])->findOrFail($srcId);
                $k = 'sppk_no';
                $d = TrSPPKdetail::where('sppkid', $h->sppkid)->orderBy($k)->get();
                break;
            case 'SPPT':
                $h = TrSPPT::with(['requestType','creator','purchaser'])->findOrFail($srcId);
                $k = 'sppt_no';
                $d = TrSPPTdetail::where('spptid', $h->spptid)->orderBy($k)->get();
                break;
            default: abort(422, 'Invalid doc type');
        }
        $idx = [];
        foreach ($d as $sd) {
            $key = strtoupper(trim($sd->inventoryid ?? '')) . '|' .
                strtoupper(trim($sd->uom ?? '')) . '|' .
                strtoupper(trim($sd->inventory_descr ?? ''));
            $idx[$key] = $sd;
        }
        return [$h, $d, $k, $idx];
    }

    private function updateOrderedOnSource(array $details, $srcHeader, $srcDetails, array $srcIndex, string $cpnyId): void {
        $addedTotalOrdered = 0.0;
        foreach ($details as $i => $d) {
            $hasPick = false;
            foreach (($d['vendor'] ?? []) as $v) { if (!empty($v['selected'])) { $hasPick = true; break; } }
            if (!$hasPick) continue;

            $orderedQty = (float)($d['qty'] ?? 0);
            if ($orderedQty <= 0) continue;

            $key = strtoupper(trim(($d['inventoryid'] ?? ''))) . '|' .
                strtoupper(trim(($d['uom'] ?? ''))) . '|' .
                strtoupper(trim(($d['inventory_descr'] ?? '')));
            $srcDet = $srcIndex[$key] ?? ($srcDetails[$i] ?? null);
            if (!$srcDet) continue;

            $detTable = $srcDet->getTable();
            if (Schema::connection('pgsql')->hasColumn($detTable, 'ordered')) {
                $srcDet->ordered = (float)($srcDet->ordered ?? 0) + $orderedQty;
            }
            if (Schema::connection('pgsql')->hasColumn($detTable, 'openordered')) {
                $srcDet->openordered = max(0, (float)($srcDet->openordered ?? 0) - $orderedQty);
            }
            $srcDet->save();

            $addedTotalOrdered += $orderedQty;
        }

        $hdrTable = $srcHeader->getTable();
        if (Schema::connection('pgsql')->hasColumn($hdrTable, 'totalordered')) {
            $srcHeader->totalordered = (float)($srcHeader->totalordered ?? 0) + $addedTotalOrdered;
        }
        if (Schema::connection('pgsql')->hasColumn($hdrTable, 'totalopenordered')) {
            $srcHeader->totalopenordered = max(0, (float)($srcHeader->totalopenordered ?? 0) - $addedTotalOrdered);
        }
        $srcHeader->save();
    }

    // private function reserveBudget(array $details, string $cpnyId, TrCS $cs, string $username): void {
    //     $csDate   = Carbon::parse($cs->csdate ?? now());
    //     $yearStr  = $csDate->format('Y'); // perpost YYYY
    //     $periodCol = 'period' . $csDate->format('m') . '_reserve';

    //     $buckets = [];
    //     foreach ($details as $d) {
    //         $selectedTotal = 0.0;
    //         foreach (($d['vendor'] ?? []) as $v) { if (!empty($v['selected'])) { $selectedTotal = (float)($v['total'] ?? 0); break; } }
    //         if ($selectedTotal <= 0) continue;

    //         $key = json_encode([
    //             'perpost'           => $yearStr,
    //             'cpny_id'           => $d['budget_cpny_id'] ?? $cpnyId,
    //             'business_unit_id'  => $d['budget_business_unit_id'] ?? null,
    //             'department_fin_id' => $d['budget_department_fin_id'] ?? null,
    //             'account_id'        => $d['budget_account_id'] ?? null,
    //             'activity_id'       => $d['budget_activity_id'] ?? null,
    //         ]);
    //         $buckets[$key] = ($buckets[$key] ?? 0) + $selectedTotal;
    //     }

    //     foreach ($buckets as $keyJson => $amount) {
    //         $crit = json_decode($keyJson, true);
    //         $bd = BudgetDetail::where([['perpost','=',$crit['perpost']],['cpny_id','=',$crit['cpny_id']]])
    //             ->when($crit['business_unit_id'],  fn($q,$v)=>$q->where('business_unit_id',$v))
    //             ->when($crit['department_fin_id'], fn($q,$v)=>$q->where('department_fin_id',$v))
    //             ->when($crit['account_id'],        fn($q,$v)=>$q->where('account_id',$v))
    //             ->when($crit['activity_id'],       fn($q,$v)=>$q->where('activity_id',$v))
    //             ->lockForUpdate()->first();

    //         if (!$bd) {
    //             $bd = new BudgetDetail();
    //             $bd->setConnection('pgsql');
    //             $bd->fill($crit);
    //             $bd->status = 'A';
    //             $bd->created_by = $username;
    //             for ($m=1;$m<=12;$m++){
    //                 $p = 'period'.str_pad($m,2,'0',STR_PAD_LEFT);
    //                 $bd->{$p.'_budget'}  = $bd->{$p.'_budget'}  ?? 0;
    //                 $bd->{$p.'_reserve'} = $bd->{$p.'_reserve'} ?? 0;
    //                 $bd->{$p.'_used'}    = $bd->{$p.'_used'}    ?? 0;
    //             }
    //         }

    //         $bd->{$periodCol} = (float)($bd->{$periodCol} ?? 0) + (float)$amount;
    //         $bd->updated_by = $username;
    //         $bd->save();
    //     }
    // }

    // Williem 251113 Reserve Budget
    private function reserveBudget(array $details, string $cpnyId, TrCS $cs, string $username): void {
        $csDate   = Carbon::parse($cs->csdate ?? now());
        $yearStr  = $csDate->format('Y'); // perpost YYYY
        $periodCol = 'period' . $csDate->format('m') . '_reserve';

        $perpostMonth = (int)$csDate->format('m');

        $buckets = [];
        foreach ($details as $d) {
            $selectedTotal = 0.0;
            foreach (($d['vendor'] ?? []) as $v) { if (!empty($v['selected'])) { $selectedTotal = (float)($v['total'] ?? 0); break; } }
            if ($selectedTotal <= 0) continue;

            $key = json_encode([
                'perpost'           => $yearStr,
                'cpny_id'           => $d['budget_cpny_id'] ?? $cpnyId,
                'business_unit_id'  => $d['budget_business_unit_id'] ?? null,
                'department_fin_id' => $d['budget_department_fin_id'] ?? null,
                'account_id'        => $d['budget_account_id'] ?? null,
                'activity_id'       => $d['budget_activity_id'] ?? null,
                // tambahkan info deskripsi & jenis activity kalau mau ikut di history
                'activity_descr'    => $d['budget_activity_descr'] ?? null,
                'activity_type'     => $d['budget_activity_type'] ?? null,

            ]);
            $buckets[$key] = ($buckets[$key] ?? 0) + $selectedTotal;
        }

        foreach ($buckets as $keyJson => $amount) {
            $crit = json_decode($keyJson, true);

            // ==========================
            // 1. UPDATE / INSERT ms_budget
            // ==========================
            $bd = BudgetDetail::where([['perpost','=',$crit['perpost']],['cpny_id','=',$crit['cpny_id']]])
                ->when($crit['business_unit_id'],  fn($q,$v)=>$q->where('business_unit_id',$v))
                ->when($crit['department_fin_id'], fn($q,$v)=>$q->where('department_fin_id',$v))
                ->when($crit['account_id'],        fn($q,$v)=>$q->where('account_id',$v))
                ->when($crit['activity_id'],       fn($q,$v)=>$q->where('activity_id',$v))
                ->lockForUpdate()->first();

            if (!$bd) {
                $bd = new BudgetDetail();
                $bd->setConnection('pgsql');
                $bd->fill($crit);
                $bd->status = 'A';
                $bd->created_by = $username;
                for ($m=1;$m<=12;$m++){
                    $p = 'period'.str_pad($m,2,'0',STR_PAD_LEFT);
                    $bd->{$p.'_budget'}  = $bd->{$p.'_budget'}  ?? 0;
                    $bd->{$p.'_reserve'} = $bd->{$p.'_reserve'} ?? 0;
                    $bd->{$p.'_used'}    = $bd->{$p.'_used'}    ?? 0;
                }
            }

            $bd->{$periodCol} = (float)($bd->{$periodCol} ?? 0) + (float)$amount;
            $bd->updated_by = $username;
            $bd->save();

            // ==========================
            // 2. INSERT HISTORY ke tr_budget
            // ==========================
            $tr = new TrBudget();
            $tr->setConnection('pgsql');
            $tr->refnbr          = $cs->csid ?? null;          // sesuaikan field di TrCS
            $tr->prev_refnbr     = $cs->csid ?? null;    // kalau ada, kalau tidak ya null
            $tr->doctype         = 'CS';                        // jenis dokumen
            $tr->submitdate      = $csDate->toDateString();
            $tr->perpost_year    = (int)$crit['perpost'];
            $tr->perpost_month   = $perpostMonth;

            $tr->cpny_id         = $bd->cpny_id;
            $tr->business_unit_id  = $bd->business_unit_id;
            $tr->department_fin_id = $bd->department_fin_id;
            $tr->account_id        = $bd->account_id;
            $tr->activity_id       = $bd->activity_id;
            $tr->activity_descr    = $bd->activity_descr;
            $tr->activity_type     = $bd->activity_type;

            $tr->cpny_id         = $bd->cpny_id;
            $tr->business_unit_id  = $bd->business_unit_id;
            $tr->department_fin_id = $bd->department_fin_id;
            $tr->account_id      = $bd->account_id;
            $tr->activity_id     = $bd->activity_id;
            $tr->activity_descr  = $bd->activity_descr;
            $tr->activity_descr  = $bd->activity_type;
            $tr->budget_type     = 'RESERVE';                   // reserve / used
            $tr->trancation_activity     = 'CS Submit';
            $tr->budget_amount   = (float)$amount;
            $tr->status          = 'A';
            $tr->created_by      = $username;
            $tr->created_at      = now();
            $tr->save();
        }
    }




}
