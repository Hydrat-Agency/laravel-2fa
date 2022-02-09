<?php

namespace Hydrat\Laravel2FA;

use Hydrat\Laravel2FA\Drivers\BaseDriver;
use Hydrat\Laravel2FA\Contracts\TwoFactorDriverContract;

class TwoFactorAuth
{
    /**
     * Get the two-factor driver.
     *
     * @return \Hydrat\Laravel2FA\Contracts\TwoFactorDriverContract
     */
    public static function getDriver(): TwoFactorDriverContract
    {
        $driver = config('laravel-2fa.driver', BaseDriver::class);

        return new $driver();
    }
}
