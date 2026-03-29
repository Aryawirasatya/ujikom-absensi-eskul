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
.kpi-box.amber .v{color:#D97706;}
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
    <h1>Laporan Global Kehadiran Ekstrakurikuler</h1>
    <div class="sub">Tahun Ajaran: {{ $schoolYear->name }} &nbsp;|&nbsp; Dicetak: {{ now()->format('d M Y, H:i') }}</div>
</div>

@php
    $totalEskul = count($eskulRanking);
    $avgPct     = $totalEskul > 0 ? round(collect($eskulRanking)->avg('pct'), 1) : 0;
    $topEskul   = collect($eskulRanking)->where('pct','>=',75)->count();
@endphp

<div class="kpi-row">
    <div class="kpi-box blue"><div class="v">{{ $totalEskul }}</div><div class="l">Total Eskul</div></div>
    <div class="kpi-box green"><div class="v">{{ $globalSummary['pct'] }}%</div><div class="l">Rate Global</div></div>
    <div class="kpi-box"><div class="v">{{ number_format($globalSummary['total_kegiatan']) }}</div><div class="l">Total Kegiatan</div></div>
    <div class="kpi-box red"><div class="v">{{ number_format($globalSummary['alpha']) }}</div><div class="l">Total Alpha</div></div>
    <div class="kpi-box amber"><div class="v">{{ number_format($globalSummary['telat']) }}</div><div class="l">Total Telat</div></div>
</div>

<div class="section-title">Ranking Kehadiran Per Eskul</div>
<table>
    <thead>
        <tr>
            <th style="width:20px">#</th>
            <th>Nama Eskul</th>
            <th style="text-align:center;width:40px">Anggota</th>
            <th style="text-align:center;width:40px">Kegiatan</th>
            <th style="text-align:center;width:35px">Hadir</th>
            <th style="text-align:center;width:35px">Alpha</th>
            <th style="text-align:center;width:35px">Telat</th>
            <th style="text-align:center;width:35px">Izin</th>
            <th style="text-align:center;width:35px">Sakit</th>
            <th style="text-align:center;width:45px">% Hadir</th>
        </tr>
    </thead>
    <tbody>
        @foreach($eskulRanking as $i => $row)
        <tr>
            <td>{{ $i+1 }}</td>
            <td style="font-weight:bold">{{ $row['nama'] }}</td>
            <td style="text-align:center">{{ $row['anggota'] }}</td>
            <td style="text-align:center">{{ $row['kegiatan'] }}</td>
            <td style="text-align:center"><span class="badge bg-green">{{ $row['hadir'] }}</span></td>
            <td style="text-align:center"><span class="badge bg-red">{{ $row['alpha'] }}</span></td>
            <td style="text-align:center"><span class="badge bg-amber">{{ $row['telat'] }}</span></td>
            <td style="text-align:center"><span class="badge bg-blue">{{ $row['izin'] }}</span></td>
            <td style="text-align:center"><span class="badge bg-violet">{{ $row['sakit'] }}</span></td>
            <td style="text-align:center;font-weight:bold;color:{{ $row['pct']>=75?'#16A34A':($row['pct']>=50?'#D97706':'#DC2626') }}">{{ $row['pct'] }}%</td>
        </tr>
        @endforeach
    </tbody>
</table>

@if(count($alphaWarning) > 0)
<div class="section-title" style="color:#DC2626;border-color:#DC2626">Peringatan Alpha Global (siswa alpha >=3x)</div>
<table>
    <thead style="background:#DC2626">
        <tr>
            <th style="width:20px">#</th>
            <th>Nama Siswa</th>
            <th style="width:70px">NISN</th>
            <th>Eskul</th>
            <th style="text-align:center;width:50px">Total Alpha</th>
        </tr>
    </thead>
    <tbody>
        @foreach($alphaWarning as $i => $aw)
        <tr>
            <td>{{ $i+1 }}</td>
            <td style="font-weight:bold">{{ $aw['nama'] }}</td>
            <td>{{ $aw['nisn']??'-' }}</td>
            <td>{{ $aw['eskul'] }}</td>
            <td style="text-align:center;font-weight:bold;color:#DC2626">{{ $aw['total_alpha'] }}x</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<div class="footer">
    <span>Laporan Global — {{ $schoolYear->name }}</span>
    <span>{{ now()->format('d M Y, H:i') }}</span>
</div>
</body>
</html>