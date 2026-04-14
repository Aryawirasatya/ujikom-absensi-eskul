@extends('layouts.app')

@section('title', 'Laporan — ' . $eskul->name)

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600&display=swap');
:root{--c-indigo:#4F46E5;--c-indigo-lt:#EEF2FF;--c-green:#16A34A;--c-green-lt:#DCFCE7;--c-red:#DC2626;--c-red-lt:#FEE2E2;--c-amber:#D97706;--c-amber-lt:#FEF3C7;--c-blue:#2563EB;--c-blue-lt:#DBEAFE;--c-violet:#7C3AED;--c-violet-lt:#EDE9FE;--c-slate:#64748B;--c-slate-lt:#F1F5F9;}
*{box-sizing:border-box;}
.lap-wrap *{font-family:'Inter',sans-serif;}
h1,.metric-val{font-family:'Sora',sans-serif;}
.lap-wrap{max-width:1200px;margin:0 auto;padding:1.5rem 1rem 5rem;}

.page-header{background:#fff;border-radius:18px;border:1px solid #E2E8F0;padding:1.5rem 1.75rem;margin-bottom:1rem;position:relative;overflow:hidden;}
.page-header::before{content:'';position:absolute;inset:0 0 auto 0;height:3px;background:linear-gradient(90deg,var(--c-indigo),var(--c-green));}
.page-header h1{font-size:1.3rem;font-weight:800;color:#0F172A;margin:0;}
.page-header p{color:var(--c-slate);font-size:.8rem;margin:.25rem 0 0;}

/* Eskul Tab Nav */
.eskul-tabs{display:flex;gap:.3rem;flex-wrap:wrap;background:#F8FAFC;border:1px solid #E2E8F0;border-radius:12px;padding:4px;margin-bottom:1rem;align-items:center;}
.eskul-tabs .et-label{font-size:.58rem;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:#94A3B8;padding:0 .5rem;white-space:nowrap;}
.eskul-tab{display:inline-flex;align-items:center;gap:.3rem;font-size:.7rem;font-weight:600;padding:.35rem .85rem;border-radius:8px;color:var(--c-slate);text-decoration:none;transition:all .15s;white-space:nowrap;}
.eskul-tab.active{background:#fff;color:var(--c-indigo);box-shadow:0 2px 8px rgba(0,0,0,.09);font-weight:700;}
.eskul-tab:hover:not(.active){color:var(--c-indigo);}
.back-link{display:inline-flex;align-items:center;gap:.35rem;font-size:.72rem;font-weight:600;color:var(--c-slate);text-decoration:none;padding:.3rem .75rem;border:1.5px solid #E2E8F0;border-radius:8px;background:#F8FAFC;transition:all .15s;margin-bottom:1rem;}
.back-link:hover{border-color:var(--c-indigo);color:var(--c-indigo);}

/* Tab nav (ringkasan/kegiatan/siswa/tren/alpha) */
.tab-nav{display:flex;gap:.3rem;flex-wrap:wrap;background:#F8FAFC;border:1px solid #E2E8F0;border-radius:14px;padding:4px;margin-bottom:1.25rem;width:fit-content;}
.tab-btn{display:inline-flex;align-items:center;gap:.35rem;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.7px;padding:.44rem 1rem;border-radius:10px;color:var(--c-slate);text-decoration:none;transition:all .15s;white-space:nowrap;}
.tab-btn.active{background:#fff;color:var(--c-indigo);box-shadow:0 2px 8px rgba(0,0,0,.09);}

.filter-bar{background:#fff;border:1px solid #E2E8F0;border-radius:12px;padding:.8rem 1.1rem;margin-bottom:1.25rem;display:flex;flex-wrap:wrap;gap:.6rem;align-items:center;}
.filter-bar .fl{display:flex;align-items:center;gap:.45rem;}
.filter-bar label{font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:#94A3B8;white-space:nowrap;}
.filter-bar input{border:1.5px solid #E2E8F0;border-radius:7px;padding:.35rem .65rem;font-size:.78rem;color:#1E293B;background:#F8FAFC;outline:none;font-family:'Inter',sans-serif;}
.filter-bar input:focus{border-color:var(--c-indigo);}
.btn-filter{font-size:.7rem;font-weight:700;color:#fff;background:var(--c-indigo);border:none;border-radius:7px;padding:.4rem .9rem;cursor:pointer;}
.btn-reset{font-size:.7rem;font-weight:700;color:var(--c-slate);border:1.5px solid #E2E8F0;background:#F8FAFC;border-radius:7px;padding:.38rem .8rem;text-decoration:none;cursor:pointer;}
.btn-export{font-size:.68rem;font-weight:700;display:inline-flex;align-items:center;gap:.3rem;padding:.38rem .8rem;border-radius:7px;border:1.5px solid #E2E8F0;background:#F8FAFC;color:var(--c-slate);text-decoration:none;transition:all .15s;}
.btn-export.green{border-color:#BBF7D0;background:#F0FDF4;color:var(--c-green);}
.btn-export.red{border-color:#FECACA;background:#FEF2F2;color:var(--c-red);}

.kpi-row{display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem;margin-bottom:1.25rem;}
.kpi-card{border-radius:14px;padding:1.2rem 1.4rem;position:relative;overflow:hidden;color:#fff;}
.kpi-card .blob{position:absolute;width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,.1);top:-20px;right:-25px;}
.kpi-card .kl{font-size:.58rem;font-weight:700;text-transform:uppercase;letter-spacing:1.6px;opacity:.75;margin-bottom:.3rem;}
.kpi-card .kv{font-family:'Sora',sans-serif;font-size:2rem;font-weight:800;line-height:1;letter-spacing:-.04em;}
.kpi-card .ks{font-size:.7rem;opacity:.75;margin-top:.4rem;}

.data-card{background:#fff;border:1px solid #E2E8F0;border-radius:14px;overflow:hidden;margin-bottom:1.25rem;}
.dc-head{display:flex;align-items:center;justify-content:space-between;padding:.9rem 1.2rem;border-bottom:1px solid #F1F5F9;flex-wrap:wrap;gap:.5rem;}
.dc-title{font-size:.8rem;font-weight:700;color:#0F172A;display:flex;align-items:center;gap:.4rem;}
table.dt{width:100%;border-collapse:collapse;}
table.dt th{font-size:.54rem;font-weight:800;text-transform:uppercase;letter-spacing:1.4px;color:#94A3B8;padding:.65rem .9rem;background:#F8FAFC;border-bottom:1px solid #F1F5F9;white-space:nowrap;text-align:left;}
table.dt td{padding:.7rem .9rem;border-bottom:1px solid #F8FAFC;font-size:.78rem;vertical-align:middle;}
table.dt tr:last-child td{border-bottom:none;}
table.dt tr:hover td{background:#FAFBFF;}
.pill{display:inline-flex;align-items:center;font-size:.56rem;font-weight:800;text-transform:uppercase;letter-spacing:.5px;padding:.2rem .55rem;border-radius:99px;white-space:nowrap;}
.ph{background:var(--c-green-lt);color:var(--c-green);}
.pa{background:var(--c-red-lt);color:var(--c-red);}
.pt{background:var(--c-amber-lt);color:var(--c-amber);}
.pi{background:var(--c-blue-lt);color:var(--c-blue);}
.ps{background:var(--c-violet-lt);color:var(--c-violet);}
.pl{background:var(--c-slate-lt);color:var(--c-slate);}
.pct-bar{height:5px;background:#F1F5F9;border-radius:99px;overflow:hidden;min-width:50px;}
.pct-fill{height:100%;border-radius:99px;}
.detail-link{font-size:.66rem;font-weight:700;color:var(--c-indigo);text-decoration:none;display:inline-flex;align-items:center;gap:.3rem;padding:.28rem .65rem;border:1.5px solid var(--c-indigo-lt);border-radius:7px;transition:all .15s;}
.detail-link:hover{background:var(--c-indigo-lt);}
.chart-grid{display:grid;gap:1rem;margin-bottom:1.25rem;}
.cg-2{grid-template-columns:1fr 1fr;}
.chart-card{background:#fff;border:1px solid #E2E8F0;border-radius:14px;padding:1.1rem 1.25rem;}
.cc-title{font-size:.78rem;font-weight:700;color:#0F172A;display:flex;align-items:center;gap:.4rem;margin-bottom:.9rem;}
.chart-wrap{position:relative;height:210px;}
@media(max-width:768px){.kpi-row{grid-template-columns:1fr 1fr;}.cg-2{grid-template-columns:1fr;}}
</style>

<div class="lap-wrap">

    {{-- Back Link --}}
    <a href="{{ route('pembina.laporan.index') }}" class="back-link">
        <i class="bi bi-arrow-left"></i> Semua Eskul
    </a>

    {{-- Eskul Tab Nav --}}
    @if($myEskuls->count() > 1)
    <div class="eskul-tabs">
        <span class="et-label"><i class="bi bi-trophy"></i> Eskul:</span>
        @foreach($myEskuls as $e)
        <a href="{{ route('pembina.laporan.show', $e->id) }}"
           class="eskul-tab {{ $eskul->id == $e->id ? 'active' : '' }}">
            {{ $e->name }}
        </a>
        @endforeach
    </div>
    @endif

    {{-- Header --}}
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h1><i class="bi bi-bar-chart me-2" style="color:var(--c-indigo)"></i>Laporan Kehadiran</h1>
                <p><strong>{{ $eskul->name }}</strong> &nbsp;·&nbsp; {{ $schoolYear->name }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('pembina.laporan.export-excel', array_merge(['eskul' => $eskul->id], request()->query())) }}" class="btn-export green">
                    <i class="bi bi-file-earmark-excel"></i> Excel
                </a>
                <a href="{{ route('pembina.laporan.export-pdf', array_merge(['eskul' => $eskul->id], request()->query())) }}" class="btn-export red">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                </a>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('pembina.laporan.show', $eskul) }}">
        <div class="filter-bar">
            <div class="fl"><label>Dari</label><input type="date" name="date_from" value="{{ $filter['date_from']??'' }}"></div>
            <div class="fl"><label>Sampai</label><input type="date" name="date_to" value="{{ $filter['date_to']??'' }}"></div>
            <input type="hidden" name="tab" value="{{ $tab }}">
            <button type="submit" class="btn-filter"><i class="bi bi-funnel me-1"></i>Filter</button>
            @if(!empty(array_filter($filter)))
            <a href="{{ route('pembina.laporan.show', $eskul) }}" class="btn-reset">Reset</a>
            @endif
        </div>
    </form>

    {{-- Tab Navigation --}}
    <div class="tab-nav">
        @foreach([
            'ringkasan' => ['bi bi-pie-chart',  'Ringkasan'],
            'kegiatan'  => ['bi bi-calendar-check','Per Kegiatan'],
            'siswa'     => ['bi bi-people',       'Per Siswa'],
            'tren'      => [' bi bi-graph-up',  'Tren'],
            'alpha'     => ['bi bi-bell',        'Alpha'],
        ] as $k => [$icon, $lbl])
        <a href="{{ route('pembina.laporan.show', array_merge(['eskul' => $eskul->id, 'tab' => $k], array_filter($filter))) }}"
           class="tab-btn {{ $tab===$k?'active':'' }}">
            <i class="{{ $icon }}"></i>{{ $lbl }}
        </a>
        @endforeach
    </div>

    {{-- KPI --}}
    <div class="kpi-row">
        <div class="kpi-card" style="background:linear-gradient(135deg,#4F46E5,#4338CA)">
            <div class="blob"></div>
            <div class="kl">Total Kegiatan</div>
            <div class="kv">{{ $totalKegiatan }}</div>
            <div class="ks">Kegiatan selesai</div>
        </div>
        <div class="kpi-card" style="background:linear-gradient(135deg,#16A34A,#15803D)">
            <div class="blob"></div>
            <div class="kl">Rate Kehadiran</div>
            <div class="kv">{{ $pctKeseluruhan }}%</div>
            <div class="ks">{{ $hadirTotal }} dari {{ $totalAbsensi }}</div>
        </div>
        <div class="kpi-card" style="background:linear-gradient(135deg,#DC2626,#B91C1C)">
            <div class="blob"></div>
            <div class="kl">Siswa Alpha ≥3×</div>
            <div class="kv">{{ count($alphaWarning) }}</div>
            <div class="ks">Perlu perhatian</div>
        </div>
        <div class="kpi-card" style="background:linear-gradient(135deg,#D97706,#B45309)">
            <div class="blob"></div>
            <div class="kl">Total Anggota</div>
            <div class="kv">{{ collect($studentSummary)->count() }}</div>
            <div class="ks">Anggota aktif</div>
        </div>
    </div>

    {{-- ════ RINGKASAN ════ --}}
    @if($tab === 'ringkasan')
    <div class="chart-grid cg-2">
        <div class="chart-card">
            <div class="cc-title"><i class=" bi bi-graph-up" style="color:var(--c-indigo)"></i>Tren Kehadiran 6 Bulan</div>
            <div class="chart-wrap"><canvas id="cTrend"></canvas></div>
        </div>
        <div class="chart-card">
            <div class="cc-title"><i class="bi bi-pie-chart" style="color:var(--c-blue)"></i>Distribusi Status</div>
            <div class="chart-wrap"><canvas id="cDist"></canvas></div>
        </div>
    </div>
    <div class="data-card">
        <div class="dc-head"><div class="dc-title"><i class="bi bi-table" style="color:var(--c-indigo)"></i>Ringkasan Bulanan</div></div>
        <table class="dt">
            <thead><tr><th>Bulan</th><th class="text-center">Kegiatan</th><th class="text-center">Total</th><th class="text-center">Hadir</th><th class="text-center">Alpha</th><th class="text-center">Telat</th><th style="min-width:120px">% Hadir</th></tr></thead>
            <tbody>
                @foreach($monthlyTrend as $m)
                <tr>
                    <td class="fw-semibold">{{ $m['label'] }}</td>
                    <td class="text-center">{{ $m['kegiatan'] }}</td>
                    <td class="text-center fw-semibold">{{ $m['total'] }}</td>
                    <td class="text-center"><span class="pill ph">{{ $m['hadir'] }}</span></td>
                    <td class="text-center"><span class="pill pa">{{ $m['alpha'] }}</span></td>
                    <td class="text-center"><span class="pill pt">{{ $m['telat'] }}</span></td>
                    <td>
                        @php $p=$m['pct'];$pc=$p>=75?'#16A34A':($p>=50?'#D97706':'#DC2626'); @endphp
                        <div class="d-flex align-items-center gap-2">
                            <div class="pct-bar flex-fill"><div class="pct-fill" style="width:{{ $p }}%;background:{{ $pc }}"></div></div>
                            <span class="fw-semibold" style="font-size:.76rem;color:{{ $pc }};width:34px;text-align:right">{{ $p }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- ════ PER KEGIATAN ════ --}}
    @if($tab === 'kegiatan')
    <div class="data-card">
        <div class="dc-head">
            <div class="dc-title"><i class="bi bi-calendar-check" style="color:var(--c-indigo)"></i>Rekap Per Kegiatan</div>
            <a href="{{ route('pembina.laporan.export-excel', ['eskul' => $eskul->id, 'tab' => $tab]) }}" class="btn-export green">
                <i class="bi bi-file-earmark-excel"></i> Excel
            </a>
        </div>
        <div class="table-responsive">
            <table class="dt">
                <thead><tr><th>#</th><th>Tanggal</th><th>Judul</th><th class="text-center">Tipe</th><th class="text-center">Total</th><th class="text-center">Hadir</th><th class="text-center">Telat</th><th class="text-center">Alpha</th><th class="text-center">Izin</th><th class="text-center">Sakit</th><th style="min-width:120px">% Hadir</th></tr></thead>
                <tbody>
                    @forelse($activitySummary as $i => $act)
                    <tr>
                        <td class="text-muted" style="font-size:.72rem">{{ $i+1 }}</td>
                        <td style="font-size:.76rem;font-weight:600;color:#0F172A">{{ is_object($act['tanggal'])?$act['tanggal']->format('d M Y'):$act['tanggal'] }}</td>
                        <td class="fw-semibold" style="color:#0F172A">{{ $act['judul'] }}</td>
                        <td class="text-center"><span class="pill {{ $act['tipe']==='routine'?'ph':'pi' }}">{{ $act['tipe']==='routine'?'Rutin':'Non-Rutin' }}</span></td>
                        <td class="text-center fw-semibold">{{ $act['total'] }}</td>
                        <td class="text-center"><span class="pill ph">{{ $act['hadir'] }}</span></td>
                        <td class="text-center"><span class="pill pt">{{ $act['telat'] }}</span></td>
                        <td class="text-center"><span class="pill pa">{{ $act['alpha'] }}</span></td>
                        <td class="text-center"><span class="pill pi">{{ $act['izin'] }}</span></td>
                        <td class="text-center"><span class="pill ps">{{ $act['sakit'] }}</span></td>
                        <td>
                            @php $p=$act['pct'];$pc=$p>=75?'#16A34A':($p>=50?'#D97706':'#DC2626'); @endphp
                            <div class="d-flex align-items-center gap-2">
                                <div class="pct-bar flex-fill"><div class="pct-fill" style="width:{{ $p }}%;background:{{ $pc }}"></div></div>
                                <span class="fw-semibold" style="font-size:.76rem;color:{{ $pc }};width:34px;text-align:right">{{ $p }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="11" class="text-center py-5 text-muted">Belum ada kegiatan selesai.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ════ PER SISWA ════ --}}
    @if($tab === 'siswa')
    <div class="data-card">
        <div class="dc-head">
            <div class="dc-title"><i class="bi bi-people" style="color:var(--c-blue)"></i>Rekap Per Siswa</div>
        </div>
        <div class="table-responsive">
            <table class="dt">
                <thead><tr><th>#</th><th>Nama Siswa</th><th>Kelas</th><th class="text-center">Total</th><th class="text-center">Hadir</th><th class="text-center">Telat</th><th class="text-center">Alpha</th><th class="text-center">Izin</th><th class="text-center">Sakit</th><th style="min-width:120px">% Kehadiran</th><th></th></tr></thead>
                <tbody>
                    @forelse($studentSummary as $i => $s)
                    <tr class="{{ $s['alpha']>=3?'table-danger':'' }}" style="{{ $s['alpha']>=3?'--bs-table-bg:#FEF2F2':'' }}">
                        <td class="text-muted" style="font-size:.72rem">{{ $i+1 }}</td>
                        <td>
                            <div class="fw-semibold" style="color:#0F172A">{{ $s['nama'] }}</div>
                            <div style="font-size:.66rem;color:#94A3B8;font-family:monospace">{{ $s['nisn']??'' }}</div>
                        </td>
                        <td style="font-size:.76rem;color:var(--c-slate)">{{ $s['kelas']??'-' }}</td>
                        <td class="text-center fw-semibold">{{ $s['total'] }}</td>
                        <td class="text-center"><span class="pill ph">{{ $s['hadir'] }}</span></td>
                        <td class="text-center">@if($s['telat'])<span class="pill pt">{{ $s['telat'] }}</span>@else<span style="color:#CBD5E1">–</span>@endif</td>
                        <td class="text-center">@if($s['alpha'])<span class="pill {{ $s['alpha']>=3?'pa':'pl' }}">{{ $s['alpha'] }}</span>@else<span style="color:#CBD5E1">–</span>@endif</td>
                        <td class="text-center">@if($s['izin'])<span class="pill pi">{{ $s['izin'] }}</span>@else<span style="color:#CBD5E1">–</span>@endif</td>
                        <td class="text-center">@if($s['sakit'])<span class="pill ps">{{ $s['sakit'] }}</span>@else<span style="color:#CBD5E1">–</span>@endif</td>
                        <td>
                            @php $p=$s['pct'];$pc=$p>=75?'#16A34A':($p>=50?'#D97706':'#DC2626'); @endphp
                            <div class="d-flex align-items-center gap-2">
                                <div class="pct-bar flex-fill"><div class="pct-fill" style="width:{{ $p }}%;background:{{ $pc }}"></div></div>
                                <span class="fw-semibold" style="font-size:.76rem;color:{{ $pc }};width:34px;text-align:right">{{ $p }}%</span>
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('pembina.laporan.detail-siswa', [$eskul->id, $s['user_id']]) }}" class="detail-link">
                                Detail <i class="bi bi-arrow-right" style="font-size:.55rem"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="11" class="text-center py-5 text-muted">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ════ TREN ════ --}}
    @if($tab === 'tren')
    <div class="chart-grid cg-2">
        <div class="chart-card"><div class="cc-title"><i class=" bi bi-graph-up" style="color:var(--c-indigo)"></i>Tren % Kehadiran</div><div class="chart-wrap"><canvas id="cLine"></canvas></div></div>
        <div class="chart-card"><div class="cc-title"><i class="bi bi-bar-chart" style="color:var(--c-amber)"></i>Komposisi per Bulan</div><div class="chart-wrap"><canvas id="cStack"></canvas></div></div>
    </div>
    @endif

    {{-- ════ ALPHA ════ --}}
    @if($tab === 'alpha')
    <div class="data-card" style="border-color:{{ count($alphaWarning)?'#FECACA':'#E2E8F0' }}">
        <div class="dc-head" style="{{ count($alphaWarning)?'background:#FEF2F2':'' }}">
            <div class="dc-title" style="color:{{ count($alphaWarning)?'var(--c-red)':'#0F172A' }}">
                <i class="bi bi-bell"></i>Siswa Alpha ≥3×
                <span class="pill pa ms-1">{{ count($alphaWarning) }}</span>
            </div>
        </div>
        @if(count($alphaWarning) > 0)
        <table class="dt">
            <thead><tr><th>#</th><th>Nama</th><th>NISN</th><th class="text-center">Total Alpha</th><th>Tanggal Alpha</th><th></th></tr></thead>
            <tbody>
                @foreach($alphaWarning as $i => $a)
                <tr>
                    <td class="text-muted" style="font-size:.72rem">{{ $i+1 }}</td>
                    <td class="fw-semibold" style="color:#0F172A">{{ $a['nama'] }}</td>
                    <td style="font-family:monospace;font-size:.72rem;color:#94A3B8">{{ $a['nisn']??'-' }}</td>
                    <td class="text-center"><span class="pill pa">{{ $a['total_alpha'] }}×</span></td>
                    <td>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($a['tanggal'] as $tgl)
                            <span style="font-size:.6rem;background:#FEF2F2;color:#991B1B;padding:2px 7px;border-radius:6px;font-weight:600">{{ $tgl }}</span>
                            @endforeach
                        </div>
                    </td>
                    <td>
                        <a href="{{ route('pembina.laporan.detail-siswa', [$eskul->id, $a['user_id']]) }}" class="detail-link">
                            Detail <i class="bi bi-arrow-right" style="font-size:.55rem"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="text-center py-5">
            <i class="fas fa-check-circle text-success" style="font-size:2rem;opacity:.5;display:block;margin-bottom:.75rem"></i>
            <div class="fw-semibold text-success">Tidak ada siswa dengan alpha berlebihan!</div>
        </div>
        @endif
    </div>
    @endif

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
@if($tab === 'ringkasan')
(function(){
    const T = @json($monthlyTrend ?? []);
    if(!T.length) return;
    const GS = { hadir:{{ collect($activitySummary)->sum('hadir') }}, alpha:{{ collect($activitySummary)->sum('alpha') }}, telat:{{ collect($activitySummary)->sum('telat') }}, izin:{{ collect($activitySummary)->sum('izin') }}, sakit:{{ collect($activitySummary)->sum('sakit') }} };
    new Chart(document.getElementById('cTrend'),{type:'line',data:{labels:T.map(r=>r.label),datasets:[{label:'% Hadir',data:T.map(r=>r.pct),borderColor:'#4F46E5',backgroundColor:'rgba(79,70,229,.08)',fill:true,tension:.4,pointRadius:4,borderWidth:2.5}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{min:0,max:100,ticks:{callback:v=>v+'%'}},x:{grid:{display:false}}}}});
    new Chart(document.getElementById('cDist'),{type:'doughnut',data:{labels:['Hadir','Alpha','Telat','Izin','Sakit'],datasets:[{data:[GS.hadir,GS.alpha,GS.telat,GS.izin,GS.sakit],backgroundColor:['#16A34A','#DC2626','#D97706','#2563EB','#7C3AED'],borderWidth:2,borderColor:'#fff'}]},options:{responsive:true,maintainAspectRatio:false,cutout:'60%',plugins:{legend:{position:'right',labels:{boxWidth:10,font:{size:10}}}}}});
})();
@endif
@if($tab === 'tren')
(function(){
    const T = @json($monthlyTrend ?? []);
    if(!T.length) return;
    new Chart(document.getElementById('cLine'),{type:'line',data:{labels:T.map(r=>r.label),datasets:[{label:'% Hadir',data:T.map(r=>r.pct),borderColor:'#4F46E5',backgroundColor:'rgba(79,70,229,.08)',fill:true,tension:.4,pointRadius:4,borderWidth:2.5}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{min:0,max:100,ticks:{callback:v=>v+'%'}},x:{grid:{display:false}}}}});
    new Chart(document.getElementById('cStack'),{type:'bar',data:{labels:T.map(r=>r.label),datasets:[{label:'Hadir',data:T.map(r=>r.hadir),backgroundColor:'#16A34A'},{label:'Alpha',data:T.map(r=>r.alpha),backgroundColor:'#DC2626'},{label:'Telat',data:T.map(r=>r.telat),backgroundColor:'#D97706'},{label:'Izin',data:T.map(r=>r.izin),backgroundColor:'#2563EB'},{label:'Sakit',data:T.map(r=>r.sakit),backgroundColor:'#7C3AED'}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'top',labels:{boxWidth:10,font:{size:10}}}},scales:{x:{stacked:true,grid:{display:false}},y:{stacked:true}}}});
})();
@endif
</script>
@endsection