<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\TaskController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('register', [AuthController::class, 'register'])->name('register');

Route::prefix('task')->group(function () {
    Route::post('create', [TaskController::class, 'create'])->name('task.create');
    Route::get('get-all', [TaskController::class, 'getAllTasks'])->name('task.get-all');
    Route::delete('/{id}', [TaskController::class, 'destroy'])->name('task.delete');
    Route::get('/{id}', [TaskController::class, 'show'])->name('task.edit');
    Route::put('/{id}/update', [TaskController::class, 'update'])->name('task.update');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
