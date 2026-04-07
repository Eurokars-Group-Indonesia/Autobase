<?php

namespace App\Exports;

use App\Models\TransactionHeader;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;

class TransactionHeaderExport implements FromCollection, WithStyles, WithEvents, ShouldAutoSize
{
    protected $search;
    protected $searchField;
    protected $dateFrom;
    protected $dateTo;
    protected $userBrandCodes;
    protected $brandCode;
    protected $canViewCostPrice;

    public function __construct($search = null, $dateFrom = null, $dateTo = null, $userBrandCodes = [], $brandCode = null, $searchField = '')
    {
        $this->search = $search;
        $this->searchField = $searchField;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->userBrandCodes = $userBrandCodes;
        $this->brandCode = $brandCode;
        $this->canViewCostPrice = auth()->user()->hasPermission('cost.price.view');
    }

    public function collection()
    {
        $query = TransactionHeader::with('brand')
            ->where('tx_header.is_active', '1')
            ->orderBy('tx_header.invoice_date', 'desc');

        // Filter by user's brands or specific brand if selected
        if (!empty($this->brandCode)) {
            $query->where('tx_header.pos_code', $this->brandCode);
        } elseif (!empty($this->userBrandCodes)) {
            $query->whereIn('tx_header.pos_code', $this->userBrandCodes);
        }

        // Apply filters
        if ($this->search) {
            $this->applySearchFilter($query, $this->search, $this->searchField);
        }

        if ($this->dateFrom) {
            $query->whereDate('tx_header.invoice_date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('tx_header.invoice_date', '<=', $this->dateTo);
        }

        $transactions = $query->get();
        // Get all bodies in one query using whereIn
        $transactionKeys = $transactions->map(function($t) {
            return $t->wip_no . '|' . $t->invoice_no . '|' . $t->magic_id;
        })->toArray();
        
        // Fetch all bodies at once
        $allBodies = \DB::table('tx_body')
            ->where('is_active', '1')
            ->whereIn(\DB::raw("CONCAT(wip_no, '|', invoice_no, '|', magic_2)"), $transactionKeys)
            ->orderBy('wip_no')
            ->orderBy('invoice_no')
            ->orderBy('line')
            ->get()
            ->groupBy(function($body) {
                return $body->wip_no . '|' . $body->invoice_no . '|' . $body->magic_2;
            });
        
        // Transform data to flat structure with 2 separate tables
        $rows = collect();
        
        foreach ($transactions as $transaction) {
            $key = $transaction->wip_no . '|' . $transaction->invoice_no . '|' . $transaction->magic_id;
            $bodies = $allBodies->get($key, collect());
            
            // Add empty row for spacing (except first transaction)
            if ($rows->count() > 0) {
                $rows->push(['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '']);
            }
            
            // Add HEADER TABLE TITLE with WIP No and Invoice No
            $rows->push([
                'WIP No: ' . $transaction->wip_no . ' | Invoice No: ' . $transaction->invoice_no. ' | Magic ID: ' . $transaction->magic_id,
                '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''
            ]);
            
            // Add header table headers
            $rows->push([
                'Invoice No',
                'WIP No',
                'MAGICH',
                'Invoice Date',
                'Account',
                'Account Name',
                'Customer Name',
                'Registration No',
                'Chassis',
                'Phone Number 1',
                'Phone Number 2',
                'Phone Number 3',
                'Phone Number 4',
                'Operator Code',
                'Operator Name',
                'Document Type',
                'POS Code',
                'Gross Value',
                'Net Value'
            ]);
            
            // Add header data
            $rows->push([
                $transaction->invoice_no,
                $transaction->wip_no,
                $transaction->magic_id ?? '',
                $transaction->invoice_date ? $transaction->invoice_date->format('d-m-Y') : '',
                $transaction->account_code ?? '',
                $transaction->account_name ?? '',
                $transaction->customer_name ?? '',
                $transaction->registration_no ?? '',
                $transaction->chassis ?? '',
                $transaction->phone_number_1 ?? '',
                $transaction->phone_number_2 ?? '',
                $transaction->phone_number_3 ?? '',
                $transaction->phone_number_4 ?? '',
                $transaction->operator_code ?? '',
                $transaction->operator_name ?? '',
                $transaction->getDocumentTypeLabel(),
                ($transaction->brand->brand_code ?? '') . ($transaction->brand ? ' - ' . $transaction->brand->brand_name : ''),
                $transaction->gross_value,
                $transaction->net_value
            ]);
            
            // Add empty row between tables
            $rows->push(['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '']);
            
            // Add body table headers (no title, just headers)
            $bodyHeaders = [
                'No',
                'Part No',
                'HMagic2',
                'Description',
                'Date Decard',
                'Qty',
            ];
            
            if ($this->canViewCostPrice) {
                $bodyHeaders[] = 'Cost Price';
            }
            
            $bodyHeaders = array_merge($bodyHeaders, [
                'Selling Price',
                'Discount %',
                'Extended Price',
                'Part/Labour',
                '', '', '', '', '', '', ''
            ]);
            
            $rows->push($bodyHeaders);
            
            // Add body rows
            if ($bodies->count() > 0) {
                $no = 1;
                foreach ($bodies as $body) {
                    $bodyRow = [
                        $no++,
                        $body->part_no,
                        $body->magic_2 ?? '',
                        $body->description ?? '',
                        $body->date_decard ? \Carbon\Carbon::parse($body->date_decard)->format('d-m-Y') : '',
                        $body->qty,
                    ];
                    
                    if ($this->canViewCostPrice) {
                        $bodyRow[] = $body->cost_price ?? 0;
                    }
                    
                    $bodyRow = array_merge($bodyRow, [
                        $body->selling_price,
                        $body->discount,
                        $body->extended_price,
                        $body->part_or_labour === 'P' ? 'Part' : 'Labour',
                        '', '', '', '', '', '', ''
                    ]);
                    
                    $rows->push($bodyRow);
                }
            } else {
                $rows->push([
                    'No body details available',
                    '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''
                ]);
            }
        }
        
        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [];
        
        // Default style for all cells
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A1:S' . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ]);
        
        return $styles;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                $highestRow = $sheet->getHighestRow();
                $headerTableRows = [];
                $bodyTableRows = [];
                
                // Loop through all rows to identify table sections and apply styling
                for ($row = 1; $row <= $highestRow; $row++) {
                    $cellValue = $sheet->getCell('A' . $row)->getValue();
                    
                    // Style for "WIP No: ... | Invoice No: ..." title
                    if (strpos($cellValue, 'WIP No:') !== false && strpos($cellValue, 'Invoice No:') !== false) {
                        $sheet->mergeCells('A' . $row . ':S' . $row);
                        $sheet->getStyle('A' . $row . ':S' . $row)->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 12,
                                'color' => ['rgb' => 'FFFFFF']
                            ],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => '002856']
                            ],
                            'alignment' => [
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            ],
                        ]);
                        $sheet->getRowDimension($row)->setRowHeight(25);
                        
                        // Mark next rows as header table
                        $headerTableRows[] = $row + 1; // Column headers
                        $headerTableRows[] = $row + 2; // Data row
                    }
                    
                    // Style for header table column headers (Invoice No)
                    if ($cellValue === 'Invoice No') {
                        $sheet->getStyle('A' . $row . ':S' . $row)->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => 'FFFFFF']
                            ],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => '4472C4']
                            ],
                            'alignment' => [
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            ],
                        ]);
                    }
                    
                    // Style for body table column headers (No)
                    if ($cellValue === 'No' && $sheet->getCell('B' . $row)->getValue() === 'Part No') {
                        $sheet->getStyle('A' . $row . ':S' . $row)->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => 'FFFFFF']
                            ],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => '4472C4']
                            ],
                            'alignment' => [
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            ],
                        ]);
                        
                        // Mark body table start
                        $bodyTableStart = $row;
                        
                        // Find body table end (next empty row or end of sheet)
                        $bodyTableEnd = $row;
                        for ($i = $row + 1; $i <= $highestRow; $i++) {
                            $checkValue = $sheet->getCell('A' . $i)->getValue();
                            if ($checkValue === '' || strpos($checkValue, 'WIP No:') !== false) {
                                $bodyTableEnd = $i - 1;
                                break;
                            }
                            $bodyTableEnd = $i;
                        }
                        
                        // Apply outer border to body table
                        $sheet->getStyle('A' . $bodyTableStart . ':K' . $bodyTableEnd)->applyFromArray([
                            'borders' => [
                                'outline' => [
                                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                                    'color' => ['rgb' => '000000'],
                                ],
                            ],
                        ]);
                    }
                }
                
                // Apply outer border to header tables
                for ($row = 1; $row <= $highestRow; $row++) {
                    $cellValue = $sheet->getCell('A' . $row)->getValue();
                    if ($cellValue === 'Invoice No') {
                        // Apply outer border to header table (2 rows: header + data)
                        $sheet->getStyle('A' . $row . ':S' . ($row + 1))->applyFromArray([
                            'borders' => [
                                'outline' => [
                                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                                    'color' => ['rgb' => '000000'],
                                ],
                            ],
                        ]);
                    }
                }
                
                // Apply thin borders to all cells
                $sheet->getStyle('A1:S' . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
            },
        ];
    }

    /**
     * Apply search filter based on search_field parameter
     */
    private function applySearchFilter(&$query, $search, $searchField = '')
    {
        if (empty($search)) {
            return;
        }

        // Check if search is a date format
        $isDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $search);
        $isPureDigits = preg_match('/^\d+$/', $search);

        $query->where(function($q) use ($search, $searchField, $isDate, $isPureDigits) {
            // Search in header fields
            $q->where(function($searchWhere) use ($search, $searchField, $isDate, $isPureDigits) {
                // If specific field is selected
                if (!empty($searchField)) {
                    $this->applyFieldSpecificSearch($searchWhere, $search, $searchField, $isPureDigits);
                } else {
                    // Search all fields (original logic)
                    $this->applyAllFieldsSearch($searchWhere, $search, $isPureDigits);
                }

                // Only add date search if format matches
                if ($isDate) {
                    $searchWhere->orWhere('tx_header.invoice_date', '=', $search);
                }
            })
            // Search in body fields using whereExists - optimized with limit
            ->orWhereExists(function($existsQuery) use ($search, $isDate) {
                $existsQuery->select(\DB::raw(1))
                            ->from('tx_body')
                            ->whereColumn('tx_body.wip_no', 'tx_header.wip_no')
                            ->whereColumn('tx_body.invoice_no', 'tx_header.invoice_no')
                            ->whereColumn('tx_body.magic_2', 'tx_header.magic_id')
                            ->whereColumn('tx_body.pos_code', 'tx_header.pos_code')
                            ->where('tx_body.is_active', '1')
                            ->where(function($bodyWhere) use ($search, $isDate) {
                                $bodyWhere->where('tx_body.part_no', 'like', $search . '%')
                                          ->orWhere('tx_body.wip_no', 'like', $search . '%')
                                          ->orWhere('tx_body.invoice_no', 'like', $search . '%');
                                
                                // Only add date search if format matches
                                if ($isDate) {
                                    $bodyWhere->orWhere('tx_body.date_decard', '=', $search);
                                }
                            })
                            ->limit(1);
            });
        });
    }

    /**
     * Apply search for specific field
     */
    private function applyFieldSpecificSearch(&$searchWhere, $search, $searchField, $isPureDigits)
    {
        switch ($searchField) {
            case 'customer_name':
                $this->applyTextSearch($searchWhere, $search, 'tx_header.customer_name');
                break;
            case 'registration_no':
                $this->applyTextSearch($searchWhere, $search, 'tx_header.registration_no');
                break;
            case 'chassis':
                $searchWhere->where('tx_header.chassis', 'like', $search . '%');
                break;
            case 'invoice_no':
                $searchWhere->where('tx_header.invoice_no', 'like', $search . '%');
                break;
            case 'wip_no':
                $searchWhere->where('tx_header.wip_no', 'like', $search . '%');
                break;
            case 'account_code':
                $searchWhere->where('tx_header.account_code', 'like', $search . '%');
                break;
            case 'account_name':
                $this->applyTextSearch($searchWhere, $search, 'tx_header.account_name');
                break;
            case 'phone_number':
                $this->applyPhoneNumberSearch($searchWhere, $search, $isPureDigits);
                break;
        }
    }

    /**
     * Apply search for all fields (original logic)
     */
    private function applyAllFieldsSearch(&$searchWhere, $search, $isPureDigits)
    {
        if ($isPureDigits) {
            // Pure digits - search in invoice_no, wip_no, chassis, account_code, AND phone numbers
            $phoneSearch = $search . '*';
            $searchWhere->where('tx_header.invoice_no', 'like', $search . '%')
                        ->orWhere('tx_header.wip_no', 'like', $search . '%')
                        ->orWhere('tx_header.chassis', 'like', $search . '%')
                        ->orWhere('tx_header.account_code', 'like', $search . '%')
                        ->orWhereRaw(
                            'MATCH(phone_number_1, phone_number_2, phone_number_3, phone_number_4) AGAINST(? IN BOOLEAN MODE)',
                            [$phoneSearch]
                        );
        } else {
            // Strip common titles/prefixes from search to improve matching
            $searchClean = preg_replace('/^(mr|mrs|ms|miss|dr|prof|sir|madam|lady|lord)\.?\s+/i', '', trim($search));
            
            // For text search, use FULLTEXT search without NGRAM parser
            $words = preg_split('/\s+/', $searchClean);
            $fulltextSearch = implode(' ', array_map(function($word) {
                return $word . '*';
            }, $words));
            
            // Use FULLTEXT search for customer_name, registration_no, account_name, and phone numbers
            $searchWhere->whereRaw('MATCH(tx_header.customer_name) AGAINST(? IN BOOLEAN MODE)', [$fulltextSearch])
                        ->orWhereRaw('MATCH(tx_header.registration_no) AGAINST(? IN BOOLEAN MODE)', [$fulltextSearch])
                        ->orWhere('tx_header.chassis', 'like', $search . '%')
                        ->orWhere('tx_header.invoice_no', 'like', $search . '%')
                        ->orWhere('tx_header.wip_no', 'like', $search . '%')
                        ->orWhere('tx_header.account_code', 'like', $search . '%')
                        ->orWhereRaw('MATCH(tx_header.account_name) AGAINST(? IN BOOLEAN MODE)', [$fulltextSearch])
                        ->orWhereRaw('MATCH(phone_number_1, phone_number_2, phone_number_3, phone_number_4) AGAINST(? IN BOOLEAN MODE)', [$fulltextSearch]);
        }
    }

    /**
     * Apply text search with FULLTEXT and partial/full string matching
     */
    private function applyTextSearch(&$searchWhere, $search, $field)
    {
        // Check if search contains spaces (potential full string match)
        $hasSpaces = strpos($search, ' ') !== false;
        
        if ($hasSpaces) {
            // Try exact match first (full string)
            $searchWhere->where($field, '=', $search)
                        ->orWhere(function($q) use ($search, $field) {
                            // Also allow partial word matching
                            $words = preg_split('/\s+/', trim($search));
                            $fulltextSearch = implode(' ', array_map(function($word) {
                                return $word . '*';
                            }, $words));
                            $q->whereRaw('MATCH(' . $field . ') AGAINST(? IN BOOLEAN MODE)', [$fulltextSearch]);
                        });
        } else {
            // Single word - use FULLTEXT with wildcard for partial matching
            $fulltextSearch = $search . '*';
            $searchWhere->whereRaw('MATCH(' . $field . ') AGAINST(? IN BOOLEAN MODE)', [$fulltextSearch]);
        }
    }

    /**
     * Apply phone number search with special logic
     */
    private function applyPhoneNumberSearch(&$searchWhere, $search, $isPureDigits)
    {
        $hasSpaces = strpos($search, ' ') !== false;
        
        if ($isPureDigits) {
            // Pure digits - search for partial digit match
            $phoneSearch = $search . '*';
            $searchWhere->whereRaw(
                'MATCH(phone_number_1, phone_number_2, phone_number_3, phone_number_4) AGAINST(? IN BOOLEAN MODE)',
                [$phoneSearch]
            );
        } elseif ($hasSpaces) {
            // Has spaces - try exact match first, then partial
            $searchWhere->where(function($q) use ($search) {
                // Exact match across all phone fields
                $q->where('phone_number_1', '=', $search)
                  ->orWhere('phone_number_2', '=', $search)
                  ->orWhere('phone_number_3', '=', $search)
                  ->orWhere('phone_number_4', '=', $search);
            })
            ->orWhere(function($q) use ($search) {
                // Partial match with FULLTEXT
                $words = preg_split('/\s+/', trim($search));
                $fulltextSearch = implode(' ', array_map(function($word) {
                    return $word . '*';
                }, $words));
                $q->whereRaw(
                    'MATCH(phone_number_1, phone_number_2, phone_number_3, phone_number_4) AGAINST(? IN BOOLEAN MODE)',
                    [$fulltextSearch]
                );
            });
        } else {
            // Single word/text - use FULLTEXT with wildcard
            $fulltextSearch = $search . '*';
            $searchWhere->whereRaw(
                'MATCH(phone_number_1, phone_number_2, phone_number_3, phone_number_4) AGAINST(? IN BOOLEAN MODE)',
                [$fulltextSearch]
            );
        }
    }
}
