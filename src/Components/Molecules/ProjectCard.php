<?php

namespace Webfloo\Components\Molecules;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ProjectCard extends Component
{
    /**
     * @param  array<int, string>  $technologies
     */
    public function __construct(
        public string $title,
        public ?string $slug = null,
        public ?string $excerpt = null,
        public ?string $image = null,
        public ?string $category = null,
        public array $technologies = [],
        public ?string $url = null,
        public string $linkText = 'Zobacz projekt',
    ) {
        // Build URL from slug if not explicitly provided
        if ($this->url === null && $this->slug !== null) {
            $this->url = '/portfolio/'.$this->slug;
        }
    }

    public function render(): View
    {
        return view('webfloo::components.molecules.project-card');
    }

    public function hasImage(): bool
    {
        return ! empty($this->image);
    }

    public function hasTechnologies(): bool
    {
        return ! empty($this->technologies);
    }

    public function isClickable(): bool
    {
        return ! empty($this->url);
    }

    /**
     * Get technology badge color based on tech name.
     *
     * @return array<string, string>
     */
    public static function getTechColors(): array
    {
        return [
            'Laravel' => 'badge-error',
            'PHP' => 'badge-info',
            'Vue' => 'badge-success',
            'React' => 'badge-info',
            'Tailwind' => 'badge-primary',
            'Filament' => 'badge-warning',
            'Livewire' => 'badge-accent',
            'MySQL' => 'badge-info',
            'PostgreSQL' => 'badge-primary',
            'Redis' => 'badge-error',
            'Docker' => 'badge-info',
            'REST' => 'badge-success',
            'API' => 'badge-success',
            'JavaScript' => 'badge-warning',
            'TypeScript' => 'badge-info',
            'Node' => 'badge-success',
            'Python' => 'badge-warning',
        ];
    }

    /**
     * Get badge color class for a technology.
     */
    public static function getTechColor(string $tech): string
    {
        foreach (self::getTechColors() as $key => $color) {
            if (stripos($tech, $key) !== false) {
                return $color;
            }
        }

        return 'badge-neutral';
    }
}
