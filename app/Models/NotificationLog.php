<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    use HasFactory;
    protected $table = 'notifications_log';
    protected $fillable = [
        'channel', 'driver', 'to', 'subject', 'body', 'event',
        'customer_id', 'status', 'gateway_reference', 'error', 'sent_at',
        'delivered_at', 'provider_status', 'provider_error_code',
        'delivered_at', 'provider_status', 'provider_error_code',
    ];
    protected $casts = ['sent_at' => 'datetime', 'delivered_at' => 'datetime'];
}
