<?php

namespace App\Jobs;

use App\Models\ImportHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LogImportHistory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $transactionType;
    protected $totalRow;
    protected $successRow;
    protected $errorRow;
    protected $executionTime;

    /**
     * Create a new job instance.
     */
    public function __construct($userId, $transactionType, $totalRow, $successRow, $errorRow, $executionTime)
    {
        $this->userId = $userId;
        $this->transactionType = $transactionType;
        $this->totalRow = $totalRow;
        $this->successRow = $successRow;
        $this->errorRow = $errorRow;
        $this->executionTime = $executionTime;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Log untuk debugging
            Log::info('LogImportHistory job started', [
                'user_id' => $this->userId,
                'transaction_type' => $this->transactionType,
                'total_row' => $this->totalRow,
            ]);
            
            // Calculate success rate
            $successRate = $this->totalRow > 0 
                ? round(($this->successRow / $this->totalRow) * 100, 2) 
                : 0;
            
            $data = [
                'user_id' => $this->userId,
                'transaction_type' => $this->transactionType, // Pastikan ini terisi
                'total_row' => $this->totalRow,
                'success_row' => $this->successRow,
                'error_row' => $this->errorRow,
                'success_rate' => $successRate,
                'executed_date' => now(),
                'execution_time' => round($this->executionTime, 2),
            ];
            
            // Log data yang akan disimpan
            Log::info('Saving import history', $data);
            
            ImportHistory::create($data);
            
            Log::info('Import history saved successfully');
        } catch (\Exception $e) {
            Log::error('Failed to log import history', [
                'user_id' => $this->userId,
                'transaction_type' => $this->transactionType,
                'total_row' => $this->totalRow,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
