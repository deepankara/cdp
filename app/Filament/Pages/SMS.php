<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;


class SMS extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left';

    protected static ?string $navigationGroup = 'Analytics';
    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.s-m-s';

    public static function getNavigationIcon(): string | Htmlable | null
    {
        return 'https://cdn-icons-png.flaticon.com/512/1280/1280089.png';
    }


}
