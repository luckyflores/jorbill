<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'label', 'description',
        'channel', 'subject', 'body',
        'is_active', 'use_count', 'last_used_at',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'use_count'    => 'integer',
        'last_used_at' => 'datetime',
    ];
}
