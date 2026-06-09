<?php

namespace App\Http\Controllers\Admin;

use App\Extensions\UpdateManager;
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
        return back()->with('success', 'Informations de mise à jour actualisées.');
    }

    public function download()
    {
        try {
            $this->updates->download();
            return back()->with('success', 'Mise à jour téléchargée. Vous pouvez maintenant l\'installer.');
        } catch (Throwable $t) {
            return back()->with('error', $t->getMessage());
        }
    }

    public function backupFiles()
    {
        try {
            $path = $this->updates->backupFiles();
            return response()->download($path)->deleteFileAfterSend(false);
        } catch (Throwable $t) {
            return back()->with('error', $t->getMessage());
        }
    }

    public function backupDatabase()
    {
        try {
            $path = $this->updates->backupDatabase();
            return response()->download($path)->deleteFileAfterSend(false);
        } catch (Throwable $t) {
            return back()->with('error', $t->getMessage());
        }
    }

    public function install()
    {
        try {
            $this->updates->install();
            return back()->with('success', 'Mise à jour installée avec succès.');
        } catch (Throwable $t) {
            return back()->with('error', $t->getMessage());
        }
    }
}
