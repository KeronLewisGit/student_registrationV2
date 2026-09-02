<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Deployment Token
    |--------------------------------------------------------------------------
    |
    | Token required by the /deploy endpoint. Read via config() rather than
    | env() so it keeps working after `php artisan optimize` caches the
    | configuration (env() returns null once config is cached).
    |
    */

    'token' => env('DEPLOY_TOKEN'),

];
