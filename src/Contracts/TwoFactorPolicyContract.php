<?php

namespace Hydrat\Laravel2FA\Contracts;

use Illuminate\Http\Request;
use Hydrat\Laravel2FA\Models\LoginAttempt;
use Hydrat\Laravel2FA\Contracts\TwoFactorAuthenticatableContract;

interface TwoFactorPolicyContract
{
    /**
     * The class constructor.
     *
     * @return void
     */
    public function __construct(Request $request, TwoFactorAuthenticatableContract $user, LoginAttempt $attempt);

    /**
     * Check that the request passes the policy.
     * If this return false, the 2FA Auth will be triggered.
     *
     * @return bool
     */
    public function passes(): bool;
    
    /**
     * The reason sent to the Notification and the frontend view,
     * to tell the user why the 2FA check was triggered.
     *
     * @return string
     */
    public function message(): string;

    /**
     * An action to perform on successful 2FA login.
     * May be used to remember stuff for the next policy check.
     *
     * @return void
     */
    public function onSucceed(): void;
}
