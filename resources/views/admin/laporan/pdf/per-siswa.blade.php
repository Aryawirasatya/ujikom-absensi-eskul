{{-- FILE: resources/views/admin/laporan/pdf/per-siswa.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'DejaVu Sans',Arial,sans-serif;font-size:9px;color:#1E293B;}
.header{background:#4F46E5;color:#fff;padding:14px 20px;margin-bottom:16px;}
.header h1{font-size:14px;font-weight:bold;}
.header .sub{font-size:8px;opacity:.75;margin-top:3px;}
.kpi-row{display:flex;gap:8px;margin:0 20px 16px;}
.kpi-box{flex:1;background:#F8FAFC;border:1px solid #E2E8F0;border-radius:6px;padding:8px;text-align:center;}
.kpi-box .v{font-size:16px;font-weight:bold;}
.kpi-box.green .v{color:#16A34A;}
.kpi-box.red .v{color:#DC2626;}
.kpi-box.blue .v{color:#2563EB;}
.kpi-box.violet .v{color:#7C3AED;}
.kpi-box .l{font-size:7px;color:#64748B;margin-top:2px;}
.section-title{font-size:10px;font-weight:bold;color:#4F46E5;border-bottom:2px solid #4F46E5;padding-bottom:3px;margin:0 20px 8px;}
table{width:calc(100% - 40px);margin:0 20px 16px;border-collapse:collapse;}
th{background:#4F46E5;color:#fff;padding:5px 6px;font-size:8px;text-align:left;}
td{padding:4px 6px;font-size:8px;border-bottom:1px solid #F3F4F6;}
tr:nth-child(even) td{background:#F9FAFB;}
.badge{display:inline-block;padding:1px 5px;border-radius:8px;font-size:7px;font-weight:bold;}
.bg-green{background:#DCFCE7;color:#16A34A;}
.bg-red{background:#FEE2E2;color:#DC2626;}
.bg-amber{background:#FEF3C7;color:#D97706;}
.bg-blue{background:#DBEAFE;color:#2563EB;}
.bg-violet{background:#EDE9FE;color:#7C3AED;}
.bg-gray{background:#F1F5F9;color:#64748B;}
.footer{position:fixed;bottom:0;left:0;right:0;padding:4px 20px;font-size:7px;color:#94A3B8;border-top:1px solid #E2E8F0;display:flex;justify-content:space-between;}
</style>
</head>
<body>
<div class="header">
    <h1>Laporan Kehadiran Per Siswa — Semua Eskul</h1>
    <div class="sub">Tahun Ajaran: {{ $schoolYear->name }} &nbsp;|&nbsp; Dicetak: {{ now()->format('d M Y, H:i') }}</div>
</div>

@php
    $totalSiswa = count($studentReport);
    $avgPct     = $totalSiswa > 0 ? round(collect($studentReport)->avg('pct'), 1) : 0;
    $topSiswa   = collect($studentReport)->where('pct', '>=', 75)->count();
    $atRisk     = collect($studentReport)->where('alpha', '>=', 3)->count();
@endphp

<div class="kpi-row">
    <div class="kpi-box violet"><div class="v">{{ $totalSiswa }}</div><div class="l">Total Siswa</div></div>
    <div class="kpi-box green"><div class="v">{{ $avgPct }}%</div><div class="l">Rata-rata Hadir</div></div>
    <div class="kpi-box blue"><div class="v">{{ $topSiswa }}</div><div class="l">Siswa ≥75%</div></div>
    <div class="kpi-box red"><div class="v">{{ $atRisk }}</div><div class="l">Alpha ≥3x</div></div>
</div>

<div class="section-title">Rekap Kehadiran Per Siswa</div>
<table>
    <thead>
        <tr>
            <th style="width:20px">No</th>
            <th>Nama Siswa</th>
            <th style="width:70px">NISN</th>
            <th style="width:55px">Kelas</th>
            <th style="text-align:center;width:35px">Total</th>
            <th style="text-align:center;width:35px">Hadir</th>
            <th style="text-align:center;width:35px">Alpha</th>
            <th style="text-align:center;width:35px">Telat</th>
            <th style="text-align:center;width:35px">Izin</th>
            <th style="text-align:center;width:35px">Sakit</th>
            <th style="text-align:center;width:45px">% Hadir</th>
            <th style="text-align:center;width:45px">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($studentReport as $i => $s)
        @php
            $statusLabel = $s['pct']>=75?'Baik':($s['pct']>=50?'Cukup':'Rendah');
            $statusBg    = $s['pct']>=75?'bg-green':($s['pct']>=50?'bg-amber':'bg-red');
        @endphp
        <tr>
            <td>{{ $i+1 }}</td>
            <td style="font-weight:bold">{{ $s['nama'] }}</td>
            <td>{{ $s['nisn']??'-' }}</td>
            <td>{{ $s['kelas']??'-' }}</td>
            <td style="text-align:center">{{ $s['total'] }}</td>
            <td style="text-align:center"><span class="badge bg-green">{{ $s['hadir'] }}</span></td>
            <td style="text-align:center"><span class="badge {{ $s['alpha']>=3?'bg-red':'bg-gray' }}">{{ $s['alpha'] }}</span></td>
            <td style="text-align:center"><span class="badge bg-amber">{{ $s['telat'] }}</span></td>
            <td style="text-align:center"><span class="badge bg-blue">{{ $s['izin'] }}</span></td>
            <td style="text-align:center"><span class="badge bg-violet">{{ $s['sakit'] }}</span></td>
            <td style="text-align:center;font-weight:bold;color:{{ $s['pct']>=75?'#16A34A':($s['pct']>=50?'#D97706':'#DC2626') }}">{{ $s['pct'] }}%</td>
            <td style="text-align:center"><span class="badge {{ $statusBg }}">{{ $statusLabel }}</span></td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="footer">
    <span>Laporan Per Siswa — {{ $schoolYear->name }}</span>
    <span>{{ now()->format('d M Y, H:i') }}</span>
</div>
</body>
</html>