<?php

declare(strict_types=1);

namespace Webfloo\Filament\Resources\LeadTagResource\Pages;

use Webfloo\Filament\Resources\LeadTagResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLeadTag extends EditRecord
{
    protected static string $resource = LeadTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
