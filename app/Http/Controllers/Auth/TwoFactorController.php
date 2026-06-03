<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TwoFactorController extends Controller
{
    public function show()
    {
        return view('auth.two-factor-challenge');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $user = $request->session()->get('two_factor_user_id')
            ? \App\Models\User::find($request->session()->get('two_factor_user_id'))
            : auth()->user();

        if (!$user || !$user->isValidTwoFactorCode($request->code)) {
            return back()->withErrors(['code' => 'Le code est invalide ou a expiré.']);
        }

        // Si on utilise un code de récupération, on le consomme
        if ($user->isValidRecoveryCode($request->code)) {
            $user->replaceRecoveryCode($request->code);
        }

        // Si l'utilisateur n'est pas encore connecté (flow post-login)
        if (!auth()->check()) {
            auth()->login($user);
        }

        $request->session()->put('two_factor_verified', true);
        $request->session()->forget('two_factor_user_id');

        return redirect()->intended(route('dashboard'));
    }
}
