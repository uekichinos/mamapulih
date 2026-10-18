<?php

use App\Domain\Settings\SmtpSettings;
use App\Mail\SmtpTestMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

it('saves SMTP settings with the password encrypted at rest', function () {
    $owner = User::factory()->create();

    $this->actingAs($owner)->put(route('admin.email-settings.update'), [
        'host' => 'smtp.example.test',
        'port' => 587,
        'encryption' => 'tls',
        'username' => 'kitchen@example.test',
        'password' => 'super-secret',
        'from_address' => 'kitchen@example.test',
        'from_name' => 'MamaPulih',
    ])->assertRedirect();

    $smtp = app(SmtpSettings::class);

    expect($smtp->get()['password'])->not->toBe('super-secret');
    expect($smtp->decryptedPassword())->toBe('super-secret');
    expect($smtp->masked()['password'])->toBeNull();
    expect($smtp->masked()['has_password'])->toBeTrue();
});

it('keeps the existing password when none is submitted on update', function () {
    $owner = User::factory()->create();
    $smtp = app(SmtpSettings::class);

    $smtp->save([
        'host' => 'smtp.example.test',
        'port' => 587,
        'encryption' => 'tls',
        'username' => 'kitchen@example.test',
        'password' => 'original-secret',
        'from_address' => 'kitchen@example.test',
        'from_name' => 'MamaPulih',
    ]);

    $this->actingAs($owner)->put(route('admin.email-settings.update'), [
        'host' => 'smtp.example.test',
        'port' => 2525,
        'encryption' => 'tls',
        'username' => 'kitchen@example.test',
        'password' => '',
        'from_address' => 'kitchen@example.test',
        'from_name' => 'MamaPulih',
    ]);

    expect($smtp->decryptedPassword())->toBe('original-secret');
    expect($smtp->get()['port'])->toBe(2525);
});

it('sends a test email using the configured settings', function () {
    Mail::fake();

    $owner = User::factory()->create();
    app(SmtpSettings::class)->save([
        'host' => 'smtp.example.test',
        'port' => 587,
        'encryption' => 'tls',
        'username' => 'kitchen@example.test',
        'password' => 'secret',
        'from_address' => 'kitchen@example.test',
        'from_name' => 'MamaPulih',
    ]);

    $this->actingAs($owner)->post(route('admin.email-settings.test'))->assertRedirect();

    Mail::assertSent(SmtpTestMail::class, fn ($mailable) => $mailable->hasTo($owner->email));
});
