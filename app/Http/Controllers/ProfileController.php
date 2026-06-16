<?php

namespace App\Http\Controllers;

use App\Helpers\LogHelper;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Progress;
use App\Models\Score;
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
        $user = $request->user();

        $stats = [
            'mastered'     => Progress::where('user_id', $user->id)->where('streak', '>=', 3)->count(),
            'practiced'    => Progress::where('user_id', $user->id)->count(),
            'tests'        => Score::where('user_id', $user->id)->count(),
            'avg_score'    => (int) round(Score::where('user_id', $user->id)->avg(\DB::raw('score / total * 100')) ?? 0),
            'best_streak'  => Progress::where('user_id', $user->id)->max('streak') ?? 0,
            'modules_used' => Progress::where('user_id', $user->id)->distinct('item_id')
                              ->join('items', 'progress.item_id', '=', 'items.id')
                              ->distinct('items.module_id')->count('items.module_id'),
        ];

        return view('profile.edit', compact('user', 'stats'));
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

        LogHelper::log('updated_profile', 'user', Auth::id());

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function updateAccent(Request $request): RedirectResponse
    {
        $request->validate(['accent_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/']]);
        $request->user()->update(['accent_color' => $request->accent_color]);
        return Redirect::route('profile.edit')->with('status', 'accent-updated');
    }

    public function resetAccent(Request $request): RedirectResponse
    {
        $request->user()->update(['accent_color' => null]);
        return Redirect::route('profile.edit')->with('status', 'accent-updated');
    }

    public function updateEmailNotifications(Request $request): RedirectResponse
    {
        $request->user()->update(['email_notifications' => $request->boolean('email_notifications')]);
        return Redirect::route('profile.edit')->with('status', 'email-notifications-updated');
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
        $userId = $user->id;

        LogHelper::log('deleted_account', 'user', $userId, [], 'warning');

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
