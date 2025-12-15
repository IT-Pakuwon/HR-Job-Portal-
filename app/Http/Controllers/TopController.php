<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Models\MsTop;
use App\Models\MsTopdetail;

class TopController extends Controller
{
    public function index()
    {
        return view('pages.top.index');
    }

    /* =========================
     *  TOP (HEADER)
     * ========================= */
    public function topJson()
    {
        $tops = MsTop::select([
                'id',
                'topid',
                'top_name',
                'top_type',
                'top_days',
                'is_rfca',
                'is_fastapprove',
                'status',
            ])
            ->orderByDesc('id')
            ->get();

        return response()->json(['data' => $tops]);
    }

    public function storeTop(Request $request)
    {
        $request->validate([
            'topid'          => 'required|string|max:50|unique:pgsql.ms_top,topid',
            'top_name'       => 'required|string|max:200',
            'top_type'       => 'required|string|max:50',
            'top_days'       => 'required|integer|min:0|max:3650',
            'is_rfca'        => 'nullable|boolean',
            'is_fastapprove' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $loginUser = Auth::user();

            $top = MsTop::create([
                'topid'          => strtoupper($request->topid),
                'top_name'       => strtoupper($request->top_name),
                'top_type'       => strtoupper($request->top_type),
                'top_days'       => (int) $request->top_days,
                'is_rfca'        => $request->boolean('is_rfca'),
                'is_fastapprove' => $request->boolean('is_fastapprove'),
                'status'         => 'A',
                'created_by'     => $loginUser->username ?? 'system',
                'created_at'     => now(),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'top' => $top]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Gagal menyimpan TOP',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function editTop($id)
    {
        $top = MsTop::findOrFail($id);

        return response()->json([
            'id'            => $top->id,
            'topid'         => $top->topid,
            'top_name'      => $top->top_name,
            'top_type'      => $top->top_type,
            'top_days'      => $top->top_days,
            'is_rfca'       => (bool) $top->is_rfca,
            'is_fastapprove'=> (bool) $top->is_fastapprove,
            'status'        => $top->status,
        ]);
    }

    public function updateTop(Request $request, $id)
    {
        $top = MsTop::findOrFail($id);

        $request->validate([
            'topid'          => 'required|string|max:50|unique:pgsql.ms_top,topid,' . $top->id,
            'top_name'       => 'required|string|max:200',
            'top_type'       => 'required|string|max:50',
            'top_days'       => 'required|integer|min:0|max:3650',
            'is_rfca'        => 'nullable|boolean',
            'is_fastapprove' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $loginUser = Auth::user();

            $top->update([
                'topid'          => strtoupper($request->topid),
                'top_name'       => strtoupper($request->top_name),
                'top_type'       => strtoupper($request->top_type),
                'top_days'       => (int) $request->top_days,
                'is_rfca'        => $request->boolean('is_rfca'),
                'is_fastapprove' => $request->boolean('is_fastapprove'),
                'updated_by'     => $loginUser->username ?? 'system',
                'updated_at'     => now(),
            ]);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Gagal update TOP',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function toggleTopStatus($id)
    {
        $top = MsTop::findOrFail($id);
        $newStatus = request('status'); // A / X

        $top->update([
            'status' => $newStatus,
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Status updated']);
    }

    /* =========================
     *  TOP DETAIL
     * ========================= */
    public function topDetailJson(Request $request)
    {
        $q = MsTopdetail::select([
                'id',
                'terms_id',
                'topid',
                'top_type',
                'terms_name',
                'order_term',
                'payment_pct',
                'progress_pct',
                'terms_type',
                'flag_bast',
                'status',
            ])
            ->orderBy('order_term');

        if ($request->filled('topid')) {
            $q->where('topid', $request->topid);
        }

        return response()->json(['data' => $q->get()]);
    }

    public function storeTopDetail(Request $request)
    {
        $request->validate([
            'terms_id'     => 'required|string|max:50|unique:pgsql.ms_top_detail,terms_id',
            'topid'        => 'required|string|max:50',
            'top_type'     => 'required|string|max:50',
            'terms_name'   => 'required|string|max:200',
            'order_term'   => 'required|integer|min:1|max:999',
            'payment_pct'  => 'required|numeric|min:0|max:100',
            'progress_pct' => 'nullable|numeric|min:0|max:100',
            'terms_type'   => 'nullable|string|max:50',
            'flag_bast'    => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $loginUser = Auth::user();

            $detail = MsTopdetail::create([
                'terms_id'     => strtoupper($request->terms_id),
                'topid'        => $request->topid,
                'top_type'     => strtoupper($request->top_type),
                'terms_name'   => strtoupper($request->terms_name),
                'order_term'   => (int) $request->order_term,
                'payment_pct'  => (float) $request->payment_pct,
                'progress_pct' => $request->filled('progress_pct') ? (float) $request->progress_pct : null,
                'terms_type'   => $request->terms_type ? strtoupper($request->terms_type) : null,
                'flag_bast'    => $request->boolean('flag_bast'),
                'status'       => 'A',
                'created_by'   => $loginUser->username ?? 'system',
                'created_at'   => now(),
            ]);

            DB::commit();
            return response()->json(['success' => true, 'detail' => $detail]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Gagal menyimpan TOP Detail',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function editTopDetail($id)
    {
        $d = MsTopdetail::findOrFail($id);

        return response()->json([
            'id'           => $d->id,
            'terms_id'     => $d->terms_id,
            'topid'        => $d->topid,
            'top_type'     => $d->top_type,
            'terms_name'   => $d->terms_name,
            'order_term'   => $d->order_term,
            'payment_pct'  => $d->payment_pct,
            'progress_pct' => $d->progress_pct,
            'terms_type'   => $d->terms_type,
            'flag_bast'    => (bool) $d->flag_bast,
            'status'       => $d->status,
        ]);
    }

    public function updateTopDetail(Request $request, $id)
    {
        $d = MsTopdetail::findOrFail($id);

        $request->validate([
            'terms_id'     => 'required|string|max:50|unique:pgsql.ms_top_detail,terms_id,' . $d->id,
            'topid'        => 'required|string|max:50',
            'top_type'     => 'required|string|max:50',
            'terms_name'   => 'required|string|max:200',
            'order_term'   => 'required|integer|min:1|max:999',
            'payment_pct'  => 'required|numeric|min:0|max:100',
            'progress_pct' => 'nullable|numeric|min:0|max:100',
            'terms_type'   => 'nullable|string|max:50',
            'flag_bast'    => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $loginUser = Auth::user();

            $d->update([
                'terms_id'     => strtoupper($request->terms_id),
                'topid'        => $request->topid,
                'top_type'     => strtoupper($request->top_type),
                'terms_name'   => strtoupper($request->terms_name),
                'order_term'   => (int) $request->order_term,
                'payment_pct'  => (float) $request->payment_pct,
                'progress_pct' => $request->filled('progress_pct') ? (float) $request->progress_pct : null,
                'terms_type'   => $request->terms_type ? strtoupper($request->terms_type) : null,
                'flag_bast'    => $request->boolean('flag_bast'),
                'updated_by'   => $loginUser->username ?? 'system',
                'updated_at'   => now(),
            ]);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Gagal update TOP Detail',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function toggleTopDetailStatus($id)
    {
        $d = MsTopdetail::findOrFail($id);
        $newStatus = request('status');

        $d->update([
            'status' => $newStatus,
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Status updated']);
    }
}
