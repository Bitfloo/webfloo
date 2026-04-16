<?php

declare(strict_types=1);

namespace Webfloo\Filament\Resources;

use BackedEnum;
use Webfloo\Filament\Resources\LeadTagResource\Pages;
use Webfloo\Models\LeadTag;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LeadTagResource extends Resource
{
    protected static ?string $model = LeadTag::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Tag;

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('CRM');
    }

    public static function getNavigationLabel(): string
    {
        return __('Tagi');
    }

    public static function getModelLabel(): string
    {
        return __('Tag');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Tagi');
    }

    public static function canAccess(): bool
    {
        if (! config('webfloo.features.crm', true)) {
            return false;
        }

        return auth()->user()?->can('view_any_lead_tag') === true;
    }

    // Ukryj z nawigacji -- tagi zarzadzane przez CRM Dashboard
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->schema([
                    TextInput::make('name')
                        ->label('Nazwa')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50),

                    Select::make('color')
                        ->label('Kolor')
                        ->options(LeadTag::getColorOptions())
                        ->default('gray')
                        ->native(false)
                        ->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nazwa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('color')
                    ->label('Kolor')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => LeadTag::getColorOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => $state),

                TextColumn::make('leads_count')
                    ->label('Leady')
                    ->counts('leads')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Data utworzenia')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name', 'asc')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeadTags::route('/'),
            'create' => Pages\CreateLeadTag::route('/create'),
            'edit' => Pages\EditLeadTag::route('/{record}/edit'),
        ];
    }
}
