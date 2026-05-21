<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id', 'service_id', 'router_id', 'status',
        'username', 'password', 'mac_address', 'ip_address',
        'price_centavos_override',
        'activated_at', 'suspended_at', 'cancelled_at',
        'next_billing_date', 'notes',
    ];

    protected $casts = [
        'password' => 'encrypted',
        'price_centavos_override' => 'integer',
        'activated_at' => 'datetime',
        'suspended_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'next_billing_date' => 'date',
    ];

    // TODO Phase 2: customer() and service() belongsTo relationships
    public function priceCentavos(): int
    {
        return (int) ($this->price_centavos_override ?? 0);
        // TODO Phase 2: fall back to $this->service->price_centavos when relationship exists
    }
}
