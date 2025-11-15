<?php

namespace App\Helpers;

class NodeHelper
{
    /**
     * Get current node identifier
     *
     * @return string
     */
    public static function getNodeId(): string
    {
        return env('APP_NODE_ID', 'unknown-node');
    }

    /**
     * Get node name for display
     *
     * @return string
     */
    public static function getNodeName(): string
    {
        $nodeId = self::getNodeId();
        
        // Map node IDs to friendly names
        $nodeNames = [
            'app-node-1' => 'Server Jakarta',
            'app-node-2' => 'Server Bandung',
            'app-node-3' => 'Server Surabaya',
            'app-node-4' => 'Server Bali',
            'dev-node-1' => 'Development Server',
        ];

        return $nodeNames[$nodeId] ?? $nodeId;
    }

    /**
     * Check if running in Docker environment
     *
     * @return bool
     */
    public static function isDockerEnvironment(): bool
    {
        return env('APP_ENV') === 'production' && str_starts_with(self::getNodeId(), 'app-node-');
    }
}
