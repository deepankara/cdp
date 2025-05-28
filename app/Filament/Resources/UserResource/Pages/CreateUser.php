<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Illuminate\Support\Facades\DB;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function beforeFill(): void
    {
        $roles = DB::table('roles')->pluck('name','id')->toArray();
        $this->data['roles'] = $roles;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if(isset($data['password']) && $data['password'] != ''){
            $data['password'] = Hash::make($data['password']);
        }else{
            unset($data['password']);
        }

        // DB::table('model_has_roles')->where('model_id',$this->record['id'])->update(['role_id' => $data['role_id']]);

        return $data;
    }
}
