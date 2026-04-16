<?php

namespace Webfloo\Components\Atoms;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Badge component using FlyonUI semantic classes.
 *
 * @example <x-webfloo-badge color="success">Active</x-webfloo-badge>
 * @example <x-webfloo-badge color="warning" size="lg">Pending</x-webfloo-badge>
 */
class Badge extends Component
{
    public function __construct(
        public string $color = 'primary',
        public string $size = 'md',
        public bool $soft = true,
    ) {}

    public function render(): View
    {
        return view('webfloo::components.atoms.badge');
    }

    /**
     * Get FlyonUI badge classes based on props.
     */
    public function classes(): string
    {
        $classes = ['badge'];

        // Soft variant (lighter background)
        if ($this->soft) {
            $classes[] = 'badge-soft';
        }

        // Color
        $classes[] = match ($this->color) {
            'primary' => 'badge-primary',
            'secondary' => 'badge-secondary',
            'accent' => 'badge-accent',
            'neutral' => 'badge-neutral',
            'success' => 'badge-success',
            'warning' => 'badge-warning',
            'danger', 'error' => 'badge-error',
            'info' => 'badge-info',
            default => 'badge-neutral',
        };

        // Size
        if ($this->size !== 'md') {
            $classes[] = match ($this->size) {
                'sm' => 'badge-sm',
                'lg' => 'badge-lg',
                default => '',
            };
        }

        return implode(' ', array_filter($classes));
    }
}
