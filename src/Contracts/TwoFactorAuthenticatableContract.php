<?php

namespace Hydrat\Laravel2FA\Contracts;

use Hydrat\Laravel2FA\Models\Token;

interface TwoFactorAuthenticatableContract
{
    /**
     * Generates a new two-factor authentication token.
     *
     * @return \Hydrat\Laravel2FA\Models\Token
     */
    public function generateTwoFactorToken(): Token;

    /**
     * Send the given notification.
     *
     * @param  mixed  $instance
     * @return void
     */
    public function notify($instance);
}
