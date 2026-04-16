<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The fully qualified class name of the User model used by your
    | application. Every host project MUST override this in its own
    | config/webfloo.php (published via `php artisan vendor:publish
    | --tag=webfloo-config`) — the package cannot ship a host-specific
    | namespace. webfloo_user_model() helper throws a RuntimeException
    | if left null.
    |
    */
    'user_model' => null,

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Enable/disable features per project. Set to false to hide
    | the feature from navigation and disable its functionality.
    |
    */
    'features' => [
        'blog' => true,
        'portfolio' => true,
        'services' => true,
        'testimonials' => true,
        'faq' => true,
        'newsletter' => true,
        'crm' => true,
        'menu' => true,

        // Admin-editable inline JS injected on every public page. Disabled
        // by default — stored-XSS surface (admin-authored script runs for
        // all visitors). Enable in host config only if operationally required
        // AND panel access is restricted to trusted super_admins. No CSP
        // ships with the package.
        'custom_js' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Page Settings
    |--------------------------------------------------------------------------
    |
    | Enable/disable individual page settings in the admin panel.
    | Each page has its own settings page for content management.
    |
    */
    'pages' => [
        'home' => true,
        'contact' => true,
    ],

    'settings' => [
        'cache_ttl' => 3600,
    ],

    /*
    |--------------------------------------------------------------------------
    | Theme Configuration (SSOT)
    |--------------------------------------------------------------------------
    |
    | Default values for the theme system. These can be overridden
    | in the admin panel via Theme Settings page.
    |
    */
    'theme' => [
        'base_theme' => 'bitfloo-dark',
        'mode' => 'dark',
        'colors' => [
            'primary' => '#3b82f6',
            'accent' => '#10b981',
        ],
        'style' => [
            'roundness' => 'default', // sharp, default, rounded, pill
            'density' => 'comfortable', // compact, comfortable, spacious
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Secret key for authenticating incoming webhook requests.
    | Set this in your .env file as BITFLOO_WEBHOOK_SECRET
    |
    */
    'webhook_secret' => env('BITFLOO_WEBHOOK_SECRET'),
];
