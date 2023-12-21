<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware('guest')
    ->group(function () {
        Route::get('login', [AuthController::class, 'login'])
            ->name('login');
        Route::get('signup', [AuthController::class, 'signup'])
            ->name('signup');
    });

Route::post('authenticate', [AuthController::class, 'authenticate'])->name('authenticate');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'destroy'])
        ->name('logout');

    Route::get('tasks', [TaskController::class, 'index'])
        ->name('tasks');
});
