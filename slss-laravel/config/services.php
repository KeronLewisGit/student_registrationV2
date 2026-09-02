<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Student Registration Webhook
    |--------------------------------------------------------------------------
    |
    | Shared secret that the external registration form (Elementor) must send
    | with every webhook request, either as an X-Webhook-Token header or a
    | "token" query/body parameter. Requests without a matching token are
    | rejected. Read via config() so it survives `php artisan config:cache`.
    |
    */

    'webhook' => [
        'secret' => env('WEBHOOK_SECRET'),
    ],

];
