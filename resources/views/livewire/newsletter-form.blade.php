<div>
    @if ($submitted)
        <div class="alert alert-success" role="status">
            <span class="icon-[tabler--circle-check] size-5" aria-hidden="true"></span>
            <span>{{ __('Dziękujemy za zapis do newslettera.') }}</span>
        </div>
    @else
        <form wire:submit="subscribe" class="space-y-2">
            @error('form')
                <div class="alert alert-error" role="alert">{{ $message }}</div>
            @enderror

            <div class="flex flex-col gap-2 sm:flex-row">
                <label class="sr-only" for="newsletter-email">{{ __('Adres e-mail') }}</label>
                <input
                    id="newsletter-email"
                    type="email"
                    wire:model="email"
                    class="input input-bordered w-full"
                    placeholder="{{ __('Twój adres e-mail') }}"
                    autocomplete="email"
                />
                <button type="submit" class="btn btn-primary shrink-0">
                    {{ __('Zapisz się') }}
                </button>
            </div>
            @error('email')<span class="text-error text-sm">{{ $message }}</span>@enderror

            {{-- Honeypot: invisible to humans, attractive to bots. --}}
            <div class="hidden" aria-hidden="true">
                <input type="text" wire:model="website" tabindex="-1" autocomplete="off" />
            </div>
        </form>
    @endif
</div>
