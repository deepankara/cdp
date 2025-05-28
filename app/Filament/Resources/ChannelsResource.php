<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChannelsResource\Pages;
use App\Filament\Resources\ChannelsResource\RelationManagers;
use App\Models\Channels;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Forms\Get;


class ChannelsResource extends Resource
{
    protected static ?string $model = Channels::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationIcon(): string | Htmlable | null
    {
        return 'https://cdn-icons-png.flaticon.com/512/650/650975.png';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('source')
                    ->options(['Email'=>'Email','Whatsapp'=>'Whatsapp','Sms'=>'SMS'])
                    ->native(false)
                    ->live()
                    ->required(),
                Forms\Components\Select::make('provider')
                    ->options(function (Get $get) {
                        if($get('source') == ''){
                            return [];
                        }

                        if($get('source') == "Email"){
                            return ['TelSpiel'=>'TelSpiel','SES'=>'Amazon SES'];
                        }

                        if($get('source') == "Whatsapp"){
                            return ['Meta'=>'Whatsapp Offical API'];
                        }

                        if($get('source') == "SMS"){
                            return ['Telspiel'=>'Telspiel'];
                        }

                    })
                    ->native(false)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('source')
                    ->searchable(),
                Tables\Columns\TextColumn::make('provider')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChannels::route('/'),
            'create' => Pages\CreateChannels::route('/create'),
            'edit' => Pages\EditChannels::route('/{record}/edit'),
        ];
    }
}
