<?php

use Webfloo\Models\Setting;
use Illuminate\Foundation\Auth\User as AuthUser;

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        $locale = app()->getLocale();
        $fallback = config('app.fallback_locale', 'pl');

        // Try locale-specific key first (e.g. home.hero_title.en)
        if ($locale !== $fallback) {
            $localized = Setting::get("{$key}.{$locale}");
            if ($localized !== null) {
                return $localized;
            }
        }

        // Fall back to base key (existing PL data)
        return Setting::get($key, $default);
    }
}

if (! function_exists('webfloo_user_model')) {
    /**
     * Resolve the host app User model class for relations in core models.
     *
     * Host User model must extend Illuminate\Foundation\Auth\User so that
     * BelongsTo<User, ...> PHPDocs in Post/Lead/LeadActivity/LeadReminder
     * type-check cleanly against the returned class-string.
     *
     * @return class-string<AuthUser>
     */
    function webfloo_user_model(): string
    {
        $model = config('webfloo.user_model');

        if (! is_string($model) || ! class_exists($model)) {
            throw new RuntimeException(
                'webfloo.user_model must be a class-string of an Eloquent User model. '
                .'Set it in config/webfloo.php (published via `php artisan vendor:publish --tag=webfloo-config`).'
            );
        }

        if (! is_subclass_of($model, AuthUser::class)) {
            throw new RuntimeException(
                "webfloo.user_model [{$model}] must extend Illuminate\\Foundation\\Auth\\User."
            );
        }

        /** @var class-string<AuthUser> $model */
        return $model;
    }
}
