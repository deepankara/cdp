<?php

namespace App\Filament\Resources\CampaignResource\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Campaign;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;

class EmailAnalyticsTable extends BaseWidget
{
    protected static bool $isLazy = false;
    public function table(Table $table): Table
    {
        return $table
            ->query(Campaign::query())
            ->columns([
                TextColumn::make('email'),
            ]);
    }
}
