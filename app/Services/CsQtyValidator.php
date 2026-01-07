<?php

namespace App\Services;

use App\Models\TrSPPBdetail;
use App\Models\TrSPPJdetail;
use App\Models\TrSPPKdetail;
use App\Models\TrSPPTdetail;
use App\Models\TrPOReuse;

class CsQtyValidator
{
    /**
     * Validasi qty detail CS dibanding sisa (open) di dokumen sumber.
     *
     * @param  string $doc   SPPB | SPPJ | SPPK | SPPT | PO | SPK
     * @param  string $srcId id sumber:
     *                      - SPPB/J/K/T: id header (sppbid/sppjid/sppkid/spptid)
     *                      - PO/SPK: csid lama (prev_csid) untuk lookup ke tr_po_reuse
     * @param  array  $details array detail dari request (inventoryid, uom, qty)
     * @return array  ['ok' => bool, 'message' => string, 'errors' => array]
     */
    public static function validate(string $doc, string $srcId, array $details): array
    {
        $doc = strtoupper(trim($doc));

        // Map doc ke model detail + cara filter sumber
        $map = [
            'SPPB' => [
                'model'      => TrSPPBdetail::class,
                'filter_col' => 'sppbid',
            ],
            'SPPJ' => [
                'model'      => TrSPPJdetail::class,
                'filter_col' => 'sppjid',
            ],
            'SPPK' => [
                'model'      => TrSPPKdetail::class,
                'filter_col' => 'sppkid',
            ],
            'SPPT' => [
                'model'      => TrSPPTdetail::class,
                'filter_col' => 'spptid',
            ],

            // ✅ Revisi dari PO/SPK → pakai tr_po_reuse by csid (prev_csid)
            'PO' => [
                'model'      => TrPOReuse::class,
                'filter_col' => 'csid',
            ],
            'SPK' => [
                'model'      => TrPOReuse::class,
                'filter_col' => 'csid',
            ],
        ];

        if (!isset($map[$doc])) {
            return [
                'ok'      => false,
                'message' => "Doc type {$doc} tidak dikenali.",
                'errors'  => [],
            ];
        }

        if (!$srcId) {
            return [
                'ok'      => false,
                'message' => "ID dokumen sumber tidak valid.",
                'errors'  => [],
            ];
        }

        $detailModel = $map[$doc]['model'];
        $filterCol   = $map[$doc]['filter_col'];

        // Ambil semua detail sumber untuk dokumen ini
        $srcDetails = $detailModel::query()
            ->where($filterCol, $srcId)
            ->get();

        // Index-kan by key (inventoryid + uom)
        $indexed = $srcDetails->groupBy(function ($row) {
            return ($row->inventoryid ?? '') . '|' . ($row->uom ?? '');
        });

        $errors = [];

        foreach ($details as $idx => $detail) {
            $inv = $detail['inventoryid'] ?? null;
            $uom = $detail['uom'] ?? null;
            $qty = (float) ($detail['qty'] ?? 0);

            if (!$inv || !$uom) {
                continue;
            }

            $key = $inv . '|' . $uom;

            if (!isset($indexed[$key])) {
                // detail ini tidak ditemukan di sumber → optional, bisa diabaikan
                continue;
            }

            $src = $indexed[$key]->first();

            $srcQty      = (float) ($src->qty ?? 0);
            $ordered     = (float) ($src->ordered ?? 0);
            $reject      = (float) ($src->rejectordered ?? 0);
            $completed   = (float) ($src->completeordered ?? 0);

            // beberapa tabel punya openordered, beberapa mungkin tidak
            $openordered = (float) ($src->openordered ?? 0);

            // Sisa secara perhitungan
            $calculatedRemain = $srcQty - $ordered - $reject - $completed;

            // Pakai max antara openordered dan hasil hitung
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

        if (!empty($errors)) {
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
