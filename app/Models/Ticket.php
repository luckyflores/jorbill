<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Ticket extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'ticket_number', 'customer_id', 'subject', 'body',
        'status', 'priority', 'category', 'channel',
        'assigned_to', 'subscription_id',
        'resolved_at', 'first_response_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'first_response_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'assigned_to', 'priority'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function customer() { return $this->belongsTo(Customer::class); }

}
