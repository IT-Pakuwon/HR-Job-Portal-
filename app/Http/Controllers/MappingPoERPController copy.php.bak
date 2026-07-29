<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $st = strtoupper(trim((string)$status));
        switch ($st) {
            case 'D': return 'Waiting Review';
            case 'P': return 'Review';
            case 'C': return 'Completed';
            default:  return $st !== '' ? $st : '-';
        }
    }

    public function json(Request $req)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['data'=>[]], 401);

        $status = strtoupper(trim((string) $req->query('status', ''))); // D/P/C atau kosong
        $search = trim((string) $req->query('search', ''));

        $q = StagingIfcaPoApprove::query();

        if (in_array($status, ['D','P','C'], true)) {
            $q->where('status', $status);
        }

        if ($search !== '') {
            $q->where(function($w) use ($search){
                $w->where('order_no', 'ilike', "%{$search}%")
                  ->orWhere('supplier_cd', 'ilike', "%{$search}%")
                  ->orWhere('remark', 'ilike', "%{$search}%")
                  ->orWhere('ref_no_cs', 'ilike', "%{$search}%")
                  ->orWhere('ref_no_sppbjkt', 'ilike', "%{$search}%")
                  ->orWhere('cpny_id', 'ilike', "%{$search}%");
            });
        }

        $rows = $q->orderByDesc('id')->limit(500)->get();

        // ===== lookup vendor_name dari ms_vendor (pgsql) =====
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

    public function showMapping(int $id)
    {
        $row = StagingIfcaPoApprove::findOrFail($id);

        $row->status_label = $this->statusLabel($row->status ?? null);

        $vid = trim((string)($row->supplier_cd ?? ''));
        $row->vendor_name = $vid !== ''
            ? MsVendor::query()->where('vendor_id', $vid)->value('vendor_name')
            : null;

        return response()->json(['success' => true, 'data' => $row]);
    }

    public function updateMapping(Request $req, int $id)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['success'=>false,'message'=>'Unauthenticated'], 401);

        $row = StagingIfcaPoApprove::findOrFail($id);

        // ✅ editable fields (bagian bawah modal)
        $data = $req->validate([
            'status'   => ['required','in:D,P,C'],
            'process_note' => ['nullable','string','max:500'],

            // IFCA editable
            'entity_cd'   => ['nullable','string','max:50'],
            'location_cd' => ['nullable','string','max:50'],
            'acct_cd'     => ['nullable','string','max:50'],
            'div_cd'      => ['nullable','string','max:50'],
            'dept_cd'     => ['nullable','string','max:50'],

            // SOLOMON editable
            'solomon_acct_cd'        => ['nullable','string','max:50'],
            'solomon_allocation_cd'  => ['nullable','string','max:50'],
            'solomon_subaccount_dept'=> ['nullable','string','max:50'],
        ]);

        $row->status = $data['status'];
        $row->process_note = $data['process_note'] ?? $row->process_note;

        // set editable mapping fields (kalau tidak ada di request, biarkan yang lama)
        foreach ([
            'entity_cd','location_cd','acct_cd','div_cd','dept_cd',
            'solomon_acct_cd','solomon_allocation_cd','solomon_subaccount_dept',
        ] as $f) {
            if (array_key_exists($f, $data)) {
                $row->{$f} = $data[$f];
            }
        }

        $row->reviewed_by = $user->username ?? ($user->name ?? 'system');
        $row->reviewed_at = Carbon::now();

        $row->updated_by = $user->username ?? ($user->name ?? 'system');
        $row->updated_at = Carbon::now();

        $row->save();

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diupdate.',
        ]);
    }
}
