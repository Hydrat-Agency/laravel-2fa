<?php

namespace Hydrat\Laravel2FA\Policies;

use Hydrat\Laravel2FA\Models\LoginAttempt;
use Hydrat\Laravel2FA\Policies\AbstractPolicy;

class GeoipPolicy extends AbstractPolicy
{
    /**
     * Check that the request passes the policy.
     * If this return false, the 2FA Auth will be triggered.
     *
     * @return bool
     */
    public function passes(): bool
    {
        $mode = $this->config('geoip', 'country');

        if (!in_array($mode, ['country', 'region', 'city', 'time_zone'])) {
            $mode = 'country';
        }

        $last_attempt = LoginAttempt::orderBy('created_at', 'DESC')
                            ->where('user_id', $this->user->id)
                            ->where('succeed', true)
                            ->first();

        # No attempt to compare with
        if (!$last_attempt) {
            return false;
        }

        return $this->compare(
            $this->attempt,
            $last_attempt,
            $mode
        );
    }
    
    /**
     * The reason sent to the Notification and the frontend view,
     * to tell the user why the 2FA check was triggered.
     *
     * @return string
     */
    public function message(): string
    {
        return $this->message ?: __('your current location differs from your usual location');
    }

    /**
     * Compare login attemps to ensure they math the policy in the given $mode.
     *
     * @return bool false if attempts missmatchs
     */
    protected function compare(LoginAttempt $current, LoginAttempt $last, string $mode): bool
    {
        $fields = [];

        if (in_array($mode, ['country', 'region', 'city'])) {
            $fields[] = 'country_code';
        }

        if (in_array($mode, ['region', 'city'])) {
            $fields[] = 'region_code';
        }

        if (in_array($mode, ['time_zone', 'city'])) {
            $fields[] = $mode;
        }

        foreach ($fields as $field) {
            if ($current->{$field} !== $last->{$field}) {
                return false;
            }
        }

        return true;
    }
}
