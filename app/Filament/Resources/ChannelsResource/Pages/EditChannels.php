<?php

namespace App\Filament\Resources\ChannelsResource\Pages;

use App\Filament\Resources\ChannelsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChannels extends EditRecord
{
    protected static string $resource = ChannelsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
