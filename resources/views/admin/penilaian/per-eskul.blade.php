{{-- resources/views/admin/penilaian/per-eskul.blade.php --}}
@extends('layouts.app')
@section('title', 'Laporan – ' . $eskul->name)

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
.pn-wrap { max-width: 1200px; margin: 0 auto; }

/* ── BREADCRUMB ── */
.pn-crumb { display:flex; align-items:center; gap:6px; font-size:.75rem; color:var(--ink-mute); margin-bottom:20px; }
.pn-crumb a { color:var(--ink-mute); text-decoration:none; }
.pn-crumb a:hover { color:var(--ink); }
.pn-crumb-active { color:var(--ink); font-weight:600; }
.pn-crumb-sep { opacity:.35; }

/* ── PAGE HEADER ── */
.page-header {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 18px; padding: 22px 28px; margin-bottom: 20px;
    box-shadow: var(--shadow);
    display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;
}
.page-header-title { font-size: 1.05rem; font-weight: 700; color: var(--ink); }
.page-header-sub   { font-size: .78rem; color: var(--ink-mute); margin-top: 3px; }
.hdr-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 10px; font-size: .8rem; font-weight: 600;
    border: 1px solid var(--border); background: var(--surface); color: var(--ink-soft);
    text-decoration: none; transition: all .15s; white-space: nowrap;
}
.hdr-btn:hover { background: var(--base); color: var(--ink); }

/* ── FILTER ── */
.filter-bar {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); padding: 14px 18px; margin-bottom: 18px;
    box-shadow: var(--shadow);
}
.filter-label  { font-size: .72rem; font-weight: 600; color: var(--ink-soft); margin-bottom: 4px; }
.filter-select {
    border: 1px solid var(--border); border-radius: 9px; font-size: .8rem;
    padding: 7px 12px; font-family: inherit; color: var(--ink); background: var(--surface);
    outline: none; min-width: 200px;
}
.filter-select:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(59,130,246,.08); }
.filter-btn {
    padding: 7px 18px; border-radius: 9px; font-size: .8rem; font-weight: 600;
    background: var(--ink); color: #fff; border: none; cursor: pointer;
    display: inline-flex; align-items: center; gap: 5px;
}
.filter-btn:hover { background: #1e293b; }
.filter-reset {
    padding: 7px 14px; border-radius: 9px; font-size: .8rem; font-weight: 600;
    background: var(--base); color: var(--ink-soft); border: 1px solid var(--border);
    text-decoration: none; transition: all .15s;
}
.filter-reset:hover { background: var(--border); }

/* ── TABLE ── */
.table-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 18px; overflow: hidden; box-shadow: var(--shadow);
}
table { width: 100%; border-collapse: collapse; }
thead th {
    font-size: .67rem; text-transform: uppercase; letter-spacing: .07em;
    color: var(--ink-mute); font-weight: 700;
    background: var(--base); border-bottom: 1px solid var(--border);
    padding: 12px 14px; white-space: nowrap;
}
tbody td { padding: 11px 14px; border-bottom: 1px solid var(--base); vertical-align: middle; font-size: .82rem; }
tbody tr:last-child td { border-bottom: none; }
tbody tr:hover td { background: #fafbff; }

.av-sm { width: 30px; height: 30px; border-radius: 7px; object-fit: cover; flex-shrink: 0; }
.av-sm-letter {
    width: 30px; height: 30px; border-radius: 7px; flex-shrink: 0;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    display: flex; align-items: center; justify-content: center;
    font-size: .68rem; font-weight: 700; color: #fff;
}

/* ── CATEGORY CHIPS (bukan kolom, tapi chips dalam satu cell) ── */
.cat-chips-wrap {
    display: flex; flex-wrap: wrap; gap: 4px; max-width: 340px;
}
.cat-chip {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 9px; border-radius: 8px;
    background: var(--base); border: 1px solid var(--border);
    font-size: .69rem; font-weight: 500; color: var(--ink-soft);
    white-space: nowrap;
}
.cat-chip-name { color: var(--ink-mute); max-width: 70px; overflow: hidden; text-overflow: ellipsis; }
.cat-chip-score {
    font-weight: 700; color: var(--ink); font-size: .71rem;
    display: inline-flex; align-items: center; gap: 2px;
}
.cat-chip-score .bi { color: var(--amber); font-size: .6rem; }
.cat-chip-none { color: var(--ink-mute); font-style: italic; }

/* Score chips */
.score-chip {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 9px; border-radius: 50px; font-size: .73rem; font-weight: 700;
}
.score-chip.good { background: var(--green-bg); color: #059669; }
.score-chip.mid  { background: var(--blue-bg);  color: var(--blue); }
.score-chip.low  { background: #fef3c7;          color: #92400e; }
.score-chip.poor { background: #fef2f2;          color: var(--red); }

.p-pill { font-size: .7rem; font-weight: 600; padding: 3px 10px; border-radius: 50px; background: var(--base); border: 1px solid var(--border); color: var(--ink-soft); }
.action-btn {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 5px 11px; border-radius: 7px; font-size: .73rem; font-weight: 600;
    border: 1px solid var(--border); background: var(--surface); color: var(--ink-soft);
    text-decoration: none; transition: all .15s;
}
.action-btn:hover { background: var(--blue-bg); border-color: #93c5fd; color: var(--blue); }

.empty-row td { text-align: center; padding: 56px 20px; color: var(--ink-mute); }
</style>

@section('content')
<div class="pn-wrap">

    <div class="pn-crumb">
        <a href="{{ route('admin.penilaian.index') }}">Laporan Penilaian</a>
        <span class="pn-crumb-sep">›</span>
        <span class="pn-crumb-active">{{ $eskul->name }}</span>
    </div>

    {{-- PAGE HEADER --}}
    <div class="page-header">
        <div>
            <div class="page-header-title">{{ $eskul->name }}</div>
            <div class="page-header-sub">Rekap penilaian seluruh anggota eskul ini</div>
        </div>
        <a href="{{ route('admin.penilaian.index') }}" class="hdr-btn">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    {{-- FILTER --}}
    <div class="filter-bar">
        <form method="GET" class="d-flex flex-wrap gap-3 align-items-end">
            <div>
                <div class="filter-label">Periode</div>
                <select name="period_label" class="filter-select">
                    <option value="">Semua Periode</option>
                    @foreach($periods as $p)
                        <option value="{{ $p }}" {{ $selectedPeriod == $p ? 'selected' : '' }}>{{ fmtPeriod($p) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d-flex gap-2 align-self-end">
                <button type="submit" class="filter-btn">
                    <i class="bi bi-funnel-fill" style="font-size:.68rem"></i> Filter
                </button>
                <a href="{{ route('admin.penilaian.per-eskul', $eskul) }}" class="filter-reset">Reset</a>
            </div>
        </form>
    </div>

    {{-- TABLE --}}
    <div class="table-card">
        <div style="overflow-x:auto">
            <table>
                <thead>
                    <tr>
                        <th style="padding-left:20px">Siswa</th>
                        <th>Pembina</th>
                        <th>Periode</th>
                        <th>Nilai per Aspek</th>
                        <th style="text-align:center">Rata-rata</th>
                        <th style="text-align:center;padding-right:20px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assessments as $a)
                        @php
                            $avg = $a->details->avg('score') ?? 0;
                            $cls = $avg >= 4 ? 'good' : ($avg >= 3 ? 'mid' : ($avg >= 2 ? 'low' : 'poor'));
                        @endphp
                        <tr>
                            <td style="padding-left:20px">
                                <div class="d-flex align-items-center gap-2">
                                    @if($a->evaluatee->photo)
                                        <img src="{{ $a->evaluatee->photo_url }}" class="av-sm">
                                    @else
                                        <div class="av-sm-letter">{{ strtoupper(substr($a->evaluatee->name,0,1)) }}</div>
                                    @endif
                                    <span style="font-weight:600;color:var(--ink)">{{ $a->evaluatee->name }}</span>
                                </div>
                            </td>
                            <td style="color:var(--ink-soft)">{{ $a->evaluator->name }}</td>
                            <td><span class="p-pill">{{ fmtPeriod($a->period_label) }}</span></td>

                            {{-- Nilai per aspek sebagai chips, tidak jadi kolom dinamis --}}
                            <td>
                                <div class="cat-chips-wrap">
                                    @foreach($categories as $cat)
                                        @php $detail = $a->details->firstWhere('category_id', $cat->id); @endphp
                                        <span class="cat-chip" title="{{ $cat->name }}">
                                            <span class="cat-chip-name">{{ $cat->name }}</span>
                                            @if($detail)
                                                <span class="cat-chip-score">
                                                    <i class="bi bi-star-fill"></i>{{ $detail->score }}
                                                </span>
                                            @else
                                                <span class="cat-chip-none">—</span>
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            </td>

                            <td style="text-align:center">
                                <span class="score-chip {{ $cls }}">
                                    <i class="bi bi-star-fill" style="font-size:.6rem"></i>
                                    {{ number_format($avg,1) }}
                                </span>
                            </td>
                            <td style="text-align:center;padding-right:20px">
                                <a href="{{ route('admin.penilaian.per-siswa', $a->evaluatee) }}" class="action-btn">
                                    <i class="bi bi-graph-up" style="font-size:.68rem"></i> Rapor
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr class="empty-row">
                            <td colspan="6">
                                <i class="bi bi-inbox" style="font-size:2rem;display:block;margin-bottom:10px;opacity:.4"></i>
                                Belum ada data penilaian untuk periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(method_exists($assessments,'hasPages') && $assessments->hasPages())
            <div style="padding:14px 22px;border-top:1px solid var(--border)">{{ $assessments->links() }}</div>
        @endif
    </div>

</div>
@endsection