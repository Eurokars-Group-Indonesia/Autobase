<?php

namespace App\Imports;

use App\Models\TransactionBody;
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

class TransactionBodyImport implements 
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
    public $currentRow = 1;

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
            Log::info("Processing row {$this->currentRow}", ['data' => $row]);

            // Array to collect all errors for this row
            $rowErrors = [];

            // Validate required fields
            if (empty($row['part'])) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'Part',
                    'value' => $row['part'] ?? 'empty',
                    'error' => 'Part is required and cannot be empty'
                ];
            }

            if (empty($row['invno'])) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'InvNo',
                    'value' => $row['invno'] ?? 'empty',
                    'error' => 'Invoice Number is required and cannot be empty'
                ];
            }

            if (empty($row['wipno'])) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'WIPNo',
                    'value' => $row['wipno'] ?? 'empty',
                    'error' => 'WIP Number is required and cannot be empty'
                ];
            }

            if (empty($row['line'])) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'Line',
                    'value' => $row['line'] ?? 'empty',
                    'error' => 'Line is required and cannot be empty'
                ];
            }

            // Parse numeric fields
            $invoiceNo = $this->parseNumeric($row['invno'] ?? null);
            $wipNo = $this->parseNumeric($row['wipno'] ?? null);
            $line = $this->parseNumeric($row['line'] ?? null);
            $qty = $this->parseDecimal($row['qty'] ?? 0);
            $sellingPrice = $this->parseDecimal($row['sellprice'] ?? 0);
            $discount = $this->parseDecimal($row['disc'] ?? 0);
            $extendedPrice = $this->parseDecimal($row['extprice'] ?? 0);
            $menuPrice = $this->parseDecimal($row['mp'] ?? 0);
            $costPrice = $this->parseDecimal($row['costpr'] ?? 0);
            $contribution = $this->parseDecimal($row['contrib'] ?? 0);
            $currencyPrice = $this->parseDecimal($row['curprice'] ?? null);
            $minsPerUnit = $this->parseNumeric($row['mpu'] ?? null);
            $magic1 = $this->parseNumeric($row['hmagic1'] ?? 0);
            $magic2 = $this->parseNumeric($row['hmagic2'] ?? 0);
            $poNo = $this->parseNumeric($row['po'] ?? null);
            $grnNo = $this->parseNumeric($row['grn'] ?? null);
            $menuCode = $this->parseNumeric($row['menu'] ?? null);
            $menuLink = $this->parseNumeric($row['menulink'] ?? 0);

            // Parse date
            $dateDecard = $this->parseDate($row['datedecard'] ?? null);
            
            // Debug logging for date parsing
            if (!empty($row['datedecard'])) {
                Log::debug("Date parsing for row {$this->currentRow}", [
                    'original_value' => $row['datedecard'],
                    'parsed_value' => $dateDecard ? $dateDecard->format('Y-m-d') : 'null',
                    'is_numeric' => is_numeric($row['datedecard']),
                ]);
            }

            // Validate required numeric fields
            // InvNo boleh 0, tapi tidak boleh null atau empty string
            if ($invoiceNo === null || $invoiceNo === '') {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'InvNo',
                    'value' => $row['invno'] ?? 'empty',
                    'error' => 'Invoice Number must be a valid number (0 is allowed)'
                ];
            }

            if ($wipNo === null || $wipNo === '') {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'WIPNo',
                    'value' => $row['wipno'] ?? 'empty',
                    'error' => 'WIP Number must be a valid integer number (e.g., 1, 123). Text values like "WIP000001" are not allowed.'
                ];
            }

            if ($line === null || $line === '') {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'Line',
                    'value' => $row['line'] ?? 'empty',
                    'error' => 'Line must be a valid number'
                ];
            }

            // Validate required numeric fields
            if ($magic2 === null || $magic2 === '') {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'HMagic2',
                    'value' => $row['hmagic2'] ?? 'empty',
                    'error' => '(HMagic2) is required and must be a valid number'
                ];
            }

            // Validate analysis_code (required, 1 char)
            $analysisCode = strtoupper($row['analcode'] ?? '');
            if (empty($analysisCode) || strlen($analysisCode) > 1) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'AnalCode',
                    'value' => $row['analcode'] ?? 'empty',
                    'error' => 'Analysis Code is required and must be 1 character'
                ];
            }

            // Validate invoice_status (required, 1 char, X or C)
            $invoiceStatus = strtoupper($row['invstat'] ?? '');
            if (!in_array($invoiceStatus, ['X', 'C'])) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'InvStat',
                    'value' => $row['invstat'] ?? 'empty',
                    'error' => 'Invoice Status must be either X (Closed) or C (Completed)'
                ];
            }

            // Validate sales_type (required, 1 char)
            $salesType = strtoupper($row['saletype'] ?? '');
            if (empty($salesType) || strlen($salesType) > 1) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'SaleType',
                    'value' => $row['saletype'] ?? 'empty',
                    'error' => 'Sales Type is required and must be 1 character'
                ];
            }

            // Validate warranty_code (optional, max 3 chars)
            $warrantyCode = !empty($row['wcode']) ? strtoupper($row['wcode']) : null;
            if ($warrantyCode !== null && strlen($warrantyCode) > 3) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'Wcode',
                    'value' => $row['wcode'],
                    'error' => 'Warranty Code must be 3 characters or less'
                ];
            }

            // Validate part_or_labour (required, P or L)
            $partOrLabour = strtoupper($row['partslabour'] ?? '');
            if (!in_array($partOrLabour, ['P', 'L'])) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'Parts/Labour',
                    'value' => $row['partslabour'] ?? 'empty',
                    'error' => 'Parts/Labour must be either P (Part) or L (Labour)'
                ];
            }

            // Validate part_no (max 100 chars)
            if (strlen($row['part']) > 100) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'Part',
                    'value' => substr($row['part'], 0, 50) . '...',
                    'error' => 'Part Number must be 100 characters or less'
                ];
            }

            // Validate description (max 250 chars)
            if (!empty($row['desc']) && strlen($row['desc']) > 250) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'Desc',
                    'value' => substr($row['desc'], 0, 50) . '...',
                    'error' => 'Description must be 250 characters or less'
                ];
            }

            // Validate unit (max 10 chars)
            if (!empty($row['uoi']) && strlen($row['uoi']) > 10) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'UOI',
                    'value' => $row['uoi'],
                    'error' => 'Unit must be 10 characters or less'
                ];
            }

            // Validate account_code (max 20 chars)
            if (!empty($row['acct']) && strlen($row['acct']) > 20) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'Acct',
                    'value' => $row['acct'],
                    'error' => 'Account Code must be 20 characters or less'
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

            // Validate franchise_code (max 3 chars)
            if (!empty($row['fc']) && strlen($row['fc']) > 3) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'FC',
                    'value' => $row['fc'],
                    'error' => 'Franchise Code must be 3 characters or less'
                ];
            }

            // Validate supplier_code (max 20 chars)
            if (!empty($row['supp']) && strlen($row['supp']) > 20) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'Supp',
                    'value' => $row['supp'],
                    'error' => 'Supplier Code must be 20 characters or less'
                ];
            }

            // Validate VAT (max 1 char)
            $vat = !empty($row['vat']) ? strtoupper($row['vat']) : null;
            if ($vat !== null && strlen($vat) > 1) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'VAT',
                    'value' => $row['vat'],
                    'error' => 'VAT must be 1 character or less'
                ];
            }

            // Validate Menu VAT (max 1 char)
            $menuVat = !empty($row['mv']) ? strtoupper($row['mv']) : null;
            if ($menuVat !== null && strlen($menuVat) > 1) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'MV (Menu VAT)',
                    'value' => $row['mv'],
                    'error' => 'Menu VAT must be 1 character or less'
                ];
            }

            // Validate Menu Flag (max 1 char)
            $menuFlag = !empty($row['menuflag']) ? strtoupper($row['menuflag']) : null;
            if ($menuFlag !== null && strlen($menuFlag) > 1) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'MenuFlag',
                    'value' => $row['menuflag'],
                    'error' => 'Menu Flag must be 1 character or less'
                ];
            }

            // Validate Labour Rates (max 1 char)
            $labourRates = !empty($row['lr']) ? strtoupper($row['lr']) : null;
            if ($labourRates !== null && strlen($labourRates) > 1) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'LR (Labour Rates)',
                    'value' => $row['lr'],
                    'error' => 'Labour Rates must be 1 character or less'
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
                        'error' => 'POS Code "' . $row['posco'] . '" is not assigned to your user account. You can only import data for brands: ' . implode(', ', $this->userBrandCodes)
                    ];
                }
            }

            // Validate operator_code (COper) - NULLABLE, max 20 chars
            if (!empty($row['coper']) && strlen($row['coper']) > 20) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'COper',
                    'value' => $row['coper'],
                    'error' => 'Operator Code must be 20 characters or less'
                ];
            }

            // Validate operator_name (COperName) - NULLABLE, max 150 chars
            if (!empty($row['copername']) && strlen($row['copername']) > 150) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'COperName',
                    'value' => $row['copername'],
                    'error' => 'Operator Name must be 150 characters or less'
                ];
            }

            // If there are any validation errors, add them all and skip this row
            if (!empty($rowErrors)) {
                $this->errors = array_merge($this->errors, $rowErrors);
                return null;
            }

            // Check if record exists: part_no + invoice_no + wip_no + line + pos_code + magic_2
            $existing = TransactionBody::where('part_no', $row['part'])
                ->where('invoice_no', $invoiceNo)
                ->where('wip_no', $wipNo)
                ->where('line', $line)
                ->where('magic_2', $magic2)
                ->where('pos_code', $row['posco'])
                ->first();

            // Prepare data
            $data = [
                'part_no' => $row['part'],
                'invoice_no' => $invoiceNo,
                'pos_code' => $row['posco'],
                'wip_no' => $wipNo,
                'line' => $line,
                'description' => $row['desc'] ?? null,
                'qty' => $qty,
                'selling_price' => $sellingPrice,
                'discount' => $discount,
                'extended_price' => $extendedPrice,
                'menu_price' => $menuPrice,
                'vat' => strtoupper($row['vat'] ?? ''),
                'menu_vat' => strtoupper($row['mv'] ?? ''),
                'cost_price' => $costPrice,
                'analysis_code' => $analysisCode,
                'invoice_status' => $invoiceStatus,
                'unit' => $row['uoi'] ?? null,
                'mins_per_unit' => $minsPerUnit,
                'account_code' => $row['acct'] ?? null,
                'department' => $row['dept'] ?? null,
                'franchise_code' => $row['fc'] ?? null,
                'sales_type' => $salesType,
                'warranty_code' => $warrantyCode,
                'menu_flag' => strtoupper($row['menuflag'] ?? ''),
                'contribution' => $contribution,
                'date_decard' => $dateDecard,
                'magic_1' => $magic1,
                'magic_2' => $magic2,
                'po_no' => $poNo,
                'grn_no' => $grnNo,
                'menu_code' => $menuCode,
                'labour_rates' => strtoupper($row['lr'] ?? ''),
                'supplier_code' => $row['supp'] ?? null,
                'menu_link' => $menuLink,
                'currency_price' => $currencyPrice,
                'part_or_labour' => $partOrLabour,
                'operator_code' => $row['coper'] ?? null,
                'operator_name' => $row['copername'] ?? null,
                'is_active' => '1',
            ];

            if ($existing) {
                // UPDATE: Record exists
                $data['updated_by'] = (string) Auth::id();
                $existing->update($data);
                $body = $existing;
                Log::info("Row {$this->currentRow} UPDATED", [
                    'body_id' => $body->body_id,
                    'part_no' => $row['part'],
                    'invno' => $invoiceNo,
                    'wipno' => $wipNo,
                    'line' => $line
                ]);
            } else {
                // INSERT: Record not exists
                $data['created_by'] = (string) Auth::id();
                $data['unique_id'] = (string) \Illuminate\Support\Str::uuid();
                $body = TransactionBody::create($data);
                Log::info("Row {$this->currentRow} INSERTED", [
                    'body_id' => $body->body_id,
                    'part_no' => $row['part'],
                    'invno' => $invoiceNo,
                    'wipno' => $wipNo,
                    'line' => $line
                ]);
            }

            $this->successCount++;
            return $body;

        } catch (\Illuminate\Database\QueryException $e) {
            // Handle SQL errors specifically
            $errorMessage = $e->getMessage();
            
            $rowErrors = [];
            // Check for integer value error
            if (strpos($errorMessage, 'Incorrect integer value') !== false) {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'WIPNo',
                    'value' => $row['wipno'] ?? 'N/A',
                    'error' => 'WIP Number must be a valid integer. Text values like "WIP000001" are not allowed. Please use only numbers (e.g., 1, 123).'
                ];
            } else {
                $rowErrors[] = [
                    'row' => $this->currentRow,
                    'field' => 'Database',
                    'value' => 'N/A',
                    'error' => 'Database error: ' . $errorMessage
                ];
            }
            
            $this->errors = array_merge($this->errors, $rowErrors);
            
            Log::error("Database error importing row {$this->currentRow}", [
                'error' => $errorMessage,
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        } catch (\Exception $e) {
            $rowErrors = [];
            $rowErrors[] = [
                'row' => $this->currentRow,
                'field' => 'General',
                'value' => 'N/A',
                'error' => $e->getMessage()
            ];
            
            $this->errors = array_merge($this->errors, $rowErrors);
            
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
            'part' => 'required',
            'invno' => 'required|numeric',
            'wipno' => 'required|numeric',
            'line' => 'required|numeric',
            'qty' => 'nullable|numeric',
            'sellprice' => 'nullable|numeric',
            'disc' => 'nullable|numeric',
            'extprice' => 'nullable|numeric',
            'mp' => 'nullable|numeric',
            'costpr' => 'nullable|numeric',
            'contrib' => 'nullable|numeric',
            'curprice' => 'nullable|numeric',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'part.required' => 'Part is required.',
            'invno.required' => 'Invoice Number is required.',
            'invno.numeric' => 'Invoice Number must be a number.',
            'wipno.required' => 'WIP Number is required.',
            'wipno.numeric' => 'WIP Number must be a number.',
            'line.required' => 'Line is required.',
            'line.numeric' => 'Line must be a number.',
            'qty.numeric' => 'Qty must be a number.',
            'sellprice.numeric' => 'Selling Price must be a number.',
            'disc.numeric' => 'Discount must be a number.',
            'extprice.numeric' => 'Extended Price must be a number.',
            'mp.numeric' => 'Menu Price must be a number.',
            'costpr.numeric' => 'Cost Price must be a number.',
            'contrib.numeric' => 'Contribution must be a number.',
            'curprice.numeric' => 'Currency Price must be a number.',
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
        if ($value === null || $value === '') {
            return null;
        }
        
        if ($value === 0 || $value === '0' || $value === 0.0 || $value === '0.0') {
            return 0;
        }
        
        $cleaned = preg_replace('/[^0-9\.\-]/', '', $value);
        
        return is_numeric($cleaned) ? (float)$cleaned : null;
    }

    private function parseDate($date)
    {
        if (empty($date) || $date === '' || $date === null) {
            return null;
        }

        try {
            // Handle Excel date serial number (numeric value)
            if (is_numeric($date)) {
                // Excel dates start from 1900-01-01 (serial 1)
                // Valid Excel dates are typically between 1 (1900-01-01) and 50000 (2036-11-05)
                if ($date > 0 && $date < 100000) {
                    try {
                        $parsed = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date));
                        // Validate the parsed date is reasonable (between 1900 and 2100)
                        if ($parsed->year >= 1900 && $parsed->year <= 2100) {
                            return $parsed;
                        }
                    } catch (\Exception $e) {
                        Log::warning("Failed to parse Excel date serial: {$date}", ['error' => $e->getMessage()]);
                    }
                }
                
                // If numeric but not valid Excel serial, try as Unix timestamp
                if ($date > 946684800 && $date < 2147483647) { // Between 2000-01-01 and 2038-01-19
                    try {
                        return Carbon::createFromTimestamp($date);
                    } catch (\Exception $e) {
                        Log::warning("Failed to parse Unix timestamp: {$date}", ['error' => $e->getMessage()]);
                    }
                }
            }
            
            // Handle string dates with various formats
            $dateString = trim((string)$date);
            
            // Try common date formats
            $formats = [
                'Y-m-d',    // 2009-08-31 (ISO format)
                'd/m/Y',    // 31/08/2009
                'd-m-Y',    // 31-08-2009
                'm/d/Y',    // 08/31/2009
                'm-d-Y',    // 08-31-2009
                'd/m/y',    // 31/08/09
                'd-m-y',    // 31-08-09
                'Y/m/d',    // 2009/08/31
                'd.m.Y',    // 31.08.2009
                'd M Y',    // 31 Aug 2009
                'd F Y',    // 31 August 2009
            ];
            
            foreach ($formats as $format) {
                try {
                    $parsed = Carbon::createFromFormat($format, $dateString);
                    if ($parsed !== false && $parsed->year >= 1900 && $parsed->year <= 2100) {
                        return $parsed;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
            
            // If all formats fail, try Carbon::parse as fallback
            try {
                $parsed = Carbon::parse($dateString);
                if ($parsed->year >= 1900 && $parsed->year <= 2100) {
                    return $parsed;
                }
            } catch (\Exception $e) {
                Log::warning("Failed to parse date with Carbon::parse: {$dateString}", ['error' => $e->getMessage()]);
            }
            
            // If everything fails, return null instead of invalid date
            Log::warning("Could not parse date, returning null: {$date}");
            return null;
            
        } catch (\Exception $e) {
            Log::error("Error in parseDate function", [
                'date' => $date,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
