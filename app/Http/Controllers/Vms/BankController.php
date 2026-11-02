<?php

namespace App\Http\Controllers\Vms;

use App\Http\Controllers\Controller;
use App\Models\Vms\Bank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class BankController extends Controller
{
    private const COLUMNS = ['bank_id', 'bank_name', 'kode_kliring', 'kode_rtgs', 'swift_kode', 'transfer_fee'];

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user() && in_array('admin', $request->user()->roles(), true), 403);
    }

    public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        return response()->view('pages.vms.bank.index')->header('Cache-Control', 'no-store, private');
    }

    public function json(Request $request)
    {
        $this->authorizeAdmin($request);

        return response()->json(['data' => Bank::orderBy('bank_id')->get(self::COLUMNS)])->header('Cache-Control', 'no-store, private');
    }

    public function edit(Request $request, string $id)
    {
        $this->authorizeAdmin($request);

        return response()->json(Bank::select(self::COLUMNS)->findOrFail($id))->header('Cache-Control', 'no-store, private');
    }

    private function validated(Request $request, ?string $id = null): array
    {
        $rules = ['bank_name' => ['required', 'string', 'max:50'], 'transfer_fee' => ['required', 'integer', 'min:0', 'max:2147483647']];
        foreach (['kode_kliring', 'kode_rtgs', 'swift_kode'] as $field) {
            $rules[$field] = ['nullable', 'string', 'max:20', Rule::unique('mysql7.master_bank', $field)->ignore($id, 'bank_id')];
        }
        if ($id === null) {
            $rules['bank_id'] = ['required', 'string', 'max:4', 'regex:/^[A-Za-z0-9_-]+$/', Rule::unique('mysql7.master_bank', 'bank_id')];
        }
        $data = $request->validate($rules);
        foreach (['kode_kliring', 'kode_rtgs', 'swift_kode'] as $field) {
            $data[$field] ??= '';
        }

        return $data;
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin($request);
        $data = $this->validated($request);
        DB::connection('mysql7')->transaction(fn () => Bank::create($data + ['logo' => '']));
        $this->audit($request, $data['bank_id'], 'created');

        return response()->json(['message' => 'Bank berhasil ditambahkan.'], 201);
    }

    public function update(Request $request, string $id)
    {
        $this->authorizeAdmin($request);
        $data = $this->validated($request, $id);
        DB::connection('mysql7')->transaction(fn () => Bank::whereKey($id)->lockForUpdate()->firstOrFail()->fill($data)->save());
        $this->audit($request, $id, 'updated');

        return response()->json(['message' => 'Bank berhasil diperbarui.']);
    }

    private function audit(Request $request, string $id, string $action): void
    {
        Log::notice('VMS bank updated', ['bank_id' => $id, 'action' => $action, 'actor' => $request->user()->username]);
    }
}
