<?php

namespace Webfloo\Components\Atoms;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Icon component using Iconify Tabler icons (FlyonUI native).
 *
 * @example <x-webfloo-icon name="check" size="md" />
 * @example <x-webfloo-icon name="arrow-right" size="lg" />
 *
 * Uses Tabler icons: https://tabler.io/icons
 */
class Icon extends Component
{
    /**
     * Mapping from common Heroicon names to Tabler equivalents.
     * This ensures backward compatibility with existing code.
     */
    private const ICON_MAP = [
        // Navigation
        'menu' => 'menu-2',
        'x' => 'x',
        'x-mark' => 'x',
        'chevron-down' => 'chevron-down',
        'chevron-up' => 'chevron-up',
        'chevron-left' => 'chevron-left',
        'chevron-right' => 'chevron-right',
        'arrow-right' => 'arrow-right',
        'arrow-left' => 'arrow-left',
        'arrow-up' => 'arrow-up',
        'arrow-down' => 'arrow-down',

        // Actions
        'check' => 'check',
        'plus' => 'plus',
        'minus' => 'minus',
        'pencil' => 'pencil',
        'trash' => 'trash',
        'eye' => 'eye',
        'eye-slash' => 'eye-off',
        'magnifying-glass' => 'search',
        'search' => 'search',

        // Communication
        'envelope' => 'mail',
        'phone' => 'phone',
        'chat-bubble-left' => 'message',
        'chat-bubble-left-right' => 'messages',

        // Social
        'at-symbol' => 'at',

        // UI
        'bars-3' => 'menu-2',
        'home' => 'home',
        'user' => 'user',
        'users' => 'users',
        'cog-6-tooth' => 'settings',
        'cog' => 'settings',
        'bell' => 'bell',
        'heart' => 'heart',
        'star' => 'star',
        'bookmark' => 'bookmark',
        'folder' => 'folder',
        'document' => 'file',
        'photo' => 'photo',
        'camera' => 'camera',
        'calendar' => 'calendar',
        'clock' => 'clock',
        'map-pin' => 'map-pin',
        'globe-alt' => 'world',
        'link' => 'link',
        'paper-clip' => 'paperclip',
        'clipboard' => 'clipboard',
        'download' => 'download',
        'upload' => 'upload',
        'share' => 'share',
        'printer' => 'printer',

        // Status
        'check-circle' => 'circle-check',
        'x-circle' => 'circle-x',
        'exclamation-circle' => 'alert-circle',
        'exclamation-triangle' => 'alert-triangle',
        'information-circle' => 'info-circle',
        'question-mark-circle' => 'help',

        // Development
        'code-bracket' => 'code',
        'command-line' => 'terminal',
        'cpu-chip' => 'cpu',
        'server' => 'server',
        'cloud' => 'cloud',
        'database' => 'database',
        'wrench' => 'tool',
        'wrench-screwdriver' => 'tool',
        'adjustments-horizontal' => 'adjustments',
        'bolt' => 'bolt',
        'rocket-launch' => 'rocket',
        'light-bulb' => 'bulb',
        'puzzle-piece' => 'puzzle',
        'cube' => 'box',

        // Business
        'building-office' => 'building',
        'building-office-2' => 'building-skyscraper',
        'briefcase' => 'briefcase',
        'chart-bar' => 'chart-bar',
        'chart-pie' => 'chart-pie',
        'currency-dollar' => 'currency-dollar',
        'shopping-cart' => 'shopping-cart',
        'shopping-bag' => 'shopping-bag',
        'credit-card' => 'credit-card',
        'receipt-percent' => 'receipt',
        'banknotes' => 'cash',
        'presentation-chart-line' => 'presentation',
        'clipboard-document-check' => 'clipboard-check',
        'clipboard-document-list' => 'clipboard-list',
        'academic-cap' => 'school',
        'trophy' => 'trophy',
        'fire' => 'flame',
        'sparkles' => 'sparkles',
        'gift' => 'gift',

        // Devices
        'device-phone-mobile' => 'device-mobile',
        'computer-desktop' => 'device-desktop',
        'device-tablet' => 'device-tablet',
        'tv' => 'device-tv',
        'wifi' => 'wifi',
        'signal' => 'antenna-bars-5',

        // Arrows/Direction
        'arrows-pointing-out' => 'arrows-maximize',
        'arrows-pointing-in' => 'arrows-minimize',
        'arrow-path' => 'refresh',
        'arrow-uturn-left' => 'arrow-back-up',
    ];

    public function __construct(
        public string $name,
        public string $size = 'md',
        public string $set = 'tabler',
    ) {}

    public function render(): View
    {
        return view('webfloo::components.atoms.icon');
    }

    /**
     * Get size classes using Tailwind's size utility.
     */
    public function sizeClasses(): string
    {
        return match ($this->size) {
            'xs' => 'size-3',
            'sm' => 'size-4',
            'md' => 'size-5',
            'lg' => 'size-6',
            'xl' => 'size-8',
            '2xl' => 'size-10',
            default => 'size-5',
        };
    }

    /**
     * Get the Iconify class for the icon.
     * Maps Heroicon names to Tabler equivalents for backward compatibility.
     */
    public function iconClass(): string
    {
        // Map old Heroicon names to Tabler
        $iconName = self::ICON_MAP[$this->name] ?? $this->name;

        return "icon-[{$this->set}--{$iconName}]";
    }
}
