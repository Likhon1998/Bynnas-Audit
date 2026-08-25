<?php

use App\Http\Controllers\AnnualAuditController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrganogramController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ShakhaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/organogram', [OrganogramController::class, 'index'])->name('organogram');
    Route::post('/organogram/employees', [OrganogramController::class, 'store'])->name('organogram.employees.store');
    Route::post('/organogram/positions', [OrganogramController::class, 'storePosition'])->name('organogram.positions.store');
    Route::delete('/organogram/employees/{employee}', [OrganogramController::class, 'destroy'])->name('organogram.employees.destroy');

    Route::get('/shakhas', [ShakhaController::class, 'index'])->name('shakhas.index');
    Route::get('/shakhas/create', [ShakhaController::class, 'create'])->name('shakhas.create');
    Route::post('/shakhas', [ShakhaController::class, 'store'])->name('shakhas.store');

    Route::get('/areas', [AreaController::class, 'index'])->name('areas.index');
    Route::get('/areas/create', [AreaController::class, 'create'])->name('areas.create');
    Route::post('/areas', [AreaController::class, 'store'])->name('areas.store');

    Route::get('/annual-audit', [AnnualAuditController::class, 'index'])->name('annual-audit.index');
    Route::post('/annual-audit/years', [AnnualAuditController::class, 'createYear'])->name('annual-audit.years.store');
    Route::delete('/annual-audit/years', [AnnualAuditController::class, 'destroyYear'])->name('annual-audit.years.destroy');
    Route::post('/annual-audit/generate', [AnnualAuditController::class, 'generate'])->name('annual-audit.generate');
    Route::post('/annual-audit/publish', [AnnualAuditController::class, 'publish'])->name('annual-audit.publish');
    Route::post('/annual-audit/policies', [AnnualAuditController::class, 'updatePolicies'])->name('annual-audit.policies');
    Route::post('/annual-audit/toggle-month', [AnnualAuditController::class, 'toggleMonth'])->name('annual-audit.toggle-month');
    Route::get('/annual-audit/export', [AnnualAuditController::class, 'export'])->name('annual-audit.export');
    Route::post('/annual-audit/hq-departments', [AnnualAuditController::class, 'storeHqDepartment'])->name('annual-audit.hq.store');
    Route::delete('/annual-audit/hq-departments/{department}', [AnnualAuditController::class, 'destroyHqDepartment'])->name('annual-audit.hq.destroy');
    Route::post('/annual-audit/projects', [AnnualAuditController::class, 'storeProject'])->name('annual-audit.projects.store');
    Route::delete('/annual-audit/projects/{project}', [AnnualAuditController::class, 'destroyProject'])->name('annual-audit.projects.destroy');
    Route::post('/annual-audit/projects/{project}/locations', [AnnualAuditController::class, 'storeProjectLocation'])->name('annual-audit.projects.locations.store');
    Route::delete('/annual-audit/projects/{project}/locations/{location}', [AnnualAuditController::class, 'destroyProjectLocation'])->name('annual-audit.projects.locations.destroy');

    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::post('/projects/{project}/locations', [ProjectController::class, 'storeLocation'])->name('projects.locations.store');
    Route::delete('/projects/{project}/locations/{location}', [ProjectController::class, 'destroyLocation'])->name('projects.locations.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
