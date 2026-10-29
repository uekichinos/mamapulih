<?php

use App\Http\Controllers\Customer\Auth\AuthenticatedCustomerSessionController;
use App\Http\Controllers\Customer\Auth\RegisteredCustomerController;
use App\Http\Controllers\Customer\BookingController;
use App\Http\Controllers\Customer\ConsentController;
use App\Http\Controllers\Customer\MyBookingController;
use App\Http\Controllers\Customer\PaymentController;
use App\Http\Controllers\Customer\StartController;
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
    Route::post('/my-booking/cancel', [MyBookingController::class, 'cancel'])->name('customer.my-booking.cancel');
    Route::get('/my-booking/receipts/{receipt}', [MyBookingController::class, 'viewReceipt'])->name('customer.my-booking.receipts.view');
    Route::get('/my-booking/schedule', [MyBookingController::class, 'schedule'])->name('customer.my-booking.schedule');
    Route::get('/my-booking/{booking}', [MyBookingController::class, 'showArchived'])->name('customer.my-booking.archive');

    Route::get('/payment', [PaymentController::class, 'show'])->name('customer.payment');
    Route::post('/payment', [PaymentController::class, 'store']);
    Route::post('/payment/receipt', [PaymentController::class, 'uploadReceipt'])->name('customer.payment.receipt');

    Route::get('/start', [StartController::class, 'show'])->name('customer.start');
    Route::post('/start', [StartController::class, 'store']);
    Route::post('/start/confirm', [StartController::class, 'confirm'])->name('customer.start.confirm');
});

// Public, token-secured — no customer login required to view/decide.
Route::get('/consent/{token}', [ConsentController::class, 'show'])->name('customer.consent.show');
Route::post('/consent/{token}', [ConsentController::class, 'store'])->name('customer.consent.store');
