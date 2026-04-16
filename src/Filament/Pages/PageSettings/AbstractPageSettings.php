<?php

namespace Webfloo\Filament\Pages\PageSettings;

use Webfloo\Models\Setting;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use UnitEnum;

/**
 * Base class for all settings pages (page-specific and site-wide).
 *
 * Provides locale-aware getSetting/save and eliminates boilerplate.
 * Children define: icon, sort, labels, slug, shouldRegisterNavigation,
 * mount(), form(), settingsPrefix(), notificationBody(), nonTranslatableKeys().
 *
 * @property-read Schema $form
 */
abstract class AbstractPageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|UnitEnum|null $navigationGroup = 'Strony';

    protected string $view = 'webfloo::filament.pages.settings-page';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    /**
     * Settings key prefix (e.g. 'home', 'blog', 'contact').
     * Empty string means no prefix (keys stored flat).
     */
    abstract protected function settingsPrefix(): string;

    /**
     * Notification body shown after saving.
     */
    abstract protected function notificationBody(): string;

    /**
     * Shield permission name gating access to this settings page.
     *
     * Implementations return the Shield-style permission string
     * (e.g. 'view_home_page_settings'). Used by canAccess() below.
     */
    abstract protected static function getPermissionName(): string;

    /**
     * SSOT permission check for every settings page.
     *
     * Subclasses must NOT override canAccess(); override getPermissionName()
     * instead. This ensures a single, audited gate across all settings pages.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->can(static::getPermissionName()) === true;
    }

    /**
     * Keys that are NOT translatable (files, booleans, numbers).
     * These are saved without locale suffix regardless of current locale.
     *
     * @return list<string>
     */
    protected function nonTranslatableKeys(): array
    {
        return [];
    }

    /**
     * Settings group for storage (e.g. 'pages', 'general').
     * Override for custom group logic per key.
     */
    protected function settingsGroup(string $key): string
    {
        return 'pages';
    }

    /**
     * Extract the short key (without prefix) for nonTranslatableKeys() lookup.
     */
    private function shortKey(string $key): string
    {
        $prefix = $this->settingsPrefix();

        if ($prefix !== '' && str_starts_with($key, $prefix.'.')) {
            return substr($key, strlen($prefix) + 1);
        }

        return $key;
    }

    /**
     * Build the full storage key from a form field key.
     *
     * With prefix 'home': form key 'hero_title' -> 'home.hero_title'
     * With empty prefix: form key 'site_name' -> 'site_name'
     */
    protected function buildStorageKey(string $key): string
    {
        $prefix = $this->settingsPrefix();

        return $prefix !== '' ? "{$prefix}.{$key}" : $key;
    }

    /**
     * Check whether a key is translatable.
     */
    protected function isTranslatable(string $key): bool
    {
        return ! in_array($this->shortKey($key), $this->nonTranslatableKeys(), true);
    }

    /**
     * Read a setting value with locale awareness.
     *
     * For non-fallback locale: tries {key}.{locale} first.
     * For fallback locale (PL): reads base key directly.
     * For non-translatable keys: always reads base key.
     */
    protected function getSetting(string $key, mixed $default = null): mixed
    {
        if (! $this->isTranslatable($key)) {
            return Setting::get($key, $default);
        }

        $locale = app()->getLocale();
        $fallback = is_string($fb = config('app.fallback_locale')) ? $fb : 'pl';

        if ($locale !== $fallback) {
            return Setting::get("{$key}.{$locale}", $default);
        }

        return Setting::get($key, $default);
    }

    /**
     * Save all form data with locale awareness.
     *
     * Translatable keys get .{locale} suffix for non-fallback locales.
     * Non-translatable keys always save to base key.
     */
    public function save(): void
    {
        /** @var array<string, mixed> $data */
        $data = $this->form->getState();

        $locale = app()->getLocale();
        $fallback = is_string($fb = config('app.fallback_locale')) ? $fb : 'pl';

        /**
         * @var string $key
         * @var mixed $value
         */
        foreach ($data as $key => $value) {
            $storageKey = $this->buildStorageKey($key);
            $group = $this->settingsGroup($key);

            if ($locale !== $fallback && $this->isTranslatable($storageKey)) {
                Setting::set("{$storageKey}.{$locale}", $value, $group);
            } else {
                Setting::set($storageKey, $value, $group);
            }
        }

        Notification::make()
            ->title('Zapisano')
            ->body($this->notificationBody())
            ->success()
            ->send();
    }
}
