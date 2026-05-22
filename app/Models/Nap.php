<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nap extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'type', 'latitude', 'longitude',
        'capacity', 'ports_used', 'parent_nap_id', 'olt_id',
        'pon_port', 'address', 'notes',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'capacity' => 'integer',
        'ports_used' => 'integer',
    ];

    public function parentNap() { return $this->belongsTo(Nap::class, 'parent_nap_id'); }
    public function children()  { return $this->hasMany(Nap::class, 'parent_nap_id'); }
    public function onus()      { return $this->hasMany(Onu::class); }

}
