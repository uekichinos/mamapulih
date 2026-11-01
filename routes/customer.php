<?php

use App\Http\Controllers\Customer\Auth\AuthenticatedCustomerSessionController;
use App\Http\Controllers\Customer\Auth\RegisteredCustomerController;
use App\Http\Controllers\Customer\BookingController;
use App\Http\Controllers\Customer\ConsentController;
use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Customer\MyBookingController;
use App\Http\Controllers\Customer\PasswordController;
use App\Http\Controllers\Customer\PaymentController;
use App\Http\Controllers\Customer\ProfileController;
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

    // Not "/dashboard" — that path already belongs to the staff dashboard
    // route (routes/web.php) under the default "web" guard; same
    // first-registered-route-wins collision as the "/profile" one above.
    Route::get('/home', [DashboardController::class, 'index'])->name('customer.dashboard');

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

    Route::get('/booking', [MyBookingController::class, 'show'])->name('customer.my-booking');
    Route::post('/booking/{booking}/cancel', [MyBookingController::class, 'cancel'])->name('customer.my-booking.cancel');
    Route::get('/booking/receipts/{receipt}', [MyBookingController::class, 'viewReceipt'])->name('customer.my-booking.receipts.view');
    Route::get('/schedule/{booking}', [MyBookingController::class, 'schedule'])->name('customer.my-booking.schedule');
    Route::get('/booking/{booking}', [MyBookingController::class, 'showArchived'])->name('customer.my-booking.archive');

    Route::get('/payment', [PaymentController::class, 'show'])->name('customer.payment');
    Route::post('/payment', [PaymentController::class, 'store']);
    Route::post('/payment/receipt', [PaymentController::class, 'uploadReceipt'])->name('customer.payment.receipt');

    Route::get('/start/{booking}', [StartController::class, 'show'])->name('customer.start');
    Route::post('/start/{booking}', [StartController::class, 'store']);
    Route::post('/start/{booking}/confirm', [StartController::class, 'confirm'])->name('customer.start.confirm');

    // Not "/profile" — that path already belongs to the staff profile route
    // (routes/web.php), which runs under the default "web" guard; reusing it
    // here would make this customer route unreachable (same method + URI as
    // an earlier-registered route is never matched).
    Route::get('/my-profile', [ProfileController::class, 'edit'])->name('customer.profile.edit');
    Route::patch('/my-profile', [ProfileController::class, 'update'])->name('customer.profile.update');
    Route::put('/my-profile/password', [PasswordController::class, 'update'])->name('customer.password.update');
});

// Public, token-secured — no customer login required to view/decide.
Route::get('/consent/{token}', [ConsentController::class, 'show'])->name('customer.consent.show');
Route::post('/consent/{token}', [ConsentController::class, 'store'])->name('customer.consent.store');
