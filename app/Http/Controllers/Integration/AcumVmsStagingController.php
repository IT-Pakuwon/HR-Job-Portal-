<?php

namespace App\Http\Controllers\Integration;

use App\Http\Controllers\Controller;
use App\Models\SysStagingSetting;
use App\Models\TrPO;
use App\Models\TrPOdetail;
use App\Models\TrReceipt;
use App\Models\TrReceiptdetail;
use App\Models\TrKontrak;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AcumVmsStagingController extends Controller
{
    /**
     * Halaman setting + tombol execute.
     * GET /staging/acumvms?app=ACUMVMS
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $appId = (string)($request->query('app', 'ACUMVMS'));

        $setting = SysStagingSetting::query()
            ->where('id_application', $appId)
            ->first();

        // status lock (running?)
        $running = $this->isRunning();

        return view('pages.integration.acumvms', [
            'appId'   => $appId,
            'setting' => $setting,
            'running' => $running,
        ]);
    }

    /**
     * Save window last_update & next_update dari form.
     * POST /staging/acumvms/save
     */
    public function saveWindow(Request $request)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $appId = (string)($request->input('app', 'ACUMVMS'));

        $request->validate([
            'app'        => ['required', 'string', 'max:50'],
            'last_update'=> ['required', 'date'],
            'next_update'=> ['required', 'date', 'after_or_equal:last_update'],
            'interval'   => ['nullable', 'integer', 'min:1'], // minutes
            'status'     => ['nullable', 'in:A,I'],
        ]);

        $setting = SysStagingSetting::query()
            ->where('id_application', $appId)
            ->first();

        if (!$setting) {
            // kalau mau auto-create, bisa dibuat di sini; untuk sekarang return error
            return back()->with('error', "Setting sys_staging_setting untuk {$appId} tidak ditemukan.");
        }

        $setting->last_update = Carbon::parse($request->input('last_update'));
        $setting->next_update = Carbon::parse($request->input('next_update'));

        if ($request->filled('interval')) {
            $setting->interval = (int)$request->input('interval');
        }

        if ($request->filled('status')) {
            $setting->status = $request->input('status');
        }

        $setting->lastupdate_user = $user->username ?? 'SYSTEM';
        $setting->lastupdate_datetime = now();
        $setting->save();

        return back()->with('success', 'Window berhasil disimpan.');
    }

    /**
     * Tombol Run Now dari UI (manual).
     * POST /staging/acumvms/run
     */
    public function runNow(Request $request)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $appId = (string)($request->input('app', 'ACUMVMS'));

        $res = $this->run($appId);

        // UI biasa enaknya balik JSON (kalau kamu pakai ajax) ATAU redirect with flash
        if ($request->expectsJson()) {
            return response()->json($res, $res['ok'] ? 200 : 409);
        }

        if ($res['ok']) {
            return back()->with('success', $res['message'] ?? 'Staging sukses.')
                         ->with('result', $res['data'] ?? null);
        }

        return back()->with('error', $res['message'] ?? 'Staging gagal.');
    }

    /**
     * Endpoint untuk cek status running (buat polling UI).
     * GET /staging/acumvms/status
     */
    public function status()
    {
        return response()->json([
            'running' => $this->isRunning(),
        ]);
    }

    /**
     * Helper cek lock sedang aktif atau tidak.
     */
    private function isRunning(): bool
    {
        // Cara aman: coba ambil lock dengan ttl kecil
        $lock = Cache::lock('acumvms_staging_lock', 1);
        $got = $lock->get();

        if ($got) {
            $lock->release();
            return false;
        }
        return true;
    }

    // =====================================================================
    // RUNNER (INI LOGIKA STAGING YANG DULU ADA DI AcumVmsStagingController)
    // =====================================================================
    public function run(string $appId = 'ACUMVMS', string $runBy = 'SYSTEM'): array
    {
        // =========================
        // LOCK (ANTI DOUBLE CRON/UI)
        // =========================
        $lock = Cache::lock('acumvms_staging_lock', 7200); // 2 jam (sesuaikan)
        if (!$lock->get()) {
            return ['ok' => false, 'message' => 'Staging masih berjalan'];
        }

        try {
            // =========================
            // 1) Ambil setting
            // =========================
            $setting = SysStagingSetting::query()
                ->where('id_application', $appId)
                ->where('status', 'A')
                ->first();

            if (!$setting) {
                return ['ok' => false, 'message' => "sys_staging_setting {$appId} tidak ditemukan / tidak aktif"];
            }

            $from = Carbon::parse($setting->last_update);
            $to   = Carbon::parse($setting->next_update);

            $result = [
                'po'             => 0,
                'po_detail'      => 0,
                'receipt'        => 0,
                'receipt_detail' => 0,
                'kontrak'        => 0,
                'window'         => [
                    'from' => $from->toDateTimeString(),
                    'to'   => $to->toDateTimeString(),
                ],
            ];

            // =========================
            // 2) Ambil PO HEADER (ONCE)
            // =========================
            $poHeaders = TrPO::query()
                ->where(function ($q) use ($from, $to) {
                    $q->whereBetween('submitdate', [$from, $to])
                    ->orWhereBetween('updated_at', [$from, $to]);
                })
                ->select(['id', 'ponbr', 'cpny_id'])
                ->get();

            $poHeaderIds = $poHeaders->pluck('id')->all();

            /**
             * Simpan pair unik PO header:
             * [
             *   ['ponbr' => '1000000001', 'cpny_id' => 'AW'],
             *   ['ponbr' => '1000000001', 'cpny_id' => 'EP'],
             * ]
             */
            $poHeaderPairs = $poHeaders
                ->map(fn ($r) => [
                    'ponbr'   => (string) $r->ponbr,
                    'cpny_id' => (string) $r->cpny_id,
                ])
                ->filter(fn ($r) => $r['ponbr'] !== '' && $r['cpny_id'] !== '')
                ->unique(fn ($r) => $r['ponbr'] . '|' . $r['cpny_id'])
                ->values();

            // $poHeaders = TrPO::query()
            //     ->where(function ($q) use ($from, $to) {
            //         $q->whereBetween('submitdate', [$from, $to])
            //           ->orWhereBetween('updated_at', [$from, $to]);
            //     })
            //     ->select(['id', 'ponbr', 'cpny_id'])
            //     ->get();

            // $poHeaderIds = $poHeaders->pluck('id')->all();
            // $ponbrList   = $poHeaders->pluck('ponbr')->filter()->unique()->values()->all();
            // $poCpnyMap   = $poHeaders->pluck('cpny_id', 'ponbr')->all();

            // =========================
            // 3) STAGING PO
            // =========================
            if (!empty($poHeaderIds)) {
                TrPO::query()
                    ->whereIn('id', $poHeaderIds)
                    ->select([
                        'id',
                        'ponbr','podate','cpny_id','vendorid','vendorname',
                        'potype','user_peminta',
                        'totalamt','taxcodeid','taxamt','grandtotalamt',
                        'status','created_by','created_at'
                    ])
                    ->orderBy('id')
                    ->chunkById(500, function ($rows) use (&$result) {
                        $payload = [];

                        foreach ($rows as $po) {
                            $potype = strtoupper(trim((string) $po->potype));

                            if ($potype === 'PO') {
                                $materialService = 'Purchase Material';
                            } elseif ($potype === 'SPK') {
                                $materialService = 'Purchase Service';
                            } else {
                                $materialService = $po->potype;
                            }

                            $payload[] = [
                                'ponbr'            => $po->ponbr,
                                'cpny_id'          => $po->cpny_id,
                                'podate'           => $po->podate,
                                'vendor_id'        => $po->vendorid,
                                'vendorname'       => $po->vendorname,
                                'purchaser'        => $po->created_by,
                                'material_service' => $materialService,
                                'totalamt'         => $po->totalamt,
                                'taxcodeid'        => $po->taxcodeid,
                                'taxamt'           => $po->taxamt,
                                'grandtotalamt'    => $po->grandtotalamt,
                                'status'           => $po->status,
                                'created_by'       => $po->created_by,
                                'created_at'       => $po->created_at,
                            ];
                        }

                        $this->upsertMysql7('staging_po', $payload, ['ponbr','cpny_id'], [
                            'podate','vendor_id','vendorname','purchaser','material_service',
                            'totalamt','taxcodeid','taxamt','grandtotalamt','status'
                        ]);

                        $result['po'] += count($payload);
                    });
            }

            // =========================
            // 4) STAGING PO DETAIL
            // =========================
            if ($poHeaderPairs->isNotEmpty()) {
                TrPOdetail::query()
                    ->where(function ($q) use ($poHeaderPairs) {
                        foreach ($poHeaderPairs as $pair) {
                            $q->orWhere(function ($sub) use ($pair) {
                                $sub->where('ponbr', $pair['ponbr'])
                                    ->where('budget_cpny_id', $pair['cpny_id']);
                            });
                        }
                    })
                    ->select([
                        'id',
                        'ponbr',
                        'budget_cpny_id',
                        'inventoryid',
                        'inventory_descr',
                        'qty',
                        'uom',
                        'unitcost',
                        'taxcodeid',
                        'taxamt',
                        'totalcost',
                        'status',
                        'created_by',
                        'created_at'
                    ])
                    ->orderBy('id')
                    ->chunkById(1000, function ($rows) use (&$result) {
                        $payload = [];

                        foreach ($rows as $d) {
                            $cpny = (string) ($d->budget_cpny_id ?? '');
                            if ($cpny === '') {
                                continue;
                            }

                            $payload[] = [
                                'ponbr'           => $d->ponbr,
                                'cpny_id'         => $cpny, // ambil dari budget_cpny_id
                                'linenbr'         => (int) $d->id,
                                'inventoryid'     => $d->inventoryid,
                                'inventory_descr' => $d->inventory_descr,
                                'qty'             => $d->qty,
                                'uom'             => $d->uom,
                                'unitcost'        => $d->unitcost,
                                'taxcodeid'       => $d->taxcodeid,
                                'taxamt'          => $d->taxamt,
                                'totalcost'       => $d->totalcost,
                                'status'          => $d->status,
                                'created_by'      => $d->created_by,
                                'created_at'      => $d->created_at,
                            ];
                        }

                        $this->upsertMysql7(
                            'staging_po_detail',
                            $payload,
                            ['ponbr', 'cpny_id', 'linenbr'],
                            [
                                'inventoryid',
                                'inventory_descr',
                                'qty',
                                'uom',
                                'unitcost',
                                'taxcodeid',
                                'taxamt',
                                'totalcost',
                                'status'
                            ]
                        );

                        $result['po_detail'] += count($payload);
                    });
            }

            // // =========================
            // // 4) STAGING PO DETAIL
            // // =========================
            // if (!empty($ponbrList)) {
            //     TrPOdetail::query()
            //         ->whereIn('ponbr', $ponbrList)
            //         ->select([
            //             'id','ponbr',
            //             'inventoryid','inventory_descr','qty','uom',
            //             'unitcost','taxcodeid','taxamt','totalcost',
            //             'status','created_by','created_at'
            //         ])
            //         ->orderBy('id')
            //         ->chunkById(1000, function ($rows) use (&$result, $poCpnyMap) {

            //             $payload = [];
            //             foreach ($rows as $d) {
            //                 $cpny = $poCpnyMap[$d->ponbr] ?? null;
            //                 if (!$cpny) continue;

            //                 $payload[] = [
            //                     'ponbr'           => $d->ponbr,
            //                     'cpny_id'         => $cpny,
            //                     'linenbr'         => (int) $d->id,
            //                     'inventoryid'     => $d->inventoryid,
            //                     'inventory_descr' => $d->inventory_descr,
            //                     'qty'             => $d->qty,
            //                     'uom'             => $d->uom,
            //                     'unitcost'        => $d->unitcost,
            //                     'taxcodeid'       => $d->taxcodeid,
            //                     'taxamt'          => $d->taxamt,
            //                     'totalcost'       => $d->totalcost,
            //                     'status'          => $d->status,
            //                     'created_by'      => $d->created_by,
            //                     'created_at'      => $d->created_at,
            //                 ];
            //             }

            //             $this->upsertMysql7(
            //                 'staging_po_detail',
            //                 $payload,
            //                 ['ponbr','cpny_id','linenbr'],
            //                 [
            //                     'inventoryid','inventory_descr','qty','uom',
            //                     'unitcost','taxcodeid','taxamt','totalcost','status'
            //                 ]
            //             );

            //             $result['po_detail'] += count($payload);
            //         });
            // }

            // =========================
            // 5) STAGING RECEIPT
            // =========================
            $receiptHeaders = TrReceipt::query()
                ->where(function ($q) use ($from, $to) {
                    $q->whereBetween('updated_at', [$from, $to])
                      ->orWhereBetween('created_at', [$from, $to]);
                })
                ->select(['receiptnbr','cpny_id'])
                ->get();

            $receiptNbrList = $receiptHeaders->pluck('receiptnbr')->unique()->values()->all();
            $receiptCpnyMap = $receiptHeaders->pluck('cpny_id', 'receiptnbr')->all();

            if (!empty($receiptNbrList)) {
                TrReceipt::query()
                    ->whereIn('receiptnbr', $receiptNbrList)
                    ->select([
                        'id','receiptnbr','receiptdate','receipttype',
                        'cpny_id','ponbr','status','created_by','created_at'
                    ])
                    ->orderBy('id')
                    ->chunkById(500, function ($rows) use (&$result) {

                        $payload = [];
                        foreach ($rows as $r) {
                            $payload[] = [
                                'receiptnbr'  => $r->receiptnbr,
                                'cpny_id'     => $r->cpny_id,
                                'receiptdate' => $r->receiptdate,
                                'receipttype' => $r->receipttype,
                                'ponbr'       => $r->ponbr,
                                'status'      => $r->status,
                                'created_by'  => $r->created_by,
                                'created_at'  => $r->created_at,
                            ];
                        }

                        $this->upsertMysql7(
                            'staging_receipt',
                            $payload,
                            ['receiptnbr','cpny_id'],
                            ['receiptdate','receipttype','ponbr','status']
                        );

                        $result['receipt'] += count($payload);
                    });
            }

            // =========================
            // 6) STAGING RECEIPT DETAIL
            // =========================
            if (!empty($receiptNbrList)) {
                TrReceiptdetail::query()
                    ->whereIn('receiptnbr', $receiptNbrList)
                    ->select([
                        'id','receiptnbr','ponbr',
                        'inventoryid','inventory_descr',
                        'qty_received','uom','status',
                        'created_by','created_at'
                    ])
                    ->orderBy('id')
                    ->chunkById(1000, function ($rows) use (&$result, $receiptCpnyMap) {

                        $payload = [];
                        foreach ($rows as $d) {
                            $cpny = $receiptCpnyMap[$d->receiptnbr] ?? null;
                            if (!$cpny) continue;

                            $payload[] = [
                                'receiptnbr'      => $d->receiptnbr,
                                'cpny_id'         => $cpny,
                                'ponbr'           => $d->ponbr,
                                'receiptlinenbr'  => (int) $d->id,
                                'polinenbr'       => null,
                                'inventoryid'     => $d->inventoryid,
                                'inventory_descr' => $d->inventory_descr,
                                'receiptqty'      => $d->qty_received,
                                'uom'             => $d->uom,
                                'status'          => $d->status,
                                'created_by'      => $d->created_by,
                                'created_at'      => $d->created_at,
                            ];
                        }

                        $this->upsertMysql7(
                            'staging_receipt_detail',
                            $payload,
                            ['receiptnbr','cpny_id','receiptlinenbr'],
                            [
                                'ponbr','polinenbr','inventoryid',
                                'inventory_descr','receiptqty','uom','status'
                            ]
                        );

                        $result['receipt_detail'] += count($payload);
                    });
            }

            // =========================
            // 6B) STAGING KONTRAK
            // =========================
            TrKontrak::query()
                ->where(function ($q) use ($from, $to) {
                    $q->whereBetween('submitdate', [$from, $to])
                      ->orWhereBetween('updated_at', [$from, $to]);
                })
                ->select([
                    'kontrakid','kontrakdate','cpny_id','csid','sppbjktid','department_id',
                    'vendorid','vendorname','purchaser','user_approval',
                    'kontraktype','kontrakcategory','nosk','nopklegal',
                    'startdate','enddate','kontaknote',
                    'status','created_by','created_at'
                ])
                ->orderBy('kontrakid')
                ->chunk(500, function ($rows) use (&$result) {

                    $payload = [];

                    foreach ($rows as $k) {
                        $payload[] = [
                            'kontrakid'       => $k->kontrakid,
                            'cpny_id'         => $k->cpny_id,
                            'kontrakdate'     => $k->kontrakdate,
                            'csid'            => $k->csid,
                            'sppbjktid'       => $k->sppbjktid,
                            'department_id'   => $k->department_id,
                            'vendor_id'       => $k->vendorid,
                            'vendorname'      => $k->vendorname,
                            'purchaser'       => $k->purchaser,
                            'user_approval'   => $k->user_approval,
                            'kontraktype'     => $k->kontraktype,
                            'kontrakcategory' => $k->kontrakcategory,
                            'nosk'            => $k->nosk,
                            'nopklegal'       => $k->nopklegal,
                            'startdate'       => $k->startdate,
                            'enddate'         => $k->enddate,
                            'kontaknote'      => $k->kontaknote,
                            'status'          => $k->status,
                            'created_by'      => $k->created_by,
                            'created_at'      => $k->created_at,
                        ];
                    }

                    $this->upsertMysql7(
                        'staging_kontrak',
                        $payload,
                        ['kontrakid','cpny_id'],
                        [
                            'kontrakdate','csid','sppbjktid','department_id',
                            'vendor_id','vendorname','purchaser','user_approval',
                            'kontraktype','kontrakcategory','nosk','nopklegal',
                            'startdate','enddate','kontaknote',
                            'status','created_by','created_at'
                        ]
                    );

                    $result['kontrak'] += count($payload);
                });

            // =========================
            // 7) UPDATE STAGING SETTING (BENAR)
            // =========================
            // last_update = "to" yang baru diproses
            // next_update = to + interval minutes
            // $intervalMin = (int)($setting->interval ?? 1440); // default 1 hari
            // $setting->last_update = $to;
            // $setting->next_update = $to->copy()->addMinutes($intervalMin);
            // $setting->lastupdate_user = 'SYSTEM';
            // $setting->lastupdate_datetime = now();
            // $setting->save();
            $nextDay = Carbon::parse($to)->addDay()->startOfDay(); // D+1 00:00:00

            $setting->last_update = $nextDay->copy()->setTime(0, 1, 0);   // D+1 00:01:00
            $setting->next_update = $nextDay->copy()->setTime(23, 59, 0); // D+1 23:59:00

            $setting->lastupdate_user = $runBy; // lebih bagus siapa yang run
            $setting->lastupdate_datetime = now();
            $setting->save();

            return ['ok' => true, 'message' => 'Staging injected sukses', 'data' => $result];

        } catch (\Throwable $e) {

            Log::error('[ACUMVMS STAGING] '.$e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return ['ok' => false, 'message' => $e->getMessage()];

        } finally {

            // =========================
            // WAJIB: PUTUS KONEKSI
            // =========================
            DB::disconnect('pgsql');
            DB::disconnect('pgsql2');
            DB::disconnect('mysql7');

            optional($lock)->release();
        }
    }

    private function upsertMysql7(string $table, array $payload, array $uniqueBy, array $updateColumns): void
    {
        if (empty($payload)) return;

        DB::connection('mysql7')
            ->table($table)
            ->upsert($payload, $uniqueBy, $updateColumns);
    }
}
