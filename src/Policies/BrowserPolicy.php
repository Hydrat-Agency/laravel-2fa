<?php

namespace Hydrat\Laravel2FA\Policies;

use Illuminate\Support\Facades\Cookie;
use Hydrat\Laravel2FA\Models\LoginAttempt;
use Hydrat\Laravel2FA\Policies\AbstractPolicy;

class BrowserPolicy extends AbstractPolicy
{
    /**
     * @var string
     */
    protected const COOKIE_NAME = '2fa_browser_check';


    /**
     * Check that the request passes the policy.
     * If this return false, the 2FA Auth will be triggered.
     *
     * @return bool
     */
    public function passes(): bool
    {
        $uid = Cookie::get(static::COOKIE_NAME);

        if (!$uid) {
            return false;
        }

        return LoginAttempt::where('user_id', $this->user->id)
                        ->where('succeed', true)
                        ->where('uid', $uid)
                        ->exists();
    }
    
    /**
     * The reason sent to the Notification and the frontend view,
     * to tell the user why the 2FA check was triggered.
     *
     * @return string
     */
    public function message(): string
    {
        return $this->message ?: __('your current browser is unknown');
    }

    /**
     * An action to perform on successful 2FA login.
     * May be used to remember stuff for the next policy check.
     *
     * @return void
     */
    public function onSucceed(): void
    {
        if ($this->attempt) {
            $lifetime = intval($this->config('browser', 30 * 1440));

            Cookie::queue(
                static::COOKIE_NAME,
                $this->attempt->uid,
                $lifetime
            );
        }
    }
}
