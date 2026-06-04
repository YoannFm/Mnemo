<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ThemeController extends Controller
{
    public function index()
    {
        $themes = Theme::orderBy('is_active', 'desc')->orderBy('name')->get();
        return view('admin.themes.index', compact('themes'));
    }

    public function create()
    {
        return view('admin.themes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'body_bg'     => 'required|string|max:20',
            'content_bg'  => 'required|string|max:20',
            'card_bg'     => 'required|string|max:20',
            'accent_color'=> 'required|string|max:20',
            'header_bg'   => 'required|string|max:20',
            'text_color'  => 'required|string|max:20',
        ]);

        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = false;

        Theme::create($data);

        return redirect()->route('admin.themes.index')->with('success', 'Theme cree.');
    }

    public function edit(Theme $theme)
    {
        return view('admin.themes.edit', compact('theme'));
    }

    public function update(Request $request, Theme $theme)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'body_bg'     => 'required|string|max:20',
            'content_bg'  => 'required|string|max:20',
            'card_bg'     => 'required|string|max:20',
            'accent_color'=> 'required|string|max:20',
            'header_bg'   => 'required|string|max:20',
            'text_color'  => 'required|string|max:20',
        ]);

        $data['slug'] = Str::slug($data['name']);

        $theme->update($data);

        return redirect()->route('admin.themes.index')->with('success', 'Theme mis a jour.');
    }

    public function destroy(Theme $theme)
    {
        if ($theme->is_active) {
            return back()->with('error', 'Impossible de supprimer le theme actif.');
        }
        $theme->delete();
        return back()->with('success', 'Theme supprime.');
    }

    public function activate(Theme $theme)
    {
        Theme::where('is_active', true)->update(['is_active' => false]);
        $theme->update(['is_active' => true]);

        $map = [
            'theme_body_bg'    => $theme->body_bg,
            'theme_content_bg' => $theme->content_bg,
            'theme_card_bg'    => $theme->card_bg,
            'theme_accent'     => $theme->accent_color,
            'theme_header_bg'  => $theme->header_bg,
            'theme_text_color' => $theme->text_color,
        ];

        foreach ($map as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now()]
            );
        }

        return back()->with('success', 'Theme "' . $theme->name . '" active.');
    }
}
