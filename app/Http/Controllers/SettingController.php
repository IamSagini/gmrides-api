<?php
namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    // Public: fare rates the frontend needs to calculate prices
    public function fares()
    {
        return response()->json([
            'success' => true,
            'rates' => [
                'base_rate' => (float) Setting::get('base_rate', 4.5),
                'airport_base' => (float) Setting::get('airport_base', 1500),
                'airport_rate' => (float) Setting::get('airport_rate', 5.5),
            ],
        ]);
    }

    // Admin only: update fare rates
    public function updateFares(Request $request)
    {
        $data = $request->validate([
            'base_rate' => 'required|numeric|min:0',
            'airport_base' => 'required|numeric|min:0',
            'airport_rate' => 'required|numeric|min:0',
        ]);

        foreach ($data as $key => $value) {
            Setting::put($key, $value);
        }

        return response()->json([
            'success' => true,
            'rates' => [
                'base_rate' => (float) $data['base_rate'],
                'airport_base' => (float) $data['airport_base'],
                'airport_rate' => (float) $data['airport_rate'],
            ],
        ]);
    }

    public function siteSettings()
    {
        return response()->json([
            'success' => true,
            'settings' => [
                'support_email' => Setting::get('support_email', 'hello@gmrides.net'),
                'brand_name'    => Setting::get('brand_name', 'GM Rides'),
                'website'       => Setting::get('website', 'gmrides.net'),
            ],
        ]);
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'support_email' => 'nullable|email|max:255',
            'brand_name'    => 'nullable|string|max:255',
            'website'       => 'nullable|string|max:255',
        ]);
        foreach ($data as $key => $value) {
            if ($value !== null) Setting::put($key, $value);
        }
        return response()->json(['success' => true, 'settings' => $data]);
    }
}
