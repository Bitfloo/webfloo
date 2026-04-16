<?php

declare(strict_types=1);

namespace Webfloo\Filament\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Webfloo\Models\LeadReminder;

class UpcomingRemindersWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Nadchodzące przypomnienia';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                LeadReminder::query()
                    ->pending()
                    ->with(['lead', 'user'])
                    ->orderBy('due_at')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('lead.name')
                    ->label('Lead')
                    ->url(fn (LeadReminder $record): string => route('filament.admin.resources.leads.edit', $record->lead_id))
                    ->description(fn (LeadReminder $record): ?string => $record->lead->company),

                TextColumn::make('title')
                    ->label('Tytuł')
                    ->description(fn (LeadReminder $record): ?string => $record->description),

                TextColumn::make('priority')
                    ->label('Priorytet')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => LeadReminder::getPriorityOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => LeadReminder::getPriorityColors()[$state] ?? 'gray'),

                TextColumn::make('due_at')
                    ->label('Termin')
                    ->dateTime('d.m.Y H:i')
                    ->color(fn (LeadReminder $record): string => $record->isOverdue() ? 'danger' : 'gray'),

                TextColumn::make('user.name')
                    ->label('Przypisany'),
            ])
            ->paginated(false);
    }
}
