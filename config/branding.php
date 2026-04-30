<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Brand Identity
    |--------------------------------------------------------------------------
    |
    | Configure your brand identity including name, tagline, and logo.
    |
    */

    'name' => env('BRAND_NAME', 'LHDN Middleware'),
    'tagline' => env('BRAND_TAGLINE', 'MyInvois Invoice Submission Platform'),
    'description' => 'Effortlessly submit invoices to LHDN\'s MyInvois platform. Automated, secure, and compliant invoice management for Malaysian businesses.',

    /*
    |--------------------------------------------------------------------------
    | Brand Colors
    |--------------------------------------------------------------------------
    |
    | Define your brand color palette. These colors are used throughout
    | the public-facing pages (landing, auth, etc.). Use Tailwind color names.
    |
    | Available colors: slate, gray, zinc, neutral, stone, red, orange, amber,
    | yellow, lime, green, emerald, teal, cyan, sky, blue, indigo, violet,
    | purple, fuchsia, pink, rose
    |
    */

    'colors' => [
        // Primary brand color (main CTAs, links, accents)
        'primary' => env('BRAND_PRIMARY_COLOR', 'blue'),
        'primary_shade' => env('BRAND_PRIMARY_SHADE', '600'), // 50-950

        // Secondary/accent color (optional highlights)
        'secondary' => env('BRAND_SECONDARY_COLOR', 'indigo'),
        'secondary_shade' => env('BRAND_SECONDARY_SHADE', '600'),

        // Success color
        'success' => env('BRAND_SUCCESS_COLOR', 'green'),
        'success_shade' => env('BRAND_SUCCESS_SHADE', '600'),

        // Warning color
        'warning' => env('BRAND_WARNING_COLOR', 'yellow'),
        'warning_shade' => env('BRAND_WARNING_SHADE', '500'),

        // Danger/error color
        'danger' => env('BRAND_DANGER_COLOR', 'red'),
        'danger_shade' => env('BRAND_DANGER_SHADE', '600'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Social Links
    |--------------------------------------------------------------------------
    |
    | Add your social media links here.
    |
    */

    'social' => [
        'facebook' => env('BRAND_FACEBOOK_URL'),
        'twitter' => env('BRAND_TWITTER_URL'),
        'linkedin' => env('BRAND_LINKEDIN_URL'),
        'instagram' => env('BRAND_INSTAGRAM_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Contact Information
    |--------------------------------------------------------------------------
    */

    'contact' => [
        'email' => env('BRAND_CONTACT_EMAIL', 'support@lhdn-middleware.com'),
        'phone' => env('BRAND_CONTACT_PHONE', '+60 3-1234 5678'),
        'address' => env('BRAND_CONTACT_ADDRESS', 'Kuala Lumpur, Malaysia'),
    ],

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    */

    'urls' => [
        'terms' => env('BRAND_TERMS_URL', '#'),
        'privacy' => env('BRAND_PRIVACY_URL', '#'),
        'documentation' => env('BRAND_DOCS_URL', '#'),
    ],
];
