<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Carbon::setLocale('fr');

        // Supprimer install.php si l'app est installée (sécurité après git pull)
        if (env('APP_INSTALLED') === 'true') {
            $installFile = public_path('install.php');
            if (file_exists($installFile)) {
                @unlink($installFile);
            }
        }
    }
}
