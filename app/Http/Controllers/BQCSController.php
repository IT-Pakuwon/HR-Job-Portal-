<?php

namespace App\Http\Controllers;

use App\Models\TrCS;
use App\Models\BqDetail;
use App\Models\TrBQCS;
use App\Models\TrBQCSDetail;
use App\Models\Autonbr;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class BQCSController extends Controller
{
    /** Tampilkan form Create BQ, sumbernya dari CS yang sudah ada */
    public function createFromCS(string $csid)
    {
        $cs = TrCS::on('pgsql')->where('csid', $csid)->firstOrFail();

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
    public function store(Request $request)
    {
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
}
