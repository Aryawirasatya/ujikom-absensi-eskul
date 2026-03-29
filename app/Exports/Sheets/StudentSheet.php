<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\{
    FromArray,
    WithHeadings,
    WithTitle,
    ShouldAutoSize,
    WithStyles,
    WithColumnWidths,
    WithEvents,
    WithCustomStartCell
};

use Maatwebsite\Excel\Events\AfterSheet;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\{
    Fill,
    Alignment,
    Border,
    NumberFormat
};

class StudentSheet implements
    FromArray,
    WithHeadings,
    WithTitle,
    ShouldAutoSize,
    WithStyles,
    WithColumnWidths,
    WithEvents,
    WithCustomStartCell
{

    public function __construct(
        protected array $data,
        protected $eskul,
        protected $schoolYear
    ) {}

    public function title(): string
    {
        return 'Per Siswa';
    }

    /*
    | HEADER TABLE START
    */

    public function startCell(): string
    {
        return 'A5';
    }

    /*
    | DATA
    */

    public function array(): array
    {
        return collect($this->data)->map(fn($r,$i)=>[

            $i+1,
            $r['nama'],

            $r['nisn'] ? '="'.$r['nisn'].'"' : '-', // tidak perlu tanda '

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

    /*
    | HEADER KOLOM
    */

    public function headings(): array
    {
        return [[

            '#',
            'Nama Siswa',
            'NISN',
            'Kelas',
            'Total',
            'Hadir',
            'Telat',
            'Alpha',
            'Izin',
            'Sakit',
            '% Kehadiran'

        ]];
    }

    /*
    | COLUMN WIDTH
    */

    public function columnWidths(): array
    {
        return [

            'A'=>5,
            'B'=>28,
            'C'=>20,
            'D'=>12,
            'E'=>10,
            'F'=>10,
            'G'=>10,
            'H'=>10,
            'I'=>10,
            'J'=>10,
            'K'=>14

        ];
    }

    /*
    | STYLE
    */

    public function styles(Worksheet $sheet)
    {

        $dataStart = 6;
        $lastRow   = $dataStart + count($this->data) - 1;

        /*
        | HEADER TABLE
        */

        $sheet->getStyle('A5:K5')->applyFromArray([

            'font'=>[
                'bold'=>true,
                'size'=>10,
                'color'=>['rgb'=>'FFFFFF']
            ],

            'fill'=>[
                'fillType'=>Fill::FILL_SOLID,
                'startColor'=>['rgb'=>'4F46E5']
            ],

            'alignment'=>[
                'horizontal'=>Alignment::HORIZONTAL_CENTER,
                'vertical'=>Alignment::VERTICAL_CENTER
            ],

            'borders'=>[
                'allBorders'=>[
                    'borderStyle'=>Border::BORDER_THIN
                ]
            ]

        ]);

        $sheet->getRowDimension(5)->setRowHeight(22);

        /*
        | FORCE TEXT FORMAT FOR NISN
        */

        $sheet->getStyle('C6:C'.$lastRow)
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_TEXT);

        /*
        | FREEZE HEADER
        */

        $sheet->freezePane('A6');

        /*
        | DATA STYLE
        */

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

    /*
    | HEADER LAPORAN
    */

    public function registerEvents(): array
    {

        return [

            AfterSheet::class => function (AfterSheet $event){

                $sheet = $event->sheet->getDelegate();

                $sheet->setCellValue(
                    'A1',
                    'DATA SISWA — '.strtoupper($this->eskul->name).' | '.$this->schoolYear->name
                );

                $sheet->setCellValue(
                    'A2',
                    'Ekstrakurikuler : '.$this->eskul->name
                );

                $sheet->setCellValue(
                    'A3',
                    'Tahun Ajaran : '.$this->schoolYear->name
                );

                $sheet->setCellValue(
                    'A4',
                    'Dicetak : '.now()->format('d F Y H:i')
                );

                $sheet->mergeCells('A1:K1');

                $sheet->getStyle('A1')
                    ->getFont()
                    ->setBold(true)
                    ->setSize(14);

                $sheet->getRowDimension(1)->setRowHeight(28);

                $sheet->getStyle('A2:K4')->getFont()->setSize(9);

            }

        ];
    }
}