<x-webfloo-layout :seo="$page->getSeoData()" :canonical="url('/')">
    <x-webfloo-hero
        :title="(string) setting('home.hero_title', (string) $page->title)"
        :subtitle="setting('home.hero_subtitle')"
        :description="setting('home.hero_description')"
        :cta-text="setting('home.hero_cta_text')"
        :cta-href="setting('home.hero_cta_url')"
        :secondary-cta-text="setting('home.hero_secondary_cta_text')"
        :secondary-cta-href="setting('home.hero_secondary_cta_url')"
    />

    @if ($services !== [])
        <x-webfloo-services :services="$services" />
    @endif

    @if ($projects !== [])
        <x-webfloo-portfolio :projects="$projects" :show-filters="false" />
    @endif

    @if ($testimonials !== [])
        <x-webfloo-testimonials :testimonials="$testimonials" />
    @endif

    @if ($faqs !== [])
        <x-webfloo-faq :items="$faqs" />
    @endif

    <x-webfloo-contact id="contact" />
</x-webfloo-layout>
