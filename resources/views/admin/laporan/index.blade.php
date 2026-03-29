@extends('layouts.app')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap');

:root {
    --c-indigo: #4F46E5; --c-indigo-lt: #EEF2FF;
    --c-green:  #16A34A; --c-green-lt:  #DCFCE7;
    --c-red:    #DC2626; --c-red-lt:    #FEE2E2;
    --c-amber:  #D97706; --c-amber-lt:  #FEF3C7;
    --c-blue:   #2563EB; --c-blue-lt:   #DBEAFE;
    --c-violet: #7C3AED; --c-violet-lt: #EDE9FE;
    --c-slate:  #64748B; --c-slate-lt:  #F1F5F9;
    --radius:   14px;
    --shadow:   0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.04);
}
* { box-sizing: border-box; }
body, .lap-wrap * { font-family: 'Inter', sans-serif; }
h1, h2, h3, .metric-val { font-family: 'Sora', sans-serif; }

.lap-wrap { max-width: 1440px; margin: 0 auto; padding: 2rem 1.5rem 6rem; }

.page-header {
    background: #fff; border-radius: 20px; border: 1px solid #E2E8F0;
    padding: 1.75rem 2rem; margin-bottom: 1.5rem; position: relative; overflow: hidden;
}
.page-header::before {
    content: ''; position: absolute; inset: 0 0 auto 0; height: 3px;
    background: linear-gradient(90deg, var(--c-indigo) 0%, var(--c-green) 50%, var(--c-amber) 100%);
}
.page-header h1 { font-size: 1.4rem; font-weight: 800; color: #0F172A; margin: 0; }
.page-header p  { color: var(--c-slate); font-size: .82rem; margin: .3rem 0 0; }

.top-tabs {
    display: flex; gap: .35rem; flex-wrap: wrap;
    background: #F8FAFC; border: 1px solid #E2E8F0;
    border-radius: 16px; padding: 5px; margin-bottom: 1.5rem; width: fit-content;
}
.top-tabs a {
    display: inline-flex; align-items: center; gap: .4rem;
    font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .7px;
    padding: .5rem 1.1rem; border-radius: 11px; color: var(--c-slate);
    text-decoration: none; transition: all .15s; white-space: nowrap;
}
.top-tabs a:hover { color: var(--c-indigo); background: rgba(79,70,229,.06); }
.top-tabs a.active { background: #fff; color: var(--c-indigo); box-shadow: 0 2px 8px rgba(0,0,0,.09); }
.top-tabs a .badge-count {
    font-size: .6rem; background: var(--c-indigo-lt); color: var(--c-indigo);
    border-radius: 99px; padding: 1px 7px; font-weight: 800;
}

.eskul-nav {
    display: flex; gap: .35rem; flex-wrap: wrap;
    margin-bottom: 1.5rem; align-items: center;
}
.eskul-nav .en-label {
    font-size: .6rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;
    color: #94A3B8; white-space: nowrap; margin-right: .25rem;
}
.eskul-nav a {
    font-size: .7rem; font-weight: 600; padding: .35rem .85rem; border-radius: 99px;
    border: 1.5px solid #E2E8F0; color: var(--c-slate); text-decoration: none;
    background: #fff; transition: all .15s; white-space: nowrap;
}
.eskul-nav a:hover { border-color: var(--c-indigo); color: var(--c-indigo); }
.eskul-nav a.active { background: var(--c-indigo); color: #fff; border-color: var(--c-indigo); }

.filter-bar {
    background: #fff; border: 1px solid #E2E8F0; border-radius: var(--radius);
    padding: .9rem 1.25rem; margin-bottom: 1.5rem;
    display: flex; flex-wrap: wrap; gap: .75rem; align-items: center;
}
.filter-bar .fl { display: flex; align-items: center; gap: .5rem; }
.filter-bar label { font-size: .62rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #94A3B8; white-space: nowrap; }
.filter-bar input[type=date], .filter-bar select {
    border: 1.5px solid #E2E8F0; border-radius: 8px; padding: .38rem .7rem;
    font-size: .8rem; color: #1E293B; background: #F8FAFC; outline: none;
    font-family: 'Inter', sans-serif; transition: border .15s;
}
.filter-bar input:focus, .filter-bar select:focus { border-color: var(--c-indigo); }
.filter-sep { width: 1px; height: 24px; background: #E2E8F0; }
.btn-filter { font-size: .72rem; font-weight: 700; color: #fff; background: var(--c-indigo); border: none; border-radius: 8px; padding: .42rem 1rem; cursor: pointer; transition: opacity .15s; }
.btn-filter:hover { opacity: .9; }
.btn-reset  { font-size: .72rem; font-weight: 700; color: var(--c-slate); border: 1.5px solid #E2E8F0; background: #F8FAFC; border-radius: 8px; padding: .4rem .9rem; cursor: pointer; text-decoration: none; }
.btn-export { font-size: .7rem; font-weight: 700; display: inline-flex; align-items: center; gap: .35rem; padding: .4rem .9rem; border-radius: 8px; border: 1.5px solid #E2E8F0; background: #F8FAFC; color: var(--c-slate); text-decoration: none; cursor: pointer; transition: all .15s; white-space: nowrap; }
.btn-export:hover { border-color: var(--c-indigo); color: var(--c-indigo); }
.btn-export.green { border-color: #BBF7D0; background: #F0FDF4; color: var(--c-green); }
.btn-export.red   { border-color: #FECACA; background: #FEF2F2; color: var(--c-red); }

.kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
.kpi-card {
    border-radius: 18px; padding: 1.5rem 1.6rem;
    position: relative; overflow: hidden; color: #fff;
}
.kpi-card .blob {
    position: absolute; width: 120px; height: 120px; border-radius: 50%;
    background: rgba(255,255,255,.12); top: -25px; right: -30px; pointer-events: none;
}
.kpi-card .kl  { font-size: .6rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1.8px; opacity: .75; margin-bottom: .4rem; }
.kpi-card .kv  { font-size: 2.2rem; font-weight: 800; line-height: 1; letter-spacing: -.04em; }
.kpi-card .ks  { font-size: .72rem; opacity: .8; margin-top: .5rem; }

.stat-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: .75rem; margin-bottom: 1.5rem; }
.stat-card {
    background: #fff; border: 1px solid #E2E8F0; border-radius: 12px;
    padding: .9rem 1rem; position: relative; overflow: hidden;
}
.stat-card .stripe { position: absolute; left: 0; top: 0; bottom: 0; width: 3px; border-radius: 12px 0 0 12px; }
.stat-card .sv { font-size: 1.5rem; font-weight: 800; line-height: 1; margin-bottom: .15rem; }
.stat-card .sl { font-size: .56rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1.3px; color: #94A3B8; }

.chart-grid { display: grid; gap: 1rem; margin-bottom: 1.5rem; }
.cg-3-1 { grid-template-columns: 3fr 1.4fr; }
.cg-2   { grid-template-columns: 1fr 1fr; }
.chart-card { background: #fff; border: 1px solid #E2E8F0; border-radius: 16px; padding: 1.25rem 1.4rem; }
.cc-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
.cc-title { font-size: .8rem; font-weight: 700; color: #0F172A; display: flex; align-items: center; gap: .4rem; }
.chart-wrap { position: relative; height: 220px; }

.data-card { background: #fff; border: 1px solid #E2E8F0; border-radius: 16px; overflow: hidden; margin-bottom: 1.5rem; }
.dc-head { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.3rem; border-bottom: 1px solid #F1F5F9; flex-wrap: wrap; gap: .5rem; }
.dc-title { font-size: .82rem; font-weight: 700; color: #0F172A; display: flex; align-items: center; gap: .4rem; }
table.dt { width: 100%; border-collapse: collapse; }
table.dt th { font-size: .56rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: #94A3B8; padding: .7rem 1rem; background: #F8FAFC; border-bottom: 1px solid #F1F5F9; white-space: nowrap; text-align: left; }
table.dt td { padding: .75rem 1rem; border-bottom: 1px solid #F8FAFC; font-size: .8rem; vertical-align: middle; }
table.dt tr:last-child td { border-bottom: none; }
table.dt tr:hover td { background: #FAFBFF; }

.pill { display: inline-flex; align-items: center; font-size: .58rem; font-weight: 800; text-transform: uppercase; letter-spacing: .6px; padding: .22rem .6rem; border-radius: 99px; white-space: nowrap; }
.ph { background: var(--c-green-lt);  color: var(--c-green); }
.pa { background: var(--c-red-lt);    color: var(--c-red); }
.pt { background: var(--c-amber-lt);  color: var(--c-amber); }
.pi { background: var(--c-blue-lt);   color: var(--c-blue); }
.ps { background: var(--c-violet-lt); color: var(--c-violet); }
.pl { background: var(--c-slate-lt);  color: var(--c-slate); }
.pct-bar { height: 5px; background: #F1F5F9; border-radius: 99px; overflow: hidden; min-width: 60px; }
.pct-fill { height: 100%; border-radius: 99px; }
.rank-num { display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 50%; font-size: .7rem; font-weight: 800; }
.rank-1 { background: #FEF9C3; color: #B45309; }
.rank-2 { background: #F1F5F9; color: #475569; }
.rank-3 { background: #FEE2E2; color: #DC2626; }
.rank-n { background: #F8FAFC; color: #94A3B8; }
.detail-link { font-size: .68rem; font-weight: 700; color: var(--c-indigo); text-decoration: none; display: inline-flex; align-items: center; gap: .3rem; padding: .3rem .7rem; border: 1.5px solid var(--c-indigo-lt); border-radius: 8px; transition: all .15s; }
.detail-link:hover { background: var(--c-indigo-lt); }

@media(max-width:1100px){ .kpi-grid{grid-template-columns:1fr 1fr;} .stat-grid{grid-template-columns:repeat(3,1fr);} .cg-3-1,.cg-2{grid-template-columns:1fr;} }
@media(max-width:600px){ .kpi-grid{grid-template-columns:1fr 1fr;} .stat-grid{grid-template-columns:repeat(2,1fr);} }
</style>

<div class="container-fluid lap-wrap">

    {{-- ── Page Header ── --}}
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h1><i class="bi bi-bar-chart me-2" style="color:var(--c-indigo)"></i>Laporan & Analitik</h1>
                <p>Tahun Ajaran <strong>{{ $schoolYear->name }}</strong> &nbsp;·&nbsp; Dashboard kehadiran seluruh ekstrakurikuler</p>
            </div>
            {{-- ★ TOMBOL EXPORT (Excel + PDF) ★ --}}
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.laporan.export-excel', array_filter($filter)) }}" class="btn-export green">
                    <i class="bi bi-file-earmark-excel"></i> Excel
                </a>
                <a href="{{ route('admin.laporan.export-pdf', array_filter($filter)) }}" class="btn-export red">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                </a>
            </div>
        </div>
    </div>

    {{-- ── Top-Level Navigation ── --}}
    <div class="top-tabs">
        <a href="{{ route('admin.laporan.index', array_filter($filter)) }}" class="active">
            <i class="fas fa-globe"></i> Global
        </a>
        <a href="{{ route('admin.laporan.per-pembina', array_filter($filter)) }}">
            <i class="bi bi-person-workspace"></i> Per Pembina
        </a>
        <a href="{{ route('admin.laporan.per-siswa', array_filter($filter)) }}">
            <i class="bi bi-people"></i> Per Siswa
        </a>
        <a href="#" id="toggleEskulNav" onclick="document.getElementById('eskulNavRow').classList.toggle('d-none');return false;">
            <i class="bi bi-trophy"></i> Per Eskul
            <span class="badge-count">{{ $eskuls->count() }}</span>
        </a>
    </div>

    {{-- ── Eskul Sub-Nav ── --}}
    <div id="eskulNavRow" class="eskul-nav d-none mb-3">
        <span class="en-label">Pilih Eskul:</span>
        @foreach($eskuls as $e)
        <a href="{{ route('admin.laporan.per-eskul', array_merge(['eskul'=>$e->id], array_filter($filter))) }}">
            {{ $e->name }}
        </a>
        @endforeach
    </div>

    {{-- ── Filter ── --}}
    <form action="{{ route('admin.laporan.index') }}" method="GET">
        <div class="filter-bar">
            <div class="fl"><label>Dari</label><input type="date" name="date_from" value="{{ $filter['date_from']??'' }}"></div>
            <div class="filter-sep"></div>
            <div class="fl"><label>Sampai</label><input type="date" name="date_to" value="{{ $filter['date_to']??'' }}"></div>
            <div class="filter-sep"></div>
            <div class="fl">
                <label>Eskul</label>
                <select name="eskul_id">
                    <option value="">Semua</option>
                    @foreach($eskuls as $e)
                    <option value="{{ $e->id }}" {{ ($filter['eskul_id']??'')==$e->id?'selected':'' }}>{{ $e->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-filter"><i class="bi bi-funnel me-1"></i>Filter</button>
            <a href="{{ route('admin.laporan.index') }}" class="btn-reset">Reset</a>
        </div>
    </form>

    {{-- ── KPI Cards ── --}}
    <div class="kpi-grid">
        <div class="kpi-card" style="background:linear-gradient(135deg,#16A34A,#15803D)">
            <div class="blob"></div>
            <div class="kl">Rate Kehadiran</div>
            <div class="kv">{{ $globalSummary['pct'] }}%</div>
            <div class="ks">{{ number_format($globalSummary['hadir']) }} dari {{ number_format($globalSummary['total']) }}</div>
        </div>
        <div class="kpi-card" style="background:linear-gradient(135deg,#4F46E5,#4338CA)">
            <div class="blob"></div>
            <div class="kl">Total Kegiatan</div>
            <div class="kv">{{ $globalSummary['total_kegiatan'] }}</div>
            <div class="ks">Kegiatan selesai direkap</div>
        </div>
        <div class="kpi-card" style="background:linear-gradient(135deg,#DC2626,#B91C1C)">
            <div class="blob"></div>
            <div class="kl">Total Alpha</div>
            <div class="kv">{{ number_format($globalSummary['alpha']) }}</div>
            <div class="ks">{{ count($alphaWarning) }} siswa alpha ≥3x</div>
        </div>
        <div class="kpi-card" style="background:linear-gradient(135deg,#D97706,#B45309)">
            <div class="blob"></div>
            <div class="kl">Total Telat</div>
            <div class="kv">{{ number_format($globalSummary['telat']) }}</div>
            <div class="ks">Check-in terlambat</div>
        </div>
    </div>

    {{-- ── Status Breakdown ── --}}
    <div class="stat-grid">
        @php $breakdown = [
            ['val'=>$globalSummary['hadir'],'label'=>'Hadir','color'=>'#16A34A'],
            ['val'=>$globalSummary['alpha'],'label'=>'Alpha','color'=>'#DC2626'],
            ['val'=>$globalSummary['telat'],'label'=>'Telat','color'=>'#D97706'],
            ['val'=>$globalSummary['izin'], 'label'=>'Izin', 'color'=>'#2563EB'],
            ['val'=>$globalSummary['sakit'],'label'=>'Sakit','color'=>'#7C3AED'],
            ['val'=>$globalSummary['libur'],'label'=>'Libur','color'=>'#64748B'],
        ]; @endphp
        @foreach($breakdown as $s)
        <div class="stat-card">
            <div class="stripe" style="background:{{ $s['color'] }}"></div>
            <div class="sv" style="color:{{ $s['color'] }}">{{ number_format($s['val']) }}</div>
            <div class="sl">{{ $s['label'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- ── Charts Row 1 ── --}}
    <div class="chart-grid cg-3-1">
        <div class="chart-card">
            <div class="cc-head">
                <div class="cc-title"><i class=" bi bi-graph-up" style="color:var(--c-indigo)"></i>Tren Kehadiran 6 Bulan</div>
            </div>
            <div class="chart-wrap"><canvas id="cTrend"></canvas></div>
        </div>
        <div class="chart-card">
            <div class="cc-head"><div class="cc-title"><i class="bi bi-pie-chart" style="color:var(--c-blue)"></i>Distribusi</div></div>
            <div class="chart-wrap"><canvas id="cDist"></canvas></div>
        </div>
    </div>

    {{-- ── Charts Row 2 ── --}}
    <div class="chart-grid cg-2">
        <div class="chart-card">
            <div class="cc-head"><div class="cc-title"><i class="bi bi-bar-chart" style="color:var(--c-amber)"></i>Komposisi Status per Bulan</div></div>
            <div class="chart-wrap"><canvas id="cStacked"></canvas></div>
        </div>
        <div class="chart-card">
            <div class="cc-head"><div class="cc-title"><i class="fas fa-sort-amount-down" style="color:var(--c-green)"></i>Ranking % Kehadiran per Eskul</div></div>
            <div style="position:relative;height:{{ max(200, count($eskulRanking)*38+50) }}px;">
                <canvas id="cEskul"></canvas>
            </div>
        </div>
    </div>

    {{-- ── Eskul Ranking Table ── --}}
    <div class="data-card">
        <div class="dc-head">
            <div class="dc-title"><i class="fas fa-list-ol" style="color:var(--c-indigo)"></i>Ranking Kehadiran per Eskul</div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.laporan.export-excel', array_filter($filter)) }}" class="btn-export green"><i class="bi bi-file-earmark-excel"></i>Excel</a>
                <a href="{{ route('admin.laporan.export-pdf', array_filter($filter)) }}" class="btn-export red"><i class="bi bi-file-earmark-pdf"></i>PDF</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="dt">
                <thead>
                    <tr>
                        <th style="width:40px">#</th><th>Eskul</th>
                        <th class="text-center">Anggota</th><th class="text-center">Kegiatan</th>
                        <th class="text-center">Hadir</th><th class="text-center">Alpha</th>
                        <th class="text-center">Telat</th><th class="text-center">Izin</th><th class="text-center">Sakit</th>
                        <th style="min-width:140px">% Kehadiran</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($eskulRanking as $i => $row)
                    <tr>
                        <td>
                            <span class="rank-num {{ $i===0?'rank-1':($i===1?'rank-2':($i===2?'rank-3':'rank-n')) }}">
                                {{ $i+1 }}
                            </span>
                        </td>
                        <td class="fw-bold" style="color:#0F172A">{{ $row['nama'] }}</td>
                        <td class="text-center"><span class="pill pi">{{ $row['anggota'] }}</span></td>
                        <td class="text-center"><span class="pill pl">{{ $row['kegiatan'] }}</span></td>
                        <td class="text-center"><span class="pill ph">{{ $row['hadir'] }}</span></td>
                        <td class="text-center"><span class="pill pa">{{ $row['alpha'] }}</span></td>
                        <td class="text-center"><span class="pill pt">{{ $row['telat'] }}</span></td>
                        <td class="text-center"><span class="pill pi">{{ $row['izin'] }}</span></td>
                        <td class="text-center"><span class="pill ps">{{ $row['sakit'] }}</span></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="pct-bar flex-fill">
                                    <div class="pct-fill" style="width:{{ $row['pct'] }}%;background:{{ $row['pct']>=75?'#16A34A':($row['pct']>=50?'#D97706':'#DC2626') }}"></div>
                                </div>
                                <span class="fw-semibold" style="font-size:.78rem;width:38px;text-align:right;color:{{ $row['pct']>=75?'#16A34A':($row['pct']>=50?'#D97706':'#DC2626') }}">{{ $row['pct'] }}%</span>
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('admin.laporan.per-eskul', $row['id']) }}" class="detail-link">
                                Detail <i class="bi bi-arrow-right" style="font-size:.55rem"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="11" class="text-center py-5 text-muted">Belum ada data eskul.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Alpha Warning ── --}}
    @if(count($alphaWarning) > 0)
    <div class="data-card">
        <div class="dc-head">
            <div class="dc-title">
                <i class="bi bi-exclamation-triangle" style="color:var(--c-red)"></i>
                Peringatan Alpha Global <span class="pill pa ms-1">≥3x</span>
            </div>
            <span class="text-muted" style="font-size:.75rem">{{ count($alphaWarning) }} siswa</span>
        </div>
        <div class="table-responsive">
            <table class="dt">
                <thead><tr><th>#</th><th>Nama Siswa</th><th>NISN</th><th>Eskul</th><th class="text-center">Total Alpha</th><th>Tanggal Alpha</th></tr></thead>
                <tbody>
                    @foreach($alphaWarning as $i => $aw)
                    <tr>
                        <td class="text-muted">{{ $i+1 }}</td>
                        <td class="fw-bold" style="color:#0F172A">{{ $aw['nama'] }}</td>
                        <td><code style="font-size:.75rem;background:#F8FAFC;padding:1px 5px;border-radius:4px">{{ $aw['nisn']??'-' }}</code></td>
                        <td style="color:var(--c-slate);font-size:.76rem">{{ $aw['eskul'] }}</td>
                        <td class="text-center"><span class="pill pa">{{ $aw['total_alpha'] }}×</span></td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach(array_slice($aw['tanggal'],0,4) as $tgl)
                                <span style="font-size:.6rem;background:#FEF2F2;color:#991B1B;padding:2px 7px;border-radius:6px;font-weight:600">{{ $tgl }}</span>
                                @endforeach
                                @if(count($aw['tanggal']) > 4)
                                <span style="font-size:.6rem;background:#F1F5F9;color:#64748B;padding:2px 7px;border-radius:6px;font-weight:600">+{{ count($aw['tanggal'])-4 }} lagi</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function(){
    const TREND = @json($monthlyTrend ?? []);
    const ESKUL = @json($eskulRanking ?? []);
    const GS    = @json($globalSummary ?? []);
    const COLORS = ['#16A34A','#DC2626','#D97706','#2563EB','#7C3AED','#64748B'];

    const lc = document.getElementById('cTrend');
    if(lc && TREND.length) new Chart(lc, {
        type: 'line',
        data: {
            labels: TREND.map(r => r.label),
            datasets: [{
                label: '% Hadir', data: TREND.map(r => r.pct),
                borderColor: '#4F46E5', backgroundColor: 'rgba(79,70,229,.08)',
                fill: true, tension: .4, pointRadius: 5, borderWidth: 2.5,
                pointBackgroundColor: '#4F46E5', pointBorderColor: '#fff', pointBorderWidth: 2,
            }]
        },
        options: { responsive:true, maintainAspectRatio:false,
            plugins: { legend:{display:false}, tooltip:{callbacks:{label:v=>v.raw+'%'}} },
            scales: {
                y:{min:0,max:100,ticks:{callback:v=>v+'%'},grid:{color:'#F1F5F9'}},
                x:{grid:{display:false}}
            }
        }
    });

    const dc = document.getElementById('cDist');
    if(dc) new Chart(dc, {
        type: 'doughnut',
        data: {
            labels: ['Hadir','Alpha','Telat','Izin','Sakit','Libur'],
            datasets: [{
                data: [GS.hadir,GS.alpha,GS.telat,GS.izin,GS.sakit,GS.libur],
                backgroundColor: COLORS, borderWidth: 2, borderColor: '#fff'
            }]
        },
        options: { responsive:true, maintainAspectRatio:false, cutout:'62%',
            plugins:{legend:{position:'right',labels:{boxWidth:10,font:{size:10},padding:8}}}
        }
    });

    const sc = document.getElementById('cStacked');
    if(sc && TREND.length) new Chart(sc, {
        type: 'bar',
        data: {
            labels: TREND.map(r => r.label),
            datasets: [
                {label:'Hadir',data:TREND.map(r=>r.hadir),backgroundColor:'#16A34A',borderRadius:3},
                {label:'Alpha',data:TREND.map(r=>r.alpha),backgroundColor:'#DC2626',borderRadius:3},
                {label:'Telat',data:TREND.map(r=>r.telat),backgroundColor:'#D97706',borderRadius:3},
                {label:'Izin', data:TREND.map(r=>r.izin), backgroundColor:'#2563EB',borderRadius:3},
                {label:'Sakit',data:TREND.map(r=>r.sakit),backgroundColor:'#7C3AED',borderRadius:3},
            ]
        },
        options: { responsive:true, maintainAspectRatio:false,
            plugins:{legend:{position:'top',labels:{boxWidth:10,font:{size:10},padding:10}}},
            scales:{x:{stacked:true,grid:{display:false}},y:{stacked:true,grid:{color:'#F1F5F9'}}}
        }
    });

    const ec = document.getElementById('cEskul');
    if(ec && ESKUL.length) new Chart(ec, {
        type: 'bar',
        data: {
            labels: ESKUL.map(r => r.nama),
            datasets: [{
                label:'% Kehadiran', data: ESKUL.map(r => r.pct),
                backgroundColor: ESKUL.map(r => r.pct>=75?'rgba(22,163,74,.8)':r.pct>=50?'rgba(217,119,6,.8)':'rgba(220,38,38,.8)'),
                borderRadius: 5,
            }]
        },
        options: { indexAxis:'y', responsive:true, maintainAspectRatio:false,
            plugins:{legend:{display:false}},
            scales:{
                x:{min:0,max:100,ticks:{callback:v=>v+'%'},grid:{color:'#F1F5F9'}},
                y:{grid:{display:false},ticks:{font:{size:11}}}
            }
        }
    });
})();
</script>
@endsection