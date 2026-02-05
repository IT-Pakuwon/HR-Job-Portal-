<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Agenda;
use Carbon\Carbon;

class AgendaController extends Controller
{
    public function store(Request $request)
    {
        Agenda::create([
            'title' => $request->title,
            'description' => $request->link,
            'agendadate' => $request->deadline,
            'startdate' => Carbon::parse($request->deadline . ' ' . $request->start_time),
            'enddate' => $request->end_time
                ? Carbon::parse($request->deadline . ' ' . $request->end_time)
                : null,
            'location' => $request->location,
            'status' => 'OPEN',
            'created_user' => auth()->user()->username,
        ]);

        return response()->json(['ok' => true]);
    }
}
