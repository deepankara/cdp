<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms;
use App\Models\EmailAnalyticsTable;
use App\Models\Campaign;
use App\Filament\Resources\CampaignResource\Widgets\EmailAnalyticsWidget;
use Filament\Forms\Components\Tabs;
use Illuminate\Support\Facades\Session;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Section;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Select;
use Filament\Actions\Action;
use Filament\Support\Exceptions\Halt;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Notifications\Notification;

class EmailAnalytics extends Page implements HasTable,HasForms
{
    use InteractsWithTable,InteractsWithForms ;

    public ?array $data = [];

    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $slug = 'email-analytics';
    protected static string $view = 'filament.pages.email-analytics';
    // protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationGroup = 'Analytics';
    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return 'Email';
    }

    public static function getNavigationIcon(): string | Htmlable | null
    {
        return 'https://cdn-icons-png.flaticon.com/512/7286/7286142.png';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')->action(function (Session $session) {
                Session::forget('camp_id');
                return redirect()->to(env('APP_URL').'/admin/email-analytics/');
            }),
        ];
    }

    
	public function mount(): void
	{
        // Session::forget('camp_id');
        if(Session::get('camp_id') != ''){
            $campaign = DB::table('campaign')->where('campaign.id',Session::get('camp_id'))
                                            ->select('campaign.*','segment.name as segment_name','rules.name as rule_name','templates.html_content','campaign.id as campaign_id')
                                            ->leftjoin('segment','segment.id','campaign.include_segment_id')
                                            ->leftjoin('templates','templates.id','campaign.template_id')
                                            ->leftjoin('rules','rules.id','campaign.rule_id')
                                            ->get()->toArray();
            
            if(count($campaign) < 1){
                Session::forget('camp_id');
                Notification::make()
                    ->title("Sorry Campaign Didn't Executed")
                    ->success()
                    ->send();
                $this->campaignForm->fill();
            }else{
                $campaign = (array) current($campaign);
                Session::put('email_analytics',$campaign);
            }
        }else{
            $this->campaignForm->fill();
        }
	}

    public function table(Table $table): Table
    {
        return $table
            ->query(EmailAnalyticsTable::query()->orderBy('indian_time','desc')->where('event','click')->where('campaign_id',Session::get('camp_id')))
            ->columns([
                TextColumn::make('email')->searchable(),
                TextColumn::make('country')->searchable(),
                TextColumn::make('device')->searchable(),
                TextColumn::make('platform')->searchable(),
                TextColumn::make('browser')->searchable(),
                // TextColumn::make('click_url')->searchable(),
                TextColumn::make('indian_time')->sortable()->label('Event Activity Time'),
            ])
            ;
    }

    protected function getForms(): array
    {
        return [
            'form',
            'campaignForm',
        ];
    }

    public function save(): void
    {
        try {
            $data = $this->campaignForm->getState();
            if (isset($data['campaign_id']) && $data['campaign_id'] != '') {
                Session::put('camp_id', $data['campaign_id']);
                redirect()->to(env('APP_URL').'/admin/email-analytics');
                return;
            }
        } catch (Halt $exception) {
            return;
        }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Check Analytics')
                ->submit('save'),
        ];
    }

    public function textForm(Form $form): Form
    {
        return $form
            ->schema([
            
            ]);
    }

    public function campaignForm(Form $form): Form
    {
        return $form
            ->schema([
            Select::make('campaign_id')
                ->options(Campaign::all()->pluck('name','id'))->native(false)
                ->label('Campaign')
                ->required()
                ,
            ])->statePath('data');
    }

    public function form(Form $form): Form
    {
        if(isset(Session::get('email_analytics')['name']) && Session::get('email_analytics')['name'] != ''){
            return $form
                ->schema([
                    Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Campaign Info')
                        ->schema([
                            Placeholder::make('Name')
                            ->content(Session::get('email_analytics')['name']),

                            Placeholder::make('Segment Name')
                            ->content(Session::get('email_analytics')['segment_name']),

                            Placeholder::make('Rule Name')
                            ->content(Session::get('email_analytics')['rule_name']),

                            Placeholder::make('Email Subject')
                            ->content(Session::get('email_analytics')['email_subject']),

                            Placeholder::make('Email Sender Name')
                            ->content(Session::get('email_analytics')['email_from_name']),
                            

                            Section::make('Templates')
                            ->schema([
                                ViewField::make('rating')->view('template')
                            ])


                            // EmailAnalyticsWidget::class
                        ]),

                        Tabs\Tab::make('Stats')
                        ->schema([
                            ViewField::make('stats_view')->view('stats')
                            // EmailAnalyticsWidget::class
                        ]),

                        Tabs\Tab::make('Click Analytics')
                        ->schema([
                            Section::make('Click Tracking')
                            ->schema([
                                ViewField::make('clicking')->view('maps')
                            ])
                            // ViewField::make('rating')->view('maps')
                            // EmailAnalyticsWidget::class
                        ]),
                    
                    ])
                ]);
        }else{
            return $form
                ->schema([

                ]);
        }
    }
}
