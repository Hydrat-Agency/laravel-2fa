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


<a name="building-view"></a>

## Building the view

When you published the package assets, a new `resources/views/auth/2fa/token.blade.php` file has been created. It's up to you how you design this page, but you must keep the `token` input name and send the form to the `route('auth.2fa.store')` route. 

You may access a `$reason` variable which tells you why the 2FA auth has been triggered. It's up to you to display it to the user or not.


<a name="configuration"></a>

## Configuration

All configurations are set in the `config/laravel-2fa.php` file which should have been created when you published the package.   

<a name="configuration-builtin"></a>

### Built-in

First of all, you will need to choose which policies applies. A policy job is the check if the two-factor auth must occur, or if it can be skipped (e.g : the browser is known ? skeep the two-factor auth).

The policies are defined in the `policy` key. Rules can be combined, with an order of priority. Each policy is called, and tells the driver if it should trigger the two-factor auth. When a policy requires a two-factor auth, the check stop and its `message()` will be used as the `$reason` in the view (see [Building the view](#building-view) section).   

If none of policies triggers or if the `policy` array is empty, the two-factor authentication is skipped and the user logs in normally.  

```php
return [
    'policy' => [
        'browser',  // first check if we know the browser
        'geoip', // if so, check if we know the user ip location
    ],
];
``` 

Built-in policies are :   

| Policy name  | Description  |
|---|---|
| `always`  | The 2FA always triggers when logging in, no matter what. |
| `browser` | Skip 2FA if we know the browser (using a cookie). |
| `geoip`   | Skip 2FA if we know the IP address location (based on country, region, city or timezone) |
| `ip`      | Skip 2FA if we know the IP address. ⚠️ Be aware that some users has dynamic IP addresses. |


ℹ️ Need to create your own policy ? See [Custom Policies](#configuration-custom-policies) section below.

Some policies has additionnal settings, which are self-documented in the configuration file.  

```php
return [
    /*
    |--------------------------------------------------------------------------
    | The 2FA package options.
    |--------------------------------------------------------------------------
    |
    | Here you may specify the package options, such as policies parameters.
    |
    */

    'options' => [
        # 2FA token lifetime in minutes.
        'token_lifetime' => 10,

        'policies' => [
            # Can be one of "country", "region", "city", "time_zone".
            'geoip'   => 'country',
            
            # Cookie expiration time in minutes (default 30 days).
            'browser' => 30 * 1440,
        ],
    ],
];
```

<a name="configuration-custom-notification"></a>

## Cutom notification

This package uses the laravel [notifications](https://laravel.com/docs/8.x/notifications) system. The built-in notification `TwoFactorToken` sends the two-factor token to the user via mail.  

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

<a name="configuration-custom-policies"></a>

### Custom policies

If you are not satisfied by built-in policies, you may override an existing policy or create you own.  
The simpler way of doing this is by extending the `AbstractPolicy`.  

To change an existing policy, you may directly extend the policy class :  

```php
<?php

namespace App\Auth\Policies;

use Hydrat\Laravel2FA\Policies\AlwaysPolicy as BaseAlwaysPolicy;

class AlwaysPolicy extends BaseAlwaysPolicy
{
    /**
     * The reason sent to the Notification and the frontend view,
     * to tell the user why the 2FA check was triggered.
     *
     * @return string
     */
    public function message(): string
    {
        return $this->message ?: __('the two-factor is activated for everyone');
    }
}
```

Then, change the `mapping` array in the settings :  

```php
return [
    [...]

    'mapping' => [
        [...]
        'always' => \Auth\Policies\AlwaysPolicy::class,
    ],
];
``` 

The `AbstractPolicy` has 3 available properties your may use to build you policy :  

```php
/**
 * @var \Illuminate\Http\Request
 */
protected $request = null;

/**
 * @var \Hydrat\Laravel2FA\Contracts\TwoFactorAuthenticatableContract
 */
protected $user = null;

/**
 * @var \Hydrat\Laravel2FA\Models\LoginAttempt
 */
protected $attempt = null;
```

Creating a policy is also trivial. For example, let's say your user might active 2FA for their account in their settings. You could create a policy which verify if the user activated 2FA, and if so fails the `passes()` method, which result in triggering the 2FA auth :  


```php
<?php

namespace App\Auth\Policies;

use Hydrat\Laravel2FA\Policies\AbstractPolicy;

class TwoFactorActivePolicy extends AbstractPolicy
{
    /**
     * Check that the request passes the policy.
     * If this return false, the 2FA Auth will be triggered.
     *
     * @return bool
     */
    public function passes(): bool
    {
        return $user->hasTwoFactorAuthActive() ? false : true;
    }

    /**
     * The reason sent to the Notification and the frontend view,
     * to tell the user why the 2FA check was triggered.
     *
     * @return string
     */
    public function message(): string
    {
        return $this->message ?: __('your account activated the 2FA auth');
    }
}
```

You may also have different checks which results in different `$reason` messages :  


```php
<?php

namespace App\Auth\Policies;

use Hydrat\Laravel2FA\Policies\AbstractPolicy;

class TwoFactorActivePolicy extends AbstractPolicy
{
    /**
     * Check that the request passes the policy.
     * If this return false, the 2FA Auth will be triggered.
     *
     * @return bool
     */
    public function passes(): bool
    {
        if ($user->hasTwoFactorAuthActive()) {
            $this->message = __('your account activated the 2FA auth');
            return false;
        }
        
        if ($user->didntSpecifyTwoAuthActive()) {
            $this->message = __('2FA auth is activated by default');
            return false;
        }

        if (anyReason()) {
            return false; // will use the default reason used in message() method.
        }

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
        return $this->message ?: __('2FA auth is automatically activated for your account');
    }
}
```

After creating your policy, you may use it in configuration file :  

```php
return [
    'policy' => [
        \Auth\PoliciesTwoFactorActivePolicy::class,
    ],
];
``` 

Event better, you can create a shortname to keep your `policy` array clean !  

```php
return [
    'policy' => [
        'account',
        'browser', // if not activated for the account, will check if the browser is known
    ],

    [...]

    'mapping' => [
        [...]
        'account' => \Auth\Policies\TwoFactorActivePolicy::class,
    ],
];
``` 

<a name="configuration-custom-drivers"></a>

### Custom driver

If you need more flexibility in the process, you can extend the `BaseDriver` class and change its workflow.  

```php
namespace App\Auth\Drivers;

use Hydrat\Laravel2FA\Drivers\BaseDriver;

class CustomDriver extends BaseDriver
{
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
        // custom workflow.
    }
}
```

⚠️ If you wish to build it from scratch, you MUST implement the `TwoFactorDriverContract`.  
Don't forget to update `driver` key in the config file : 


```php
return [
    'driver' => \App\Auth\Drivers\CustomDriver::class;
];
``` 

<a name="contribute"></a>

## Contribute

Feel free to contribute to the package !  
[First contribution guide](https://github.com/firstcontributions/first-contributions/blob/master/README.md)