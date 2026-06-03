<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasAutonbr;
use App\Models\MsCategory;
use App\Models\TrAttachment;
use App\Models\TrCarExpense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Vinkla\Hashids\Facades\Hashids;

class CarExpenseController extends Controller
{
    use HasAutonbr;

    private function gateGA()
    {
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        if (!$user->hasRole('GAACCESS')) {
            return null;
        }

        return $user;
    }

    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->hasRole('GAACCESS')) {
            abort(403, 'Unauthorized');
        }

        $kendaraan = DB::connection('pgsql')
            ->table('ms_kendaraan')
            ->where('status', 'A')
            ->orderBy('no_polisi')
            ->get(['no_polisi', 'namakendaraan', 'merk_kendaraan', 'typekendaraan']);

        $drivers = DB::connection('pgsql5')
            ->table('ms_driver_opr')
            ->where('status', 'A')
            ->orderBy('drivername')
            ->get(['drivername']);

        $costTypes = MsCategory::query()
            ->where('groups', 'CAR COST')
            ->where('status', 'A')
            ->orderBy('category_name')
            ->get(['id', 'categoryid', 'category_name']);

        return view(
            'pages.carexpense.carexpense',
            compact(
                'kendaraan',
                'drivers',
                'costTypes'
            )
        );
    }

    public function json(Request $request)
    {
        $user = $this->gateGA();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $perPage = 10;
        $page = max(1, (int) $request->input('page', 1));
        $offset = ($page - 1) * $perPage;

        $search    = trim((string) $request->input('search', ''));
        $filter    = trim((string) $request->input('filter', ''));
        $nopol     = trim((string) $request->input('nopol', ''));
        $dateFrom  = trim((string) $request->input('date_from', ''));
        $dateTo    = trim((string) $request->input('date_to', ''));

        $base = TrCarExpense::query()->whereNull('deleted_at');

        if ($filter !== '') {
            $base->where('cost_type', $filter);
        }

        if ($nopol !== '') {
            $base->where('nopol', $nopol);
        }

        if ($dateFrom !== '') {
            $base->whereDate('ref_date', '>=', $dateFrom);
        }

        if ($dateTo !== '') {
            $base->whereDate('ref_date', '<=', $dateTo);
        }

        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('refnbr', 'ilike', "%{$search}%")
                    ->orWhere('nopol', 'ilike', "%{$search}%")
                    ->orWhere('driver', 'ilike', "%{$search}%")
                    ->orWhere('cost_descr', 'ilike', "%{$search}%");
            });
        }

        $total = (clone $base)->count();

        $data = $base
            ->orderBy('refnbr', 'desc')
            ->skip($offset)
            ->take($perPage)
            ->get();

        $costTypeMap = MsCategory::query()
            ->where('groups', 'CAR COST')
            ->where('status', 'A')
            ->pluck('category_name', 'id');

        $data->transform(function ($row) use ($costTypeMap) {
            $row->eid            = Hashids::encode($row->id);
            $row->cost_type_name = $costTypeMap[$row->cost_type] ?? $row->cost_type;
            unset($row->id);

            return $row;
        });

        return response()->json([
            'data' => $data,
            'total' => $total,
            'page' => $page,
        ]);
    }

    public function store(Request $request)
    {
        $user = $this->gateGA();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'ref_date' => ['required', 'date'],
            'nopol' => ['required', 'string'],
            'driver' => ['required', 'string'],
            'cost_type' => ['required', 'string'],
            'cost_descr' => ['required', 'string'],
            'cost_qty' => ['required', 'numeric', 'min:1'],
            'cost_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $costType = MsCategory::query()
            ->where('groups', 'CAR COST')
            ->where('status', 'A')
            ->where('id', $validated['cost_type'])
            ->first();

        if (!$costType) {
            return response()->json([
                'success' => false,
                'message' => 'Cost type tidak valid.',
            ], 422);
        }

        $dt = now();
        $year = (int) $dt->year;
        $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
        $doctype = 'CEX';

        $auto = $this->nextAutonbr($doctype, $year, $month, $user->username, 'Car Expense');
        $urutan = (int) $auto['next'];
        $tglbln = substr((string) $year, 2).$month;
        $refnbr = $doctype.$tglbln.sprintf('%03d', $urutan);

        DB::connection('pgsql')->beginTransaction();

        try {
            $expense = TrCarExpense::create([
                'refnbr' => $refnbr,
                'ref_date' => $validated['ref_date'],
                'nopol' => $validated['nopol'],
                'driver' => $validated['driver'],
                'cost_type' => $costType->id,
                'cost_descr' => $validated['cost_descr'],
                'cost_qty' => $validated['cost_qty'],
                'cost_amount' => $validated['cost_amount'],
                'status' => 'A',
                'created_by' => $user->username,
                'created_at' => $dt,
                'updated_by' => $user->username,
                'updated_at' => $dt,
            ]);

            DB::connection('pgsql')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Car Expense berhasil dibuat.',
                'data' => ['refnbr' => $expense->refnbr, 'eid' => Hashids::encode($expense->id)],
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql')->rollBack();

            Log::error('CarExpense store error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($eid)
    {
        $user = $this->gateGA();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $id = Hashids::decode($eid)[0] ?? null;

        if (!$id) {
            return response()->json(['success' => false, 'message' => 'Invalid reference.'], 404);
        }

        $expense = TrCarExpense::whereNull('deleted_at')->find($id);

        if (!$expense) {
            return response()->json(['success' => false, 'message' => 'Car Expense not found.'], 404);
        }

        $costType = MsCategory::query()
            ->where('groups', 'CAR COST')
            ->where('id', $expense->cost_type)
            ->first(['id', 'category_name']);

        return response()->json([
            'success' => true,
            'data' => [
                'eid' => $eid,
                'refnbr' => $expense->refnbr,
                'ref_date' => $expense->ref_date,
                'nopol' => $expense->nopol,
                'driver' => $expense->driver,
                'cost_type' => $expense->cost_type,
                'cost_type_name' => $costType?->category_name,
                'cost_descr' => $expense->cost_descr,
                'cost_qty' => $expense->cost_qty,
                'cost_amount' => $expense->cost_amount,
                'status' => $expense->status,
                'created_by' => $expense->created_by,
                'created_at' => $expense->created_at,
                'updated_by' => $expense->updated_by,
                'updated_at' => $expense->updated_at,
            ],
        ]);
    }

    public function update(Request $request, $eid)
    {
        $user = $this->gateGA();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $id = Hashids::decode($eid)[0] ?? null;

        if (!$id) {
            return response()->json(['success' => false, 'message' => 'Invalid reference.'], 404);
        }

        $expense = TrCarExpense::whereNull('deleted_at')->find($id);

        if (!$expense) {
            return response()->json(['success' => false, 'message' => 'Car Expense not found.'], 404);
        }

        $validated = $request->validate([
            'ref_date' => ['required', 'date'],
            'nopol' => ['required', 'string'],
            'driver' => ['required', 'string'],
            'cost_type' => ['required', 'string'],
            'cost_descr' => ['required', 'string'],
            'cost_qty' => ['required', 'numeric', 'min:1'],
            'cost_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $costType = MsCategory::query()
            ->where('groups', 'CAR COST')
            ->where('status', 'A')
            ->where('id', $validated['cost_type'])
            ->first();

        if (!$costType) {
            return response()->json([
                'success' => false,
                'message' => 'Cost type tidak valid.',
            ], 422);
        }

        DB::connection('pgsql')->beginTransaction();

        try {
            $expense->ref_date = $validated['ref_date'];
            $expense->nopol = $validated['nopol'];
            $expense->driver = $validated['driver'];
            $expense->cost_type = $costType->id;
            $expense->cost_descr = $validated['cost_descr'];
            $expense->cost_qty = $validated['cost_qty'];
            $expense->cost_amount = $validated['cost_amount'];
            $expense->updated_by = $user->username;
            $expense->updated_at = now();
            $expense->save();

            DB::connection('pgsql')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Car Expense berhasil diupdate.',
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql')->rollBack();

            Log::error('CarExpense update error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($eid)
    {
        $user = $this->gateGA();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $id = Hashids::decode($eid)[0] ?? null;

        if (!$id) {
            return response()->json(['success' => false, 'message' => 'Invalid reference.'], 404);
        }

        $expense = TrCarExpense::whereNull('deleted_at')->find($id);

        if (!$expense) {
            return response()->json(['success' => false, 'message' => 'Car Expense not found.'], 404);
        }

        DB::connection('pgsql')->beginTransaction();

        try {
            $expense->status = 'X';
            $expense->deleted_by = $user->username;
            $expense->deleted_at = now();
            $expense->updated_by = $user->username;
            $expense->updated_at = now();
            $expense->save();

            DB::connection('pgsql')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Car Expense berhasil dihapus.',
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql')->rollBack();

            Log::error('CarExpense destroy error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAttachments($eid)
    {
        $user = $this->gateGA();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $id = Hashids::decode($eid)[0] ?? null;

        if (!$id) {
            return response()->json(['success' => false, 'message' => 'Invalid reference.'], 404);
        }

        $expense = TrCarExpense::whereNull('deleted_at')->find($id);

        if (!$expense) {
            return response()->json(['success' => false, 'message' => 'Car Expense not found.'], 404);
        }

        return app(TrAttachmentController::class)
            ->listAttachments(request(), 'CEX', $expense->refnbr);
    }

    public function uploadAttachment(Request $request, $eid)
    {
        $user = $this->gateGA();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $id = Hashids::decode($eid)[0] ?? null;

        if (!$id) {
            return response()->json(['success' => false, 'message' => 'Invalid reference.'], 404);
        }

        $expense = TrCarExpense::whereNull('deleted_at')->find($id);

        if (!$expense) {
            return response()->json(['success' => false, 'message' => 'Car Expense not found.'], 404);
        }

        $request->validate([
            'attachments'   => ['required', 'array'],
            'attachments.*' => ['file', 'max:5120'],
        ]);

        $meta = [
            'refnbr'        => $expense->refnbr,
            'doctype'       => 'CEX',
            'cpny_id'       => null,
            'department_id' => null,
            'base_folder'   => 'att-car-expense',
            'created_by'    => $user->username,
        ];

        try {
            app(TrAttachmentController::class)
                ->uploadInternal($meta, (array) $request->file('attachments'));

            return response()->json([
                'success' => true,
                'message' => 'Attachment uploaded successfully.',
            ]);
        } catch (\Throwable $e) {
            Log::error('CarExpense uploadAttachment error', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroyAttachment($id)
    {
        $user = $this->gateGA();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return app(TrAttachmentController::class)->deleteAttachment((int) $id);
    }
}
