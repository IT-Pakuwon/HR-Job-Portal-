<?php

namespace App\Services;

use App\Models\TrSPPBdetail;
use App\Models\TrSPPJdetail;
use App\Models\TrSPPKdetail;
use App\Models\TrSPPTdetail;
use Vinkla\Hashids\Facades\Hashids;

class CsQtyValidator
{
    /**
     * Validasi qty detail CS dibanding sisa (open) di dokumen sumber.
     *
     * @param  string $doc   SPPB | SPPJ | SPPK | SPPT
     * @param  string $srcId hashids dari header sumber (sppbid/sppjid/sppkid/spptid)
     * @param  array  $details array detail dari request (inventoryid, uom, qty)
     * @return array  ['ok' => bool, 'message' => string, 'errors' => array]
     */
    public static function validate(string $doc, string $srcId, array $details): array
    {
        // dd($doc.'-'.$srcId.'-'.$details);
        // Map doc ke model detail + FK header
        $map = [
            'SPPB' => [
                'model' => TrSPPBdetail::class,
                'fk'    => 'sppbid',
            ],
            'SPPJ' => [
                'model' => TrSPPJdetail::class,
                'fk'    => 'sppjid',
            ],
            'SPPK' => [
                'model' => TrSPPKdetail::class,
                'fk'    => 'sppkid',
            ],
            'SPPT' => [
                'model' => TrSPPTdetail::class,
                'fk'    => 'spptid',
            ],
        ];

        if (! isset($map[$doc])) {
            return [
                'ok'      => false,
                'message' => "Doc type {$doc} tidak dikenali.",
                'errors'  => [],
            ];
        }
       
        if (! $srcId) {
            return [
                'ok'      => false,
                'message' => "ID dokumen sumber tidak valid.",
                'errors'  => [],
            ];
        }

        $detailModel = $map[$doc]['model'];
        $fk          = $map[$doc]['fk'];

        // Ambil semua detail sumber untuk header ini
        /** @var \Illuminate\Support\Collection $srcDetails */
        $srcDetails = $detailModel::where($fk, $srcId)->get();
       
        // Index-kan by key (inventoryid + uom) supaya gampang dicocokkan
        $indexed = $srcDetails->groupBy(function ($row) {
            return ($row->inventoryid ?? '') . '|' . ($row->uom ?? '');
        });

        $errors = [];

        foreach ($details as $idx => $detail) {
            $inv = $detail['inventoryid'] ?? null;
            $uom = $detail['uom']         ?? null;
            $qty = (float) ($detail['qty'] ?? 0);

            if (! $inv || ! $uom) {
                // kalau tidak ada key, skip saja
                continue;
            }

            $key = $inv . '|' . $uom;

            if (! isset($indexed[$key])) {
                // detail ini tidak ditemukan di sumber → optional, bisa diabaikan
                continue;
            }

            /** @var \App\Models\Model $src */
            $src = $indexed[$key]->first();

            $srcQty      = (float) ($src->qty              ?? 0);
            $ordered     = (float) ($src->ordered          ?? 0);
            $reject      = (float) ($src->rejectordered    ?? 0);
            $completed   = (float) ($src->completeordered  ?? 0);
            $openordered = (float) ($src->openordered      ?? 0);

            // Sisa secara perhitungan
            $calculatedRemain = $srcQty - $ordered - $reject - $completed;

            // Pakai max antara kolom openordered dan hasil hitung
            $allow = max($openordered, $calculatedRemain, 0);

            if ($qty > $allow) {
                $errors[] = [
                    'row_index'     => $idx,
                    'inventoryid'   => $inv,
                    'uom'           => $uom,
                    'requested_qty' => $qty,
                    'allowed_qty'   => $allow,
                    'source_qty'    => $srcQty,
                    'ordered'       => $ordered,
                    'reject'        => $reject,
                    'completed'     => $completed,
                    'openordered'   => $openordered,
                    'message'       => "Qty {$qty} melebihi sisa ({$allow}) untuk {$inv} ({$uom}).",
                ];
            }
        }

        if (! empty($errors)) {
            return [
                'ok'      => false,
                'message' => 'Terdapat item dengan qty melebihi sisa open/order yang diizinkan.',
                'errors'  => $errors,
            ];
        }

        return [
            'ok'      => true,
            'message' => 'OK',
            'errors'  => [],
        ];
    }
}
