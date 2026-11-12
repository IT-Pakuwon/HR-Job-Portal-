<?php

namespace App\Http\Controllers;

use App\Models\MsKontrakBQ;
use App\Models\MsKontrakCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class MasterKontrakBqController extends Controller
{
    private function authorizeAccess(Request $request): void
    {
        abort_unless($request->user() && $request->user()->hasRole('PURCHACCESS'), 403);
    }

    public function index(Request $request)
    {
        $this->authorizeAccess($request);

        return view('pages.kontrak.master-bq', [
            'categories' => MsKontrakCategory::whereNull('deleted_at')->orderBy('kontrakcategory')->get(),
            'types' => MsKontrakBQ::whereNull('deleted_at')->whereNotNull('kontrak_bq_type')
                ->distinct()->orderBy('kontrak_bq_type')->pluck('kontrak_bq_type'),
        ]);
    }

    public function json(Request $request)
    {
        $this->authorizeAccess($request);
        $lineNumber = "CASE WHEN BTRIM(kontrak_bq_line_no::text) ~ '^[0-9]+$' THEN BTRIM(kontrak_bq_line_no::text)::numeric END";
        $query = MsKontrakBQ::whereNull('deleted_at')->select([
            'id', 'kontrak_bq_id', 'kontrakcategory', 'kontrak_bq_type', 'kontrak_bq_line_no',
            'kontrak_bq_descr', 'kontrak_bq_uom', 'kontrak_duration_qty', 'status',
        ]);
        foreach (['kontrakcategory', 'kontrak_bq_type'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }
        if (!$request->filled('order')) {
            $query->orderBy('kontrakcategory')->orderByRaw($lineNumber.' ASC NULLS LAST')->orderBy('id');
        }

        return DataTables::eloquent($query)
            ->orderColumn('kontrak_bq_line_no', $lineNumber.' $1 NULLS LAST')
            ->toJson();
    }

    private function rules(): array
    {
        return [
            'kontrak_bq_id' => ['required', 'string', 'max:255'],
            'kontrakcategory' => ['required', 'string', Rule::exists(MsKontrakCategory::class, 'kontrakcategory')->whereNull('deleted_at')],
            'kontrak_bq_type' => ['required', 'string', 'max:255'],
            'kontrak_bq_line_no' => ['required', 'integer', 'min:1'],
            'kontrak_bq_descr' => ['required', 'string', 'max:10000'],
            'kontrak_bq_uom' => ['required', 'string', 'max:50'],
            'kontrak_duration_qty' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['A', 'I'])],
        ];
    }

    public function store(Request $request)
    {
        $this->authorizeAccess($request);
        $data = $request->validate($this->rules());
        $row = MsKontrakBQ::create($data + ['created_by' => $request->user()->username]);

        return response()->json(['message' => 'Kontrak BQ berhasil ditambahkan.', 'data' => $row], 201);
    }

    public function update(Request $request, int $id)
    {
        $this->authorizeAccess($request);
        $row = MsKontrakBQ::whereNull('deleted_at')->findOrFail($id);
        $data = $request->validate($this->rules());
        $row->fill($data);
        $row->updated_by = $request->user()->username;
        $row->save();

        return response()->json(['message' => 'Kontrak BQ berhasil diperbarui.', 'data' => $row]);
    }

    public function categoriesJson(Request $request)
    {
        $this->authorizeAccess($request);
        return DataTables::eloquent(MsKontrakCategory::whereNull('deleted_at')
            ->select(['id', 'kontrakcategory', 'kontrakcategory_descr', 'status']))->toJson();
    }

    private function categoryRules(?MsKontrakCategory $category = null): array
    {
        return [
            'kontrakcategory' => ['required', 'string', 'max:255', $category
                ? Rule::in([$category->kontrakcategory])
                : Rule::unique(MsKontrakCategory::class, 'kontrakcategory')],
            'kontrakcategory_descr' => ['required', 'string', 'max:10000'],
            'status' => ['required', Rule::in(['A', 'I'])],
        ];
    }

    public function storeCategory(Request $request)
    {
        $this->authorizeAccess($request);
        $data = $request->validate($this->categoryRules());
        $row = MsKontrakCategory::create($data + ['created_by' => $request->user()->username]);
        return response()->json(['message' => 'Kategori berhasil ditambahkan.', 'data' => $row], 201);
    }

    public function updateCategory(Request $request, int $id)
    {
        $this->authorizeAccess($request);
        $row = MsKontrakCategory::whereNull('deleted_at')->findOrFail($id);
        // Category codes are referenced by BQ and transaction records; keep them stable.
        $data = $request->validate($this->categoryRules($row));
        $row->fill($data);
        $row->updated_by = $request->user()->username;
        $row->save();
        return response()->json(['message' => 'Kategori berhasil diperbarui.', 'data' => $row]);
    }
}
