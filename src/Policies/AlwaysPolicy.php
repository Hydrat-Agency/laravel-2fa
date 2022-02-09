<?php

namespace Hydrat\Laravel2FA\Policies;

use Hydrat\Laravel2FA\Policies\AbstractPolicy;

class AlwaysPolicy extends AbstractPolicy
{
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
        return $this->message ?: __('');
    }
}
