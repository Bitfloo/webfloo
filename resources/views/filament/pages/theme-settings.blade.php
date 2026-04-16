<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex items-center gap-4">
            <x-filament::button type="submit" icon="heroicon-o-check">
                Zapisz ustawienia
            </x-filament::button>

            <span class="text-sm text-gray-500 dark:text-gray-400">
                Zmiany zostaną zastosowane na stronie publicznej.
            </span>
        </div>
    </form>
</x-filament-panels::page>
