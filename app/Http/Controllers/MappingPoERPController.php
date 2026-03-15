<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\StagingIfcaPoApprove;
use App\Models\MsVendor;

class MappingPoERPController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        return view('pages.budgets.mapping_po_erp');
    }

    private function statusLabel(?string $status): string
    {
        $st = strtoupper(trim((string) $status));

        switch ($st) {
            case 'D':
                return 'Waiting Review';
            case 'P':
                return 'Review';
            case 'C':
                return 'Completed';
            default:
                return $st !== '' ? $st : '-';
        }
    }

    public function json(Request $req)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['data' => []], 401);

        if (is_string($user->cpny_id)) {
            $cpnyIds = array_map('trim', explode(',', $user->cpny_id));
        } else {
            $cpnyIds = (array) $user->cpny_id;
        }

        $status = strtoupper(trim((string)$req->query('status', '')));
        $search = trim((string)$req->query('search', ''));

        $sub = StagingIfcaPoApprove::query()
            ->selectRaw('MAX(id) AS id')
            ->groupBy('cpny_id', 'order_no');

        // $q = StagingIfcaPoApprove::query()->whereIn('id', $sub);
        $q = StagingIfcaPoApprove::query()
            ->whereIn('id', $sub)
            ->whereIn('cpny_id', $cpnyIds);

        if (in_array($status, ['D', 'P', 'C'], true)) {
            $q->where('status', $status);
        }

        if ($search !== '') {
            $q->where(function ($w) use ($search) {
                $w->where('order_no', 'ilike', "%{$search}%")
                    ->orWhere('supplier_cd', 'ilike', "%{$search}%")
                    ->orWhere('remark', 'ilike', "%{$search}%")
                    ->orWhere('ref_no_cs', 'ilike', "%{$search}%")
                    ->orWhere('ref_no_sppbjkt', 'ilike', "%{$search}%")
                    ->orWhere('cpny_id', 'ilike', "%{$search}%");
            });
        }

        $rows = $q->orderByDesc('id')->limit(500)->get([
            'id',
            'cpny_id',
            'order_no',
            'order_date',
            'supplier_cd',
            'ref_no_sppbjkt',
            'ref_no_cs',
            'status'
        ]);

        $vendorIds = $rows->pluck('supplier_cd')
            ->filter()
            ->map(fn($v) => trim((string)$v))
            ->unique()
            ->values()
            ->all();

        $vendorMap = [];
        if (!empty($vendorIds)) {
            $vendorMap = MsVendor::query()
                ->whereIn('vendor_id', $vendorIds)
                ->pluck('vendor_name', 'vendor_id')
                ->map(fn($n) => (string)$n)
                ->toArray();
        }

        $rows->transform(function ($r) use ($vendorMap) {
            $r->status_label = $this->statusLabel($r->status ?? null);

            $vid = trim((string)($r->supplier_cd ?? ''));
            $r->vendor_name = $vendorMap[$vid] ?? null;

            return $r;
        });

        return response()->json(['data' => $rows]);
    }

    public function integrationTypes()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'data' => []
            ], 401);
        }
        // dd($user->username);        
        $rows = StagingIfcaPoApprove::query()
            ->select('integration_type')
            ->whereNotNull('integration_type')
            // ->where('integration_type', '<>', '')
            ->distinct()
            ->orderBy('integration_type')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rows,
        ]);
    }

    public function showMapping(int $id)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);

        $row = StagingIfcaPoApprove::query()->findOrFail($id);

        $cpny  = (string)$row->cpny_id;
        $order = (string)$row->order_no;

        $details = StagingIfcaPoApprove::query()
            ->where('cpny_id', $cpny)
            ->where('order_no', $order)
            ->orderBy('order_line', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $vid = trim((string)($row->supplier_cd ?? ''));
        $vendorName = $vid !== ''
            ? MsVendor::query()->where('vendor_id', $vid)->value('vendor_name')
            : null;

        $header = [
            'cpny_id'          => $row->cpny_id,
            'order_no'         => $row->order_no,
            'order_date'       => $row->order_date,
            'order_type'       => $row->order_type,
            'supplier_cd'      => $row->supplier_cd,
            'vendor_name'      => $vendorName,
            'remark'           => $row->remark,
            'ref_no_sppbjkt'    => $row->ref_no_sppbjkt,
            'ref_no_cs'        => $row->ref_no_cs,
            'status'           => $row->status,
            'status_label'     => $this->statusLabel($row->status ?? null),
            'reviewed_note'     => $row->reviewed_note,
            'integration_type' => $row->integration_type,
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'header'  => $header,
                'details' => $details,
            ]
        ]);
    }

    public function updateMapping(Request $req, int $id)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);

        $rep = StagingIfcaPoApprove::query()->findOrFail($id);
        $cpny  = (string)$rep->cpny_id;
        $order = (string)$rep->order_no;

        $data = $req->validate([
            'status'            => ['required', 'in:D,P,C'],
            'reviewed_note'      => ['nullable', 'string', 'max:500'],
            'integration_type'  => ['nullable', 'in:IFCA,SOLOMON'],

            'lines' => ['required', 'array', 'min:1'],
            'lines.*.id' => ['required', 'integer'],

            'lines.*.entity_cd'   => ['nullable', 'string', 'max:50'],
            'lines.*.location_cd' => ['nullable', 'string', 'max:50'],
            'lines.*.acct_cd'     => ['nullable', 'string', 'max:50'],
            'lines.*.div_cd'      => ['nullable', 'string', 'max:50'],
            'lines.*.dept_cd'     => ['nullable', 'string', 'max:50'],

            'lines.*.solomon_acct_cd'         => ['nullable', 'string', 'max:50'],
            'lines.*.solomon_allocation_cd'   => ['nullable', 'string', 'max:50'],
            'lines.*.solomon_subaccount_dept' => ['nullable', 'string', 'max:50'],
        ]);

        $groupRows = StagingIfcaPoApprove::query()
            ->where('cpny_id', $cpny)
            ->where('order_no', $order)
            ->get()
            ->keyBy('id');

        $now = Carbon::now();

        foreach ($groupRows as $r) {
            $r->status = $data['status'];

            if (array_key_exists('reviewed_note', $data)) {
                $r->reviewed_note = $data['reviewed_note'];
            }

            if (array_key_exists('integration_type', $data)) {
                $r->integration_type = $data['integration_type'];
            }

            $r->reviewed_by = $user->username ?? ($user->name ?? 'system');
            $r->reviewed_at = $now;
            $r->updated_by  = $user->username ?? ($user->name ?? 'system');
            $r->updated_at  = $now;
        }

        $integrationType = strtoupper((string)($data['integration_type'] ?? ''));

        foreach ($data['lines'] as $ln) {
            $rid = (int)$ln['id'];
            if (!isset($groupRows[$rid])) continue;

            $row = $groupRows[$rid];

            if ($integrationType === 'IFCA') {
                foreach (['entity_cd', 'location_cd', 'acct_cd', 'div_cd', 'dept_cd'] as $f) {
                    if (array_key_exists($f, $ln)) {
                        $row->{$f} = $ln[$f];
                    }
                }
            } elseif ($integrationType === 'SOLOMON') {
                foreach (['solomon_acct_cd', 'solomon_allocation_cd', 'solomon_subaccount_dept'] as $f) {
                    if (array_key_exists($f, $ln)) {
                        $row->{$f} = $ln[$f];
                    }
                }
            }
        }

        foreach ($groupRows as $r) {
            $r->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Mapping berhasil diupdate.',
        ]);
    }
}