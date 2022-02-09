<?php

return [

    /*
    |--------------------------------------------------------------------------
    | The 2FA policy.
    |--------------------------------------------------------------------------
    |
    | Here you may specify the 2FA policies.
    | Policies are run in the below order, if any of them don't pass, it will trigger the 2FA check.
    | If the policy array is empty, the check never occurs.
    |
    | Built-in policies are :
    |    - 'always'     => 2FA must occur at every login.
    |    - 'browser'    => 2FA must occur when the user sign-in from unknown browser (using cookie).
    |    - 'geoip'      => 2FA must occur when the user sign-in from unknown location.
    |    - 'ip'         => 2FA must occur when the user sign-in from unknown ip address (carefull of dynamic ips).
    |
    | You may create your own policies by extending the abstract
    | policy class. Please see the package's README file.
    |
    */

    'policy' => [
        // 'always',
        // 'ip',
        'browser',
        'geoip',
    ],


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


    /*
    |--------------------------------------------------------------------------
    | The 2FA driver.
    |--------------------------------------------------------------------------
    |
    | Here you may specify an alternative driver to use.
    | Make sure your driver implements TwoFactorDriverContract or extends the BaseDriver.
    |
    */

    'driver' => \Hydrat\Laravel2FA\Drivers\BaseDriver::class,


    /*
    |--------------------------------------------------------------------------
    | The 2FA notification containing the token.
    |--------------------------------------------------------------------------
    |
    | Here you may specify an alternative notification to use.
    |
    */

    'notification' => \Hydrat\Laravel2FA\Notifications\TwoFactorToken::class,


    /*
    |--------------------------------------------------------------------------
    | The 2FA policies mapping.
    |--------------------------------------------------------------------------
    |
    | This array define which policy class match the shortname
    | specified in `policy` conf key.
    |
    */

    'mapping' => [
        'always'  => \Hydrat\Laravel2FA\Policies\AlwaysPolicy::class,
        'browser' => \Hydrat\Laravel2FA\Policies\BrowserPolicy::class,
        'geoip'   => \Hydrat\Laravel2FA\Policies\GeoipPolicy::class,
        'ip'      => \Hydrat\Laravel2FA\Policies\IpPolicy::class,
    ],

];
