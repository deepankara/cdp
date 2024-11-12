<?php

namespace App\Filament\Resources\RetargetCampaignResource\Pages;

use App\Filament\Resources\RetargetCampaignResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRetargetCampaigns extends ListRecords
{
    protected static string $resource = RetargetCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
