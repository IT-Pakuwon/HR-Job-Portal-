<?php

namespace App\Http\Controllers;

use App\Models\TrCS;
use App\Models\BqDetail;
use App\Models\TrBQCS;
use App\Models\TrBQCSDetail;
use App\Models\Autonbr;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Vinkla\Hashids\Facades\Hashids;
use App\Models\MsCompany;

class BQCSController extends Controller
{
    /** Tampilkan form Create BQ, sumbernya dari CS yang sudah ada */
    public function createFromCS($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);


        $cs = TrCS::on('pgsql')->where('id', $id)->firstOrFail();

        // ambil vendor dari header CS (vendor1..6)
        $vendors = [];
        for ($i=1; $i<=6; $i++) {
            $vidCol = "vendorid{$i}";
            $vnmCol = "vendorname{$i}";
            if (!filled($cs->{$vidCol}) && !filled($cs->{$vnmCol})) continue;

            $vendors[] = [
                'idx'   => $i,
                'id'    => $cs->{$vidCol},
                'name'  => $cs->{$vnmCol},
                'addr'  => $cs->{"vendoralamat{$i}"} ?? null,
                'cp'    => $cs->{"vendorcp{$i}"} ?? null,
                'telp'  => $cs->{"vendortelp{$i}"} ?? null,
                'top'   => $cs->{"vendortop{$i}"} ?? null,
            ];
        }

        // detail sumber BQ: dari tr_bq_detail sesuai bqid di CS (khusus SPPJ/SPPT)
        $bqid = $cs->bqid;
        $bqDetails = BqDetail::on('pgsql')
                        ->where('bqid', $bqid)
                        ->orderBy('bq_no')->orderBy('bq_line_no')
                        ->get();

        return view('pages.canvass.createbqcs', [
            'cs'        => $cs,
            'vendors'   => $vendors,     // akan jadi kolom-kolom di atas
            'bqDetails' => $bqDetails,   // baris-baris detail (qty, uom, descr)
        ]);
    }

    /** Simpan BQCS (header + detail) */
    public function storeBQCS(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'csid'   => 'required|string',
            'bqid'   => 'required|string',
            'cpny_id'=> 'required|string',
            'details'=> 'required|string',     // JSON: [{bq_no,bq_line_no,qty, uom, descr, vendor:[{idx, product_price, jasa_price}]}]
            'vendors'=> 'required|string',     // JSON ringkas vendor untuk header
        ]);

        $cs = TrCS::on('pgsql')->where('csid', $request->csid)->firstOrFail();

        $vendors  = json_decode($request->vendors, true) ?: [];
        $details  = json_decode($request->details, true) ?: [];
        $username = $request->user()->username ?? 'system';
        $now      = Carbon::now();

        DB::connection('pgsql')->beginTransaction();
        try {
            // HEADER: tr_bq_cs satu baris per dokumen BQ ini
            $hdr = new TrBQCS();
            $hdr->setConnection('pgsql');
            $hdr->bqid       = $request->bqid;
            $hdr->csid       = $cs->csid;
            $hdr->sppjtid    = $cs->sppbjktid;      // simpan referensi
            $hdr->cpny_id    = $request->cpny_id;
            $hdr->bq_type    = 'CS';                // bebas: penanda dibuat dari CS
            $hdr->status     = 'H';
            $hdr->created_by = $username;
            $hdr->created_at = $now;

            // mapping vendor ke kolom vendorid1..6 + grand total (material/jasa) dihitung dari detail
            $sumMaterial = array_fill(1, 6, 0.0);
            $sumJasa     = array_fill(1, 6, 0.0);

            // (hitung di loop detail; di sini isi vendoridX)
            for ($i=0; $i<min(count($vendors),6); $i++) {
                $idx = $i+1;
                $hdr->{"vendorid{$idx}"} = $vendors[$i]['id'] ?? null;
            }
            $hdr->save();

            // DETAIL: tr_bq_cs_detail
            $line = 0;
            foreach ($details as $row) {
                $line++;

                $bqNo   = $row['bq_no']       ?? null;
                $bqLn   = $row['bq_line_no']  ?? $line;
                $descr  = $row['bq_descr']    ?? '';
                $qty    = (float)($row['qty'] ?? 0);
                $uom    = $row['uom']         ?? null;

                $det = new TrBQCSDetail();
                $det->setConnection('pgsql');
                $det->bqid        = $request->bqid;
                $det->csid        = $cs->csid;
                $det->sppjtid     = $cs->sppbjktid;
                $det->bq_no       = $bqNo;
                $det->bq_line_no  = $bqLn;
                $det->bq_descr    = $descr;
                $det->qty         = round($qty,2);
                $det->uom         = $uom;
                $det->status      = 'H';
                $det->created_by  = $username;
                $det->created_at  = $now;

                // per vendor: isi price material & jasa + totalnya (qty * price)
                for ($i=0; $i<min(count($row['vendor'] ?? []),6); $i++) {
                    $vIdx      = $i+1;
                    $prodPrice = (float)($row['vendor'][$i]['product_price'] ?? 0);
                    $jasaPrice = (float)($row['vendor'][$i]['jasa_price']    ?? 0);

                    $det->{"vendorid{$vIdx}"}                 = $vendors[$i]['id'] ?? null;
                    $det->{"vendorproductprice{$vIdx}"}       = round($prodPrice, 2);
                    $det->{"vendortotalproductprice{$vIdx}"}  = round($qty * $prodPrice, 2);
                    $det->{"vendorjasaprice{$vIdx}"}          = round($jasaPrice, 2);
                    $det->{"vendortotaljasaprice{$vIdx}"}     = round($qty * $jasaPrice, 2);

                    $sumMaterial[$vIdx] += $qty * $prodPrice;
                    $sumJasa[$vIdx]     += $qty * $jasaPrice;
                }

                $det->save();
            }

            // setelah tahu total per vendor → update header grand total material/jasa
            for ($i=1; $i<=6; $i++) {
                if (!empty($hdr->{"vendorid{$i}"})) {
                    $hdr->{"grandtotalmaterialvendor{$i}"} = round($sumMaterial[$i],2);
                    $hdr->{"grandtotaljasavendor{$i}"}     = round($sumJasa[$i],2);
                }
            }
            $hdr->save();

            DB::connection('pgsql')->commit();

            return response()->json([
                'ok'   => true,
                'msg'  => 'BQ created from CS',
                'bqid' => $hdr->bqid,
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql')->rollBack();
            report($e);
            return response()->json(['ok'=>false,'msg'=>$e->getMessage()], 422);
        }
    }

    public function EditBQCS($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        // 1) Ambil header BQ
        $bq = TrBQCS::on('pgsql')->findOrFail($id);

        // 2) Ambil CS (vendor master untuk BQ ini)
        $cs = TrCS::on('pgsql')
            ->where('bqid', $bq->bqid)
            ->where('csid', $bq->csid)
            ->first();

        abort_if(!$cs, 404, 'CS untuk BQ ini tidak ditemukan (bqid & csid tidak cocok).');

        // --- siapkan array vendor dari CS (1..6) ---
        $csVendors = [];
        for ($i = 1; $i <= 6; $i++) {
            $csVendors[$i] = [
                'id'   => $cs->{"vendorid{$i}"} ?? null,
                'name' => $cs->{"vendorname{$i}"} ?? null,
                'addr' => $cs->{"vendoralamat{$i}"} ?? null,
                'cp'   => $cs->{"vendorcp{$i}"} ?? null,
                'telp' => $cs->{"vendortelp{$i}"} ?? null,
                'top'  => $cs->{"vendortop{$i}"} ?? null,
            ];
        }

        // 3) SINKRONISASI vendor dari CS → BQ header & detail (transaksi)
        DB::connection('pgsql')->transaction(function () use ($bq, $csVendors) {
            $now  = Carbon::now();
            $user = Auth::user();
            $u    = isset($user) && isset($user->username) ? $user->username : 'system';

            // 3a) Update header BQ: vendorid1..6 mengikuti CS (nama/alamat/cp/telp/top memang tidak ada di tabel BQ header)
            for ($i = 1; $i <= 6; $i++) {
                $bq->{"vendorid{$i}"} = $csVendors[$i]['id']; // bisa null kalau tak diisi
            }
            $bq->updated_by = $u;
            $bq->updated_at = $now;
            $bq->save();

            // 3b) Update semua detail BQ: vendorid1..6 mengikuti CS
            TrBQCSDetail::on('pgsql')
                ->where('bqid', $bq->bqid)
                ->update([
                    'vendorid1' => $csVendors[1]['id'],
                    'vendorid2' => $csVendors[2]['id'],
                    'vendorid3' => $csVendors[3]['id'],
                    'vendorid4' => $csVendors[4]['id'],
                    'vendorid5' => $csVendors[5]['id'],
                    'vendorid6' => $csVendors[6]['id'],
                    // kolom harga/totals tidak diubah
                ]);
        });

        // 4) Ambil ulang detail setelah sinkron (kalau perlu)
        $details = TrBQCSDetail::on('pgsql')
            ->where('bqid', $bq->bqid)
            ->orderBy('bq_no')
            ->orderBy('bq_line_no')
            ->get();

        // 5) Vendor untuk tampilan: SELALU dari CS supaya name/addr/cp/telp/top tampil
        $vendors = [];
        for ($i = 1; $i <= 6; $i++) {
            $vid = $csVendors[$i]['id'];
            $vnm = $csVendors[$i]['name'];
            if (!filled($vid) && !filled($vnm)) continue;

            $vendors[] = [
                'idx'  => $i,
                'id'   => $vid,
                'name' => $vnm,
                'addr' => $csVendors[$i]['addr'],
                'cp'   => $csVendors[$i]['cp'],
                'telp' => $csVendors[$i]['telp'],
                'top'  => $csVendors[$i]['top'],
            ];
        }

        $hash_id = $hash;
        $cs_eid = Hashids::encode($cs->id);

        return view('pages.canvass.editbqcs', compact('bq', 'details', 'vendors', 'hash_id','cs','cs_eid'));
    }


    public function updateBQCS(Request $request, $hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);
       
        $hdr = TrBQCS::on('pgsql')->lockForUpdate()->findOrFail($id);

        // === Validasi (samakan dengan store) ===
        $request->validate([
            'vendors' => 'required|string', // JSON ringkas vendor header: [{id,name}, ...] max 6
            'details' => 'required|string', // JSON baris: [{bq_no,bq_line_no,bq_descr,qty,uom,vendor:[{idx,product_price,jasa_price}]}, ...]
        ]);

        $vendors  = json_decode($request->input('vendors'), true) ?: [];
        $details  = json_decode($request->input('details'), true) ?: [];
        $username = $request->user()->username ?? 'system';
        $now      = \Carbon\Carbon::now();

        // siapkan penjumlahan ulang (akan diisi dari detail)
        $sumMaterial = array_fill(1, 6, 0.0);
        $sumJasa     = array_fill(1, 6, 0.0);

        return DB::connection('pgsql')->transaction(function () use ($hdr, $vendors, $details, $username, $now, &$sumMaterial, &$sumJasa) {

            // === 1) Update header: vendorid1..6 sesuai urutan vendors yang dikirim ===
            for ($i = 1; $i <= 6; $i++) {
                $vid = $vendors[$i-1]['id'] ?? null;
                $hdr->{"vendorid{$i}"} = $vid ?: null;

                // reset grand total agar aman dihitung ulang
                $hdr->{"grandtotalmaterialvendor{$i}"} = null;
                $hdr->{"grandtotaljasavendor{$i}"}     = null;
            }

            // === 2) Loop detail: update/insert baris sesuai (bqid + bq_no + bq_line_no) ===
            foreach ($details as $row) {
                $bqNo   = $row['bq_no']      ?? null;
                $bqLn   = $row['bq_line_no'] ?? null;
                
                $det = TrBQCSDetail::on('pgsql')
                    ->where('bqid', $hdr->bqid)
                    ->where('bq_no', $bqNo)
                    ->where('bq_line_no', $bqLn)
                    ->first();

                $isNew = false;
                if (!$det) {
                    // jika belum ada → buat baru (upsert sederhana)
                    $det = new TrBQCSDetail();
                    $det->setConnection('pgsql');
                    $det->bqid       = $hdr->bqid;
                    $det->csid       = $hdr->csid;      // referensi tetap dari header
                    $det->sppjtid    = $hdr->sppjtid;   // referensi tetap dari header
                    $det->bq_no      = $bqNo;
                    $det->bq_line_no = $bqLn;
                    $det->status     = $det->status ?: 'H';
                    $det->created_by = $username;
                    $det->created_at = $now;
                    $isNew = true;
                }

                // qty/uom/descr
                $qty           = (float)($row['qty'] ?? $det->qty ?? 0);
                $det->qty      = round($qty, 2);
                $det->uom      = $row['uom']      ?? $det->uom;
                $det->bq_descr = $row['bq_descr'] ?? $det->bq_descr;

                // set ulang kolom vendor 1..6 sesuai urutan vendors header
                // (supaya vendoridX konsisten dengan header)
                for ($i = 1; $i <= 6; $i++) {
                    $det->{"vendorid{$i}"} = $vendors[$i-1]['id'] ?? null;

                    // default 0 jika tidak dikirim
                    $prodPrice = 0.0;
                    $jasaPrice = 0.0;

                    // cari dari payload detail->vendor berdasarkan index urutan (1..6)
                    if (!empty($row['vendor']) && is_array($row['vendor'])) {
                        foreach ($row['vendor'] as $vv) {
                            $idx = (int)($vv['idx'] ?? 0);
                            if ($idx === $i) {
                                $prodPrice = (float)($vv['product_price'] ?? 0);
                                $jasaPrice = (float)($vv['jasa_price']    ?? 0);
                                break;
                            }
                        }
                    }

                    // set harga & total
                    $det->{"vendorproductprice{$i}"}      = round($prodPrice, 2);
                    $det->{"vendortotalproductprice{$i}"} = round($qty * $prodPrice, 2);
                    $det->{"vendorjasaprice{$i}"}         = round($jasaPrice, 2);
                    $det->{"vendortotaljasaprice{$i}"}    = round($qty * $jasaPrice, 2);

                    // akumulasi grand total header
                    $sumMaterial[$i] += ($qty * $prodPrice);
                    $sumJasa[$i]     += ($qty * $jasaPrice);
                }

                $det->updated_by = $username;
                $det->updated_at = $now;
                $det->save();
            }

           
            // === 3) Update grand total material/jasa per vendor di header ===
            for ($i = 1; $i <= 6; $i++) {
                if (!empty($hdr->{"vendorid{$i}"})) {
                    $hdr->{"grandtotalmaterialvendor{$i}"} = round($sumMaterial[$i], 2);
                    $hdr->{"grandtotaljasavendor{$i}"}     = round($sumJasa[$i], 2);
                } else {
                    // jika vendor kosong, pastikan kolom grand total null
                    $hdr->{"grandtotalmaterialvendor{$i}"} = null;
                    $hdr->{"grandtotaljasavendor{$i}"}     = null;
                }
            }

            $hdr->updated_by = $username;
            $hdr->updated_at = $now;
            $hdr->save();

            return response()->json([
                'ok'   => true,
                'msg'  => 'BQ updated',
                'bqid' => $hdr->bqid,
            ]);
        });
    }

    public function showBQCS($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        // 1) Ambil header BQ
        $bq = TrBQCS::on('pgsql')->findOrFail($id);

        // 2) Ambil CS (vendor master untuk BQ ini)
        $cs = TrCS::on('pgsql')
            ->where('bqid', $bq->bqid)
            ->where('csid', $bq->csid)
            ->first();

        abort_if(!$cs, 404, 'CS untuk BQ ini tidak ditemukan (bqid & csid tidak cocok).');

        // --- siapkan array vendor dari CS (1..6) ---
        $csVendors = [];
        for ($i = 1; $i <= 6; $i++) {
            $csVendors[$i] = [
                'id'   => $cs->{"vendorid{$i}"} ?? null,
                'name' => $cs->{"vendorname{$i}"} ?? null,
                'addr' => $cs->{"vendoralamat{$i}"} ?? null,
                'cp'   => $cs->{"vendorcp{$i}"} ?? null,
                'telp' => $cs->{"vendortelp{$i}"} ?? null,
                'top'  => $cs->{"vendortop{$i}"} ?? null,
            ];
        }

        // 3) SINKRONISASI vendor dari CS → BQ header & detail (transaksi)
        DB::connection('pgsql')->transaction(function () use ($bq, $csVendors) {
            $now  = Carbon::now();
            $user = Auth::user();
            $u    = isset($user) && isset($user->username) ? $user->username : 'system';

            // 3a) Update header BQ: vendorid1..6 mengikuti CS (nama/alamat/cp/telp/top memang tidak ada di tabel BQ header)
            for ($i = 1; $i <= 6; $i++) {
                $bq->{"vendorid{$i}"} = $csVendors[$i]['id']; // bisa null kalau tak diisi
            }
            $bq->updated_by = $u;
            $bq->updated_at = $now;
            $bq->save();

            // 3b) Update semua detail BQ: vendorid1..6 mengikuti CS
            TrBQCSDetail::on('pgsql')
                ->where('bqid', $bq->bqid)
                ->update([
                    'vendorid1' => $csVendors[1]['id'],
                    'vendorid2' => $csVendors[2]['id'],
                    'vendorid3' => $csVendors[3]['id'],
                    'vendorid4' => $csVendors[4]['id'],
                    'vendorid5' => $csVendors[5]['id'],
                    'vendorid6' => $csVendors[6]['id'],
                    // kolom harga/totals tidak diubah
                ]);
        });

        // 4) Ambil ulang detail setelah sinkron (kalau perlu)
        $details = TrBQCSDetail::on('pgsql')
            ->where('bqid', $bq->bqid)
            ->orderBy('bq_no')
            ->orderBy('bq_line_no')
            ->get();

        // 5) Vendor untuk tampilan: SELALU dari CS supaya name/addr/cp/telp/top tampil
        $vendors = [];
        for ($i = 1; $i <= 6; $i++) {
            $vid = $csVendors[$i]['id'];
            $vnm = $csVendors[$i]['name'];
            if (!filled($vid) && !filled($vnm)) continue;

            $vendors[] = [
                'idx'  => $i,
                'id'   => $vid,
                'name' => $vnm,
                'addr' => $csVendors[$i]['addr'],
                'cp'   => $csVendors[$i]['cp'],
                'telp' => $csVendors[$i]['telp'],
                'top'  => $csVendors[$i]['top'],
            ];
        }

        $hash_id = $hash;
        $cs_eid = Hashids::encode($cs->id);

        return view('pages.canvass.showbqcs', compact('bq', 'details', 'vendors', 'hash_id','cs','cs_eid','hash'));
    }

    public function printBQCSVend($hash, $idx)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);

        $authUser = Auth::user();
        if (!$authUser) return redirect()->route('login');

        $idx = (int) $idx;
        abort_if($idx < 1 || $idx > 6, 404, 'Vendor index invalid');

        $bq = TrBQCS::on('pgsql')->findOrFail($id);

        // ambil CS untuk data vendor (nama/alamat/cp/telp/top)
        $cs = TrCS::on('pgsql')
            ->where('bqid', $bq->bqid)
            ->where('csid', $bq->csid)
            ->first();

        abort_if(!$cs, 404, 'CS untuk BQ ini tidak ditemukan.');

        $vendor = [
            'id'   => $cs->{"vendorid{$idx}"} ?? null,
            'name' => $cs->{"vendorname{$idx}"} ?? null,
            'addr' => $cs->{"vendoralamat{$idx}"} ?? null,
            'cp'   => $cs->{"vendorcp{$idx}"} ?? null,
            'telp' => $cs->{"vendortelp{$idx}"} ?? null,
            'top'  => $cs->{"vendortop{$idx}"} ?? null,
        ];
        abort_if(!filled($vendor['id']) && !filled($vendor['name']), 404, 'Vendor tidak ditemukan.');

        $company = MsCompany::where('cpny_id', $bq->cpny_id)->first();

        $bqdetail = TrBQCSDetail::on('pgsql')
            ->where('bqid', $bq->bqid)
            ->orderBy('bq_no')
            ->orderBy('bq_line_no')
            ->get();

        // hitung grand total berdasarkan vendor terpilih
        $grandTotalMaterial = 0;
        $grandTotalJasa     = 0;

        foreach ($bqdetail as $item) {
            $qty = (float) ($item->qty ?? 0);

            $mat = (float) ($item->{"vendorproductprice{$idx}"} ?? 0);
            $jasa = (float) ($item->{"vendorjasaprice{$idx}"} ?? 0);

            $grandTotalMaterial += $qty * $mat;
            $grandTotalJasa     += $qty * $jasa;
        }

        $data = [
            'title'     => 'CS Bills of Quantities (BQ)',
            'doc_type'  => 'BQ',
            'cpny_id'   => $company->cpny_id ?? $bq->cpny_id,
            'cpny_name' => $company->cpny_name ?? '',
            'vendor'    => $vendor,
            'idx'       => $idx,
            'grandTotalMaterial' => $grandTotalMaterial,
            'grandTotalJasa'     => $grandTotalJasa,
        ];

        $pdf = \PDF::loadView('pages.canvass.pdfbq_cs_vendor', array_merge($data, [
            'bq'       => $bq,
            'bqdetail' => $bqdetail,
        ]));

        $pdf->setPaper('A4');

        $safeVendorName = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string)($vendor['name'] ?? "vendor{$idx}"));
        return $pdf->stream("pdfbq_cs_{$bq->bqid}_{$safeVendorName}.pdf");
    }


    public function printBQCS_xxx($hash)
    {
        $id = Hashids::decode($hash)[0] ?? null;
        abort_if(!$id, 404);
        
        $authUser = Auth::user();
        if (!$authUser) {
            return redirect()->route('login');
        }

        // Ambil SPPJ + relasi yang dibutuhkan
        $bq = TrBQCS::findOrFail($id);

        // Detail baris SPPJ
        $bqdetail = TrBQCSDetail::where('bqid', $bq->bqid)
            ->get();
            
        // $sppt = TrSPPT::where('spptid', $bq->sppjtid)
        //     ->first();       
       
        $company = MsCompany::where('cpny_id', $bq->cpny_id)->first();
        
        $data = [
            'title'               => 'CS Bills of Quantities (BQ)',
            'doc_type'            => 'BQ',
            'cpny_id'             => $company->cpny_id,           
            'cpny_name'           => $company->cpny_name, 
            // 'keperluan'           => $sppt->keperluan,
        ];

        // Kirim ke view
        $pdf = \PDF::loadView(
            'pages.canvass.pdfbq_cs',
            array_merge($data, [
                'bq'             => $bq,
                'bqdetail'         => $bqdetail,               
            ])
        );

        // Portrait jika <= 5 approver, else landscape
        $pdf->setPaper('A4');

        return $pdf->stream("pdfbq_cs_{$bq->bqid}.pdf");
    }


}
