<?php

namespace App\Http\Controllers\Admin;

use App\Extensions\UpdateManager;
use App\Helpers\ActivityLogger;
use App\Http\Controllers\Controller;
use App\Mnemo;
use Throwable;

class UpdateController extends Controller
{
    public function __construct(private UpdateManager $updates) {}

    public function index()
    {
        return view('admin.update.index', [
            'currentVersion' => Mnemo::version(),
            'latest'         => $this->updates->getLatestVersion(),
            'hasUpdate'      => $this->updates->hasUpdate(),
            'isDownloaded'   => $this->updates->isDownloaded(),
        ]);
    }

    public function fetch()
    {
        $this->updates->fetch(force: true);
        ActivityLogger::log('Vérification des mises à jour', 'update');
        return back()->with('success', 'Informations de mise à jour actualisées.');
    }

    public function download()
    {
        try {
            $this->updates->download();
            ActivityLogger::log('Téléchargement mise à jour v' . $this->updates->getLatestVersion(), 'update');
            return back()->with('success', 'Mise à jour téléchargée. Vous pouvez maintenant l\'installer.');
        } catch (Throwable $t) {
            ActivityLogger::log('Échec téléchargement mise à jour : ' . $t->getMessage(), 'update');
            return back()->with('error', $t->getMessage());
        }
    }

    public function backupFiles()
    {
        try {
            $path = $this->updates->backupFiles();
            $filename = basename($path);
            ActivityLogger::log('Sauvegarde fichiers créée', 'update', null, [
                'fichier'      => $filename,
                'download_url' => route('admin.update.backup-download', $filename),
            ]);
            return response()->download($path);
        } catch (Throwable $t) {
            ActivityLogger::log('Échec sauvegarde fichiers : ' . $t->getMessage(), 'update');
            return back()->with('error', $t->getMessage());
        }
    }

    public function backupDatabase()
    {
        try {
            $path = $this->updates->backupDatabase();
            $filename = basename($path);
            ActivityLogger::log('Export base de données créé', 'update', null, [
                'fichier'      => $filename,
                'download_url' => route('admin.update.backup-download', $filename),
            ]);
            return response()->download($path);
        } catch (Throwable $t) {
            ActivityLogger::log('Échec export base de données : ' . $t->getMessage(), 'update');
            return back()->with('error', $t->getMessage());
        }
    }

    public function backupDownload(string $filename)
    {
        $backupDir = realpath(storage_path('app/backups'));
        $path      = realpath($backupDir . DIRECTORY_SEPARATOR . basename($filename));

        if (!$path || !str_starts_with($path, $backupDir . DIRECTORY_SEPARATOR) || !file_exists($path)) {
            abort(404);
        }

        return response()->download($path);
    }

    public function install()
    {
        try {
            $version = $this->updates->getLatestVersion();
            $this->updates->install();
            ActivityLogger::log('Mise à jour installée : v' . Mnemo::version() . ' -> v' . $version, 'update');
            return back()->with('success', 'Mise à jour installée avec succès.');
        } catch (Throwable $t) {
            ActivityLogger::log('Échec installation mise à jour : ' . $t->getMessage(), 'update');
            return back()->with('error', $t->getMessage());
        }
    }
}
