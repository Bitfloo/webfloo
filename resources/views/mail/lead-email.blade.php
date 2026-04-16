<x-mail::message>
# {{ $lead->name }},

{!! nl2br(e($body)) !!}

<x-mail::button :url="config('app.url')">
Odwiedź naszą stronę
</x-mail::button>

Pozdrawiamy,<br>
{{ config('app.name') }}
</x-mail::message>
