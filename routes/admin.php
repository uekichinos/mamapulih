<?php

use App\Http\Controllers\Admin\AddonController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BookingApprovalController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\BusinessProfileController;
use App\Http\Controllers\Admin\CapacityController;
use App\Http\Controllers\Admin\ClosureDayController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmailSettingsController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\PaymentSettingsController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\StaffUserController;
use App\Http\Controllers\Admin\VariantController;
use App\Http\Controllers\Admin\ZoneController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'role:owner', 'force-2fa'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/business-profile', [BusinessProfileController::class, 'edit'])->name('business-profile.edit');
    Route::put('/business-profile', [BusinessProfileController::class, 'update'])->name('business-profile.update');

    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

    Route::get('/email-settings', [EmailSettingsController::class, 'edit'])->name('email-settings.edit');
    Route::put('/email-settings', [EmailSettingsController::class, 'update'])->name('email-settings.update');
    Route::post('/email-settings/test', [EmailSettingsController::class, 'test'])->name('email-settings.test');

    Route::resource('packages', PackageController::class)->except(['show']);
    Route::resource('packages.variants', VariantController::class)
        ->except(['show', 'index', 'create', 'edit'])
        ->shallow();
    Route::resource('addons', AddonController::class)->except(['show']);
    Route::resource('zones', ZoneController::class)->except(['show']);
    Route::resource('closure-days', ClosureDayController::class)->except(['show', 'edit', 'update']);
    Route::resource('staff', StaffUserController::class)->except(['show']);

    Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');

    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');

    Route::get('/booking-approvals', [BookingApprovalController::class, 'index'])->name('booking-approvals.index');
    Route::post('/booking-approvals/{booking}/approve', [BookingApprovalController::class, 'approve'])->name('booking-approvals.approve');
    Route::post('/booking-approvals/{booking}/reject', [BookingApprovalController::class, 'reject'])->name('booking-approvals.reject');

    Route::get('/capacity', [CapacityController::class, 'index'])->name('capacity.index');

    Route::get('/payment-settings', [PaymentSettingsController::class, 'edit'])->name('payment-settings.edit');
    Route::put('/payment-settings', [PaymentSettingsController::class, 'update'])->name('payment-settings.update');

    Route::post('/bookings/{booking}/payments', [BookingController::class, 'recordPayment'])->name('bookings.payments.store');
    Route::post('/bookings/{booking}/refunds/{refund}/mark-paid', [BookingController::class, 'markRefundPaid'])->name('bookings.refunds.mark-paid');
    Route::post('/bookings/{booking}/resend-consent', [BookingController::class, 'resendConsent'])->name('bookings.resend-consent');
});
