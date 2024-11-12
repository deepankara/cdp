<?php

namespace App\Filament\Resources\RulesResource\Pages;

use App\Filament\Resources\RulesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRules extends EditRecord
{
    protected static string $resource = RulesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // protected function mutateFormDataBeforeSave(array $data): array
    // {
    //     echo "<pre>";print_r($data);exit;
    //     return $data;
    // }

    protected function beforeValidate(): void
    {
        $data = $this->data;
        foreach($data['rule'] as $key => $ruleValue){
            if($ruleValue['where'] == 'login_date' && $ruleValue['options'] == 'include'){
                if(is_array($ruleValue['date_include_exclude'])){
                    $data['rule'][$key]['date_include_exclude'] = implode(",",$ruleValue['date_include_exclude']);
                }
            } 
        }
        $this->data = $data;
    }

    // protected function beforeSave(): void
    // {
    //     $data = $this->data;
    // }
}
