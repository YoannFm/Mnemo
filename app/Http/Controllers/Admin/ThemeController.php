<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    public function index()
    {
        $colors = [
            'theme_body_bg'    => Setting::get('theme_body_bg', '#111113'),
            'theme_content_bg' => Setting::get('theme_content_bg', '#2E2E34'),
            'theme_card_bg'    => Setting::get('theme_card_bg', '#212227'),
            'theme_accent'     => Setting::get('theme_accent', '#EFB702'),
            'theme_header_bg'  => Setting::get('theme_header_bg', '#1a1b1f'),
            'theme_text_color' => Setting::get('theme_text_color', '#e2e8f0'),
        ];

        return view('admin.themes.index', compact('colors'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'theme_body_bg'    => 'required|string|max:20',
            'theme_content_bg' => 'required|string|max:20',
            'theme_card_bg'    => 'required|string|max:20',
            'theme_accent'     => 'required|string|max:20',
            'theme_header_bg'  => 'required|string|max:20',
            'theme_text_color' => 'required|string|max:20',
        ]);

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        return back()->with('success', 'Thème mis à jour.');
    }
}
