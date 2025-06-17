<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PersonsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DivisionsController;
use App\Http\Controllers\PersonDivController;
use App\Http\Controllers\RolsController;
use App\Http\Controllers\MotivesController;
use App\Http\Controllers\CheckController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\CustomReportController;
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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/t', function () {
    $data = \App\PersonCheck::all();
    $times = [];

    try {
        for ($i = 0; $i <= count($data) - 1; $i++) {
            $times[] = \Carbon\Carbon::parse($data[$i + 1]['moment'])
                ->diffInHours(\Carbon\Carbon::parse($data[$i]['moment']));
            $i++;
            if (($i + 1) > count($data) - 1) {
                $i = count($data) - 1;
            }
        }
    } catch (Exception $e) {
    }

    $total = collect($times)->sum();
    echo $total;
});

Route::middleware('auth')->group(function () {
    Route::get('/back', [HomeController::class, 'index'])->name('home');
    Route::get('/persons', [PersonsController::class, 'index'])->name('persons');
    Route::get('/users', [UsersController::class, 'index'])->name('users');
    Route::get('/company', [CompanyController::class, 'index'])->name('company');
    Route::get('/divisions', [DivisionsController::class, 'index'])->name('divisions');
    Route::get('/divisions/div/{id?}', [PersonDivController::class, 'index'])->name('div');
    Route::get('/rols', [RolsController::class, 'index'])->name('rols');
    Route::get('/motives', [MotivesController::class, 'index'])->name('motives');
    Route::get('/checks/{id?}', [CheckController::class, 'index'])->name('check');
    Route::get('/report', [ReportController::class, 'index'])->name('report');
    Route::post('/custom-reports', [CustomReportController::class, 'store']);
    Route::post('/company/logo-upload', [CompanyController::class, 'uploadLogo'])->name('company.uploadLogo');
});

Route::get('/home', [HomeController::class, 'index'])->name('home');