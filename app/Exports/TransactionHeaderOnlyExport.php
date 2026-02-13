<?php

namespace App\Exports;

use App\Models\TransactionHeader;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;

class TransactionHeaderOnlyExport implements FromCollection, WithHeadings, WithStyles, WithEvents, ShouldAutoSize
{
    public function collection()
    {
        return TransactionHeader::with('brand')
            ->where('tx_header.is_active', '1')
            ->orderBy('tx_header.invoice_date', 'desc')
            ->get()
            ->map(function($transaction) {
                return [
                    'invoice_no' => $transaction->invoice_no,
                    'wip_no' => $transaction->wip_no,
                    'invoice_date' => $transaction->invoice_date ? $transaction->invoice_date->format('d-m-Y') : '',
                    'account' => $transaction->account_code ?? '',
                    'customer_name' => $transaction->customer_name ?? '',
                    'registration_no' => $transaction->registration_no ?? '',
                    'chassis' => $transaction->chassis ?? '',
                    'document_type' => $transaction->getDocumentTypeLabel(),
                    'brand' => $transaction->brand->brand_name ?? '',
                    'gross_value' => $transaction->gross_value,
                    'net_value' => $transaction->net_value,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Invoice No',
            'WIP No',
            'Invoice Date',
            'Account',
            'Customer Name',
            'Registration No',
            'Chassis',
            'Document Type',
            'Brand',
            'Gross Value',
            'Net Value',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '002856']
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
                $sheet->getStyle('A1:K' . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
                
                // Apply outer border
                $sheet->getStyle('A1:K' . $highestRow)->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
