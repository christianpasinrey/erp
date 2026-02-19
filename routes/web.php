<?php

use App\Http\Controllers\Landlord\DashboardController as LandlordDashboardController;
use App\Http\Controllers\Landlord\TenantController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

/*
|--------------------------------------------------------------------------
| Landlord Routes (central domain: erp.test)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    // Redirect dashboard to landlord
    Route::get('dashboard', fn () => redirect()->route('landlord.dashboard'))->name('dashboard');

    Route::prefix('landlord')->name('landlord.')->group(function () {
        Route::get('/', LandlordDashboardController::class)->name('dashboard');

        Route::resource('tenants', TenantController::class)->except(['show']);
        Route::get('tenants/{tenant}/modules', [TenantController::class, 'modules'])->name('tenants.modules');
        Route::put('tenants/{tenant}/modules', [TenantController::class, 'updateModules'])->name('tenants.modules.update');
    });
});

require __DIR__.'/settings.php';
