<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GuruAbsensiExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $detailAbsensis;
    protected $guruName;
    protected $startDate;
    protected $endDate;

    public function __construct($detailAbsensis, $guruName, $startDate, $endDate)
    {
        $this->detailAbsensis = $detailAbsensis;
        $this->guruName = $guruName;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function view(): View
    {
        return view('exports.guru_report', [
            'detailAbsensis' => $this->detailAbsensis,
            'guruName' => $this->guruName,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $headerRow = 5;
        for ($i = 1; $i <= 6; $i++) {
            if (trim($sheet->getCell('A' . $i)->getValue() ?? '') == 'No') {
                $headerRow = $i;
                break;
            }
        }

        // Merge title rows
        $sheet->mergeCells('A1:' . $highestColumn . '1');
        $sheet->mergeCells('A2:' . $highestColumn . '2');
        $sheet->mergeCells('A3:' . $highestColumn . '3');

        // General table borders
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

        // Specific Header Row styling
        $sheet->getStyle('A' . $headerRow . ':' . $highestColumn . $headerRow)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FF0070C0'],
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFFFFFFF'],
            ],
        ]);

        // Center body columns (Except Nama Siswa and Keterangan)
        if ($highestRow > $headerRow) {
            $sheet->getStyle('A' . ($headerRow + 1) . ':D' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . ($headerRow + 1) . ':F' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FF333333']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ],
            2 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FF666666']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ],
            3 => [
                'font' => ['italic' => true, 'color' => ['argb' => 'FF666666']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}
