<section {{ $attributes->merge(['class' => 'py-16 lg:py-24 bg-gradient-to-b from-base-100 via-base-200/30 to-base-100 relative']) }}>
    {{-- Subtle pattern overlay --}}
    <div class="absolute inset-0 opacity-[0.02]" style="background-image: radial-gradient(circle at 1px 1px, currentColor 1px, transparent 0); background-size: 40px 40px;"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        {{-- Header --}}
        <x-webfloo-section-header
            :title="$title"
            :subtitle="$subtitle"
            :description="$description"
        />

        {{-- Services Grid --}}
        <div class="grid gap-8 {{ $gridClasses() }}">
            @foreach($services as $index => $service)
                <x-webfloo-service-card
                    :title="$service['title']"
                    :description="$service['description'] ?? null"
                    :icon="$service['icon'] ?? null"
                    :href="$service['href'] ?? null"
                />
            @endforeach
        </div>

        {{-- Slot for additional content --}}
        @if($slot->isNotEmpty())
            <div class="mt-12 text-center">
                {{ $slot }}
            </div>
        @endif
    </div>
</section>
