<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('dashboard/store', [DashboardController::class, 'store'])->name('employees.store');
    Route::get('dashboard/show/{id}', [DashboardController::class, 'show'])->name('employees.show');
    Route::put('dashboard/update/{id}', [DashboardController::class, 'update'])->name('employees.update');
    Route::delete('dashboard/destroy/{id}', [DashboardController::class, 'destroy'])->name('employees.destroy');
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
});

Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.checkIn');
    Route::post('/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.checkOut');
    Route::get('/attendance/show/{id}', [AttendanceController::class, 'show'])->name('attendance.show');
});
Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});

require __DIR__.'/auth.php';
