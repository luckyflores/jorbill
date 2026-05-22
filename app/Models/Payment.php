<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Payment extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'payment_number', 'customer_id', 'invoice_id', 'amount_centavos',
        'gateway', 'gateway_reference', 'received_at', 'status', 'notes',
    ];

    protected $casts = [
        'amount_centavos' => 'integer',
        'received_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'amount_centavos', 'gateway_reference'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function customer() { return $this->belongsTo(Customer::class); }
    public function invoice()  { return $this->belongsTo(Invoice::class); }

}
