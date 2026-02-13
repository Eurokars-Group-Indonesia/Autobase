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
    protected $dateFrom;
    protected $dateTo;
    protected $userBrandCodes;
    protected $brandCode;

    public function __construct($search = null, $dateFrom = null, $dateTo = null, $userBrandCodes = [], $brandCode = null)
    {
        $this->search = $search;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->userBrandCodes = $userBrandCodes;
        $this->brandCode = $brandCode;
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
            $search = $this->search;
            $query->where(function($q) use ($search) {
                // Use FULLTEXT search for customer_name and registration_no
                $q->whereRaw('MATCH(tx_header.customer_name) AGAINST(? IN BOOLEAN MODE)', [$search . '*'])
                  ->orWhereRaw('MATCH(tx_header.registration_no) AGAINST(? IN BOOLEAN MODE)', [$search . '*'])
                  ->orWhere('tx_header.chassis', 'like', $search . '%')
                  ->orWhere('tx_header.invoice_no', 'like', $search . '%')
                  ->orWhere('tx_header.wip_no', 'like', $search . '%')
                  ->orWhereDate('tx_header.invoice_date', '=', $search)
                  ->orWhereExists(function($existsQuery) use ($search) {
                      $existsQuery->select(\DB::raw(1))
                                  ->from('tx_body')
                                  ->whereColumn('tx_body.wip_no', 'tx_header.wip_no')
                                  ->whereColumn('tx_body.invoice_no', 'tx_header.invoice_no')
                                  ->whereColumn('tx_body.magic_2', 'tx_header.magic_id')
                                  ->where('tx_body.is_active', '1')
                                  ->where(function($bodyWhere) use ($search) {
                                      $bodyWhere->where('tx_body.part_no', 'like', $search . '%')
                                                ->orWhere('tx_body.wip_no', 'like', $search . '%')
                                                ->orWhere('tx_body.invoice_no', 'like', $search . '%')
                                                ->orWhereDate('tx_body.date_decard', '=', $search);
                                  });
                  });
            });
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
            $rows->push([
                'No',
                'Part No',
                'HMagic2',
                'Description',
                'Date Decard',
                'Qty',
                'Cost Price',
                'Selling Price',
                'Discount %',
                'Extended Price',
                'Part/Labour',
                '', '', '', '', '', '', ''
            ]);
            
            // Add body rows
            if ($bodies->count() > 0) {
                $no = 1;
                foreach ($bodies as $body) {
                    $rows->push([
                        $no++,
                        $body->part_no,
                        $body->magic_2 ?? '',
                        $body->description ?? '',
                        $body->date_decard ? \Carbon\Carbon::parse($body->date_decard)->format('d-m-Y') : '',
                        $body->qty,
                        $body->cost_price ?? 0,
                        $body->selling_price,
                        $body->discount,
                        $body->extended_price,
                        $body->part_or_labour === 'P' ? 'Part' : 'Labour',
                        '', '', '', '', '', '', ''
                    ]);
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
        $sheet->getStyle('A1:R' . $highestRow)->applyFromArray([
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
                        $sheet->mergeCells('A' . $row . ':R' . $row);
                        $sheet->getStyle('A' . $row . ':R' . $row)->applyFromArray([
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
                        $sheet->getStyle('A' . $row . ':R' . $row)->applyFromArray([
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
                        $sheet->getStyle('A' . $row . ':R' . $row)->applyFromArray([
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
                        $sheet->getStyle('A' . $row . ':R' . ($row + 1))->applyFromArray([
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
                $sheet->getStyle('A1:R' . $highestRow)->applyFromArray([
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
}
