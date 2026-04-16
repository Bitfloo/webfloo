<?php

namespace Webfloo\Components\Atoms;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Heading extends Component
{
    public function __construct(
        public int $level = 2,
        public string $size = 'auto',
    ) {}

    public function render(): View
    {
        return view('webfloo::components.atoms.heading');
    }

    public function tag(): string
    {
        return "h{$this->level}";
    }

    public function classes(): string
    {
        $base = 'font-heading font-bold text-base-content';

        if ($this->size !== 'auto') {
            return "$base ".match ($this->size) {
                'sm' => 'text-lg',
                'md' => 'text-xl',
                'lg' => 'text-2xl',
                'xl' => 'text-3xl',
                '2xl' => 'text-4xl',
                '3xl' => 'text-5xl',
                default => 'text-xl',
            };
        }

        return "$base ".match ($this->level) {
            1 => 'text-4xl md:text-5xl lg:text-6xl',
            2 => 'text-3xl md:text-4xl',
            3 => 'text-2xl md:text-3xl',
            4 => 'text-xl md:text-2xl',
            5 => 'text-lg md:text-xl',
            6 => 'text-base md:text-lg',
            default => 'text-xl',
        };
    }
}
