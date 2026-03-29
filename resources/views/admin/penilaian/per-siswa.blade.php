{{-- resources/views/admin/penilaian/per-siswa.blade.php --}}
@extends('layouts.app')
@section('title', 'Rapor – ' . $user->name)

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
.ps-wrap { max-width: 1100px; margin: 0 auto; }

.ps-crumb { display:flex; align-items:center; gap:6px; font-size:.75rem; color:var(--ink-mute); margin-bottom:20px; }
.ps-crumb a { color:var(--ink-mute); text-decoration:none; }
.ps-crumb a:hover { color:var(--ink); }
.ps-crumb-active { color:var(--ink); font-weight:600; }
.ps-crumb-sep { opacity:.35; }

.student-card {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: 18px; padding: 22px 26px; margin-bottom: 20px; box-shadow: var(--shadow);
}
.stu-av-img { width:54px; height:54px; border-radius:13px; object-fit:cover; flex-shrink:0; border:2px solid var(--border); }
.stu-av-ltr {
    width:54px; height:54px; border-radius:13px; flex-shrink:0;
    background:linear-gradient(135deg,#6366f1,#8b5cf6);
    display:flex; align-items:center; justify-content:center;
    font-weight:700; font-size:1.2rem; color:#fff;
}
.stu-name { font-size:1.05rem; font-weight:700; color:var(--ink); }
.stu-sub  { font-size:.78rem; color:var(--ink-mute); margin-top:3px; }
.stu-tag {
    display:inline-flex; align-items:center; gap:4px;
    padding:3px 10px; border-radius:50px; font-size:.7rem; font-weight:600;
    background:var(--base); border:1px solid var(--border); color:var(--ink-soft);
}
.stu-tag.green { background:var(--green-bg); border-color:#a7f3d0; color:#059669; }
.hdr-btn {
    display:inline-flex; align-items:center; gap:5px;
    padding:7px 14px; border-radius:9px; font-size:.78rem; font-weight:600;
    border:1px solid var(--border); background:var(--surface); color:var(--ink-soft);
    text-decoration:none; transition:all .15s; white-space:nowrap;
}
.hdr-btn:hover { background:var(--base); color:var(--ink); }

.section-card {
    background:var(--surface); border:1px solid var(--border);
    border-radius:18px; margin-bottom:20px; box-shadow:var(--shadow); overflow:hidden;
}
.section-hdr {
    padding:14px 20px; border-bottom:1px solid var(--border);
    display:flex; align-items:center; justify-content:space-between; gap:10px;
}
.section-title { font-size:.875rem; font-weight:700; color:var(--ink); }
.section-sub   { font-size:.72rem; color:var(--ink-mute); margin-top:2px; }

.radar-wrap { padding:22px; }

.cat-bar-item {
    padding:12px 20px; border-bottom:1px solid var(--base);
    display:flex; align-items:center; gap:12px;
}
.cat-bar-item:last-child { border-bottom:none; }
.cat-bar-name  { font-size:.8rem; font-weight:600; color:var(--ink); flex:1; min-width:0; }
.cat-bar-track { flex:2; height:5px; background:var(--border); border-radius:20px; overflow:hidden; min-width:70px; }
.cat-bar-fill  { height:100%; border-radius:20px; background:linear-gradient(90deg,var(--blue),var(--green)); transition:width .8s ease; }
.cat-bar-score { font-size:.78rem; font-weight:700; color:var(--ink); min-width:32px; text-align:right; }

.history-period { padding:16px 20px; border-bottom:1px solid var(--border); }
.history-period:last-child { border-bottom:none; }
.history-period-hdr { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:12px; flex-wrap:wrap; }
.period-badge {
    display:inline-flex; align-items:center; gap:5px;
    padding:4px 12px; border-radius:50px; font-size:.73rem; font-weight:700;
    background:var(--blue-bg); color:var(--blue); border:1px solid #bfdbfe;
}
.avg-chip { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:50px; font-size:.73rem; font-weight:700; }
.avg-chip.good { background:var(--green-bg); color:#059669; }
.avg-chip.mid  { background:var(--blue-bg);  color:var(--blue); }
.avg-chip.low  { background:#fef3c7;          color:#92400e; }
.avg-chip.poor { background:#fef2f2;          color:var(--red); }

.eval-meta { font-size:.72rem; color:var(--ink-mute); margin-bottom:10px; display:flex; align-items:center; gap:6px; }
.detail-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:8px; }
.detail-chip { background:var(--base); border:1px solid var(--border); border-radius:10px; padding:8px 12px; }
.detail-chip-name  { font-size:.68rem; color:var(--ink-mute); margin-bottom:3px; }
.detail-chip-stars { color:var(--amber); font-size:.68rem; }
.detail-chip-val   { font-size:.75rem; font-weight:700; color:var(--ink); margin-left:3px; }
.notes-box {
    background:#fffdf5; border:1px solid #fde68a; border-radius:9px;
    padding:9px 13px; margin-top:10px; font-size:.78rem; color:var(--ink-soft);
}
.notes-box-lbl { font-size:.65rem; font-weight:700; color:#92400e; margin-bottom:3px; text-transform:uppercase; letter-spacing:.04em; }

.interp-list { list-style:none; padding:0; margin:0; }
.interp-list li {
    padding:5px 0; font-size:.78rem; color:var(--ink-soft);
    display:flex; align-items:flex-start; gap:7px; border-bottom:1px solid var(--base);
}
.interp-list li:last-child { border-bottom:none; }
.interp-dot { width:5px; height:5px; border-radius:50%; background:var(--blue); flex-shrink:0; margin-top:5px; }

.empty-state { text-align:center; padding:52px 32px; }
</style>

@section('content')
<div class="ps-wrap">

    {{-- BREADCRUMB --}}
    <div class="ps-crumb">
        <a href="{{ route('admin.penilaian.index') }}">Laporan Penilaian</a>
        <span class="ps-crumb-sep">›</span>
        <span class="ps-crumb-active">{{ $user->name }}</span>
    </div>

    {{-- STUDENT HERO --}}
    <div class="student-card">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            @if($user->photo)
                <img src="{{ $user->photo_url }}" class="stu-av-img">
            @else
                <div class="stu-av-ltr">{{ strtoupper(substr($user->name,0,1)) }}</div>
            @endif
            <div class="flex-grow-1">
                <div class="stu-name">{{ $user->name }}</div>
                <div class="stu-sub">
                    NISN: {{ $user->nisn ?? '—' }}
                    @if(isset($eskulList) && $eskulList->isNotEmpty())
                        &nbsp;&middot;&nbsp; {{ $eskulList->pluck('name')->join(', ') }}
                    @endif
                </div>
                <div class="d-flex gap-2 flex-wrap mt-2">
                    <span class="stu-tag">
                        <i class="bi bi-clipboard2-check" style="font-size:.6rem"></i>
                        {{ $assessments->count() }} Penilaian
                    </span>
                    @if($assessments->count() > 0)
                        @php $overallAvg = $assessments->flatMap->details->avg('score') ?? 0; @endphp
                        <span class="stu-tag green">
                            <i class="bi bi-star-fill" style="font-size:.6rem"></i>
                            Rata-rata {{ number_format($overallAvg,1) }} / 5
                        </span>
                    @endif
                </div>
            </div>
            <a href="{{ route('admin.penilaian.index') }}" class="hdr-btn flex-shrink-0">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    {{-- EMPTY STATE --}}
    @if($assessments->isEmpty())
        <div class="section-card">
            <div class="empty-state">
                <i class="bi bi-clipboard2-x" style="font-size:3rem;color:var(--ink-mute);display:block;margin-bottom:12px"></i>
                <p style="font-weight:700;color:var(--ink)">Belum Ada Penilaian</p>
                <p style="color:var(--ink-mute);font-size:.83rem">Siswa ini belum memiliki data penilaian sikap.</p>
            </div>
        </div>

    @else

    <div class="row g-4">

        {{-- ══ KIRI ══ --}}
        <div class="col-lg-5">

            @if(count($radarData['labels']) >= 3)
            <div class="section-card">
                <div class="section-hdr">
                    <div>
                        <div class="section-title">Profil Sikap</div>
                        <div class="section-sub">Visualisasi rata-rata per aspek</div>
                    </div>
                    <i class="bi bi-bullseye" style="font-size:1.1rem;color:var(--blue)"></i>
                </div>
                <div class="radar-wrap">
                    <canvas id="radarChart" style="max-height:230px"></canvas>
                </div>
                <div style="padding:0 20px 16px">
                    <ul class="interp-list">
                        <li><span class="interp-dot"></span>Area lebih besar = sikap lebih kuat secara keseluruhan.</li>
                        <li><span class="interp-dot"></span>Area kecil menunjukkan aspek yang perlu ditingkatkan.</li>
                        <li><span class="interp-dot"></span>Nilai 5 = sempurna, nilai 1 = sangat kurang.</li>
                    </ul>
                </div>
            </div>
            @endif

            <div class="section-card">
                <div class="section-hdr">
                    <div>
                        <div class="section-title">Skor per Aspek</div>
                        <div class="section-sub">Rata-rata dari semua penilaian</div>
                    </div>
                </div>
                @foreach($categories as $cat)
                    @php
                        $catAvg = $assessments->flatMap->details->where('category_id',$cat->id)->avg('score') ?? 0;
                        $catPct = ($catAvg / 5) * 100;
                    @endphp
                    <div class="cat-bar-item">
                        <div class="cat-bar-name text-truncate">{{ $cat->name }}</div>
                        <div class="cat-bar-track">
                            <div class="cat-bar-fill" style="width:{{ $catPct }}%"></div>
                        </div>
                        <div class="cat-bar-score">{{ $catAvg > 0 ? number_format($catAvg,1) : '—' }}</div>
                    </div>
                @endforeach
            </div>

        </div>

        {{-- ══ KANAN ══ --}}
        <div class="col-lg-7">
            <div class="section-card">
                <div class="section-hdr">
                    <div>
                        <div class="section-title">Riwayat Penilaian</div>
                        <div class="section-sub">{{ $assessments->count() }} penilaian &middot; {{ $history->count() }} periode</div>
                    </div>
                </div>

                @foreach($history as $periodLabel => $periodAssessments)
                    @php
                        $periodAvg = $periodAssessments->flatMap->details->avg('score') ?? 0;
                        $avgCls    = $periodAvg >= 4 ? 'good' : ($periodAvg >= 3 ? 'mid' : ($periodAvg >= 2 ? 'low' : 'poor'));
                    @endphp
                    <div class="history-period">
                        <div class="history-period-hdr">
                            <span class="period-badge">
                                <i class="bi bi-calendar3" style="font-size:.62rem"></i>
                                {{ fmtPeriod($periodLabel) }}
                            </span>
                            <span class="avg-chip {{ $avgCls }}">
                                <i class="bi bi-star-fill" style="font-size:.62rem"></i>
                                {{ number_format($periodAvg,1) }} / 5
                            </span>
                        </div>

                        @foreach($periodAssessments as $assessment)
                            <div class="eval-meta">
                                <i class="bi bi-person-badge"></i>
                                {{ $assessment->evaluator?->name ?? '—' }}
                                <span style="opacity:.3">&middot;</span>
                                <i class="bi bi-calendar-event"></i>
                                {{ Carbon::parse($assessment->assessment_date)->translatedFormat('d F Y') }}
                            </div>

                            <div class="detail-grid">
                                @foreach($categories as $cat)
                                    @php $detail = $assessment->details->firstWhere('category_id',$cat->id); @endphp
                                    <div class="detail-chip">
                                        <div class="detail-chip-name text-truncate">{{ $cat->name }}</div>
                                        @if($detail)
                                            <div class="d-flex align-items-center">
                                                <span class="detail-chip-stars">
                                                    @for($i=1;$i<=5;$i++)
                                                        <i class="bi bi-star{{ $i<=$detail->score ? '-fill' : '' }}"></i>
                                                    @endfor
                                                </span>
                                                <span class="detail-chip-val">{{ $detail->score }}</span>
                                            </div>
                                        @else
                                            <span style="font-size:.7rem;color:var(--ink-mute)">—</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            @if($assessment->general_notes)
                                <div class="notes-box">
                                    <div class="notes-box-lbl">
                                        <i class="bi bi-chat-left-quote me-1"></i> Catatan Pembina
                                    </div>
                                    {{ $assessment->general_notes }}
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

    </div>
    @endif

</div>
@endsection

@push('scripts')
@if(count($radarData['labels']) >= 3)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const data   = @json($radarData);
    const canvas = document.getElementById('radarChart');
    if (!canvas || !data.labels.length) return;
    new Chart(canvas.getContext('2d'), {
        type: 'radar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Rata-rata Sikap',
                data: data.scores,
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
@endif
@endpush