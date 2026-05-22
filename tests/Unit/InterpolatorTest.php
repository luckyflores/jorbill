<?php

namespace Tests\Unit;

use App\Services\Automation\Interpolator;
use PHPUnit\Framework\TestCase;

class InterpolatorTest extends TestCase
{
    public function test_replaces_placeholders(): void
    {
        $i = new Interpolator();
        $ctx = ['customer' => ['name' => 'John', 'phone' => '0917']];
        $this->assertSame('Hi John at 0917', $i->render('Hi {{customer.name}} at {{customer.phone}}', $ctx));
    }

    public function test_missing_placeholder_becomes_empty(): void
    {
        $i = new Interpolator();
        $this->assertSame('Hi  end', $i->render('Hi {{customer.name}} end', []));
    }

    public function test_handles_whitespace_inside_braces(): void
    {
        $i = new Interpolator();
        $this->assertSame('Hello world', $i->render('Hello {{  greeting  }}', ['greeting' => 'world']));
    }
}
