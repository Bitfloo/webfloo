@php
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }}
    {{ $attributes->merge(['class' => $classes()]) }}
    @if($href) href="{{ $href }}" @endif
>
    @if($image)
        <figure>
            <img src="{{ $image }}" alt="{{ $title ?? '' }}" class="w-full">
        </figure>
    @endif

    <div class="card-body">
        @if($title)
            <h3 class="card-title">{{ $title }}</h3>
        @endif

        @if($subtitle)
            <p class="text-base-content/70">{{ $subtitle }}</p>
        @endif

        {{ $slot }}

        @isset($actions)
            <div class="card-actions justify-end">
                {{ $actions }}
            </div>
        @endisset
    </div>

    @isset($footer)
        <div class="card-footer border-t border-base-content/10">
            {{ $footer }}
        </div>
    @endisset
</{{ $tag }}>
