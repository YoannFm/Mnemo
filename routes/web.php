<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfileTwoFactorController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\AnkiController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ModuleExportController;
use App\Http\Controllers\SharedExamController;
use App\Http\Controllers\SharedExamIndexController;
use App\Http\Controllers\MyExamResultsController;
use App\Http\Controllers\GuestExamController;
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

// ─── Examens partagés (authentification requise) ───
Route::middleware(['auth', 'two-factor'])->group(function () {
    Route::get('/e/{uuid}', [GuestExamController::class, 'show'])->name('guest.exam.show');
    Route::post('/e/{uuid}/start', [GuestExamController::class, 'start'])->name('guest.exam.start');
    Route::get('/e/{uuid}/question', [GuestExamController::class, 'question'])->name('guest.exam.question');
    Route::post('/e/{uuid}/answer', [GuestExamController::class, 'answer'])->name('guest.exam.answer');
    Route::get('/e/{uuid}/finish', [GuestExamController::class, 'finish'])->name('guest.exam.finish');
});

// ─── 2FA Challenge (après login, authentification requise) ───
Route::middleware('auth')->group(function () {
    Route::get('/two-factor-challenge', [TwoFactorController::class, 'show'])->name('two-factor.show');
    Route::post('/two-factor-challenge', [TwoFactorController::class, 'store'])->name('two-factor.store');
});

Route::middleware(['auth', 'two-factor'])->group(function () {

    // ─── Gestion 2FA (profil) ───
    Route::get('/profile/2fa', [ProfileTwoFactorController::class, 'show'])->name('profile.2fa.show');
    Route::match(['GET', 'POST'], '/profile/2fa/enable', [ProfileTwoFactorController::class, 'enable'])->name('profile.2fa.enable');
    Route::post('/profile/2fa/confirm', [ProfileTwoFactorController::class, 'confirm'])->name('profile.2fa.confirm');
    Route::delete('/profile/2fa', [ProfileTwoFactorController::class, 'disable'])->name('profile.2fa.disable');

    // Dashboard - tableau de bord de l'utilisateur
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // ─── Profil utilisateur ───
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/accent', [ProfileController::class, 'updateAccent'])->name('profile.accent');
    Route::get('/profile/accent/reset', [ProfileController::class, 'resetAccent'])->name('profile.accent.reset');
    Route::patch('/profile/email-notifications', [ProfileController::class, 'updateEmailNotifications'])->name('profile.email-notifications');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Prévisualisation rapide d'un module (JSON pour le modal bibliothèque)
    Route::get('/modules/{module}/preview', [ModuleController::class, 'preview'])->name('modules.preview');

    // Import de module ZIP (doit être avant le resource pour éviter le conflit avec {module})
    Route::get('/modules/import', [ModuleExportController::class, 'showImportForm'])->name('modules.import.form');
    Route::post('/modules/import', [ModuleExportController::class, 'import'])->name('modules.import');

    // ─── Corbeille & restauration des modules (avant resource pour éviter conflit) ───
    Route::get('/modules/trash', [ModuleController::class, 'trash'])->name('modules.trash');
    Route::post('/modules/{id}/restore', [ModuleController::class, 'restore'])->name('modules.restore');
    Route::delete('/modules/{id}/force-delete', [ModuleController::class, 'forceDelete'])->name('modules.force-delete');

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
    Route::post('/modules/{module}/anki/review', [AnkiController::class, 'reviewStart'])->name('anki.review');

    // ─── Mode Examen (tous les items, score final) ───
    Route::get('/modules/{module}/exam', [ExamController::class, 'show'])->name('exam.show');
    Route::post('/modules/{module}/exam/start', [ExamController::class, 'start'])->name('exam.start');
    Route::get('/modules/{module}/exam/question', [ExamController::class, 'question'])->name('exam.question');
    Route::post('/modules/{module}/exam/submit', [ExamController::class, 'submit'])->name('exam.submit');
    Route::get('/modules/{module}/exam/result', [ExamController::class, 'result'])->name('exam.result');

    // Export de module ZIP
    Route::get('/modules/{module}/export', [ModuleExportController::class, 'export'])->name('modules.export');

    // Réinitialiser sa progression sur un module
    Route::delete('/modules/{module}/progress/reset', [ModuleController::class, 'resetProgress'])->name('modules.progress.reset');

    // Dupliquer un module public dans son espace personnel
    Route::post('/modules/{module}/duplicate', [ModuleController::class, 'duplicate'])->name('modules.duplicate');

    // Signaler un module public
    Route::post('/modules/{module}/report', [ModuleController::class, 'report'])->name('modules.report');

    // Noter un module (1 vote par compte)
    Route::post('/modules/{module}/rate', [ModuleController::class, 'rate'])->name('modules.rate');

    // Signaler un avis sur un module
    Route::post('/module-ratings/{rating}/report', [ModuleController::class, 'reportRating'])->name('modules.ratings.report');

    // Réactions et réponses sur un avis
    Route::post('/module-ratings/{rating}/react', [ModuleController::class, 'reactToRating'])->name('modules.ratings.react');
    Route::delete('/module-ratings/{rating}', [ModuleController::class, 'deleteRating'])->name('modules.ratings.destroy');
    Route::post('/module-ratings/{rating}/replies', [ModuleController::class, 'replyToRating'])->name('modules.ratings.replies.store');
    Route::delete('/module-rating-replies/{reply}', [ModuleController::class, 'deleteRatingReply'])->name('modules.ratings.replies.destroy');
    Route::post('/module-rating-replies/{reply}/report', [ModuleController::class, 'reportRatingReply'])->name('modules.ratings.replies.report');

    // ─── Bibliothèque publique ───
    Route::get('/bibliotheque', [LibraryController::class, 'index'])->name('library.index');

    // ─── Progression et historique ───
    Route::get('/progression', [ProgressController::class, 'index'])->name('progress.index');

    // ─── Notifications utilisateur ───
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    // ─── Examens partagés (côté créateur) ───
    Route::get('/mes-examens', [SharedExamIndexController::class, 'index'])->name('shared-exam.index');
    Route::get('/mes-resultats', [MyExamResultsController::class, 'index'])->name('my-exam-results');
    Route::post('/modules/{module}/share', [SharedExamController::class, 'create'])->name('shared-exam.create');
    Route::get('/shared-exam/{sharedExam}/results', [SharedExamController::class, 'results'])->name('shared-exam.results');
    Route::delete('/shared-exam/{sharedExam}', [SharedExamController::class, 'destroy'])->name('shared-exam.destroy');
    Route::post('/shared-exam/{sharedExam}/add-attempt', [SharedExamController::class, 'addAttempt'])->name('shared-exam.add-attempt');
    Route::delete('/shared-exam/{sharedExam}/attempt/{attempt}', [SharedExamController::class, 'resetAttempt'])->name('shared-exam.reset-attempt');
    Route::post('/shared-exam/{sharedExam}/attempt/{attempt}/send-results', [SharedExamController::class, 'sendResults'])->name('shared-exam.send-results');
    Route::post('/shared-exam/{sharedExam}/send-all-results', [SharedExamController::class, 'sendAllResults'])->name('shared-exam.send-all-results');
    Route::get('/shared-exam/{sharedExam}/export-grades', [SharedExamController::class, 'exportGrades'])->name('shared-exam.export-grades');
    Route::get('/shared-exam/{sharedExam}/export', [SharedExamController::class, 'export'])->name('shared-exam.export');
    Route::get('/shared-exam/{sharedExam}/export-excel', [SharedExamController::class, 'exportExcel'])->name('shared-exam.export-excel');
    Route::get('/shared-exam/{sharedExam}/export-pdf', [SharedExamController::class, 'exportPdf'])->name('shared-exam.export-pdf');

    // ─── Groupes / classes ───
    Route::get('/groupes', [\App\Http\Controllers\GroupController::class, 'index'])->name('groups.index');
    Route::post('/groupes', [\App\Http\Controllers\GroupController::class, 'store'])->name('groups.store');
    Route::get('/groupes/{group}', [\App\Http\Controllers\GroupController::class, 'show'])->name('groups.show');
    Route::delete('/groupes/{group}', [\App\Http\Controllers\GroupController::class, 'destroy'])->name('groups.destroy');
    Route::post('/groupes/{group}/membres', [\App\Http\Controllers\GroupController::class, 'addMember'])->name('groups.members.add');
    Route::delete('/groupes/{group}/membres/{user}', [\App\Http\Controllers\GroupController::class, 'removeMember'])->name('groups.members.remove');
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

Route::middleware('auth')->get('/emojis.json', function () {
    return response()->json(\App\Models\Emoji::orderBy('name')->get(['id','name','slug','type','image_path'])->map(fn($e) => [
        'id'    => $e->id,
        'name'  => $e->name,
        'slug'  => $e->slug,
        'type'  => $e->type,
        'url'   => $e->imageUrl(),
    ]));
})->name('emojis.json');

/*
|--------------------------------------------------------------------------
| Routes Admin
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'two-factor', 'admin'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::get('/users/import', [AdminUserController::class, 'importForm'])->name('users.import');
    Route::get('/users/export-all', [AdminUserController::class, 'exportAll'])->name('users.export-all');
    Route::post('/users/import', [AdminUserController::class, 'importStore'])->name('users.import.store');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{user}/force-password-change', [AdminUserController::class, 'forcePasswordChange'])->name('users.force-password-change');
    Route::get('/users/{user}/export', [AdminUserController::class, 'exportData'])->name('users.export');
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
    Route::get('/modules/trash', [AdminModuleController::class, 'trash'])->name('modules.trash');
    Route::post('/modules/{id}/restore', [AdminModuleController::class, 'restore'])->name('modules.restore');
    Route::delete('/modules/{id}/force-delete', [AdminModuleController::class, 'forceDelete'])->name('modules.force-delete');

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
    Route::get('/settings/features', [SettingsController::class, 'features'])->name('settings.features');
    Route::post('/settings/features', [SettingsController::class, 'updateFeatures'])->name('settings.features.update');
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
    // Emojis
    Route::get('/emojis', [\App\Http\Controllers\Admin\EmojiController::class, 'index'])->name('emojis.index');
    Route::post('/emojis', [\App\Http\Controllers\Admin\EmojiController::class, 'store'])->name('emojis.store');
    Route::post('/emojis/import-pack', [\App\Http\Controllers\Admin\EmojiController::class, 'importPack'])->name('emojis.import-pack');
    Route::delete('/emojis/{emoji}', [\App\Http\Controllers\Admin\EmojiController::class, 'destroy'])->name('emojis.destroy');
    // Plugins
    Route::get('/plugins', [\App\Http\Controllers\Admin\PluginController::class, 'index'])->name('plugins.index');
    Route::post('/plugins/reload', [\App\Http\Controllers\Admin\PluginController::class, 'reload'])->name('plugins.reload');
    Route::post('/plugins/{plugin}/enable', [\App\Http\Controllers\Admin\PluginController::class, 'enable'])->name('plugins.enable');
    Route::post('/plugins/{plugin}/disable', [\App\Http\Controllers\Admin\PluginController::class, 'disable'])->name('plugins.disable');
    Route::post('/plugins/{slug}/install', [\App\Http\Controllers\Admin\PluginController::class, 'install'])->name('plugins.install');
    Route::post('/plugins/{plugin}/update', [\App\Http\Controllers\Admin\PluginController::class, 'update'])->name('plugins.update');
    Route::delete('/plugins/{plugin}', [\App\Http\Controllers\Admin\PluginController::class, 'delete'])->name('plugins.delete');
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
    // Updates
    Route::get('/update', [\App\Http\Controllers\Admin\UpdateController::class, 'index'])->name('update.index');
    Route::post('/update/fetch', [\App\Http\Controllers\Admin\UpdateController::class, 'fetch'])->name('update.fetch');
    Route::post('/update/download', [\App\Http\Controllers\Admin\UpdateController::class, 'download'])->name('update.download');
    Route::get('/update/backup-files', [\App\Http\Controllers\Admin\UpdateController::class, 'backupFiles'])->name('update.backup-files');
    Route::get('/update/backup-database', [\App\Http\Controllers\Admin\UpdateController::class, 'backupDatabase'])->name('update.backup-database');
    Route::get('/update/backup-download/{filename}', [\App\Http\Controllers\Admin\UpdateController::class, 'backupDownload'])->name('update.backup-download')->where('filename', '[a-zA-Z0-9._-]+');
    Route::post('/update/install', [\App\Http\Controllers\Admin\UpdateController::class, 'install'])->name('update.install');
    // Themes duplicate
    Route::post('/themes/{theme}/duplicate', [\App\Http\Controllers\Admin\ThemeController::class, 'duplicate'])->name('themes.duplicate');
    // License check
    Route::post('/license/check', [\App\Http\Controllers\Admin\SettingsController::class, 'checkLicense'])->name('license.check');
    // Reports (commentaires)
    Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/{report}/sanctioned', [\App\Http\Controllers\Admin\ReportController::class, 'markSanctioned'])->name('reports.sanctioned');
    Route::post('/reports/{report}/unsanctioned', [\App\Http\Controllers\Admin\ReportController::class, 'markUnsanctioned'])->name('reports.unsanctioned');
    Route::post('/reports/{report}/delete-comment', [\App\Http\Controllers\Admin\ReportController::class, 'deleteComment'])->name('reports.delete-comment');
    Route::post('/reports/{report}/mute-user', [\App\Http\Controllers\Admin\ReportController::class, 'muteUser'])->name('reports.mute-user');
    Route::post('/reports/modules/{report}/treated', [\App\Http\Controllers\Admin\ReportController::class, 'markModuleTreated'])->name('reports.modules.treated');
    Route::post('/reports/modules/{report}/rejected', [\App\Http\Controllers\Admin\ReportController::class, 'markModuleRejected'])->name('reports.modules.rejected');
    // Historique des commentaires
    Route::get('/comment-history', [\App\Http\Controllers\Admin\CommentHistoryController::class, 'index'])->name('comment-history.index');
    // Signalements d'avis
    Route::post('/reports/ratings/{report}/treated', [\App\Http\Controllers\Admin\ReportController::class, 'markRatingTreated'])->name('reports.ratings.treated');
    Route::post('/reports/ratings/{report}/rejected', [\App\Http\Controllers\Admin\ReportController::class, 'markRatingRejected'])->name('reports.ratings.rejected');
    Route::post('/reports/ratings/{report}/delete-rating', [\App\Http\Controllers\Admin\ReportController::class, 'deleteRating'])->name('reports.ratings.delete');
    Route::post('/reports/ratings/{report}/mute-user', [\App\Http\Controllers\Admin\ReportController::class, 'muteRatingUser'])->name('reports.ratings.mute-user');
    // Signalements de réponses
    Route::post('/reports/replies/{report}/treated', [\App\Http\Controllers\Admin\ReportController::class, 'markReplyTreated'])->name('reports.replies.treated');
    Route::post('/reports/replies/{report}/rejected', [\App\Http\Controllers\Admin\ReportController::class, 'markReplyRejected'])->name('reports.replies.rejected');
    Route::post('/reports/replies/{report}/delete-reply', [\App\Http\Controllers\Admin\ReportController::class, 'deleteReply'])->name('reports.replies.delete');
    Route::post('/reports/replies/{report}/mute-user', [\App\Http\Controllers\Admin\ReportController::class, 'muteReplyUser'])->name('reports.replies.mute-user');
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
