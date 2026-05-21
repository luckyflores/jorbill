<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RadiusSession extends Model
{
    protected $table = 'radacct';
    protected $primaryKey = 'radacctid';
    public $timestamps = false;

    protected $casts = [
        'acctstarttime'   => 'datetime',
        'acctupdatetime'  => 'datetime',
        'acctstoptime'    => 'datetime',
        'acctinputoctets' => 'integer',
        'acctoutputoctets'=> 'integer',
        'acctsessiontime' => 'integer',
        'acctinterval'    => 'integer',
    ];

    public function scopeActive($q) { return $q->whereNull('acctstoptime'); }

    public function getBytesInMbAttribute(): float
    {
        return round(($this->acctinputoctets ?? 0) / 1024 / 1024, 2);
    }

    public function getBytesOutMbAttribute(): float
    {
        return round(($this->acctoutputoctets ?? 0) / 1024 / 1024, 2);
    }
}
