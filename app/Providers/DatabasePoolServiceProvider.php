<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PDO;

class DatabasePoolServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Monitor database connections
        if (config('app.debug')) {
            DB::listen(function ($query) {
                Log::debug('Database Query', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time . 'ms',
                    'connection' => $query->connectionName,
                ]);
            });
        }

        // Reconnect on connection lost
        DB::beforeExecuting(function ($query, $bindings, $connection) {
            try {
                $connection->getPdo();
            } catch (\Exception $e) {
                Log::warning('Database connection lost, attempting to reconnect...', [
                    'error' => $e->getMessage(),
                ]);
                $connection->reconnect();
            }
        });

        // Set connection pool limits
        $this->configureConnectionPool();
    }

    /**
     * Configure connection pool settings
     */
    protected function configureConnectionPool(): void
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if (isset($config['pool'])) {
            $minConnections = $config['pool']['min_connections'] ?? 2;
            $maxConnections = $config['pool']['max_connections'] ?? 10;

            // Set MySQL connection pool variables if using MySQL/MariaDB
            if (in_array($config['driver'], ['mysql', 'mariadb'])) {
                try {
                    // Set max connections on MySQL server
                    DB::statement("SET GLOBAL max_connections = ?", [$maxConnections * 2]);
                    
                    // Set connection timeout
                    DB::statement("SET GLOBAL connect_timeout = ?", [$config['options'][PDO::ATTR_TIMEOUT] ?? 5]);
                    
                    // Set wait timeout (how long to keep idle connections)
                    DB::statement("SET GLOBAL wait_timeout = 600");
                    DB::statement("SET GLOBAL interactive_timeout = 600");
                    
                    Log::info('Database connection pool configured', [
                        'min_connections' => $minConnections,
                        'max_connections' => $maxConnections,
                    ]);
                } catch (\Exception $e) {
                    // Silently fail if user doesn't have SUPER privilege
                    Log::debug('Could not set global MySQL variables (requires SUPER privilege)', [
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }
}
