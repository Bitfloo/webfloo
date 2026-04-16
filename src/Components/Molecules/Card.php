<?php

namespace Webfloo\Components\Molecules;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Card component using FlyonUI semantic classes.
 *
 * @example <x-webfloo-card title="Title" subtitle="Description">Content</x-webfloo-card>
 * @example <x-webfloo-card image="/path.jpg" hoverable>Content</x-webfloo-card>
 */
class Card extends Component
{
    public function __construct(
        public ?string $title = null,
        public ?string $subtitle = null,
        public ?string $image = null,
        public ?string $href = null,
        public bool $hoverable = false,
        public bool $bordered = false,
        public string $padding = 'md',
    ) {}

    public function render(): View
    {
        return view('webfloo::components.molecules.card');
    }

    /**
     * Get FlyonUI card classes based on props.
     */
    public function classes(): string
    {
        $classes = ['card', 'bg-base-100'];

        if ($this->bordered) {
            $classes[] = 'card-bordered';
        } else {
            $classes[] = 'shadow-sm';
        }

        if ($this->hoverable) {
            $classes[] = 'transition-shadow hover:shadow-lg';
        }

        // Padding variant
        $classes[] = match ($this->padding) {
            'sm' => 'card-sm',
            'lg' => 'card-lg',
            'none' => 'card-compact',
            default => '',
        };

        return implode(' ', array_filter($classes));
    }
}
