<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth; // baru
use App\Http\Controllers\M_approvalController;
use App\Http\Controllers\TrController;
use App\Http\Controllers\KendaraanController;
use App\Http\Controllers\TalentaController;
use App\Http\Controllers\CartrackController;
use App\Http\Controllers\M_approvalgroupbiayaController;
use App\Http\Controllers\APIController;
use App\Http\Controllers\ZoomController;
use App\Http\Controllers\ZoomOAuthController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\ZoomXController;
use App\Http\Controllers\StagingController;
use App\Http\Controllers\RFPController;
use App\Http\Controllers\Integrasi\StagingvmsController;


Route::get('/', function () {   
    return view('auth.login');
});

Route::get('/generate_pdf', [App\Http\Controllers\PaymentController::class, 'generate_pdf']);
    
// Route::get('/zoom/auth', [MeetingController::class, 'authorizeZoom'])->name('zoom.authorize');
// Route::get('/zoom/callback', [MeetingController::class, 'callback']);  
Route::get('/access_zoom_auth', [MeetingController::class, 'access_zoom_auth']); 
Route::get('/zoom_auth', [ZoomXController::class, 'getAccessToken']);
Route::get('/schedule', [ZoomXController::class, 'scheduleMeeting']);
Route::get('/createMeeting', [ZoomXController::class, 'createMeeting']);  

//API
Route::post('/api/login', [App\Http\Controllers\APIController::class, 'login']);
Route::post('/api/data', [App\Http\Controllers\APIController::class, 'data']);
Route::post('/api/checkin', [App\Http\Controllers\APIController::class, 'checkin']);
Route::post('/api/checkout', [App\Http\Controllers\APIController::class, 'checkout']);

Route::get('/sendemail', [App\Http\Controllers\SendemailController::class, 'index']);
Route::get('/sendemailrevise', [App\Http\Controllers\SendemailController::class, 'revise']);
Route::get('/showroommeet_{id}', [App\Http\Controllers\RoommeetController::class, 'show_room']);
Route::get('/checkinroomx_{id}', [App\Http\Controllers\RoommeetController::class, 'checkin_room_screen']);
Route::get('/checkoutroomx_{id}', [App\Http\Controllers\RoommeetController::class, 'checkout_room_screen']);
Route::get('/talenta', [TalentaController::class, 'insert_users']);
Route::get('/employee_all', [TalentaController::class, 'employee_all']);
Route::get('/branch', [TalentaController::class, 'get_branch']);
Route::get('/organization', [TalentaController::class, 'get_dept']);
Route::get('/joblevel', [TalentaController::class, 'get_joblevel']);
Route::get('/vehicle', [CartrackController::class, 'vehicle_list']);
Route::get('/trips', [CartrackController::class, 'all_trips']);
Route::get('/fuel', [CartrackController::class, 'vehicle_fuel_fills']);
Route::get('/temp', [CartrackController::class, 'temp']);
Route::get('/tfvms', [StagingvmsController::class, 'transfer_vendor_acumatica']); 
Route::get('/employee_manual', [TalentaController::class, 'employee_manual']);
Route::get('/check_staging_vms', [StagingController::class, 'check_staging_vms']);

Auth::routes();

Route::middleware(['auth'])->group(function () {

    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    
    // Route::get('/dashboardbi', function () {   
    //     return view('dashboard_bi');
    // });
    Route::get('/dashboardbi', [App\Http\Controllers\HomeController::class, 'dashboardbi']);
    Route::get('/dash_bookingcar', [App\Http\Controllers\HomeController::class, 'dash_bookingcar']);
    Route::get('/dash_vouchertaxi', [App\Http\Controllers\HomeController::class, 'dash_vouchertaxi']);
    Route::get('/dash_parking', [App\Http\Controllers\HomeController::class, 'dash_parking']);
    Route::get('/dash_purchasing', [App\Http\Controllers\HomeController::class, 'dash_purchasing']);
    Route::get('/dash_warehouse', [App\Http\Controllers\HomeController::class, 'dash_warehouse']);
    Route::get('/dash_finance', [App\Http\Controllers\HomeController::class, 'dash_finance']);
    Route::get('/dash_ticket', [App\Http\Controllers\HomeController::class, 'dash_ticket']);
    Route::get('/dash_test', [App\Http\Controllers\HomeController::class, 'dash_test']);
    Route::get('/dash_adv_pms', [App\Http\Controllers\HomeController::class, 'dash_adv_pms']);
    Route::get('/dash_adv_bookingcar', [App\Http\Controllers\HomeController::class, 'dash_adv_bookingcar']);
    Route::get('/dash_test', [App\Http\Controllers\HomeController::class, 'dash_test']);

    Route::get('/hierarchychart', [App\Http\Controllers\HomeController::class, 'hierarchychart']);

    Route::middleware(['admin'])->group(function () {
        Route::get('admin', [AdminController::class, 'index'])->name('admin');
    });

    Route::middleware(['user'])->group(function () {
        Route::get('user', [UserController::class, 'index']);
    });

    Route::get('/logout', function () {
        Auth::logout();

        redirect('/');
    });

    Route::get('/w_approved', [App\Http\Controllers\UserController::class, 'index']);
    Route::get('/approved', [App\Http\Controllers\UserController::class, 'approved']);

    Route::get('/w_approvedx', [App\Http\Controllers\AdminController::class, 'index']);
    Route::get('/approvedx', [App\Http\Controllers\AdminController::class, 'approved']);
    Route::get('/appacum_refresh', [App\Http\Controllers\AdminController::class, 'appacum_refresh']);
    Route::get('/appdas_refresh', [App\Http\Controllers\AdminController::class, 'appdas_refresh']);

    //users
    Route::get('/users', [App\Http\Controllers\UsersController::class, 'index']);
    Route::get('/addusers', [App\Http\Controllers\UsersController::class, 'add']);
    Route::post('/saveusers', [App\Http\Controllers\UsersController::class, 'save']);
    Route::get('/editusers_{id}', [App\Http\Controllers\UsersController::class, 'edit']);
    Route::put('/updateusers_{id}', [App\Http\Controllers\UsersController::class, 'update']);
    Route::get('/delusers_{id}', [App\Http\Controllers\UsersController::class, 'del']);
    Route::put('/resetusers_{id}', [App\Http\Controllers\UsersController::class, 'reset']);
    Route::get('/change_{id}', [App\Http\Controllers\UsersController::class, 'changepwd']);
    Route::post('/updatepwd_{id}', [App\Http\Controllers\UsersController::class, 'updatepwd']);
    Route::get('/addapp_{id}', [App\Http\Controllers\UsersController::class, 'addapp']);
    Route::post('/saveapp_{id}', [App\Http\Controllers\UsersController::class, 'saveapp']);
    Route::get('/editapp_{id}', [App\Http\Controllers\UsersController::class, 'editapp']);
    Route::put('/updateapp_{id}', [App\Http\Controllers\UsersController::class, 'updateapp']);
    Route::get('/delapp_{id}', [App\Http\Controllers\UsersController::class, 'delapp']);

    //company
    Route::get('/company', [App\Http\Controllers\CompanyController::class, 'index']);
    Route::get('/addcompany', [App\Http\Controllers\CompanyController::class, 'add']);
    Route::post('/savecompany', [App\Http\Controllers\CompanyController::class, 'save']);
    Route::get('/editcompany_{id}', [App\Http\Controllers\CompanyController::class, 'edit']);
    Route::put('/updatecompany_{id}', [App\Http\Controllers\CompanyController::class, 'update']);
    Route::get('/delcompany_{id}', [App\Http\Controllers\CompanyController::class, 'del']);


    //departement
    Route::get('/dept', [App\Http\Controllers\DeptController::class, 'index']);
    Route::get('/adddept', [App\Http\Controllers\DeptController::class, 'add']);
    Route::post('/savedept', [App\Http\Controllers\DeptController::class, 'save']);
    Route::get('/editdept_{id}', [App\Http\Controllers\DeptController::class, 'edit']);
    Route::put('/updatedept_{id}', [App\Http\Controllers\DeptController::class, 'update']);
    Route::get('/deldept_{id}', [App\Http\Controllers\DeptController::class, 'del']);

    //location
    // Route::get('/location', [App\Http\Controllers\LocationController::class, 'index']);
    Route::get('/addlocation', [App\Http\Controllers\LocationController::class, 'add']);
    Route::post('/savelocation', [App\Http\Controllers\LocationController::class, 'save']);
    Route::get('/editlocation_{id}', [App\Http\Controllers\LocationController::class, 'edit']);
    Route::put('/updatelocation_{id}', [App\Http\Controllers\LocationController::class, 'update']);
    Route::get('/dellocation_{id}', [App\Http\Controllers\LocationController::class, 'del']);

    //site
    Route::get('/site', [App\Http\Controllers\SiteController::class, 'index']);
    Route::get('/addsite', [App\Http\Controllers\SiteController::class, 'add']);
    Route::post('/savesite', [App\Http\Controllers\SiteController::class, 'save']);
    Route::get('/editsite_{id}', [App\Http\Controllers\SiteController::class, 'edit']);
    Route::put('/updatesite_{id}', [App\Http\Controllers\SiteController::class, 'update']);
    Route::get('/delsite_{id}', [App\Http\Controllers\SiteController::class, 'del']);

    // category
    Route::get('/category', [App\Http\Controllers\CategoryController::class, 'index']);
    Route::get('/addcategory', [App\Http\Controllers\CategoryController::class, 'add']);
    Route::post('/savecategory', [App\Http\Controllers\CategoryController::class, 'save']);
    Route::get('/editcategory_{id}', [App\Http\Controllers\CategoryController::class, 'edit']);
    Route::put('/updatecategory_{id}', [App\Http\Controllers\CategoryController::class, 'update']);
    Route::get('/delcategory_{id}', [App\Http\Controllers\CategoryController::class, 'del']);

    // subcategory
    Route::get('/subcategory', [App\Http\Controllers\SubcategoryController::class, 'index']);
    Route::get('/addsubcategory', [App\Http\Controllers\SubcategoryController::class, 'add']);
    Route::post('/savesubcategory', [App\Http\Controllers\SubcategoryController::class, 'save']);
    Route::get('/editsubcategory_{id}', [App\Http\Controllers\SubcategoryController::class, 'edit']);
    Route::put('/updatesubcategory_{id}', [App\Http\Controllers\SubcategoryController::class, 'update']);
    Route::get('/delsubcategory_{id}', [App\Http\Controllers\SubcategoryController::class, 'del']);

    //inventory
    Route::get('/inventory', [App\Http\Controllers\InventoryController::class, 'index']);
    Route::get('/addinventory', [App\Http\Controllers\InventoryController::class, 'add']);
    Route::post('/saveinventory', [App\Http\Controllers\InventoryController::class, 'save']);
    Route::get('/editinventory_{id}', [App\Http\Controllers\InventoryController::class, 'edit']);
    Route::put('/updateinventory_{id}', [App\Http\Controllers\InventoryController::class, 'update']);
    Route::get('/delinventory_{id}', [App\Http\Controllers\InventoryController::class, 'del']);
    Route::get('/checkacumatica', [App\Http\Controllers\InventoryController::class, 'checkacumatica']);
    Route::get('/addacumatica_{data}', [App\Http\Controllers\InventoryController::class, 'addacumatica']);
    Route::post('/saveacumatica', [App\Http\Controllers\InventoryController::class, 'saveacumatica']);

    //vendor
    Route::get('/vendor', [App\Http\Controllers\VendorController::class, 'index']);
    Route::get('/addvendor', [App\Http\Controllers\VendorController::class, 'add']);
    Route::post('/savevendor', [App\Http\Controllers\VendorController::class, 'save']);
    Route::get('/editvendor_{id}', [App\Http\Controllers\VendorController::class, 'edit']);
    Route::put('/updatevendor_{id}', [App\Http\Controllers\VendorController::class, 'update']);
    Route::get('/delvendor_{id}', [App\Http\Controllers\VendorController::class, 'del']);

     //autonbr
     Route::get('/autonbr', [App\Http\Controllers\AutonbrController::class, 'index']);
     Route::get('/addautonbr', [App\Http\Controllers\AutonbrController::class, 'add']);
     Route::post('/saveautonbr', [App\Http\Controllers\AutonbrController::class, 'save']);
     Route::get('/editautonbr_{id}', [App\Http\Controllers\AutonbrController::class, 'edit']);
     Route::put('/updateautonbr_{id}', [App\Http\Controllers\AutonbrController::class, 'update']);
     Route::get('/delautonbr_{id}', [App\Http\Controllers\AutonbrController::class, 'del']);

    //tr
    // Route::get('/tr', [App\Http\Controllers\TrController::class, 'index']);
    Route::get('/addtr', [App\Http\Controllers\TrController::class, 'add']);
    Route::post('/savetr', [App\Http\Controllers\TrController::class, 'save']);
    Route::get('/edittr_{id}', [App\Http\Controllers\TrController::class, 'edit']);
    Route::put('/updatetr_{id}', [App\Http\Controllers\TrController::class, 'updatetr']);
    Route::put('/updatetruser_{id}', [App\Http\Controllers\TrController::class, 'updatetruser']);
    Route::post('/savehardware_{id}', [App\Http\Controllers\TrController::class, 'savehardware']);
    Route::get('/deltr_{id}', [App\Http\Controllers\TrController::class, 'del']);
    Route::get('/showtr_{id}', [App\Http\Controllers\TrController::class, 'show']);
    Route::put('/approve_{id}', [App\Http\Controllers\TrController::class, 'approve']);
    Route::put('/reject_{id}', [App\Http\Controllers\TrController::class, 'reject']);
    Route::put('/revise_{id}', [App\Http\Controllers\TrController::class, 'revise']);    
    Route::get('/tr_waiting', [App\Http\Controllers\TrController::class, 'tr_waiting'])->name('tr_waiting.tr_waiting');    
    Route::get('/tr_completed', [App\Http\Controllers\TrController::class, 'tr_completed'])->name('tr_completed.tr_completed');    
    Route::get('/tr_rejected', [App\Http\Controllers\TrController::class, 'tr_rejected'])->name('tr_rejected.tr_rejected');     
    Route::get('/tr_all', [App\Http\Controllers\TrController::class, 'tr_all'])->name('tr_all.tr_all'); 
    Route::get('/tr_myjob', [App\Http\Controllers\TrController::class, 'tr_myjob']);
    Route::post('/saveacc_{id}', [App\Http\Controllers\TrController::class, 'saveacc']);
    Route::put('/process_{id}', [App\Http\Controllers\TrController::class, 'process_itchecked']);
    Route::get('/show_checked_{id}', [App\Http\Controllers\TrController::class, 'show_itchecked']);
    Route::get('/showinvt_{id}', [App\Http\Controllers\TrController::class, 'showinvt']);
    Route::post('/sendmsg_{id}', [App\Http\Controllers\TrController::class, 'sendmsg']);
    Route::get('/category_{id}', [App\Http\Controllers\TrController::class, 'getCategory']);
    Route::get('/deltrdetail_{id}', [App\Http\Controllers\TrController::class, 'deltrdetail']);
    Route::get('/tr_pdf_{id}', [App\Http\Controllers\TrController::class, 'print_pdf']);
    Route::get('/delattach_{id}', [App\Http\Controllers\TrController::class, 'delattach']);
    Route::post('/attach_{id}', [App\Http\Controllers\TrController::class, 'attach']);
    Route::put('/rollbacktr_{id}', [App\Http\Controllers\TrController::class, 'rollback']);
    Route::get('/tr_approval', [App\Http\Controllers\TrController::class, 'tr_approval']);
    Route::get('/tr_cancel_{id}', [App\Http\Controllers\TrController::class, 'cancel_doc']);
    Route::get('/sendmsgtr_{id}', [App\Http\Controllers\TrController::class, 'sendmsg_ajax']);
    Route::get('tr_waitingprocess', [App\Http\Controllers\TrController::class, 'tr_waitingprocess']);
    
    //Approval
    // Route::get('/mapproval', [App\Http\Controllers\M_approvalController::class, 'index']);
    Route::get('/addmapproval', [App\Http\Controllers\M_approvalController::class, 'add']);
    Route::post('/savemapproval', [App\Http\Controllers\M_approvalController::class, 'save']);
    Route::get('/editmapproval_{id}', [App\Http\Controllers\M_approvalController::class, 'edit']);
    Route::put('/updatemapproval_{id}', [App\Http\Controllers\M_approvalController::class, 'update']);
    Route::get('/delmapproval_{id}', [App\Http\Controllers\M_approvalController::class, 'del']);
    Route::get('/appfilter', [App\Http\Controllers\M_approvalController::class, 'appfilter']);
    // Route::get('mapproval', ['uses'=>'M_approvalController@index', 'as'=>'mapproval.index']);
    Route::get('mapproval', [M_approvalController::class, 'index'])->name('mapproval.index');

    // access
    // Route::get('/access', [App\Http\Controllers\AccessController::class, 'index']);
    Route::get('/addaccess', [App\Http\Controllers\AccessController::class, 'add']);
    Route::post('/saveaccess', [App\Http\Controllers\AccessController::class, 'save']);
    Route::get('/editaccess_{id}', [App\Http\Controllers\AccessController::class, 'edit']);
    Route::put('/updateaccess_{id}', [App\Http\Controllers\AccessController::class, 'update']);
    Route::get('/delaccess_{id}', [App\Http\Controllers\AccessController::class, 'del']);
    Route::get('/access_waiting', [App\Http\Controllers\AccessController::class, 'access_waiting'])->name('access_waiting.access_waiting');
    Route::get('/access_completed', [App\Http\Controllers\AccessController::class, 'access_completed'])->name('access_completed.access_completed');
    Route::get('/access_rejected', [App\Http\Controllers\AccessController::class, 'access_rejected'])->name('access_rejected.access_rejected');  
    Route::get('/access_all', [App\Http\Controllers\AccessController::class, 'access_all'])->name('access_all.access_all');
    Route::get('/access_myjob', [App\Http\Controllers\AccessController::class, 'access_myjob']);
    Route::get('/showaccess_{id}', [App\Http\Controllers\AccessController::class, 'show']);
    Route::put('/approveaccess_{id}', [App\Http\Controllers\AccessController::class, 'approve']);
    Route::put('/rejectaccess_{id}', [App\Http\Controllers\AccessController::class, 'reject']);
    Route::put('/reviseaccess_{id}', [App\Http\Controllers\AccessController::class, 'revise']);
    Route::put('/processaccess_{id}', [App\Http\Controllers\AccessController::class, 'process_checked']);
    // Route::post('/sendmsgaccess_{id}', [App\Http\Controllers\AccessController::class, 'sendmsg']);
    Route::get('/access_pdf_{id}', [App\Http\Controllers\AccessController::class, 'print_pdf']);
    Route::get('/delattachaccess_{id}', [App\Http\Controllers\AccessController::class, 'delattach']);
    Route::post('/attachaccess_{id}', [App\Http\Controllers\AccessController::class, 'attach']);
    Route::put('/rollbackaccess_{id}', [App\Http\Controllers\AccessController::class, 'rollback']);
    Route::put('/updateprocess_{id}', [App\Http\Controllers\AccessController::class, 'updateprocess']);
    // Route::get('todos/{id}/edit', 'AccessController@edit_process');
    Route::get('/edit_process_{todo}', [App\Http\Controllers\AccessController::class, 'edit_process']);
    Route::post('store_process', [App\Http\Controllers\AccessController::class, 'store_process']);
    Route::put('/updateuseracc_{id}', [App\Http\Controllers\AccessController::class, 'updateuser']);
    Route::get('/access_approval', [App\Http\Controllers\AccessController::class, 'access_approval']);
    Route::get('/access_cancel_{id}', [App\Http\Controllers\AccessController::class, 'cancel_doc']);
    Route::get('/sendmsgaccess_{id}', [App\Http\Controllers\AccessController::class, 'sendmsg_ajax']);

    // voucher
    // Route::get('/voucher', [App\Http\Controllers\VoucherController::class, 'index']);
    Route::get('/addvoucher', [App\Http\Controllers\VoucherController::class, 'add']);
    Route::post('/savevoucher', [App\Http\Controllers\VoucherController::class, 'save']);
    Route::get('/editvoucher_{id}', [App\Http\Controllers\VoucherController::class, 'edit']);
    Route::put('/updatevoucher_{id}', [App\Http\Controllers\VoucherController::class, 'update']);
    Route::get('/delvoucher_{id}', [App\Http\Controllers\VoucherController::class, 'del']);
    Route::get('/voucher_waiting', [App\Http\Controllers\VoucherController::class, 'voucher_waiting'])->name('voucher_waiting.voucher_waiting');
    Route::get('/voucher_completed', [App\Http\Controllers\VoucherController::class, 'voucher_completed'])->name('voucher_completed.voucher_completed');
    Route::get('/voucher_rejected', [App\Http\Controllers\VoucherController::class, 'voucher_rejected'])->name('voucher_rejected.voucher_rejected');    
    Route::get('/voucher_all', [App\Http\Controllers\VoucherController::class, 'voucher_all'])->name('voucher_all.voucher_all');
    Route::get('voucher_myjob', [App\Http\Controllers\VoucherController::class, 'voucher_myjob']);
    Route::put('/processvoucher_{id}', [App\Http\Controllers\VoucherController::class, 'process_checked']);
    Route::get('/showvoucher_{id}', [App\Http\Controllers\VoucherController::class, 'show']);
    Route::get('/voucher_pdf_{id}', [App\Http\Controllers\VoucherController::class, 'print_pdf']);
    // Route::post('/sendmsgvoucher_{id}', [App\Http\Controllers\VoucherController::class, 'sendmsg']);
    Route::put('/approvevoucher_{id}', [App\Http\Controllers\VoucherController::class, 'approve']);
    Route::put('/rejectvoucher_{id}', [App\Http\Controllers\VoucherController::class, 'reject']);
    Route::put('/revisevoucher_{id}', [App\Http\Controllers\VoucherController::class, 'revise']);
    Route::get('/delattachvoucher_{id}', [App\Http\Controllers\VoucherController::class, 'delattach']);
    Route::post('/attachvoucher_{id}', [App\Http\Controllers\VoucherController::class, 'attach']);
    Route::put('/rollbackvoucher_{id}', [App\Http\Controllers\VoucherController::class, 'rollback']);
    Route::put('/updateactual_{id}', [App\Http\Controllers\VoucherController::class, 'update_actual']);
    Route::put('/updateuser_{id}', [App\Http\Controllers\VoucherController::class, 'updateuser']);
    Route::get('/voucher_approval', [App\Http\Controllers\VoucherController::class, 'voucher_approval']);
    Route::get('/voucher_cancel_{id}', [App\Http\Controllers\VoucherController::class, 'cancel_doc']);
    Route::get('/sendmsgvoucher_{id}', [App\Http\Controllers\VoucherController::class, 'sendmsg_ajax']);
    Route::get('voucher_waitingprocess', [App\Http\Controllers\VoucherController::class, 'voucher_waitingprocess']);
    Route::get('voucher_completed_actual', [App\Http\Controllers\VoucherController::class, 'voucher_completed_actual']);
    //Budget
    // Route::get('/budget', [App\Http\Controllers\BudgetController::class, 'index']);
    Route::get('/addbudget', [App\Http\Controllers\BudgetController::class, 'add']);
    Route::post('/savebudget', [App\Http\Controllers\BudgetController::class, 'save']);
    Route::get('/editbudget_{id}', [App\Http\Controllers\BudgetController::class, 'edit']);
    Route::put('/updatebudget_{id}', [App\Http\Controllers\BudgetController::class, 'update']);
    Route::get('/delbudget_{id}', [App\Http\Controllers\BudgetController::class, 'del']);
    Route::get('/showbudget_{id}', [App\Http\Controllers\BudgetController::class, 'show']);
    Route::put('/approvebudget_{id}', [App\Http\Controllers\BudgetController::class, 'approve']);
    Route::put('/rejectbudget_{id}', [App\Http\Controllers\BudgetController::class, 'reject']);
    Route::put('/revisebudget_{id}', [App\Http\Controllers\BudgetController::class, 'revise']);
    Route::get('/budget_waiting', [App\Http\Controllers\BudgetController::class, 'budget_waiting'])->name('budget_waiting.budget_waiting');
    Route::get('/budget_completed', [App\Http\Controllers\BudgetController::class, 'budget_completed'])->name('budget_completed.budget_completed');
    Route::get('/budget_rejected', [App\Http\Controllers\BudgetController::class, 'budget_rejected'])->name('budget_rejected.budget_rejected');
    Route::get('/budget_all', [App\Http\Controllers\BudgetController::class, 'budget_all'])->name('budget_all.budget_all');   
    Route::get('/budget_myjob', [App\Http\Controllers\BudgetController::class, 'budget_myjob']);
    // Route::post('/sendmsgbudget_{id}', [App\Http\Controllers\BudgetController::class, 'sendmsg']);
    Route::get('/delattachbudget_{id}', [App\Http\Controllers\BudgetController::class, 'delattach']);
    Route::post('/attachbudget_{id}', [App\Http\Controllers\BudgetController::class, 'attach']); 
    Route::put('/processbudget_{id}', [App\Http\Controllers\BudgetController::class, 'process_itchecked']);    
    Route::get('/budget_pdf_{id}', [App\Http\Controllers\BudgetController::class, 'print_pdf']);
    Route::get('/delattachbudget_{id}', [App\Http\Controllers\BudgetController::class, 'delattach']);
    Route::post('/attachbudget_{id}', [App\Http\Controllers\BudgetController::class, 'attach']);
    Route::put('/rollbackbudget_{id}', [App\Http\Controllers\BudgetController::class, 'rollback']);
    Route::get('/budget_approval', [App\Http\Controllers\BudgetController::class, 'budget_approval']);
    Route::get('/budget_cancel_{id}', [App\Http\Controllers\BudgetController::class, 'cancel_doc']);
    Route::get('/sendmsgbudget_{id}', [App\Http\Controllers\BudgetController::class, 'sendmsg_ajax']);

    //Bookingcar
    // Route::get('/bookingcar', [App\Http\Controllers\BookingcarController::class, 'index']);
    Route::get('/addbookingcar', [App\Http\Controllers\BookingcarController::class, 'add']);
    Route::post('/savebookingcar', [App\Http\Controllers\BookingcarController::class, 'save']);
    Route::get('/editbookingcar_{id}', [App\Http\Controllers\BookingcarController::class, 'edit']);
    Route::put('/updatebookingcar_{id}', [App\Http\Controllers\BookingcarController::class, 'update']);
    Route::get('/delbookingcar_{id}', [App\Http\Controllers\BookingcarController::class, 'del']);
    Route::get('/showbookingcar_{id}', [App\Http\Controllers\BookingcarController::class, 'show']);
    Route::put('/approvebookingcar_{id}', [App\Http\Controllers\BookingcarController::class, 'approve']);
    Route::put('/rejectbookingcar_{id}', [App\Http\Controllers\BookingcarController::class, 'reject']);
    Route::put('/revisebookingcar_{id}', [App\Http\Controllers\BookingcarController::class, 'revise']);
    Route::get('/bookingcar_waiting', [App\Http\Controllers\BookingcarController::class, 'bookingcar_waiting'])->name('bookingcar_waiting.bookingcar_waiting');
    Route::get('/bookingcar_completed', [App\Http\Controllers\BookingcarController::class, 'bookingcar_completed'])->name('bookingcar_completed.bookingcar_completed');
    Route::get('/bookingcar_rejected', [App\Http\Controllers\BookingcarController::class, 'bookingcar_rejected'])->name('bookingcar_rejected.bookingcar_rejected');
    Route::get('/bookingcar_all', [App\Http\Controllers\BookingcarController::class, 'bookingcar_all'])->name('bookingcar_all.bookingcar_all');
    Route::get('/bookingcar_myjob', [App\Http\Controllers\BookingcarController::class, 'bookingcar_myjob']);
    // Route::post('/sendmsgbookingcar_{id}', [App\Http\Controllers\BookingcarController::class, 'sendmsg']);
    Route::get('/delattachbookingcar_{id}', [App\Http\Controllers\BookingcarController::class, 'delattach']);
    Route::post('/attachbookingcar_{id}', [App\Http\Controllers\BookingcarController::class, 'attach']); 
    Route::put('/processbookingcar_{id}', [App\Http\Controllers\BookingcarController::class, 'process_checked']);    
    Route::get('/bookingcar_pdf_{id}', [App\Http\Controllers\BookingcarController::class, 'print_pdf']);
    Route::get('/delattachbookingcar_{id}', [App\Http\Controllers\BookingcarController::class, 'delattach']);
    Route::post('/attachbookingcar_{id}', [App\Http\Controllers\BookingcarController::class, 'attach']);
    Route::put('/rollbackbookingcar_{id}', [App\Http\Controllers\BookingcarController::class, 'rollback']);
    Route::get('/showdriver_{id}', [App\Http\Controllers\BookingcarController::class, 'showdriver']);
    Route::post('/createvoucher_{id}', [App\Http\Controllers\BookingcarController::class, 'create_voucher']);
    Route::get('/bookingcalender', [App\Http\Controllers\BookingcarController::class, 'bookingcalender']);   
    Route::post('/crudcalender', [App\Http\Controllers\BookingcarController::class, 'crudcalender']);
    Route::put('/updateuserbooking_{id}', [App\Http\Controllers\BookingcarController::class, 'updateuser']);
    Route::get('/bookingcar_approval', [App\Http\Controllers\BookingcarController::class, 'bookingcar_approval']);
    Route::get('full-calender', [App\Http\Controllers\BookingcarController::class, 'indexbooking']);
    Route::post('full-calender/action', [App\Http\Controllers\BookingcarController::class, 'actionbooking']);
    Route::get('/bookingcar_cancel_{id}', [App\Http\Controllers\BookingcarController::class, 'cancel_doc']);
    Route::get('/sendmsgbookingcar_{id}', [App\Http\Controllers\BookingcarController::class, 'sendmsg_ajax']);
    Route::get('bookingcar_waitingprocess', [App\Http\Controllers\BookingcarController::class, 'bookingcar_waitingprocess']);
    Route::get('/bookingcar_tripdriver', [App\Http\Controllers\BookingcarController::class, 'bookingcar_tripdriver'])->name('bookingcar_tripdriver.bookingcar_tripdriver');
    Route::post('bookingcar_tripdriver', [App\Http\Controllers\BookingcarController::class, 'save_tripdriver'])->name('bookingcar_tripdriver.save_tripdriver');
    Route::get('bookingcar_tripdriver/{id}/edit', [App\Http\Controllers\BookingcarController::class, 'edit_tripdriver'])->name('bookingcar_tripdriver.edit_tripdriver');
    Route::get('/bookingcar_testmap', [App\Http\Controllers\BookingcarController::class, 'bookingcar_testmap']);

    Route::post('bookingcar_completed', [App\Http\Controllers\BookingcarController::class, 'save_drivercar'])->name('bookingcar_completed.save_drivercar');
    Route::get('bookingcar_completed/{id}/edit', [App\Http\Controllers\BookingcarController::class, 'edit_drivercar'])->name('bookingcar_completed.edit_drivercar');

    //ticket
    Route::get('/ticket', [App\Http\Controllers\TicketController::class, 'index']);
    Route::get('/addticket', [App\Http\Controllers\TicketController::class, 'add']);
    Route::post('/saveticket', [App\Http\Controllers\TicketController::class, 'save']);
    Route::get('/editticket_{id}', [App\Http\Controllers\TicketController::class, 'edit']);
    Route::put('/updateticket_{id}', [App\Http\Controllers\TicketController::class, 'update']);
    Route::get('/delticket_{id}', [App\Http\Controllers\TicketController::class, 'del']);

    //Parking
    // Route::get('/parking', [App\Http\Controllers\ParkingController::class, 'index']);
    Route::get('/addparking', [App\Http\Controllers\ParkingController::class, 'add']);
    Route::post('/saveparking', [App\Http\Controllers\ParkingController::class, 'save']);
    Route::get('/editparking_{id}', [App\Http\Controllers\ParkingController::class, 'edit']);
    Route::put('/updateparking_{id}', [App\Http\Controllers\ParkingController::class, 'update_parking']);
    Route::get('/delparking_{id}', [App\Http\Controllers\ParkingController::class, 'delete_parking']);
    Route::get('/showparking_{id}', [App\Http\Controllers\ParkingController::class, 'show']);
    Route::put('/approveparking_{id}', [App\Http\Controllers\ParkingController::class, 'approve']);
    Route::put('/rejectparking_{id}', [App\Http\Controllers\ParkingController::class, 'reject']);
    Route::put('/reviseparking_{id}', [App\Http\Controllers\ParkingController::class, 'revise']);
    Route::get('/parking_waiting', [App\Http\Controllers\ParkingController::class, 'parking_waiting'])->name('parking_waiting.parking_waiting');
    Route::get('/parking_completed', [App\Http\Controllers\ParkingController::class, 'parking_completed'])->name('parking_completed.parking_completed');
    Route::get('/parking_rejected', [App\Http\Controllers\ParkingController::class, 'parking_rejected'])->name('parking_rejected.parking_rejected');
    Route::get('/parking_all', [App\Http\Controllers\ParkingController::class, 'parking_all'])->name('parking_all.parking_all');
    
    Route::get('/parking_myjob', [App\Http\Controllers\ParkingController::class, 'parking_myjob']);
    Route::post('/sendmsgparking_{id}', [App\Http\Controllers\ParkingController::class, 'sendmsg']);
    Route::get('/delattachparking_{id}', [App\Http\Controllers\ParkingController::class, 'delattach']);
    Route::post('/attachparking_{id}', [App\Http\Controllers\ParkingController::class, 'attach']); 
    Route::put('/processparking_{id}', [App\Http\Controllers\ParkingController::class, 'process_itchecked']);
    // Route::post('/sendmsgparking_{id}', [App\Http\Controllers\ParkingController::class, 'sendmsg']);
    Route::get('/parking_pdf_{id}', [App\Http\Controllers\ParkingController::class, 'print_pdf']);
    Route::get('/delparking_{id}', [App\Http\Controllers\ParkingController::class, 'delete_parking']);    
    Route::put('/rollbackparking_{id}', [App\Http\Controllers\ParkingController::class, 'rollback']);
    Route::post('/saveparking_detail_{id}', [App\Http\Controllers\ParkingController::class, 'saveparking_detail']);
    Route::get('/showinfo_{id}', [App\Http\Controllers\ParkingController::class, 'showinfo']);
    Route::get('/cekuser_{id}', [App\Http\Controllers\ParkingController::class, 'cekuser']);
    Route::post('getkaryawan',[App\Http\Controllers\ParkingController::class, 'getkaryawan'])->name('getkaryawan'); 
    Route::post('getmember',[App\Http\Controllers\ParkingController::class, 'getmember'])->name('getmember');
    Route::put('/updatekartu_{id}', [App\Http\Controllers\ParkingController::class, 'update_kartu']); 
    Route::get('/parking_cancel_{id}', [App\Http\Controllers\ParkingController::class, 'cancel_doc']);
    // Route::get('/kendaraan_ori', [App\Http\Controllers\ParkingController::class, 'master_kendaraan_ori']);      
    Route::resource('kendaraan', KendaraanController::class);
    Route::put('/ustatus_kend_{id}', [App\Http\Controllers\ParkingController::class, 'status_kendaraan']);
    Route::get('/unokartu_{id}', [App\Http\Controllers\ParkingController::class, 'nokartu_kendaraan']);
    Route::put('/ufreeparking_{id}', [App\Http\Controllers\ParkingController::class, 'freeparking_kendaraan']);
    Route::get('/parking_approval', [App\Http\Controllers\ParkingController::class, 'parking_approval']);
    Route::get('/sendmsgparking_{id}', [App\Http\Controllers\ParkingController::class, 'sendmsg_ajax']);

    //Approval Other
    Route::get('/appother', [App\Http\Controllers\M_appOtherController::class, 'index']);
    Route::get('/addappother', [App\Http\Controllers\M_appOtherController::class, 'add']);
    Route::post('/saveappother', [App\Http\Controllers\M_appOtherController::class, 'save']);
    Route::get('/editappother_{id}', [App\Http\Controllers\M_appOtherController::class, 'edit']);
    Route::put('/updateappother_{id}', [App\Http\Controllers\M_appOtherController::class, 'update']);
    Route::get('/delappother_{id}', [App\Http\Controllers\M_appOtherController::class, 'del']);
    Route::get('/appfilter', [App\Http\Controllers\M_appOtherController::class, 'appfilter']);    

    //Meeting
    // Route::get('/meeting', [App\Http\Controllers\MeetingController::class, 'index']);
    Route::get('/addmeeting', [App\Http\Controllers\MeetingController::class, 'add']);
    Route::post('/savemeeting', [App\Http\Controllers\MeetingController::class, 'save']);
    Route::get('/editmeeting_{id}', [App\Http\Controllers\MeetingController::class, 'edit']);
    Route::put('/updatemeeting_{id}', [App\Http\Controllers\MeetingController::class, 'update']);
    Route::get('/delmeeting_{id}', [App\Http\Controllers\MeetingController::class, 'del']);
    Route::get('/showmeeting_{id}', [App\Http\Controllers\MeetingController::class, 'show']);
    Route::put('/approvemeeting_{id}', [App\Http\Controllers\MeetingController::class, 'approve']);
    Route::put('/rejectmeeting_{id}', [App\Http\Controllers\MeetingController::class, 'reject']);
    Route::put('/revisemeeting_{id}', [App\Http\Controllers\MeetingController::class, 'revise']);
    Route::get('/meeting_waiting', [App\Http\Controllers\MeetingController::class, 'meeting_waiting']);
    Route::get('/meeting_completed', [App\Http\Controllers\MeetingController::class, 'meeting_completed']);
    Route::get('/meeting_reject', [App\Http\Controllers\MeetingController::class, 'meeting_reject']);
    Route::get('/meeting_all', [App\Http\Controllers\MeetingController::class, 'meeting_all']);
    Route::get('/meeting_myjob', [App\Http\Controllers\MeetingController::class, 'meeting_myjob']);
    Route::post('/sendmsgmeeting_{id}', [App\Http\Controllers\MeetingController::class, 'sendmsg']);
    Route::get('/delattachmeeting_{id}', [App\Http\Controllers\MeetingController::class, 'delattach']);
    Route::post('/attachmeeting_{id}', [App\Http\Controllers\MeetingController::class, 'attach']); 
    Route::put('/processmeeting_{id}', [App\Http\Controllers\MeetingController::class, 'process_checked']);
    Route::post('/sendmsgmeeting_{id}', [App\Http\Controllers\MeetingController::class, 'sendmsg']);
    Route::get('/meeting_pdf_{id}', [App\Http\Controllers\MeetingController::class, 'print_pdf']);
    Route::get('/delattachmeeting_{id}', [App\Http\Controllers\MeetingController::class, 'delattach']);
    Route::post('/attachmeeting_{id}', [App\Http\Controllers\MeetingController::class, 'attach']);
    Route::put('/rollbackmeeting_{id}', [App\Http\Controllers\MeetingController::class, 'rollback']);
    Route::get('/showdriver_{id}', [App\Http\Controllers\MeetingController::class, 'showdriver']);
    Route::post('/createvoucher_{id}', [App\Http\Controllers\MeetingController::class, 'create_voucher']);
    Route::get('/bookingcalender', [App\Http\Controllers\MeetingController::class, 'bookingcalender']);   
    Route::post('/crudcalender', [App\Http\Controllers\MeetingController::class, 'crudcalender']);
    Route::put('/updateusermeeting_{id}', [App\Http\Controllers\MeetingController::class, 'updateuser']);
    Route::get('/meeting_approval', [App\Http\Controllers\MeetingController::class, 'meeting_approval']);  
    Route::get('/meeting_cancel_{id}', [App\Http\Controllers\MeetingController::class, 'cancel_doc']);
    Route::get('room_meet', [App\Http\Controllers\MeetingController::class, 'room_meeting']);
    Route::get('infomeet_{id}', [App\Http\Controllers\MeetingController::class, 'info_meeting']);
    Route::get('inforoom_{id}', [App\Http\Controllers\MeetingController::class, 'info_room']);
    Route::get('infoacc_{id}', [App\Http\Controllers\MeetingController::class, 'info_acc']);
    Route::put('/checkinmeeting_{id}', [App\Http\Controllers\MeetingController::class, 'checkin_meeting']);
    Route::put('/checkoutmeeting_{id}', [App\Http\Controllers\MeetingController::class, 'checkout_meeting']);
    Route::get('/meeting_today', [App\Http\Controllers\MeetingController::class, 'meeting_today']);
    Route::get('/meeting_tomorrow', [App\Http\Controllers\MeetingController::class, 'meeting_tomorrow']);
    Route::get('/list_zoom', [App\Http\Controllers\MeetingController::class, 'list_zoom']);
    Route::put('/updatezoom_{id}', [App\Http\Controllers\MeetingController::class, 'update_zoom']);    
    // Route::post('/create_zoom', [App\Http\Controllers\MeetingController::class, 'create_zoom']);
    Route::get('/sendmsgmeeting_{id}', [App\Http\Controllers\MeetingController::class, 'sendmsg_ajax']);
    Route::get('/zoomonly', [App\Http\Controllers\MeetingController::class, 'zoom_only']);
    Route::put('/updatezoomonly_{id}', [App\Http\Controllers\MeetingController::class, 'update_zoomonly']);
    Route::put('/checkinmeetingshow_{id}', [App\Http\Controllers\MeetingController::class, 'checkin_meeting_show']);
    Route::put('/checkoutmeetingshow_{id}', [App\Http\Controllers\MeetingController::class, 'checkout_meeting_show']);
    Route::get('/checkinmeetingdash_{id}', [App\Http\Controllers\MeetingController::class, 'checkin_meeting_dash']);
    Route::get('/checkoutmeetingdash_{id}', [App\Http\Controllers\MeetingController::class, 'checkout_meeting_dash']);
    Route::get('/zoom/auth', [MeetingController::class, 'authorizeZoom'])->name('zoom.authorize');
    Route::get('/zoom/callback', [MeetingController::class, 'callback']);  

    //roommeet
    Route::get('/roommeet', [App\Http\Controllers\RoommeetController::class, 'index']);
    Route::get('/addroommeet', [App\Http\Controllers\RoommeetController::class, 'add']);
    Route::post('/saveroommeet', [App\Http\Controllers\RoommeetController::class, 'save']);
    Route::get('/editroommeet_{id}', [App\Http\Controllers\RoommeetController::class, 'edit']);
    Route::put('/updateroommeet_{id}', [App\Http\Controllers\RoommeetController::class, 'update']);
    Route::get('/delroommeet_{id}', [App\Http\Controllers\RoommeetController::class, 'del']);
    Route::get('/checkinroom_{id}', [App\Http\Controllers\RoommeetController::class, 'checkin_room']);
    Route::put('/checkoutroom_{id}', [App\Http\Controllers\RoommeetController::class, 'checkout_room']);
    // Route::get('/showroommeet_{id}', [App\Http\Controllers\RoommeetController::class, 'show_room']);

    // personalia
    Route::get('/personnel', [App\Http\Controllers\PersonnelController::class, 'index']);
    Route::get('/addpersonnel', [App\Http\Controllers\PersonnelController::class, 'add']);
    Route::post('/savepersonnel', [App\Http\Controllers\PersonnelController::class, 'save']);
    Route::get('/editpersonnel_{id}', [App\Http\Controllers\PersonnelController::class, 'edit']);
    Route::put('/updatepersonnel_{id}', [App\Http\Controllers\PersonnelController::class, 'update']);
    Route::get('/delpersonnel_{id}', [App\Http\Controllers\PersonnelController::class, 'del']);
    Route::get('/personnel_waiting', [App\Http\Controllers\PersonnelController::class, 'personnel_waiting']);
    Route::get('/personnel_completed', [App\Http\Controllers\PersonnelController::class, 'personnel_completed']);
    Route::get('/personnel_reject', [App\Http\Controllers\PersonnelController::class, 'personnel_reject']);
    Route::get('personnel_all', [App\Http\Controllers\PersonnelController::class, 'personnel_all']);
    Route::get('personnel_myjob', [App\Http\Controllers\PersonnelController::class, 'personnel_myjob']);
    Route::put('/processpersonnel_{id}', [App\Http\Controllers\PersonnelController::class, 'process_checked']);
    Route::get('/showpersonnel_{id}', [App\Http\Controllers\PersonnelController::class, 'show']);
    Route::get('/personnel_pdf_{id}', [App\Http\Controllers\PersonnelController::class, 'print_pdf']);
    // Route::post('/sendmsgpersonnel_{id}', [App\Http\Controllers\PersonnelController::class, 'sendmsg']);
    Route::put('/approvepersonnel_{id}', [App\Http\Controllers\PersonnelController::class, 'approve']);
    Route::put('/rejectpersonnel_{id}', [App\Http\Controllers\PersonnelController::class, 'reject']);
    Route::put('/revisepersonnel_{id}', [App\Http\Controllers\PersonnelController::class, 'revise']);
    Route::get('/delattachpersonnel_{id}', [App\Http\Controllers\PersonnelController::class, 'delattach']);
    Route::post('/attachpersonnel_{id}', [App\Http\Controllers\PersonnelController::class, 'attach']);
    Route::put('/rollbackpersonnel_{id}', [App\Http\Controllers\PersonnelController::class, 'rollback']);
    Route::put('/updateactualpersonnel_{id}', [App\Http\Controllers\PersonnelController::class, 'update_actual']);
    Route::put('/updateuserpersonnel_{id}', [App\Http\Controllers\PersonnelController::class, 'updateuser']);
    Route::get('/personnel_approval', [App\Http\Controllers\PersonnelController::class, 'personnel_approval']);
    Route::get('/personnel_cancel_{id}', [App\Http\Controllers\PersonnelController::class, 'cancel_doc']);
    Route::get('/sendmsgpersonnel_{id}', [App\Http\Controllers\PersonnelController::class, 'sendmsg_ajax']);
    Route::get('/showemployee_{id}', [App\Http\Controllers\PersonnelController::class, 'show_employee']);
    Route::put('/updaterequired_{id}', [App\Http\Controllers\PersonnelController::class, 'update_required']);

    //manpower
    Route::get('/manpower', [App\Http\Controllers\ManpowerController::class, 'index']);
    Route::get('/addmanpower', [App\Http\Controllers\ManpowerController::class, 'add']);
    Route::post('/savemanpower', [App\Http\Controllers\ManpowerController::class, 'save']);
    Route::get('/editmanpower_{id}', [App\Http\Controllers\ManpowerController::class, 'edit']);
    Route::put('/updatemanpower_{id}', [App\Http\Controllers\ManpowerController::class, 'update']);
    Route::get('/delmanpower_{id}', [App\Http\Controllers\ManpowerController::class, 'del']);
    Route::get('/showmanpower_{id}', [App\Http\Controllers\ManpowerController::class, 'show']);
    Route::put('/approvemanpower_{id}', [App\Http\Controllers\ManpowerController::class, 'approve']);
    Route::put('/rejectmanpower_{id}', [App\Http\Controllers\ManpowerController::class, 'reject']);
    Route::put('/revisemanpower_{id}', [App\Http\Controllers\ManpowerController::class, 'revise']);
    Route::get('/manpower_waiting', [App\Http\Controllers\ManpowerController::class, 'manpower_waiting']);
    Route::get('/manpower_completed', [App\Http\Controllers\ManpowerController::class, 'manpower_completed']);
    Route::get('/manpower_reject', [App\Http\Controllers\ManpowerController::class, 'manpower_reject']);
    Route::get('/manpower_all', [App\Http\Controllers\ManpowerController::class, 'manpower_all']);
    Route::get('/manpower_myjob', [App\Http\Controllers\ManpowerController::class, 'manpower_myjob']);
    // Route::post('/sendmsgmanpower_{id}', [App\Http\Controllers\ManpowerController::class, 'sendmsg']);
    Route::get('/delattachmanpower_{id}', [App\Http\Controllers\ManpowerController::class, 'delattach']);
    Route::post('/attachmanpower_{id}', [App\Http\Controllers\ManpowerController::class, 'attach']); 
    Route::put('/processmanpower_{id}', [App\Http\Controllers\ManpowerController::class, 'process_itchecked']);    
    Route::get('/manpower_pdf_{id}', [App\Http\Controllers\ManpowerController::class, 'print_pdf']);
    Route::get('/deldetailmanpower_{id}', [App\Http\Controllers\ManpowerController::class, 'deldetailmanpower']);
    Route::post('/attachmanpower_{id}', [App\Http\Controllers\ManpowerController::class, 'attach']);
    Route::put('/rollbackmanpower_{id}', [App\Http\Controllers\ManpowerController::class, 'rollback']);
    Route::get('/manpower_approval', [App\Http\Controllers\ManpowerController::class, 'manpower_approval']);
    Route::get('/manpower_cancel_{id}', [App\Http\Controllers\ManpowerController::class, 'cancel_doc']);
    Route::get('/sendmsgmanpower_{id}', [App\Http\Controllers\ManpowerController::class, 'sendmsg_ajax']);
    Route::get('msmanpower', [App\Http\Controllers\ManpowerController::class, 'msmanpower'])->name('msmanpower.msmanpower');
    Route::get('msmanpower/create', [App\Http\Controllers\ManpowerController::class, 'msmanpower_create'])->name('msmanpower.msmanpower_create');
    Route::post('msmanpower/store', [App\Http\Controllers\ManpowerController::class, 'msmanpower_store'])->name('msmanpower.msmanpower_store');
    Route::get('msmanpower/{id}/edit', [App\Http\Controllers\ManpowerController::class, 'msmanpower_edit'])->name('msmanpower.msmanpower_edit');
    Route::post('msmanpower/getcpnyid', [App\Http\Controllers\ManpowerController::class, 'getcpnyid'])->name('msmanpower.getcpnyid');
    Route::get('/showdetailmanpower_{id}', [App\Http\Controllers\ManpowerController::class, 'show_detailmanpower']);
    Route::post('/storemanpower', [App\Http\Controllers\ManpowerController::class, 'store_manpower']);
    Route::post('/showupdatemanpower', [App\Http\Controllers\ManpowerController::class, 'show_updatemanpower']);

     //budgetprf
     Route::get('/budgetprf', [App\Http\Controllers\BudgetprfController::class, 'index']);
    //  Route::get('/addbudgetprf', [App\Http\Controllers\BudgetprfController::class, 'add']);    
     Route::get('/addbudgetprf_{id}', [App\Http\Controllers\BudgetprfController::class, 'add']);  
     Route::post('/savebudgetprf', [App\Http\Controllers\BudgetprfController::class, 'save']);
     Route::get('/editbudgetprf_{id}', [App\Http\Controllers\BudgetprfController::class, 'edit']);
     Route::put('/updatebudgetprf_{id}', [App\Http\Controllers\BudgetprfController::class, 'update']);
     Route::get('/delbudgetprf_{id}', [App\Http\Controllers\BudgetprfController::class, 'del']);
     Route::get('/showbudgetprf_{id}', [App\Http\Controllers\BudgetprfController::class, 'show']);
     Route::put('/approvebudgetprf_{id}', [App\Http\Controllers\BudgetprfController::class, 'approve']);
     Route::put('/rejectbudgetprf_{id}', [App\Http\Controllers\BudgetprfController::class, 'reject']);
     Route::put('/revisebudgetprf_{id}', [App\Http\Controllers\BudgetprfController::class, 'revise']);
     Route::get('/budgetprf_waiting', [App\Http\Controllers\BudgetprfController::class, 'budgetprf_waiting']);
     Route::get('/budgetprf_completed', [App\Http\Controllers\BudgetprfController::class, 'budgetprf_completed']);
     Route::get('/budgetprf_reject', [App\Http\Controllers\BudgetprfController::class, 'budgetprf_reject']);
     Route::get('/budgetprf_all', [App\Http\Controllers\BudgetprfController::class, 'budgetprf_all']);
     Route::get('/budgetprf_myjob', [App\Http\Controllers\BudgetprfController::class, 'budgetprf_myjob']);
     // Route::post('/sendmsgbudgetprf_{id}', [App\Http\Controllers\BudgetprfController::class, 'sendmsg']);
     Route::get('/delattachbudgetprf_{id}', [App\Http\Controllers\BudgetprfController::class, 'delattach']);
     Route::post('/attachbudgetprf_{id}', [App\Http\Controllers\BudgetprfController::class, 'attach']); 
     Route::put('/processbudgetprf_{id}', [App\Http\Controllers\BudgetprfController::class, 'process_itchecked']);    
     Route::get('/budgetprf_pdf_{id}', [App\Http\Controllers\BudgetprfController::class, 'print_pdf']);
     Route::get('/deldetailbudgetprf_{id}', [App\Http\Controllers\BudgetprfController::class, 'deldetailbudgetprf']);
     Route::post('/attachbudgetprf_{id}', [App\Http\Controllers\BudgetprfController::class, 'attach']);
     Route::put('/rollbackbudgetprf_{id}', [App\Http\Controllers\BudgetprfController::class, 'rollback']);
     Route::get('/budgetprf_approval', [App\Http\Controllers\BudgetprfController::class, 'budgetprf_approval']);
     Route::get('/budgetprf_cancel_{id}', [App\Http\Controllers\BudgetprfController::class, 'cancel_doc']);
     Route::get('/sendmsgbudgetprf_{id}', [App\Http\Controllers\BudgetprfController::class, 'sendmsg_ajax']);
     Route::get('msbudgetprf', [App\Http\Controllers\BudgetprfController::class, 'msbudgetprf'])->name('msbudgetprf.msbudgetprf');
     Route::get('msbudgetprf/create', [App\Http\Controllers\BudgetprfController::class, 'msbudgetprf_create'])->name('msbudgetprf.msbudgetprf_create');
     Route::post('msbudgetprf/store', [App\Http\Controllers\BudgetprfController::class, 'msbudgetprf_store'])->name('msbudgetprf.msbudgetprf_store');
     Route::get('msbudgetprf/{id}/edit', [App\Http\Controllers\BudgetprfController::class, 'msbudgetprf_edit'])->name('msbudgetprf.msbudgetprf_edit');
     Route::get('/personnel_im', [App\Http\Controllers\BudgetprfController::class, 'budgetprf_all']);

      //exit clearance form
    Route::get('/exitclearance', [App\Http\Controllers\ExitclearanceController::class, 'index']);
    Route::get('/addexitclearance', [App\Http\Controllers\ExitclearanceController::class, 'add']);
    Route::post('/saveexitclearance', [App\Http\Controllers\ExitclearanceController::class, 'save']);
    Route::get('/editexitclearance_{id}', [App\Http\Controllers\ExitclearanceController::class, 'edit']);
    Route::put('/updateexitclearance_{id}', [App\Http\Controllers\ExitclearanceController::class, 'update']);
    Route::get('/delexitclearance_{id}', [App\Http\Controllers\ExitclearanceController::class, 'del']);
    Route::get('/showexitclearance_{id}', [App\Http\Controllers\ExitclearanceController::class, 'show']);
    Route::put('/approveexitclearance_{id}', [App\Http\Controllers\ExitclearanceController::class, 'approve']);
    Route::put('/rejectexitclearance_{id}', [App\Http\Controllers\ExitclearanceController::class, 'reject']);
    Route::put('/reviseexitclearance_{id}', [App\Http\Controllers\ExitclearanceController::class, 'revise']);
    Route::get('/exitclearance_waiting', [App\Http\Controllers\ExitclearanceController::class, 'exitclearance_waiting']);
    Route::get('/exitclearance_completed', [App\Http\Controllers\ExitclearanceController::class, 'exitclearance_completed']);
    Route::get('/exitclearance_reject', [App\Http\Controllers\ExitclearanceController::class, 'exitclearance_reject']);
    Route::get('/exitclearance_all', [App\Http\Controllers\ExitclearanceController::class, 'exitclearance_all']);
    Route::get('/exitclearance_myjob', [App\Http\Controllers\ExitclearanceController::class, 'exitclearance_myjob']);
    // Route::post('/sendmsgexitclearance_{id}', [App\Http\Controllers\ExitclearanceController::class, 'sendmsg']);
    Route::get('/delattachexitclearance_{id}', [App\Http\Controllers\ExitclearanceController::class, 'delattach']);
    Route::post('/attachexitclearance_{id}', [App\Http\Controllers\ExitclearanceController::class, 'attach']); 
    Route::put('/processexitclearance_{id}', [App\Http\Controllers\ExitclearanceController::class, 'process_itchecked']);    
    Route::get('/exitclearance_pdf_{id}', [App\Http\Controllers\ExitclearanceController::class, 'print_pdf']);
    Route::get('/deldetailexitclearance_{id}', [App\Http\Controllers\ExitclearanceController::class, 'deldetailexitclearance']);
    Route::post('/attachexitclearance_{id}', [App\Http\Controllers\ExitclearanceController::class, 'attach']);
    Route::put('/rollbackexitclearance_{id}', [App\Http\Controllers\ExitclearanceController::class, 'rollback']);
    Route::get('/exitclearance_approval', [App\Http\Controllers\ExitclearanceController::class, 'exitclearance_approval']);
    Route::get('/exitclearance_cancel_{id}', [App\Http\Controllers\ExitclearanceController::class, 'cancel_doc']);
    Route::get('/sendmsgexitclearance_{id}', [App\Http\Controllers\ExitclearanceController::class, 'sendmsg_ajax']);
    Route::get('msexitclearance', [App\Http\Controllers\ExitclearanceController::class, 'msexitclearance'])->name('msexitclearance.msexitclearance');
    Route::get('msexitclearance/create', [App\Http\Controllers\ExitclearanceController::class, 'msexitclearance_create'])->name('msexitclearance.msexitclearance_create');
    Route::post('msexitclearance/store', [App\Http\Controllers\ExitclearanceController::class, 'msexitclearance_store'])->name('msexitclearance.msexitclearance_store');
    Route::get('msexitclearance/{id}/edit', [App\Http\Controllers\ExitclearanceController::class, 'msexitclearance_edit'])->name('msexitclearance.msexitclearance_edit');
    Route::get('/showcpnyname_{id}', [App\Http\Controllers\ExitclearanceController::class, 'show_cpnyname']);
    Route::put('/updateprocessexit_{id}', [App\Http\Controllers\ExitclearanceController::class, 'updateprocess_exit']);
    Route::get('/agree_exitclearance_pdf_{id}', [App\Http\Controllers\ExitclearanceController::class, 'generate_agreement_pdf']);

    //periode
    Route::get('/periode', [App\Http\Controllers\PeriodeController::class, 'index']);
    Route::get('/addperiode', [App\Http\Controllers\PeriodeController::class, 'add']);
    Route::post('/saveperiode', [App\Http\Controllers\PeriodeController::class, 'save']);
    Route::get('/editperiode_{id}', [App\Http\Controllers\PeriodeController::class, 'edit']);
    Route::put('/updateperiode_{id}', [App\Http\Controllers\PeriodeController::class, 'update']);
    Route::get('/delperiode_{id}', [App\Http\Controllers\PeriodeController::class, 'del']);

     //exit Resign Letter
     Route::get('/resignletter', [App\Http\Controllers\ResignletterController::class, 'index']);
     Route::get('/addresignletter', [App\Http\Controllers\ResignletterController::class, 'add']);
     Route::post('/saveresignletter', [App\Http\Controllers\ResignletterController::class, 'save']);
     Route::get('/editresignletter_{id}', [App\Http\Controllers\ResignletterController::class, 'edit']);
     Route::put('/updateresignletter_{id}', [App\Http\Controllers\ResignletterController::class, 'update']);
     Route::get('/delresignletter_{id}', [App\Http\Controllers\ResignletterController::class, 'del']);
     Route::get('/showresignletter_{id}', [App\Http\Controllers\ResignletterController::class, 'show']);
     Route::put('/approveresignletter_{id}', [App\Http\Controllers\ResignletterController::class, 'approve']);
     Route::put('/rejectresignletter_{id}', [App\Http\Controllers\ResignletterController::class, 'reject']);
     Route::put('/reviseresignletter_{id}', [App\Http\Controllers\ResignletterController::class, 'revise']);
     Route::get('/resignletter_waiting', [App\Http\Controllers\ResignletterController::class, 'resignletter_waiting']);
     Route::get('/resignletter_completed', [App\Http\Controllers\ResignletterController::class, 'resignletter_completed']);
     Route::get('/resignletter_reject', [App\Http\Controllers\ResignletterController::class, 'resignletter_reject']);
     Route::get('/resignletter_all', [App\Http\Controllers\ResignletterController::class, 'resignletter_all']);
     Route::get('/resignletter_myjob', [App\Http\Controllers\ResignletterController::class, 'resignletter_myjob']);    
     Route::get('/delattachresignletter_{id}', [App\Http\Controllers\ResignletterController::class, 'delattach']);
     Route::post('/attachresignletter_{id}', [App\Http\Controllers\ResignletterController::class, 'attach']); 
     Route::put('/processresignletter_{id}', [App\Http\Controllers\ResignletterController::class, 'process_itchecked']);    
     Route::get('/resignletter_pdf_{id}', [App\Http\Controllers\ResignletterController::class, 'print_pdf']);
     Route::get('/deldetailresignletter_{id}', [App\Http\Controllers\ResignletterController::class, 'deldetailresignletter']);
     Route::post('/attachresignletter_{id}', [App\Http\Controllers\ResignletterController::class, 'attach']);    
     Route::get('/resignletter_approval', [App\Http\Controllers\ResignletterController::class, 'resignletter_approval']);
     Route::get('/resignletter_cancel_{id}', [App\Http\Controllers\ResignletterController::class, 'cancel_doc']);
     Route::get('/sendmsgresignletter_{id}', [App\Http\Controllers\ResignletterController::class, 'sendmsg_ajax']);

     //Budget
    Route::get('/vouchertenant', [App\Http\Controllers\VouchertenantController::class, 'index']);
    Route::get('/addvouchertenant', [App\Http\Controllers\VouchertenantController::class, 'add']);
    Route::post('/savevouchertenant', [App\Http\Controllers\VouchertenantController::class, 'save']);
    Route::get('/editvouchertenant_{id}', [App\Http\Controllers\VouchertenantController::class, 'edit']);
    Route::put('/updatevouchertenant_{id}', [App\Http\Controllers\VouchertenantController::class, 'updatevouchertenant']);
    // Route::get('/delvouchertenant_{id}', [App\Http\Controllers\VouchertenantController::class, 'del']);
    Route::get('/showvouchertenant_{id}', [App\Http\Controllers\VouchertenantController::class, 'show']);
    Route::put('/approvevouchertenant_{id}', [App\Http\Controllers\VouchertenantController::class, 'approve']);
    Route::put('/rejectvouchertenant_{id}', [App\Http\Controllers\VouchertenantController::class, 'reject']);
    Route::put('/revisevouchertenant_{id}', [App\Http\Controllers\VouchertenantController::class, 'revise']);
    Route::get('/vouchertenant_waiting', [App\Http\Controllers\VouchertenantController::class, 'vouchertenant_waiting']);
    Route::get('/vouchertenant_completed', [App\Http\Controllers\VouchertenantController::class, 'vouchertenant_completed']);
    Route::get('/vouchertenant_reject', [App\Http\Controllers\VouchertenantController::class, 'vouchertenant_reject']);
    Route::get('/vouchertenant_all', [App\Http\Controllers\VouchertenantController::class, 'vouchertenant_all']);
    Route::get('/vouchertenant_myjob', [App\Http\Controllers\VouchertenantController::class, 'vouchertenant_myjob']);
    Route::get('/delattachvouchertenant_{id}', [App\Http\Controllers\VouchertenantController::class, 'delattach']);
    Route::post('/attachvouchertenant_{id}', [App\Http\Controllers\VouchertenantController::class, 'attach']);    
    Route::get('/vouchertenant_pdf_{id}', [App\Http\Controllers\VouchertenantController::class, 'print_pdf']);
    Route::get('/deldetailvouchertenant_{id}', [App\Http\Controllers\VouchertenantController::class, 'deldetailvouchertenant']);      
    Route::get('/vouchertenant_approval', [App\Http\Controllers\VouchertenantController::class, 'vouchertenant_approval']);
    Route::get('/vouchertenant_cancel_{id}', [App\Http\Controllers\VouchertenantController::class, 'cancel_doc']);
    Route::get('/sendmsgvouchertenant_{id}', [App\Http\Controllers\VouchertenantController::class, 'sendmsg_ajax']);
    Route::get('/msvouchertenant', [App\Http\Controllers\VouchertenantController::class, 'ms_vouchertenant']);
    // Route::get('/codevouchertenant', [App\Http\Controllers\VouchertenantController::class, 'code_vouchertenant']);
    Route::get('codevouchertenant', [App\Http\Controllers\VouchertenantController::class, 'codevouchertenant'])->name('codevouchertenant.codevouchertenant');
    Route::get('codevouchertenant/create', [App\Http\Controllers\VouchertenantController::class, 'codevouchertenant_create'])->name('codevouchertenant.codevouchertenant_create');
    Route::post('codevouchertenant/store', [App\Http\Controllers\VouchertenantController::class, 'codevouchertenant_store'])->name('codevouchertenant.codevouchertenant_store');
    Route::get('codevouchertenant/{id}/edit', [App\Http\Controllers\VouchertenantController::class, 'codevouchertenant_edit'])->name('codevouchertenant.codevouchertenant_edit');
    Route::get('/showstock_{id}', [App\Http\Controllers\VouchertenantController::class, 'show_stock']);
    Route::get('/createaccountability_{id}', [App\Http\Controllers\VouchertenantController::class, 'create_accountability']);
    Route::put('/updateaccountability_{id}', [App\Http\Controllers\VouchertenantController::class, 'update_accountability']);
    Route::get('/showrefnbrvoucher_{id}', [App\Http\Controllers\VouchertenantController::class, 'show_refnbr']);

    //Payment
    Route::get('/payment', [App\Http\Controllers\PaymentController::class, 'index']);
    Route::get('/addpayment', [App\Http\Controllers\PaymentController::class, 'add']);
    Route::post('/savepayment', [App\Http\Controllers\PaymentController::class, 'save']);
    Route::get('/editpayment_{id}', [App\Http\Controllers\PaymentController::class, 'edit']);
    Route::put('/updatepayment_{id}', [App\Http\Controllers\PaymentController::class, 'update_payment']);
    Route::get('/delpayment_{id}', [App\Http\Controllers\PaymentController::class, 'delete_payment']);
    Route::get('/showpayment_{id}', [App\Http\Controllers\PaymentController::class, 'show']);
    Route::put('/approvepayment_{id}', [App\Http\Controllers\PaymentController::class, 'approve']);
    Route::put('/rejectpayment_{id}', [App\Http\Controllers\PaymentController::class, 'reject']);
    Route::put('/revisepayment_{id}', [App\Http\Controllers\PaymentController::class, 'revise']);    
    Route::get('/payment_waiting', [App\Http\Controllers\PaymentController::class, 'payment_waiting'])->name('payment_waiting.payment_waiting');
    Route::get('/payment_all', [App\Http\Controllers\PaymentController::class, 'payment_all'])->name('payment_all.payment_all');
    Route::get('/payment_myjob', [App\Http\Controllers\PaymentController::class, 'payment_myjob']);
    // Route::post('/sendmsgpayment_{id}', [App\Http\Controllers\PaymentController::class, 'sendmsg']);
    Route::get('/delattachpayment_{id}', [App\Http\Controllers\PaymentController::class, 'delattach']);
    Route::post('/attachpayment_{id}', [App\Http\Controllers\PaymentController::class, 'attach']); 
    Route::put('/processpayment_{id}', [App\Http\Controllers\PaymentController::class, 'process_itchecked']);    
    Route::get('/payment_pdf_{id}', [App\Http\Controllers\PaymentController::class, 'print_pdf']);
    Route::put('/rollbackpayment_{id}', [App\Http\Controllers\PaymentController::class, 'rollback']);
    Route::get('/payment_approval', [App\Http\Controllers\PaymentController::class, 'payment_approval']);
    Route::put('/payment_cancel_{id}', [App\Http\Controllers\PaymentController::class, 'cancel_doc']);
    Route::get('/sendmsgpayment_{id}', [App\Http\Controllers\PaymentController::class, 'sendmsg_ajax']);
    Route::get('rfca_list', [App\Http\Controllers\PaymentController::class, 'rfca_index'])->name('rfca_list.rfca_index');
    // Route::get('/payment_finance', [App\Http\Controllers\PaymentController::class, 'payment_finance']);
    // Route::post('/payment_updatefinance/{id}', [App\Http\Controllers\PaymentController::class, 'payment_updatefinance']);
    // Route::put('/payment_updatetreasury/{id}', [App\Http\Controllers\PaymentController::class, 'payment_updatetreasury']);
    Route::get('payment_finance', [App\Http\Controllers\PaymentController::class, 'payment_finance'])->name('payment_finance.payment_finance');    
    Route::post('payment_finance', [App\Http\Controllers\PaymentController::class, 'save_paymentfinance'])->name('payment_finance.save_paymentfinance');
    //Route::post('payment_finance', [App\Http\Controllers\PaymentController::class, 'update_paymentfinance'])->name('payment_finance.update_paymentfinance');
    Route::get('payment_finance/{id}/edit', [App\Http\Controllers\PaymentController::class, 'edit_paymentfinance'])->name('payment_finance.edit_paymentfinance');
    Route::get('/pay_pdf_{id}', [App\Http\Controllers\PaymentController::class, 'print_payment']);
    Route::get('/im_pdf_{id}', [App\Http\Controllers\PaymentController::class, 'print_internalmemo']);
    Route::get('/deposit_pdf_{id}', [App\Http\Controllers\PaymentController::class, 'print_deposit']);

    //calr
    Route::get('calr_list', [App\Http\Controllers\CalrController::class, 'calr_index'])->name('calr_list.calr_index');
    Route::get('/addcalr_{id}', [App\Http\Controllers\CalrController::class, 'add_calr']);
    Route::post('/savecalr_{id}', [App\Http\Controllers\CalrController::class, 'save_calr']);
    Route::get('/editcalr_{id}', [App\Http\Controllers\CalrController::class, 'edit']);
    Route::put('/updatecalr_{id}', [App\Http\Controllers\CalrController::class, 'update_calr']);
    Route::get('/delcalr_{id}', [App\Http\Controllers\CalrController::class, 'delete_calr']);
    Route::get('/showcalr_{id}', [App\Http\Controllers\CalrController::class, 'show_calr']);
    Route::put('/approvecalr_{id}', [App\Http\Controllers\CalrController::class, 'approve']);
    Route::put('/rejectcalr_{id}', [App\Http\Controllers\CalrController::class, 'reject']);
    Route::put('/revisecalr_{id}', [App\Http\Controllers\CalrController::class, 'revise']);  
    // Route::post('/sendmsgcalr_{id}', [App\Http\Controllers\CalrController::class, 'sendmsg']);
    Route::get('/delattachcalr_{id}', [App\Http\Controllers\CalrController::class, 'delattach']);
    Route::post('/attachcalr_{id}', [App\Http\Controllers\CalrController::class, 'attach']);       
    // Route::get('/calr_pdf_{id}', [App\Http\Controllers\CalrController::class, 'print_pdf']);
    Route::put('/rollbackcalr_{id}', [App\Http\Controllers\CalrController::class, 'rollback']);
    Route::get('/calr_approval', [App\Http\Controllers\CalrController::class, 'calr_approval']);
    Route::put('/calr_cancel_{id}', [App\Http\Controllers\CalrController::class, 'cancel_doc']);
    Route::get('/sendmsgcalr_{id}', [App\Http\Controllers\CalrController::class, 'sendmsg_ajax']);
    Route::get('/realization_finance', [App\Http\Controllers\CalrController::class, 'realization_finance']);
    Route::post('/realization_updatefinance/{id}', [App\Http\Controllers\CalrController::class, 'realization_updatefinance']);
    Route::put('/realization_updatetreasury/{id}', [App\Http\Controllers\CalrController::class, 'realization_updatetreasury']);

    Route::get('payment_calr', [App\Http\Controllers\CalrController::class, 'payment_calr'])->name('payment_calr.payment_calr');    
    Route::get('payment_calr/{id}/edit', [App\Http\Controllers\CalrController::class, 'edit_paymentcalr'])->name('payment_calr.edit_paymentcalr');
    Route::post('payment_calr', [App\Http\Controllers\CalrController::class, 'save_paymentcalr'])->name('payment_calr.save_paymentcalr');
    Route::get('/calr_pdf_{id}', [App\Http\Controllers\CalrController::class, 'print_calr']);

    //master payment limit
    Route::get('payment_limit', [App\Http\Controllers\PaymentlimitController::class, 'payment_limit'])->name('payment_limit.payment_limit');
    Route::post('payment_limit', [App\Http\Controllers\PaymentlimitController::class, 'save_limit'])->name('payment_limit.save_limit');
    Route::get('payment_limit/{id}/edit', [App\Http\Controllers\PaymentlimitController::class, 'edit_limit'])->name('payment_limit.edit_limit');
    Route::get('groupbiaya', [App\Http\Controllers\GroupbiayaController::class, 'groupbiaya'])->name('groupbiaya.groupbiaya');
    Route::post('groupbiaya', [App\Http\Controllers\GroupbiayaController::class, 'save_biaya'])->name('groupbiaya.save_biaya');
    Route::get('groupbiaya/{id}/edit', [App\Http\Controllers\GroupbiayaController::class, 'edit_biaya'])->name('groupbiaya.edit_biaya');

    //Approval group biaya    
    Route::get('/addmapprovalgroupbiaya', [App\Http\Controllers\M_approvalgroupbiayaController::class, 'add']);
    Route::post('/savemapprovalgroupbiaya', [App\Http\Controllers\M_approvalgroupbiayaController::class, 'save']);
    Route::get('/editmapprovalgroupbiaya_{id}', [App\Http\Controllers\M_approvalgroupbiayaController::class, 'edit']);
    Route::put('/updatemapprovalgroupbiaya_{id}', [App\Http\Controllers\M_approvalgroupbiayaController::class, 'update']);
    Route::get('/delmapprovalgroupbiaya_{id}', [App\Http\Controllers\M_approvalgroupbiayaController::class, 'del']);
    Route::get('/appfilter', [App\Http\Controllers\M_approvalgroupbiayaController::class, 'appfilter']);    
    Route::get('mapproval_groupbiaya', [M_approvalgroupbiayaController::class, 'index'])->name('mapproval_groupbiaya.index');
      
    //Staging RFP
    Route::get('/invoice_das', [App\Http\Controllers\PaymentvmsController::class, 'index']);
    // Route::get('/check_staging_vms', [App\Http\Controllers\StagingController::class, 'check_staging_vms']);
    Route::get('/check_staging_acumatica', [App\Http\Controllers\StagingController::class, 'check_staging_acumatica']);
    Route::get('/gen_staging_rfp', [App\Http\Controllers\StagingController::class, 'gen_staging_rfp']);
    Route::get('/gen_rfp_approval', [App\Http\Controllers\StagingController::class, 'gen_rfp_approval']);

    // RFP
    Route::get('/rfp_waiting', [App\Http\Controllers\RFPController::class, 'rfp_waiting'])->name('rfp_waiting.rfp_waiting');
    Route::get('/rfp_all', [App\Http\Controllers\RFPController::class, 'rfp_all'])->name('rfp_all.rfp_all');
    Route::get('/rfp_completed', [App\Http\Controllers\RFPController::class, 'rfp_completed'])->name('rfp_completed.rfp_completed');
    Route::get('/rfp_rejected', [App\Http\Controllers\RFPController::class, 'rfp_rejected'])->name('rfp_rejected.rfp_rejected');
    Route::get('/rfp_hold', [App\Http\Controllers\RFPController::class, 'rfp_hold'])->name('rfp_hold.rfp_hold');
    Route::get('/showrfp_{id}', [App\Http\Controllers\RFPController::class, 'show']);  
    Route::put('/approverfp_{id}', [App\Http\Controllers\RFPController::class, 'approve']);
    Route::put('/rejectrfp_{id}', [App\Http\Controllers\RFPController::class, 'reject']);
    Route::post('/attachrfp_{id}', [App\Http\Controllers\RFPController::class, 'attach_rfp']); 
    Route::get('/sendmsgrfp_{id}', [App\Http\Controllers\RFPController::class, 'sendmsg_ajax']);  
    Route::get('rfp_finance', [App\Http\Controllers\RFPController::class, 'rfp_finance'])->name('rfp_finance.rfp_finance');    
    Route::post('rfp_finance', [App\Http\Controllers\RFPController::class, 'save_rfpfinance'])->name('rfp_finance.save_rfpfinance');    
    Route::get('rfp_finance/{id}/edit', [App\Http\Controllers\RFPController::class, 'edit_rfpfinance'])->name('rfp_finance.edit_rfpfinance');
    Route::get('/rfp_pdf_{id}', [App\Http\Controllers\RFPController::class, 'print_rfp']);

    Route::post('/checkin', [BookingcarController::class, 'store'])->name('checkin.store');    

    Route::get('emailblast', [App\Http\Controllers\EmailblastController::class, 'emailblast'])->name('emailblast.emailblast');
    Route::post('emailblast', [App\Http\Controllers\EmailblastController::class, 'save_email'])->name('emailblast.save_email');
    Route::get('emailblast/{id}/edit', [App\Http\Controllers\EmailblastController::class, 'edit_email'])->name('emailblast.edit_email');
    Route::post('import_email', [App\Http\Controllers\EmailblastController::class, 'import_email'])->name('import_email.import_email');

    Route::get('newemail', [App\Http\Controllers\EmailblastController::class, 'newemail'])->name('newemail.newemail');
    Route::post('newemail', [App\Http\Controllers\EmailblastController::class, 'save_email'])->name('newemail.save_email');

    //ms_warehouse    
    Route::get('mswhs', [App\Http\Controllers\MswhsController::class, 'mswhs'])->name('mswhs.mswhs');
    Route::post('mswhs', [App\Http\Controllers\MswhsController::class, 'save_whs'])->name('mswhs.save_whs');
    Route::get('mswhs/{id}/edit', [App\Http\Controllers\MswhsController::class, 'edit_whs'])->name('mswhs.edit_whs');

    //ms_warehouse dept    
    Route::get('mswhsdept', [App\Http\Controllers\MswhsdeptController::class, 'mswhsdept'])->name('mswhsdept.mswhsdept');
    Route::post('mswhsdept', [App\Http\Controllers\MswhsdeptController::class, 'save_whsdept'])->name('mswhsdept.save_whsdept');
    Route::get('mswhsdept/{id}/edit', [App\Http\Controllers\MswhsdeptController::class, 'edit_whsdept'])->name('mswhsdept.edit_whsdept');

    //ms_source_receive    
    Route::get('mssource', [App\Http\Controllers\MssourceController::class, 'mssource'])->name('mssource.mssource');
    Route::post('mssource', [App\Http\Controllers\MssourceController::class, 'save_source'])->name('mssource.save_source');
    Route::get('mssource/{id}/edit', [App\Http\Controllers\MssourceController::class, 'edit_source'])->name('mssource.edit_source');

    //ms_source_receive    
    Route::get('msproduct', [App\Http\Controllers\MsproductController::class, 'msproduct'])->name('msproduct.msproduct');
    Route::post('msproduct', [App\Http\Controllers\MsproductController::class, 'save_product'])->name('msproduct.save_product');
    Route::get('msproduct/{id}/edit', [App\Http\Controllers\MsproductController::class, 'edit_product'])->name('msproduct.edit_product'); 
    Route::get('/viewproduct_{id}', [App\Http\Controllers\MsproductController::class, 'viewproduct']);
    // Route::post('/viewproduct/save', [App\Http\Controllers\MsproductController::class, 'saveProductDetail'])->name('viewproduct.save_viewproduct');
    Route::post('/viewproduct/saveattach', [App\Http\Controllers\MsproductController::class, 'saveProductAttach'])->name('viewproduct.save_viewproductattach');
    Route::get('/get-category', [App\Http\Controllers\MsproductController::class, 'getCategoryproduct'])->name('category.get');

    Route::get('producttarget', [App\Http\Controllers\MsproductController::class, 'producttarget'])->name('producttarget.producttarget');
    // Route::post('producttarget', [App\Http\Controllers\MsproductController::class, 'save_producttarget'])->name('producttarget.save_producttarget');
    // Route::get('producttarget/{id}/edit', [App\Http\Controllers\MsproductController::class, 'edit_producttarget'])->name('producttarget.edit_producttarget'); 
    Route::get('/producttarget/detail/{product_id}', [App\Http\Controllers\MsproductController::class, 'getProductDetails']);
    Route::post('/producttarget/update-target-date', [App\Http\Controllers\MsproductController::class, 'updateTargetDate']);



    Route::get('/vplreceive_waiting', [App\Http\Controllers\VplreceiveController::class, 'vplreceive_waiting'])->name('vplreceive_waiting.vplreceive_waiting');
    Route::get('/vplreceive_completed', [App\Http\Controllers\VplreceiveController::class, 'vplreceive_completed'])->name('vplreceive_completed.vplreceive_completed');
    Route::get('/vplreceive_rejected', [App\Http\Controllers\VplreceiveController::class, 'vplreceive_rejected'])->name('vplreceive_rejected.vplreceive_rejected');
    Route::get('/vplreceive_all', [App\Http\Controllers\VplreceiveController::class, 'vplreceive_all'])->name('vplreceive_all.vplreceive_all');
    Route::get('/addvplreceive', [App\Http\Controllers\VplreceiveController::class, 'add_vplreceive']);    
    Route::post('/get-products-receive', [App\Http\Controllers\VplreceiveController::class, 'getProducts'])->name('get-products-receive');
    Route::post('/get-tenants-by-cpnyid', [App\Http\Controllers\VplreceiveController::class, 'getTenantsByCpnyid'])->name('get-tenants-by-cpnyid');
    Route::post('/get-warehouse', [App\Http\Controllers\VplreceiveController::class, 'getWarehouse'])->name('get-warehouse');
    Route::post('/get-product-details', [App\Http\Controllers\VplreceiveController::class, 'getProductDetails'])->name('get-product-details');    
    Route::post('/savevplreceive', [App\Http\Controllers\VplreceiveController::class, 'saveVplreceive'])->name('savevplreceive');
    Route::get('/showvplreceive_{id}', [App\Http\Controllers\VplreceiveController::class, 'show_vplreceive']);
    Route::get('/sendmsgvplreceive_{id}', [App\Http\Controllers\VplreceiveController::class, 'sendmsg_ajax']);
    Route::put('/approvevplreceive_{id}', [App\Http\Controllers\VplreceiveController::class, 'approve']);
    Route::put('/rejectvplreceive_{id}', [App\Http\Controllers\VplreceiveController::class, 'reject']);
    Route::put('/revisevplreceive_{id}', [App\Http\Controllers\VplreceiveController::class, 'revise']);  
    Route::get('/printvplreceive_{id}', [App\Http\Controllers\VplreceiveController::class, 'print_vplreceive_pdf']); 
    Route::get('/editvplreceive_{id}', [App\Http\Controllers\VplreceiveController::class, 'edit_vplreceive']); 
    Route::post('/delete-vplreceive-detail', [App\Http\Controllers\VplreceiveController::class, 'deleteVplreceiveDetail'])->name('delete_vplreceive_detail');
    Route::post('/delete-vplreceive-attach', [App\Http\Controllers\VplreceiveController::class, 'deleteVplreceiveAttach'])->name('delete_vplreceive_attach');
    Route::post('/updatevplreceive', [App\Http\Controllers\VplreceiveController::class, 'updateVplreceive'])->name('updatevplreceive');
    Route::put('/vplreceivecancel_{id}', [App\Http\Controllers\VplreceiveController::class, 'vplreceive_cancel']); 

    Route::get('/vpltransfer_waiting', [App\Http\Controllers\VpltransferController::class, 'vpltransfer_waiting'])->name('vpltransfer_waiting.vpltransfer_waiting');
    Route::get('/vpltransfer_completed', [App\Http\Controllers\VpltransferController::class, 'vpltransfer_completed'])->name('vpltransfer_completed.vpltransfer_completed');
    Route::get('/vpltransfer_rejected', [App\Http\Controllers\VpltransferController::class, 'vpltransfer_rejected'])->name('vpltransfer_rejected.vpltransfer_rejected');
    Route::get('/vpltransfer_all', [App\Http\Controllers\VpltransferController::class, 'vpltransfer_all'])->name('vpltransfer_all.vpltransfer_all');
    Route::get('/addvpltransfer', [App\Http\Controllers\VpltransferController::class, 'add_vpltransfer']);       
    Route::post('/savevpltransfer', [App\Http\Controllers\VpltransferController::class, 'saveVpltransfer'])->name('savevpltransfer');
    Route::get('/showvpltransfer_{id}', [App\Http\Controllers\VpltransferController::class, 'show_vpltransfer']);
    Route::get('/sendmsgvpltransfer_{id}', [App\Http\Controllers\VpltransferController::class, 'sendmsg_ajax']);
    Route::put('/approvevpltransfer_{id}', [App\Http\Controllers\VpltransferController::class, 'approve']);
    Route::put('/rejectvpltransfer_{id}', [App\Http\Controllers\VpltransferController::class, 'reject']);
    Route::put('/revisevpltransfer_{id}', [App\Http\Controllers\VpltransferController::class, 'revise']);  
    Route::get('/printvpltransfer_{id}', [App\Http\Controllers\VpltransferController::class, 'print_vpltransfer_pdf']); 
    Route::get('/editvpltransfer_{id}', [App\Http\Controllers\VpltransferController::class, 'edit_vpltransfer']); 
    Route::post('/delete-vpltransfer-detail', [App\Http\Controllers\VpltransferController::class, 'deleteVpltransferDetail'])->name('delete_vpltransfer_detail');
    Route::post('/delete-vpltransfer-attach', [App\Http\Controllers\VpltransferController::class, 'deleteVpltransferAttach'])->name('delete_vpltransfer_attach');
    Route::post('/updatevpltransfer', [App\Http\Controllers\VpltransferController::class, 'updateVpltransfer'])->name('updatevpltransfer');
    Route::put('/vpltransfercancel_{id}', [App\Http\Controllers\VpltransferController::class, 'vpltransfer_cancel']);  
    Route::get('/getProductsByTransferType', [App\Http\Controllers\VpltransferController::class, 'getProductsByTransferType'])->name('getProductsByTransferType');
    Route::get('/getToWhsOptionsTransfer', [App\Http\Controllers\VpltransferController::class, 'getToWhsOptionsTransfer'])->name('getToWhsOptionsTransfer');
    Route::get('/getFromWhsOptionsTransfer', [App\Http\Controllers\VpltransferController::class, 'getFromWhsOptionsTransfer'])->name('getFromWhsOptionsTransfer');
    Route::get('/vpltransfer/ref-options', [App\Http\Controllers\VpltransferController::class, 'getRefTransferOptions'])->name('getRefTransferOptions');

    Route::get('/vplrequest_waiting', [App\Http\Controllers\VplrequestController::class, 'vplrequest_waiting'])->name('vplrequest_waiting.vplrequest_waiting');
    Route::get('/vplrequest_completed', [App\Http\Controllers\VplrequestController::class, 'vplrequest_completed'])->name('vplrequest_completed.vplrequest_completed');
    Route::get('/vplrequest_rejected', [App\Http\Controllers\VplrequestController::class, 'vplrequest_rejected'])->name('vplrequest_rejected.vplrequest_rejected');
    Route::get('/vplrequest_all', [App\Http\Controllers\VplrequestController::class, 'vplrequest_all'])->name('vplrequest_all.vplrequest_all');
    Route::get('/addvplrequest', [App\Http\Controllers\VplrequestController::class, 'add_vplrequest']);    
    // Route::post('/get-products', [App\Http\Controllers\VplrequestController::class, 'getProducts'])->name('get-products');
    Route::post('/savevplrequest', [App\Http\Controllers\VplrequestController::class, 'saveVplrequest'])->name('savevplrequest');
    Route::get('/showvplrequest_{id}', [App\Http\Controllers\VplrequestController::class, 'show_vplrequest']);
    Route::get('/sendmsgvplrequest_{id}', [App\Http\Controllers\VplrequestController::class, 'sendmsg_ajax']);
    Route::put('/approvevplrequest_{id}', [App\Http\Controllers\VplrequestController::class, 'approve']);
    Route::put('/rejectvplrequest_{id}', [App\Http\Controllers\VplrequestController::class, 'reject']);
    Route::put('/revisevplrequest_{id}', [App\Http\Controllers\VplrequestController::class, 'revise']);  
    Route::get('/printvplrequest_{id}', [App\Http\Controllers\VplrequestController::class, 'print_vplrequest_pdf']); 
    Route::get('/editvplrequest_{id}', [App\Http\Controllers\VplrequestController::class, 'edit_vplrequest']); 
    Route::post('/delete-vplrequest-detail', [App\Http\Controllers\VplrequestController::class, 'deleteVplrequestDetail'])->name('delete_vplrequest_detail');
    Route::post('/delete-vplrequest-attach', [App\Http\Controllers\VplrequestController::class, 'deleteVplrequestAttach'])->name('delete_vplrequest_attach');
    Route::post('/updatevplrequest', [App\Http\Controllers\VplrequestController::class, 'updateVplrequest'])->name('updatevplrequest');
    Route::put('/vplrequestcancel_{id}', [App\Http\Controllers\VplrequestController::class, 'vplrequest_cancel']);  
    Route::get('/getProductsByRequestType', [App\Http\Controllers\VplrequestController::class, 'getProductsByRequestType'])->name('getProductsByRequestType');
    Route::post('/addvplrequest/save', [App\Http\Controllers\VplrequestController::class, 'saveAddvplrequesttemp'])->name('addvplrequest.save_addvplrequesttemp');
    Route::get('/products/{cpnyid}', [App\Http\Controllers\VplrequestController::class, 'getProductsRequesttemp']);
    Route::get('/product-details/{cpnyid}/{product_id}', [App\Http\Controllers\VplrequestController::class, 'getProductDetailsRequesttemp']);
    Route::get('/existing-entries/{refid}', [App\Http\Controllers\VplrequestController::class, 'getExistingEntries']);
    Route::delete('/delete-entry/{id}', [App\Http\Controllers\VplrequestController::class, 'deleteEntry']);
    Route::get('/validate-stock/{productId}', [App\Http\Controllers\VplrequestController::class, 'validateStock']);
    Route::get('/productsreturn/{cpnyid}', [App\Http\Controllers\VplrequestController::class, 'getProductsReturn']);
    Route::get('/product-details-return/{cpnyid}/{product_id}', [App\Http\Controllers\VplrequestController::class, 'getProductDetailsReturn']);
    Route::post('/addvplreturn/save', [App\Http\Controllers\VplrequestController::class, 'saveAddvplreturn'])->name('addvplreturn.save_addvplreturn');
    Route::get('/vplledgerall', [App\Http\Controllers\VplledgerController::class, 'vplledgerall'])->name('vplledgerall.vplledgerall');
    Route::get('/validate-return/{productId}', [App\Http\Controllers\VplrequestController::class, 'validateReturn']);

    Route::get('/vpladjustment_waiting', [App\Http\Controllers\VpladjustmentController::class, 'vpladjustment_waiting'])->name('vpladjustment_waiting.vpladjustment_waiting');
    Route::get('/vpladjustment_completed', [App\Http\Controllers\VpladjustmentController::class, 'vpladjustment_completed'])->name('vpladjustment_completed.vpladjustment_completed');
    Route::get('/vpladjustment_rejected', [App\Http\Controllers\VpladjustmentController::class, 'vpladjustment_rejected'])->name('vpladjustment_rejected.vpladjustment_rejected');
    Route::get('/vpladjustment_all', [App\Http\Controllers\VpladjustmentController::class, 'vpladjustment_all'])->name('vpladjustment_all.vpladjustment_all');
    Route::get('/addvpladjustment', [App\Http\Controllers\VpladjustmentController::class, 'add_vpladjustment']);       
    Route::post('/savevpladjustment', [App\Http\Controllers\VpladjustmentController::class, 'saveVpladjustment'])->name('savevpladjustment');
    Route::get('/showvpladjustment_{id}', [App\Http\Controllers\VpladjustmentController::class, 'show_vpladjustment']);
    Route::get('/sendmsgvpladjustment_{id}', [App\Http\Controllers\VpladjustmentController::class, 'sendmsg_ajax']);
    Route::put('/approvevpladjustment_{id}', [App\Http\Controllers\VpladjustmentController::class, 'approve']);
    Route::put('/rejectvpladjustment_{id}', [App\Http\Controllers\VpladjustmentController::class, 'reject']);
    Route::put('/revisevpladjustment_{id}', [App\Http\Controllers\VpladjustmentController::class, 'revise']);  
    Route::get('/printvpladjustment_{id}', [App\Http\Controllers\VpladjustmentController::class, 'print_vpladjustment_pdf']); 
    Route::get('/editvpladjustment_{id}', [App\Http\Controllers\VpladjustmentController::class, 'edit_vpladjustment']); 
    Route::post('/delete-vpladjustment-detail', [App\Http\Controllers\VpladjustmentController::class, 'deleteVpladjustmentDetail'])->name('delete_vpladjustment_detail');
    Route::post('/delete-vpladjustment-attach', [App\Http\Controllers\VpladjustmentController::class, 'deleteVpladjustmentAttach'])->name('delete_vpladjustment_attach');
    Route::post('/updatevpladjustment', [App\Http\Controllers\VpladjustmentController::class, 'updateVpladjustment'])->name('updatevpladjustment');
    Route::put('/vpladjustmentcancel_{id}', [App\Http\Controllers\VpladjustmentController::class, 'vpladjustment_cancel']);  
    Route::get('/getProductsByAdjustmentType', [App\Http\Controllers\VpladjustmentController::class, 'getProductsByAdjustmentType'])->name('getProductsByAdjustmentType');
    Route::get('/getToWhsOptionsAdjustment', [App\Http\Controllers\VpladjustmentController::class, 'getToWhsOptionsAdjustment'])->name('getToWhsOptionsAdjustment');
    Route::get('/msproduct_rpt', [App\Http\Controllers\VplledgerController::class, 'msproduct_rpt'])->name('msproduct_rpt.msproduct_rpt');
    Route::get('/inoutproduct_rpt', [App\Http\Controllers\VplledgerController::class, 'inoutproduct_rpt'])->name('inoutproduct_rpt.inoutproduct_rpt');
    Route::get('/stocktrialbalance', [App\Http\Controllers\VplledgerController::class, 'stocktrialbalance'])->name('stocktrialbalance.stocktrialbalance');
    Route::get('/posting_periode', [App\Http\Controllers\VplledgerController::class, 'posting_periode'])->name('posting_periode.posting_periode');
    Route::get('/rekapvoucher', [App\Http\Controllers\VplledgerController::class, 'rekapvoucher'])->name('rekapvoucher.rekapvoucher');
    Route::post('/posting-periode/process', [VplledgerController::class, 'process'])->name('posting_periode.process');
    Route::post('/posting_process', [App\Http\Controllers\VplledgerController::class, 'postingProcess'])->name('posting_process');
    Route::get('/get-latest-active-month', [App\Http\Controllers\VplledgerController::class, 'getLatestActiveMonth'])->name('latest_active_month');
    Route::get('/warehouse_portion', [App\Http\Controllers\VplledgerController::class, 'warehouse_portion'])->name('warehouse_portion.warehouse_portion');
    Route::get('/trialbalancedetail', [App\Http\Controllers\VplledgerController::class, 'trialBalancedetail'])->name('trialbalancedetail.trialbalancedetail');
    Route::get('/trialbalancesummary', [App\Http\Controllers\VplledgerController::class, 'trialBalancesummary'])->name('trialbalancesummary.trialbalancesummary');
    Route::get('/trialbalancesummarygroup', [App\Http\Controllers\VplledgerController::class, 'trialBalancesummarygroup'])->name('trialbalancesummarygroup.trialbalancesummarygroup');
    Route::get('/get-warehouse-by-company/{cpnyid}', [App\Http\Controllers\VplledgerController::class, 'getWarehouseByCompany'])->name('getWarehouseByCompany');
    Route::get('/agingtargetdate', [App\Http\Controllers\VplledgerController::class, 'agingtargetdatedate'])->name('agingtargetdate.agingtargetdate');
    Route::get('/agingexpireddate', [App\Http\Controllers\VplledgerController::class, 'agingexpireddatedate'])->name('agingexpireddate.agingexpireddate');

    Route::get('setupaging', [App\Http\Controllers\MsproductController::class, 'setupaging'])->name('setupaging.setupaging');
    Route::post('setupaging', [App\Http\Controllers\MsproductController::class, 'save_aging'])->name('setupaging.save_aging');
    Route::get('setupaging/{id}/edit', [App\Http\Controllers\MsproductController::class, 'edit_aging'])->name('setupaging.edit_aging'); 
    
    Route::get('/list_task', [App\Http\Controllers\ProjecttaskController::class, 'list_task'])->name('list_task.list_task');
    Route::post('list_task', [App\Http\Controllers\ProjecttaskController::class, 'save_task'])->name('list_task.save_task');
    Route::get('list_task/{id}/edit', [App\Http\Controllers\ProjecttaskController::class, 'edit_task'])->name('list_task.edit_task');
    Route::get('/showtask_{id}', [App\Http\Controllers\ProjecttaskController::class, 'show_task']);    
    Route::get('/sendmsgprojecttask_{id}', [App\Http\Controllers\ProjecttaskController::class, 'sendmsg_ajax']);
    Route::post('/attachprojecttask_{id}', [App\Http\Controllers\ProjecttaskController::class, 'attach']); 
    Route::get('delattachprojecttask_{id}', [App\Http\Controllers\ProjecttaskController::class, 'delattach']);
    Route::post('/updateproject_task_{id}', [App\Http\Controllers\ProjecttaskController::class, 'update_task']);

    Route::get('/data_ptkp_talenta', [App\Http\Controllers\TalentaController::class, 'data_ptkp_talenta'])->name('data_ptkp_talenta.data_ptkp_talenta');
    Route::get('/print_ptkp_{id}', [App\Http\Controllers\TalentaController::class, 'print_ptkp_pdf']); 
});
