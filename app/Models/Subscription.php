<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Subscription extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'customer_id', 'service_id', 'router_id', 'status',
        'username', 'password', 'mac_address', 'ip_address',
        'price_centavos_override',
        'activated_at', 'suspended_at', 'cancelled_at',
        'next_billing_date', 'notes',
    ];

    protected $casts = [
        'price_centavos_override' => 'integer',
        'activated_at' => 'datetime',
        'suspended_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'next_billing_date' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'customer_id', 'service_id', 'router_id', 'username', 'ip_address', 'next_billing_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function priceCentavos(): int
    {
        return (int) ($this->price_centavos_override ?? 0);
        // TODO Phase 3: fall back to $this->service->price_centavos via belongsTo
    }

    public function customer()   { return $this->belongsTo(Customer::class); }
    public function service()    { return $this->belongsTo(Service::class); }
    public function router()     { return $this->belongsTo(Router::class); }

}
