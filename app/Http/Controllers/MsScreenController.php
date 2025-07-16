<?php
namespace App\Http\Controllers;

use App\Models\MsScreen;
use Illuminate\Http\Request;

class MsScreenController extends Controller
{
    public function index()
    {
        return view('pages.screens.screens');
    }

    // public function json()
    // {
    //     return response()->json(MsScreen::latest()->get());
    // }
    public function json()
    {
        $tasks = MsScreen::select(['screen_id', 'screen_code', 'screen_name', 'status'])
            ->latest()
            ->get();

        return response()->json(['data' => $tasks]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'screen_code' => 'required',
            'screen_name' => 'required',
        ]);

        $post = MsScreen::create([
            'screen_code' => $request->screen_code,
            'screen_name' => $request->screen_name,
            'status' => 'A',
        ]);  

        return response()->json($post);
    }

    public function edit($id)
    {
        $post = MsScreen::findOrFail($id);
        return response()->json($post);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'screen_code' => 'required',
            'screen_name' => 'required',
        ]);

        $post = MsScreen::findOrFail($id);
        $post->update($request->all());

        return response()->json($post);
    }

    public function toggleStatus($id)
    {
        $screen = MsScreen::findOrFail($id);
        $screen->update(['status' => request('status')]);

        return response()->json(['message' => 'Status updated successfully']);
    }

}
