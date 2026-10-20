<?php

namespace App\Http\Controllers;

use App\Models\TrLndRescheduleEmailQueue;
use App\Models\TrLndTrainingRegistration;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Read-only view of tr_lnd_reschedule_email_queue — the queue drained by
 * `training:send-reschedule-emails` — so its progress can be checked
 * without opening the database.
 */
class RescheduleEmailQueueController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $search = trim((string) $request->query('q', ''));

        $counts = TrLndRescheduleEmailQueue::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $query = TrLndRescheduleEmailQueue::query()->orderByDesc('id');

        if (in_array($status, [
            TrLndRescheduleEmailQueue::STATUS_PENDING,
            TrLndRescheduleEmailQueue::STATUS_SENT,
            TrLndRescheduleEmailQueue::STATUS_FAILED,
        ], true)) {
            $query->where('status', $status);
        } else {
            $status = null;
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('training_regist_id', 'ilike', "%{$search}%")
                    ->orWhere('schedule_id', 'ilike', "%{$search}%");
            });
        }

        $rows = $query->paginate(50)->withQueryString();

        // Names/emails come from other connections, so resolve them in two
        // lookups for the visible page instead of joining.
        $usernames = TrLndTrainingRegistration::whereIn('training_regist_id', $rows->pluck('training_regist_id'))
            ->pluck('user_registration', 'training_regist_id');

        $users = User::whereIn('username', $usernames->values())->get()->keyBy('username');

        $rows->getCollection()->transform(function ($row) use ($usernames, $users) {
            $user = $users->get($usernames->get($row->training_regist_id));

            $row->participant_name = $user->name ?? $usernames->get($row->training_regist_id);
            $row->participant_email = $user ? ($user->notification_email ?: $user->email) : null;

            return $row;
        });

        return view('pages.reschedule_email_queue.index', [
            'rows' => $rows,
            'counts' => $counts,
            'status' => $status,
            'search' => $search,
        ]);
    }

    /**
     * Put a Sent/Failed row back in the queue; the scheduled
     * `training:send-reschedule-emails` run delivers it on its next tick.
     */
    public function resend(int $id)
    {
        $row = TrLndRescheduleEmailQueue::findOrFail($id);

        if ($row->status === TrLndRescheduleEmailQueue::STATUS_PENDING) {
            return back()->with('error', 'Email is already pending.');
        }

        $row->status = TrLndRescheduleEmailQueue::STATUS_PENDING;
        $row->attempts = 0;
        $row->last_error = null;
        $row->sent_at = null;
        $row->save();

        return back()->with('success', "Email for {$row->training_regist_id} re-queued; it will be sent within 5 minutes.");
    }
}
