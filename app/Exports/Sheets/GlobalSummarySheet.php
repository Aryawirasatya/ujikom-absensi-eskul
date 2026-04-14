<?php
namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\{FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles, WithColumnWidths, WithEvents};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\{Fill, Alignment, Border};

class GlobalSummarySheet implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles, WithColumnWidths, WithEvents
{
    public function __construct(protected array $data, protected $schoolYear) {}

    public function title(): string { return 'Ringkasan Global'; }

    public function headings(): array
    {
        return [['Metrik', 'Nilai']];
    }

    public function array(): array
    {
        return [
            ['Tahun Ajaran',        $this->schoolYear->name],
            ['Total Kegiatan',      $this->data['total_kegiatan']],
            ['Total Absensi',       $this->data['total']],
            ['Total Hadir',         $this->data['hadir']],
            ['Total Alpha',         $this->data['alpha']],
            ['Total Telat',         $this->data['telat']],
            ['Total Izin',          $this->data['izin']],
            ['Total Sakit',         $this->data['sakit']],
            ['Total Libur',         $this->data['libur']],
            ['% Kehadiran Global',  $this->data['pct'] . '%'],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 25, 'B' => 20];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = 14; // 4 header + heading + 10 data

        $sheet->getStyle('A1:B1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '1E3A5F']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']],
        ]);
        $sheet->mergeCells('A1:B1');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $sheet->getStyle('A5:B5')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        foreach (range(6, $lastRow) as $row) {
            $bg = ($row % 2 === 0) ? 'F0F9FF' : 'FFFFFF';
            $sheet->getStyle("A{$row}:B{$row}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                'font' => ['size' => 10],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
            ]);
        }

        // Highlight baris % kehadiran (baris terakhir)
        $sheet->getStyle("A{$lastRow}:B{$lastRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '15803D']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DCFCE7']],
        ]);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->insertNewRowBefore(1, 4);
                $sheet->setCellValue('A1', 'RINGKASAN GLOBAL EKSTRAKURIKULER — ' . strtoupper($this->schoolYear->name));
                $sheet->setCellValue('A2', 'Tahun Ajaran : ' . $this->schoolYear->name);
                $sheet->setCellValue('A3', 'Laporan ini mencakup seluruh data kehadiran semua ekstrakurikuler.');
                $sheet->setCellValue('A4', 'Dicetak pada : ' . now()->format('d F Y, H:i'));
            }
        ];
    }
}