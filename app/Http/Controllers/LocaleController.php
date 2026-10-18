<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Switch the application locale for the current session.
     */
    public function update(Request $request, string $locale): RedirectResponse
    {
        if (! in_array($locale, SetLocale::SUPPORTED_LOCALES, true)) {
            abort(404);
        }

        $request->session()->put('locale', $locale);

        return back();
    }
}
