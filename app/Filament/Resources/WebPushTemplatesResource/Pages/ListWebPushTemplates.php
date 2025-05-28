<?php

namespace App\Filament\Resources\WebPushTemplatesResource\Pages;

use App\Filament\Resources\WebPushTemplatesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWebPushTemplates extends ListRecords
{
    protected static string $resource = WebPushTemplatesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
