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
use Illuminate\Contracts\Support\Htmlable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Radio;

class TemplatesResource extends Resource
{
    protected static ?string $model = Templates::class;

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationGroup = 'Templates';

    protected static ?string $modelLabel = 'Email';
    protected static ?string $pluralModelLabel = 'Email';

    protected static ?string $navigationIcon = 'heroicon-o-document';

    public static function getNavigationIcon(): string | Htmlable | null
    {
        return 'https://cdn-icons-png.flaticon.com/512/6863/6863985.png';
    }

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
                        ->label('Segment')
                        // ->disabledOn('edit') 
                        ->afterStateUpdated(function (Get $get, ?string $state) {
                            $array = [
                                'name' => $get('name'),
                                'segment_id' => $get('segment_id')
                            ];
                            Session::put('segment_array',$array);
                            return redirect(env('APP_URL')."/admin/templates/create");
                        }),
                    
                        // Radio::make('type')->options([
                        //     'drag' => 'Drag & Drop',
                        //     'html' => 'HTML',
                        //     'fetch_url' => 'Fetch URL'
                        // ])
                        // ->descriptions([
                        //     'drag' => 'Use our advanced drag & drop editor',
                        //     'html' => 'Write your own HTML with our editor',
                        //     'fetch_url' => 'Description text'
                        // ])->inline(true),
                    
                    
                        // ViewField::make('rating')
                        //     ->view('dragDrop'),
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
                // Tables\Columns\TextColumn::make('segment_id')
                //     ->numeric()
                //     ->sortable(),
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
                    SelectFilter::make('segment_id')
                    ->options(Segment::all()->pluck('name','id'))->native(false)->label('Segment')
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
