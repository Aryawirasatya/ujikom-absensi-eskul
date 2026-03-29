{{-- FILE: resources/views/pembina/laporan/pdf/rekap.blade.php --}}
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
.bg-gray{background:#F1F5F9;color:#64748B;}
.footer{position:fixed;bottom:0;left:0;right:0;padding:4px 20px;font-size:7px;color:#94A3B8;border-top:1px solid #E2E8F0;display:flex;justify-content:space-between;}
.page-break{page-break-before:always;}
</style>
</head>
<body>
<div class="header">
    <h1>Laporan Kehadiran — {{ $eskul->name }}</h1>
    <div class="sub">Tahun Ajaran: {{ $schoolYear->name }} &nbsp;|&nbsp; Dicetak: {{ now()->format('d M Y, H:i') }}</div>
</div>

<div class="kpi-row">
    <div class="kpi-box"><div class="v">{{ $totalKegiatan }}</div><div class="l">Total Kegiatan</div></div>
    <div class="kpi-box green"><div class="v">{{ $pctKeseluruhan }}%</div><div class="l">Rate Hadir</div></div>
    <div class="kpi-box"><div class="v">{{ $hadirTotal }}</div><div class="l">Total Hadir</div></div>
    <div class="kpi-box red"><div class="v">{{ $totalAbsensi - $hadirTotal }}</div><div class="l">Tidak Hadir</div></div>
    <div class="kpi-box red"><div class="v">{{ count($alphaWarning) }}</div><div class="l">Siswa Alpha ≥3×</div></div>
</div>

<div class="section-title">Rekap Per Kegiatan</div>
<table>
    <thead>
        <tr>
            <th style="width:20px">No</th>
            <th style="width:60px">Tanggal</th>
            <th>Judul</th>
            <th style="text-align:center;width:45px">Tipe</th>
            <th style="text-align:center;width:35px">Total</th>
            <th style="text-align:center;width:35px">Hadir</th>
            <th style="text-align:center;width:35px">Alpha</th>
            <th style="text-align:center;width:35px">Telat</th>
            <th style="text-align:center;width:45px">% Hadir</th>
        </tr>
    </thead>
    <tbody>
        @foreach($activitySummary as $i => $act)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ is_object($act['tanggal'])?$act['tanggal']->format('d/m/Y'):$act['tanggal'] }}</td>
            <td>{{ $act['judul'] }}</td>
            <td style="text-align:center"><span class="badge {{ $act['tipe']==='routine'?'bg-green':'bg-blue' }}">{{ $act['tipe']==='routine'?'Rutin':'Non-Rutin' }}</span></td>
            <td style="text-align:center">{{ $act['total'] }}</td>
            <td style="text-align:center"><span class="badge bg-green">{{ $act['hadir'] }}</span></td>
            <td style="text-align:center"><span class="badge bg-red">{{ $act['alpha'] }}</span></td>
            <td style="text-align:center"><span class="badge bg-amber">{{ $act['telat'] }}</span></td>
            <td style="text-align:center;font-weight:bold;color:{{ $act['pct']>=75?'#16A34A':($act['pct']>=50?'#D97706':'#DC2626') }}">{{ $act['pct'] }}%</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="page-break"></div>
<div class="section-title">Rekap Per Siswa</div>
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
            <th style="text-align:center;width:45px">% Hadir</th>
        </tr>
    </thead>
    <tbody>
        @foreach($studentSummary as $i => $s)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $s['nama'] }}</td>
            <td>{{ $s['nisn']??'-' }}</td>
            <td>{{ $s['kelas']??'-' }}</td>
            <td style="text-align:center">{{ $s['total'] }}</td>
            <td style="text-align:center"><span class="badge bg-green">{{ $s['hadir'] }}</span></td>
            <td style="text-align:center"><span class="badge {{ $s['alpha']>=3?'bg-red':'bg-gray' }}">{{ $s['alpha'] }}</span></td>
            <td style="text-align:center"><span class="badge bg-amber">{{ $s['telat'] }}</span></td>
            <td style="text-align:center;font-weight:bold;color:{{ $s['pct']>=75?'#16A34A':($s['pct']>=50?'#D97706':'#DC2626') }}">{{ $s['pct'] }}%</td>
        </tr>
        @endforeach
    </tbody>
</table>

@if(count($alphaWarning) > 0)
<div class="section-title" style="color:#DC2626;border-color:#DC2626">⚠ Peringatan Alpha (≥3×)</div>
<table>
    <thead style="background:#DC2626"><tr><th>No</th><th>Nama Siswa</th><th>NISN</th><th style="text-align:center">Total Alpha</th><th>Tanggal</th></tr></thead>
    <tbody>
        @foreach($alphaWarning as $i => $a)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $a['nama'] }}</td>
            <td>{{ $a['nisn']??'-' }}</td>
            <td style="text-align:center;font-weight:bold;color:#DC2626">{{ $a['total_alpha'] }}×</td>
            <td>{{ implode(', ', $a['tanggal']) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<div class="footer">
    <span>{{ $eskul->name }} — {{ $schoolYear->name }}</span>
    <span>{{ now()->format('d M Y, H:i') }}</span>
</div>
</body>
</html>