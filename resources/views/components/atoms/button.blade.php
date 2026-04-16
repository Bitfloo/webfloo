@props([])

@php
    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }}
    {{ $attributes->merge(['class' => $classes()]) }}
    @if($href) href="{{ $href }}" @endif
    @if(!$href) type="{{ $type }}" @endif
    @if($disabled) disabled @endif
>
    @if($loading)
        <span class="loading loading-spinner loading-sm"></span>
    @endif
    {{ $slot }}
</{{ $tag }}>
