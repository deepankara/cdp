<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappTemplate extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_templates';

    protected $fillable = [
        "name",
        "language",
        "category",
        "header_type",
        "header_name",
        "header_variables_sample",
        "body_variables_sample",
        "html_content",
        "attachment",
        "document",
        "content",
        "media_id",
        "upload_id",
        "buttons",
    ];

    protected $casts = [
        "header_variables_sample" => 'array',
        "body_variables_sample" => 'array',
        "buttons" => 'array'
    ];

}
