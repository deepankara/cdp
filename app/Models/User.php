<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
// use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Passport\HasApiTokens;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Solutionforest\FilamentEmail2fa\Interfaces\RequireTwoFALogin;
use Solutionforest\FilamentEmail2fa\Trait\HasTwoFALogin;
use NotificationChannels\WebPush\HasPushSubscriptions;
 


class User extends Authenticatable implements FilamentUser,RequireTwoFALogin
{
    use HasApiTokens, HasFactory, Notifiable,HasRoles,HasPanelShield,HasTwoFALogin,HasPushSubscriptions;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'email_scopes',
        'whatsapp_scopes',
        'sms_scopes',
    ];

 
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'email_scopes' => 'array',
        'whatsapp_scopes' => 'array',
        'sms_scopes' => 'array',
    ];
}
