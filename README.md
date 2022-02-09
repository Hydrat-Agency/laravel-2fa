# Laravel Two-Factor Authentication

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/srmklive/authy.svg?style=flat-square)](https://packagist.org/packages/srmklive/authy)
[![Total Downloads](https://img.shields.io/packagist/dt/srmklive/authy.svg?style=flat-square)](https://packagist.org/packages/srmklive/authy)

- [Introduction](#introduction)
- [Installation](#installation)
- [Configuration](#configuration)
    - [Built-in](#configuration-builtin)
    - [Custom Notification](#configuration-custom-notification)
    - [Custom Policies](#configuration-custom-policies)
    - [Custom Drivers](#configuration-custom-drivers)
- [Contribute](#contribute)

<a name="introduction"></a>

## Introduction

This package allows you to enable two-factor authentication in your Laravel applications. It stores tokens locally and notify users about their token using mail, SMS or any custom channel. Supports conditionnal two-factor check using known devices, IP addresses or IP locations.

This package is inspired by the [srmklive/laravel-twofactor-authentication](https://github.com/srmklive/laravel-twofactor-authentication) package.  


<a name="installation"></a>

## Installation

1. Use composer to install the package :  

```bash
composer require hydrat-agency/laravel-2fa
```

2. Add the service provider to your `$providers` array in `config/app.php` file like so: 

```php
'providers' => [   
    [...]
    /*
     * Package Service Providers...
     */
    Hydrat\Laravel2FA\Laravel2FAServiceProvider::class,
],
```

3. Run the following command to publish assets :

```bash
php artisan vendor:publish --provider "Hydrat\Laravel2FA\Laravel2FAServiceProvider"
```

This will import 2 files :
 - `config/laravel-2fa.php`
 - `resources/views/auth/2fa/token.blade.php`

4. Run the following command to migrate database :

```bash
php artisan migrate
```

5. Add the following lines in your User model (e.g `App\Models\User.php`)

 - Before the class declaration, add these lines:

```php
use Hydrat\Laravel2FA\TwoFactorAuthenticatable;
use Hydrat\Laravel2FA\Contracts\TwoFactorAuthenticatableContract;
```

 - Alter the class definition to implements the `TwoFactorAuthenticatableContract` contract :

```php
class User extends Authenticatable implements AuthenticatableContract,
                                              AuthorizableContract,
                                              CanResetPasswordContract,
                                              TwoFactorAuthenticatableContract
```

 - Add the `TwoFactorAuthenticatable` trait :

```php
use Authenticatable,
    Authorizable, 
    CanResetPassword, 
    TwoFactorAuthenticatable;
```

6. You need to change the login workflow by adding the `authenticated` method to your `app\Http\Controllers\Auth\LoginController.php` class.

```php
<?php

namespace App\Http\Controllers\Auth;

use Hydrat\Laravel2FA\TwoFactorAuth;

class LoginController extends Controller
{
    /** [...] **/

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        # Trigger 2FA if necessary.
        if (TwoFactorAuth::getDriver()->mustTrigger($request, $user)) {
            return TwoFactorAuth::getDriver()->trigger($request, $user);
        }

        # If not, do the usual job.
        return redirect()->intended($this->redirectPath());
    }      
```

> 🚀 You may also use the shorthand version if you like it most : 

```php
    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        return TwoFactorAuth::getDriver()->maybeTrigger($request, $user) 
                ?: redirect()->intended($this->redirectPath());
    }
```

That's it ! Now you want to change the configurations & the view file.


<a name="configuration"></a>

## Configuration

<a name="configuration-builtin"></a>

### Built-in
// TODO

<a name="configuration-custom-policies"></a>

<a name="configuration-custom-notification"></a>

## Cutom notification

This package uses the laravel [notifications](https://laravel.com/docs/8.x/notifications) system. The built-in notification is `Hydrat\Laravel2FA\Notifications\TwoFactorToken`, which sends the two-factor token to the user via mail.  

You can extend this notification and configure other channels such as [SMS](https://laravel.com/docs/8.x/notifications#sms-notifications) by extending this class :

```php
<?php

namespace App\Notifications;

use Hydrat\Laravel2FA\Notifications\TwoFactorToken as BaseTwoFactorToken;

class TwoFactorToken extends BaseTwoFactorToken
{
    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [
            'nexmo',
        ];
    }

    /**
     * Get the Vonage / SMS representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return NexmoMessage
     */
    public function toNexmo($notifiable)
    {
        return (new NexmoMessage)
                    ->content('Your two-factor token is ' . $this->token)
                    ->from('MYAPP');
    }
}
```

You'll need to change the `laravel-2fa.notification` configuration key to specify your new notification class :  

```php
return [
    [...]
    /*
    |--------------------------------------------------------------------------
    | The 2FA notification containing the token.
    |--------------------------------------------------------------------------
    |
    | Here you may specify an alternative notification to use.
    |
    */

    'notification' => \App\Notifications\TwoFactorToken::class,
];
```

### Custom policies
// TODO

<a name="configuration-custom-drivers"></a>

### Custom driver
// TODO

<a name="contribute"></a>

## Contribute

Feel free to contribute to the package !  
[First contribution guide](https://github.com/firstcontributions/first-contributions/blob/master/README.md)