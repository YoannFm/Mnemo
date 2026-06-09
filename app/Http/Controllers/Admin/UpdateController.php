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
            ActivityLogger::log('Sauvegarde fichiers téléchargée', 'update', null, [
                'fichier'      => basename($path),
                'download_url' => route('admin.update.backup-files'),
            ]);
            return response()->download($path)->deleteFileAfterSend(false);
        } catch (Throwable $t) {
            ActivityLogger::log('Échec sauvegarde fichiers : ' . $t->getMessage(), 'update');
            return back()->with('error', $t->getMessage());
        }
    }

    public function backupDatabase()
    {
        try {
            $path = $this->updates->backupDatabase();
            ActivityLogger::log('Export base de données téléchargé', 'update', null, [
                'fichier'      => basename($path),
                'download_url' => route('admin.update.backup-database'),
            ]);
            return response()->download($path)->deleteFileAfterSend(false);
        } catch (Throwable $t) {
            ActivityLogger::log('Échec export base de données : ' . $t->getMessage(), 'update');
            return back()->with('error', $t->getMessage());
        }
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
