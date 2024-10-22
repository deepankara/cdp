<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;
    protected $table = "campaign";

    protected $fillable = [
        'name',
        'include_segment_id',
        'rule_id',
        'email_subject',
        'email_from_name',
        'template_id',
        'schedule',
    ];
}
