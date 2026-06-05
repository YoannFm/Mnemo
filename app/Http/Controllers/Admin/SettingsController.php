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
            'copyright'        => Setting::get('copyright', ''),
            'site_key'         => Setting::get('site_key', ''),
        ];

        return view('admin.settings.index', compact('settings', 'images', 'timezones'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'site_name'        => 'required|string|max:100',
            'site_url'         => 'nullable|url|max:255',
            'site_description' => 'nullable|string|max:500',
            'site_keywords'    => 'nullable|string|max:500',
            'site_logo'        => 'nullable|string|max:255',
            'timezone'         => 'nullable|string|max:100',
            'locale'           => 'nullable|in:fr,en',
            'copyright'        => 'nullable|string|max:255',
            'site_key'         => 'nullable|string|max:255',
        ]);

        $fields = [
            'site_name', 'site_url', 'site_description', 'site_keywords',
            'site_logo', 'timezone', 'locale', 'copyright', 'site_key',
        ];

        $fieldsToLog = ['site_name', 'site_url', 'site_description', 'site_keywords', 'site_logo', 'timezone', 'locale', 'site_key'];
        foreach ($fieldsToLog as $field) {
            $old = Setting::get($field, '');
            $new = $request->input($field, '');
            if ($old !== $new) {
                LogHelper::log('updated_setting', 'setting', null, ['field' => $field, 'section' => 'general'], 'info', $old, $new);
            }
        }

        foreach ($fields as $field) {
            Setting::set($field, $request->input($field, ''));
        }

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

        $old = Setting::get('home_message', '');
        $new = $request->input('home_message', '');
        if ($old !== $new) {
            LogHelper::log('updated_setting', 'setting', null, ['field' => 'home_message', 'section' => 'home'], 'info', $old, $new);
        }

        Setting::set('home_message', $new);

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

        $oldConditions = Setting::get('registration_conditions', '');
        $newConditions = $request->input('registration_conditions', '');
        if ($oldConditions !== $newConditions) {
            LogHelper::log('updated_setting', 'setting', null, ['field' => 'registration_conditions', 'section' => 'auth'], 'info', $oldConditions, $newConditions);
        }
        Setting::set('registration_conditions', $newConditions);

        foreach ($booleans as $field) {
            $old = Setting::get($field, '');
            $new = $request->boolean($field) ? '1' : '0';
            if ($old !== $new) {
                LogHelper::log('updated_setting', 'setting', null, ['field' => $field, 'section' => 'auth'], 'info', $old, $new);
            }
            Setting::set($field, $new);
        }

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

        $mailFields = [
            'mailer'       => config('mail.default', ''),
            'from_address' => config('mail.from.address', ''),
            'smtp_host'    => config('mail.mailers.smtp.host', ''),
            'smtp_port'    => (string) config('mail.mailers.smtp.port', ''),
            'smtp_username'=> config('mail.mailers.smtp.username', ''),
            'smtp_scheme'  => config('mail.mailers.smtp.scheme', ''),
        ];
        foreach ($mailFields as $field => $oldVal) {
            $newVal = (string) ($data[$field] ?? '');
            if ($oldVal !== $newVal) {
                LogHelper::log('updated_setting', 'setting', null, ['field' => $field, 'section' => 'mail'], 'info', $oldVal, $newVal);
            }
        }
        if (!empty($data['smtp_password'])) {
            LogHelper::log('updated_setting', 'setting', null, ['field' => 'smtp_password', 'section' => 'mail'], 'info', null, 'updated');
        }

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
        ];

        return view('admin.settings.maintenance', compact('settings'));
    }

    public function updateMaintenance(Request $request)
    {
        $request->validate([
            'maintenance_message' => 'nullable|string',
        ]);

        $oldMsg = Setting::get('maintenance_message', '');
        $newMsg = $request->input('maintenance_message', '');
        if ($oldMsg !== $newMsg) {
            LogHelper::log('updated_setting', 'setting', null, ['field' => 'maintenance_message', 'section' => 'maintenance'], 'info', $oldMsg, $newMsg);
        }

        $oldEnabled = Setting::get('maintenance_enabled', '0');
        $newEnabled = $request->boolean('maintenance_enabled') ? '1' : '0';
        if ($oldEnabled !== $newEnabled) {
            LogHelper::log('updated_setting', 'setting', null, ['field' => 'maintenance_enabled', 'section' => 'maintenance'], 'info', $oldEnabled, $newEnabled);
        }

        Setting::set('maintenance_message', $newMsg);
        Setting::set('maintenance_enabled', $newEnabled);

        return back()->with('success', 'Paramètres de maintenance sauvegardés.');
    }
}
