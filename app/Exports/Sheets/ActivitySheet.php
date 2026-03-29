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
    Fill, Alignment, Border
};

class ActivitySheet implements
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
        return 'Per Kegiatan';
    }

    /*
    | DATA
    */

    public function array(): array
    {
        return collect($this->data)->map(fn($r,$i)=>[

            $i+1,

            is_object($r['tanggal'])
                ? $r['tanggal']->format('d/m/Y')
                : $r['tanggal'],

            $r['judul'],

            $r['tipe']=='routine'
                ? 'Rutin'
                : 'Non-Rutin',

            strtoupper($r['mode'] ?? '-'),

            $r['total'],
            $r['hadir'],
            $r['telat'],
            $r['alpha'],
            $r['izin'],
            $r['sakit'],
            $r['libur'],

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
            'Tanggal',
            'Judul Kegiatan',
            'Tipe',
            'Mode',
            'Total',
            'Hadir',
            'Telat',
            'Alpha',
            'Izin',
            'Sakit',
            'Libur',
            '% Hadir'

        ]];
    }

    /*
    | START CELL (HEADER ROW)
    */

    public function startCell(): string
    {
        return 'A5';
    }

    /*
    | WIDTH
    */

    public function columnWidths(): array
    {
        return [

            'A'=>5,
            'B'=>14,
            'C'=>38,
            'D'=>12,
            'E'=>10,
            'F'=>8,
            'G'=>8,
            'H'=>8,
            'I'=>8,
            'J'=>8,
            'K'=>8,
            'L'=>8,
            'M'=>12

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

        $sheet->getStyle('A5:M5')->applyFromArray([

            'font'=>[
                'bold'=>true,
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

            $sheet->getStyle("A{$row}:M{$row}")
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

            $sheet->getStyle("F{$row}:M{$row}")
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
                    'LAPORAN KEGIATAN — '
                    .strtoupper($this->eskul->name)
                    .' | '
                    .$this->schoolYear->name
                );

                $sheet->setCellValue(
                    'A2',
                    'Ekstrakurikuler : '.$this->eskul->name
                );

                $sheet->setCellValue(
                    'A3',
                    'Tahun Ajaran : '.$this->schoolYear->name
                    .' | Total Kegiatan : '
                    .count($this->data)
                );

                $sheet->setCellValue(
                    'A4',
                    'Dicetak : '.now()->format('d F Y H:i')
                );

                $sheet->mergeCells('A1:M1');

                $sheet->getStyle('A1')
                    ->getFont()
                    ->setBold(true)
                    ->setSize(14);

                $sheet->getRowDimension(1)->setRowHeight(26);

            }

        ];
    }
}