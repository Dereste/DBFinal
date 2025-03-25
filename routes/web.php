<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use App\Livewire\CropList;
use App\Livewire\CropAdd;
use App\Livewire\CropStatusTracker;
use App\Livewire\ReportGenerator;
use App\Livewire\PlantingRecordList;
use App\Models\PlantingRecord;
use App\Livewire\FieldList;
use App\Livewire\HarvestRecordList;
use App\Livewire\Dashboard;
use App\Livewire\UserList;

Route::get('/planting-records', PlantingRecordList::class)->name('planting-record-list');
Route::get('/harvest-records', HarvestRecordList::class)->name('harvest-record-list');
Route::get('/crops', CropList::class)->name('crop-list');
Route::get('/crop-add', CropAdd::class)->name('crop-add');
Route::get('/fields', FieldList::class)->name('field-list');
Route::get('/crop-status', CropStatusTracker::class);
Route::get('/reports', ReportGenerator::class)->name('report-generator');
Route::get('/dashboard', Dashboard::class)->name('dashboard');
Route::get('/', Dashboard::class);
Route::redirect('/', '/login');
Route::get('/users', UserList::class)->name('user-list');
Route::get('/report/export', [ReportGenerator::class, 'export'])->name('report.export');


// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected route
Route::middleware('auth')->group(function () {
    Route::view('/main', 'layouts.app')->name('main');
});

Route::post('/admin-login', function (Request $request) {
    if (Hash::check($request->admin_password, Hash::make(env('ADMIN_PASSWORD')))) {
        Session::put('is_admin', true);
        return redirect()->back();
    }
    return back()->withErrors(['admin_password' => 'Incorrect password.']);
})->name('admin.login');

Route::post('/admin-logout', function () {
    Session::forget('is_admin');
    return redirect()->back();
})->name('admin.logout');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/fields', FieldList::class)->name('field-list');
    Route::get('/users', UserList::class)->name('user-list');
});
Route::get('/api/years', function () {
    $plantedYears = PlantingRecord::selectRaw('YEAR(DatePlanted) as year')->distinct()->pluck('year');
    $harvestedYears = HarvestRecord::selectRaw('YEAR(DateHarvested) as year')->distinct()->pluck('year');

    return response()->json([
        'plantedYears' => $plantedYears,
        'harvestedYears' => $harvestedYears,
    ]);
});

