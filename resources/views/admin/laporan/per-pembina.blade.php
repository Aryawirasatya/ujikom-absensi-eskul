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
.page-header::before{content:'';position:absolute;inset:0 0 auto 0;height:3px;background:linear-gradient(90deg,var(--c-amber),var(--c-indigo));}
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
.filter-bar input,.filter-bar select{border:1.5px solid #E2E8F0;border-radius:8px;padding:.38rem .7rem;font-size:.8rem;color:#1E293B;background:#F8FAFC;outline:none;font-family:'Inter',sans-serif;}
.filter-bar input:focus,.filter-bar select:focus{border-color:var(--c-indigo);}
.filter-sep{width:1px;height:24px;background:#E2E8F0;}
.btn-filter{font-size:.72rem;font-weight:700;color:#fff;background:var(--c-indigo);border:none;border-radius:8px;padding:.42rem 1rem;cursor:pointer;}
.btn-reset{font-size:.72rem;font-weight:700;color:var(--c-slate);border:1.5px solid #E2E8F0;background:#F8FAFC;border-radius:8px;padding:.4rem .9rem;cursor:pointer;text-decoration:none;}
.btn-export{font-size:.7rem;font-weight:700;display:inline-flex;align-items:center;gap:.35rem;padding:.4rem .9rem;border-radius:8px;border:1.5px solid #E2E8F0;background:#F8FAFC;color:var(--c-slate);text-decoration:none;cursor:pointer;transition:all .15s;}
.btn-export.green{border-color:#BBF7D0;background:#F0FDF4;color:var(--c-green);}

/* Pembina Cards */
.pembina-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1rem;margin-bottom:1.5rem;}
.pembina-card{background:#fff;border:1px solid #E2E8F0;border-radius:16px;padding:1.3rem;transition:box-shadow .2s;}
.pembina-card:hover{box-shadow:0 4px 20px rgba(0,0,0,.08);}
.pc-top{display:flex;align-items:center;gap:.9rem;margin-bottom:1rem;}
.pc-avatar{width:44px;height:44px;border-radius:50%;background:var(--c-indigo-lt);color:var(--c-indigo);display:flex;align-items:center;justify-content:center;font-size:1rem;font-weight:800;flex-shrink:0;font-family:'Sora',sans-serif;}
.pc-name{font-size:.9rem;font-weight:700;color:#0F172A;}
.pc-eskul{font-size:.7rem;color:var(--c-slate);margin-top:.1rem;}
.pc-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem;margin-bottom:.9rem;}
.pc-stat{background:#F8FAFC;border-radius:8px;padding:.5rem;text-align:center;}
.pc-stat .v{font-size:1.1rem;font-weight:800;line-height:1;}
.pc-stat .l{font-size:.55rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#94A3B8;margin-top:.15rem;}
.pc-pct{display:flex;align-items:center;gap:.6rem;}
.pct-bar{height:6px;background:#F1F5F9;border-radius:99px;overflow:hidden;flex:1;}
.pct-fill{height:100%;border-radius:99px;}
.pill{display:inline-flex;align-items:center;font-size:.58rem;font-weight:800;text-transform:uppercase;letter-spacing:.6px;padding:.22rem .6rem;border-radius:99px;white-space:nowrap;}
.ph{background:var(--c-green-lt);color:var(--c-green);}
.pa{background:var(--c-red-lt);color:var(--c-red);}
.pt{background:var(--c-amber-lt);color:var(--c-amber);}
.pi{background:var(--c-blue-lt);color:var(--c-blue);}
.ps{background:var(--c-violet-lt);color:var(--c-violet);}
.pl{background:var(--c-slate-lt);color:var(--c-slate);}

/* Table fallback */
.data-card{background:#fff;border:1px solid #E2E8F0;border-radius:16px;overflow:hidden;margin-bottom:1.5rem;}
.dc-head{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.3rem;border-bottom:1px solid #F1F5F9;flex-wrap:wrap;gap:.5rem;}
.dc-title{font-size:.82rem;font-weight:700;color:#0F172A;display:flex;align-items:center;gap:.4rem;}
table.dt{width:100%;border-collapse:collapse;}
table.dt th{font-size:.56rem;font-weight:800;text-transform:uppercase;letter-spacing:1.5px;color:#94A3B8;padding:.7rem 1rem;background:#F8FAFC;border-bottom:1px solid #F1F5F9;white-space:nowrap;text-align:left;}
table.dt td{padding:.75rem 1rem;border-bottom:1px solid #F8FAFC;font-size:.8rem;vertical-align:middle;}
table.dt tr:last-child td{border-bottom:none;}
table.dt tr:hover td{background:#FAFBFF;}
@media(max-width:768px){.pembina-grid{grid-template-columns:1fr;}}
</style>

<div class="container-fluid lap-wrap">

    <div class="page-header">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h1><i class="bi bi-person-workspace me-2" style="color:var(--c-amber)"></i>Laporan Per Pembina</h1>
                <p>Tahun Ajaran <strong>{{ $schoolYear->name }}</strong> &nbsp;·&nbsp; Statistik kegiatan & kehadiran per pembina</p>
            </div>
            <a href="{{ route('admin.laporan.per-pembina.export-excel', array_filter($filter)) }}" class="btn-export green">
                <i class="bi bi-file-earmark-excel"></i> Excel
            </a>
            <a href="{{ route('admin.laporan.per-pembina.export-pdf', request()->query()) }}"
                class="btn-export btn-pdf">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    Export PDF
                </a>
        </div>
    </div>

    {{-- Top Navigation --}}
    <div class="top-tabs">
        <a href="{{ route('admin.laporan.index', array_filter($filter)) }}">
            <i class="fas fa-globe"></i> Global
        </a>
        <a href="{{ route('admin.laporan.per-pembina', array_filter($filter)) }}" class="active">
            <i class="bi bi-person-workspace"></i> Per Pembina
        </a>
        <a href="{{ route('admin.laporan.per-siswa', array_filter($filter)) }}">
            <i class="bi bi-people"></i> Per Siswa
        </a>
        <a href="#" onclick="document.getElementById('eskulNavRow').classList.toggle('d-none');return false;">
            <i class="bi bi-trophy"></i> Per Eskul
            <span class="badge-count">{{ $eskuls->count() }}</span>
        </a>
    </div>

    {{-- Eskul sub-nav --}}
    <div id="eskulNavRow" class="eskul-nav d-none">
        <span class="en-label">Pilih Eskul:</span>
        @foreach($eskuls as $e)
        <a href="{{ route('admin.laporan.per-eskul', array_merge(['eskul'=>$e->id], array_filter($filter))) }}">{{ $e->name }}</a>
        @endforeach
    </div>

    {{-- Filter --}}
    <form action="{{ route('admin.laporan.per-pembina') }}" method="GET">
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
            <a href="{{ route('admin.laporan.per-pembina') }}" class="btn-reset">Reset</a>
        </div>
    </form>

    {{-- Pembina Cards --}}
    @if(count($pembinaReport) > 0)
    <div class="pembina-grid">
        @foreach($pembinaReport as $p)
        @php
            $pctColor = $p['pct']>=75?'#16A34A':($p['pct']>=50?'#D97706':'#DC2626');
            $initial  = strtoupper(substr($p['nama'],0,1));
            $colors   = ['4F46E5','D97706','16A34A','DC2626','7C3AED','0891B2'];
            $bg       = ['EEF2FF','FEF3C7','DCFCE7','FEE2E2','EDE9FE','CFFAFE'];
            $ci       = crc32($p['nama']) % 6; $ci = abs($ci);
        @endphp
        <div class="pembina-card">
            <div class="pc-top">
                <div class="pc-avatar" style="background:#{{ $bg[$ci] }};color:#{{ $colors[$ci] }}">{{ $initial }}</div>
                <div>
                    <div class="pc-name">{{ $p['nama'] }}</div>
                    <div class="pc-eskul"><i class="bi bi-trophy" style="font-size:.6rem;margin-right:.25rem"></i>{{ $p['eskuls'] ?: '-' }}</div>
                </div>
            </div>
            <div class="pc-stats">
                <div class="pc-stat">
                    <div class="v" style="color:var(--c-indigo)">{{ $p['total_kegiatan'] }}</div>
                    <div class="l">Total</div>
                </div>
                <div class="pc-stat">
                    <div class="v" style="color:var(--c-green)">{{ $p['selesai'] }}</div>
                    <div class="l">Selesai</div>
                </div>
                <div class="pc-stat">
                    <div class="v" style="color:var(--c-red)">{{ $p['cancelled'] }}</div>
                    <div class="l">Batal</div>
                </div>
            </div>
            <div class="pc-pct">
                <div class="pct-bar"><div class="pct-fill" style="width:{{ $p['pct'] }}%;background:{{ $pctColor }}"></div></div>
                <span class="fw-bold" style="font-size:.82rem;color:{{ $pctColor }};white-space:nowrap">{{ $p['pct'] }}% hadir</span>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Table view --}}
    <div class="data-card">
        <div class="dc-head">
            <div class="dc-title"><i class="bi bi-table" style="color:var(--c-amber)"></i>Tabel Rekap Per Pembina</div>
            <a href="{{ route('admin.laporan.per-pembina.export-excel', array_filter($filter)) }}" class="btn-export green"><i class="bi bi-file-earmark-excel"></i>Excel</a>
        </div>
        <div class="table-responsive">
            <table class="dt">
                <thead>
                    <tr>
                        <th>#</th><th>Nama Pembina</th><th>Eskul Diampu</th>
                        <th class="text-center">Total Kegiatan</th><th class="text-center">Selesai</th>
                        <th class="text-center">Dibatalkan</th><th style="min-width:140px">% Kehadiran</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pembinaReport as $i => $p)
                    <tr>
                        <td class="text-muted" style="font-size:.75rem">{{ $i+1 }}</td>
                        <td class="fw-semibold" style="color:#0F172A">{{ $p['nama'] }}</td>
                        <td style="color:var(--c-slate);font-size:.78rem">{{ $p['eskuls']??'-' }}</td>
                        <td class="text-center fw-semibold">{{ $p['total_kegiatan'] }}</td>
                        <td class="text-center"><span class="pill ph">{{ $p['selesai'] }}</span></td>
                        <td class="text-center"><span class="pill {{ $p['cancelled']>0?'pa':'pl' }}">{{ $p['cancelled'] }}</span></td>
                        <td>
                            @php $pc=$p['pct']; $pc_c=$pc>=75?'#16A34A':($pc>=50?'#D97706':'#DC2626'); @endphp
                            <div class="d-flex align-items-center gap-2">
                                <div class="pct-bar flex-fill"><div class="pct-fill" style="width:{{ $pc }}%;background:{{ $pc_c }}"></div></div>
                                <span class="fw-semibold" style="font-size:.78rem;color:{{ $pc_c }};width:36px;text-align:right">{{ $pc }}%</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-5 text-muted">Tidak ada data pembina.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection