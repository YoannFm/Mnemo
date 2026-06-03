<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'site_name'        => Setting::get('site_name', 'Mnémo'),
            'site_description' => Setting::get('site_description', ''),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'site_name'        => 'required|string|max:100',
            'site_description' => 'nullable|string|max:500',
        ]);

        Setting::set('site_name', $request->input('site_name'));
        Setting::set('site_description', $request->input('site_description', ''));

        return back()->with('success', 'Paramètres sauvegardés.');
    }

    public function mail()
    {
        $smtpConfig = [
            'host'     => config('mail.mailers.smtp.host', ''),
            'port'     => config('mail.mailers.smtp.port', 587),
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

        if (!empty($data['smtp_password'])) {
            $replace('MAIL_PASSWORD', $data['smtp_password']);
        }

        file_put_contents(base_path('.env'), $env);

        Artisan::call('config:clear');

        return back()->with('success', 'Configuration e-mail sauvegardée.');
    }
}
