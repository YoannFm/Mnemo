<?php

namespace App\Http\Controllers;

use App\Helpers\LogHelper;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

class ProfileTwoFactorController extends Controller
{
    public function show(Request $request)
    {
        return view('profile.two-factor', [
            'user' => $request->user(),
        ]);
    }

    public function enable(Request $request)
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $request->user()->email,
            $secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrSvg = $writer->writeString($qrCodeUrl);

        // Stocker temporairement le secret en session
        $request->session()->put('two_factor_secret', $secret);

        return view('profile.two-factor', [
            'user'     => $request->user(),
            'secret'   => $secret,
            'qrSvg'    => $qrSvg,
            'enabling' => true,
        ]);
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'code'   => ['required', 'string'],
            'secret' => ['required', 'string'],
        ]);

        $secret = $request->session()->get('two_factor_secret');

        // Vérifier que le secret en session correspond à celui soumis (anti-tampering)
        if (!$secret || $secret !== $request->secret) {
            return back()->withErrors(['code' => 'Session expirée. Veuillez recommencer.']);
        }

        $google2fa = new Google2FA();
        if (!$google2fa->verifyKey($secret, $request->code)) {
            return back()->withErrors(['code' => 'Code invalide. Vérifiez votre application d\'authentification.'])
                ->withInput();
        }

        $recoveryCodes = $request->user()->generateRecoveryCodes();

        $request->user()->forceFill([
            'two_factor_secret'         => $secret,
            'two_factor_recovery_codes' => $recoveryCodes,
        ])->save();

        $request->session()->forget('two_factor_secret');
        $request->session()->put('two_factor_verified', true);

        LogHelper::log('2fa_enabled', 'user', Auth::id());

        return view('profile.two-factor', [
            'user'          => $request->user(),
            'recoveryCodes' => $recoveryCodes,
            'justEnabled'   => true,
        ]);
    }

    public function disable(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (!Hash::check($request->password, $request->user()->password)) {
            return back()->withErrors(['password' => 'Mot de passe incorrect.']);
        }

        $request->user()->forceFill([
            'two_factor_secret'         => null,
            'two_factor_recovery_codes' => null,
        ])->save();

        $request->session()->forget('two_factor_verified');

        LogHelper::log('2fa_disabled', 'user', Auth::id(), [], 'warning');

        return redirect()->route('profile.2fa.show')
            ->with('status', '2fa-disabled');
    }
}
