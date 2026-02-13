<?php

namespace App\Jobs;

use App\Models\SearchHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LogSearchHistory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $search;
    protected $dateFrom;
    protected $dateTo;
    protected $executionTime;
    protected $transactionType;

    /**
     * Create a new job instance.
     */
    public function __construct($userId, $search, $dateFrom, $dateTo, $executionTime, $transactionType)
    {
        $this->userId = $userId;
        $this->search = $search;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->executionTime = $executionTime;
        $this->transactionType = $transactionType;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            SearchHistory::create([
                'user_id' => $this->userId,
                'search' => $this->search,
                'date_from' => $this->dateFrom,
                'date_to' => $this->dateTo,
                'executed_date' => now(),
                'execution_time' => round($this->executionTime, 2),
                'transaction_type' => $this->transactionType,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log search history', [
                'user_id' => $this->userId,
                'transaction_type' => $this->transactionType,
                'error' => $e->getMessage()
            ]);
        }
    }
}
