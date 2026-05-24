<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerDiagnostic extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id', 'tech_user_id', 'ran_at', 'public_ip',
        'wifi', 'ping_results', 'speedtest',
        'notes', 'gps_lat', 'gps_lng', 'photo_path',
        'app_version', 'device_info',
    ];

    protected $casts = [
        'ran_at'        => 'datetime',
        'wifi'          => 'array',
        'ping_results'  => 'array',
        'speedtest'     => 'array',
        'device_info'   => 'array',
        'gps_lat'       => 'decimal:7',
        'gps_lng'       => 'decimal:7',
    ];

    public function customer() { return $this->belongsTo(Customer::class); }
    public function tech()     { return $this->belongsTo(User::class, 'tech_user_id'); }

    public function avgPingTo(string $target): ?float
    {
        $row = collect($this->ping_results ?? [])->firstWhere('target', $target);
        return $row['avg_ms'] ?? null;
    }

    public function wifiRssi(): ?int { return $this->wifi['rssi'] ?? null; }
    public function speedtestDownMbps(): ?float { return $this->speedtest['download_mbps'] ?? null; }
    public function speedtestUpMbps(): ?float   { return $this->speedtest['upload_mbps']   ?? null; }
}
