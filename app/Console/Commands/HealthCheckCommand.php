<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HealthCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'health:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check application health status';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            // Check database connection
            DB::connection()->getPdo();
            
            // Check Redis connection (optional, won't fail if Redis is down)
            try {
                Redis::ping();
            } catch (\Exception $e) {
                // Redis is optional for basic health check
                $this->warn('Redis connection failed, but continuing...');
            }
            
            $this->info('Health check passed');
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Health check failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
