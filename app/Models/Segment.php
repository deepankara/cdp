<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Segment extends Model
{
    use HasFactory;

    protected $table = 'segment';

    protected $fillable = [
        "name",
        "database_type",
        "reporting_name",
        "reporting_email",
    ];
}
