<?php

namespace App\Filament\Pages;


use Filament\Pages\Page;
use Filament\Forms\Form;
use App\Models\Customers;
use Filament\Forms;
use App\Models\EmailAnalyticsTable;
use App\Models\WhatsappAnalytics;
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

class UserAnalytics extends Page implements HasTable,HasForms
{
    use InteractsWithTable,InteractsWithForms ;
    public ?array $data = [];

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.user-analytics';
    protected static ?string $navigationGroup = 'Analytics';
    protected static ?int $navigationSort = 7;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')->action(function (Session $session) {
                Session::forget('email_id');
                return redirect()->to(env('APP_URL').'/admin/user-analytics');
            }),
        ];
    }


    public static function getNavigationIcon(): string | Htmlable | null
    {
        return 'https://cdn-icons-png.flaticon.com/512/7403/7403270.png';
    }
    public function mount(): void
	{
        if(Session::get('email_id') != ''){
            $users = DB::table('customers')->where('email',Session::get('email_id'))->get()->toArray();
            $users = (array) current($users);

            $mobileNumber = $users['contact_no'];
            $normalizedPhoneNumber = preg_replace('/\D/', '', $mobileNumber);

            $whatsapp = DB::table('whatsapp_analytics')->where('mobile_number',$normalizedPhoneNumber)
                                                        ->select('status', DB::raw('count(*) as status_count'))
                                                        ->groupBy('status')->get()->toArray();

            $keyValueArray = [];
            foreach ($whatsapp as $item) {
                $keyValueArray[$item->status] = $item->status_count;
            }
            $keyValueArray['custom'] = 'Show';

            $emailAnalyticsData = DB::table('email_analytics_api')
                ->select('event', DB::raw('COUNT(DISTINCT sg_message_id) as count'))
                ->where('email', Session::get('email_id'))
                ->groupBy('event')
                ->get();
        
            $result = [];
            foreach ($emailAnalyticsData as $data) {
                $result[$data->event] = $data->count;
            }
            Session::put('whatsapp_no',$normalizedPhoneNumber);
            Session::put('whatsapp_user_analytics',$keyValueArray);
            Session::put('email_user_analytics',$result);
        }else{
            $this->userForm->fill();
        }
    }

    protected function getForms(): array
    {
        
        return [
            'form',
            'userForm',
        ];
    }

    public function table(Table $table): Table
    {

        return $table
            ->query(WhatsappAnalytics::query()->where('mobile_number',Session::get('whatsapp_no'))->orderBy('time','desc'))
            ->columns([
                TextColumn::make('mobile_number'),
                TextColumn::make('status')->searchable(),
                TextColumn::make('time')->searchable()->sortable(),
            ])
            ;
    }


    public function save(): void
    {
        try {
            $data = $this->userForm->getState();
            if (isset($data['email_id']) && $data['email_id'] != '') {
                Session::put('email_id', $data['email_id']);
                redirect()->to(env('APP_URL').'/admin/user-analytics');
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

    public function userForm(Form $form): Form
    {
        return $form
            ->schema([
            Select::make('email_id')
                ->options(Customers::all()->pluck('email','email'))->native(false)
                ->label('Users')
                ->required()
                ,
            ])->statePath('data');
    }

    public function form(Form $form): Form
    {
        return $form
        ->schema([
            Tabs::make('Tabs')
            ->tabs([
                Tabs\Tab::make('Whatsapp Info')
                ->schema([
                    ViewField::make('whatsapp_stats')->view('whatsappStats')
                ]),
                Tabs\Tab::make('Email Info')
                ->schema([
                    ViewField::make('email_stats')->view('emailStats')
                ])
            ])
        ]);
    }
}

