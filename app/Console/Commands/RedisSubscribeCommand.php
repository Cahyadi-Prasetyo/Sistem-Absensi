<?php

namespace App\Console\Commands;

use App\Services\RedisEventSubscriber;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RedisSubscribeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis:subscribe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscribe to Redis pub/sub channel for inter-node event distribution';

    /**
     * Execute the console command.
     */
    public function handle(RedisEventSubscriber $subscriber): int
    {
        $nodeId = config('app.node_id', 'unknown');
        
        $this->info("Starting Redis subscriber for node: {$nodeId}");
        $this->info('Listening for attendance events...');
        $this->info('Press Ctrl+C to stop');
        
        Log::info('Redis subscriber command started', [
            'node_id' => $nodeId,
            'pid' => getmypid()
        ]);

        try {
            // This will block and listen for messages
            $subscriber->subscribe();
        } catch (\Throwable $e) {
            $this->error('Redis subscriber failed: ' . $e->getMessage());
            Log::error('Redis subscriber command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
