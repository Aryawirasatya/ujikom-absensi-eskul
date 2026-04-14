{{-- resources/views/pembina/penilaian/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Penilaian – ' . $eskul->name)

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
    --ink:       #0f172a; --ink-soft:  #475569; --ink-mute:  #94a3b8;
    --surface:   #ffffff; --base:      #f8fafc;  --border:    #e2e8f0;
    --green:     #10b981; --green-bg:  #ecfdf5;
    --amber:     #f59e0b; --red:       #ef4444;
    --blue:      #3b82f6; --blue-bg:   #eff6ff;
    --r:         14px;
    --shadow:    0 1px 3px rgba(0,0,0,.05), 0 4px 16px rgba(0,0,0,.06);
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
* { font-family: 'Inter', system-ui, sans-serif; }
body { background: #f1f5f9; }
.pn-wrap { max-width: 1300px; margin: 0 auto; }
.modal-body {
    max-height: 60vh;
    overflow-y: auto;
}
/* ─── BREADCRUMB ─── */
.pn-crumb {
    display: flex; align-items: center; gap: 6px;
    font-size: .75rem; color: var(--ink-mute); margin-bottom: 18px;
}
.pn-crumb a { color: var(--ink-mute); text-decoration: none; }
.pn-crumb a:hover { color: var(--ink); }
.pn-crumb-active { color: var(--ink); font-weight: 600; }
.pn-crumb-sep { opacity: .35; }

/* ─── FLASH ─── */
.flash {
    display: flex; align-items: center; gap: 10px;
    padding: 11px 16px; border-radius: var(--r); font-size: .82rem; font-weight: 500;
    margin-bottom: 14px; animation: fadeIn .25s ease;
}
.flash.success { background: var(--green-bg); border: 1px solid #a7f3d0; color: #065f46; }
.flash.error   { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
.flash-x { margin-left: auto; background: none; border: none; cursor: pointer; opacity: .45; font-size: 1.1rem; color: inherit; line-height: 1; padding: 0 2px; }
.flash-x:hover { opacity: .9; }
@keyframes fadeIn { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:translateY(0); } }

/* ─── HEADER CARD ─── */
.pn-header {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 18px; padding: 24px 28px; margin-bottom: 18px;
    box-shadow: var(--shadow);
}

.p-ring { position: relative; width: 68px; height: 68px; flex-shrink: 0; }
.p-ring svg { transform: rotate(-90deg); }
.p-ring-label {
    position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
    font-size: .82rem; font-weight: 700; color: var(--ink);
}

.s-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px; border-radius: 50px; font-size: .7rem; font-weight: 700;
}
.s-badge.open   { background: var(--green-bg); color: #059669; }
.s-badge.closed { background: #fef2f2;         color: var(--red); }
.s-badge-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }

.stat-row { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 10px; }
.stat-chip {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 14px; border-radius: 10px;
    background: var(--base); border: 1px solid var(--border); min-width: 110px;
}
.stat-icon { width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: .9rem; flex-shrink: 0; }
.stat-val  { font-size: 1.1rem; font-weight: 700; color: var(--ink); line-height: 1; }
.stat-lbl  { font-size: .68rem; color: var(--ink-mute); margin-top: 1px; }

.hdr-prog-wrap { margin-top: 12px; max-width: 220px; }
.hdr-prog { height: 4px; border-radius: 20px; background: var(--border); overflow: hidden; }
.hdr-prog-fill { height: 100%; border-radius: 20px; background: var(--green); transition: width 1.2s cubic-bezier(.22,.61,.36,1); }
.hdr-prog-label { font-size: .68rem; color: var(--ink-mute); margin-top: 5px; }

.period-selector {
    display: flex; align-items: center; gap: 4px;
    background: var(--base); border: 1px solid var(--border);
    border-radius: 50px; padding: 3px 4px 3px 12px; flex-wrap: wrap;
}
.period-selector-lbl { font-size: .72rem; color: var(--ink-mute); font-weight: 500; white-space: nowrap; }
.p-tab {
    padding: 4px 12px; border-radius: 50px; font-size: .73rem; font-weight: 600;
    color: var(--ink-mute); text-decoration: none; transition: all .15s; white-space: nowrap;
    display: flex; align-items: center; gap: 4px;
}
.p-tab:hover  { background: var(--border); color: var(--ink); }
.p-tab.active { background: var(--ink);    color: #fff; }
.p-tab-dot    { width: 5px; height: 5px; border-radius: 50%; background: var(--green); }

.hdr-btn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 7px 14px; border-radius: 10px; font-size: .78rem; font-weight: 600;
    border: 1px solid var(--border); background: var(--surface); color: var(--ink-soft);
    cursor: pointer; transition: all .15s; text-decoration: none; white-space: nowrap;
}
.hdr-btn:hover  { background: var(--base); border-color: #c7d2da; color: var(--ink); }
.hdr-btn.danger { background: #fef2f2; border-color: #fecaca; color: var(--red); }
.hdr-btn.danger:hover { background: #fee2e2; }
.hdr-btn.warn   { background: #fffbeb; border-color: #fde68a; color: #92400e; }
.hdr-btn.warn:hover { background: #fef3c7; }
.hdr-btn.ok     { background: var(--green-bg); border-color: #a7f3d0; color: #065f46; }
.hdr-btn.ok:hover { background: #d1fae5; }

/* ─── FILTER BAR ─── */
.filter-bar { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-bottom: 18px; }
.fpill {
    padding: 6px 16px; border-radius: 50px; font-size: .78rem; font-weight: 600;
    border: 1px solid var(--border); background: var(--surface); color: var(--ink-soft);
    cursor: pointer; transition: all .15s; user-select: none;
}
.fpill.active { background: var(--ink); color: #fff; border-color: var(--ink); }
.fpill:hover:not(.active) { border-color: #93c5fd; color: var(--blue); }
.search-box { position: relative; margin-left: auto; }
.search-box .bi { position: absolute; top: 50%; left: 12px; transform: translateY(-50%); color: var(--ink-mute); font-size: .8rem; pointer-events: none; }
.search-input {
    border: 1px solid var(--border); border-radius: 50px;
    padding: 7px 16px 7px 34px; font-size: .8rem; font-family: inherit;
    background: var(--surface); color: var(--ink); outline: none; transition: all .2s; width: 210px;
}
.search-input:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(59,130,246,.08); width: 250px; }
.search-input::placeholder { color: var(--ink-mute); }

/* ─── NOTICE ─── */
.notice {
    display: flex; align-items: center; gap: 8px;
    padding: 10px 16px; border-radius: 10px; font-size: .8rem;
    background: var(--base); border: 1px solid var(--border); color: var(--ink-soft);
    margin-bottom: 16px;
}

/* ─── MEMBER CARDS ─── */
.member-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); transition: border-color .2s, transform .2s, box-shadow .2s;
    overflow: hidden; height: 100%; position: relative;
}
.member-card.clickable { cursor: pointer; }
.member-card.clickable:hover {
    border-color: #93c5fd; transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(59,130,246,.1);
}
.member-card.assessed { border-color: #a7f3d0; }
.member-card.assessed::after {
    content: ''; position: absolute; top: 0; left: 0; right: 0;
    height: 2.5px; background: var(--green);
}
.member-card.period-closed { cursor: default; opacity: .72; }

.mc-inner { padding: 14px 16px; display: flex; align-items: center; gap: 12px; }
.mc-av { width: 42px; height: 42px; border-radius: 10px; object-fit: cover; flex-shrink: 0; }
.mc-av-letter {
    width: 42px; height: 42px; border-radius: 10px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: .95rem; color: #fff;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
}
.mc-name { font-size: .85rem; font-weight: 600; color: var(--ink); }
.mc-sub  { font-size: .72rem; color: var(--ink-mute); margin-top: 2px; }
.mc-stars { color: var(--amber); font-size: .68rem; }
.mc-score { font-size: .72rem; font-weight: 700; color: var(--ink); margin-left: 4px; }
.mc-done {
    width: 20px; height: 20px; border-radius: 50%; background: var(--green);
    display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-left: auto;
}

/* ─── EMPTY STATE ─── */
.empty-state {
    text-align: center; padding: 68px 40px;
    background: var(--surface); border: 1.5px dashed var(--border); border-radius: 18px;
}
.empty-state h5 { font-size: 1rem; font-weight: 700; color: var(--ink); margin-bottom: 6px; margin-top: 14px; }
.empty-state p  { font-size: .83rem; color: var(--ink-mute); max-width: 340px; margin: 0 auto 20px; }
.btn-solid {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 10px 24px; border-radius: 10px; font-weight: 700; font-size: .83rem;
    background: var(--ink); color: #fff; border: none; cursor: pointer; transition: all .15s;
}
.btn-solid:hover { background: #1e293b; transform: translateY(-1px); box-shadow: 0 5px 14px rgba(15,23,42,.2); }

/* ─── MODAL ─── */
.modal-penilaian .modal-dialog { max-width: 620px; }
.modal-penilaian .modal-content {
    border: 1px solid var(--border); border-radius: 18px;
    box-shadow: 0 24px 64px rgba(15,23,42,.18); overflow: hidden;
}
.m-hdr {
    padding: 18px 22px; display: flex; align-items: center; justify-content: space-between; gap: 12px;
    border-bottom: 1px solid var(--border); background: var(--surface);
}
.m-hdr-av { width: 44px; height: 44px; border-radius: 10px; object-fit: cover; flex-shrink: 0; border: 1px solid var(--border); }
.m-hdr-av-ltr {
    width: 44px; height: 44px; border-radius: 10px; flex-shrink: 0;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 1.05rem; color: #fff;
}
.m-hdr-name { font-size: .95rem; font-weight: 700; color: var(--ink); }
.m-hdr-meta { font-size: .72rem; color: var(--ink-mute); margin-top: 2px; }

.m-legend {
    padding: 9px 22px; background: var(--base); border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px;
}
.m-legend-txt { font-size: .72rem; color: var(--ink-mute); }
.m-legend-item { font-size: .68rem; font-weight: 700; }

/* Star rows */
.sr-row {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 22px; border-bottom: 1px solid var(--base);
    transition: background .12s; position: relative;
}
.sr-row:last-of-type { border-bottom: none; }
.sr-row:hover { background: #fafbff; }
.sr-row.sr-filled { background: #fffdf5; }
.sr-row.sr-filled::before { content:''; position:absolute; left:0; top:0; bottom:0; width:2px; background:var(--amber); }
.sr-lbl-wrap { flex: 1; min-width: 0; }
.sr-label { font-size: .83rem; font-weight: 600; color: var(--ink); }
.sr-desc  { font-size: .7rem; color: var(--ink-mute); margin-top: 1px; }
.sr-hint  { font-size: .68rem; margin-top: 4px; color: var(--border); transition: color .15s; }
.sr-hint.active { color: var(--ink-soft); }

.sr-stars { display: flex; gap: 3px; flex-shrink: 0; }
.sr-star {
    font-size: 1.5rem; cursor: pointer; color: #d1d5db;
    transition: color .08s, transform .08s; line-height: 1; user-select: none;
}
.sr-star:hover    { transform: scale(1.15); }
.sr-star.lit      { color: var(--amber); }
.sr-star.lit-hover { color: #fcd34d; }

.sr-badge {
    width: 30px; height: 30px; border-radius: 7px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: .82rem; background: var(--base); color: var(--ink-mute); transition: all .15s;
}
.sr-badge.s1{background:#fee2e2;color:#dc2626}.sr-badge.s2{background:#ffedd5;color:#ea580c}
.sr-badge.s3{background:#fef9c3;color:#ca8a04}.sr-badge.s4{background:#dcfce7;color:#16a34a}
.sr-badge.s5{background:#d1fae5;color:#059669}

.m-notes { padding: 16px 22px; background: var(--base); border-top: 1px solid var(--border); }
.m-notes label { font-size: .75rem; font-weight: 600; color: var(--ink-soft); display: block; margin-bottom: 7px; }
.notes-ctrl {
    width: 100%; border: 1px solid var(--border); border-radius: 10px;
    padding: 10px 13px; font-size: .8rem; font-family: inherit;
    background: var(--surface); color: var(--ink); resize: none; outline: none; transition: border-color .2s;
}
.notes-ctrl:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(59,130,246,.07); }

.m-progress { padding: 12px 22px 14px; border-top: 1px solid var(--border); }
.m-progress-hdr { display: flex; justify-content: space-between; margin-bottom: 7px; }
.m-progress-lbl { font-size: .75rem; font-weight: 600; color: var(--ink-soft); }
.m-progress-cnt { font-size: .75rem; font-weight: 700; color: var(--blue); }
.prog-track { height: 4px; background: var(--border); border-radius: 20px; overflow: hidden; }
.prog-fill  { height: 100%; background: linear-gradient(90deg,var(--blue),var(--green)); border-radius: 20px; transition: width .3s ease; }
.prog-hint  { font-size: .7rem; color: var(--ink-mute); margin-top: 5px; }

.m-footer {
    padding: 12px 22px; display: flex; align-items: center; justify-content: flex-end; gap: 8px;
    border-top: 1px solid var(--border); background: var(--surface);
}
.btn-cancel {
    padding: 7px 18px; border-radius: 9px; font-size: .8rem; font-weight: 600;
    background: var(--surface); border: 1px solid var(--border); color: var(--ink-soft);
    cursor: pointer; transition: all .15s;
}
.btn-cancel:hover { background: var(--base); }
.btn-save {
    padding: 7px 20px; border-radius: 9px; font-size: .8rem; font-weight: 700;
    background: var(--green); color: #fff; border: none; cursor: pointer;
    display: inline-flex; align-items: center; gap: 6px; transition: all .15s;
}
.btn-save:hover { background: #059669; transform: translateY(-1px); box-shadow: 0 4px 10px rgba(16,185,129,.25); }
.btn-next {
    padding: 7px 20px; border-radius: 9px; font-size: .8rem; font-weight: 700;
    background: var(--blue); color: #fff; border: none; cursor: pointer;
    display: inline-flex; align-items: center; gap: 6px; transition: all .15s;
}
.btn-next:hover { background: #2563eb; transform: translateY(-1px); box-shadow: 0 4px 10px rgba(59,130,246,.25); }
</style>

@section('content')
<div class="pn-wrap">

    <div class="pn-crumb mb-4">
        <a href="{{ route('pembina.eskul.index') }}">Eskul</a>
        <span class="pn-crumb-sep">›</span>
        <span class="pn-crumb-active">Penilaian — {{ $eskul->name }}</span>
    </div>

    {{-- FLASH --}}
    @if(session('success'))
        <div class="flash success">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ session('success') }}</span>
            <button class="flash-x" onclick="this.closest('.flash').remove()">&times;</button>
        </div>
    @endif
    @if(session('error'))
        <div class="flash error">
            <i class="bi bi-exclamation-circle-fill"></i>
            <span>{{ session('error') }}</span>
            <button class="flash-x" onclick="this.closest('.flash').remove()">&times;</button>
        </div>
    @endif

    {{-- ═══════ HEADER ═══════ --}}
    <div class="pn-header">
        <div class="d-flex align-items-start gap-4 flex-wrap">

            @if(!empty($activePeriod))
                @php $r=28; $c=2*pi()*$r; $o=$c*(1-$pct/100); $ringColor = $pct>=100 ? '#10b981' : '#3b82f6'; @endphp
                <div class="p-ring">
                    <svg width="68" height="68" viewBox="0 0 68 68">
                        <circle cx="34" cy="34" r="{{ $r }}" fill="none" stroke="#e2e8f0" stroke-width="5.5"/>
                        <circle cx="34" cy="34" r="{{ $r }}" fill="none" stroke="{{ $ringColor }}" stroke-width="5.5"
                            stroke-dasharray="{{ $c }}" stroke-dashoffset="{{ $o }}"
                            stroke-linecap="round" style="transition:stroke-dashoffset 1.2s cubic-bezier(.22,.61,.36,1)"/>
                    </svg>
                    <div class="p-ring-label">{{ $pct }}%</div>
                </div>
            @else
                <div style="width:68px;height:68px;border-radius:14px;background:var(--base);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:1.6rem;flex-shrink:0">
                    <i class="bi bi-clipboard2" style="color:var(--ink-mute)"></i>
                </div>
            @endif

            <div class="flex-grow-1" style="min-width:200px">
                <div class="d-flex align-items-center gap-2 flex-wrap" style="margin-bottom:4px">
                    <h2 style="font-size:1.15rem;font-weight:700;color:var(--ink);margin:0">{{ $eskul->name }}</h2>
                    <span style="font-size:.68rem;font-weight:600;color:var(--ink-mute);background:var(--base);border:1px solid var(--border);border-radius:6px;padding:2px 8px;text-transform:uppercase;letter-spacing:.04em">Penilaian Sikap</span>
                </div>

                @if(!empty($activePeriod))
                    <div class="d-flex align-items-center gap-2 flex-wrap" style="margin-bottom:10px">
                        <span style="font-size:.8rem;color:var(--ink-soft);font-weight:500">
                            <i class="bi bi-calendar3 me-1" style="color:var(--ink-mute)"></i>
                            {{ fmtPeriod($periodLabel) }}
                        </span>
                        <span class="s-badge {{ $periodStatus }}">
                            <span class="s-badge-dot"></span>
                            {{ $periodStatus === 'open' ? 'Periode Aktif' : 'Periode Ditutup' }}
                        </span>
                    </div>

                    <div class="stat-row">
                        <div class="stat-chip">
                            <div class="stat-icon" style="background:var(--blue-bg)">
                                <i class="bi bi-people-fill" style="color:var(--blue)"></i>
                            </div>
                            <div><div class="stat-val">{{ $totalMembers }}</div><div class="stat-lbl">Total Siswa</div></div>
                        </div>
                        <div class="stat-chip">
                            <div class="stat-icon" style="background:var(--green-bg)">
                                <i class="bi bi-check-circle-fill" style="color:var(--green)"></i>
                            </div>
                            <div><div class="stat-val">{{ $assessedCount }}</div><div class="stat-lbl">Sudah Dinilai</div></div>
                        </div>
                        <div class="stat-chip">
                            <div class="stat-icon" style="background:#fef3c7">
                                <i class="bi bi-hourglass-split" style="color:var(--amber)"></i>
                            </div>
                            <div><div class="stat-val">{{ $totalMembers - $assessedCount }}</div><div class="stat-lbl">Belum Dinilai</div></div>
                        </div>
                    </div>

                    <div class="hdr-prog-wrap">
                        <div class="hdr-prog"><div class="hdr-prog-fill" style="width:{{ $pct }}%"></div></div>
                        <div class="hdr-prog-label">{{ $assessedCount }} dari {{ $totalMembers }} siswa telah dinilai</div>
                    </div>
                @else
                    <p style="font-size:.82rem;color:var(--ink-mute);margin-top:4px">Belum ada periode penilaian aktif.</p>
                @endif
            </div>

            <div class="d-flex flex-column align-items-end" style="gap:10px">
                @if($periods->isNotEmpty())
                    <div class="period-selector">
                        <span class="period-selector-lbl">Periode</span>
                        @foreach($periods as $p)
                            <a href="{{ route('pembina.penilaian.index', $eskul) }}?period={{ $p->period_label }}"
                               class="p-tab {{ isset($periodLabel) && $periodLabel === $p->period_label ? 'active' : '' }}">
                                {{ fmtPeriod($p->period_label) }}
                                @if($p->status === 'open')<span class="p-tab-dot"></span>@endif
                            </a>
                        @endforeach
                    </div>
                @endif

                <div class="d-flex gap-2 flex-wrap justify-content-end">
                    @if($periods->isNotEmpty())
                        <a href="{{ route('pembina.penilaian.laporan', $eskul) }}" class="hdr-btn">
                            <i class="bi bi-graph-up"></i> Laporan
                        </a>
                    @endif

                    @if(isset($periodStatus) && $periodStatus === 'open' && !empty($activePeriod))
                        <form method="POST" action="{{ route('pembina.period.close', ['eskul'=>$eskul->id,'period'=>$activePeriod->id]) }}">
                            @csrf
                            <button class="hdr-btn danger" type="submit">
                                <i class="bi bi-lock-fill"></i> Tutup Periode
                            </button>
                        </form>
                    @elseif(isset($periodStatus) && $periodStatus === 'closed' && !empty($activePeriod))
                        <form method="POST" action="{{ route('pembina.period.open', ['eskul'=>$eskul->id,'period'=>$activePeriod->id]) }}">
                            @csrf
                            <button class="hdr-btn warn" type="submit">
                                <i class="bi bi-lock-open-fill"></i> Buka Kembali
                            </button>
                        </form>
                    @endif

                    @if($periods->isEmpty() || $periods->where('status','open')->isEmpty())
                        <form method="POST" action="{{ route('pembina.period.create', $eskul) }}">
                            @csrf
                            <button class="hdr-btn ok" type="submit">
                                <i class="bi bi-plus-lg"></i>
                                {{ $periods->isEmpty() ? 'Mulai Penilaian' : 'Periode Baru' }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- EMPTY STATE — belum ada periode --}}
    @if(empty($activePeriod))
        <div class="empty-state">
            <i class="bi bi-calendar2-plus" style="font-size:3rem;color:var(--ink-mute)"></i>
            <h5>Belum Ada Periode Penilaian</h5>
            <p>Buat periode pertama untuk mulai menilai siswa di <strong>{{ $eskul->name }}</strong>.</p>
            <form method="POST" action="{{ route('pembina.period.create', $eskul) }}" class="d-inline">
                @csrf
                <button class="btn-solid" type="submit">
                    <i class="bi bi-plus-lg"></i> Buat Periode Penilaian
                </button>
            </form>
        </div>

    @else

        @if($periodStatus === 'closed')
            <div class="notice">
                <i class="bi bi-lock-fill" style="color:var(--ink-mute)"></i>
                <span>Periode <strong>{{ fmtPeriod($periodLabel) }}</strong> sudah ditutup. Data hanya bisa dilihat, tidak bisa diubah.</span>
            </div>
        @endif

        <div class="filter-bar">
            <button class="fpill active" data-filter="all">
                Semua <span style="opacity:.55;font-weight:500">{{ $totalMembers }}</span>
            </button>
            <button class="fpill" data-filter="pending">
                <i class="bi bi-hourglass-split me-1"></i>Belum Dinilai
                <span style="opacity:.55;font-weight:500"> {{ $totalMembers - $assessedCount }}</span>
            </button>
            <button class="fpill" data-filter="assessed">
                <i class="bi bi-check2 me-1"></i>Sudah Dinilai
                <span style="opacity:.55;font-weight:500"> {{ $assessedCount }}</span>
            </button>
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="searchMember" class="search-input" placeholder="Cari nama siswa…">
            </div>
        </div>

        <div class="row g-3" id="memberGrid">
            @forelse($members as $member)
                @php $isAssessed = $member->already_assessed; @endphp
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 member-col"
                     data-status="{{ $isAssessed ? 'assessed' : 'pending' }}"
                     data-name="{{ strtolower($member->name) }}">

                    <div class="member-card
                                {{ $isAssessed ? 'assessed' : '' }}
                                {{ $periodStatus === 'open' ? 'clickable' : 'period-closed' }}"
                        @if($periodStatus === 'open')
                            data-member-id="{{ $member->id }}"
                            onclick="openModal(this)"
                        @endif
                    >
                        <div class="mc-inner">
                            @if($member->photo)
                                <img src="{{ asset('storage/students/'.$member->photo) }}" class="mc-av">
                            @else
                                <div class="mc-av-letter">{{ strtoupper(substr($member->name,0,1)) }}</div>
                            @endif

                            <div style="flex:1;min-width:0">
                                <div class="mc-name text-truncate">{{ $member->name }}</div>
                                @if($isAssessed)
                                    <div class="d-flex align-items-center mt-1">
                                        <div class="mc-stars">
                                            @for($i=1;$i<=5;$i++)
                                                <i class="bi bi-star{{ $i<=round($member->avg_score)?'-fill':'' }}"></i>
                                            @endfor
                                        </div>
                                        <span class="mc-score">{{ number_format($member->avg_score,1) }}</span>
                                    </div>
                                @else
                                    <div class="mc-sub">
                                        {{ $periodStatus === 'open' ? 'Klik untuk menilai' : 'Belum dinilai' }}
                                    </div>
                                @endif
                            </div>

                            @if($isAssessed)
                                <div class="mc-done">
                                    <i class="bi bi-check" style="color:#fff;font-size:.65rem"></i>
                                </div>
                            @elseif($periodStatus === 'closed')
                                <i class="bi bi-lock-fill" style="color:var(--ink-mute);font-size:.75rem;margin-left:auto"></i>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="empty-state">
                        <i class="bi bi-people" style="font-size:3rem;color:var(--ink-mute)"></i>
                        <h5>Tidak Ada Anggota Aktif</h5>
                        <p>Belum ada siswa aktif di eskul ini.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <div id="noResults" class="d-none" style="text-align:center;padding:56px 0">
            <i class="bi bi-search" style="font-size:2.5rem;color:var(--ink-mute);display:block;margin-bottom:10px"></i>
            <p style="color:var(--ink-mute);font-size:.85rem">Nama siswa tidak ditemukan.</p>
        </div>

    @endif
</div>

{{-- ═══════ MODAL ═══════ --}}
@if(!empty($activePeriod) && $periodStatus === 'open')

<div class="modal fade modal-penilaian"
     id="modalNilai"
     tabindex="-1"
     data-bs-backdrop="static"
     data-bs-keyboard="false">

    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">
                <div class="d-flex align-items-center gap-3">
                    <div id="mAvatar"></div>
                    <div>
                        <div class="m-hdr-name" id="mName">—</div>
                        <div class="m-hdr-meta">
                            <i class="bi bi-calendar3 me-1"></i>
                            {{ fmtPeriod($periodLabel) }} · Penilaian Sikap
                        </div>
                    </div>
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="formNilai"
                  action="{{ route('pembina.penilaian.store', $eskul) }}"
                  method="POST">

                @csrf

                <input type="hidden" name="evaluatee_id" id="mEvaluateeId">
                <input type="hidden" name="period_label" value="{{ $periodLabel }}">
                <input type="hidden" name="next_evaluatee_id" id="mNextId">

                <div class="modal-body">

                    <div class="m-legend">
                        Klik bintang untuk memberi nilai (1–5)
                    </div>
                  @foreach($categories as $cat)

                    <div class="px-4 pt-3 pb-1 text-uppercase text-muted fw-semibold" style="font-size:.68rem">
                        {{ $cat->name }}
                    </div>

                    @foreach($cat->questions as $q)

                    <div class="sr-row" id="sr_{{ $q->id }}">

                        <div class="sr-lbl-wrap">
                            <div class="sr-label">{{ $q->question }}</div>

                            <div class="sr-hint" id="hint_{{ $q->id }}">
                                Belum dipilih
                            </div>
                        </div>

                        <div class="sr-stars" data-question="{{ $q->id }}">
                            @for($s=1;$s<=5;$s++)
                                <span class="sr-star" data-val="{{ $s }}">★</span>
                            @endfor
                        </div>

                        <div class="sr-badge" id="badge_{{ $q->id }}">—</div>

                        <input
                            type="hidden"
                            name="scores[{{ $q->id }}]"
                            id="score_{{ $q->id }}">
                    </div>

                    @endforeach

                    @endforeach

                    <div class="m-notes">
                        <label>Catatan & Feedback</label>

                        <textarea
                            name="general_notes"
                            id="mNotes"
                            class="notes-ctrl"
                            rows="3"></textarea>
                    </div>

                    <div class="m-progress">

                        <div class="m-progress-hdr">
                            <span class="m-progress-lbl">Progress</span>
                            <span class="m-progress-cnt" id="progCount">
                                0/{{ $categories->count() }}
                            </span>
                        </div>

                        <div class="prog-track">
                            <div class="prog-fill" id="progBar"></div>
                        </div>

                        <div class="prog-hint" id="progHint">
                            Isi semua indikator
                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn-cancel"
                        data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="btn-save"
                        id="btnSave">
                        Simpan
                    </button>

                    <button
                        type="button"
                        class="btn-next d-none"
                        id="btnNext"
                        onclick="submitAndNext()">
                        Simpan & Lanjut
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endif
@endsection
@php
// ===== PREPARE DATA UNTUK JAVASCRIPT =====
$membersJs = isset($members)
    ? $members->values()->map(function($m) {
        return [
            'id'       => $m->id,
            'name'     => $m->name,
            'photo'    => $m->photo ? asset('storage/students/'.$m->photo) : null,
            'assessed' => (bool)($m->already_assessed ?? false),
            'assessment' => ($m->already_assessed ?? false) && $m->current_assessment
                ? [
                    'general_notes' => $m->current_assessment->general_notes,
                    'details' => $m->current_assessment->details
                        ->map(function($d) {
                            return [
                                'question_id' => $d->question_id,
                                'score'       => $d->score
                            ];
                        })
                        ->values()
                        ->all()
                ]
                : null,
        ];
    })->values()->all()
    : [];

$questionIdsJs = isset($categories)
    ? $categories->flatMap(function($cat){
        return $cat->questions->pluck('id');
    })->values()->all()
    : [];

$totalQuestionsJs = count($questionIdsJs);
@endphp

@push('scripts')
<script>
/*
|--------------------------------------------------------------------------
| DATA DARI SERVER
|--------------------------------------------------------------------------
*/
const MEMBERS         = @json($membersJs);
const QUESTION_IDS    = @json($questionIdsJs);
const TOTAL_QUESTIONS = {{ $totalQuestionsJs }};

const HINT_TEXT = {
    0: '<i class="bi bi-circle" style="font-size:.6rem"></i> Belum dipilih',
    1: 'Sangat Kurang',
    2: 'Kurang',
    3: 'Cukup',
    4: 'Baik',
    5: 'Luar Biasa!'
};

let scores = {};

/*
|--------------------------------------------------------------------------
| UTILITY: VALIDASI SEMUA TERISI
|--------------------------------------------------------------------------
*/
function isAllFilled() {
    const filledCount = QUESTION_IDS.filter(id => scores[id] > 0).length;
    return filledCount === TOTAL_QUESTIONS;
}

/*
|--------------------------------------------------------------------------
| STAR RENDER
|--------------------------------------------------------------------------
*/
function paintStars(questionId, litUpTo, isHover) {
    document.querySelectorAll(`[data-question="${questionId}"] .sr-star`)
        .forEach(star => {
            const v = +star.dataset.val;
            star.classList.remove('lit','lit-hover');
            if(v <= litUpTo)
                star.classList.add(isHover ? 'lit-hover' : 'lit');
        });
}

/*
|--------------------------------------------------------------------------
| SET SCORE
|--------------------------------------------------------------------------
*/
function setScore(questionId, value) {
    scores[questionId] = value;
    paintStars(questionId, value, false);

    document.getElementById(`score_${questionId}`).value = value;

    const hint = document.getElementById(`hint_${questionId}`);
    hint.innerHTML = `<i class="bi bi-check-circle-fill" style="color:#10b981;font-size:.65rem"></i> ${HINT_TEXT[value]}`;
    hint.classList.add('active');

    const badge = document.getElementById(`badge_${questionId}`);
    badge.textContent = value;
    badge.className = `sr-badge s${value}`;

    document.getElementById(`sr_${questionId}`).classList.add('sr-filled');

    updateProgress();
}

/*
|--------------------------------------------------------------------------
| RESET ROW
|--------------------------------------------------------------------------
*/
function resetRow(questionId) {
    scores[questionId] = 0;
    paintStars(questionId, 0, false);
    document.getElementById(`score_${questionId}`).value = '';
    const hint = document.getElementById(`hint_${questionId}`);
    hint.innerHTML = HINT_TEXT;
    hint.classList.remove('active');
    const badge = document.getElementById(`badge_${questionId}`);
    badge.textContent = '—';
    badge.className = 'sr-badge';
    document.getElementById(`sr_${questionId}`).classList.remove('sr-filled');
}

/*
|--------------------------------------------------------------------------
| PROGRESS BAR & BUTTON CONTROL
|--------------------------------------------------------------------------
*/
function updateProgress() {
    const filled = QUESTION_IDS.filter(id => scores[id] > 0).length;
    const pct = TOTAL_QUESTIONS > 0 ? Math.round((filled / TOTAL_QUESTIONS) * 100) : 0;

    document.getElementById('progBar').style.width = pct + '%';
    document.getElementById('progCount').textContent = `${filled}/${TOTAL_QUESTIONS}`;

    const btnSave = document.getElementById('btnSave');
    const btnNext = document.getElementById('btnNext');
    const progHint = document.getElementById('progHint');

    if (filled === TOTAL_QUESTIONS) {
        progHint.innerHTML = '<span style="color:#10b981; font-weight:700">✓ Lengkap! Siap disimpan.</span>';
        btnSave.disabled = false;
        btnSave.style.opacity = "1";
        if(btnNext) {
            btnNext.disabled = false;
            btnNext.style.opacity = "1";
        }
    } else {
        progHint.innerHTML = `<span style="color:#ef4444; font-weight:700">! Harap isi ${TOTAL_QUESTIONS - filled} indikator lagi.</span>`;
        btnSave.disabled = true;
        btnSave.style.opacity = "0.5";
        if(btnNext) {
            btnNext.disabled = true;
            btnNext.style.opacity = "0.5";
        }
    }
}

/*
|--------------------------------------------------------------------------
| STAR EVENTS
|--------------------------------------------------------------------------
*/
document.querySelectorAll('.sr-stars').forEach(group => {
    const questionId = +group.dataset.question;
    group.querySelectorAll('.sr-star').forEach(star => {
        const value = +star.dataset.val;
        star.addEventListener('mouseenter', () => paintStars(questionId, value, true));
        star.addEventListener('mouseleave', () => paintStars(questionId, scores[questionId] || 0, false));
        star.addEventListener('click', () => setScore(questionId, value));
    });
});

/*
|--------------------------------------------------------------------------
| OPEN MODAL
|--------------------------------------------------------------------------
*/
function openModal(el) {
    scores = {};
    const memberId = +el.dataset.memberId;
    const member = MEMBERS.find(m => m.id === memberId);
    if (!member) return;

    QUESTION_IDS.forEach(id => resetRow(id));
    document.getElementById('mNotes').value = '';
    document.getElementById('mName').textContent = member.name;
    document.getElementById('mEvaluateeId').value = member.id;

    const avatar = document.getElementById('mAvatar');
    avatar.innerHTML = member.photo
        ? `<img src="${member.photo}" class="m-hdr-av">`
        : `<div class="m-hdr-av-ltr">${member.name.charAt(0).toUpperCase()}</div>`;

    if (member.assessed && member.assessment) {
        if (member.assessment.general_notes)
            document.getElementById('mNotes').value = member.assessment.general_notes;
        if (Array.isArray(member.assessment.details)) {
            member.assessment.details.forEach(d => setScore(d.question_id, d.score));
        }
    }

    const currentIndex = MEMBERS.findIndex(m => m.id === memberId);
    const next = MEMBERS[currentIndex + 1] ?? null;
    document.getElementById('mNextId').value = next ? next.id : '';

    const btnNext = document.getElementById('btnNext');
    if (btnNext) btnNext.classList.toggle('d-none', !next);

    updateProgress();
    const modalEl = document.getElementById('modalNilai');
    if (modalEl) bootstrap.Modal.getOrCreateInstance(modalEl).show();
}

/*
|--------------------------------------------------------------------------
| SUBMIT LOGIC
|--------------------------------------------------------------------------
*/
function submitAndNext() {
    if (!isAllFilled()) {
        alert("Waduh! Semua bintang harus diisi dulu bos.");
        return;
    }
    // Form akan otomatis menyertakan next_evaluatee_id yang sudah diset di openModal
    document.getElementById('formNilai').submit();
}

// Untuk tombol simpan biasa (bukan lanjut)
document.getElementById('formNilai')?.addEventListener('submit', function(e) {
    if (!isAllFilled()) {
        e.preventDefault();
        alert("Harap isi semua indikator sebelum menyimpan!");
    }
});

// Pastikan next_id kosong kalau cuma klik "Simpan" (bukan Simpan & Lanjut)
document.getElementById('btnSave')?.addEventListener('click', () => {
    document.getElementById('mNextId').value = '';
});

/*
|--------------------------------------------------------------------------
| FILTER & SEARCH
|--------------------------------------------------------------------------
*/
document.querySelectorAll('.fpill').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.fpill').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        applyFilter();
    });
});

const searchInput = document.getElementById('searchMember');
if (searchInput) searchInput.addEventListener('input', applyFilter);

function applyFilter() {
    const filter = document.querySelector('.fpill.active')?.dataset.filter || 'all';
    const search = (searchInput?.value || '').toLowerCase().trim();
    let visible = 0;

    document.querySelectorAll('.member-col').forEach(col => {
        const ok = (filter === 'all' || col.dataset.status === filter) && col.dataset.name.includes(search);
        col.classList.toggle('d-none', !ok);
        if (ok) visible++;
    });

    const noResults = document.getElementById('noResults');
    if (noResults) noResults.classList.toggle('d-none', visible > 0);
}

/*
|--------------------------------------------------------------------------
| AUTO OPEN MODAL (SESSION)
|--------------------------------------------------------------------------
*/
@if(session('open_modal'))
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        const id = {{ (int) session('open_modal') }};
        const card = document.querySelector(`[data-member-id="${id}"]`);
        if (card) openModal(card);
    }, 400);
});
@endif
</script>
@endpush