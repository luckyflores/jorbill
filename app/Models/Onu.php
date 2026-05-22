<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Onu extends Model
{
    use HasFactory;

    protected $fillable = [
        'serial_number', 'vendor', 'model_name', 'mac_address',
        'subscription_id', 'nap_id', 'nap_port',
        'rx_power_dbm', 'tx_power_dbm', 'status',
        'installed_at', 'last_seen_at', 'notes',
    ];

    protected $casts = [
        'nap_port' => 'integer',
        'rx_power_dbm' => 'decimal:2',
        'tx_power_dbm' => 'decimal:2',
        'installed_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public function subscription() { return $this->belongsTo(Subscription::class); }
    public function nap()          { return $this->belongsTo(Nap::class); }

}
