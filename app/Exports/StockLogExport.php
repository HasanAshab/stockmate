<?php

namespace App\Exports;

use App\Models\StockLog;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockLogExport implements FromCollection, WithHeadings, WithStyles
{
    protected ?string $from = null;

    protected ?string $to = null;

    public function __construct(?string $from, ?string $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    public function headings(): array
    {
        return [
            'Product',
            'SKU',
            'Type',
            'Quantity',
            'Unit Cost',
            'Recorded By',
            'Note',
            'Date',
        ];
    }

    public function collection(): Collection
    {
        return StockLog::with(['product', 'user'])
            ->when($this->from, function ($query) {
                return $query->whereDate('created_at', '>=', $this->from);
            })
            ->when($this->to, function ($query) {
                return $query->whereDate('created_at', '<=', $this->to);
            })
            ->get()
            ->map(function (StockLog $stockLog) {
                return [
                    'Product' => $stockLog->product->name,
                    'SKU' => $stockLog->product->sku,
                    'Type' => strtoupper($stockLog->type->name),
                    'Quantity' => $stockLog->quantity,
                    'Unit Cost' => $stockLog->unit_cost,
                    'Recorded By' => $stockLog->user->name,
                    'Note' => $stockLog->note ?? 'N/A',
                    'Date' => $stockLog->created_at->format('Y-m-d H:i'),
                ];
            });
    }

    public function styles(Worksheet $sheet)
    {
        // Bold + background on header row
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1E3A5F'],
            ],
        ]);

        // Highlight low stock rows (Type = OUT)
        $highestRow = $sheet->getHighestRow();
        for ($row = 2; $row <= $highestRow; $row++) {
            $type = $sheet->getCell("C$row")->getValue();
            if ($type === 'OUT') {
                $sheet->getStyle("A$row:H$row")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFFFF3CD'],
                    ],
                ]);
            }
        }
    }
}
