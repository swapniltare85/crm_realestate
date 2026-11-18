<?php

use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\LeadSourceController;
use App\Http\Controllers\Api\LeadStatusController;
use App\Http\Controllers\Api\ProjectController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

// Public Auth endpoint
Route::post('/v1/auth/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid email or password'
        ], 401);
    }

    $token = $user->createToken('crm-api-token')->plainTextToken;

    return response()->json([
        'success' => true,
        'message' => 'Login successful',
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ]
    ]);
})->name('api.auth.login');

// Public Lead Ingestion Webhook (e.g., from Website forms or Meta Ads webhook)
Route::post('/v1/leads/webhook', [LeadController::class, 'store'])->name('api.leads.webhook');

// CRM REST API Endpoints (v1)
Route::prefix('v1')->name('api.')->group(function () {
    // Leads CRUD & actions
    Route::apiResource('leads', LeadController::class);
    Route::post('leads/{id}/followups', [LeadController::class, 'addFollowup'])->name('leads.followups');
    Route::post('leads/{id}/site-visits', [LeadController::class, 'scheduleSiteVisit'])->name('leads.site-visits');

    // Master metadata
    Route::get('lead-sources', [LeadSourceController::class, 'index'])->name('lead-sources.index');
    Route::post('lead-sources', [LeadSourceController::class, 'store'])->name('lead-sources.store');
    Route::get('lead-statuses', [LeadStatusController::class, 'index'])->name('lead-statuses.index');
    Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::post('projects', [ProjectController::class, 'store'])->name('projects.store');
});
