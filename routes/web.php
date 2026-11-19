<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\LeadImportExportController;
use App\Http\Controllers\Web\LeadWebController;
use App\Http\Controllers\Web\ProjectWebController;
use App\Http\Controllers\Web\UserWebController;
use Illuminate\Support\Facades\Route;

// Authentication Routes (Guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Logout (Authenticated only)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected CRM Web Routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // CSV Export & Import (placed before {id} routes to prevent route collisions)
    Route::get('/leads/export', [LeadImportExportController::class, 'export'])->name('leads.export');
    Route::get('/leads/sample-csv', [LeadImportExportController::class, 'sampleCsv'])->name('leads.sample-csv');
    Route::post('/leads/import', [LeadImportExportController::class, 'import'])->name('leads.import');

    // Leads Management CRUD
    Route::prefix('leads')->name('leads.')->group(function () {
        Route::get('/', [LeadWebController::class, 'index'])->name('index');
        Route::post('/', [LeadWebController::class, 'store'])->name('store');
        Route::post('/bulk-reassign', [LeadWebController::class, 'bulkReassign'])->name('bulk-reassign');
        Route::post('/bulk-status', [LeadWebController::class, 'bulkStatus'])->name('bulk-status');
        Route::get('/{id}', [LeadWebController::class, 'show'])->name('show');
        Route::put('/{id}', [LeadWebController::class, 'update'])->name('update');
        Route::delete('/{id}', [LeadWebController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/status', [LeadWebController::class, 'updateStatus'])->name('update-status');
        Route::post('/{id}/followup', [LeadWebController::class, 'addFollowup'])->name('add-followup');
        Route::post('/{id}/site-visit', [LeadWebController::class, 'scheduleSiteVisit'])->name('schedule-visit');
        Route::post('/{id}/site-visit/{visitId}/outcome', [LeadWebController::class, 'updateSiteVisitOutcome'])->name('site-visit-outcome');
    });

    // Projects / Inventory
    Route::get('/projects', [ProjectWebController::class, 'index'])->name('projects.index');
    Route::post('/projects', [ProjectWebController::class, 'store'])->name('projects.store');
    Route::post('/projects/units/{id}/status', [ProjectWebController::class, 'updateUnitStatus'])->name('projects.units.status');

    // Admin Team & Users Management (Only accessible by Admin role)
    Route::prefix('users')->name('users.')->middleware(\App\Http\Middleware\EnsureAdmin::class)->group(function () {
        Route::get('/', [UserWebController::class, 'index'])->name('index');
        Route::post('/', [UserWebController::class, 'store'])->name('store');
        Route::put('/{id}', [UserWebController::class, 'update'])->name('update');
        Route::post('/{id}/toggle-status', [UserWebController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{id}', [UserWebController::class, 'destroy'])->name('destroy');
    });

    // API Documentation & Webhook guide
    Route::get('/api-documentation', function () {
        return view('api_docs');
    })->name('api.docs');
});
