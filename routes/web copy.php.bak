<?php

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
use App\Models\MsScreen;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/







Route::redirect('/', 'login');

// Route::get('/settings/post', function () {
//     return view('post.index');
// })->name('post');

// Route::get('/posts/json', [PostController::class, 'json'])->name('posts.json'); // Untuk Fetch API
// Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
// Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
// Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');


// Route::get('/', [PostController::class, 'index'])->name('posts.index');
// Route::get('/posts', [PostController::class, 'getPosts'])->name('posts.get');
// Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
// Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
// Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
// Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');




Route::middleware(['auth:sanctum', 'verified'])->group(function () {

    // Ambil semua screens dan buat route otomatis
    $screens = MsScreen::all();
    foreach ($screens as $screen) {
        Route::get($screen->screen_name, function () use ($screen) {
            return view($screen->screen_name); // Load view sesuai database
        })->name($screen->screen_name);
    }

    Route::get('/screens', [MsScreenController::class, 'index'])->name('screens');
    Route::get('/screens/json', [MsScreenController::class, 'json'])->name('screens.json'); // Untuk Fetch API
    Route::post('/screens', [MsScreenController::class, 'store'])->name('screens.store');
    Route::put('/screens/{post}', [MsScreenController::class, 'update'])->name('screens.update');
    Route::delete('/screens/{post}', [MsScreenController::class, 'destroy'])->name('screens.destroy');

    Route::get('/applications', [MsApplicationController::class, 'index'])->name('applications');
    Route::get('/applications/json', [MsApplicationController::class, 'json'])->name('applications.json'); // Untuk Fetch API
    Route::post('/applications', [MsApplicationController::class, 'store'])->name('applications.store');
    Route::put('/applications/{post}', [MsApplicationController::class, 'update'])->name('applications.update');
    Route::delete('/applications/{post}', [MsApplicationController::class, 'destroy'])->name('applications.destroy');

    Route::get('/groups', [MsGroupController::class, 'index'])->name('groups');
    Route::get('/groups/json', [MsGroupController::class, 'json'])->name('groups.json'); // Untuk Fetch API
    Route::post('/groups', [MsGroupController::class, 'store'])->name('groups.store');
    Route::put('/groups/{post}', [MsGroupController::class, 'update'])->name('groups.update');
    Route::delete('/groups/{post}', [MsGroupController::class, 'destroy'])->name('groups.destroy');
  
    Route::get('/tasks', [ProjectTaskController::class, 'index'])->name('tasks');    
    Route::get('/tasks/json', [ProjectTaskController::class, 'json'])->name('tasks.json'); 
    Route::post('/tasks', [ProjectTaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{post}/edit', [ProjectTaskController::class, 'edit'])->name('tasks.edit');
    Route::put('/tasks/{post}', [ProjectTaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{post}', [ProjectTaskController::class, 'destroy'])->name('tasks.destroy');  
    Route::get('/participants', [ProjectTaskController::class, 'getParticipants'])->name('participants.json');
    Route::get('/companytask/list', [ProjectTaskController::class, 'getCompany'])->name('company.list');
    Route::get('/departementtask/list', [ProjectTaskController::class, 'getDepartement'])->name('departement.list');
    Route::get('/showtasks/{id}', [ProjectTaskController::class, 'showtasks'])->name('showtasks');
    Route::get('/tasks/{task}', [ProjectTaskController::class, 'show']);
    Route::get('/tasks/{taskId}/approvals', [ProjectTaskController::class, 'getApprovals']);


    Route::get('/agendas', [AgendaController::class, 'index'])->name('agendas');    
    Route::get('/agendas/json', [AgendaController::class, 'json'])->name('agendas.json'); 
    Route::post('/agendas', [AgendaController::class, 'store'])->name('agendas.store');
    Route::get('/agendas/{post}/edit', [AgendaController::class, 'edit'])->name('agendas.edit');
    Route::put('/agendas/{post}', [AgendaController::class, 'update'])->name('agendas.update');
    Route::delete('/agendas/{post}', [AgendaController::class, 'destroy'])->name('agendas.destroy');  
    Route::get('/participantsagendas', [AgendaController::class, 'getParticipants'])->name('participantsagendas.json');
    Route::get('/companyagenda/list', [AgendaController::class, 'getCompany'])->name('companyagendas.list');
    Route::get('/departementagenda/list', [AgendaController::class, 'getDepartement'])->name('departementagendas.list');
    Route::get('/showagendas/{id}', [AgendaController::class, 'showagendas'])->name('showagendas');
    Route::get('/agendas/{agenda}', [AgendaController::class, 'show']);
    Route::get('/agendas/{agendaId}/approvals', [AgendaController::class, 'getApprovals']);
    

    Route::get('/news', [NewsController::class, 'index'])->name('news');    
    Route::get('/news/json', [NewsController::class, 'json'])->name('news.json'); 
    Route::post('/news', [NewsController::class, 'store'])->name('news.store');
    Route::get('/news/{post}/edit', [NewsController::class, 'edit'])->name('news.edit');
    Route::put('/news/{post}', [NewsController::class, 'update'])->name('news.update');
    Route::delete('/news/{post}', [NewsController::class, 'destroy'])->name('news.destroy');   
    Route::get('/company/list', [NewsController::class, 'getCompany'])->name('company.list');
    Route::get('/departement/list', [NewsController::class, 'getDepartement'])->name('departement.list');




   
    // Route for the getting the data feed
    Route::get('/json-data-feed', [DataFeedController::class, 'getDataFeed'])->name('json_data_feed');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/mastercard', [DashboardController::class, 'analytics'])->name('mastercard');


    Route::get('/settings/account', function () {
        return view('profile/show');
    })->name('account');
    Route::get('/settings/notifications', function () {
        return view('pages/settings/notifications');
    })->name('notifications');


});
