<?php

namespace Hydrat\Laravel2FA\Drivers;

use Illuminate\Http\Request;
use Hydrat\Laravel2FA\Models\Token;
use Illuminate\Support\Facades\Auth;
use Hydrat\Laravel2FA\Notifications\TwoFactorToken;
use Hydrat\Laravel2FA\Contracts\TwoFactorAuthenticatableContract;

class BaseDriver
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
     * @return Illuminate\Http\RedirectResponse|null
     */
    public function maybeTrigger(Request $request, TwoFactorAuthenticatableContract $user)
    {
        return $this->mustTrigger($user)
                    ? $this->trigger($request, $user)
                    : null;
    }

    /**
     * Check if must trigger 2FA token for this user.
     *
     * @return bool
     */
    public function mustTrigger(TwoFactorAuthenticatableContract $user)
    {
        return true;
    }
    
    /**
     * Trigger 2FA token for this user and redirect the the 2FA token submit page.
     *
     * @param \Illuminate\Http\Request $request
     * @param TwoFactorAuthenticatableContract $user
     *
     * @return Illuminate\Http\RedirectResponse
     */
    public function trigger(Request $request, TwoFactorAuthenticatableContract $user)
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
     * @param TwoFactorAuthenticatableContract $user
     * @param \Hydrat\Laravel2FA\Models\Token $token
     *
     * @return void
     */
    public function notify(TwoFactorAuthenticatableContract $user, Token $token): void
    {
        $user->notify(new TwoFactorToken($token->token));
    }

    /**
     * Check if the given token is valid for the given user.
     *
     * @param TwoFactorAuthenticatableContract $user
     * @param string $token
     *
     * @return bool
     */
    public function validateToken(TwoFactorAuthenticatableContract $user, string $token): bool
    {
        return Token::unexpired()
            ->where('token', $token)
            ->where('user_id', $user->id)
            ->exists();
    }
    
    /**
     * Forget the user tokens & clear session inputs.
     *
     * @param \Illuminate\Http\Request $request
     * @param TwoFactorAuthenticatableContract $user Optionnal.
     *
     * @return void
     */
    public function clear(Request $request, TwoFactorAuthenticatableContract $user = null): void
    {
        $user = $user ?: $request->user();

        $request->session()->forget('2fa:auth:id');

        if ($user) {
            Token::where('user_id', $user->id)->delete();
        }
    }
}
