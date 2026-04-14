{{--
    admin.penilaian._rapor-content
    Dipakai oleh: admin/per-siswa, pembina/detail-siswa, siswa/index
    Variables: $assessments, $radarData, $history, $categories
--}}
<style>
.rapor-stat {
    background:#fff; border-radius:16px; border:1.5px solid #e2e8f0;
    padding:20px 22px; text-align:center; transition:all .2s;
}
.rapor-stat:hover { border-color:#93c5fd; transform:translateY(-2px); box-shadow:0 8px 24px rgba(59,130,246,.08); }
.rapor-stat-val { font-size:2rem; font-weight:800; line-height:1; margin-bottom:4px; }

.radar-card, .indicator-card, .timeline-card {
    background:#fff; border-radius:20px; border:1.5px solid #e2e8f0;
    box-shadow:0 4px 24px rgba(15,23,42,.05);
}
.rc-head { padding:22px 24px 8px; }
.rc-head h6 { font-weight:800; font-size:.95rem; margin-bottom:2px; }
.rc-head p  { font-size:.78rem; color:#94a3b8; margin:0; }

.ind-row { margin-bottom:16px; }
.ind-row:last-child { margin-bottom:0; }
.ind-bar-bg  { height:10px; background:#f1f5f9; border-radius:20px; overflow:hidden; }
.ind-bar-fill { height:100%; border-radius:20px; transition:width 1.1s cubic-bezier(.22,.61,.36,1); }
.ind-score-badge {
    width:38px; height:38px; border-radius:10px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    font-weight:800; font-size:.88rem;
}

.period-group-title {
    display:flex; align-items:center; gap:10px;
    font-size:.7rem; font-weight:800; text-transform:uppercase;
    letter-spacing:1.2px; color:#64748b; margin-bottom:12px;
}
.period-group-title::after { content:''; flex:1; height:1px; background:#e2e8f0; }

.tl-item {
    border-radius:16px; border:1.5px solid #e2e8f0;
    overflow:hidden; margin-bottom:10px; transition:all .2s;
}
.tl-item:hover { border-color:#93c5fd; box-shadow:0 4px 16px rgba(59,130,246,.08); }
.tl-item:last-child { margin-bottom:0; }
.tl-head {
    padding:14px 18px; cursor:pointer;
    display:flex; align-items:center; justify-content:space-between; gap:12px;
}
.tl-avatar {
    width:38px; height:38px; border-radius:12px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    font-weight:800; font-size:.85rem; color:#fff;
    background:linear-gradient(135deg,#3b82f6,#8b5cf6);
}
.tl-body { padding:0 18px 14px; border-top:1px solid #f1f5f9; }

.cat-pill {
    display:inline-flex; align-items:center; gap:5px;
    font-size:.72rem; font-weight:600; padding:4px 10px;
    border-radius:20px; background:#f1f5f9; color:#475569; margin:2px;
}
.star-gold { color:#f59e0b; }
.note-box {
    background:#fafbff; border:1px solid #e8edf5; border-radius:10px;
    padding:10px 14px; font-size:.82rem; color:#475569; font-style:italic; margin-top:10px;
}
.lvl {
    font-size:.7rem; font-weight:800; padding:3px 9px;
    border-radius:7px; text-transform:uppercase; letter-spacing:.5px;
}
.lvl-5 { background:#d1fae5; color:#065f46; }
.lvl-4 { background:#dbeafe; color:#1e40af; }
.lvl-3 { background:#fef3c7; color:#92400e; }
.lvl-2 { background:#fee2e2; color:#991b1b; }
.lvl-1 { background:#f1f5f9; color:#475569; }
</style>

@if($assessments->isEmpty())
<div class="card border-0" style="border-radius:20px;border:1.5px solid #e2e8f0!important">
    <div class="card-body text-center py-5">
        <div style="font-size:4rem;margin-bottom:12px">📭</div>
        <h5 class="fw-bold text-dark mb-1">Belum ada data penilaian</h5>
        <p class="text-muted small mb-0">Penilaian akan muncul setelah pembina melakukan evaluasi.</p>
    </div>
</div>
@else

@php
    $overallAvg   = $assessments->flatMap->details->avg('score') ?? 0;
    $totalPeriode = $history->count();
    $totalEskul   = $assessments->unique('extracurricular_id')->count();
@endphp

{{-- STAT CARDS --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="rapor-stat">
            <div class="rapor-stat-val" style="color:#3b82f6">{{ $assessments->count() }}</div>
            <div class="text-muted small">Total Penilaian</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rapor-stat">
            <div class="rapor-stat-val" style="color:#10b981">
                {{ number_format($overallAvg,1) }}<span style="font-size:1rem;color:#94a3b8;font-weight:400">/5</span>
            </div>
            <div class="text-muted small">Rata-rata Skor</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rapor-stat">
            <div class="rapor-stat-val" style="color:#f59e0b">{{ $totalPeriode }}</div>
            <div class="text-muted small">Periode Dinilai</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="rapor-stat">
            <div class="rapor-stat-val" style="color:#8b5cf6">{{ $totalEskul }}</div>
            <div class="text-muted small">Eskul Terpantau</div>
        </div>
    </div>
</div>

{{-- RADAR + BARS --}}
<div class="row g-4 mb-4">
    <div class="col-xl-5">
        <div class="radar-card h-100">
            <div class="rc-head">
                <h6><span class="me-2">🕸️</span>Grafik Radar Sikap</h6>
                <p>Rata-rata dari semua penilaian yang diterima</p>
            </div>
            <div class="d-flex align-items-center justify-content-center px-3 pb-4" style="min-height:300px">
                @if(count($radarData['labels']) >= 3)
                    <canvas id="radarChart" style="max-height:320px;max-width:100%"></canvas>
                @else
                    <div class="text-center text-muted py-4">
                        <div style="font-size:3rem;margin-bottom:12px">📡</div>
                        <p class="small">Minimal 3 indikator aktif diperlukan<br>untuk menampilkan grafik radar.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="indicator-card h-100">
            <div class="rc-head mb-3">
                <h6><span class="me-2">📈</span>Skor Per Indikator</h6>
                <p>Diurutkan dari tertinggi — kekuatan &amp; area pengembangan</p>
            </div>
            <div class="px-4 pb-4">
                @php
                    $sortedItems = collect(array_map(null, $radarData['labels'], $radarData['scores']))
                        ->sortByDesc(fn($x) => $x[1])->values();
                @endphp
                @foreach($sortedItems as [$label, $score])
                @php
                    $filled = round($score);
                    $pctBar = ($score / 5) * 100;
                    $bar = $score >= 4.5 ? '#10b981' : ($score >= 3.5 ? '#3b82f6' : ($score >= 2.5 ? '#f59e0b' : '#ef4444'));
                    $bg  = $score >= 4.5 ? '#d1fae5' : ($score >= 3.5 ? '#dbeafe' : ($score >= 2.5 ? '#fef3c7' : '#fee2e2'));
                    $txt = $score >= 4.5 ? '#065f46' : ($score >= 3.5 ? '#1e40af' : ($score >= 2.5 ? '#92400e' : '#991b1b'));
                @endphp
                <div class="ind-row">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <span class="fw-semibold text-dark flex-grow-1" style="font-size:.88rem">{{ $label }}</span>
                        <span class="star-gold" style="font-size:.78rem">
                            @for($s=1;$s<=5;$s++)<i class="fa{{ $s<=$filled?'s':'r' }} fa-star fa-xs"></i>@endfor
                        </span>
                        <div class="ind-score-badge" style="background:{{ $bg }};color:{{ $txt }}">{{ number_format($score,1) }}</div>
                    </div>
                    <div class="ind-bar-bg">
                        <div class="ind-bar-fill" style="width:{{ $pctBar }}%;background:{{ $bar }}"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- TIMELINE HISTORY --}}
<div class="timeline-card mb-4">
    <div class="rc-head" style="padding-bottom:12px;border-bottom:1px solid #f1f5f9">
        <h6><span class="me-2">📜</span>Riwayat Penilaian</h6>
        <p>Semua feedback pembina dari waktu ke waktu</p>
    </div>
    <div class="px-4 pb-4 pt-3">
        @foreach($history as $period => $periodItems)
        @php
            $firstItem  = $periodItems->first();
            $ptLabel    = match($firstItem->period_type ?? '') {
                'daily'   => 'Harian',
                'weekly'  => 'Mingguan',
                'monthly' => 'Bulanan',
                default   => '',
            };
        @endphp
        <div class="mb-4">
            <div class="period-group-title">
                <i class="fas fa-calendar-alt"></i>{{ $period }}
                @if($ptLabel)
                <span style="font-size:.65rem;font-weight:700;padding:2px 7px;border-radius:5px;background:#f1f5f9;color:#64748b;text-transform:uppercase;letter-spacing:.5px">{{ $ptLabel }}</span>
                @endif
            </div>
            @foreach($periodItems as $assessment)
            @php
                $avg      = $assessment->details->avg('score') ?? 0;
                $filled   = round($avg);
                $lvlClass = $avg >= 4.5 ? 'lvl-5' : ($avg >= 3.5 ? 'lvl-4' : ($avg >= 2.5 ? 'lvl-3' : ($avg >= 1.5 ? 'lvl-2' : 'lvl-1')));
                $lvlText  = $avg >= 4.5 ? 'Luar Biasa' : ($avg >= 3.5 ? 'Baik' : ($avg >= 2.5 ? 'Cukup' : ($avg >= 1.5 ? 'Kurang' : 'Sangat Kurang')));
            @endphp
            <div class="tl-item">
                <div class="tl-head" data-bs-toggle="collapse" data-bs-target="#tl_{{ $assessment->id }}">
                    <div class="d-flex align-items-center gap-3 min-w-0">
                        <div class="tl-avatar">{{ strtoupper(substr($assessment->evaluator->name, 0, 1)) }}</div>
                        <div class="min-w-0">
                            <div class="fw-semibold small text-dark text-truncate">{{ $assessment->evaluator->name }}</div>
                            <div class="text-muted" style="font-size:.73rem">
                                @isset($assessment->extracurricular){{ $assessment->extracurricular->name }} &middot; @endisset
                                {{ $assessment->assessment_date->translatedFormat('d F Y') }}
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                        <span class="star-gold" style="font-size:.82rem">
                            @for($s=1;$s<=5;$s++)<i class="fa{{ $s<=$filled?'s':'r' }} fa-star fa-xs"></i>@endfor
                            <span class="text-muted small ms-1">{{ number_format($avg,1) }}</span>
                        </span>
                        <span class="lvl {{ $lvlClass }}">{{ $lvlText }}</span>
                        <i class="fas fa-chevron-down text-muted collapsed-icon" style="font-size:.7rem"></i>
                    </div>
                </div>
                <div id="tl_{{ $assessment->id }}" class="collapse show tl-body">
                    <div class="pt-2 pb-2">
                        @foreach($assessment->details as $detail)
                        @if($detail->category)
                        <span class="cat-pill">
                            {{ $detail->category->name }}
                            <span class="star-gold">{{ str_repeat('★', $detail->score) }}<span style="opacity:.22">{{ str_repeat('★', 5-$detail->score) }}</span></span>
                        </span>
                        @endif
                        @endforeach
                    </div>
                    @if($assessment->general_notes)
                    <div class="note-box">
                        <i class="fas fa-quote-left text-muted me-1" style="font-size:.7rem"></i>
                        {{ $assessment->general_notes }}
                        <i class="fas fa-quote-right text-muted ms-1" style="font-size:.7rem"></i>
                    </div>
                    @else
                    <div class="text-muted small fst-italic">— Tidak ada catatan tambahan.</div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endforeach
    </div>
</div>


@endif