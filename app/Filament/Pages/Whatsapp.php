<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use App\Models\WhatsappAnalytics;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;


class Whatsapp extends Page implements HasTable
{
    use InteractsWithTable;
    // protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.whatsapp';
    protected static ?string $navigationGroup = 'Analytics';
    protected static ?int $navigationSort = 4;


    public static function getNavigationIcon(): string | Htmlable | null
    {
        return 'https://cdn-icons-png.flaticon.com/512/15713/15713434.png';
    }

    public function mount(): void
	{
        $whatsapp = DB::table('whatsapp_analytics')->select('status', DB::raw('count(*) as status_count'))
                                                    ->groupBy('status')->get()->toArray();
        $keyValueArray = [];
        foreach ($whatsapp as $item) {
            $keyValueArray[$item->status] = $item->status_count;
        }
        $keyValueArray['custom'] = 'Hide';
        Session::put('whatsapp_user_analytics',$keyValueArray);

    }

    public function table(Table $table): Table
    {

        return $table
            ->query(WhatsappAnalytics::query())
            ->columns([
                TextColumn::make('mobile_number'),
                TextColumn::make('status'),
                TextColumn::make('time')->searchable()->sortable(),
            ])
            ;
    }

}
