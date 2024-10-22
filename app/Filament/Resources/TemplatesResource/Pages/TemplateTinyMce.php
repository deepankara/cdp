<?php

namespace App\Filament\Resources\TemplatesResource\Pages;

use App\Filament\Resources\TemplatesResource;
use Filament\Resources\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use App\Models\Segment;
use Illuminate\Support\Facades\Session;

class TemplateTinyMce extends Page
{
    use InteractsWithForms;
    protected static string $resource = TemplatesResource::class;

    protected static string $view = 'filament.resources.templates-resource.pages.template-tiny-mce';

    public function mount(): void 
    {
        $this->form->fill();
    }
 
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required(),

                Select::make('segment_id')
                ->options(Segment::all()->pluck('name','id'))->native(false)
                ->live(onBlur: true)
                ->afterStateUpdated(function (Set $set, ?string $state) {
                    $set('html_content', '');
                    $customVariables = [
                        ['key' => 'userName', 'label' => 'User Name'],
                        ['key' => 'email', 'label' => 'Email Address'],
                        ['key' => 'phone', 'label' => 'Phone Number'],
                    ];
                    Session::put('customVariables', $customVariables);
                }),
            ])
            ->statePath('data');
    } 

}
