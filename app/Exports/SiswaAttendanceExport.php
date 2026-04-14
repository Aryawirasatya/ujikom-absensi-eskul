<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\{
    FromCollection, WithHeadings, ShouldAutoSize,
    WithStyles, WithColumnWidths, WithTitle, WithMapping
};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\{Fill, Alignment, Border, Color};
use Illuminate\Support\Collection;

class SiswaAttendanceExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithColumnWidths, WithTitle, WithMapping
{
    public function __construct(
        protected Collection $attendances,
        protected array $summary,
        protected $schoolYear,
        protected $user,
        protected $eskulName = null

    ) {}

    public function title(): string { return 'Riwayat Kehadiran'; }

    // Mapping data agar lebih bersih
    public function map($att): array
    {
        static $no = 0;
        $no++;
        $activity = $att->activity;
        
        return [
            $no,
            optional($activity)->activity_date ? \Carbon\Carbon::parse($activity->activity_date)->format('d/m/Y') : '-',
            optional($activity)->title ?? '-',
            optional(optional($activity)->extracurricular)->name ?? '-',
            optional($activity)->type === 'routine' ? 'Rutin' : 'Non-Rutin',
            strtoupper($att->final_status ?? '-'),
        ];
    }

    public function headings(): array
{
    $s = (object) $this->summary;
    $total = $s->total ?? 0;
    $hadir = $s->hadir ?? 0;
    $pct   = $total > 0 ? round($hadir / $total * 100, 1) : 0;

    return [
        ['LAPORAN KEHADIRAN SISWA'],
        ['Nama Siswa', ': ' . $this->user->name],
        ['Tahun Ajaran', ': ' . $this->schoolYear->name],
        ['Ekstrakurikuler', ': ' . ($this->eskulName ?? 'Semua Ekstrakurikuler')],
        ['Statistik', ': ' . $total . ' Kegiatan | ' . $hadir . ' Hadir | ' . $pct . '% Kehadiran'],
        ['Dicetak', ': ' . now()->format('d/m/Y H:i')],
        ['#', 'TANGGAL', 'NAMA KEGIATAN', 'EKSTRAKURIKULER', 'TIPE', 'STATUS']
    ];
}

    public function collection(): Collection
    {
        return $this->attendances;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 15,
            'C' => 40,
            'D' => 30,
            'E' => 15,
            'F' => 18,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $this->attendances->count() + 7;

        // --- STYLING HEADER INFO (Baris 1-5) ---
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1E293B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        $sheet->getStyle('A2:A5')->getFont()->setBold(true);
        $sheet->getStyle('A7:F7')->getFont()->setBold(true);

        // --- STYLING TABLE HEADER (Baris 7) ---
        $sheet->getStyle('A7:F7')->applyFromArray([
            'font' => ['color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'] // Indigo Modern
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
        ]);
        $sheet->getRowDimension(7)->setRowHeight(25);

        // --- STYLING DATA ROWS ---
        $rows = $this->attendances->values();
        foreach (range(8, $lastRow) as $row) {
            $idx = $row - 8;
            $status = isset($rows[$idx]) ? strtolower($rows[$idx]->final_status) : '';

            // Warna teks berdasarkan status (lebih clean daripada warna background penuh)
            $statusColor = match($status) {
                'hadir' => '16A34A', // Hijau
                'alpha' => 'DC2626', // Merah
                'telat' => 'D97706', // Amber
                'izin', 'sakit' => '2563EB', // Biru
                default => '1E293B'
            };

            // Border tipis untuk semua sel
            $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                'borders' => [
                    'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'F1F5F9']],
                ],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);

            // Styling kolom Status (Kolom F)
            $sheet->getStyle("F{$row}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => $statusColor]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);

            $sheet->getStyle("A{$row}:E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getRowDimension($row)->setRowHeight(20);
        }

        return [];
    }
}