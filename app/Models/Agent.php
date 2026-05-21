<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'agent_code', 'name', 'email', 'phone',
        'commission_type', 'commission_percentage', 'commission_flat_centavos',
        'bank_name', 'bank_account', 'gcash_number',
        'is_active', 'notes',
    ];

    protected $casts = [
        'commission_percentage' => 'decimal:2',
        'commission_flat_centavos' => 'integer',
        'is_active' => 'boolean',
    ];
}
