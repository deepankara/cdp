<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use App\Models\SmsAnalytics;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;





class SMS extends Page implements HasTable
{
    use InteractsWithTable,HasPageShield;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left';

    protected static ?string $navigationGroup = 'Analytics';
    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.s-m-s';

    public static function getNavigationIcon(): string | Htmlable | null
    {
        return 'https://cdn-icons-png.flaticon.com/512/1280/1280089.png';
    }

    public function table(Table $table): Table
    {

        return $table
            ->query(SmsAnalytics::query()
            ->select('sms_analytics.id','sms_analytics.phone','sms_analytics.sms','campaign.name as campaign','sms_analytics.created_at')
            // ->where('phone', Session::get('whatsapp_no'))
            ->leftjoin('campaign','campaign.id','sms_analytics.campaign_id'))
            ->columns([
                TextColumn::make('phone')->searchable(),
                TextColumn::make('sms')
                ->limit(50)
                ->tooltip(function (TextColumn $column): ?string {
                    $state = $column->getState();
                    
                    if (strlen($state) <= $column->getCharacterLimit()) {
                        return null;
                    }
                    
                    // Only render the tooltip if the column content exceeds the length limit.
                    return $state;
                }),
                TextColumn::make('created_at')->sortable(),
            ])
            ->defaultGroup('campaign')

            
            ;
    }


}
