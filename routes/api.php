<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RolsController;
use App\Http\Controllers\CheckController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\MotivesController;
use App\Http\Controllers\PersonsController;
use App\Http\Controllers\DivisionsController;
use App\Http\Controllers\PersonDivController;

Route::get('/company/list', [CompanyController::class, 'getList']);
Route::resource('/company', CompanyController::class);

// RUTAS DE SUCURSALES
Route::prefix('divisions')->group(function () {
    Route::get('list', [DivisionsController::class, 'getList']);
    Route::get('data/{token}', [DivisionsController::class, 'data']);
});
Route::resource('/divisions', DivisionsController::class);

// RUTAS DE ROLES
Route::prefix('rols')->group(function () {
    Route::get('list', [RolsController::class, 'getList']);
});
Route::resource('/rols', RolsController::class);

// RUTAS DE MOTIVOS
Route::prefix('motives')->group(function () {
    Route::get('list', [MotivesController::class, 'getList']);
    Route::get('motives', [MotivesController::class, 'getMotives']);
});
Route::resource('/motives', MotivesController::class);

// RUTAS DE TRABAJADORES
Route::prefix('persons')->group(function () {
    Route::get('list', [PersonsController::class, 'getList']);
    Route::post('check', [PersonsController::class, 'check']);
});
Route::resource('/persons', PersonsController::class);

// RUTAS DE CHEQUEO
Route::prefix('checks')->group(function () {
    Route::get('list', [CheckController::class, 'getList']);
});
Route::resource('/checks', CheckController::class);

// RUTAS DE DIVISIONES
Route::prefix('divs')->group(function () {
    Route::get('list', [PersonDivController::class, 'getList']);
});
Route::resource('/divs', PersonDivController::class);

// REPORT CONTROLLER
Route::prefix('reports')->group(function () {
    Route::get('list', [ReportController::class, 'getList']);
    Route::post('pdf', [ReportController::class, 'pdf']);
});
Route::resource('/reports', ReportController::class);

// RUTAS DE USUARIOS
Route::prefix('users')->group(function () {
    Route::get('list', [UsersController::class, 'getList']);
});
Route::resource('/users', UsersController::class);
