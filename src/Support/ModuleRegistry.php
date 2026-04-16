<?php

declare(strict_types=1);

namespace Webfloo\Support;

/**
 * Webfloo Module Registry.
 *
 * Czyta `config/webfloo-modules.php` + sprawdza `webfloo.features.*` flagi
 * żeby zdecydować które moduły są enabled runtime. Używana przez
 * WebflooServiceProvider (commands/migrations) i WebflooPanel (Resources).
 *
 * Nie jest mutowalna — klient wywołuje static methody, które zawsze
 * czytają current config state (flags mogą być zmienione przez host
 * środowisko przed boot).
 */
final class ModuleRegistry
{
    /**
     * Wszystkie moduły z rejestru (ID → definicja).
     *
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        $modules = config('webfloo-modules', []);

        if (! is_array($modules)) {
            return [];
        }

        $normalized = [];
        foreach ($modules as $id => $definition) {
            if (is_string($id) && is_array($definition)) {
                /** @var array<string, mixed> $definition */
                $normalized[$id] = $definition;
            }
        }

        return $normalized;
    }

    /**
     * Moduły enabled runtime — always-on (feature_flag null) lub
     * z włączonym `webfloo.features.<flag>`.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function enabled(): array
    {
        return array_filter(
            self::all(),
            static fn (array $module): bool => self::isModuleEnabled($module),
        );
    }

    /**
     * @return list<string>
     */
    public static function enabledIds(): array
    {
        return array_keys(self::enabled());
    }

    /**
     * FQN Filament Resources enabled-only.
     *
     * @return list<class-string>
     */
    public static function enabledResources(): array
    {
        return self::collectFromEnabled('resources');
    }

    /**
     * FQN Filament Pages (non-Resource, enabled-only).
     *
     * @return list<class-string>
     */
    public static function enabledPages(): array
    {
        return self::collectFromEnabled('pages');
    }

    /**
     * FQN Filament Widgets enabled-only.
     *
     * @return list<class-string>
     */
    public static function enabledWidgets(): array
    {
        return self::collectFromEnabled('widgets');
    }

    /**
     * FQN Console Commands enabled-only.
     *
     * @return list<class-string>
     */
    public static function enabledCommands(): array
    {
        return self::collectFromEnabled('commands');
    }

    /**
     * @param  array<string, mixed>  $module
     */
    private static function isModuleEnabled(array $module): bool
    {
        $flag = $module['feature_flag'] ?? null;

        if ($flag === null) {
            return true;
        }

        if (! is_string($flag)) {
            return false;
        }

        return (bool) config("webfloo.features.{$flag}", true);
    }

    /**
     * @return list<class-string>
     */
    private static function collectFromEnabled(string $key): array
    {
        $collected = [];

        foreach (self::enabled() as $module) {
            $items = $module[$key] ?? [];
            if (! is_array($items)) {
                continue;
            }

            foreach ($items as $item) {
                if (is_string($item) && class_exists($item)) {
                    $collected[] = $item;
                }
            }
        }

        /** @var list<class-string> $collected */
        return $collected;
    }
}
