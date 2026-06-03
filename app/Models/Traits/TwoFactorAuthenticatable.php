<?php

namespace App\Models\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

trait TwoFactorAuthenticatable
{
    public function hasTwoFactorAuth(): bool
    {
        return filled($this->two_factor_secret);
    }

    public function replaceRecoveryCode(string $code): void
    {
        $codes = $this->two_factor_recovery_codes;
        if (in_array($code, $codes, true)) {
            $this->forceFill([
                'two_factor_recovery_codes' => array_diff($codes, [$code]),
            ])->save();
        }
    }

    public function isValidTwoFactorCode(string $code): bool
    {
        if (!filled($this->two_factor_secret)) return false;
        $code = Str::remove(' ', $code);
        if ((new Google2FA())->verifyKey($this->two_factor_secret, $code)) return true;
        return $this->isValidRecoveryCode($code);
    }

    public function isValidRecoveryCode(string $code): bool
    {
        return collect($this->two_factor_recovery_codes)
            ->contains(fn($c) => hash_equals($c, $code));
    }

    public function generateRecoveryCodes(): array
    {
        return Collection::times(8, fn() => Str::random(8).'-'.Str::random(8))->all();
    }

    public function getTwoFactorRecoveryCodesAttribute($value): array
    {
        return $value ? json_decode($value, true) : [];
    }

    public function setTwoFactorRecoveryCodesAttribute($value): void
    {
        $this->attributes['two_factor_recovery_codes'] = json_encode($value);
    }
}
