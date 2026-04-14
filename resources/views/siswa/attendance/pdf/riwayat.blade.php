<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kehadiran - {{ $user->name }}</title>
    <style>
        /* ── RESET & BASE ── */
        @page { margin: 0cm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 10px; 
            color: #1f2937; 
            background: #fff; 
            line-height: 1.4;
        }

        /* ── HEADER (Revisi Warna: Gelap di atas Terang) ── */
        .doc-header {
            background: #f8fafc; /* Background sangat muda agar teks gelap terlihat */
            border-bottom: 3px solid #1e3a8a;
            color: #1e3a8a; 
            padding: 30px 30px 20px 30px;
            width: 100%;
        }
        .school-name {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b; /* Abu-abu metalik */
            margin-bottom: 5px;
        }
        .doc-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 2px;
            color: #1e3a8a;
        }
        .doc-subtitle {
            font-size: 11px;
            color: #475569;
            margin-bottom: 15px;
        }

        /* ── META INFO (Informasi Siswa) ── */
        .meta-table { 
            width: 100%; 
            margin-top: 10px; 
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
        /* Warna teks diubah ke hitam/biru tua agar tidak hilang */
        .meta-table td { color: #1e2937; font-size: 9px; padding-right: 20px; }
        .meta-label { 
            display: block; 
            font-size: 7px; 
            text-transform: uppercase; 
            color: #64748b; 
            font-weight: bold;
            margin-bottom: 2px;
        }
        .meta-value {
            font-weight: bold;
            color: #0f172a;
            font-size: 10px;
        }

        /* ── STAT BAR ── */
        .stat-bar-table {
            width: 100%;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            border-collapse: collapse;
        }
        .stat-pill {
            text-align: center;
            padding: 15px 5px;
            border-right: 1px solid #f1f5f9;
            width: 16.66%;
        }
        .stat-pill:last-child { border-right: none; }
        .stat-pill .val { font-size: 15px; font-weight: bold; display: block; }
        .stat-pill .lbl { 
            font-size: 7.5px; 
            color: #64748b; 
            text-transform: uppercase; 
            font-weight: bold;
            margin-top: 3px;
        }
        
        .val-hadir { color: #16a34a; }
        .val-telat { color: #d97706; }
        .val-alpha { color: #dc2626; }
        .val-izin  { color: #2563eb; }
        .val-sakit { color: #7c3aed; }
        .val-pct   { color: #0891b2; }

        /* ── CONTENT SECTION ── */
        .section { padding: 20px 30px; }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #1e3a8a;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #4f46e5;
        }

        /* ── DATA TABLE ── */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table thead th {
            background: #1e3a8a;
            color: #fff;
            padding: 8px 10px;
            text-align: left;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .data-table tbody td {
            padding: 8px 10px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 9px;
        }
        .data-table tbody tr:nth-child(even) td { background: #fcfdfe; }
        .center { text-align: center; }

        /* ── BADGES ── */
        .badge {
            display: inline-block;
            padding: 3px 7px;
            border-radius: 4px;
            font-size: 7.5px;
            font-weight: bold;
            text-align: center;
            min-width: 50px;
        }
        .badge-hadir { background: #dcfce7; color: #15803d; }
        .badge-telat { background: #fef3c7; color: #92400e; }
        .badge-alpha { background: #fee2e2; color: #991b1b; }
        .badge-izin  { background: #dbeafe; color: #1e40af; }
        .badge-sakit { background: #ede9fe; color: #5b21b6; }
        .badge-libur { background: #f3f4f6; color: #374151; }

        /* ── FOOTER ── */
        .doc-footer {
            position: fixed;
            bottom: 20px;
            left: 30px;
            right: 30px;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
            padding-top: 10px;
        }
        .pagenum:before { content: counter(page); }
    </style>
</head>
<body>

    <div class="doc-header">
        <div class="school-name">SISTEM INFORMASI MONITORING EKSTRAKURIKULER</div>
        <div class="doc-title">Riwayat Kehadiran</div>
        <div class="doc-subtitle">Tahun Ajaran {{ $schoolYear->name ?? '-' }}</div>

        <table class="meta-table">
            <tr>
                <td width="30%">
                    <span class="meta-label">Nama Siswa</span>
                    <strong class="meta-value">{{ $user->name }}</strong>
                </td>
                <td width="20%">
                    <span class="meta-label">NISN</span>
                    <strong class="meta-value">{{ $user->nisn ?? '-' }}</strong>
                </td>
                <td width="20%">
                    <span class="meta-label">Kelas</span>
                    <strong class="meta-value">{{ optional($user->currentAcademic)->grade ?? '-' }}</strong>
                </td>
                <td width="20%">
                    <span class="meta-label">Ekstrakurikuler</span>
                    <strong class="meta-value">
                        {{ $eskulName ?? 'Semua Ekstrakurikuler' }}
                    </strong>
                </td>
                <td width="30%">
                    <span class="meta-label">Tanggal Cetak</span>
                    <strong class="meta-value">{{ now()->format('d M Y, H:i') }}</strong>
                </td>
            </tr>
        </table>
    </div>

    @php
        $total = $summary->total ?? 0;
        $hadir = $summary->hadir ?? 0;
        $calculatedPct = $total > 0 ? round(($hadir / $total) * 100, 1) : 0;
    @endphp

    <table class="stat-bar-table">
        <tr>
            <td class="stat-pill">
                <span class="val val-hadir">{{ $summary->hadir ?? 0 }}</span>
                <span class="lbl">Hadir</span>
            </td>
            <td class="stat-pill">
                <span class="val val-telat">{{ $summary->telat ?? 0 }}</span>
                <span class="lbl">Telat</span>
            </td>
            <td class="stat-pill">
                <span class="val val-alpha">{{ $summary->alpha ?? 0 }}</span>
                <span class="lbl">Alpha</span>
            </td>
            <td class="stat-pill">
                <span class="val val-izin">{{ $summary->izin ?? 0 }}</span>
                <span class="lbl">Izin</span>
            </td>
            <td class="stat-pill">
                <span class="val val-sakit">{{ $summary->sakit ?? 0 }}</span>
                <span class="lbl">Sakit</span>
            </td>
            <td class="stat-pill">
                <span class="val val-pct">{{ $calculatedPct }}%</span>
                <span class="lbl">Kehadiran</span>
            </td>
        </tr>
    </table>

    <div class="section">
        <div class="section-title">Detail Riwayat Kehadiran ({{ $attendances->count() }} Kegiatan)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 30px" class="center">No</th>
                    <th style="width: 80px">Tanggal</th>
                    <th>Nama Kegiatan</th>
                    <th style="width: 140px">Ekstrakurikuler</th>
                    <th style="width: 70px" class="center">Tipe</th>
                    <th style="width: 80px" class="center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $i => $att)
                    @php $act = $att->activity; @endphp
                    <tr>
                        <td class="center">{{ $i + 1 }}</td>
                        <td>{{ $act ? \Carbon\Carbon::parse($act->activity_date)->format('d/m/Y') : '-' }}</td>
                        <td><strong>{{ $act->title ?? '-' }}</strong></td>
                        <td>{{ optional(optional($act)->extracurricular)->name ?? '-' }}</td>
                        <td class="center">{{ ($act->type ?? '') === 'routine' ? 'Rutin' : 'Non-Rutin' }}</td>
                        <td class="center">
                            @php $st = strtolower($att->final_status); @endphp
                            <span class="badge badge-{{ $st }}">{{ strtoupper($st) }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="center" style="padding: 40px; color: #94a3b8;">
                            Belum ada riwayat kehadiran pada tahun ajaran ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="doc-footer">
        <table style="width: 100%">
            <tr>
                <td>Laporan Kehadiran Otomatis - {{ $user->name }}</td>
                <td style="text-align: right">Halaman <span class="pagenum"></span></td>
            </tr>
        </table>
    </div>

</body>
</html>