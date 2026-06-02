<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes publiques
|--------------------------------------------------------------------------
*/

// Page d'accueil — redirige vers le dashboard si connecté, sinon vers login
Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

/*
|--------------------------------------------------------------------------
| Routes protégées par authentification
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // Dashboard — tableau de bord de l'utilisateur
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
});

require __DIR__.'/auth.php';
