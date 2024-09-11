<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriveFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'file_id',
        'mime_type',
        'url',
        'icon_url',
        'embed_url',
        'organization_display_name',
        'is_shared'
    ];
}