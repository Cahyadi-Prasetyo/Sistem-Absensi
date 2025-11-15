<?php

namespace Tests\Feature;

use App\Services\RedisEventSubscriber;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class RedisEventSubscriberTest extends TestCase
{

    /**
     * Test that RedisEventSubscriber can publish events to Redis
     */
    public function test_can_publish_event_to_redis(): void
    {
        // Mock Redis to verify publish is called
        Redis::shouldReceive('publish')
            ->once()
            ->with('absensi-events', \Mockery::type('string'))
            ->andReturn(1);

        // Publish an event
        RedisEventSubscriber::publish('AttendanceCreated', 123);

        // If we get here without exception, test passes
        $this->assertTrue(true);
    }

    /**
     * Test graceful degradation when Redis fails
     */
    public function test_graceful_degradation_when_redis_fails(): void
    {
        // Mock Redis to throw exception
        Redis::shouldReceive('publish')
            ->once()
            ->andThrow(new \Exception('Redis connection failed'));

        // Mock Log to verify warning is logged
        Log::shouldReceive('warning')
            ->once()
            ->with(
                'Failed to publish event to Redis, continuing with graceful degradation',
                \Mockery::type('array')
            );

        // Publish should not throw exception
        RedisEventSubscriber::publish('AttendanceCreated', 123);

        // If we get here without exception, graceful degradation works
        $this->assertTrue(true);
    }

    /**
     * Test that event message has correct format
     */
    public function test_published_message_has_correct_format(): void
    {
        $capturedMessage = null;

        Redis::shouldReceive('publish')
            ->once()
            ->with('absensi-events', \Mockery::capture($capturedMessage))
            ->andReturn(1);

        RedisEventSubscriber::publish('AttendanceCreated', 456);

        // Verify message is valid JSON
        $this->assertJson($capturedMessage);

        // Decode and verify structure
        $data = json_decode($capturedMessage, true);
        $this->assertArrayHasKey('event', $data);
        $this->assertArrayHasKey('attendance_id', $data);
        $this->assertArrayHasKey('source_node', $data);
        $this->assertArrayHasKey('timestamp', $data);

        // Verify values
        $this->assertEquals('AttendanceCreated', $data['event']);
        $this->assertEquals(456, $data['attendance_id']);
    }
}
