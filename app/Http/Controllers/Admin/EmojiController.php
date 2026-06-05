<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Models\Emoji;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;

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

        $path = $request->file('image')->store('emojis', 'public');

        $emoji = Emoji::create([
            'name'       => $data['name'],
            'slug'       => $this->uniqueSlug(Str::slug($data['name'])),
            'type'       => $data['type'],
            'image_path' => $path,
        ]);
        LogHelper::log('created_emoji', 'emoji', $emoji->id, ['name' => $emoji->name, 'slug' => $emoji->slug]);

        return back()->with('success', 'Emoji "' . $data['name'] . '" ajouté.');
    }

    public function importPack(Request $request)
    {
        $request->validate([
            'pack' => 'required|file|mimes:zip|max:51200',
        ]);

        $zip = new \ZipArchive();
        $zipPath = $request->file('pack')->getRealPath();

        if ($zip->open($zipPath) !== true) {
            return back()->with('error', 'Impossible d\'ouvrir le fichier ZIP.');
        }

        $manifest = null;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (basename($name) === 'manifest.yml') {
                $manifest = Yaml::parse($zip->getFromIndex($i));
                break;
            }
        }

        if (!$manifest || empty($manifest['namespace']) || empty($manifest['emojis'])) {
            $zip->close();
            return back()->with('error', 'manifest.yml manquant ou invalide (namespace et emojis requis).');
        }

        $namespace = Str::slug($manifest['namespace']);
        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        Storage::disk('public')->makeDirectory('emojis');

        foreach ($manifest['emojis'] as $entry) {
            if (empty($entry['name']) || empty($entry['file'])) {
                $skipped++;
                $errors[] = 'Entrée invalide (name ou file manquant)';
                continue;
            }

            $fileContent = $zip->getFromName($entry['file']);
            if ($fileContent === false) {
                $fileContent = $zip->getFromName(basename($entry['file']));
            }
            if ($fileContent === false) {
                $skipped++;
                $errors[] = '"' . $entry['file'] . '" introuvable dans le ZIP';
                continue;
            }

            $ext  = strtolower(pathinfo($entry['file'], PATHINFO_EXTENSION));
            $type = in_array($ext, ['gif', 'webp']) ? 'animated' : 'simple';
            $slug = $this->uniqueSlug($namespace . '-' . Str::slug($entry['name']));
            $filename = 'emojis/' . $slug . '.' . $ext;

            Storage::disk('public')->put($filename, $fileContent);

            Emoji::create([
                'name'       => $entry['name'],
                'slug'       => $slug,
                'type'       => $type,
                'image_path' => $filename,
            ]);

            $imported++;
        }

        $zip->close();

        $msg = "{$imported} emoji(s) importé(s), {$skipped} ignoré(s).";
        if (!empty($errors)) {
            $shown = array_slice($errors, 0, 3);
            $msg .= ' Raisons : ' . implode(' ; ', $shown);
            if (count($errors) > 3) {
                $msg .= '... (' . count($errors) . ' erreurs au total)';
            }
        }
        if ($imported > 0) {
            LogHelper::log('imported_emojis', 'emoji', null, ['namespace' => $namespace, 'count' => $imported]);
        }
        return back()->with($imported > 0 || $skipped === 0 ? 'success' : 'error', $msg);
    }

    public function destroy(Emoji $emoji)
    {
        LogHelper::log('deleted_emoji', 'emoji', $emoji->id, ['name' => $emoji->name, 'slug' => $emoji->slug], 'warning');
        Storage::disk('public')->delete($emoji->image_path);
        $emoji->delete();
        return back()->with('success', 'Emoji supprimé.');
    }

    private function uniqueSlug(string $base): string
    {
        $slug = $base;
        $i = 1;
        while (Emoji::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
