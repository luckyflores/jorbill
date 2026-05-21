<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceLineItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id', 'description', 'quantity',
        'unit_price_centavos', 'amount_centavos',
        'subscription_id', 'service_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price_centavos' => 'integer',
        'amount_centavos' => 'integer',
    ];
}
