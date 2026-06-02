<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\AnkiController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\InstallController;
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

Route::middleware(['auth'])->group(function () {

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

require __DIR__.'/auth.php';
