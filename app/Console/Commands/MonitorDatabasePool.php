<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;

class MonitorDatabasePool extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:pool:monitor {--watch : Watch mode - refresh every 2 seconds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor database connection pool status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $watch = $this->option('watch');

        if ($watch) {
            $this->info('Monitoring database pool (Press Ctrl+C to stop)...');
            $this->newLine();
            
            while (true) {
                $this->displayPoolStatus();
                sleep(2);
                
                // Clear screen for better readability
                if (PHP_OS_FAMILY === 'Windows') {
                    system('cls');
                } else {
                    system('clear');
                }
            }
        } else {
            $this->displayPoolStatus();
        }

        return 0;
    }

    /**
     * Display pool status
     */
    protected function displayPoolStatus()
    {
        $this->info('=== Database Connection Pool Status ===');
        $this->newLine();

        try {
            // Get connection info
            $connection = config('database.default');
            $config = config("database.connections.{$connection}");
            
            $this->table(
                ['Setting', 'Value'],
                [
                    ['Connection', $connection],
                    ['Driver', $config['driver'] ?? 'N/A'],
                    ['Host', $config['host'] ?? 'N/A'],
                    ['Database', $config['database'] ?? 'N/A'],
                    ['Persistent', $config['options'][PDO::ATTR_PERSISTENT] ?? false ? 'Yes' : 'No'],
                    ['Min Pool Size', $config['pool']['min_connections'] ?? 'N/A'],
                    ['Max Pool Size', $config['pool']['max_connections'] ?? 'N/A'],
                    ['Timeout', ($config['options'][PDO::ATTR_TIMEOUT] ?? 'N/A') . 's'],
                ]
            );

            $this->newLine();

            // Get MySQL status
            if (in_array($config['driver'], ['mysql', 'mariadb'])) {
                $this->info('=== MySQL Server Status ===');
                $this->newLine();

                $status = $this->getMySQLStatus();
                
                $this->table(
                    ['Metric', 'Value'],
                    $status
                );

                $this->newLine();

                // Connection health
                $threadsConnected = (int) collect($status)->firstWhere('Metric', 'Threads Connected')['Value'];
                $maxConnections = (int) collect($status)->firstWhere('Metric', 'Max Connections')['Value'];
                $usage = ($threadsConnected / $maxConnections) * 100;

                if ($usage > 80) {
                    $this->error("⚠️  High connection usage: {$usage}%");
                } elseif ($usage > 50) {
                    $this->warn("⚠️  Moderate connection usage: {$usage}%");
                } else {
                    $this->info("✓ Healthy connection usage: {$usage}%");
                }
            }

        } catch (\Exception $e) {
            $this->error('Error monitoring database pool: ' . $e->getMessage());
        }

        $this->newLine();
        $this->info('Last updated: ' . now()->format('Y-m-d H:i:s'));
    }

    /**
     * Get MySQL status
     */
    protected function getMySQLStatus(): array
    {
        $status = [];

        try {
            // Threads connected
            $result = DB::select("SHOW STATUS LIKE 'Threads_connected'");
            $status[] = ['Metric' => 'Threads Connected', 'Value' => $result[0]->Value ?? 'N/A'];

            // Max connections
            $result = DB::select("SHOW VARIABLES LIKE 'max_connections'");
            $status[] = ['Metric' => 'Max Connections', 'Value' => $result[0]->Value ?? 'N/A'];

            // Threads running
            $result = DB::select("SHOW STATUS LIKE 'Threads_running'");
            $status[] = ['Metric' => 'Threads Running', 'Value' => $result[0]->Value ?? 'N/A'];

            // Threads cached
            $result = DB::select("SHOW STATUS LIKE 'Threads_cached'");
            $status[] = ['Metric' => 'Threads Cached', 'Value' => $result[0]->Value ?? 'N/A'];

            // Aborted connections
            $result = DB::select("SHOW STATUS LIKE 'Aborted_connects'");
            $status[] = ['Metric' => 'Aborted Connects', 'Value' => $result[0]->Value ?? 'N/A'];

            // Connection errors
            $result = DB::select("SHOW STATUS LIKE 'Connection_errors_max_connections'");
            $status[] = ['Metric' => 'Connection Errors', 'Value' => $result[0]->Value ?? 'N/A'];

            // Uptime
            $result = DB::select("SHOW STATUS LIKE 'Uptime'");
            $uptime = $result[0]->Value ?? 0;
            $status[] = ['Metric' => 'Server Uptime', 'Value' => $this->formatUptime($uptime)];

        } catch (\Exception $e) {
            $status[] = ['Metric' => 'Error', 'Value' => $e->getMessage()];
        }

        return $status;
    }

    /**
     * Format uptime
     */
    protected function formatUptime($seconds): string
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        return "{$days}d {$hours}h {$minutes}m";
    }
}
