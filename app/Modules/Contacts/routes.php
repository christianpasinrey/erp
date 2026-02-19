<?php

use App\Modules\Contacts\Controllers\ContactAddressController;
use App\Modules\Contacts\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Contacts Module Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by ContactsModuleServiceProvider and protected by
| the module:contacts middleware to ensure the module is active.
|
*/

Route::middleware(['web', 'auth', 'verified', 'company', 'module:contacts'])
    ->prefix('contacts')
    ->name('contacts.')
    ->group(function () {
        Route::get('/', [ContactController::class, 'index'])->name('index');
        Route::get('/create', [ContactController::class, 'create'])->name('create');
        Route::post('/', [ContactController::class, 'store'])->name('store');
        Route::get('/{contact}', [ContactController::class, 'show'])->name('show');
        Route::get('/{contact}/edit', [ContactController::class, 'edit'])->name('edit');
        Route::put('/{contact}', [ContactController::class, 'update'])->name('update');
        Route::delete('/{contact}', [ContactController::class, 'destroy'])->name('destroy');

        // Address routes (nested under contacts)
        Route::post('/{contact}/addresses', [ContactAddressController::class, 'store'])->name('addresses.store');
        Route::put('/{contact}/addresses/{address}', [ContactAddressController::class, 'update'])->name('addresses.update');
        Route::delete('/{contact}/addresses/{address}', [ContactAddressController::class, 'destroy'])->name('addresses.destroy');
    });
