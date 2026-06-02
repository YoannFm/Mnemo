<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

/**
 * Contrôleur de gestion du profil utilisateur.
 * Permet la consultation, la modification et la suppression du compte.
 */
class ProfileController extends Controller
{
    /**
     * Affiche le formulaire d'édition du profil utilisateur.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Enregistre les modifications du profil utilisateur.
     * Invalide la vérification d'email si celui-ci a été changé.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // Mettre à jour les données validées du profil
        $request->user()->fill($request->validated());

        // Si l'email a été modifié, marquer l'email comme non vérifié
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        // Sauvegarder les changements
        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Supprime complètement le compte utilisateur.
     * Valide le mot de passe avant suppression, déconnecte l'utilisateur et invalidate la session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Valider que l'utilisateur a entré le bon mot de passe
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Déconnecter l'utilisateur avant suppression
        Auth::logout();

        // Supprimer le compte (cascade suppression des modules et items)
        $user->delete();

        // Invalider la session après suppression du compte
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
