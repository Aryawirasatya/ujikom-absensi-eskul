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

class PembinaReportExport implements
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
        return 'Laporan Pembina';
    }

  

    public function startCell(): string
    {
        return 'A5';
    }

  

    public function array(): array
    {
        return collect($this->data)->map(fn($r,$i)=>[

            $i + 1,
            $r['nama'],
            $r['eskuls'],
            $r['total_kegiatan'],
            $r['selesai'],
            $r['cancelled'],
            $r['pct'].'%'

        ])->toArray();
    }

  

    public function headings(): array
    {
        return [[

            '#',
            'Nama Pembina',
            'Eskul Diampu',
            'Total Kegiatan',
            'Selesai',
            'Dibatalkan',
            '% Kehadiran Rata-rata'

        ]];
    }

  

    public function columnWidths(): array
    {
        return [

            'A'=>5,
            'B'=>28,
            'C'=>30,
            'D'=>16,
            'E'=>12,
            'F'=>14,
            'G'=>22

        ];
    }

  

    public function styles(Worksheet $sheet)
    {

        $dataStart = 6;
        $lastRow   = $dataStart + count($this->data) - 1;

      

        $sheet->getStyle('A5:G5')->applyFromArray([

            'font'=>[
                'bold'=>true,
                'size'=>10,
                'color'=>['rgb'=>'FFFFFF']
            ],

            'fill'=>[
                'fillType'=>Fill::FILL_SOLID,
                'startColor'=>['rgb'=>'D97706']
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

      

        $sheet->freezePane('A6');

      

        foreach(range($dataStart,$lastRow) as $row){

            $bg = ($row % 2 === 0)
                ? 'FFFBEB'
                : 'FFFFFF';

            $sheet->getStyle("A{$row}:G{$row}")
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

            $sheet->getStyle("D{$row}:G{$row}")
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

                $sheet->setCellValue(
                    'A1',
                    'LAPORAN PEMBINA EKSTRAKURIKULER — '.strtoupper($this->schoolYear->name)
                );

                $sheet->setCellValue(
                    'A2',
                    'Tahun Ajaran : '.$this->schoolYear->name
                );

                $sheet->setCellValue(
                    'A3',
                    'Total Pembina : '.count($this->data).' pembina'
                );

                $sheet->setCellValue(
                    'A4',
                    'Dicetak : '.now()->format('d F Y H:i')
                );

                $sheet->mergeCells('A1:G1');

                $sheet->getStyle('A1')
                    ->getFont()
                    ->setBold(true)
                    ->setSize(14);

                $sheet->getRowDimension(1)->setRowHeight(28);

                $sheet->getStyle('A2:G4')->getFont()->setSize(9);

            }

        ];
    }

}