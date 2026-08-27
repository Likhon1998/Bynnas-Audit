<?php

use App\Http\Controllers\AnnualAuditController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\AuditFindingController;
use App\Http\Controllers\AuditReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MonthlyVisitController;
use App\Http\Controllers\OrganogramController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RiskAssessmentController;
use App\Http\Controllers\ShakhaController;
use App\Http\Controllers\ShakhaKpiController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified', 'active'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('role_or_permission:superadmin|users.manage')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    });

    Route::middleware('permission:organogram.view|organogram.manage')->group(function () {
        Route::get('/organogram', [OrganogramController::class, 'index'])->name('organogram');
    });
    Route::middleware('permission:organogram.manage')->group(function () {
        Route::post('/organogram/employees', [OrganogramController::class, 'store'])->name('organogram.employees.store');
        Route::post('/organogram/positions', [OrganogramController::class, 'storePosition'])->name('organogram.positions.store');
        Route::delete('/organogram/employees/{employee}', [OrganogramController::class, 'destroy'])->name('organogram.employees.destroy');
    });

    Route::middleware('permission:kpis.manage')->group(function () {
        Route::get('/kpi', [ShakhaKpiController::class, 'index'])->name('kpis.index');
        Route::get('/kpi/export', [ShakhaKpiController::class, 'export'])->name('kpis.export');
        Route::get('/kpi/{shakha}/edit', [ShakhaKpiController::class, 'edit'])->name('kpis.edit');
        Route::post('/kpi/{shakha}', [ShakhaKpiController::class, 'store'])->name('kpis.store');
    });

    Route::middleware('permission:audits.create|audits.manage')->group(function () {
        Route::get('/audits', [AuditReportController::class, 'index'])->name('audits.index');
    });

    Route::middleware('permission:findings.view_all|findings.enter')->group(function () {
        Route::get('/audit-findings', [AuditFindingController::class, 'index'])->name('audit-findings.index');
        Route::get('/audit-findings/entry', [AuditFindingController::class, 'entry'])->name('audit-findings.entry');
        Route::post('/audit-findings/entry', [AuditFindingController::class, 'storeEntry'])->name('audit-findings.entry.store');
        Route::get('/audit-findings/{indicator}', [AuditFindingController::class, 'show'])->name('audit-findings.show');
    });

    Route::middleware('permission:shakhas.manage|shakhas.view_all')->group(function () {
        Route::get('/shakhas', [ShakhaController::class, 'index'])->name('shakhas.index');
    });
    Route::middleware('permission:shakhas.manage')->group(function () {
        Route::get('/shakhas/create', [ShakhaController::class, 'create'])->name('shakhas.create');
        Route::post('/shakhas', [ShakhaController::class, 'store'])->name('shakhas.store');
        Route::get('/shakhas/{shakha}/edit', [ShakhaController::class, 'edit'])->name('shakhas.edit');
        Route::put('/shakhas/{shakha}', [ShakhaController::class, 'update'])->name('shakhas.update');
    });
    Route::middleware('permission:risk.manage')->group(function () {
        Route::get('/shakhas/risk/export', [RiskAssessmentController::class, 'export'])->name('shakhas.risk.export');
        Route::get('/shakhas/{shakha}/risk', [RiskAssessmentController::class, 'create'])->name('shakhas.risk.create');
        Route::post('/shakhas/{shakha}/risk', [RiskAssessmentController::class, 'store'])->name('shakhas.risk.store');
    });

    Route::middleware('permission:areas.manage')->group(function () {
        Route::get('/areas', [AreaController::class, 'index'])->name('areas.index');
        Route::get('/areas/create', [AreaController::class, 'create'])->name('areas.create');
        Route::post('/areas', [AreaController::class, 'store'])->name('areas.store');
    });

    Route::middleware('permission:annual_audit.manage')->group(function () {
        Route::get('/annual-audit', [AnnualAuditController::class, 'index'])->name('annual-audit.index');
        Route::post('/annual-audit/years', [AnnualAuditController::class, 'createYear'])->name('annual-audit.years.store');
        Route::delete('/annual-audit/years', [AnnualAuditController::class, 'destroyYear'])->name('annual-audit.years.destroy');
        Route::post('/annual-audit/generate', [AnnualAuditController::class, 'generate'])->name('annual-audit.generate');
        Route::post('/annual-audit/sync-missing', [AnnualAuditController::class, 'syncMissing'])->name('annual-audit.sync-missing');
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
    });

    Route::middleware('permission:monthly_visits.manage|monthly_visits.execute')->group(function () {
        Route::get('/monthly-visits', [MonthlyVisitController::class, 'index'])->name('monthly-visits.index');
        Route::get('/monthly-visits/report', [MonthlyVisitController::class, 'report'])->name('monthly-visits.report');
        Route::get('/monthly-visits/schedule/print', [MonthlyVisitController::class, 'printSchedule'])->name('monthly-visits.schedule.print');
        Route::get('/monthly-visits/schedule/pdf', [MonthlyVisitController::class, 'exportSchedulePdf'])->name('monthly-visits.schedule.pdf');
        Route::get('/monthly-visits/schedule/doc', [MonthlyVisitController::class, 'exportScheduleDoc'])->name('monthly-visits.schedule.doc');
        Route::get('/monthly-visits/schedule/excel', [MonthlyVisitController::class, 'exportScheduleExcel'])->name('monthly-visits.schedule.excel');
        Route::get('/monthly-visits/assignments/{assignment}/execution', [MonthlyVisitController::class, 'executionForm'])->name('monthly-visits.execution');
        Route::post('/monthly-visits/assignments/{assignment}/execution', [MonthlyVisitController::class, 'updateExecution'])->name('monthly-visits.execution.store');
    });
    Route::middleware('permission:monthly_visits.manage')->group(function () {
        Route::post('/monthly-visits/generate', [MonthlyVisitController::class, 'generate'])->name('monthly-visits.generate');
        Route::post('/monthly-visits/bulk-allocate', [MonthlyVisitController::class, 'bulkAllocate'])->name('monthly-visits.bulk-allocate');
        Route::post('/monthly-visits/resolve-conflicts', [MonthlyVisitController::class, 'resolveConflicts'])->name('monthly-visits.resolve-conflicts');
        Route::post('/monthly-visits/special', [MonthlyVisitController::class, 'storeSpecial'])->name('monthly-visits.special.store');
        Route::get('/monthly-visits/items/{workItem}/assign', [MonthlyVisitController::class, 'assignForm'])->name('monthly-visits.assign');
        Route::post('/monthly-visits/items/{workItem}/assign', [MonthlyVisitController::class, 'assign'])->name('monthly-visits.assign.store');
        Route::get('/monthly-visits/assignments/{assignment}/reschedule', [MonthlyVisitController::class, 'rescheduleForm'])->name('monthly-visits.reschedule');
        Route::post('/monthly-visits/assignments/{assignment}/reschedule', [MonthlyVisitController::class, 'reschedule'])->name('monthly-visits.reschedule.store');
    });

    Route::middleware('permission:projects.manage')->group(function () {
        Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
        Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
        Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
        Route::post('/projects/{project}/locations', [ProjectController::class, 'storeLocation'])->name('projects.locations.store');
        Route::delete('/projects/{project}/locations/{location}', [ProjectController::class, 'destroyLocation'])->name('projects.locations.destroy');
    });
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
