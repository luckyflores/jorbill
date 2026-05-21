<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PisoRate extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'amount_centavos', 'duration_minutes', 'is_active', 'sort_order'];
    protected $casts = [
        'amount_centavos' => 'integer',
        'duration_minutes' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
