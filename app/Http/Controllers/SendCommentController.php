<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TrMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class SendCommentController extends Controller
{
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
}
