<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AdminAbsensiExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $absensis;
    protected $startDate;
    protected $endDate;

    public function __construct($absensis, $startDate, $endDate)
    {
        $this->absensis = $absensis;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function view(): View
    {
        return view('exports.admin_report', [
            'absensis' => $this->absensis,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // Dynamically find the header row containing "No"
        $headerRow = 4;
        for ($i = 1; $i <= 6; $i++) {
            if (trim($sheet->getCell('A' . $i)->getValue() ?? '') == 'No') {
                $headerRow = $i;
                break;
            }
        }

        // Merge title rows across the table length
        $sheet->mergeCells('A1:' . $highestColumn . '1');
        $sheet->mergeCells('A2:' . $highestColumn . '2');

        // Apply light gray borders to the data table
        $sheet->getStyle('A' . $headerRow . ':' . $highestColumn . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FFBFBFBF'],
                ],
            ],
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ]
        ]);

        // Specific style for the Header Row (Matches the reference image)
        $sheet->getStyle('A' . $headerRow . ':' . $highestColumn . $headerRow)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FF0070C0'], // Blue text
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM, // Thick bottom black border
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFFFFFFF'], // White background
            ],
        ]);

        // Center certain body columns
        if ($highestRow > $headerRow) {
            $sheet->getStyle('A' . ($headerRow + 1) . ':B' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . ($headerRow + 1) . ':H' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FF333333']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ],
            2 => [
                'font' => ['bold' => false, 'italic' => true, 'color' => ['argb' => 'FF666666']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}
