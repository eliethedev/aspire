<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PHPMailer Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration settings for PHPMailer email service.
    | These settings are used by the PHPMailerService to send emails.
    |
    */

    'host' => env('MAIL_HOST', 'smtp.gmail.com'),
    'port' => env('MAIL_PORT', 587),
    'encryption' => env('MAIL_ENCRYPTION', 'tls'),
    'username' => env('MAIL_USERNAME'),
    'password' => env('MAIL_PASSWORD'),
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@aspire.edu'),
        'name' => env('MAIL_FROM_NAME', 'ASPIRE System'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Templates
    |--------------------------------------------------------------------------
    |
    | Configuration for email templates and styling
    |
    */
    'templates' => [
        'logo_url' => env('APP_URL') . '/images/aspire-logo.png',
        'primary_color' => '#1e40af',
        'secondary_color' => '#dc2626',
        'footer_text' => '© 2026 ASPIRE - Department of Education Philippines',
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug Settings
    |--------------------------------------------------------------------------
    |
    | Enable debugging for PHPMailer in development environments
    |
    */
    'debug' => env('APP_DEBUG', false),
    'exceptions' => env('APP_ENV') !== 'production',

    /*
    |--------------------------------------------------------------------------
    | SMTP Options
    |--------------------------------------------------------------------------
    |
    | Additional SMTP options for advanced configuration
    |
    */
    'smtp_options' => [
        'ssl' => [
            'verify_peer' => env('APP_ENV') === 'production',
            'verify_peer_name' => env('APP_ENV') === 'production',
            'allow_self_signed' => env('APP_ENV') !== 'production',
        ],
    ],
];
