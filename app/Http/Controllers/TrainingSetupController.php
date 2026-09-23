<?php

namespace App\Http\Controllers;

use App\Models\MsCategory;
use App\Models\MsLndPlaces;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class TrainingSetupController extends Controller
{
    protected const DOCTYPE = 'TE';

    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        return view('pages.master_training.setup');
    }

    // ------------------------------------------------------------------
    // Training Places (ms_lnd_places)
    // ------------------------------------------------------------------

    public function getPlaces()
    {
        $query = MsLndPlaces::query()
            ->orderByDesc('id');

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('status', function ($row) {
                return $row->status == 'A'
                    ? '<span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Active</span>'
                    : '<span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">Inactive</span>';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at
                    ? Carbon::parse($row->created_at)->format('d M Y H:i')
                    : '-';
            })
            ->addColumn('action', function ($row) {
                return '
                    <div class="flex items-center justify-end gap-2">
                        <button type="button" onclick="editPlace(' . $row->id . ')"
                            class="rounded-lg bg-blue-100 px-3 py-1.5 text-xs font-medium text-blue-700 transition hover:bg-blue-200">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button type="button" onclick="deletePlace(' . $row->id . ', \'' . addslashes($row->places_name) . '\')"
                            class="rounded-lg bg-red-100 px-3 py-1.5 text-xs font-medium text-red-700 transition hover:bg-red-200">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function findPlace($id)
    {
        $place = MsLndPlaces::findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $place,
        ]);
    }

    public function storePlace(Request $request)
    {
        $request->validate([
            'places_name'    => 'required|string|max:255',
            'places_address' => 'nullable|string',
            'status'         => 'required|in:A,X',
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {
            $user = Auth::user();
            $createdBy = $user->username ?? 'system';
            $now = now();

            $place = MsLndPlaces::create([
                'places_name'    => trim($request->places_name),
                'places_address' => $request->filled('places_address') ? trim($request->places_address) : null,
                'status'         => $request->status,
                'created_by'     => $createdBy,
                'created_at'     => $now,
            ]);

            $place->update([
                'places_id' => $this->generatePlacesId($place->id),
            ]);

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Training place berhasil disimpan',
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan training place',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function updatePlace(Request $request, $id)
    {
        $place = MsLndPlaces::findOrFail($id);

        $request->validate([
            'places_name'    => 'required|string|max:255',
            'places_address' => 'nullable|string',
            'status'         => 'required|in:A,X',
        ]);

        DB::connection('pgsql5')->beginTransaction();

        try {
            $user = Auth::user();
            $updatedBy = $user->username ?? 'system';

            $place->update([
                'places_name'    => trim($request->places_name),
                'places_address' => $request->filled('places_address') ? trim($request->places_address) : null,
                'status'         => $request->status,
                'updated_by'     => $updatedBy,
                'updated_at'     => now(),
            ]);

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Training place berhasil diupdate',
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal update training place',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function deletePlace($id)
    {
        DB::connection('pgsql5')->beginTransaction();

        try {
            $place = MsLndPlaces::findOrFail($id);
            $user = Auth::user();

            $place->update([
                'deleted_by' => $user->username ?? 'system',
            ]);

            $place->delete();

            DB::connection('pgsql5')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Training place berhasil dihapus',
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql5')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus training place',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ------------------------------------------------------------------
    // Training Categories (ms_category, doctype = 'TE')
    // ------------------------------------------------------------------

    public function getCategories()
    {
        $query = MsCategory::query()
            ->where('doctype', self::DOCTYPE)
            ->orderByDesc('id');

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('status', function ($row) {
                return $row->status == 'A'
                    ? '<span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Active</span>'
                    : '<span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">Inactive</span>';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at
                    ? Carbon::parse($row->created_at)->format('d M Y H:i')
                    : '-';
            })
            ->addColumn('action', function ($row) {
                return '
                    <div class="flex items-center justify-end gap-2">
                        <button type="button" onclick="editCategory(' . $row->id . ')"
                            class="rounded-lg bg-blue-100 px-3 py-1.5 text-xs font-medium text-blue-700 transition hover:bg-blue-200">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button type="button" onclick="deleteCategory(' . $row->id . ', \'' . addslashes($row->category_name) . '\')"
                            class="rounded-lg bg-red-100 px-3 py-1.5 text-xs font-medium text-red-700 transition hover:bg-red-200">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function findCategory($id)
    {
        $category = MsCategory::query()
            ->where('doctype', self::DOCTYPE)
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
            'category_name' => 'required|string|max:200',
            'status'        => 'required|in:A,X',
        ]);

        DB::connection('pgsql2')->beginTransaction();

        try {
            $user = Auth::user();
            $createdBy = $user->username ?? 'system';

            $category = MsCategory::create([
                'doctype'       => self::DOCTYPE,
                'categoryid'    => strtolower(trim($request->categoryid)),
                'category_name' => trim($request->category_name),
                'status'        => $request->status,
                'created_by'    => $createdBy,
                'created_at'    => now(),
            ]);

            DB::connection('pgsql2')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Training category berhasil disimpan',
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql2')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan training category',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function updateCategory(Request $request, $id)
    {
        $category = MsCategory::query()
            ->where('doctype', self::DOCTYPE)
            ->findOrFail($id);

        $request->validate([
            'categoryid'    => 'required|string|max:50',
            'category_name' => 'required|string|max:200',
            'status'        => 'required|in:A,X',
        ]);

        DB::connection('pgsql2')->beginTransaction();

        try {
            $user = Auth::user();
            $updatedBy = $user->username ?? 'system';

            $category->update([
                'categoryid'    => strtolower(trim($request->categoryid)),
                'category_name' => trim($request->category_name),
                'status'        => $request->status,
                'updated_by'    => $updatedBy,
                'updated_at'    => now(),
            ]);

            DB::connection('pgsql2')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Training category berhasil diupdate',
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql2')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal update training category',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteCategory($id)
    {
        $category = MsCategory::query()
            ->where('doctype', self::DOCTYPE)
            ->findOrFail($id);

        DB::connection('pgsql2')->beginTransaction();

        try {
            $user = Auth::user();

            $category->update([
                'deleted_by' => $user->username ?? 'system',
            ]);

            $category->delete();

            DB::connection('pgsql2')->commit();

            return response()->json([
                'success' => true,
                'message' => 'Training category berhasil dihapus',
            ]);
        } catch (\Throwable $e) {
            DB::connection('pgsql2')->rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus training category',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    private function generatePlacesId(int $id): string
    {
        return 'PL' . Carbon::now()->format('y') . '-' . str_pad((string) $id, 5, '0', STR_PAD_LEFT);
    }
}
