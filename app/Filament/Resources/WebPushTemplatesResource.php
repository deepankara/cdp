<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WebPushTemplatesResource\Pages;
use App\Filament\Resources\WebPushTemplatesResource\RelationManagers;
use App\Models\WebPushTemplates;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;



class WebPushTemplatesResource extends Resource
{
    protected static ?string $model = WebPushTemplates::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->maxLength(255)
                    ->default(null),

                TextInput::make('title')
                    ->maxLength(255)
                    ->default(null),

                Textarea::make('message')
                    ->maxLength(255)
                    ->default(null)->columnSpanFull(),
                
                TextInput::make('image')
                    ->maxLength(255)
                    ->default(null),

                TextInput::make('icon')
                ->maxLength(255)
                ->default(null),
                
                TextInput::make('launch_url')
                ->maxLength(255)
                ->default(null),
                
                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\ListWebPushTemplates::route('/'),
            'create' => Pages\CreateWebPushTemplates::route('/create'),
            'edit' => Pages\EditWebPushTemplates::route('/{record}/edit'),
        ];
    }
}
