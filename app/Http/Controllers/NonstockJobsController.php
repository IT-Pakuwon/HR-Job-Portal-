<?php

namespace App\Http\Controllers;

use App\Models\TrItemRequest;
use App\Models\MsInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Vinkla\Hashids\Facades\Hashids;
use App\Models\MsBaseUom;
use App\Models\MsInvItemType;
use App\Models\MsInvItemSubType;
use App\Models\MsInvItemClass;
use App\Models\MsInvItemSubClass;

class NonstockJobsController extends Controller
{
    private function userCpnyIds($user): array
    {
        if (!$user) return [];

        $cpny = $user->cpny_id ?? [];
        if (is_string($cpny)) {
            return array_values(array_filter(array_map('trim', explode(',', $cpny))));
        }
        if (is_array($cpny)) {
            return array_values(array_filter(array_map('trim', $cpny)));
        }
        return [(string) $cpny];
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $cpnyIds = $this->userCpnyIds($user);

        $nonstockJobs = TrItemRequest::query()
            ->where('status', 'C')
            ->where('inventory_type', 'NONSTOCK')
            ->whereIn('cpny_id', $cpnyIds)
            ->whereNull('inventoryid')
            ->count();

        $nonstockDone = TrItemRequest::query()
            ->where('status', 'C')
            ->where('inventory_type', 'NONSTOCK')
            ->whereIn('cpny_id', $cpnyIds)
            ->whereNotNull('inventoryid')
            ->count();

        // Card count: GI & status A
        $inventoryNonstock = MsInventory::query()
            ->whereIn('item_type', ['NS', 'SE'])
            ->where('status', 'A')
            ->count();

        $baseuom = MsBaseUom::query()           
            ->where('status', 'A')
            ->get();
        
        return view('pages.itemrequest.nonstockjobs', compact(
            'nonstockJobs',
            'nonstockDone',
            'inventoryNonstock',
            'baseuom'
        ));
    }

    public function json(Request $request)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);

        $source = strtolower((string) $request->get('source', 'jobs'));
        if ($source === 'inventory') {
            return $this->jsonInventory($request);
        }
        return $this->jsonJobs($request, $user);
    }

    private function jsonInventory(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $search = trim((string) $request->input('search.value', ''));

        // IMPORTANT:
        // tampilkan GI (all status), biar toggle A/X masih terlihat
        $q = MsInventory::query()
            ->whereIn('item_type', ['NS', 'SE']);

        $recordsTotal = (clone $q)->count();

        if ($search !== '') {
            $q->where(function ($qq) use ($search) {
                // $qq->where('inventoryid', 'ilike', "%{$search}%")
                //     ->orWhere('inventory_descr', 'ilike', "%{$search}%")
                //     ->orWhere('item_sub_type', 'ilike', "%{$search}%")
                //     ->orWhere('item_class', 'ilike', "%{$search}%")
                //     ->orWhere('item_sub_class', 'ilike', "%{$search}%")
                //     ->orWhere('stock_unit', 'ilike', "%{$search}%")
                //     ->orWhere('status', 'ilike', "%{$search}%");
                $qq->where('inventoryid', 'ilike', "%{$search}%")
                    ->orWhere('inventory_descr', 'ilike', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $q)->count();

        // karena kolom ke-0 di datatable adalah "Actions",
        // mapping order harus start dari index 1
        $columns = [
            1 => 'inventoryid',
            2 => 'inventory_descr',
            3 => 'item_sub_type',
            4 => 'item_class',
            5 => 'item_sub_class',
            6 => 'stock_unit',
            7 => 'status',
        ];

        $orderColIndex = (int) $request->input('order.0.column', 1);
        $orderDir      = $request->input('order.0.dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $orderBy       = $columns[$orderColIndex] ?? 'inventoryid';

        $rows = $q->orderBy($orderBy, $orderDir)
            ->skip($start)
            ->take($length)
            ->get([
                'id',
                'inventoryid',
                'inventory_descr',
                'item_sub_type',
                'item_class',
                'item_sub_class',
                'stock_unit',
                'status',
            ]);

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $rows,
        ]);
    }

    private function jsonJobs(Request $request, $user)
    {
        $cpnyIds = $this->userCpnyIds($user);

        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $search = trim((string) $request->input('search.value', ''));

        $filter = strtolower((string) $request->get('filter', 'all')); // all | jobs | done

        $q = TrItemRequest::query()
            ->where('status', 'C')
            ->where('inventory_type', 'NONSTOCK')
            ->whereIn('cpny_id', $cpnyIds);

        if ($filter === 'jobs') {
            $q->whereNull('inventoryid');
        } elseif ($filter === 'done') {
            $q->whereNotNull('inventoryid');
        }

        $recordsTotal = (clone $q)->count();

        if ($search !== '') {
            $q->where(function ($qq) use ($search) {
                $qq->where('irid', 'ilike', "%{$search}%")
                    ->orWhere('cpny_id', 'ilike', "%{$search}%")
                    ->orWhere('department_id', 'ilike', "%{$search}%")
                    ->orWhere('inventory_descr_req', 'ilike', "%{$search}%")
                    ->orWhere('inventoryid', 'ilike', "%{$search}%")
                    ->orWhere('created_by', 'ilike', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $q)->count();

        $columns = [
            0 => 'irid',
            1 => 'irdate',
            2 => 'cpny_id',
            3 => 'department_id',
            4 => 'inventory_descr_req',
            5 => 'inventoryid',
            6 => 'created_by',
            7 => 'created_at',
        ];

        $orderColIndex = (int) $request->input('order.0.column', 7);
        $orderDir      = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $orderBy       = $columns[$orderColIndex] ?? 'created_at';

        $rows = $q->orderBy($orderBy, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(function ($r) {
            return [
                'trid'                => $r->id,
                'eid'                 => Hashids::encode($r->id),
                'irid'                => $r->irid,
                'irdate'              => $r->irdate,
                'cpny_id'             => $r->cpny_id,
                'department_id'       => $r->department_id,
                'inventory_descr_req' => $r->inventory_descr_req,
                'inventoryid'         => $r->inventoryid,
                'is_done'             => !empty($r->inventoryid),
                'created_by'          => $r->created_by,
                'created_at'          => $r->created_at,
            ];
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    // =========================
    // INVENTORY CRUD (AJAX)
    // =========================

    public function store(Request $request)
    {
        $request->validate([
            'inventory_descr'     => ['required', 'string', 'max:255'],

            // ⚠️ ini "code string" (GI/STOCK/ATK/BKU), bukan bigint
            'item_type_id'        => ['required', 'string', 'max:50'],
            'item_sub_type_id'    => ['required', 'string', 'max:50'],
            'item_class_id'       => ['required', 'string', 'max:50'],
            'item_sub_class_id'   => ['required', 'string', 'max:50'],

            'stock_unit'          => ['required', 'string', 'max:50'],
            'purchase_unit'       => ['required', 'string', 'max:50'],
            'item_category'       => ['nullable', 'string', 'max:100'],
        ]);

        DB::beginTransaction();
        try {
            $loginUser = Auth::user();

            $typeCode    = strtoupper(trim($request->item_type_id));       // GI
            $subTypeCode = strtoupper(trim($request->item_sub_type_id));   // STOCK
            $classCode   = strtoupper(trim($request->item_class_id));      // ATK
            $subClsCode  = strtoupper(trim($request->item_sub_class_id));  // BKU

            /*
            |--------------------------------------------------------------------------
            | 1) VALIDASI MASTER (lookup pakai CODE STRING)
            |--------------------------------------------------------------------------
            | Sesuaikan nama kolom jika di tabel master berbeda.
            */
            $type = MsInvItemType::whereRaw('UPPER(TRIM(item_type_id)) = ?', [$typeCode])->first();
            if (!$type) {
                return response()->json(['error' => "Item Type tidak valid: {$typeCode}"], 422);
            }

            $subType = MsInvItemSubType::whereRaw('UPPER(TRIM(item_sub_type_id)) = ?', [$subTypeCode])
                // kalau tabelmu punya relasi ke type pakai id string:
                ->whereRaw('UPPER(TRIM(item_type_id)) = ?', [$typeCode])
                ->first();
            if (!$subType) {
                return response()->json(['error' => "Item Sub Type tidak valid: {$subTypeCode}"], 422);
            }

            $cls = MsInvItemClass::whereRaw('UPPER(TRIM(item_class_id)) = ?', [$classCode])
                // kalau tabelmu punya relasi ke sub type pakai code:
                ->whereRaw('UPPER(TRIM(item_sub_type_id)) = ?', [$subTypeCode])
                ->first();
            if (!$cls) {
                return response()->json(['error' => "Item Class tidak valid: {$classCode}"], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | 2) SUB CLASS + LOCK autonbr (autonbr + 1)
            |--------------------------------------------------------------------------
            */
            $subCls = MsInvItemSubClass::whereRaw('UPPER(TRIM(item_sub_class_id)) = ?', [$subClsCode])
                // kalau tabelmu punya relasi ke class pakai code:
                ->whereRaw('UPPER(TRIM(item_class_id)) = ?', [$classCode])
                ->lockForUpdate()
                ->first();

            if (!$subCls) {
                return response()->json(['error' => "Item Sub Class tidak valid: {$subClsCode}"], 422);
            }

            $nextAutoNbr = ((int) ($subCls->autonbr ?? 0)) + 1;

            // update autonbr
            $subCls->autonbr     = $nextAutoNbr;
            $subCls->updated_by  = $loginUser->username ?? 'system';
            $subCls->updated_at  = now();
            $subCls->save();

            /*
            |--------------------------------------------------------------------------
            | 3) GENERATE inventoryid: ATK + BKU + 1001 (padded)
            |--------------------------------------------------------------------------
            */
            $inventoryid = $classCode . $subClsCode . str_pad($nextAutoNbr, 4, '0', STR_PAD_LEFT);

            // pastikan unique (double safety)
            $exists = MsInventory::where('inventoryid', $inventoryid)->exists();
            if ($exists) {
                // kalau kejadian, biasanya karena data sebelumnya sudah pakai nomor itu.
                // opsi: loop + increment lagi (jarang kalau lock benar)
                return response()->json(['error' => "InventoryID sudah ada: {$inventoryid}"], 409);
            }

            /*
            |--------------------------------------------------------------------------
            | 4) INSERT ke ms_inventory (ms_inventory simpan STRING sesuai model kamu)
            |--------------------------------------------------------------------------
            */
            $inv = MsInventory::create([
                'inventoryid'     => strtoupper($inventoryid),
                'inventory_descr' => strtoupper(trim($request->inventory_descr)),

                // simpan sebagai string (sesuai model MsInventory kamu)
                'item_type'       => $typeCode,
                'item_sub_type'   => $subTypeCode,
                'item_class'      => $classCode,
                'item_sub_class'  => $subClsCode,

                'item_category'   => $request->item_category,
                'stock_unit'      => strtoupper(trim($request->stock_unit)),
                'purchase_unit'   => strtoupper(trim($request->purchase_unit)),

                'status'          => 'A',
                'created_by'      => $loginUser->username ?? 'system',
                'created_at'      => now(),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'inventory' => $inv]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Gagal menyimpan inventory',
                'message' => $e->getMessage()
            ], 500);
        }
    }

   
    public function edit($id)
    {
        $inv = MsInventory::findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | Mapping NAME -> ID (AMAN, CASE INSENSITIVE, TRIM)
        |--------------------------------------------------------------------------
        */

        $type = MsInvItemType::whereRaw(
            'UPPER(TRIM(item_type_name)) = ?',
            [strtoupper(trim($inv->item_type))]
        )->first();

        $subType = MsInvItemSubType::whereRaw(
            'UPPER(TRIM(item_sub_type_name)) = ?',
            [strtoupper(trim($inv->item_sub_type))]
        )->first();

        $cls = MsInvItemClass::whereRaw(
            'UPPER(TRIM(item_class_name)) = ?',
            [strtoupper(trim($inv->item_class))]
        )->first();

        $subCls = MsInvItemSubClass::whereRaw(
            'UPPER(TRIM(item_sub_class_name)) = ?',
            [strtoupper(trim($inv->item_sub_class))]
        )->first();

        /*
        |--------------------------------------------------------------------------
        | Response JSON (KIRIM NAMA + ID)
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'id' => $inv->id,
            'inventoryid' => $inv->inventoryid,
            'inventory_descr' => $inv->inventory_descr,

            // ===== NAMA (untuk fallback)
            'item_type' => $inv->item_type,
            'item_sub_type' => $inv->item_sub_type,
            
            'item_class' => $inv->item_class,
            'item_sub_class' => $inv->item_sub_class,

            'item_category' => $inv->item_category,
            'stock_unit' => $inv->stock_unit,
            'purchase_unit' => $inv->purchase_unit,

            // ===== ID (INI YANG DIPAKAI CHAIN DROPDOWN)
            'item_type_id'      => $type ? $type->item_type_id : null,
            'item_sub_type_id'  => $subType ? $subType->item_sub_type_id : null,
            'item_class_id'     => $cls ? $cls->item_class_id : null,
            'item_sub_class_id' => $subCls ? $subCls->item_sub_class_id : null,

            // ===== DEBUG (boleh dihapus nanti)
            '_debug' => [
                'type_found' => (bool) $type,
                'sub_type_found' => (bool) $subType,
                'class_found' => (bool) $cls,
                'sub_class_found' => (bool) $subCls,
            ]
        ]);
    }




    public function update(Request $request, $id)
    {
        $inv = MsInventory::findOrFail($id);

        $request->validate([
            'inventory_descr'   => ['required','string','max:255'],

            // ⬇️ masih STRING (GI / STOCK / ATK / BKU)
            // 'item_type_id'      => ['required','string','max:50'],
            // 'item_sub_type_id'  => ['required','string','max:50'],
            // 'item_class_id'     => ['required','string','max:50'],
            // 'item_sub_class_id' => ['required','string','max:50'],

            'item_category'     => ['nullable','string','max:100'],
            // 'stock_unit'        => ['required','string','max:50'],
            // 'purchase_unit'     => ['required','string','max:50'],
        ]);

        DB::beginTransaction();
        try {
            $loginUser = Auth::user();

            // normalisasi ke uppercase
            $typeCode    = strtoupper(trim($request->item_type_id));
            $subTypeCode = strtoupper(trim($request->item_sub_type_id));
            $classCode   = strtoupper(trim($request->item_class_id));
            $subClsCode  = strtoupper(trim($request->item_sub_class_id));

            /*
            |--------------------------------------------------------------------------
            | OPTIONAL: validasi master (tanpa autonbr & tanpa inventoryid)
            |--------------------------------------------------------------------------
            */
            // $type = MsInvItemType::whereRaw(
            //     'UPPER(TRIM(item_type_id)) = ?',
            //     [$typeCode]
            // )->first();

            // if (!$type) {
            //     return response()->json(['error' => "Item Type tidak valid: {$typeCode}"], 422);
            // }

            // $subType = MsInvItemSubType::whereRaw(
            //         'UPPER(TRIM(item_sub_type_id)) = ?',
            //         [$subTypeCode]
            //     )
            //     ->whereRaw('UPPER(TRIM(item_type_id)) = ?', [$typeCode])
            //     ->first();

            // if (!$subType) {
            //     return response()->json(['error' => "Item Sub Type tidak valid: {$subTypeCode}"], 422);
            // }

            // $cls = MsInvItemClass::whereRaw(
            //         'UPPER(TRIM(item_class_id)) = ?',
            //         [$classCode]
            //     )
            //     ->whereRaw('UPPER(TRIM(item_sub_type_id)) = ?', [$subTypeCode])
            //     ->first();

            // if (!$cls) {
            //     return response()->json(['error' => "Item Class tidak valid: {$classCode}"], 422);
            // }

            // $subCls = MsInvItemSubClass::whereRaw(
            //         'UPPER(TRIM(item_sub_class_id)) = ?',
            //         [$subClsCode]
            //     )
            //     ->whereRaw('UPPER(TRIM(item_class_id)) = ?', [$classCode])
            //     ->first();

            // if (!$subCls) {
            //     return response()->json(['error' => "Item Sub Class tidak valid: {$subClsCode}"], 422);
            // }

            /*
            |--------------------------------------------------------------------------
            | UPDATE ms_inventory (inventoryid TIDAK DIUBAH)
            |--------------------------------------------------------------------------
            */
            $inv->update([
                'inventory_descr' => strtoupper(trim($request->inventory_descr)),

                // 'item_type'       => $typeCode,
                // 'item_sub_type'   => $subTypeCode,
                // 'item_class'      => $classCode,
                // 'item_sub_class'  => $subClsCode,

                'item_category'   => $request->item_category,
                // 'stock_unit'      => strtoupper(trim($request->stock_unit)),
                // 'purchase_unit'   => strtoupper(trim($request->purchase_unit)),

                'updated_by'      => $loginUser->username ?? 'system',
                'updated_at'      => now(),
            ]);

            DB::commit();
            return response()->json(['success' => true]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Gagal update inventory',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function toggleStatus(Request $request, $id)
    {
        $inv = MsInventory::findOrFail($id);

        $request->validate([
            'status' => ['required', Rule::in(['A','X'])],
        ]);

        $loginUser = Auth::user();

        $inv->update([
            'status'     => $request->status,
            'updated_by' => $loginUser->username ?? 'system',
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Status updated']);
    }

    public function inventoryPickJson(Request $request)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);

        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = trim((string) $request->input('search.value', ''));

        $q = MsInventory::query()
            ->whereIn('item_type', ['NS', 'SE'])
            ->where('status', 'A');

        $recordsTotal = (clone $q)->count();

        if ($search !== '') {
            $q->where(function ($qq) use ($search) {
                $qq->where('inventoryid', 'ilike', "%{$search}%")
                ->orWhere('inventory_descr', 'ilike', "%{$search}%");
            });
        }

        $recordsFiltered = (clone $q)->count();

        $rows = $q->orderBy('inventoryid', 'asc')
            ->skip($start)
            ->take($length)
            ->get(['inventoryid', 'inventory_descr']);

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows,
        ]);
    }

    public function setInventoryToItemRequest(Request $request)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);

        $data = $request->validate([
            'trid'       => ['required','integer'],
            'inventoryid'=> ['required','string','max:50'],
        ]);

        DB::beginTransaction();
        try {
            // validasi inventory exists & aktif
            $inv = MsInventory::query()
                ->where('inventoryid', $data['inventoryid'])
                ->whereIn('item_type', ['NS', 'SE'])
                ->where('status', 'A')
                ->first();


            if (!$inv) {
                return response()->json(['message' => 'Inventory tidak ditemukan / tidak aktif'], 422);
            }

            $tr = TrItemRequest::findOrFail($data['trid']);

            // optional: hanya boleh update kalau masih STOCK & status C
            if (strtoupper((string)$tr->inventory_type) !== 'NONSTOCK' || strtoupper((string)$tr->status) !== 'C') {
                return response()->json(['message' => 'Dokumen tidak valid untuk update inventory'], 422);
            }

            $tr->update([
                'inventoryid' => $data['inventoryid'],
                'updated_by'  => $user->username ?? 'system',
                'updated_at'  => now(),
            ]);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function rollbackInventory(Request $request, $eid)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);

        $decoded = Hashids::decode($eid);
        if (empty($decoded)) {
            return response()->json(['message' => 'Invalid ID'], 422);
        }
        $id = $decoded[0];

        $cpnyIds = $this->userCpnyIds($user);

        $row = TrItemRequest::query()
            ->where('id', $id)
            ->where('status', 'C')
            ->where('inventory_type', 'NONSTOCK')
            ->whereIn('cpny_id', $cpnyIds)
            ->first();

        if (!$row) {
            return response()->json(['message' => 'Data not found / not allowed'], 404);
        }

        DB::beginTransaction();
        try {
            $row->update([
                'inventoryid'  => null,
                'updated_by'   => $user->username ?? 'system',
                'updated_at'   => now(),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Inventory ID cleared']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function NonstockTypes()
    {
        $rows = MsInvItemType::query()
            ->whereIn('item_type_id', ['NS', 'SE'])
            ->where('status', 'A')
            ->orderBy('item_type_name')
            ->get(['item_type_id', 'item_type_name']);

        return response()->json([
            'data' => $rows->map(fn($r) => [
                'id'   => $r->item_type_id,
                'text' => $r->item_type_name,
            ])
        ]);
    }

    public function NonstockSubTypes(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'item_type_id' => ['required','string'],
        ]);

        $rows = MsInvItemSubType::query()
            ->where('status', 'A')
            ->where('item_type_id', $request->item_type_id)
            ->orderBy('item_sub_type_name')
            ->get(['item_sub_type_id', 'item_sub_type_name']);

        return response()->json([
            'data' => $rows->map(fn($r) => [
                'id'   => $r->item_sub_type_id,
                'text' => $r->item_sub_type_name,
            ])
        ]);
    }

    public function NonstockClasses(Request $request)
    {
        $request->validate([
            'item_sub_type_id' => ['required','string'],
        ]);

        $rows = MsInvItemClass::query()
            ->where('status', 'A')
            ->where('item_sub_type_id', $request->item_sub_type_id)
            ->orderBy('item_class_name')
            ->get(['item_class_id', 'item_class_name']);

        return response()->json([
            'data' => $rows->map(fn($r) => [
                'id'   => $r->item_class_id,
                'text' => $r->item_class_name,
            ])
        ]);
    }

    public function NonstockSubClasses(Request $request)
    {
        $request->validate([
            'item_class_id' => ['required','string'],
        ]);

        $rows = MsInvItemSubClass::query()
            ->where('status', 'A')
            ->where('item_class_id', $request->item_class_id)
            ->orderBy('item_sub_class_name')
            ->get(['item_sub_class_id', 'item_sub_class_name']);

        return response()->json([
            'data' => $rows->map(fn($r) => [
                'id'   => $r->item_sub_class_id,
                'text' => $r->item_sub_class_name,
            ])
        ]);
    }


}
