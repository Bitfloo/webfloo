<?php

declare(strict_types=1);

namespace Webfloo\Filament\Resources\LeadResource\RelationManagers;

use Webfloo\Models\LeadActivity;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    protected static ?string $title = 'Historia aktywności';

    protected static ?string $recordTitleAttribute = 'title';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user']))
            ->columns([
                TextColumn::make('type')
                    ->label('Typ')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => LeadActivity::getTypeOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => LeadActivity::getTypeColors()[$state] ?? 'gray'),

                TextColumn::make('title')
                    ->label('Tytuł')
                    ->searchable(),

                TextColumn::make('description')
                    ->label('Opis')
                    ->limit(50)
                    ->toggleable(),

                TextColumn::make('user.name')
                    ->label('Użytkownik')
                    ->placeholder('System'),

                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
