<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DataFeedController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\MsScreenController;
use App\Http\Controllers\MsApplicationController;
use App\Http\Controllers\MsGroupController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProjectTaskController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ManpowerController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\JobpostingController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\WorkInstructionController;
use App\Http\Controllers\JobapplicantController;
use App\Models\MsScreen;
use App\Http\Controllers\OrgChartController;
use App\Http\Controllers\StrukturOrgController;
use App\Http\Controllers\UsersEngController;
use App\Http\Controllers\AssetsLocationController;
use App\Http\Controllers\WorksCategoryController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\ChangeStoController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\SppbController;  
use App\Http\Controllers\SppjController; 
use App\Http\Controllers\SpptController; 
use App\Http\Controllers\SppkController; 
use App\Http\Controllers\ReceivedListController;
use App\Http\Controllers\CsJobController;
use App\Http\Controllers\CsListController;
use App\Http\Controllers\CanvassController;
use App\Http\Controllers\BqCSController;
use App\Http\Controllers\PoListController;
use App\Http\Controllers\PoController;


use App\Http\Controllers\CanvassxController;


use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

// use Illuminate\Support\Facades\Response;
// use Illuminate\Support\Facades\File;

// Route::get('/avatar/{filename}', function($filename){
//     $path = public_path('avatar/' . $filename);   // <- Ubah ke public_path!
//     if (!file_exists($path)) abort(404);

//     $type = File::mimeType($path);
//     $fileContent = File::get($path);

//     return Response::make($fileContent, 200, [
//         'Content-Type' => $type,
//         'Access-Control-Allow-Origin' => '*',
//         'Access-Control-Allow-Methods' => 'GET, OPTIONS',
//         'Access-Control-Allow-Headers' => 'Origin, Content-Type, Accept, Authorization',
//     ]);
// });

Route::get('/avatar/{filename}', function($filename){
    return response($filename, 200, [
        'Access-Control-Allow-Origin' => '*',
        'X-Debug-Header' => 'ROUTE AVATAR TES'
    ]);
});




Route::redirect('/', 'login');
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'login' => ['required'], // Bisa email, username, atau NIP
        'password' => ['required'],
        // 'g-recaptcha-response' => ['required', 'captcha'],        
    ]);

    // Cari user berdasarkan email, username, atau NIP
    $user = User::where('email', $credentials['login'])
                ->orWhere('username', $credentials['login'])
                ->orWhere('npk', $credentials['login'])
                ->first();

    if (!$user || !Auth::attempt(['email' => $user->email, 'password' => $credentials['password']])) {
        throw ValidationException::withMessages([
            'login' => ['These credentials do not match our records.'],
        ]);
    }

    return redirect()->intended('/dashboard');
})->name('login');


Route::post('/logout', function () {
    Auth::logout();
    return redirect('/login');
})->name('logout');


    // Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::middleware(['auth'])->group(function () {

    // Dashboard Approval JSON endpoints
    Route::get('/waitingjson', [DashboardController::class, 'Waitingjson'])->name('dashboard.waitingjson');
    Route::get('/approvejson', [DashboardController::class, 'Approvejson'])->name('dashboard.approvejson');

    // Ambil semua screens dan buat route otomatis
    // $screens = MsScreen::all();
    // foreach ($screens as $screen) {
    //     Route::get($screen->screen_name, function () use ($screen) {
    //         return view($screen->screen_name); // Load view sesuai database
    //     })->name($screen->screen_name);
    // }

    Route::get('/screens', [MsScreenController::class, 'index'])->name('screens');
    Route::get('/screens/json', [MsScreenController::class, 'json'])->name('screens.json'); // Untuk Fetch API
    Route::post('/screens', [MsScreenController::class, 'store'])->name('screens.store');
    Route::get('/screens/{id}/edit', [MsScreenController::class, 'edit'])->name('screens.edit');
    Route::put('/screens/{post}', [MsScreenController::class, 'update'])->name('screens.update');
    Route::put('/screens/{id}/toggle-status', [MsScreenController::class, 'toggleStatus']);

    Route::get('/applications', [MsApplicationController::class, 'index'])->name('applications');
    Route::get('/applications/json', [MsApplicationController::class, 'json'])->name('applications.json'); // Untuk Fetch API
    Route::post('/applications', [MsApplicationController::class, 'store'])->name('applications.store');
    Route::get('/applications/{id}/edit', [MsApplicationController::class, 'edit'])->name('applications.edit');
    Route::put('/applications/{post}', [MsApplicationController::class, 'update'])->name('applications.update');
    Route::put('/applications/{id}/toggle-status', [MsApplicationController::class, 'toggleStatus']);

    Route::get('/groups', [MsGroupController::class, 'index'])->name('groups');
    Route::get('/groups/json', [MsGroupController::class, 'json'])->name('groups.json'); // Untuk Fetch API
    Route::post('/groups', [MsGroupController::class, 'store'])->name('groups.store');
    Route::get('/groups/{id}/edit', [MsGroupController::class, 'edit'])->name('groups.edit');
    Route::put('/groups/{post}', [MsGroupController::class, 'update'])->name('groups.update');
    Route::put('/groups/{id}/toggle-status', [MsGroupController::class, 'toggleStatus']);
      
    // Route::get('/agendas', [AgendaController::class, 'index']);
    // Route::post('/agendas/store', [AgendaController::class, 'store']);
    // Route::get('/agendas', [AgendaController::class, 'index'])->name('agendas');    
    // Route::get('/agendas/json', [AgendaController::class, 'json'])->name('agendas.json'); 
    // Route::post('/agendas', [AgendaController::class, 'store'])->name('agendas.store'); 
    Route::get('/api/agendas/today', [AgendaController::class, 'getAgendas'])->name('agendas.today');
    Route::get('/api/agendas/{id}', [AgendaController::class, 'show'])->name('agendas.show');
    Route::put('/api/agendas/{id}', [AgendaController::class, 'update'])->name('agendas.update');
    Route::get('/api/agendas/month', [AgendaController::class, 'getMonthlyAgendas']);
    

    Route::get('/news', [NewsController::class, 'index'])->name('news');
    Route::get('/news/json', [NewsController::class, 'json'])->name('news.json');
    Route::get('/createnews', [NewsController::class, 'createNews']);
    Route::post('/news', [NewsController::class, 'storeNews'])->name('news.store');
    Route::get('/shownews/{id}', [NewsController::class, 'showNews']);
    Route::get('/news/{id}/comments', [NewsController::class, 'fetchComments']);
    Route::post('/news/{id}/comments', [NewsController::class, 'storeComment']);
    Route::post('/news/{id}/approve', [NewsController::class, 'approveNews']);
    Route::post('/news/{id}/reject', [NewsController::class, 'rejectNews']);
    Route::post('/news/{id}/revise', [NewsController::class, 'reviseNews']);
    Route::get('/editnews/{id}', [NewsController::class, 'editNews']);
    Route::put('/news/{id}', [NewsController::class, 'updateNews'])->name('news.update');
    Route::put('/news/remove-attachment/{id}', [NewsController::class, 'removeAttachment']);    
    Route::get('/news/{id}/check-approval/{action}', [NewsController::class, 'checkApproval']);

    Route::get('/personnels', [PersonnelController::class, 'index'])->name('personnels');
    Route::get('/personnels/json', [PersonnelController::class, 'json'])->name('personnels.json');
    Route::get('/createpersonnels', [PersonnelController::class, 'createPersonnel']);
    Route::post('/personnels', [PersonnelController::class, 'storePersonnel'])->name('personnels.store');
    Route::get('/showpersonnels/{id}', [PersonnelController::class, 'showPersonnel']);
    Route::get('/personnel/{id}/comments', [PersonnelController::class, 'fetchComments']);
    Route::post('/personnel/{id}/comments', [PersonnelController::class, 'storeComment']);
    Route::post('/personnel/{id}/approve', [PersonnelController::class, 'approvePersonnel']);
    Route::post('/personnel/{id}/reject', [PersonnelController::class, 'rejectPersonnel']);
    Route::post('/personnel/{id}/revise', [PersonnelController::class, 'revisePersonnel']);
    Route::get('/editpersonnels/{id}', [PersonnelController::class, 'editPersonnel']);
    Route::put('/personnels/{id}', [PersonnelController::class, 'updatePersonnel'])->name('personnels.update');
    Route::put('/personnels/remove-attachment/{id}', [PersonnelController::class, 'removeAttachment']);    
    Route::get('/personnel/{id}/check-approval/{action}', [PersonnelController::class, 'checkApproval']);   
    Route::get('/api/sites/{cpnyid}', [PersonnelController::class, 'getSitesByCompany']);
    Route::get('/api/job-parent-info/{parentId}/{departementId}/{deptId}', [PersonnelController::class, 'getParentJobInfo']);
    Route::get('/api/vacant-employees/{deptId}', [PersonnelController::class, 'getVacantByTopParent']);
    Route::get('/api/replacement-employees/{deptname}', [PersonnelController::class, 'getReplacementByTopParent']);
    Route::get('/createpersonnelsx', [PersonnelController::class, 'createPersonnelx']);
    Route::get('/api/job-parent-info/{parentId}/{departementId}/{deptId}', [PersonnelController::class, 'getParentJobInfo']);
    Route::get('/api/job-parent-info-edit/{parentId}/{departementId}/{deptId}', [PersonnelController::class, 'getJobParentInfoEdit']);

    Route::get('/tasks', [ProjectTaskController::class, 'index'])->name('tasks');
    Route::get('/tasks/json', [ProjectTaskController::class, 'json'])->name('tasks.json');
    Route::get('/createtasks', [ProjectTaskController::class, 'createTask']);
    Route::post('/tasks', [ProjectTaskController::class, 'storeTask'])->name('tasks.store');
    Route::get('/showtasks/{id}', [ProjectTaskController::class, 'showTask']);
    Route::get('/task/{id}/comments', [ProjectTaskController::class, 'fetchComments']);
    Route::post('/task/{id}/comments', [ProjectTaskController::class, 'storeComment']);
    Route::post('/task/{id}/approve', [ProjectTaskController::class, 'approveTask']);
    Route::post('/task/{id}/reject', [ProjectTaskController::class, 'rejectTask']);
    Route::post('/task/{id}/revise', [ProjectTaskController::class, 'reviseTask']);
    Route::get('/edittasks/{id}', [ProjectTaskController::class, 'editTask']);
    Route::put('/tasks/{id}', [ProjectTaskController::class, 'updateTask'])->name('tasks.update');
    Route::put('/tasks/remove-attachment/{id}', [ProjectTaskController::class, 'removeAttachment']);    
    Route::get('/task/{id}/check-approval/{action}', [ProjectTaskController::class, 'checkApproval']);

    Route::get('/assignwo', [WorkInstructionController::class, 'assignWo'])->name('assignwo');
    Route::get('/workinstruction', [WorkInstructionController::class, 'workInstruction'])->name('workinstruction');
    Route::post('/wi/store', [WorkInstructionController::class, 'storeWi'])->name('wi.store');
    Route::get('/wi/complaints', [WorkInstructionController::class, 'getComplaintTypes'])->name('wi.complaints');
    Route::get('/wi/wotype', [WorkInstructionController::class, 'getWoTypes'])->name('wi.wotype');
    Route::get('/wi/subworktype', [WorkInstructionController::class, 'getSubWorkTypes'])->name('wi.subworktype');
    Route::get('/wi/locations', [WorkInstructionController::class, 'getLocations'])->name('wi.locations');
    Route::get('/wi/sublocations', [WorkInstructionController::class, 'getSubLocations'])->name('wi.sublocations');
    Route::get('/wi/workers', [WorkInstructionController::class, 'getWorkers'])->name('wi.workers');


    Route::get('/manpowers', [ManpowerController::class, 'index'])->name('manpowers');
    Route::get('/manpowers/json', [ManpowerController::class, 'json'])->name('manpowers.json');
    Route::get('/createmanpowers', [ManpowerController::class, 'createManpower']);
    Route::post('/manpowers', [ManpowerController::class, 'storeManpower'])->name('manpowers.store');
    Route::get('/showmanpowers/{id}', [ManpowerController::class, 'showManpower']);
    Route::get('/manpower/{id}/comments', [ManpowerController::class, 'fetchComments']);
    Route::post('/manpower/{id}/comments', [ManpowerController::class, 'storeComment']);
    Route::post('/manpower/{id}/approve', [ManpowerController::class, 'approveManpower']);
    Route::post('/manpower/{id}/reject', [ManpowerController::class, 'rejectManpower']);
    Route::post('/manpower/{id}/revise', [ManpowerController::class, 'reviseManpower']);
    Route::get('/editmanpowers/{id}', [ManpowerController::class, 'editManpower']);
    Route::put('/manpowers/{id}', [ManpowerController::class, 'updateManpower'])->name('manpowers.update');
    Route::put('/manpowers/remove-attachment/{id}', [ManpowerController::class, 'removeAttachment']);    
    Route::get('/manpower/{id}/check-approval/{action}', [ManpowerController::class, 'checkApproval']);

    Route::get('/agendas', [AgendaController::class, 'index'])->name('agendas');
    Route::get('/agendas/json', [AgendaController::class, 'json'])->name('agendas.json');
    Route::get('/createagendas', [AgendaController::class, 'createAgenda']);
    Route::post('/agendas', [AgendaController::class, 'storeAgenda'])->name('agendas.store');
    Route::get('/showagendas/{id}', [AgendaController::class, 'showAgenda']);
    Route::get('/agenda/{id}/comments', [AgendaController::class, 'fetchComments']);
    Route::post('/agenda/{id}/comments', [AgendaController::class, 'storeComment']);
    Route::post('/agenda/{id}/approve', [AgendaController::class, 'approveAgenda']);
    Route::post('/agenda/{id}/reject', [AgendaController::class, 'rejectAgenda']);
    Route::post('/agenda/{id}/revise', [AgendaController::class, 'reviseAgenda']);
    Route::get('/editagendas/{id}', [AgendaController::class, 'editAgenda']);
    Route::put('/agendas/{id}', [AgendaController::class, 'updateAgenda'])->name('agendas.update');
    Route::put('/agendas/remove-attachment/{id}', [AgendaController::class, 'removeAttachment']);    
    Route::get('/agenda/{id}/check-approval/{action}', [AgendaController::class, 'checkApproval']);
    Route::post('/agendas/cancel', [AgendaController::class, 'cancelAgenda'])->name('agendas.cancel');
    Route::post('/agendas/checkRoomAvailability', [AgendaController::class, 'checkRoomAvailability'])->name('agendas.checkRoomAvailability');
    Route::get('/company-address/{site}', [AgendaController::class, 'getBySite']);

    Route::get('/send_email_all', [AgendaController::class, 'send_email_all'])->name('send_email_all');


    Route::get('/careers', [CareerController::class, 'index'])->name('careers');
    Route::get('/careers/json', [CareerController::class, 'json'])->name('careers.json');
    Route::get('/createcareers', [CareerController::class, 'createCareer']);
    Route::post('/careers', [CareerController::class, 'storeCareer'])->name('careers.store');
    Route::get('/showcareers/{id}', [CareerController::class, 'showCareer']);
    Route::get('/career/{id}/comments', [CareerController::class, 'fetchComments']);
    Route::post('/career/{id}/comments', [CareerController::class, 'storeComment']);
    Route::post('/career/{id}/approve', [CareerController::class, 'approveCareer']);
    Route::post('/career/{id}/reject', [CareerController::class, 'rejectCareer']);
    Route::post('/career/{id}/revise', [CareerController::class, 'reviseCareer']);
    Route::get('/editcareers/{id}', [CareerController::class, 'editCareer']);
    Route::put('/careers/{id}', [CareerController::class, 'updateCareer'])->name('careers.update');
    Route::put('/careers/remove-attachment/{id}', [CareerController::class, 'removeAttachment']);    
    Route::get('/career/{id}/check-approval/{action}', [CareerController::class, 'checkApproval']);
    Route::post('/checklist/upload', [CareerController::class, 'uploadDocument'])->name('checklist.upload');
    Route::post('/assessment/update', [CareerController::class, 'updateAssessment'])->name('assessment.update');
    Route::post('/assessmentuser/update', [CareerController::class, 'updateAssessmentuser'])->name('assessmentuser.update');
    Route::get('/career/{docid}/check-reject-permission', [CareerController::class, 'checkRejectPermission']);
    // Route::get('/payroll-confirmation', [CareerController::class, 'index'])->name('payroll.index');
    Route::post('/payroll-confirmation/generate', [CareerController::class, 'generatePayroll'])->name('payroll.generate');
    Route::post('/offering-letter/generate', [CareerController::class, 'generateOffering'])->name('offering.generate');
    Route::post('/payroll-confirmation/pdf', [CareerController::class, 'pdfPayrollconfirmation'])->name('payrollconfirmation.pdf');
    Route::post('/offering-letter/pdf', [CareerController::class, 'pdfOfferingletter'])->name('offeringletter.pdf');
    Route::post('/pakta-integritas/pdf', [CareerController::class, 'pdfPaktaintegritas'])->name('paktaintegritas.pdf');
    Route::post('/pernyataan-electonik/pdf', [CareerController::class, 'pdfPernyataanelectonik'])->name('pernyataanelectonik.pdf');
    Route::get('/careers/stats', [CareerController::class, 'stats'])->name('careers.stats');   
    Route::post('/payrollconfirm/store', [CareerController::class, 'storePayroll'])->name('payrollconfirm.store');
    Route::post('/payrollconfirm/update', [CareerController::class, 'updatePayroll'])->name('payrollconfirm.update');
    Route::get('/payrollconfirm/{id}', [CareerController::class, 'editPayroll'])->name('payrollconfirm.edit');
    Route::get('/onboarding/{docid_onboarding}', [CareerController::class, 'getChecklist'])->name('onboarding.checklist');
    Route::post('/onboarding/update', [CareerController::class, 'updateChecklist'])->name('onboarding.checklist.update');
    Route::post('/applicant-profile/pdf', [CareerController::class, 'pdfApplicantprofile'])->name('applicantprofile.pdf');

    Route::post('/signconfirm/store', [CareerController::class, 'storeSign'])->name('signconfirm.store');
    Route::post('/signconfirm/update', [CareerController::class, 'updateSign'])->name('signconfirm.update');
    Route::get('/signconfirm/{id}', [CareerController::class, 'editSign'])->name('signconfirm.edit');
    Route::delete('/signconfirm/{id}', [CareerController::class, 'destroySign'])->name('signconfirm.destroy');
   
    Route::post('/onboarding/schedule/update', [CareerController::class, 'updateSchedule'])->name('onboarding.schedule.update');

    Route::post('/payrollconfirm/reveal', [CareerController::class, 'revealSalary'])->name('payrollconfirm.reveal');
    Route::get('/payrollconfirm/{id}', [CareerController::class, 'getPayroll'])->name('payrollconfirm.get');





    Route::get('/jobpostings', [JobpostingController::class, 'index'])->name('jobpostings');
    Route::get('/jobpostings/json', [JobpostingController::class, 'json'])->name('jobpostings.json'); 
    Route::get('/showjobpostings/{id}', [JobpostingController::class, 'showJobposting']);

    Route::get('/jobapplicant', [JobapplicantController::class, 'index'])->name('jobapplicant');
    Route::get('/jobapplicant/json', [JobapplicantController::class, 'json'])->name('jobapplicant.json'); 
    Route::get('/jobapplicant/applicants/{jobId}', [JobapplicantController::class, 'JobApplicants'])->name('jobapplicant.applicants');
    // Route::get('/jobapplicant/counts', [JobapplicantController::class, 'getCounts'])->name('jobapplicant.counts');
    
    Route::get('/job-filters/tl', [JobapplicantController::class, 'jobTitleLevels'])->name('jobfilters.tl');



    Route::get('/applicants', [ApplicantController::class, 'index'])->name('applicants');    
    Route::get('/applicants/json', [ApplicantController::class, 'json'])->name('applicants.json'); 
    Route::get('/showapplicants/{id}', [ApplicantController::class, 'showApplicant']);    

    Route::get('/assessments', [AssessmentController::class, 'index'])->name('assessments');
    Route::get('/assessments/json', [AssessmentController::class, 'json'])->name('assessments.json'); // Untuk Fetch API
    Route::post('/assessments', [AssessmentController::class, 'store'])->name('assessments.store');
    Route::get('/assessments/{id}/edit', [AssessmentController::class, 'edit'])->name('assessments.edit');
    Route::put('/assessments/{post}', [AssessmentController::class, 'update'])->name('assessments.update');
    Route::put('/assessments/{id}/toggle-status', [AssessmentController::class, 'toggleStatus']);

    // Route::get('/orgchart', [OrgChartController::class, 'getData'])->name('orgchart.data');
    // Route::get('/orgchart', [OrgChartController::class, 'index'])->name('orgchart.index');
    // Route::get('/orgchart/json', [OrgChartController::class, 'json'])->name('orgchart.json');
    // Route::post('/orgchart/store', [OrgChartController::class, 'store'])->name('orgchart.store');
    // Route::get('/orgchart/employee/by-dept/{dept_id}', [OrgChartController::class, 'getEmployeesByDept']);
    // Route::post('/sto/storehd', [OrgChartController::class, 'storeDraft'])->name('sto.storehd');



    Route::get('/stos', [StrukturOrgController::class, 'index'])->name('stos');
    Route::get('/stos/json', [StrukturOrgController::class, 'json'])->name('stos.json');
    Route::get('/createstos', [StrukturOrgController::class, 'createSto']);
    Route::post('/stos', [StrukturOrgController::class, 'storeSto'])->name('stos.store');
    Route::get('/showstos/{id}', [StrukturOrgController::class, 'showSto']);
    Route::get('/sto/{id}/comments', [StrukturOrgController::class, 'fetchComments']);
    Route::post('/sto/{id}/comments', [StrukturOrgController::class, 'storeComment']);
    Route::post('/sto/{id}/approve', [StrukturOrgController::class, 'approveSto']);
    Route::post('/sto/{id}/reject', [StrukturOrgController::class, 'rejectSto']);
    Route::post('/sto/{id}/revise', [StrukturOrgController::class, 'reviseSto']);
    Route::get('/editstos/{id}', [StrukturOrgController::class, 'editSto']);
    Route::put('/stos/{id}', [StrukturOrgController::class, 'updateSto'])->name('stos.update');
    Route::put('/stos/remove-attachment/{id}', [StrukturOrgController::class, 'removeAttachment']);    
    Route::get('/sto/{id}/check-approval/{action}', [StrukturOrgController::class, 'checkApproval']);
    Route::get('/orgchart/json', [StrukturOrgController::class, 'jsonOrg'])->name('orgchart.json');
    Route::post('/orgchart/store', [StrukturOrgController::class, 'storeOrg'])->name('orgchart.store');
    Route::get('/orgchart/employee/by-dept/{dept_id}', [StrukturOrgController::class, 'getEmployeesByDept']);
    Route::post('/orgchart/employee/update/{id}', [StrukturOrgController::class, 'updateEmployee']);
    Route::delete('/orgchart/employee/delete/{id}', [StrukturOrgController::class, 'deleteEmployee']);
    Route::post('/orgchart/employee/change-dept', [StrukturOrgController::class, 'changeEmployeeDepartment'])->name('orgchart.change-dept');
    Route::get('/stoall', [StrukturOrgController::class, 'stoall'])->name('stoall');
    Route::get('/orgchartall/json', [StrukturOrgController::class, 'jsonOrgall'])->name('orgchartall.json');
    Route::get('/orgchart/by-dept/{deptname}', [StrukturOrgController::class, 'jsonOrgByDept'])->name('orgchart.by-dept');   
    Route::get('/orgchart/show/{sto}', [StrukturOrgController::class, 'jsonOrgShow'])->name('orgchartShow.json');
    Route::get('/orgchart/job-profile/{id}', [StrukturOrgController::class, 'getJobProfile']);
    Route::delete('/orgchart/job-profile/{id}', [StrukturOrgController::class, 'deleteJobProfile']);
    Route::post('/orgchart/change-parent', [StrukturOrgController::class, 'changeParent'])->name('orgchart.change-parent');
    Route::get('/departement/detail/{id}', [StrukturOrgController::class, 'getDepartmentDetail']);
    Route::get('/orgchart/fullscreen/{sto}', [StrukturOrgController::class, 'fullscreen'])->name('orgchart.fullscreen'); 


    
    Route::get('/changestos', [ChangeStoController::class, 'index'])->name('changestos');
    Route::get('/changestos/json', [ChangeStoController::class, 'json'])->name('changestos.json');
    Route::get('/createchangestos', [ChangeStoController::class, 'createChangesto']);
    Route::post('/changestos', [ChangeStoController::class, 'storeChangesto'])->name('changestos.store');
    Route::get('/showchangestos/{id}', [ChangeStoController::class, 'showChangesto']);
    Route::get('/changesto/{id}/comments', [ChangeStoController::class, 'fetchComments']);
    Route::post('/changesto/{id}/comments', [ChangeStoController::class, 'storeComment']);
    Route::post('/changesto/{id}/approve', [ChangeStoController::class, 'approveChangesto']);
    Route::post('/changesto/{id}/reject', [ChangeStoController::class, 'rejectChangesto']);
    Route::post('/changesto/{id}/revise', [ChangeStoController::class, 'reviseChangesto']);
    Route::get('/editchangestos/{id}', [ChangeStoController::class, 'editChangesto']);
    Route::put('/changestos/{id}', [ChangeStoController::class, 'updateChangesto'])->name('changestos.update');
    Route::put('/changestos/remove-attachment/{id}', [ChangeStoController::class, 'removeAttachment']);    
    Route::get('/changesto/{id}/check-approval/{action}', [ChangeStoController::class, 'checkApproval']);   

    Route::get('/budgets', [BudgetController::class, 'index'])->name('budgets');
    Route::get('/budgets/json', [BudgetController::class, 'json'])->name('budgets.json');
    Route::get('/createbudgets', [BudgetController::class, 'createBudget'])->name('budget.create');
    Route::post('/budgets', [BudgetController::class, 'storeBudget'])->name('budgets.store');
    Route::get('/showbudgets/{hash}', [BudgetController::class, 'showBudget']);
    Route::get('/budget/{id}/comments', [BudgetController::class, 'fetchComments']);
    Route::post('/budget/{id}/comments', [BudgetController::class, 'storeComment']);
    Route::post('/budget/{id}/approve', [BudgetController::class, 'approveBudget']);
    Route::post('/budget/{id}/reject', [BudgetController::class, 'rejectBudget']);
    Route::post('/budget/{id}/revise', [BudgetController::class, 'reviseBudget']);
    Route::get('/editbudgets/{hash}', [BudgetController::class, 'editBudget'])->name('budget.edit');
    Route::put('/budgets/{id}', [BudgetController::class, 'updateBudget'])->name('budgets.update');
    Route::put('/budgets/remove-attachment/{id}', [BudgetController::class, 'removeAttachment']);    
    Route::get('/budget/{id}/check-approval/{action}', [BudgetController::class, 'checkApproval']);  
    Route::get('/get-business-units/{cpny_id}', [BudgetController::class, 'getBusinessUnits']);  

    Route::post('/budgets/import', [BudgetController::class, 'import'])->name('budgets.import');
    Route::post('/budgets/{budget}/import', [BudgetController::class, 'import'])->name('budgets.import.edit');

    Route::get('/sppbs', [SppbController::class, 'index'])->name('sppbs');
    Route::get('/sppbs/json', [SppbController::class, 'json'])->name('sppbs.json');
    Route::get('/createsppbs', [SppbController::class, 'createSppb']);
    Route::post('/sppbs', [SppbController::class, 'storeSppb'])->name('sppbs.store');
    Route::get('/showsppbs/{hash}', [SppbController::class, 'showSppb']);
    Route::get('/sppb/{id}/comments', [SppbController::class, 'fetchComments']);
    Route::post('/sppb/{id}/comments', [SppbController::class, 'storeComment']);
    Route::post('/sppb/{id}/approve', [SppbController::class, 'approveSppb']);
    Route::post('/sppb/{id}/reject', [SppbController::class, 'rejectSppb']);
    Route::post('/sppb/{id}/revise', [SppbController::class, 'reviseSppb']);
    Route::get('/editsppbs/{hash}', [SppbController::class, 'editSppb']);
    Route::put('/sppbs/{id}', [SppbController::class, 'updateSppb'])->name('sppbs.update');
    Route::put('/sppbs/remove-attachment/{id}', [SppbController::class, 'removeAttachment']);    
    Route::get('/sppb/{id}/check-approval/{action}', [SppbController::class, 'checkApproval']);     
    Route::get('/sppbs/{id}/tracking', [SppbController::class, 'tracking'])->name('sppbs.tracking');
    Route::get('/pdf_sppbs/{hash}', [SppbController::class, 'printSppb']);

    Route::get('/sppjs', [SppjController::class, 'index'])->name('sppjs');
    Route::get('/sppjs/json', [SppjController::class, 'json'])->name('sppjs.json');
    Route::get('/createsppjs', [SppjController::class, 'createSppj']);
    Route::post('/sppjs', [SppjController::class, 'storeSppj'])->name('sppjs.store');
    Route::get('/showsppjs/{hash}', [SppjController::class, 'showSppj']);
    Route::get('/sppj/{id}/comments', [SppjController::class, 'fetchComments']);
    Route::post('/sppj/{id}/comments', [SppjController::class, 'storeComment']);
    Route::post('/sppj/{id}/approve', [SppjController::class, 'approveSppj']);
    Route::post('/sppj/{id}/reject', [SppjController::class, 'rejectSppj']);
    Route::post('/sppj/{id}/revise', [SppjController::class, 'reviseSppj']);
    Route::get('/editsppjs/{hash}', [SppjController::class, 'editSppj']);
    Route::put('/sppjs/{id}', [SppjController::class, 'updateSppj'])->name('sppjs.update');
    Route::put('/sppjs/remove-attachment/{id}', [SppjController::class, 'removeAttachment']);    
    Route::get('/sppj/{id}/check-approval/{action}', [SppjController::class, 'checkApproval']);     
    Route::get('/sppjs/{id}/tracking', [SppjController::class, 'tracking'])->name('sppjs.tracking');
    Route::get('/showbqsppjs/{hash}', [SppjController::class, 'showBQ']);
    Route::get('/editbqsppjs/{id}', [SppjController::class, 'editBQ'])->name('bqsppj.edit');    
    Route::put('/bqs/remove-attachment/{id}', [SppjController::class, 'removeAttachment']);
    Route::get('/pdf_sppjs/{hash}', [SppjController::class, 'printSppj']);

    Route::get('/createbqsppj/{id}', [SppjController::class, 'createBQ'])->name('bqsppj.create');   
    Route::post('/bqsppj/import', [SppjController::class, 'importCreate'])->name('bqsppj.import');
    Route::post('/bqsppj/{bq}/import', [SppjController::class, 'importEdit'])->name('bqsppj.import.edit');
    Route::post('/bqsppj', [SppjController::class, 'storeBQ'])->name('bqsppj.store');
    Route::put('/bqsppj/{id}', [SppjController::class, 'updateBQ'])->name('bqsppj.update');

    Route::get('/kendaraan/all', [MasterController::class, 'listKendaraan'])->name('kendaraan.all');
    Route::get('/lookup/tenants', [MasterController::class, 'tenants'])->name('tenants.search');
    Route::get('/lookup/users',   [MasterController::class, 'users'])->name('users.search');
    Route::get('/vendorscs', [MasterController::class, 'vendors']); 
    Route::get('/taxes', [MasterController::class, 'taxes'])->name('taxes.index');

    Route::get('/sppts', [SpptController::class, 'index'])->name('sppts');
    Route::get('/sppts/json', [SpptController::class, 'json'])->name('sppts.json');
    Route::get('/createsppts', [SpptController::class, 'createSppt']);
    Route::post('/sppts', [SpptController::class, 'storeSppt'])->name('sppts.store');
    Route::get('/showsppts/{hash}', [SpptController::class, 'showSppt']);
    Route::get('/sppt/{id}/comments', [SpptController::class, 'fetchComments']);
    Route::post('/sppt/{id}/comments', [SpptController::class, 'storeComment']);
    Route::post('/sppt/{id}/approve', [SpptController::class, 'approveSppt']);
    Route::post('/sppt/{id}/reject', [SpptController::class, 'rejectSppt']);
    Route::post('/sppt/{id}/revise', [SpptController::class, 'reviseSppt']);
    Route::get('/editsppts/{hash}', [SpptController::class, 'editSppt']);
    Route::put('/sppts/{id}', [SpptController::class, 'updateSppt'])->name('sppts.update');
    Route::put('/sppts/remove-attachment/{id}', [SpptController::class, 'removeAttachment']);    
    Route::get('/sppt/{id}/check-approval/{action}', [SpptController::class, 'checkApproval']);     
    Route::get('/sppts/{id}/tracking', [SpptController::class, 'tracking'])->name('sppts.tracking');
    Route::get('/showbqsppts/{hash}', [SpptController::class, 'showBQ']);
    Route::get('/editbqsppts/{id}', [SpptController::class, 'editBQ'])->name('bqsppt.edit');    
    Route::put('/bqs/remove-attachment/{id}', [SpptController::class, 'removeAttachment']);
    Route::get('/pdf_sppts/{hash}', [SpptController::class, 'printSppt']);

    Route::get('/createbqsppt/{id}', [SpptController::class, 'createBQ'])->name('bqsppt.create');   
    Route::post('/bqsppt/import', [SpptController::class, 'importCreate'])->name('bqsppt.import');
    Route::post('/bqsppt/{bq}/import', [SpptController::class, 'importEdit'])->name('bqsppt.import.edit');
    Route::post('/bqsppt', [SpptController::class, 'storeBQ'])->name('bqsppt.store');
    Route::put('/bqsppt/{id}', [SpptController::class, 'updateBQ'])->name('bqsppt.update');

    Route::get('/sppks', [SppkController::class, 'index'])->name('sppks');
    Route::get('/sppks/json', [SppkController::class, 'json'])->name('sppks.json');
    Route::get('/createsppks', [SppkController::class, 'createSppk']);
    Route::post('/sppks', [SppkController::class, 'storeSppk'])->name('sppks.store');
    Route::get('/showsppks/{hash}', [SppkController::class, 'showSppk']);
    Route::get('/sppk/{id}/comments', [SppkController::class, 'fetchComments']);
    Route::post('/sppk/{id}/comments', [SppkController::class, 'storeComment']);
    Route::post('/sppk/{id}/approve', [SppkController::class, 'approveSppk']);
    Route::post('/sppk/{id}/reject', [SppkController::class, 'rejectSppk']);
    Route::post('/sppk/{id}/revise', [SppkController::class, 'reviseSppk']);
    Route::get('/editsppks/{hash}', [SppkController::class, 'editSppk']);
    Route::put('/sppks/{id}', [SppkController::class, 'updateSppk'])->name('sppks.update');
    Route::put('/sppks/remove-attachment/{id}', [SppkController::class, 'removeAttachment']);    
    Route::get('/sppk/{id}/check-approval/{action}', [SppkController::class, 'checkApproval']);     
    Route::get('/sppks/{id}/tracking', [SppkController::class, 'tracking'])->name('sppks.tracking');
    Route::get('/pdf_sppks/{hash}', [SppkController::class, 'printSppk']);

    Route::get('/receivedlist', [ReceivedListController::class, 'ReceivedList'])->name('receivedlist');
    Route::get('/receivedlist/json', [ReceivedListController::class, 'ReceivedListJson'])->name('receivedlist.json');
    Route::get('/receivedlist/users', [ReceivedListController::class, 'ReceivedListUsers'])->name('receivedlist.users');
    Route::post('/receivedlist/assign', [ReceivedListController::class, 'AssignPurchasing'])->name('receivedlist.assign');

    Route::get('/csjobs', [CsJobController::class, 'CsJobs'])->name('csjobs');   
    Route::get('/csjobs/mine/json', [CsJobController::class, 'CsJobsMineJson'])->name('csjobs.mine.json');                 
    Route::get('/csjobs/all/json',  [CsJobController::class, 'CsJobsAllJson'])->name('csjobs.all.json');                   
    Route::get('/csjobs/revision/json', [CsJobController::class, 'CsJobsRevisionJson'])->name('csjobs.revision.json');     
    Route::get('/csjobs/sppbjkt-progress/json', [CsJobController::class, 'SppbjktOnProgressJson'])->name('csjobs.sppbjkt.progress.json'); 
    Route::get('/csjobs/counts', [CsJobController::class,'CsJobsCounts'])->name('csjobs.counts');    
    Route::get('/csjobs/entry.json', [CsJobController::class, 'CsJobsEntryJson'])->name('csjobs.entry.json')->middleware('auth');
    Route::get('/editcs/{eid}', [CsJobController::class, 'editCS'])->name('csjobs.edit');      
    Route::put('/csjobs/{csid}', [CsJobController::class, 'updateCS'])->name('csjobs.update');
    Route::put('/csjobs/remove-attachment/{id}', [CsJobController::class, 'removeAttachment']);

    
    // Route::get('/cslist', [CsListController::class, 'index'])->name('cslist');
    // Route::get('/cslist/my.json',         [CsListController::class, 'jsonMy'])->name('cslist.my.json');
    // Route::get('/cslist/onprogress.json', [CsListController::class, 'jsonOnprogress'])->name('cslist.onprogress.json');
    // Route::get('/cslist/rejected.json',   [CsListController::class, 'jsonRejected'])->name('cslist.rejected.json');
    // Route::get('/cslist/completed.json',  [CsListController::class, 'jsonCompleted'])->name('cslist.completed.json');
    // Route::get('/cslist/all.json',        [CsListController::class, 'jsonAll'])->name('cslist.all.json');
    // Route::get('/cslist/counts',          [CsListController::class, 'counts'])->name('cslist.counts');

    Route::get('/cslist', [CsListController::class, 'index'])->name('cslist');
    Route::get('/cslist/json', [CsListController::class, 'json'])->name('cslist.json');



    Route::get('/createcs/{doc}/{hash}', [CanvassController::class, 'createCS'])
        ->where(['doc' => 'SPPB|SPPJ|SPPK|SPPT', 'src' => '[0-9]+'])
        ->name('canvass.createcs');
    Route::post('/csstore', [CanvassController::class, 'storeCS'])->name('cs.store');
    Route::post('/cssave', [CanvassController::class, 'saveCS'])->name('cs.save');
    Route::get('/showcs/{hash}', [CanvassController::class, 'showCS']);
    Route::get('/cs/{id}/comments', [CanvassController::class, 'fetchComments']);
    Route::post('/cs/{id}/comments', [CanvassController::class, 'storeComment']);
    Route::post('/cs/{id}/approve', [CanvassController::class, 'approveCS']);
    Route::post('/cs/{id}/reject', [CanvassController::class, 'rejectCS']);
    Route::post('/cs/{id}/revise', [CanvassController::class, 'reviseCS']);
    // Route::get('/editcs/{id}', [CanvassController::class, 'editCS']);
    // Route::put('/cs/{id}', [CanvassController::class, 'updateCS'])->name('cs.update');
    Route::put('/cs/remove-attachment/{id}', [CanvassController::class, 'removeAttachment']);    
    Route::get('/cs/{id}/check-approval/{action}', [CanvassController::class, 'checkApproval']); 
    Route::get('/pdf_cs/{hash}', [CanvassController::class, 'printCS']);
        
    Route::get('/bqcs/create-from-cs/{hash}', [BQCSController::class, 'createFromCS'])->name('bqcs.createFromCS');
    Route::post('/bqcs', [BQCSController::class, 'storeBQCS'])->name('bqcs.store');
    Route::get('/bqcs/edit/{hash}', [BQCSController::class, 'EditBQCS'])->name('bqcs.edit');
    // Route::post('/bqcs/update/{hash}', [BQCSController::class, 'updateBQCS'])->name('bqcs.update');
    Route::put('bqcs/update/{hash}', [BQCSController::class, 'updateBQCS'])->name('bqcs.update');



    Route::get('/polist', [PoListController::class, 'index'])->name('polist');
    Route::get('/polist/json', [PoListController::class, 'json'])->name('polist.json');
    Route::get('/showpo/{hash}', [PoController::class, 'showPo']);
    Route::get('/po/{id}/comments', [PoController::class, 'fetchComments']);
    Route::post('/po/{id}/comments', [PoController::class, 'storeComment']);
    Route::post('/po/{poid}/attachments', [PoController::class, 'uploadAttachments'])->name('po.attachments.upload');
   
    Route::get('/po/{ponbr}/attachments', [PoController::class, 'listAttachment'])->name('po.attachments.list');
    Route::post('/po/{ponbr}/attachments', [PoController::class, 'uploadAttachments'])->name('po.attachments.upload');
    Route::delete('/po/attachments/{id}', [PoController::class, 'removeAttachment'])->name('po.attachments.delete');

 
    Route::post('/po/{poid}/submit',       [PoController::class, 'submitPO'])->name('po.submit');
    Route::post('/po/{poid}/cancel-reuse', [PoController::class, 'cancelReuse'])->name('po.cancel_reuse');
    Route::post('/po/{poid}/cancel',       [PoController::class, 'cancel'])->name('po.cancel');
    Route::get('/pdf_po/{hash}', [PoController::class, 'printPO']);
    Route::get('/po/{ponbr}/view-email', [POController::class, 'viewEmailPO'])->name('po.viewemail');
    Route::post('/po/{ponbr}/email/send', [POController::class, 'sendNowPO'])->name('po.email.send');


    Route::get('/inventory/list', [MasterController::class, 'InventoryList'])->name('inventory.list');
    Route::get('/request-types/by-doctype', [MasterController::class, 'RequestType'])->name('requesttypes.byDoctype');
    Route::get('/locations/by-company', [MasterController::class, 'Location'])->name('locations.byCompany'); 
    Route::get('/sublocations/by-location', [MasterController::class, 'SubLocation'])->name('sublocations.byLocation');
    Route::get('/departments/{cpny_id}', [MasterController::class, 'DepartmentFin'])->name('finance.departments.byCompany');
    Route::get('/coa/by-dept', [MasterController::class, 'CoaBudget'])->name('coa.byDept');   
    Route::get('/uom/by-inventory', [MasterController::class, 'UomInventory'])->name('uom.byInventory');

    Route::get('/eng/users', [UsersEngController::class, 'index'])->name('userseng');
    Route::get('/eng/users/json', [UsersEngController::class, 'json'])->name('userseng.json');
    Route::post('/eng/users', [UsersEngController::class, 'store'])->name('userseng.store');
    Route::get('/eng/users/{id}/edit', [UsersEngController::class, 'edit'])->name('userseng.edit');
    Route::put('/eng/users/{post}', [UsersEngController::class, 'update'])->name('userseng.update');
    Route::put('/eng/users/{id}/toggle-status', [UsersEngController::class, 'toggleStatus']);

    Route::get('/eng/assetslocation', [AssetsLocationController::class, 'index'])->name('assetslocation');
    Route::get('/eng/assetslocation/json', [AssetsLocationController::class, 'json'])->name('assetslocation.json');
    Route::post('/eng/assetslocation', [AssetsLocationController::class, 'store'])->name('assetslocation.store');
    Route::get('/eng/assetslocation/{id}/edit', [AssetsLocationController::class, 'edit'])->name('assetslocation.edit');
    Route::put('/eng/assetslocation/{post}', [AssetsLocationController::class, 'update'])->name('assetslocation.update');
    Route::put('/eng/assetslocation/{id}/toggle-status', [AssetsLocationController::class, 'toggleStatus']);

    Route::get('/eng/workscategory', [WorksCategoryController::class, 'index'])->name('workscategory');
    Route::get('/eng/workscategory/tree-json', [WorksCategoryController::class, 'treeJson'])->name('workscategory.tree-json');
    Route::post('/eng/workscategory/store', [WorksCategoryController::class, 'store']);
    Route::post('/eng/workscategory/update', [WorksCategoryController::class, 'update'])->name('workscategory.update');
    Route::post('/eng/workscategory/delete/{id}', [WorksCategoryController::class, 'delete']);

    



    Route::get('/canvasssheet', [BudgetController::class, 'CanvassSheet'])->name('canvasssheet');
    Route::get ('/canvass/create', [CanvassxController::class, 'createCS'])->name('canvass.create');
    Route::get('/vendors', [VendorController::class, 'index']);  
   

    
    // Route for the getting the data feed
    Route::get('/json-data-feed', [DataFeedController::class, 'getDataFeed'])->name('json_data_feed');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/waitingapproval', [DashboardController::class, 'waitingApproval'])->name('waitingapproval');
    Route::get('/waitingapproval/json', [DashboardController::class, 'Waitingjson'])->name('waitingapproval.json');
    Route::get('/dashapproval/json', [DashboardController::class, 'Approvejson'])->name('dashapproval.json');
    Route::get('/mastercard', [DashboardController::class, 'analytics'])->name('mastercard');

    Route::get('/test', [DashboardController::class, 'test'])->name('test');
    


    // Route::get('/settings/account', function () {
    //     return view('profile/show');
    // })->name('account');
    Route::get('/settings/account', [DashboardController::class, 'showProfile'])->name('profile.showx');

    Route::get('/settings/notifications', function () {
        return view('pages/settings/notifications');
    })->name('notifications');

    Route::get('/users', [UsersController::class, 'index'])->name('users');
    Route::get('/users/json', [UsersController::class, 'json'])->name('users.json');
    Route::post('/users', [UsersController::class, 'store'])->name('users.store');
    Route::get('/users/{id}/edit', [UsersController::class, 'edit'])->name('users.edit');
    Route::put('/users/{post}', [UsersController::class, 'update'])->name('users.update');
    Route::put('/users/{id}/toggle-status', [UsersController::class, 'toggleStatus']);
    Route::post('/settings/password', [UsersController::class, 'updatePassword'])->name('password.update.custom');

  
    


});
