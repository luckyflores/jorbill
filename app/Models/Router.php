<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Router extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'location', 'vendor', 'model', 'ip_address',
        'api_port', 'api_user', 'api_password', 'ssh_port',
        'is_active', 'last_seen_at', 'notes',
    ];

    protected $casts = [
        'api_password' => 'encrypted',
        'api_port' => 'integer',
        'ssh_port' => 'integer',
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
    ];
}
