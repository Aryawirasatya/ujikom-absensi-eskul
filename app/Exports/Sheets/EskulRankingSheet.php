<?php
namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\{FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles, WithColumnWidths, WithEvents};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\{Fill, Alignment, Border};

class EskulRankingSheet implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles, WithColumnWidths, WithEvents
{
    public function __construct(protected array $data, protected $schoolYear) {}

    public function title(): string { return 'Ranking Eskul'; }

    public function headings(): array
    {
        return [['Rank', 'Nama Eskul', 'Anggota', 'Kegiatan', 'Total', 'Hadir', 'Alpha', 'Telat', 'Izin', 'Sakit', '% Hadir']];
    }

    public function array(): array
    {
        return collect($this->data)->map(fn($r, $i) => [
            $i + 1,
            $r['nama'],
            $r['anggota'],
            $r['kegiatan'],
            $r['total'],
            $r['hadir'],
            $r['alpha'],
            $r['telat'],
            $r['izin'],
            $r['sakit'],
            $r['pct'] . '%',
        ])->toArray();
    }

    public function columnWidths(): array
    {
        return [
            'A' => 7, 'B' => 28, 'C' => 10, 'D' => 10,
            'E' => 8, 'F' => 8,  'G' => 8,  'H' => 8,
            'I' => 8, 'J' => 8,  'K' => 12,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->data) + 5;

        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '14532D']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DCFCE7']],
        ]);
        $sheet->mergeCells('A1:K1');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $sheet->getStyle('A5:K5')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '16A34A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '15803D']]],
        ]);
        $sheet->getRowDimension(5)->setRowHeight(22);
        $sheet->freezePane('A6');

        foreach (range(6, $lastRow) as $row) {
            $bg = ($row % 2 === 0) ? 'F0FDF4' : 'FFFFFF';
            $sheet->getStyle("A{$row}:K{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                'font' => ['size' => 9],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]],
            ]);
            $sheet->getStyle("C{$row}:K{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->insertNewRowBefore(1, 4);
                $sheet->setCellValue('A1', 'RANKING EKSTRAKURIKULER — ' . strtoupper($this->schoolYear->name));
                $sheet->setCellValue('A2', 'Tahun Ajaran : ' . $this->schoolYear->name);
                $sheet->setCellValue('A3', 'Total Eskul  : ' . count($this->data) . ' ekstrakurikuler, diurutkan dari kehadiran tertinggi.');
                $sheet->setCellValue('A4', 'Dicetak pada : ' . now()->format('d F Y, H:i'));
            }
        ];
    }
}