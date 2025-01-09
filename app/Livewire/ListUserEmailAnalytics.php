<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use App\Models\EmailAnalyticsTable;
use Illuminate\Support\Facades\Session;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class ListUserEmailAnalytics extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;
    public function render()
    {
        return view('livewire.list-user-email-analytics');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(EmailAnalyticsTable::query()->select('email_analytics.id','email_analytics.event','email_analytics.indian_time','campaign.name as campaign')->where('email', Session::get('email_id'))->leftjoin('campaign','campaign.id','email_analytics.campaign_id'))
            ->columns([
                TextColumn::make('event'),
                TextColumn::make('indian_time'),
            ])
            ->filters([
                SelectFilter::make('status')
                ->options([
                    'processed' => 'Processed',
                    'delivered' => 'Delivered',
                    'open' => 'Open',
                    'click' => 'Click',
                ])->native(false)
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
