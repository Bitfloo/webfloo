<?php

declare(strict_types=1);

namespace Webfloo\Filament\Resources\LeadResource\RelationManagers;

use Webfloo\Models\LeadReminder;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RemindersRelationManager extends RelationManager
{
    protected static string $relationship = 'reminders';

    protected static ?string $title = 'Przypomnienia';

    protected static ?string $recordTitleAttribute = 'title';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->label('Tytuł')
                ->required()
                ->maxLength(100)
                ->columnSpanFull(),

            DateTimePicker::make('due_at')
                ->label('Termin')
                ->required()
                ->native(false),

            Select::make('priority')
                ->label('Priorytet')
                ->options(LeadReminder::getPriorityOptions())
                ->default(LeadReminder::PRIORITY_NORMAL)
                ->native(false),

            Textarea::make('description')
                ->label('Opis')
                ->rows(2)
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user']))
            ->columns([
                IconColumn::make('completed_at')
                    ->label('')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->getStateUsing(fn (LeadReminder $record): bool => $record->completed_at !== null),

                TextColumn::make('title')
                    ->label('Tytuł')
                    ->searchable()
                    ->description(fn (LeadReminder $record): ?string => $record->description),

                TextColumn::make('priority')
                    ->label('Priorytet')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => LeadReminder::getPriorityOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => LeadReminder::getPriorityColors()[$state] ?? 'gray'),

                TextColumn::make('due_at')
                    ->label('Termin')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->color(fn (LeadReminder $record): string => $record->isOverdue() ? 'danger' : 'gray'),

                TextColumn::make('user.name')
                    ->label('Przypisany')
                    ->toggleable(),

                TextColumn::make('completed_at')
                    ->label('Wykonano')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('Oczekuje')
                    ->toggleable(),
            ])
            ->defaultSort('due_at', 'asc')
            ->filters([
                TernaryFilter::make('completed')
                    ->label('Status')
                    ->placeholder('Wszystkie')
                    ->trueLabel('Wykonane')
                    ->falseLabel('Oczekujące')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('completed_at'),
                        false: fn (Builder $query) => $query->whereNull('completed_at'),
                    ),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();

                        return $data;
                    }),
            ])
            ->recordActions([
                Action::make('complete')
                    ->label('Wykonaj')
                    ->icon(Heroicon::Check)
                    ->color('success')
                    ->visible(fn (LeadReminder $record): bool => $record->isPending())
                    ->requiresConfirmation()
                    ->action(function (LeadReminder $record): void {
                        $record->markAsCompleted();
                        Notification::make()
                            ->title('Przypomnienie oznaczone jako wykonane')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
