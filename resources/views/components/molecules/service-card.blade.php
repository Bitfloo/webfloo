@php
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }}
    {{ $attributes->merge(['class' => 'card bg-base-100 card-bordered group levitate-card']) }}
    @if($href) href="{{ $href }}" @endif
>
    <div class="card-body">
        @if($icon)
            <div class="relative w-16 h-16 rounded-xl flex items-center justify-center mb-4 overflow-hidden">
                {{-- Base background --}}
                <div class="absolute inset-0 bg-primary/10 transition-opacity duration-500 ease-in-out group-hover:opacity-0"></div>
                {{-- Hover background --}}
                <div class="absolute inset-0 bg-primary opacity-0 transition-opacity duration-500 ease-in-out group-hover:opacity-100"></div>
                {{-- Icon --}}
                <x-webfloo-icon :name="$icon" size="2xl" class="relative z-10 text-primary group-hover:text-primary-content transition-colors duration-500 ease-in-out" />
            </div>
        @endif

        <h3 class="card-title text-lg group-hover:text-primary transition-colors">
            {{ $title }}
        </h3>

        @if($description)
            <p class="text-base-content/70 text-sm leading-relaxed">
                {{ $description }}
            </p>
        @endif

        {{ $slot }}

        @if($href)
            <div class="card-actions mt-4">
                <span class="text-primary text-sm font-medium inline-flex items-center gap-1">
                    {{ $linkText }}
                    <span class="icon-[tabler--arrow-right] size-4 group-hover:translate-x-1 transition-transform" aria-hidden="true"></span>
                </span>
            </div>
        @endif
    </div>
</{{ $tag }}>
