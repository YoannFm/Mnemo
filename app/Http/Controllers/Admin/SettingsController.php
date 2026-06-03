<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'site_name'        => Setting::get('site_name', 'Mnémo'),
            'site_description' => Setting::get('site_description', ''),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'site_name'        => 'required|string|max:100',
            'site_description' => 'nullable|string|max:500',
        ]);

        Setting::set('site_name', $request->input('site_name'));
        Setting::set('site_description', $request->input('site_description', ''));

        return back()->with('success', 'Paramètres sauvegardés.');
    }
}
