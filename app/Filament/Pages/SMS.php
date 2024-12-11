<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use App\Models\SmsAnalytics;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;




class SMS extends Page implements HasTable
{
    use InteractsWithTable;
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
            ->query(SmsAnalytics::query())
            ->columns([
                TextColumn::make('phone'),
                TextColumn::make('sms')->searchable()->limit(50),
                TextColumn::make('created_at')->searchable()->sortable(),
            ])
            ;
    }


}
