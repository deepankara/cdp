<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use App\Models\SmsAnalytics;
use Illuminate\Support\Facades\Session;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class ListUserSms extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;
    public function render()
    {
        return view('livewire.list-user-sms');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(SmsAnalytics::query()
            ->select('sms_analytics.id','sms_analytics.phone','sms_analytics.sms','campaign.name as campaign','sms_analytics.created_at')
            ->where('phone', Session::get('whatsapp_no'))
            ->leftjoin('campaign','campaign.id','sms_analytics.campaign_id'))
            ->columns([
                TextColumn::make('phone'),
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
                TextColumn::make('created_at')->dateTime(),
            ])
            ->defaultGroup('campaign')
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ]);
    }
}
