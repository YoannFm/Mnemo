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

    // Signaler un module public
    Route::post('/modules/{module}/report', [ModuleController::class, 'report'])->name('modules.report');

    // ─── Bibliothèque publique ───
    Route::get('/bibliotheque', [LibraryController::class, 'index'])->name('library.index');

    // ─── Progression et historique ───
    Route::get('/progression', [ProgressController::class, 'index'])->name('progress.index');

    // ─── Notifications utilisateur ───
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.read-all');
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
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{user}/force-password-change', [AdminUserController::class, 'forcePasswordChange'])->name('users.force-password-change');
    // Bans (nested under user)
    Route::post('/users/{user}/bans', [\App\Http\Controllers\Admin\BanController::class, 'store'])->name('users.bans.store');
    Route::delete('/users/{user}/bans/{ban}', [\App\Http\Controllers\Admin\BanController::class, 'destroy'])->name('users.bans.destroy');
    Route::get('/bans', [\App\Http\Controllers\Admin\BanController::class, 'index'])->name('bans.index');
    // Roles
    Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);
    Route::post('/roles/order', [\App\Http\Controllers\Admin\RoleController::class, 'updateOrder'])->name('roles.order');
    // Modules
    Route::get('/modules', [AdminModuleController::class, 'index'])->name('modules.index');
    Route::delete('/modules/{module}', [AdminModuleController::class, 'destroy'])->name('modules.destroy');
    // Navbar
    Route::get('/navbar', [NavbarController::class, 'index'])->name('navbar.index');
    Route::get('/navbar/create', [NavbarController::class, 'create'])->name('navbar.create');
    Route::post('/navbar', [NavbarController::class, 'store'])->name('navbar.store');
    Route::get('/navbar/{navItem}/edit', [NavbarController::class, 'edit'])->name('navbar.edit');
    Route::put('/navbar/{navItem}', [NavbarController::class, 'update'])->name('navbar.update');
    Route::delete('/navbar/{navItem}', [NavbarController::class, 'destroy'])->name('navbar.destroy');
    Route::post('/navbar/order', [NavbarController::class, 'updateOrder'])->name('navbar.order');
    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/settings/home', [SettingsController::class, 'home'])->name('settings.home');
    Route::post('/settings/home', [SettingsController::class, 'updateHome'])->name('settings.home.update');
    Route::get('/settings/auth', [SettingsController::class, 'auth'])->name('settings.auth');
    Route::post('/settings/auth', [SettingsController::class, 'updateAuth'])->name('settings.auth.update');
    Route::get('/settings/mail', [SettingsController::class, 'mail'])->name('settings.mail');
    Route::post('/settings/mail', [SettingsController::class, 'updateMail'])->name('settings.mail.update');
    Route::post('/settings/mail/send', [SettingsController::class, 'sendTestMail'])->name('settings.mail.send');
    Route::get('/settings/maintenance', [SettingsController::class, 'maintenance'])->name('settings.maintenance');
    Route::post('/settings/maintenance', [SettingsController::class, 'updateMaintenance'])->name('settings.maintenance.update');
    // Pages
    Route::resource('pages', \App\Http\Controllers\Admin\PageController::class);
    // Posts
    Route::resource('posts', \App\Http\Controllers\Admin\PostController::class);
    // Images
    Route::resource('images', \App\Http\Controllers\Admin\ImageController::class)->except(['show']);
    // Redirects
    Route::resource('redirects', \App\Http\Controllers\Admin\RedirectController::class);
    // Logs
    Route::get('/logs', [\App\Http\Controllers\Admin\LogController::class, 'index'])->name('logs.index');
    Route::get('/logs/{log}', [\App\Http\Controllers\Admin\LogController::class, 'show'])->name('logs.show');
    Route::post('/logs/clear', [\App\Http\Controllers\Admin\LogController::class, 'clear'])->name('logs.clear');
    Route::delete('/logs/purge', [\App\Http\Controllers\Admin\LogController::class, 'purge'])->name('logs.purge');
    // Plugins placeholder
    Route::get('/plugins', fn() => view('admin.plugins.index'))->name('plugins.index');
    // Themes
    Route::get('/themes', [\App\Http\Controllers\Admin\ThemeController::class, 'index'])->name('themes.index');
    Route::get('/themes/create', [\App\Http\Controllers\Admin\ThemeController::class, 'create'])->name('themes.create');
    Route::post('/themes', [\App\Http\Controllers\Admin\ThemeController::class, 'store'])->name('themes.store');
    Route::get('/themes/{theme}/edit', [\App\Http\Controllers\Admin\ThemeController::class, 'edit'])->name('themes.edit');
    Route::put('/themes/{theme}', [\App\Http\Controllers\Admin\ThemeController::class, 'update'])->name('themes.update');
    Route::delete('/themes/{theme}', [\App\Http\Controllers\Admin\ThemeController::class, 'destroy'])->name('themes.destroy');
    Route::post('/themes/{theme}/activate', [\App\Http\Controllers\Admin\ThemeController::class, 'activate'])->name('themes.activate');
    // Private modules
    Route::get('/private-modules', [\App\Http\Controllers\Admin\PrivateModuleController::class, 'index'])->name('private-modules.index');
    Route::delete('/private-modules/{module}', [\App\Http\Controllers\Admin\PrivateModuleController::class, 'destroy'])->name('private-modules.destroy');
    // Update placeholder
    Route::get('/update', fn() => view('admin.update.index'))->name('update.index');
    // Reports (commentaires)
    Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/{report}/sanctioned', [\App\Http\Controllers\Admin\ReportController::class, 'markSanctioned'])->name('reports.sanctioned');
    Route::post('/reports/{report}/unsanctioned', [\App\Http\Controllers\Admin\ReportController::class, 'markUnsanctioned'])->name('reports.unsanctioned');
    Route::post('/reports/{report}/delete-comment', [\App\Http\Controllers\Admin\ReportController::class, 'deleteComment'])->name('reports.delete-comment');
    Route::post('/reports/{report}/mute-user', [\App\Http\Controllers\Admin\ReportController::class, 'muteUser'])->name('reports.mute-user');
    // Reports (modules)
    Route::get('/module-reports', [\App\Http\Controllers\Admin\ModuleReportController::class, 'index'])->name('module-reports.index');
    Route::post('/module-reports/{report}/treated', [\App\Http\Controllers\Admin\ModuleReportController::class, 'markTreated'])->name('module-reports.treated');
    Route::post('/module-reports/{report}/rejected', [\App\Http\Controllers\Admin\ModuleReportController::class, 'markRejected'])->name('module-reports.rejected');
    // Historique des commentaires
    Route::get('/comment-history', [\App\Http\Controllers\Admin\CommentHistoryController::class, 'index'])->name('comment-history.index');
    // Sanctions
    Route::get('/sanctions', [\App\Http\Controllers\Admin\SanctionController::class, 'index'])->name('sanctions.index');
    Route::post('/sanctions/{sanction}/unmute', [\App\Http\Controllers\Admin\SanctionController::class, 'unmute'])->name('sanctions.unmute');
    // Notifications admin
    Route::get('/notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/create', [\App\Http\Controllers\Admin\NotificationController::class, 'create'])->name('notifications.create');
    Route::post('/notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'store'])->name('notifications.store');
    Route::delete('/notifications/{notification}', [\App\Http\Controllers\Admin\NotificationController::class, 'destroy'])->name('notifications.destroy');
});

// Pages statiques publiques
Route::get('/p/{slug}', [\App\Http\Controllers\PageController::class, 'show'])->name('pages.show');

// Articles publics
Route::get('/news/{post:slug}', [\App\Http\Controllers\PostController::class, 'show'])->name('posts.show');
Route::middleware('auth')->group(function () {
    Route::post('/news/{post:slug}/react', [\App\Http\Controllers\PostController::class, 'react'])->name('posts.react');
    Route::post('/news/{post:slug}/comment', [\App\Http\Controllers\PostController::class, 'comment'])->name('posts.comment');
    Route::post('/news/comments/{comment}/report', [\App\Http\Controllers\PostController::class, 'reportComment'])->name('posts.comment.report');
    Route::patch('/news/comments/{comment}', [\App\Http\Controllers\PostController::class, 'updateComment'])->name('posts.comment.update');
    Route::delete('/news/comments/{comment}', [\App\Http\Controllers\PostController::class, 'deleteComment'])->name('posts.comment.delete');
    Route::post('/news/{post:slug}/reply', [\App\Http\Controllers\PostController::class, 'reply'])->name('posts.reply');
});

require __DIR__.'/auth.php';
