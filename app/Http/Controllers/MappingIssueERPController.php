<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Models\StagingIfcaIcStkIssue;

class MappingIssueERPController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        return view('pages.budgets.mapping_issue_erp');
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

        $status = strtoupper(trim((string) $req->query('status', '')));
        $search = trim((string) $req->query('search', ''));

        $sub = StagingIfcaIcStkIssue::query()
            ->selectRaw('MAX(id) AS id')
            ->groupBy('cpny_id', 'issue_id');

        $q = StagingIfcaIcStkIssue::query()->whereIn('id', $sub);

        if (in_array($status, ['D', 'P', 'C'], true)) {
            $q->where('status', $status);
        }

        if ($search !== '') {
            $q->where(function ($w) use ($search) {
                $w->where('cpny_id', 'ilike', "%{$search}%")
                    ->orWhere('issue_id', 'ilike', "%{$search}%")
                    ->orWhere('reference_no', 'ilike', "%{$search}%")
                    ->orWhere('spb_id', 'ilike', "%{$search}%")
                    ->orWhere('wo_id', 'ilike', "%{$search}%")
                    ->orWhere('department_id', 'ilike', "%{$search}%")
                    ->orWhere('user_peminta', 'ilike', "%{$search}%")
                    ->orWhere('issuehd_descs', 'ilike', "%{$search}%");
            });
        }

        $rows = $q->orderByDesc('id')->limit(500)->get([
            'id',
            'cpny_id',
            'issue_id',
            'issue_date',
            'reference_no',
            'spb_id',
            'wo_id',
            'department_id',
            'user_peminta',
            'status',
        ]);

        $rows->transform(function ($r) {
            $r->status_label = $this->statusLabel($r->status ?? null);
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

        return response()->json([
            'success' => true,
            'data' => [
                ['integration_type' => 'IFCA'],
                ['integration_type' => 'SOLOMON'],
            ],
        ]);
    }

    public function showMapping(int $id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $row = StagingIfcaIcStkIssue::query()->findOrFail($id);

        $cpny    = (string) $row->cpny_id;
        $issueId = (string) $row->issue_id;

        $details = StagingIfcaIcStkIssue::query()
            ->where('cpny_id', $cpny)
            ->where('issue_id', $issueId)
            ->orderBy('line_no', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $header = [
            'cpny_id'          => $row->cpny_id,
            'issue_id'         => $row->issue_id,
            'issue_date'       => $row->issue_date,
            'issuehd_descs'    => $row->issuehd_descs,
            'reference_no'     => $row->reference_no,
            'spb_id'           => $row->spb_id,
            'wo_id'            => $row->wo_id,
            'department_id'    => $row->department_id,
            'user_peminta'     => $row->user_peminta,
            'keeper'           => $row->keeper,
            'status'           => $row->status,
            'status_label'     => $this->statusLabel($row->status ?? null),
            'reviewed_note'    => $row->reviewed_note,
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
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $rep = StagingIfcaIcStkIssue::query()->findOrFail($id);

        $cpny    = (string) $rep->cpny_id;
        $issueId = (string) $rep->issue_id;

        $data = $req->validate([
            'status'            => ['required', 'in:D,P,C'],
            'reviewed_note'     => ['nullable', 'string', 'max:500'],
            'integration_type'  => ['nullable', 'in:IFCA,SOLOMON'],

            'lines' => ['required', 'array', 'min:1'],
            'lines.*.id' => ['required', 'integer'],

            'lines.*.entity_cd'   => ['nullable', 'string', 'max:50'],
            'lines.*.ic_location' => ['nullable', 'string', 'max:50'],
            'lines.*.trx_cd'      => ['nullable', 'string', 'max:50'],
            'lines.*.div_cd'      => ['nullable', 'string', 'max:50'],
            'lines.*.dept_cd'     => ['nullable', 'string', 'max:50'],

            'lines.*.solomon_reason_cd'        => ['nullable', 'string', 'max:50'],
            'lines.*.solomon_acct_cd'          => ['nullable', 'string', 'max:50'],
            'lines.*.solomon_allocation_cd'    => ['nullable', 'string', 'max:50'],
            'lines.*.solomon_subaccount_dept'  => ['nullable', 'string', 'max:50'],
        ]);

        $groupRows = StagingIfcaIcStkIssue::query()
            ->where('cpny_id', $cpny)
            ->where('issue_id', $issueId)
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

        $integrationType = strtoupper((string) ($data['integration_type'] ?? ''));

        foreach ($data['lines'] as $ln) {
            $rid = (int) $ln['id'];
            if (!isset($groupRows[$rid])) continue;

            $row = $groupRows[$rid];

            if ($integrationType === 'IFCA') {
                foreach (['entity_cd', 'ic_location', 'trx_cd', 'div_cd', 'dept_cd'] as $f) {
                    if (array_key_exists($f, $ln)) {
                        $row->{$f} = $ln[$f];
                    }
                }
            } elseif ($integrationType === 'SOLOMON') {
                foreach (['solomon_reason_cd', 'solomon_acct_cd', 'solomon_allocation_cd', 'solomon_subaccount_dept'] as $f) {
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
            'message' => 'Mapping issue berhasil diupdate.',
        ]);
    }
}