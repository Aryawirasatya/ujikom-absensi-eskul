{{-- resources/views/admin/penilaian/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Laporan Penilaian Sikap')

@php
use Carbon\Carbon;
if (!function_exists('fmtPeriod')) {
    function fmtPeriod(string $label): string {
        try { return Carbon::createFromFormat('Y-m', $label)->translatedFormat('F Y'); }
        catch (\Throwable $e) { return $label; }
    }
}
@endphp

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
:root {
    --ink:      #0f172a; --ink-soft: #475569; --ink-mute: #94a3b8;
    --surface:  #fff;    --base:     #f8fafc;  --border:   #e2e8f0;
    --green:    #10b981; --green-bg: #ecfdf5;
    --amber:    #f59e0b; --red:      #ef4444;
    --blue:     #3b82f6; --blue-bg:  #eff6ff;
    --r: 14px;
    --shadow: 0 1px 3px rgba(0,0,0,.05), 0 4px 16px rgba(0,0,0,.06);
}
*, *::before, *::after { box-sizing: border-box; }
* { font-family: 'Inter', system-ui, sans-serif; }
body { background: #f1f5f9; }
.pn-wrap { max-width: 1300px; margin: 0 auto; }

/* ── PAGE HEADER ── */
.page-header {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 18px; padding: 22px 28px; margin-bottom: 20px;
    box-shadow: var(--shadow);
    display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;
}
.page-header-title { font-size: 1.1rem; font-weight: 700; color: var(--ink); }
.page-header-sub   { font-size: .8rem; color: var(--ink-mute); margin-top: 3px; }
.hdr-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 10px; font-size: .8rem; font-weight: 600;
    border: 1px solid var(--border); background: var(--surface); color: var(--ink-soft);
    text-decoration: none; transition: all .15s; cursor: pointer; white-space: nowrap;
}
.hdr-btn:hover { background: var(--base); border-color: #c7d2da; color: var(--ink); }
.hdr-btn.primary { background: var(--ink); color: #fff; border-color: var(--ink); }
.hdr-btn.primary:hover { background: #1e293b; }

/* ── FLASH ── */
.flash {
    display: flex; align-items: center; gap: 10px;
    padding: 11px 16px; border-radius: var(--r); font-size: .82rem; font-weight: 500;
    margin-bottom: 16px; animation: fadeIn .25s ease;
}
.flash.success { background: var(--green-bg); border: 1px solid #a7f3d0; color: #065f46; }
.flash-x { margin-left: auto; background: none; border: none; cursor: pointer; opacity: .45; font-size: 1.1rem; color: inherit; padding: 0 2px; }
.flash-x:hover { opacity: .9; }
@keyframes fadeIn { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:translateY(0); } }

/* ── SECTION LABEL ── */
.section-label {
    font-size: .7rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .06em; color: var(--ink-mute); margin-bottom: 12px;
}

/* ── ESKUL CARDS ── */
.eskul-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 16px; transition: all .2s; overflow: hidden;
    box-shadow: var(--shadow); height: 100%;
}
.eskul-card:hover {
    border-color: #93c5fd; transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(59,130,246,.12);
}
.eskul-card-link { display: block; text-decoration: none; padding: 16px; }
.score-ring { width: 50px; height: 50px; position: relative; flex-shrink: 0; }
.score-ring svg { transform: rotate(-90deg); }
.score-ring-label {
    position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
    font-size: .72rem; font-weight: 700; color: var(--ink);
}
.eskul-name { font-size: .875rem; font-weight: 700; color: var(--ink); }
.eskul-meta { font-size: .72rem; color: var(--ink-mute); margin-top: 2px; }
.eskul-footer {
    padding: 10px 16px; border-top: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
    background: var(--base);
}
.period-pill {
    font-size: .7rem; font-weight: 600; padding: 3px 10px;
    border-radius: 50px; background: var(--border); color: var(--ink-soft);
    display: inline-flex; align-items: center; gap: 4px;
}
.toggle-wrap { display: flex; align-items: center; }

/* ── FILTER BAR ── */
.filter-bar {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); padding: 16px 20px; margin-bottom: 18px;
    box-shadow: var(--shadow);
}
.filter-label { font-size: .75rem; font-weight: 600; color: var(--ink-soft); margin-bottom: 5px; }
.filter-select {
    border: 1px solid var(--border); border-radius: 9px; font-size: .82rem;
    padding: 7px 12px; font-family: inherit; color: var(--ink); background: var(--surface); outline: none;
    transition: border-color .15s;
}
.filter-select:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(59,130,246,.08); }
.filter-btn {
    padding: 7px 18px; border-radius: 9px; font-size: .82rem; font-weight: 600;
    background: var(--ink); color: #fff; border: none; cursor: pointer; transition: all .15s;
    display: inline-flex; align-items: center; gap: 6px;
}
.filter-btn:hover { background: #1e293b; }
.filter-reset {
    padding: 7px 14px; border-radius: 9px; font-size: .82rem; font-weight: 600;
    background: var(--base); color: var(--ink-soft); border: 1px solid var(--border); cursor: pointer;
    text-decoration: none; transition: all .15s;
}
.filter-reset:hover { background: var(--border); }

/* ── TABLE ── */
.table-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 18px; overflow: hidden; box-shadow: var(--shadow);
}
.table-card-header {
    padding: 16px 22px; border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
}
.table-card-title { font-size: .9rem; font-weight: 700; color: var(--ink); }
table { width: 100%; border-collapse: collapse; }
thead th {
    font-size: .68rem; text-transform: uppercase; letter-spacing: .07em;
    color: var(--ink-mute); font-weight: 700;
    background: var(--base); border-bottom: 1px solid var(--border);
    padding: 12px 16px; white-space: nowrap;
}
tbody td { padding: 12px 16px; border-bottom: 1px solid var(--base); vertical-align: middle; font-size: .83rem; }
tbody tr:last-child td { border-bottom: none; }
tbody tr:hover td { background: #fafbff; }

.av-sm { width: 32px; height: 32px; border-radius: 8px; object-fit: cover; flex-shrink: 0; }
.av-sm-letter {
    width: 32px; height: 32px; border-radius: 8px; flex-shrink: 0;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    display: flex; align-items: center; justify-content: center;
    font-size: .72rem; font-weight: 700; color: #fff;
}
.score-chip {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 10px; border-radius: 50px; font-size: .75rem; font-weight: 700;
}
.score-chip.good { background: var(--green-bg); color: #059669; }
.score-chip.mid  { background: var(--blue-bg);  color: var(--blue); }
.score-chip.low  { background: #fef3c7;          color: #92400e; }
.score-chip.poor { background: #fef2f2;          color: var(--red); }

.p-pill { font-size: .7rem; font-weight: 600; padding: 3px 10px; border-radius: 50px; background: var(--base); border: 1px solid var(--border); color: var(--ink-soft); }
.action-btn {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 5px 12px; border-radius: 8px; font-size: .75rem; font-weight: 600;
    border: 1px solid var(--border); background: var(--surface); color: var(--ink-soft);
    text-decoration: none; transition: all .15s; cursor: pointer;
}
.action-btn:hover { background: var(--blue-bg); border-color: #93c5fd; color: var(--blue); }

.empty-row td { text-align: center; padding: 56px 20px; color: var(--ink-mute); }
.empty-icon { font-size: 2rem; display: block; margin-bottom: 10px; opacity: .5; color: var(--ink-mute); }
</style>

@section('content')
<div class="pn-wrap">

    {{-- PAGE HEADER --}}
    <div class="page-header">
        <div>
            <div class="page-header-title">Laporan Penilaian Sikap</div>
            <div class="page-header-sub">Rekap evaluasi sikap dari seluruh ekstrakurikuler aktif</div>
        </div>
        <a href="{{ route('admin.assessment-categories.index') }}" class="hdr-btn">
            <i class="bi bi-gear"></i> Kelola Kategori
        </a>
    </div>

    {{-- FLASH --}}
    @if(session('success'))
        <div class="flash success">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ session('success') }}</span>
            <button class="flash-x" onclick="this.closest('.flash').remove()">&times;</button>
        </div>
    @endif

    {{-- ═══ RINGKASAN PER ESKUL ═══ --}}
    <div class="section-label">Ringkasan Per Eskul</div>
    <div class="row g-3 mb-4">
        @forelse($eskulStats as $stat)
            @php
                $avg = $stat['avg_score'];
                $pct = $avg > 0 ? ($avg / 5) * 100 : 0;
                $r   = 19; $c = 2*pi()*$r;
                $strokeColor = $avg >= 4 ? '#10b981' : ($avg >= 3 ? '#3b82f6' : ($avg >= 2 ? '#f59e0b' : '#ef4444'));
            @endphp
            <div class="col-xl-3 col-lg-4 col-md-6">
                <div class="eskul-card">
                    <a href="{{ route('admin.penilaian.per-eskul', $stat['eskul']) }}" class="eskul-card-link">
                        <div class="d-flex align-items-center gap-3">
                            <div class="score-ring">
                                <svg width="50" height="50" viewBox="0 0 50 50">
                                    <circle cx="25" cy="25" r="{{ $r }}" fill="none" stroke="#e2e8f0" stroke-width="4.5"/>
                                    <circle cx="25" cy="25" r="{{ $r }}" fill="none" stroke="{{ $strokeColor }}" stroke-width="4.5"
                                        stroke-dasharray="{{ $c }}"
                                        stroke-dashoffset="{{ $c * (1 - $pct/100) }}"
                                        stroke-linecap="round"/>
                                </svg>
                                <div class="score-ring-label">{{ $avg > 0 ? number_format($avg,1) : '—' }}</div>
                            </div>
                            <div style="flex:1;min-width:0">
                                <div class="eskul-name text-truncate">{{ $stat['eskul']->name }}</div>
                                <div class="eskul-meta">
                                    {{ $stat['total_students'] }} siswa &middot; {{ $stat['total_assessments'] }} penilaian
                                </div>
                            </div>
                        </div>
                    </a>

                    <div class="eskul-footer">
                        <span class="period-pill">
                            <i class="bi bi-clock" style="font-size:.6rem"></i>
                            {{ $stat['last_period'] ? fmtPeriod($stat['last_period']) : 'Belum ada' }}
                        </span>
                        <form action="{{ route('admin.eskul.assessment-visibility', $stat['eskul']) }}" method="POST" class="toggle-wrap">
                            @csrf @method('PATCH')
                            <input type="hidden" name="show_assessment_to_student" value="0">
                            <div class="form-check form-switch mb-0"
                                 title="{{ $stat['eskul']->show_assessment_to_student ? 'Rapor tampil ke siswa' : 'Rapor tersembunyi dari siswa' }}">
                                <input class="form-check-input" type="checkbox"
                                       name="show_assessment_to_student" value="1"
                                       onchange="this.form.submit()"
                                       {{ $stat['eskul']->show_assessment_to_student ? 'checked' : '' }}>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div style="text-align:center;padding:48px;background:var(--surface);border:1.5px dashed var(--border);border-radius:16px">
                    <i class="bi bi-clipboard2-x" style="font-size:2.5rem;color:var(--ink-mute);display:block;margin-bottom:10px"></i>
                    <p style="color:var(--ink-mute);font-size:.85rem;margin:0">Belum ada data penilaian.</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- ═══ FILTER ═══ --}}
    <div class="filter-bar">
        <form method="GET" class="d-flex flex-wrap gap-3 align-items-end">
            <div>
                <div class="filter-label">Eskul</div>
                <select name="eskul_id" class="filter-select">
                    <option value="">Semua Eskul</option>
                    @foreach($extracurriculars as $e)
                        <option value="{{ $e->id }}" {{ $selectedEskul == $e->id ? 'selected' : '' }}>{{ $e->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <div class="filter-label">Periode</div>
                <select name="period_label" class="filter-select">
                    <option value="">Semua Periode</option>
                    @foreach($periods as $p)
                        <option value="{{ $p }}" {{ $selectedPeriod == $p ? 'selected' : '' }}>{{ fmtPeriod($p) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="filter-btn">
                    <i class="bi bi-funnel-fill" style="font-size:.72rem"></i> Filter
                </button>
                <a href="{{ route('admin.penilaian.index') }}" class="filter-reset">Reset</a>
            </div>
        </form>
    </div>

    {{-- ═══ TABLE ═══ --}}
    <div class="table-card">
        <div class="table-card-header">
            <span class="table-card-title">Detail Semua Penilaian</span>
            @php $total = method_exists($assessments,'total') ? $assessments->total() : $assessments->count(); @endphp
            @if($total)
                <span style="font-size:.75rem;color:var(--ink-mute)">{{ $total }} data</span>
            @endif
        </div>
        <div style="overflow-x:auto">
            <table>
                <thead>
                    <tr>
                        <th style="padding-left:22px">Siswa</th>
                        <th>Ekstrakulikuler</th>
                        <th>Pembina Ekstrakulikuler</th>
                        <th>Periode</th>
                        <th style="text-align:center">Rata-rata</th>
                        <th style="text-align:center;padding-right:22px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assessments as $a)
                        @php
                            $avg = $a->details->avg('score') ?? 0;
                            $cls = $avg >= 4 ? 'good' : ($avg >= 3 ? 'mid' : ($avg >= 2 ? 'low' : 'poor'));
                        @endphp
                        <tr>
                            <td style="padding-left:22px">
                                <div class="d-flex align-items-center gap-2">
                                    @if($a->evaluatee->photo)
                                        <img src="{{ $a->evaluatee->photo_url }}" class="av-sm">
                                    @else
                                        <div class="av-sm-letter">{{ strtoupper(substr($a->evaluatee->name,0,1)) }}</div>
                                    @endif
                                    <div>
                                        <div style="font-weight:600;color:var(--ink);font-size:.83rem">{{ $a->evaluatee->name }}</div>
                                        <div style="font-size:.7rem;color:var(--ink-mute)">{{ $a->evaluatee->nisn ?? '—' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="color:var(--ink-soft);font-size:.8rem">{{ $a->extracurricular->name }}</td>
                            <td style="color:var(--ink-soft);font-size:.8rem">{{ $a->evaluator->name }}</td>
                            <td><span class="p-pill bg-primary text-white">{{ fmtPeriod($a->period_label) }}</span></td>
                            <td style="text-align:center">
                                <span class="score-chip {{ $cls }}">
                                    <i class="bi bi-star-fill" style="font-size:.6rem"></i>
                                    {{ number_format($avg,1) }}
                                </span>
                            </td>
                            <td style="text-align:center;padding-right:22px">
                                <a href="{{ route('admin.penilaian.per-siswa', $a->evaluatee) }}" class="action-btn">
                                    <i class="bi bi-graph-up" style="font-size:.7rem"></i> Rapor
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr class="empty-row">
                            <td colspan="6">
                                <i class="bi bi-inbox empty-icon"></i>
                                Tidak ada data penilaian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($assessments,'hasPages') && $assessments->hasPages())
            <div style="padding:14px 22px;border-top:1px solid var(--border)">
                {{ $assessments->links() }}
            </div>
        @endif
    </div>

</div>
@endsection