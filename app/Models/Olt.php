<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Olt extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'vendor', 'model', 'location', 'ip_address',
        'ssh_port', 'ssh_user', 'ssh_password', 'enable_password',
        'prompt_pattern', 'save_command',
        'is_active', 'last_seen_at', 'notes',
    ];

    protected $casts = [
        'ssh_password'    => 'encrypted',
        'enable_password' => 'encrypted',
        'ssh_port'        => 'integer',
        'is_active'       => 'boolean',
        'last_seen_at'    => 'datetime',
    ];
}
