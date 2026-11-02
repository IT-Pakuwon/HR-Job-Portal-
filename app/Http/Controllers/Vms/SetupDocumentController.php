<?php

namespace App\Http\Controllers\Vms;

use App\Http\Controllers\Controller;
use App\Models\Vms\Document;
use App\Models\Vms\SetupDocument;
use App\Models\Vms\VendorBusinessCategory;
use App\Models\Vms\VendorCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SetupDocumentController extends Controller
{
    private function context(Request $request, int $parent, int $business): array
    {
        abort_unless($request->user() && in_array('admin', $request->user()->roles(), true), 403);

        return [VendorCategory::findOrFail($parent), VendorBusinessCategory::where('parent_category', $parent)->findOrFail($business)];
    }

    public function index(Request $request, int $parent, int $business)
    {
        [$parent, $business] = $this->context($request, $parent, $business);

        return response()->view('pages.vms.vendor-category.documents', compact('parent', 'business'))->header('Cache-Control', 'no-store, private');
    }

    public function json(Request $request, int $parent, int $business)
    {
        $this->context($request, $parent, $business);
        $rows = SetupDocument::query()->leftJoin('master_document as d', 'd.document_id', '=', 'setup_document.document_id')->where('category_id', $business)->select('setup_document.*', 'd.document_name')->orderBy('d.document_name')->get();
        $documents = Document::where('status', 1)->whereNotIn('document_id', SetupDocument::where('category_id', $business)->select('document_id'))->orderBy('document_name')->get(['document_id', 'document_name']);

        return response()->json(['data' => $rows, 'documents' => $documents])->header('Cache-Control', 'no-store, private');
    }

    public function store(Request $request, int $parent, int $business)
    {
        $this->context($request, $parent, $business);
        $data = $request->validate(['document_id' => ['required', 'integer', Rule::exists('mysql7.master_document', 'document_id')->where('status', 1)]]);
        DB::connection('mysql7')->transaction(function () use ($business, $data) {
            VendorBusinessCategory::whereKey($business)->lockForUpdate()->firstOrFail();
            abort_if(SetupDocument::where('category_id', $business)->where('document_id', $data['document_id'])->exists(), 422, 'Dokumen sudah terdaftar pada bidang usaha ini.');
            SetupDocument::create($data + ['category_id' => $business, 'is_mandatory' => 1, 'is_published' => 1, 'created_date' => now()]);
        });
        Log::notice('VMS setup document created', ['business_id' => $business, 'document_id' => $data['document_id'], 'actor' => $request->user()->username]);

        return response()->json(['message' => 'Dokumen berhasil ditambahkan.'], 201);
    }

    public function update(Request $request, int $parent, int $business, int $id)
    {
        $this->context($request, $parent, $business);
        $data = $request->validate(['field' => ['required', Rule::in(['is_mandatory', 'is_published'])], 'value' => ['required', 'integer', Rule::in([1, 2])], 'expected' => ['required', 'integer', Rule::in([1, 2])]]);
        DB::connection('mysql7')->transaction(function () use ($business, $id, $data) {
            $row = SetupDocument::where('category_id', $business)->whereKey($id)->lockForUpdate()->firstOrFail();
            abort_unless($row->{$data['field']} === (int) $data['expected'], 409, 'Data sudah berubah. Muat ulang dan coba kembali.');
            $row->{$data['field']} = $data['value'];
            $row->save();
        });
        Log::notice('VMS setup document updated', ['setup_id' => $id, 'business_id' => $business, 'field' => $data['field'], 'value' => $data['value'], 'actor' => $request->user()->username]);

        return response()->json(['message' => 'Pengaturan berhasil diperbarui.']);
    }
}
