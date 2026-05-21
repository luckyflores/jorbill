<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Service extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'name', 'slug', 'code', 'type',
        'bandwidth_down_kbps', 'bandwidth_up_kbps',
        'price_centavos', 'vat_inclusive', 'billing_cycle', 'prepaid_days',
        'mikrotik_profile_name', 'description', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'price_centavos' => 'integer',
        'bandwidth_down_kbps' => 'integer',
        'bandwidth_up_kbps' => 'integer',
        'prepaid_days' => 'integer',
        'vat_inclusive' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'code', 'price_centavos', 'bandwidth_down_kbps', 'bandwidth_up_kbps', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getPriceDisplayAttribute(): string
    {
        return '? ' . number_format($this->price_centavos / 100, 2);
    }
}
