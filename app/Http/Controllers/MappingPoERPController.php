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

        $status = strtoupper(trim((string)$req->query('status', ''))); // D/P/C atau kosong
        $search = trim((string)$req->query('search', ''));

        // 1) subquery: ambil id TERAKHIR per (cpny_id, order_no)
        $sub = StagingIfcaPoApprove::query()
            ->selectRaw('MAX(id) AS id')
            ->groupBy('cpny_id', 'order_no');

        // 2) ambil representative rows
        $q = StagingIfcaPoApprove::query()->whereIn('id', $sub);

        if (in_array($status, ['D','P','C'], true)) {
            $q->where('status', $status);
        }

        if ($search !== '') {
            $q->where(function($w) use ($search){
                $w->where('order_no', 'ilike', "%{$search}%")
                  ->orWhere('supplier_cd', 'ilike', "%{$search}%")
                  ->orWhere('remark', 'ilike', "%{$search}%")
                  ->orWhere('ref_no_cs', 'ilike', "%{$search}%")
                  ->orWhere('ref_no_spbjkt', 'ilike', "%{$search}%")
                  ->orWhere('cpny_id', 'ilike', "%{$search}%");
            });
        }

        $rows = $q->orderByDesc('id')->limit(500)->get([
            'id','cpny_id','order_no','order_date','supplier_cd',
            'ref_no_spbjkt','ref_no_cs','status'
        ]);

        // vendor map (bulk)
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
        $user = Auth::user();
        if (!$user) return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);

        // representative row
        $row = StagingIfcaPoApprove::query()->findOrFail($id);

        $cpny  = (string)$row->cpny_id;
        $order = (string)$row->order_no;

        // all lines for this PO
        $details = StagingIfcaPoApprove::query()
            ->where('cpny_id', $cpny)
            ->where('order_no', $order)
            ->orderBy('order_line', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // vendor name
        $vid = trim((string)($row->supplier_cd ?? ''));
        $vendorName = $vid !== ''
            ? MsVendor::query()->where('vendor_id', $vid)->value('vendor_name')
            : null;

        // header (dari row representative)
        $header = [
            'cpny_id'        => $row->cpny_id,
            'order_no'       => $row->order_no,
            'order_date'     => $row->order_date,
            'order_type'     => $row->order_type,
            'supplier_cd'    => $row->supplier_cd,
            'vendor_name'    => $vendorName,
            'remark'         => $row->remark,
            'ref_no_spbjkt'  => $row->ref_no_spbjkt,
            'ref_no_cs'      => $row->ref_no_cs,
            'status'         => $row->status,
            'status_label'   => $this->statusLabel($row->status ?? null),
            'process_note'   => $row->process_note,
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
        if (!$user) return response()->json(['success'=>false,'message'=>'Unauthenticated'], 401);

        // representative row untuk tahu group
        $rep = StagingIfcaPoApprove::query()->findOrFail($id);
        $cpny  = (string)$rep->cpny_id;
        $order = (string)$rep->order_no;

        $data = $req->validate([
            'status'       => ['required','in:D,P,C'],
            'process_note' => ['nullable','string','max:500'],

            // lines update (per item)
            'lines' => ['required','array','min:1'],
            'lines.*.id' => ['required','integer'],
            'lines.*.entity_cd'   => ['nullable','string','max:50'],
            'lines.*.location_cd' => ['nullable','string','max:50'],
            'lines.*.acct_cd'     => ['nullable','string','max:50'],
            'lines.*.div_cd'      => ['nullable','string','max:50'],
            'lines.*.dept_cd'     => ['nullable','string','max:50'],

            'lines.*.solomon_acct_cd'        => ['nullable','string','max:50'],
            'lines.*.solomon_allocation_cd'  => ['nullable','string','max:50'],
            'lines.*.solomon_subaccount_dept'=> ['nullable','string','max:50'],
        ]);

        // ambil semua row group sekali
        $groupRows = StagingIfcaPoApprove::query()
            ->where('cpny_id', $cpny)
            ->where('order_no', $order)
            ->get()
            ->keyBy('id');

        // update status/note untuk semua baris group biar konsisten
        $now = Carbon::now();
        foreach ($groupRows as $r) {
            $r->status = $data['status'];
            if (array_key_exists('process_note', $data)) {
                $r->process_note = $data['process_note'];
            }
            $r->reviewed_by = $user->username ?? ($user->name ?? 'system');
            $r->reviewed_at = $now;
            $r->updated_by  = $user->username ?? ($user->name ?? 'system');
            $r->updated_at  = $now;
        }

        // apply mapping per line
        foreach ($data['lines'] as $ln) {
            $rid = (int)$ln['id'];
            if (!isset($groupRows[$rid])) continue; // safety: ignore non-group id

            $row = $groupRows[$rid];
            foreach ([
                'entity_cd','location_cd','acct_cd','div_cd','dept_cd',
                'solomon_acct_cd','solomon_allocation_cd','solomon_subaccount_dept',
            ] as $f) {
                if (array_key_exists($f, $ln)) {
                    $row->{$f} = $ln[$f];
                }
            }
        }

        // save all
        foreach ($groupRows as $r) $r->save();

        return response()->json([
            'success' => true,
            'message' => 'Mapping berhasil diupdate.',
        ]);
    }
}
