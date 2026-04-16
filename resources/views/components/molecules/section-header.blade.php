<div {{ $attributes->merge(['class' => 'max-w-3xl mb-12 lg:mb-16 ' . $alignmentClasses()]) }}>
    @if($subtitle)
        <p class="text-primary font-semibold mb-4 uppercase tracking-wider text-sm">
            {{ $subtitle }}
        </p>
    @endif

    <h2 class="text-3xl md:text-4xl font-bold text-base-content font-heading">
        {{ $title }}
    </h2>

    @if($description)
        <p class="mt-4 text-lg text-base-content/70 leading-relaxed">
            {{ $description }}
        </p>
    @endif
</div>
