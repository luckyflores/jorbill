<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BirCounter extends Model
{
    protected $fillable = ['series', 'next_value'];

    protected $casts = [
        'next_value' => 'integer',
    ];
}
