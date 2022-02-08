<?php

return [

    /*
    |--------------------------------------------------------------------------
    | The 2FA policy.
    |--------------------------------------------------------------------------
    |
    | Here you may specify the 2FA policy. Available policies are :
    |    - 'never'      => 2FA never occurs.
    |    - 'always'     => 2FA must occur at every login.
    |    - 'browser'    => 2FA must occur when the user sign-in from unknown browser.
    |    - 'geoip'      => 2FA must occur when the user sign-in from unknown location.
    |    - 'ip'         => 2FA must occur when the user sign-in from unknown ip address.
    |
    | You may create your own policies by extending the abstract
    | policy class and specifing your new class name here.
    |
    */

    'policy' => [
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
        'policies' => [
            'geoip'   => 'country',  // Can be one of "country", "city".
            'browser' => 30,         // Expiration time in days.
        ],
    ],

];
