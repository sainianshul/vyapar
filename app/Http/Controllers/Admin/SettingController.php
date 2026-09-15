<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    /**
     * Show the general settings page.
     */
    public function general()
    {
        return view('admin.settings.general');
    }

    /**
     * Update the general settings.
     */
    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'min_withdrawal_amount' => 'required|numeric|min:0',
            'nurse_cancel_strike_limit' => 'required|integer|min:0',
            'max_booking_advance_days' => 'required|integer|min:1',
            'min_booking_notice_hours' => 'required|integer|min:0',
        ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'type' => is_int($value) ? 'integer' : 'numeric']
            );
        }

        // Clear the cache so new settings load instantly
        Cache::forget('app_settings');

        return redirect()->back()->with('success', 'General settings updated successfully.');
    }
}
