<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\AnnualAuditController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\AuditFindingController;
use App\Http\Controllers\AuditReportController;
use App\Http\Controllers\AuditReportReviewController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\MonthlyVisitController;
use App\Http\Controllers\OrganogramController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RiskAssessmentController;
use App\Http\Controllers\ShakhaController;
use App\Http\Controllers\ShakhaEmployeeController;
use App\Http\Controllers\ShakhaKpiController;
use App\Http\Controllers\SuperAdminChatController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\RoleManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware(['auth', 'verified', 'active'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('permission:map.view')->group(function () {
        Route::get('/map', [MapController::class, 'index'])->name('map.index');
        Route::get('/map/live', [MapController::class, 'live'])->name('map.live');
    });

    Route::middleware(['superadmin', 'throttle:20,1'])->prefix('superadmin/chat')->name('superadmin.chat.')->group(function () {
        Route::get('/history', [SuperAdminChatController::class, 'history'])->name('history');
        Route::post('/ask', [SuperAdminChatController::class, 'ask'])->name('ask');
    });

    Route::middleware('role_or_permission:superadmin|users.manage')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/toggle-active', [UserManagementController::class, 'toggleActive'])->name('users.toggle-active');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');

        Route::get('/roles', [RoleManagementController::class, 'index'])->name('roles.index');
        Route::get('/roles/create', [RoleManagementController::class, 'create'])->name('roles.create');
        Route::post('/roles', [RoleManagementController::class, 'store'])->name('roles.store');
        Route::get('/roles/{role}/edit', [RoleManagementController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{role}', [RoleManagementController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [RoleManagementController::class, 'destroy'])->name('roles.destroy');
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
        Route::get('/audits/send-history', [AuditReportController::class, 'sendHistory'])->name('audits.send-history');
        Route::get('/audits/{report}/checklist', [AuditReportController::class, 'checklist'])->name('audits.checklist');
        Route::get('/audits/{report}/checklist/{file}/download', [AuditReportController::class, 'downloadChecklistFile'])->name('audits.checklist.download');
        Route::post('/audits/{report}/review/submit', [AuditReportReviewController::class, 'submit'])->name('audit-review.submit');
        Route::get('/checklists', fn () => view('checklists.index'))->name('checklists.index');
    });

    Route::middleware('permission:audits.review_assign')->group(function () {
        Route::get('/audit-review/assignments', [AuditReportReviewController::class, 'assignments'])->name('audit-review.assignments');
        Route::post('/audit-review/assignments', [AuditReportReviewController::class, 'saveAssignments'])->name('audit-review.assignments.save');
    });
    Route::middleware('permission:audits.review|audits.review_assign|audits.create|audits.manage')->group(function () {
        Route::get('/audit-review', [AuditReportReviewController::class, 'index'])->name('audit-review.index');
        Route::get('/audit-review/{report}', [AuditReportReviewController::class, 'show'])->whereNumber('report')->name('audit-review.show');
        Route::get('/audit-review/{report}/document', [AuditReportReviewController::class, 'document'])->whereNumber('report')->name('audit-review.document');
        Route::get('/audit-review/{report}/download', [AuditReportReviewController::class, 'downloadReviewPack'])->whereNumber('report')->name('audit-review.download');
    });
    Route::middleware('permission:audits.review')->group(function () {
        Route::post('/audit-review/{report}/request-changes', [AuditReportReviewController::class, 'requestChanges'])->whereNumber('report')->name('audit-review.request-changes');
        Route::post('/audit-review/{report}/approve', [AuditReportReviewController::class, 'approve'])->whereNumber('report')->name('audit-review.approve');
        Route::post('/audit-review/{report}/done', [AuditReportReviewController::class, 'completeReview'])->whereNumber('report')->name('audit-review.done');
        Route::post('/audit-review/{report}/send-to-maker', [AuditReportReviewController::class, 'sendToMaker'])->whereNumber('report')->name('audit-review.send-to-maker');
        Route::post('/audit-review/{report}/reopen', [AuditReportReviewController::class, 'reopenReview'])->whereNumber('report')->name('audit-review.reopen');
        Route::post('/audit-review/{report}/annotations', [AuditReportReviewController::class, 'storeAnnotation'])->whereNumber('report')->name('audit-review.annotations.store');
        Route::patch('/audit-review/{report}/annotations/{annotation}', [AuditReportReviewController::class, 'updateAnnotation'])->whereNumber('report')->whereNumber('annotation')->name('audit-review.annotations.update');
        Route::post('/audit-review/{report}/annotations/{annotation}/snapshot', [AuditReportReviewController::class, 'attachAnnotationSnapshot'])->whereNumber('report')->whereNumber('annotation')->name('audit-review.annotations.snapshot');
        Route::delete('/audit-review/{report}/annotations/{annotation}', [AuditReportReviewController::class, 'destroyAnnotation'])->whereNumber('report')->whereNumber('annotation')->name('audit-review.annotations.destroy');
    });

    Route::middleware('permission:findings.view_all')->group(function () {
        Route::get('/audit-findings', [AuditFindingController::class, 'index'])->name('audit-findings.index');
        Route::get('/audit-findings/summary', [AuditFindingController::class, 'summary'])->name('audit-findings.summary');
        Route::get('/audit-findings/summary/export', [AuditFindingController::class, 'exportSummary'])->name('audit-findings.summary.export');
        Route::get('/audit-findings/summary/export-ppt', [AuditFindingController::class, 'exportSummaryPpt'])->name('audit-findings.summary.export-ppt');
        Route::get('/audit-findings/export', [AuditFindingController::class, 'export'])->name('audit-findings.export');
        Route::get('/audit-findings/{indicator}', [AuditFindingController::class, 'show'])->whereNumber('indicator')->name('audit-findings.show');
    });
    Route::middleware('permission:findings.enter')->group(function () {
        Route::get('/audit-findings/entry', [AuditFindingController::class, 'entry'])->name('audit-findings.entry');
        Route::post('/audit-findings/entry', [AuditFindingController::class, 'storeEntry'])->name('audit-findings.entry.store');
        Route::patch('/audit-findings/findings/{finding}/staff', [AuditFindingController::class, 'updateStaff'])->name('audit-findings.staff.update');
    });

    Route::middleware('permission:shakhas.manage|shakhas.view_all')->group(function () {
        Route::get('/shakhas', [ShakhaController::class, 'index'])->name('shakhas.index');
        Route::get('/shakha-employees', [ShakhaEmployeeController::class, 'index'])->name('shakha-employees.index');
        Route::get('/shakhas/{shakha}/employees', [ShakhaEmployeeController::class, 'manage'])->name('shakha-employees.manage');
        Route::get('/shakha-employees/{shakhaEmployee}/dossier', [ShakhaEmployeeController::class, 'dossier'])->name('shakha-employees.dossier');
    });
    Route::middleware('permission:shakhas.manage')->group(function () {
        Route::get('/shakhas/create', [ShakhaController::class, 'create'])->name('shakhas.create');
        Route::post('/shakhas', [ShakhaController::class, 'store'])->name('shakhas.store');
        Route::get('/shakhas/{shakha}/edit', [ShakhaController::class, 'edit'])->name('shakhas.edit');
        Route::put('/shakhas/{shakha}', [ShakhaController::class, 'update'])->name('shakhas.update');
        Route::post('/shakhas/{shakha}/employees', [ShakhaEmployeeController::class, 'store'])->name('shakha-employees.store');
        Route::get('/shakha-employees/{shakhaEmployee}/edit', [ShakhaEmployeeController::class, 'edit'])->name('shakha-employees.edit');
        Route::put('/shakha-employees/{shakhaEmployee}', [ShakhaEmployeeController::class, 'update'])->name('shakha-employees.update');
        Route::post('/shakha-employees/{shakhaEmployee}/transfer', [ShakhaEmployeeController::class, 'transfer'])->name('shakha-employees.transfer');
        Route::post('/shakha-employees/{shakhaEmployee}/fire', [ShakhaEmployeeController::class, 'fire'])->name('shakha-employees.fire');
        Route::delete('/shakha-employees/{shakhaEmployee}', [ShakhaEmployeeController::class, 'destroy'])->name('shakha-employees.destroy');
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
        Route::get('/monthly-visits/assignments/{assignment}/start-work', [MonthlyVisitController::class, 'startWork'])->name('monthly-visits.start-work');
    });
    Route::middleware('permission:monthly_visits.manage')->group(function () {
        Route::post('/monthly-visits/generate', [MonthlyVisitController::class, 'generate'])->name('monthly-visits.generate');
        Route::post('/monthly-visits/bulk-allocate', [MonthlyVisitController::class, 'bulkAllocate'])->name('monthly-visits.bulk-allocate');
        Route::post('/monthly-visits/special', [MonthlyVisitController::class, 'storeSpecial'])->name('monthly-visits.special.store');
        Route::get('/monthly-visits/items/{workItem}/assign', [MonthlyVisitController::class, 'assignForm'])->name('monthly-visits.assign');
        Route::post('/monthly-visits/items/{workItem}/assign', [MonthlyVisitController::class, 'assign'])->name('monthly-visits.assign.store');
        Route::get('/monthly-visits/assignments/{assignment}/reschedule', [MonthlyVisitController::class, 'rescheduleForm'])->name('monthly-visits.reschedule');
        Route::post('/monthly-visits/assignments/{assignment}/reschedule', [MonthlyVisitController::class, 'reschedule'])->name('monthly-visits.reschedule.store');
        Route::post('/monthly-visits/assignments/{assignment}/lock', [MonthlyVisitController::class, 'lock'])->name('monthly-visits.lock');
        Route::post('/monthly-visits/assignments/{assignment}/unlock', [MonthlyVisitController::class, 'unlock'])->name('monthly-visits.unlock');
    });

    Route::middleware('permission:calendar.manage|monthly_visits.manage|monthly_visits.execute')->group(function () {
        Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    });
    Route::middleware('permission:calendar.manage')->group(function () {
        Route::post('/calendar/holidays', [CalendarController::class, 'store'])->name('calendar.store');
        Route::put('/calendar/holidays/{holiday}', [CalendarController::class, 'update'])->name('calendar.update');
        Route::patch('/calendar/holidays/{holiday}/toggle', [CalendarController::class, 'toggle'])->name('calendar.toggle');
        Route::delete('/calendar/holidays/{holiday}', [CalendarController::class, 'destroy'])->name('calendar.destroy');
        Route::put('/calendar/weekends', [CalendarController::class, 'updateWeekends'])->name('calendar.weekends');
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
