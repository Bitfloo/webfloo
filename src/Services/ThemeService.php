<?php

declare(strict_types=1);

namespace Webfloo\Services;

use Illuminate\Support\Facades\Cache;
use Webfloo\Models\Setting;

/**
 * Theme configuration service - SSOT for visual settings.
 *
 * Handles color conversion (HEX → OKLCH), palette derivation,
 * and CSS variable generation for the frontend.
 */
class ThemeService
{
    /**
     * Default theme configuration.
     *
     * @var array<string, mixed>
     */
    protected array $defaults = [
        'base_theme' => 'bitfloo-dark',
        'mode' => 'dark',
        'colors' => [
            'primary' => '#3b82f6',
            'accent' => '#10b981',
        ],
        'style' => [
            'roundness' => 'default',
            'density' => 'comfortable',
        ],
        'custom' => [
            'css' => '',
            'js' => '',
        ],
    ];

    /**
     * Roundness presets mapping to CSS values.
     *
     * @var array<string, array<string, string>>
     */
    protected array $roundnessPresets = [
        'sharp' => [
            '--radius-box' => '0',
            '--radius-field' => '0',
            '--radius-selector' => '0',
            '--radius-btn' => '0',
            '--radius-badge' => '0',
        ],
        'default' => [
            '--radius-box' => '0.5rem',
            '--radius-field' => '0.5rem',
            '--radius-selector' => '0.375rem',
            '--radius-btn' => '0.375rem',
            '--radius-badge' => '9999px',
        ],
        'rounded' => [
            '--radius-box' => '1rem',
            '--radius-field' => '0.75rem',
            '--radius-selector' => '0.5rem',
            '--radius-btn' => '0.75rem',
            '--radius-badge' => '9999px',
        ],
        'pill' => [
            '--radius-box' => '9999px',
            '--radius-field' => '9999px',
            '--radius-selector' => '9999px',
            '--radius-btn' => '9999px',
            '--radius-badge' => '9999px',
        ],
    ];

    /**
     * Density presets mapping to CSS values.
     *
     * @var array<string, array<string, string>>
     */
    protected array $densityPresets = [
        'compact' => [
            '--btn-padding-x' => '1rem',
            '--btn-padding-y' => '0.5rem',
            '--input-min-height' => '2.25rem',
            '--input-padding-x' => '0.75rem',
            '--input-padding-y' => '0.5rem',
            '--size-selector' => '0.2rem',
            '--size-field' => '0.2rem',
        ],
        'comfortable' => [
            '--btn-padding-x' => '1.25rem',
            '--btn-padding-y' => '0.75rem',
            '--input-min-height' => '2.75rem',
            '--input-padding-x' => '1rem',
            '--input-padding-y' => '0.75rem',
            '--size-selector' => '0.25rem',
            '--size-field' => '0.25rem',
        ],
        'spacious' => [
            '--btn-padding-x' => '1.5rem',
            '--btn-padding-y' => '1rem',
            '--input-min-height' => '3.25rem',
            '--input-padding-x' => '1.25rem',
            '--input-padding-y' => '1rem',
            '--size-selector' => '0.3rem',
            '--size-field' => '0.3rem',
        ],
    ];

    /**
     * Design tokens (constant across all settings).
     *
     * @var array<string, string>
     */
    protected array $designTokens = [
        '--border' => '1px',
        '--depth' => '1',
        '--noise' => '0',
    ];

    /** @var array<string, mixed>|null Memoized config for current request */
    protected ?array $resolvedConfig = null;

    /**
     * Get the current theme configuration (memoized per request).
     *
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        if ($this->resolvedConfig !== null) {
            return $this->resolvedConfig;
        }

        $config = Setting::get('theme_config');

        if (! is_array($config)) {
            $this->resolvedConfig = $this->defaults;

            return $this->resolvedConfig;
        }

        /** @var array<string, mixed> $merged */
        $merged = array_replace_recursive($this->defaults, $config);
        $this->resolvedConfig = $merged;

        return $this->resolvedConfig;
    }

    /**
     * Save theme configuration.
     *
     * @param  array<string, mixed>  $config
     */
    public function saveConfig(array $config): void
    {
        $merged = array_replace_recursive($this->defaults, $config);
        Setting::set('theme_config', $merged, 'appearance');
        $this->resolvedConfig = null;
        $this->clearCache();
    }

    /**
     * Get the base theme name.
     */
    public function getBaseTheme(): string
    {
        $config = $this->getConfig();

        return is_string($config['base_theme']) ? $config['base_theme'] : 'bitfloo-dark';
    }

    /**
     * Generate CSS variables string for injection into layout.
     */
    public function generateCssVariables(): string
    {
        $result = Cache::remember('theme_css_variables', 3600, function (): string {
            return $this->buildCssVariables();
        });

        return $result;
    }

    /**
     * Build CSS variables from current configuration.
     */
    protected function buildCssVariables(): string
    {
        $config = $this->getConfig();
        $variables = [];

        // Determine mode (light/dark)
        $mode = is_string($config['mode'] ?? null) ? $config['mode'] : 'dark';
        $isDark = $mode === 'dark' || ($mode === 'auto');

        // Color variables
        $colors = is_array($config['colors'] ?? null) ? $config['colors'] : [];
        /** @var array{primary: string, accent: string} $defaultColors */
        $defaultColors = $this->defaults['colors'];

        if (is_array($config['colors'] ?? null)) {
            $primary = is_string($colors['primary'] ?? null)
                ? $colors['primary']
                : $defaultColors['primary'];
            $accent = is_string($colors['accent'] ?? null)
                ? $colors['accent']
                : $defaultColors['accent'];

            // Primary color and derived colors
            $variables['--color-primary'] = $this->hexToOklch($primary);
            $variables['--color-primary-content'] = $this->calculateContrastColor($primary);

            // Accent color
            $variables['--color-accent'] = $this->hexToOklch($accent);
            $variables['--color-accent-content'] = $this->calculateContrastColor($accent);

            // Derived colors from primary
            $variables['--color-secondary'] = $this->deriveSecondary($primary);
            $variables['--color-secondary-content'] = 'oklch(100% 0 0)';
            $variables['--color-neutral'] = $this->deriveNeutral($primary);
            $variables['--color-neutral-content'] = 'oklch(95% 0.005 260)';

            // Base colors (background/text) - derived from mode and primary hue
            $primaryOklch = $this->hexToOklchComponents($primary);
            $hue = round($primaryOklch['h']);

            if ($isDark) {
                $variables['--color-base-100'] = "oklch(20% 0.025 {$hue})";
                $variables['--color-base-200'] = "oklch(16% 0.02 {$hue})";
                $variables['--color-base-300'] = "oklch(12% 0.015 {$hue})";
                $variables['--color-base-content'] = 'oklch(95% 0.005 260)';
            } else {
                $variables['--color-base-100'] = 'oklch(100% 0 0)';
                $variables['--color-base-200'] = "oklch(97.8% 0.005 {$hue})";
                $variables['--color-base-300'] = "oklch(94% 0.01 {$hue})";
                $variables['--color-base-content'] = 'oklch(25% 0.02 260)';
            }

            // Semantic colors - standard values (rarely brand-specific)
            // Info - blue tones
            $variables['--color-info'] = $isDark ? 'oklch(68% 0.14 235)' : 'oklch(65% 0.15 235)';
            $variables['--color-info-content'] = 'oklch(100% 0 0)';

            // Success - green tones
            $variables['--color-success'] = $isDark ? 'oklch(72% 0.17 150)' : 'oklch(70% 0.17 150)';
            $variables['--color-success-content'] = 'oklch(100% 0 0)';

            // Warning - yellow/orange tones
            $variables['--color-warning'] = $isDark ? 'oklch(78% 0.15 85)' : 'oklch(80% 0.16 85)';
            $variables['--color-warning-content'] = 'oklch(20% 0 0)';

            // Error - red tones
            $variables['--color-error'] = $isDark ? 'oklch(62% 0.22 25)' : 'oklch(60% 0.22 25)';
            $variables['--color-error-content'] = 'oklch(100% 0 0)';
        }

        // Style variables
        if (isset($config['style']) && is_array($config['style'])) {
            $roundness = is_string($config['style']['roundness'] ?? null)
                ? $config['style']['roundness']
                : 'default';
            $density = is_string($config['style']['density'] ?? null)
                ? $config['style']['density']
                : 'comfortable';

            // Roundness
            if (isset($this->roundnessPresets[$roundness])) {
                $variables = array_merge($variables, $this->roundnessPresets[$roundness]);
            }

            // Density
            if (isset($this->densityPresets[$density])) {
                $variables = array_merge($variables, $this->densityPresets[$density]);
            }
        }

        // Design tokens (constant)
        $variables = array_merge($variables, $this->designTokens);

        // Build CSS string
        $css = ":root {\n";
        foreach ($variables as $name => $value) {
            $css .= "    {$name}: {$value};\n";
        }
        $css .= '}';

        return $css;
    }

    /**
     * Convert HEX color to OKLCH format.
     *
     * @param  string  $hex  HEX color (e.g., #3b82f6 or 3b82f6)
     * @return string OKLCH color string (e.g., oklch(60% 0.2 264))
     */
    public function hexToOklch(string $hex): string
    {
        $hex = ltrim($hex, '#');
        if (! preg_match('/^[0-9a-fA-F]{3,6}$/', $hex)) {
            return 'oklch(50% 0.15 264)';
        }

        return $this->formatOklch($this->hexToOklchComponents($hex));
    }

    /**
     * Derive secondary color from primary (reduced chroma).
     */
    public function deriveSecondary(string $primaryHex): string
    {
        $oklch = $this->hexToOklchComponents($primaryHex);

        // Reduce chroma significantly, keep hue
        $oklch['c'] = max(0.02, $oklch['c'] * 0.3);
        $oklch['l'] = min(0.55, $oklch['l']);

        return $this->formatOklch($oklch);
    }

    /**
     * Derive neutral color from primary (minimal chroma).
     */
    public function deriveNeutral(string $primaryHex): string
    {
        $oklch = $this->hexToOklchComponents($primaryHex);

        // Near-zero chroma, same hue family
        $oklch['c'] = 0.025;
        $oklch['l'] = 0.15;

        return $this->formatOklch($oklch);
    }

    /**
     * Calculate the best contrast color (white or dark) for text on a background.
     */
    public function calculateContrastColor(string $backgroundHex): string
    {
        $luminance = $this->getRelativeLuminance($backgroundHex);

        return $luminance > 0.4 ? 'oklch(20% 0 0)' : 'oklch(100% 0 0)';
    }

    /**
     * Check contrast ratio between two colors (WCAG).
     *
     * @return float Contrast ratio (1-21)
     */
    public function checkContrast(string $foregroundHex, string $backgroundHex): float
    {
        $fgLuminance = $this->getRelativeLuminance($foregroundHex);
        $bgLuminance = $this->getRelativeLuminance($backgroundHex);

        $lighter = max($fgLuminance, $bgLuminance);
        $darker = min($fgLuminance, $bgLuminance);

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    /**
     * Check if contrast meets WCAG AA standard (4.5:1 for normal text).
     */
    public function meetsWcagAA(string $foregroundHex, string $backgroundHex): bool
    {
        return $this->checkContrast($foregroundHex, $backgroundHex) >= 4.5;
    }

    /**
     * Get available base themes.
     *
     * @return array<string, string>
     */
    public function getAvailableThemes(): array
    {
        return [
            'bitfloo-dark' => 'Bitfloo Dark (domyślny)',
            'bitfloo' => 'Bitfloo Light',
            'dark' => 'FlyonUI Dark',
            'light' => 'FlyonUI Light',
            'corporate' => 'Corporate',
            'luxury' => 'Luxury',
            'soft' => 'Soft',
            'gourmet' => 'Gourmet',
        ];
    }

    /**
     * Get roundness options.
     *
     * @return array<string, string>
     */
    public function getRoundnessOptions(): array
    {
        return [
            'sharp' => 'Ostre (0)',
            'default' => 'Domyślne (0.5rem)',
            'rounded' => 'Zaokrąglone (1rem)',
            'pill' => 'Pigułka (max)',
        ];
    }

    /**
     * Get density options.
     *
     * @return array<string, string>
     */
    public function getDensityOptions(): array
    {
        return [
            'compact' => 'Kompaktowe',
            'comfortable' => 'Komfortowe',
            'spacious' => 'Przestronne',
        ];
    }

    /**
     * Get mode options.
     *
     * @return array<string, string>
     */
    public function getModeOptions(): array
    {
        return [
            'light' => 'Jasny',
            'dark' => 'Ciemny',
            'auto' => 'Auto (systemowy)',
        ];
    }

    /**
     * Get default configuration.
     *
     * @return array<string, mixed>
     */
    public function getDefaults(): array
    {
        return $this->defaults;
    }

    /**
     * Clear cached CSS variables.
     */
    public function clearCache(): void
    {
        Cache::forget('theme_css_variables');
    }

    /**
     * Get custom CSS code (sanitized).
     */
    public function getCustomCss(): string
    {
        $config = $this->getConfig();
        $custom = is_array($config['custom'] ?? null) ? $config['custom'] : [];
        $css = $custom['css'] ?? '';

        if (! is_string($css) || $css === '') {
            return '';
        }

        // Sanitize: remove potential script injections and CSS attack vectors
        $css = preg_replace('/<\s*script/i', '&lt;script', $css) ?? $css;
        $css = preg_replace('/expression\s*\(/i', 'expression-disabled(', $css) ?? $css;
        $css = preg_replace('/javascript\s*:/i', 'javascript-disabled:', $css) ?? $css;
        // Strip </style> to prevent breaking out of <style> block
        $css = preg_replace('/<\s*\/\s*style\s*>/i', '&lt;/style&gt;', $css) ?? $css;
        // Remove @import to prevent external resource loading
        $css = preg_replace('/@import\b/i', '/* @import-blocked */', $css) ?? $css;
        // Block behavior: (IE expression vector)
        $css = preg_replace('/behavior\s*:/i', 'behavior-disabled:', $css) ?? $css;
        // Block -moz-binding: (Firefox XBL vector)
        $css = preg_replace('/-moz-binding\s*:/i', '-moz-binding-disabled:', $css) ?? $css;

        return $css;
    }

    /**
     * Get custom JavaScript code.
     *
     * Defense in depth: the `bitfloo.features.custom_js` flag MUST be enabled
     * for any saved JS to be rendered. If the flag is off, we return an empty
     * string regardless of what's in settings. This prevents accidental XSS
     * exposure when a host flips off the feature without purging the DB.
     */
    public function getCustomJs(): string
    {
        if (! config('webfloo.features.custom_js', false)) {
            return '';
        }

        $config = $this->getConfig();
        $custom = is_array($config['custom'] ?? null) ? $config['custom'] : [];
        $js = $custom['js'] ?? '';

        if (! is_string($js) || $js === '') {
            return '';
        }

        // Closing-tag escape only. Full sanitization is the admin's responsibility
        // — enabling the feature is an explicit acknowledgment of the XSS surface.
        $js = str_replace('</script>', '<\/script>', $js);

        return $js;
    }

    /**
     * Validate HEX color format.
     */
    public function isValidHex(string $hex): bool
    {
        $hex = ltrim($hex, '#');

        return (bool) preg_match('/^[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/', $hex);
    }

    /**
     * Convert sRGB component to linear RGB.
     */
    protected function srgbToLinear(float $value): float
    {
        return $value <= 0.04045
            ? $value / 12.92
            : pow(($value + 0.055) / 1.055, 2.4);
    }

    /**
     * Cube root that handles negative numbers.
     */
    protected function cbrt(float $value): float
    {
        return $value < 0 ? -pow(-$value, 1 / 3) : pow($value, 1 / 3);
    }

    /**
     * Get OKLCH components from HEX.
     *
     * @return array{l: float, c: float, h: float}
     */
    protected function hexToOklchComponents(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        $r = $this->srgbToLinear($r);
        $g = $this->srgbToLinear($g);
        $b = $this->srgbToLinear($b);

        $x = 0.4124564 * $r + 0.3575761 * $g + 0.1804375 * $b;
        $y = 0.2126729 * $r + 0.7151522 * $g + 0.0721750 * $b;
        $z = 0.0193339 * $r + 0.1191920 * $g + 0.9503041 * $b;

        $l_ = $this->cbrt(0.8189330101 * $x + 0.3618667424 * $y - 0.1288597137 * $z);
        $m_ = $this->cbrt(0.0329845436 * $x + 0.9293118715 * $y + 0.0361456387 * $z);
        $s_ = $this->cbrt(0.0482003018 * $x + 0.2643662691 * $y + 0.6338517070 * $z);

        $L = 0.2104542553 * $l_ + 0.7936177850 * $m_ - 0.0040720468 * $s_;
        $a = 1.9779984951 * $l_ - 2.4285922050 * $m_ + 0.4505937099 * $s_;
        $okB = 0.0259040371 * $l_ + 0.7827717662 * $m_ - 0.8086757660 * $s_;

        $C = sqrt($a * $a + $okB * $okB);
        $H = atan2($okB, $a) * 180 / M_PI;
        if ($H < 0) {
            $H += 360;
        }

        return ['l' => $L, 'c' => $C, 'h' => $H];
    }

    /**
     * Format OKLCH components to CSS string.
     *
     * @param  array{l: float, c: float, h: float}  $oklch
     */
    protected function formatOklch(array $oklch): string
    {
        $l = round($oklch['l'] * 100, 1);
        $c = round($oklch['c'], 3);
        $h = round($oklch['h'], 0);

        return "oklch({$l}% {$c} {$h})";
    }

    /**
     * Get relative luminance of a color for WCAG calculations.
     */
    protected function getRelativeLuminance(string $hex): float
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        return 0.2126 * $this->srgbToLinear($r)
             + 0.7152 * $this->srgbToLinear($g)
             + 0.0722 * $this->srgbToLinear($b);
    }
}
