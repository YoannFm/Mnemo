<?php

namespace App\Console\Commands;

use App\Services\LicenseService;
use Illuminate\Console\Command;

class CheckLicense extends Command
{
    protected $signature   = 'license:check';
    protected $description = 'Verify license with MnemoCloud';

    public function handle(): int
    {
        LicenseService::forget();
        $result = LicenseService::check();

        if ($result['valid']) {
            $this->info('License valid. Plan: ' . ($result['plan'] ?? 'n/a'));
            return 0;
        }

        $this->warn('License invalid: ' . ($result['reason'] ?? 'unknown'));
        return 1;
    }
}
