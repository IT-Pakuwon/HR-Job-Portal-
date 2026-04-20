<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\ApplicantCourse;
use App\Models\ApplicantEducation;
use App\Models\ApplicantFamily;
use App\Models\ApplicantLanguage;
use App\Models\ApplicantMarital;
use App\Models\ApplicantSkill;
use App\Models\ApplicantSW;
use App\Models\ApplicantWorking;
use Illuminate\Http\Request;

class ApplicantController extends Controller
{
    public function index()
    {
        $all = Applicant::count();
        $onProgress = Applicant::where('status', 'P')->count();
        $reject = Applicant::where('status', 'R')->count();
        $revise = Applicant::where('status', 'D')->count();
        $completed = Applicant::where('status', 'C')->count();

        return view('pages.applicants.applicants', compact('all', 'onProgress', 'reject', 'revise', 'completed'));
    }

    public function json(Request $request)
    {
        // $status = $request->query('status', 'P');
        $status = $request->has('status') ? $request->query('status') : 'P';

        $query = Applicant::query();

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $applicant = $query->orderBy('id', 'desc')->get();

        return response()->json(['data' => $applicant]);
    }

    public function showApplicant($id)
    {
        $applicant = Applicant::with('driverLicenses')->findOrFail($id);

        $applicant_family = ApplicantFamily::where('applicant_id', $applicant->applicant_id)->get();
        $applicant_marital = ApplicantMarital::where('applicant_id', $applicant->applicant_id)->get();
        $applicant_education = ApplicantEducation::where('applicant_id', $applicant->applicant_id)->get();
        $applicant_working = ApplicantWorking::where('applicant_id', $applicant->applicant_id)->get();
        $applicant_language = ApplicantLanguage::where('applicant_id', $applicant->applicant_id)->get();
        $applicant_course = ApplicantCourse::where('applicant_id', $applicant->applicant_id)->get();
        $applicant_sw = ApplicantSW::where('applicant_id', $applicant->applicant_id)->get();
        $applicant_skill = ApplicantSkill::where('applicant_id', $applicant->applicant_id)->get();

        $year = now()->year;
        $photo = 'http://127.0.0.1:7777/attachments/'.$year.'/'.$applicant->upload_photo;
        $cv = 'http://127.0.0.1:7777/attachments/'.$year.'/'.$applicant->upload_cv;
        $coverletter = 'http://127.0.0.1:7777/attachments/'.$year.'/'.$applicant->upload_coverletter;

        return view('pages.applicants.showapplicants', compact(
            'applicant',
            'applicant_family',
            'applicant_marital',
            'applicant_education',
            'applicant_working',
            'applicant_language',
            'applicant_course',
            'applicant_sw',
            'applicant_skill',
            'photo',
            'cv',
            'coverletter'
        ));
    }
}
