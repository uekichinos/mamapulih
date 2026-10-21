<?php

use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Auth\TwoFactorSetupController;
use App\Http\Controllers\Install\InstallController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::prefix('install')->name('install.')->middleware('not-installed')->group(function () {
    Route::get('/', [InstallController::class, 'code'])->name('index');
    Route::post('/', [InstallController::class, 'verify'])->name('verify');
    Route::get('/environment', [InstallController::class, 'environment'])->name('environment');
    Route::get('/database', [InstallController::class, 'database'])->name('database');
    Route::post('/database', [InstallController::class, 'createAdmin'])->name('database.admin');
    Route::get('/finish', [InstallController::class, 'finish'])->name('finish');
    Route::post('/finish', [InstallController::class, 'complete'])->name('complete');
});

Route::middleware('installed')->group(function () {
    Route::patch('/locale/{locale}', [LocaleController::class, 'update'])->name('locale.update');

    Route::get('/', function () {
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    });

    Route::get('/dashboard', function () {
        if (auth()->user()->isOwner()) {
            return redirect()->route('admin.dashboard');
        }

        // Kitchen has no screens of its own yet (prep sheet arrives in a
        // later sprint), so just show a holding page rather than a dead end.
        return Inertia::render('Dashboard');
    })->middleware(['auth', 'verified', 'force-2fa'])->name('dashboard');

    Route::middleware(['auth', 'force-2fa'])->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });

    // 2FA setup is reachable without the force-2fa gate (it's the way out of it).
    Route::middleware('auth')->group(function () {
        Route::get('/two-factor-setup', [TwoFactorSetupController::class, 'show'])->name('two-factor.setup');
        Route::post('/two-factor-setup/confirm', [TwoFactorSetupController::class, 'confirm'])->name('two-factor.setup.confirm');
        Route::delete('/two-factor-setup', [TwoFactorSetupController::class, 'destroy'])->name('two-factor.setup.destroy');
    });

    // The challenge happens between password auth and a real session, so
    // the user is still a guest as far as the auth guard is concerned.
    Route::middleware('guest')->group(function () {
        Route::get('/two-factor-challenge', [TwoFactorChallengeController::class, 'show'])->name('two-factor.challenge');
        Route::post('/two-factor-challenge', [TwoFactorChallengeController::class, 'store'])->name('two-factor.challenge.store');
    });

    require __DIR__.'/admin.php';
    require __DIR__.'/auth.php';
});
