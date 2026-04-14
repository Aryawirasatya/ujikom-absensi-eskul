@extends('layouts.app')

@section('title', 'Rapor Penilaian Saya')

<style>
.welcome-banner {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 60%, #1cc88a 100%);
    border-radius: 18px;
    color: #fff;
    padding: 28px 32px;
    position: relative;
    overflow: hidden;
}
.welcome-banner::before {
    content: '';
    position: absolute;
    top: -40px; right: -40px;
    width: 200px; height: 200px;
    background: rgba(255,255,255,.07);
    border-radius: 50%;
}
.welcome-banner::after {
    content: '';
    position: absolute;
    bottom: -30px; right: 80px;
    width: 120px; height: 120px;
    background: rgba(255,255,255,.05);
    border-radius: 50%;
}
.stat-card { border-radius: 14px; border: none; }
.score-bar-bg { height: 8px; background: #e9ecef; border-radius: 4px; }
.score-bar-fill { height: 100%; border-radius: 4px; background: linear-gradient(90deg, #4e73df, #1cc88a); transition: width .8s ease; }
.history-item { border-left: 3px solid #4e73df; padding: 14px 16px; background: #f8f9fc; border-radius: 0 12px 12px 0; margin-bottom: 10px; }
.cat-chip { font-size: .72rem; padding: 3px 8px; border-radius: 12px; background: #e9ecef; color: #495057; }
.star-gold { color: #f6c23e; }
.hidden-notice { background: linear-gradient(135deg,#fff3cd,#fff8e1); border: 1px solid #ffc107; border-radius: 12px; padding: 16px 20px; }
.period-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #6c757d; }
</style>

@section('content')
<div class="container-fluid">

    {{-- Welcome Banner --}}
    <div class="welcome-banner mb-4">
        <div class="position-relative" style="z-index:1">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                @if(auth()->user()->photo)
                    <img src="{{ auth()->user()->photo_url }}" class="rounded-circle" style="width:56px;height:56px;object-fit:cover;border:3px solid rgba(255,255,255,.4)">
                @else
                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold"
                        style="width:56px;height:56px;background:rgba(255,255,255,.2);font-size:1.4rem;border:3px solid rgba(255,255,255,.3)">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                @endif
                <div>
                    <div class="fw-bold fs-5 mb-0">Rapor Penilaian Saya</div>
                    <div class="opacity-75 small">{{ auth()->user()->name }}</div>
                </div>
                <div class="ms-auto text-end opacity-75 small d-none d-md-block">
                    <i class="fas fa-info-circle me-1"></i>
                    Nilai diberikan oleh pembina eskul Anda
                </div>
            </div>
        </div>
    </div>

    {{-- Info jika ada eskul yang tersembunyi --}}
    @if($hiddenEskulCount > 0)
    <div class="hidden-notice mb-4 d-flex align-items-center gap-3">
        <i class="fas fa-eye-slash fa-lg text-warning"></i>
        <div>
            <div class="fw-semibold small">{{ $hiddenEskulCount }} eskul belum menampilkan penilaian</div>
            <div class="text-muted small">Pembina atau admin belum mengaktifkan visibilitas penilaian untuk eskul tersebut.</div>
        </div>
    </div>
    @endif

    @if($assessments->isEmpty())
    {{-- Empty State --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <div style="font-size:4rem">📊</div>
            <h5 class="fw-bold mt-3 mb-1">Belum Ada Penilaian</h5>
            <p class="text-muted small">
                @if($hiddenEskulCount > 0)
                    Penilaianmu belum ditampilkan oleh pembina atau belum ada penilaian yang diberikan.
                @else
                    Pembina belum memberikan penilaian. Tetap semangat! 💪
                @endif
            </p>
        </div>
    </div>
    @else

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card shadow-sm stat-card h-100">
                <div class="card-body text-center py-3">
                    <div class="h2 fw-bold text-primary mb-0">{{ $stats['total_penilaian'] }}</div>
                    <div class="text-muted small">Penilaian Diterima</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm stat-card h-100">
                <div class="card-body text-center py-3">
                    <div class="h2 fw-bold text-success mb-0">
                        {{ number_format($stats['avg_score'], 1) }}
                        <span class="fs-6 text-muted fw-normal">/5</span>
                    </div>
                    <div class="text-muted small">Rata-rata Skor</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm stat-card h-100">
                <div class="card-body text-center py-3">
                    <div class="h2 fw-bold text-warning mb-0">{{ $stats['total_periode'] }}</div>
                    <div class="text-muted small">Periode Dinilai</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm stat-card h-100">
                <div class="card-body text-center py-3">
                    <div class="h2 fw-bold text-info mb-0">{{ $stats['total_eskul'] }}</div>
                    <div class="text-muted small">Eskul Terpantau</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        {{-- Radar Chart --}}
        <div class="col-xl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-0 bg-white px-4 pt-4 pb-2">
                    <h6 class="fw-bold mb-0"><i class="fas fa-chart-area text-primary me-2"></i>Profil Sikapku</h6>
                    <p class="text-muted small mb-0">Rata-rata dari semua penilaian yang diterima</p>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center" style="min-height:300px">
                    @if(count($radarData['labels']) >= 3)
                        <canvas id="radarChart" style="max-height:320px"></canvas>
                    @else
                        <div class="text-center text-muted px-3">
                            <i class="fas fa-chart-area fa-3x opacity-20 mb-2 d-block"></i>
                            <small>Minimal 3 kategori diperlukan untuk grafik radar</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Skor per kategori --}}
        <div class="col-xl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-0 bg-white px-4 pt-4 pb-2">
                    <h6 class="fw-bold mb-0"><i class="fas fa-star text-warning me-2"></i>Detail Per Indikator</h6>
                    <p class="text-muted small mb-0">Kekuatan dan area yang bisa ditingkatkan</p>
                </div>
                <div class="card-body px-4">
                    @php $sorted = array_map(null, $radarData['labels'], $radarData['scores']); usort($sorted, fn($a,$b)=>$b[1]<=>$a[1]); @endphp
                    @foreach($sorted as [$label, $score])
                    @php
                        $filled = round($score);
                        $colorClass = $score >= 4 ? 'success' : ($score >= 3 ? 'primary' : ($score >= 2 ? 'warning' : 'danger'));
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-semibold small text-dark">{{ $label }}</span>
                            <div class="d-flex align-items-center gap-2">
                                <span class="star-gold small">
                                    @for($s=1;$s<=5;$s++)<i class="fa{{ $s<=$filled?'s':'r' }} fa-star fa-xs"></i>@endfor
                                </span>
                                <span class="badge bg-{{ $colorClass }}-subtle text-{{ $colorClass }} fw-bold">{{ number_format($score,1) }}</span>
                            </div>
                        </div>
                       @php
if ($score >= 4) {
    $color1 = '#1cc88a';
    $color2 = '#17a673';
} elseif ($score >= 3) {
    $color1 = '#4e73df';
    $color2 = '#224abe';
} elseif ($score >= 2) {
    $color1 = '#f6c23e';
    $color2 = '#dda20a';
} else {
    $color1 = '#e74a3b';
    $color2 = '#be2617';
}
@endphp

<div class="score-bar-bg">
<div class="score-bar-fill"
style="
width:{{ ($score/5)*100 }}%;
background-image:linear-gradient(90deg, {{ $color1 }}, {{ $color2 }});
">
</div>
</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- History Feedback --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header border-0 bg-white px-4 pt-4 pb-2">
            <h6 class="fw-bold mb-0"><i class="fas fa-scroll text-info me-2"></i>Riwayat Feedback</h6>
            <p class="text-muted small mb-0">Catatan evaluasi dari pembina dari waktu ke waktu</p>
        </div>
        <div class="card-body px-4 pb-4">
            @foreach($history as $period => $periodItems)
            <div class="mb-4">
                <div class="period-label mb-2">
                    <i class="fas fa-calendar me-1"></i>{{ $period }}
                </div>
                @foreach($periodItems as $assessment)
                @php $avg = $assessment->details->avg('score') ?? 0; @endphp
                <div class="history-item">
                    <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                        <div>
                            <div class="fw-semibold small">
                                <i class="fas fa-chalkboard-teacher text-primary me-1"></i>
                                {{ $assessment->evaluator->name }}
                                <span class="text-muted fw-normal"> — {{ $assessment->extracurricular->name }}</span>
                            </div>
                            <div class="text-muted" style="font-size:.72rem">
                                {{ $assessment->assessment_date->translatedFormat('d F Y') }}
                            </div>
                        </div>
                        <div class="star-gold">
                            @for($s=1;$s<=5;$s++)<i class="fa{{ $s<=round($avg)?'s':'r' }} fa-star fa-xs"></i>@endfor
                            <span class="text-muted small ms-1">{{ number_format($avg,1) }}/5</span>
                        </div>
                    </div>

                    {{-- Chips (hanya kategori yang show_to_student=true) --}}
                    <div class="d-flex flex-wrap gap-1 mb-2">
                        @foreach($assessment->details->filter(fn($d) => $d->category && $d->category->show_to_student) as $detail)
                        <span class="cat-chip">
                            {{ $detail->category->name }}:
                            <span class="star-gold">{{ str_repeat('★', $detail->score) }}{{ str_repeat('☆', 5-$detail->score) }}</span>
                        </span>
                        @endforeach
                    </div>

                    {{-- Catatan --}}
                    @if($assessment->general_notes)
                    <div class="bg-white rounded-2 border px-3 py-2 small text-muted fst-italic">
                        "{{ $assessment->general_notes }}"
                    </div>
                    @else
                    <div class="text-muted small">— Tidak ada catatan tambahan</div>
                    @endif
                </div>
                @endforeach
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@if(count($radarData['labels']) >= 3)
@include('admin.penilaian._radar-script')
@endif
@endpush
