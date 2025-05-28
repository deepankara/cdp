<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use App\Models\WhatsappAnalytics;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Select;
use App\Models\Campaign;
use Illuminate\Support\Facades\Session;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Form;
use Filament\Support\Exceptions\Halt;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;


class Whatsapp extends Page implements HasTable,HasForms
{
    use InteractsWithTable,HasPageShield,InteractsWithForms;
    public ?array $data = [];

    // protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.whatsapp';
    protected static ?string $navigationGroup = 'Analytics';
    protected static ?int $navigationSort = 4;

    public function getTitle(): string | Htmlable
    {
        if(Session::get('wa_camp_id') != ''){
            return __('Whatsapp Analytics for '.Session::get('campaign_analytics')['name']);
        }else{
            return __('Whatsapp Analytics');
        }
        // return __('Whatsapp Analytics');
    }


    public static function getNavigationIcon(): string | Htmlable | null
    {
        return 'https://cdn-icons-png.flaticon.com/512/15713/15713434.png';
    }

    public function mount(): void
	{   
        if(Session::get('wa_camp_id') != ''){
            $whatsapp = DB::table('whatsapp_analytics')->select('status', DB::raw('count(*) as status_count'))
            ->whereNotNull('time')
                                                        ->where('campaign_id',Session::get('wa_camp_id'))
                                                    ->groupBy('status')->get()->toArray();
                                                    $keyValueArray = [];
                                                    foreach ($whatsapp as $item) {
                                                        $keyValueArray[$item->status] = $item->status_count;
                                                    }
                                                    $keyValueArray['custom'] = 'Hide';
            $campagin = DB::table('campaign')->whereId(Session::get('wa_camp_id'))->get()->toArray();
            $campagin = (array) current($campagin);
            Session::put('campaign_analytics',$campagin);
            Session::put('whatsapp_user_analytics',$keyValueArray);
        }else{
            $this->campaignForm->fill();

        }
    }


    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')->action(function (Session $session) {
                Session::forget('wa_camp_id');
                return redirect()->to(env('APP_URL').'/admin/whatsapp/');
            }),
        ];
    }

    public function campaignForm(Form $form): Form
    {
        return $form
            ->schema([
            Select::make('campaign_id')
                ->options(Campaign::all()->where('channel','Whatsapp')->where('campaign_executed',true)->pluck('name','id'))->native(false)
                ->label('Campaign')
                ->required()
                ,
            ])->statePath('data');
    }

    public function save(): void
    {
        try {
            $data = $this->campaignForm->getState();
            if (isset($data['campaign_id']) && $data['campaign_id'] != '') {
                Session::put('wa_camp_id', $data['campaign_id']);
                redirect()->to(env('APP_URL').'/admin/whatsapp');
                return;
            }
        } catch (Halt $exception) {
            return;
        }
    }

    protected function getForms(): array
    {
        return [
            'campaignForm',
        ];
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Check Analytics')
                ->submit('save'),
        ];
    }

    public function table(Table $table): Table
    {

        return $table
            ->query(
                WhatsappAnalytics::query()
                ->select(
                    'whatsapp_analytics.id',
                    'whatsapp_analytics.mobile_number',
                    'campaign.name as campaign',
                    'whatsapp_analytics.status',
                    'whatsapp_analytics.time',
                    'whatsapp_analytics.template_id as template'
                )
                ->whereNotNull('whatsapp_analytics.time')
                ->leftJoin('campaign', 'campaign.id', 'whatsapp_analytics.campaign_id')
                ->when(Session::get('wa_camp_id'), function ($query, $campId) {
                    return $query->where('whatsapp_analytics.campaign_id', $campId);
                })
                ->orderBy('time', 'desc')
            )->columns([
                TextColumn::make('mobile_number')->searchable(),
                TextColumn::make('status'),
                TextColumn::make('time')->searchable()->sortable(),
            ])
            // ->defaultGroup('mobile_number')
            ->filters([
                SelectFilter::make('status')
                ->options([
                    'sent' => 'Sent',
                    'delivered' => 'Delivered',
                    'read' => 'Read',
                    'failed' => 'Failed',
                ])->native(false)
            ])
            ;
    }

}