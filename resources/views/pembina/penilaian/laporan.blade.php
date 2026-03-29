{{-- resources/views/pembina/penilaian/laporan.blade.php --}}
@extends('layouts.app')
@section('title', 'Laporan Penilaian – ' . $eskul->name)

@php
use Carbon\Carbon;
if (!function_exists('fmtPeriod')) {
    function fmtPeriod(string $label): string {
        try { return Carbon::createFromFormat('Y-m', $label)->translatedFormat('F Y'); }
        catch (\Throwable $e) { return $label; }
    }
}

// Pre-compute label map untuk JS agar tidak bergantung pada JS call ke PHP
$periodLabels = collect($periods)->mapWithKeys(fn($p) => [$p, fmtPeriod($p)])->toArray();
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

/* ── HEADER ── */
.report-header {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 18px; padding: 22px 28px; margin-bottom: 20px;
    box-shadow: var(--shadow);
}

/* ── STAT CARD ── */
.stat-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); padding: 18px 20px; box-shadow: var(--shadow);
    display: flex; align-items: center; gap: 14px; height: 100%;
}
.stat-icon {
    width: 44px; height: 44px; border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; flex-shrink: 0;
}
.stat-val { font-size: 1.55rem; font-weight: 700; color: var(--ink); line-height: 1; }
.stat-lbl { font-size: .72rem; color: var(--ink-mute); margin-top: 3px; }
.hdr-prog { height: 4px; background: var(--border); border-radius: 20px; overflow: hidden; margin-top: 16px; }
.hdr-prog-fill { height:100%; border-radius:20px; background:var(--green); transition:width 1.2s cubic-bezier(.22,.61,.36,1); }

/* ── SECTION CARD ── */
.section-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 18px; margin-bottom: 20px; box-shadow: var(--shadow); overflow: hidden;
}
.section-hdr {
    padding: 14px 20px; border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
}
.section-title { font-size: .875rem; font-weight: 700; color: var(--ink); }
.section-sub   { font-size: .72rem; color: var(--ink-mute); margin-top: 2px; }

/* ── PERIOD FILTER (form GET, bukan JS) ── */
.period-form-wrap {
    display: inline-flex; align-items: center; gap: 8px;
}
.period-select {
    border: 1px solid var(--border); border-radius: 9px; font-size: .78rem;
    padding: 7px 12px; font-family: inherit; color: var(--ink);
    background: var(--surface); outline: none; min-width: 180px;
    transition: border-color .15s;
}
.period-select:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(59,130,246,.08); }
.period-filter-btn {
    padding: 7px 16px; border-radius: 9px; font-size: .78rem; font-weight: 600;
    background: var(--ink); color: #fff; border: none; cursor: pointer;
    display: inline-flex; align-items: center; gap: 5px; transition: background .15s;
}
.period-filter-btn:hover { background: #1e293b; }
.period-reset-btn {
    padding: 7px 12px; border-radius: 9px; font-size: .78rem; font-weight: 600;
    background: var(--base); color: var(--ink-soft); border: 1px solid var(--border);
    text-decoration: none; transition: all .15s; display: inline-flex; align-items: center;
}
.period-reset-btn:hover { background: var(--border); }

/* ── FILTER BAR ── */
.filter-bar {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); padding: 14px 18px; margin-bottom: 18px;
    display: flex; flex-wrap: wrap; align-items: center; gap: 8px;
    box-shadow: var(--shadow);
}
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
    padding: 7px 16px 7px 34px; font-size: .78rem; font-family: inherit;
    background: var(--surface); color: var(--ink); outline: none; transition: all .2s; width: 210px;
}
.search-input:focus { border-color: var(--blue); box-shadow: 0 0 0 3px rgba(59,130,246,.08); width: 250px; }
.search-input::placeholder { color: var(--ink-mute); }

/* ── STUDENT ROWS ── */
.siswa-row {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r); padding: 13px 16px;
    display: flex; align-items: center; gap: 12px;
    margin-bottom: 8px; transition: all .18s;
}
.siswa-row:hover { border-color: #93c5fd; box-shadow: 0 4px 14px rgba(59,130,246,.09); }
.siswa-row.dinilai { border-left: 3px solid var(--green); }
.siswa-row.belum   { border-left: 3px solid var(--border); }

.av-img    { width:40px; height:40px; border-radius:10px; object-fit:cover; flex-shrink:0; }
.av-letter {
    width:40px; height:40px; border-radius:10px; flex-shrink:0;
    background: linear-gradient(135deg,#6366f1,#8b5cf6);
    display:flex; align-items:center; justify-content:center;
    font-weight:700; color:#fff; font-size:.9rem;
}
.star-mini { color:var(--amber); font-size:.75rem; }
.status-pill {
    display:inline-flex; align-items:center; gap:4px;
    font-size:.7rem; font-weight:700; padding:4px 10px; border-radius:50px;
}
.score-chip {
    display:inline-flex; align-items:center; gap:4px;
    padding:4px 10px; border-radius:50px; font-size:.73rem; font-weight:700;
}
.score-chip.good { background:var(--green-bg); color:#059669; }
.score-chip.mid  { background:var(--blue-bg);  color:var(--blue); }
.score-chip.low  { background:#fef3c7;          color:#92400e; }
.score-chip.poor { background:#fef2f2;          color:var(--red); }
.action-btn {
    display:inline-flex; align-items:center; gap:4px;
    padding:5px 11px; border-radius:8px; font-size:.73rem; font-weight:600;
    border:1px solid var(--border); background:var(--surface); color:var(--ink-soft);
    text-decoration:none; transition:all .15s;
}
.action-btn:hover { background:var(--blue-bg); border-color:#93c5fd; color:var(--blue); }
.action-btn.warn  { background:#fffbeb; border-color:#fde68a; color:#92400e; }
.action-btn.warn:hover { background:#fef3c7; }

.empty-state { text-align:center; padding:52px 24px; }
</style>

@section('content')
<div class="pn-wrap">

    <div class="pn-crumb">
        <a href="{{ route('pembina.eskul.index') }}">Eskul Saya</a>
        <span class="pn-crumb-sep">›</span>
        <a href="{{ route('pembina.penilaian.index', $eskul) }}">Penilaian</a>
        <span class="pn-crumb-sep">›</span>
        <span class="pn-crumb-active">Laporan</span>
    </div>

    {{-- REPORT HEADER --}}
    <div class="report-header">
        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
            <div>
                <h2 style="font-size:1.1rem;font-weight:700;color:var(--ink);margin:0">Laporan Penilaian Sikap</h2>
                <div style="font-size:.8rem;color:var(--ink-mute);margin-top:4px">
                    <i class="bi bi-building me-1"></i>{{ $eskul->name }}
                </div>
            </div>

            {{-- Period filter — pakai FORM GET, bukan JS agar tidak munculkan raw kode --}}
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <form method="GET" action="{{ route('pembina.penilaian.laporan', $eskul) }}" class="period-form-wrap">
                    <select name="period_label" class="period-select">
                    <option value="">Semua Periode</option>
                    @foreach($periods as $p)
                        @php
                            $label = is_object($p) ? $p->period_label : $p;
                        @endphp
                        <option value="{{ $label }}"
                            {{ ($selectedPeriod ?? '') == $label ? 'selected' : '' }}>
                            {{ fmtPeriod($label) }}
                        </option>
                    @endforeach
                </select>
                    <button type="submit" class="period-filter-btn">
                        <i class="bi bi-funnel-fill"></i> Filter
                    </button>
                    @if(!empty($selectedPeriod))
                        <a href="{{ route('pembina.penilaian.laporan', $eskul) }}" class="period-reset-btn">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </form>
                <a href="{{ route('pembina.penilaian.index', $eskul) }}"
                   style="display:inline-flex;align-items:center;gap:5px;padding:7px 14px;border-radius:9px;font-size:.78rem;font-weight:600;border:1px solid var(--border);background:var(--surface);color:var(--ink-soft);text-decoration:none;transition:all .15s">
                    <i class="bi bi-star-fill" style="color:var(--amber)"></i> Beri Penilaian
                </a>
            </div>
        </div>

        {{-- Stat row --}}
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:var(--blue-bg)">
                        <i class="bi bi-people-fill" style="color:var(--blue)"></i>
                    </div>
                    <div>
                        <div class="stat-val">{{ $totalMembers }}</div>
                        <div class="stat-lbl">Total Anggota</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:var(--green-bg)">
                        <i class="bi bi-check-circle-fill" style="color:var(--green)"></i>
                    </div>
                    <div>
                        <div class="stat-val" style="color:var(--green)">{{ $assessedCount }}</div>
                        <div class="stat-lbl">Sudah Dinilai</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fef3c7">
                        <i class="bi bi-star-fill" style="color:var(--amber)"></i>
                    </div>
                    <div>
                        <div class="stat-val" style="color:var(--amber)">{{ number_format($globalAvg,1) }}</div>
                        <div class="stat-lbl">Rata-rata Skor</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#faf5ff">
                        <i class="bi bi-pie-chart-fill" style="color:#8b5cf6"></i>
                    </div>
                    <div>
                        <div class="stat-val">{{ $pct }}%</div>
                        <div class="stat-lbl">Progress</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="hdr-prog">
            <div class="hdr-prog-fill" style="width:{{ $pct }}%"></div>
        </div>
    </div>

    {{-- RADAR CHART --}}
    @if(isset($radarData) && count($radarData['labels'] ?? []) >= 3)
    <div class="section-card mb-4">
        <div class="section-hdr">
            <div>
                <div class="section-title">Analisis Profil Sikap Anggota</div>
                <div class="section-sub">Rata-rata skor per aspek dari semua penilaian</div>
            </div>
            <span style="background:var(--blue-bg);color:var(--blue);padding:4px 12px;border-radius:50px;font-size:.7rem;font-weight:700;border:1px solid #bfdbfe">
                Radar Chart
            </span>
        </div>
        <div class="row g-0 align-items-center">
            <div class="col-lg-6" style="padding:22px">
                <canvas id="radarChart" style="max-height:250px"></canvas>
            </div>
            <div class="col-lg-6" style="padding:22px;border-left:1px solid var(--border)">
                <div style="font-size:.75rem;font-weight:700;color:var(--ink-soft);margin-bottom:10px">Interpretasi</div>
                <ul style="list-style:none;padding:0;margin:0">
                    @foreach([
                        'Grafik menunjukkan rata-rata sikap anggota eskul.',
                        'Area yang lebih besar menandakan sikap lebih kuat secara umum.',
                        'Area kecil pada satu sudut = aspek yang perlu ditingkatkan.',
                        'Target ideal: nilai 4–5 di seluruh aspek.'
                    ] as $txt)
                    <li style="display:flex;align-items:flex-start;gap:8px;padding:5px 0;border-bottom:1px solid var(--base);font-size:.78rem;color:var(--ink-soft)">
                        <span style="width:5px;height:5px;border-radius:50%;background:var(--blue);flex-shrink:0;margin-top:6px"></span>
                        {{ $txt }}
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    {{-- FILTER BAR --}}
    <div class="filter-bar">
        <button class="fpill active" data-filter="all">
            Semua <span style="opacity:.55;font-weight:500"> {{ $totalMembers }}</span>
        </button>
        <button class="fpill" data-filter="dinilai">
            <i class="bi bi-check2 me-1"></i>Sudah
            <span style="opacity:.55;font-weight:500"> {{ $assessedCount }}</span>
        </button>
        <button class="fpill" data-filter="belum">
            <i class="bi bi-hourglass-split me-1"></i>Belum
            <span style="opacity:.55;font-weight:500"> {{ $totalMembers - $assessedCount }}</span>
        </button>
        <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" id="searchSiswa" class="search-input" placeholder="Cari nama siswa…">
        </div>
    </div>

    {{-- STUDENT LIST --}}
    <div id="siswaList">
        @forelse($members as $member)
            @php
                $status = $member->has_assessment ? 'dinilai' : 'belum';
                $avg    = $member->avg_score ?? 0;
                $cls    = $avg >= 4 ? 'good' : ($avg >= 3 ? 'mid' : ($avg >= 2 ? 'low' : 'poor'));
            @endphp
            <div class="siswa-row {{ $status }}" data-status="{{ $status }}" data-name="{{ strtolower($member->name) }}">

                @if($member->photo)
                    <img src="{{ asset('storage/students/'.$member->photo) }}" class="av-img">
                @else
                    <div class="av-letter">{{ strtoupper(substr($member->name,0,1)) }}</div>
                @endif

                <div style="flex:1;min-width:0">
                    <div style="font-weight:600;color:var(--ink);font-size:.875rem" class="text-truncate">{{ $member->name }}</div>
                    <div style="font-size:.72rem;color:var(--ink-mute)">{{ $member->nisn ?? '—' }}</div>
                </div>

                <div class="text-center d-none d-sm-block" style="min-width:110px">
                    @if($member->has_assessment)
                        <div class="star-mini mb-1">
                            @for($s=1;$s<=5;$s++)
                                <i class="bi bi-star{{ $s<=round($avg)?'-fill':'' }}"></i>
                            @endfor
                        </div>
                        <span class="score-chip {{ $cls }}">{{ number_format($avg,1) }} / 5</span>
                    @else
                        <span style="font-size:.72rem;color:var(--ink-mute)">
                            <i class="bi bi-hourglass-split me-1"></i>Belum dinilai
                        </span>
                    @endif
                </div>

                <div class="d-none d-md-flex" style="width:90px;justify-content:center">
                    @if($member->has_assessment)
                        <span class="status-pill" style="background:var(--green-bg);color:#059669">
                            <i class="bi bi-check2"></i> Sudah
                        </span>
                    @else
                        <span class="status-pill" style="background:var(--base);color:var(--ink-mute);border:1px solid var(--border)">
                            Belum
                        </span>
                    @endif
                </div>

                <div class="d-flex gap-2 flex-shrink-0">
                    <a href="{{ route('pembina.penilaian.siswa', [$eskul, $member]) }}" class="action-btn">
                        <i class="bi bi-graph-up"></i>
                        <span class="d-none d-md-inline">Rapor</span>
                    </a>
                    <a href="{{ route('pembina.penilaian.index', $eskul) }}" class="action-btn warn">
                        <i class="bi bi-star-fill"></i>
                        <span class="d-none d-md-inline">{{ $member->has_assessment ? 'Edit' : 'Nilai' }}</span>
                    </a>
                </div>

            </div>
        @empty
            <div class="empty-state">
                <i class="bi bi-people" style="font-size:3rem;color:var(--ink-mute);display:block;margin-bottom:12px"></i>
                <p style="color:var(--ink-mute);font-size:.85rem">Belum ada anggota aktif di eskul ini.</p>
            </div>
        @endforelse
    </div>

    <div id="noResults" class="d-none" style="text-align:center;padding:48px 0">
        <i class="bi bi-search" style="font-size:2.5rem;color:var(--ink-mute);display:block;margin-bottom:10px"></i>
        <p style="color:var(--ink-mute);font-size:.83rem">Tidak ada siswa yang cocok.</p>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
/* ── Filter & Search ── */
document.querySelectorAll('.fpill').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.fpill').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        applyFilter();
    });
});
const si = document.getElementById('searchSiswa');
if (si) si.addEventListener('input', applyFilter);

function applyFilter() {
    const filter = document.querySelector('.fpill.active')?.dataset.filter || 'all';
    const search = (si?.value || '').toLowerCase();
    let vis = 0;
    document.querySelectorAll('.siswa-row').forEach(row => {
        const ok = (filter === 'all' || row.dataset.status === filter)
                && row.dataset.name.includes(search);
        row.style.display = ok ? '' : 'none';
        if (ok) vis++;
    });
    const nr = document.getElementById('noResults');
    if (nr) nr.classList.toggle('d-none', vis > 0);
}

/* ── Radar Chart ── */
document.addEventListener('DOMContentLoaded', function () {
    const radarData = @json($radarData ?? ['labels' => [], 'scores' => []]);
    const canvas = document.getElementById('radarChart');
    if (!canvas || !radarData.labels.length) return;

    new Chart(canvas.getContext('2d'), {
        type: 'radar',
        data: {
            labels: radarData.labels,
            datasets: [{
                label: 'Rata-rata Sikap',
                data: radarData.scores,
                backgroundColor: 'rgba(59,130,246,.14)',
                borderColor: '#3b82f6',
                borderWidth: 2,
                pointBackgroundColor: '#3b82f6',
                pointRadius: 4,
                pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                r: {
                    min: 0, max: 5,
                    ticks: { stepSize:1, font:{size:9,family:'Inter'}, color:'#94a3b8', backdropColor:'transparent' },
                    grid: { color:'#e2e8f0' }, angleLines: { color:'#e2e8f0' },
                    pointLabels: { font:{size:10,family:'Inter',weight:'600'}, color:'#475569' }
                }
            },
            plugins: {
                legend: { display:false },
                tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.r.toFixed(1)} / 5` } }
            }
        }
    });
});
</script>
@endpush