<?php

namespace Tests\Unit;

use App\Services\Automation\ConditionEvaluator;
use PHPUnit\Framework\TestCase;

class ConditionEvaluatorTest extends TestCase
{
    public function test_empty_conditions_returns_true(): void
    {
        $e = new ConditionEvaluator();
        $this->assertTrue($e->evaluate([], ['x' => 1]));
    }

    public function test_eq_operator(): void
    {
        $e = new ConditionEvaluator();
        $ctx = ['subscription' => ['status' => 'active']];
        $this->assertTrue($e->evaluate([['field' => 'subscription.status', 'operator' => 'eq', 'value' => 'active']], $ctx));
        $this->assertFalse($e->evaluate([['field' => 'subscription.status', 'operator' => 'eq', 'value' => 'suspended']], $ctx));
    }

    public function test_in_operator_with_comma_string(): void
    {
        $e = new ConditionEvaluator();
        $ctx = ['x' => ['status' => 'overdue']];
        $this->assertTrue($e->evaluate([['field' => 'x.status', 'operator' => 'in', 'value' => 'issued,overdue,paid']], $ctx));
        $this->assertFalse($e->evaluate([['field' => 'x.status', 'operator' => 'in', 'value' => 'paid,cancelled']], $ctx));
    }

    public function test_gt_lt_operators(): void
    {
        $e = new ConditionEvaluator();
        $ctx = ['p' => ['amount' => 1500]];
        $this->assertTrue($e->evaluate([['field' => 'p.amount', 'operator' => 'gt', 'value' => 1000]], $ctx));
        $this->assertFalse($e->evaluate([['field' => 'p.amount', 'operator' => 'lt', 'value' => 1000]], $ctx));
    }

    public function test_is_null_and_is_not_null(): void
    {
        $e = new ConditionEvaluator();
        $ctx = ['x' => ['a' => null, 'b' => 'hi']];
        $this->assertTrue($e->evaluate([['field' => 'x.a', 'operator' => 'is_null']], $ctx));
        $this->assertTrue($e->evaluate([['field' => 'x.b', 'operator' => 'is_not_null']], $ctx));
    }

    public function test_all_conditions_anded(): void
    {
        $e = new ConditionEvaluator();
        $ctx = ['s' => ['status' => 'active', 'username' => 'john']];
        $conds = [
            ['field' => 's.status', 'operator' => 'eq', 'value' => 'active'],
            ['field' => 's.username', 'operator' => 'contains', 'value' => 'jo'],
        ];
        $this->assertTrue($e->evaluate($conds, $ctx));
        $conds[1]['value'] = 'xyz';
        $this->assertFalse($e->evaluate($conds, $ctx));
    }
}
