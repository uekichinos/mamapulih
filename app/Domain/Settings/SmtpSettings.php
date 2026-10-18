<?php

namespace App\Domain\Settings;

use Illuminate\Support\Facades\Crypt;

class SmtpSettings
{
    public function __construct(private SettingsManager $settings) {}

    /**
     * @return array<string, mixed>|null
     */
    public function get(): ?array
    {
        return $this->settings->get('smtp');
    }

    /**
     * Save SMTP settings, encrypting the password at rest. When $password is
     * null/empty the previously stored (still encrypted) password is kept.
     *
     * @param  array<string, mixed>  $data
     */
    public function save(array $data): void
    {
        $password = $data['password'] ?? null;

        if (blank($password)) {
            $password = $this->get()['password'] ?? null;
        } else {
            $password = Crypt::encryptString($password);
        }

        $this->settings->set('smtp', [...$data, 'password' => $password]);
    }

    public function decryptedPassword(): ?string
    {
        $encrypted = $this->get()['password'] ?? null;

        return $encrypted ? Crypt::decryptString($encrypted) : null;
    }

    /**
     * Apply the admin-configured SMTP settings to the running app's mail
     * config, so Laravel's Mail facade uses them for this request/job.
     */
    public function applyToRuntimeConfig(): void
    {
        $settings = $this->get();

        if (! $settings) {
            return;
        }

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $settings['host'],
            'mail.mailers.smtp.port' => $settings['port'],
            'mail.mailers.smtp.encryption' => $settings['encryption'] === 'none' ? null : $settings['encryption'],
            'mail.mailers.smtp.username' => $settings['username'],
            'mail.mailers.smtp.password' => $this->decryptedPassword(),
            'mail.from.address' => $settings['from_address'],
            'mail.from.name' => $settings['from_name'],
        ]);
    }

    /**
     * The settings with the password masked, safe to send to the frontend.
     *
     * @return array<string, mixed>|null
     */
    public function masked(): ?array
    {
        $settings = $this->get();

        if (! $settings) {
            return null;
        }

        return [...$settings, 'password' => null, 'has_password' => filled($settings['password'] ?? null)];
    }
}
