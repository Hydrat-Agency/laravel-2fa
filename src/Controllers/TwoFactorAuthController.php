<?php

namespace Hydrat\Laravel2FA\Controllers;

use ReflectionClass;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use Hydrat\Laravel2FA\Drivers\BaseDriver as TwoFactorDriver;

class TwoFactorAuthController extends Controller
{
    /**
     * Show two-factor authentication page.
     *
     * @return \Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index()
    {
        return session('2fa:auth:id')
                ? view('auth.2fa.token', $this->getViewParams())
                : redirect(url('login'));
    }

    /**
     * Verify the two-factor authentication token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'token' => 'required',
        ]);
        
        if (!session('2fa:auth:id')) {
            return redirect(url('login'));
        }

        $model = $this->getAutenticableModel();

        $user = (new $model)->findOrFail(
            $request->session()->get('2fa:auth:id')
        );

        if (! TwoFactorDriver::make()->validateToken($user, $request->token)) {
            return redirect(route('auth.2fa.index'))->with('error', 'Invalid two-factor authentication token provided!');
        }

        Auth::login(
            $user,
            $request->session()->get('2fa:auth:remember')
        );
            
        TwoFactorDriver::make()->succeed($request, $user);
        
        return redirect()->intended($this->redirectPath());
    }

    /**
     * Get the parameters to be sent with the view.
     *
     * @return array
     */
    protected function getViewParams(): array
    {
        return [
            'reason' => session('2fa:auth:reason'),
        ];
    }

    /**
     * Get the redirect path after successful login.
     *
     * @return string
     */
    protected function redirectPath()
    {
        $reflection = new ReflectionClass(LoginController::class);
        $loginController = $reflection->newInstanceWithoutConstructor();
        
        return $loginController->redirectPath();
    }
    
    /**
     * Get the Autenticable model.
     *
     * @return string
     */
    protected function getAutenticableModel()
    {
        $guard    = config('auth.defaults.guard');
        $provider = config('auth.guards.' . $guard . '.provider');
        $model    = config('auth.providers.' . $provider . '.model');

        return $model;
    }
}
