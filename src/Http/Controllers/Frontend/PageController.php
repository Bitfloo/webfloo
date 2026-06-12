<?php

declare(strict_types=1);

namespace Webfloo\Http\Controllers\Frontend;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Webfloo\Models\Faq;
use Webfloo\Models\Page;
use Webfloo\Models\Project;
use Webfloo\Models\Service;
use Webfloo\Models\Testimonial;
use Webfloo\Support\ModuleRegistry;

class PageController extends FrontendController
{
    public function home(): View
    {
        /** @var Page|null $page */
        $page = Page::query()->published()->withParentChain()->byTemplate('home')->first()
            ?? Page::query()->published()->withParentChain()->where('slug', 'home')->first();

        if ($page === null) {
            $this->abortNotFound();
        }

        return $this->renderPage($page);
    }

    /**
     * Fallback route handler: resolves nested page paths like /services/web.
     */
    public function show(Request $request): View
    {
        $path = trim($request->path(), '/');

        if ($path === '') {
            $this->abortNotFound();
        }

        $segments = explode('/', $path);
        $slug = (string) end($segments);

        /** @var Page|null $page */
        $page = Page::query()->published()->withParentChain()->where('slug', $slug)->first();

        // Slug must resolve AND the requested path must match the page's
        // canonical URL (a nested page is not served at its bare slug).
        if ($page === null || trim($page->url, '/') !== $path) {
            $this->abortNotFound();
        }

        return $this->renderPage($page);
    }

    public function robots(): Response
    {
        $lines = ['User-agent: *', 'Allow: /'];

        if (file_exists(public_path('sitemap.xml'))) {
            $lines[] = 'Sitemap: '.url('/sitemap.xml');
        }

        return response(implode("\n", $lines), 200, ['Content-Type' => 'text/plain']);
    }

    protected function renderPage(Page $page): View
    {
        $template = array_key_exists($page->template, Page::TEMPLATES) ? $page->template : 'default';
        $view = "webfloo::frontend.pages.{$template}";

        if (! view()->exists($view)) {
            $view = 'webfloo::frontend.pages.default';
        }

        return view($view, ['page' => $page] + $this->templateData($template));
    }

    /**
     * Template-specific data for section components (they take props,
     * they do not self-fetch).
     *
     * @return array<string, mixed>
     */
    protected function templateData(string $template): array
    {
        return match ($template) {
            'home' => [
                'services' => $this->services(),
                'testimonials' => $this->testimonials(),
                'faqs' => $this->faqs(),
                'projects' => $this->projects(),
            ],
            'services' => ['services' => $this->services()],
            'about' => ['testimonials' => $this->testimonials()],
            default => [],
        };
    }

    /**
     * @return array<int, array{title: string, description: string|null, icon: string|null, href: string|null}>
     */
    protected function services(): array
    {
        if (! ModuleRegistry::isEnabled('services')) {
            return [];
        }

        return Service::query()->active()->ordered()->get()
            ->map(fn (Service $service): array => [
                'title' => (string) $service->title,
                'description' => $service->description,
                'icon' => $service->icon,
                'href' => $service->href,
            ])->all();
    }

    /**
     * @return array<int, array{content: string, author: string, role: string|null, rating: int|null}>
     */
    protected function testimonials(): array
    {
        if (! ModuleRegistry::isEnabled('testimonials')) {
            return [];
        }

        return Testimonial::query()->active()->ordered()->get()
            ->map(fn (Testimonial $testimonial): array => [
                'content' => (string) $testimonial->content,
                'author' => (string) $testimonial->author,
                'role' => $testimonial->role,
                'rating' => $testimonial->rating,
            ])->all();
    }

    /**
     * @return array<int, array{question: string, answer: string}>
     */
    protected function faqs(): array
    {
        if (! ModuleRegistry::isEnabled('faq')) {
            return [];
        }

        return Faq::query()->active()->ordered()->get()
            ->map(fn (Faq $faq): array => [
                'question' => (string) $faq->question,
                'answer' => (string) $faq->answer,
            ])->all();
    }

    /**
     * @return array<int, array{title: string|null, slug: string|null, excerpt: string|null, image: string|null, category: string|null, technologies: array<string>}>
     */
    protected function projects(): array
    {
        if (! ModuleRegistry::isEnabled('portfolio')) {
            return [];
        }

        return Project::query()->active()->ordered()->get()
            ->map(fn (Project $project): array => $project->toCardArray())
            ->all();
    }
}
