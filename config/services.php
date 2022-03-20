<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'google' => [
        //Id suministrado por google        
        'client_id'     => '1065119762743-gbco8ifhqr8t5fo33qgmra2su6nqijt1.apps.googleusercontent.com', 
        //Secret suministrado por google 
        'client_secret' => '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',
        //Página a la que sera redireccionado el navegador cuando el login se exitoso 
        'redirect'      => 'http://localhost/ventaCartas/public/api/usuarios/google/callback'
     ]
];
