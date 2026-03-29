<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\{
    FromArray,
    WithHeadings,
    ShouldAutoSize,
    WithStyles,
    WithColumnWidths,
    WithEvents,
    WithTitle,
    WithCustomStartCell
};

use Maatwebsite\Excel\Events\AfterSheet;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\{
    Fill,
    Alignment,
    Border
};

class StudentReportExport implements
    FromArray,
    WithHeadings,
    ShouldAutoSize,
    WithStyles,
    WithColumnWidths,
    WithEvents,
    WithTitle,
    WithCustomStartCell
{

    public function __construct(
        protected array $data,
        protected $schoolYear
    ) {}

    public function title(): string
    {
        return 'Data Siswa';
    }

  

    public function startCell(): string
    {
        return 'A5';
    }

  

    public function headings(): array
    {
        return [[
            '#',
            'Nama Siswa',
            'NISN',
            'Kelas',
            'Total Kegiatan',
            'Hadir',
            'Telat',
            'Alpha',
            'Izin',
            'Sakit',
            '% Kehadiran'
        ]];
    }

  

    public function array(): array
    {
        return collect($this->data)->map(fn($r,$i)=>[

            $i + 1,

            $r['nama'],

            "'" . ($r['nisn'] ?? '-'), // supaya tidak jadi E+11

            $r['kelas'] ?? '-',

            $r['total'],
            $r['hadir'],
            $r['telat'],
            $r['alpha'],
            $r['izin'],
            $r['sakit'],

            $r['pct'].'%'

        ])->toArray();
    }

  

    public function columnWidths(): array
    {
        return [

            'A' => 5,
            'B' => 28,
            'C' => 18,
            'D' => 12,
            'E' => 14,
            'F' => 10,
            'G' => 10,
            'H' => 10,
            'I' => 10,
            'J' => 10,
            'K' => 14

        ];
    }

  

    public function styles(Worksheet $sheet)
    {

        $dataStart = 6;
        $lastRow   = $dataStart + count($this->data) - 1;

      

        $sheet->getStyle('A5:K5')->applyFromArray([

            'font'=>[
                'bold'=>true,
                'size'=>10,
                'color'=>['rgb'=>'FFFFFF']
            ],

            'fill'=>[
                'fillType'=>Fill::FILL_SOLID,
                'startColor'=>['rgb'=>'7C3AED']
            ],

            'alignment'=>[
                'horizontal'=>Alignment::HORIZONTAL_CENTER,
                'vertical'=>Alignment::VERTICAL_CENTER
            ],

            'borders'=>[
                'allBorders'=>[
                    'borderStyle'=>Border::BORDER_THIN,
                    'color'=>['rgb'=>'6D28D9']
                ]
            ]

        ]);

        $sheet->getRowDimension(5)->setRowHeight(22);

      

        $sheet->freezePane('A6');

      

        foreach(range($dataStart,$lastRow) as $row){

            $idx = $row - $dataStart;

            $pct = isset($this->data[$idx])
                ? (float)$this->data[$idx]['pct']
                : 0;

            $bg = $pct >= 75
                ? 'F0FDF4'
                : ($pct >= 50 ? 'FFFBEB' : 'FEF2F2');

            $sheet->getStyle("A{$row}:K{$row}")
                ->applyFromArray([

                    'fill'=>[
                        'fillType'=>Fill::FILL_SOLID,
                        'startColor'=>['rgb'=>$bg]
                    ],

                    'font'=>['size'=>9],

                    'borders'=>[
                        'allBorders'=>[
                            'borderStyle'=>Border::BORDER_THIN,
                            'color'=>['rgb'=>'E5E7EB']
                        ]
                    ]

                ]);

            $sheet->getStyle("E{$row}:K{$row}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        }

        return [];
    }

  

    public function registerEvents(): array
    {

        return [

            AfterSheet::class => function (AfterSheet $event){

                $sheet = $event->sheet->getDelegate();

                $tanggal = now()->format('d F Y, H:i');

                $sheet->setCellValue(
                    'A1',
                    'LAPORAN KEHADIRAN SISWA — '.strtoupper($this->schoolYear->name)
                );

                $sheet->setCellValue(
                    'A2',
                    'Tahun Ajaran  : '.$this->schoolYear->name
                );

                $sheet->setCellValue(
                    'A3',
                    'Total Siswa   : '.count($this->data).' siswa'
                );

                $sheet->setCellValue(
                    'A4',
                    'Dicetak pada  : '.$tanggal
                );

                $sheet->mergeCells('A1:K1');

                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

                $sheet->getRowDimension(1)->setRowHeight(28);

                $sheet->getStyle('A2:K4')->getFont()->setSize(9);

            }

        ];
    }

}