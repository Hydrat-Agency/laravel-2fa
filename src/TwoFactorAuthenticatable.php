<?php

namespace Hydrat\Laravel2FA;

use Hydrat\Laravel2FA\Models\Token;

trait TwoFactorAuthenticatable
{
    /**
     * Generates a new two-factor authentication token.
     *
     * @return \Hydrat\Laravel2FA\Models\Token
     */
    public function generateTwoFactorToken(): Token
    {
        Token::where('user_id', $this->id)->delete();

        return Token::create([
            'user_id'    => $this->id,
            'token'      => rand(100000, 999999),
            'expires_at' => now()->addMinutes(10),
        ]);
    }
}
