<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Invoice extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'invoice_number', 'series_code', 'customer_id', 'subscription_id',
        'issued_at', 'due_at',
        'subtotal_centavos', 'vat_centavos', 'withholding_centavos',
        'discount_centavos', 'total_centavos', 'amount_paid_centavos',
        'status', 'notes', 'bir_atp_id',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'due_at' => 'date',
        'subtotal_centavos' => 'integer',
        'vat_centavos' => 'integer',
        'withholding_centavos' => 'integer',
        'discount_centavos' => 'integer',
        'total_centavos' => 'integer',
        'amount_paid_centavos' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'total_centavos', 'amount_paid_centavos', 'due_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
