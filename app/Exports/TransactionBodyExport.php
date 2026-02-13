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
        $query = TransactionBody::with('brand')
            ->where('tx_body.is_active', '1')
            ->orderBy('tx_body.date_decard', 'desc');

        // Filter by user's brands or specific brand if selected
        if (!empty($this->brandCode)) {
            $query->where('tx_body.pos_code', $this->brandCode);
        } elseif (!empty($this->userBrandCodes)) {
            $query->whereIn('tx_body.pos_code', $this->userBrandCodes);
        }

        // Apply filters
        if ($this->search) {
            $search = $this->search;
            $query->where(function($q) use ($search) {
                $q->where('tx_body.part_no', 'like', $search . '%')
                  ->orWhere('tx_body.invoice_no', 'like', $search . '%')
                  ->orWhere('tx_body.wip_no', 'like', $search . '%')
                  ->orWhere('tx_body.description', 'like', $search . '%');
            });
        }

        if ($this->dateFrom) {
            $query->whereDate('tx_body.date_decard', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('tx_body.date_decard', '<=', $this->dateTo);
        }

        $bodies = $query->get();
        
        // Transform data to flat structure
        $rows = collect();
        
        // Add header row
        $rows->push([
            'No',
            'Part No',
            'Invoice No',
            'WIP No',
            'Magic 2',
            'Description',
            'Date Decard',
            'Qty',
            'Unit',
            'Cost Price',
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
        
        // Add data rows
        $no = 1;
        foreach ($bodies as $body) {
            $rows->push([
                $no++,
                $body->part_no ?? '',
                $body->invoice_no ?? '',
                $body->wip_no ?? '',
                $body->magic_2 ?? '',
                $body->description ?? '',
                $body->date_decard ? \Carbon\Carbon::parse($body->date_decard)->format('d-m-Y') : '',
                $body->qty ?? 0,
                $body->unit ?? '',
                $body->cost_price ?? 0,
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
        }
        
        return $rows;
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
