@extends('layouts.app')

@section('title', 'Laporan Pembina')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600&display=swap');
:root{--c-indigo:#4F46E5;--c-indigo-lt:#EEF2FF;--c-green:#16A34A;--c-green-lt:#DCFCE7;--c-red:#DC2626;--c-red-lt:#FEE2E2;--c-amber:#D97706;--c-amber-lt:#FEF3C7;--c-blue:#2563EB;--c-blue-lt:#DBEAFE;--c-violet:#7C3AED;--c-violet-lt:#EDE9FE;--c-slate:#64748B;--c-slate-lt:#F1F5F9;}
*{box-sizing:border-box;}
.lap-wrap *{font-family:'Inter',sans-serif;}
h1,.kv{font-family:'Sora',sans-serif;}
.lap-wrap{max-width:1200px;margin:0 auto;padding:2rem 1.5rem 6rem;}

.page-header{background:#fff;border-radius:20px;border:1px solid #E2E8F0;padding:1.75rem 2rem;margin-bottom:1.5rem;position:relative;overflow:hidden;}
.page-header::before{content:'';position:absolute;inset:0 0 auto 0;height:3px;background:linear-gradient(90deg,var(--c-indigo),var(--c-green));}
.page-header h1{font-size:1.4rem;font-weight:800;color:#0F172A;margin:0;}
.page-header p{color:var(--c-slate);font-size:.82rem;margin:.3rem 0 0;}

.eskul-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:1.25rem;}
.eskul-card{background:#fff;border:1px solid #E2E8F0;border-radius:18px;overflow:hidden;transition:box-shadow .2s,transform .2s;text-decoration:none;color:inherit;display:block;}
.eskul-card:hover{box-shadow:0 8px 30px rgba(0,0,0,.1);transform:translateY(-3px);border-color:var(--c-indigo);}
.ec-head{padding:1.4rem 1.5rem 1rem;background:linear-gradient(135deg,#4F46E5,#4338CA);color:#fff;position:relative;overflow:hidden;}
.ec-head::after{content:'';position:absolute;width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,.1);top:-20px;right:-20px;}
.ec-head .ec-icon{font-size:1.6rem;opacity:.8;margin-bottom:.5rem;}
.ec-head .ec-name{font-family:'Sora',sans-serif;font-size:1.1rem;font-weight:800;line-height:1.2;}
.ec-head .ec-badge{display:inline-flex;align-items:center;gap:.3rem;font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;background:rgba(255,255,255,.2);padding:.2rem .55rem;border-radius:99px;margin-top:.5rem;}
.ec-body{padding:1.25rem 1.5rem;}
.ec-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;margin-bottom:1rem;}
.ec-stat{text-align:center;padding:.6rem .5rem;background:#F8FAFC;border-radius:10px;}
.ec-stat .sv{font-family:'Sora',sans-serif;font-size:1.4rem;font-weight:800;line-height:1;}
.ec-stat .sl{font-size:.56rem;font-weight:700;text-transform:uppercase;letter-spacing:1.2px;color:#94A3B8;margin-top:.15rem;}
.ec-pct{display:flex;align-items:center;gap:.75rem;}
.pct-bar{height:7px;background:#F1F5F9;border-radius:99px;overflow:hidden;flex:1;}
.pct-fill{height:100%;border-radius:99px;}
.ec-action{display:flex;align-items:center;gap:.4rem;margin-top:1rem;font-size:.74rem;font-weight:700;color:var(--c-indigo);}

.empty-state{text-align:center;padding:4rem 2rem;background:#fff;border:1px solid #E2E8F0;border-radius:18px;}
.empty-state i{font-size:3rem;color:#CBD5E1;margin-bottom:1rem;}
.empty-state h3{font-family:'Sora',sans-serif;font-size:1.1rem;font-weight:700;color:#0F172A;margin-bottom:.5rem;}
.empty-state p{color:var(--c-slate);font-size:.82rem;}

@media(max-width:600px){.eskul-grid{grid-template-columns:1fr;}}
</style>

<div class="lap-wrap">

    <div class="page-header">
        <h1><i class="bi bi-bar-chart me-2" style="color:var(--c-indigo)"></i>Laporan Kehadiran</h1>
        <p>Tahun Ajaran <strong>{{ $schoolYear->name }}</strong> &nbsp;·&nbsp; Pilih ekstrakurikuler untuk melihat laporan detail</p>
    </div>

    @if(count($eskulSummaries) > 0)
    <div class="eskul-grid">
        @foreach($eskulSummaries as $item)
        @php
            $eskul  = $item['eskul'];
            $pct    = $item['pctKeseluruhan'];
            $pctClr = $pct >= 75 ? '#16A34A' : ($pct >= 50 ? '#D97706' : '#DC2626');
            $gradients = [
                'linear-gradient(135deg,#4F46E5,#4338CA)',
                'linear-gradient(135deg,#16A34A,#15803D)',
                'linear-gradient(135deg,#D97706,#B45309)',
                'linear-gradient(135deg,#DC2626,#B91C1C)',
                'linear-gradient(135deg,#7C3AED,#6D28D9)',
                'linear-gradient(135deg,#2563EB,#1D4ED8)',
            ];
            $grad = $gradients[$loop->index % count($gradients)];
        @endphp
        <a href="{{ route('pembina.laporan.show', $eskul->id) }}" class="eskul-card">
            <div class="ec-head" style="background:{{ $grad }}">
                <div class="ec-icon"><i class="bi bi-trophy"></i></div>
                <div class="ec-name">{{ $eskul->name }}</div>
                <div class="ec-badge"><i class="bi bi-people"></i> {{ $item['totalAnggota'] }} anggota</div>
            </div>
            <div class="ec-body">
                <div class="ec-stats">
                    <div class="ec-stat">
                        <div class="sv" style="color:var(--c-indigo)">{{ $item['totalKegiatan'] }}</div>
                        <div class="sl">Kegiatan</div>
                    </div>
                    <div class="ec-stat">
                        <div class="sv" style="color:{{ $pctClr }}">{{ $pct }}%</div>
                        <div class="sl">Hadir</div>
                    </div>
                    <div class="ec-stat">
                        <div class="sv" style="color:var(--c-red)">{{ $item['alphaCount'] }}</div>
                        <div class="sl">Alpha ≥3×</div>
                    </div>
                </div>
                <div class="ec-pct">
                    <div class="pct-bar">
                        <div class="pct-fill" style="width:{{ $pct }}%;background:{{ $pctClr }}"></div>
                    </div>
                    <span style="font-size:.8rem;font-weight:800;color:{{ $pctClr }};white-space:nowrap;min-width:42px;text-align:right">{{ $pct }}%</span>
                </div>
                <div class="ec-action">
                    <span>Lihat Laporan Detail</span>
                    <i class="bi bi-arrow-right" style="font-size:.65rem"></i>
                </div>
            </div>
        </a>
        @endforeach
    </div>
    @else
    <div class="empty-state">
        <div><i class="fas fa-folder-open"></i></div>
        <h3>Belum Ada Eskul</h3>
        <p>Anda belum ditugaskan sebagai pembina di ekstrakurikuler manapun.</p>
    </div>
    @endif

</div>
@endsection