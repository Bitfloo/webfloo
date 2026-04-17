<x-filament-panels::page>
    @php
        $stats = $this->getStats();
        $reminders = $this->getRecentReminders();
    @endphp

    {{-- Compact Stats Bar --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-700">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['pipeline'] }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">W pipeline</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-700">
            <div class="text-2xl font-bold text-warning-600">{{ $stats['new'] }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Nowych</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-700">
            <div class="text-2xl font-bold text-success-600">{{ $stats['converted'] }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Skonwertowanych</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-700">
            <div class="text-2xl font-bold text-primary-600">{{ number_format($stats['pipeline_value'], 0, ',', ' ') }} <span class="text-sm font-normal">PLN</span></div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Wartość pipeline</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-700">
            <div class="text-2xl font-bold text-info-600">{{ $stats['conversion_rate'] }}%</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Konwersja</div>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">
        {{-- Main Content --}}
        <div class="flex-1">
            {{-- Search Bar --}}
            <div class="mb-4">
                <div class="relative">
                    <x-filament::icon icon="heroicon-m-magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="searchQuery"
                        placeholder="Szukaj leadów..."
                        class="w-full pl-10 pr-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    />
                </div>
            </div>

            @if($this->viewMode === 'kanban')
                {{-- Kanban Board --}}
                <div x-data="kanbanBoard()" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($this->getKanbanColumns() as $column)
                        <div
                            class="bg-gray-50 dark:bg-gray-900 rounded-xl p-4 min-h-[400px]"
                            x-on:dragover.prevent="onDragOver($event)"
                            x-on:drop="onDrop($event, '{{ $column['id'] }}')"
                        >
                            {{-- Column Header --}}
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-2">
                                    <span @class([
                                        'w-2.5 h-2.5 rounded-full',
                                        'bg-warning-500' => $column['color'] === 'warning',
                                        'bg-info-500' => $column['color'] === 'info',
                                        'bg-primary-500' => $column['color'] === 'primary',
                                    ])></span>
                                    <h3 class="font-medium text-sm text-gray-700 dark:text-gray-300">
                                        {{ $column['title'] }}
                                    </h3>
                                </div>
                                <span class="text-xs text-gray-500 bg-gray-200 dark:bg-gray-800 px-2 py-0.5 rounded-full" title="Łącznie w kolumnie">
                                    {{ $this->getKanbanCounts()[$column['id']] ?? 0 }}
                                </span>
                            </div>

                            {{-- Lead Cards --}}
                            <div class="space-y-2">
                                @foreach($this->getKanbanLeads()[$column['id']] ?? [] as $lead)
                                    <div
                                        draggable="true"
                                        x-on:dragstart="onDragStart($event, {{ $lead['id'] }})"
                                        x-on:dragend="onDragEnd($event)"
                                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-3 cursor-move hover:shadow-md transition-all group"
                                    >
                                        {{-- Card Header --}}
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="flex-1 min-w-0">
                                                <button
                                                    wire:click="openLeadModal({{ $lead['id'] }}, 'editLead')"
                                                    class="font-medium text-sm text-gray-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400 truncate block text-left"
                                                >
                                                    {{ $lead['name'] }}
                                                </button>
                                                @if($lead['company'])
                                                    <p class="text-xs text-gray-500 truncate">{{ $lead['company'] }}</p>
                                                @endif
                                            </div>
                                            <x-filament::dropdown placement="bottom-end">
                                                <x-slot name="trigger">
                                                    <button class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-gray-600 p-1">
                                                        <x-filament::icon icon="heroicon-m-ellipsis-vertical" class="w-4 h-4" />
                                                    </button>
                                                </x-slot>
                                                <x-filament::dropdown.list>
                                                    <x-filament::dropdown.list.item
                                                        icon="heroicon-m-pencil"
                                                        wire:click="openLeadModal({{ $lead['id'] }}, 'editLead')"
                                                    >
                                                        Edytuj
                                                    </x-filament::dropdown.list.item>
                                                    <x-filament::dropdown.list.item
                                                        icon="heroicon-m-document-text"
                                                        wire:click="openLeadModal({{ $lead['id'] }}, 'addNote')"
                                                    >
                                                        Dodaj notatkę
                                                    </x-filament::dropdown.list.item>
                                                    <x-filament::dropdown.list.item
                                                        icon="heroicon-m-phone"
                                                        wire:click="openLeadModal({{ $lead['id'] }}, 'logCall')"
                                                    >
                                                        Zaloguj rozmowę
                                                    </x-filament::dropdown.list.item>
                                                    <x-filament::dropdown.list.item
                                                        icon="heroicon-m-envelope"
                                                        wire:click="openLeadModal({{ $lead['id'] }}, 'sendEmail')"
                                                    >
                                                        Wyślij email
                                                    </x-filament::dropdown.list.item>
                                                    <x-filament::dropdown.list.item
                                                        icon="heroicon-m-bell"
                                                        wire:click="openLeadModal({{ $lead['id'] }}, 'scheduleReminder')"
                                                    >
                                                        Przypomnienie
                                                    </x-filament::dropdown.list.item>
                                                    <x-filament::dropdown.list.item
                                                        icon="heroicon-m-trophy"
                                                        color="success"
                                                        wire:click="$dispatch('lead-converted', { leadId: {{ $lead['id'] }} })"
                                                    >
                                                        Konwertuj
                                                    </x-filament::dropdown.list.item>
                                                    <x-filament::dropdown.list.item
                                                        icon="heroicon-m-x-circle"
                                                        color="danger"
                                                        wire:click="$dispatch('lead-lost', { leadId: {{ $lead['id'] }} })"
                                                    >
                                                        Utracony
                                                    </x-filament::dropdown.list.item>
                                                </x-filament::dropdown.list>
                                            </x-filament::dropdown>
                                        </div>

                                        {{-- Contact Info --}}
                                        <div class="text-xs text-gray-500 dark:text-gray-400 space-y-0.5 mb-2">
                                            <a href="mailto:{{ $lead['email'] }}" class="hover:text-primary-600 block truncate">{{ $lead['email'] }}</a>
                                            @if($lead['phone'])
                                                <a href="tel:{{ $lead['phone'] }}" class="hover:text-primary-600 block">{{ $lead['phone'] }}</a>
                                            @endif
                                        </div>

                                        {{-- Tags --}}
                                        @if(!empty($lead['tags']))
                                            <div class="flex flex-wrap gap-1 mb-2">
                                                @foreach($lead['tags'] as $tag)
                                                    <span @class([
                                                        'inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium',
                                                        'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' => ($tag['color'] ?? 'gray') === 'gray',
                                                        'bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-300' => ($tag['color'] ?? 'gray') === 'primary',
                                                        'bg-success-100 text-success-700 dark:bg-success-900 dark:text-success-300' => ($tag['color'] ?? 'gray') === 'success',
                                                        'bg-warning-100 text-warning-700 dark:bg-warning-900 dark:text-warning-300' => ($tag['color'] ?? 'gray') === 'warning',
                                                        'bg-danger-100 text-danger-700 dark:bg-danger-900 dark:text-danger-300' => ($tag['color'] ?? 'gray') === 'danger',
                                                    ])>
                                                        {{ $tag['name'] }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif

                                        {{-- Footer --}}
                                        <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-700">
                                            @if($lead['estimated_value'])
                                                <span class="text-xs font-medium text-success-600">
                                                    {{ number_format($lead['estimated_value'], 0, ',', ' ') }} PLN
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-400">{{ $lead['created_at'] }}</span>
                                            @endif

                                            <div class="flex items-center gap-1.5">
                                                @if($lead['pending_reminders_count'] > 0)
                                                    <span class="inline-flex items-center gap-0.5 text-warning-500" title="Przypomnienia">
                                                        <x-filament::icon icon="heroicon-m-bell" class="w-3.5 h-3.5" />
                                                        <span class="text-[10px]">{{ $lead['pending_reminders_count'] }}</span>
                                                    </span>
                                                @endif
                                                @if($lead['assignee'])
                                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 text-[10px] font-medium" title="{{ $lead['assignee']['name'] }}">
                                                        {{ substr($lead['assignee']['name'], 0, 1) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                @php
                                    $visibleCount = count($this->getKanbanLeads()[$column['id']] ?? []);
                                    $totalCount = $this->getKanbanCounts()[$column['id']] ?? 0;
                                    $hiddenCount = max(0, $totalCount - $visibleCount);
                                @endphp
                                @if($hiddenCount > 0)
                                    <button
                                        type="button"
                                        wire:click="loadMore('{{ $column['id'] }}')"
                                        wire:loading.attr="disabled"
                                        wire:target="loadMore('{{ $column['id'] }}')"
                                        class="w-full text-xs text-center py-2 px-3 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white bg-white/50 dark:bg-gray-800/50 hover:bg-white dark:hover:bg-gray-800 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 hover:border-gray-400 transition disabled:opacity-60 disabled:cursor-wait"
                                    >
                                        <span wire:loading.remove wire:target="loadMore('{{ $column['id'] }}')">
                                            Pokaż więcej ({{ $hiddenCount }})
                                        </span>
                                        <span wire:loading wire:target="loadMore('{{ $column['id'] }}')">
                                            Ładowanie…
                                        </span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                {{-- List View --}}
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                            <tr>
                                <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-400">Lead</th>
                                <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-400">Kontakt</th>
                                <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-400">Status</th>
                                <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-400">Wartość</th>
                                <th class="text-right px-4 py-3 font-medium text-gray-600 dark:text-gray-400">Akcje</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($this->getKanbanColumns() as $column)
                                @foreach($this->getKanbanLeads()[$column['id']] ?? [] as $lead)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50">
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-gray-900 dark:text-white">{{ $lead['name'] }}</div>
                                            @if($lead['company'])
                                                <div class="text-xs text-gray-500">{{ $lead['company'] }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="text-gray-600 dark:text-gray-400">{{ $lead['email'] }}</div>
                                            @if($lead['phone'])
                                                <div class="text-xs text-gray-500">{{ $lead['phone'] }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <span @class([
                                                'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
                                                'bg-warning-100 text-warning-700' => $column['color'] === 'warning',
                                                'bg-info-100 text-info-700' => $column['color'] === 'info',
                                                'bg-primary-100 text-primary-700' => $column['color'] === 'primary',
                                            ])>
                                                {{ $column['title'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($lead['estimated_value'])
                                                <span class="font-medium text-success-600">{{ number_format($lead['estimated_value'], 0, ',', ' ') }} PLN</span>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex items-center justify-end gap-1">
                                                <button wire:click="openLeadModal({{ $lead['id'] }}, 'editLead')" class="p-1 text-gray-400 hover:text-primary-600" title="Edytuj">
                                                    <x-filament::icon icon="heroicon-m-pencil" class="w-4 h-4" />
                                                </button>
                                                <button wire:click="openLeadModal({{ $lead['id'] }}, 'addNote')" class="p-1 text-gray-400 hover:text-primary-600" title="Notatka">
                                                    <x-filament::icon icon="heroicon-m-document-text" class="w-4 h-4" />
                                                </button>
                                                <button wire:click="$dispatch('lead-converted', { leadId: {{ $lead['id'] }} })" class="p-1 text-gray-400 hover:text-success-600" title="Konwertuj">
                                                    <x-filament::icon icon="heroicon-m-trophy" class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Sidebar: Reminders --}}
        @if(!empty($reminders))
            <div class="lg:w-72">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                    <h3 class="font-medium text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                        <x-filament::icon icon="heroicon-m-bell" class="w-4 h-4 text-warning-500" />
                        Przypomnienia
                    </h3>
                    <div class="space-y-2">
                        @foreach($reminders as $reminder)
                            <div @class([
                                'p-2 rounded-lg text-sm',
                                'bg-danger-50 dark:bg-danger-900/20' => $reminder['is_overdue'],
                                'bg-gray-50 dark:bg-gray-900' => !$reminder['is_overdue'],
                            ])>
                                <div class="font-medium text-gray-900 dark:text-white truncate">{{ $reminder['title'] }}</div>
                                <div class="text-xs text-gray-500 flex items-center justify-between">
                                    <span>{{ $reminder['lead_name'] }}</span>
                                    <span @class([
                                        'text-danger-600' => $reminder['is_overdue'],
                                    ])>{{ $reminder['due_at'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script>
        function kanbanBoard() {
            return {
                draggedLeadId: null,
                onDragStart(event, leadId) {
                    this.draggedLeadId = leadId;
                    event.target.classList.add('opacity-50', 'rotate-2');
                    event.dataTransfer.effectAllowed = 'move';
                },
                onDragEnd(event) {
                    event.target.classList.remove('opacity-50', 'rotate-2');
                    this.draggedLeadId = null;
                },
                onDragOver(event) {
                    event.preventDefault();
                    event.dataTransfer.dropEffect = 'move';
                },
                onDrop(event, newStatus) {
                    event.preventDefault();
                    if (this.draggedLeadId) {
                        this.$wire.$dispatch('lead-moved', {
                            leadId: this.draggedLeadId,
                            newStatus: newStatus
                        });
                    }
                }
            }
        }
    </script>
</x-filament-panels::page>
