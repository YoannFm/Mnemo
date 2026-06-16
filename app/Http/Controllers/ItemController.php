<?php

namespace App\Http\Controllers;

use App\Helpers\LogHelper;
use App\Models\Item;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ItemController extends Controller
{
    public function index(Module $module)
    {
        $this->authorizeOwner($module);
        return redirect()->route('modules.show', $module);
    }

    public function create(Module $module)
    {
        $this->authorizeOwner($module);
        return view('items.create', compact('module'));
    }

    public function store(Request $request, Module $module)
    {
        $this->authorizeOwner($module);

        $rules = [
            'name_fr'         => $module->field_name_fr  ? 'required|string|max:255' : 'nullable|string|max:255',
            'name_alt'        => $module->field_name_alt ? 'required|string|max:255' : 'nullable|string|max:255',
            'function_text'   => $module->field_function ? 'required|string|max:2000' : 'nullable|string|max:2000',
            'photo'           => $module->field_photo    ? 'required|image|mimes:jpeg,png,jpg,webp|max:4096' : 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'photo_crop_data' => 'nullable|string',
            'audio'           => $module->field_audio    ? 'required|file|mimes:mp3,ogg,wav,m4a|max:10240' : 'nullable|file|mimes:mp3,ogg,wav,m4a|max:10240',
        ];

        $validated = $request->validate($rules);

        $photoPath = $request->hasFile('photo') ? $this->storePhoto($request->file('photo')) : null;
        $audioPath = $request->hasFile('audio') ? $this->storeAudio($request->file('audio')) : null;

        $item = $module->items()->create([
            'name_fr'       => $validated['name_fr'] ?? null,
            'name_alt'      => $validated['name_alt'] ?? null,
            'function_text' => $validated['function_text'] ?? null,
            'photo_path'    => $photoPath,
            'audio_path'    => $audioPath,
        ]);

        LogHelper::log('created_item', 'module', $module->id, ['item_id' => $item->id]);

        return redirect()->route('modules.show', $module)
            ->with('success', 'Item ajouté avec succès !');
    }

    public function edit(Module $module, Item $item)
    {
        $this->authorizeOwner($module);
        $this->ensureBelongsToModule($item, $module);

        return view('items.edit', compact('module', 'item'));
    }

    public function update(Request $request, Module $module, Item $item)
    {
        $this->authorizeOwner($module);
        $this->ensureBelongsToModule($item, $module);

        $rules = [
            'name_fr'         => $module->field_name_fr  ? 'required|string|max:255' : 'nullable|string|max:255',
            'name_alt'        => $module->field_name_alt ? 'required|string|max:255' : 'nullable|string|max:255',
            'function_text'   => $module->field_function ? 'required|string|max:2000' : 'nullable|string|max:2000',
            'photo'           => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'photo_crop_data' => 'nullable|string',
            'audio'           => 'nullable|mimes:mp3,wav,ogg,m4a|max:20480',
        ];

        $validated = $request->validate($rules);

        $old = [
            'name_fr'       => $item->name_fr,
            'name_alt'      => $item->name_alt,
            'function_text' => $item->function_text,
        ];

        $photoPath = $item->photo_path;
        $audioPath = $item->audio_path;

        if ($request->hasFile('photo')) {
            if ($item->photo_path) {
                Storage::disk('public')->delete($item->photo_path);
            }
            $photoPath = $this->storePhoto($request->file('photo'));
        }

        if ($request->hasFile('audio')) {
            if ($item->audio_path) {
                Storage::disk('public')->delete($item->audio_path);
            }
            $audioPath = $this->storeAudio($request->file('audio'));
        }

        $item->update([
            'name_fr'       => $validated['name_fr'] ?? $item->name_fr,
            'name_alt'      => $validated['name_alt'] ?? $item->name_alt,
            'function_text' => $validated['function_text'] ?? $item->function_text,
            'photo_path'    => $photoPath,
            'audio_path'    => $audioPath,
        ]);

        $new = [
            'name_fr'       => $item->name_fr,
            'name_alt'      => $item->name_alt,
            'function_text' => $item->function_text,
        ];

        LogHelper::log('updated_item', 'module', $module->id, ['item_id' => $item->id], 'info', json_encode($old), json_encode($new));

        return redirect()->route('modules.show', $module)
            ->with('success', 'Item mis a jour !');
    }

    public function destroy(Module $module, Item $item)
    {
        $this->authorizeOwner($module);
        $this->ensureBelongsToModule($item, $module);

        if ($item->photo_path) {
            Storage::disk('public')->delete($item->photo_path);
        }

        if ($item->audio_path) {
            Storage::disk('public')->delete($item->audio_path);
        }

        LogHelper::log('deleted_item', 'module', $module->id, ['item_id' => $item->id], 'warning');

        $item->delete();

        return redirect()->route('modules.show', $module)
            ->with('success', 'Item supprime.');
    }

    public function showImportForm(Module $module)
    {
        $this->authorizeOwner($module);
        return view('items.import', compact('module'));
    }

    public function importCsv(Request $request, Module $module)
    {
        $this->authorizeOwner($module);

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $content = file_get_contents($file->getRealPath());
        $sep = substr_count($content, ';') >= substr_count($content, ',') ? ';' : ',';

        $handle = fopen($file->getRealPath(), 'r');

        $count = 0;
        $errors = [];
        $lineNum = 0;

        while (($row = fgetcsv($handle, 1000, $sep)) !== false) {
            $lineNum++;

            $nameFr = trim($row[0] ?? '');
            $nameAlt = trim($row[1] ?? '');
            $function = trim($row[2] ?? '');

            if (empty($nameFr)) {
                continue;
            }

            $module->items()->create([
                'name_fr' => mb_substr($nameFr, 0, 255),
                'name_alt' => mb_substr($nameAlt, 0, 255),
                'function_text' => mb_substr($function, 0, 2000),
                'photo_path' => null,
            ]);

            $count++;
        }

        fclose($handle);

        LogHelper::log('imported_items_csv', 'module', $module->id, ['count' => $count]);

        return redirect()->route('modules.show', $module)
            ->with('success', "{$count} item(s) importes.");
    }

    private function storePhoto($file): string
    {
        $targetSize = 800;
        $quality = 85;

        $tmpPath = $file->getRealPath();
        $mime = $file->getMimeType();

        $source = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($tmpPath),
            'image/png' => @imagecreatefrompng($tmpPath),
            'image/webp' => @imagecreatefromwebp($tmpPath),
            default => @imagecreatefromjpeg($tmpPath),
        };

        if (!$source) {
            throw new \RuntimeException('Image invalide');
        }

        [$w, $h] = getimagesize($tmpPath);

        $scale = $targetSize / min($w, $h);
        $nw = (int)($w * $scale);
        $nh = (int)($h * $scale);

        $resized = imagecreatetruecolor($nw, $nh);

        if ($mime === 'image/png') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
        }

        imagecopyresampled($resized, $source, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($source);

        $x = (int)(($nw - $targetSize) / 2);
        $y = (int)(($nh - $targetSize) / 2);

        $canvas = imagecreatetruecolor($targetSize, $targetSize);
        imagecopy($canvas, $resized, 0, 0, $x, $y, $targetSize, $targetSize);
        imagedestroy($resized);

        $tmp = tempnam(sys_get_temp_dir(), 'img') . '.jpg';
        imagejpeg($canvas, $tmp, $quality);
        imagedestroy($canvas);

        $path = 'items/' . Str::uuid() . '.jpg';
        Storage::disk('public')->put($path, file_get_contents($tmp));
        unlink($tmp);

        return $path;
    }

    private function storeAudio($file): string
    {
        $ext = $file->getClientOriginalExtension() ?: 'mp3';
        $path = 'items/audio/' . Str::uuid() . '.' . $ext;

        Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));

        return $path;
    }

    private function authorizeOwner(Module $module): void
    {
        if ($module->owner_id !== Auth::id() && !Auth::user()?->is_admin) {
            abort(403);
        }
    }

    private function ensureBelongsToModule(Item $item, Module $module): void
    {
        if ($item->module_id !== $module->id) {
            abort(404);
        }
    }
}
