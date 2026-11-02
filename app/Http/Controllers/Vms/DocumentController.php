<?php

namespace App\Http\Controllers\Vms;

use App\Http\Controllers\Controller;
use App\Models\Vms\Document;
use App\Models\Vms\InvoiceDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user() && in_array('admin', $request->user()->roles(), true), 403);
    }

    private function model(string $kind): Document|InvoiceDocument
    {
        abort_unless(in_array($kind, ['vendor', 'invoice'], true), 404);

        return $kind === 'vendor' ? new Document : new InvoiceDocument;
    }

    private function audit(Request $request, string $kind, int $id, string $action): void
    {
        Log::notice('VMS document master updated', ['action' => $action, 'kind' => $kind, 'document_id' => $id, 'actor' => $request->user()->username, 'ip' => $request->ip()]);
    }

    public function index(Request $request)
    {
        $this->authorizeAdmin($request);
        $departments = DB::connection('mysql7')->table('master_devision')->orderBy('devision_name')->get(['devision_id', 'devision_name']);

        return response()->view('pages.vms.document.index', compact('departments'))->header('Cache-Control', 'no-store, private');
    }

    public function json(Request $request, string $kind)
    {
        $this->authorizeAdmin($request);
        $model = $this->model($kind);
        $rows = $model->newQuery()->orderByDesc($model->getKeyName())->get();

        return response()->json(['data' => $rows])->header('Cache-Control', 'no-store, private');
    }

    public function edit(Request $request, string $kind, int $id)
    {
        $this->authorizeAdmin($request);

        return response()->json($this->model($kind)->newQuery()->findOrFail($id))->header('Cache-Control', 'no-store, private');
    }

    private function validated(Request $request, string $kind, bool $creating): array
    {
        $rules = [
            'document_name' => ['required', 'string', 'max:255'],
        ];
        if ($kind === 'vendor') {
            $rules += [
                'document_remark' => ['nullable', 'string', 'max:10000'],
                'devision_id' => ['required', 'string', 'max:20', Rule::exists('mysql7.master_devision', 'devision_id')],
                'document_order' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
                'has_expire_date' => ['required', Rule::in(['yes', 'no'])],
                'status' => ['required', 'integer', Rule::in([0, 1])],
                'has_conditional' => ['required', Rule::in(['Y', 'N'])],
                'conditional_date' => ['nullable', 'required_if:has_conditional,Y', 'date_format:Y-m-d'],
                'multiple_files' => ['required', Rule::in(['Y', 'N'])],
                'related_privy' => ['required', Rule::in(['Y', 'N'])],
            ];
        } else {
            $rules += [
                'Module' => ['required', Rule::in(['Purchase Material', 'Purchase Service', 'Kontrak'])],
                'DivisionID' => ['required', 'string', 'max:50', Rule::exists('mysql7.master_devision', 'devision_id')],
                'Required' => ['required', Rule::in(['Y', 'N'])],
                'scan_imagick' => ['required', Rule::in(['Y', 'N'])],
                'ICDP' => ['nullable', 'string', 'max:5'],
            ];
            if ($creating) {
                $rules['id'] = ['required', 'integer', 'min:1', 'max:2147483647', Rule::unique('mysql7.ord_masterdoc', 'id')];
            }
        }
        $data = $request->validate($rules);
        if ($kind === 'vendor') {
            $data['document_remark'] ??= '';
            if ($data['has_conditional'] !== 'Y') {
                $data['conditional_date'] = null;
            }
        } else {
            $data['ICDP'] ??= '';
        }

        return $data;
    }

    public function store(Request $request, string $kind)
    {
        $this->authorizeAdmin($request);
        $model = $this->model($kind);
        $data = $this->validated($request, $kind, true);
        DB::connection('mysql7')->transaction(fn () => $model->fill($data)->save());
        $this->audit($request, $kind, (int) $model->getKey(), 'created');

        return response()->json(['success' => true, 'message' => 'Document berhasil ditambahkan.', 'data' => $model], 201);
    }

    public function update(Request $request, string $kind, int $id)
    {
        $this->authorizeAdmin($request);
        $model = $this->model($kind);
        $data = $this->validated($request, $kind, false);
        DB::connection('mysql7')->transaction(function () use ($model, $id, $data) {
            $model->newQuery()->whereKey($id)->lockForUpdate()->firstOrFail()->fill($data)->save();
        });
        $this->audit($request, $kind, $id, 'updated');

        return response()->json(['success' => true, 'message' => 'Document berhasil diperbarui.']);
    }

    public function toggle(Request $request, string $kind, int $id)
    {
        $this->authorizeAdmin($request);
        $model = $this->model($kind);
        $column = $kind === 'vendor' ? 'status' : 'Required';
        $allowed = $kind === 'vendor' ? [0, 1] : ['Y', 'N'];
        $data = $request->validate([$column => ['required', Rule::in($allowed)], 'expected' => ['required', Rule::in($allowed)]]);
        DB::connection('mysql7')->transaction(function () use ($model, $id, $column, $data) {
            $record = $model->newQuery()->whereKey($id)->lockForUpdate()->firstOrFail();
            abort_unless((string) $record->{$column} === (string) $data['expected'], 409, 'Data sudah berubah. Muat ulang daftar lalu coba kembali.');
            $record->setAttribute($column, $data[$column])->save();
        });
        $this->audit($request, $kind, $id, $kind === 'vendor' ? 'status.changed' : 'required.changed');

        return response()->json(['success' => true, 'message' => 'Pengaturan document berhasil diperbarui.']);
    }
}
