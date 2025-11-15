<?php

namespace App\Services;

use App\Events\AttendanceCreated;
use App\Events\AttendanceUpdated;
use App\Models\Attendance;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Throwable;

class RedisEventSubscriber
{
    private const CHANNEL = 'absensi-events';
    
    /**
     * Subscribe to Redis pub/sub channel and handle incoming events
     */
    public function subscribe(): void
    {
        try {
            Log::info('Redis subscriber started', [
                'channel' => self::CHANNEL,
                'node_id' => config('app.node_id', 'unknown')
            ]);

            Redis::subscribe([self::CHANNEL], function (string $message) {
                $this->handleMessage($message);
            });
        } catch (Throwable $e) {
            Log::error('Redis subscription failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Wait before retry
            sleep(5);
            
            // Retry subscription
            $this->subscribe();
        }
    }

    /**
     * Handle incoming message from Redis pub/sub
     */
    private function handleMessage(string $message): void
    {
        try {
            $data = json_decode($message, true);
            
            if (!$data || !isset($data['event'], $data['attendance_id'], $data['source_node'])) {
                Log::warning('Invalid message format received', ['message' => $message]);
                return;
            }

            // Skip if this event originated from current node (prevent loop)
            $currentNodeId = config('app.node_id', 'unknown');
            if ($data['source_node'] === $currentNodeId) {
                Log::debug('Skipping event from same node', [
                    'event' => $data['event'],
                    'node_id' => $currentNodeId
                ]);
                return;
            }

            Log::info('Processing event from Redis', [
                'event' => $data['event'],
                'attendance_id' => $data['attendance_id'],
                'source_node' => $data['source_node'],
                'current_node' => $currentNodeId
            ]);

            // Fetch fresh attendance data from database
            $attendance = Attendance::with('user')->find($data['attendance_id']);
            
            if (!$attendance) {
                Log::warning('Attendance not found', ['attendance_id' => $data['attendance_id']]);
                return;
            }

            // Broadcast to WebSocket clients connected to this node
            $this->broadcastToClients($data['event'], $attendance);
            
        } catch (Throwable $e) {
            Log::error('Error handling Redis message', [
                'error' => $e->getMessage(),
                'message' => $message
            ]);
        }
    }

    /**
     * Broadcast event to WebSocket clients via Reverb
     */
    private function broadcastToClients(string $eventType, Attendance $attendance): void
    {
        try {
            match ($eventType) {
                'AttendanceCreated' => broadcast(new AttendanceCreated($attendance)),
                'AttendanceUpdated' => broadcast(new AttendanceUpdated($attendance)),
                default => Log::warning('Unknown event type', ['event' => $eventType])
            };
            
            Log::info('Event broadcasted to clients', [
                'event' => $eventType,
                'attendance_id' => $attendance->id
            ]);
        } catch (Throwable $e) {
            Log::error('Error broadcasting to clients', [
                'error' => $e->getMessage(),
                'event' => $eventType,
                'attendance_id' => $attendance->id
            ]);
        }
    }

    /**
     * Publish event to Redis pub/sub channel
     */
    public static function publish(string $eventType, int $attendanceId): void
    {
        try {
            $message = json_encode([
                'event' => $eventType,
                'attendance_id' => $attendanceId,
                'source_node' => config('app.node_id', 'unknown'),
                'timestamp' => now()->toISOString()
            ]);

            Redis::publish(self::CHANNEL, $message);
            
            Log::info('Event published to Redis', [
                'event' => $eventType,
                'attendance_id' => $attendanceId,
                'node_id' => config('app.node_id', 'unknown')
            ]);
        } catch (Throwable $e) {
            Log::warning('Failed to publish event to Redis, continuing with graceful degradation', [
                'error' => $e->getMessage(),
                'event' => $eventType,
                'attendance_id' => $attendanceId
            ]);
            
            // Graceful degradation: event still broadcasted locally via Laravel's event system
            // Real-time updates will work for clients connected to this node
        }
    }
}
