<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class ServerStatusService
{
    /**
     * Get status of all nodes
     */
    public function getServerStatus(): array
    {
        $nodes = [
            'app-node-1' => 'Server Jakarta',
            'app-node-2' => 'Server Bandung',
            'app-node-3' => 'Server Surabaya',
            'app-node-4' => 'Server Bali',
        ];

        $status = [];

        foreach ($nodes as $nodeId => $nodeName) {
            $status[] = [
                'node_id' => $nodeId,
                'name' => $nodeName,
                'status' => $this->checkNodeHealth($nodeId),
                'last_sync' => $this->getLastSyncTime($nodeId),
            ];
        }

        return $status;
    }

    /**
     * Check if node is healthy
     */
    private function checkNodeHealth(string $nodeId): string
    {
        try {
            // Check if Redis is available
            if (!extension_loaded('redis')) {
                // If Redis extension not loaded, only current node is online
                return $nodeId === env('APP_NODE_ID') ? 'online' : 'offline';
            }
            
            // Try to get node heartbeat from Redis
            $heartbeat = Redis::get("node:{$nodeId}:heartbeat");
            
            if ($heartbeat) {
                $lastHeartbeat = Carbon::parse($heartbeat);
                $secondsAgo = now()->diffInSeconds($lastHeartbeat);
                
                // Consider node online if heartbeat within last 30 seconds
                return $secondsAgo < 30 ? 'online' : 'offline';
            }
            
            // If no heartbeat in Redis, check if it's current node
            if ($nodeId === env('APP_NODE_ID')) {
                return 'online';
            }
            
            return 'offline';
            
        } catch (\Exception $e) {
            // If Redis is down, assume offline
            return 'offline';
        }
    }

    /**
     * Get last sync time for node
     */
    private function getLastSyncTime(string $nodeId): string
    {
        try {
            // Check if Redis is available
            if (!extension_loaded('redis')) {
                return $nodeId === env('APP_NODE_ID') ? 'baru saja' : 'tidak diketahui';
            }
            
            $heartbeat = Redis::get("node:{$nodeId}:heartbeat");
            
            if ($heartbeat) {
                $lastHeartbeat = Carbon::parse($heartbeat);
                return $lastHeartbeat->diffForHumans();
            }
            
            // If it's current node, return "just now"
            if ($nodeId === env('APP_NODE_ID')) {
                return 'baru saja';
            }
            
            return 'tidak diketahui';
            
        } catch (\Exception $e) {
            return 'tidak diketahui';
        }
    }

    /**
     * Update node heartbeat
     */
    public function updateHeartbeat(): void
    {
        try {
            if (!extension_loaded('redis')) {
                return;
            }
            
            $nodeId = env('APP_NODE_ID');
            Redis::setex("node:{$nodeId}:heartbeat", 60, now()->toIso8601String());
        } catch (\Exception $e) {
            // Silently fail if Redis is down
        }
    }
}
