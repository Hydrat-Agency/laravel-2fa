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
        $this->deleteTwoFactorTokens();

        $lifetime = config('laravel-2fa.options.token_lifetime', 10);

        return Token::create([
            'user_id'    => $this->id,
            'token'      => rand(100000, 999999),
            'expires_at' => now()->addMinutes($lifetime),
        ]);
    }
    
    /**
     * Delete the user two-factor authentication tokens.
     *
     * @return void
     */
    public function deleteTwoFactorTokens(): void
    {
        Token::where('user_id', $this->id)->delete();
    }
}
