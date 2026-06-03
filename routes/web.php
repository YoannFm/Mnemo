<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfileTwoFactorController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\AnkiController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ModuleController as AdminModuleController;
use App\Http\Controllers\Admin\NavbarController;
use App\Http\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes d'installation (sans authentification)
|--------------------------------------------------------------------------
*/

Route::prefix('install')->group(function () {
    Route::get('/', [InstallController::class, 'index'])->name('install.index');
    Route::get('/prerequisites', [InstallController::class, 'checkPrerequisites'])->name('install.prerequisites');
    Route::get('/database', [InstallController::class, 'setupDatabase'])->name('install.database');
    Route::get('/admin', [InstallController::class, 'createAdmin'])->name('install.admin');
    Route::post('/admin', [InstallController::class, 'storeAdmin'])->name('install.admin.store');
    Route::get('/complete', [InstallController::class, 'complete'])->name('install.complete');
});

/*
|--------------------------------------------------------------------------
| Routes publiques
|--------------------------------------------------------------------------
*/

// Page d'accueil - redirige vers le dashboard si connecté, sinon vers login
Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

/*
|--------------------------------------------------------------------------
| Routes protégées par authentification
|--------------------------------------------------------------------------
*/

// ─── 2FA Challenge (après login) ───
Route::middleware('guest')->group(function () {
    Route::get('/two-factor-challenge', [TwoFactorController::class, 'show'])->name('two-factor.show');
    Route::post('/two-factor-challenge', [TwoFactorController::class, 'store'])->name('two-factor.store');
});

Route::middleware(['auth', 'two-factor'])->group(function () {

    // ─── Gestion 2FA (profil) ───
    Route::get('/profile/2fa', [ProfileTwoFactorController::class, 'show'])->name('profile.2fa.show');
    Route::get('/profile/2fa/enable', [ProfileTwoFactorController::class, 'enable'])->name('profile.2fa.enable');
    Route::post('/profile/2fa/confirm', [ProfileTwoFactorController::class, 'confirm'])->name('profile.2fa.confirm');
    Route::delete('/profile/2fa', [ProfileTwoFactorController::class, 'disable'])->name('profile.2fa.disable');

    // Dashboard - tableau de bord de l'utilisateur
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // ─── Profil utilisateur ───
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ─── CRUD Modules ───
    // Génère : modules.index, modules.create, modules.store,
    //          modules.show, modules.edit, modules.update, modules.destroy
    Route::resource('modules', ModuleController::class);

    // ─── CRUD Items (imbriqués dans un module) ───
    // Génère : modules.items.create, modules.items.store,
    //          modules.items.edit, modules.items.update, modules.items.destroy
    Route::resource('modules.items', ItemController::class)
        ->except(['index', 'show']); // Ces actions redirigent vers la page du module

    // Import CSV d'items en masse
    Route::get('/modules/{module}/items/import', [ItemController::class, 'showImportForm'])->name('modules.items.import.form');
    Route::post('/modules/{module}/items/import', [ItemController::class, 'importCsv'])->name('modules.items.import');

    // ─── Mode Test (nombre fixe de questions) ───
    Route::get('/modules/{module}/test', [TestController::class, 'show'])->name('test.show');
    Route::post('/modules/{module}/test/start', [TestController::class, 'start'])->name('test.start');
    Route::get('/modules/{module}/test/question', [TestController::class, 'question'])->name('test.question');
    Route::post('/modules/{module}/test/submit', [TestController::class, 'submit'])->name('test.submit');
    Route::get('/modules/{module}/test/result', [TestController::class, 'result'])->name('test.result');

    // ─── Mode Anki (questions infinies avec progression) ───
    Route::get('/modules/{module}/anki', [AnkiController::class, 'show'])->name('anki.show');
    Route::post('/modules/{module}/anki/start', [AnkiController::class, 'start'])->name('anki.start');
    Route::get('/modules/{module}/anki/question', [AnkiController::class, 'question'])->name('anki.question');
    Route::post('/modules/{module}/anki/submit', [AnkiController::class, 'submit'])->name('anki.submit');
    Route::post('/modules/{module}/anki/quit', [AnkiController::class, 'quit'])->name('anki.quit');

    // Dupliquer un module public dans son espace personnel
    Route::post('/modules/{module}/duplicate', [ModuleController::class, 'duplicate'])->name('modules.duplicate');

    // ─── Bibliothèque publique ───
    Route::get('/bibliotheque', [LibraryController::class, 'index'])->name('library.index');

    // ─── Progression et historique ───
    Route::get('/progression', [ProgressController::class, 'index'])->name('progress.index');
});

// ─── Sitemap XML dynamique ───
// Accessible publiquement pour les moteurs de recherche
Route::get('/sitemap.xml', function () {
    $content = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $content .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    $content .= '  <url><loc>' . url('/') . '</loc><changefreq>weekly</changefreq><priority>1.0</priority></url>' . "\n";
    $content .= '  <url><loc>' . url('/bibliotheque') . '</loc><changefreq>daily</changefreq><priority>0.8</priority></url>' . "\n";
    $content .= '</urlset>';
    return response($content, 200)->header('Content-Type', 'application/xml');
})->name('sitemap');

/*
|--------------------------------------------------------------------------
| Routes Admin
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'two-factor', 'admin'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/toggle-admin', [AdminUserController::class, 'toggleAdmin'])->name('users.toggle-admin');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    // Bans (nested under user)
    Route::post('/users/{user}/bans', [\App\Http\Controllers\Admin\BanController::class, 'store'])->name('users.bans.store');
    Route::delete('/users/{user}/bans/{ban}', [\App\Http\Controllers\Admin\BanController::class, 'destroy'])->name('users.bans.destroy');
    Route::get('/bans', [\App\Http\Controllers\Admin\BanController::class, 'index'])->name('bans.index');
    // Roles
    Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);
    // Modules
    Route::get('/modules', [AdminModuleController::class, 'index'])->name('modules.index');
    Route::delete('/modules/{module}', [AdminModuleController::class, 'destroy'])->name('modules.destroy');
    // Navbar
    Route::get('/navbar', [NavbarController::class, 'index'])->name('navbar.index');
    Route::post('/navbar', [NavbarController::class, 'store'])->name('navbar.store');
    Route::put('/navbar/{navItem}', [NavbarController::class, 'update'])->name('navbar.update');
    Route::delete('/navbar/{navItem}', [NavbarController::class, 'destroy'])->name('navbar.destroy');
    Route::post('/navbar/order', [NavbarController::class, 'updateOrder'])->name('navbar.order');
    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/settings/mail', [SettingsController::class, 'mail'])->name('settings.mail');
    Route::post('/settings/mail', [SettingsController::class, 'updateMail'])->name('settings.mail.update');
    // Pages
    Route::resource('pages', \App\Http\Controllers\Admin\PageController::class);
    // Posts
    Route::resource('posts', \App\Http\Controllers\Admin\PostController::class);
    // Images
    Route::get('/images', [\App\Http\Controllers\Admin\ImageController::class, 'index'])->name('images.index');
    Route::get('/images/create', [\App\Http\Controllers\Admin\ImageController::class, 'create'])->name('images.create');
    Route::post('/images', [\App\Http\Controllers\Admin\ImageController::class, 'store'])->name('images.store');
    Route::delete('/images/{image}', [\App\Http\Controllers\Admin\ImageController::class, 'destroy'])->name('images.destroy');
    // Redirects
    Route::resource('redirects', \App\Http\Controllers\Admin\RedirectController::class);
    // Logs
    Route::get('/logs', [\App\Http\Controllers\Admin\LogController::class, 'index'])->name('logs.index');
    Route::get('/logs/{log}', [\App\Http\Controllers\Admin\LogController::class, 'show'])->name('logs.show');
    Route::post('/logs/clear', [\App\Http\Controllers\Admin\LogController::class, 'clear'])->name('logs.clear');
    // Plugins placeholder
    Route::get('/plugins', fn() => view('admin.plugins.index'))->name('plugins.index');
    // Themes placeholder
    Route::get('/themes', fn() => view('admin.themes.index'))->name('themes.index');
    // Update placeholder
    Route::get('/update', fn() => view('admin.update.index'))->name('update.index');
});

require __DIR__.'/auth.php';
