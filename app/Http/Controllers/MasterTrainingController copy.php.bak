<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class MasterTrainingController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $trainings = session()->get('master_trainings', []);

        foreach ($trainings as &$training) {

            $sessions = $training['sessions'] ?? [];

            if (empty($sessions) || empty($training['is_active'])) {
                $training['status'] = 'DRAFT';
                continue;
            }

            $earliest = collect($sessions)->sortBy('start_date')->first();
            $closeDate = Carbon::parse($earliest['start_date'])->subDays(3);

            if (now()->lessThanOrEqualTo($closeDate)) {
                $training['status'] = 'OPEN';
            } else {
                $training['status'] = 'CLOSED';
            }
        }

        return view('pages.master_training.master', compact('trainings'));
    }

    public function show($id)
{
    $trainings = session()->get('master_trainings', []);

    $training = collect($trainings)->firstWhere('id', (int)$id);

    if (!$training) {
        abort(404);
    }

    return view('pages.master_training.showtraining', compact('training'));
}

    /*
    |--------------------------------------------------------------------------
    | REGISTER USER
    |--------------------------------------------------------------------------
    */
    public function register($trainingId, $sessionId, $userId)
    {
        $trainings = session()->get('master_trainings', []);
        $registrations = session()->get('training_registrations', []);

        foreach ($trainings as &$training) {

            if ($training['id'] != $trainingId) continue;

            foreach ($training['sessions'] as &$session) {

                if ($session['id'] != $sessionId) continue;

                $today = now();
                $closeDate = Carbon::parse($session['close_date']);

                if ($today->greaterThan($closeDate)) {
                    return back()->with('error', 'Registration closed');
                }

                $approvedCount = collect($registrations)
                    ->where('session_id', $sessionId)
                    ->where('status', 'APPROVED')
                    ->count();

                if ($approvedCount < $session['quota']) {

                    $status = 'PENDING';

                } else {

                    $status = 'WAITLIST';
                }

                $registrations[] = [
                    'id' => count($registrations) + 1,
                    'training_id' => $trainingId,
                    'session_id' => $sessionId,
                    'user_id' => $userId,
                    'status' => $status,
                    'created_at' => now()->toDateTimeString(),
                ];

                session()->put('training_registrations', $registrations);

                return back()->with('success', 'Registration submitted: ' . $status);
            }
        }

        return back()->with('error', 'Session not found');
    }


    /*
    |--------------------------------------------------------------------------
    | APPROVE REGISTRATION
    |--------------------------------------------------------------------------
    */
    public function approve($registrationId)
    {
        $registrations = session()->get('training_registrations', []);

        foreach ($registrations as &$reg) {

            if ($reg['id'] == $registrationId) {
                $reg['status'] = 'APPROVED';
                break;
            }
        }

        session()->put('training_registrations', $registrations);

        return back()->with('success', 'Approved');
    }


    /*
    |--------------------------------------------------------------------------
    | REJECT REGISTRATION
    |--------------------------------------------------------------------------
    */
    public function reject($registrationId)
    {
        $registrations = session()->get('training_registrations', []);

        foreach ($registrations as &$reg) {

            if ($reg['id'] == $registrationId) {
                $reg['status'] = 'REJECTED';
                break;
            }
        }

        session()->put('training_registrations', $registrations);

        return back()->with('success', 'Rejected');
    }


    /*
    |--------------------------------------------------------------------------
    | CANCEL REGISTRATION (H-3 RULE)
    |--------------------------------------------------------------------------
    */
    public function cancel($registrationId)
    {
        $registrations = session()->get('training_registrations', []);
        $trainings = session()->get('master_trainings', []);

        foreach ($registrations as &$reg) {

            if ($reg['id'] != $registrationId) continue;

            foreach ($trainings as &$training) {

                foreach ($training['sessions'] as &$session) {

                    if ($session['id'] != $reg['session_id']) continue;

                    $today = now();
                    $closeDate = Carbon::parse($session['close_date']);

                    if ($today->lessThanOrEqualTo($closeDate)) {

                        $reg['status'] = 'CANCELLED_BEFORE_CLOSE';

                        $this->promoteWaitlist($session['id'], $registrations);

                    } else {

                        $reg['status'] = 'CANCELLED_AFTER_CLOSE';
                    }
                }
            }
        }

        session()->put('training_registrations', $registrations);

        return back()->with('success', 'Registration cancelled');
    }


    /*
    |--------------------------------------------------------------------------
    | PROMOTE WAITLIST
    |--------------------------------------------------------------------------
    */
    private function promoteWaitlist($sessionId, array &$registrations)
    {
        $nextWaitlist = collect($registrations)
            ->where('session_id', $sessionId)
            ->where('status', 'WAITLIST')
            ->sortBy('created_at')
            ->first();

        if (!$nextWaitlist) return;

        foreach ($registrations as &$reg) {

            if ($reg['id'] == $nextWaitlist['id']) {
                $reg['status'] = 'APPROVED';
                break;
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | EXPIRE WAITLIST AFTER CLOSE
    |--------------------------------------------------------------------------
    */
    public function expireWaitlists()
    {
        $registrations = session()->get('training_registrations', []);
        $trainings = session()->get('master_trainings', []);

        foreach ($trainings as $training) {

            foreach ($training['sessions'] as $session) {

                $closeDate = Carbon::parse($session['close_date']);

                if (now()->lessThanOrEqualTo($closeDate)) continue;

                foreach ($registrations as &$reg) {

                    if (
                        $reg['session_id'] == $session['id'] &&
                        $reg['status'] === 'WAITLIST'
                    ) {
                        $reg['status'] = 'WAITLIST_EXPIRED';
                    }
                }
            }
        }

        session()->put('training_registrations', $registrations);

        return back()->with('success', 'Waitlist expired');
    }


    /*
    |--------------------------------------------------------------------------
    | CALCULATE SESSION SUMMARY
    |--------------------------------------------------------------------------
    */
    public function calculateSummary($trainingId)
    {
        $trainings = session()->get('master_trainings', []);
        $registrations = session()->get('training_registrations', []);

        foreach ($trainings as &$training) {

            if ($training['id'] != $trainingId) continue;

            foreach ($training['sessions'] as &$session) {

                $approved = collect($registrations)
                    ->where('session_id', $session['id'])
                    ->where('status', 'APPROVED')
                    ->count();

                $waitlist = collect($registrations)
                    ->where('session_id', $session['id'])
                    ->where('status', 'WAITLIST')
                    ->count();

                $session['approved_count'] = $approved;
                $session['waitlist_count'] = $waitlist;
                $session['available_quota'] = max(0, $session['quota'] - $approved);
            }
        }

        return $trainings;
    }
}
