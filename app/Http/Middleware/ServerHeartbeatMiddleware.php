<?php

namespace App\Http\Middleware;

use App\Services\ServerStatusService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ServerHeartbeatMiddleware
{
    public function __construct(
        private ServerStatusService $serverStatusService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Send heartbeat setiap request (dengan throttle)
        $this->sendHeartbeatThrottled();

        return $next($request);
    }

    /**
     * Send heartbeat with throttling (max once per 10 seconds per node)
     */
    private function sendHeartbeatThrottled(): void
    {
        $nodeId = config('app.node_id') ?? env('APP_NODE_ID', 'unknown');
        $cacheKey = "heartbeat_throttle_{$nodeId}";
        
        // Check if we already sent heartbeat recently
        if (cache()->has($cacheKey)) {
            return;
        }
        
        // Send heartbeat
        $this->serverStatusService->sendHeartbeat();
        
        // Set throttle for 10 seconds
        cache()->put($cacheKey, true, 10);
    }
}
