<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Settings\SettingsManager;
use App\Http\Controllers\Controller;
use App\Models\BusinessProfile;
use App\Models\Package;
use App\Models\Zone;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private SettingsManager $settings) {}

    public function index(): Response
    {
        $profile = BusinessProfile::current();

        return Inertia::render('Admin/Dashboard', [
            'checklist' => [
                'business_profile' => filled($profile->name) && filled($profile->email),
                'email' => (bool) $this->settings->get('smtp'),
                'zones' => Zone::query()->exists(),
                'packages' => Package::query()->exists(),
            ],
        ]);
    }
}
