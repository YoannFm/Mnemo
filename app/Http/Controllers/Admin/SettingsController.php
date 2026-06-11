<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class SettingsController extends Controller
{
    // ─────────────────────────────────────────────
    // Paramètres généraux
    // ─────────────────────────────────────────────

    public function index()
    {
        $images    = Image::orderBy('name')->get();
        $timezones = timezone_identifiers_list();
        $settings  = [
            'site_name'        => Setting::get('site_name', 'Mnémo'),
            'site_url'         => Setting::get('site_url', config('app.url')),
            'site_description' => Setting::get('site_description', ''),
            'site_keywords'    => Setting::get('site_keywords', ''),
            'site_logo'        => Setting::get('site_logo', ''),
            'timezone'         => Setting::get('timezone', 'Europe/Paris'),
            'locale'           => Setting::get('locale', 'fr'),
            'site_key'         => Setting::get('site_key', ''),
            'posts_webhook'    => Setting::get('posts_webhook', ''),
            'exam_default_expires_days' => Setting::get('exam_default_expires_days', ''),
            'item_required_name_fr'    => Setting::get('item_required_name_fr', '1'),
            'item_required_name_alt'   => Setting::get('item_required_name_alt', '1'),
            'item_required_photo'      => Setting::get('item_required_photo', '0'),
            'item_required_function'   => Setting::get('item_required_function', '0'),
        ];

        return view('admin.settings.index', compact('settings', 'images', 'timezones'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'site_name'        => 'required|string|max:100',
            'site_url'         => 'nullable|url|max:255',
            'site_description' => 'nullable|string',
            'site_keywords'    => 'nullable|string|max:500',
            'site_logo'        => 'nullable|string|max:255',
            'timezone'         => 'nullable|string|max:100',
            'locale'           => 'nullable|in:fr,en',
            'site_key'         => 'nullable|string|max:255',
            'posts_webhook'            => 'nullable|url|max:500',
            'exam_default_expires_days' => 'nullable|integer|min:1|max:365',
        ]);

        $fields = [
            'site_name', 'site_url', 'site_description', 'site_keywords',
            'site_logo', 'timezone', 'locale', 'site_key', 'posts_webhook',
            'exam_default_expires_days',
        ];

        // Checkboxes champs items (non cochée = absent de la requête = '0')
        foreach (['item_required_name_fr', 'item_required_name_alt', 'item_required_photo', 'item_required_function'] as $cb) {
            Setting::set($cb, $request->has($cb) ? '1' : '0');
        }

        foreach ($fields as $field) {
            Setting::set($field, $request->input($field, ''));
        }

        LogHelper::log('updated_settings', 'settings', null, ['section' => 'general'], 'info');

        return back()->with('success', 'Paramètres sauvegardés.');
    }

    // ─────────────────────────────────────────────
    // Accueil
    // ─────────────────────────────────────────────

    public function home()
    {
        $home_message = Setting::get('home_message', '');
        return view('admin.settings.home', compact('home_message'));
    }

    public function updateHome(Request $request)
    {
        $request->validate([
            'home_message' => 'nullable|string',
        ]);

        Setting::set('home_message', $request->input('home_message', ''));

        LogHelper::log('updated_settings', 'settings', null, ['section' => 'home'], 'info');

        return back()->with('success', 'Message d\'accueil sauvegardé.');
    }

    // ─────────────────────────────────────────────
    // Authentification
    // ─────────────────────────────────────────────

    public function auth()
    {
        $settings = [
            'registration_conditions'     => Setting::get('registration_conditions', ''),
            'registration_enabled'        => Setting::get('registration_enabled', '1'),
            'allow_name_change'           => Setting::get('allow_name_change', '1'),
            'allow_account_deletion'      => Setting::get('allow_account_deletion', '1'),
            'email_verification_required' => Setting::get('email_verification_required', '0'),
            'admin_2fa_required'          => Setting::get('admin_2fa_required', '0'),
        ];

        return view('admin.settings.auth', compact('settings'));
    }

    public function updateAuth(Request $request)
    {
        $booleans = [
            'registration_enabled', 'allow_name_change', 'allow_account_deletion',
            'email_verification_required', 'admin_2fa_required',
        ];

        Setting::set('registration_conditions', $request->input('registration_conditions', ''));

        foreach ($booleans as $field) {
            Setting::set($field, $request->boolean($field) ? '1' : '0');
        }

        LogHelper::log('updated_settings', 'settings', null, ['section' => 'auth'], 'info');

        return back()->with('success', 'Paramètres d\'authentification sauvegardés.');
    }

    // ─────────────────────────────────────────────
    // Mail
    // ─────────────────────────────────────────────

    public function mail()
    {
        $smtpConfig = [
            'host'     => config('mail.mailers.smtp.host', ''),
            'port'     => config('mail.mailers.smtp.port', 587),
            'scheme'   => config('mail.mailers.smtp.scheme', null),
            'username' => config('mail.mailers.smtp.username', ''),
            'from'     => config('mail.from.address', ''),
        ];

        $currentMailer = config('mail.default', 'array');

        return view('admin.settings.mail', compact('smtpConfig', 'currentMailer'));
    }

    public function updateMail(Request $request)
    {
        $data = $request->validate([
            'mailer'        => 'required|string|in:smtp,sendmail,array',
            'from_address'  => 'required|email|max:255',
            'smtp_host'     => 'nullable|string|max:255',
            'smtp_port'     => 'nullable|integer|min:1|max:65535',
            'smtp_scheme'   => 'nullable|string|in:,smtp,smtps',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
        ]);

        $env = file_get_contents(base_path('.env'));

        $replace = function (string $key, string $value) use (&$env) {
            $value = strpos($value, ' ') !== false ? '"' . $value . '"' : $value;
            if (preg_match("/^{$key}=/m", $env)) {
                $env = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $env);
            } else {
                $env .= "\n{$key}={$value}";
            }
        };

        $replace('MAIL_MAILER',       $data['mailer']);
        $replace('MAIL_FROM_ADDRESS', $data['from_address']);
        $replace('MAIL_HOST',         $data['smtp_host'] ?? '');
        $replace('MAIL_PORT',         (string) ($data['smtp_port'] ?? 587));
        $replace('MAIL_USERNAME',     $data['smtp_username'] ?? '');
        $replace('MAIL_SCHEME',       $data['smtp_scheme'] ?? '');

        if (!empty($data['smtp_password'])) {
            $replace('MAIL_PASSWORD', $data['smtp_password']);
        }

        file_put_contents(base_path('.env'), $env);

        Setting::set('mail.users_email_verification', $request->boolean('users_email_verification') ? '1' : '0');

        Artisan::call('config:clear');

        return back()->with('success', 'Configuration e-mail sauvegardée.');
    }

    public function sendTestMail()
    {
        try {
            $user = Auth::user();
            Mail::raw('Ceci est un e-mail de test envoyé depuis le panel d\'administration de Mnémo.', function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Test e-mail - Mnémo');
            });

            return response()->json(['message' => 'E-mail de test envoyé à ' . $user->email]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────
    // Maintenance
    // ─────────────────────────────────────────────

    public function maintenance()
    {
        $settings = [
            'maintenance_message' => Setting::get('maintenance_message', 'Le site est en maintenance. Merci de revenir plus tard.'),
            'maintenance_enabled' => Setting::get('maintenance_enabled', '0'),
            'maintenance_all'     => Setting::get('maintenance_all', '0'),
        ];

        return view('admin.settings.maintenance', compact('settings'));
    }

    public function updateMaintenance(Request $request)
    {
        $request->validate([
            'maintenance_message' => 'nullable|string',
        ]);

        Setting::set('maintenance_message', $request->input('maintenance_message', ''));
        Setting::set('maintenance_enabled', $request->boolean('maintenance_enabled') ? '1' : '0');
        Setting::set('maintenance_all',     $request->boolean('maintenance_all') ? '1' : '0');

        LogHelper::log('updated_settings', 'settings', null, ['section' => 'maintenance'], 'info');

        return back()->with('success', 'Paramètres de maintenance sauvegardés.');
    }
}
