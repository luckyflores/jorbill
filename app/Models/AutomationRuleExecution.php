<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutomationRuleExecution extends Model
{
    use HasFactory;

    protected $fillable = [
        'rule_id', 'batch_id', 'fired_at', 'trigger_summary', 'trigger_payload',
        'conditions_matched', 'actions_executed', 'duration_ms', 'error',
    ];

    protected $casts = [
        'fired_at'           => 'datetime',
        'trigger_payload'    => 'array',
        'actions_executed'   => 'array',
        'conditions_matched' => 'boolean',
        'duration_ms'        => 'integer',
    ];

    public function rule()
    {
        return $this->belongsTo(AutomationRule::class, 'rule_id');
    }
}
