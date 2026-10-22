<?php

namespace App\Domain\Payments;

use App\Models\PaymentProvider;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Wraps the single `manual` row in payment_providers (bank details, QR
 * image, customer-facing instructions) — the admin-facing counterpart of
 * SmtpSettings, but backed by the payment_providers table rather than
 * the generic settings key, since the docs model it as its own table.
 */
class ManualPaymentSettings
{
    public function provider(): PaymentProvider
    {
        return PaymentProvider::query()->where('driver', 'manual')->firstOrFail();
    }

    /**
     * @return array<string, mixed>
     */
    public function get(): array
    {
        $provider = $this->provider();
        $credentials = $provider->credentials ?? [];

        return [
            'bank_name' => $credentials['bank_name'] ?? null,
            'account_name' => $credentials['account_name'] ?? null,
            'account_number' => $credentials['account_number'] ?? null,
            'qr_image_url' => isset($credentials['qr_image_path'])
                ? Storage::disk('public')->url($credentials['qr_image_path'])
                : null,
            'instructions' => $provider->instructions ?? ['en' => '', 'ms' => ''],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function save(array $data, ?UploadedFile $qrImage = null): void
    {
        $provider = $this->provider();
        $credentials = $provider->credentials ?? [];

        $credentials['bank_name'] = $data['bank_name'];
        $credentials['account_name'] = $data['account_name'];
        $credentials['account_number'] = $data['account_number'];

        if ($qrImage) {
            $credentials['qr_image_path'] = $qrImage->store('payment-qr', 'public');
        }

        $provider->update([
            'credentials' => $credentials,
            'instructions' => [
                'en' => $data['instructions']['en'] ?? '',
                'ms' => $data['instructions']['ms'] ?? '',
            ],
        ]);
    }
}
