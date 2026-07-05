<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SysUserRole;
use App\Models\TrMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class SendCommentController extends Controller
{
    private function hasCostCtrlAccess(string $username): bool
    {
        return SysUserRole::where('username', $username)
            ->where('role_id', 'COSTCTRLACCESS')
            ->exists();
    }

    public function sendmsg(int $id, string $doctype, Request $request)
    {
        // $user = $request->user();   // ambil user yg login
        $user = Auth::user();
        $username = $user ? $user->username : 'system';


        TrMessage::create([
            'refnbr'        => $request->doc_no ?? $request->docid ?? (string)$id,          // menyesuaikan dengan nama field baru
            'doctype'       => $doctype,
            'message_date'  => Carbon::now(),
            'cpny_id'        => $user->cpnyid ?? null,     // jika user memiliki cpnyid
            'department_id' => $user->departementid ?? null, // jika user memiliki departementid
            'username'      => $user->username,
            'name'          => $user->name,
            'message'       => $request->reason,
            'status'        => 'A',
            'created_by'    => $user->username,
            'updated_by'    => $user->username,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message successfully saved!'
        ]);
    }

    public function fetchComments(string $doctype, $id)
    {
        $comments = TrMessage::where('doctype', $doctype)
            ->where('refnbr', $id)
            ->where(function ($q) {
                $q->whereNull('message_type')
                    ->orWhere('message_type', '!=', 'Private');
            })
            ->orderByDesc('message_date')
            ->get();

        return response()->json([
            'status'   => 'success',
            'comments' => $comments,
        ]);
    }

    // POST /comments/{doctype}/{id}
    public function storeComment(Request $request, string $doctype, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:500',
        ]);

        $user = $request->user();

        $comment = TrMessage::create([
            'refnbr'        => $id,
            'doctype'       => $doctype,
            'message_date'  => Carbon::now(),
            'message_type'  => 'Public',
            'cpny_id'        => $user->cpnyid ?? null,
            'department_id' => $user->departementid ?? null,
            'username'      => $user->username ?? ($user->email ?? 'system'),
            'name'          => $user->name ?? $user->username ?? 'System',
            'message'       => $request->comment,
            'status'        => 'A',
            'created_by'    => $user->username ?? ($user->email ?? 'system'),
            'updated_by'    => $user->username ?? ($user->email ?? 'system'),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Comment added successfully!',
            'comment' => $comment,
        ]);
    }

    // GET /private-notes/{doctype}/{id}
    public function fetchPrivateNotes(string $doctype, $id)
    {
        $user = Auth::user();
        $username = $user->username ?? ($user->email ?? null);

        if (!$username || !$this->hasCostCtrlAccess($username)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'You do not have access to private notes.',
            ], 403);
        }

        $notes = TrMessage::where('doctype', $doctype)
            ->where('refnbr', $id)
            ->where('message_type', 'Private')
            ->orderByDesc('message_date')
            ->get();

        return response()->json([
            'status' => 'success',
            'notes'  => $notes,
        ]);
    }

    // GET /private-notes-counts/{doctype}
    public function countPrivateNotes(Request $request, string $doctype)
    {
        $user = Auth::user();
        $username = $user->username ?? ($user->email ?? null);

        if (!$username || !$this->hasCostCtrlAccess($username)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'You do not have access to private notes.',
            ], 403);
        }

        $refnbrs = array_values(array_filter(array_map('trim', explode(',', (string) $request->query('refnbrs', '')))));

        if (empty($refnbrs)) {
            return response()->json([
                'status' => 'success',
                'counts' => (object) [],
            ]);
        }

        $counts = TrMessage::where('doctype', $doctype)
            ->where('message_type', 'Private')
            ->whereIn('refnbr', $refnbrs)
            ->selectRaw('refnbr, count(*) as total')
            ->groupBy('refnbr')
            ->pluck('total', 'refnbr');

        return response()->json([
            'status' => 'success',
            'counts' => $counts,
        ]);
    }

    // POST /private-notes/{doctype}/{id}
    public function storePrivateNote(Request $request, string $doctype, $id)
    {
        $request->validate([
            'note' => 'required|string|max:500',
        ]);

        $user = $request->user();
        $username = $user->username ?? ($user->email ?? null);

        if (!$username || !$this->hasCostCtrlAccess($username)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'You do not have access to private notes.',
            ], 403);
        }

        $note = TrMessage::create([
            'refnbr'        => $id,
            'doctype'       => $doctype,
            'message_date'  => Carbon::now(),
            'message_type'  => 'Private',
            'cpny_id'        => $user->cpnyid ?? null,
            'department_id' => $user->departementid ?? null,
            'username'      => $username,
            'name'          => $user->name ?? $username,
            'message'       => $request->note,
            'status'        => 'A',
            'created_by'    => $username,
            'updated_by'    => $username,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Private note added successfully!',
            'note'    => $note,
        ]);
    }
}
