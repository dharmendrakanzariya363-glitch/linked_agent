<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAppearanceRequest;
use Illuminate\Http\RedirectResponse;

class AppearanceController extends Controller
{
    public function update(UpdateAppearanceRequest $request): RedirectResponse
    {
        $appearance = $request->validated('appearance');

        cookie()->queue(cookie(
            'appearance',
            $appearance,
            60 * 24 * 365,
            '/',
            null,
            config('session.secure'),
            false,
            false,
            config('session.same_site'),
        ));

        return back();
    }
}
