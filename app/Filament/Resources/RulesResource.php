<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RulesResource\Pages;
use App\Filament\Resources\RulesResource\RelationManagers;
use App\Models\Rules;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Segment;
use App\Models\Customers;
use Filament\Forms\Set;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Session;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Illuminate\Support\Collection;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\DatePicker;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use Coolsam\FilamentFlatpickr\Enums\FlatpickrTheme;
use Illuminate\Contracts\Support\Htmlable;


class RulesResource extends Resource
{
    protected static ?string $model = Rules::class;
    protected static ?int $navigationSort = 3;


    protected static ?string $navigationIcon = 'heroicon-o-funnel';

    public static function getNavigationIcon(): string | Htmlable | null
    {
        return 'https://cdn-icons-png.flaticon.com/512/1160/1160523.png';
    }

    protected static ?string $modelLabel = 'Audience Filter';
    protected static ?string $pluralModelLabel = 'Audience Filter';


    public static function getNavigationLabel(): string
    {
        return 'Audience Filter';
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
                    ->live()
                    ->label('Segment')
                    ->disabledOn('edit') 
                    ,
                
                Radio::make('rule_condition')
                    ->options([
                        'and' => 'All rules should satisfy',
                        'or' => 'Any Rules can satisfy',
                    ])->inline()->inlineLabel(false)->label(''),
                
                 
                Repeater::make('rule')
                    ->schema([
                        Select::make('where')
                        ->options(function (Get $get) {
                            if($get('../../segment_id') == ''){
                                return [];
                            }
                            $customers = Customers::where('segment_id',$get('../../segment_id'))->get()->toArray();
                            $customers = (array) current($customers);
                            
                            $attributes = array_keys(json_decode($customers['attributes'],true));
                            array_push($attributes,"name","email","contact_no");
                            $data = [];
                            foreach($attributes as $key => $value){
                                $data[$value] = $value;
                            }
                            return $data;
                        })->live()->native(false),

                        Select::make('options')
                        ->options(function (Get $get) {
                            if(str_contains($get('where'),"date")){
                                $options = [
                                    'include' => 'Include',
                                    'exclude' => 'Exclude',
                                    // 'contains' => 'Contains',
                                    // 'not_contains' => 'Not Contains',
                                    // 'equals_to' => 'Equals to',
                                    'greater_than' => 'Greater Than',
                                    'less_than' => 'Less Than',
                                    'range' => 'Range'
                                ];
                            }else{
                                $options = [
                                    'include' => 'Include',
                                    'exclude' => 'Exclude',
                                    'contains' => 'Contains',
                                    'not_contains' => 'Not Contains',
                                    'equals_to' => 'Equals to',
                                ];
                            }
                            return $options;
                        })->live()->native(false),


                        Flatpickr::make('date_include_exclude')->conjunction(',')
                        ->multiple()
                        ->dateFormat('Y-m-d')
                        ->conjunction(',')
                        ->hidden(function (Get $get) {
                            if(str_contains($get('where'),"date")){
                                if($get('options') == "include" || $get('options') == "exclude"){
                                    return false;
                                }else{
                                    return true;
                                }
                            }else{
                                return true;
                            }
                        })->label('Date'),

                        Flatpickr::make('date')
                        ->theme(FlatpickrTheme::AIRBNB) 
                        ->dateFormat('d-m-Y')
                        // ->multiple()
                        ->hidden(function (Get $get) {
                            if(str_contains($get('where'),"date")){
                                if($get('options') == "include" || $get('options') == "exclude" || $get('options') == "range"){
                                    return true;
                                }else{
                                    return false;
                                }
                            }else{
                                return true;
                            }
                        })->label('Date'),

                        Flatpickr::make('date_range')
                        ->theme(FlatpickrTheme::AIRBNB) 
                        ->range()
                        ->dateFormat('d-m-Y')
                        // ->multiple()
                        ->hidden(function (Get $get) {
                            if(str_contains($get('where'),"date")){
                                if($get('options') == "range"){
                                    return false;
                                }else{
                                    return true;
                                }
                            }else{
                                return true;
                            }
                        })->label('Date'),

                        

                        TextInput::make('value')->required()
                        ->hidden(function (Get $get) {
                            if($get('options') == 'contains' || $get('options') == 'not_contains' || $get('options') == 'equals_to'){
                                if(str_contains($get('where'),"date")){
                                    return true;
                                }
                                return false;
                            }else{
                                return true;
                            }
                        }),
                        TagsInput::make('values')
                        ->hidden(function (Get $get) {
                            if($get('options') == 'include' || $get('options') == 'exclude'){
                                if(str_contains($get('where'),"date")){
                                    return true;
                                }
                                return false;
                            }else{
                                return true;
                            }
                        }),


                    ])->columns(3)->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Tables\Columns\TextColumn::make('id')
                //     ->label('ID')
                //     ->numeric()
                //     ->sortable(),
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
            'index' => Pages\ListRules::route('/'),
            'create' => Pages\CreateRules::route('/create'),
            'edit' => Pages\EditRules::route('/{record}/edit'),
        ];
    }
}
