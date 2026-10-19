<?php

namespace App\Http\Controllers;

use App\Models\MsCompany;
use App\Models\TrAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AttachmentMasterController extends Controller
{
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

        return view('pages.attachmentsmaster.attachmentsmaster', compact('companies', 'doctypes'));
    }

    public function json(Request $request)
    {
        $cpnyId = (string) $request->get('cpny_id', '');
        $doctype = trim((string) $request->get('doctype', ''));
        $q = trim((string) $request->get('q', ''));
        $dtSearch = trim((string) data_get($request->get('search'), 'value', ''));

        $query = TrAttachment::query()
            ->select([
                'id',
                'refnbr',
                'doctype',
                'attachment_date',
                'cpny_id',
                'department_id',
                'attachment_name',
                'folder',
                'filename',
                'filesize',
                'extention',
                'status',
            ]);

        if ($cpnyId !== '') {
            $query->where('cpny_id', $cpnyId);
        }

        if ($doctype !== '') {
            $query->where('doctype', $doctype);
        }

        foreach (array_filter([$q, $dtSearch], fn ($t) => $t !== '') as $term) {
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

        $total = (clone $query)->count();

        // DataTables column index => DB column (0/1 are not orderable)
        $orderable = [
            2 => 'refnbr', 3 => 'doctype', 4 => 'attachment_date', 5 => 'cpny_id',
            6 => 'department_id', 7 => 'attachment_name', 8 => 'folder', 9 => 'filename',
            10 => 'filesize', 11 => 'extention', 12 => 'status',
        ];
        $orderCol = $orderable[(int) data_get($request->get('order'), '0.column', -1)] ?? null;
        if ($orderCol) {
            $dir = data_get($request->get('order'), '0.dir') === 'asc' ? 'asc' : 'desc';
            $query->orderBy($orderCol, $dir)->orderByDesc('id');
        } else {
            $query->orderByDesc('id');
        }

        $start = max(0, (int) $request->get('start', 0));
        $length = (int) $request->get('length', 10);
        $length = $length < 0 ? 50000 : min($length, 50000); // -1 = export all

        $rows = $query->offset($start)->limit($length)->get()
            ->map(function ($row) {
                return [
                    'id' => $row->id,
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
                ];
            });

        return response()->json([
            'draw' => (int) $request->get('draw', 0),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $rows,
        ]);
    }

    public function toggleStatus(Request $request, $id)
    {
        $row = TrAttachment::findOrFail($id);
        $loginUser = Auth::user();

        $row->update([
            'status' => $request->status,
            'updated_by' => $loginUser->username ?? 'system',
        ]);

        return response()->json(['message' => 'Status updated']);
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
