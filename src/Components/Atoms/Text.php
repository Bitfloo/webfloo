<?php

namespace Webfloo\Components\Atoms;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Text extends Component
{
    public function __construct(
        public string $size = 'base',
        public string $color = 'default',
        public string $weight = 'normal',
        public bool $lead = false,
    ) {}

    public function render(): View
    {
        return view('webfloo::components.atoms.text');
    }

    public function classes(): string
    {
        $sizes = match ($this->size) {
            'xs' => 'text-xs',
            'sm' => 'text-sm',
            'base' => 'text-base',
            'lg' => 'text-lg',
            'xl' => 'text-xl',
            default => 'text-base',
        };

        $colors = match ($this->color) {
            'default' => 'text-base-content',
            'muted' => 'text-base-content/70',
            'dark' => 'text-base-content',
            'primary' => 'text-primary',
            'white' => 'text-white',
            default => 'text-base-content',
        };

        $weights = match ($this->weight) {
            'light' => 'font-light',
            'normal' => 'font-normal',
            'medium' => 'font-medium',
            'semibold' => 'font-semibold',
            'bold' => 'font-bold',
            default => 'font-normal',
        };

        $lead = $this->lead ? 'leading-relaxed' : '';

        return trim("$sizes $colors $weights $lead");
    }
}
