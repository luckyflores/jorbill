<?php

namespace Tests\Unit;

use App\Services\Notifications\NotifierRegistry;
use App\Services\Notifications\Drivers\NullNotifier;
use App\Services\Notifications\Drivers\LogNotifier;
use Tests\TestCase;

class NotifierRegistryTest extends TestCase
{
    public function test_registry_resolves_default_channels(): void
    {
        $registry = app(NotifierRegistry::class);
        $this->assertInstanceOf(NullNotifier::class, $registry->forChannel('null'));
        $this->assertInstanceOf(LogNotifier::class, $registry->forChannel('log'));
    }

    public function test_registry_lists_available_channels(): void
    {
        $registry = app(NotifierRegistry::class);
        $channels = $registry->availableChannels();
        $this->assertContains('null', $channels);
        $this->assertContains('log', $channels);
        $this->assertContains('semaphore', $channels);
        $this->assertContains('globe', $channels);
    }

    public function test_unknown_channel_returns_null(): void
    {
        $this->assertNull(app(NotifierRegistry::class)->forChannel('mars-pigeon'));
    }
}
