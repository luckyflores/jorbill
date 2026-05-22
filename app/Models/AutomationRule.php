<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutomationRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'is_enabled',
        'trigger_type', 'trigger_config',
        'conditions', 'target_filter', 'actions',
        'last_fired_at', 'fire_count',
    ];

    protected $casts = [
        'is_enabled'     => 'boolean',
        'trigger_config' => 'array',
        'conditions'     => 'array',
        'actions'        => 'array',
        'target_filter'  => 'array',
        'last_fired_at'  => 'datetime',
        'fire_count'     => 'integer',
    ];

    public function executions()
    {
        return $this->hasMany(AutomationRuleExecution::class, 'rule_id');
    }
}
