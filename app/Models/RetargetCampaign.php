<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetargetCampaign extends Model
{
    use HasFactory;

    protected $table = 'retarget_campaign';

    protected $fillable = [
        "name",
        "campaign_id",
        "retarget"
    ];

    protected $casts = [
        "retarget" => 'array'
    ];
}
