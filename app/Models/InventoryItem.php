<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku', 'name', 'category', 'serial_number', 'quantity',
        'unit_cost_centavos', 'location', 'assigned_to',
        'subscription_id', 'status', 'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost_centavos' => 'integer',
    ];
}
