<?php

use App\Http\Controllers\Customer\Auth\AuthenticatedCustomerSessionController;
use App\Http\Controllers\Customer\Auth\RegisteredCustomerController;
use App\Http\Controllers\Customer\BookingController;
use App\Http\Controllers\Customer\MyBookingController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:customers')->group(function () {
    Route::get('/register', [RegisteredCustomerController::class, 'create'])->name('customer.register');
    Route::post('/register', [RegisteredCustomerController::class, 'store']);

    Route::get('/customer-login', [AuthenticatedCustomerSessionController::class, 'create'])->name('customer.login');
    Route::post('/customer-login', [AuthenticatedCustomerSessionController::class, 'store']);
});

Route::middleware('auth:customers')->group(function () {
    Route::post('/customer-logout', [AuthenticatedCustomerSessionController::class, 'destroy'])->name('customer.logout');

    Route::prefix('book')->name('customer.booking.')->group(function () {
        Route::get('/', [BookingController::class, 'build'])->name('build');
        Route::post('/', [BookingController::class, 'storeBuild']);
        Route::get('/slot', [BookingController::class, 'slot'])->name('slot');
        Route::post('/slot', [BookingController::class, 'storeSlot']);
        Route::get('/details', [BookingController::class, 'details'])->name('details');
        Route::post('/details', [BookingController::class, 'storeDetails']);
        Route::get('/review', [BookingController::class, 'review'])->name('review');
        Route::post('/review', [BookingController::class, 'store'])->name('submit');
    });

    Route::get('/my-booking', [MyBookingController::class, 'show'])->name('customer.my-booking');
});
