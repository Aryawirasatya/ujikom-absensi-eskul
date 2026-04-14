{{-- riwayat siswa  --}}
@extends('layouts.app')

@section('content')
<style>
/* ─── Typography & Base ─────────────────────────────────────────────── */
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,500;0,9..40,700;0,9..40,900;1,9..40,400&family=DM+Mono:wght@400;500&display=swap');

.att-wrap * { font-family: 'DM Sans', sans-serif; box-sizing: border-box; }
.att-wrap { max-width: 1200px; margin: 0 auto; padding: 2rem 1.5rem 5rem; }

/* ─── Page Header ──────────────────────────────────────────────────── */
.att-header {
    display: flex; flex-wrap: wrap; align-items: flex-end;
    justify-content: space-between; gap: 1rem; margin-bottom: 2.5rem;
}
.att-header-left .att-eyebrow {
    font-size: .65rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 2.5px; color: #94a3b8; margin-bottom: .3rem;
    display: flex; align-items: center; gap: .4rem;
}
.att-header-left h1 {
    font-size: 1.8rem; font-weight: 900; color: #0f172a; margin: 0;
    letter-spacing: -.03em; line-height: 1.1;
}
.att-header-left .att-sub {
    font-size: .82rem; color: #64748b; margin-top: .3rem;
}

/* ─── Summary Bar (4 quick stats) ──────────────────────────────────── */
.att-summary {
    display: grid; grid-template-columns: repeat(4,1fr);
    gap: 1rem; margin-bottom: 2rem;
}
.att-sum-card {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
    padding: 1.1rem 1.3rem; position: relative; overflow: hidden;
    transition: transform .15s, box-shadow .15s;
}
.att-sum-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.08); }
.att-sum-card .sum-stripe {
    position: absolute; left: 0; top: 0; bottom: 0; width: 4px; border-radius: 14px 0 0 14px;
}
.att-sum-card .sum-val {
    font-size: 2rem; font-weight: 900; color: #0f172a; line-height: 1;
}
.att-sum-card .sum-label {
    font-size: .65rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 1.5px; color: #94a3b8; margin-top: .25rem;
}
.att-sum-card .sum-pct {
    font-size: .72rem; color: #64748b; margin-top: .3rem;
}

/* ─── Filter Row ───────────────────────────────────────────────────── */
.att-filter-row {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
    padding: 1rem 1.3rem; margin-bottom: 1.5rem;
    display: flex; flex-wrap: wrap; gap: .8rem; align-items: center;
}
.att-filter-row label {
    font-size: .68rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 1.2px; color: #64748b; white-space: nowrap;
}
.att-filter-row select,
.att-filter-row input[type=text] {
    border: 1.5px solid #e2e8f0; border-radius: 8px; padding: .42rem .8rem;
    font-size: .82rem; color: #1e293b; background: #f8fafc; outline: none;
    font-family: inherit;
}
.att-filter-row select:focus,
.att-filter-row input[type=text]:focus { border-color: #6366f1; background: #fff; }
.att-filter-row .filter-grp { display: flex; align-items: center; gap: .5rem; }
.att-filter-row .filter-sep { width: 1px; height: 28px; background: #e2e8f0; }
.btn-reset {
    font-size: .72rem; font-weight: 700; color: #64748b; text-transform: uppercase;
    letter-spacing: 1px; border: 1.5px solid #e2e8f0; background: #f8fafc;
    border-radius: 8px; padding: .42rem .9rem; cursor: pointer;
    transition: all .15s;
}
.btn-reset:hover { border-color: #6366f1; color: #6366f1; background: #eef2ff; }
.btn-export {
    font-size: .72rem; font-weight: 700; color: #fff; text-transform: uppercase;
    letter-spacing: 1px; border: none; background: #6366f1;
    border-radius: 8px; padding: .45rem 1rem; cursor: pointer;
    transition: all .15s; margin-left: auto; display: flex; align-items: center; gap: .4rem;
}
.btn-export:hover { background: #4f46e5; }

/* ─── Eskul Tab Chips ───────────────────────────────────────────────── */
.eskul-chips { display: flex; flex-wrap: wrap; gap: .5rem; margin-bottom: 1.5rem; }
.chip {
    font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .8px;
    padding: .35rem 1rem; border-radius: 99px; cursor: pointer;
    border: 1.5px solid #e2e8f0; background: #f8fafc; color: #64748b;
    transition: all .15s;
}
.chip.active { background: #6366f1; border-color: #6366f1; color: #fff; box-shadow: 0 4px 12px rgba(99,102,241,.3); }
.chip:hover:not(.active) { border-color: #6366f1; color: #6366f1; }

/* ─── Main Table ───────────────────────────────────────────────────── */
.att-table-wrap {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 16px;
    overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04);
}
.att-table { width: 100%; border-collapse: collapse; }
.att-table thead th {
    font-size: .6rem; font-weight: 800; text-transform: uppercase;
    letter-spacing: 1.5px; color: #94a3b8; padding: .85rem 1.1rem;
    background: #f8fafc; border-bottom: 1px solid #f1f5f9;
    white-space: nowrap; text-align: left;
}
.att-table tbody td {
    padding: .9rem 1.1rem; border-bottom: 1px solid #f8fafc;
    vertical-align: middle; font-size: .82rem; color: #374151;
}
.att-table tbody tr:last-child td { border-bottom: none; }
.att-table tbody tr:hover td { background: #fafbff; }

/* ─── Status Pills ─────────────────────────────────────────────────── */
.pill {
    display: inline-flex; align-items: center; gap: .3rem;
    font-size: .6rem; font-weight: 800; text-transform: uppercase;
    letter-spacing: .8px; padding: .3rem .75rem; border-radius: 99px;
    white-space: nowrap;
}
.pill-hadir  { background: #dcfce7; color: #16a34a; }
.pill-alpha  { background: #fee2e2; color: #dc2626; }
.pill-telat  { background: #fef9c3; color: #ca8a04; }
.pill-izin   { background: #dbeafe; color: #2563eb; }
.pill-sakit  { background: #ede9fe; color: #7c3aed; }
.pill-libur  { background: #f1f5f9; color: #64748b; }
.pill-on_time { background: #dcfce7; color: #16a34a; }
.pill-late    { background: #fef9c3; color: #ca8a04; }
.pill-absent  { background: #fee2e2; color: #dc2626; }
.pill-pending { background: #f1f5f9; color: #94a3b8; }

/* ─── Source Badge ─────────────────────────────────────────────────── */
.src-badge {
    display: inline-flex; align-items: center; gap: .35rem;
    font-size: .68rem; font-weight: 600; padding: .22rem .6rem;
    border-radius: 6px;
}
.src-scan   { background: #dcfce7; color: #15803d; }
.src-manual { background: #dbeafe; color: #1d4ed8; }
.src-system { background: #f1f5f9; color: #64748b; }

/* ─── Time mono ─────────────────────────────────────────────────────── */
.mono {
    font-family: 'DM Mono', monospace; font-size: .75rem;
    background: #f8fafc; padding: .15rem .45rem; border-radius: 5px;
    color: #374151; letter-spacing: -.01em;
}
.mono.empty { color: #cbd5e1; }

/* ─── Pagination ───────────────────────────────────────────────────── */
.att-pagination {
    display: flex; justify-content: space-between; align-items: center;
    padding: .9rem 1.1rem; background: #f8fafc; border-top: 1px solid #f1f5f9;
    font-size: .75rem; color: #64748b;
}
.att-pagination .page-links { display: flex; gap: .35rem; }
.att-pagination .page-links a,
.att-pagination .page-links span {
    width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;
    border-radius: 8px; font-size: .72rem; font-weight: 700;
    border: 1.5px solid #e2e8f0; color: #64748b; text-decoration: none;
    transition: all .12s;
}
.att-pagination .page-links a:hover { border-color: #6366f1; color: #6366f1; }
.att-pagination .page-links span.current { background: #6366f1; border-color: #6366f1; color: #fff; }

/* ─── Empty state ───────────────────────────────────────────────────── */
.empty-state {
    padding: 4rem 2rem; text-align: center;
}
.empty-state .empty-icon {
    width: 64px; height: 64px; background: #f1f5f9; border-radius: 16px;
    display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;
    font-size: 1.6rem; color: #cbd5e1;
}
.empty-state h4 { font-weight: 700; color: #374151; margin-bottom: .4rem; }
.empty-state p  { font-size: .82rem; color: #94a3b8; margin: 0; }

/* ─── Progress rings ─────────────────────────────────────────────────── */
.pct-ring { position: relative; display: inline-flex; align-items: center; justify-content: center; }
.pct-ring svg { transform: rotate(-90deg); }
.pct-ring .ring-val {
    position: absolute; font-size: .62rem; font-weight: 900; color: #0f172a;
    font-family: 'DM Sans', sans-serif; letter-spacing: -.02em;
}

/* ─── Date column ───────────────────────────────────────────────────── */
.date-col .date-main { font-weight: 700; color: #0f172a; font-size: .82rem; }
.date-col .date-day  { font-size: .62rem; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; }
/* Letakkan di bagian paling bawah tag <style> Anda */
.fas, .fa, .far, .fab {
    font-family: "Font Awesome 5 Free" !important;
    font-weight: 900 !important;
    display: inline-block;
    font-style: normal;
    font-variant: normal;
    text-rendering: auto;
    -webkit-font-smoothing: antialiased;
}
/* ─── Responsive ─────────────────────────────────────────────────────── */
@media (max-width: 768px) {
    .att-summary { grid-template-columns: repeat(2,1fr); }
    .att-table thead th:nth-child(6),
    .att-table tbody td:nth-child(6),
    .att-table thead th:nth-child(7),
    .att-table tbody td:nth-child(7) { display: none; }
}
@media (max-width: 480px) {
    .att-summary { grid-template-columns: 1fr 1fr; }
    .att-header-left h1 { font-size: 1.4rem; }
}
</style>

<div class="att-wrap">

    {{-- ── PAGE HEADER ─────────────────────────────────────────────── --}}
    <div class="att-header">
        <div class="att-header-left">
            <div class="att-eyebrow">
                <i class="fas fa-clock"></i>
                Tahun Ajaran {{ $schoolYear->name ?? '—' }}
            </div>
            <h1>Riwayat Absensi</h1>
            <p class="att-sub">
                Semua catatan kehadiran kamu di seluruh ekstrakurikuler
            </p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-pill fw-bold" style="font-size:.72rem;">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
    </div>

    {{-- ── SUMMARY CARDS ────────────────────────────────────────────── --}}
    <div class="att-summary">
        @php
            $totalAll  = $summary->total ?? 0;
            $hadirAll  = $summary->hadir ?? 0;
            $alphaAll  = $summary->alpha ?? 0;
            $telatAll  = $summary->telat ?? 0;
            $pctAll    = $totalAll > 0 ? round($hadirAll/$totalAll*100,1) : 0;
        @endphp
        <div class="att-sum-card">
            <div class="sum-stripe" style="background:#6366f1;"></div>
            <div class="sum-val">{{ $totalAll }}</div>
            <div class="sum-label">Total Pertemuan</div>
            <div class="sum-pct">Seluruh eskul</div>
        </div>
        <div class="att-sum-card">
            <div class="sum-stripe" style="background:#22c55e;"></div>
            <div class="sum-val" style="color:#16a34a;">{{ $pctAll }}%</div>
            <div class="sum-label">Rate Kehadiran</div>
            <div class="sum-pct">{{ number_format($hadirAll) }} hadir</div>
        </div>
        <div class="att-sum-card">
            <div class="sum-stripe" style="background:#ef4444;"></div>
            <div class="sum-val" style="color:#dc2626;">{{ $alphaAll }}</div>
            <div class="sum-label">Total Alpha</div>
            <div class="sum-pct">Tidak masuk tanpa keterangan</div>
        </div>
        <div class="att-sum-card">
            <div class="sum-stripe" style="background:#f59e0b;"></div>
            <div class="sum-val" style="color:#ca8a04;">{{ $telatAll }}</div>
            <div class="sum-label">Total Telat</div>
            <div class="sum-pct">Check-in terlambat</div>
        </div>
    </div>

    {{-- ── ESKUL CHIPS ──────────────────────────────────────────────── --}}
    @if($eskulList->count() > 0)
   <div class="eskul-chips">
        <button 
            class="chip {{ !$eskulId ? 'active' : '' }}"
            onclick="filterEskul(null)">
            Semua Eskul
        </button>

        @foreach($eskulList as $eskul)
        <button
            class="chip {{ $eskulId == $eskul->id ? 'active' : '' }}"
            onclick="filterEskul({{ $eskul->id }})">
            {{ $eskul->name }}
        </button>
        @endforeach
    </div>
    @endif

    {{-- ── FILTER ROW ───────────────────────────────────────────────── --}}
    <div class="att-filter-row">
        <div class="filter-grp">
            <label>Status</label>
            <select id="filterStatus" onchange="applyFilters()">
                <option value="">Semua</option>
                <option value="hadir">Hadir</option>
                <option value="alpha">Alpha</option>
                <option value="telat">Telat</option>
                <option value="izin">Izin</option>
                <option value="sakit">Sakit</option>
                <option value="libur">Libur</option>
            </select>
        </div>
        <div class="filter-sep"></div>
        <div class="filter-grp">
            <label>Sumber</label>
            <select id="filterSumber" onchange="applyFilters()">
                <option value="">Semua</option>
                <option value="scan">QR Scan</option>
                <option value="manual">Manual</option>
                <option value="system">Sistem</option>
            </select>
        </div>
        <div class="filter-sep"></div>
        <div class="filter-grp">
            <label>Cari</label>
            <input type="text" id="filterSearch" placeholder="Nama kegiatan..." oninput="applyFilters()">
        </div>
        <button class="btn-reset" onclick="resetFilters()">
            <i class="fas fa-undo me-1"></i>Reset
        </button>
        <button class="btn-export" onclick="exportCSV()">
            <i class="fas fa-download"></i>Export CSV
        </button>
         {{-- Export PDF --}}
        <a href="{{ route('siswa.kehadiran.export-pdf', request()->only('eskul_id')) }}"
        style="display:inline-flex; align-items:center; gap:6px;
                padding:7px 14px; border-radius:8px; font-size:12px; font-weight:600;
                background:#dc2626; color:#fff; text-decoration:none;
                box-shadow:0 1px 4px rgba(220,38,38,.3);">
        {{-- Heroicon: document --}}
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
        </svg>
        PDF
        </a>
          {{-- Export Excel --}}
    <a href="{{ route('siswa.kehadiran.export-excel', request()->only('eskul_id')) }}"
       style="display:inline-flex; align-items:center; gap:6px;
              padding:7px 14px; border-radius:8px; font-size:12px; font-weight:600;
              background:#16a34a; color:#fff; text-decoration:none;
              box-shadow:0 1px 4px rgba(22,163,74,.3);">
      {{-- Heroicon: table-cells --}}
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
           fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="3" width="18" height="18" rx="2"/>
        <path d="M3 9h18M3 15h18M9 3v18"/>
      </svg>
      Excel
    </a>
    </div>

    {{-- ── TABLE ────────────────────────────────────────────────────── --}}
    <div class="att-table-wrap">
        <table class="att-table" id="mainTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tanggal</th>
                    <th>Kegiatan</th>
                    <th>Ekstrakurikuler</th>
                    <th>Status Final</th>
                    <th>Check-in</th>
                    <th>Jam Masuk</th>
                    <th>Jam Pulang</th>
                    <th>Sumber</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                @forelse($attendances as $i => $att)
                @php
                    $fs  = $att->final_status ?? '—';
                    $cs  = $att->checkin_status ?? 'pending';
                    $src = $att->attendance_source ?? 'system';
                    $actDate = optional($att->activity)->activity_date;

                    $srcLabels = ['scan' => 'QR Scan', 'manual' => 'Manual', 'system' => 'Sistem'];
                  $srcIcons = [
                        'scan'   => 'bi bi-qr-code-scan', 
                        'manual' => 'bi bi-pencil-square', 
                        'system' => 'bi bi-cpu'
                    ];
                    $srcLabel  = $srcLabels[$src] ?? ucfirst($src);
                    $srcIcon   = $srcIcons[$src] ?? 'fas fa-circle';
                @endphp
                <tr
                    data-eskul="{{ optional(optional($att->activity)->extracurricular)->id }}"
                    data-status="{{ $fs }}"
                    data-sumber="{{ $src }}"
                    data-title="{{ strtolower(optional($att->activity)->title ?? '') }}"
                >
                    <td class="text-muted fw-bold" style="font-size:.72rem;">{{ $attendances->firstItem() + $i }}</td>
                    <td class="date-col">
                        <div class="date-main">{{ $actDate ? \Carbon\Carbon::parse($actDate)->format('d M Y') : '—' }}</div>
                        <div class="date-day">{{ $actDate ? \Carbon\Carbon::parse($actDate)->locale('id')->translatedFormat('l') : '' }}</div>
                    </td>
                    <td>
                        <span class="fw-bold text-dark" style="font-size:.82rem;">{{ optional($att->activity)->title ?? '—' }}</span>
                    </td>
                    <td>
                        <span class="text-muted" style="font-size:.78rem;">{{ optional(optional($att->activity)->extracurricular)->name ?? '—' }}</span>
                    </td>
                    <td>
                        <span class="pill pill-{{ $fs }}">
                            @php
                               $statusIcons = [
                                    'hadir'  => 'bi bi-check-circle-fill',
                                    'alpha'  => 'bi bi-x-circle-fill',
                                    'telat'  => 'bi bi-clock-history',
                                    'izin'   => 'bi bi-file-earmark-text-fill',
                                    'sakit'  => 'bi bi-heart-pulse-fill',
                                    'libur'  => 'bi bi-umbrella-fill'
                                ];
                            @endphp
                            <i class="{{ $statusIcons[$fs] ?? 'fas fa-circle' }}"></i>
                            {{ strtoupper($fs) }}
                        </span>
                    </td>
                    <td>
                        <span class="pill pill-{{ $cs }}">{{ str_replace('_',' ',strtoupper($cs)) }}</span>
                    </td>
                    <td>
                        @if($att->checkin_at)
                            <span class="mono">{{ \Carbon\Carbon::parse($att->checkin_at)->format('H:i') }}</span>
                        @else
                            <span class="mono empty">—</span>
                        @endif
                    </td>
                    <td>
                        @if($att->checkout_at)
                            <span class="mono">{{ \Carbon\Carbon::parse($att->checkout_at)->format('H:i') }}</span>
                        @else
                            <span class="mono empty">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="src-badge src-{{ $src }}">
                            <i class="{{ $srcIcon }}"></i>
                            {{ $srcLabel }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <div class="empty-icon"><i class="fas fa-calendar-times"></i></div>
                            <h4>Belum ada riwayat absensi</h4>
                            <p>Data absensi akan muncul setelah kamu mengikuti kegiatan eskul.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        @if($attendances->hasPages())
        <div class="att-pagination">
            <span>Menampilkan {{ $attendances->firstItem() }}–{{ $attendances->lastItem() }} dari {{ $attendances->total() }} data</span>
            <div class="page-links">
                {{-- Prev --}}
                @if($attendances->onFirstPage())
                    <span style="opacity:.4;"><i class="fas fa-chevron-left" style="font-size:.6rem;"></i></span>
                @else
                    <a href="{{ $attendances->previousPageUrl() }}"><i class="fas fa-chevron-left" style="font-size:.6rem;"></i></a>
                @endif

                {{-- Pages --}}
                @foreach($attendances->getUrlRange(max(1,$attendances->currentPage()-2), min($attendances->lastPage(),$attendances->currentPage()+2)) as $page => $url)
                    @if($page == $attendances->currentPage())
                        <span class="current">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach

                {{-- Next --}}
                @if($attendances->hasMorePages())
                    <a href="{{ $attendances->nextPageUrl() }}"><i class="fas fa-chevron-right" style="font-size:.6rem;"></i></a>
                @else
                    <span style="opacity:.4;"><i class="fas fa-chevron-right" style="font-size:.6rem;"></i></span>
                @endif
            </div>
        </div>
        @endif
    </div>

</div>

<script>
let activeEskul = 'all';

function filterEskul(id, btn) {

    const url = new URL(window.location.href);

    if(id === 'all'){
        url.searchParams.delete('eskul_id');
    }else{
        url.searchParams.set('eskul_id', id);
    }

    window.location.href = url.toString();
}

function applyFilters() {
    const status = document.getElementById('filterStatus').value;
    const sumber = document.getElementById('filterSumber').value;
    const search = document.getElementById('filterSearch').value.toLowerCase();

    document.querySelectorAll('#tableBody tr[data-status]').forEach(row => {
        const matchEskul  = activeEskul === 'all' || row.dataset.eskul === activeEskul;
        const matchStatus = !status || row.dataset.status === status;
        const matchSumber = !sumber || row.dataset.sumber === sumber;
        const matchSearch = !search || row.dataset.title.includes(search);
        row.style.display = (matchEskul && matchStatus && matchSumber && matchSearch) ? '' : 'none';
    });
}

function resetFilters() {
    document.getElementById('filterStatus').value = '';
    document.getElementById('filterSumber').value = '';
    document.getElementById('filterSearch').value = '';
    activeEskul = 'all';
    document.querySelectorAll('.eskul-chips .chip').forEach((c,i) => {
        c.classList.toggle('active', i === 0);
    });
    applyFilters();
}

function exportCSV() {
    const rows = [];
    const headers = ['No','Tanggal','Hari','Kegiatan','Eskul','Status','Check-in Status','Jam Masuk','Jam Pulang','Sumber'];
    rows.push(headers.map(h => '"'+h+'"').join(','));

    document.querySelectorAll('#tableBody tr[data-status]').forEach(row => {
        if (row.style.display === 'none') return;
        const cells = row.querySelectorAll('td');
        const rowData = [...cells].map(c => '"' + c.innerText.trim().replace(/\n/g,' ').replace(/"/g,'""') + '"');
        rows.push(rowData.join(','));
    });

    const blob = new Blob([rows.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.setAttribute('download', 'riwayat_absensi.csv');
    document.body.appendChild(a); a.click(); a.remove();
}
</script>
@endsection