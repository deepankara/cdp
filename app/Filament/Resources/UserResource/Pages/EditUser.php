<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterFill(): void
    {
        $roles = DB::table('roles')->orderBy('id','asc')->pluck('name','id')->toArray();
        $this->data['roles'] = $roles;
        $this->data['password'] = '';
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if(isset($data['password']) && $data['password'] != ''){
            $data['password'] = Hash::make($data['password']);
        }else{
            unset($data['password']);
        }

        DB::table('model_has_roles')->where('model_id',$this->record['id'])->update(['role_id' => $data['role_id']]);

        return $data;
    }
}
