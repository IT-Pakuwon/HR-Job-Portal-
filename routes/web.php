<?php

use App\Http\Controllers\AccessRequestController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\ApprovalDashboardController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AssetsLocationController;
use App\Http\Controllers\AssignListController;
use App\Http\Controllers\AttachmentMasterController;
use App\Http\Controllers\AutonbrController;
use App\Http\Controllers\BastController;
use App\Http\Controllers\BastListController;
use App\Http\Controllers\BookingCarController;
use App\Http\Controllers\BookingCarSetupController;
use App\Http\Controllers\BQCSController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\BudgetMonitorController;
use App\Http\Controllers\BusinessUnitController;
use App\Http\Controllers\CalrController;
use App\Http\Controllers\CalrListController;
use App\Http\Controllers\CalrNonPurchController;
use App\Http\Controllers\CanvassController;
use App\Http\Controllers\CanvassxController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\CarExpenseController;
use App\Http\Controllers\ChangeStoController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CostControlDashboardController;
use App\Http\Controllers\CsJobController;
use App\Http\Controllers\CsListController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentNotificationController;
use App\Http\Controllers\DataFeedController;
use App\Http\Controllers\DepartmentsController;
use App\Http\Controllers\GaDashboardController;
use App\Http\Controllers\GmReportController;
use App\Http\Controllers\GoogleCalendarApiController;
use App\Http\Controllers\GoogleCalendarController;
use App\Http\Controllers\HrDashboardController;
use App\Http\Controllers\IMBudgetController;
use App\Http\Controllers\IMBudgetNonPurchController;
use App\Http\Controllers\Integration\AcumVmsStagingController;
use App\Http\Controllers\Integration\IFCAAPIBASTController;
use App\Http\Controllers\Integration\IFCAAPIGRNController;
use App\Http\Controllers\Integration\IFCAAPIIssueController;
use App\Http\Controllers\Integration\IFCAAPINonStockController;
use App\Http\Controllers\Integration\IFCAAPIPOController;
use App\Http\Controllers\Integration\IFCAAPIStockController;
use App\Http\Controllers\Integration\IFCAAPISupplierController;
use App\Http\Controllers\Integration\IFCAIntegrationController;
use App\Http\Controllers\Integration\SLAPIGRNController;
use App\Http\Controllers\Integration\SLAPIIssueController;
use App\Http\Controllers\Integration\SLAPIPOController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InventoryUserController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\IssueListController;
use App\Http\Controllers\ItDashboardController;
use App\Http\Controllers\ItemRequestController;
use App\Http\Controllers\ItRecommendationController;
use App\Http\Controllers\JobapplicantController;
use App\Http\Controllers\JobpostingController;
use App\Http\Controllers\KendaraanController;
use App\Http\Controllers\KontrakController;
use App\Http\Controllers\LastOrderController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ManpowerController;
use App\Http\Controllers\MappingIssueERPController;
use App\Http\Controllers\MappingPoERPController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\MasterTrainingController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\MeetingRoomSetupController;
use App\Http\Controllers\MsApprovalController;
use App\Http\Controllers\MsApprovalGroupBiayaController;
use App\Http\Controllers\MsCategoryController;
use App\Http\Controllers\MsGroupbiayaNonPurchController;
use App\Http\Controllers\MultiDashboardController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\NonstockJobsController;
use App\Http\Controllers\OperationalDashboardController;
use App\Http\Controllers\OrgChartController;
use App\Http\Controllers\ParkingRegistrationController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\PoController;
use App\Http\Controllers\PoListController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectTaskController;
use App\Http\Controllers\PurchasingDashboardController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ReceiptListController;
use App\Http\Controllers\ReportBastController;
use App\Http\Controllers\ReportCanvassSheetController;
use App\Http\Controllers\ReportFixedAssetController;
use App\Http\Controllers\ReportGeneralGAController;
use App\Http\Controllers\ReportOperationalController;
use App\Http\Controllers\ReportPurchasingController;
use App\Http\Controllers\ReportWarehouseController;
use App\Http\Controllers\RfcaListController;
use App\Http\Controllers\RfpController;
use App\Http\Controllers\RfpNonPurchController;
use App\Http\Controllers\SelfRegisterApplicantController;
use App\Http\Controllers\SendCommentController;
use App\Http\Controllers\SpbController;
use App\Http\Controllers\SpbJobsController;
use App\Http\Controllers\SppbController;
use App\Http\Controllers\SppjController;
use App\Http\Controllers\SppkController;
use App\Http\Controllers\SpptController;
use App\Http\Controllers\StockJobsController;
// INTEGRATION
use App\Http\Controllers\StrukturOrgController;
use App\Http\Controllers\SysAccessRightController;
use App\Http\Controllers\SysApplicationController;
use App\Http\Controllers\SysCalendarController;
use App\Http\Controllers\SysMenuController;
use App\Http\Controllers\SysRoleController;
use App\Http\Controllers\SysRoleMenuController;
use App\Http\Controllers\SysScreenController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\TestEmailController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketSetupController;
use App\Http\Controllers\TopController;
use App\Http\Controllers\TrainingRegistrationController;
use App\Http\Controllers\TrAttachmentController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\UsersEngController;
use App\Http\Controllers\UserSyncController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\VoucherTaxiController;
use App\Http\Controllers\VoucherTaxiSetupController;
use App\Http\Controllers\WoController;
use App\Http\Controllers\WorkInstructionController;
use App\Http\Controllers\WorksCategoryController;
use App\Models\SysMenu;
use App\Models\SysRoleMenu;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

Route::get('/avatar/{filename}', function ($filename) {
    return response($filename, 200, [
        'Access-Control-Allow-Origin' => '*',
        'X-Debug-Header' => 'ROUTE AVATAR TES',
    ]);
});

Route::redirect('/', 'login');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'login' => ['required'],
        'password' => ['required'],
    ]);

    // Cari user berdasarkan email, username, atau NIP
    $user = User::where('status', 'A')
        ->where(function ($query) use ($credentials) {
            $query->where('email', $credentials['login'])
                ->orWhere('username', $credentials['login'])
                ->orWhere('npk', $credentials['login']);
        })
        ->first();

    if (!$user || !Auth::attempt([
        'email' => $user->email,
        'password' => $credentials['password'],
    ], $request->boolean('remember'))) {
        throw ValidationException::withMessages(['login' => ['These credentials do not match our records.']]);
    }

    return redirect()->intended('/dashboard');
})->name('login.submit'); // ✅ FIXED (was duplicate)

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login');
})->name('logout');

Route::get('/modules', function () {
    return view('layouts.module');
})->name('modules');

// Route::middleware(['auth:sanctum', 'verified'])->group(function () {
Route::middleware(['auth'])->group(function () {
    // Dashboard Approval JSON endpoints

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
    Route::get('/showpersonnels/{hash}', [PersonnelController::class, 'showPersonnel']);
    Route::get('/personnel/{id}/comments', [PersonnelController::class, 'fetchComments']);
    Route::post('/personnel/{id}/comments', [PersonnelController::class, 'storeComment']);
    Route::post('/personnel/{id}/approve', [PersonnelController::class, 'approvePersonnel']);
    Route::post('/personnel/{id}/reject', [PersonnelController::class, 'rejectPersonnel']);
    Route::post('/personnel/{id}/revise', [PersonnelController::class, 'revisePersonnel']);
    Route::get('/editpersonnels/{hash}', [PersonnelController::class, 'editPersonnel']);
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
    Route::get('/attachments/view/{id}', [PersonnelController::class, 'viewAttachment'])->name('attachments.view');
    Route::get('/hr/departments', [PersonnelController::class, 'byDivision'])->name('hr.departments');
    Route::post('/jobposting/toggle-status', [PersonnelController::class, 'toggleJobPostingStatus']);

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
    Route::get('/showcareers/{hash}', [CareerController::class, 'showCareer']);
    Route::get('/career/{id}/comments', [CareerController::class, 'fetchComments']);
    Route::post('/career/{id}/comments', [CareerController::class, 'storeComment']);
    Route::post('/career/{id}/approve', [CareerController::class, 'approveCareer']);
    Route::post('/career/{id}/reject', [CareerController::class, 'rejectCareer']);
    Route::post('/career/{id}/rollback', [CareerController::class, 'rollbackCareer']);
    Route::get('/editcareers/{hash}', [CareerController::class, 'editCareer']);
    Route::put('/careers/{id}', [CareerController::class, 'updateCareer'])->name('careers.update');
    Route::put('/careers/remove-attachment/{id}', [CareerController::class, 'removeAttachment']);
    Route::get('/career/{id}/check-approval/{action}', [CareerController::class, 'checkApproval']);
    Route::post('/checklist/upload', [CareerController::class, 'uploadDocument'])->name('checklist.upload');
    Route::post('/assessment/update', [CareerController::class, 'updateAssessment'])->name('assessment.update');
    Route::post('/assessmentuser/update', [CareerController::class, 'updateAssessmentuser'])->name('assessmentuser.update');
    Route::get('/career/{docid}/check-reject-permission', [CareerController::class, 'checkRejectPermission']);
    Route::get('/career/{docid}/check-rollback-permission', [CareerController::class, 'checkRollbackPermission']);
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
    Route::get('/checklist/{id}/view', [CareerController::class, 'viewDocument'])->name('checklist.view');

    Route::get('/jobpostings', [JobpostingController::class, 'index'])->name('jobpostings');
    Route::get('/jobpostings/json', [JobpostingController::class, 'json'])->name('jobpostings.json');
    Route::get('/showjobpostings/{id}', [JobpostingController::class, 'showJobposting']);
    Route::get('/jobposting/list', [JobpostingController::class, 'list'])->name('jobposting.list');
    // Route::post('/applicant/mapping', [ApplicantController::class, 'storeMapping'])->name('applicant.mapping.store');
    Route::post('/applicant/mapping', [SelfRegisterApplicantController::class, 'storeMapping'])
        ->name('applicant.mapping.store');
    Route::post('/applicant/mapping/rollback', [SelfRegisterApplicantController::class, 'rollbackMapping'])
        ->name('applicant.mapping.rollback');

    Route::get('/jobapplicant', [JobapplicantController::class, 'index'])->name('jobapplicant');
    Route::get('/jobapplicant/json', [JobapplicantController::class, 'json'])->name('jobapplicant.json');
    Route::get('/jobapplicant/applicants/{jobId}', [JobapplicantController::class, 'JobApplicants'])->name('jobapplicant.applicants');
    // Route::get('/jobapplicant/counts', [JobapplicantController::class, 'getCounts'])->name('jobapplicant.counts');

    Route::get('/job-filters/tl', [JobapplicantController::class, 'jobTitleLevels'])->name('jobfilters.tl');

    Route::get('/selfregister', [SelfRegisterApplicantController::class, 'index'])->name('selfregister');
    Route::get('/selfregister/json', [SelfRegisterApplicantController::class, 'json'])->name('selfregister.json');
    Route::get('/showselfregister/{hash}', [SelfRegisterApplicantController::class, 'showSelfRegister']);

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
    Route::get('/showstos/{hash}', [StrukturOrgController::class, 'showSto']);
    Route::get('/sto/{id}/comments', [StrukturOrgController::class, 'fetchComments']);
    Route::post('/sto/{id}/comments', [StrukturOrgController::class, 'storeComment']);
    Route::post('/sto/{id}/approve', [StrukturOrgController::class, 'approveSto']);
    Route::post('/sto/{id}/reject', [StrukturOrgController::class, 'rejectSto']);
    Route::post('/sto/{id}/revise', [StrukturOrgController::class, 'reviseSto']);
    Route::get('/editstos/{hash}', [StrukturOrgController::class, 'editSto']);
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
    Route::get('/showchangestos/{hash}', [ChangeStoController::class, 'showChangesto']);
    Route::get('/changesto/{id}/comments', [ChangeStoController::class, 'fetchComments']);
    Route::post('/changesto/{id}/comments', [ChangeStoController::class, 'storeComment']);
    Route::post('/changesto/{id}/approve', [ChangeStoController::class, 'approveChangesto']);
    Route::post('/changesto/{id}/reject', [ChangeStoController::class, 'rejectChangesto']);
    Route::post('/changesto/{id}/revise', [ChangeStoController::class, 'reviseChangesto']);
    Route::get('/editchangestos/{hash}', [ChangeStoController::class, 'editChangesto']);
    Route::put('/changestos/{id}', [ChangeStoController::class, 'updateChangesto'])->name('changestos.update');
    Route::put('/changestos/remove-attachment/{id}', [ChangeStoController::class, 'removeAttachment']);
    Route::get('/changesto/{id}/check-approval/{action}', [ChangeStoController::class, 'checkApproval']);

    // Route::get('/changestos', [ChangeStoController::class, 'index'])->name('changestos');
    // Route::get('/changestos/json', [ChangeStoController::class, 'json'])->name('changestos.json');
    // Route::get('/createchangestos', [ChangeStoController::class, 'createChangesto']);
    // Route::post('/changestos', [ChangeStoController::class, 'storeChangesto'])->name('changestos.store');
    // Route::get('/showchangestos/{id}', [ChangeStoController::class, 'showChangesto']);
    // Route::get('/changesto/{id}/comments', [ChangeStoController::class, 'fetchComments']);
    // Route::post('/changesto/{id}/comments', [ChangeStoController::class, 'storeComment']);
    // Route::post('/changesto/{id}/approve', [ChangeStoController::class, 'approveChangesto']);
    // Route::post('/changesto/{id}/reject', [ChangeStoController::class, 'rejectChangesto']);
    // Route::post('/changesto/{id}/revise', [ChangeStoController::class, 'reviseChangesto']);
    // Route::get('/editchangestos/{id}', [ChangeStoController::class, 'editChangesto']);
    // Route::put('/changestos/{id}', [ChangeStoController::class, 'updateChangesto'])->name('changestos.update');
    // Route::put('/changestos/remove-attachment/{id}', [ChangeStoController::class, 'removeAttachment']);
    // Route::get('/changesto/{id}/check-approval/{action}', [ChangeStoController::class, 'checkApproval']);

    // Route::get('/budgets', [BudgetController::class, 'index'])->name('budgets');
    // Route::get('/budgets/json', [BudgetController::class, 'json'])->name('budgets.json');
    // Route::get('/createbudgets', [BudgetController::class, 'createBudget'])->name('budget.create');
    // Route::post('/budgets', [BudgetController::class, 'storeBudget'])->name('budgets.store');
    // Route::get('/showbudgets/{hash}', [BudgetController::class, 'showBudget']);
    // Route::post('/budget/{id}/approve', [BudgetController::class, 'approveBudget']);
    // Route::post('/budget/{id}/reject', [BudgetController::class, 'rejectBudget']);
    // Route::post('/budget/{id}/revise', [BudgetController::class, 'reviseBudget']);
    // Route::get('/editbudgets/{hash}', [BudgetController::class, 'editBudget'])->name('budget.edit');
    // Route::put('/budgets/{id}', [BudgetController::class, 'updateBudget'])->name('budgets.update');
    // Route::put('/budgets/remove-attachment/{id}', [BudgetController::class, 'removeAttachment']);
    // Route::get('/budget/{id}/check-approval/{action}', [BudgetController::class, 'checkApproval']);
    // Route::get('/get-business-units/{cpny_id}', [BudgetController::class, 'getBusinessUnits']);
    // Route::get('/pdf_budgets/{hash}', [BudgetController::class, 'printBudget']);
    // Route::post('/budgets/import', [BudgetController::class, 'import'])->name('budgets.import');
    // Route::post('/budgets/{budget}/import', [BudgetController::class, 'import'])->name('budgets.import.edit');

    Route::middleware('access:BUDGET,VIEW')->group(function () {
        Route::get('/budgets', [BudgetController::class, 'index'])->name('budgets');
        Route::get('/budgets/json', [BudgetController::class, 'json'])->name('budgets.json');
        Route::get('/showbudgets/{hash}', [BudgetController::class, 'showBudget']);
        Route::get('/pdf_budgets/{hash}', [BudgetController::class, 'printBudget']);
        Route::get('/get-business-units/{cpny_id}', [BudgetController::class, 'getBusinessUnits']);

        Route::get('/budgetmonitor', [BudgetMonitorController::class, 'index'])->name('budgetmonitor');
        Route::get('/budgetmonitor/options/companies', [BudgetMonitorController::class, 'companies'])->name('budgetmonitor.options.companies');
        Route::get('/budgetmonitor/options/business-units', [BudgetMonitorController::class, 'businessUnits'])->name('budgetmonitor.options.businessUnits');
        Route::get('/budgetmonitor/options/departments', [BudgetMonitorController::class, 'departments'])->name('budgetmonitor.options.departments');
        Route::get('/budgetmonitor/master.json', [BudgetMonitorController::class, 'masterJson'])->name('budgetmonitor.master.json');
        Route::get('/budgetmonitor/trx.json', [BudgetMonitorController::class, 'trxJson'])->name('budgetmonitor.trx.json');

        Route::get('/mapping-po-erp', [MappingPoERPController::class, 'index'])->name('mapping_po_erp.index');
        Route::get('/mapping-po-erp/json', [MappingPoERPController::class, 'json'])->name('mapping_po_erp.json');
        Route::get('/mapping-po-erp/integration-types', [MappingPoERPController::class, 'integrationTypes'])->name('mapping_po_erp.integration-types');
        Route::get('/mapping-po-erp/{id}', [MappingPoERPController::class, 'showMapping'])->name('mapping_po_erp.show');
        Route::put('/mapping-po-erp/{id}', [MappingPoERPController::class, 'updateMapping'])->name('mapping_po_erp.update');

        Route::get('/mapping-issue-erp', [MappingIssueERPController::class, 'index'])->name('mapping_issue_erp.index');
        Route::get('/mapping-issue-erp/json', [MappingIssueERPController::class, 'json'])->name('mapping_issue_erp.json');
        Route::get('/mapping-issue-erp/integration-types', [MappingIssueERPController::class, 'integrationTypes'])->name('mapping_issue_erp.integration-types');
        Route::get('/mapping-issue-erp/{id}', [MappingIssueERPController::class, 'showMapping'])->whereNumber('id')->name('mapping_issue_erp.show');
        Route::put('/mapping-issue-erp/{id}', [MappingIssueERPController::class, 'updateMapping'])->whereNumber('id')->name('mapping_issue_erp.update');
    });

    Route::middleware('access:BUDGET,CREATE')->group(function () {
        Route::get('/createbudgets', [BudgetController::class, 'createBudget'])->name('budget.create');
        Route::post('/budgets', [BudgetController::class, 'storeBudget'])->name('budgets.store');
        Route::post('/budgets/import', [BudgetController::class, 'import'])->name('budgets.import');
    });

    Route::middleware('access:BUDGET,EDIT')->group(function () {
        Route::get('/editbudgets/{hash}', [BudgetController::class, 'editBudget'])->name('budget.edit');
        Route::put('/budgets/{id}', [BudgetController::class, 'updateBudget'])->name('budgets.update');
        Route::put('/budgets/remove-attachment/{id}', [BudgetController::class, 'removeAttachment']);
        Route::post('/budget/{id}/approve', [BudgetController::class, 'approveBudget']);
        Route::post('/budget/{id}/reject', [BudgetController::class, 'rejectBudget']);
        Route::post('/budget/{id}/revise', [BudgetController::class, 'reviseBudget']);
        Route::post('/budgets/{budget}/import', [BudgetController::class, 'import'])->name('budgets.import.edit');
    });

    // 👀 VIEW SPPB
    Route::middleware('access:SPPB,VIEW')->group(function () {
        Route::get('/sppbs', [SppbController::class, 'index'])->name('sppbs');
        Route::get('/sppbs/json', [SppbController::class, 'json'])->name('sppbs.json');

        Route::get('/showsppbs/{hash}', [SppbController::class, 'showSppb']);
        // Route::get('/sppbs/{id}/tracking', [SppbController::class, 'tracking'])->name('sppbs.tracking');
        Route::get('/sppbs/{id}/tracking-detail', [SppbController::class, 'trackingDetail']);
        Route::get('/sppbs/{id}/tracking-detail/item', [SppbController::class, 'trackingDetailItem']);

        Route::get('/pdf_sppbs/{hash}', [SppbController::class, 'printSppb']);
        Route::get('/sppb/export/{id}', [SppbController::class, 'exportDetail'])
            ->name('sppb.export');
    });

    // ✍️ CREATE SPPB
    Route::middleware('access:SPPB,CREATE')->group(function () {
        Route::get('/createsppbs', [SppbController::class, 'createSppb']);
        Route::post('/sppbs', [SppbController::class, 'storeSppb'])->name('sppbs.store');
    });

    // ✏️ EDIT / APPROVAL SPPB
    Route::middleware('access:SPPB,EDIT')->group(function () {
        Route::get('/editsppbs/{hash}', [SppbController::class, 'editSppb']);
        Route::put('/sppbs/{id}', [SppbController::class, 'updateSppb'])->name('sppbs.update');
        Route::put('/sppbs/remove-attachment/{id}', [SppbController::class, 'removeAttachment']);

        Route::post('/sppb/{id}/approve', [SppbController::class, 'approveSppb']);
        Route::post('/sppb/{id}/reject', [SppbController::class, 'rejectSppb']);
        Route::post('/sppb/{id}/revise', [SppbController::class, 'reviseSppb']);
        Route::put('/sppbs/{hash}/cancel', [SppbController::class, 'cancelSppb'])->name('sppbs.cancel');
    });

    Route::middleware('access:SPPJ,VIEW')->group(function () {
        Route::get('/sppjs', [SppjController::class, 'index'])->name('sppjs');
        Route::get('/sppjs/json', [SppjController::class, 'json'])->name('sppjs.json');
        Route::get('/showsppjs/{hash}', [SppjController::class, 'showSppj']);
        // Route::get('/sppjs/{id}/tracking', [SppjController::class, 'tracking'])->name('sppjs.tracking');
        Route::get('/sppjs/{hash}/tracking-detail', [SppjController::class, 'trackingDetail']);
        Route::get('/sppjs/{hash}/tracking-detail/item', [SppjController::class, 'trackingDetailItem']);
        Route::get('/pdf_sppjs/{hash}', [SppjController::class, 'printSppj']);
        // BQ (Bill of Quantity) VIEW
        Route::get('/showbqsppjs/{hash}', [SppjController::class, 'showBQ']);
        Route::get('/pdf_bq_pj/{hash}', [SppjController::class, 'printBQ']);

        Route::get('/createbqkontrak/{sppjId}/categories', [SppjController::class, 'categoriesBqKontrak'])->name('bqkontrak.categories');
        Route::get('/showbqkontrak/{hash}', [SppjController::class, 'showBqKontrak'])->name('bqkontrak.show');
        Route::get('/sppj/export/{id}', [SppjController::class, 'exportDetail'])
            ->name('sppj.export');
    });

    Route::middleware('access:SPPJ,CREATE')->group(function () {
        Route::get('/createsppjs', [SppjController::class, 'createSppj']);
        Route::post('/sppjs', [SppjController::class, 'storeSppj'])->name('sppjs.store');
        // BQ CREATE
        Route::get('/createbqsppj/{id}', [SppjController::class, 'createBQ'])->name('bqsppj.create');
        Route::post('/bqsppj', [SppjController::class, 'storeBQ'])->name('bqsppj.store');
        Route::post('/bqsppj/import', [SppjController::class, 'importCreate'])->name('bqsppj.import');

        Route::get('/createbqkontrak/{sppjId}', [SppjController::class, 'createBqKontrak'])->name('bqkontrak.create');
        Route::post('/createbqkontrak/{sppjId}/pick-category', [SppjController::class, 'pickCategoryBqKontrak'])->name('bqkontrak.pickCategory');
        Route::post('/createbqkontrak/{sppjId}/save', [SppjController::class, 'saveBqKontrak'])->name('bqkontrak.save');
    });

    Route::middleware('access:SPPJ,EDIT')->group(function () {
        Route::get('/editsppjs/{hash}', [SppjController::class, 'editSppj']);
        Route::put('/sppjs/{id}', [SppjController::class, 'updateSppj'])->name('sppjs.update');
        Route::put('/sppjs/remove-attachment/{id}', [SppjController::class, 'removeAttachment']);
        // approval actions
        Route::post('/sppj/{id}/approve', [SppjController::class, 'approveSppj']);
        Route::post('/sppj/{id}/reject', [SppjController::class, 'rejectSppj']);
        Route::post('/sppj/{id}/revise', [SppjController::class, 'reviseSppj']);
        // BQ (Bill of Quantity) EDIT
        Route::get('/editbqsppjs/{id}', [SppjController::class, 'editBQ'])->name('bqsppj.edit');
        Route::put('/bqsppj/{id}', [SppjController::class, 'updateBQ'])->name('bqsppj.update');
        Route::put('/bqs/remove-attachment/{id}', [SppjController::class, 'removeAttachment']);
        // BQ import (action edit)
        Route::post('/bqsppj/{bq}/import', [SppjController::class, 'importEdit'])->name('bqsppj.import.edit');
        Route::put('/sppjs/{hash}/cancel', [SppjController::class, 'cancelSppj'])->name('sppjs.cancel');

        Route::get('/editbqkontrak/{eid}', [SppjController::class, 'editBqKontrak'])->name('bqkontrak.edit');
        Route::put('/bqkontrak/{eid}', [SppjController::class, 'updateBqKontrak'])->name('bqkontrak.update');

        Route::get('/bqkontrak/{eid}/categories', [SppjController::class, 'listKontrakCategories'])->name('bqkontrak.categories');
        Route::post('/bqkontrak/{eid}/pick-category', [SppjController::class, 'pickKontrakCategory'])->name('bqkontrak.pickCategory');
    });

    Route::middleware('access:SPPK,VIEW')->group(function () {
        Route::get('/sppks', [SppkController::class, 'index'])->name('sppks');
        Route::get('/sppks/json', [SppkController::class, 'json'])->name('sppks.json');
        Route::get('/showsppks/{hash}', [SppkController::class, 'showSppk']);
        // Route::get('/sppks/{id}/tracking', [SppkController::class, 'tracking'])->name('sppks.tracking');
        Route::get('/sppks/{hash}/tracking-detail', [SppkController::class, 'trackingDetail'])->name('sppks.trackingDetail');
        Route::get('/sppks/{hash}/tracking-detail/item', [SppkController::class, 'trackingDetailItem'])->name('sppks.trackingDetailItem');
        Route::get('/pdf_sppks/{hash}', [SppkController::class, 'printSppk']);
        Route::get('/sppk/export/{id}', [SppkController::class, 'exportDetail'])
            ->name('sppk.export');
    });

    Route::middleware('access:SPPK,CREATE')->group(function () {
        Route::get('/createsppks', [SppkController::class, 'createSppk']);
        Route::post('/sppks', [SppkController::class, 'storeSppk'])->name('sppks.store');
    });

    Route::middleware('access:SPPK,EDIT')->group(function () {
        Route::get('/editsppks/{hash}', [SppkController::class, 'editSppk']);
        Route::put('/sppks/{id}', [SppkController::class, 'updateSppk'])->name('sppks.update');
        Route::put('/sppks/remove-attachment/{id}', [SppkController::class, 'removeAttachment']);
        Route::post('/sppk/{id}/approve', [SppkController::class, 'approveSppk']);
        Route::post('/sppk/{id}/reject', [SppkController::class, 'rejectSppk']);
        Route::post('/sppk/{id}/revise', [SppkController::class, 'reviseSppk']);
        Route::put('/sppks/{hash}/cancel', [SppkController::class, 'cancelSppk'])->name('sppks.cancel');
    });

    Route::middleware('access:SPPT,VIEW')->group(function () {
        Route::get('/sppts', [SpptController::class, 'index'])->name('sppts');
        Route::get('/sppts/json', [SpptController::class, 'json'])->name('sppts.json');
        Route::get('/showsppts/{hash}', [SpptController::class, 'showSppt']);
        // Route::get('/sppts/{id}/tracking', [SpptController::class, 'tracking'])->name('sppts.tracking');
        Route::get('/sppts/{hash}/tracking-detail', [SpptController::class, 'trackingDetail'])->name('sppts.trackingDetail');
        Route::get('/sppts/{hash}/tracking-detail/item', [SpptController::class, 'trackingDetailItem'])->name('sppts.trackingDetailItem');
        Route::get('/pdf_sppts/{hash}', [SpptController::class, 'printSppt']);
        // BQ VIEW
        Route::get('/showbqsppts/{hash}', [SpptController::class, 'showBQ']);
        Route::get('/pdf_bq_pt/{hash}', [SpptController::class, 'printBQ']);
        Route::get('/sppt/export/{id}', [SpptController::class, 'exportDetail'])
            ->name('sppt.export');
    });

    Route::middleware('access:SPPT,CREATE')->group(function () {
        Route::get('/createsppts', [SpptController::class, 'createSppt']);
        Route::post('/sppts', [SpptController::class, 'storeSppt'])->name('sppts.store');
        // BQ CREATE
        Route::get('/createbqsppt/{id}', [SpptController::class, 'createBQ'])->name('bqsppt.create');
        Route::post('/bqsppt', [SpptController::class, 'storeBQ'])->name('bqsppt.store');
        Route::post('/bqsppt/import', [SpptController::class, 'importCreate'])->name('bqsppt.import');
    });

    Route::middleware('access:SPPT,EDIT')->group(function () {
        Route::get('/editsppts/{hash}', [SpptController::class, 'editSppt']);
        Route::put('/sppts/{id}', [SpptController::class, 'updateSppt'])->name('sppts.update');
        Route::put('/sppts/remove-attachment/{id}', [SpptController::class, 'removeAttachment']);
        // Approvals
        Route::post('/sppt/{id}/approve', [SpptController::class, 'approveSppt']);
        Route::post('/sppt/{id}/reject', [SpptController::class, 'rejectSppt']);
        Route::post('/sppt/{id}/revise', [SpptController::class, 'reviseSppt']);
        // BQ EDIT
        Route::get('/editbqsppts/{id}', [SpptController::class, 'editBQ'])->name('bqsppt.edit');
        Route::put('/bqsppt/{id}', [SpptController::class, 'updateBQ'])->name('bqsppt.update');
        Route::put('/bqs/remove-attachment/{id}', [SpptController::class, 'removeAttachment']);
        // BQ Import (edit)
        Route::post('/bqsppt/{bq}/import', [SpptController::class, 'importEdit'])->name('bqsppt.import.edit');
        Route::put('/sppts/{hash}/cancel', [SpptController::class, 'cancelSppt'])->name('sppts.cancel');
    });

    Route::middleware('access:ASSIGN,VIEW')->group(function () {
        Route::get('/assignlist', [AssignListController::class, 'AssignList'])->name('assignlist');
        Route::get('/assignlist/json', [AssignListController::class, 'AssignListJson'])->name('assignlist.json');
        Route::get('/assignlist/users', [AssignListController::class, 'AssignListUsers'])->name('assignlist.users');

        Route::get('/canvass/transferjobs/json', [AssignListController::class, 'TransferJobsJson'])->name('transferjobs.json');
    });

    Route::middleware('access:ASSIGN,EDIT')->group(function () {
        Route::post('/assignlist/assign', [AssignListController::class, 'AssignPurchasing'])->name('assignlist.assign');
        Route::post('/canvass/transferjobs/update', [AssignListController::class, 'TransferJobsUpdate'])->name('transferjobs.update');
    });

    Route::middleware('access:CSJOBS,VIEW')->group(function () {
        Route::get('/csjobs', [CsJobController::class, 'CsJobs'])->name('csjobs');
        Route::get('/csjobs/mine/json', [CsJobController::class, 'CsJobsMineJson'])->name('csjobs.mine.json');
        Route::get('/csjobs/all/json', [CsJobController::class, 'CsJobsAllJson'])->name('csjobs.all.json');
        Route::get('/csjobs/revision/json', [CsJobController::class, 'CsJobsRevisionJson'])->name('csjobs.revision.json');
        Route::get('/csjobs/sppbjkt-progress/json', [CsJobController::class, 'SppbjktOnProgressJson'])->name('csjobs.sppbjkt.progress.json');
        Route::get('/csjobs/completed/json', [CsJobController::class, 'CsJobsCompletedJson'])->name('csjobs.completed.json');
        Route::get('/csjobs/counts', [CsJobController::class, 'CsJobsCounts'])->name('csjobs.counts');
        Route::get('/csjobs/entry.json', [CsJobController::class, 'CsJobsEntryJson'])->name('csjobs.entry.json');
        Route::get('/csjobs/dataset-counts', [CsJobController::class, 'CsJobsDatasetCounts'])->name('csjobs.dataset.counts');
    });

    Route::middleware('access:CSJOBS,EDIT')->group(function () {
        Route::put('/csjobs/remove-attachment/{id}', [CsJobController::class, 'removeAttachment']);
        Route::post('/csjobs/complete/{doc}/{eid}', [CsJobController::class, 'CompleteRemainingOpen'])
            ->name('csjobs.complete');
        // CREATE CS (CANVASS SHEET)
        Route::get('/createcs/{doc}/{hash}', [CanvassController::class, 'createCS'])
            ->where([
                'doc' => 'SPPB|SPPJ|SPPK|SPPT|PO',
                'hash' => '[A-Za-z0-9]+',
            ])
            ->name('canvass.createcs');

        Route::get('/createcsrev/{doc}/{hash}', [CanvassController::class, 'createCSReuse'])
            ->where([
                'doc' => 'PO',
                'hash' => '[A-Za-z0-9]+',
            ])
            ->name('canvass.createcsreuse');

        // SAVE / STORE CANVASS SHEET
        Route::post('/csstore', [CanvassController::class, 'storeCS'])->name('cs.store');
        Route::post('/cssave', [CanvassController::class, 'saveCS'])->name('cs.save');
        Route::get('/editcs/{eid}', [CanvassController::class, 'editCS'])->name('csjobs.edit');
        Route::put('/csjobs/{csid}', [CanvassController::class, 'updateCS'])->name('csjobs.update');
        Route::post('/cs/{id}/approve', [CanvassController::class, 'approveCS']);
        Route::post('/cs/{id}/reject', [CanvassController::class, 'rejectCS']);
        Route::post('/cs/{id}/revise', [CanvassController::class, 'reviseCS']);

        Route::get('/bqcs/create-from-cs/{hash}', [BQCSController::class, 'createFromCS'])->name('bqcs.createFromCS');
        Route::post('/bqcs', [BQCSController::class, 'storeBQCS'])->name('bqcs.store');
        Route::get('/bqcs/edit/{hash}', [BQCSController::class, 'editBQCS'])->name('bqcs.edit');
        Route::put('bqcs/update/{hash}', [BQCSController::class, 'updateBQCS'])->name('bqcs.update');
        Route::get('/bqcs/{hash}/download-template', [BQCSController::class, 'downloadBqCsTemplate'])->name('bqcs.downloadTemplate');
        Route::post('/bqcs/{hash}/import-template', [BQCSController::class, 'importBqCsTemplate'])->name('bqcs.importTemplate');
        Route::get('/bqcs/{hash}/download-edit-template', [BQCSController::class, 'downloadBqCsEditTemplate'])->name('bqcs.downloadEditTemplate');
        Route::post('/bqcs/{hash}/import-edit-template', [BQCSController::class, 'importBqCsEditTemplate'])->name('bqcs.importEditTemplate');

        Route::post('/cs/check-qty', [CsJobController::class, 'checkQtyBeforeSubmit'])->name('cs.check-qty');
        Route::put('/csjobs/{csid}/cancel', [CsJobController::class, 'cancelCS'])->name('csjobs.cancel');
        Route::post('/csjobs/revise', [CsJobController::class, 'reviseSPPBJKT'])->name('csjobs.revise');
    });

    Route::middleware('access:CSLIST,VIEW')->group(function () {
        Route::get('/cslist', [CsListController::class, 'index'])->name('cslist');
        Route::get('/cslist/json', [CsListController::class, 'json'])->name('cslist.json');
        Route::get('/pdf_cs/{hash}', [CanvassController::class, 'printCS']);
        Route::get('/showcs/{hash}', [CanvassController::class, 'showCS']);
        Route::get('/showbqcs/{hash}', [BQCSController::class, 'showBQCS'])->name('bqcs.show');
        Route::get('/cs/lastprice/history', [CanvassController::class, 'getLastPriceHistory'])->name('cs.lastprice.history');
        Route::get('/cs/lastprice/history.entry', [CanvassController::class, 'getLastPriceHistoryEntry'])->name('cs.lastprice.history.entry');
        // Route::get('/pdf_bqcs/{hash}', [BQCSController::class, 'printBQCS']);
        Route::get('/pdf_bqcs/{hash}/{idx}', [BQCSController::class, 'printBQCSVend'])->whereNumber('idx')->name('bqcs.print.vendor');

        Route::get('/purchasing/last-order', [LastOrderController::class, 'index'])->name('lastorder');
        Route::get('/purchasing/last-order/inventory/json', [LastOrderController::class, 'inventoryJson'])->name('lastorder.inventory.json');
        Route::get('/purchasing/last-order/bq/json', [LastOrderController::class, 'bqJson'])->name('lastorder.bq.json');
    });

    Route::middleware('access:POLIST,VIEW')->group(function () {
        // PO LIST
        Route::get('/polist', [PoListController::class, 'index'])->name('polist');
        Route::get('/polist/json', [PoListController::class, 'json'])->name('polist.json');
        // VIEW PO
        Route::get('/showpo/{hash}', [PoController::class, 'showPo']);
        Route::get('/pdf_po/{hash}', [PoController::class, 'printPO']);
        Route::get('/pdf_spk_bq/{hash}', [PoController::class, 'printSpkBq']);

        // VIEW EMAIL PREVIEW
        Route::get('/po/{hash}/view-email', [PoController::class, 'viewEmailPO'])->name('po.viewemail');
    });

    Route::middleware('access:POLIST,EDIT')->group(function () {
        // ACTIONS
        Route::post('/po/{poid}/submit', [PoController::class, 'submitPO'])->name('po.submit');
        Route::post('/po/{poid}/cancel-reuse', [PoController::class, 'ReusePO'])->name('po.cancel_reuse');
        Route::post('/po/{poid}/cancel', [PoController::class, 'cancelPO'])->name('po.cancel');
        // SEND EMAIL
        Route::post('/po/{ponbr}/email/send', [PoController::class, 'sendNowPO'])->name('po.email.send');
        Route::post('/po/{ponbr}/complete-partial', [PoController::class, 'completePartial'])->name('po.complete-partial');
    });

    Route::middleware('access:RECEIPTLIST,VIEW')->group(function () {
        // List & JSON
        Route::get('/receiptlist', [ReceiptListController::class, 'index'])->name('receiptlist');
        Route::get('/receiptlist/json', [ReceiptListController::class, 'json'])->name('receiptlist.json');
        // Detail & Print
        Route::get('/showreceipt/{hash}', [ReceiptController::class, 'showReceipt']);
        Route::get('/receipts/print/{hash}', [ReceiptController::class, 'printReceipt'])->name('receipts.print');
        Route::get('/receipt/export/{id}', [ReceiptController::class, 'export'])
            ->name('receipt.export');
        // Lookup sites / warehouse
    });

    Route::middleware('access:RECEIPTLIST,CREATE')->group(function () {
        // Create Receipt
        Route::get('/receipt/create', [ReceiptController::class, 'createReceipt'])->name('receipt.create');
        Route::post('/receipts', [ReceiptController::class, 'storeReceipt'])->name('receipt.store');
        // Create Return
        Route::get('/receipt-return/create', [ReceiptController::class, 'createReturn'])->name('receipt.return.create');
        Route::post('/receipt-return', [ReceiptController::class, 'storeReturn'])->name('receipt.return.store');
    });

    Route::middleware('access:RECEIPTLIST,EDIT')->group(function () {
        // Edit Receipt
        Route::get('/editreceipts/{hash}', [ReceiptController::class, 'editReceipt'])->name('receipt.edit');
        Route::put('/editreceipts/{hash}', [ReceiptController::class, 'updateReceipt'])->name('receipt.update');
        // Approval actions
        Route::post('/receipt/{id}/approve', [ReceiptController::class, 'approveReceipt']);
        Route::post('/receipt/{id}/reject', [ReceiptController::class, 'rejectReceipt']);
        Route::post('/receipt/{id}/revise', [ReceiptController::class, 'reviseReceipt']);

        Route::get('/receipt/{receiptnbr}/validate-approve', [ReceiptController::class, 'validateApprove'])->name('receipt.validate-approve');
    });

    Route::middleware('access:WOLIST,VIEW')->group(function () {
        // WO List
        Route::get('/wos', [WoController::class, 'index'])->name('wos');
        Route::get('/wos/json', [WoController::class, 'json'])->name('wos.json');
        // View WO Detail
        Route::get('/showwos/{hash}', [WoController::class, 'showWo']);
        Route::get('/wos/{id}/tracking', [WoController::class, 'tracking'])->name('wos.tracking');
        Route::get('/pdf_wos/{hash}', [WoController::class, 'printWo'])->name('wos.print');
        // Job Monitoring (READ ONLY)
        Route::get('/wojobs', [WoController::class, 'woJobs'])->name('wojobs');
        Route::get('/wos/jsonJobs', [WoController::class, 'jsonJobs'])->name('wos.jsonJobs');
        Route::get('/wo-business-units', [WoController::class, 'businessUnits']);
    });

    Route::middleware('access:WOLIST,CREATE')->group(function () {
        // Create WO
        Route::get('/createwos', [WoController::class, 'createWo']);
        Route::post('/wos', [WoController::class, 'storeWo'])->name('wos.store');
    });

    Route::middleware('access:WOLIST,EDIT')->group(function () {
        // Edit WO
        Route::get('/editwos/{hash}', [WoController::class, 'editWo']);
        Route::put('/wos/{id}', [WoController::class, 'updateWo'])->name('wos.update');
        // Approval Actions
        Route::post('/wo/{id}/approve', [WoController::class, 'approveWo']);
        Route::post('/wo/{id}/reject', [WoController::class, 'rejectWo']);
        Route::post('/wo/{id}/revise', [WoController::class, 'reviseWo']);
        // WO Job Actions (affects process)
        Route::post('/wo/{woid}/process', [WoController::class, 'processWo'])->name('wo.process');
        Route::post('/wo/{woid}/job-status', [WoController::class, 'updateJobStatus'])->name('wo.jobstatus');
    });

    Route::middleware('access:SPBLIST,VIEW')->group(function () {
        Route::get('/spbs', [SpbController::class, 'index'])->name('spbs');
        Route::get('/spbs/json', [SpbController::class, 'json'])->name('spbs.json');
        Route::get('/spbs/track-json', [SpbController::class, 'trackJson'])->name('spbs.trackJson');
        Route::get('/showspbs/{hash}', [SpbController::class, 'showSpb']);
        Route::get('/spbs/{id}/tracking', [SpbController::class, 'tracking'])->name('spbs.tracking');
        Route::get('/pdf_spbs/{hash}', [SpbController::class, 'printSpb']);
        Route::get('/spb/export/{id}', [SpbController::class, 'exportDetail'])
            ->name('spb.export');
    });

    Route::middleware('access:SPBLIST,CREATE')->group(function () {
        Route::get('/createspbs', [SpbController::class, 'createSpb']);
        Route::post('/spbs', [SpbController::class, 'storeSpb'])->name('spbs.store');
    });

    Route::middleware('access:SPBLIST,EDIT')->group(function () {
        Route::get('/editspbs/{hash}', [SpbController::class, 'editSpb']);
        Route::put('/spbs/{id}', [SpbController::class, 'updateSpb'])->name('spbs.update');
        Route::post('/spb/{id}/approve', [SpbController::class, 'approveSpb']);
        Route::post('/spb/{id}/reject', [SpbController::class, 'rejectSpb']);
        Route::post('/spb/{id}/revise', [SpbController::class, 'reviseSpb']);
    });

    Route::middleware('access:SPBJOBS,VIEW')->group(function () {
        Route::get('/spbjobs', [SpbJobsController::class, 'index'])->name('spbjobs');
        Route::get('/spbjobs/json', [SpbJobsController::class, 'json'])->name('spbjobs.json');
    });

    Route::middleware('access:SPBJOBS,CREATE')->group(function () {
        // Create Issue
        Route::get('/issue/create', [SpbJobsController::class, 'createIssue'])->name('issue.create');
        // Create SPPB from SPB Job
        Route::get('/sppb/create', [SpbJobsController::class, 'createSPPB'])->name('sppb.create');
        Route::post('/sppb', [SpbJobsController::class, 'storeSPPB'])->name('sppb.store');
    });

    Route::middleware('access:ISSUELIST,VIEW')->group(function () {
        // List
        Route::get('/issuelist', [IssueListController::class, 'index'])->name('issuelist');
        Route::get('/issuelist/json', [IssueListController::class, 'json'])->name('issuelist.json');
        // Detail
        Route::get('/showissue/{hash}', [IssueController::class, 'showIssue']);
        // Printing
        Route::get('/issue/print/{hash}', [IssueController::class, 'printIssue'])->name('issues.print');
        Route::get('/pdf_issues/{hash}', [IssueController::class, 'printIssue']);
    });

    Route::middleware('access:ISSUELIST,CREATE')->group(function () {
        // Create Issue
        Route::post('/issues', [IssueController::class, 'storeIssue'])->name('issue.store');
        // Create Issue Return
        Route::get('/issue-return/create', [IssueController::class, 'createReturn'])->name('issue.return.create');
        Route::post('/issue-return', [IssueController::class, 'storeReturn'])->name('issue.return.store');
    });

    Route::middleware('access:ISSUELIST,EDIT')->group(function () {
        // Edit Issue
        Route::get('/editissues/{hash}', [IssueController::class, 'editIssue'])->name('issue.edit');
        Route::put('/issues/{hash}', [IssueController::class, 'updateIssue'])->name('issue.update');
        // Approval Actions
        Route::post('/issue/{id}/approve', [IssueController::class, 'approveIssue']);
        Route::post('/issue/{id}/reject', [IssueController::class, 'rejectIssue']);
        Route::post('/issue/{id}/revise', [IssueController::class, 'reviseIssue']);
    });

    Route::middleware('access:IMBUDGET,VIEW')->group(function () {
        Route::get('/imbudgets', [IMBudgetController::class, 'index'])->name('imbudgets');
        Route::get('/imbudgets/json', [IMBudgetController::class, 'json'])->name('imbudgets.json');
        Route::get('/showimbudgets/{hash}', [IMBudgetController::class, 'showIMBudget']);
        // Comments (view only)
        Route::get('/imbudget/{id}/comments', [IMBudgetController::class, 'fetchComments']);
        Route::get('/imbudgets/{id}/tracking', [IMBudgetController::class, 'tracking'])->name('imbudgets.tracking');
        Route::get('/pdf_imbudgets/{hash}', [IMBudgetController::class, 'printIMBudget']);
        Route::get('/editimbudgets/{hash}', [IMBudgetController::class, 'editIMBudget']);
    });

    Route::middleware('access:IMBUDGET,CREATE')->group(function () {
        Route::get('/createimbudgets', [IMBudgetController::class, 'createIMBudget']);
        Route::post('/imbudgets', [IMBudgetController::class, 'storeIMBudget'])->name('imbudgets.store');
    });

    Route::middleware('access:IMBUDGET,EDIT')->group(function () {
        Route::put('/imbudgets/{id}', [IMBudgetController::class, 'updateIMBudget'])->name('imbudgets.update');

        // Comments (create)
        Route::post('/imbudget/{id}/comments', [IMBudgetController::class, 'storeComment']);

        // Approval actions
        Route::post('/imbudget/{id}/approve', [IMBudgetController::class, 'approveIMBudget']);
        Route::post('/imbudget/{id}/reject', [IMBudgetController::class, 'rejectIMBudget']);
        Route::post('/imbudget/{id}/revise', [IMBudgetController::class, 'reviseIMBudget']);
    });

    Route::middleware('access:BASTLIST,VIEW')->group(function () {
        Route::get('/bastlist', [BastListController::class, 'index'])->name('bastlist');
        Route::get('/bastlist/json', [BastListController::class, 'json'])->name('bastlist.json');
        Route::get('/showbast/{hash}', [BastController::class, 'showBast']);
        // PDF
        Route::get('/pdf_bast/{hash}', [BastController::class, 'printBast'])->name('basts.print');
        Route::get('/pdf_bast_vendor/{hash}', [BastController::class, 'printBastVendor'])->name('basts.printvendor');
        // Ratings
        Route::get('/bast/{bastid}/ratings', [BastController::class, 'getBastRatings'])->name('bast.ratings');
    });

    Route::middleware('access:BASTLIST,CREATE')->group(function () {
        Route::get('/bast/create', [BastController::class, 'createBast'])->name('bast.create');
        Route::post('/bast', [BastController::class, 'storeBast'])->name('bast.store');
    });

    Route::middleware('access:BASTLIST,EDIT')->group(function () {
        Route::get('/editbasts/{hash}', [BastController::class, 'editBast'])->name('bast.edit');
        Route::put('/editbasts/{hash}', [BastController::class, 'updateBast'])->name('bast.update');

        // Approval Actions
        Route::post('/bast/{id}/approve', [BastController::class, 'approveBast']);
        Route::post('/bast/{id}/reject', [BastController::class, 'rejectBast']);
        Route::post('/bast/{id}/revise', [BastController::class, 'reviseBast']);
        Route::post('/bast/{hash}/cancel', [BastController::class, 'cancelBast'])->name('bast.cancel');
    });

    Route::middleware('access:RFCALIST,VIEW')->group(function () {
        Route::get('/rfcalist', [RfcaListController::class, 'index'])->name('rfcalist');
        Route::get('/rfcalist/json', [RfcaListController::class, 'json'])->name('rfcalist.json');
        // Detail
        Route::get('/showrfca/{hash}', [RfcaListController::class, 'showRfca']);
        // PDF
        Route::get('/pdf_rfca/{hash}', [RfcaListController::class, 'printRfca'])->name('rfca.print');
        Route::get('/po/matching-rfca/list', [RfcaListController::class, 'getMatchingRfcaList'])->name('po.matching-rfca.list');
        Route::post('/rfca/{hash}/matching-rfca/select', [RfcaListController::class, 'selectMatchingRfca'])->name('rfca.matching-rfca.select');
    });

    Route::middleware('access:RFCALIST,EDIT')->group(function () {
        Route::post('/rfca/{hash}/submit-type', [RfcaListController::class, 'submitType'])->name('rfca.submitType');
        Route::post('/rfca/{hash}/approve-step', [RfcaListController::class, 'approveStep'])->name('rfca.approveStep');
    });

    Route::middleware('access:CALRLIST,VIEW')->group(function () {
        Route::get('/calrlist', [CalrListController::class, 'index'])->name('calrlist');
        Route::get('/calrlist/json', [CalrListController::class, 'json'])->name('calrlist.json');
        Route::get('/showcalr/{hash}', [CalrController::class, 'showCalr']);
        // PDF (internal & vendor)
        Route::get('/pdf_calr/{hash}', [CalrController::class, 'printCalr'])->name('calrs.print');
        Route::get('/pdf_calr_vendor/{hash}', [CalrController::class, 'printCalrVendor'])->name('calrs.printvendor');
    });
    Route::middleware('access:CALRLIST,CREATE')->group(function () {
        Route::get('/calr/create', [CalrController::class, 'createCalr'])->name('calr.create');
        Route::post('/calr', [CalrController::class, 'storeCalr'])->name('calr.store');
    });

    Route::middleware('access:CALRLIST,EDIT')->group(function () {
        Route::get('/editcalrs/{hash}', [CalrController::class, 'editCalr'])->name('calr.edit');
        Route::put('/editcalrs/{hash}', [CalrController::class, 'updateCalr'])->name('calr.update');
        Route::post('/calr/{id}/approve', [CalrController::class, 'approveCalr']);
        Route::post('/calr/{id}/reject', [CalrController::class, 'rejectCalr']);
        Route::post('/calr/{id}/revise', [CalrController::class, 'reviseCalr']);
        Route::post('/calr/{hash}/cancel', [CalrController::class, 'cancelCalr'])->name('calr.cancel');
    });

    // 👀 VIEW ITEMREQ
    Route::middleware('access:ITEMREQ,VIEW')->group(function () {
        Route::get('/itemreq', [ItemRequestController::class, 'index'])->name('itemreq');
        Route::get('/itemreq/json', [ItemRequestController::class, 'json'])->name('itemreq.json');

        Route::get('/showitemreq/{hash}', [ItemRequestController::class, 'showItemReq']);
        Route::get('/itemreq/{id}/tracking', [ItemRequestController::class, 'tracking'])->name('itemreq.tracking');
        Route::get('/pdf_itemreq/{hash}', [ItemRequestController::class, 'printItemReq']);
    });

    // ✍️ CREATE ITEMREQ
    Route::middleware('access:ITEMREQ,CREATE')->group(function () {
        Route::get('/createitemreq', [ItemRequestController::class, 'createItemReq']);
        Route::post('/itemreq', [ItemRequestController::class, 'storeItemReq'])->name('itemreq.store');
    });

    // ✏️ EDIT / APPROVAL ITEMREQ
    Route::middleware('access:ITEMREQ,EDIT')->group(function () {
        Route::get('/edititemreq/{hash}', [ItemRequestController::class, 'editItemReq']);
        Route::put('/itemreq/{id}', [ItemRequestController::class, 'updateItemReq'])->name('itemreq.update');
        Route::put('/itemreq/remove-attachment/{id}', [ItemRequestController::class, 'removeAttachment']);

        Route::post('/itemreq/{id}/approve', [ItemRequestController::class, 'approveItemReq']);
        Route::post('/itemreq/{id}/reject', [ItemRequestController::class, 'rejectItemReq']);
        Route::post('/itemreq/{id}/revise', [ItemRequestController::class, 'reviseItemReq']);
    });

    Route::middleware('access:STOCKJOBS,VIEW')->group(function () {
        Route::get('/stockjobs', [StockJobsController::class, 'index'])->name('stockjobs');
        Route::get('/stockjobs/json', [StockJobsController::class, 'json'])->name('stockjobs.json');
        Route::get('/stockjobs/inventory-pick/json', [StockJobsController::class, 'inventoryPickJson'])->name('stockjobs.inventory-pick.json');
        Route::get('/stock-types', [StockJobsController::class, 'StockTypes'])->name('stockjobs.stock-types');
        Route::get('/stock-sub-types', [StockJobsController::class, 'StockSubTypes'])->name('stockjobs.stock-sub-types');
        Route::get('/stock-classes', [StockJobsController::class, 'StockClasses'])->name('stockjobs.stock-classes');
        Route::get('/stock-sub-classes', [StockJobsController::class, 'StockSubClasses'])->name('stockjobs.stock-sub-classes');
    });

    Route::middleware('access:STOCKJOBS,EDIT')->group(function () {
        Route::post('/invstock', [StockJobsController::class, 'store'])->name('invstock.store');
        Route::get('/invstock/{id}/edit', [StockJobsController::class, 'edit'])->name('invstock.edit');
        Route::put('/invstock/{id}', [StockJobsController::class, 'update'])->name('invstock.update');
        Route::put('/invstock/{id}/toggle-status', [StockJobsController::class, 'toggleStatus'])->name('invstock.toggle-status');
        Route::post('/stockjobs/set-inventory', [StockJobsController::class, 'setInventoryToItemRequest'])->name('stockjobs.set-inventory');
        Route::put('/stockjobs/{eid}/rollback', [StockJobsController::class, 'rollbackInventory'])->name('stockjobs.rollback');
    });

    Route::middleware('access:NONSTOCKJOBS,VIEW')->group(function () {
        Route::get('/nonstockjobs', [NonstockJobsController::class, 'index'])->name('nonstockjobs');
        Route::get('/nonstockjobs/json', [NonstockJobsController::class, 'json'])->name('nonstockjobs.json');
        Route::get('/nonstockjobs/inventory-pick/json', [NonstockJobsController::class, 'inventoryPickJson'])->name('nonstockjobs.inventory-pick.json');
        Route::get('/nonstock-types', [NonstockJobsController::class, 'NonstockTypes'])->name('nonstockjobs.nonstock-types');
        Route::get('/nonstock-sub-types', [NonstockJobsController::class, 'NonstockSubTypes'])->name('nonstockjobs.nonstock-sub-types');
        Route::get('/nonstock-classes', [NonstockJobsController::class, 'NonstockClasses'])->name('nonstockjobs.nonstock-classes');
        Route::get('/nonstock-sub-classes', [NonstockJobsController::class, 'NonstockSubClasses'])->name('nonstockjobs.nonstock-sub-classes');
    });

    Route::middleware('access:NONSTOCKJOBS,EDIT')->group(function () {
        Route::post('/invnonstock', [NonstockJobsController::class, 'store'])->name('invnonstock.store');
        Route::get('/invnonstock/{id}/edit', [NonstockJobsController::class, 'edit'])->name('invnonstock.edit');
        Route::put('/invnonstock/{id}', [NonstockJobsController::class, 'update'])->name('invnonstock.update');
        Route::put('/invnonstock/{id}/toggle-status', [NonstockJobsController::class, 'toggleStatus'])->name('invnonstock.toggle-status');
        Route::post('/nonstockjobs/set-inventory', [NonstockJobsController::class, 'setInventoryToItemRequest'])->name('nonstockjobs.set-inventory');
        Route::put('/nonstockjobs/{eid}/rollback', [NonstockJobsController::class, 'rollbackInventory'])->name('nonstockjobs.rollback');
    });

    Route::get('/kontrak', [KontrakController::class, 'index'])->name('kontrak');
    Route::get('/kontrak/json', [KontrakController::class, 'json'])->name('kontrak.json');
    Route::get('/showkontrak/{hash}', [KontrakController::class, 'showKontrak'])->name('kontrak.show');
    Route::get('/createkontrak/{hash}', [KontrakController::class, 'createKontrak'])->name('kontrak.create');
    Route::post('/kontrak/{kontrakid}/submit', [KontrakController::class, 'submitKontrak'])->name('kontrak.submit');
    Route::get('/kontrak/edit/{eid}', [KontrakController::class, 'editKontrak'])->name('kontrak.edit');
    Route::post('/kontrak/{eid}/reuse', [KontrakController::class, 'reuse'])->name('kontrak.reuse');
    Route::post('/kontrak/{eid}/terminate', [KontrakController::class, 'terminate'])->name('kontrak.terminate');

    Route::get('/kendaraan/all', [MasterController::class, 'listKendaraan'])->name('kendaraan.all');
    Route::get('/lookup/tenants', [MasterController::class, 'tenants'])->name('tenants.search');
    Route::get('/lookup/users', [MasterController::class, 'users'])->name('users.search');
    Route::get('/api/tenants/show', [MasterController::class, 'showTenant'])->name('tenants.show');
    Route::get('/vendorscs', [MasterController::class, 'vendors']);
    Route::get('/taxes', [MasterController::class, 'taxes'])->name('taxes.index');
    Route::get('/sites', [MasterController::class, 'sitesWarehouse'])->name('sites.index');
    Route::get('/inventory/list', [MasterController::class, 'InventoryList'])->name('inventory.list');
    Route::get('/inventory/listjoin', [MasterController::class, 'InventoryListJoin'])->name('inventory.listjoin');
    Route::get('/request-types/by-doctype', [MasterController::class, 'RequestType'])->name('requesttypes.byDoctype');
    Route::get('/locations/by-company', [MasterController::class, 'Location'])->name('locations.byCompany');
    Route::get('/sublocations/by-location', [MasterController::class, 'SubLocation'])->name('sublocations.byLocation');
    Route::get('/departments/{cpny_id}', [MasterController::class, 'DepartmentFin'])->name('finance.departments.byCompany');
    Route::get('/coa/by-dept', [MasterController::class, 'CoaBudget'])->name('coa.byDept');
    Route::get('/coa/by-wo', [MasterController::class, 'CoaBudgetWo'])->name('coa.byWo');
    Route::get('/coa/by-wo-spb', [MasterController::class, 'CoaBudgetWoSPB'])->name('coa.byWoSPB');
    Route::get('/editcoa/by-dept', [MasterController::class, 'editCoaBudget'])->name('editcoa.byDept');
    Route::get('/uom/by-inventory', [MasterController::class, 'UomInventory'])->name('uom.byInventory');
    Route::get('/wos/ajax/categories/{categoryid}', [MasterController::class, 'getCategories']);
    Route::get('/wos/ajax/worktypes', [MasterController::class, 'getWorktypes']);
    Route::get('/wos/ajax/subworktypes/{worktypeid}', [MasterController::class, 'getSubWorktypes']);
    Route::get('/wos/ajax/locations/{cpny_id}', [MasterController::class, 'getLocations']);
    Route::get('/wos/ajax/sublocations/{cpny_id}/{location_id}', [MasterController::class, 'getSubLocations']);
    Route::get('/wos/ajax/wos', [MasterController::class, 'getWoComplated'])->name('wos.ajax.index');
    Route::get('/inventory/by-worktype', [MasterController::class, 'InventoryByWorktype'])->name('inventory.byWorktype');
    Route::get('/wos/ajax/completed-wo', [MasterController::class, 'completedWoSppb'])->name('wos.ajax.completed-wo');
    Route::post('/coa', [MasterController::class, 'updateCoa'])->name('coa.update');
    Route::get('/itrecommend/ajax/completed', [MasterController::class, 'ajaxCompletedItr'])->name('itrecommend.ajax.completed');

    Route::get('/users/businessunits/by-cpny', [MasterController::class, 'businessUnitsByCpny'])->name('businessunits.byCpny');
    Route::get('/coa/by-deptwo', [MasterController::class, 'CoaBudgetbyDept'])->name('coa.byDeptWo');

    Route::post('/attachments/{doctype}/{refnbr}', [TrAttachmentController::class, 'uploadAttachments'])->name('attachments.upload');
    Route::get('/attachments/{doctype}/{refnbr}', [TrAttachmentController::class, 'listAttachments'])->name('attachments.list');
    Route::delete('/attachments/{id}', [TrAttachmentController::class, 'deleteAttachment'])->name('attachments.delete');
    Route::put('/remove-attachment/{id}', [TrAttachmentController::class, 'removeAttachment']);
    Route::get('/comments/{doctype}/{id}', [SendCommentController::class, 'fetchComments']);
    Route::post('/comments/{doctype}/{id}', [SendCommentController::class, 'storeComment']);
    Route::get('/approval/{refnbr}/{doctype}', [ApprovalController::class, 'getApprovalByDocument'])->name('approval.get');
    Route::get('/approval/{refnbr}/check/{action}', [ApprovalController::class, 'checkApproval'])->name('approval.check');

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

    Route::get('/rfp', [RfpController::class, 'index'])->name('rfp');
    Route::get('/rfp/json', [RfpController::class, 'json'])->name('rfp.json');
    Route::get('/showrfp/{hash}', [RfpController::class, 'showRfp']);
    Route::post('/rfp/{hash}/received', [RfpController::class, 'updateReceived'])->name('rfp.received');
    Route::post('/rfp/{hash}/treasury', [RfpController::class, 'updateTreasury'])->name('rfp.treasury');
    Route::get('/rfp/{id}/tracking', [RfpController::class, 'tracking'])->name('rfp.tracking');
    // Route::get('/pdf_rfp/{hash}', [RfpController::class, 'printRfp'])->name('rfp.print');
    Route::get('/pdf_rfp/{hash}', [RfpController::class, 'printPdfRfp'])->name('rfp.print.pdf');

    Route::post('/rfp/{id}/approve', [RfpController::class, 'approveRfp']);
    Route::post('/rfp/{id}/reject', [RfpController::class, 'rejectRfp']);
    Route::post('/rfp/{id}/revise', [RfpController::class, 'reviseRfp']);
    Route::post('/rfp/{hash}/reminder', [RfpController::class, 'reminderRfp'])->name('rfp.reminder');
    Route::post('/rfp/{hash}/finance-revise', [RfpController::class, 'financeReviseRfp'])->name('rfp.finance-revise');

    Route::get('/calrnonpurch', [CalrNonPurchController::class, 'index'])->name('calrnonpurch');
    Route::get('/calrnonpurch/json', [CalrNonPurchController::class, 'json'])->name('calrnonpurch.json');
    Route::get('/showcalrnonpurch/{hash}', [CalrNonPurchController::class, 'showCalrNonPurch']);
    // Route::get('/pdf_calrnonpurch/{hash}', [CalrNonPurchController::class, 'printCalrNonPurch'])->name('calrnonpurch.print');
    // Route::get('/pdf_calrnonpurch_vendor/{hash}', [CalrNonPurchController::class, 'printCalrVendor'])->name('calrnonpurch.printvendor');
    Route::get('/calrnonpurch/create', [CalrNonPurchController::class, 'createCalrNonPurch'])->name('calrnonpurch.create');
    Route::post('/calrnonpurch', [CalrNonPurchController::class, 'storeCalrNonPurch'])->name('calrnonpurch.store');
    Route::get('/editcalrnonpurch/{hash}', [CalrNonPurchController::class, 'editCalrNonPurch'])->name('calrnonpurch.edit');
    Route::put('/editcalrnonpurch/{hash}', [CalrNonPurchController::class, 'updateCalrNonPurch'])->name('calrnonpurch.update');
    Route::post('/calrnonpurch/{id}/approve', [CalrNonPurchController::class, 'approveCalrNonPurch']);
    Route::post('/calrnonpurch/{id}/reject', [CalrNonPurchController::class, 'rejectCalrNonPurch']);
    Route::post('/calrnonpurch/{id}/revise', [CalrNonPurchController::class, 'reviseCalrNonPurch']);
    Route::post('/calrnonpurch/{hash}/received', [CalrNonPurchController::class, 'receivedCalrNonPurch'])->name('calrnonpurch.received');
    Route::post('/calrnonpurch/{hash}/treasury', [CalrNonPurchController::class, 'treasuryCalrNonPurch'])->name('calrnonpurch.treasury');

    Route::post('/calrnonpurch/{hash}/reminder', [CalrNonPurchController::class, 'reminderCalrNonPurch'])->name('calrnonpurch.reminder');
    Route::post('/calrnonpurch/{hash}/finance-revise', [CalrNonPurchController::class, 'financeReviseCalrNonPurch'])->name('calrnonpurch.finance-revise');
    Route::get('/printcalrnonpurch/{hash}', [CalrNonPurchController::class, 'printPdfCalrNonPurch'])->name('calrnonpurch.print');

    Route::get('/parkingregistration', [ParkingRegistrationController::class, 'index'])->name('parkingregistration');
    Route::get('/parkingregistration/json', [ParkingRegistrationController::class, 'json'])->name('parkingregistration.json');
    Route::get('/showparkingregistration/{hash}', [ParkingRegistrationController::class, 'showParkingRegistration']);
    Route::get('/parkingregistration/{id}/tracking', [ParkingRegistrationController::class, 'tracking'])->name('parkingregistration.tracking');
    Route::get('/pdf_parkingregistration/{hash}', [ParkingRegistrationController::class, 'printParkingRegistration']);
    Route::get('/createparkingregistration', [ParkingRegistrationController::class, 'createParkingRegistration']);
    Route::post('/parkingregistration', [ParkingRegistrationController::class, 'storeParkingRegistration'])->name('parkingregistration.store');
    Route::get('/editparkingregistration/{hash}', [ParkingRegistrationController::class, 'editParkingRegistration']);
    Route::put('/parkingregistration/{id}', [ParkingRegistrationController::class, 'updateParkingRegistration'])->name('parkingregistration.update');
    Route::put('/parkingregistration/remove-attachment/{id}', [ParkingRegistrationController::class, 'removeAttachment']);
    Route::post('/parkingregistration/{id}/approve', [ParkingRegistrationController::class, 'approveParkingRegistration']);
    Route::post('/parkingregistration/{id}/reject', [ParkingRegistrationController::class, 'rejectParkingRegistration']);
    Route::post('/parkingregistration/{id}/revise', [ParkingRegistrationController::class, 'reviseParkingRegistration']);

    Route::get('/parkingregistration/employees', [ParkingRegistrationController::class, 'employeesByFilter'])->name('parkingregistration.employees');
    Route::put('/parkingregistration/{docid}/cancel', [ParkingRegistrationController::class, 'cancelParkingRegistration'])->name('parkingregistration.cancel');
    Route::put('/parking-kendaraan/{id}/toggle-status', [ParkingRegistrationController::class, 'toggleStatusParkingKendaraan'])->name('parkingkendaraan.toggleStatus');
    Route::put('/parking-kendaraan/{id}/no-kartu', [ParkingRegistrationController::class, 'updateNoKartuParkingKendaraan'])->name('parkingkendaraan.updateNoKartu');

    Route::get('/meeting-tv/{id}', [MeetingController::class, 'showRoomTv'])->name('meeting.tv');

    Route::middleware(['auth'])->group(function () {
        Route::controller(VoucherTaxiController::class)->group(function () {
            Route::get('/vouchertaxi', 'index')->name('vouchertaxi');

            Route::prefix('vouchertaxi')->name('vouchertaxi.')->group(function () {
                Route::middleware('ajax')->group(function () {
                    Route::get('/json', 'json')->name('json');
                    Route::get('/calendar-json', 'calendarJson')->name('calendar-json');
                    Route::get('/detail/{eid}', 'detail')->name('detail');
                    Route::get('/tracking/{eid}', 'tracking')->name('tracking');
                    Route::get('/find/{eid}', 'findByHash')->name('find');
                });

                Route::get('/purpose-search', 'purposeSearch')->name('purpose-search');
                Route::get('/employee-by-department', 'employeeByDepartment')->name('employee-by-department');

                Route::get('/print/{hash}', 'printVoucherTaxi')->name('print');

                Route::post('/store', 'storeVoucher')->name('store');
                Route::put('/update/{docid}', 'updateVoucherTaxi')->name('update');
                Route::post('/cancel/{docid}', 'cancel')->name('cancel');

                Route::post('/approve/{docid}', 'approveVoucherTaxi')->name('approve');
                Route::post('/reject/{docid}', 'rejectVoucherTaxi')->name('reject');
                Route::post('/revise/{docid}', 'reviseVoucherTaxi')->name('revise');

                Route::post('/process/{docid}', 'updateGaAdvice')->name('process');
            });

            Route::get('/showvouchertaxi/{eid}', 'index')->name('vouchertaxi.show');
        });

        Route::controller(VoucherTaxiSetupController::class)
            ->prefix('vouchertaxi/setup')
            ->name('vouchertaxi.setup.')
            ->group(function () {
                Route::get('/', 'index')->name('index');

                Route::middleware('ajax')->group(function () {
                    Route::get('/category/json', 'jsonCategory')->name('category.json');
                    Route::get('/category/find/{id}', 'findCategory')->name('category.find');
                });

                Route::post('/category/store', 'storeCategory')->name('category.store');
                Route::post('/category/update/{id}', 'updateCategory')->name('category.update');
                Route::post('/category/status/{id}', 'updateCategoryStatus')->name('category.status');
            });

        Route::controller(BookingCarController::class)->group(function () {
            Route::get('/bookingcar', 'index')->name('bookingcar');

            Route::prefix('bookingcar')->name('bookingcar.')->group(function () {
                Route::middleware('ajax')->group(function () {
                    Route::get('/json', 'json')->name('json');
                    Route::get('/calendar-json', 'calendarJson')->name('calendar-json');
                    Route::get('/detail/{eid}', 'detail')->name('detail');
                    Route::get('/tracking/{eid}', 'tracking')->name('tracking');
                    Route::get('/find/{eid}', 'findByHash')->name('find');
                });

                Route::get('/print/{hash}', 'printBookingCar')->name('print');

                Route::post('/store', 'storeBookingCar')->name('store');
                Route::put('/update/{docid}', 'updateBookingCar')->name('update');
                Route::post('/cancel/{docid}', 'cancel')->name('cancel');

                Route::post('/approve/{docid}', 'approveBookingCar')->name('approve');
                Route::post('/reject/{docid}', 'rejectBookingCar')->name('reject');
                Route::post('/revise/{docid}', 'reviseBookingCar')->name('revise');

                Route::post('/process/{docid}', 'updateGaAdvice')->name('process');
                Route::post('/change-expense/{eid}', 'changeCompanyExpense')->name('change-expense');
            });

            Route::get('/showbookingcar/{eid}', 'index')->name('bookingcar.show');
        });

        Route::controller(BookingCarSetupController::class)
            ->prefix('bookingcar/setup')
            ->name('bookingcar.setup.')
            ->group(function () {
                Route::get('/', 'index')->name('index');

                Route::middleware('ajax')->group(function () {
                    Route::get('/driver/json', 'jsonDriver')->name('driver.json');
                    Route::get('/driver/find/{id}', 'findDriver')->name('driver.find');

                    Route::get('/vehicle/json', 'jsonVehicle')->name('vehicle.json');
                    Route::get('/vehicle/find/{id}', 'findVehicle')->name('vehicle.find');

                    Route::get('/category/json', 'jsonCategory')->name('category.json');
                    Route::get('/category/find/{id}', 'findCategory')->name('category.find');
                });

                Route::post('/driver/store', 'storeDriver')->name('driver.store');
                Route::post('/driver/update/{id}', 'updateDriver')->name('driver.update');
                Route::post('/driver/status/{id}', 'updateDriverStatus')->name('driver.status');

                Route::post('/vehicle/store', 'storeVehicle')->name('vehicle.store');
                Route::post('/vehicle/update/{id}', 'updateVehicle')->name('vehicle.update');
                Route::post('/vehicle/status/{id}', 'updateVehicleStatus')->name('vehicle.status');

                Route::post('/category/store', 'storeCategory')->name('category.store');
                Route::post('/category/update/{id}', 'updateCategory')->name('category.update');
                Route::post('/category/status/{id}', 'updateCategoryStatus')->name('category.status');
            });

        Route::controller(CarExpenseController::class)
            ->prefix('carexpense')
            ->name('carexpense.')
            ->group(function () {
                Route::get('/', 'index')->middleware('access:CAREXPENSE,VIEW')->name('index');

                Route::middleware('ajax')->group(function () {
                    Route::get('/json', 'json')->name('json');
                    Route::get('/show/{eid}', 'show')->name('show');
                    Route::get('/attachments/{eid}', 'getAttachments')->name('attachments');
                });

                Route::post('/store', 'store')->name('store');
                Route::put('/update/{eid}', 'update')->name('update');
                Route::delete('/delete/{eid}', 'destroy')->name('delete');

                Route::post('/upload-attachment/{eid}', 'uploadAttachment')->name('upload-attachment');
                Route::delete('/delete-attachment/{id}', 'destroyAttachment')->name('delete-attachment');
            });

        Route::prefix('ticket')->controller(TicketController::class)->group(function () {
            Route::get('/', 'index')->name('ticket');
            Route::get('/export', 'export')->name('ticket.export');

            Route::middleware('ajax')->group(function () {
                Route::get('/json', 'json')->name('ticket.json');
                Route::get('/detail/{hash}', 'detail')->name('ticket.detail');
                Route::get('/tracking/{hash}', 'tracking')->name('ticket.tracking');
                Route::get('/comments/{hash}', 'comments')->name('ticket.comments');
                Route::get('/category-search', 'categorySearch')->name('ticket.categorySearch');
                Route::get('/subcategory-search', 'subcategorySearch')->name('ticket.subcategorySearch');
                Route::get('/priority-search', 'prioritySearch')->name('ticket.prioritySearch');
                Route::get('/location-search', 'locationSearch')->name('ticket.locationSearch');
                Route::get('/sub-location-search', 'subLocationSearch')->name('ticket.subLocationSearch');
                Route::get('/pic-search', 'picSearch')->name('ticket.picSearch');
                Route::get('/create-dropdown', 'createDropdown')->name('ticket.create-dropdown');
            });

            Route::post('/store', 'store')->name('ticket.store');
            Route::post('/update/{hash}', 'update')->name('ticket.update');
            Route::post('/cancel/{hash}', 'cancel')->name('ticket.cancel');
            Route::post('/response/{hash}', 'responseTicket')->name('ticket.response');
            Route::post('/process/{hash}', 'processTicket')->name('ticket.process');
            Route::post('/pending/{hash}', 'pendingTicket')->name('ticket.pending');
            Route::post('/envision/{hash}', 'envisionTicket')->name('ticket.envision');
            Route::post('/transfer/{hash}', 'transferTicket')->name('ticket.transfer');
            Route::post('/complete/{hash}', 'completeTicket')->name('ticket.complete');
            Route::post('/reopen/{hash}', 'reopenTicket')->name('ticket.reopen');
            Route::post('/comment/{hash}', 'comment')->name('ticket.comment');

            Route::get('/print/{hash}', 'printTicket')->name('ticket.print');
        });

        Route::controller(TicketController::class)->group(function () {
            Route::get('/showticket/{eid}', 'index');
            Route::get('/editticket/{eid}', 'index');
            Route::get('/responseticket/{eid}', 'index');
            Route::get('/processticket/{eid}', 'index');
            Route::get('/pendingticket/{eid}', 'index');
            Route::get('/envisionticket/{eid}', 'index');
            Route::get('/transferticket/{eid}', 'index');
            Route::get('/completeticket/{eid}', 'index');
            Route::get('/reopenticket/{eid}', 'index');
        });

        Route::prefix('ticket-setup')->controller(TicketSetupController::class)->group(function () {
            Route::get('/', 'index')->name('ticketsetup');

            Route::middleware('ajax')->group(function () {
                Route::get('/type-json', 'typeJson')->name('ticketsetup.typeJson');
                Route::get('/category-json', 'categoryJson')->name('ticketsetup.categoryJson');
                Route::get('/subcategory-json', 'subcategoryJson')->name('ticketsetup.subcategoryJson');
                Route::get('/priority-json', 'priorityJson')->name('ticketsetup.priorityJson');
                Route::get('/dept-json', 'deptJson')->name('ticketsetup.deptJson');

                Route::get('/wa-setting-json', 'waSettingJson')->name('ticketsetup.waSettingJson');

                Route::get('/category-by-type/{ticket_type}', 'categoryByType')->name('ticketsetup.categoryByType');
                Route::get('/subcategory-by-category/{ticket_categoryid}', 'subcategoryByCategory')->name('ticketsetup.subcategoryByCategory');
                Route::get('/priority-by-category/{ticket_categoryid}', 'priorityByCategory')->name('ticketsetup.priorityByCategory');
            });

            Route::post('/store-type', 'storeType')->name('ticketsetup.storeType');
            Route::put('/update-type/{ticket_type}', 'updateType')->name('ticketsetup.updateType');
            Route::delete('/destroy-type/{ticket_type}', 'destroyType')->name('ticketsetup.destroyType');

            Route::post('/store-category', 'storeCategory')->name('ticketsetup.storeCategory');
            Route::put('/update-category/{ticket_categoryid}', 'updateCategory')->name('ticketsetup.updateCategory');
            Route::delete('/destroy-category/{ticket_categoryid}', 'destroyCategory')->name('ticketsetup.destroyCategory');

            Route::post('/store-subcategory', 'storeSubcategory')->name('ticketsetup.storeSubcategory');
            Route::put('/update-subcategory/{ticket_subcategoryid}', 'updateSubcategory')->name('ticketsetup.updateSubcategory');
            Route::delete('/destroy-subcategory/{ticket_subcategoryid}', 'destroySubcategory')->name('ticketsetup.destroySubcategory');

            Route::post('/store-priority', 'storePriority')->name('ticketsetup.storePriority');
            Route::put('/update-priority/{id}', 'updatePriority')->name('ticketsetup.updatePriority');
            Route::delete('/destroy-priority/{id}', 'destroyPriority')->name('ticketsetup.destroyPriority');

            Route::post('/store-dept', 'storeDept')->name('ticketsetup.storeDept');
            Route::put('/update-dept/{id}', 'updateDept')->name('ticketsetup.updateDept');
            Route::delete('/destroy-dept/{id}', 'destroyDept')->name('ticketsetup.destroyDept');

            Route::post('/store-wa-setting', 'storeWaSetting')->name('ticketsetup.storeWaSetting');
            Route::put('/update-wa-setting/{id}', 'updateWaSetting')->name('ticketsetup.updateWaSetting');
            Route::delete('/destroy-wa-setting/{id}', 'destroyWaSetting')->name('ticketsetup.destroyWaSetting');
        });

        Route::prefix('access-request')->controller(AccessRequestController::class)->group(function () {
            Route::get('/', 'index')->name('accessrequest');

            Route::middleware('ajax')->group(function () {
                Route::get('/json', 'json')->name('access-request.json');
                Route::get('/detail/{hash}', 'detail')->name('access-request.detail');
                Route::get('/tracking/{hash}', 'tracking')->name('access-request.tracking');
                Route::get('/comments/{hash}', 'comments')->name('access-request.comments');
                Route::get('/category-search', 'categorySearch')->name('access-request.category-search');
            });

            Route::post('/store', 'store')->name('access-request.store');
            Route::post('/update/{hash}', 'update')->name('access-request.update');
            Route::post('/cancel/{hash}', 'cancel')->name('access-request.cancel');

            Route::post('/upload-attachment', 'uploadAttachment')->name('access-request.upload-attachment');

            Route::post('/approve/{docid}', 'approve')->name('access-request.approve');
            Route::post('/reject/{docid}', 'reject')->name('access-request.reject');
            Route::post('/revise/{docid}', 'revise')->name('access-request.revise');

            Route::post('/process-hardware/{hash}', 'processHardware')->name('access-request.process-hardware');
            Route::post('/process-software/{hash}', 'processSoftware')->name('access-request.process-software');

            Route::post('/comment/{hash}', 'comment')->name('access-request.comment');

            Route::get('/print/{hash}', 'print')->name('access-request.print');
        });

        Route::controller(AccessRequestController::class)->group(function () {
            Route::get('/showaccessrequest/{eid}', 'index');
            Route::get('/editaccessrequest/{eid}', 'index');
            Route::get('/processhardwareaccess/{eid}', 'index');
            Route::get('/processsoftwareaccess/{eid}', 'index');
        });

        Route::controller(MeetingController::class)->group(function () {
            Route::get('/meeting', 'index')->name('meeting');
            Route::get('/inforoom_{id}', 'getRoom');

            Route::post('/savemeeting', 'storeMeeting')->name('meeting.store');
            Route::put('/updatemeeting/{id}', 'updateMeeting')->name('updatemeeting');

            Route::get('/meetinglist/json', 'json')->name('meetinglist.json');
            Route::get('/calendar-json', 'calendarJson')->name('meeting.calendarJson');

            Route::get('/get-accessories/{id}', 'getAccessories');

            Route::get('/meetingteams', 'MeetingTeams')->name('meetingteams');

            Route::post('/saveteams', 'storeTeams')->name('teams.store');

            Route::put('/updateteams/{id}', 'updateTeams');

            Route::post('/cancel-meeting/{id}', 'cancelMeeting');

            Route::get('/teamslist/json', 'jsonTeams')->name('teamslist.json');

            Route::post('/update-zoom/{id}', 'updateZoomLink');
        });

        Route::controller(MeetingRoomSetupController::class)->prefix('meetingroom/setup')->name('meetingroom.setup.')->group(function () {
            Route::get('/', 'index')->name('index');

            Route::get('/room/json', 'jsonRoom')->name('room.json');
            Route::get('/room/find/{id}', 'findRoom')->name('room.find');

            Route::post('/room/store', 'storeRoom')->name('room.store');
            Route::post('/room/update/{id}', 'updateRoom')->name('room.update');
            Route::post('/room/status/{id}', 'updateRoomStatus')->name('room.status');

            Route::get('/accessories/json', 'jsonAccessories')->name('accessories.json');
            Route::get('/accessories/find/{id}', 'findAccessories')->name('accessories.find');

            Route::post('/accessories/store', 'storeAccessories')->name('accessories.store');
            Route::post('/accessories/update/{id}', 'updateAccessories')->name('accessories.update');
            Route::post('/accessories/status/{id}', 'updateAccessoriesStatus')->name('accessories.status');
        });

        Route::prefix('it-recommendation')->controller(ItRecommendationController::class)->group(function () {
            Route::get('/', 'index')->name('it-recommendation');

            Route::middleware('ajax')->group(function () {
                Route::get('/json', 'json')->name('it-recommendation.json');
                Route::get('/detail/{hash}', 'detail')->name('it-recommendation.detail');
                Route::get('/tracking/{hash}', 'tracking')->name('it-recommendation.tracking');
                Route::get('/comments/{docid}', 'comments')->name('it-recommendation.comments');
                Route::get('/inventory-search', 'inventorySearch')->name('it-recommendation.inventory-search');
                Route::get('/ticket-search', 'ticketSearch')->name('it-recommendation.ticket-search');
            });

            Route::post('/store', 'store')->name('it-recommendation.store');
            Route::put('/update/{hash}', 'update')->name('it-recommendation.update');
            Route::post('/cancel/{hash}', 'cancel')->name('it-recommendation.cancel');
            Route::post('/process/{hash}', 'process')->name('it-recommendation.process');
            Route::post('/upload-attachment/{hash}', 'uploadAttachment')
                ->name('it-recommendation.upload-attachment');

            Route::delete('/delete-attachment/{attachment}', 'deleteAttachment')
                ->name('it-recommendation.delete-attachment');
            Route::post('/it-revise/{hash}', 'itRevise')->name('it-recommendation.it-revise');
            Route::post('/it-reject/{hash}', 'itReject')->name('it-recommendation.it-reject');
            Route::post('/approve/{docid}', 'approve')->name('it-recommendation.approve');
            Route::post('/reject/{docid}', 'reject')->name('it-recommendation.reject');
            Route::post('/revise/{docid}', 'revise')->name('it-recommendation.revise');
            Route::post('/comment/{hash}', 'comment')->name('it-recommendation.comment');
            Route::get('/print/{hash}', 'print')->name('it-recommendation.print');
        });

        Route::controller(ItRecommendationController::class)->group(function () {
            Route::get('/showitrecommendation/{eid}', 'index');
            Route::get('/edititrecommendation/{eid}', 'index');
            Route::get('/processitrecommendation/{eid}', 'index');
            Route::get('/edit-processitrecommendation/{eid}', 'index');
            Route::get('/createitrecommendation', 'index');
        });

        Route::prefix('gm-report')
            ->controller(GmReportController::class)
            ->group(function () {
                Route::get('/dashboard', 'dashboard')->middleware('access:REPORTGM,VIEW')->name('reportgm');

                // API endpoints
                Route::get('/api/companies', 'companies')->name('gm.companies');
                Route::get('/api/budget-summary', 'budgetSummary')->name('gm.budget-summary');
                Route::get('/api/budget-by-company', 'budgetByCompany')->name('gm.budget-by-company');
                Route::get('/api/budget-by-department', 'budgetByDepartment')->name('gm.budget-by-department');
                Route::get('/api/budget-by-activity', 'budgetByActivity')->name('gm.budget-by-activity');
                Route::get('/api/budget-years', 'budgetYears')->name('gm.budget-years');
                Route::get('/api/departments', 'departments')->name('gm.departments');
                Route::get('/api/budget-by-month', 'budgetByMonth')->name('gm.budget-by-month');
            });

        Route::get('/dashboard', [MultiDashboardController::class, 'index'])->name('dashboard');

        Route::get('/card-chart/catalog', function () {
            return view('pages.card-chart.index');
        })->middleware('access:CARDCHAT,VIEW')->name('card-chart.catalog');

        Route::get('/card-chart/drag-dashboard', function () {
            return view('pages.card-chart.drag-dashboard');
        })->middleware('access:CARDCHAT,VIEW')->name('card-chart.drag-dashboard');

        Route::get('/settings/account', [ProfileController::class, 'show'])->name('profile.showx');

        Route::prefix('approval-dashboard')->controller(ApprovalDashboardController::class)->group(function () {
            Route::get('/summary-json', 'summaryJson');
            Route::get('/waiting-json', 'waitingJson');
            Route::get('/approve-json', 'approveJson');
        });

        Route::get('/my-document-notifications', [DocumentNotificationController::class, 'index'])->name('my.document.notifications');

        Route::prefix('it-dashboard')->controller(ItDashboardController::class)->name('it-dashboard.')->group(function () {
            Route::get('/summary-json', 'summaryJson')->name('summary-json');
            Route::get('/ticket-json', 'ticketJson')->name('ticket-json');
            Route::get('/access-request-json', 'accessRequestJson')->name('access-request-json');
            Route::get('/recommendation-json', 'recommendationJson')->name('recommendation-json');
            Route::get('/waiting-approval-json', 'waitingApprovalJson')->name('waiting-approval-json');
            Route::get('/approval-history-json', 'approvalHistoryJson')->name('approval-history-json');
            Route::get('/approval-doctypes-json', 'approvalDocTypes')->name('approval-doctypes-json');
        });

        Route::prefix('cost-control-dashboard')->controller(CostControlDashboardController::class)->group(function () {
            Route::get('/summary-json', 'summaryJson')->name('costdashboard.summary');
            Route::get('/waiting-approval-json', 'waitingApprovalJson')->name('costdashboard.waiting');
            Route::get('/approval-history-json', 'approvalHistoryJson')->name('costdashboard.history');
            Route::get('/pending-po-json', 'pendingPoJson')->name('costdashboard.po');
            Route::get('/pending-issue-json', 'pendingIssueJson')->name('costdashboard.issue');
            Route::get('/budget-json', 'budgetJson')->name('costdashboard.budget');
            Route::get('/im-budget-json', 'imBudgetJson')->name('costdashboard.imbudget');
            Route::get('/approval-doctypes-json', 'approvalDocTypes')->name('costdashboard.approval-doctypes');
        });

        Route::prefix('operational-dashboard')->controller(OperationalDashboardController::class)->group(function () {
            Route::get('/summary-json', 'summaryJson')->name('operational.summary');
            Route::get('/waiting-approval-json', 'waitingApprovalJson')->name('operational.waiting');
            Route::get('/approval-history-json', 'approvalHistoryJson')->name('operational.history');
            Route::get('/work-order-json', 'workOrderJson')->name('operational.work-order');
            Route::get('/approval-doctypes', 'approvalDocTypes')->name('operational.doctypes');
        });

        Route::prefix('ga-dashboard')->controller(GaDashboardController::class)->group(function () {
            Route::get('/summary-json', 'summaryJson')->name('ga.summary');
            Route::get('/waiting-approval-json', 'waitingApprovalJson')->name('ga.approval');
            Route::get('/approval-history-json', 'approvalHistoryJson')->name('ga.approval-history');
            Route::get('/voucher-taxi-json', 'voucherTaxiJson')->name('ga.voucher-taxi');
            Route::get('/booking-car-json', 'bookingCarJson')->name('ga.booking-car');
            Route::get('/parking-json', 'parkingJson')->name('ga.parking');
            Route::get('/approval-doctypes', 'approvalDocTypes')->name('ga.approval-doctypes');
        });

        Route::prefix('hr-dashboard')->controller(HrDashboardController::class)->name('hr-dashboard.')->group(function () {
            Route::get('/summary-json', 'summaryJson')->name('summary-json');
            Route::get('/waiting-approval-json', 'waitingApprovalJson')->name('waiting-approval-json');
            Route::get('/approval-history-json', 'approvalHistoryJson')->name('approval-history-json');
            Route::get('/prf-json', 'prfJson')->name('prf-json');
            Route::get('/applicant-json', 'applicantJson')->name('applicant-json');
            Route::get('/self-register-json', 'selfRegisterJson')->name('self-register-json');
            Route::get('/approval-doctypes-json', 'approvalDocTypes')->name('approval-doctypes-json');
        });

        Route::prefix('purchasing-dashboard')->controller(PurchasingDashboardController::class)->name('purchasing.')->group(function () {
            Route::get('/summary-json', 'summaryJson')->name('summary');
            Route::get('/waiting-approval-json', 'waitingApprovalJson')->name('approval');
            Route::get('/approval-history-json', 'approvalHistoryJson')->name('approval-history');
            Route::get('/cs-json', 'csJson')->name('cs');
            Route::get('/po-unsend-json', 'poUnsendJson')->name('po-unsend');
            Route::get('/approval-doctypes', 'approvalDocTypes')->name('approval-doctypes');
        });
    });

    Route::get('/imbudgetnonpurch', [IMBudgetNonPurchController::class, 'index'])->name('imbudgetnonpurch');
    Route::get('/imbudgetnonpurch/json', [IMBudgetNonPurchController::class, 'json'])->name('imbudgetnonpurch.json');
    Route::get('/showimbudgetnonpurch/{hash}', [IMBudgetNonPurchController::class, 'showIMBudgetNonPurch']);
    Route::get('/imbudgetnonpurch/{id}/tracking-detail', [IMBudgetNonPurchController::class, 'trackingDetail']);
    Route::get('/imbudgetnonpurch/{id}/tracking-detail/item', [IMBudgetNonPurchController::class, 'trackingDetailItem']);
    Route::get('/pdf_imbudgetnonpurch/{hash}', [IMBudgetNonPurchController::class, 'printIMBudgetNonPurch']);
    Route::get('/imbudgetnonpurch/export/{id}', [IMBudgetNonPurchController::class, 'exportDetail'])->name('imbudgetnonpurch.export');

    Route::get('/createimbudgetnonpurch', [IMBudgetNonPurchController::class, 'createIMBudgetNonPurch']);
    Route::post('/imbudgetnonpurch', [IMBudgetNonPurchController::class, 'storeIMBudgetNonPurch'])->name('imbudgetnonpurch.store');
    Route::get('/editimbudgetnonpurch/{hash}', [IMBudgetNonPurchController::class, 'editIMBudgetNonPurch']);
    Route::put('/imbudgetnonpurch/{id}', [IMBudgetNonPurchController::class, 'updateIMBudgetNonPurch'])->name('imbudgetnonpurch.update');
    Route::put('/imbudgetnonpurch/remove-attachment/{id}', [IMBudgetNonPurchController::class, 'removeAttachment']);
    Route::post('/imbudgetnonpurch/{id}/approve', [IMBudgetNonPurchController::class, 'approveIMBudgetNonPurch']);
    Route::post('/imbudgetnonpurch/{id}/reject', [IMBudgetNonPurchController::class, 'rejectIMBudgetNonPurch']);
    Route::post('/imbudgetnonpurch/{id}/revise', [IMBudgetNonPurchController::class, 'reviseIMBudgetNonPurch']);
    Route::put('/imbudgetnonpurch/{hash}/cancel', [IMBudgetNonPurchController::class, 'cancelIMBudgetNonPurch'])->name('imbudgetnonpurch.cancel');

    Route::get('/rfpnonpurch', [RfpNonPurchController::class, 'index'])->name('rfpnonpurch');
    Route::get('/rfpnonpurch/json', [RfpNonPurchController::class, 'json'])->name('rfpnonpurch.json');
    Route::get('/showrfpnonpurch/{hash}', [RfpNonPurchController::class, 'showRfpNonPurch']);
    Route::post('/rfpnonpurch/{hash}/received', [RfpNonPurchController::class, 'updateReceived'])->name('rfpnonpurch.received');
    Route::post('/rfpnonpurch/{hash}/treasury', [RfpNonPurchController::class, 'updateTreasury'])->name('rfpnonpurch.treasury');
    Route::get('/rfpnonpurch/{id}/tracking', [RfpNonPurchController::class, 'tracking'])->name('rfpnonpurch.tracking');
    // Route::get('/pdf_rfpnonpurch/{hash}', [RfpNonPurchController::class, 'printRfp'])->name('rfpnonpurch.print');
    Route::get('/pdf_rfpnonpurch/{hash}', [RfpNonPurchController::class, 'printPdfRfpNonPurch'])->name('rfpnonpurch.print.pdf');

    Route::post('/rfpnonpurch/{id}/approve', [RfpNonPurchController::class, 'approveRfpNonPurch']);
    Route::post('/rfpnonpurch/{id}/reject', [RfpNonPurchController::class, 'rejectRfpNonPurch']);
    Route::post('/rfpnonpurch/{id}/revise', [RfpNonPurchController::class, 'reviseRfpNonPurch']);
    Route::get('/createrfpnonpurch', [RfpNonPurchController::class, 'createRfpNonPurch']);
    Route::post('/rfpnonpurch', [RfpNonPurchController::class, 'storeRfpNonPurch'])->name('rfpnonpurch.store');
    Route::get('/editrfpnonpurch/{hash}', [RfpNonPurchController::class, 'editRfpNonPurch']);
    Route::put('/updaterfpnonpurch/{hash}', [RfpNonPurchController::class, 'updateRfpNonPurch'])->name('rfpnonpurch.update');
    Route::post('/rfpnonpurch/{hash}/reminder', [RfpNonPurchController::class, 'reminderRfpNonPurch'])->name('rfpnonpurch.reminder');
    Route::post('/rfpnonpurch/{hash}/finance-revise', [RfpNonPurchController::class, 'financeReviseRfpNonPurch'])->name('rfpnonpurch.finance-revise');

    // Route::get('/canvasssheet', [BudgetController::class, 'CanvassSheet'])->name('canvasssheet');
    // Route::get ('/canvass/create', [CanvassxController::class, 'createCS'])->name('canvass.create');
    // Route::get('/vendors', [VendorController::class, 'index']);

    // Route for the getting the data feed
    // Route::get('/json-data-feed', [DataFeedController::class, 'getDataFeed'])->name('json_data_feed');

    // Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Route::get('/waitingapproval', [DashboardController::class, 'waitingApproval'])->name('waitingapproval');
    // Route::get('/waitingapproval/json', [DashboardController::class, 'Waitingjson'])->name('waitingapproval.json');
    // Route::get('/dashapproval/json', [DashboardController::class, 'Approvejson'])->name('dashapproval.json');
    // Route::get('/mastercard', [DashboardController::class, 'analytics'])->name('mastercard');

    // Route::get('/test', [DashboardController::class, 'test'])->name('test');

    Route::get('/test-email', [TestEmailController::class, 'index'])->name('test-email.index');
    Route::post('/test-email/send', [TestEmailController::class, 'send'])->name('test-email.send');

    // Route::get('/settings/account', function () {
    //     return view('profile/show');
    // })->name('account');
    // Route::get('/settings/account', [DashboardController::class, 'showProfile'])->name('profile.showx');

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

    Route::post('/users/{id}/reset-password', [UsersController::class, 'resetPassword'])->name('users.reset-password');
    Route::post('/users/{id}/impersonate', [UsersController::class, 'impersonate'])->name('users.impersonate');

    // === APPLICATION MASTER ===
    Route::get('/applications', [SysApplicationController::class, 'index'])->name('applications');
    Route::get('/applications/json', [SysApplicationController::class, 'json'])->name('applications.json');
    Route::post('/applications', [SysApplicationController::class, 'store'])->name('applications.store');
    Route::get('/applications/{id}/edit', [SysApplicationController::class, 'edit'])->name('applications.edit');
    Route::put('/applications/{id}', [SysApplicationController::class, 'update'])->name('applications.update');
    Route::put('/applications/{id}/toggle-status', [SysApplicationController::class, 'toggleStatus'])->name('applications.toggle-status');

    // === SCREEN MASTER ===
    Route::get('/screens', [SysScreenController::class, 'index'])->name('screens');
    Route::get('/screens/json', [SysScreenController::class, 'json'])->name('screens.json');
    Route::post('/screens', [SysScreenController::class, 'store'])->name('screens.store');
    Route::get('/screens/{id}/edit', [SysScreenController::class, 'edit'])->name('screens.edit');
    Route::put('/screens/{id}', [SysScreenController::class, 'update'])->name('screens.update');
    Route::put('/screens/{id}/toggle-status', [SysScreenController::class, 'toggleStatus'])->name('screens.toggle-status');

    Route::get('/menus', [SysMenuController::class, 'index'])->name('menus');
    Route::get('/menus/json', [SysMenuController::class, 'json'])->name('menus.json');
    Route::post('/menus', [SysMenuController::class, 'store'])->name('menus.store');
    Route::get('/menus/{id}/edit', [SysMenuController::class, 'edit'])->name('menus.edit');
    Route::put('/menus/{id}', [SysMenuController::class, 'update'])->name('menus.update');
    Route::put('/menus/{id}/toggle-status', [SysMenuController::class, 'toggleStatus'])->name('menus.toggle-status');

    Route::get('/roles', [SysRoleController::class, 'index'])->name('roles');
    Route::get('/roles/json', [SysRoleController::class, 'json'])->name('roles.json');
    Route::post('/roles', [SysRoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{id}/edit', [SysRoleController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{id}', [SysRoleController::class, 'update'])->name('roles.update');
    Route::put('/roles/{id}/toggle-status', [SysRoleController::class, 'toggleStatus'])->name('roles.toggle-status');

    // ================== SYS ROLE MENU ==================
    Route::get('/role-menus', [SysRoleMenuController::class, 'index'])->name('role_menus');
    Route::get('/role-menus/json', [SysRoleMenuController::class, 'json'])->name('role_menus.json');
    Route::post('/role-menus', [SysRoleMenuController::class, 'store'])->name('role_menus.store');
    Route::get('/role-menus/{id}/edit', [SysRoleMenuController::class, 'edit'])->name('role_menus.edit');
    Route::put('/role-menus/{id}', [SysRoleMenuController::class, 'update'])->name('role_menus.update');
    Route::put('/role-menus/{id}/toggle-status', [SysRoleMenuController::class, 'toggleStatus'])->name('role_menus.toggle-status');

    // ================== SYS ACCESS RIGHT ==================
    Route::get('/access-rights', [SysAccessRightController::class, 'index'])->name('access_rights');
    Route::get('/access-rights/json', [SysAccessRightController::class, 'json'])->name('access_rights.json');
    Route::post('/access-rights', [SysAccessRightController::class, 'store'])->name('access_rights.store');
    Route::get('/access-rights/{id}/edit', [SysAccessRightController::class, 'edit'])->name('access_rights.edit');
    Route::put('/access-rights/{id}', [SysAccessRightController::class, 'update'])->name('access_rights.update');
    Route::put('/access-rights/{id}/toggle-status', [SysAccessRightController::class, 'toggleStatus'])->name('access_rights.toggle-status');

    Route::get('/approvals', [MsApprovalController::class, 'index'])->name('approvals');
    Route::get('/approvals/json', [MsApprovalController::class, 'json'])->name('approvals.json');
    Route::post('/approvals', [MsApprovalController::class, 'store'])->name('approvals.store');
    Route::get('/approvals/{id}/edit', [MsApprovalController::class, 'edit']);
    Route::put('/approvals/{id}', [MsApprovalController::class, 'update']);
    Route::put('/approvals/{id}/toggle-status', [MsApprovalController::class, 'toggleStatus']);
    Route::get('/approvals/departments', [MsApprovalController::class, 'departmentHR'])->name('approvals.departments');

    Route::get('/approvals-groupbiaya', [MsApprovalGroupBiayaController::class, 'index'])->name('approvalsgroupbiaya');
    Route::get('/approvals-groupbiaya/json', [MsApprovalGroupBiayaController::class, 'json'])->name('approvalsgroupbiaya.json');
    Route::post('/approvals-groupbiaya', [MsApprovalGroupBiayaController::class, 'store'])->name('approvalsgroupbiaya.store');
    Route::get('/approvals-groupbiaya/{id}/edit', [MsApprovalGroupBiayaController::class, 'edit'])->name('approvalsgroupbiaya.edit');
    Route::put('/approvals-groupbiaya/{id}', [MsApprovalGroupBiayaController::class, 'update'])->name('approvalsgroupbiaya.update');
    Route::put('/approvals-groupbiaya/{id}/toggle-status', [MsApprovalGroupBiayaController::class, 'toggleStatus'])->name('approvalsgroupbiaya.toggle');
    Route::get('/approvals-groupbiaya/departments/list', [MsApprovalGroupBiayaController::class, 'departments'])->name('approvalsgroupbiaya.departments');

    Route::get('/companies', [CompanyController::class, 'index'])->name('companies');
    Route::get('/companies/json', [CompanyController::class, 'json'])->name('companies.json');
    Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
    Route::get('/companies/{id}/edit', [CompanyController::class, 'edit'])->name('companies.edit');
    Route::put('/companies/{id}', [CompanyController::class, 'update'])->name('companies.update');
    Route::put('/companies/{id}/toggle-status', [CompanyController::class, 'toggleStatus'])->name('companies.toggle-status');

    Route::get('/department', [DepartmentsController::class, 'index'])->name('department');
    Route::get('/department/json', [DepartmentsController::class, 'json'])->name('department.json');
    Route::post('/department', [DepartmentsController::class, 'store'])->name('department.store');
    Route::get('/department/{id}/edit', [DepartmentsController::class, 'edit'])->name('department.edit');
    Route::put('/department/{id}', [DepartmentsController::class, 'update'])->name('department.update');
    Route::put('/department/{id}/toggle-status', [DepartmentsController::class, 'toggleStatus'])->name('department.toggle-status');

    Route::get('/categories', [MsCategoryController::class, 'index'])->name('categories');
    Route::get('/categories/json', [MsCategoryController::class, 'json'])->name('categories.json');
    Route::post('/categories', [MsCategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{id}/edit', [MsCategoryController::class, 'edit']);
    Route::put('/categories/{id}', [MsCategoryController::class, 'update']);
    Route::put('/categories/{id}/toggle-status', [MsCategoryController::class, 'toggleStatus']);

    Route::get('/autonbrs', [AutonbrController::class, 'index'])->name('autonbrs');
    Route::get('/autonbrs/json', [AutonbrController::class, 'json'])->name('autonbrs.json');
    Route::post('/autonbrs', [AutonbrController::class, 'store'])->name('autonbrs.store');
    Route::get('/autonbrs/{id}/edit', [AutonbrController::class, 'edit']);
    Route::put('/autonbrs/{id}', [AutonbrController::class, 'update']);
    Route::put('/autonbrs/{id}/toggle-status', [AutonbrController::class, 'toggleStatus']);

    Route::get('/vendors', [VendorController::class, 'index'])->name('vendors');
    Route::get('/vendors/json', [VendorController::class, 'json'])->name('vendors.json');
    Route::post('/vendors', [VendorController::class, 'store'])->name('vendors.store');
    Route::get('/vendors/{id}/edit', [VendorController::class, 'edit'])->name('vendors.edit');
    Route::put('/vendors/{id}', [VendorController::class, 'update'])->name('vendors.update');
    Route::put('/vendors/{id}/toggle-status', [VendorController::class, 'toggleStatus'])->name('vendors.toggle-status');
    Route::post('/vendors/sync', [VendorController::class, 'syncVendor'])->name('vendors.sync');

    Route::get('/inventories', [InventoryController::class, 'index'])->name('inventories');
    Route::get('/inventories/json', [InventoryController::class, 'json'])->name('inventories.json');
    Route::post('/inventories', [InventoryController::class, 'store'])->name('inventories.store');
    Route::get('/inventories/{id}/edit', [InventoryController::class, 'edit'])->name('inventories.edit');
    Route::put('/inventories/{id}', [InventoryController::class, 'update'])->name('inventories.update');
    Route::put('/inventories/{id}/toggle-status', [InventoryController::class, 'toggleStatus'])->name('inventories.toggle-status');

    Route::get('/inventories-user', [InventoryUserController::class, 'index'])->name('inventories-user');
    Route::get('/inventories-user/json', [InventoryUserController::class, 'json'])->name('inventories-user.json');

    Route::get('/locations', [LocationController::class, 'index'])->name('locations');

    Route::get('/locations/json', [LocationController::class, 'locationJson'])->name('locations.json');
    Route::post('/locations', [LocationController::class, 'storeLocation'])->name('locations.store');
    Route::get('/locations/{id}/edit', [LocationController::class, 'editLocation'])->name('locations.edit');
    Route::put('/locations/{id}', [LocationController::class, 'updateLocation'])->name('locations.update');
    Route::put('/locations/{id}/toggle-status', [LocationController::class, 'toggleLocationStatus'])->name('locations.toggle-status');

    Route::get('/sub-locations/json', [LocationController::class, 'subLocationJson'])->name('sub_locations.json');
    Route::post('/sub-locations', [LocationController::class, 'storeSubLocation'])->name('sub_locations.store');
    Route::get('/sub-locations/{id}/edit', [LocationController::class, 'editSubLocation'])->name('sub_locations.edit');
    Route::put('/sub-locations/{id}', [LocationController::class, 'updateSubLocation'])->name('sub_locations.update');
    Route::put('/sub-locations/{id}/toggle-status', [LocationController::class, 'toggleSubLocationStatus'])->name('sub_locations.toggle-status');

    Route::get('/tops', [TopController::class, 'index'])->name('tops');
    Route::get('/tops/json', [TopController::class, 'topJson'])->name('tops.json');
    Route::post('/tops', [TopController::class, 'storeTop'])->name('tops.store');
    Route::get('/tops/{id}/edit', [TopController::class, 'editTop'])->name('tops.edit');
    Route::put('/tops/{id}', [TopController::class, 'updateTop'])->name('tops.update');
    Route::put('/tops/{id}/toggle-status', [TopController::class, 'toggleTopStatus'])->name('tops.toggle-status');

    Route::get('/top-details/json', [TopController::class, 'topDetailJson'])->name('top_details.json');
    Route::post('/top-details', [TopController::class, 'storeTopDetail'])->name('top_details.store');
    Route::get('/top-details/{id}/edit', [TopController::class, 'editTopDetail'])->name('top_details.edit');
    Route::put('/top-details/{id}', [TopController::class, 'updateTopDetail'])->name('top_details.update');
    Route::put('/top-details/{id}/toggle-status', [TopController::class, 'toggleTopDetailStatus'])->name('top_details.toggle-status');

    Route::get('/tenants', [TenantController::class, 'index'])->name('tenants');
    Route::get('/tenants/json', [TenantController::class, 'json'])->name('tenants.json');
    Route::post('/tenants', [TenantController::class, 'store'])->name('tenants.store');
    Route::get('/tenants/{id}/edit', [TenantController::class, 'edit'])->name('tenants.edit');
    Route::put('/tenants/{id}', [TenantController::class, 'update'])->name('tenants.update');
    Route::put('/tenants/{id}/toggle-status', [TenantController::class, 'toggleStatus'])->name('tenants.toggle-status');

    Route::get('/sys-calendar', [SysCalendarController::class, 'index'])->name('sys-calendar');
    Route::get('/sys-calendar/json', [SysCalendarController::class, 'json'])->name('sys-calendar.json');
    Route::post('/sys-calendar', [SysCalendarController::class, 'store'])->name('sys-calendar.store');
    Route::get('/sys-calendar/{id}/edit', [SysCalendarController::class, 'edit'])->name('sys-calendar.edit');
    Route::post('/sys-calendar/{id}', [SysCalendarController::class, 'update'])->name('sys-calendar.update');
    Route::put('/sys-calendar/{id}/toggle-status', [SysCalendarController::class, 'toggleStatus'])->name('sys-calendar.toggle-status');

    Route::get('/business-units', [BusinessUnitController::class, 'index'])->name('business-units');
    Route::get('/business-units/json', [BusinessUnitController::class, 'json'])->name('business-units.json');
    Route::post('/business-units', [BusinessUnitController::class, 'store'])->name('business-units.store');
    Route::get('/business-units/{id}/edit', [BusinessUnitController::class, 'edit'])->name('business-units.edit');
    Route::put('/business-units/{id}', [BusinessUnitController::class, 'update'])->name('business-units.update');
    Route::put('/business-units/{id}/toggle-status', [BusinessUnitController::class, 'toggleStatus'])->name('business-units.toggle-status');

    Route::get('/attachments-master', [AttachmentMasterController::class, 'index'])->name('attachments-master');
    Route::get('/attachments-master/json', [AttachmentMasterController::class, 'json'])->name('attachments-master.json');
    Route::put('/attachments-master/{id}/toggle-status', [AttachmentMasterController::class, 'toggleStatus'])->name('attachments-master.toggle-status');
    Route::delete('/attachments/{id}', [AttachmentMasterController::class, 'delete'])
        ->name('attachments.delete');

    Route::get('/kendaraan', [KendaraanController::class, 'index'])->name('kendaraan');
    Route::get('/kendaraan/json', [KendaraanController::class, 'json'])->name('kendaraan.json');
    Route::post('/kendaraan', [KendaraanController::class, 'store'])->name('kendaraan.store');
    Route::get('/kendaraan/{id}/edit', [KendaraanController::class, 'edit'])->name('kendaraan.edit');
    Route::put('/kendaraan/{id}', [KendaraanController::class, 'update'])->name('kendaraan.update');
    Route::put('/kendaraan/{id}/toggle-status', [KendaraanController::class, 'toggleStatus'])->name('kendaraan.toggle-status');

    Route::get('/groupbiaya-nonpurch', [MsGroupbiayaNonPurchController::class, 'index'])->name('groupbiayanonpurch');
    Route::get('/groupbiaya-nonpurch/json', [MsGroupbiayaNonPurchController::class, 'json'])->name('groupbiayanonpurch.json');
    Route::post('/groupbiaya-nonpurch/store', [MsGroupbiayaNonPurchController::class, 'store'])->name('groupbiayanonpurch.store');
    Route::get('/groupbiaya-nonpurch/{id}/edit', [MsGroupbiayaNonPurchController::class, 'edit'])->name('groupbiayanonpurch.edit');
    Route::put('/groupbiaya-nonpurch/{id}', [MsGroupbiayaNonPurchController::class, 'update'])->name('groupbiayanonpurch.update');
    Route::put('/groupbiaya-nonpurch/{id}/toggle-status', [MsGroupbiayaNonPurchController::class, 'toggleStatus'])->name('groupbiayanonpurch.toggle-status');

    Route::middleware(['auth'])->group(function () {
        // halaman setting + tombol run
        Route::get('/integration/acumvms-staging', [AcumVmsStagingController::class, 'index'])
            ->name('integration.acumvms.index');
        // simpan window last_update / next_update
        Route::post('/integration/acumvms-staging/save', [AcumVmsStagingController::class, 'saveWindow'])
            ->name('integration.acumvms.save');
        // run manual (bisa submit normal / ajax)
        Route::post('/integration/acumvms-staging/run', [AcumVmsStagingController::class, 'runNow'])
            ->name('integration.acumvms.run');
        // status lock (polling)
        Route::get('/integration/acumvms-staging/status', [AcumVmsStagingController::class, 'status'])
            ->name('integration.acumvms.status');
    });

    Route::get('/user-sync', [UserSyncController::class, 'index'])->name('user_sync.index');
    Route::post('/user-sync/run', [UserSyncController::class, 'run'])->name('user_sync.run');

    // User must be logged in to START OAuth
    Route::get('/google/calendar/connect', [GoogleCalendarController::class, 'redirect'])
        ->middleware('auth');

    // CALLBACK MUST BE PUBLIC (Google redirect)
    Route::get('/google/calendar/callback', [GoogleCalendarController::class, 'callback']);

    /*
|--------------------------------------------------------------------------
| AJAX (Session-based, CSRF-protected)
|--------------------------------------------------------------------------
*/
    Route::middleware('auth')->group(function () {
        // 🔹 Google calendar (read / write)
        Route::get('/google/calendar/status', [GoogleCalendarApiController::class, 'status']);
        Route::get('/google/calendar/events', [GoogleCalendarApiController::class, 'events']);
        Route::post('/google/calendar/event', [GoogleCalendarApiController::class, 'createEvent']);
        Route::post('/google/calendar/disconnect', [GoogleCalendarController::class, 'disconnect']);

        // 🔹 Tasks
        Route::post('/tasks', [TaskController::class, 'store']);
        Route::put('/tasks/{id}', [TaskController::class, 'update']);
        Route::post('/tasks/{id}/move', [TaskController::class, 'move']);
        Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
    });

    Route::prefix('mastertraining')->group(function () {
        Route::get('/mastertraining', [MasterTrainingController::class, 'index'])
            ->name('mastertraining');

        Route::get('/create', [MasterTrainingController::class, 'create'])
            ->name('mastertraining.create');

        Route::post('/store', [MasterTrainingController::class, 'store'])
            ->name('mastertraining.store');

        Route::get('/{id}', [MasterTrainingController::class, 'show'])
            ->name('mastertraining.show');

        Route::get('/mastertraining/{id}/edit', [MasterTrainingController::class, 'edit'])->name('mastertraining.edit');

        Route::put('/mastertraining/{id}', [MasterTrainingController::class, 'update'])
            ->name('mastertraining.update');
    });

    Route::get('/training-events', [TrainingRegistrationController::class, 'index'])
        ->name('training');

    Route::get(
        '/training/{id}/register',
        [TrainingRegistrationController::class, 'showRegisterForm']
    )->name('training.register.form');

    Route::post(
        '/training/{id}/register',
        [TrainingRegistrationController::class, 'register']
    )->name('training.register');
    Route::get('/training', [TrainingRegistrationController::class, 'index'])
        ->name('training.list');

    Route::get('/manual/{root?}/{parent?}/{child?}', function ($root = null, $parent = null, $child = null) {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        /*
        |--------------------------------------------------------------------------
        | 1️⃣ Get Allowed Menu IDs Based On User Role
        |--------------------------------------------------------------------------
        */

        $allowedIds = SysRoleMenu::where('role_id', $user->user_role)
            ->where('status', 'A')
            ->pluck('menu_id')
            ->toArray();

        if (empty($allowedIds)) {
            return view('manual.layout', [
                'rootMenus' => collect(),
                'root' => $root,
                'parent' => $parent,
                'child' => $child,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 2️⃣ Add Parent Chain (Ensure Root & Parent Visible)
        |--------------------------------------------------------------------------
        */

        $allMenus = SysMenu::where('status', 'A')
            ->get()
            ->keyBy('menu_id');

        foreach ($allowedIds as $id) {
            $menu = $allMenus->get($id);

            while ($menu && $menu->parent_menu_id) {
                if (!in_array($menu->parent_menu_id, $allowedIds)) {
                    $allowedIds[] = $menu->parent_menu_id;
                }

                $menu = $allMenus->get($menu->parent_menu_id);
            }
        }

        $allowedIds = array_unique($allowedIds);

        /*
        |--------------------------------------------------------------------------
        | 3️⃣ Build Sidebar Tree Based On Allowed IDs
        |--------------------------------------------------------------------------
        */

        $rootMenus = SysMenu::whereNull('parent_menu_id')
            ->whereIn('menu_id', $allowedIds)
            ->where('status', 'A')
            ->with(['children' => function ($q) use ($allowedIds) {
                $q->whereIn('menu_id', $allowedIds)
                    ->where('status', 'A')
                    ->with(['children' => function ($qq) use ($allowedIds) {
                        $qq->whereIn('menu_id', $allowedIds)
                            ->where('status', 'A');
                    }]);
            }])
            ->orderBy('menu_sort_order')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 4️⃣ Secure Direct URL Access (Root / Parent / Child)
        |--------------------------------------------------------------------------
        */

        $currentMenu = null;

        if ($child) {
            $currentMenu = SysMenu::where('menu_slug', $child)
                ->where('status', 'A')
                ->first();
        } elseif ($parent) {
            $currentMenu = SysMenu::where('menu_slug', $parent)
                ->where('status', 'A')
                ->first();
        } elseif ($root) {
            $currentMenu = SysMenu::where('menu_slug', $root)
                ->where('status', 'A')
                ->first();
        }

        if ($currentMenu && !in_array($currentMenu->menu_id, $allowedIds)) {
            abort(403, 'Unauthorized manual access.');
        }

        /*
        |--------------------------------------------------------------------------
        | 5️⃣ Return View
        |--------------------------------------------------------------------------
        */

        return view('manual.layout', compact('rootMenus', 'root', 'parent', 'child'));
    })->middleware('auth')->name('manual');

    Route::prefix('report-warehouse')->group(function () {
        Route::get('/', [ReportWarehouseController::class, 'index'])
            ->name('reportwh');

        Route::get('/json', [ReportWarehouseController::class, 'json'])
            ->name('report.warehouse.json');

        Route::get('/export', [ReportWarehouseController::class, 'export'])
            ->name('report.warehouse.export');

        Route::get('/inventory/search', [ReportWarehouseController::class, 'searchInventory'])
            ->name('inventory.search');

        Route::get('/view/{type}', function ($type) {
            if ($type == 'spb') {
                return view('pages.report-warehouse.spb-detail');
            }

            if ($type == 'issue') {
                return view('pages.report-warehouse.issue-detail');
            }

            if ($type == 'receipt') {
                return view('pages.report-warehouse.receipt-detail');
            }
        });
    });

    Route::prefix('report-purchasing')->group(function () {
        Route::get('/', [ReportPurchasingController::class, 'index'])
            ->name('reportpurchasing');

        Route::get('/json', [ReportPurchasingController::class, 'json'])
            ->name('report.purchasing.json');

        Route::get('/export', [ReportPurchasingController::class, 'export'])
            ->name('report.purchasing.export');
    });

    Route::prefix('report-cs')->group(function () {
        Route::get('/', [ReportCanvassSheetController::class, 'index'])
            ->name('reportcs');

        Route::get('/json', [ReportCanvassSheetController::class, 'json'])
            ->name('report.cs.json');

        Route::get('/tracking/json', [ReportCanvassSheetController::class, 'trackingJson'])
            ->name('report.cs.tracking.json');

        Route::get('/export', [ReportCanvassSheetController::class, 'export'])
            ->name('report.cs.export');

        Route::get('/view/detail', function () {
            return view('pages.report-cs.canvas-detail');
        });

        Route::get('/view/tracking', function () {
            return view('pages.report-cs.canvas-tracking');
        });

        Route::get('/cs/{hash}/tracking', [ReportCanvassSheetController::class, 'tracking']);
    });

    Route::prefix('report-operational')->group(function () {
        Route::get('/', [ReportOperationalController::class, 'index'])
            ->name('reportoperational');

        Route::get('/json', [ReportOperationalController::class, 'json'])
            ->name('report.operational.json');

        Route::get('/export', [ReportOperationalController::class, 'export'])
            ->name('report.operational.export');

        Route::get('/view/operational', function () {
            return view('pages.report-operational.wo-detail');
        });
    });

    Route::prefix('report-bast')->group(function () {
        Route::get('/', [ReportBastController::class, 'index'])
            ->name('reportbast');

        Route::get('/json', [ReportBastController::class, 'json'])
            ->name('report.bast.json');

        Route::get('/export', [ReportBastController::class, 'export'])
            ->name('report.bast.export');

        Route::get('/view/detail', function () {
            return view('pages.report-bast.bast-detail');
        });
    });

    Route::prefix('report-fixedassets')->middleware('access:REPORTFA,VIEW')->group(function () {
        Route::get('/', [ReportFixedAssetController::class, 'index'])
            ->name('reportfixedassets');

        Route::get('/json', [ReportFixedAssetController::class, 'json'])
            ->name('report.fa.json');

        Route::get('/export', [ReportFixedAssetController::class, 'export'])
            ->name('report.fa.export');
    });

    Route::prefix('report-ga')->group(function () {
        Route::get('/', [ReportGeneralGAController::class, 'index'])
            ->name('reportga');

        Route::get('/json/{type}', [ReportGeneralGAController::class, 'json'])
            ->name('report.ga.json');

        Route::get('/export/{type}', [ReportGeneralGAController::class, 'export'])
            ->name('report.ga.export');

        Route::get('/view/{type}', function ($type) {
            return view('pages.report.'.$type);
        });
    });

    // Route::middleware('access:REPORTCS,VIEW')->group(function () {

    // });
    // === IFCA Integration MASTER ===
    // Route::get('/ifcaintegration', [IFCAIntegrationController::class, 'index'])->name('integration.ifcaintegration');
    // Route::get('/ifcaintegration/nonstock', [IFCAIntegrationController::class, 'nonStockList'])->name('integration.ifcaintegration.nonstock.list');
    // Route::post('/ifcaintegration/nonstock/process', [IFCAIntegrationController::class, 'processNonStock'])->name('integration.ifcaintegration.nonstock.process');
    Route::prefix('integration')->name('integration.')->group(function () {
        // UI shell
        Route::get('ifcaintegration', [IFCAIntegrationController::class, 'index'])
            ->name('ifcaintegration');

        // module: NonStock API endpoints
        Route::prefix('ifcaintegration/nonstock')->name('ifcaintegration.nonstock.')->group(function () {
            Route::get('list', [IFCAAPINonStockController::class, 'list'])->name('list');
            Route::post('process', [IFCAAPINonStockController::class, 'process'])->name('process');
        });

        // ✅ module: Stock API endpoints
        Route::prefix('ifcaintegration/stock')->name('ifcaintegration.stock.')->group(function () {
            Route::get('list', [IFCAAPIStockController::class, 'list'])->name('list');
            Route::post('process', [IFCAAPIStockController::class, 'process'])->name('process');
        });

        // ✅ module: Supplier API endpoints
        Route::prefix('ifcaintegration/supplier')->name('ifcaintegration.supplier.')->group(function () {
            Route::get('list', [IFCAAPISupplierController::class, 'list'])->name('list');
            Route::post('process', [IFCAAPISupplierController::class, 'process'])->name('process');
        });

        // ✅ module: PO API endpoints
        Route::prefix('ifcaintegration/po')->name('ifcaintegration.po.')->group(function () {
            Route::get('list', [IFCAAPIPOController::class, 'list'])->name('list');
            Route::get('/filters', [IFCAAPIPOController::class, 'filters'])->name('filters');
            Route::post('process', [IFCAAPIPOController::class, 'process'])->name('process');
        });

        // ✅ module: GRN API endpoints
        Route::prefix('ifcaintegration/grn')->name('ifcaintegration.grn.')->group(function () {
            Route::get('/filters', [IFCAAPIGRNController::class, 'filters'])->name('filters');
            Route::get('list', [IFCAAPIGRNController::class, 'list'])->name('list');
            Route::post('process', [IFCAAPIGRNController::class, 'process'])->name('process');
        });

        // ✅ module: BAST API endpoints
        Route::prefix('ifcaintegration/bast')->name('ifcaintegration.bast.')->group(function () {
            Route::get('/filters', [IFCAAPIBASTController::class, 'filters'])->name('filters');
            Route::get('list', [IFCAAPIBASTController::class, 'list'])->name('list');
            Route::post('process', [IFCAAPIBASTController::class, 'process'])->name('process');
        });

        // ✅ module: ISSUE API endpoints
        Route::prefix('ifcaintegration/issue')->name('ifcaintegration.issue.')->group(function () {
            Route::get('filters', [IFCAAPIIssueController::class, 'filters'])->name('filters');
            Route::get('list', [IFCAAPIIssueController::class, 'list'])->name('list');
            Route::post('process', [IFCAAPIIssueController::class, 'process'])->name('process');
        });

        // ✅ module: ISSUE API endpoints (Solomon)
        Route::prefix('ifcaintegration/issuesolomon')->name('ifcaintegration.issuesolomon.')->group(function () {
            Route::get('list', [SLAPIIssueController::class, 'list'])->name('list');
            Route::get('filters', [SLAPIIssueController::class, 'filters'])->name('filters');
            Route::post('process', [SLAPIIssueController::class, 'process'])->name('process');
        });

        // ✅ module: GRN Solomon
        Route::prefix('ifcaintegration/grnsolomon')->name('ifcaintegration.grnsolomon.')->group(function () {
            Route::get('list', [SLAPIGRNController::class, 'list'])->name('list');
            Route::get('/filters', [SLAPIGRNController::class, 'filters'])->name('filters');
            Route::post('process', [SLAPIGRNController::class, 'process'])->name('process');
        });

        // ✅ module: PO Solomon
        Route::prefix('ifcaintegration/posolomon')->name('ifcaintegration.posolomon.')->group(function () {
            Route::get('list', [SLAPIPOController::class, 'list'])->name('list');
            Route::get('/filters', [SLAPIPOController::class, 'filters'])->name('filters');
            Route::post('process', [SLAPIPOController::class, 'process'])->name('process');
        });
    });
});
