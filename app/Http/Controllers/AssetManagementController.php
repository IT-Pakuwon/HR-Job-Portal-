<?php

namespace App\Http\Controllers;

use App\Models\MsCompany;
use App\Models\MsDepartment;
use App\Models\Usercpny;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class AssetManagementController extends Controller
{
    // ─── JSON file path ───────────────────────────────────────────────────────────

    private function storagePath(): string
    {
        return storage_path('app/asset-management.json');
    }

    // ─── File-based storage helpers ───────────────────────────────────────────────

    private function readStore(): array
    {
        if (!file_exists($this->storagePath())) {
            return ['next_id' => 1, 'records' => []];
        }

        $decoded = json_decode(file_get_contents($this->storagePath()), true);

        return $decoded ?? ['next_id' => 1, 'records' => []];
    }

    private function writeStore(array $store): void
    {
        $fp = fopen($this->storagePath(), 'c+');
        flock($fp, LOCK_EX);
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, json_encode($store, JSON_PRETTY_PRINT));
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    private function allRecords(): \Illuminate\Support\Collection
    {
        return collect($this->readStore()['records'] ?? []);
    }

    private function findRecord(int $id): ?array
    {
        return $this->allRecords()->firstWhere('id', $id);
    }

    private function insertRecord(array $data): array
    {
        $store = $this->readStore();

        $data['id']         = $store['next_id'];
        $data['created_at'] = now()->toDateTimeString();
        $data['updated_at'] = null;

        $store['records'][] = $data;
        $store['next_id']++;

        $this->writeStore($store);

        return $data;
    }

    private function updateRecord(int $id, array $updates): bool
    {
        $store = $this->readStore();
        $found = false;

        foreach ($store['records'] as &$record) {
            if ((int) $record['id'] === $id) {
                $record               = array_merge($record, $updates);
                $record['updated_at'] = now()->toDateTimeString();
                $found                = true;
                break;
            }
        }

        if ($found) {
            $this->writeStore($store);
        }

        return $found;
    }

    // ─── Index ────────────────────────────────────────────────────────────────────

    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $allRecords    = $this->allRecords();
        $assignedCount = $allRecords->count();
        $activeCount   = $allRecords->where('has_expired', false)->count();
        $expiredCount  = $allRecords->where('has_expired', true)->count();

        try {
            $totalCount = $this->applyUserScope($this->buildQuery())->count();
        } catch (\Exception $e) {
            $totalCount = 0;
        }

        $unassignedCount = max(0, $totalCount - $assignedCount);

        return view('pages.asset-management.index', compact(
            'totalCount', 'unassignedCount', 'activeCount', 'expiredCount'
        ));
    }

    // ─── DataTable JSON ───────────────────────────────────────────────────────────

    public function json(Request $request)
    {
        $query = $this->buildQuery();
        $query = $this->applyUserScope($query);
        $query = $this->applyFilters($query, $request);

        $assets = $this->allRecords()->keyBy('compound_id');

        return DataTables::of($query)
            ->filter(function ($query) use ($request) {
                $search = $request->input('search.value');
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('receiptnbr', 'ilike', "%{$search}%")
                          ->orWhere('ponbr', 'ilike', "%{$search}%")
                          ->orWhere('vendorname', 'ilike', "%{$search}%")
                          ->orWhere('inventoryid', 'ilike', "%{$search}%")
                          ->orWhere('inventory_descr', 'ilike', "%{$search}%");
                    });
                }
            }, true)
            ->addColumn('receipt_date_fmt', fn($row) =>
                $row->receiptdate ? Carbon::parse($row->receiptdate)->format('d-M-Y') : ''
            )
            ->addColumn('unit_label', fn($row) =>
                (int) $row->unit_num . ' / ' . (int) $row->qty_received
            )
            ->addColumn('unitcost_fmt', fn($row) =>
                $row->unitcost
                    ? number_format((float) $row->unitcost, 0, '.', ',')
                    : '-'
            )
            ->addColumn('assign_info', function ($row) use ($assets) {
                $asset = $assets->get($row->compound_id);
                if (!$asset) return '';

                return implode('<br>', array_filter([
                    '<span class="text-gray-500">' . e($asset['assign_cpny_id']) . '</span>',
                    '<span class="text-gray-500">' . e($asset['assign_department_id']) . '</span>',
                    '<span class="font-medium text-gray-800">' . e($asset['assign_username']) . '</span>',
                ]));
            })
            ->addColumn('warranty_info', function ($row) use ($assets) {
                $asset = $assets->get($row->compound_id);
                if (!$asset) return '';

                $start = !empty($asset['start_date'])
                    ? Carbon::parse($asset['start_date'])->format('d-M-Y')
                    : '—';
                $end = !empty($asset['end_date'])
                    ? Carbon::parse($asset['end_date'])->format('d-M-Y')
                    : '—';

                if ($asset['has_expired']) {
                    return '<span class="px-2 py-0.5 rounded text-xs bg-red-100 text-red-700">Expired</span>'
                        . '<div class="text-xs text-gray-400 mt-0.5">' . $start . ' → ' . $end . '</div>';
                }

                return '<span class="px-2 py-0.5 rounded text-xs bg-green-100 text-green-700">Active</span>'
                    . '<div class="text-xs text-gray-400 mt-0.5">Since ' . $start . '</div>';
            })
            ->addColumn('action', function ($row) use ($assets) {
                $asset = $assets->get($row->compound_id);

                if ($asset) {
                    return '<button type="button"
                        class="edit-btn inline-flex items-center rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-100"
                        data-id="' . (int) $asset['id'] . '">Edit</button>';
                }

                // e() = htmlspecialchars ENT_QUOTES — "→&quot; survives the JSON→JS→innerHTML round-trip
                // because the browser decodes HTML entities when reading DOM attributes.
                $json = e(json_encode([
                    'compound_id'       => (int) $row->compound_id,
                    'receipt_detail_id' => (int) $row->receipt_detail_id,
                    'unit_num'          => (int) $row->unit_num,
                    'qty_received'      => (int) $row->qty_received,
                    'receiptnbr'        => $row->receiptnbr,
                    'budget_cpny_id'    => $row->budget_cpny_id,
                    'ponbr'             => $row->ponbr,
                    'vendorid'          => $row->vendorid,
                    'vendorname'        => $row->vendorname,
                    'inventoryid'       => $row->inventoryid,
                    'inventory_descr'   => $row->inventory_descr,
                ]));

                return '<button type="button"
                    class="assign-btn inline-flex items-center rounded-lg bg-gray-900 px-3 py-1.5 text-xs font-medium text-white hover:bg-gray-700"
                    data-receipt="' . $json . '">Assign</button>';
            })
            ->rawColumns(['assign_info', 'warranty_info', 'action'])
            ->make(true);
    }

    // ─── Dropdown AJAX ────────────────────────────────────────────────────────────

    public function companies()
    {
        $user    = Auth::user();
        $cpnyIds = Usercpny::where('username', $user->username)->pluck('cpny_id');

        $companies = MsCompany::whereIn('cpny_id', $cpnyIds)
            ->where('status', 'A')
            ->orderBy('cpny_name')
            ->get(['cpny_id', 'cpny_name']);

        return response()->json($companies);
    }

    public function departments(Request $request)
    {
        $query = MsDepartment::where('status', 'A')->orderBy('department_name');

        if ($request->cpny_id) {
            $deptIds = User::where('cpny_id', $request->cpny_id)
                ->where('status', 'A')
                ->pluck('department_id')
                ->unique();
            $query->whereIn('department_id', $deptIds);
        }

        return response()->json($query->get(['department_id', 'department_name']));
    }

    public function users(Request $request)
    {
        $query = User::where('status', 'A')->orderBy('name');

        if ($request->cpny_id) {
            $query->where('cpny_id', $request->cpny_id);
        }

        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        return response()->json($query->get(['username', 'name', 'npk']));
    }

    public function inventories()
    {
        $cpnyIds = Usercpny::where('username', Auth::user()->username)->pluck('cpny_id');

        $items = DB::connection('pgsql')
            ->table('tr_receipt_detail as sttbdt')
            ->leftJoin('tr_receipt as sttb', function ($join) {
                $join->on('sttbdt.receiptnbr', '=', 'sttb.receiptnbr')
                     ->on('sttbdt.budget_cpny_id', '=', 'sttb.cpny_id');
            })
            ->select('sttbdt.inventoryid', 'sttbdt.inventory_descr')
            ->where('sttbdt.inventory_category', 'ilike', '%KOMPUTER%')
            ->where('sttb.status', 'C')
            ->whereIn('sttb.cpny_id', $cpnyIds)
            ->distinct()
            ->orderBy('sttbdt.inventoryid')
            ->get();

        return response()->json($items);
    }

    // ─── Store (new assignment) ────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'compound_id'          => 'required|integer',
            'receipt_detail_id'    => 'required|integer',
            'unit_num'             => 'required|integer',
            'receiptnbr'           => 'required|string|max:50',
            'budget_cpny_id'       => 'nullable|string|max:20',
            'ponbr'                => 'nullable|string|max:50',
            'vendorid'             => 'nullable|string|max:50',
            'vendorname'           => 'nullable|string|max:255',
            'inventoryid'          => 'nullable|string|max:100',
            'inventory_descr'      => 'nullable|string|max:255',
            'assign_cpny_id'       => 'required|string|max:20',
            'assign_department_id' => 'required|string|max:50',
            'assign_username'      => 'required|string|max:100',
            'start_date'           => 'required|date',
            'has_expired'          => 'nullable',
            'end_date'             => 'nullable|date|after_or_equal:start_date',
            'serial_number'        => 'nullable|string|max:100',
            'notes'                => 'nullable|string',
        ]);

        $hasExpired = $request->boolean('has_expired');

        $existing = $this->allRecords()->firstWhere('compound_id', (int) $validated['compound_id']);
        if ($existing) {
            return response()->json(['message' => 'This item is already assigned.'], 422);
        }

        $this->insertRecord([
            'compound_id'          => (int) $validated['compound_id'],
            'receipt_detail_id'    => (int) $validated['receipt_detail_id'],
            'unit_num'             => (int) $validated['unit_num'],
            'receiptnbr'           => $validated['receiptnbr'],
            'budget_cpny_id'       => $validated['budget_cpny_id'] ?? null,
            'ponbr'                => $validated['ponbr'] ?? null,
            'vendorid'             => $validated['vendorid'] ?? null,
            'vendorname'           => $validated['vendorname'] ?? null,
            'inventoryid'          => $validated['inventoryid'] ?? null,
            'inventory_descr'      => $validated['inventory_descr'] ?? null,
            'assign_cpny_id'       => $validated['assign_cpny_id'],
            'assign_department_id' => $validated['assign_department_id'],
            'assign_username'      => $validated['assign_username'],
            'start_date'           => $validated['start_date'],
            'end_date'             => $hasExpired ? ($validated['end_date'] ?? null) : null,
            'has_expired'          => $hasExpired,
            'serial_number'        => $validated['serial_number'] ?? null,
            'notes'                => $validated['notes'] ?? null,
            'created_by'           => Auth::user()->username,
            'updated_by'           => null,
        ]);

        return response()->json(['message' => 'Asset assigned successfully.']);
    }

    // ─── Show (for edit modal) ────────────────────────────────────────────────────

    public function show(int $id)
    {
        $record = $this->findRecord($id);

        if (!$record) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        return response()->json($record);
    }

    // ─── Update ───────────────────────────────────────────────────────────────────

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'assign_cpny_id'       => 'required|string|max:20',
            'assign_department_id' => 'required|string|max:50',
            'assign_username'      => 'required|string|max:100',
            'start_date'           => 'required|date',
            'has_expired'          => 'nullable',
            'end_date'             => 'nullable|date|after_or_equal:start_date',
            'serial_number'        => 'nullable|string|max:100',
            'notes'                => 'nullable|string',
        ]);

        $hasExpired = $request->boolean('has_expired');

        $found = $this->updateRecord($id, [
            'assign_cpny_id'       => $validated['assign_cpny_id'],
            'assign_department_id' => $validated['assign_department_id'],
            'assign_username'      => $validated['assign_username'],
            'start_date'           => $validated['start_date'],
            'end_date'             => $hasExpired ? ($validated['end_date'] ?? null) : null,
            'has_expired'          => $hasExpired,
            'serial_number'        => $validated['serial_number'] ?? null,
            'notes'                => $validated['notes'] ?? null,
            'updated_by'           => Auth::user()->username,
        ]);

        if (!$found) {
            return response()->json(['message' => 'Record not found.'], 404);
        }

        return response()->json(['message' => 'Asset updated successfully.']);
    }

    // ─── Private helpers ──────────────────────────────────────────────────────────

    private function buildQuery()
    {
        // Each receipt_detail row is expanded by qty_received via generate_series,
        // so that 1 row = 1 unit = 1 potential user assignment.
        // compound_id is a unique bigint per expanded row: receipt_detail_id * 10000 + unit_num.
        $sql = "
            SELECT
                sttbdt.id::bigint * 10000 + gs.unit_num  AS compound_id,
                sttbdt.id                                 AS receipt_detail_id,
                gs.unit_num,
                sttbdt.receiptnbr,
                sttbdt.budget_cpny_id,
                sttb.cpny_id,
                sttb.receiptdate,
                sttb.ponbr,
                sttb.vendorid,
                sttb.vendorname,
                sttbdt.inventoryid,
                sttbdt.inventory_descr,
                sttbdt.inventory_category,
                sttbdt.qty_received,
                sttbdt.uom,
                (
                    SELECT podt.unitcost
                    FROM   tr_po_detail podt
                    WHERE  podt.ponbr       = sttb.ponbr
                      AND  podt.inventoryid = sttbdt.inventoryid
                    ORDER  BY podt.id
                    LIMIT  1
                ) AS unitcost
            FROM tr_receipt_detail sttbdt
            LEFT JOIN tr_receipt sttb
                ON  sttbdt.receiptnbr     = sttb.receiptnbr
                AND sttbdt.budget_cpny_id = sttb.cpny_id
            CROSS JOIN LATERAL generate_series(
                1, GREATEST(COALESCE(sttbdt.qty_received::int, 1), 1)
            ) AS gs(unit_num)
            WHERE sttbdt.inventory_category ILIKE '%KOMPUTER%'
              AND sttb.status = 'C'
        ";

        return DB::connection('pgsql')
            ->table(DB::raw("($sql) AS receipt_expanded"));
    }

    private function applyUserScope($query)
    {
        $cpnyIds = Usercpny::where('username', Auth::user()->username)->pluck('cpny_id');

        return $query->whereIn('cpny_id', $cpnyIds);
    }

    private function applyFilters($query, Request $request)
    {
        if ($request->filter_inventory) {
            $query->where('inventoryid', $request->filter_inventory);
        }

        $status = $request->filter_status;
        if ($status === 'unassigned') {
            $ids = $this->allRecords()->pluck('compound_id')->filter()->values()->toArray();
            if (!empty($ids)) {
                $query->whereNotIn('compound_id', $ids);
            }
        } elseif ($status === 'active') {
            $ids = $this->allRecords()->where('has_expired', false)->pluck('compound_id')->filter()->values()->toArray();
            $query->whereIn('compound_id', empty($ids) ? [0] : $ids);
        } elseif ($status === 'expired') {
            $ids = $this->allRecords()->where('has_expired', true)->pluck('compound_id')->filter()->values()->toArray();
            $query->whereIn('compound_id', empty($ids) ? [0] : $ids);
        }

        return $query;
    }
}
