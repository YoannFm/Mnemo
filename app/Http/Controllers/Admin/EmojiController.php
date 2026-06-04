<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Emoji;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmojiController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->input('type', 'simple');
        $emojis = Emoji::where('type', $type)->orderBy('name')->paginate(40)->withQueryString();
        return view('admin.emojis.index', compact('emojis', 'type'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:50',
            'type'  => 'required|in:simple,animated',
            'image' => 'required|file|mimes:png,jpg,jpeg,gif,webp|max:2048',
        ]);

        $slug = Str::slug($data['name']);
        $base = $slug;
        $i = 1;
        while (Emoji::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        $path = $request->file('image')->store('emojis', 'public');

        Emoji::create([
            'name'       => $data['name'],
            'slug'       => $slug,
            'type'       => $data['type'],
            'image_path' => $path,
        ]);

        return back()->with('success', 'Emoji "' . $data['name'] . '" ajouté.');
    }

    public function destroy(Emoji $emoji)
    {
        Storage::disk('public')->delete($emoji->image_path);
        $emoji->delete();
        return back()->with('success', 'Emoji supprimé.');
    }
}
