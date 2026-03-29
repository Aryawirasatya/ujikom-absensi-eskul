@extends('layouts.app')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600&display=swap');
:root{--c-indigo:#4F46E5;--c-indigo-lt:#EEF2FF;--c-green:#16A34A;--c-green-lt:#DCFCE7;--c-red:#DC2626;--c-red-lt:#FEE2E2;--c-amber:#D97706;--c-amber-lt:#FEF3C7;--c-blue:#2563EB;--c-blue-lt:#DBEAFE;--c-violet:#7C3AED;--c-violet-lt:#EDE9FE;--c-slate:#64748B;--c-slate-lt:#F1F5F9;}
*{box-sizing:border-box;}
body,.lap-wrap *{font-family:'Inter',sans-serif;}
h1,.metric-val{font-family:'Sora',sans-serif;}
.lap-wrap{max-width:1440px;margin:0 auto;padding:2rem 1.5rem 6rem;}
.page-header{background:#fff;border-radius:20px;border:1px solid #E2E8F0;padding:1.75rem 2rem;margin-bottom:1.5rem;position:relative;overflow:hidden;}
.page-header::before{content:'';position:absolute;inset:0 0 auto 0;height:3px;background:linear-gradient(90deg,var(--c-green),var(--c-indigo));}
.page-header h1{font-size:1.4rem;font-weight:800;color:#0F172A;margin:0;}
.page-header p{color:var(--c-slate);font-size:.82rem;margin:.3rem 0 0;}

/* ── Top navigation (same across all admin laporan) ── */
.top-tabs{display:flex;gap:.35rem;flex-wrap:wrap;background:#F8FAFC;border:1px solid #E2E8F0;border-radius:16px;padding:5px;margin-bottom:.75rem;width:fit-content;}
.top-tabs a{display:inline-flex;align-items:center;gap:.4rem;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.7px;padding:.5rem 1.1rem;border-radius:11px;color:var(--c-slate);text-decoration:none;transition:all .15s;white-space:nowrap;}
.top-tabs a.active{background:#fff;color:var(--c-indigo);box-shadow:0 2px 8px rgba(0,0,0,.09);}
.top-tabs a .badge-count{font-size:.6rem;background:var(--c-indigo-lt);color:var(--c-indigo);border-radius:99px;padding:1px 7px;font-weight:800;}
.eskul-nav{display:flex;gap:.35rem;flex-wrap:wrap;margin-bottom:1.5rem;align-items:center;padding:.5rem .75rem;background:#F8FAFC;border:1px solid #E2E8F0;border-radius:12px;}
.eskul-nav .en-label{font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:#94A3B8;margin-right:.25rem;white-space:nowrap;}
.eskul-nav a{font-size:.7rem;font-weight:600;padding:.35rem .85rem;border-radius:99px;border:1.5px solid #E2E8F0;color:var(--c-slate);text-decoration:none;background:#fff;transition:all .15s;}
.eskul-nav a:hover{border-color:var(--c-indigo);color:var(--c-indigo);}
.eskul-nav a.active{background:var(--c-indigo);color:#fff;border-color:var(--c-indigo);}

/* ── Inner tab nav (kegiatan / siswa / tren / alpha) ── */
.inner-tabs{display:flex;gap:.35rem;flex-wrap:wrap;background:#F8FAFC;border:1px solid #E2E8F0;border-radius:14px;padding:4px;margin-bottom:1.5rem;width:fit-content;}
.inner-tab{display:inline-flex;align-items:center;gap:.4rem;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.7px;padding:.45rem 1rem;border-radius:10px;color:var(--c-slate);text-decoration:none;transition:all .15s;white-space:nowrap;}
.inner-tab.active{background:#fff;color:var(--c-indigo);box-shadow:0 2px 8px rgba(0,0,0,.09);}

.filter-bar{background:#fff;border:1px solid #E2E8F0;border-radius:14px;padding:.9rem 1.25rem;margin-bottom:1.5rem;display:flex;flex-wrap:wrap;gap:.75rem;align-items:center;}
.filter-bar .fl{display:flex;align-items:center;gap:.5rem;}
.filter-bar label{font-size:.62rem;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:#94A3B8;white-space:nowrap;}
.filter-bar input,.filter-bar select{border:1.5px solid #E2E8F0;border-radius:8px;padding:.38rem .7rem;font-size:.8rem;color:#1E293B;background:#F8FAFC;outline:none;font-family:'Inter',sans-serif;}
.filter-sep{width:1px;height:24px;background:#E2E8F0;}
.btn-filter{font-size:.72rem;font-weight:700;color:#fff;background:var(--c-indigo);border:none;border-radius:8px;padding:.42rem 1rem;cursor:pointer;}
.btn-reset{font-size:.72rem;font-weight:700;color:var(--c-slate);border:1.5px solid #E2E8F0;background:#F8FAFC;border-radius:8px;padding:.4rem .9rem;cursor:pointer;text-decoration:none;}
.btn-export{font-size:.7rem;font-weight:700;display:inline-flex;align-items:center;gap:.35rem;padding:.4rem .9rem;border-radius:8px;border:1.5px solid #E2E8F0;background:#F8FAFC;color:var(--c-slate);text-decoration:none;cursor:pointer;transition:all .15s;}
.btn-export.green{border-color:#BBF7D0;background:#F0FDF4;color:var(--c-green);}
.btn-export.red{border-color:#FECACA;background:#FEF2F2;color:var(--c-red);}
.stat-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:.75rem;margin-bottom:1.5rem;}
.stat-card{background:#fff;border:1px solid #E2E8F0;border-radius:12px;padding:.9rem 1rem;position:relative;overflow:hidden;}
.stat-card .stripe{position:absolute;left:0;top:0;bottom:0;width:3px;border-radius:12px 0 0 12px;}
.stat-card .sv{font-family:'Sora',sans-serif;font-size:1.5rem;font-weight:800;line-height:1;}
.stat-card .sl{font-size:.56rem;font-weight:700;text-transform:uppercase;letter-spacing:1.3px;color:#94A3B8;margin-top:.2rem;}
.chart-grid{display:grid;gap:1rem;margin-bottom:1.5rem;}
.cg-2{grid-template-columns:1fr 1fr;}
.chart-card{background:#fff;border:1px solid #E2E8F0;border-radius:16px;padding:1.25rem 1.4rem;}
.cc-title{font-size:.8rem;font-weight:700;color:#0F172A;display:flex;align-items:center;gap:.4rem;margin-bottom:1rem;}
.chart-wrap{position:relative;height:220px;}
.data-card{background:#fff;border:1px solid #E2E8F0;border-radius:16px;overflow:hidden;margin-bottom:1.5rem;}
.dc-head{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.3rem;border-bottom:1px solid #F1F5F9;flex-wrap:wrap;gap:.5rem;}
.dc-title{font-size:.82rem;font-weight:700;color:#0F172A;display:flex;align-items:center;gap:.4rem;}
table.dt{width:100%;border-collapse:collapse;}
table.dt th{font-size:.56rem;font-weight:800;text-transform:uppercase;letter-spacing:1.5px;color:#94A3B8;padding:.7rem 1rem;background:#F8FAFC;border-bottom:1px solid #F1F5F9;white-space:nowrap;text-align:left;}
table.dt td{padding:.75rem 1rem;border-bottom:1px solid #F8FAFC;font-size:.8rem;vertical-align:middle;}
table.dt tr:last-child td{border-bottom:none;}
table.dt tr:hover td{background:#FAFBFF;}
.pill{display:inline-flex;align-items:center;font-size:.58rem;font-weight:800;text-transform:uppercase;letter-spacing:.6px;padding:.22rem .6rem;border-radius:99px;white-space:nowrap;}
.ph{background:var(--c-green-lt);color:var(--c-green);}
.pa{background:var(--c-red-lt);color:var(--c-red);}
.pt{background:var(--c-amber-lt);color:var(--c-amber);}
.pi{background:var(--c-blue-lt);color:var(--c-blue);}
.ps{background:var(--c-violet-lt);color:var(--c-violet);}
.pl{background:var(--c-slate-lt);color:var(--c-slate);}
.pct-bar{height:5px;background:#F1F5F9;border-radius:99px;overflow:hidden;min-width:50px;}
.pct-fill{height:100%;border-radius:99px;}
.back-link{display:inline-flex;align-items:center;gap:.35rem;font-size:.75rem;font-weight:600;color:var(--c-slate);text-decoration:none;padding:.35rem .8rem;border:1.5px solid #E2E8F0;border-radius:8px;background:#F8FAFC;transition:all .15s;margin-bottom:1rem;}
.back-link:hover{border-color:var(--c-indigo);color:var(--c-indigo);}
@media(max-width:900px){.stat-grid{grid-template-columns:repeat(3,1fr);}.cg-2{grid-template-columns:1fr;}}
</style>

<div class="container-fluid lap-wrap">

    <a href="{{ route('admin.laporan.index') }}" class="back-link"><i class="bi bi-arrow-left"></i> Kembali ke Global</a>

    <div class="page-header">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h1><i class="bi bi-trophy me-2" style="color:var(--c-green)"></i>{{ $eskul->name }}</h1>
                <p>Tahun Ajaran <strong>{{ $schoolYear->name }}</strong> &nbsp;·&nbsp; Laporan detail per ekstrakurikuler</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.laporan.per-eskul.export-excel', array_merge(['eskul'=>$eskul->id],array_filter($filter))) }}" class="btn-export green">
                    <i class="bi bi-file-earmark-excel"></i> Excel
                </a>
                <a href="{{ route('admin.laporan.per-eskul.export-pdf', array_merge(['eskul'=>$eskul->id],array_filter($filter))) }}" class="btn-export red">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                </a>
            </div>
        </div>
    </div>

    {{-- ── Top Navigation ── --}}
    <div class="top-tabs">
        <a href="{{ route('admin.laporan.index') }}"><i class="fas fa-globe"></i> Global</a>
        <a href="{{ route('admin.laporan.per-pembina') }}"><i class="bi bi-person-workspace"></i> Per Pembina</a>
        <a href="{{ route('admin.laporan.per-siswa') }}"><i class="bi bi-people"></i> Per Siswa</a>
        <a href="#" onclick="document.getElementById('eskulNavRow').classList.toggle('d-none');return false;" class="active">
            <i class="bi bi-trophy"></i> Per Eskul
            <span class="badge-count">{{ $eskuls->count() }}</span>
        </a>
    </div>

    {{-- ── Eskul Sub-Nav (always visible here) ── --}}
    <div id="eskulNavRow" class="eskul-nav">
        <span class="en-label">Eskul:</span>
        @foreach($eskuls as $e)
        <a href="{{ route('admin.laporan.per-eskul', $e->id) }}" class="{{ $eskul->id==$e->id?'active':'' }}">
            {{ $e->name }}
        </a>
        @endforeach
    </div>

    {{-- ── Filter ── --}}
    <form action="{{ route('admin.laporan.per-eskul', $eskul->id) }}" method="GET">
        <div class="filter-bar">
            <div class="fl"><label>Dari</label><input type="date" name="date_from" value="{{ $filter['date_from']??'' }}"></div>
            <div class="filter-sep"></div>
            <div class="fl"><label>Sampai</label><input type="date" name="date_to" value="{{ $filter['date_to']??'' }}"></div>
            <div class="filter-sep"></div>
            <div class="fl">
                <label>Tipe</label>
                <select name="type">
                    <option value="">Semua</option>
                    <option value="routine" {{ ($filter['type']??'')==='routine'?'selected':'' }}>Rutin</option>
                    <option value="non_routine" {{ ($filter['type']??'')==='non_routine'?'selected':'' }}>Non-Rutin</option>
                </select>
            </div>
            <input type="hidden" name="tab" value="{{ $tab }}">
            <button type="submit" class="btn-filter"><i class="bi bi-funnel me-1"></i>Filter</button>
            <a href="{{ route('admin.laporan.per-eskul', $eskul->id) }}" class="btn-reset">Reset</a>
        </div>
    </form>

    {{-- ── Inner Tab Nav ── --}}
    <div class="inner-tabs">
        @foreach(['kegiatan'=>['bi bi-calendar-check','Per Kegiatan'],'siswa'=>['bi bi-people','Per Siswa'],'tren'=>[' bi bi-graph-up','Tren'],'alpha'=>['bi bi-exclamation-triangle','Alpha']] as $k=>[$icon,$lbl])
        <a href="{{ route('admin.laporan.per-eskul', array_merge(['eskul'=>$eskul->id,'tab'=>$k],array_filter($filter))) }}"
           class="inner-tab {{ $tab===$k?'active':'' }}">
            <i class="{{ $icon }}"></i>{{ $lbl }}
        </a>
        @endforeach
    </div>

    {{-- ── Stats ── --}}
    <div class="stat-grid">
        @php $s = $stats; @endphp
        @foreach([
            ['val'=>$s['totalKegiatan'],'label'=>'Total Kegiatan','color'=>'#4F46E5'],
            ['val'=>$s['pctKeseluruhan'].'%','label'=>'Rate Kehadiran','color'=>$s['pctKeseluruhan']>=75?'#16A34A':($s['pctKeseluruhan']>=50?'#D97706':'#DC2626')],
            ['val'=>collect($studentSummary)->count(),'label'=>'Total Anggota','color'=>'#2563EB'],
            ['val'=>count($alphaWarning),'label'=>'Siswa Alpha ≥3x','color'=>'#DC2626'],
            ['val'=>collect($activitySummary)->sum('alpha'),'label'=>'Total Alpha','color'=>'#D97706'],
        ] as $sc)
        <div class="stat-card">
            <div class="stripe" style="background:{{ $sc['color'] }}"></div>
            <div class="sv" style="color:{{ $sc['color'] }}">{{ $sc['val'] }}</div>
            <div class="sl">{{ $sc['label'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- ════ PER KEGIATAN ════ --}}
    @if($tab === 'kegiatan')
    <div class="data-card">
        <div class="dc-head">
            <div class="dc-title"><i class="bi bi-calendar-check" style="color:var(--c-indigo)"></i>Rekap Per Kegiatan</div>
            <a href="{{ route('admin.laporan.per-eskul.export-excel', array_merge(['eskul'=>$eskul->id],array_filter($filter))) }}" class="btn-export green"><i class="bi bi-file-earmark-excel"></i>Excel</a>
        </div>
        <div class="table-responsive">
            <table class="dt">
                <thead><tr><th>#</th><th>Tanggal</th><th>Judul Kegiatan</th><th class="text-center">Tipe</th><th class="text-center">Mode</th><th class="text-center">Total</th><th class="text-center">Hadir</th><th class="text-center">Telat</th><th class="text-center">Alpha</th><th class="text-center">Izin</th><th class="text-center">Sakit</th><th style="min-width:130px">% Hadir</th></tr></thead>
                <tbody>
                    @forelse($activitySummary as $i => $act)
                    <tr>
                        <td class="text-muted" style="font-size:.75rem">{{ $i+1 }}</td>
                        <td style="font-size:.78rem;color:#0F172A;font-weight:600">{{ \Carbon\Carbon::parse($act['tanggal'])->format('d M Y') }}</td>
                        <td class="fw-semibold" style="color:#0F172A">{{ $act['judul'] }}</td>
                        <td class="text-center"><span class="pill {{ $act['tipe']==='routine'?'ph':'pi' }}">{{ $act['tipe']==='routine'?'Rutin':'Khusus' }}</span></td>
                        <td class="text-center"><span class="pill pl">{{ strtoupper($act['mode']??'-') }}</span></td>
                        <td class="text-center fw-semibold">{{ $act['total'] }}</td>
                        <td class="text-center"><span class="pill ph">{{ $act['hadir'] }}</span></td>
                        <td class="text-center"><span class="pill pt">{{ $act['telat'] }}</span></td>
                        <td class="text-center"><span class="pill pa">{{ $act['alpha'] }}</span></td>
                        <td class="text-center"><span class="pill pi">{{ $act['izin'] }}</span></td>
                        <td class="text-center"><span class="pill ps">{{ $act['sakit'] }}</span></td>
                        <td>
                            @php $p=$act['pct']; $pc=$p>=75?'#16A34A':($p>=50?'#D97706':'#DC2626'); @endphp
                            <div class="d-flex align-items-center gap-2">
                                <div class="pct-bar flex-fill"><div class="pct-fill" style="width:{{ $p }}%;background:{{ $pc }}"></div></div>
                                <span class="fw-semibold" style="font-size:.78rem;color:{{ $pc }};width:36px;text-align:right">{{ $p }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="12" class="text-center py-5 text-muted">Belum ada data kegiatan selesai.</td></tr>
                    @endforelse
                </tbody>
                @if(count($activitySummary)>0)
                <tfoot style="background:#F8FAFC;border-top:2px solid #E2E8F0">
                    <tr>
                        <td colspan="5" class="fw-bold ps-3" style="font-size:.75rem;padding:.75rem 1rem">TOTAL</td>
                        <td class="text-center fw-bold">{{ collect($activitySummary)->sum('total') }}</td>
                        <td class="text-center fw-bold" style="color:var(--c-green)">{{ collect($activitySummary)->sum('hadir') }}</td>
                        <td class="text-center fw-bold" style="color:var(--c-amber)">{{ collect($activitySummary)->sum('telat') }}</td>
                        <td class="text-center fw-bold" style="color:var(--c-red)">{{ collect($activitySummary)->sum('alpha') }}</td>
                        <td class="text-center fw-bold" style="color:var(--c-blue)">{{ collect($activitySummary)->sum('izin') }}</td>
                        <td class="text-center fw-bold" style="color:var(--c-violet)">{{ collect($activitySummary)->sum('sakit') }}</td>
                        <td class="fw-bold" style="color:var(--c-indigo);padding:.75rem 1rem">{{ $stats['pctKeseluruhan'] }}%</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
    @endif

    {{-- ════ PER SISWA ════ --}}
    @if($tab === 'siswa')
    <div class="data-card">
        <div class="dc-head">
            <div class="dc-title"><i class="bi bi-people" style="color:var(--c-blue)"></i>Rekap Per Siswa</div>
            <a href="{{ route('admin.laporan.per-eskul.export-excel', array_merge(['eskul'=>$eskul->id],array_filter($filter))) }}" class="btn-export green"><i class="bi bi-file-earmark-excel"></i>Excel</a>
        </div>
        <div class="table-responsive">
            <table class="dt">
                <thead><tr><th>#</th><th>Nama Siswa</th><th>NISN</th><th>Kelas</th><th class="text-center">Total</th><th class="text-center">Hadir</th><th class="text-center">Telat</th><th class="text-center">Alpha</th><th class="text-center">Izin</th><th class="text-center">Sakit</th><th style="min-width:130px">% Kehadiran</th></tr></thead>
                <tbody>
                    @forelse($studentSummary as $i => $s)
                    <tr>
                        <td class="text-muted" style="font-size:.75rem">{{ $i+1 }}</td>
                        <td class="fw-semibold" style="color:#0F172A">{{ $s['nama'] }}</td>
                        <td><code style="font-size:.72rem;background:#F8FAFC;padding:1px 5px;border-radius:4px">{{ $s['nisn']??'-' }}</code></td>
                        <td style="color:var(--c-slate);font-size:.78rem">{{ $s['kelas'] }}</td>
                        <td class="text-center fw-semibold">{{ $s['total'] }}</td>
                        <td class="text-center"><span class="pill ph">{{ $s['hadir'] }}</span></td>
                        <td class="text-center"><span class="pill pt">{{ $s['telat'] }}</span></td>
                        <td class="text-center"><span class="pill {{ $s['alpha']>=3?'pa':'pl' }}">{{ $s['alpha'] }}</span></td>
                        <td class="text-center"><span class="pill pi">{{ $s['izin'] }}</span></td>
                        <td class="text-center"><span class="pill ps">{{ $s['sakit'] }}</span></td>
                        <td>
                            @php $p=$s['pct']; $pc=$p>=75?'#16A34A':($p>=50?'#D97706':'#DC2626'); @endphp
                            <div class="d-flex align-items-center gap-2">
                                <div class="pct-bar flex-fill"><div class="pct-fill" style="width:{{ $p }}%;background:{{ $pc }}"></div></div>
                                <span class="fw-semibold" style="font-size:.78rem;color:{{ $pc }};width:36px;text-align:right">{{ $p }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="11" class="text-center py-5 text-muted">Belum ada data siswa.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ════ TREN ════ --}}
    @if($tab === 'tren')
    <div class="chart-grid cg-2">
        <div class="chart-card">
            <div class="cc-title"><i class=" bi bi-graph-up" style="color:var(--c-indigo)"></i>Tren % Kehadiran 6 Bulan</div>
            <div class="chart-wrap"><canvas id="cLine"></canvas></div>
        </div>
        <div class="chart-card">
            <div class="cc-title"><i class="bi bi-bar-chart" style="color:var(--c-amber)"></i>Komposisi Status per Bulan</div>
            <div class="chart-wrap"><canvas id="cStack"></canvas></div>
        </div>
    </div>
    <div class="data-card">
        <div class="dc-head"><div class="dc-title"><i class="bi bi-table"></i>Data Tren Bulanan</div></div>
        <table class="dt">
            <thead><tr><th>Bulan</th><th class="text-center">Kegiatan</th><th class="text-center">Total</th><th class="text-center">Hadir</th><th class="text-center">Alpha</th><th class="text-center">Telat</th><th class="text-center">Izin</th><th class="text-center">Sakit</th><th style="min-width:130px">% Hadir</th></tr></thead>
            <tbody>
                @foreach($monthlyTrend as $m)
                <tr>
                    <td class="fw-semibold">{{ $m['label'] }}</td>
                    <td class="text-center">{{ $m['kegiatan'] }}</td>
                    <td class="text-center fw-semibold">{{ $m['total'] }}</td>
                    <td class="text-center"><span class="pill ph">{{ $m['hadir'] }}</span></td>
                    <td class="text-center"><span class="pill pa">{{ $m['alpha'] }}</span></td>
                    <td class="text-center"><span class="pill pt">{{ $m['telat'] }}</span></td>
                    <td class="text-center"><span class="pill pi">{{ $m['izin'] }}</span></td>
                    <td class="text-center"><span class="pill ps">{{ $m['sakit'] }}</span></td>
                    <td>
                        @php $p=$m['pct']; $pc=$p>=75?'#16A34A':($p>=50?'#D97706':'#DC2626'); @endphp
                        <div class="d-flex align-items-center gap-2">
                            <div class="pct-bar flex-fill"><div class="pct-fill" style="width:{{ $p }}%;background:{{ $pc }}"></div></div>
                            <span class="fw-semibold" style="font-size:.78rem;color:{{ $pc }};width:36px;text-align:right">{{ $p }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- ════ ALPHA ════ --}}
    @if($tab === 'alpha')
    <div class="data-card" style="border-color:{{ count($alphaWarning)?'#FECACA':'#E2E8F0' }}">
        <div class="dc-head" style="{{ count($alphaWarning)?'background:#FEF2F2':'' }}">
            <div class="dc-title" style="color:{{ count($alphaWarning)?'var(--c-red)':'#0F172A' }}">
                <i class="bi bi-exclamation-triangle"></i>
                Siswa Alpha ≥3× <span class="pill pa ms-1">{{ count($alphaWarning) }}</span>
            </div>
        </div>
        @if(count($alphaWarning) > 0)
        <table class="dt">
            <thead><tr><th>#</th><th>Nama Siswa</th><th>NISN</th><th class="text-center">Total Alpha</th><th>Tanggal Alpha</th></tr></thead>
            <tbody>
                @foreach($alphaWarning as $i => $aw)
                <tr>
                    <td class="text-muted" style="font-size:.75rem">{{ $i+1 }}</td>
                    <td class="fw-semibold" style="color:#0F172A">{{ $aw['nama'] }}</td>
                    <td><code style="font-size:.72rem;background:#F8FAFC;padding:1px 5px;border-radius:4px">{{ $aw['nisn']??'-' }}</code></td>
                    <td class="text-center"><span class="pill pa">{{ $aw['total_alpha'] }}×</span></td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($aw['tanggal'] as $tgl)
                            <span style="font-size:.6rem;background:#FEF2F2;color:#991B1B;padding:2px 7px;border-radius:6px;font-weight:600">{{ $tgl }}</span>
                            @endforeach
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="text-center py-5">
            <i class="fas fa-check-circle text-success" style="font-size:2rem;opacity:.5;margin-bottom:.75rem;display:block"></i>
            <div class="fw-semibold text-success">Tidak ada siswa dengan alpha berlebihan. Hebat!</div>
        </div>
        @endif
    </div>
    @endif

</div>

@if($tab === 'tren')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function(){
    const T = @json($monthlyTrend ?? []);
    if(!T.length) return;
    new Chart(document.getElementById('cLine'),{type:'line',data:{labels:T.map(r=>r.label),datasets:[{label:'% Hadir',data:T.map(r=>r.pct),borderColor:'#4F46E5',backgroundColor:'rgba(79,70,229,.08)',fill:true,tension:.4,pointRadius:5,borderWidth:2.5}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{min:0,max:100,ticks:{callback:v=>v+'%'}},x:{grid:{display:false}}}}});
    new Chart(document.getElementById('cStack'),{type:'bar',data:{labels:T.map(r=>r.label),datasets:[{label:'Hadir',data:T.map(r=>r.hadir),backgroundColor:'#16A34A'},{label:'Alpha',data:T.map(r=>r.alpha),backgroundColor:'#DC2626'},{label:'Telat',data:T.map(r=>r.telat),backgroundColor:'#D97706'},{label:'Izin',data:T.map(r=>r.izin),backgroundColor:'#2563EB'},{label:'Sakit',data:T.map(r=>r.sakit),backgroundColor:'#7C3AED'}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'top',labels:{boxWidth:10,font:{size:10}}}},scales:{x:{stacked:true,grid:{display:false}},y:{stacked:true}}}});
})();
</script>
@endif
@endsection