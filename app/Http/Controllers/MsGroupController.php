<?php
namespace App\Http\Controllers;

use App\Models\MsGroup;
use Illuminate\Http\Request;

class MsGroupController extends Controller
{
    public function index()
    {
        return view('pages.groups.groups');
    }

    // public function json()
    // {
    //     return response()->json(MsGroup::latest()->get());
    // }
    public function json()
    {
        $tasks = MsGroup::select(['group_id', 'group_code', 'group_name', 'status'])
            ->latest()
            ->get();

        return response()->json(['data' => $tasks]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'group_code' => 'required',
            'group_name' => 'required',
        ]);

        $post = MsGroup::create([
            'group_code' => $request->group_code,
            'group_name' => $request->group_name,
            'status' => 'A',
        ]);  

        return response()->json($post);
    }

    public function edit($id)
    {
        $post = MsGroup::findOrFail($id);
        return response()->json($post);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'group_code' => 'required',
            'group_name' => 'required',
        ]);

        $post = MsGroup::findOrFail($id);
        $post->update($request->all());

        return response()->json($post);
    }

    public function toggleStatus($id)
    {
        $group = MsGroup::findOrFail($id);
        $group->update(['status' => request('status')]);

        return response()->json(['message' => 'Status updated successfully']);
    }

}
