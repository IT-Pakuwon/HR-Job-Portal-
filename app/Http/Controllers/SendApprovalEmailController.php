<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PendingApprovalReminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SendApprovalEmailController extends Controller
{
    private function authorizeAccess(Request $request): void
    {
        abort_unless($request->user() && $request->user()->isPrimaryAdmin(), 403);
    }

    public function index(Request $request)
    {
        $this->authorizeAccess($request);
        return view('pages.workflow.send-email');
    }

    public function users(Request $request)
    {
        $this->authorizeAccess($request);
        $data = $request->validate(['q' => 'nullable|string|max:255']);
        $search = trim($data['q'] ?? '');
        if (mb_strlen($search) < 2) return response()->json(['results' => []]);
        $users = User::where('status', 'A')->where(function ($query) use ($search) {
            $query->where('name', 'ilike', '%'.$search.'%')->orWhere('username', 'ilike', '%'.$search.'%');
        })->orderBy('name')->limit(30)->get(['username', 'name', 'notification_email']);
        return response()->json(['results' => $users->map(fn ($user) => [
            'id' => $user->username,
            'text' => ($user->name ?: $user->username).' ('.$user->username.')',
        ])]);
    }

    private function recipient(Request $request): User
    {
        $data = $request->validate(['username' => 'required|string|max:255']);
        return User::where('username', $data['username'])->where('status', 'A')->firstOrFail();
    }

    public function pending(Request $request, PendingApprovalReminder $reminder)
    {
        $this->authorizeAccess($request);
        $user = $this->recipient($request);
        return response()->json($reminder->summary($user) + ['email' => $user->notification_email]);
    }

    public function send(Request $request, PendingApprovalReminder $reminder)
    {
        $this->authorizeAccess($request);
        $user = $this->recipient($request);
        if (!filter_var(trim((string) $user->notification_email), FILTER_VALIDATE_EMAIL)) {
            return response()->json(['message' => 'Notification email pengguna belum diisi atau tidak valid.'], 422);
        }
        $lock = Cache::lock('manual-approval-email:'.hash('sha256', $user->username), 120);
        if (!$lock->get()) return response()->json(['message' => 'Pengiriman untuk pengguna ini sedang berlangsung.'], 429);
        try {
            // Re-read pending approvals at send time; never send document data from the browser.
            $summary = $reminder->summary($user);
            if ($summary['total'] === 0) {
                return response()->json(['message' => 'Tidak ada pending approval yang dapat dikirim.'], 422);
            }
            $reminder->send($user, $summary);
            Log::info('Manual pending approval email sent', [
                'actor' => $request->user()->username, 'recipient' => $user->username, 'total' => $summary['total'],
            ]);
            return response()->json(['message' => 'Email berhasil dikirim ke '.$user->notification_email.'.', 'total' => $summary['total']]);
        } catch (\Throwable $e) {
            Log::error('Manual pending approval email failed', ['actor' => $request->user()->username,
                'recipient' => $user->username, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Email gagal dikirim. Periksa log server untuk detail.'], 500);
        } finally {
            $lock->release();
        }
    }
}
