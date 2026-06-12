<?php

use Webfloo\Sitemap\PageSitemapSource;
use Webfloo\Sitemap\PostSitemapSource;
use Webfloo\Sitemap\ProjectSitemapSource;

return [
    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The fully qualified class name of the User model used by your
    | application. When left null, webfloo_user_model() falls back to
    | the auth provider model (config auth.providers.users.model).
    | Override here when your User model differs from the auth default.
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

        // Public Blade frontend (routes, controllers, page templates).
        // Disabled by default — hosts with their own frontend (e.g. Inertia)
        // keep this off; turnkey client sites opt in.
        'frontend' => false,

        // 301/302 redirects: admin-managed rules served by a 404-only
        // middleware plus automatic redirects on slug renames. Opt-in.
        'redirects' => false,

        // GDPR cookie banner rendered by the frontend layout. Texts come
        // from settings (cookie_consent.* keys); the visitor's decision
        // lands in localStorage("webfloo-cookie-consent"). Opt-in.
        'cookie_consent' => false,

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
    | CRM
    |--------------------------------------------------------------------------
    |
    | Currency code used for lead estimated values (forms, tables, stats,
    | exports). Read via webfloo_currency().
    |
    */
    'crm' => [
        'currency' => 'PLN',
    ],

    /*
    |--------------------------------------------------------------------------
    | Sitemap
    |--------------------------------------------------------------------------
    |
    | providers   — SitemapSource classes queried by sitemap:generate.
    |               Hosts append their own sources or replace the defaults.
    | static_urls — fixed entries (landing pages, custom routes).
    | locales     — hreflang variants; first entry is the unprefixed default
    |               (and x-default), the rest are served under /{locale}.
    |               A single entry disables alternates entirely.
    |
    */
    'sitemap' => [
        'providers' => [
            PageSitemapSource::class,
            PostSitemapSource::class,
            ProjectSitemapSource::class,
        ],
        'static_urls' => [
            ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'weekly'],
        ],
        'locales' => ['pl', 'en'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Scheduler
    |--------------------------------------------------------------------------
    |
    | When enabled, the package registers its recurring commands
    | (sitemap:generate, leads:send-reminders) on Laravel's scheduler.
    | Set to false if the host app manages scheduling itself.
    |
    */
    'schedule' => [
        'enabled' => true,
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
