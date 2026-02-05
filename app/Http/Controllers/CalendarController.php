<?php
use App\Models\Agenda;
use App\Models\UserGoogle;
use App\Services\GoogleCalendarService;
use Carbon\Carbon;

$start = Carbon::parse($request->week)->startOfWeek();
$end = Carbon::parse($request->week)->endOfWeek();

$agendas = Agenda::whereBetween('startdate', [$start, $end])
    ->whereNull('deleted_at')
    ->get();

$googleEvents = collect();

$google = UserGoogle::where('username', auth()->user()->username)
    ->whereNull('deleted_at')
    ->first();

if ($google) {
    $service = GoogleCalendarService::make($google);

    $events = $service->events->listEvents('primary', [
        'timeMin' => $start->toRfc3339String(),
        'timeMax' => $end->toRfc3339String(),
        'singleEvents' => true,
    ]);

    foreach ($events->getItems() as $event) {
        $googleEvents->push($event);
    }
}
