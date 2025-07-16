<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use App\Models\Autonbr;
use App\Models\Applicant;
use App\Models\ApplicantCourse;
use App\Models\ApplicantEducation;
use App\Models\ApplicantFamily;
use App\Models\ApplicantLanguage;
use App\Models\ApplicantMarital;
use App\Models\ApplicantSW;
use App\Models\ApplicantWorking;
use App\Models\JobApply;
use App\Models\Jobposting;
use App\Models\JobpostingResponsiblities;
use App\Models\JobpostingQualification;
use App\Models\JobApplyStep;
use App\Models\JobStep;

class ApplicantController extends Controller
{
    public function storeApplicants(Request $request)
    {
        // dd($request->all());         
               
        $doctype = 'APP';      
        DB::beginTransaction();
        try {
            $datenow = Carbon::now()->format('Y-m-d');
            $dt = Carbon::now();
            $year = $dt->year;
            $month = str_pad($dt->month, 2, '0', STR_PAD_LEFT);
            // $doctype = 'TSK';
            $datestamp = Carbon::now()->toDateTimeString();
            $user = request()->user();

            // Generate task ID
            $autonbr = Autonbr::lockForUpdate()
                ->where('doctype', $doctype)
                ->where('year', $year)
                ->where('month', $month)
                ->where('status', 'A')
                ->first();

            if (!$autonbr) {
                $autonbr = Autonbr::create([
                    'doctype' => $doctype,
                    'year' => $year,
                    'month' => $month,
                    'status' => 'A',
                    'number' => 1
                ]);
                $urutan = 1;
            } else {
                $urutan = $autonbr->number + 1;
                $autonbr->number = $urutan;
                $autonbr->save();
            }

            $tglbln = substr($year, 2) . $month;
            $docid = $doctype . $tglbln . sprintf("%03d", $urutan);

            $upload_cv = null;
            $upload_coverletter = null;
            $upload_photo = null;
                                    
            // Handle upload_cv
            if ($request->hasFile('upload_cv')) {
                $file = $request->file('upload_cv');
                $randomNumber = random_int(10000000, 99999999);
                $originalName = str_replace('%', '', $file->getClientOriginalName());
                $upload_cv = md5($randomNumber) . '-' . $originalName;
            
                $folder_attach = public_path('/attachments/' . $year);
                if (!is_dir($folder_attach)) {
                    mkdir($folder_attach, 0777, true);
                }
                $file->move($folder_attach, $upload_cv);
            }
            
            // Handle upload_coverletter
            if ($request->hasFile('upload_coverletter')) {
                $file = $request->file('upload_coverletter');
                $randomNumber = random_int(10000000, 99999999);
                $originalName = str_replace('%', '', $file->getClientOriginalName());
                $upload_coverletter = md5($randomNumber) . '-' . $originalName;
            
                $folder_attach = public_path('/attachments/' . $year);
                if (!is_dir($folder_attach)) {
                    mkdir($folder_attach, 0777, true);
                }
                $file->move($folder_attach, $upload_coverletter);
            }
            
            // Handle upload_photo
            if ($request->hasFile('upload_photo')) {
                $file = $request->file('upload_photo');
                $randomNumber = random_int(10000000, 99999999);
                $originalName = str_replace('%', '', $file->getClientOriginalName());
                $upload_photo = md5($randomNumber) . '-' . $originalName;
            
                $folder_attach = public_path('/attachments/' . $year);
                if (!is_dir($folder_attach)) {
                    mkdir($folder_attach, 0777, true);
                }
                $file->move($folder_attach, $upload_photo);
            }
            
                                
            $app = Applicant::create([
                'applicant_id' => $docid,                           
                'full_name' => $request->full_name,
                'nick_name' => $request->nick_name,
                'birth_place' => $request->birth_place,
                'date_of_birth' => $request->date_of_birth,
                'age' => $request->age,
                'religion' => $request->religion,
                'gender' => $request->gender,
                'blood_type' => $request->blood_type,
                'martial_status' => $request->martial_status,
                'ktp_id' => $request->ktp_id,
                'citizenship' => $request->citizenship,
                'id_address' => $request->id_address,
                'domicile_address' => $request->domicile_address,
                'domicile_city' => $request->domicile_city,
                'phone_number' => $request->phone_number,
                'mobile_phone' => $request->mobile_phone,
                'email_address' => $request->email_address,
                'height' => $request->height,
                'weight' => $request->weight,
                'sosmed_facebook_account' => $request->sosmed_facebook_account,
                'sosmed_instagram_account' => $request->sosmed_instagram_account,
                'sosmed_x_account' => $request->sosmed_x_account,
                'sosmed_linkedin_account' => $request->sosmed_linkedin_account,
                'urgent_contact_name' => $request->urgent_contact_name,
                'urgent_phone' => $request->urgent_phone,
                'urgent_contact_relation' => $request->urgent_contact_relation,
                'existing_last_thp' => $request->existing_last_thp,
                'expected_thp' => $request->expected_thp,
                'expectations' => $request->expectations,
                'relative_work_status' => $request->relative_work_status,
                'relative_work_name' => $request->relative_work_name,
                'relative_work_division' => $request->relative_work_division,
                'career_achievement' => $request->career_achievement,
                'reference_name' => $request->reference_name,
                'reference_division' => $request->reference_division,
                'reference_contact_number' => $request->reference_contact_number,
                'apply_other_on_progress' => $request->apply_other_on_progress,
                'apply_other_on_progress_descr' => $request->apply_other_on_progress_descr,
                'apply_status' => $request->apply_status,
                'upload_cv' => $upload_cv,
                'upload_coverletter' => $upload_coverletter,
                'upload_photo' => $upload_photo,
                'status' => 'A',
                'created_user' => $user->email,
                'created_at' => $datenow,              
                       
            ]);

            $family_name = $request->input('family_name');
            $family_type = $request->input('family_type');
            $family_gender = $request->input('family_gender');
            $family_birt_of_date = $request->input('family_birt_of_date');
            $family_education = $request->input('family_education');
            $family_profession = $request->input('family_profession');

            // Loop dan simpan per baris
            foreach ($family_name as $index => $familyname) {
                ApplicantFamily::create([
                    'applicant_id' => $docid,
                    'family_name' => $familyname,
                    'family_type' => $family_type[$index],
                    'family_gender' => $family_gender[$index],
                    'family_birt_of_date' => $family_birt_of_date[$index],
                    'family_education' => $family_education[$index], 
                    'family_profession' => $family_profession[$index],
                    'status' => 'A',
                    'created_user' => $user->email,
                    'created_at' => $datenow,
                ]);
            }

            $core_family_name = $request->input('core_family_name');
            $core_family_type = $request->input('core_family_type');
            $core_family_gender = $request->input('core_family_gender');
            $core_family_birt_of_date = $request->input('core_family_birt_of_date');
            $core_family_education = $request->input('core_family_education');
            $core_family_profession = $request->input('core_family_profession');

            if (is_array($core_family_name)) {
                foreach ($core_family_name as $index => $name) {
                    if (!empty($name)) {
                        ApplicantMarital::create([
                            'applicant_id' => $docid,
                            'core_family_name' => $name,
                            'core_family_type' => $core_family_type[$index] ?? null,
                            'core_family_gender' => $core_family_gender[$index] ?? null,
                            'core_family_birt_of_date' => $core_family_birt_of_date[$index] ?? null,
                            'core_family_education' => $core_family_education[$index] ?? null,
                            'core_family_profession' => $core_family_profession[$index] ?? null,
                            'status' => 'A',
                            'created_user' => $user->email,
                            'created_at' => $datenow,
                        ]);
                    }
                }
            }

            $education_name = $request->input('education_name');
            $education_type = $request->input('education_type');
            $start_education = $request->input('start_year');
            $end_education = $request->input('end_year');
            $education_score = $request->input('education_score');

            if (is_array($education_name)) {
                foreach ($education_name as $index => $name) {
                    if (!empty($name)) {
                        ApplicantEducation::create([
                            'applicant_id' => $docid,
                            'education_name' => $name,
                            'education_type' => $education_type[$index] ?? null,
                            'start_year' => $start_education[$index] ?? null,
                            'end_year' => $end_education[$index] ?? null,
                            'education_score' => $education_score[$index] ?? null,
                            'status' => 'A',
                            'created_user' => $user->email,
                            'created_at' => $datenow,
                        ]);
                    }
                }
            }

            $companies = $request->input('company_name');
            $titles = $request->input('job_title');
            $startDates = $request->input('start_date');
            $endDates = $request->input('end_date');
            $superiors = $request->input('superior_name');
            $reasons = $request->input('reason_for_leaving');

            if (is_array($companies)) {
                foreach ($companies as $index => $company) {
                    if (!empty($company)) {
                        ApplicantWorking::create([
                            'applicant_id' => $docid,
                            'company_name' => $company,
                            'job_title' => $titles[$index] ?? null,
                            'start_date' => $startDates[$index] ?? null,
                            'end_date' => $endDates[$index] ?? null,
                            'superior_name' => $superiors[$index] ?? null,
                            'reason_for_leaving' => $reasons[$index] ?? null,
                            'status' => 'A',
                            'created_user' => $user->email,
                            'created_at' => $datenow,
                        ]);
                    }
                }
            }

            $descriptions = $request->input('language_descr');
            $language_score = $request->input('language_score');

            if (is_array($descriptions)) {
                foreach ($descriptions as $index => $desc) {
                    if (!empty($desc)) {
                        ApplicantLanguage::create([
                            'applicant_id' => $docid,
                            'language_descr' => $desc,
                            'language_score' => $language_score[$index] ?? null,
                            'status' => 'A',
                            'created_user' => $user->email,
                            'created_at' => $datenow,
                        ]);
                    }
                }
            }

            $course_name = $request->input('course_name');
            $course_type = $request->input('course_type');
            $start_course = $request->input('start_year');
            $end_course = $request->input('end_year');

            if (is_array($course_name)) {
                foreach ($course_name as $index => $coursename) {
                    if (!empty($name)) {
                        ApplicantCourse::create([
                            'applicant_id' => $docid,
                            'course_name' => $coursename,
                            'course_type' => $course_type[$index] ?? null,
                            'start_year' => $start_course[$index] ?? null,
                            'end_year' => $end_course[$index] ?? null,
                            'status' => 'A',
                            'created_user' => $user->email,
                            'created_at' => $datenow,
                        ]);
                    }
                }
            }
            
            $sw_type = $request->input('sw_type');
            $sw_descr = $request->input('sw_descr');

            if (is_array($sw_type)) {
                foreach ($sw_type as $index => $type) {
                    if (!empty($type) && !empty($sw_descr[$index])) {
                        ApplicantSW::create([
                            'applicant_id' => $docid,
                            'sw_type' => $type,
                            'sw_descr' => $sw_descr[$index],
                            'status' => 'A',
                            'created_user' => $user->email,
                            'created_at' => $datenow,
                        ]);
                    }
                }
            }       
            
            
            $skill_descr = $request->input('skill_descr');

            if (is_array($skill_descr)) {
                foreach ($skill_descr as $index => $skill) {
                    if (!empty($type) && !empty($skill_descr[$index])) {
                        ApplicantSW::create([
                            'applicant_id' => $docid,                           
                            'skill_descr' => $skill,
                            'status' => 'A',
                            'created_user' => $user->email,
                            'created_at' => $datenow,
                        ]);
                    }
                }
            }       

     
            DB::commit();
            return response()->json(['success' => true, 'app' => $app]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan applicant', 'message' => $e->getMessage()], 500);
        }
    }
   
    public function JobApply(Request $request)
    {
        // dd($request->all());      
         
        DB::beginTransaction();
        try {
            $datenow = Carbon::now()->format('Y-m-d');       
            $datestamp = Carbon::now()->toDateTimeString();
            $user = Auth::user();

            $applicant = Applicant::where('created_user', $user->email)   
                ->first();
            
            $existing = JobApply::where('docid', $request->job_id)
                    ->where('applicant_id', $applicant->applicant_id)
                    ->first();
            
            if ($existing) {
                return response()->json([
                'error' => true,
                'message' => 'You have already applied for this job.'
                ], 409); // Conflict
            }           
                              
            $app = JobApply::create([
                'docid' => $request->job_id,                           
                'applicant_id' => $applicant->applicant_id,
                'apply_date' => $datenow,
                'apply_step' => 'JOAPP',                        
                'status' => 'A',
                'created_user' => $user->email,
                'created_at' => $datenow,          
            ]);

            $msjob_step = JobStep::orderby('step_order','ASC')         
                ->get();
            
            foreach ($msjob_step as $js) {
                JobApplyStep::create([
                    'docid' => $request->job_id,
                    'applicant_id' => $applicant->applicant_id,
                    'step_id' => $js->step_id,
                    'step_order' => $js->step_order,
                    'created_user' => $user->email,
                    'status' => 'P'                                               
                ]);
            }          
     
            DB::commit();
            return response()->json(['success' => true, 'app' => $app]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal menyimpan Job Apply', 'message' => $e->getMessage()], 500);
        }
    }

    public function MyApplicant()
    {

        return view('pages.applicant.applicant');
    }

    public function MyJobapply()
    {

        return view('pages.applicant.jobapply');
    }

    public function json()
    {
        $user = Auth::user();
        // $tasks = JobApply::select(['docid', 'applicant_id', 'apply_date', 'apply_step'])
        //     ->where('created_user',$user->email)
        //     ->latest()
        //     ->get();
        $tasks = JobApply::select([
            'hr_trx_job_apply.docid',
            'hr_trx_job_apply.applicant_id',
            'hr_trx_job_apply.apply_date',
            'hr_trx_job_apply.apply_step',
            'hr_trx_job_apply.status',
            'hr_trx_jobposting.job_title as job_title',       
        ])
        ->join('hr_trx_jobposting', 'hr_trx_job_apply.docid', '=', 'hr_trx_jobposting.docid')
        ->where('hr_trx_job_apply.created_user', $user->email)
        ->latest('hr_trx_job_apply.created_at')
        ->get();
    

        return response()->json(['data' => $tasks]);
    }

    public function ShowJob($id)
    {
        // Contoh ambil job apply berdasarkan docid
        // $jobapply = JobApply::where('docid', $id)->firstOrFail();
        $jobapply = JobApply::select([
            'hr_trx_job_apply.docid',
            'hr_trx_job_apply.applicant_id',
            'hr_trx_job_apply.apply_date',
            'hr_trx_job_apply.apply_step',
            'hr_trx_job_apply.status',
            'hr_trx_jobposting.job_title as job_title',       
        ])
        ->join('hr_trx_jobposting', 'hr_trx_job_apply.docid', '=', 'hr_trx_jobposting.docid')
        ->where('hr_trx_job_apply.docid', $id)       
        ->first();

        $jobposting = Jobposting::where('docid', $id)           
            ->first();

        $jobres = JobpostingResponsiblities::where('docid', $id)->get();
        $jobqua = JobpostingQualification::where('docid', $id)->get();

        return view('pages.applicant.showjob', compact('jobapply','jobposting','jobres','jobqua'));
    }


  
}

