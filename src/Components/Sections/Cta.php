<?php

namespace Webfloo\Components\Sections;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Cta extends Component
{
    /**
     * @param  array{text: string, href: string}|null  $primaryCta
     * @param  array{text: string, href: string}|null  $secondaryCta
     */
    public function __construct(
        public string $title,
        public ?string $subtitle = null,
        public ?string $description = null,
        public ?array $primaryCta = null,
        public ?array $secondaryCta = null,
        public string $variant = 'gradient',
        public ?string $backgroundImage = null,
        public string $alignment = 'center',
        public bool $showAmbientBg = false,
        public string $ambientVariant = 'aurora',
    ) {}

    public function render(): View
    {
        return view('webfloo::components.sections.cta');
    }

    public function backgroundClasses(): string
    {
        return match ($this->variant) {
            'gradient' => 'bg-gradient-to-r from-primary to-accent',
            'solid' => 'bg-primary',
            'image' => '',
            default => 'bg-gradient-to-r from-primary to-accent',
        };
    }

    public function alignmentClasses(): string
    {
        return match ($this->alignment) {
            'left' => 'text-left items-start',
            'center' => 'text-center items-center',
            default => 'text-center items-center',
        };
    }

    public function hasImageBackground(): bool
    {
        return $this->variant === 'image' && $this->backgroundImage !== null;
    }
}
