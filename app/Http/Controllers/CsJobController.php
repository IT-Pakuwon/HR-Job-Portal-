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
use App\Models\TrCS;
use App\Models\TrCSdetail;
use App\Models\BudgetDetail;
use App\Models\vCsJobs;
use App\Models\vCsRevision;
use Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;


class CsJobController extends Controller
{

    public function CsJobs()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        // Kartu ringkasan
        $all  = vCsJobs::count();
        $sppb = vCsJobs::where('doc_type', 'SPPB')->count();
        $sppj = vCsJobs::where('doc_type', 'SPPJ')->count();
        $sppk = vCsJobs::where('doc_type', 'SPPK')->count();
        $sppt = vCsJobs::where('doc_type', 'SPPT')->count();

        return view('pages.canvass.csjobs', compact('all', 'sppb', 'sppj', 'sppk', 'sppt'));
    }

    private function buildJobsJson($base, Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $search = trim((string) $request->input('search.value', ''));
        $doc    = (string) $request->query('doc', '');

        $columns = [
            0 => 'assigndate',
            1 => 'doc_no',
            2 => 'doc_date',
            3 => 'cpny_id',
            4 => 'created_by',
            5 => 'assignpurchasing',
            6 => 'assignby',
            7 => 'department_id',
            8 => 'keperluan',
        ];
        $orderIdx = (int) $request->input('order.0.column', 2);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'doc_date';

        $base->with('creator:username,name');

        if ($doc !== '') {
            $base->where('doc_type', $doc);
        }

        $recordsTotal = (clone $base)->count();

        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('doc_no', 'ilike', "%{$search}%")
                ->orWhere('doc_type', 'ilike', "%{$search}%")
                ->orWhere('created_by', 'ilike', "%{$search}%")
                ->orWhere('assignpurchasing', 'ilike', "%{$search}%")
                ->orWhere('assignby', 'ilike', "%{$search}%")
                ->orWhere('keperluan', 'ilike', "%{$search}%")
                ->orWhereRaw("CAST(cpny_id AS TEXT) ILIKE ?", ["%{$search}%"])
                ->orWhereRaw("CAST(department_id AS TEXT) ILIKE ?", ["%{$search}%"])
                ->orWhereRaw("TO_CHAR(doc_date, 'YYYY-MM-DD HH24:MI:SS') ILIKE ?", ["%{$search}%"])
                ->orWhereRaw("TO_CHAR(assigndate, 'YYYY-MM-DD HH24:MI:SS') ILIKE ?", ["%{$search}%"]);
            });
        }

        $recordsFiltered = (clone $base)->count();

        $data = $base->select(
                    'doc_type',
                    'src_id',
                    'doc_no',
                    'doc_date',
                    'assigndate',
                    'assignby',
                    'assignpurchasing',
                    'cpny_id',
                    'created_by',
                    'department_id',
                    'keperluan',
                    'row_id'
                )
                ->orderBy($orderCol, $orderDir)
                ->orderBy('doc_no', 'desc')
                ->skip($start)
                ->take($length)
                ->get();

        // enrich
        $data->transform(function ($row) {
            $row->created_by_name = optional($row->creator)->name;
            // normalisasi optional field biar aman di front-end
            $row->assigndate        = $row->assigndate ?? null;
            $row->assignby          = $row->assignby ?? null;
            $row->assignpurchasing  = $row->assignpurchasing ?? null;
            $row->eid = Hashids::encode($row->src_id);
            return $row;
        });
        

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    /**
     * TAB 1: CS Jobs (punya saya) -> vCsJobs where assignpurchasing = user login
     */
    public function CsJobsMineJson(Request $request)
    {
        $username = Auth::user()->username ?? '';
        $base = vCsJobs::query()->where('assignpurchasing', $username);
        return $this->buildJobsJson($base, $request);
    }

    /**
     * TAB 2: All Jobs -> vCsJobs (tanpa filter assignee)
     */
    public function CsJobsAllJson(Request $request)
    {
        $base = vCsJobs::query();
        return $this->buildJobsJson($base, $request);
    }

    /**
     * TAB 3: My Revision -> vCsRevision
     */
    public function CsJobsRevisionJson(Request $request)
    {
        $base = vCsRevision::query();
        return $this->buildJobsJson($base, $request);
    }

    /**
     * TAB 4: SPPBJKT IN Progress -> vSppbjktOnProgress
     */
    public function SppbjktOnProgressJson(Request $request)
    {
        $base = vSppbjktOnProgress::query();
        return $this->buildJobsJson($base, $request);
    }

   
    private function baseQueryForTab(string $tab)
    {
        switch ($tab) {
            case 'mine':
                $username = Auth::user()->username ?? '';
                return vCsJobs::query()->where('assignpurchasing', $username);
            case 'all':
                return vCsJobs::query();
            case 'revision':
                return vCsRevision::query();
            case 'sppbjkt':
                return vSppbjktOnProgress::query();
            default:
                return vCsJobs::query();
        }
    }

    /**
     * GET /csjobs/counts?tab=mine|all|revision|sppbjkt
     * Kembalikan angka All/SPPB/SPPJ/SPPK/SPPT untuk tab aktif
     */
    public function CsJobsCounts(Request $request)
    {
        $tab  = $request->query('tab', 'mine');
        $base = $this->baseQueryForTab($tab);

        // total semua dokumen
        $all = (clone $base)->count();

        // pecah per doc_type
        $perType = (clone $base)
            ->select('doc_type', DB::raw('count(*) as cnt'))
            ->groupBy('doc_type')
            ->pluck('cnt', 'doc_type'); // ['SPPB'=>x, 'SPPJ'=>y, ...]

        return response()->json([
            'all'  => $all,
            'sppb' => (int)($perType['SPPB'] ?? 0),
            'sppj' => (int)($perType['SPPJ'] ?? 0),
            'sppk' => (int)($perType['SPPK'] ?? 0),
            'sppt' => (int)($perType['SPPT'] ?? 0),
        ]);
    }

   

    public function CsJobsEntryJson(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $search = trim((string) $request->input('search.value', ''));

        $username = Auth::user()->username ?? '';

        // hanya draft (H) dan milik user login
        $base = TrCS::query()
            ->where('status', 'H')
            ->where('created_by', $username);

        // kolom yang diizinkan untuk ordering
        $columns = [
            0 => 'csid',
            1 => 'csdate',
            2 => 'cpny_id',
            3 => 'department_id',
            4 => 'user_peminta',
            5 => 'csnote',
        ];
        $orderIdx = (int) $request->input('order.0.column', 1);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderCol = $columns[$orderIdx] ?? 'csdate';

        $recordsTotal = (clone $base)->count();

        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('csid', 'ilike', "%{$search}%")
                ->orWhere('cpny_id', 'ilike', "%{$search}%")
                ->orWhere('department_id', 'ilike', "%{$search}%")
                ->orWhere('user_peminta', 'ilike', "%{$search}%")
                ->orWhere('csnote', 'ilike', "%{$search}%")
                ->orWhereRaw("TO_CHAR(csdate, 'YYYY-MM-DD HH24:MI:SS') ILIKE ?", ["%{$search}%"]);
            });
        }

        $recordsFiltered = (clone $base)->count();

        $data = $base->select('id','csid','csdate','cpny_id','department_id','user_peminta','csnote','created_by','status')
            ->orderBy($orderCol, $orderDir)
            ->orderBy('csid', 'desc')
            ->skip($start)
            ->take($length)
            ->get();

        $data->transform(function($r){
            $r->eid = Hashids::encode($r->id);
            unset($r->id); // opsional sembunyikan id asli
            return $r;
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
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
            case 'SPPB': $header = TrSPPB::with(['creator','purchaser'])->where('sppbid',$docno)->first(); break;
            case 'SPPJ': $header = TrSPPJ::with(['creator','purchaser'])->where('sppjid',$docno)->first(); break;
            case 'SPPK': $header = TrSPPK::with(['creator','purchaser'])->where('sppkid',$docno)->first(); break;
            case 'SPPT': $header = TrSPPT::with(['creator','purchaser'])->where('spptid',$docno)->first(); break;
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

        $attachment = Attachment::where('docid', $cs->sppbjktid)->where('status','A')->orderBy('created_at')->get();
        $attachmentCS = Attachment::where('docid', $cs->csid)->where('status','A')->orderBy('created_at')->get();

        $eid = Hashids::encode($cs->id);

        return view('pages.canvass.editcs', [
            'eid'        => $eid,
            'doc'        => $doc,
            'src_id'     => $header->id,
            'docno'      => $docno,
            'header'     => $header ?? $cs,
            'items'      => $items,
            'attachment' => $attachment,
            'attachmentCS' => $attachmentCS,
            'cs'         => $cs,
            // payload untuk preload JS
            'vendorsUsed'         => $vendorsUsed,
            'detailVendorMatrix'  => $detailVendorMatrix,
        ]);
    }

    public function updateCS(Request $request, $csid)
    {      
        // dd($request->all());
        // ===== Validasi dasar payload (mirip saveCS) =====
        $request->validate([
            'doc'             => 'required|string',     // SPPB|SPPJ|SPPK|SPPT
            // 'src_id'          => 'required|string',     // id sumber doc
            'sppbjktid'       => 'nullable|string',
            'cpny_id'         => 'required|string',
            'department_id'   => 'required|string',
            'bqid'            => 'nullable|string',
            'user_peminta'    => 'nullable|string',
            'csnote'          => 'nullable|string',
            'assigndate'      => 'nullable|string',
            'vendors'         => 'required|string', // JSON
            'details'         => 'required|string', // JSON
        ]);

        // ===== Decode payload JSON =====
        $vendors = json_decode($request->input('vendors', '[]'), true) ?: [];
        $details = json_decode($request->input('details', '[]'), true) ?: [];

        // ===== Context user & waktu (dipakai untuk attachments dan audit) =====
        $user     = $request->user();
        $username = $user->username ?? 'system';

        $dt        = Carbon::now();
        $year      = $dt->year;
        $month     = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        $datestamp = $dt->toDateTimeString();

        // ===== Helper yang sama seperti saveCS =====
        $round2 = fn($n) => round((float)$n, 2);
        $safeSet = function ($model, string $table, string $column, $value) {
            if (Schema::connection('pgsql')->hasColumn($table, $column)) {
                $model->{$column} = $value;
            }
        };

        // ===== Ambil sumber header+detail untuk melengkapi field seperti di saveCS =====
        $doc    = strtoupper($request->input('doc'));
        $srcId  = $request->input('src_id');

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

        // Index detail sumber untuk pencocokan (persis seperti saveCS)
        $srcIndex = [];
        foreach ($srcDetails as $sd) {
            $key = strtoupper(trim(($sd->inventoryid ?? ''))) . '|' .
                strtoupper(trim(($sd->uom ?? ''))) . '|' .
                strtoupper(trim(($sd->inventory_descr ?? '')));
            $srcIndex[$key] = $sd;
        }

        DB::connection('pgsql')->beginTransaction();
        try {
            // ===== 1) Lock header TrCS =====
            /** @var TrCS $cs */
            $cs = TrCS::on('pgsql')->lockForUpdate()->where('csid', $csid)->firstOrFail();
            $csTable = $cs->getTable();

            // ===== 2) Update header (mirror dari saveCS) =====
            $cpnyId      = $request->input('cpny_id');
            $deptId      = $request->input('department_id');
            $bqid        = $request->input('bqid');
            $userPeminta = $request->input('user_peminta');
            $csnote      = $request->input('csnote');
            $assigndate  = $request->input('assigndate');
            $sppbjktid   = $request->input('sppbjktid');

            $cs->sppbjktid     = $sppbjktid;
            $cs->cpny_id       = $cpnyId;
            $cs->bqid          = $bqid ?: ($srcHeader->bqid ?? $cs->bqid);
            $cs->department_id = $deptId ?: ($srcHeader->department_id ?? $cs->department_id);
            $cs->user_peminta  = $userPeminta ?: (optional($srcHeader->creator)->name ?? $cs->user_peminta);
            $cs->csnote        = $csnote ?: null;
            $cs->assigndate    = $assigndate ?: null;

            // Lengkapi dari header sumber kalau kolom ada (sama seperti saveCS)
            $safeSet($cs, $csTable, 'budget_perpost', $srcHeader->budget_perpost ?? null);
            $safeSet($cs, $csTable, 'woid',           $srcHeader->woid           ?? null);
            $safeSet($cs, $csTable, 'spbid',          $srcHeader->spbid          ?? null);

            // Map maksimal 6 vendor seperti saveCS
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

            // status & audit
            $cs->status = $cs->status ?? 'H';
            if (Schema::connection('pgsql')->hasColumn($csTable, 'updated_by')) {
                $cs->updated_by = $username;
            }
            $cs->save();

            // ===== 3) Replace detail ala saveCS (1 baris detail berisi hingga 6 vendor kolom) =====
            TrCSdetail::on('pgsql')->where('csid', $csid)->delete();

            $lineNo = 0;
            foreach ($details as $d) {
                $lineNo++;

                // Cari pasangan di detail sumber
                $matchKey = strtoupper(trim(($d['inventoryid'] ?? ''))) . '|' .
                            strtoupper(trim(($d['uom'] ?? ''))) . '|' .
                            strtoupper(trim(($d['inventory_descr'] ?? '')));
                $src = $srcIndex[$matchKey] ?? null;

                if (!$src && isset($srcDetails[$lineNo - 1])) {
                    $src = $srcDetails[$lineNo - 1];
                }

                $srcRefNo = $src ? ($src->{$srcLineKey} ?? null) : null;

                $det = new TrCSdetail();
                $det->setConnection('pgsql');

                $det->csid          = $csid;
                $det->sppbjktid     = $sppbjktid;
                $det->cs_no         = $lineNo;
                $det->sppbjkt_no    = $srcRefNo;

                $det->inventoryid       = $d['inventoryid']        ?? ($src->inventoryid ?? null);
                $det->inventory_descr   = $d['inventory_descr']    ?? ($src->inventory_descr ?? null);
                $det->qty               = $round2($d['qty']        ?? ($src->qty ?? 0));
                $det->uom               = $d['uom']                ?? ($src->uom ?? null);

                // konversi dari sumber (jika ada)
                $det->type_multiplier   = $src->type_multiplier     ?? null;
                $det->base_multiplier   = isset($src->base_multiplier) ? $round2($src->base_multiplier) : null;
                $det->base_qty          = isset($src->base_qty)        ? $round2($src->base_qty)        : null;
                $det->base_uom          = $src->base_uom ?? null;

                // harga terakhir & note detail
                $det->inventory_last_price = isset($d['inventory_last_price']) ? $round2($d['inventory_last_price'])
                                            : (isset($src->inventory_last_price) ? $round2($src->inventory_last_price) : 0);
                $det->csnote_detail        = $d['csnote_detail']      ?? ($src->note ?? null);

                // lokasi & budgeting (ambil dari sumber bila ada)
                $det->location_id               = $src->location_id               ?? null;
                $det->sub_location_id           = $src->sub_location_id           ?? null;
                $det->budget_perpost            = $src->budget_perpost            ?? null;
                $det->budget_cpny_id            = $cpnyId;
                $det->budget_business_unit_id   = $src->budget_business_unit_id   ?? null;
                $det->budget_department_fin_id  = $src->budget_department_fin_id  ?? null;
                $det->budget_account_id         = $src->budget_account_id         ?? null;
                $det->budget_activity_id        = $src->budget_activity_id        ?? null;

                // Map harga per vendor (maks 6) — mengikuti struktur saveCS
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

                $det->status     = 'H';
                $det->created_by = $username; // atau updated_by jika ada
                $det->save();
            }

            // ===== 4) Attachments BARU (opsional) =====
            if ($request->hasfile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $randomNumber = random_int(10000000, 99999999);
                    $filename     = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $originalName = str_replace('%', '', $file->getClientOriginalName());
                    $attachfile   = md5($randomNumber) . '-' . $originalName;

                    $folder_attach = public_path() . '/attachments/' . $year;
                    if (!is_dir($folder_attach)) {
                        @mkdir($folder_attach, 0777, true);
                    }

                    $file->move($folder_attach, $attachfile);

                    $attach = new Attachment();
                    $attach->docid        = $csid;
                    $attach->name         = $filename;
                    $attach->attachfile   = $attachfile;
                    $attach->status       = 'A';
                    $attach->extention    = $file->getClientOriginalExtension();
                    $attach->created_user = $username;
                    $attach->save();
                }
            }

            $action = strtolower($request->input('action', 'save'));

            if (!in_array($action, ['save', 'submit'], true)) {
                $action = 'save';
            }

           // setelah simpan header & detail:
            if ($action === 'submit') {
                // 0) Validasi submit server-side (baris berharga > 0 harus punya vendor selected, dst.)
                $this->validateSubmitServerSide($details);

                // 1) Pastikan approval line ada (doctype = 'CS')
                $this->ensureApprovalLineExists($cs);

                // 2) Set status header CS jadi Pending + cap submitted
                $now = Carbon::now();
                $cs->status = 'P';
                if (Schema::connection('pgsql')->hasColumn($cs->getTable(), 'updated_by')) {
                    $cs->updated_by = $username;
                }
                if (Schema::connection('pgsql')->hasColumn($cs->getTable(), 'submitdate')) {                   
                    $cs->submitdate = $now;
                }
                $cs->save();

                // 3) Ambil sumber (header+detail) persis seperti storeCS
                [$srcHeader, $srcDetails, $srcLineKey, $srcIndex] = $this->buildSourceForDoc($doc, $srcId);

                // 4) Update ordered/openordered di sumber (blok 5b storeCS)
                $this->updateOrderedOnSource($details, $srcHeader, $srcDetails, $srcIndex, $cpnyId);

                // 5) Reserve budget (blok 5c storeCS)
                $this->reserveBudget($details, $cpnyId, $cs, $username);

                // 6) Generate/picu T_approval + email approver pertama (blok 6 & 8 storeCS)
                $this->createApprovalLinesAndNotify(
                    $cs->csid,
                    $cpnyId,
                    $deptId,
                    'CS',
                    $username,
                    $now, // untuk aprvdatebefore level-1
                    [
                        'docname'  => 'CS',
                        'status'   => 'P',
                        'info'     => $srcHeader->keperluan ?? $cs->csnote,
                        // HATI-HATI: jangan set / update kolom "eid" di table karena kolom itu tak ada.
                        // Untuk link, bikin on-the-fly: Hashids::encode($cs->id) langsung di variabel, JANGAN di-assign ke model.
                        'url'      => url('/showcs/' . Hashids::encode($cs->id)),
                        'createdby'=> $cs->created_by,
                    ]
                );
            }



            DB::connection('pgsql')->commit();

            return response()->json([
                'ok'       => true,
                'message'  => 'CS berhasil diupdate',
                'csid'     => $csid,
            ]);
        } catch (\Throwable $e) {
            \DB::connection('pgsql')->rollBack();
            report($e);

            return response()->json([
                'ok'      => false,
                'message' => 'Gagal update CS: ' . ($e->getMessage()),
            ], 422);
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

    private function ensureApprovalLineExists(TrCS $cs): void
    {
        $cnt = M_approval::where([
            ['status','=','A'],
            ['aprvcpnyid','=',$cs->cpny_id],
            ['aprvdeptid','=',$cs->department_id],
            ['aprvdoctype','=','CS'],
        ])->count();

        if ($cnt === 0) {
            abort(422, 'Approval line CS belum di-setup, please contact IT!');
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

    private function reserveBudget(array $details, string $cpnyId, TrCS $cs, string $username): void {
        $csDate   = Carbon::parse($cs->csdate ?? now());
        $yearStr  = $csDate->format('Y'); // perpost YYYY
        $periodCol = 'period' . $csDate->format('m') . '_reserve';

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
            ]);
            $buckets[$key] = ($buckets[$key] ?? 0) + $selectedTotal;
        }

        foreach ($buckets as $keyJson => $amount) {
            $crit = json_decode($keyJson, true);
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
        }
    }


    private function createApprovalLinesAndNotify(
            string $docid,
            string $cpnyId,
            string $deptId,
            string $doctype,
            string $username,
            Carbon $firstDate,
            array $mail // ['docname','status','info','url','createdby']
        ): void {
            // Copy lines
            $masters = M_approval::where([
                ['status','=','A'],
                ['aprvcpnyid','=',$cpnyId],
                ['aprvdeptid','=',$deptId],
                ['aprvdoctype','=',$doctype],
            ])->orderBy('aprvid')->get();

            foreach ($masters as $m) {
                T_approval::updateOrCreate(
                    ['docid'=>$docid,'aprvid'=>$m->aprvid,'aprvdoctype'=>$doctype],
                    [
                        'aprvcpnyid'     => $m->aprvcpnyid,
                        'aprvdeptid'     => $m->aprvdeptid,
                        'aprvusername'   => $m->aprvusername,
                        'name'           => $m->name,
                        'aprvdatebefore' => $m->aprvid==1 ? $firstDate : null,
                        'aprvtotalday'   => 1,
                        'status'         => 'P',
                        'created_by'     => $username,
                    ]
                );
            }

            // Notifikasi approver pertama
            $first = T_approval::where('docid',$docid)->where('aprvdoctype',$doctype)->where('status','P')->orderBy('aprvid')->first();
            if ($first) {
                $subjectMap = ['P'=>'Waiting Approval','R'=>'Rejected Approval','D'=>'Revise Approval','A'=>'Approved','C'=>'Completed'];
                $subjectSuffix = $subjectMap[$mail['status'] ?? 'P'] ?? 'Notification';

                $approvers = array_filter(array_map('trim', explode(',', (string)$first->aprvusername)));
                $users = User::whereIn('username', $approvers)->where('status','A')->get();

                $data = [
                    'docid'    => $docid,
                    'cpnyid'   => $cpnyId,
                    'deptname' => $deptId,
                    'date'     => $first->aprvdatebefore,
                    'name'     => $first->name,
                    'createdby'=> $mail['createdby'] ?? $username,
                    'info'     => $mail['info'] ?? '',
                    'status'   => $mail['status'] ?? 'P',
                    'docname'  => $mail['docname'] ?? $doctype,
                    'url'      => $mail['url'] ?? url('/'),
                ];

                foreach ($users as $u) {
                    try {
                        $to = $u->test_email ?? $u->email;
                        Mail::send('emails.mailapprovenew', $data, function ($message) use ($to, $data, $subjectSuffix) {
                            $message->to($to)
                                ->subject($data['docid'].' - '.$subjectSuffix.' '.$data['docname'])
                                ->from('digitalserver@pakuwon.com', 'Pakuwon System');
                        });
                    } catch (\Throwable $e) {
                        Log::error('Failed sending CS waiting-approval email', ['error'=>$e->getMessage()]);
                    }
                }
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




}
