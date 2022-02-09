<?php

namespace Hydrat\Laravel2FA\Contracts;

use Illuminate\Http\Request;
use Hydrat\Laravel2FA\Models\Token;
use Hydrat\Laravel2FA\Contracts\TwoFactorAuthenticatableContract as Authenticatable;

interface TwoFactorDriverContract
{
    /**
     * Check if must trigger 2FA token for this user,
     * If so, trigger it and return a redirect response, else return null.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Hydrat\Laravel2FA\Contracts\TwoFactorAuthenticatableContract $user
     *
     * @return Illuminate\Http\RedirectResponse|null
     */
    public function maybeTrigger(Request $request, Authenticatable $user);

    
    /**
     * Check if must trigger 2FA token for this user.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Hydrat\Laravel2FA\Contracts\TwoFactorAuthenticatableContract $user
     *
     * @return bool
     */
    public function mustTrigger(Request $request, Authenticatable $user): bool;
    
    
    /**
     * Trigger 2FA token for this user and redirect the the 2FA token submit page.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Hydrat\Laravel2FA\Contracts\TwoFactorAuthenticatableContract $user
     *
     * @return Illuminate\Http\RedirectResponse
     */
    public function trigger(Request $request, Authenticatable $user);

    
    /**
     * Notify the user about his created token.
     *
     * @param \Hydrat\Laravel2FA\Contracts\TwoFactorAuthenticatableContract $user
     * @param \Hydrat\Laravel2FA\Models\Token $token
     *
     * @return void
     */
    public function notify(Authenticatable $user, Token $token): void;

    
    /**
     * Check if the given token is valid for the given user.
     *
     * @param \Hydrat\Laravel2FA\Contracts\TwoFactorAuthenticatableContract $user
     * @param string $token
     *
     * @return bool
     */
    public function validateToken(Authenticatable $user, string $token): bool;
    
    
    /**
     * Trigger this after a successful 2FA login.
     * Forget the user tokens & clear session inputs.
     * Also validates the Login attempt.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Hydrat\Laravel2FA\Contracts\TwoFactorAuthenticatableContract $user Optionnal.
     *
     * @return void
     */
    public function succeed(Request $request, Authenticatable $user = null): void;
}
