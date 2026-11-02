<?php

namespace App\Http\Controllers\Vms;

use App\Http\Controllers\Controller;
use App\Models\Vms\VendorBusinessCategory;
use App\Models\Vms\VendorCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VendorCategoryController extends Controller
{
    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user() && in_array('admin', $request->user()->roles(), true), 403);
    }

    private function model(string $kind): VendorCategory|VendorBusinessCategory
    {
        abort_unless(in_array($kind, ['category', 'business'], true), 404);

        return $kind === 'category' ? new VendorCategory : new VendorBusinessCategory;
    }

    private function audit(Request $request, string $kind, int $id, string $action): void
    {
        Log::notice('VMS vendor category updated', ['action' => $action, 'kind' => $kind, 'category_id' => $id, 'actor' => $request->user()->username, 'ip' => $request->ip()]);
    }

    public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        return response()->view('pages.vms.vendor-category.index', ['parent' => null])->header('Cache-Control', 'no-store, private');
    }

    public function businesses(Request $request, int $parent)
    {
        $this->authorizeAdmin($request);
        $parent = VendorCategory::findOrFail($parent);

        return response()->view('pages.vms.vendor-category.index', compact('parent'))->header('Cache-Control', 'no-store, private');
    }

    public function json(Request $request, string $kind)
    {
        $this->authorizeAdmin($request);
        $model = $this->model($kind);
        $query = $model->newQuery();
        if ($kind === 'business' && $request->filled('parent')) {
            VendorCategory::findOrFail($request->integer('parent'));
            $query->where('parent_category', $request->integer('parent'));
        }
        $rows = $query->orderByDesc($model->getKeyName())->get();
        $parents = VendorCategory::orderBy('parent_category_name')->get(['id', 'parent_category_name', 'status']);
        if ($kind === 'business') {
            $names = $parents->pluck('parent_category_name', 'id');
            $rows->each(fn ($row) => $row->setAttribute('parent_name', $names[$row->parent_category] ?? null));
        }

        return response()->json(['data' => $rows, 'parents' => $parents])->header('Cache-Control', 'no-store, private');
    }

    public function edit(Request $request, string $kind, int $id)
    {
        $this->authorizeAdmin($request);

        return response()->json($this->model($kind)->newQuery()->findOrFail($id))->header('Cache-Control', 'no-store, private');
    }

    private function validated(Request $request, string $kind, ?int $id = null): array
    {
        $name = $kind === 'category' ? 'parent_category_name' : 'category_name';
        $table = $kind === 'category' ? 'vendor_category_parent' : 'vendor_category';
        $key = $kind === 'category' ? 'id' : 'category_id';
        $unique = Rule::unique('mysql7.'.$table, $name);
        if ($id) {
            $unique->ignore($id, $key);
        }
        if ($kind === 'business') {
            $unique->where('parent_category', $request->input('parent_category'));
        }
        $rules = [$name => ['required', 'string', 'max:50', $unique], 'status' => ['required', 'integer', Rule::in([0, 1])]];
        if ($kind === 'category') {
            $rules['ClassID'] = ['nullable', 'string', 'max:10'];
        } else {
            $rules['parent_category'] = ['required', 'integer', Rule::exists('mysql7.vendor_category_parent', 'id')];
        }
        $data = $request->validate($rules);
        if ($kind === 'category') {
            $data['ClassID'] ??= '';
        }

        return $data;
    }

    private function lockParent(array $data, ?VendorBusinessCategory $current = null): void
    {
        $parent = VendorCategory::whereKey($data['parent_category'])->lockForUpdate()->firstOrFail();
        if ($parent->status !== 1 && (! $current || $current->parent_category !== (int) $data['parent_category'])) {
            throw ValidationException::withMessages(['parent_category' => 'Pilih kategori induk yang aktif.']);
        }
    }

    public function store(Request $request, string $kind)
    {
        $this->authorizeAdmin($request);
        $model = $this->model($kind);
        $data = $this->validated($request, $kind);
        DB::connection('mysql7')->transaction(function () use ($model, $kind, $data) {
            if ($kind === 'business') {
                $this->lockParent($data);
            }
            $model->fill($data)->save();
        });
        $this->audit($request, $kind, (int) $model->getKey(), 'created');

        return response()->json(['success' => true, 'message' => 'Data berhasil ditambahkan.', 'data' => $model], 201);
    }

    public function update(Request $request, string $kind, int $id)
    {
        $this->authorizeAdmin($request);
        $model = $this->model($kind);
        $data = $this->validated($request, $kind, $id);
        DB::connection('mysql7')->transaction(function () use ($model, $kind, $data, $id) {
            // Lock parent before child, matching the create order.
            if ($kind === 'business') {
                VendorCategory::whereKey($data['parent_category'])->lockForUpdate()->firstOrFail();
            }
            $record = $model->newQuery()->whereKey($id)->lockForUpdate()->firstOrFail();
            if ($kind === 'business') {
                $this->lockParent($data, $record);
            }
            $record->fill($data)->save();
        });
        $this->audit($request, $kind, $id, 'updated');

        return response()->json(['success' => true, 'message' => 'Data berhasil diperbarui.']);
    }

    public function toggle(Request $request, string $kind, int $id)
    {
        $this->authorizeAdmin($request);
        $model = $this->model($kind);
        $data = $request->validate(['status' => ['required', 'integer', Rule::in([0, 1])], 'expected' => ['required', 'integer', Rule::in([0, 1])]]);
        DB::connection('mysql7')->transaction(function () use ($model, $id, $data) {
            $record = $model->newQuery()->whereKey($id)->lockForUpdate()->firstOrFail();
            abort_unless($record->status === (int) $data['expected'], 409, 'Status sudah berubah. Muat ulang daftar lalu coba kembali.');
            $record->status = $data['status'];
            $record->save();
        });
        $this->audit($request, $kind, $id, 'status.changed');

        return response()->json(['success' => true, 'message' => 'Status berhasil diperbarui.']);
    }
}
