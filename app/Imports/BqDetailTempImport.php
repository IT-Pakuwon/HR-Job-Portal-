<?php

namespace App\Imports;

use App\Models\BqDetailTemp;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BqDetailTempImport implements ToModel, WithHeadingRow
{
    protected string $temp_id;
    protected ?string $bqid;
    protected ?string $sppjtid;

    /**
     * @param string $temp_id   : penanda batch import (simpan juga di session biar bisa preview)
     * @param string|null $bqid : opsional, kalau belum ada boleh null
     * @param string|null $sppjtid : id SPPJ (bukan kode), sesuaikan field yang kamu pakai
     */
    public function __construct(string $temp_id, ?string $sppjtid = null, ?string $bqid = null)
    {
        $this->temp_id  = $temp_id;
        $this->sppjtid  = $sppjtid;
        $this->bqid     = $bqid;
        
    }

    public function model(array $row)
    {
        // Pastikan header di Excel sesuai kunci berikut (lihat catatan di bawah):
        // bq_line_no, bq_descr, qty, uom, est_material_price, total_est_material_price, est_jasa_price, total_est_jasa_price
        return new BqDetailTemp([
            'temp_id'                   => $this->temp_id,
            'sppjtid'                   => $this->sppjtid,
            'bqid'                      => $this->bqid,            
            // 'bq_no'                  => $row['bq_no'] ?? null, // aktifkan jika header bq_no tersedia
            'bq_line_no'                => $row['bq_line_no'] ?? null,
            'bq_descr'                  => $row['bq_descr'] ?? null,
            'qty'                       => isset($row['qty']) ? (float)$row['qty'] : null,
            'uom'                       => $row['uom'] ?? null,
            'est_material_price'        => isset($row['est_material_price']) ? (float)$row['est_material_price'] : null,
            'total_est_material_price'  => isset($row['total_est_material_price']) ? (float)$row['total_est_material_price'] : null,
            'est_jasa_price'            => isset($row['est_jasa_price']) ? (float)$row['est_jasa_price'] : null,
            'total_est_jasa_price'      => isset($row['total_est_jasa_price']) ? (float)$row['total_est_jasa_price'] : null,
            'status'                    => 'P',
            'created_by'                => Auth::user()->username ?? 'system',
            'updated_by'                => Auth::user()->username ?? 'system',
        ]);
    }
}
