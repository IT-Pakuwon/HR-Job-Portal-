<?php

namespace App\Http\Controllers;

use App\Models\MsCompany;
use App\Models\MsDepartment;
use App\Models\MsEventLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EventLocationSetupController extends Controller
{
    private function assertAdmin(): void
    {
        $user = auth()->user();

        abort_unless($user && in_array('admin', $user->roles(), true), 403);
    }

    public function index()
    {
        $this->assertAdmin();

        $companies = MsCompany::query()
            ->where('status', 'A')
            ->orderBy('cpny_name')
            ->get(['cpny_id', 'cpny_name']);

        $departments = MsDepartment::query()
            ->where('status', 'A')
            ->orderBy('department_name')
            ->get(['department_id', 'department_name']);

        return response()
            ->view('pages.event_calendar.setup', [
                'companies' => $companies,
                'departments' => $departments,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function json()
    {
        $this->assertAdmin();

        $locations = MsEventLocation::query()
            ->whereNull('deleted_at')
            ->with('company')
            ->orderBy('event_location_name')
            ->get();

        $departmentNames = MsDepartment::query()->pluck('department_name', 'department_id');

        return response()->json([
            'success' => true,
            'data' => $locations->map(function (MsEventLocation $location) use ($departmentNames) {
                return [
                    'id' => $location->id,
                    'cpny_id' => $location->cpny_id,
                    'cpny_name' => optional($location->company)->cpny_name,
                    'event_location_id' => $location->event_location_id,
                    'event_location_name' => $location->event_location_name,
                    'event_total_area' => $location->event_total_area,
                    'event_department_id' => $location->event_department_id,
                    'department_names' => collect($location->departmentIds())
                        ->map(fn ($id) => $departmentNames->get($id, $id))
                        ->implode(', '),
                    'status' => $location->status,
                ];
            }),
        ]);
    }

    private function rules(Request $request, $ignoreId = null): array
    {
        return [
            'cpny_id' => 'required|string|exists:pgsql2.ms_company,cpny_id',
            'event_location_id' => [
                'required',
                'string',
                'max:50',
                Rule::unique('pgsql5.ms_event_location', 'event_location_id')
                    ->where('cpny_id', $request->input('cpny_id'))
                    ->ignore($ignoreId),
            ],
            'event_location_name' => 'required|string|max:255',
            'event_total_area' => 'nullable|numeric',
            'event_department_id' => 'nullable|string|max:100',
            'status' => 'required|in:A,X',
        ];
    }

    private function validateDepartmentIds(?string $commaSeparatedIds): void
    {
        $ids = array_values(array_filter(array_map('trim', explode(',', (string) $commaSeparatedIds))));

        if (empty($ids)) {
            return;
        }

        $validCount = MsDepartment::query()->whereIn('department_id', $ids)->count();

        abort_if($validCount !== count($ids), 422, 'One or more selected departments are invalid.');
    }

    public function store(Request $request)
    {
        $this->assertAdmin();

        $data = $request->validate($this->rules($request));
        $this->validateDepartmentIds($data['event_department_id'] ?? null);

        $location = DB::connection('pgsql5')->transaction(function () use ($data) {
            return MsEventLocation::create([
                ...$data,
                'created_by' => auth()->user()->username,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Event location created successfully',
            'data' => $location,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->assertAdmin();

        $location = MsEventLocation::query()->whereNull('deleted_at')->findOrFail($id);

        $data = $request->validate($this->rules($request, $location->id));
        $this->validateDepartmentIds($data['event_department_id'] ?? null);

        DB::connection('pgsql5')->transaction(function () use ($data, $location) {
            $location->update([
                ...$data,
                'updated_by' => auth()->user()->username,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Event location updated successfully',
            'data' => $location,
        ]);
    }

    public function destroy($id)
    {
        $this->assertAdmin();

        $location = MsEventLocation::query()->whereNull('deleted_at')->findOrFail($id);

        $location->update([
            'status' => 'X',
            'deleted_by' => auth()->user()->username,
            'deleted_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Event location deleted successfully',
        ]);
    }
}
