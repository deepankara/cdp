<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;


class Whatsapp extends Page
{
    // protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.whatsapp';
    protected static ?string $navigationGroup = 'Analytics';
    protected static ?int $navigationSort = 4;


    public static function getNavigationIcon(): string | Htmlable | null
    {
        return 'https://cdn-icons-png.flaticon.com/512/15713/15713434.png';
    }
}
