<?php

namespace App\Http\Controllers\Vms;

use App\Http\Controllers\Controller;
use App\Models\Vms\Company;
use App\Models\Vms\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CompanyDepartmentController extends Controller
{
    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user() && in_array('admin', $request->user()->roles(), true), 403);
    }

    private function model(string $kind): Company|Department
    {
        abort_unless(in_array($kind, ['company', 'department'], true), 404);

        return $kind === 'company' ? new Company : new Department;
    }

    public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        return response()->view('pages.vms.company-department.index')->header('Cache-Control', 'no-store, private');
    }

    public function json(Request $request, string $kind)
    {
        $this->authorizeAdmin($request);
        $model = $this->model($kind);
        $columns = $kind === 'company' ? ['CpnyID', 'CpnyName', 'NpwpName', 'NpwpNumber', 'finance_address'] : ['devision_id', 'devision_name'];

        return response()->json(['data' => $model->newQuery()->orderBy($model->getKeyName())->get($columns)])->header('Cache-Control', 'no-store, private');
    }

    public function edit(Request $request, string $kind, string $id)
    {
        $this->authorizeAdmin($request);
        $model = $this->model($kind);
        $columns = $kind === 'company' ? ['CpnyID', 'CpnyName', 'NpwpName', 'NpwpNumber', 'finance_address'] : ['devision_id', 'devision_name'];

        return response()->json($model->newQuery()->select($columns)->findOrFail($id))->header('Cache-Control', 'no-store, private');
    }

    private function validated(Request $request, string $kind, bool $creating): array
    {
        $company = $kind === 'company';
        $key = $company ? 'CpnyID' : 'devision_id';
        $rules = $company ? [
            'CpnyName' => ['required', 'string', 'max:255'],
            'NpwpName' => ['nullable', 'string', 'max:255'],
            'NpwpNumber' => ['nullable', 'string', 'max:50'],
            'finance_address' => ['nullable', 'string', 'max:255'],
        ] : ['devision_name' => ['required', 'string', 'max:225']];
        if ($creating) {
            $rules[$key] = ['required', 'string', 'max:'.($company ? 10 : 20), 'regex:/^[A-Za-z0-9_-]+$/', Rule::unique('mysql7.'.($company ? 'company' : 'master_devision'), $key)];
        }
        $data = $request->validate($rules);
        if ($company) {
            foreach (['NpwpName', 'NpwpNumber', 'finance_address'] as $field) {
                $data[$field] ??= null;
            }
        }

        return $data;
    }

    private function audit(Request $request, string $kind, string $id, string $action): void
    {
        Log::notice('VMS company department master updated', ['kind' => $kind, 'record_id' => $id, 'action' => $action, 'actor' => $request->user()->username, 'ip' => $request->ip()]);
    }

    public function store(Request $request, string $kind)
    {
        $this->authorizeAdmin($request);
        $model = $this->model($kind);
        $data = $this->validated($request, $kind, true);
        // Existing VMS tables have mandatory fields without database defaults.
        $data += $kind === 'company' ? ['Addr1' => ''] : ['cerated_at' => now()];
        DB::connection('mysql7')->transaction(fn () => $model->fill($data)->save());
        $this->audit($request, $kind, $model->getKey(), 'created');

        return response()->json(['message' => 'Data berhasil ditambahkan.'], 201);
    }

    public function update(Request $request, string $kind, string $id)
    {
        $this->authorizeAdmin($request);
        $model = $this->model($kind);
        $data = $this->validated($request, $kind, false);
        DB::connection('mysql7')->transaction(function () use ($model, $id, $data) {
            $model->newQuery()->whereKey($id)->lockForUpdate()->firstOrFail()->fill($data)->save();
        });
        $this->audit($request, $kind, $id, 'updated');

        return response()->json(['message' => 'Data berhasil diperbarui.']);
    }
}
