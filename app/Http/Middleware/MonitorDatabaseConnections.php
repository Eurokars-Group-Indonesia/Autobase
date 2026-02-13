<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class MonitorDatabaseConnections
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $startQueries = $this->getQueryCount();

        $response = $next($request);

        $endTime = microtime(true);
        $endQueries = $this->getQueryCount();

        $executionTime = round(($endTime - $startTime) * 1000, 2);
        $queryCount = $endQueries - $startQueries;

        // Log if request took too long or had too many queries
        if ($executionTime > 1000 || $queryCount > 50) {
            Log::warning('Slow request or too many queries', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'execution_time' => $executionTime . 'ms',
                'query_count' => $queryCount,
                'memory_usage' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . 'MB',
            ]);
        }

        // Add headers for debugging (only in debug mode)
        if (config('app.debug')) {
            $response->headers->set('X-Database-Queries', $queryCount);
            $response->headers->set('X-Execution-Time', $executionTime . 'ms');
        }

        return $response;
    }

    /**
     * Get total query count
     */
    protected function getQueryCount(): int
    {
        $count = 0;
        foreach (DB::getConnections() as $connection) {
            $count += count($connection->getQueryLog());
        }
        return $count;
    }
}
