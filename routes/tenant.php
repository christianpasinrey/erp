<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\CompanyController;
use App\Http\Controllers\Tenant\CompanyUserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Tenant routes are loaded by TenancyServiceProvider::mapRoutes().
| Module-specific routes are registered by each ModuleServiceProvider.
|
| IMPORTANT: Do NOT define routes here that share URIs with central routes
| (web.php), as tenant routes are loaded after central routes and will
| override them, causing 404 on central domains via PreventAccessFromCentralDomains.
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/app', function () {
        return Inertia::render('Tenant/Dashboard');
    })->name('tenant.home');

    // Company CRUD (admin-only)
    Route::middleware(['auth', 'verified', 'company', 'permission:companies.manage'])
        ->prefix('companies')
        ->name('tenant.companies.')
        ->group(function () {
            Route::get('/', [CompanyController::class, 'index'])->name('index');
            Route::get('/create', [CompanyController::class, 'create'])->name('create');
            Route::post('/', [CompanyController::class, 'store'])->name('store');
            Route::get('/{company}', [CompanyController::class, 'show'])->name('show');
            Route::get('/{company}/edit', [CompanyController::class, 'edit'])->name('edit');
            Route::put('/{company}', [CompanyController::class, 'update'])->name('update');
            Route::delete('/{company}', [CompanyController::class, 'destroy'])->name('destroy');
            Route::post('/{company}/logo', [CompanyController::class, 'uploadLogo'])->name('logo');
            Route::post('/{company}/users', [CompanyUserController::class, 'store'])->name('users.store');
            Route::delete('/{company}/users/{user}', [CompanyUserController::class, 'destroy'])->name('users.destroy');
        });
});
