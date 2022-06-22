<?php

namespace Hydrat\Laravel2FA\Drivers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Hydrat\Laravel2FA\Models\Token;
use Illuminate\Support\Facades\Auth;
use Hydrat\Laravel2FA\Models\LoginAttempt;
use Hydrat\Laravel2FA\Notifications\TwoFactorToken;
use Hydrat\Laravel2FA\Contracts\TwoFactorDriverContract;
use Hydrat\Laravel2FA\Contracts\TwoFactorPolicyContract;
use Hydrat\Laravel2FA\Exceptions\InvalidPolicyException;
use Hydrat\Laravel2FA\Contracts\TwoFactorAuthenticatableContract as Authenticatable;

class BaseDriver implements TwoFactorDriverContract
{
    /**
     * Creates a new instance of this class.
     *
     * @return static
     */
    public static function make()
    {
        return new static();
    }

    /**
     * Check if must trigger 2FA token for this user,
     * If so, trigger it and return a redirect response, else return null.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Hydrat\Laravel2FA\Contracts\TwoFactorAuthenticatableContract $user
     *
     * @return Illuminate\Http\RedirectResponse|null
     */
    public function maybeTrigger(Request $request, Authenticatable $user)
    {
        return $this->mustTrigger($request, $user)
                    ? $this->trigger($request, $user)
                    : null;
    }

    /**
     * Check if must trigger 2FA token for this user.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Hydrat\Laravel2FA\Contracts\TwoFactorAuthenticatableContract $user
     *
     * @return bool
     */
    public function mustTrigger(Request $request, Authenticatable $user): bool
    {
        $attempt  = LoginAttempt::newForUser($user);
        $policies = $this->getPolicies();

        # Ask policies if we should trigger.
        if ($policies->isNotEmpty()) {
            foreach ($policies as $policy) {
                $check = new $policy($request, $user, $attempt);

                if (!$check->passes()) {
                    $request->session()->put('2fa:auth:reason', $check->message());
                    return true;
                }
            }
        }

        # No policy triggered.
        $attempt->succeed();
        return false;
    }
    
    /**
     * Trigger 2FA token for this user and redirect the the 2FA token submit page.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Hydrat\Laravel2FA\Contracts\TwoFactorAuthenticatableContract $user
     *
     * @return Illuminate\Http\RedirectResponse
     */
    public function trigger(Request $request, Authenticatable $user)
    {
        Auth::logout();

        $request->session()->put('2fa:auth:id', $user->id);
        $request->session()->put('2fa:auth:remember', $request->filled('remember'));

        # Generate the token.
        $token = $user->generateTwoFactorToken();

        # Notify the user about his token.
        $this->notify($user, $token);

        return redirect(
            route('auth.2fa.index')
        );
    }

    /**
     * Notify the user about his created token.
     *
     * @param \Hydrat\Laravel2FA\Contracts\TwoFactorAuthenticatableContract $user
     * @param \Hydrat\Laravel2FA\Models\Token $token
     *
     * @return void
     */
    public function notify(Authenticatable $user, Token $token): void
    {
        $notification = config('laravel-2fa.notification', TwoFactorToken::class);

        $user->notify(new $notification($token->token));
    }

    /**
     * Check if the given token is valid for the given user.
     *
     * @param \Hydrat\Laravel2FA\Contracts\TwoFactorAuthenticatableContract $user
     * @param string $token
     *
     * @return bool
     */
    public function validateToken(Authenticatable $user, string $token): bool
    {
        return Token::unexpired()
                ->where('token', $token)
                ->where('user_id', $user->id)
                ->exists();
    }
    
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
    public function succeed(Request $request, Authenticatable $user = null): void
    {
        $user = $user ?: $request->user();

        # Clear session
        $request->session()->forget('2fa:auth:id');
        $request->session()->forget('2fa:auth:remember');
        $request->session()->forget('2fa:auth:reason');

        # Clear token
        if ($user) {
            Token::where('user_id', $user->id)->delete();
            
            # Succeed attempt
            $attempt = LoginAttempt::orderBy('created_at', 'DESC')
                            ->where('user_id', $user->id)
                            ->where('succeed', false)
                            ->first();
            
            if ($attempt) {
                $attempt->succeed();
            }
        }

        # Call onSucceed methods for policies.
        $this->getPolicies()->each(function ($policy) use ($request, $user, $attempt) {
            $check = new $policy($request, $user, $attempt);
            $check->onSucceed();
        });
    }

    /**
     * Get the class mapping for policies short names.
     *
     * @return Collection
     */
    protected function getPoliciesMapping()
    {
        return new Collection(
            config('laravel-2fa.mapping', [])
        );
    }
    
    /**
     * Get the enabled policies.
     *
     * @return Collection
     */
    protected function getPolicies()
    {
        $mapping  = $this->getPoliciesMapping();
        $policies = collect(config('laravel-2fa.policy'))->filter();

        return $policies->map(function ($policy) use ($mapping) {
            if ($mapping->has($policy)) {
                $policy = $mapping->get($policy);
            }

            $contract = TwoFactorPolicyContract::class;

            if (!class_exists($policy) || !array_key_exists($contract, class_implements($policy))) {
                throw new InvalidPolicyException(sprintf(
                    'The selected `%s` policy is unknown or doesn\'t implement the `%s` contract.',
                    $policy,
                    $contract
                ));
            }
            
            return $policy;
        });
    }
}
