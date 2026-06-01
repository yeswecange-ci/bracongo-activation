<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LckSetting;
use Illuminate\Http\Request;

class LckSettingsController extends Controller
{
    public function edit()
    {
        $settings = LckSetting::asMap();
        return view('admin.lck.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'pickup_name'     => 'required|string|max:100',
            'pickup_address'  => 'required|string|max:300',
            'pickup_city'     => 'required|string|max:100',
            'pickup_phone'    => 'nullable|string|max:30',
            'pickup_hours'    => 'required|string|max:100',
            'pickup_deadline' => 'required|integer|min:1|max:30',
            'website_url'     => 'nullable|url|max:200',
        ]);

        $keys = ['pickup_name', 'pickup_address', 'pickup_city', 'pickup_phone', 'pickup_hours', 'pickup_deadline', 'website_url'];

        foreach ($keys as $key) {
            LckSetting::set($key, $request->input($key));
        }

        return redirect()->route('admin.lck.settings')
            ->with('success', 'Paramètres enregistrés.');
    }
}
