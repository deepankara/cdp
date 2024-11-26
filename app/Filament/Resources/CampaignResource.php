<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignResource\Pages;
use App\Filament\Resources\CampaignResource\RelationManagers;
use App\Models\Campaign;
use Filament\Tables\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Segment;
use App\Models\Templates;
use App\Models\Rules;
use Illuminate\Support\Collection;
use Filament\Forms\Get;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\ReplicateAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Support\Htmlable;


class CampaignResource extends Resource
{
    protected static ?string $model = Campaign::class;
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Campaign';

    public static function getNavigationLabel(): string
    {
        return 'Create';
    }

    public static function getNavigationIcon(): string | Htmlable | null
    {
        return 'https://cdn-icons-png.flaticon.com/512/6104/6104532.png';
    }
    

    protected static ?string $navigationIcon = 'heroicon-o-plus-circle';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->maxLength(255)
                    ->default(null),
                
                Forms\Components\Select::make('include_segment_id')
                    ->options(Segment::all()->pluck('name','id'))->native(false)
                    ->label('Segment')
                    ->required(),
                
                Forms\Components\Select::make('rule_id')
                    ->options(fn (Get $get): Collection => Rules::query()
                        ->where('segment_id', $get('include_segment_id'))
                        ->pluck('name', 'id')
                    )
                    ->native(false)
                    ->label('Audience Filter'),
                
               

                    
                
                Section::make('Email Details')
                    ->schema([
                        Forms\Components\TextInput::make('email_subject')
                        ->maxLength(255)
                        ->default(null)
                        ->label('Subject'),

                        Forms\Components\TextInput::make('email_from_name')
                        ->maxLength(255)
                        ->label('From Name')
                        ->default(null),

                        Forms\Components\Select::make('template_id')
                        ->options(fn (Get $get): Collection => Templates::query()
                            ->where('segment_id', $get('include_segment_id'))
                            ->pluck('name', 'id'),
                        )
                        ->label('Template')
                        ->native(false),

                        DateTimePicker::make('schedule')
                        ->label('Schedule')
                        ->minDate(now()) // Restrict to today and future dates
                        ->seconds(false)
                        ->native(false)
                        ->required(), 


                    ]),

                    // Section::make('Retargetting')
                    // ->schema([
                    //     Repeater::make('retarget')
                    //     ->schema([
                    //         Select::make('when')
                    //         ->options(
                    //             ['opened'=>'Opened',
                    //              'clicked'=>'Clicked',
                    //             ]
                    //         )
                    //         ->native(true)
                    //         ->label("who didn't")
                    //         ->disableOptionsWhenSelectedInSiblingRepeaterItems(),

                    //         DateTimePicker::make('schedule')
                    //         ->label('Schedule')
                    //         ->minDate(now()) // Restrict to today and future dates
                    //         ->seconds(false)
                    //         ->native(false)
                    //         ->required(), 

                    //     ])->label('Retarget')->columns(2)
                    // ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                IconColumn::make('campaign_executed')->boolean()->label('Executed')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                ActionGroup::make([
                    Action::make('analytics')->url(fn (Campaign $record): string => route('filament.admin.resources.campaigns.analytics', $record))
                    ->openUrlInNewTab()
                ])->link()->label('Analytics')->hidden(function ($record) {
                    return !$record->campaign_executed;
                }),
                // ReplicateAction::make()->excludeAttributes(['name','schedule']);
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
            'index' => Pages\ListCampaigns::route('/'),
            'create' => Pages\CreateCampaign::route('/create'),
            'edit' => Pages\EditCampaign::route('/{record}/edit'),
            'analytics' => Pages\EmailAnalytics::route('/{record}/analytics'),
        ];
    }
}
