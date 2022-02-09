<?php

namespace Hydrat\Laravel2FA\Policies;

use Hydrat\Laravel2FA\Models\LoginAttempt;
use Hydrat\Laravel2FA\Policies\AbstractPolicy;

class IpPolicy extends AbstractPolicy
{
    /**
     * Check that the request passes the policy.
     * If this return false, the 2FA Auth will be triggered.
     *
     * @return bool
     */
    public function passes(): bool
    {
        $last_attempt = LoginAttempt::orderBy('created_at', 'DESC')
                            ->where('user_id', $this->user->id)
                            ->where('succeed', true)
                            ->first();

        # No attempt to compare with
        if (!$last_attempt) {
            return false;
        }

        return $this->attempt->ip === $last_attempt->ip;
    }
    
    /**
     * The reason sent to the Notification and the frontend view,
     * to tell the user why the 2FA check was triggered.
     *
     * @return string
     */
    public function message(): string
    {
        return $this->message ?: __('your current IP address differs from your last IP address');
    }
}
