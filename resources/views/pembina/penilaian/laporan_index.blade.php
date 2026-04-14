@extends('layouts.app')

@section('title', 'Laporan Penilaian Sikap')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600&display=swap');
:root{
    --c-indigo:#4F46E5; --c-indigo-lt:#EEF2FF;
    --c-green:#16A34A; --c-green-lt:#DCFCE7;
    --c-blue:#2563EB; --c-blue-lt:#DBEAFE;
    --c-slate:#64748B; --c-slate-lt:#F1F5F9;
    --c-amber:#D97706;
}
.lap-wrap *{font-family:'Inter',sans-serif;}
h1, .ec-name, .sv{font-family:'Sora',sans-serif;}
.lap-wrap{max-width:1200px;margin:0 auto;padding:2rem 1.5rem 6rem;}

/* Header */
.page-header{background:#fff;border-radius:20px;border:1px solid #E2E8F0;padding:1.75rem 2rem;margin-bottom:1.5rem;position:relative;overflow:hidden;}
.page-header::before{content:'';position:absolute;inset:0 0 auto 0;height:3px;background:linear-gradient(90deg,var(--c-indigo),var(--c-blue));}
.page-header h1{font-size:1.4rem;font-weight:800;color:#0F172A;margin:0;}
.page-header p{color:var(--c-slate);font-size:.82rem;margin:.3rem 0 0;}

/* Grid */
.eskul-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:1.25rem;}
.eskul-card{background:#fff;border:1px solid #E2E8F0;border-radius:18px;overflow:hidden;transition:all .2s;text-decoration:none;color:inherit;display:block;}
.eskul-card:hover{box-shadow:0 12px 30px rgba(79, 70, 229, 0.1);transform:translateY(-4px);border-color:var(--c-indigo);}

/* Card Head */
.ec-head{padding:1.4rem 1.5rem 1rem;color:#fff;position:relative;overflow:hidden;}
.ec-head::after{content:'';position:absolute;width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,.1);top:-20px;right:-20px;}
.ec-icon{font-size:1.6rem;opacity:.8;margin-bottom:.5rem;}
.ec-name{font-size:1.15rem;font-weight:800;line-height:1.2;}
.ec-badge{display:inline-flex;align-items:center;gap:.3rem;font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;background:rgba(255,255,255,.2);padding:.2rem .55rem;border-radius:99px;margin-top:.5rem;}

/* Card Body */
.ec-body{padding:1.25rem 1.5rem;}
.ec-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;margin-bottom:1.25rem;}
.ec-stat{text-align:center;padding:.6rem .5rem;background:#F8FAFC;border-radius:12px;border:1px solid #F1F5F9;}
.ec-stat .sv{font-size:1.3rem;font-weight:800;line-height:1;margin-bottom:4px;}
.ec-stat .sl{font-size:.54rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#94A3B8;}

/* Progress Bar */
.ec-progress-label{display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;font-size:0.75rem;font-weight:700;}
.pct-bar{height:8px;background:#F1F5F9;border-radius:99px;overflow:hidden;}
.pct-fill{height:100%;border-radius:99px;transition:width 1s ease-in-out;}

.ec-action{display:flex;align-items:center;gap:.4rem;margin-top:1.25rem;font-size:.78rem;font-weight:700;color:var(--c-indigo);}

/* Empty State */
.empty-state{text-align:center;padding:5rem 2rem;background:#fff;border:2px dashed #E2E8F0;border-radius:20px;}
.empty-state i{font-size:3.5rem;color:#CBD5E1;margin-bottom:1.2rem;}

@media(max-width:600px){.eskul-grid{grid-template-columns:1fr;}}
</style>

<div class="lap-wrap">
    <div class="page-header">
        <h1><i class="bi bi-patch-check-fill me-2" style="color:var(--c-indigo)"></i>Laporan Penilaian Sikap</h1>
        <p>Tahun Ajaran <strong>{{ $schoolYear->name ?? '2025/2026' }}</strong> &nbsp;·&nbsp; Pilih eskul untuk melihat ringkasan nilai siswa</p>
    </div>

    @if(count($eskulSummaries) > 0)
    <div class="eskul-grid">
        @foreach($eskulSummaries as $item)
        @php
            $eskul = $item['eskul'];
            $pct = $item['completion_pct']; // % siswa yang sudah dinilai periode ini
            $avg = number_format($item['avg_score'], 1); // Rata-rata nilai eskul
            
            // Warna dinamis berdasarkan persentase pengisian
            $color = $pct >= 100 ? 'var(--c-green)' : ($pct >= 50 ? 'var(--c-indigo)' : '#D97706');
            
            $gradients = [
                'linear-gradient(135deg,#4F46E5,#4338CA)',
                'linear-gradient(135deg,#7C3AED,#6D28D9)',
                'linear-gradient(135deg,#2563EB,#1D4ED8)',
                'linear-gradient(135deg,#0F172A,#1E293B)',
            ];
            $grad = $gradients[$loop->index % count($gradients)];
        @endphp

        <a href="{{ route('pembina.penilaian.laporan', $eskul->id) }}" class="eskul-card">
            <div class="ec-head" style="background:{{ $grad }}">
                <div class="ec-icon"><i class="bi bi-star-fill"></i></div>
                <div class="ec-name">{{ $eskul->name }}</div>
                <div class="ec-badge"><i class="bi bi-people-fill"></i> {{ $item['total_members'] }} Anggota</div>
            </div>
            
            <div class="ec-body">
                <div class="ec-stats">
                    <div class="ec-stat">
                        <div class="sv" style="color:var(--c-indigo)">{{ $item['total_members'] }}</div>
                        <div class="sl">Siswa</div>
                    </div>
                    <div class="ec-stat">
                        <div class="sv" style="color:var(--c-amber)">{{ $avg }}</div>
                        <div class="sl">Rata-rata</div>
                    </div>
                    <div class="ec-stat">
                        <div class="sv" style="color:var(--c-green)">{{ $item['assessed_count'] }}</div>
                        <div class="sl">Dinilai</div>
                    </div>
                </div>

                <div class="ec-progress-wrap">
                    <div class="ec-progress-label">
                        <span class="text-muted">Progres Penilaian Periode Ini</span>
                        <span style="color:{{ $color }}">{{ $pct }}%</span>
                    </div>
                    <div class="pct-bar">
                        <div class="pct-fill" style="width:{{ $pct }}%; background:{{ $color }}"></div>
                    </div>
                </div>

                <div class="ec-action">
                    <span>Lihat Statistik & Radar Chart</span>
                    <i class="bi bi-arrow-right"></i>
                </div>
            </div>
        </a>
        @endforeach
    </div>
    @else
    <div class="empty-state">
        <i class="bi bi-clipboard-x"></i>
        <h3>Belum Ada Data</h3>
        <p>Anda belum memiliki akses ke laporan eskul manapun.</p>
    </div>
    @endif
</div>
@endsection