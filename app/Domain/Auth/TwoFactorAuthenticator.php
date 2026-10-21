<?php

namespace App\Domain\Auth;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use PragmaRX\Google2FA\Google2FA;

/**
 * Wraps the TOTP (Google Authenticator-compatible) library so the rest of
 * the app never touches the underlying package directly. QR codes are
 * rendered locally as inline SVG — no external image service is called,
 * since self-hosted installs may have no outbound internet access.
 */
class TwoFactorAuthenticator
{
    public function __construct(private Google2FA $engine) {}

    public function generateSecretKey(): string
    {
        return $this->engine->generateSecretKey();
    }

    public function verify(string $secret, string $code): bool
    {
        return (bool) $this->engine->verifyKey($secret, $code);
    }

    /**
     * Inline SVG markup for the setup QR code. $holder is the account
     * identifier shown under the entry in the authenticator app.
     */
    public function qrCodeSvg(string $secret, string $holder): string
    {
        $url = $this->engine->getQRCodeUrl(config('app.name'), $holder, $secret);

        $renderer = new ImageRenderer(new RendererStyle(192), new SvgImageBackEnd);

        return (new Writer($renderer))->writeString($url);
    }
}
