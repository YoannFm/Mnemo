<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\LogHelper;
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
            'name'             => 'required|string|max:100|unique:themes,name',
            'body_bg'          => 'required|string|max:20',
            'content_bg'       => 'required|string|max:20',
            'card_bg'          => 'required|string|max:20',
            'accent_color'     => 'required|string|max:20',
            'header_bg'        => 'required|string|max:20',
            'text_color'       => 'required|string|max:20',
            'light_body_bg'    => 'required|string|max:20',
            'light_content_bg' => 'required|string|max:20',
            'light_card_bg'    => 'required|string|max:20',
            'light_header_bg'  => 'required|string|max:20',
            'light_text_color' => 'required|string|max:20',
        ]);

        $data['slug'] = $this->uniqueSlug(Str::slug($data['name']));
        $data['is_active'] = false;

        $theme = Theme::create($data);
        LogHelper::log('created_theme', 'theme', $theme->id, ['name' => $theme->name]);

        return redirect()->route('admin.themes.index')->with('success', 'Theme cree.');
    }

    public function edit(Theme $theme)
    {
        return view('admin.themes.edit', compact('theme'));
    }

    public function update(Request $request, Theme $theme)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:100|unique:themes,name,' . $theme->id,
            'body_bg'          => 'required|string|max:20',
            'content_bg'       => 'required|string|max:20',
            'card_bg'          => 'required|string|max:20',
            'accent_color'     => 'required|string|max:20',
            'header_bg'        => 'required|string|max:20',
            'text_color'       => 'required|string|max:20',
            'light_body_bg'    => 'required|string|max:20',
            'light_content_bg' => 'required|string|max:20',
            'light_card_bg'    => 'required|string|max:20',
            'light_header_bg'  => 'required|string|max:20',
            'light_text_color' => 'required|string|max:20',
        ]);

        $data['slug'] = $this->uniqueSlug(Str::slug($data['name']), $theme->id);

        $theme->update($data);
        LogHelper::log('updated_theme', 'theme', $theme->id, ['name' => $theme->name]);

        return redirect()->route('admin.themes.index')->with('success', 'Theme mis a jour.');
    }

    public function destroy(Theme $theme)
    {
        if ($theme->is_active) {
            return back()->with('error', 'Impossible de supprimer le theme actif.');
        }
        LogHelper::log('deleted_theme', 'theme', $theme->id, ['name' => $theme->name], 'warning');
        $theme->delete();
        return back()->with('success', 'Theme supprime.');
    }

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = $base;
        $i = 2;
        while (Theme::where('slug', $slug)->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    public function duplicate(Theme $theme)
    {
        $baseName = $theme->name . ' (copie)';
        $name = $baseName;
        $i = 2;
        while (Theme::where('name', $name)->exists()) {
            $name = $baseName . ' ' . $i++;
        }

        $new = $theme->replicate();
        $new->name = $name;
        $new->slug = Str::slug($name);
        $new->is_active = false;
        $new->save();

        LogHelper::log('duplicated_theme', 'theme', $new->id, ['name' => $new->name]);

        return redirect()->route('admin.themes.edit', $new)->with('success', 'Thème dupliqué.');
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
            'theme_text_color'       => $theme->text_color,
            'theme_light_body_bg'    => $theme->light_body_bg,
            'theme_light_content_bg' => $theme->light_content_bg,
            'theme_light_card_bg'    => $theme->light_card_bg,
            'theme_light_header_bg'  => $theme->light_header_bg,
            'theme_light_text_color' => $theme->light_text_color,
        ];

        foreach ($map as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now()]
            );
        }

        LogHelper::log('activated_theme', 'theme', $theme->id, ['name' => $theme->name]);
        return back()->with('success', 'Theme "' . $theme->name . '" active.');
    }
}
