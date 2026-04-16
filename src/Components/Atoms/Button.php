<?php

namespace Webfloo\Components\Atoms;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Button component using FlyonUI semantic classes.
 *
 * @example <x-webfloo-button variant="primary" size="lg">Click me</x-webfloo-button>
 * @example <x-webfloo-button variant="outline" href="/contact">Contact</x-webfloo-button>
 */
class Button extends Component
{
    public function __construct(
        public string $variant = 'primary',
        public string $size = 'md',
        public ?string $href = null,
        public string $type = 'button',
        public bool $disabled = false,
        public bool $loading = false,
    ) {}

    public function render(): View
    {
        return view('webfloo::components.atoms.button');
    }

    /**
     * Get FlyonUI button classes based on props.
     */
    public function classes(): string
    {
        $classes = ['btn'];

        // Variant
        $classes[] = match ($this->variant) {
            'primary' => 'btn-primary',
            'secondary' => 'btn-secondary',
            'accent' => 'btn-accent',
            'outline' => 'btn-outline btn-primary',
            'ghost' => 'btn-text btn-primary',
            'neutral' => 'btn-neutral',
            'info' => 'btn-info',
            'success' => 'btn-success',
            'warning' => 'btn-warning',
            'error' => 'btn-error',
            default => 'btn-primary',
        };

        // Size
        if ($this->size !== 'md') {
            $classes[] = match ($this->size) {
                'xs' => 'btn-xs',
                'sm' => 'btn-sm',
                'lg' => 'btn-lg',
                'xl' => 'btn-xl',
                default => '',
            };
        }

        return implode(' ', array_filter($classes));
    }
}
