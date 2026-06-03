<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InstallController extends Controller
{
    public function index()
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        return view('install.welcome');
    }

    public function checkPrerequisites()
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        $checks = [
            'php_version' => [
                'name' => 'PHP 8.3+',
                'passed' => version_compare(PHP_VERSION, '8.3.0', '>='),
                'version' => PHP_VERSION,
            ],
            'gd_extension' => [
                'name' => 'Extension GD',
                'passed' => extension_loaded('gd'),
            ],
            'fileinfo_extension' => [
                'name' => 'Extension Fileinfo',
                'passed' => extension_loaded('fileinfo'),
            ],
            'pdo_extension' => [
                'name' => 'Extension PDO',
                'passed' => extension_loaded('pdo'),
            ],
            'storage_writable' => [
                'name' => 'Dossier storage/ accessible',
                'passed' => is_writable(storage_path()),
            ],
            'bootstrap_writable' => [
                'name' => 'Dossier bootstrap/ accessible',
                'passed' => is_writable(base_path('bootstrap')),
            ],
        ];

        $allPassed = collect($checks)->every(fn($check) => $check['passed']);

        return view('install.prerequisites', compact('checks', 'allPassed'));
    }

    public function setupDatabase()
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        try {
            \Artisan::call('migrate', ['--force' => true]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de l\'initialisation de la base de données: ' . $e->getMessage()]);
        }

        return view('install.database');
    }

    public function createAdmin()
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        return view('install.admin');
    }

    public function storeAdmin(Request $request)
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'email_verified_at' => now(),
            ]);

            return view('install.success', compact('user'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de la création du compte: ' . $e->getMessage()]);
        }
    }

    public function complete()
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        try {
            DB::table('installation')->truncate();
            DB::table('installation')->insert(['completed' => true]);

            // Marquer l'installation comme terminée dans .env
            $envPath = base_path('.env');
            $env = file_get_contents($envPath);
            if (preg_match('/^APP_INSTALLED=/m', $env)) {
                $env = preg_replace('/^APP_INSTALLED=.*/m', 'APP_INSTALLED=true', $env);
            } else {
                $env .= "\nAPP_INSTALLED=true\n";
            }
            file_put_contents($envPath, $env);

            session(['installation_completed' => true]);

            return redirect('/login')->with('success', 'Installation réussie ! Bienvenue sur Mnemo.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    private function isInstalled(): bool
    {
        try {
            if (!DB::connection()->getDatabaseName()) {
                return false;
            }

            return DB::table('installation')
                ->where('completed', true)
                ->exists();
        } catch (\Exception) {
            return false;
        }
    }
}
