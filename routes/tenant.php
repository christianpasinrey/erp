<?php

declare(strict_types=1);

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
});
