<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TemplatesResource\Pages;
use App\Filament\Resources\TemplatesResource\RelationManagers;
use App\Models\Templates;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Mohamedsabil83\FilamentFormsTinyeditor\Components\TinyEditor;
use Filament\Forms\Get;
use Illuminate\Support\Str;
use App\Models\Segment;
use Illuminate\Support\Facades\Session;
use Filament\Forms\Components\ViewField;
use Illuminate\Http\Request;


class TemplatesResource extends Resource
{
    protected static ?string $model = Templates::class;

    protected static ?int $navigationSort = 2;


    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                    Forms\Components\TextInput::make('name')
                        ->maxLength(255)
                        ->default(null),
                    Forms\Components\Select::make('segment_id')
                        ->options(Segment::all()->pluck('name','id'))->native(false)
                        ->live(onBlur: true)
                        ->disabledOn('edit') 
                        ->afterStateUpdated(function (Get $get, ?string $state) {
                            $array = [
                                'name' => $get('name'),
                                'segment_id' => $get('segment_id')
                            ];
                            Session::put('segment_array',$array);
                            return redirect("http://127.0.0.1:8000/admin/templates/create");
                        }),
                    TinyEditor::make('html_content')
                    ->required()
                    ->columnSpanFull(),

                    // ViewField::make('html_content')
                    // ->view('tinymce')
                    // ->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('segment_id')
                    ->numeric()
                    ->sortable(),
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
            'index' => Pages\ListTemplates::route('/'),
            'create' => Pages\CreateTemplates::route('/create'),
            'edit' => Pages\EditTemplates::route('/{record}/edit'),
            'temp' => Pages\TemplateTinyMce::route('/temp'),
        ];
    }
}
