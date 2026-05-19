<?php

namespace App\Http\Controllers;

use App\Models\MsCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class VoucherTaxiSetupController extends Controller
{
    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return view('pages.vouchertaxi.setup');
    }

    public function jsonCategory(Request $request)
    {
        $query = MsCategory::query()
            ->where('doctype', 'VCR')
            ->orderByDesc('id');

        return DataTables::of($query)

            ->addIndexColumn()

            ->editColumn('status', function ($row) {

                return $row->status == 'A'
                    ? '
                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                            Active
                        </span>
                    '
                    : '
                        <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                            Inactive
                        </span>
                    ';
            })

            ->addColumn('action', function ($row) {

                $checked = $row->status == 'A'
                    ? 'checked'
                    : '';

                return '
                    <div class="flex items-center justify-end gap-3">

                        <button
                            type="button"
                            onclick="editCategory(' . $row->id . ')"
                            class="rounded-lg bg-blue-100 px-3 py-1.5 text-xs font-medium text-blue-700 transition hover:bg-blue-200">

                            Edit

                        </button>

                        <label class="relative inline-flex cursor-pointer items-center">

                            <input
                                type="checkbox"
                                class="peer sr-only"
                                ' . $checked . '
                                onchange="updateCategoryStatus(' . $row->id . ', this.checked ? \'A\' : \'X\', this)">

                            <div class="peer h-6 w-11 rounded-full bg-gray-300 transition
                                after:absolute after:left-[2px]
                                after:top-[2px]
                                after:h-5 after:w-5
                                after:rounded-full
                                after:bg-white
                                after:transition-all
                                after:content-[\'\']
                                peer-checked:bg-emerald-500
                                peer-checked:after:translate-x-full">
                            </div>

                        </label>

                    </div>
                ';
            })

            ->rawColumns([
                'status',
                'action'
            ])

            ->make(true);
    }

    public function findCategory($id)
    {
        $category = MsCategory::query()
            ->where('doctype', 'VCR')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $category,
        ]);
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'categoryid'    => 'required|string|max:50',
            'category_name' => 'required|string|max:255',
            'groups'        => 'nullable|string|max:100',
        ]);

        DB::connection('pgsql2')->beginTransaction();

        try {

            MsCategory::create([
                'doctype'       => 'VCR',
                'categoryid'    => strtoupper($request->categoryid),
                'category_name' => strtoupper($request->category_name),
                'groups'        => strtoupper($request->groups),
                'username'      => Auth::user()->username ?? Auth::user()->name,
                'status'        => 'A',
                'created_by'    => Auth::user()->username ?? Auth::user()->name,
                'updated_by'    => Auth::user()->username ?? Auth::user()->name,
            ]);

            DB::connection('pgsql2')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Category successfully created.',
            ]);
        } catch (\Throwable $th) {

            DB::connection('pgsql2')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function updateCategory(Request $request, $id)
    {
        $request->validate([
            'categoryid'    => 'required|string|max:50',
            'category_name' => 'required|string|max:255',
            'groups'        => 'nullable|string|max:100',
        ]);

        DB::connection('pgsql2')->beginTransaction();

        try {

            $category = MsCategory::query()
                ->where('doctype', 'VCR')
                ->findOrFail($id);

            $category->update([
                'categoryid'    => strtoupper($request->categoryid),
                'category_name' => strtoupper($request->category_name),
                'groups'        => strtoupper($request->groups),
                'updated_by'    => Auth::user()->username ?? Auth::user()->name,
            ]);

            DB::connection('pgsql2')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Category successfully updated.',
            ]);
        } catch (\Throwable $th) {

            DB::connection('pgsql2')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function updateCategoryStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:A,X',
        ]);

        DB::connection('pgsql2')->beginTransaction();

        try {

            $category = MsCategory::query()
                ->where('doctype', 'VCR')
                ->findOrFail($id);

            $category->update([
                'status'     => $request->status,
                'updated_by' => Auth::user()->username ?? Auth::user()->name,
            ]);

            DB::connection('pgsql2')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Category status successfully updated.',
            ]);
        } catch (\Throwable $th) {

            DB::connection('pgsql2')->rollBack();

            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
