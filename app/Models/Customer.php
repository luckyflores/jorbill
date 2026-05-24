<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Customer extends Model implements AuthenticatableContract, FilamentUser
{
    use Authenticatable, HasApiTokens, Notifiable;
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'password', 'remember_token', 'email_verified_at',
        'portal_enabled', 'last_login_at',
        'odoo_id', 'odoo_synced_at',
        'customer_code', 'name', 'email', 'phone', 'alt_phone',
        'address_line1', 'barangay', 'city', 'province', 'postal_code',
        'latitude', 'longitude', 'status', 'tax_id', 'notes',
        'agent_id', 'activated_at',
    ];

    protected $casts = [
        'password' => 'hashed',
        'email_verified_at' => 'datetime',
        'portal_enabled' => 'boolean',
        'last_login_at' => 'datetime',
        'odoo_id' => 'integer',
        'odoo_synced_at' => 'datetime',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'activated_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'name', 'email', 'phone', 'address_line1', 'city', 'agent_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function subscriptions() { return $this->hasMany(Subscription::class); }
    public function invoices()      { return $this->hasMany(Invoice::class); }
    public function payments()      { return $this->hasMany(Payment::class); }


    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'customer'
            && (bool) $this->portal_enabled
            && $this->status !== 'cancelled';
    }

    public function getEmailForPasswordReset(): ?string
    {
        return $this->email;
    }
}
