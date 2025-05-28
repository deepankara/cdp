<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignResource\Pages;
use App\Filament\Resources\CampaignResource\RelationManagers;
use App\Models\Campaign;
use App\Models\WhatsappTemplate;
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
use Filament\Forms\Set;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\ReplicateAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Support\Htmlable;
use App\Models\Customers;
use App\Models\SmsTemplate;



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
                
                Forms\Components\Select::make('channel')
                    ->options(
                        [
                            "Email" => "Email",
                            "Whatsapp" => "Whatsapp",
                            "SMS" => "SMS"
                        ]
                    )->native(false)
                    ->label('Channel')
                    ->live()
                    ->required(),
                
                Forms\Components\Select::make('sms_template')
                    ->options(SmsTemplate::all()->pluck('name','id'))->native(false)
                    ->label('Sms Template')
                    ->live()
                    ->hidden(function (Get $get){
                        if($get('channel') == "SMS"){
                            return false;
                        }else{
                            return true;
                        }
                    })
                    ->afterStateUpdated(function (?string $state,Set $set){
                        $sms = SmsTemplate::whereId($state)->get()->toArray();
                        $sms = (array) current($sms);
                        $array = [];

                        $template = $sms['sms'];

                        $findText = preg_match_all('/{{(.*?)}}/', $template, $matches);

                        foreach ($matches[1] as $placeholder) {
                            $body = [];
                            $body['name'] = $placeholder;
                            array_push($array, $body);
                        }
                        $set('sms_variables',$array);
                    }),

                Forms\Components\Select::make('whatsapp_template')
                    ->options(WhatsappTemplate::where('status',"APPROVED")->pluck('name','id'))->native(false)
                    ->label('Whatsapp Tempalte')
                    ->live()
                    ->hidden(function (Get $get){
                        if($get('channel') == "Whatsapp"){
                            return false;
                        }else{
                            return true;
                        }
                    })
                    ->afterStateUpdated(function (?string $state,Set $set){
                        $whatsapp = WhatsappTemplate::whereId($state)->get()->toArray();
                        $whatsapp = (array) current($whatsapp);
                        $array = [];

                        if($whatsapp['header_type'] != 'NONE'){
                            if($whatsapp['header_type'] == "TEXT"){
                                if(str_contains($whatsapp['header_name'],"{{")){
                                    $header = [];
                                    $header["name"] = "{{1}}";
                                    $header["type"] = "header";
                                    array_push($array, $header);
                                }
                            }else{
                                $header = [];
                                $header["name"] = "{{1}}";
                                $header["type"] = "header";
                                array_push($array, $header);
                            }
                        }


                        if(isset($whatsapp['html_content']) && $whatsapp['html_content'] != ''){
                            $count = substr_count($whatsapp['html_content'], "{{");
                            $body = [];
                            for($i=1; $i <= $count; $i++){ 
                                $body = [];
                                $body["name"] = "{{" . $i . "}}";
                                $body["type"] = "body";
                                array_push($array, $body);
                            }
                        }

                        $buttons = $whatsapp['buttons'];
                        if(count($buttons) >= 1){
                            foreach($buttons as $Key => $value){
                                if(($value['option'] == "URL" && $value['url_type'] == "dynamic") || $value["option"] == "COPY_CODE"){
                                    $body = [];
                                    $body["name"] = $value['option'];
                                    $body["type"] = "button";
                                    array_push($array, $body);
                                }
                            }
                        }
                        $set('wa_variables',$array);
                    }),
                    
                
                Forms\Components\Select::make('include_segment_id')
                    ->options(Segment::all()->pluck('name','id'))->native(false)
                    ->label('Segment')
                    
                    ->live()
                    ->required(),
                
                Forms\Components\Select::make('rule_id')
                    ->options(fn (Get $get): Collection => Rules::query()
                        ->where('segment_id', $get('include_segment_id'))
                        ->pluck('name', 'id')
                    )
                    ->native(false)
                    ->live()
                    ->label('Audience Filter'),

                    Section::make('SMS Details')
                    ->schema([
                        Repeater::make('sms_variables')
                            ->schema([
                                TextInput::make('name')->required()->readonly(),
                                Select::make('value')
                                ->options(function (Get $get){
                                    if($get('../../include_segment_id') == ''){
                                        return [];
                                    }
                                    $customers = Customers::where('segment_id',$get('../../include_segment_id'))->get()->toArray();
                                    $customers = (array) current($customers);
                                    
                                    $attributes = array_keys(json_decode($customers['attributes'],true));
                                    array_push($attributes,"name","email","contact_no");
                                    $data = [];
                                    foreach($attributes as $key => $value){
                                        $data[$value] = $value;
                                    }
                                    return $data;
                                }),
                            ])
                            ->label('Variables')
                            ->required()
                            ->deletable(false)
                            ->addable(false)
                            ->addable(false)
                            ->reorderable(false)
                            ->columns(2)
                        ])
                        ->hidden(function (Get $get){
                        if($get('channel') == "SMS"){
                            return false;
                        }else{
                            return true;
                        }
                    }),

                
                Section::make('Whatsapp Details')
                    ->schema([
                        Repeater::make('wa_variables')
                            ->schema([
                                TextInput::make('name')->required()->readonly(),
                                TextInput::make('type')->required()->readonly(),
                                Select::make('value')
                                ->options(function (Get $get){
                                    if($get('../../include_segment_id') == ''){
                                        return [];
                                    }
                                    $customers = Customers::where('segment_id',$get('../../include_segment_id'))->get()->toArray();
                                    $customers = (array) current($customers);
                                    
                                    $attributes = array_keys(json_decode($customers['attributes'],true));
                                    array_push($attributes,"name","email","contact_no");
                                    $data = [];
                                    foreach($attributes as $key => $value){
                                        $data[$value] = $value;
                                    }
                                    return $data;
                                }),
                            ])
                            ->label('Variables')
                            ->required()
                            ->deletable(false)
                            ->addable(false)
                            ->addable(false)
                            ->reorderable(false)
                            ->columns(3)

                        // Forms\Components\Select::make('body_variable')
                        //     ->multiple()
                        //     ->minItems(2)
                        //     ->maxItems(2)
                        //     ->options(function (Get $get){
                        //         if($get('include_segment_id') == ''){
                        //             return [];
                        //         }
                        //         $customers = Customers::where('segment_id',$get('include_segment_id'))->get()->toArray();
                        //         $customers = (array) current($customers);
                                
                        //         $attributes = array_keys(json_decode($customers['attributes'],true));
                        //         array_push($attributes,"name","email","contact_no");
                        //         $data = [];
                        //         foreach($attributes as $key => $value){
                        //             $data[$value] = $value;
                        //         }
                        //         return $data;
                        //     })

                        ])
                        ->hidden(function (Get $get){
                        if($get('channel') == "Whatsapp"){
                            return false;
                        }else{
                            return true;
                        }
                    }),
               

                    
                
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

                        


                    ])
                    ->hidden(function (Get $get){
                        if($get('channel') == "Email"){
                            return false;
                        }else{
                            return true;
                        }
                    }),

                    DateTimePicker::make('schedule')
                        ->label('Schedule')
                        ->minDate(now()) // Restrict to today and future dates
                        ->seconds(false)
                        ->native(false)
                        ->timezone('Asia/Kolkata')
                        ->required(), 

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
                // ActionGroup::make([
                //     Action::make('analytics')->url(fn (Campaign $record): string => route('filament.admin.resources.campaigns.analytics', $record))
                //     ->openUrlInNewTab()
                // ])->link()->label('Analytics')->hidden(function ($record) {
                //     return !$record->campaign_executed;
                // }),
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
