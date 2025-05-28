<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebPushTemplates extends Model
{
    use HasFactory;

    protected $table = 'web_push_templates';

    protected $fillable = [
        'name',
        'title',
        'message',
        'image',
        'icon',
        'launch_url',
        'actions_buttons',
    ];

    protected $casts = [
        'actions_buttons' => 'array',
    ];
    
    
}
