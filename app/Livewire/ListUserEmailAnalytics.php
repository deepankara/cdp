<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use App\Models\EmailAnalyticsApiTable;
use Illuminate\Support\Facades\Session;
use Filament\Tables\Columns\TextColumn;


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
            ->query(EmailAnalyticsApiTable::query()->where('email', Session::get('email_id'))->distinct())
            ->columns([
                TextColumn::make('event'),
                TextColumn::make('indian_time'),
            ])
            ->filters([
                // ...
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ]);
    }
}
