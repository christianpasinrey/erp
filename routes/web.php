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
    // Redirect to appropriate dashboard based on context
    Route::get('dashboard', function () {
        if (tenancy()->initialized) {
            return redirect('/app');
        }

        return redirect()->route('landlord.dashboard');
    })->name('dashboard');

    Route::prefix('landlord')->name('landlord.')->group(function () {
        Route::get('/', LandlordDashboardController::class)->name('dashboard');

        Route::resource('tenants', TenantController::class)->except(['show']);
        Route::get('tenants/{tenant}/modules', [TenantController::class, 'modules'])->name('tenants.modules');
        Route::put('tenants/{tenant}/modules', [TenantController::class, 'updateModules'])->name('tenants.modules.update');
    });
});

/*
|--------------------------------------------------------------------------
| Tenant Routes (accessible on tenant domains via InitializeTenancyByDomainOrCentral)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'company', 'permission:companies.manage'])
    ->prefix('companies')
    ->name('tenant.companies.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Tenant\CompanyController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Tenant\CompanyController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Tenant\CompanyController::class, 'store'])->name('store');
        Route::get('/{company}', [\App\Http\Controllers\Tenant\CompanyController::class, 'show'])->name('show');
        Route::get('/{company}/edit', [\App\Http\Controllers\Tenant\CompanyController::class, 'edit'])->name('edit');
        Route::put('/{company}', [\App\Http\Controllers\Tenant\CompanyController::class, 'update'])->name('update');
        Route::delete('/{company}', [\App\Http\Controllers\Tenant\CompanyController::class, 'destroy'])->name('destroy');
        Route::post('/{company}/logo', [\App\Http\Controllers\Tenant\CompanyController::class, 'uploadLogo'])->name('logo');
        Route::post('/{company}/users', [\App\Http\Controllers\Tenant\CompanyUserController::class, 'store'])->name('users.store');
        Route::delete('/{company}/users/{user}', [\App\Http\Controllers\Tenant\CompanyUserController::class, 'destroy'])->name('users.destroy');
    });

Route::middleware(['auth'])
    ->prefix('api/world')
    ->name('api.world.')
    ->group(function () {
        Route::get('/countries', [\App\Http\Controllers\Api\WorldController::class, 'countries'])->name('countries');
        Route::get('/countries/{code}/states', [\App\Http\Controllers\Api\WorldController::class, 'states'])->name('states');
        Route::get('/states/{stateId}/cities', [\App\Http\Controllers\Api\WorldController::class, 'cities'])->name('cities');
    });

require __DIR__.'/settings.php';
