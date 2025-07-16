<?php
namespace App\Http\Controllers;

use App\Models\MsApplication;
use Illuminate\Http\Request;

class MsApplicationController extends Controller
{
    public function index()
    {
        return view('pages.applications.applications');
    }

    // public function json()
    // {
    //     return response()->json(MsApplication::latest()->get());
    // }
    public function json()
    {
        $tasks = MsApplication::select(['application_id', 'application_code', 'application_name', 'status'])
            ->latest()
            ->get();

        return response()->json(['data' => $tasks]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'application_code' => 'required',
            'application_name' => 'required',
        ]);

        $post = MsApplication::create([
            'application_code' => $request->application_code,
            'application_name' => $request->application_name,
            'status' => 'A',
        ]);  

        return response()->json($post);
    }

    public function edit($id)
    {
        $post = MsApplication::findOrFail($id);
        return response()->json($post);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'application_code' => 'required',
            'application_name' => 'required',
        ]);

        $post = MsApplication::findOrFail($id);
        $post->update($request->all());

        return response()->json($post);
    }

    public function toggleStatus($id)
    {
        $application = MsApplication::findOrFail($id);
        $application->update(['status' => request('status')]);

        return response()->json(['message' => 'Status updated successfully']);
    }

}
