<?php

namespace App\Http\Controllers;

use App\Models\TransactionBody;
use App\Imports\TransactionBodyImport;
use App\Exports\TransactionBodyExport;
use App\Jobs\LogSearchHistory;
use App\Jobs\LogImportHistory;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TransactionBodyController extends Controller
{
    public function index(Request $request)
    {
        // Start timing
        $startTime = microtime(true);
        
        // Get user's brand IDs (realtime query)
        $userBrandIds = auth()->user()->getBrandIds();
        
        // Get brands for dropdown filter
        $brands = \App\Models\Brand::where('is_active', '1')
            ->whereIn('brand_id', $userBrandIds)
            ->orderBy('brand_name')
            ->get();
        
        $query = TransactionBody::with('brand')
            ->where('tx_body.is_active', '1')
            ->orderBy('tx_body.created_date', 'desc');
        
        // Check if there's any search/filter parameter
        $hasSearch = $request->has('search') && $request->search != '';
        $hasDateFrom = $request->has('date_from') && $request->date_from != '';
        $hasDateTo = $request->has('date_to') && $request->date_to != '';
        $hasBrandFilter = $request->has('brand_code') && $request->brand_code != '';
        $hasFilter = $hasSearch || $hasDateFrom || $hasDateTo;
        
        // Filter by user's brands or specific brand if selected
        if ($hasBrandFilter) {
            $query->where('tx_body.pos_code', $request->brand_code);
        } elseif (!empty($userBrandIds)) {
            // Get brand codes for user's brands
            $userBrandCodes = \App\Models\Brand::whereIn('brand_id', $userBrandIds)
                ->pluck('brand_code')
                ->toArray();
            $query->whereIn('tx_body.pos_code', $userBrandCodes);
        }
        
        // Only use cache when there's search/filter (including brand filter for query optimization)
        $shouldUseCache = $hasFilter || $hasBrandFilter;
        
        if ($shouldUseCache) {
            // Generate cache key based on user and search parameters
            $userId = auth()->id();
            $search = $request->get('search', '');
            $dateFrom = $request->get('date_from', '');
            $dateTo = $request->get('date_to', '');
            $brandCode = $request->get('brand_code', '');
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);
            
            $cacheKey = "body:{$userId}:{$search}:{$dateFrom}:{$dateTo}:{$brandCode}:{$perPage}:{$page}";
            
            // Try to get from cache (1 hour)
            $transactions = cache()->remember($cacheKey, now()->addHour(), function () use ($request, $query) {
                // Search by text
                if ($request->has('search') && $request->search != '') {
                    $search = $request->search;
                    $query->where(function($q) use ($search) {
                        $q->where('part_no', 'like', $search . '%')
                          ->orWhere('invoice_no', 'like', $search . '%')
                          ->orWhere('wip_no', 'like', $search . '%');
                    });
                }
                
                // Filter by date range
                if ($request->has('date_from') && $request->date_from != '') {
                    $query->whereDate('date_decard', '>=', $request->date_from);
                }
                
                if ($request->has('date_to') && $request->date_to != '') {
                    $query->whereDate('date_decard', '<=', $request->date_to);
                }
                
                // Pagination
                $perPage = $request->get('per_page', 10);
                $perPageValue = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;
                
                return $query->paginate($perPageValue)->withQueryString();
            });
        } else {
            // No search/filter - execute query directly without cache
            $perPage = $request->get('per_page', 10);
            $perPageValue = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;
            $transactions = $query->paginate($perPageValue)->withQueryString();
        }
        
        // Calculate execution time
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;
        
        // Log search history asynchronously only if there's a search query or date filter
        if ($hasFilter) {
            LogSearchHistory::dispatch(
                auth()->id(),
                $request->get('search'),
                $request->get('date_from'),
                $request->get('date_to'),
                $executionTime,
                'B'
            );
        }
        
        // Return view without transactions data - will be loaded via AJAX
        return view('transaction-body.index', compact('brands'));
    }

    public function search(Request $request)
    {
        // Start timing
        $startTime = microtime(true);
        
        // Get user's brand IDs (realtime query)
        $userBrandIds = auth()->user()->getBrandIds();
        
        $query = TransactionBody::with('brand')
            ->where('tx_body.is_active', '1')
            ->orderBy('tx_body.created_date', 'desc');
        
        // Check if there's any search/filter parameter
        $hasSearch = $request->has('search') && $request->search != '';
        $hasDateFrom = $request->has('date_from') && $request->date_from != '';
        $hasDateTo = $request->has('date_to') && $request->date_to != '';
        $hasBrandFilter = $request->has('brand_code') && $request->brand_code != '';
        $hasFilter = $hasSearch || $hasDateFrom || $hasDateTo;
        
        // Filter by user's brands or specific brand if selected
        if ($hasBrandFilter) {
            $query->where('tx_body.pos_code', $request->brand_code);
        } elseif (!empty($userBrandIds)) {
            // Get brand codes for user's brands
            $userBrandCodes = \App\Models\Brand::whereIn('brand_id', $userBrandIds)
                ->pluck('brand_code')
                ->toArray();
            $query->whereIn('tx_body.pos_code', $userBrandCodes);
        }
        
        // Only use cache when there's search/filter (including brand filter for query optimization)
        $shouldUseCache = $hasFilter || $hasBrandFilter;
        
        if ($shouldUseCache) {
            // Generate cache key based on user and search parameters
            $userId = auth()->id();
            $search = $request->get('search', '');
            $dateFrom = $request->get('date_from', '');
            $dateTo = $request->get('date_to', '');
            $brandCode = $request->get('brand_code', '');
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);
            
            $cacheKey = "body:{$userId}:{$search}:{$dateFrom}:{$dateTo}:{$brandCode}:{$perPage}:{$page}";
            
            // Try to get from cache (1 hour)
            $transactions = cache()->remember($cacheKey, now()->addHour(), function () use ($request, $query) {
                // Search by text
                if ($request->has('search') && $request->search != '') {
                    $search = $request->search;
                    $query->where(function($q) use ($search) {
                        $q->where('part_no', 'like', $search . '%')
                          ->orWhere('invoice_no', 'like', $search . '%')
                          ->orWhere('wip_no', 'like', $search . '%');
                    });
                }
                
                // Filter by date range
                if ($request->has('date_from') && $request->date_from != '') {
                    $query->whereDate('date_decard', '>=', $request->date_from);
                }
                
                if ($request->has('date_to') && $request->date_to != '') {
                    $query->whereDate('date_decard', '<=', $request->date_to);
                }
                
                // Pagination
                $perPage = $request->get('per_page', 10);
                $perPageValue = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;
                
                return $query->paginate($perPageValue)->withQueryString();
            });
        } else {
            // No search/filter - execute query directly without cache
            $perPage = $request->get('per_page', 10);
            $perPageValue = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;
            $transactions = $query->paginate($perPageValue)->withQueryString();
        }
        
        // Calculate execution time
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;
        
        // Log search history asynchronously only if there's a search query or date filter
        if ($hasFilter) {
            LogSearchHistory::dispatch(
                auth()->id(),
                $request->get('search'),
                $request->get('date_from'),
                $request->get('date_to'),
                $executionTime,
                'B'
            );
        }
        
        // Return JSON response for AJAX
        return response()->json([
            'success' => true,
            'hasFilter' => $hasFilter,
            'html' => view('transaction-body.partials.table', compact('transactions'))->render(),
            'pagination' => view('transaction-body.partials.pagination', compact('transactions'))->render()
        ]);
    }

    public function showImport()
    {
        return view('transaction-body.import');
    }

    public function import(Request $request)
    {
        // Start timing
        $startTime = microtime(true);
        
        $request->validate([
            'file' => [
                'required',
                'file',
                'max:10240',
            ],
        ], [
            'file.required' => 'Please select a file to upload.',
            'file.max' => 'File size must not exceed 10MB.',
        ]);

        // Manual validation for file extension
        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = ['csv', 'xls', 'xlsx'];
        
        if (!in_array($extension, $allowedExtensions)) {
            return redirect()->route('transaction-body.import')
                ->withErrors(['file' => 'File must be in CSV, XLS, or XLSX format.'])
                ->withInput();
        }

        try {
            $import = new TransactionBodyImport();
            Excel::import($import, $file);

            // Get custom errors from import class
            $customErrors = $import->getErrors();
            $successCount = $import->getSuccessCount();
            
            // Get validation failures
            $failures = $import->failures();
            
            // Combine all errors
            $allErrors = [];
            
            // Add custom errors
            foreach ($customErrors as $error) {
                $allErrors[] = [
                    'row' => $error['row'],
                    'field' => $error['field'],
                    'value' => $error['value'],
                    'error' => $error['error']
                ];
            }
            
            // Add validation failures
            foreach ($failures as $failure) {
                $allErrors[] = [
                    'row' => $failure->row(),
                    'field' => $failure->attribute(),
                    'value' => $failure->values()[$failure->attribute()] ?? 'N/A',
                    'error' => implode(', ', $failure->errors())
                ];
            }
            
            // Calculate execution time
            $endTime = microtime(true);
            $executionTime = ($endTime - $startTime) * 1000;
            
            // Calculate total rows (success + errors)
            $totalRows = $successCount + count($allErrors);
            
            // Log import history asynchronously
            LogImportHistory::dispatch(
                auth()->id(),
                'B',
                $totalRows,
                $successCount,
                count($allErrors),
                $executionTime
            );
            
            if (count($allErrors) > 0) {
                // Clear cache after import (even with errors, some data might be imported)
                $this->clearTransactionBodyCache();
                
                // Clear import cache
                $import->clearCache();
                
                return redirect()->route('transaction-body.import')
                    ->with('import_errors', $allErrors)
                    ->with('success_count', $successCount)
                    ->with('error', "Import completed with {$successCount} success and " . count($allErrors) . " error(s). Please check the details below.");
            }

            // Clear cache after successful import
            $this->clearTransactionBodyCache();
            
            // Clear import cache
            $import->clearCache();

            return redirect()->route('transaction-body.index')
                ->with('success', "Transaction bodies imported successfully! {$successCount} records imported.");
        } catch (\Illuminate\Database\QueryException $e) {
            // Clear cache on error
            $this->clearTransactionBodyCache();
            
            // Handle SQL errors with detailed messages
            $errorDetails = $this->parseSqlErrorDetailed($e, isset($import) ? $import : null);
            
            \Log::error('Import SQL Error', [
                'message' => $e->getMessage(),
                'sql' => $e->getSql() ?? 'N/A',
                'bindings' => $e->getBindings() ?? []
            ]);
            
            return redirect()->route('transaction-body.import')
                ->with('sql_error', $errorDetails);
        } catch (\Exception $e) {
            // Clear cache on error
            $this->clearTransactionBodyCache();
            
            \Log::error('Import Error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return redirect()->route('transaction-body.import')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    private function clearTransactionBodyCache()
    {
        cache()->flush();
        \Log::info('All cache cleared after import by user: ' . auth()->id());
    }

    private function parseSqlErrorDetailed($exception, $import = null)
    {
        $message = $exception->getMessage();
        $errors = [];
        
        $currentRow = $import ? $import->currentRow : 'Unknown';
        
        // Parse Incorrect integer value
        if (strpos($message, 'Incorrect integer value') !== false) {
            preg_match_all("/Incorrect integer value: '([^']+)' for column '([^']+)' at row (\d+)/", $message, $matches, PREG_SET_ORDER);
            
            if (count($matches) > 0) {
                foreach ($matches as $match) {
                    $value = $match[1];
                    $column = $match[2];
                    $row = $match[3] + 1;
                    $friendlyColumn = $this->getFriendlyColumnName($column);
                    
                    $errors[] = [
                        'row' => $row,
                        'field' => $friendlyColumn,
                        'value' => $value,
                        'error' => "'{$value}' is not a valid number. Please ensure this field contains only numeric values."
                    ];
                }
                return $errors;
            }
        }
        
        // Parse Data too long
        if (strpos($message, 'Data too long for column') !== false) {
            preg_match("/Data too long for column '([^']+)' at row (\d+)/", $message, $matches);
            if (count($matches) >= 3) {
                $column = $matches[1];
                $row = $matches[2] + 1;
                $friendlyColumn = $this->getFriendlyColumnName($column);
                
                $errors[] = [
                    'row' => $row,
                    'field' => $friendlyColumn,
                    'value' => 'Too long',
                    'error' => "The value exceeds the maximum allowed length for this field."
                ];
                return $errors;
            }
        }
        
        // Parse Incorrect date value
        if (strpos($message, 'Incorrect date value') !== false || strpos($message, 'Incorrect datetime value') !== false) {
            preg_match("/Incorrect (?:date|datetime) value: '([^']+)' for column '([^']+)' at row (\d+)/", $message, $matches);
            if (count($matches) >= 4) {
                $value = $matches[1];
                $column = $matches[2];
                $row = $matches[3] + 1;
                $friendlyColumn = $this->getFriendlyColumnName($column);
                
                $errors[] = [
                    'row' => $row,
                    'field' => $friendlyColumn,
                    'value' => $value,
                    'error' => "Invalid date format. Please use YYYY-MM-DD format (e.g., 2026-01-22)."
                ];
                return $errors;
            }
        }
        
        // Parse Column cannot be null
        if (strpos($message, 'cannot be null') !== false) {
            preg_match("/Column '([^']+)' cannot be null/", $message, $matches);
            if (count($matches) >= 2) {
                $column = $matches[1];
                $friendlyColumn = $this->getFriendlyColumnName($column);
                
                $errors[] = [
                    'row' => $currentRow,
                    'field' => $friendlyColumn,
                    'value' => 'NULL',
                    'error' => "This field is required and cannot be empty."
                ];
                return $errors;
            }
        }
        
        // Generic error
        $cleanMessage = str_replace('Database error: ', '', $message);
        $cleanMessage = str_replace('SQLSTATE[', '', $cleanMessage);
        $cleanMessage = preg_replace('/\[.*?\]/', '', $cleanMessage);
        $cleanMessage = substr($cleanMessage, 0, 200);
        
        $errors[] = [
            'row' => $currentRow,
            'field' => 'System',
            'value' => 'N/A',
            'error' => "An error occurred: " . trim($cleanMessage)
        ];
        
        return $errors;
    }

    private function getFriendlyColumnName($column)
    {
        $columnMap = [
            'body_id' => 'Body ID',
            'part_no' => 'Part Number (Part)',
            'invoice_no' => 'Invoice Number (InvNo)',
            'brand_code' => 'Brand',
            'description' => 'Description (Desc)',
            'qty' => 'Quantity (Qty)',
            'selling_price' => 'Selling Price (SellPrice)',
            'discount' => 'Discount (Disc%)',
            'extended_price' => 'Extended Price (ExtPrice)',
            'menu_price' => 'Menu Price (MP)',
            'vat' => 'VAT',
            'menu_vat' => 'Menu VAT (MV)',
            'cost_price' => 'Cost Price (CostPr)',
            'analysis_code' => 'Analysis Code (AnalCode)',
            'invoice_status' => 'Invoice Status (InvStat)',
            'unit' => 'Unit (UOI)',
            'mins_per_unit' => 'Minutes Per Unit (MpU)',
            'wip_no' => 'WIP Number (WIPNo)',
            'line' => 'Line',
            'account_code' => 'Account Code (Acct)',
            'department' => 'Department (Dept)',
            'franchise_code' => 'Franchise Code (FC)',
            'sales_type' => 'Sales Type (SaleType)',
            'warranty_code' => 'Warranty Code (Wcode)',
            'menu_flag' => 'Menu Flag',
            'contribution' => 'Contribution (Contrib)',
            'date_decard' => 'Date Decard',
            'magic_1' => 'Magic 1 (HMagic1)',
            'magic_2' => 'Magic 2 (HMagic2)',
            'po_no' => 'PO Number (PO)',
            'grn_no' => 'GRN Number (GRN)',
            'menu_code' => 'Menu Code (Menu)',
            'labour_rates' => 'Labour Rates (LR)',
            'supplier_code' => 'Supplier Code (Supp)',
            'menu_link' => 'Menu Link',
            'currency_price' => 'Currency Price (CurPrice)',
            'part_or_labour' => 'Parts/Labour',
            'operator_code' => 'Operator Code (COper)',
            'operator_name' => 'Operator Name (COperName)',
            'pos_code' => 'POS Code (PosCo)',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'unique_id' => 'Unique ID',
            'is_active' => 'Active Status',
        ];
        
        return $columnMap[$column] ?? ucwords(str_replace('_', ' ', $column));
    }

    public function downloadTemplate()
    {
        $headers = [
            'Part',
            'Desc',
            'Qty',
            'SellPrice',
            'Disc%',
            'ExtPrice',
            'MP',
            'VAT',
            'MV',
            'CostPr',
            'AnalCode',
            'InvStat',
            'UOI',
            'MpU',
            'WIPNo',
            'Line',
            'Acct',
            'Dept',
            'InvNo',
            'FC',
            'SaleType',
            'Wcode',
            'MenuFlag',
            'Contrib',
            'DateDecard',
            'HMagic1',
            'HMagic2',
            'PO',
            'GRN',
            'Menu',
            'LR',
            'Supp',
            'MenuLink',
            'CurPrice',
            'Parts/Labour',
            'COper',
            'COperName',
            'PosCo',
        ];

        $filename = 'transaction_body_template.csv';
        
        $callback = function() use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function export(Request $request)
    {
        $search = $request->get('search');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $brandCode = $request->get('brand_code');

        // Check if there's any filter (search or date, not just brand)
        $hasFilter = !empty($search) || !empty($dateFrom) || !empty($dateTo);

        // Only allow export when there's search or date filter
        if (!$hasFilter) {
            return redirect()->route('transaction-body.index')
                ->with('error', 'Please apply search or date filter before exporting.');
        }

        // Get user's brand IDs (realtime query)
        $userBrandIds = auth()->user()->getBrandIds();
        
        // Get brand codes for user's brands
        $userBrandCodes = \App\Models\Brand::whereIn('brand_id', $userBrandIds)
            ->pluck('brand_code')
            ->toArray();

        $filename = 'transaction_bodies_' . date('Y-m-d_His') . '.xlsx';

        // Export with brand filter
        return Excel::download(
            new TransactionBodyExport($search, $dateFrom, $dateTo, $userBrandCodes, $brandCode),
            $filename
        );
    }
}
