<?php

namespace App\Filament\Resources\CampaignResource\Pages;

use App\Filament\Resources\CampaignResource;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use App\Filament\Resources\CampaignResource\Widgets\EmailAnalyticsWidget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Filament\Tables\Table;
use App\Models\EmailAnalyticsTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Filters\SelectFilter;

class EmailAnalytics extends Page implements HasTable
{
    use InteractsWithRecord,InteractsWithTable ;

    protected static string $resource = CampaignResource::class;

    protected static string $view = 'filament.resources.campaign-resource.pages.email-analytics';

    public function mount(int | string $record): void
    {
        Session::put('camp_id',$record);
        $this->record = $this->resolveRecord($record);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            EmailAnalyticsWidget::class,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(EmailAnalyticsTable::query()->orderBy('indian_time','desc')->where('campaign_id',Session::get('camp_id')))
            ->columns([
                TextColumn::make('email')->searchable(),
                TextColumn::make('event'),
                TextColumn::make('indian_time')->sortable()->label('Event Activity Time'),
            ])
            ->filters([
                SelectFilter::make('event')
                ->options([
                    'open' => 'Opened',
                    'click' => 'Clicked',
                    'delivered' => 'Delivered',
                ])->native(false)
            ])
            ;
    }

}
