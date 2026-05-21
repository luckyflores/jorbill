<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_code', 'name', 'email', 'phone', 'alt_phone',
        'address_line1', 'barangay', 'city', 'province', 'postal_code',
        'latitude', 'longitude', 'status', 'tax_id', 'notes',
        'agent_id', 'activated_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'activated_at' => 'datetime',
    ];
}
