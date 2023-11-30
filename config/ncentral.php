<?php

return [

    /*
    |--------------------------------------------------------------------------
    | JSON Web Token
    |--------------------------------------------------------------------------
    |
    | The JWT for the user to request a token.
    |
    */

    'jwt' => env('NCENTRAL_JWT'),

    /*
    |--------------------------------------------------------------------------
    | Overrides for the token request
    |--------------------------------------------------------------------------
    |
    | Allow overriding the expiration timeout.
    |
    */

    'override' => [ // TODO: Does not seem to be respected

        /*
        |--------------------------------------------------------------------------
        | Access Token timeout in seconds
        |--------------------------------------------------------------------------
        |
        | The default is 3600.  They system allows a lower number,
        | but not a higher one.
        |
        */

        'access' => env('NCENTRAL_ACCESS_OVERRIDE', 3600),

        /*
        |--------------------------------------------------------------------------
        | Refresh Token timeout in seconds
        |--------------------------------------------------------------------------
        |
        | The default is 90000.  They system allows a lower number,
        | but not a higher one.
        |
        */

        'refresh' => env('NCENTRAL_REFRESH_OVERRIDE', 90000),
    ],

    /*
    |--------------------------------------------------------------------------
    | API URL
    |--------------------------------------------------------------------------
    |
    | The URL to the N-Central resource server
    |
    */

    'url' => env('NCENTRAL_URL'),

];
