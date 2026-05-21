<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;
    protected $fillable = [
        'code', 'batch_id', 'value_centavos', 'duration_minutes', 'expires_at',
        'status', 'used_by_customer_id', 'used_by_subscription_id', 'used_at',
    ];
    protected $casts = [
        'value_centavos' => 'integer',
        'duration_minutes' => 'integer',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];
}
