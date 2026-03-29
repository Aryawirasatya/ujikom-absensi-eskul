@extends('layouts.app')

@section('content')
<style>
/* ══════════════════════════════════════════════════
   DASHBOARD SHARED STYLES
══════════════════════════════════════════════════ */
.dash-wrap { max-width:1400px; margin:auto; padding:1.5rem 1.5rem 4rem; }
.dash-greeting h2 { font-size:1.6rem; font-weight:800; color:#1e293b; margin:0; }
.dash-greeting p  { color:#64748b; margin:.2rem 0 0; font-size:.88rem; }

/* Stat Cards */
.kd-stat {
    background:#fff; border-radius:16px; padding:1.2rem 1.4rem;
    border:1px solid #edf2f7; position:relative; overflow:hidden;
    transition:transform .18s,box-shadow .18s;
}
.kd-stat:hover { transform:translateY(-3px); box-shadow:0 10px 28px rgba(0,0,0,.09); }
.kd-stat .s-icon {
    width:46px; height:46px; border-radius:12px;
    display:flex; align-items:center; justify-content:center; font-size:1.25rem;
    margin-bottom:.7rem;
}
.kd-stat .s-val   { font-size:1.9rem; font-weight:900; color:#1e293b; line-height:1; }
.kd-stat .s-label { font-size:.65rem; font-weight:700; text-transform:uppercase;
                    letter-spacing:1.2px; color:#94a3b8; margin-top:.2rem; }
.kd-stat .s-sub   { font-size:.75rem; color:#64748b; margin-top:.35rem; }
.kd-stat .s-bar   { position:absolute; top:0; left:0; width:4px; height:100%; border-radius:16px 0 0 16px; }

/* Section Title */
.sec-title {
    font-size:.68rem; font-weight:800; text-transform:uppercase;
    letter-spacing:2px; color:#94a3b8; margin-bottom:1rem;
    display:flex; align-items:center; gap:.5rem;
}
.sec-title::after { content:''; flex:1; height:1px; background:#e2e8f0; }

/* Chart Card */
.chart-card {
    background:#fff; border-radius:16px; padding:1.4rem;
    border:1px solid #edf2f7; height:100%;
}
.chart-card .cc-title { font-weight:700; color:#1e293b; font-size:.9rem; margin-bottom:1rem; }
.chart-wrap-md { position:relative; height:230px; }
.chart-wrap-sm { position:relative; height:180px; }
.chart-wrap-lg { position:relative; height:260px; }

/* Progress bar */
.pct-bar { height:6px; border-radius:99px; background:#f0f0f5; overflow:hidden; }
.pct-fill { height:100%; border-radius:99px; transition:width .5s ease; }

/* Table */
.kd-table th {
    font-size:.62rem; font-weight:800; text-transform:uppercase;
    letter-spacing:1.5px; color:#94a3b8; padding:.7rem 1rem;
    border-bottom:1px solid #f1f5f9; background:#f8fafc; white-space:nowrap;
}
.kd-table td { padding:.8rem 1rem; border-bottom:1px solid #f8fafc; vertical-align:middle; }
.kd-table tr:last-child td { border-bottom:none; }
.kd-table tr:hover td { background:#fafbfc; }

/* Phase badge */
.phase-pill {
    font-size:.58rem; font-weight:800; text-transform:uppercase;
    letter-spacing:.8px; padding:.28rem .65rem; border-radius:99px;
}

/* Eskul tab btn */
.eskul-tab { font-size:.72rem; padding:.3rem .9rem; border-radius:99px; font-weight:700; cursor:pointer; }

/* Status colors */
.bg-hadir  { background:#dcfce7; color:#16a34a; }
.bg-alpha  { background:#fee2e2; color:#dc2626; }
.bg-telat  { background:#fef9c3; color:#ca8a04; }
.bg-izin   { background:#dbeafe; color:#2563eb; }
.bg-sakit  { background:#ede9fe; color:#7c3aed; }
.bg-libur  { background:#f1f5f9; color:#64748b; }

/* Big pct number */
.pct-huge { font-size:2.8rem; font-weight:900; line-height:1; }
.delta-up   { color:#16a34a; }
.delta-down { color:#dc2626; }
.delta-flat { color:#94a3b8; }
.siswa-dash { font-family: 'DM Sans', sans-serif; }

/* Hero Stats Row */
.siswa-hero{
display:grid;
grid-template-columns: minmax(220px,320px) 1fr;
gap:20px;
align-items:stretch;
min-width:0;
margin-bottom:28px;
}
.siswa-big-card {
    border-radius: 20px; padding: 1.8rem 1.6rem;
    display: flex; flex-direction: column; justify-content: space-between;
    position: relative; overflow: hidden; min-height: 200px;
    min-width:0;
word-break:break-word;
}
.siswa-big-card .bg-glow {
    position: absolute; width: 180px; height: 180px; border-radius: 50%;
    background: rgba(255,255,255,.12); top: -40px; right: -50px; pointer-events: none;
}
.siswa-big-card .pct-display {
    font-size: 4rem; font-weight: 900; color: #fff; line-height: 1; letter-spacing: -.04em;
}
.siswa-big-card .pct-label {
    font-size: .65rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 2px; color: rgba(255,255,255,.7); margin-top: .2rem;
}
.siswa-big-card .pct-bar {
    height: 5px; background: rgba(255,255,255,.25); border-radius: 99px; overflow: hidden; margin-top: .7rem;
}
.siswa-big-card .pct-fill { height: 100%; background: #fff; border-radius: 99px; transition: width .6s ease; }
.siswa-big-card .pct-bottom {
    font-size: .75rem; color: rgba(255,255,255,.75); margin-top: .6rem;
}

/* Mini stat grid */
.siswa-mini-grid{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(120px,1fr));
gap:.75rem;
}
.siswa-mini-card {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
    padding: 1rem 1.1rem; position: relative; overflow: hidden; transition: transform .15s, box-shadow .15s;
}
.siswa-mini-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,.08); }
.siswa-mini-card .mc-stripe {
    position: absolute; left: 0; top: 0; bottom: 0; width: 3px; border-radius: 14px 0 0 14px;
}
.siswa-mini-card .mc-val { font-size: 1.6rem; font-weight: 900; color: #0f172a; line-height: 1; }
.siswa-mini-card .mc-label {
    font-size: .58rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 1.5px; color: #94a3b8; margin-top: .2rem;
}
.siswa-mini-card .mc-pct { font-size: .7rem; color: #94a3b8; margin-top: .15rem; }

/* Chart section */
.siswa-charts-row {
    display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem;
}
.siswa-chart-card {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 16px;
    padding: 1.3rem 1.4rem;
}
.siswa-chart-card .scc-title {
    font-size: .78rem; font-weight: 700; color: #0f172a; margin-bottom: 1rem;
    display: flex; align-items: center; gap: .5rem;
}
.scc-title .scc-icon {
    width: 26px; height: 26px; border-radius: 7px;
    display: flex; align-items: center; justify-content: center; font-size: .65rem;
}

/* Eskul Tab (if multi-eskul) */
.siswa-eskul-tabs {
    display: flex; flex-wrap: wrap; gap: .5rem; margin-bottom: 1.25rem;
}
.s-tab {
    font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .8px;
    padding: .32rem .9rem; border-radius: 99px; cursor: pointer;
    border: 1.5px solid #e2e8f0; background: #f8fafc; color: #64748b;
    transition: all .15s;
}
.s-tab.active { background: #6366f1; border-color: #6366f1; color: #fff; box-shadow: 0 4px 12px rgba(99,102,241,.3); }
.s-tab:hover:not(.active) { border-color: #6366f1; color: #6366f1; }

/* Recent attendance table */
.siswa-table-card {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden;
    margin-bottom: 1.5rem;
}
.siswa-table-card .stc-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1rem 1.3rem; border-bottom: 1px solid #f1f5f9;
}
.stc-head .stc-title { font-size: .82rem; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: .5rem; }
.stc-head .stc-link {
    font-size: .68rem; font-weight: 700; color: #6366f1; text-decoration: none;
    display: flex; align-items: center; gap: .35rem; padding: .3rem .75rem;
    border: 1.5px solid #e0e7ff; border-radius: 8px; transition: all .15s;
}
.stc-head .stc-link:hover { background: #eef2ff; }
.siswa-table th {
    font-size: .6rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px;
    color: #94a3b8; padding: .7rem 1.1rem; background: #f8fafc;
    border-bottom: 1px solid #f1f5f9; text-align: left; white-space: nowrap;
}
.siswa-table td {
    padding: .8rem 1.1rem; border-bottom: 1px solid #f8fafc;
    vertical-align: middle; font-size: .8rem; color: #374151;
}
.siswa-table tr:last-child td { border-bottom: none; }
.siswa-table tr:hover td { background: #fafbff; }

/* Pills */
.s-pill {
    display: inline-flex; align-items: center; gap: .28rem;
    font-size: .58rem; font-weight: 800; text-transform: uppercase;
    letter-spacing: .7px; padding: .25rem .65rem; border-radius: 99px;
}
.sp-hadir  { background: #dcfce7; color: #16a34a; }
.sp-alpha  { background: #fee2e2; color: #dc2626; }
.sp-telat  { background: #fef9c3; color: #ca8a04; }
.sp-izin   { background: #dbeafe; color: #2563eb; }
.sp-sakit  { background: #ede9fe; color: #7c3aed; }
.sp-libur  { background: #f1f5f9; color: #64748b; }
.sp-on_time { background: #dcfce7; color: #16a34a; }
.sp-late    { background: #fef9c3; color: #ca8a04; }
.sp-absent  { background: #fee2e2; color: #dc2626; }
.sp-pending { background: #f1f5f9; color: #94a3b8; }

/* Source badge */
.s-src {
    display: inline-flex; align-items: center; gap: .3rem;
    font-size: .65rem; font-weight: 600; padding: .2rem .55rem; border-radius: 6px;
}
.ssrc-scan   { background: #dcfce7; color: #15803d; }
.ssrc-manual { background: #dbeafe; color: #1d4ed8; }
.ssrc-system { background: #f1f5f9; color: #64748b; }

/* Mono time */
.s-mono {
    font-family: 'DM Mono', monospace; font-size: .72rem;
    background: #f8fafc; padding: .13rem .4rem; border-radius: 5px;
    color: #374151;
}
.s-mono.empty { color: #cbd5e1; }
.siswa-charts-row{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
gap:20px;
width:100%;
min-width:0;
}
.siswa-table-card{
width:100%;
overflow:visible;
margin-top:10px;
margin-bottom:30px;
}

.siswa-table-card .table-responsive{
overflow-x:auto;
-webkit-overflow-scrolling:touch;
}
.siswa-dash{
width:100%;
overflow-x:hidden;
}

.siswa-eskul-panel{
min-width:0;
}
/* Responsive */
@media (max-width: 900px){

.siswa-hero{
grid-template-columns:1fr;
}

.siswa-charts-row{
grid-template-columns:1fr;
}

.siswa-mini-grid{
grid-template-columns:repeat(2,1fr);
}

}
@media (max-width: 576px) {
    .siswa-mini-grid { grid-template-columns: repeat(2,1fr); }
    .siswa-hero .siswa-big-card { min-height: 160px; }
    .siswa-big-card .pct-display { font-size: 3rem; }
}
@media(max-width:576px){
    .dash-wrap { padding:1rem 1rem 3rem; }
    .s-val { font-size:1.5rem !important; }
}

/* Utility */
.card-footer-gray { background:#f8fafc; padding:.8rem 1.2rem; border-top:1px solid #f1f5f9; font-size:.75rem; }
</style>

<div class="dash-wrap container-fluid">

{{-- ══════════════════════════════════════════════════
     GREETING BAR
══════════════════════════════════════════════════ --}}
<div class="dash-greeting d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h2>
            @if(auth()->user()->hasRole('admin'))
                <i class="fas fa-shield-alt text-primary me-2"></i>
            @elseif(auth()->user()->hasRole('pembina'))
                <i class="bi bi-person-workspace text-success me-2"></i>
            @else
                <i class="bi bi-person-graduate text-info me-2"></i>
            @endif
            Selamat datang, {{ auth()->user()->name }}
        </h2>
        <p>
            <i class="fas fa-calendar-alt me-1"></i>
            {{ now()->locale('id')->translatedFormat('l, d F Y') }}
            &nbsp;•&nbsp;
            <i class="fas fa-graduation-cap me-1"></i>
            {{ $schoolYear->name ?? '—' }}
            &nbsp;•&nbsp;
            <span class="badge bg-primary-subtle text-primary fw-bold" style="font-size:.68rem;">
                {{ strtoupper(optional(auth()->user()->roles->first())->name ?? 'USER') }}
            </span>
        </p>
    </div>
</div>

@if(!$schoolYear)
<div class="alert alert-warning rounded-4 fw-bold">
    <i class="bi bi-exclamation-triangle me-2"></i>
    Belum ada tahun ajaran aktif. Mohon hubungi admin.
</div>
@else

{{-- ══════════════════════════════════════════════════════════════════
     ██████  ADMIN DASHBOARD
══════════════════════════════════════════════════════════════════ --}}
@if(auth()->user()->hasRole('admin'))

{{-- ── ROW 1: SYSTEM STATS (8 cards) ───────────────────────────── --}}
<div class="sec-title"><i class="fas fa-server text-primary"></i> Ringkasan Sistem</div>
<div class="row g-3 mb-3">
    @php
    $sysStats = [
        ['val'=>$totalEskul,         'label'=>'Eskul Aktif',       'icon'=>'bi bi-trophy',          'color'=>'primary',  'bg'=>'#eef2ff', 'sub'=>$totalEskulNonaktif.' nonaktif'],
        ['val'=>$totalPembina,        'label'=>'Pembina Aktif',     'icon'=>'bi bi-person-workspace','color'=>'success', 'bg'=>'#f0fdf4', 'sub'=>'terdaftar'],
        ['val'=>$totalSiswa,          'label'=>'Siswa Aktif',       'icon'=>'bi bi-people',            'color'=>'info',    'bg'=>'#eff6ff', 'sub'=>'terdaftar'],
        ['val'=>$totalAnggotaAktif,   'label'=>'Anggota Aktif',     'icon'=>'fas fa-id-badge',         'color'=>'cyan',    'bg'=>'#ecfeff', 'sub'=>'tahun ini'],
        ['val'=>$totalKegiatan,       'label'=>'Total Kegiatan',    'icon'=>'bi bi-calendar-check',   'color'=>'warning', 'bg'=>'#fffbeb', 'sub'=>$totalKegiatanSelesai.' selesai'],
        ['val'=>$totalKegiatanBerjalan,'label'=>'Sedang Berjalan',  'icon'=>'fas fa-broadcast-tower',  'color'=>'danger',  'bg'=>'#fef2f2', 'sub'=>'checkin/checkout'],
        ['val'=>$kegiatanHariIni,     'label'=>'Kegiatan Hari Ini', 'icon'=>'fas fa-bolt',             'color'=>'orange',  'bg'=>'#fff7ed', 'sub'=>$kegiatanSelesaiHariIni.' selesai hari ini'],
        ['val'=>number_format($schoolAttendanceRate,1).'%', 'label'=>'Rate Kehadiran','icon'=>' bi bi-graph-up','color'=>'success','bg'=>'#f0fdf4','sub'=>'seluruh tahun ajaran'],
    ];
    @endphp
    @foreach($sysStats as $s)
    <div class="col-6 col-md-3">
        <div class="kd-stat">
            <div class="s-bar bg-{{ $s['color'] }}"></div>
            <div class="s-icon" style="background:{{ $s['bg'] }};">
                <i class="{{ $s['icon'] }} text-{{ $s['color'] }}"></i>
            </div>
            <div class="s-val">{{ $s['val'] }}</div>
            <div class="s-label">{{ $s['label'] }}</div>
            <div class="s-sub text-muted"><i class="fas fa-circle" style="font-size:.4rem;vertical-align:middle;"></i> {{ $s['sub'] }}</div>
        </div>
    </div>
    @endforeach
</div>

{{-- ── ROW 2: KPI KEHADIRAN CARDS ───────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="kd-stat" style="background:linear-gradient(135deg,#22c55e,#16a34a);border:none;">
            <div class="text-white opacity-75" style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;">Kehadiran Keseluruhan</div>
            <div class="pct-huge text-white mt-1">{{ $schoolAttendanceRate }}%</div>
            <div class="mt-2 text-white opacity-80 small">{{ number_format($overallRate->hadir ?? 0) }} hadir dari {{ number_format($overallRate->total ?? 0) }} total</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kd-stat" style="background:linear-gradient(135deg,#3b82f6,#2563eb);border:none;">
            <div class="text-white opacity-75" style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;">Kehadiran Bulan Ini</div>
            <div class="pct-huge text-white mt-1">{{ $thisMonthPct }}%</div>
            <div class="mt-2 text-white opacity-80 small">{{ now()->locale('id')->translatedFormat('F Y') }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="kd-stat" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);border:none;">
            <div class="text-white opacity-75" style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;">Delta vs Bulan Lalu</div>
            <div class="pct-huge text-white mt-1">
                @if($attendanceDelta > 0) +{{ $attendanceDelta }}%
                @elseif($attendanceDelta < 0) {{ $attendanceDelta }}%
                @else 0%
                @endif
            </div>
            <div class="mt-2 text-white opacity-80 small">
                @if($attendanceDelta > 0) <i class="fas fa-arrow-up"></i> Meningkat
                @elseif($attendanceDelta < 0) <i class="fas fa-arrow-down"></i> Menurun
                @else <i class="fas fa-minus"></i> Stabil
                @endif
                dari bulan sebelumnya
            </div>
        </div>
    </div>
</div>

{{-- ── ROW 3: MAIN CHARTS (Trend + Status Distribusi) ─────────── --}}
<div class="sec-title"><i class="fas fa-chart-area text-primary"></i> Analitik Kehadiran</div>
<div class="row g-3 mb-3">
    {{-- Trend Line 6 Bulan --}}
    <div class="col-lg-8">
        <div class="chart-card">
            <div class="cc-title"><i class=" bi bi-graph-up me-2 text-primary"></i>Tren % Kehadiran 6 Bulan Terakhir</div>
            <div class="chart-wrap-md">
                <canvas id="chartTrendAdmin"></canvas>
            </div>
        </div>
    </div>
    {{-- Doughnut Status Distribusi --}}
    <div class="col-lg-4">
        <div class="chart-card">
            <div class="cc-title"><i class="bi bi-pie-chart me-2 text-info"></i>Distribusi Status Kehadiran</div>
            <div class="chart-wrap-md">
                <canvas id="chartStatusDist"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- ── ROW 4: STACKED BAR + SUMBER + GENDER ────────────────────── --}}
<div class="row g-3 mb-3">
    {{-- Stacked Bar Monthly --}}
    <div class="col-lg-6">
        <div class="chart-card">
            <div class="cc-title"><i class="bi bi-bar-chart me-2 text-warning"></i>Komposisi Status per Bulan</div>
            <div class="chart-wrap-md">
                <canvas id="chartStackedBulan"></canvas>
            </div>
        </div>
    </div>
    {{-- Sumber Absensi Pie --}}
    <div class="col-lg-3">
        <div class="chart-card">
            <div class="cc-title"><i class="fas fa-qrcode me-2 text-success"></i>Sumber Absensi</div>
            <div class="chart-wrap-md">
                <canvas id="chartSumber"></canvas>
            </div>
        </div>
    </div>
    {{-- Gender Doughnut --}}
    <div class="col-lg-3">
        <div class="chart-card">
            <div class="cc-title"><i class="fas fa-venus-mars me-2 text-purple"></i>Gender Siswa</div>
            <div class="chart-wrap-md">
                <canvas id="chartGender"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- ── ROW 5: KEGIATAN PER BULAN + HARI AKTIF + CHECKIN STATUS ─── --}}
<div class="row g-3 mb-4">
    {{-- Kegiatan per Bulan Bar --}}
    <div class="col-lg-5">
        <div class="chart-card">
            <div class="cc-title"><i class="fas fa-calendar-week me-2 text-orange"></i>Jumlah Kegiatan per Bulan</div>
            <div class="chart-wrap-md">
                <canvas id="chartKegiatanBulan"></canvas>
            </div>
        </div>
    </div>
    {{-- Hari Aktif Bar --}}
    <div class="col-lg-4">
        <div class="chart-card">
            <div class="cc-title"><i class="fas fa-calendar-day me-2 text-cyan"></i>Kegiatan per Hari</div>
            <div class="chart-wrap-md">
                <canvas id="chartHariAktif"></canvas>
            </div>
        </div>
    </div>
    {{-- Checkin Status Pie --}}
    <div class="col-lg-3">
        <div class="chart-card">
            <div class="cc-title"><i class="fas fa-sign-in-alt me-2 text-success"></i>Status Check-in</div>
            <div class="chart-wrap-md">
                <canvas id="chartCheckin"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- ── ROW 6: HORIZONTAL BAR ESKUL + MODE KEGIATAN ─────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="chart-card">
            <div class="cc-title"><i class="fas fa-sort-amount-down me-2 text-primary"></i>Perbandingan % Kehadiran per Eskul</div>
            <div style="position:relative; height:{{ count($eskulRanking) * 36 + 60 }}px; min-height:200px;">
                <canvas id="chartEskulBar"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="chart-card">
            <div class="cc-title"><i class="fas fa-random me-2 text-info"></i>Mode Kegiatan</div>
            <div class="chart-wrap-sm">
                <canvas id="chartModeKegiatan"></canvas>
            </div>
            <hr class="my-3">
            {{-- Stat numbers from overallRate --}}
            <div class="row g-2 text-center">
                @php
                $statItems = [
                    ['val'=>$overallRate->hadir??0,'label'=>'Hadir','cls'=>'bg-hadir'],
                    ['val'=>$overallRate->alpha??0,'label'=>'Alpha','cls'=>'bg-alpha'],
                    ['val'=>$overallRate->telat??0,'label'=>'Telat','cls'=>'bg-telat'],
                    ['val'=>$overallRate->izin??0, 'label'=>'Izin', 'cls'=>'bg-izin'],
                    ['val'=>$overallRate->sakit??0,'label'=>'Sakit','cls'=>'bg-sakit'],
                    ['val'=>$overallRate->libur??0,'label'=>'Libur','cls'=>'bg-libur'],
                ];
                @endphp
                @foreach($statItems as $si)
                <div class="col-4">
                    <div class="rounded-3 p-2 {{ $si['cls'] }}">
                        <div class="fw-bold" style="font-size:.9rem;">{{ number_format($si['val']) }}</div>
                        <div style="font-size:.58rem;text-transform:uppercase;letter-spacing:.5px;opacity:.8;">{{ $si['label'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ── ROW 7: KEGIATAN HARI INI + ALPHA WARNING ─────────────────── --}}
<div class="row g-3 mb-4">
    {{-- Kegiatan Hari Ini --}}
    <div class="col-lg-5">
        <div class="sec-title"><i class="fas fa-bolt text-warning"></i> Kegiatan Hari Ini</div>
        <div class="chart-card p-0" style="border-radius:16px;overflow:hidden;">
            @forelse($kegiatanToday as $act)
            @php
                $phaseMap=[
                    'not_started'=>['warning','Belum Mulai','fas fa-clock'],
                    'checkin'    =>['success','Check-in','fas fa-sign-in-alt'],
                    'checkout'   =>['info','Check-out','fas fa-sign-out-alt'],
                    'finished'   =>['secondary','Selesai','fas fa-check-circle'],
                ];
                $ph=$phaseMap[$act->attendance_phase]??['light','—','fas fa-circle'];
            @endphp
            <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
                <div class="d-flex align-items-center gap-3">
                    <div class="s-icon bg-{{ $ph[0] }}-subtle" style="width:38px;height:38px;border-radius:10px;">
                        <i class="{{ $ph[2] }} text-{{ $ph[0] }}"></i>
                    </div>
                    <div>
                        <div class="fw-bold text-dark" style="font-size:.85rem;">{{ optional($act->extracurricular)->name }}</div>
                        <div class="text-muted" style="font-size:.72rem;">{{ $act->title }}</div>
                    </div>
                </div>
                <span class="phase-pill bg-{{ $ph[0] }}-subtle text-{{ $ph[0] }}">{{ $ph[1] }}</span>
            </div>
            @empty
            <div class="text-center py-5 text-muted">
                <i class="fas fa-calendar-times fs-1 opacity-25 d-block mb-2"></i>
                Tidak ada kegiatan hari ini
            </div>
            @endforelse
        </div>
    </div>

    {{-- Alpha Warning --}}
    <div class="col-lg-7">
        <div class="sec-title"><i class="bi bi-exclamation-triangle text-danger"></i> Peringatan Alpha — Siswa ≥ 3x Alpha</div>
        @if(count($alphaWarning) > 0)
        <div class="card border-0 rounded-4 overflow-hidden shadow-sm">
            <div class="table-responsive">
                <table class="table mb-0 kd-table" id="tblAlpha">
                    <thead><tr>
                        <th>#</th><th>Nama Siswa</th><th>NISN</th>
                        <th>Eskul</th><th class="text-center">Total Alpha</th>
                    </tr></thead>
                    <tbody>
                        @foreach($alphaWarning as $i=>$w)
                        <tr>
                            <td class="fw-bold text-muted">{{ $i+1 }}</td>
                            <td class="fw-bold text-dark">{{ $w->name }}</td>
                            <td><code class="small">{{ $w->nisn ?? '—' }}</code></td>
                            <td class="text-muted small">{{ $w->eskul_name }}</td>
                            <td class="text-center">
                                <span class="badge fw-bold px-3 bg-danger">{{ $w->total_alpha }}x</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer-gray d-flex justify-content-end">
                <button class="btn btn-sm btn-outline-secondary" onclick="exportCSV('#tblAlpha','alpha_warning.csv')">
                    <i class="fas fa-download me-1"></i>Export CSV
                </button>
            </div>
        </div>
        @else
        <div class="chart-card text-center py-5 text-muted">
            <i class="fas fa-check-circle fs-1 text-success opacity-50 d-block mb-2"></i>
            Tidak ada siswa dengan alpha ≥ 3x
        </div>
        @endif
    </div>
</div>

{{-- ── ROW 8: ESKUL RANKING TABLE ───────────────────────────────── --}}
<div class="sec-title mt-5"><i class="fas fa-list-ol text-primary"></i> Ranking Kehadiran Eskul — {{ $schoolYear->name }}</div>
<div class="card border-0 rounded-4 overflow-hidden shadow-sm mb-4">
    <div class="table-responsive">
        <table class="table mb-0 kd-table" id="tblEskulRanking">
            <thead><tr>
                <th>#</th>
                <th>Eskul</th>
                <th class="text-center">Anggota</th>
                <th class="text-center">Kegiatan</th>
                <th class="text-center">Hadir</th>
                <th class="text-center">Alpha</th>
                <th class="text-center">Telat</th>
                <th class="text-center">Izin</th>
                <th class="text-center">Sakit</th>
                <th style="min-width:160px;">% Kehadiran</th>
            </tr></thead>
            <tbody>
                @forelse($eskulRanking as $i=>$row)
                <tr>
                    <td class="fw-bold text-muted">{{ $i+1 }}</td>
                    <td class="fw-bold text-dark">{{ $row->name }}</td>
                    <td class="text-center"><span class="badge bg-primary-subtle text-primary">{{ $row->anggota }}</span></td>
                    <td class="text-center"><span class="badge bg-secondary-subtle text-secondary">{{ $row->kegiatan }}</span></td>
                    <td class="text-center"><span class="badge bg-success-subtle text-success fw-bold">{{ $row->hadir }}</span></td>
                    <td class="text-center"><span class="badge bg-danger-subtle text-danger fw-bold">{{ $row->alpha }}</span></td>
                    <td class="text-center"><span class="badge bg-warning-subtle text-warning fw-bold">{{ $row->telat }}</span></td>
                    <td class="text-center"><span class="badge bg-info-subtle text-info fw-bold">{{ $row->izin }}</span></td>
                    <td class="text-center"><span class="badge bg-purple-subtle text-purple fw-bold" style="background:#ede9fe;color:#7c3aed;">{{ $row->sakit }}</span></td>
                    <td>
                        @php $pct = $row->pct ?? 0; @endphp
                        <div class="d-flex align-items-center gap-2">
                            <div class="pct-bar flex-fill">
                                <div class="pct-fill {{ $pct >= 75 ? 'bg-success' : ($pct >= 50 ? 'bg-warning' : 'bg-danger') }}"
                                     style="width:{{ $pct }}%"></div>
                            </div>
                            <span class="fw-bold small {{ $pct >= 75 ? 'text-success' : ($pct >= 50 ? 'text-warning' : 'text-danger') }}">{{ $pct }}%</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center py-4 text-muted">Belum ada data eskul.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer-gray d-flex justify-content-between align-items-center">
        <span class="text-muted">Tahun ajaran: {{ $schoolYear->name }}</span>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary" onclick="exportCSV('#tblEskulRanking','ranking_eskul.csv')">
                <i class="fas fa-download me-1"></i>Export CSV
            </button>
            <button class="btn btn-sm btn-outline-primary" onclick="printTbl('#tblEskulRanking')">
                <i class="fas fa-print me-1"></i>Print
            </button>
        </div>
    </div>
</div>

{{-- END ADMIN ─────────────────────────────────────────────────────────── --}}

{{-- ══════════════════════════════════════════════════════════════════
     ██████  PEMBINA DASHBOARD
══════════════════════════════════════════════════════════════════ --}}
@elseif(auth()->user()->hasRole('pembina'))

{{-- ── ROW 1: SUMMARY STATS ─────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    @php
    $pStats = [
        ['val'=>$eskulList->count(),        'label'=>'Eskul Diampu',    'icon'=>'bi bi-trophy',         'color'=>'primary','bg'=>'#eef2ff'],
        ['val'=>$totalAnggotaPembina??0,     'label'=>'Total Anggota',   'icon'=>'bi bi-people',          'color'=>'info',   'bg'=>'#eff6ff'],
        ['val'=>$totalKegiatanPembina??0,    'label'=>'Total Kegiatan',  'icon'=>'bi bi-calendar-check', 'color'=>'success','bg'=>'#f0fdf4'],
        ['val'=>$kegiatanTodayPembina->count(),'label'=>'Hari Ini',      'icon'=>'fas fa-bolt',           'color'=>'warning','bg'=>'#fffbeb'],
        ['val'=>$recentActivitiesPembina->count(),'label'=>'Kegiatan Selesai (terbaru)','icon'=>'fas fa-check-double','color'=>'secondary','bg'=>'#f8fafc'],
    ];
    @endphp
    @foreach($pStats as $s)
    <div class="col-6 col-md">
        <div class="kd-stat">
            <div class="s-bar bg-{{ $s['color'] }}"></div>
            <div class="s-icon" style="background:{{ $s['bg'] }};"><i class="{{ $s['icon'] }} text-{{ $s['color'] }}"></i></div>
            <div class="s-val">{{ $s['val'] }}</div>
            <div class="s-label">{{ $s['label'] }}</div>
        </div>
    </div>
    @endforeach
</div>

@if($eskulList->isEmpty())
<div class="chart-card text-center py-5 mb-4">
    <i class="bi bi-person-slash fs-1 text-muted opacity-25 d-block mb-3"></i>
    <p class="fw-bold text-muted">Anda belum ditugaskan sebagai pembina di eskul manapun.</p>
</div>
@else

{{-- Tab selector jika lebih dari 1 eskul --}}
@if($eskulList->count() > 1)
<div class="d-flex gap-2 flex-wrap mb-4" id="eskulTabBar">
    @foreach($eskulList as $eskul)
    <button class="btn eskul-tab {{ $loop->first ? 'btn-primary' : 'btn-outline-secondary' }}"
            onclick="switchEskul({{ $eskul->id }}, this)">
        {{ $eskul->name }}
    </button>
    @endforeach
</div>
@endif

@foreach($eskulList as $eskul)
@php $st = $eskulStats[$eskul->id] ?? null; @endphp
<div class="eskul-panel" id="panel-{{ $eskul->id }}" style="{{ $loop->first ? '' : 'display:none;' }}">

    {{-- ── ESKUL: KPI ROW ─────────────────────────────── --}}
    <div class="row g-3 mb-3">
        {{-- Big pct card --}}
        <div class="col-md-3">
            <div class="kd-stat h-100" style="background:linear-gradient(135deg,{{ $st && $st['pct']>=75 ? '#22c55e,#16a34a' : ($st && $st['pct']>=50 ? '#f59e0b,#d97706' : '#ef4444,#dc2626') }});border:none;">
                <div class="text-white opacity-75" style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;">{{ $eskul->name }}</div>
                <div class="pct-huge text-white mt-1">{{ $st['pct'] ?? 0 }}%</div>
                <div class="text-white opacity-80 small mt-1">kehadiran keseluruhan</div>
                <div class="pct-bar mt-2" style="background:rgba(255,255,255,.3);">
                    <div class="pct-fill" style="width:{{ $st['pct']??0 }}%;background:#fff;"></div>
                </div>
            </div>
        </div>
        {{-- 5 status mini cards --}}
        @if($st)
        @php
        $miniStats = [
            ['val'=>$st['hadir'],'label'=>'Hadir','cls'=>'bg-hadir'],
            ['val'=>$st['alpha'],'label'=>'Alpha','cls'=>'bg-alpha'],
            ['val'=>$st['telat'],'label'=>'Telat','cls'=>'bg-telat'],
            ['val'=>$st['izin'], 'label'=>'Izin', 'cls'=>'bg-izin'],
            ['val'=>$st['sakit'],'label'=>'Sakit','cls'=>'bg-sakit'],
        ];
        @endphp
        @foreach($miniStats as $ms)
        <div class="col">
            <div class="kd-stat text-center">
                <div class="{{ $ms['cls'] }} rounded-3 px-3 py-2 d-inline-block mb-1">
                    <div style="font-size:1.4rem;font-weight:900;">{{ number_format($ms['val']) }}</div>
                </div>
                <div class="s-label">{{ $ms['label'] }}</div>
                @if($st['total'] > 0)
                <div class="s-sub text-muted">{{ round($ms['val']/$st['total']*100,1) }}%</div>
                @endif
            </div>
        </div>
        @endforeach
        @endif
    </div>

    {{-- ── ESKUL: CHARTS ROW 1 ────────────────────────── --}}
    <div class="row g-3 mb-3">
        {{-- Monthly Line + Stacked --}}
        <div class="col-lg-7">
            <div class="chart-card">
                <div class="cc-title"><i class=" bi bi-graph-up me-2 text-primary"></i>Tren Kehadiran 6 Bulan — {{ $eskul->name }}</div>
                <div class="chart-wrap-md">
                    <canvas id="chartPembinaMonthly{{ $eskul->id }}"></canvas>
                </div>
            </div>
        </div>
        {{-- Status Doughnut --}}
        <div class="col-lg-5">
            <div class="chart-card">
                <div class="cc-title"><i class="bi bi-pie-chart me-2 text-info"></i>Komposisi Status Kehadiran</div>
                <div class="chart-wrap-md">
                    <canvas id="chartPembinaStatus{{ $eskul->id }}"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ── ESKUL: CHARTS ROW 2 ────────────────────────── --}}
    <div class="row g-3 mb-3">
        {{-- Weekly Trend Bar --}}
        <div class="col-lg-7">
            <div class="chart-card">
                <div class="cc-title"><i class="bi bi-bar-chart me-2 text-success"></i>Tren Mingguan (8 Minggu Terakhir)</div>
                <div class="chart-wrap-md">
                    <canvas id="chartPembinaTrend{{ $eskul->id }}"></canvas>
                </div>
            </div>
        </div>
        {{-- Checkin on_time vs late --}}
        <div class="col-lg-5">
            <div class="chart-card">
                <div class="cc-title"><i class="fas fa-clock me-2 text-warning"></i>Tepat Waktu vs Terlambat (10 Kegiatan)</div>
                <div class="chart-wrap-md">
                    <canvas id="chartPembinaCheckin{{ $eskul->id }}"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ── ESKUL: ALPHA WARNING TABLE ──────────────────── --}}
    @php $aw = $eskulAlphaWarning[$eskul->id] ?? []; @endphp
    @if(count($aw) > 0)
    <div class="sec-title"><i class="bi bi-exclamation-triangle text-danger"></i> Peringatan Alpha — {{ $eskul->name }}</div>
    <div class="card border-0 rounded-4 overflow-hidden shadow-sm mb-3">
        <div class="table-responsive">
            <table class="table mb-0 kd-table">
                <thead><tr><th>#</th><th>Nama Siswa</th><th>NISN</th><th class="text-center">Total Alpha</th></tr></thead>
                <tbody>
                    @foreach($aw as $i=>$awi)
                    <tr>
                        <td class="text-muted fw-bold">{{ $i+1 }}</td>
                        <td class="fw-bold text-dark">{{ $awi->name }}</td>
                        <td><code class="small">{{ $awi->nisn ?? '—' }}</code></td>
                        <td class="text-center"><span class="badge bg-danger fw-bold px-3">{{ $awi->total_alpha }}x Alpha</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ── ESKUL: TODAY + RECENT ────────────────────────── --}}
    <div class="row g-3 mb-4">
        {{-- Today --}}
        <div class="col-lg-5">
            <div class="sec-title"><i class="fas fa-bolt text-warning"></i> Kegiatan Hari Ini</div>
            <div class="chart-card p-0" style="border-radius:16px;overflow:hidden;">
                @php $todayList = $kegiatanTodayPembina->where('extracurricular_id',$eskul->id); @endphp
                @forelse($todayList as $act)
                @php
                    $phMap=['not_started'=>['warning','Belum','fas fa-clock'],'checkin'=>['success','Check-in','fas fa-sign-in-alt'],'checkout'=>['info','Check-out','fas fa-sign-out-alt'],'finished'=>['secondary','Selesai','fas fa-check-circle']];
                    $ph=$phMap[$act->attendance_phase]??['light','—','fas fa-circle'];
                @endphp
                <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
                    <div>
                        <div class="fw-bold text-dark small">{{ $act->title }}</div>
                        <div class="text-muted" style="font-size:.72rem;">{{ $act->type }}</div>
                    </div>
                    <a href="{{ $act->attendance_mode==='manual'
                        ? route('pembina.activity.manual_page',[$act->extracurricular_id,$act->id])
                        : route('pembina.activity.show',[$act->extracurricular_id,$act->id]) }}"
                       class="btn btn-sm btn-{{ $ph[0] }} rounded-pill fw-bold px-3" style="font-size:.62rem;">
                        <i class="{{ $ph[2] }} me-1"></i>{{ $ph[1] }}
                    </a>
                </div>
                @empty
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-calendar-times fs-2 opacity-25 d-block mb-2"></i>
                    Tidak ada kegiatan hari ini
                </div>
                @endforelse
            </div>
        </div>
        {{-- Recent finished --}}
        <div class="col-lg-7">
            <div class="sec-title"><i class="fas fa-history text-secondary"></i> Riwayat Kegiatan Selesai</div>
            <div class="card border-0 rounded-4 overflow-hidden shadow-sm">
                <div class="table-responsive">
                    <table class="table mb-0 kd-table">
                        <thead><tr><th>Tanggal</th><th>Judul</th><th>Mode</th><th class="text-center">Fase</th></tr></thead>
                        <tbody>
                            @php $recentFiltered = $recentActivitiesPembina->where('extracurricular_id',$eskul->id)->take(8); @endphp
                            @forelse($recentFiltered as $ra)
                            <tr>
                                <td class="small text-muted">{{ \Carbon\Carbon::parse($ra->activity_date)->format('d M Y') }}</td>
                                <td class="fw-bold text-dark small">{{ $ra->title }}</td>
                                <td>
                                    @if($ra->attendance_mode==='qr')
                                        <span class="badge bg-success-subtle text-success"><i class="fas fa-qrcode me-1"></i>QR</span>
                                    @elseif($ra->attendance_mode==='manual')
                                        <span class="badge bg-info-subtle text-info"><i class="fas fa-pencil-alt me-1"></i>Manual</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">—</span>
                                    @endif
                                </td>
                                <td class="text-center"><span class="phase-pill bg-secondary-subtle text-secondary">Selesai</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-3 text-muted small">Belum ada riwayat.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>{{-- end eskul-panel --}}
@endforeach

@endif{{-- end eskulList not empty --}}

{{-- ══════════════════════════════════════════════════════════════════
     ██████  SISWA DASHBOARD
══════════════════════════════════════════════════════════════════ --}}
@else
<div class="siswa-dash">

@if($memberships->isEmpty())
<div class="siswa-chart-card text-center py-5 mb-4">
    <i class="fas fa-journal-whills fs-1 text-muted opacity-25 d-block mb-3"></i>
    <h5 class="fw-bold text-dark">Anda belum terdaftar di eskul manapun.</h5>
    <p class="text-muted small">Hubungi pembina atau admin untuk mendaftarkan diri.</p>
</div>
@else

{{-- ── If multiple eskul, show tabs ─────────────────────────────────── --}}
@if(count($eskulRiwayat) > 1)
<div class="siswa-eskul-tabs">
    @foreach($eskulRiwayat as $idx => $er)
    <button class="s-tab {{ $idx === 0 ? 'active' : '' }}"
            onclick="switchSiswaEskul({{ $er->eskul->id }}, this)">
        {{ optional($er->eskul)->name }}
    </button>
    @endforeach
</div>
@endif

{{-- ── Eskul Panels ──────────────────────────────────────────────────── --}}
@foreach($eskulRiwayat as $idx => $er)
<div class="siswa-eskul-panel" id="siswa-panel-{{ $er->eskul->id }}"
     style="{{ $idx > 0 ? 'display:none;' : '' }}">

    {{-- ── HERO: Big % + Mini stats ──────────────────────────────── --}}
    @php
        $pct = $er->pct ?? 0;
        $gradients = [
            'good'   => 'linear-gradient(135deg,#22c55e,#16a34a)',
            'mid'    => 'linear-gradient(135deg,#f59e0b,#d97706)',
            'bad'    => 'linear-gradient(135deg,#ef4444,#dc2626)',
        ];
        $grad = $pct >= 75 ? $gradients['good'] : ($pct >= 50 ? $gradients['mid'] : $gradients['bad']);
    @endphp
    <div class="siswa-hero">
        {{-- Big attendance % card --}}
        <div class="siswa-big-card" style="background: {{ $grad }};">
            <div class="bg-glow"></div>
            <div>
                <div class="pct-label">{{ optional($er->eskul)->name }}</div>
                <div class="pct-label mt-1" style="opacity:.6;font-size:.58rem;">Tahun Ajaran {{ $schoolYear->name }}</div>
            </div>
            <div>
                <div class="pct-display">{{ $pct }}<span style="font-size:1.5rem;">%</span></div>
                <div class="pct-label">Tingkat Kehadiran</div>
                <div class="pct-bar">
                    <div class="pct-fill" style="width:{{ $pct }}%;"></div>
                </div>
                <div class="pct-bottom">{{ number_format($er->hadir) }} hadir dari {{ number_format($er->total) }} pertemuan</div>
            </div>
        </div>

        {{-- Mini stat grid (5 status) --}}
        @php
        $mStats = [
            ['val'=>$er->hadir,'label'=>'Hadir', 'color'=>'#22c55e','bg'=>'#dcfce7'],
            ['val'=>$er->alpha,'label'=>'Alpha', 'color'=>'#ef4444','bg'=>'#fee2e2'],
            ['val'=>$er->telat,'label'=>'Telat', 'color'=>'#f59e0b','bg'=>'#fef9c3'],
            ['val'=>$er->izin, 'label'=>'Izin',  'color'=>'#3b82f6','bg'=>'#dbeafe'],
            ['val'=>$er->sakit,'label'=>'Sakit', 'color'=>'#8b5cf6','bg'=>'#ede9fe'],
            ['val'=>($er->total > 0 ? round($er->on_time/$er->total*100,0) : 0).'%','label'=>'Tepat Waktu','color'=>'#06b6d4','bg'=>'#ecfeff'],
        ];
        @endphp
        <div class="siswa-mini-grid">
            @foreach($mStats as $ms)
            <div class="siswa-mini-card">
                <div class="mc-stripe" style="background:{{ $ms['color'] }};"></div>
                <div class="mc-val" style="color:{{ $ms['color'] }};">{{ $ms['val'] }}</div>
                <div class="mc-label">{{ $ms['label'] }}</div>
                @if(is_numeric($ms['val']) && $er->total > 0 && $ms['label'] !== 'Tepat Waktu')
                <div class="mc-pct">{{ round($ms['val']/$er->total*100,1) }}%</div>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── Charts: Trend Line + Status Doughnut ──────────────────── --}}
    <div class="siswa-charts-row">
        <div class="siswa-chart-card">
            <div class="scc-title">
                <span class="scc-icon" style="background:#eef2ff;color:#6366f1;"><i class=" bi bi-graph-up"></i></span>
                Tren % Kehadiran 6 Bulan Terakhir
            </div>
            <div style="position:relative;height:210px;">
                <canvas id="siswaLineMain{{ $er->eskul->id }}"></canvas>
            </div>
        </div>
        <div class="siswa-chart-card">
            <div class="scc-title">
                <span class="scc-icon" style="background:#fdf4ff;color:#a855f7;"><i class="bi bi-pie-chart"></i></span>
                Distribusi Status Kehadiran
            </div>
            <div style="position:relative;height:210px;">
                <canvas id="siswaDonut{{ $er->eskul->id }}"></canvas>
            </div>
        </div>
    </div>

</div>{{-- end siswa-eskul-panel --}}
@endforeach

{{-- ── RECENT ATTENDANCE TABLE ──────────────────────────────────────── --}}
<div class="siswa-table-card">
    <div class="stc-head">
        <div class="stc-title">
            <i class="fas fa-clock text-indigo-500" style="color:#6366f1;"></i>
            Riwayat Absensi Terbaru
        </div>
        <a href="{{ route('siswa.attendance.index') }}" class="stc-link">
            Lihat Semua <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 siswa-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Kegiatan</th>
                    <th>Eskul</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Check-in</th>
                    <th class="text-center">Masuk</th>
                    <th class="text-center">Pulang</th>
                    <th>Sumber</th>
                </tr>
            </thead>
            <tbody>
                @forelse($riwayatTerbaru as $att)
                @php
                    $fs  = $att->final_status ?? '—';
                    $cs  = $att->checkin_status ?? 'pending';
                    $src = $att->attendance_source ?? 'system';
                    $srcLabels = ['scan' => 'QR Scan', 'manual' => 'Manual', 'system' => 'Sistem'];
                    $srcIcons  = ['scan' => 'fas fa-qrcode', 'manual' => 'fas fa-pencil-alt', 'system' => 'fas fa-robot'];
                    $statusIcons = ['hadir'=>'fas fa-check','alpha'=>'fas fa-times','telat'=>'fas fa-clock','izin'=>'fas fa-file-alt','sakit'=>'fas fa-heart','libur'=>'fas fa-umbrella-beach'];
                    $actDate = optional($att->activity)->activity_date;
                @endphp
                <tr>
                    <td>
                        <div class="fw-bold text-dark" style="font-size:.8rem;">
                            {{ $actDate ? \Carbon\Carbon::parse($actDate)->format('d M Y') : '—' }}
                        </div>
                        <div class="text-muted" style="font-size:.62rem;">
                            {{ $actDate ? \Carbon\Carbon::parse($actDate)->locale('id')->translatedFormat('l') : '' }}
                        </div>
                    </td>
                    <td class="fw-bold text-dark">{{ optional($att->activity)->title ?? '—' }}</td>
                    <td class="text-muted">{{ optional(optional($att->activity)->extracurricular)->name ?? '—' }}</td>
                    <td class="text-center">
                        <span class="s-pill sp-{{ $fs }}">
                            <i class="{{ $statusIcons[$fs] ?? 'fas fa-circle' }}"></i>
                            {{ strtoupper($fs) }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="s-pill sp-{{ $cs }}">{{ str_replace('_',' ',strtoupper($cs)) }}</span>
                    </td>
                    <td class="text-center">
                        @if($att->checkin_at)
                            <span class="s-mono">{{ \Carbon\Carbon::parse($att->checkin_at)->format('H:i') }}</span>
                        @else
                            <span class="s-mono empty">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($att->checkout_at)
                            <span class="s-mono">{{ \Carbon\Carbon::parse($att->checkout_at)->format('H:i') }}</span>
                        @else
                            <span class="s-mono empty">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="s-src ssrc-{{ $src }}">
                            <i class="{{ $srcIcons[$src] ?? 'fas fa-circle' }}"></i>
                            {{ $srcLabels[$src] ?? ucfirst($src) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">
                        <i class="fas fa-calendar-times d-block fs-2 opacity-25 mb-2"></i>
                        Belum ada riwayat absensi.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endif{{-- end memberships check --}}
</div>{{-- end siswa-dash --}}
@endif{{-- end role check --}}

@endif{{-- end schoolYear check --}}

</div>{{-- end dash-wrap --}}

{{-- ══════════════════════════════════════════════════
     SCRIPTS — Chart.js + Utilities
══════════════════════════════════════════════════ --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
/* ─── Global defaults ─────────────────────────── */
Chart.defaults.font.family = "'Public Sans', sans-serif";
Chart.defaults.color       = '#64748b';

/* ─── Color palettes ──────────────────────────── */
const COLORS = {
    hadir : '#22c55e', alpha : '#ef4444', telat : '#f59e0b',
    izin  : '#3b82f6', sakit : '#8b5cf6', libur : '#94a3b8',
    on_time:'#22c55e', late  : '#f59e0b', absent: '#ef4444', pending:'#cbd5e1',
    scan  : '#22c55e', manual: '#3b82f6', system: '#94a3b8',
    qr    : '#22c55e',
};
const PALETTE = ['#3b82f6','#22c55e','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#f97316','#ec4899'];

/* ─── Helper: pct from monthly data ──────────── */
function toPct(arr){ return arr.map(r=> r.total>0 ? Math.round(r.hadir/r.total*100) : 0); }

/* ─── Export CSV ──────────────────────────────── */
function exportCSV(sel, fname){
    const t=document.querySelector(sel); if(!t) return;
    const rows=[...t.querySelectorAll('tr')].map(r=>
        [...r.querySelectorAll('th,td')].map(c=>'"'+c.innerText.trim().replace(/"/g,'""')+'"').join(',')
    ).join('\n');
    const a=document.createElement('a');
    a.href=URL.createObjectURL(new Blob([rows],{type:'text/csv;charset=utf-8;'}));
    a.setAttribute('download',fname); document.body.appendChild(a); a.click(); a.remove();
}

/* ─── Print table ─────────────────────────────── */
function printTbl(sel){
    const el=document.querySelector(sel); if(!el) return;
    const w=window.open('','_blank');
    w.document.write('<html><head><style>table{border-collapse:collapse;width:100%}th,td{border:1px solid #ddd;padding:6px 10px;font-size:12px}</style></head><body>'+el.outerHTML+'</body></html>');
    w.document.close(); w.focus(); setTimeout(()=>{w.print();w.close();},300);
}

/* ─── Pembina eskul tab switcher ─────────────── */
function switchEskul(id, btn){
    document.querySelectorAll('.eskul-panel').forEach(p=>p.style.display='none');
    const panel=document.getElementById('panel-'+id);
    if(panel) panel.style.display='';
    document.querySelectorAll('#eskulTabBar button').forEach(b=>{
        b.className='btn eskul-tab btn-outline-secondary';
    });
    if(btn) btn.className='btn eskul-tab btn-primary';
}

/* ══════════════════════════════════════════════
   ADMIN CHARTS
══════════════════════════════════════════════ */
@if(auth()->user()->hasRole('admin'))
(function(){
    const bulanan      = @json($chartBulanan ?? []);
    const kegBulan     = @json($kegiatanPerBulan ?? []);
    const statusDist   = @json($statusDistribusi ?? []);
    const checkinDist  = @json($checkinDistribusi ?? []);
    const sumber       = @json($sumberAbsensi ?? []);
    const gender       = @json($genderDistribusi ?? []);
    const hari         = @json($hariAktif ?? []);
    const eskulBar     = @json($eskulBarData ?? []);
    const modeKeg      = @json($modeAbsensi ?? []);

    /* 1 ─ Trend Line 6 bulan */
    if(bulanan.length && document.getElementById('chartTrendAdmin')){
        new Chart(document.getElementById('chartTrendAdmin'),{
            type:'line',
            data:{
                labels: bulanan.map(r=>r.label),
                datasets:[
                    {label:'% Hadir',data:toPct(bulanan),borderColor:'#22c55e',backgroundColor:'rgba(34,197,94,.12)',fill:true,tension:.35,pointRadius:4,borderWidth:2,pointBackgroundColor:'#22c55e'},
                ]
            },
            options:{responsive:true,maintainAspectRatio:false,
                plugins:{legend:{position:'top'}},
                scales:{y:{min:0,max:100,ticks:{callback:v=>v+'%'},grid:{color:'#f1f5f9'}},x:{grid:{display:false}}}
            }
        });
    }

    /* 2 ─ Doughnut status */
    if(statusDist.length && document.getElementById('chartStatusDist')){
        const colorMap={hadir:'#22c55e',alpha:'#ef4444',telat:'#f59e0b',izin:'#3b82f6',sakit:'#8b5cf6',libur:'#94a3b8'};
        new Chart(document.getElementById('chartStatusDist'),{
            type:'doughnut',
            data:{
                labels:statusDist.map(r=>(r.label||'—').toUpperCase()),
                datasets:[{data:statusDist.map(r=>r.total),backgroundColor:statusDist.map(r=>colorMap[r.label]||'#cbd5e1'),borderWidth:2}]
            },
            options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{boxWidth:12,padding:10,font:{size:10}}}}}
        });
    }

    /* 3 ─ Stacked bar monthly */
    if(bulanan.length && document.getElementById('chartStackedBulan')){
        new Chart(document.getElementById('chartStackedBulan'),{
            type:'bar',
            data:{
                labels:bulanan.map(r=>r.label),
                datasets:[
                    {label:'Hadir', data:bulanan.map(r=>r.hadir), backgroundColor:'#22c55e'},
                    {label:'Alpha', data:bulanan.map(r=>r.alpha), backgroundColor:'#ef4444'},
                    {label:'Telat', data:bulanan.map(r=>r.telat), backgroundColor:'#f59e0b'},
                    {label:'Izin',  data:bulanan.map(r=>r.izin),  backgroundColor:'#3b82f6'},
                    {label:'Sakit', data:bulanan.map(r=>r.sakit), backgroundColor:'#8b5cf6'},
                ]
            },
            options:{responsive:true,maintainAspectRatio:false,
                plugins:{legend:{position:'top',labels:{boxWidth:10,font:{size:10}}}},
                scales:{x:{stacked:true,grid:{display:false}},y:{stacked:true,grid:{color:'#f1f5f9'}}}
            }
        });
    }

    /* 4 ─ Sumber absensi pie */
    if(sumber.length && document.getElementById('chartSumber')){
        const sc={scan:'#22c55e',manual:'#3b82f6',system:'#94a3b8'};
        new Chart(document.getElementById('chartSumber'),{
            type:'pie',
            data:{labels:sumber.map(r=>(r.label||'').toUpperCase()),
                datasets:[{data:sumber.map(r=>r.total),backgroundColor:sumber.map(r=>sc[r.label]||'#cbd5e1'),borderWidth:2}]},
            options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{boxWidth:12,font:{size:10}}}}}
        });
    }

    /* 5 ─ Gender doughnut */
    if(gender.length && document.getElementById('chartGender')){
        new Chart(document.getElementById('chartGender'),{
            type:'doughnut',
            data:{labels:gender.map(r=>r.label==='L'?'Laki-laki':r.label==='P'?'Perempuan':'Belum Diisi'),
                datasets:[{data:gender.map(r=>r.total),backgroundColor:['#3b82f6','#ec4899','#94a3b8'],borderWidth:2}]},
            options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{boxWidth:12,font:{size:10}}}}}
        });
    }

    /* 6 ─ Kegiatan per bulan bar */
    if(kegBulan.length && document.getElementById('chartKegiatanBulan')){
        new Chart(document.getElementById('chartKegiatanBulan'),{
            type:'bar',
            data:{
                labels:kegBulan.map(r=>r.label),
                datasets:[
                    {label:'Total Kegiatan', data:kegBulan.map(r=>r.total),  backgroundColor:'rgba(59,130,246,.8)',borderRadius:6},
                    {label:'Selesai',        data:kegBulan.map(r=>r.selesai), backgroundColor:'rgba(34,197,94,.8)', borderRadius:6},
                ]
            },
            options:{responsive:true,maintainAspectRatio:false,
                plugins:{legend:{position:'top',labels:{boxWidth:10,font:{size:10}}}},
                scales:{x:{grid:{display:false}},y:{grid:{color:'#f1f5f9'},ticks:{precision:0}}}
            }
        });
    }

    /* 7 ─ Hari aktif */
    if(hari.length && document.getElementById('chartHariAktif')){
        new Chart(document.getElementById('chartHariAktif'),{
            type:'bar',
            data:{
                labels:hari.map(r=>r.hari),
                datasets:[{label:'Kegiatan',data:hari.map(r=>r.total),
                    backgroundColor:hari.map((_,i)=>PALETTE[i%PALETTE.length]),borderRadius:6}]
            },
            options:{responsive:true,maintainAspectRatio:false,
                plugins:{legend:{display:false}},
                scales:{x:{grid:{display:false}},y:{grid:{color:'#f1f5f9'},ticks:{precision:0}}}
            }
        });
    }

    /* 8 ─ Checkin status pie */
    if(checkinDist.length && document.getElementById('chartCheckin')){
        const cc={on_time:'#22c55e',late:'#f59e0b',absent:'#ef4444',pending:'#cbd5e1'};
        new Chart(document.getElementById('chartCheckin'),{
            type:'pie',
            data:{labels:checkinDist.map(r=>(r.label||'—').replace('_',' ').toUpperCase()),
                datasets:[{data:checkinDist.map(r=>r.total),backgroundColor:checkinDist.map(r=>cc[r.label]||'#cbd5e1'),borderWidth:2}]},
            options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{boxWidth:12,font:{size:10}}}}}
        });
    }

    /* 9 ─ Eskul horizontal bar */
    if(eskulBar.length && document.getElementById('chartEskulBar')){
        new Chart(document.getElementById('chartEskulBar'),{
            type:'bar',
            data:{
                labels:eskulBar.map(r=>r.name),
                datasets:[
                    {label:'% Hadir',data:eskulBar.map(r=>r.pct),
                     backgroundColor:eskulBar.map(r=>r.pct>=75?'rgba(34,197,94,.85)':r.pct>=50?'rgba(245,158,11,.85)':'rgba(239,68,68,.85)'),
                     borderRadius:6}
                ]
            },
            options:{indexAxis:'y',responsive:true,maintainAspectRatio:false,
                plugins:{legend:{display:false}},
                scales:{x:{min:0,max:100,ticks:{callback:v=>v+'%'},grid:{color:'#f1f5f9'}},y:{grid:{display:false}}}
            }
        });
    }

    /* 10 ─ Mode kegiatan doughnut */
    if(modeKeg.length && document.getElementById('chartModeKegiatan')){
        const mc={qr:'#22c55e',manual:'#3b82f6'};
        new Chart(document.getElementById('chartModeKegiatan'),{
            type:'doughnut',
            data:{labels:modeKeg.map(r=>(r.label||'').toUpperCase()),
                datasets:[{data:modeKeg.map(r=>r.total),backgroundColor:modeKeg.map(r=>mc[r.label]||'#94a3b8'),borderWidth:2}]},
            options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{boxWidth:12,font:{size:10}}}}}
        });
    }
})();
@endif


{{-- ══════════════════════════════════════════════
     PEMBINA CHARTS
══════════════════════════════════════════════ --}}
@if(auth()->user()->hasRole('pembina') && isset($eskulList) && !$eskulList->isEmpty())
(function(){
    const statusChartData  = @json($eskulStatusChart ?? []);
    const checkinChartData = @json($eskulCheckinChart ?? []);
    const trendChartData   = @json($eskulTrendChart ?? []);
    const monthlyChartData = @json($eskulMonthlyChart ?? []);

    Object.keys(statusChartData).forEach(eid=>{
        /* Monthly line */
        const mData = monthlyChartData[eid] || [];
        const mc = document.getElementById('chartPembinaMonthly'+eid);
        if(mc && mData.length){
            new Chart(mc,{
                type:'line',
                data:{labels:mData.map(r=>r.label),
                    datasets:[
                        {label:'% Hadir',data:mData.map(r=>r.pct),borderColor:'#22c55e',backgroundColor:'rgba(34,197,94,.12)',fill:true,tension:.35,pointRadius:3,borderWidth:2},
                        {label:'Hadir',data:mData.map(r=>r.hadir),borderColor:'#3b82f6',backgroundColor:'rgba(59,130,246,.08)',fill:true,tension:.35,pointRadius:3,borderWidth:2,yAxisID:'y2'},
                    ]
                },
                options:{responsive:true,maintainAspectRatio:false,
                    plugins:{legend:{position:'top',labels:{boxWidth:10,font:{size:10}}}},
                    scales:{
                        y:{min:0,max:100,ticks:{callback:v=>v+'%'},grid:{color:'#f1f5f9'},title:{display:true,text:'%',font:{size:10}}},
                        y2:{position:'right',grid:{display:false},title:{display:true,text:'Jml',font:{size:10}},ticks:{precision:0}},
                        x:{grid:{display:false}}
                    }
                }
            });
        }

        /* Status doughnut */
        const sd = statusChartData[eid];
        const sc = document.getElementById('chartPembinaStatus'+eid);
        if(sc && sd){
            const labels=['Hadir','Alpha','Telat','Izin','Sakit'];
            const vals=[sd.hadir,sd.alpha,sd.telat,sd.izin,sd.sakit];
            new Chart(sc,{
                type:'doughnut',
                data:{labels,datasets:[{data:vals,backgroundColor:['#22c55e','#ef4444','#f59e0b','#3b82f6','#8b5cf6'],borderWidth:2}]},
                options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{boxWidth:10,font:{size:10}}}}}
            });
        }

        /* Weekly trend bar */
        const td = trendChartData[eid] || [];
        const tc = document.getElementById('chartPembinaTrend'+eid);
        if(tc && td.length){
            new Chart(tc,{
                type:'bar',
                data:{labels:td.map(r=>r.label),
                    datasets:[
                        {label:'% Hadir',data:td.map(r=>r.pct),backgroundColor:td.map(r=>r.pct>=75?'rgba(34,197,94,.85)':r.pct>=50?'rgba(245,158,11,.85)':'rgba(239,68,68,.85)'),borderRadius:6},
                    ]
                },
                options:{responsive:true,maintainAspectRatio:false,
                    plugins:{legend:{display:false}},
                    scales:{y:{min:0,max:100,ticks:{callback:v=>v+'%'},grid:{color:'#f1f5f9'}},x:{grid:{display:false}}}
                }
            });
        }

        /* Checkin grouped bar */
        const cd = checkinChartData[eid] || [];
        const cc = document.getElementById('chartPembinaCheckin'+eid);
        if(cc && cd.length){
            new Chart(cc,{
                type:'bar',
                data:{labels:cd.map(r=>r.tgl),
                    datasets:[
                        {label:'Tepat Waktu',data:cd.map(r=>r.on_time),backgroundColor:'rgba(34,197,94,.8)',borderRadius:4},
                        {label:'Terlambat',  data:cd.map(r=>r.late),   backgroundColor:'rgba(245,158,11,.8)',borderRadius:4},
                    ]
                },
                options:{responsive:true,maintainAspectRatio:false,
                    plugins:{legend:{position:'top',labels:{boxWidth:10,font:{size:10}}}},
                    scales:{x:{stacked:true,grid:{display:false}},y:{stacked:true,grid:{color:'#f1f5f9'},ticks:{precision:0}}}
                }
            });
        }
    });
})();
@endif


{{-- ══════════════════════════════════════════════
     SISWA CHARTS
══════════════════════════════════════════════ --}}
@if(!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('pembina') && isset($eskulRiwayat) && count($eskulRiwayat)>0)
(function(){
    const chartData = @json($siswaChartData ?? []);

    Object.keys(chartData).forEach(eid=>{
        const d = chartData[eid];

        /* Mini line trend */
        const lc = document.getElementById('siswaLine'+eid);
        if(lc && d.trend && d.trend.length){
            new Chart(lc,{
                type:'line',
                data:{labels:d.trend.map(r=>r.label),
                    datasets:[{data:d.trend.map(r=>r.pct),borderColor:'#22c55e',backgroundColor:'rgba(34,197,94,.15)',fill:true,tension:.4,pointRadius:2,borderWidth:2}]
                },
                options:{responsive:true,maintainAspectRatio:false,
                    plugins:{legend:{display:false},tooltip:{enabled:false}},
                    scales:{x:{display:false},y:{display:false,min:0,max:100}}
                }
            });
        }

        /* Mini doughnut */
        const dc = document.getElementById('siswaDoughnut'+eid);
        if(dc && d.status){
            const s=d.status;
            new Chart(dc,{
                type:'doughnut',
                data:{labels:['Hadir','Alpha','Telat','Izin','Sakit'],
                    datasets:[{data:[s.hadir,s.alpha,s.telat,s.izin,s.sakit],
                        backgroundColor:['#22c55e','#ef4444','#f59e0b','#3b82f6','#8b5cf6'],borderWidth:1}]
                },
                options:{responsive:true,maintainAspectRatio:false,
                    plugins:{legend:{display:false}},
                    cutout:'65%'
                }
            });
        }
    });
})();
@endif

(function(){
    const chartData = @json($siswaChartData ?? []);

    Object.keys(chartData).forEach(eid => {
        const d = chartData[eid];

        // Full trend line chart
        const lc = document.getElementById('siswaLineMain' + eid);
        if (lc && d.trend && d.trend.length) {
            new Chart(lc, {
                type: 'line',
                data: {
                    labels: d.trend.map(r => r.label),
                    datasets: [{
                        label: '% Kehadiran',
                        data: d.trend.map(r => r.pct),
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99,102,241,.1)',
                        fill: true, tension: .4, pointRadius: 5,
                        pointBackgroundColor: '#6366f1',
                        borderWidth: 2.5,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { min: 0, max: 100, ticks: { callback: v => v + '%' }, grid: { color: '#f1f5f9' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // Status doughnut
        const dc = document.getElementById('siswaDonut' + eid);
        if (dc && d.status) {
            const s = d.status;
            new Chart(dc, {
                type: 'doughnut',
                data: {
                    labels: ['Hadir', 'Alpha', 'Telat', 'Izin', 'Sakit'],
                    datasets: [{
                        data: [s.hadir, s.alpha, s.telat, s.izin, s.sakit],
                        backgroundColor: ['#22c55e', '#ef4444', '#f59e0b', '#3b82f6', '#8b5cf6'],
                        borderWidth: 2, borderColor: '#fff',
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    cutout: '62%',
                    plugins: {
                        legend: { position: 'right', labels: { boxWidth: 12, padding: 10, font: { size: 11 } } }
                    }
                }
            });
        }
    });
})();

function switchSiswaEskul(id, btn) {
    document.querySelectorAll('.siswa-eskul-panel').forEach(p => p.style.display = 'none');
    const panel = document.getElementById('siswa-panel-' + id);
    if (panel) panel.style.display = '';
    document.querySelectorAll('.siswa-eskul-tabs .s-tab').forEach(b => {
        b.classList.remove('active');
    });
    if (btn) btn.classList.add('active');
}
</script>
@endsection