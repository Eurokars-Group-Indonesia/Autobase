<?php

namespace App\Exports;

use App\Models\TransactionBody;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;

class TransactionBodyExport implements FromCollection, WithStyles, WithEvents, ShouldAutoSize
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
        $query = TransactionBody::with('brand')
            ->where('tx_body.is_active', '1')
            ->orderBy('tx_body.date_decard', 'desc');

        // Filter by user's brands or specific brand if selected
        if (!empty($this->brandCode)) {
            $query->where('tx_body.pos_code', $this->brandCode);
        } elseif (!empty($this->userBrandCodes)) {
            $query->whereIn('tx_body.pos_code', $this->userBrandCodes);
        }

        // Apply filters with field-specific search
        if ($this->search) {
            $this->applySearchFilter($query, $this->search, $this->searchField);
        }

        if ($this->dateFrom) {
            $query->where('tx_body.date_decard', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->where('tx_body.date_decard', '<=', $this->dateTo);
        }

        $bodies = $query->get();
        
        // Transform data to flat structure
        $rows = collect();
        
        // Add header row
        $headerRow = [
            'No',
            'Part No',
            'Invoice No',
            'WIP No',
            'Magic 2',
            'Description',
            'Date Decard',
            'Qty',
            'Unit',
        ];
        
        if ($this->canViewCostPrice) {
            $headerRow[] = 'Cost Price';
        }
        
        $headerRow = array_merge($headerRow, [
            'Selling Price',
            'Discount %',
            'Extended Price',
            'VAT',
            'Analysis Code',
            'Part/Labour',
            'Operator Code',
            'Operator Name',
            'POS Code',
            'Line'
        ]);
        
        $rows->push($headerRow);
        
        // Add data rows
        $no = 1;
        foreach ($bodies as $body) {
            $dataRow = [
                $no++,
                $body->part_no ?? '',
                $body->invoice_no ?? '',
                $body->wip_no ?? '',
                $body->magic_2 ?? '',
                $body->description ?? '',
                $body->date_decard ? \Carbon\Carbon::parse($body->date_decard)->format('d-m-Y') : '',
                $body->qty ?? 0,
                $body->unit ?? '',
            ];
            
            if ($this->canViewCostPrice) {
                $dataRow[] = $body->cost_price ?? 0;
            }
            
            $dataRow = array_merge($dataRow, [
                $body->selling_price ?? 0,
                $body->discount ?? 0,
                $body->extended_price ?? 0,
                $body->vat ?? '',
                $body->analysis_code ?? '',
                $body->part_or_labour === 'P' ? 'Part' : 'Labour',
                $body->operator_code ?? '',
                $body->operator_name ?? '',
                ($body->brand->brand_code ?? '') . ($body->brand ? ' - ' . $body->brand->brand_name : ''),
                $body->line ?? ''
            ]);
            
            $rows->push($dataRow);
        }
        
        return $rows;
    }

    private function applySearchFilter(&$query, $search, $searchField = '')
    {
        if (empty($search)) {
            return;
        }

        $isPureDigits = preg_match('/^\d+$/', $search);

        // If specific field is selected
        if (!empty($searchField)) {
            $this->applyFieldSpecificSearch($query, $search, $searchField, $isPureDigits);
        } else {
            // Search all fields (original logic)
            $this->applyAllFieldsSearch($query, $search, $isPureDigits);
        }
    }

    private function applyFieldSpecificSearch(&$query, $search, $searchField, $isPureDigits)
    {
        switch ($searchField) {
            case 'part_no':
                $query->where('tx_body.part_no', 'like', $search . '%');
                break;
            case 'description':
                $this->applyTextSearch($query, $search, 'tx_body.description');
                break;
            case 'invoice_no':
                $query->where('tx_body.invoice_no', 'like', $search . '%');
                break;
            case 'wip_no':
                $query->where('tx_body.wip_no', 'like', $search . '%');
                break;
            case 'operator_name':
                $query->where('tx_body.operator_name', 'like', $search . '%');
                break;
        }
    }

    private function applyAllFieldsSearch(&$query, $search, $isPureDigits)
    {
        // For text search, use two-step approach:
        // Step 1: Try exact phrase match (all words together in order)
        $exactPhraseSearch = '"' . $search . '"';
        
        // Step 2: Fallback to strict partial word matching (all words required but can be in any order)
        $words = preg_split('/\s+/', trim($search));
        $partialWordSearch = implode(' ', array_map(function($word) {
            return '+' . $word . '*';
        }, $words));

        $query->where(function($q) use ($search, $exactPhraseSearch, $partialWordSearch, $isPureDigits) {
            // Try exact phrase match first
            $q->where(function($exactMatch) use ($exactPhraseSearch) {
                $exactMatch->where('tx_body.part_no', 'like', $exactPhraseSearch . '%')
                           ->orWhere('tx_body.invoice_no', 'like', $exactPhraseSearch . '%')
                           ->orWhere('tx_body.wip_no', 'like', $exactPhraseSearch . '%')
                           ->orWhere('tx_body.operator_name', 'like', $exactPhraseSearch . '%')
                           ->orWhereRaw('MATCH(tx_body.description) AGAINST(? IN BOOLEAN MODE)', [$exactPhraseSearch]);
            })
            // Fallback to partial word matching
            ->orWhere(function($partialMatch) use ($partialWordSearch) {
                $partialMatch->where('tx_body.part_no', 'like', $partialWordSearch . '%')
                             ->orWhere('tx_body.invoice_no', 'like', $partialWordSearch . '%')
                             ->orWhere('tx_body.wip_no', 'like', $partialWordSearch . '%')
                             ->orWhere('tx_body.operator_name', 'like', $partialWordSearch . '%')
                             ->orWhereRaw('MATCH(tx_body.description) AGAINST(? IN BOOLEAN MODE)', [$partialWordSearch]);
            })
            // Also search with original search term for LIKE fields
            ->orWhere('tx_body.part_no', 'like', $search . '%')
            ->orWhere('tx_body.invoice_no', 'like', $search . '%')
            ->orWhere('tx_body.wip_no', 'like', $search . '%')
            ->orWhere('tx_body.operator_name', 'like', $search . '%');
        });
    }
    private function applyTextSearch(&$query, $search, $field)
    {
        // Check if search contains spaces (potential full string match)
        $hasSpaces = strpos($search, ' ') !== false;

        if ($hasSpaces) {
            // Has spaces - try exact phrase first, then strict AND matching
            $query->where($field, '=', $search)
                  ->orWhere(function($q) use ($search, $field) {
                      // Strict AND matching: all words must be present
                      // Use + prefix with * wildcard: +word1* +word2* +word3*
                      $words = preg_split('/\s+/', trim($search));
                      $fulltextSearch = implode(' ', array_map(function($word) {
                          return '+' . $word . '*';
                      }, $words));
                      $q->whereRaw('MATCH(' . $field . ') AGAINST(? IN BOOLEAN MODE)', [$fulltextSearch]);
                  });
        } else {
            // Single word - use FULLTEXT with wildcard for partial matching
            $fulltextSearch = '+' . $search . '*';
            $query->whereRaw('MATCH(' . $field . ') AGAINST(? IN BOOLEAN MODE)', [$fulltextSearch]);
        }
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
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
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                $highestRow = $sheet->getHighestRow();
                
                // Apply borders to all cells
                $sheet->getStyle('A1:T' . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
                
                // Apply outer border
                $sheet->getStyle('A1:T' . $highestRow)->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
                
                // Set row height for header
                $sheet->getRowDimension(1)->setRowHeight(25);
            },
        ];
    }
}
