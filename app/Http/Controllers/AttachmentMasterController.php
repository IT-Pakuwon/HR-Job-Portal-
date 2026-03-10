<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TrAttachment;
use App\Models\MsCompany;

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

        return view('pages.attachmentsmaster.attachmentsmaster', compact('companies'));
    }

    public function json(Request $request)
    {
        $cpnyId  = $request->get('cpny_id');
        $doctype = $request->get('doctype');
        $search  = trim((string) $request->get('search', ''));

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
            $query->where('doctype', 'ilike', '%' . $doctype . '%');
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('refnbr', 'ilike', '%' . $search . '%')
                    ->orWhere('doctype', 'ilike', '%' . $search . '%')
                    ->orWhere('attachment_name', 'ilike', '%' . $search . '%')
                    ->orWhere('filename', 'ilike', '%' . $search . '%')
                    ->orWhere('folder', 'ilike', '%' . $search . '%')
                    ->orWhere('department_id', 'ilike', '%' . $search . '%')
                    ->orWhere('extention', 'ilike', '%' . $search . '%');
            });
        }

        $rows = $query
            ->orderByDesc('id')
            ->get()
            ->map(function ($row) {
                return [
                    'id'              => $row->id,
                    'refnbr'          => $row->refnbr,
                    'doctype'         => $row->doctype,
                    'attachment_date' => $row->attachment_date ? date('Y-m-d', strtotime($row->attachment_date)) : null,
                    'cpny_id'         => $row->cpny_id,
                    'department_id'   => $row->department_id,
                    'attachment_name' => $row->attachment_name,
                    'folder'          => $row->folder,
                    'filename'        => $row->filename,
                    'filesize'        => $row->filesize,
                    'extention'       => $row->extention,
                    'status'          => $row->status,
                ];
            });

        return response()->json(['data' => $rows]);
    }

    public function toggleStatus(Request $request, $id)
    {
        $row = TrAttachment::findOrFail($id);
        $loginUser = Auth::user();

        $row->update([
            'status'     => $request->status,
            'updated_by' => $loginUser->username ?? 'system',
        ]);

        return response()->json(['message' => 'Status updated']);
    }
}