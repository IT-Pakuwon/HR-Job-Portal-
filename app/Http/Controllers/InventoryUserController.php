<?php

namespace App\Http\Controllers;

use App\Models\BusinessUnit;
use App\Models\MsInventory;
use App\Models\Userbusinessunit;
use App\Models\Usercpny;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryUserController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        abort_if(!$user, 401);

        // ✅ company dari usercpny
        $cpnyIds = Usercpny::where('username', $user->username)
            ->where('status', 'A')
            ->pluck('cpny_id')
            ->map(fn ($v) => strtoupper(trim((string) $v)))
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // ✅ BU dari ms_user_business_unit (hanya yang user punya)
        // lalu join ke ms_business_unit untuk ambil nama + integration fields
        $buList = Userbusinessunit::query()
    ->where('ms_user_business_unit.username', $user->username)
    ->where('ms_user_business_unit.status', 'A') // ✅ FIX
    ->join('ms_business_unit as bu', function ($join) {
        $join->on('bu.business_unit_id', '=', 'ms_user_business_unit.business_unit_id');
    })
    ->where('bu.status', 'A')
    ->get([
        'ms_user_business_unit.cpny_id as cpny_id',
        'ms_user_business_unit.business_unit_id as business_unit_id',
        'bu.business_unit_name',
        'bu.integration_type',
        'bu.ifca_entity_cd',
        'bu.solomon_cpny_id',
        'bu.cpny_id as bu_cpny_id',
    ])
            ->map(function ($r) {
                // rapikan
                $r->cpny_id = strtoupper(trim((string) $r->cpny_id));
                $r->business_unit_id = trim((string) $r->business_unit_id);

                return $r;
            });

        return view('pages.inventory.inventoryuser', compact('cpnyIds', 'buList'));
    }

    public function json(Request $request)
    {
        $user = Auth::user();

        $typeFilter = strtoupper(trim((string) $request->get('type_filter', ''))); // STOCK / NONSTOCK / ''
        $cpnyId = strtoupper(trim((string) $request->get('cpny_id', '')));
        $businessUnitId = trim((string) $request->get('business_unit_id', ''));

        // =========================
        // BASE QUERY (POSTGRES)
        // =========================
        $q = MsInventory::query()->select([
            'id',
            'inventoryid',
            'inventory_descr',
            'item_type',
            'stock_unit',
            'item_sub_type',
            'item_class',
            'item_sub_class',
            'item_category',
            'stock_unit',
            'purchase_unit',
            'status',
        ]);

        if ($typeFilter === 'STOCK') {
            $q->where('item_type', 'GI');
        } elseif ($typeFilter === 'NONSTOCK') {
            $q->whereIn('item_type', ['NS', 'SE']);
        }

        // kalau NONSTOCK, tidak perlu stock
        $needStock = ($typeFilter === 'STOCK');

        // ambil data inventory dari PG (kamu masih non-serverSide, jadi ambil semua)
        $items = $q->orderByDesc('id')->get();

        // =========================
        // AMBIL STOCK (SQL SERVER) - CHUNK SAFE
        // =========================
        $stockMap = [];

        if ($needStock && $businessUnitId !== '') {
            $bu = BusinessUnit::query()
                ->where('business_unit_id', $businessUnitId)
                ->where('status', 'A')
                ->first(['business_unit_id', 'cpny_id', 'integration_type', 'ifca_entity_cd', 'solomon_cpny_id']);

            if ($bu) {
                $integrationType = strtoupper(trim((string) $bu->integration_type));

                // tentukan site filter sesuai BU
                $siteFilter = null;
                if ($integrationType === 'SOLOMON') {
                    $siteFilter = trim((string) ($bu->solomon_cpny_id ?? ''));
                } elseif ($integrationType === 'IFCA') {
                    $siteFilter = trim((string) ($bu->ifca_entity_cd ?? ''));
                } else {
                    $siteFilter = trim((string) ($bu->ifca_entity_cd ?? ''));
                    if ($siteFilter === '') {
                        $siteFilter = trim((string) ($bu->solomon_cpny_id ?? ''));
                    }
                }
                if ($siteFilter === '') {
                    $siteFilter = null;
                }

                // pilih model view inventory sesuai cpny BU + integration type
                $model = null;

                if ($integrationType === 'SOLOMON') {
                    switch (strtoupper($bu->cpny_id)) {
                        case 'AW':  $model = \App\Models\ViewInventoryAW::class;
                            break;
                        case 'EP':  $model = \App\Models\ViewInventoryEPH::class;
                            break;
                        case 'O8':  $model = \App\Models\ViewInventoryO8::class;
                            break;
                        case 'PSA': $model = \App\Models\ViewInventoryPSA::class;
                            break;
                    }
                } elseif ($integrationType === 'IFCA') {
                    switch (strtoupper($bu->cpny_id)) {
                        case 'AW':  $model = \App\Models\ViewInventoryAWIfca::class;
                            break;
                        case 'EP':  $model = \App\Models\ViewInventoryEPHIfca::class;
                            break;
                        case 'GPS': $model = \App\Models\ViewInventoryGPSIfca::class;
                            break;
                        case 'O8':  $model = \App\Models\ViewInventoryO8Ifca::class;
                            break;
                        case 'PSA': $model = \App\Models\ViewInventoryPSAIfca::class;
                            break;
                    }
                }

                if ($model) {
                    // list invtid dari PG
                    $invIds = $items->pluck('inventoryid')
                        ->filter()
                        ->map(fn ($v) => strtoupper(trim((string) $v)))
                        ->unique()
                        ->values()
                        ->all();

                    // ✅ CHUNK supaya tidak kena limit 2100 parameter
                    $chunks = array_chunk($invIds, 1000); // aman

                    foreach ($chunks as $chunk) {
                        $stockRows = $model::query()
                            ->selectRaw('RTRIM(LTRIM(invtid)) AS invtid, CAST(stock AS float) AS stock, siteid, cpnyid')
                            ->whereIn('invtid', $chunk)
                            // SOLOMON biasanya butuh cpnyid + siteid
                            ->when($integrationType === 'SOLOMON' && $cpnyId !== '', fn ($q) => $q->where('cpnyid', $cpnyId))
                            ->when($siteFilter, fn ($q) => $q->where('siteid', $siteFilter))
                            ->get();

                        foreach ($stockRows as $r) {
                            $key = strtoupper(trim((string) $r->invtid));
                            // kalau ada multiple site, kamu bisa sum; sekarang ambil yang terakhir
                            $stockMap[$key] = (float) $r->stock;
                        }
                    }
                }
            }
        }

        // =========================
        // RESPONSE
        // =========================
        $data = $items->map(function ($r) use ($stockMap, $needStock) {
            $key = strtoupper(trim((string) $r->inventoryid));

            return [
                'id' => $r->id,
                'inventoryid' => $r->inventoryid,
                'inventory_descr' => $r->inventory_descr,
                'item_type' => $r->item_type,
                'item_sub_type' => $r->item_sub_type,
                'item_class' => $r->item_class,
                'item_sub_class' => $r->item_sub_class,
                'stock_unit' => $r->stock_unit,
                'status' => $r->status,
                // stock hanya meaningful untuk STOCK tab
                'stock' => $needStock ? ($stockMap[$key] ?? 0) : null,
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function json_zzzz(Request $request)
    {
        $user = Auth::user();
        abort_if(!$user, 401);

        $typeFilter = strtoupper(trim((string) $request->get('type_filter', ''))); // STOCK / NONSTOCK / ''
        $cpnyId = strtoupper(trim((string) $request->get('cpny_id', '')));
        $businessUnitId = trim((string) $request->get('business_unit_id', ''));

        // ✅ company allowed
        $allowedCpny = Usercpny::where('username', $user->username)
            ->where('status', 'A')
            ->pluck('cpny_id')
            ->map(fn ($v) => strtoupper(trim((string) $v)))
            ->toArray();

        if ($cpnyId !== '' && !in_array($cpnyId, $allowedCpny, true)) {
            return response()->json(['data' => []]);
        }

        // =========================
        // INVENTORY (PG)
        // =========================
        $q = MsInventory::query()->select([
            'id', 'inventoryid', 'inventory_descr', 'item_type', 'item_class', 'status',
        ]);

        if ($typeFilter === 'STOCK') {
            $q->where('item_type', 'GI');
        } elseif ($typeFilter === 'NONSTOCK') {
            $q->whereIn('item_type', ['NS', 'SE']);
        }

        $items = $q->orderByDesc('id')->get();

        // =========================
        // STOCK MAP (HANYA KALAU STOCK)
        // =========================
        $stockMap = [];

        if ($typeFilter === 'STOCK' && $businessUnitId !== '') {
            // ✅ BU harus milik user (ms_user_business_unit)
            $ub = Userbusinessunit::query()
                ->where('username', $user->username)
                ->where('status', 'A')
                ->where('business_unit_id', $businessUnitId)
                ->when($cpnyId !== '', fn ($qq) => $qq->where('cpny_id', $cpnyId))
                ->first(['cpny_id', 'business_unit_id']);

            if ($ub) {
                // ambil detail BU dari master BU
                $bu = BusinessUnit::query()
                    ->where('status', 'A')
                    ->where('business_unit_id', $businessUnitId)
                    ->first(['business_unit_id', 'cpny_id', 'integration_type', 'ifca_entity_cd', 'solomon_cpny_id']);

                if ($bu) {
                    $integrationType = strtoupper(trim((string) ($bu->integration_type ?? '')));
                    $buCpny = strtoupper(trim((string) ($bu->cpny_id ?? '')));

                    // site filter (sama seperti logic lama kamu)
                    $siteFilter = null;
                    if ($integrationType === 'SOLOMON') {
                        $siteFilter = trim((string) ($bu->solomon_cpny_id ?? ''));
                    } elseif ($integrationType === 'IFCA') {
                        $siteFilter = trim((string) ($bu->ifca_entity_cd ?? ''));
                    } else {
                        $siteFilter = trim((string) ($bu->ifca_entity_cd ?? ''));
                        if ($siteFilter === '') {
                            $siteFilter = trim((string) ($bu->solomon_cpny_id ?? ''));
                        }
                    }
                    if ($siteFilter === '') {
                        $siteFilter = null;
                    }

                    // pilih model SQLServer
                    $model = null;
                    switch ($buCpny) {
                        case 'AW':
                            $model = ($integrationType === 'IFCA')
                                ? \App\Models\ViewInventoryAWIfca::class
                                : \App\Models\ViewInventoryAW::class;
                            break;
                        case 'EP':
                            $model = ($integrationType === 'IFCA')
                                ? \App\Models\ViewInventoryEPHIfca::class
                                : \App\Models\ViewInventoryEPH::class;
                            break;
                        case 'O8':
                            $model = ($integrationType === 'IFCA')
                                ? \App\Models\ViewInventoryO8Ifca::class
                                : \App\Models\ViewInventoryO8::class;
                            break;
                        case 'PSA':
                            $model = ($integrationType === 'IFCA')
                                ? \App\Models\ViewInventoryPSAIfca::class
                                : \App\Models\ViewInventoryPSA::class;
                            break;
                        case 'GPS':
                            $model = \App\Models\ViewInventoryGPSIfca::class;
                            break;
                    }

                    if ($model && $items->isNotEmpty()) {
                        $invIds = $items->pluck('inventoryid')
                            ->map(fn ($v) => strtoupper(trim((string) $v)))
                            ->filter()
                            ->unique()
                            ->values()
                            ->all();

                        $stockRows = $model::query()
                            ->selectRaw('RTRIM(LTRIM(invtid)) AS invtid, CAST(stock AS float) AS stock, siteid, cpnyid')
                            ->whereIn('invtid', $invIds)
                            // ✅ cpnyid biasanya AW/EP/O8.. (bukan entity)
                            ->when($buCpny !== '', fn ($qq) => $qq->where('cpnyid', $buCpny))
                            // ✅ siteid filter by BU mapping
                            ->when($siteFilter, fn ($qq) => $qq->where('siteid', $siteFilter))
                            ->get();

                        foreach ($stockRows as $sr) {
                            $k = strtoupper(trim((string) $sr->invtid));
                            $stockMap[$k] = ($stockMap[$k] ?? 0) + (float) $sr->stock;
                        }
                    }
                }
            }
        }

        // output
        $data = $items->map(function ($r) use ($typeFilter, $stockMap) {
            $key = strtoupper(trim((string) $r->inventoryid));

            return [
                'id' => $r->id,
                'inventoryid' => $r->inventoryid,
                'inventory_descr' => $r->inventory_descr,
                'item_type' => $r->item_type,
                'item_class' => $r->item_class,
                'status' => $r->status,
                // ✅ stock hanya meaningful di STOCK tab
                'stock' => ($typeFilter === 'STOCK') ? ($stockMap[$key] ?? 0) : null,
            ];
        })->values();

        return response()->json(['data' => $data]);
    }
}
