<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RetargetCampaignResource\Pages;
use App\Filament\Resources\RetargetCampaignResource\RelationManagers;
use App\Models\RetargetCampaign;
use App\Models\Campaign;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Get;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Session;
use Mohamedsabil83\FilamentFormsTinyeditor\Components\TinyEditor;

class RetargetCampaignResource extends Resource
{
    protected static ?string $model = RetargetCampaign::class;
    protected static ?string $navigationGroup = 'Campaign';
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationLabel(): string
    {
        return 'Retarget';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->maxLength(255)
                    ->default(null),

                Forms\Components\Select::make('campaign_id')
                    ->options(Campaign::all()->pluck('name','id'))->native(false)
                    ->live(onBlur: true)
                    ->disabledOn('edit')
                    ->afterStateUpdated(function (Get $get, ?string $state) {
                        $array = [
                            'name' => $get('name'),
                            'campaign_id' => $get('campaign_id')
                        ];
                    Session::put('retarget_segment_array',$array);
                        return redirect(env('APP_URL')."/admin/retarget-campaigns/create");
                    }),
                
                Forms\Components\Repeater::make('retarget')
                ->schema([
                    Forms\Components\Select::make('when')
                    ->options(
                        ['opened'=>'Opened',
                            'clicked'=>'Clicked',
                        ]
                    )
                    ->native(true)
                    ->label("who didn't")
                    ->required()
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),

                    Forms\Components\TextInput::make('email_from_name')
                    ->maxLength(255)
                    ->default(null),

                    
                    Forms\Components\TextInput::make('email_subject')
                    ->maxLength(255)
                    ->default(null),


                    TinyEditor::make('html_content')
                    ->required()
                    ->columnSpanFull(),

                    Forms\Components\TextInput::make('click_link')
                    ->maxLength(255)->visible(fn (Get $get): string =>($get('when') == 'clicked')),

                    Forms\Components\DateTimePicker::make('schedule')
                    ->label('Schedule')
                    ->minDate(now()) // Restrict to today and future dates
                    ->seconds(false)
                    ->native(false)
                    ->required(), 
                ])->maxItems(2)->defaultItems(2)->addable(false)->columnSpanFull()->grid(2)->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
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
            'index' => Pages\ListRetargetCampaigns::route('/'),
            'create' => Pages\CreateRetargetCampaign::route('/create'),
            'edit' => Pages\EditRetargetCampaign::route('/{record}/edit'),
        ];
    }
}
