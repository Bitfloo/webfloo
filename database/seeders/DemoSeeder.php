<?php

declare(strict_types=1);

namespace Webfloo\Database\Seeders;

use Illuminate\Database\Seeder;
use Webfloo\Models\Faq;
use Webfloo\Models\MenuItem;
use Webfloo\Models\Page;
use Webfloo\Models\Service;
use Webfloo\Models\Testimonial;
use Webfloo\Support\ModuleRegistry;

/**
 * Generic demo content for a fresh client install: home/about pages,
 * header menu and one or two sample records per enabled module.
 * Idempotent — skips entirely when the demo home page already exists.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (Page::query()->where('slug', 'home')->exists()) {
            return;
        }

        $this->seedPages();
        $this->seedMenu();

        if (ModuleRegistry::isEnabled('services')) {
            $this->seedServices();
        }

        if (ModuleRegistry::isEnabled('faq')) {
            $this->seedFaqs();
        }

        if (ModuleRegistry::isEnabled('testimonials')) {
            $this->seedTestimonials();
        }
    }

    protected function seedPages(): void
    {
        Page::query()->create([
            'title' => ['pl' => 'Strona glowna', 'en' => 'Home'],
            'slug' => 'home',
            'template' => 'home',
            'status' => 'published',
            'published_at' => now(),
        ]);

        Page::query()->create([
            'title' => ['pl' => 'O nas', 'en' => 'About us'],
            'slug' => 'o-nas',
            'template' => 'about',
            'status' => 'published',
            'published_at' => now(),
        ]);

        Page::query()->create([
            'title' => ['pl' => 'Kontakt', 'en' => 'Contact'],
            'slug' => 'kontakt',
            'template' => 'contact',
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    protected function seedMenu(): void
    {
        $items = [
            ['label' => ['pl' => 'Start', 'en' => 'Home'], 'href' => '/', 'sort_order' => 0],
            ['label' => ['pl' => 'O nas', 'en' => 'About'], 'href' => '/o-nas', 'sort_order' => 1],
            ['label' => ['pl' => 'Kontakt', 'en' => 'Contact'], 'href' => '/kontakt', 'sort_order' => 2],
        ];

        foreach ($items as $item) {
            MenuItem::query()->create($item + [
                'location' => MenuItem::LOCATION_HEADER,
                'is_active' => true,
            ]);
        }
    }

    protected function seedServices(): void
    {
        $services = [
            [
                'title' => 'Przykladowa usluga',
                'description' => 'Krotki opis pierwszej uslugi. Tresc edytujesz w panelu administracyjnym.',
                'icon' => 'code-bracket',
            ],
            [
                'title' => 'Druga usluga',
                'description' => 'Krotki opis drugiej uslugi. Tresc edytujesz w panelu administracyjnym.',
                'icon' => 'wrench-screwdriver',
            ],
        ];

        foreach ($services as $index => $service) {
            Service::query()->create($service + ['is_active' => true, 'sort_order' => $index]);
        }
    }

    protected function seedFaqs(): void
    {
        $faqs = [
            [
                'question' => 'Jak edytowac tresci na stronie?',
                'answer' => 'Zaloguj sie do panelu administracyjnego pod adresem /admin i wybierz odpowiednia sekcje.',
            ],
            [
                'question' => 'Jak dodac nowa podstrone?',
                'answer' => 'W panelu administracyjnym przejdz do sekcji Strony i kliknij Utworz.',
            ],
        ];

        foreach ($faqs as $index => $faq) {
            Faq::query()->create($faq + ['is_active' => true, 'sort_order' => $index]);
        }
    }

    protected function seedTestimonials(): void
    {
        Testimonial::query()->create([
            'content' => 'Przykladowa opinia klienta. Edytuj lub usun ja w panelu administracyjnym.',
            'author' => 'Jan Przykladowy',
            'role' => 'CEO',
            'company' => 'Firma Demo',
            'rating' => 5,
            'is_active' => true,
            'sort_order' => 0,
        ]);
    }
}
