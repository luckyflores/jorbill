<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherBatch extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'code_prefix', 'count',
        'value_centavos', 'duration_minutes', 'expires_at',
        'service_id', 'created_by_user_id',
    ];
    protected $casts = [
        'count' => 'integer',
        'value_centavos' => 'integer',
        'duration_minutes' => 'integer',
        'expires_at' => 'datetime',
    ];
    public function vouchers() { return $this->hasMany(Voucher::class, 'batch_id'); }
}
