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
            $this->calculateTrainingStatus($training);
            $this->calculateSessionSummary($training);
        }

        session()->put('master_trainings', $trainings);

        return view('pages.master_training.master', compact('trainings'));
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE PAGE
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        return view('pages.master_training.createmaster');
    }

    /*
    |--------------------------------------------------------------------------
    | STORE (MATCH CREATE BLADE)
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'type' => 'required|in:MANDATORY,NON_MANDATORY',
            'category' => 'required_if:type,NON_MANDATORY',
            'trainer' => 'required',
            'poster' => 'required|image|max:2048',
            'sessions' => 'required|array|min:1',
            'sessions.*.start_date' => 'required|date',
            'sessions.*.start_time' => 'required',
            'sessions.*.end_time' => 'required',
            'sessions.*.mode' => 'required|in:ONLINE,OFFLINE,HYBRID',
            'sessions.*.quota' => 'required|integer|min:1',
        ]);

        $trainings = session()->get('master_trainings', []);

        // Upload poster
        $posterPath = $request->file('poster')->store('training_posters', 'public');

        $trainingId = count($trainings) + 1;

        $newTraining = [
            'id' => $trainingId,
            'name' => $request->name,
            'type' => $request->type,
            'category' => $request->category,
            'trainer' => $request->trainer,
            'description' => $request->description,
            'poster' => $posterPath,
            'applies_to_specific' => $request->applies_to_specific ? true : false,
            'is_active' => $request->is_active ? true : false,
            'created_at' => now()->toDateTimeString(),
            'status' => 'DRAFT',
            'sessions' => []
        ];

        foreach ($request->sessions as $index => $session) {

            $closeDate = Carbon::parse($session['start_date'])
                ->subDays(3)
                ->toDateString();

            $newTraining['sessions'][] = [
                'id' => $index + 1,
                'level' => $session['level'] ?? null,
                'start_date' => $session['start_date'],
                'start_time' => $session['start_time'],
                'end_time' => $session['end_time'],
                'mode' => $session['mode'],
                'location' => $session['location'] ?? null,
                'platform' => $session['platform'] ?? null,
                'meeting_link' => $session['meeting_link'] ?? null,
                'quota' => $session['quota'],
                'approved_count' => 0,
                'waitlist_count' => 0,
                'available_quota' => $session['quota'],
                'close_date' => $closeDate,
                'is_active' => isset($session['is_active'])
            ];
        }

        $trainings[] = $newTraining;

        session()->put('master_trainings', $trainings);

        return redirect()
            ->route('mastertraining.index')
            ->with('success', 'Training created successfully');
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $trainings = session()->get('master_trainings', []);

        $training = collect($trainings)->firstWhere('id', (int)$id);

        abort_if(!$training, 404);

        return view('pages.master_training.showtraining', compact('training'));
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */
    public function edit($id)
    {
        $trainings = session()->get('master_trainings', []);

        $training = collect($trainings)->firstWhere('id', (int)$id);

        abort_if(!$training, 404);

        return view('pages.master_training.editmaster', compact('training'));
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'type' => 'required|in:MANDATORY,NON_MANDATORY',
            'category' => 'required_if:type,NON_MANDATORY',
            'trainer' => 'required',
            'sessions' => 'required|array|min:1',
            'sessions.*.start_date' => 'required|date',
            'sessions.*.start_time' => 'required',
            'sessions.*.end_time' => 'required',
            'sessions.*.mode' => 'required|in:ONLINE,OFFLINE,HYBRID',
            'sessions.*.quota' => 'required|integer|min:1',
        ]);

        $trainings = session()->get('master_trainings', []);

        foreach ($trainings as &$training) {

            if ($training['id'] != $id) continue;

            /* =============================
            UPDATE HEADER
            ============================== */

            $training['name'] = $request->name;
            $training['type'] = $request->type;
            $training['category'] = $request->category;
            $training['trainer'] = $request->trainer;
            $training['description'] = $request->description;
            $training['applies_to_specific'] = $request->applies_to_specific ? true : false;
            $training['is_active'] = $request->is_active ? true : false;

            /* =============================
            UPDATE POSTER (IF NEW)
            ============================== */

            if ($request->hasFile('poster')) {
                $posterPath = $request->file('poster')
                    ->store('training_posters', 'public');

                $training['poster'] = $posterPath;
            }

            /* =============================
            REBUILD SESSIONS COMPLETELY
            ============================== */

            $training['sessions'] = [];

            foreach ($request->sessions as $index => $session) {

                $closeDate = Carbon::parse($session['start_date'])
                    ->subDays(3)
                    ->toDateString();

                $training['sessions'][] = [
                    'id' => $index + 1,
                    'level' => $session['level'] ?? null,
                    'start_date' => $session['start_date'],
                    'start_time' => $session['start_time'],
                    'end_time' => $session['end_time'],
                    'mode' => $session['mode'],
                    'location' => $session['location'] ?? null,
                    'platform' => $session['platform'] ?? null,
                    'meeting_link' => $session['meeting_link'] ?? null,
                    'quota' => $session['quota'],
                    'approved_count' => 0,
                    'waitlist_count' => 0,
                    'available_quota' => $session['quota'],
                    'close_date' => $closeDate,
                    'is_active' => isset($session['is_active'])
                ];
            }
        }

        session()->put('master_trainings', $trainings);

        return redirect()
            ->route('mastertraining.index')
            ->with('success', 'Training updated successfully');
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        $trainings = session()->get('master_trainings', []);

        $trainings = collect($trainings)
            ->reject(fn($t) => $t['id'] == $id)
            ->values()
            ->toArray();

        session()->put('master_trainings', $trainings);

        return back()->with('success', 'Training deleted');
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS CALCULATION
    |--------------------------------------------------------------------------
    */
    private function calculateTrainingStatus(&$training)
    {
        if (!$training['is_active']) {
            $training['status'] = 'DRAFT';
            return;
        }

        if (empty($training['sessions'])) {
            $training['status'] = 'DRAFT';
            return;
        }

        $earliest = collect($training['sessions'])
            ->sortBy('start_date')
            ->first();

        $closeDate = Carbon::parse($earliest['start_date'])->subDays(3);

        if (now()->lessThanOrEqualTo($closeDate)) {
            $training['status'] = 'OPEN';
        } elseif (now()->greaterThan(Carbon::parse($earliest['start_date']))) {
            $training['status'] = 'FINISHED';
        } else {
            $training['status'] = 'CLOSED';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SESSION SUMMARY
    |--------------------------------------------------------------------------
    */
    private function calculateSessionSummary(&$training)
    {
        foreach ($training['sessions'] as &$session) {
            $session['available_quota'] = max(
                0,
                $session['quota'] - $session['approved_count']
            );
        }
    }
}
