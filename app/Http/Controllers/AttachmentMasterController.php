<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\UploadsVplAttachment;
use App\Models\Attachment;
use App\Models\MsCompany;
use App\Models\TrAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AttachmentMasterController extends Controller
{
    use UploadsVplAttachment;

    // Legacy `attachment` (mysql2) rows merged into this list: VPL (VP*) and Project Task (TSK*) only.
    private function legacyScope($query)
    {
        return $query->where(function ($w) {
            $w->where('docid', 'like', 'VP%')->orWhere('docid', 'like', 'TSK%');
        });
    }

    public function index()
    {
        $companies = MsCompany::query()
            ->select('cpny_id', 'cpny_name')
            ->where('status', 'A')
            ->whereNull('deleted_at')
            ->orderBy('cpny_name')
            ->get();

        // DISTINCT over the whole table is slow, so cache the list briefly.
        $doctypes = Cache::remember('attachments-master.doctypes', 600, fn () => TrAttachment::query()
            ->whereNotNull('doctype')
            ->where('doctype', '<>', '')
            ->distinct()
            ->orderBy('doctype')
            ->pluck('doctype')
            ->all());

        $legacyDoctypes = Cache::remember('attachments-master.legacy-doctypes', 600, fn () => $this->legacyScope(Attachment::query())
            ->selectRaw('distinct substring(docid, 1, 3) as dt')
            ->pluck('dt')
            ->all());

        $doctypes = collect($doctypes)->merge($legacyDoctypes)->unique()->sort()->values()->all();

        return view('pages.attachmentsmaster.attachmentsmaster', compact('companies', 'doctypes'));
    }

    public function json(Request $request)
    {
        $cpnyId = (string) $request->get('cpny_id', '');
        $doctype = trim((string) $request->get('doctype', ''));
        $q = trim((string) $request->get('q', ''));
        $dtSearch = trim((string) data_get($request->get('search'), 'value', ''));
        $terms = array_values(array_filter([$q, $dtSearch], fn ($t) => $t !== ''));

        $dir = data_get($request->get('order'), '0.dir') === 'asc' ? 'asc' : 'desc';
        $colIdx = (int) data_get($request->get('order'), '0.column', -1);

        // ---- New: tr_attachment (pgsql2) ----
        $query = TrAttachment::query()
            ->select([
                'id', 'refnbr', 'doctype', 'attachment_date', 'cpny_id', 'department_id',
                'attachment_name', 'folder', 'filename', 'filesize', 'extention', 'status',
            ]);

        if ($cpnyId !== '') {
            $query->where('cpny_id', $cpnyId);
        }

        if ($doctype !== '') {
            $query->where('doctype', $doctype);
        }

        foreach ($terms as $term) {
            $query->where(function ($w) use ($term) {
                $w->where('refnbr', 'ilike', '%'.$term.'%')
                    ->orWhere('doctype', 'ilike', '%'.$term.'%')
                    ->orWhere('attachment_name', 'ilike', '%'.$term.'%')
                    ->orWhere('filename', 'ilike', '%'.$term.'%')
                    ->orWhere('folder', 'ilike', '%'.$term.'%')
                    ->orWhere('department_id', 'ilike', '%'.$term.'%')
                    ->orWhere('extention', 'ilike', '%'.$term.'%');
            });
        }

        // ---- Legacy: attachment (mysql2), VPL + TSK only. No company on this table. ----
        $legacy = $this->legacyScope(Attachment::query())
            ->select(['id', 'docid', 'name', 'attachfile', 'extention', 'status', 'created_at']);

        $legacyEnabled = $cpnyId === '';
        if ($doctype !== '') {
            $legacy->where('docid', 'like', $doctype.'%');
        }
        foreach ($terms as $term) {
            $legacy->where(function ($w) use ($term) {
                $w->where('docid', 'like', '%'.$term.'%')
                    ->orWhere('name', 'like', '%'.$term.'%')
                    ->orWhere('attachfile', 'like', '%'.$term.'%')
                    ->orWhere('extention', 'like', '%'.$term.'%');
            });
        }

        $totalNew = (clone $query)->count();
        $totalLegacy = $legacyEnabled ? (clone $legacy)->count() : 0;
        $total = $totalNew + $totalLegacy;

        // DataTables column index => DB column (0/1 are not orderable). Ordering applies within each source.
        $orderableNew = [
            2 => 'refnbr', 3 => 'doctype', 4 => 'attachment_date', 5 => 'cpny_id',
            6 => 'department_id', 7 => 'attachment_name', 8 => 'folder', 9 => 'filename',
            10 => 'filesize', 11 => 'extention', 12 => 'status',
        ];
        $orderableLegacy = [
            2 => 'docid', 3 => 'docid', 4 => 'created_at', 7 => 'name', 8 => 'attachfile',
            9 => 'attachfile', 11 => 'extention', 12 => 'status',
        ];

        if ($col = ($orderableNew[$colIdx] ?? null)) {
            $query->orderBy($col, $dir)->orderByDesc('id');
        } else {
            $query->orderByDesc('id');
        }
        if ($col = ($orderableLegacy[$colIdx] ?? null)) {
            $legacy->orderBy($col, $dir)->orderByDesc('id');
        } else {
            $legacy->orderByDesc('id');
        }

        $start = max(0, (int) $request->get('start', 0));
        $length = (int) $request->get('length', 10);
        $length = $length < 0 ? 50000 : min($length, 50000); // -1 = export all

        // New rows first, then legacy rows.
        $newRows = collect();
        if ($start < $totalNew) {
            $take = min($length, $totalNew - $start);
            $newRows = $query->offset($start)->limit($take)->get()->map(fn ($row) => [
                'id' => $row->id,
                'source' => 'new',
                'refnbr' => $row->refnbr,
                'doctype' => $row->doctype,
                'attachment_date' => $row->attachment_date ? date('Y-m-d', strtotime($row->attachment_date)) : null,
                'cpny_id' => $row->cpny_id,
                'department_id' => $row->department_id,
                'attachment_name' => $row->attachment_name,
                'folder' => $row->folder,
                'filename' => $row->filename,
                'filesize' => $row->filesize,
                'extention' => $row->extention,
                'status' => $row->status,
                'can_open' => false,
            ]);
        }

        $legacyRows = collect();
        $remaining = $length - $newRows->count();
        if ($legacyEnabled && $remaining > 0) {
            $legacyRows = $legacy->offset(max(0, $start - $totalNew))->limit($remaining)->get()
                ->map(function ($row) {
                    $file = (string) $row->attachfile;
                    $inGcs = str_contains($file, '/');

                    return [
                        'id' => $row->id,
                        'source' => 'legacy',
                        'refnbr' => $row->docid,
                        'doctype' => substr((string) $row->docid, 0, 3),
                        'attachment_date' => $row->created_at ? date('Y-m-d', strtotime($row->created_at)) : null,
                        'cpny_id' => null,
                        'department_id' => null,
                        'attachment_name' => $row->name,
                        'folder' => $inGcs ? dirname($file) : null,
                        'filename' => $inGcs ? basename($file) : $file,
                        'filesize' => null,
                        'extention' => $row->extention,
                        'status' => $row->status,
                        'can_open' => $inGcs, // GCS-backed (VPL); local-disk files have no viewer
                    ];
                });
        }

        return response()->json([
            'draw' => (int) $request->get('draw', 0),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $newRows->concat($legacyRows)->values(),
        ]);
    }

    public function toggleStatus(Request $request, $id)
    {
        $loginUser = Auth::user();

        if ($request->get('source') === 'legacy') {
            $row = $this->legacyScope(Attachment::query())->findOrFail($id);
            $row->status = $request->status;
            $row->save();
        } else {
            $row = TrAttachment::findOrFail($id);
            $row->update([
                'status' => $request->status,
                'updated_by' => $loginUser->username ?? 'system',
            ]);
        }

        return response()->json(['message' => 'Status updated']);
    }

    public function viewLegacy(int $id)
    {
        $attach = $this->legacyScope(Attachment::query())->where('id', $id)->firstOrFail();
        abort_unless(str_contains((string) $attach->attachfile, '/'), 404);

        return redirect()->away($this->vplAttachmentBucket()->object($attach->attachfile)->signedUrl(
            now()->addMinutes(15),
            ['version' => 'v4']
        ));
    }

    public function delete($id)
    {
        $attachment = TrAttachment::findOrFail($id);

        // Build file path
        $filePath = public_path($attachment->folder.'/'.$attachment->filename);

        // Delete physical file if exists
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Delete database record
        $attachment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Attachment deleted successfully.',
        ]);
    }
}
