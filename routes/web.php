<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', [AttendanceController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::post('/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.checkIn');
    Route::post('/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.checkOut');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';