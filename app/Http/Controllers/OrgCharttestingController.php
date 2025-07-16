<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Departments; 
use App\Models\Members;
// use App\Models\StoEmployee;
// use App\Models\StoDepartement;
use Illuminate\Support\Facades\DB;

class OrgCharttestingController extends Controller
{
   public function index()
    {
        $departments = Departments::with('members')->whereNull('parent_id')->get();
        return view('orgchart.orgchart', compact('departments'));
    }

    public function storeMember(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
        ]);

        Members::create($request->only('name', 'department_id'));

        return response()->json(['message' => 'Member added successfully.']);
    }

public function json_xxx()
{
    $departments = Departments::where('status', 'A')
        ->with(['members' => function ($query) {
            $query->where('status', 'A');
        }])
        ->get();

    $data = [];

    foreach ($departments as $dept) {    

        $memberList = $dept->members->map(function ($m) {
            return [
                'name' => $m->name,
                'company' => $m->company,
                'position' => $m->position,
                'image' => $m->image ?? 'https://cdn-icons-png.flaticon.com/512/149/149071.png',
            ];
        });
      
        $data[] = [
            'id' => $dept->id,
            'parentId' => $dept->parent_id,
            'name' => $dept->name,
            'position' => 'Department',
            'members' => $memberList->toArray(),
            'image' => 'https://cdn-icons-png.flaticon.com/512/149/149071.png',
        ];


    }

    return response()->json($data);
}

public function json()
{
    $departments = Departments::where('status', 'A')
        ->with(['members' => function ($query) {
            $query->where('status', 'A');
        }])
        ->get();

    $data = [];

    foreach ($departments as $dept) {    

        $memberList = $dept->members->map(function ($m) {
            return [
                'name' => $m->name,
                'company' => $m->company,
                'position' => $m->position,
                'image' => $m->image ?? 'https://cdn-icons-png.flaticon.com/512/149/149071.png',
            ];
        });
      
        $data[] = [
            'id' => $dept->id,
            'parentId' => $dept->parent_id,
            'name' => $dept->name,
            'position' => 'Department',
            'members' => $memberList->toArray(),
            'image' => 'https://cdn-icons-png.flaticon.com/512/149/149071.png',
        ];


    }

    return response()->json($data);
}

   

}
