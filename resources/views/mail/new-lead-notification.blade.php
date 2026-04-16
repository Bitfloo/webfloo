<x-mail::message>
# Nowy lead z formularza kontaktowego

Otrzymałeś nowe zapytanie od **{{ $lead->name }}**.

## Dane kontaktowe

| | |
|:--|:--|
| **Imię i nazwisko** | {{ $lead->name }} |
| **Email** | {{ $lead->email }} |
@if($lead->phone)
| **Telefon** | {{ $lead->phone }} |
@endif
@if($lead->company)
| **Firma** | {{ $lead->company }} |
@endif

## Wiadomość

{{ $lead->message }}

<x-mail::button :url="$crmUrl">
Otwórz CRM
</x-mail::button>

Pozdrawiamy,<br>
{{ config('app.name') }}
</x-mail::message>
