<?php

namespace App\Http\Controllers\Vms;

use App\Http\Controllers\Controller;
use App\Models\Vms\Company;
use App\Models\Vms\Department;
use App\Models\Vms\InternalUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private const COLUMNS = ['id', 'name', 'username', 'email', 'role', 'status', 'approver', 'notification', 'api', 'telegram', 'idacum', 'approver_vendor_manual', 'approver_dokumen'];

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user() && in_array('admin', $request->user()->roles(), true), 403);
    }

    private function roles(): array
    {
        return Department::pluck('devision_name')->map(fn ($name) => strtolower($name))->merge(['superadmin', 'user'])->unique()->values()->all();
    }

    public function index(Request $request)
    {
        $this->authorizeAdmin($request);
        $companies = Company::orderBy('CpnyName')->get(['CpnyID', 'CpnyName']);
        $roles = $this->roles();

        return response()->view('pages.vms.users.index', compact('companies', 'roles'))->header('Cache-Control', 'no-store, private');
    }

    public function json(Request $request)
    {
        $this->authorizeAdmin($request);

        return response()->json(['data' => InternalUser::orderByDesc('id')->get(self::COLUMNS)])->header('Cache-Control', 'no-store, private');
    }

    public function edit(Request $request, int $id)
    {
        $this->authorizeAdmin($request);
        $row = InternalUser::select(self::COLUMNS)->findOrFail($id)->toArray();
        $row['companies'] = DB::connection('mysql7')->table('admin_company')->where('UserID', $id)->pluck('CpnyID');

        return response()->json($row)->header('Cache-Control', 'no-store, private');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        $roles = $this->roles();
        if ($id !== null) {
            $roles[] = InternalUser::findOrFail($id)->role;
        }
        $rules = [
            'name' => ['required', 'string', 'max:50'],
            'username' => ['required', 'string', 'max:50', Rule::unique('mysql7.admin', 'username')->ignore($id)],
            'email' => ['required', 'email', 'max:50', Rule::unique('mysql7.admin', 'email')->ignore($id)],
            'role' => ['required', 'string', 'max:20', Rule::in($roles)],
            'status' => ['required', 'integer', Rule::in([0, 1])],
            'password' => [$id === null ? 'required' : 'nullable', 'string', 'min:6', 'max:128', 'confirmed'],
            'telegram' => ['nullable', 'string', 'max:32'],
            'idacum' => ['nullable', 'string', 'max:10'],
            'companies' => ['present', 'array'],
            'companies.*' => ['required', 'string', 'distinct', Rule::exists('mysql7.company', 'CpnyID')],
        ];
        foreach (['approver', 'notification', 'api', 'approver_vendor_manual', 'approver_dokumen'] as $flag) {
            $rules[$flag] = ['required', Rule::in(['Y', 'N'])];
        }
        $data = $request->validate($rules);
        // The shared CI3 admin login still checks MD5 and its password column is varchar(50).
        if (! empty($data['password'])) {
            $data['password'] = md5($data['password']);
        } else {
            unset($data['password']);
        }
        $data['telegram'] ??= null;
        $data['idacum'] ??= null;

        return $data;
    }

    private function saveCompanies(int $id, array $companies): void
    {
        $query = DB::connection('mysql7')->table('admin_company')->where('UserID', $id);
        $query->delete();
        foreach ($companies as $company) {
            DB::connection('mysql7')->table('admin_company')->insert(['UserID' => $id, 'CpnyID' => $company]);
        }
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin($request);
        $data = $this->validated($request);
        $companies = $data['companies'];
        unset($data['companies']);
        $row = DB::connection('mysql7')->transaction(function () use ($data, $companies) {
            $row = InternalUser::create($data);
            $this->saveCompanies($row->id, $companies);

            return $row;
        });
        $this->audit($request, $row->id, 'created');

        return response()->json(['message' => 'User VMS berhasil ditambahkan.'], 201);
    }

    public function update(Request $request, int $id)
    {
        $this->authorizeAdmin($request);
        $data = $this->validated($request, $id);
        $companies = $data['companies'];
        unset($data['companies']);
        DB::connection('mysql7')->transaction(function () use ($id, $data, $companies) {
            InternalUser::whereKey($id)->lockForUpdate()->firstOrFail()->fill($data)->save();
            $this->saveCompanies($id, $companies);
        });
        $this->audit($request, $id, 'updated');

        return response()->json(['message' => 'User VMS berhasil diperbarui.']);
    }

    public function toggle(Request $request, int $id)
    {
        $this->authorizeAdmin($request);
        $data = $request->validate(['status' => ['required', 'integer', Rule::in([0, 1])], 'expected' => ['required', 'integer', Rule::in([0, 1])]]);
        DB::connection('mysql7')->transaction(function () use ($id, $data) {
            $row = InternalUser::whereKey($id)->lockForUpdate()->firstOrFail();
            abort_unless($row->status === (int) $data['expected'], 409, 'Status sudah berubah. Muat ulang dan coba kembali.');
            $row->status = $data['status'];
            $row->save();
        });
        $this->audit($request, $id, 'status.changed');

        return response()->json(['message' => 'Status user berhasil diperbarui.']);
    }

    private function audit(Request $request, int $id, string $action): void
    {
        Log::notice('VMS internal user updated', ['user_id' => $id, 'action' => $action, 'actor' => $request->user()->username]);
    }
}
