<?php

namespace Webfloo\Components\Sections;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Testimonials extends Component
{
    /**
     * @param  array<int, array{author: string, content: string, role?: string, avatar?: string, rating?: int}>  $testimonials
     */
    public function __construct(
        public ?string $title = 'Co mówią nasi klienci',
        public ?string $subtitle = null,
        public array $testimonials = [],
    ) {}

    public function render(): View
    {
        return view('webfloo::components.sections.testimonials');
    }

    /**
     * Generate avatar URL for testimonial.
     *
     * @param  array{author: string, avatar?: string}  $testimonial
     */
    public function getAvatarUrl(array $testimonial, int $size = 96): string
    {
        if (! empty($testimonial['avatar'])) {
            return $testimonial['avatar'];
        }

        return 'https://ui-avatars.com/api/?'.http_build_query([
            'name' => $testimonial['author'],
            'background' => '2563eb',
            'color' => 'fff',
            'size' => $size,
            'bold' => 'true',
        ]);
    }
}
