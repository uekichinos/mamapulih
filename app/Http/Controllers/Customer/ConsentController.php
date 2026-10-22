<?php

namespace App\Http\Controllers\Customer;

use App\Domain\Booking\ConsentService;
use App\Http\Controllers\Controller;
use App\Models\Consent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ConsentController extends Controller
{
    public function __construct(private ConsentService $consents) {}

    public function show(string $token): Response
    {
        $consent = $this->findByToken($token);

        return Inertia::render('Customer/Consent', [
            'found' => $consent !== null,
            'decided' => $consent?->isDecided() ?? false,
            'expired' => $consent?->isExpired() ?? false,
            'decision' => $consent?->decision,
            'planChange' => $consent?->planChange?->load('toVariant.package', 'fromVariant'),
            'token' => $token,
        ]);
    }

    public function store(string $token, Request $request): RedirectResponse
    {
        $consent = $this->findByToken($token);

        $data = $request->validate(['decision' => ['required', 'in:agree,decline']]);

        if ($consent && ! $consent->isDecided() && ! $consent->isExpired()) {
            $this->consents->decide($consent, $data['decision'], $request);
        }

        return redirect()->route('customer.consent.show', ['token' => $token]);
    }

    private function findByToken(string $token): ?Consent
    {
        return Consent::query()->where('token_hash', hash('sha256', $token))->first();
    }
}
