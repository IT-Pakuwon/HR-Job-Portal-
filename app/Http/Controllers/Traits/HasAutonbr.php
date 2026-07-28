<?php

namespace App\Http\Controllers\Traits;

use App\Models\Autonbr;
use Illuminate\Support\Facades\DB;

trait HasAutonbr
{
    /**
     * Generate next autonumber (safe for concurrency).
     * Auto-create row if not exists (including doctype_descr).
     *
     * @param string $doctype
     * @param int    $year
     * @param string $month  // "01".."12"
     * @param string $username
     * @param string|null $doctypeDescr
     *
     * @return array{doctype:string,year:int,month:string,next:int}
     */
    protected function nextAutonbr(
        string $doctype,
        int $year,
        string $month,
        string $username = 'system',
        ?string $doctypeDescr = null
    ): array {
        return DB::connection('pgsql')->transaction(function () use (
            $doctype,
            $year,
            $month,
            $username,
            $doctypeDescr
        ) {

            $row = Autonbr::query()
                ->lockForUpdate()
                ->where('doctype', $doctype)
                ->where('year', $year)
                ->where('month', $month)
                ->first();

            if (!$row) {
                // 🔹 auto-create jika belum ada
                $row = Autonbr::create([
                    'doctype'        => $doctype,
                    'doctype_descr'  => $doctypeDescr, // ✅ SIMPAN DESKRIPSI
                    'year'           => $year,
                    'month'          => $month,
                    'status'         => 'A',
                    'number'         => 0,
                    'created_by'     => $username,
                    'updated_by'     => $username,
                ]);
            } else {
                // 🔹 optional: isi descr kalau sebelumnya kosong
                if ($doctypeDescr && empty($row->doctype_descr)) {
                    $row->doctype_descr = $doctypeDescr;
                }
            }

            $next = ((int) ($row->number ?? 0)) + 1;

            $row->number     = $next;
            $row->updated_by = $username;
            $row->save();

            return [
                'doctype' => $doctype,
                'year'    => $year,
                'month'   => $month,
                'next'    => $next,
            ];
        });
    }

    /**
     * Generate the next autonumber scoped by company without changing the
     * behavior of the existing nextAutonbr() method.
     *
     * @return array{doctype:string,year:int,month:string,cpny_id:string,next:int}
     */
    protected function nextAutonbrByCpnyid(
        string $doctype,
        int $year,
        string $month,
        string $cpnyId,
        string $username = 'system',
        ?string $doctypeDescr = null
    ): array {
        return DB::connection('pgsql2')->transaction(function () use (
            $doctype,
            $year,
            $month,
            $cpnyId,
            $username,
            $doctypeDescr
        ) {
            $row = Autonbr::query()
                ->lockForUpdate()
                ->where('doctype', $doctype)
                ->where('year', $year)
                ->where('month', $month)
                ->where('cpny_id', $cpnyId)
                ->first();

            if (!$row) {
                $row = Autonbr::create([
                    'doctype' => $doctype,
                    'doctype_descr' => $doctypeDescr,
                    'year' => $year,
                    'month' => $month,
                    'cpny_id' => $cpnyId,
                    'status' => 'A',
                    'number' => 0,
                    'created_by' => $username,
                    'updated_by' => $username,
                ]);
            } elseif ($doctypeDescr && empty($row->doctype_descr)) {
                $row->doctype_descr = $doctypeDescr;
            }

            $next = ((int) ($row->number ?? 0)) + 1;
            $row->number = $next;
            $row->updated_by = $username;
            $row->save();

            return [
                'doctype' => $doctype,
                'year' => $year,
                'month' => $month,
                'cpny_id' => $cpnyId,
                'next' => $next,
            ];
        });
    }
}
