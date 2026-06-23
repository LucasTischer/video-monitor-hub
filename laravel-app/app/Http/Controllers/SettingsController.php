<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingsRequest;
use App\Models\AppSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(Request $request): View
    {
        abort_unless($request->user()->is_admin, 403);

        return view('settings.edit', [
            'settings' => AppSetting::current(),
            'timezones' => timezone_identifiers_list(),
            'currentTime' => Carbon::now(AppSetting::currentTimezone()),
        ]);
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        AppSetting::query()->updateOrCreate(
            ['id' => AppSetting::SINGLETON_ID],
            $request->validated(),
        );

        return redirect()
            ->route('settings.edit')
            ->with('status', 'Settings updated successfully.');
    }
}
