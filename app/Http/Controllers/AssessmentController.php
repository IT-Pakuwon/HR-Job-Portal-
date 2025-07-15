<?php

namespace App\Http\Controllers;

use App\Models\MsAssessment;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    public function index()
    {
        return view('pages.assessments.assessments');
    }
 
    public function json()
    {
        $tasks = MsAssessment::select(['id','assessment_id', 'assessment_group', 'assessment_descr','assessment_score','step_order_group','step_order', 'status'])
            ->orderby('step_order_group','ASC')
            ->orderby('step_order','ASC')
            ->get();

        return response()->json(['data' => $tasks]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'assessment_group' => 'required',
            'assessment_descr' => 'required',
            'assessment_score' => 'required',
            'step_order_group' => 'required',
            'step_order' => 'required',
        ]);

        $post = MsAssessment::create([
            'assessment_group' => $request->assessment_group,
            'assessment_descr' => $request->assessment_descr,
            'assessment_score' => $request->assessment_score,
            'step_order_group' => $request->step_order_group,
            'step_order' => $request->step_order,
            'status' => 'A',
        ]);  

        return response()->json($post);
    }

    public function edit($id)
    {
        $post = MsAssessment::findOrFail($id);
        return response()->json($post);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'assessment_group' => 'required',
            'assessment_descr' => 'required',
            'assessment_score' => 'required',
            'step_order_group' => 'required',
            'step_order' => 'required',
        ]);

        $post = MsAssessment::findOrFail($id);
        $post->update($request->all());

        return response()->json($post);
    }

    public function toggleStatus($id)
    {
        $screen = MsAssessment::findOrFail($id);
        $screen->update(['status' => request('status')]);

        return response()->json(['message' => 'Status updated successfully']);
    }

}
