<?php

namespace App\Imports;

use App\Models\TransactionHeader;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TransactionHeaderImport implements 
    ToModel, 
    WithHeadingRow, 
    WithValidation, 
    SkipsEmptyRows, 
    SkipsOnFailure,
    WithChunkReading
{
    use SkipsFailures;

    protected $errors = [];
    protected $successCount = 0;
    protected $userBrandCodes = null; // Cache user brand codes
    public $currentRow = 1; // Start from 1 (header row) - public agar bisa diakses dari controller

    public function __construct()
    {
        // Cache user brand codes at the start of import
        $this->userBrandCodes = cache()->remember(
            'import_user_brands_' . auth()->id(),
            now()->addMinutes(10),
            function () {
                return auth()->user()->brands()->pluck('brand_code')->toArray();
            }
        );
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function model(array $row)
    {
        $this->currentRow++;
        
        try {
            // Log raw row data for debugging
            Log::info("Processing row {$this->currentRow}", ['data' => $row]);

            // Collect all validation errors for this row
            $rowErrors = [];

            // Validate required fields
            if (empty($row['wipno'])) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'WIPNO',
                    'value' => $row['wipno'] ?? 'empty',
                    'error' => 'WIPNO is required and cannot be empty'
                ];
            }

            // Validate WIPNO is numeric (integer only)
            $wipNo = $this->parseNumeric($row['wipno'] ?? null);
            if (!empty($row['wipno']) && ($wipNo === null || $wipNo === '')) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'WIPNO',
                    'value' => $row['wipno'] ?? 'empty',
                    'error' => 'WIPNO must be a valid integer number (e.g., 1, 123). Text values like "WIP000001" are not allowed.'
                ];
            }

            // Parse dates
            $invoiceDate = $this->parseDate($row['invdate'] ?? null);
            $registrationDate = $this->parseDate($row['regdate'] ?? null);

            if (empty($invoiceDate)) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'InvDate',
                    'value' => $row['invdate'] ?? 'empty',
                    'error' => 'Invoice Date is required and must be a valid date'
                ];
            }

            // Parse numeric fields
            $magicId = $this->parseNumeric($row['magich'] ?? null);
            $mileage = $this->parseNumeric($row['mileage'] ?? null);
            $invoiceNo = $this->parseNumeric($row['invno'] ?? null);
            $exchangeRate = $this->parseDecimal($row['exchangerate'] ?? null);
            $grossValue = $this->parseDecimal($row['grossvalue'] ?? null);
            $netValue = $this->parseDecimal($row['netvalue'] ?? null);

            // Validate required numeric fields
            if ($magicId === null || $magicId === '') {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'MAGICH',
                    'value' => $row['magich'] ?? 'empty',
                    'error' => '(MAGICH) is required and must be a valid number'
                ];
            }

            // InvNo boleh 0, tapi tidak boleh null atau empty string
            if ($invoiceNo === null || $invoiceNo === '') {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'InvNo',
                    'value' => $row['invno'] ?? 'empty',
                    'error' => 'Invoice Number is required and must be a valid integer number (0 is allowed, e.g., 0, 1, 123). Text values like "INV000001" are not allowed.'
                ];
            }

            // Mileage boleh 0, tapi tidak boleh null atau empty string
            if ($mileage === null || $mileage === '') {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'Mileage',
                    'value' => $row['mileage'] ?? 'empty',
                    'error' => 'Mileage is required and must be a valid number (0 is allowed)'
                ];
            }

            // Validate document type
            $docType = strtoupper($row['doctype'] ?? '');
            if (!in_array($docType, ['I', 'C'])) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'DocType',
                    'value' => $row['doctype'] ?? 'empty',
                    'error' => 'Document Type must be either I (Invoice) or C (Credit Note)'
                ];
            }

            // Validate currency code (max 3 chars)
            $currCode = strtoupper($row['currcode'] ?? '');
            if (empty($currCode) || strlen($currCode) > 3) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'CurrCode',
                    'value' => $row['currcode'] ?? 'empty',
                    'error' => 'Currency Code is required and must be 3 characters or less'
                ];
            }

            // Validate service_code (max 3 chars)
            $serviceCode = !empty($row['svccode']) ? strtoupper($row['svccode']) : null;
            if ($serviceCode !== null && strlen($serviceCode) > 3) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'SvcCode',
                    'value' => $row['svccode'],
                    'error' => 'Service Code must be 3 characters or less'
                ];
            }

            // Validate account_code (max 20 chars)
            if (!empty($row['account']) && strlen($row['account']) > 20) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'Account',
                    'value' => $row['account'],
                    'error' => 'Account Code must be 20 characters or less'
                ];
            }

            // Validate customer_name (max 150 chars)
            if (!empty($row['custname']) && strlen($row['custname']) > 150) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'CustName',
                    'value' => substr($row['custname'], 0, 50) . '...',
                    'error' => 'Customer Name must be 150 characters or less'
                ];
            }

            // Validate department (max 50 chars)
            if (!empty($row['dept']) && strlen($row['dept']) > 50) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'Dept',
                    'value' => $row['dept'],
                    'error' => 'Department must be 50 characters or less'
                ];
            }

            // Validate registration_no (max 20 chars)
            if (!empty($row['regno']) && strlen($row['regno']) > 20) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'RegNo',
                    'value' => $row['regno'],
                    'error' => 'Registration Number must be 20 characters or less'
                ];
            }

            // Validate chassis (max 25 chars)
            if (!empty($row['chassis']) && strlen($row['chassis']) > 25) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'Chassis',
                    'value' => $row['chassis'],
                    'error' => 'Chassis Number must be 25 characters or less'
                ];
            }

            // Validate customer_discount (max 10 chars)
            if (!empty($row['custdisc']) && strlen($row['custdisc']) > 10) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'CustDisc',
                    'value' => $row['custdisc'],
                    'error' => 'Customer Discount must be 10 characters or less'
                ];
            }

            // Validate description (max 250 chars)
            if (!empty($row['description']) && strlen($row['description']) > 250) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'Description',
                    'value' => substr($row['description'], 0, 50) . '...',
                    'error' => 'Description must be 250 characters or less'
                ];
            }

            // Validate engine_no (max 20 chars)
            if (!empty($row['engineno']) && strlen($row['engineno']) > 20) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'EngineNo',
                    'value' => $row['engineno'],
                    'error' => 'Engine Number must be 20 characters or less'
                ];
            }

            // Validate phone numbers (P1-P4) - optional, max 20 chars, numeric only
            for ($i = 1; $i <= 4; $i++) {
                $phoneField = 'p' . $i;
                if (!empty($row[$phoneField])) {
                    $phoneValue = (string)$row[$phoneField];
                    if (strlen($phoneValue) > 20) {
                        $rowErrors[] = [
                            'row' => $this->currentRow,
                            'field' => 'P' . $i,
                            'value' => $phoneValue,
                            'error' => 'Phone Number ' . $i . ' must be 20 characters or less'
                        ];
                    }
                    // Validate numeric only (allow string or number)
                    if (!preg_match('/^[0-9]+$/', $phoneValue)) {
                        $rowErrors[] = [
                            'row' => $this->currentRow,
                            'field' => 'P' . $i,
                            'value' => $phoneValue,
                            'error' => 'Phone Number ' . $i . ' must contain only numbers'
                        ];
                    }
                }
            }

            // Validate operator_code (Oper) - optional, max 20 chars
            if (!empty($row['oper']) && strlen($row['oper']) > 20) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'Oper',
                    'value' => $row['oper'],
                    'error' => 'Operator Code must be 20 characters or less'
                ];
            }

            // Validate operator_name (OperName) - optional, max 150 chars
            if (!empty($row['opername']) && strlen($row['opername']) > 150) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'OperName',
                    'value' => substr($row['opername'], 0, 50) . '...',
                    'error' => 'Operator Name must be 150 characters or less'
                ];
            }

            // Validate pos_code (PosCo) - REQUIRED, max 20 chars
            if (empty($row['posco'])) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'PosCo',
                    'value' => 'empty',
                    'error' => 'POS Code is required and cannot be empty'
                ];
            } elseif (strlen($row['posco']) > 20) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'PosCo',
                    'value' => $row['posco'],
                    'error' => 'POS Code must be 20 characters or less'
                ];
            }

            // Validate pos_code against user's brands (using cached data)
            if (!empty($row['posco'])) {
                if (!in_array($row['posco'], $this->userBrandCodes)) {
                    $rowErrors[] = [
                        'row' => $this->currentRow,
                        'field' => 'PosCo',
                        'value' => $row['posco'],
                        'error' => 'POS Company code "' . $row['posco'] . '" is not assigned to your user account. You can only import data for brands: ' . implode(', ', $this->userBrandCodes)
                    ];
                }
            }

            // Validate account_company (max 50 chars) - REQUIRED
            if (empty($row['accco'])) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'AccCo',
                    'value' => 'empty',
                    'error' => 'Account Company is required and cannot be empty'
                ];
            } elseif (strlen($row['accco']) > 50) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'AccCo',
                    'value' => $row['accco'],
                    'error' => 'Account Company must be 50 characters or less'
                ];
            }

            // If there are any validation errors, add them all and skip this row
            if (!empty($rowErrors)) {
                $this->errors = array_merge($this->errors, $rowErrors);
                return null;
            }

            // Check if record exists: wipno + invno + pos_code + magic_id
            $existing = TransactionHeader::where('wip_no', $wipNo)
                ->where('invoice_no', $invoiceNo)
                ->where('pos_code', $row['posco'])
                ->where('magic_id', $magicId)
                ->first();

            // Prepare data
            $data = [
                'wip_no' => $wipNo,
                'invoice_no' => $invoiceNo,
                'pos_code' => $row['posco'],
                'account_code' => $row['account'] ?? null,
                'customer_name' => $row['custname'] ?? null,
                'address_1' => $row['add1'] ?? null,
                'address_2' => $row['add2'] ?? null,
                'address_3' => $row['add3'] ?? null,
                'address_4' => $row['add4'] ?? null,
                'address_5' => $row['add5'] ?? null,
                'department' => $row['dept'] ?? null,
                'invoice_date' => $invoiceDate,
                'magic_id' => $magicId,
                'document_type' => $docType,
                'exchange_rate' => $exchangeRate,
                'registration_no' => $row['regno'] ?? null,
                'chassis' => $row['chassis'] ?? null,
                'mileage' => $mileage,
                'currency_code' => $currCode,
                'gross_value' => $grossValue ?? 0,
                'net_value' => $netValue ?? 0,
                'customer_discount' => $row['custdisc'] ?? '0',
                'service_code' => $serviceCode,
                'registration_date' => $registrationDate,
                'description' => $row['description'] ?? null,
                'engine_no' => $row['engineno'] ?? null,
                'phone_number_1' => !empty($row['p1']) ? (string)$row['p1'] : null,
                'phone_number_2' => !empty($row['p2']) ? (string)$row['p2'] : null,
                'phone_number_3' => !empty($row['p3']) ? (string)$row['p3'] : null,
                'phone_number_4' => !empty($row['p4']) ? (string)$row['p4'] : null,
                'operator_code' => $row['oper'] ?? null,
                'operator_name' => $row['opername'] ?? null,
                'account_company' => $row['accco'],
                'is_active' => '1',
            ];

            if ($existing) {
                // UPDATE: Record exists
                $data['updated_by'] = (string) Auth::id();
                $existing->update($data);
                $header = $existing;
                Log::info("Row {$this->currentRow} UPDATED", [
                    'header_id' => $header->header_id,
                    'wipno' => $wipNo,
                    'invno' => $invoiceNo
                ]);
            } else {
                // INSERT: Record not exists
                $data['created_by'] = (string) Auth::id();
                $data['unique_id'] = (string) \Illuminate\Support\Str::uuid();
                // header_id dibiarkan null (auto increment)
                $header = TransactionHeader::create($data);
                Log::info("Row {$this->currentRow} INSERTED", [
                    'header_id' => $header->header_id,
                    'wipno' => $wipNo,
                    'invno' => $invoiceNo
                ]);
            }

            $this->successCount++;
            return $header;

        } catch (\Illuminate\Database\QueryException $e) {
            // Handle SQL errors specifically
            $errorMessage = $e->getMessage();
            
            // Check for integer value error
            if (strpos($errorMessage, 'Incorrect integer value') !== false) {
                $this->errors[] = [
                    'row' => $this->currentRow,
                    'field' => 'WIPNO',
                    'value' => $row['wipno'] ?? 'N/A',
                    'error' => 'WIPNO must be a valid integer number. Text values like "WIP000001" are not allowed. Please use only numbers (e.g., 1, 123).'
                ];
            } else {
                $this->errors[] = [
                    'row' => $this->currentRow,
                    'field' => 'Database',
                    'value' => 'N/A',
                    'error' => 'Database error: ' . $errorMessage
                ];
            }
            
            Log::error("Database error importing row {$this->currentRow}", [
                'error' => $errorMessage,
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        } catch (\Exception $e) {
            $this->errors[] = [
                'row' => $this->currentRow,
                'field' => 'General',
                'value' => 'N/A',
                'error' => $e->getMessage()
            ];
            
            Log::error("Error importing row {$this->currentRow}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        }
    }

    public function rules(): array
    {
        return [
            'wipno' => 'required',
            'magich' => 'nullable|numeric',
            'mileage' => 'nullable|numeric',
            'exchangerate' => 'nullable|numeric',
            'grossvalue' => 'nullable|numeric',
            'netvalue' => 'nullable|numeric',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'wipno.required' => 'WIPNO is required.',
            'magich.numeric' => 'Vehicle ID (MAGICH) must be a number.',
            'mileage.numeric' => 'Mileage must be a number.',
            'exchangerate.numeric' => 'Exchange Rate must be a number.',
            'grossvalue.numeric' => 'Gross Value must be a number.',
            'netvalue.numeric' => 'Net Value must be a number.',
        ];
    }

    private function parseNumeric($value)
    {
        // Check if value is null or empty string (but allow 0)
        if ($value === null || $value === '') {
            return null;
        }
        
        // If value is already 0, return 0
        if ($value === 0 || $value === '0') {
            return 0;
        }
        
        // Remove any non-numeric characters except decimal point and minus
        //$cleaned = preg_replace('/[^0-9\-]/', '', $value);
        
        return is_numeric($value) ? (int)$value : null;
    }

    private function parseDecimal($value)
    {
        // Check if value is null or empty string (but allow 0)
        if ($value === null || $value === '') {
            return null;
        }
        
        // If value is already 0, return 0
        if ($value === 0 || $value === '0' || $value === 0.0 || $value === '0.0') {
            return 0;
        }
        
        // Remove any non-numeric characters except decimal point and minus
        $cleaned = preg_replace('/[^0-9\.\-]/', '', $value);
        
        return is_numeric($cleaned) ? (float)$cleaned : null;
    }

    private function parseDate($date)
    {
        if (empty($date)) {
            return null;
        }

        try {
            // Handle Excel date serial number
            if (is_numeric($date)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date));
            }
            
            // Handle string dates with various formats
            // Try common date formats: d/m/Y, d-m-Y, Y-m-d, etc.
            $formats = [
                'd/m/Y',    // 31/08/2009
                'd-m-Y',    // 31-08-2009
                'Y-m-d',    // 2009-08-31
                'd/m/y',    // 31/08/09
                'd-m-y',    // 31-08-09
                'm/d/Y',    // 08/31/2009
                'm-d-Y',    // 08-31-2009
            ];
            
            foreach ($formats as $format) {
                try {
                    $parsed = Carbon::createFromFormat($format, $date);
                    if ($parsed !== false) {
                        return $parsed;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
            
            // If all formats fail, try Carbon::parse as fallback
            return Carbon::parse($date);
        } catch (\Exception $e) {
            Log::warning("Failed to parse date: {$date}", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Chunk size for reading Excel file
     * Process 1000 rows at a time for better memory management
     */
    public function chunkSize(): int
    {
        return 1000;
    }

    /**
     * Clear cache after import
     */
    public function clearCache()
    {
        cache()->forget('import_user_brands_' . auth()->id());
    }
}

    