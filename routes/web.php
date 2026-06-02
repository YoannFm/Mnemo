<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\AnkiController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\ProgressController;
use Illuminate\Support\Facades\Route;

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

    // ─── Bibliothèque publique ───
    Route::get('/bibliotheque', [LibraryController::class, 'index'])->name('library.index');

    // ─── Progression et historique ───
    Route::get('/progression', [ProgressController::class, 'index'])->name('progress.index');
});

require __DIR__.'/auth.php';
