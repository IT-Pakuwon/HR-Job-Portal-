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
use App\Http\Controllers\CanvassController;


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
    Route::get('/onboarding/checklist/{docid_onboarding}', [CareerController::class, 'getChecklist'])->name('onboarding.checklist');
    Route::post('/onboarding/checklist/update', [CareerController::class, 'updateChecklist'])->name('onboarding.checklist.update');
    Route::post('/applicant-profile/pdf', [CareerController::class, 'pdfApplicantprofile'])->name('applicantprofile.pdf');



    Route::get('/jobpostings', [JobpostingController::class, 'index'])->name('jobpostings');
    Route::get('/jobpostings/json', [JobpostingController::class, 'json'])->name('jobpostings.json'); 
    Route::get('/showjobpostings/{id}', [JobpostingController::class, 'showJobposting']);

    Route::get('/jobapplicant', [JobapplicantController::class, 'index'])->name('jobapplicant');
    Route::get('/jobapplicant/json', [JobapplicantController::class, 'json'])->name('jobapplicant.json'); 
    Route::get('/jobapplicant/applicants/{jobId}', [JobapplicantController::class, 'JobApplicants'])->name('jobapplicant.applicants');
    // Route::get('/jobapplicant/counts', [JobapplicantController::class, 'getCounts'])->name('jobapplicant.counts');


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

    Route::get('/budgets', [BudgetController::class, 'index'])->name('budgets');
    Route::get('/budgets/json', [BudgetController::class, 'json'])->name('budgets.json');
    Route::get('/createbudgets', [BudgetController::class, 'createBudget'])->name('budget.create');
    Route::post('/budgets', [BudgetController::class, 'storeBudget'])->name('budgets.store');
    Route::get('/showbudgets/{id}', [BudgetController::class, 'showBudget']);
    Route::get('/budget/{id}/comments', [BudgetController::class, 'fetchComments']);
    Route::post('/budget/{id}/comments', [BudgetController::class, 'storeComment']);
    Route::post('/budget/{id}/approve', [BudgetController::class, 'approveBudget']);
    Route::post('/budget/{id}/reject', [BudgetController::class, 'rejectBudget']);
    Route::post('/budget/{id}/revise', [BudgetController::class, 'reviseBudget']);
    Route::get('/editbudgets/{id}', [BudgetController::class, 'editBudget'])->name('budget.edit');
    Route::put('/budgets/{id}', [BudgetController::class, 'updateBudget'])->name('budgets.update');
    Route::put('/budgets/remove-attachment/{id}', [BudgetController::class, 'removeAttachment']);    
    Route::get('/budget/{id}/check-approval/{action}', [BudgetController::class, 'checkApproval']);   
    // Route::get('/api/sites/{cpnyid}', [BudgetController::class, 'getSitesByCompany']);
    // Route::get('/api/job-parent-info/{parentId}/{departementId}/{deptId}', [BudgetController::class, 'getParentJobInfo']);
    Route::get('/api/vacant-employees/{deptId}', [BudgetController::class, 'getVacantByTopParent']);  
    
    Route::post('/import-budget', [BudgetController::class, 'import'])->name('budget.import.post');
    Route::get('/get-business-units/{cpny_id}', [BudgetController::class, 'getBusinessUnits']);
    Route::post('/import-budget/{budget}', [BudgetController::class, 'import'])->name('budget.import.edit');


    Route::get('/canvasssheet', [BudgetController::class, 'CanvassSheet'])->name('canvasssheet');
    Route::get ('/canvass/create', [CanvassController::class, 'createCS'])->name('canvass.create');
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


    //================================================

    Route::get('/json-data-feed', [DataFeedController::class, 'getDataFeed'])->name('json_data_feed');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/analytics', [DashboardController::class, 'analytics'])->name('analytics');
    Route::get('/dashboard/fintech', [DashboardController::class, 'fintech'])->name('fintech');
    Route::get('/ecommerce/customers', [CustomerController::class, 'index'])->name('customers');
    Route::get('/ecommerce/orders', [OrderController::class, 'index'])->name('orders');
    Route::get('/ecommerce/invoices', [InvoiceController::class, 'index'])->name('invoices');
    Route::get('/ecommerce/shop', function () {
        return view('pages/ecommerce/shop');
    })->name('shop');    
    Route::get('/ecommerce/shop-2', function () {
        return view('pages/ecommerce/shop-2');
    })->name('shop-2');     
    Route::get('/ecommerce/product', function () {
        return view('pages/ecommerce/product');
    })->name('product');
    Route::get('/ecommerce/cart', function () {
        return view('pages/ecommerce/cart');
    })->name('cart');    
    Route::get('/ecommerce/cart-2', function () {
        return view('pages/ecommerce/cart-2');
    })->name('cart-2');    
    Route::get('/ecommerce/cart-3', function () {
        return view('pages/ecommerce/cart-3');
    })->name('cart-3');    
    Route::get('/ecommerce/pay', function () {
        return view('pages/ecommerce/pay');
    })->name('pay');     
    Route::get('/campaigns', [CampaignController::class, 'index'])->name('campaigns');
    Route::get('/community/users-tabs', [MemberController::class, 'indexTabs'])->name('users-tabs');
    Route::get('/community/users-tiles', [MemberController::class, 'indexTiles'])->name('users-tiles');
    Route::get('/community/profile', function () {
        return view('pages/community/profile');
    })->name('profile');
    Route::get('/community/feed', function () {
        return view('pages/community/feed');
    })->name('feed');     
    Route::get('/community/forum', function () {
        return view('pages/community/forum');
    })->name('forum');
    Route::get('/community/forum-post', function () {
        return view('pages/community/forum-post');
    })->name('forum-post');    
    Route::get('/community/meetups', function () {
        return view('pages/community/meetups');
    })->name('meetups');    
    Route::get('/community/meetups-post', function () {
        return view('pages/community/meetups-post');
    })->name('meetups-post');    
    Route::get('/finance/cards', function () {
        return view('pages/finance/credit-cards');
    })->name('credit-cards');
    Route::get('/finance/transactions', [TransactionController::class, 'index01'])->name('transactions');
    Route::get('/finance/transaction-details', [TransactionController::class, 'index02'])->name('transaction-details');
    Route::get('/job/job-listing', [JobController::class, 'index'])->name('job-listing');
    Route::get('/job/job-post', function () {
        return view('pages/job/job-post');
    })->name('job-post');    
    Route::get('/job/company-profile', function () {
        return view('pages/job/company-profile');
    })->name('company-profile');
    Route::get('/messages', function () {
        return view('pages/messages');
    })->name('messages');
    Route::get('/tasks/kanban', function () {
        return view('pages/tasks/tasks-kanban');
    })->name('tasks-kanban');
    Route::get('/tasks/list', function () {
        return view('pages/tasks/tasks-list');
    })->name('tasks-list');       
    Route::get('/inbox', function () {
        return view('pages/inbox');
    })->name('inbox'); 
    Route::get('/calendar', function () {
        return view('pages/calendar');
    })->name('calendar'); 
    Route::get('/settings/account', function () {
        return view('pages/settings/account');
    })->name('account');  
    Route::get('/settings/notifications', function () {
        return view('pages/settings/notifications');
    })->name('notifications');  
    Route::get('/settings/apps', function () {
        return view('pages/settings/apps');
    })->name('apps');
    Route::get('/settings/plans', function () {
        return view('pages/settings/plans');
    })->name('plans');      
    Route::get('/settings/billing', function () {
        return view('pages/settings/billing');
    })->name('billing');  
    Route::get('/settings/feedback', function () {
        return view('pages/settings/feedback');
    })->name('feedback');
    Route::get('/utility/changelog', function () {
        return view('pages/utility/changelog');
    })->name('changelog');  
    Route::get('/utility/roadmap', function () {
        return view('pages/utility/roadmap');
    })->name('roadmap');  
    Route::get('/utility/faqs', function () {
        return view('pages/utility/faqs');
    })->name('faqs');  
    Route::get('/utility/empty-state', function () {
        return view('pages/utility/empty-state');
    })->name('empty-state');  
    Route::get('/utility/404', function () {
        return view('pages/utility/404');
    })->name('404');
    Route::get('/utility/knowledge-base', function () {
        return view('pages/utility/knowledge-base');
    })->name('knowledge-base');
    Route::get('/onboarding-01', function () {
        return view('pages/onboarding-01');
    })->name('onboarding-01');   
    Route::get('/onboarding-02', function () {
        return view('pages/onboarding-02');
    })->name('onboarding-02');   
    Route::get('/onboarding-03', function () {
        return view('pages/onboarding-03');
    })->name('onboarding-03');   
    Route::get('/onboarding-04', function () {
        return view('pages/onboarding-04');
    })->name('onboarding-04');
    Route::get('/component/button', function () {
        return view('pages/component/button-page');
    })->name('button-page');
    Route::get('/component/form', function () {
        return view('pages/component/form-page');
    })->name('form-page');
    Route::get('/component/dropdown', function () {
        return view('pages/component/dropdown-page');
    })->name('dropdown-page');
    Route::get('/component/alert', function () {
        return view('pages/component/alert-page');
    })->name('alert-page');
    Route::get('/component/modal', function () {
        return view('pages/component/modal-page');
    })->name('modal-page'); 
    Route::get('/component/pagination', function () {
        return view('pages/component/pagination-page');
    })->name('pagination-page');
    Route::get('/component/tabs', function () {
        return view('pages/component/tabs-page');
    })->name('tabs-page');
    Route::get('/component/breadcrumb', function () {
        return view('pages/component/breadcrumb-page');
    })->name('breadcrumb-page');
    Route::get('/component/badge', function () {
        return view('pages/component/badge-page');
    })->name('badge-page'); 
    Route::get('/component/avatar', function () {
        return view('pages/component/avatar-page');
    })->name('avatar-page');
    Route::get('/component/tooltip', function () {
        return view('pages/component/tooltip-page');
    })->name('tooltip-page');
    Route::get('/component/accordion', function () {
        return view('pages/component/accordion-page');
    })->name('accordion-page');
    Route::get('/component/icons', function () {
        return view('pages/component/icons-page');
    })->name('icons-page');
    Route::fallback(function() {
        return view('pages/utility/404');
    });  
    


});
