<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrganogramController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/organogram', [OrganogramController::class, 'index'])->name('organogram');
    Route::post('/organogram/employees', [OrganogramController::class, 'store'])->name('organogram.employees.store');
    Route::post('/organogram/positions', [OrganogramController::class, 'storePosition'])->name('organogram.positions.store');
    Route::delete('/organogram/employees/{employee}', [OrganogramController::class, 'destroy'])->name('organogram.employees.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
