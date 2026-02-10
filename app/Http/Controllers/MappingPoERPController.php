<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\StagingIfcaPoApprove;

class MappingPoERPController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        return view('pages.budgets.mapping_po_erp');
    }

    public function json(Request $req)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['data'=>[]], 401);

        $status = strtoupper(trim((string) $req->query('status', ''))); // D/P/C atau kosong

        $q = StagingIfcaPoApprove::query();

        if (in_array($status, ['D','P','C'], true)) {
            $q->where('status', $status);
        }

        // optional search sederhana (order_no, supplier_cd, remark)
        $search = trim((string) $req->query('search', ''));
        if ($search !== '') {
            $q->where(function($w) use ($search){
                $w->where('order_no', 'ilike', "%{$search}%")
                  ->orWhere('supplier_cd', 'ilike', "%{$search}%")
                  ->orWhere('remark', 'ilike', "%{$search}%")
                  ->orWhere('ref_no_cs', 'ilike', "%{$search}%")
                  ->orWhere('ref_no_spbjkt', 'ilike', "%{$search}%");
            });
        }

        $rows = $q->orderByDesc('id')->limit(500)->get(); // limit supaya aman

        // tambah label status untuk frontend
        $rows->transform(function ($r) {
            $st = strtoupper((string) ($r->status ?? ''));

            switch ($st) {
                case 'D':
                    $label = 'Waiting Review';
                    break;
                case 'P':
                    $label = 'Review';
                    break;
                case 'C':
                    $label = 'Completed';
                    break;
                default:
                    $label = $st !== '' ? $st : '-';
                    break;
            }

            $r->status_label = $label;
            return $r;
        });


        return response()->json(['data' => $rows]);
    }

    public function showMapping(int $id)
    {
        $row = StagingIfcaPoApprove::findOrFail($id);

        $st = strtoupper((string) ($row->status ?? ''));

        switch ($st) {
            case 'D':
                $label = 'Waiting Review';
                break;
            case 'P':
                $label = 'Review';
                break;
            case 'C':
                $label = 'Completed';
                break;
            default:
                $label = $st !== '' ? $st : '-';
                break;
        }

        $row->status_label = $label;

        return response()->json(['success' => true, 'data' => $row]);
    }

    public function updateMapping(Request $req, int $id)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['success'=>false,'message'=>'Unauthenticated'], 401);

        $row = StagingIfcaPoApprove::findOrFail($id);

        $data = $req->validate([
            'status'        => ['required','in:D,P,C'],
            'process_note'  => ['nullable','string','max:500'],
        ]);

        // update only
        $row->status = $data['status'];
        $row->process_note = $data['process_note'] ?? $row->process_note;

        // tracking reviewer
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
