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
        'odoo_id', 'odoo_synced_at',
        'payment_number', 'customer_id', 'invoice_id', 'amount_centavos',
        'gateway', 'gateway_reference', 'received_at',
        'reverses_payment_id', 'reversed_at', 'reversed_reason', 'status', 'notes',
    ];

    protected $casts = [
        'odoo_id' => 'integer',
        'odoo_synced_at' => 'datetime',
        'amount_centavos' => 'integer',
        'received_at' => 'datetime',
        'reversed_at' => 'datetime',
        'reverses_payment_id' => 'integer',
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


    public function reversedBy()  { return $this->hasOne(Payment::class, 'reverses_payment_id'); }
    public function originalPayment() { return $this->belongsTo(Payment::class, 'reverses_payment_id'); }
    public function isReversed(): bool { return $this->status === 'reversed'; }
    public function isReversal(): bool { return $this->status === 'reversal'; }

}
