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

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'biznet_face' => [
        'api_token' => env('BIZNET_FACE_API_TOKEN'),
        'api_url' => env('BIZNET_FACE_API_URL'),
        'gallery_id' => env('BIZNET_FACE_GALLERY_ID'),
    ],

    'admin_contact' => [
        'email' => env('ADMIN_CONTACT_EMAIL', 'admin@company.com'),
        'phone' => env('ADMIN_CONTACT_PHONE', '+62 123-456-7890'),
        'whatsapp' => env('ADMIN_CONTACT_WHATSAPP'),
        'department' => env('ADMIN_CONTACT_DEPARTMENT', 'IT Support / HR'),
        'name' => env('ADMIN_CONTACT_NAME', 'Administrator'),
    ],

];
