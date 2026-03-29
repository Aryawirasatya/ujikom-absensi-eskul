@extends('layouts.app')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600&display=swap');
:root{--c-indigo:#4F46E5;--c-indigo-lt:#EEF2FF;--c-green:#16A34A;--c-green-lt:#DCFCE7;--c-red:#DC2626;--c-red-lt:#FEE2E2;--c-amber:#D97706;--c-amber-lt:#FEF3C7;--c-blue:#2563EB;--c-blue-lt:#DBEAFE;--c-violet:#7C3AED;--c-violet-lt:#EDE9FE;--c-slate:#64748B;--c-slate-lt:#F1F5F9;}
*{box-sizing:border-box;}
body,.lap-wrap *{font-family:'Inter',sans-serif;}
h1,h2,.metric-val{font-family:'Sora',sans-serif;}
.lap-wrap{max-width:1440px;margin:0 auto;padding:2rem 1.5rem 6rem;}
.page-header{background:#fff;border-radius:20px;border:1px solid #E2E8F0;padding:1.75rem 2rem;margin-bottom:1.5rem;position:relative;overflow:hidden;}
.page-header::before{content:'';position:absolute;inset:0 0 auto 0;height:3px;background:linear-gradient(90deg,var(--c-indigo),var(--c-violet));}
.page-header h1{font-size:1.4rem;font-weight:800;color:#0F172A;margin:0;}
.page-header p{color:var(--c-slate);font-size:.82rem;margin:.3rem 0 0;}
.top-tabs{display:flex;gap:.35rem;flex-wrap:wrap;background:#F8FAFC;border:1px solid #E2E8F0;border-radius:16px;padding:5px;margin-bottom:1.5rem;width:fit-content;}
.top-tabs a{display:inline-flex;align-items:center;gap:.4rem;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.7px;padding:.5rem 1.1rem;border-radius:11px;color:var(--c-slate);text-decoration:none;transition:all .15s;white-space:nowrap;}
.top-tabs a.active{background:#fff;color:var(--c-indigo);box-shadow:0 2px 8px rgba(0,0,0,.09);}
.top-tabs a .badge-count{font-size:.6rem;background:var(--c-indigo-lt);color:var(--c-indigo);border-radius:99px;padding:1px 7px;font-weight:800;}
.eskul-nav{display:flex;gap:.35rem;flex-wrap:wrap;margin-bottom:1.5rem;align-items:center;}
.eskul-nav .en-label{font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:#94A3B8;margin-right:.25rem;}
.eskul-nav a{font-size:.7rem;font-weight:600;padding:.35rem .85rem;border-radius:99px;border:1.5px solid #E2E8F0;color:var(--c-slate);text-decoration:none;background:#fff;transition:all .15s;}
.eskul-nav a:hover{border-color:var(--c-indigo);color:var(--c-indigo);}
.filter-bar{background:#fff;border:1px solid #E2E8F0;border-radius:14px;padding:.9rem 1.25rem;margin-bottom:1.5rem;display:flex;flex-wrap:wrap;gap:.75rem;align-items:center;}
.filter-bar .fl{display:flex;align-items:center;gap:.5rem;}
.filter-bar label{font-size:.62rem;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:#94A3B8;white-space:nowrap;}
.filter-bar input,.filter-bar select{border:1.5px solid #E2E8F0;border-radius:8px;padding:.38rem .7rem;font-size:.8rem;color:#1E293B;background:#F8FAFC;outline:none;font-family:'Inter',sans-serif;transition:border .15s;}
.filter-bar input:focus,.filter-bar select:focus{border-color:var(--c-indigo);}
.filter-sep{width:1px;height:24px;background:#E2E8F0;}
.btn-filter{font-size:.72rem;font-weight:700;color:#fff;background:var(--c-indigo);border:none;border-radius:8px;padding:.42rem 1rem;cursor:pointer;}
.btn-reset{font-size:.72rem;font-weight:700;color:var(--c-slate);border:1.5px solid #E2E8F0;background:#F8FAFC;border-radius:8px;padding:.4rem .9rem;cursor:pointer;text-decoration:none;}
.btn-export{font-size:.7rem;font-weight:700;display:inline-flex;align-items:center;gap:.35rem;padding:.4rem .9rem;border-radius:8px;border:1.5px solid #E2E8F0;background:#F8FAFC;color:var(--c-slate);text-decoration:none;cursor:pointer;transition:all .15s;white-space:nowrap;}
.btn-export.green{border-color:#BBF7D0;background:#F0FDF4;color:var(--c-green);}
.btn-export.red{border-color:#FECACA;background:#FEF2F2;color:var(--c-red);}

/* KPI */
.kpi-row{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem;}
.kpi-mini{background:#fff;border:1px solid #E2E8F0;border-radius:14px;padding:1.1rem 1.2rem;position:relative;overflow:hidden;}
.kpi-mini .km-stripe{position:absolute;left:0;top:0;bottom:0;width:3px;border-radius:14px 0 0 14px;}
.kpi-mini .km-val{font-family:'Sora',sans-serif;font-size:1.8rem;font-weight:800;line-height:1;}
.kpi-mini .km-lbl{font-size:.58rem;font-weight:700;text-transform:uppercase;letter-spacing:1.3px;color:#94A3B8;margin-top:.2rem;}

/* Search inline */
.search-wrap{position:relative;}
.search-wrap input{padding-left:2rem !important;}
.search-wrap .si{position:absolute;left:.6rem;top:50%;transform:translateY(-50%);color:#94A3B8;font-size:.75rem;pointer-events:none;}

/* Table */
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
.avatar-circle{width:32px;height:32px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:.7rem;font-weight:800;flex-shrink:0;}
.detail-link{font-size:.68rem;font-weight:700;color:var(--c-indigo);text-decoration:none;display:inline-flex;align-items:center;gap:.3rem;padding:.3rem .7rem;border:1.5px solid var(--c-indigo-lt);border-radius:8px;transition:all .15s;}
.detail-link:hover{background:var(--c-indigo-lt);}
.empty-state{padding:3rem;text-align:center;color:#94A3B8;}
.empty-state i{font-size:2.5rem;margin-bottom:.75rem;opacity:.4;}
@media(max-width:900px){.kpi-row{grid-template-columns:1fr 1fr;}}
</style>

<div class="container-fluid lap-wrap">

    {{-- Page Header --}}
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h1><i class="bi bi-people me-2" style="color:var(--c-violet)"></i>Laporan Per Siswa</h1>
                <p>Tahun Ajaran <strong>{{ $schoolYear->name }}</strong> &nbsp;·&nbsp; Rekap kehadiran lintas ekstrakurikuler per siswa</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.laporan.per-siswa.export-excel', array_filter($filter)) }}" class="btn-export green">
                    <i class="bi bi-file-earmark-excel"></i> Excel
                </a>
                <a href="{{ route('admin.laporan.per-siswa.export-pdf', array_filter($filter)) }}" class="btn-export red">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                </a>
            </div>
        </div>
    </div>

    {{-- Top Navigation --}}
    <div class="top-tabs">
        <a href="{{ route('admin.laporan.index', array_filter($filter)) }}">
            <i class="fas fa-globe"></i> Global
        </a>
        <a href="{{ route('admin.laporan.per-pembina', array_filter($filter)) }}">
            <i class="bi bi-person-workspace"></i> Per Pembina
        </a>
        <a href="{{ route('admin.laporan.per-siswa', array_filter($filter)) }}" class="active">
            <i class="bi bi-people"></i> Per Siswa
        </a>
        <a href="#" onclick="document.getElementById('eskulNavRow').classList.toggle('d-none');return false;">
            <i class="bi bi-trophy"></i> Per Eskul
            <span class="badge-count">{{ $eskuls->count() }}</span>
        </a>
    </div>

    {{-- Eskul Sub-nav --}}
    <div id="eskulNavRow" class="eskul-nav d-none">
        <span class="en-label">Pilih Eskul:</span>
        @foreach($eskuls as $e)
        <a href="{{ route('admin.laporan.per-eskul', array_merge(['eskul'=>$e->id], array_filter($filter))) }}">{{ $e->name }}</a>
        @endforeach
    </div>

    {{-- Filter --}}
    <form action="{{ route('admin.laporan.per-siswa') }}" method="GET">
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
            <div class="filter-sep"></div>
            <div class="fl">
                <label>Kelas</label>
                <select name="grade">
                    <option value="">Semua</option>
                    @foreach($grades as $g)
                    <option value="{{ $g }}" {{ ($filter['grade']??'')===$g?'selected':'' }}>{{ $g }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-sep"></div>
            <div class="fl search-wrap">
                <i class="bi bi-search si"></i>
                <input type="text" name="search" placeholder="Cari nama / NISN..." value="{{ $filter['search']??'' }}" style="width:180px">
            </div>
            <button type="submit" class="btn-filter"><i class="bi bi-funnel me-1"></i>Filter</button>
            <a href="{{ route('admin.laporan.per-siswa') }}" class="btn-reset">Reset</a>
        </div>
    </form>

    {{-- KPI Mini --}}
    @php
        $totalSiswa = count($studentReport);
        $avgPct     = $totalSiswa > 0 ? round(collect($studentReport)->avg('pct'), 1) : 0;
        $topSiswa   = collect($studentReport)->where('pct', '>=', 75)->count();
        $atRisk     = collect($studentReport)->where('alpha', '>=', 3)->count();
    @endphp
    <div class="kpi-row">
        <div class="kpi-mini">
            <div class="km-stripe" style="background:var(--c-violet)"></div>
            <div class="km-val" style="color:var(--c-violet)">{{ $totalSiswa }}</div>
            <div class="km-lbl">Total Siswa</div>
        </div>
        <div class="kpi-mini">
            <div class="km-stripe" style="background:var(--c-green)"></div>
            <div class="km-val" style="color:var(--c-green)">{{ $avgPct }}%</div>   
            <div class="km-lbl">Rata-rata Kehadiran</div>
        </div>
        <div class="kpi-mini">
            <div class="km-stripe" style="background:var(--c-blue)"></div>
            <div class="km-val" style="color:var(--c-blue)">{{ $topSiswa }}</div>
            <div class="km-lbl">Siswa Hadir ≥75%</div>
        </div>
        <div class="kpi-mini">
            <div class="km-stripe" style="background:var(--c-red)"></div>
            <div class="km-val" style="color:var(--c-red)">{{ $atRisk }}</div>
            <div class="km-lbl">Siswa Alpha ≥3x</div>
        </div>
    </div>

    {{-- Tabel Siswa --}}
    <div class="data-card">
        <div class="dc-head">
            <div class="dc-title">
                <i class="bi bi-table" style="color:var(--c-violet)"></i>
                Rekap Kehadiran Per Siswa
                <span class="pill ps ms-1">{{ $totalSiswa }} siswa</span>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.laporan.per-siswa.export-excel', array_filter($filter)) }}" class="btn-export green"><i class="bi bi-file-earmark-excel"></i>Excel</a>
                <a href="{{ route('admin.laporan.per-siswa.export-pdf', array_filter($filter)) }}" class="btn-export red"><i class="bi bi-file-earmark-pdf"></i>PDF</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="dt">
                <thead>
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Hadir</th>
                        <th class="text-center">Telat</th>
                        <th class="text-center">Alpha</th>
                        <th class="text-center">Izin</th>
                        <th class="text-center">Sakit</th>
                        <th style="min-width:140px">% Kehadiran</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($studentReport as $i => $s)
                    @php
                        $status = $s['pct'] >= 75 ? ['label'=>'Baik','cls'=>'ph'] : ($s['pct'] >= 50 ? ['label'=>'Cukup','cls'=>'pt'] : ['label'=>'Rendah','cls'=>'pa']);
                    @endphp
                    <tr>
                        <td class="text-muted fw-semibold" style="font-size:.75rem">{{ $i+1 }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @php
                                    $colors = ['EEF2FF','FEF3C7','DCFCE7','FEE2E2','EDE9FE'];
                                    $tcolors= ['4F46E5','D97706','16A34A','DC2626','7C3AED'];
                                    $ci = $i % 5;
                                @endphp
                                <div class="avatar-circle" style="background:#{{ $colors[$ci] }};color:#{{ $tcolors[$ci] }}">
                                    {{ strtoupper(substr($s['nama'],0,1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold" style="color:#0F172A;font-size:.82rem">{{ $s['nama'] }}</div>
                                    <div style="font-size:.68rem;color:#94A3B8;font-family:monospace">{{ $s['nisn']??'' }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="color:var(--c-slate);font-size:.78rem">{{ $s['kelas']??'-' }}</td>
                        <td class="text-center fw-semibold" style="color:#0F172A">{{ $s['total'] }}</td>
                        <td class="text-center"><span class="pill ph">{{ $s['hadir'] }}</span></td>
                        <td class="text-center">
                            @if($s['telat'])<span class="pill pt">{{ $s['telat'] }}</span>
                            @else<span style="color:#CBD5E1;font-size:.75rem">–</span>@endif
                        </td>
                        <td class="text-center">
                            @if($s['alpha']>=3)<span class="pill pa">{{ $s['alpha'] }}</span>
                            @elseif($s['alpha'])<span class="pill pl">{{ $s['alpha'] }}</span>
                            @else<span style="color:#CBD5E1;font-size:.75rem">–</span>@endif
                        </td>
                        <td class="text-center">
                            @if($s['izin'])<span class="pill pi">{{ $s['izin'] }}</span>
                            @else<span style="color:#CBD5E1;font-size:.75rem">–</span>@endif
                        </td>
                        <td class="text-center">
                            @if($s['sakit'])<span class="pill ps">{{ $s['sakit'] }}</span>
                            @else<span style="color:#CBD5E1;font-size:.75rem">–</span>@endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="pct-bar flex-fill">
                                    <div class="pct-fill" style="width:{{ $s['pct'] }}%;background:{{ $s['pct']>=75?'#16A34A':($s['pct']>=50?'#D97706':'#DC2626') }}"></div>
                                </div>
                                <span class="fw-semibold" style="font-size:.78rem;width:36px;text-align:right;color:{{ $s['pct']>=75?'#16A34A':($s['pct']>=50?'#D97706':'#DC2626') }}">
                                    {{ $s['pct'] }}%
                                </span>
                            </div>
                        </td>
                        <td class="text-center"><span class="pill {{ $status['cls'] }}">{{ $status['label'] }}</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11">
                            <div class="empty-state">
                                <div><i class="bi bi-people"></i></div>
                                <div class="fw-semibold" style="color:#64748B">Tidak ada data siswa ditemukan.</div>
                                <div style="font-size:.78rem;margin-top:.3rem">Coba ubah filter pencarian.</div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if(count($studentReport) > 0)
                <tfoot>
                    <tr style="background:#F8FAFC;border-top:2px solid #E2E8F0">
                        <td colspan="3" class="fw-bold ps-3" style="font-size:.75rem;color:#0F172A;padding:.75rem 1rem">TOTAL / RATA-RATA</td>
                        <td class="text-center fw-bold" style="font-size:.8rem">{{ collect($studentReport)->sum('total') }}</td>
                        <td class="text-center fw-bold" style="font-size:.8rem;color:var(--c-green)">{{ collect($studentReport)->sum('hadir') }}</td>
                        <td class="text-center fw-bold" style="font-size:.8rem;color:var(--c-amber)">{{ collect($studentReport)->sum('telat') }}</td>
                        <td class="text-center fw-bold" style="font-size:.8rem;color:var(--c-red)">{{ collect($studentReport)->sum('alpha') }}</td>
                        <td class="text-center fw-bold" style="font-size:.8rem;color:var(--c-blue)">{{ collect($studentReport)->sum('izin') }}</td>
                        <td class="text-center fw-bold" style="font-size:.8rem;color:var(--c-violet)">{{ collect($studentReport)->sum('sakit') }}</td>
                        <td colspan="2" class="fw-bold" style="font-size:.8rem;color:var(--c-indigo);padding:.75rem 1rem">
                            Rata-rata: {{ $avgPct }}%
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- Alpha warning section --}}
    @php $alphaStudents = collect($studentReport)->where('alpha', '>=', 3)->sortByDesc('alpha'); @endphp
    @if($alphaStudents->count() > 0)
    <div class="data-card" style="border-color:#FECACA">
        <div class="dc-head" style="background:#FEF2F2">
            <div class="dc-title" style="color:var(--c-red)">
                <i class="bi bi-exclamation-triangle"></i>
                Siswa dengan Alpha ≥3×
                <span class="pill pa">{{ $alphaStudents->count() }} siswa</span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="dt">
                <thead><tr><th>#</th><th>Nama</th><th>Kelas</th><th class="text-center">Total Alpha</th><th class="text-center">% Kehadiran</th></tr></thead>
                <tbody>
                    @foreach($alphaStudents as $i => $s)
                    <tr>
                        <td class="text-muted">{{ $i+1 }}</td>
                        <td class="fw-semibold" style="color:#0F172A">{{ $s['nama'] }}</td>
                        <td style="color:var(--c-slate)">{{ $s['kelas']??'-' }}</td>
                        <td class="text-center"><span class="pill pa">{{ $s['alpha'] }}×</span></td>
                        <td class="text-center"><span class="pill pa">{{ $s['pct'] }}%</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection