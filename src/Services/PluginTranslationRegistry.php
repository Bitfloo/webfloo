<?php

declare(strict_types=1);

namespace Webfloo\Services;

class PluginTranslationRegistry
{
    /** @var array<string, string> namespace => lang directory path */
    private array $paths = [];

    public function register(string $namespace, string $langPath): void
    {
        $this->paths[$namespace] = $langPath;
    }

    /**
     * @return array<string, string>
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * Load all plugin translations for a locale, prefixed with namespace::
     *
     * @return array<string, string>
     */
    public function getForLocale(string $locale): array
    {
        $translations = [];

        foreach ($this->paths as $namespace => $langPath) {
            $file = rtrim($langPath, '/')."/{$locale}.json";
            if (! file_exists($file)) {
                continue;
            }

            $content = file_get_contents($file);
            if (! is_string($content)) {
                continue;
            }

            /** @var mixed $decoded */
            $decoded = json_decode($content, true);
            if (! is_array($decoded)) {
                continue;
            }

            foreach ($decoded as $key => $value) {
                if (is_string($key) && is_string($value)) {
                    $translations["{$namespace}::{$key}"] = $value;
                }
            }
        }

        return $translations;
    }
}
