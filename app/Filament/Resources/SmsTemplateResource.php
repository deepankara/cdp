<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SmsTemplateResource\Pages;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Resources\SmsTemplateResource\RelationManagers;
use App\Models\SmsTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SmsTemplateResource extends Resource
{
    protected static ?string $model = SmsTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Templates';

    
    protected static ?string $modelLabel = 'SMS';
    protected static ?string $pluralModelLabel = 'SMS';

    public static function getNavigationIcon(): string | Htmlable | null
    {
        return 'https://cdn-icons-png.flaticon.com/512/1280/1280089.png';
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->maxLength(2000)
                    ->columnSpanFull()
                    ->default(null),
                Forms\Components\Textarea::make('sms')
                    ->maxLength(2000)
                    ->columnSpanFull()
                    ->default(null),

                Forms\Components\TextInput::make('dlt_template_id')
                    ->maxLength(2000)
                    ->columnSpanFull()
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListSmsTemplates::route('/'),
            'create' => Pages\CreateSmsTemplate::route('/create'),
            'view' => Pages\ViewSmsTemplate::route('/{record}'),
            // 'edit' => Pages\EditSmsTemplate::route('/{record}/edit'),
        ];
    }
}
