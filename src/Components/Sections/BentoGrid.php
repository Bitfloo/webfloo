<?php

declare(strict_types=1);

namespace Webfloo\Components\Sections;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class BentoGrid extends Component
{
    /**
     * @param  array{value: string|int, label: string, progress?: int}|null  $statsCard
     * @param  array{title: string, subtitle?: string, avatars?: array<int, string>, stat?: string}|null  $teamCard
     * @param  array{title: string, subtitle?: string, image?: string|null}|null  $techCard
     * @param  array{value: string, label: string, image?: string|null}|null  $clientsCard
     * @param  array{logo?: string|null, quote: string}|null  $brandCard
     * @param  array{title: string, data?: array<int, int>}|null  $growthCard
     */
    public function __construct(
        public ?string $title = null,
        public ?string $subtitle = null,
        public ?string $description = null,
        public ?array $statsCard = null,
        public ?array $teamCard = null,
        public ?array $techCard = null,
        public ?array $clientsCard = null,
        public ?array $brandCard = null,
        public ?array $growthCard = null,
    ) {
        // Set sensible defaults for Bitfloo software house context
        $this->statsCard ??= [
            'value' => '50+',
            'label' => 'Projektow',
            'progress' => 85,
        ];

        $this->teamCard ??= [
            'title' => 'Doswiadczony zespol',
            'subtitle' => 'Eksperci w swoich dziedzinach',
            'avatars' => [],
            'stat' => '10+ lat doswiadczenia',
        ];

        $this->techCard ??= [
            'title' => 'Nowoczesne technologie',
            'subtitle' => 'Laravel, React, Vue, Flutter',
        ];

        $this->clientsCard ??= [
            'value' => '98%',
            'label' => 'Zadowolonych klientow',
        ];

        $this->brandCard ??= [
            'quote' => 'Tworzymy oprogramowanie, ktore napedza biznes',
        ];

        $this->growthCard ??= [
            'title' => 'Staly rozwoj',
            'data' => [20, 35, 45, 55, 70, 85, 95],
        ];
    }

    public function render(): View
    {
        return view('webfloo::components.sections.bento-grid');
    }
}
