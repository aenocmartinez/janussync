<?php

use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ScheduledTaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return view('welcome');
    return view('auth.custom.login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
    
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');    
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');   
    
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');    
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');  

    Route::resource('scheduled-tasks', ScheduledTaskController::class);

});

Route::get('/check-connection-status', [MonitoringController::class, 'checkConnectionStatus'])->name('check.connection.status');
Route::get('/monitoring/run-tasks', [MonitoringController::class, 'executeScheduledTasks'])->name('monitoring.run-tasks');
Route::get('/monitoring/run-task/{id}', [MonitoringController::class, 'runTaskById'])->name('monitoring.run-task');

require __DIR__.'/auth.php';
