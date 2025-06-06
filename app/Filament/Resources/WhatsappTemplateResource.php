<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WhatsappTemplateResource\Pages;
use App\Filament\Resources\WhatsappTemplateResource\RelationManagers;
use Illuminate\Contracts\Support\Htmlable;
use App\Models\WhatsappTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Mohamedsabil83\FilamentFormsTinyeditor\Components\TinyEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Illuminate\Support\Arr;
use TangoDevIt\FilamentEmojiPicker\EmojiPickerAction;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\FileUpload;

class WhatsappTemplateResource extends Resource
{
    protected static ?string $model = WhatsappTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Templates';

    protected static ?string $modelLabel = 'Whatsapp';
    protected static ?string $pluralModelLabel = 'Whatsapp';

    public static function getNavigationIcon(): string | Htmlable | null
    {
        return 'https://cdn-icons-png.flaticon.com/512/15713/15713434.png';
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                
                TextInput::make('name')
                    ->maxLength(255)
                    ->default(null)
                    ->extraAttributes(['id' => 'name'])
                    ->unique(ignoreRecord: true)
                    ->helperText('Only lowercase letters, numbers, and underscores are allowed.'),

                Select::make('language')
                    ->options([
                        'en' => 'English',
                        'en_US' => 'English (US)',
                    ])->label('Select Language')->required()->native(false),
                
                Select::make('category')
                    ->options([
                        'AUTHENTICATION' => 'AUTHENTICATION',
                        'MARKETING' => 'MARKETING',
                        'UTILITY' => 'UTILITY',
                    ])->label('Select Category')->required()->live()->native(false),

                
                

                // Select::make('utility_type')
                //     ->options([
                //         'NONE' => 'NONE',
                //         'PAYMENT' => 'PAYMENT',
                //     ])->label('Select Category')
                //     ->required()->native(false)
                //     ->live()
                //     ->hidden(function (Get $get) {
                //         if($get('category') == "UTILITY"){
                //             return false;
                //         }else{
                //             return true;
                //         }
                //     }),
                Section::make('Authentication')
                ->description("Content for authentication message templates can't be edited. You can add additional content from the options below.")
                ->schema([
                    Checkbox::make('add_security_recommendation')->inline()->required(),
                    TextInput::make('code_expiry')->numeric()->required(),
                    TextInput::make('copy_code_button_text')->maxLength(25)->required(),

                ])->hidden(function (Get $get) {
                    if($get('category') == "AUTHENTICATION"){
                        return false;
                    }else{
                        return true;
                    }
                }),

                
                Section::make('Content')
                    ->description('Fill in the header, body and footer sections of your template.')
                    ->schema([
                        Fieldset::make('Header (Optional)')
                        ->schema([
                            Select::make('header_type')
                            ->options([
                                'NONE' => 'NONE',
                                'TEXT' => 'TEXT',
                                'IMAGE' => 'IMAGE',
                                'VIDEO' => 'VIDEO',
                                'DOCUMENT' => 'DOCUMENT',
                            ])->label('Type')->native(false)->columns(2)->live(),

                            FileUpload::make('attachment')
                                ->disk('local')
                                ->columnSpanFull()
                                ->directory('whatsapp_template')
                                ->preserveFilenames()
                                ->image()
                                ->acceptedFileTypes(['image/jpeg','image/png'])
                                ->maxSize(5000)
                                ->hidden(fn (Get $get) => $get('header_type') !== 'IMAGE'),

                            FileUpload::make('document')
                                 ->disk('local')
                                ->columnSpanFull()
                                ->directory('whatsapp_template')
                                ->preserveFilenames()
                                // ->openable()
                                ->maxSize(5000)
                                ->hidden(function (Get $get) {
                                    if($get('header_type') == "DOCUMENT" || $get('header_type') == "VIDEO"){
                                        return false;
                                    }else{
                                        return true;
                                    }
                                })
                                ->acceptedFileTypes(function (Get $get){
                                    if($get('header_type') == "DOCUMENT"){
                                        return ['application/pdf'];
                                    }else{
                                        return ['video/mp4'];
                                    }
                                }),
                                // ->hidden(fn (Get $get) => $get('header_type') !== 'DOCUMENT'),
                                
                            TextInput::make('header_name')
                                ->maxLength(60)
                                ->default(null)
                                ->live()
                                ->afterStateUpdated(function (?string $state,Set $set,Get $get) {
                                    $array = [];
                                    $array["{{1}}"] = "";
                                    $set('header_variables_sample',$array);
                                })
                                ->hidden(fn (Get $get) => $get('header_type') !== 'TEXT')
                                ->suffixAction(
                                    Action::make('addVariable')
                                    ->icon('heroicon-m-plus')
                                    ->action(function (Set $set, $state) {
                                        $set('header_name', $state."{{1}}");
                                    })
                                ),

                            Placeholder::make('Samples for header content')
                                ->columnSpanFull()
                                ->hidden(function (Get $get, Set $set,?string $state) {
                                    if(str_contains($get('header_name'),"{{1}}")){
                                        return false;
                                    }else{
                                        return true;
                                    }
                                })
                                ->content('To help us review your content, provide examples of the variables or media in the header. Do not include any customer information. Cloud API hosted by Meta reviews templates and variable parameters to protect the security and integrity of our services.'),
                        
                            KeyValue::make('header_variables_sample')
                                ->hidden(function (Get $get, Set $set) {
                                    if(str_contains($get('header_name'),"{{1}}")){
                                        return false;
                                    }else{
                                        return true;
                                    }
                                })
                                ->editableKeys(false)
                                ->addable(false)->deletable(false)
                        ]),

                        Fieldset::make('Body')
                        ->schema([
                            Textarea::make('html_content')
                                ->required()
                                ->maxLength(1024)
                                ->live()
                                ->id('html_content')
                                ->columnSpanFull()
                                ->hintActions([
                                    Action::make('addVariable')
                                        ->icon('heroicon-m-plus')
                                        ->action(function (Set $set,Get $get) {
                                            $countString = substr_count($get('html_content'),"{{") + 1;
                                            $set('html_content',$get('html_content')." {{".$countString."}}");

                                            $values = range(1, $countString); 

                                            $result = collect($values)->mapWithKeys(function ($value, $index) {
                                                $key = "{{" . ($index + 1) . "}}"; // Create the key
                                                return [$key => '']; // Return key-value pair
                                            });
                                            $set('body_variables_sample',$result->all());
                                        }),
                                        Action::make('italic')->extraAttributes(['data-action' => 'italic']),
                                        Action::make('strike')->extraAttributes(['data-action' => 'strike']),
                                        Action::make('bold')->extraAttributes(['data-action' => 'bold']),
                                        Action::make('monospace')->extraAttributes(['data-action' => 'monospace']),
                                    EmojiPickerAction::make('emoji-messagge'),
                                ]),

                            // TinyEditor::make('html_content')
                            // ->required()
                            // ->live()
                            // ->profile('whatsapp')
                            // ->columnSpanFull(),

                            KeyValue::make('body_variables_sample')
                                ->hidden(function (Get $get, Set $set) {
                                    if(str_contains($get('html_content'),"{{1}}")){
                                        return false;
                                    }else{
                                        return true;
                                    }
                                })
                                ->editableKeys(false)
                                ->addable(false)->deletable(false)

                        ]),

                        Fieldset::make('Footer (Optional)')
                        ->schema([
                            ViewField::make('rating')->view('customTextArea'),
                            Textarea::make('content')->columnSpanFull()->label('Footer Content')

                        ]),

                        Placeholder::make('Marketing opt out button')
                    ->columnSpanFull()
                    ->hidden(function (Get $get, Set $set,?string $state) {
                        if($get('category') == "MARKETING"){
                            return false;
                        }else{
                            return true;
                        }
                    })
                    ->content("Add a 'Quick Reply' button labeled 'STOP' to allow users to opt out of marketing messages."),
            

                        Fieldset::make('Button (Optional)')
    ->schema([
        Repeater::make('buttons')
            ->schema([
                Select::make('option')
                    ->options(function (Get $get) {
                        if($get('../../category') == "UTILITY" &&  $get('../../utility_type') == "PAYMENT"){
                            $options = [
                                'ORDER_DETAILS' => "Order Details"
                            ];
                        }else{
                            $options = [
                                'QUICK_REPLY' => 'Quick Reply',
                                'URL' => 'Visit Website',
                                'PHONE_NUMBER' => 'Call Phone Number',
                                'COPY_CODE' => 'Copy Offer Code',
                            ];
                        }
                        return $options;
                    })
                    ->live()
                    ->disableOptionWhen(function (string $value, Get $get) {
                        // Get all selected buttons
                        $selectedButtons = $get('../../buttons') ?? [];
                        $selectedCounts = collect($selectedButtons)
                            ->pluck('option')
                            ->filter()
                            ->countBy()
                            ->toArray();

                        // Disable 'URL' if selected more than 2 times
                        if ($value === 'URL') {
                            return ($selectedCounts['URL'] ?? 0) >= 2;
                        }

                        // Disable 'Call_Phone_Number' if selected more than 1 time
                        if ($value === 'PHONE_NUMBER') {
                            return ($selectedCounts['PHONE_NUMBER'] ?? 0) >= 1;
                        }

                        // Disable 'COPY_CODE' if selected more than 1 time
                        if ($value === 'COPY_CODE') {
                            return ($selectedCounts['COPY_CODE'] ?? 0) >= 1;
                        }

                        // No disabling for other options
                        return false;
                    })
                    ->label('Button Option'),
                    // ->required(),

                    TextInput::make('button_text')->hidden(function (Get $get) {
                        if($get('option') == "COPY_CODE"){
                            return true;
                        }else{
                            return false;
                        }
                    }),
                    Select::make('url_type')
                    ->live()
                    ->options([
                        'static' => 'Static',
                        'dynamic' => 'Dynamic'
                    ])->hidden(function (Get $get) {
                        if($get('option') == "URL"){
                            return false;
                        }else{
                            return true;
                        }
                    }),

                  

                    TextInput::make('url')->url()->hidden(function (Get $get) {
                        if($get('option') == "URL" && $get('url_type') == "static"){
                            return false;
                        }else{
                            return true;
                        }
                    }),

                    TextInput::make('dynamic_url')->hidden(function (Get $get) {
                        if($get('url_type') == "dynamic"){
                            return false;
                        }else{
                            return true;
                        }
                    })->helperText('Adding a variable creates a personalised link for the customer to view their info. Only one variable can be added to the end of a URL.(Example-: https://www.auxilo.com/about-auxilo/{{1}})'),

                    TextInput::make('dynamic_url_example')->url()->hidden(function (Get $get) {
                        if($get('url_type') == "dynamic"){
                            return false;
                        }else{
                            return true;
                        }
                    })->helperText('To help us review your message template, please add an example of the entire website URL including {{1}}. Do not use real customer information.'),


                    TextInput::make('phone_number')->hidden(function (Get $get) {
                        if($get('option') == "PHONE_NUMBER"){
                            return false;
                        }else{
                            return true;
                        }
                    }),

                    TextInput::make('offer_code')->hidden(function (Get $get) {
                        if($get('option') == "COPY_CODE"){
                            return false;
                        }else{
                            return true;
                        }
                    }),
            ])
            ->label('Buttons')->columnSpanFull()->collapsible()->itemLabel(fn (array $state): ?string => $state['option'] ?? null),
    ])
    ->label('Button (Optional)')

                        

                    ])->hidden(function (Get $get) {
                        if($get('category') == "AUTHENTICATION"){
                            return true;
                        }else{
                            return false;
                        }
                    })

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('language')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category'),
                Tables\Columns\TextColumn::make('status'),
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
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListWhatsappTemplates::route('/'),
            'create' => Pages\CreateWhatsappTemplate::route('/create'),
            // 'edit' => Pages\EditWhatsappTemplate::route('/{record}/edit'),
        ];
    }
}
