<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Vinkla\Hashids\Facades\Hashids;

use App\Models\TrTrainingRegistration;
use App\Models\MsTraining;
use App\Models\User;
use App\Http\Controllers\ApprovalController;

class TrainingRegistrationController extends Controller
{
    protected $doctype = 'TR';

    /*
    |--------------------------------------------------------------------------
    | INDEX PAGE
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $trainings = [];

        for ($i = 1; $i <= 10; $i++) {

            $startDate = now()->addDays(rand(-10, 15));
            $quota = rand(10, 40);
            $approved = rand(0, $quota);

            if ($approved >= $quota) {
                $status = 'FULL';
            } elseif ($startDate->isPast()) {
                $status = 'FINISHED';
            } elseif ($startDate->diffInDays(now()) <= 3 && $startDate->isFuture()) {
                $status = 'CLOSED';
            } else {
                $status = 'OPEN';
            }

            $trainings[] = [
                'id' => $i,
                'name' => "Training Program {$i}",
                'poster' => null,
                'is_active' => true,
                'applies_to_specific' => rand(0,1),
                'status' => $status,
                'sessions' => [
                    [
                        'start_date' => $startDate->toDateString(),
                        'quota' => $quota,
                        'approved_count' => $approved,
                        'level' => collect(['EXECUTIVE','SR_MANAGER','OFFICER'])->random(),
                        'is_active' => true,
                    ]
                ]
            ];
        }

        return view('pages.training.training', compact('trainings'));
    }

    public function showRegister($id)
    {
        $trainings = session()->get('master_trainings', []);
        $training = collect($trainings)->firstWhere('id', $id);

        abort_if(!$training, 404);

        $availableSessions = collect($training['sessions'])
            ->where('is_active', true)
            ->values()
            ->map(function($session, $index){
                $session['index'] = $index;
                return $session;
            });

        return view('pages.training.register', [
            'training' => $training,
            'availableSessions' => $availableSessions
        ]);
    }


}
