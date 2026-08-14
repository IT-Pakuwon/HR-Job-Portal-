<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SysUserRole;
use App\Models\TrApproval;
use App\Models\TrMessage;
use App\Models\User;
use App\Services\DocumentNotificationService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SendCommentController extends Controller
{
    // Doctypes where GAACCESS users (not just COSTCTRLACCESS) can use private notes,
    // scoped to documents they're actually on the approval line for.
    private const GA_NOTE_DOCTYPES = ['VCR', 'BCR'];

    private function hasCostCtrlAccess(string $username): bool
    {
        return SysUserRole::where('username', $username)
            ->where('role_id', 'COSTCTRLACCESS')
            ->exists();
    }

    // Whether this user has private-note capability for this doctype at all
    // (regardless of which specific document), used to gate the UI/endpoints.
    private function canUsePrivateNotesForDoctype(?User $user, string $doctype): bool
    {
        $username = $user->username ?? ($user->email ?? null);
        if (!$username) {
            return false;
        }

        return $this->hasCostCtrlAccess($username)
            || (in_array($doctype, self::GA_NOTE_DOCTYPES, true) && $user->hasRole('GAACCESS'));
    }

    // Whether this user can see/post private notes on this specific document.
    // COSTCTRLACCESS: any document. GAACCESS: only VCR/BCR docs they're on the approval line for.
    private function hasPrivateNoteAccess(?User $user, string $doctype, $refnbr): bool
    {
        $username = $user->username ?? ($user->email ?? null);
        if (!$username) {
            return false;
        }

        if ($this->hasCostCtrlAccess($username)) {
            return true;
        }

        if (in_array($doctype, self::GA_NOTE_DOCTYPES, true) && $user->hasRole('GAACCESS')) {
            return DocumentNotificationService::isOnApprovalLine($doctype, $refnbr, $username);
        }

        return false;
    }

    // Subset of $refnbrs this user may see private-note counts for.
    private function privateNoteAllowedRefnbrs(?User $user, string $doctype, array $refnbrs): Collection
    {
        $username = $user->username ?? ($user->email ?? null);
        if (!$username || empty($refnbrs)) {
            return collect();
        }

        if ($this->hasCostCtrlAccess($username)) {
            return collect($refnbrs);
        }

        if (in_array($doctype, self::GA_NOTE_DOCTYPES, true) && $user->hasRole('GAACCESS')) {
            return DocumentNotificationService::approvalLineRefnbrs($doctype, $refnbrs, $username);
        }

        return collect();
    }

    // Generic @mention-audience resolver for the "creator + approval line (+ optional PIC)"
    // modules (SPPB/SPPJ/SPPK/SPPT, IMBudget, CS, WO, BAST, Item Request). Ticket/ACR/ITR keep
    // their own dedicated mentionableUsers() endpoints and are not served by this route.
    public function mentionableUsers(string $doctype, $id)
    {
        $config = DocumentNotificationService::extendedDocTypeConfig()[$doctype] ?? null;

        abort_if(!$config, 404);

        $doc = $config['model']::where($config['idCol'], $id)->firstOrFail();

        $creatorUsernames = collect($config['creatorFields'])->map(fn ($f) => $doc->{$f} ?? null);

        $approvalUsernames = !empty($config['approvalDoctype'])
            ? DocumentNotificationService::splitApproverUsernames(
                TrApproval::where('refnbr', $id)
                    ->where('aprv_doctype', $config['approvalDoctype'])
                    ->pluck('aprv_username')
            )
            : collect();

        $picUsernames = isset($config['picField']) ? collect([$doc->{$config['picField']} ?? null]) : collect();

        $roleUsernames = !empty($config['roleIds'])
            ? DocumentNotificationService::resolveRoleUsernamesForCompany($config['roleIds'], $doc->{$config['companyCol'] ?? 'cpny_id'} ?? null)
            : collect();

        $usernames = $creatorUsernames->merge($approvalUsernames)->merge($picUsernames)->merge($roleUsernames)
            ->filter()
            ->map(fn ($u) => strtolower(trim($u)))
            ->unique()
            ->reject(fn ($u) => $u === strtolower(Auth::user()->username));

        $users = User::query()
            ->whereIn(DB::raw('lower(username)'), $usernames->all())
            ->get(['username', 'name'])
            ->values();

        return response()->json($users);
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

    /**
     * Simpan pesan dengan company scope untuk dokumen yang nomor referensinya
     * dapat sama antar-company.
     */
    public function sendmsgWithCpnyid(
        int $id,
        string $doctype,
        string $cpnyId,
        Request $request
    ) {
        $user = Auth::user();
        $username = $user->username ?? 'system';

        $comment = TrMessage::create([
            'refnbr' => $request->doc_no ?? $request->docid ?? (string) $id,
            'doctype' => $doctype,
            'message_date' => Carbon::now(),
            'cpny_id' => strtoupper(trim($cpnyId)),
            'username' => $username,
            'name' => $user->name ?? $username,
            'message' => $request->reason,
            'status' => 'A',
            'created_by' => $username,
            'updated_by' => $username,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message successfully saved!',
            'comment' => $comment,
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

        if (!$this->hasPrivateNoteAccess($user, $doctype, $id)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'You do not have access to private notes.',
            ], 403);
        }

        $notes = TrMessage::where('doctype', $doctype)
            ->where('refnbr', $id)
            ->where('message_type', 'Private')
            ->where('username', $username)
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

        if (!$this->canUsePrivateNotesForDoctype($user, $doctype)) {
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

        // GAACCESS users only get counts for refnbrs they're actually on the approval line for.
        $allowedRefnbrs = $this->privateNoteAllowedRefnbrs($user, $doctype, $refnbrs);

        if ($allowedRefnbrs->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'counts' => (object) [],
            ]);
        }

        $counts = TrMessage::where('doctype', $doctype)
            ->where('message_type', 'Private')
            ->where('username', $username)
            ->whereIn('refnbr', $allowedRefnbrs)
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

        if (!$this->hasPrivateNoteAccess($user, $doctype, $id)) {
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
