<div>
    @if ($submitted)
        <div class="alert alert-success" role="status">
            <span class="icon-[tabler--circle-check] size-5" aria-hidden="true"></span>
            <span>{{ __('Dziękujemy za wiadomość. Odpowiemy najszybciej jak to możliwe.') }}</span>
        </div>
    @else
        <form wire:submit="submit" class="space-y-4">
            @error('form')
                <div class="alert alert-error" role="alert">{{ $message }}</div>
            @enderror

            <div>
                <label class="label" for="contact-name">
                    <span class="label-text">{{ __('Imię i nazwisko') }}</span>
                </label>
                <input id="contact-name" type="text" wire:model="name" class="input input-bordered w-full" autocomplete="name" />
                @error('name')<span class="text-error text-sm">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="label" for="contact-email">
                    <span class="label-text">{{ __('E-mail') }}</span>
                </label>
                <input id="contact-email" type="email" wire:model="email" class="input input-bordered w-full" autocomplete="email" />
                @error('email')<span class="text-error text-sm">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="label" for="contact-phone">
                    <span class="label-text">{{ __('Telefon (opcjonalnie)') }}</span>
                </label>
                <input id="contact-phone" type="tel" wire:model="phone" class="input input-bordered w-full" autocomplete="tel" />
                @error('phone')<span class="text-error text-sm">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="label" for="contact-message">
                    <span class="label-text">{{ __('Wiadomość') }}</span>
                </label>
                <textarea id="contact-message" wire:model="message" rows="5" class="textarea textarea-bordered w-full"></textarea>
                @error('message')<span class="text-error text-sm">{{ $message }}</span>@enderror
            </div>

            {{-- Honeypot: visually hidden, bots fill it --}}
            <div class="absolute -left-[9999px] top-auto h-px w-px overflow-hidden" aria-hidden="true">
                <label for="contact-website">Website</label>
                <input id="contact-website" type="text" wire:model="website" tabindex="-1" autocomplete="off" />
            </div>

            <div>
                <label class="flex cursor-pointer items-start gap-2">
                    <input type="checkbox" wire:model="consent" class="checkbox checkbox-sm mt-1" />
                    <span class="label-text text-sm">
                        {{ setting('contact.consent_text', __('Wyrażam zgodę na przetwarzanie moich danych osobowych w celu obsługi zapytania.')) }}
                    </span>
                </label>
                @error('consent')<span class="text-error text-sm">{{ $message }}</span>@enderror
            </div>

            <button type="submit" class="btn btn-primary w-full" wire:loading.attr="disabled">
                <span wire:loading.remove>{{ __('Wyślij wiadomość') }}</span>
                <span wire:loading>{{ __('Wysyłanie...') }}</span>
            </button>
        </form>
    @endif
</div>
