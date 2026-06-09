<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;
use ZipArchive;

class ModuleExportController extends Controller
{
    /**
     * Exporte un module en ZIP contenant manifest.yml + dossier images/.
     */
    public function export(Module $module)
    {
        $this->authorizeOwner($module);

        $items = $module->items()->get();

        $tmpZip = tempnam(sys_get_temp_dir(), 'mnemo_export_') . '.zip';

        $zip = new ZipArchive();
        if ($zip->open($tmpZip, ZipArchive::CREATE) !== true) {
            return back()->with('error', 'Impossible de créer le fichier ZIP.');
        }

        $manifest = [
            'title'       => $module->title,
            'description' => $module->description ?? '',
            'items'       => [],
        ];

        foreach ($items as $item) {
            $entry = [
                'name_fr'       => $item->name_fr,
                'name_alt'       => $item->name_alt,
                'function_text' => $item->function_text ?? '',
                'image'         => '',
            ];

            if ($item->photo_path && Storage::disk('public')->exists($item->photo_path)) {
                $ext      = pathinfo($item->photo_path, PATHINFO_EXTENSION);
                $filename = Str::slug($item->name_fr) . '-' . $item->id . '.' . $ext;
                $imgPath  = 'images/' . $filename;

                $zip->addFromString($imgPath, Storage::disk('public')->get($item->photo_path));
                $entry['image'] = $imgPath;
            }

            $manifest['items'][] = $entry;
        }

        $zip->addFromString('manifest.yml', Yaml::dump($manifest, 4, 2));
        $zip->close();

        $zipName = Str::slug($module->title) . '-mnemo.zip';

        return response()->download($tmpZip, $zipName)->deleteFileAfterSend(true);
    }

    /**
     * Affiche le formulaire d'import de module ZIP.
     */
    public function showImportForm()
    {
        return view('modules.import');
    }

    /**
     * Traite l'import d'un module depuis un ZIP.
     */
    public function import(Request $request)
    {
        $request->validate([
            'zip_file' => 'required|file|mimes:zip|max:51200',
        ]);

        $file   = $request->file('zip_file');
        $tmpDir = sys_get_temp_dir() . '/mnemo_import_' . Str::uuid();
        mkdir($tmpDir);

        $zip = new ZipArchive();
        if ($zip->open($file->getRealPath()) !== true) {
            rmdir($tmpDir);
            return back()->with('error', 'Impossible de lire le fichier ZIP.');
        }

        $zip->extractTo($tmpDir);
        $zip->close();

        $manifestPath = $tmpDir . '/manifest.yml';
        if (!file_exists($manifestPath)) {
            $this->cleanDir($tmpDir);
            return back()->with('error', 'Le fichier ZIP ne contient pas de manifest.yml.');
        }

        try {
            $data = Yaml::parseFile($manifestPath);
        } catch (\Exception $e) {
            $this->cleanDir($tmpDir);
            return back()->with('error', 'Erreur de lecture du manifest.yml : ' . $e->getMessage());
        }

        if (empty($data['title'])) {
            $this->cleanDir($tmpDir);
            return back()->with('error', 'Le manifest.yml ne contient pas de titre.');
        }

        // Créer le module
        $module = Module::create([
            'owner_id'    => Auth::id(),
            'title'       => $data['title'],
            'description' => $data['description'] ?? '',
            'is_public'   => false,
        ]);

        $count = 0;
        foreach ($data['items'] ?? [] as $row) {
            $nameFr   = trim($row['name_fr']   ?? '');
            $nameEn   = trim($row['name_alt']   ?? '');

            if (empty($nameFr) || empty($nameEn)) {
                continue;
            }

            $photoPath = null;

            if (!empty($row['image'])) {
                $localImg = $tmpDir . '/' . $row['image'];
                if (file_exists($localImg)) {
                    $ext       = pathinfo($localImg, PATHINFO_EXTENSION) ?: 'jpg';
                    $destName  = 'items/' . Str::uuid() . '.' . $ext;
                    Storage::disk('public')->put($destName, file_get_contents($localImg));
                    $photoPath = $destName;
                }
            }

            $module->items()->create([
                'name_fr'       => mb_substr($nameFr, 0, 255),
                'name_alt'       => mb_substr($nameEn, 0, 255),
                'function_text' => mb_substr(trim($row['function_text'] ?? ''), 0, 2000),
                'photo_path'    => $photoPath,
            ]);

            $count++;
        }

        $this->cleanDir($tmpDir);

        return redirect()->route('modules.show', $module)
            ->with('success', "Module importé avec {$count} item(s).");
    }

    private function authorizeOwner(Module $module): void
    {
        if ($module->owner_id !== Auth::id() && !Auth::user()?->is_admin) {
            abort(403, 'Action non autorisée.');
        }
    }

    private function cleanDir(string $dir): void
    {
        if (!is_dir($dir)) return;
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $f) {
            $f->isDir() ? rmdir($f->getRealPath()) : unlink($f->getRealPath());
        }
        rmdir($dir);
    }
}
