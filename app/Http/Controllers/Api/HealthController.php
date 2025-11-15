<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HealthController extends Controller
{
    public function check()
    {
        $nodeId = config('app.node_id', gethostname());
        $health = [
            'status' => 'healthy',
            'node_id' => $nodeId,
            'timestamp' => now()->toISOString(),
            'checks' => [],
        ];

        // Check database
        try {
            DB::connection()->getPdo();
            $health['checks']['database'] = 'ok';
        } catch (\Exception $e) {
            $health['checks']['database'] = 'error: ' . $e->getMessage();
            $health['status'] = 'unhealthy';
        }

        // Check Redis (optional)
        try {
            if (config('cache.default') === 'redis') {
                Redis::ping();
                $health['checks']['redis'] = 'ok';
            } else {
                $health['checks']['redis'] = 'not configured (using ' . config('cache.default') . ')';
            }
        } catch (\Exception $e) {
            $health['checks']['redis'] = 'not available';
            $health['status'] = 'degraded';
        }

        return response()->json($health);
    }

    public function stats()
    {
        $nodeId = config('app.node_id', gethostname());
        
        return response()->json([
            'node_id' => $nodeId,
            'timestamp' => now()->toISOString(),
            'stats' => [
                'total_attendances_today' => \App\Models\Attendance::whereDate('timestamp', today())->count(),
                'total_users' => \App\Models\User::count(),
                'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
                'php_version' => PHP_VERSION,
            ],
        ]);
    }
}
