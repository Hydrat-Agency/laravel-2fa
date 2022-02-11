<?php

namespace Hydrat\Laravel2FA\Policies;

use Illuminate\Http\Request;
use Hydrat\Laravel2FA\Models\LoginAttempt;
use Hydrat\Laravel2FA\Contracts\TwoFactorPolicyContract;
use Hydrat\Laravel2FA\Contracts\TwoFactorAuthenticatableContract;

abstract class AbstractPolicy implements TwoFactorPolicyContract
{
    /**
     * The incomming request at login.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request = null;

    /**
     * The user that just loggued in.
     *
     * @var \Hydrat\Laravel2FA\Contracts\TwoFactorAuthenticatableContract
     */
    protected $user = null;

    /**
     * The login attempt, with UID and IP address data.
     *
     * @var \Hydrat\Laravel2FA\Models\LoginAttempt
     */
    protected $attempt = null;

    /**
     * The failing message to set dynalically during check.
     *
     * @var string
     */
    protected $message = '';

    /**
     * The class constructor.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Hydrat\Laravel2FA\Contracts\TwoFactorPolicyContract $user
     * @param \Hydrat\Laravel2FA\Models\LoginAttempt $attempt
     *
     * @return void
     */
    public function __construct(Request $request, TwoFactorAuthenticatableContract $user, LoginAttempt $attempt)
    {
        $this->request = $request;
        $this->user    = $user;
        $this->attempt = $attempt;
    }
    
    /**
     * Check that the request passes the policy.
     * If this return false, the 2FA Auth will be triggered.
     *
     * @return bool
     */
    public function passes(): bool
    {
        return true;
    }
    
    /**
     * The reason sent to the Notification and the frontend view,
     * to tell the user why the 2FA check was triggered.
     *
     * @return string
     */
    public function message(): string
    {
        return $this->message ?: '';
    }
    
    /**
     * An action to perform on successful 2FA login.
     * May be used to remember stuff for the next policy check.
     *
     * @return void
     */
    public function onSucceed(): void
    {
        //
    }

    /**
     * Get the given policy options from configuration file.
     *
     * @param string $policy   The policy name as defined in the laravel-2fa.options.policies conf.
     * @param mixed  $default  Default value if not set of null.
     *
     * @return mixed
     */
    protected function config(string $policy, $default = null)
    {
        return config('laravel-2fa.options.policies.' . $policy, $default);
    }
}
