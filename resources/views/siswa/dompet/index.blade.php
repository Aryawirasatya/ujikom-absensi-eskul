@extends('layouts.app')

@section('content')
<div class="container py-5 px-lg-5" id="dompet-siswa-wrapper">

    {{-- 
    |--------------------------------------------------------------------------
    | HEADER SECTION: PERSONAL IDENTITY & BALANCE
    | Desain menggunakan rasio 60:40 untuk keseimbangan visual.
    |--------------------------------------------------------------------------
    --}}
    <div class="hero-wallet card border-0 rounded-4 shadow-sm mb-5">
    <div class="card-body p-4 p-lg-5 position-relative overflow-hidden">
        <div class="row align-items-center position-relative z-2">
            
            <div class="col-lg-7 border-end-lg border-white border-opacity-10">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar-placeholder me-3">
                        <i class="bi bi-person-fill fs-2 text-white opacity-75"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold text-white mb-0 tracking-tight">{{ auth()->user()->name }}</h4>
                        <p class="text-white text-opacity-50 small mb-0 font-monospace">NISN: {{ auth()->user()->nisn }}</p>
                    </div>
                </div>
                
            </div>

            <div class="col-lg-5 mt-4 mt-lg-0 ps-lg-5">
                <div class="balance-container">
                    <span class="text-white text-opacity-50 small fw-bold text-uppercase tracking-widest d-block mb-1">
                        Saldo Tersedia
                    </span>
                    <div class="d-flex align-items-baseline">
                        <h1 class="display-3 fw-black text-white mb-0 tracking-tighter">
                            {{ number_format($currentPoints) }}
                        </h1>
                        <span class="ms-2 fw-bold text-white text-opacity-75">PTS</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-pattern"></div>
        <div class="deco-blob"></div>
    </div>
</div>

    {{-- 
    |--------------------------------------------------------------------------
    | TAB NAVIGATION
    |--------------------------------------------------------------------------
    --}}
    <div class="nav-container mb-4">
        <ul class="nav nav-tabs nav-justified border-0 custom-tabs" id="dompetTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#history" type="button">
                    <i class="bi bi-clock-history me-2"></i>Riwayat
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#shop" type="button">
                    <i class="bi bi-shop me-2"></i>Marketplace
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#inventory" type="button">
                    <i class="bi bi-box-seam me-2"></i>Inventory
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content pt-3">

        {{-- 
        |--------------------------------------------------------------------------
        | TAB 1: HISTORY (List Compact)
        |--------------------------------------------------------------------------
        --}}
        <div class="tab-pane fade show active" id="history">
            <div class="card border-0 rounded-4 shadow-xs bg-white overflow-hidden">
                <div class="list-group list-group-flush">
                    @forelse($ledgers as $l)
                        <div class="list-group-item p-3 p-lg-4 border-bottom-subtle transition-all hover-bg-light">
                            <div class="d-flex align-items-center justify-content-between w-100">
                                
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="transaction-icon-v2 {{ $l->transaction_type == 'EARN' ? 'earn-v2' : 'spend-v2' }}">
                                            <i class="bi {{ $l->transaction_type == 'EARN' ? 'bi-arrow-up-right-circle-fill' : 'bi-arrow-down-left-circle-fill' }}"></i>
                                        </div>
                                    </div>

                                    <div>
                                        <h6 class="fw-bold text-dark mb-0 tracking-tight">
                                            {{ $l->description }}
                                        </h6>
                                        
                                        @if($l->attendance && $l->attendance->activity)
                                            <div class="mt-1">
                                                <span class="badge bg-light text-secondary fw-medium border px-2 py-1" style="font-size: 0.7rem;">
                                                    <i class="bi bi-geo-alt-fill me-1"></i>
                                                    SESI: {{ $l->attendance->activity->title }}
                                                </span>
                                            </div>
                                        @endif

                                        <div class="d-flex align-items-center gap-2 mt-2">
                                            <small class="text-muted small">
                                                <i class="bi bi-calendar3 me-1"></i> {{ $l->created_at->translatedFormat('d M Y') }}
                                            </small>
                                            <span class="text-secondary opacity-25">|</span>
                                            <small class="text-muted small">
                                                <i class="bi bi-clock me-1"></i> {{ $l->created_at->format('H:i') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex-shrink-0 text-end ps-3">
                                    <div class="point-badge-v2 {{ $l->transaction_type == 'EARN' ? 'plus' : 'minus' }}">
                                        <span class="fw-black fs-5 d-block">
                                            {{ $l->transaction_type == 'EARN' ? '+' : '-' }}{{ number_format($l->amount) }}
                                        </span>
                                        <small class="pts-label">PTS</small>
                                    </div>
                                </div>

                            </div>
                        </div>
                   @empty
                    <div class="text-center py-5">
                        <div class="empty-state-icon-wrapper mb-3">
                            <i class="bi bi-pigeon text-secondary opacity-25" style="font-size: 5rem;"></i>
                        </div>
                        <p class="text-muted fw-medium tracking-tight">Belum ada aktivitas poin tercatat.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- 
        |--------------------------------------------------------------------------
        | TAB 2: MARKETPLACE (Sleek Compact Cards)
        |--------------------------------------------------------------------------
        --}}
        <div class="tab-pane fade" id="shop">
            <div class="row g-4">
                @foreach($items as $item)
                    @php
                        $owned = $myTokens->where('item_id', $item->id)->count();
                        $limitReached = $item->stock_limit && $owned >= $item->stock_limit;
                        $notEnough = $currentPoints < $item->point_cost;
                    @endphp
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card h-100 border rounded-4 shop-card transition-all">
                            <div class="card-body p-4 d-flex flex-column">
                                <div class="mb-3">
                                    <span class="type-label">{{ str_replace('_',' ',$item->token_type) }}</span>
                                </div>

                                <h5 class="fw-bold text-dark mb-2">{{ $item->item_name }}</h5>
                                <p class="text-muted small flex-grow-1">
                                    @switch($item->token_type)
                                        @case('late_forgiveness') Menghapus denda keterlambatan hingga {{ $item->effect_value }} menit. @break
                                        @case('forget_checkout') Melengkapi data absen jika lupa melakukan scan pulang. @break
                                        @case('free_alpha') Mengubah 1 status Alpha menjadi Izin secara otomatis. @break
                                        @default Dapatkan keuntungan khusus untuk kedisiplinan Anda.
                                    @endswitch
                                </p>

                                <div class="price-pill d-flex align-items-center justify-content-between mb-3">
                                    <div class="price-value">
                                        <span class="amount">{{ number_format($item->point_cost) }}</span>
                                        <span class="pts">PTS</span>
                                    </div>
                                    <div class="owned-info">
                                        <i class="bi bi-wallet2 me-1"></i> {{ $owned }}/{{ $item->stock_limit ?? '∞' }}
                                    </div>
                                </div>

                                <form action="{{ route('siswa.dompet.buy') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="item_id" value="{{ $item->id }}">
                                    <button class="btn btn-buy-sleek w-100 py-2 fw-bold" {{ ($limitReached || $notEnough) ? 'disabled' : '' }}>
                                        @if($limitReached) Limit Habis
                                        @elseif($notEnough) Poin Kurang
                                        @else Tukar Sekarang @endif
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- 
        |--------------------------------------------------------------------------
        | TAB 3: INVENTORY (Mini Badge Style)
        |--------------------------------------------------------------------------
        --}}
        <div class="tab-pane fade" id="inventory">
            <div class="row g-3">
                @forelse($myTokens as $token)
                    <div class="col-12 col-md-6">
                        <div class="card border rounded-4 inventory-mini-card shadow-xs">
                            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="token-icon me-3">
                                        <i class="bi bi-ticket-perforated text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0">{{ $token->item->item_name }}</h6>
                                        <small class="text-muted">{{ $token->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    @if($token->status == 'AVAILABLE')
                                        <span class="badge bg-success-subtle text-success border-success-subtle px-3 py-2 rounded-pill fw-bold">READY</span>
                                    @else
                                        <span class="badge bg-light text-muted px-3 py-2 rounded-pill fw-bold">USED</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5 border border-dashed rounded-4">
                        <p class="text-muted fw-medium mb-0">Inventory Anda masih kosong.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>

<style>
    /* |--------------------------------------------------------------------------
    | DESIGN SYSTEM
    |--------------------------------------------------------------------------
    */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');

    #dompet-siswa-wrapper {
        font-family: 'Inter', sans-serif;
        background-color: #f8fafc;
    }

    .fw-black { font-weight: 800; }
    .tracking-tight { letter-spacing: -0.03em; }
    .tracking-tighter { letter-spacing: -0.05em; }
    .tracking-wider { letter-spacing: 0.05em; }
    .tracking-widest { letter-spacing: 0.1em; }

    /* HERO WALLET */
    .hero-wallet {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        min-height: 200px;
    }
    .avatar-placeholder {
        width: 54px; height: 54px;
        background: rgba(255,255,255,0.1);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
    }
    .border-end-lg { border-right: 1px solid rgba(255,255,255,0.1); }
    @media (max-width: 991px) { .border-end-lg { border-right: none; } }

    .bg-pattern {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background-image: radial-gradient(rgba(255,255,255,0.05) 1px, transparent 0);
        background-size: 24px 24px; z-index: 1;
    }
    .deco-blob {
        position: absolute; bottom: -50px; right: -20px;
        width: 180px; height: 180px;
        background: #3b82f6; filter: blur(80px); opacity: 0.2; z-index: 1;
    }

    /* TABS */
    .custom-tabs { gap: 10px; }
    .custom-tabs .nav-link {
        border: none !important;
        background: #fff;
        color: #64748b;
        font-weight: 600;
        border-radius: 12px !important;
        padding: 12px;
        transition: 0.2s;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .custom-tabs .nav-link.active {
        background: #0f172a !important;
        color: #fff !important;
        box-shadow: 0 4px 12px rgba(15,23,42,0.15) !important;
    }

    /* CARDS */
    .shadow-xs { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }
    .hover-bg-light:hover { background-color: #f8fafc; }
    
    .transaction-icon {
        width: 38px; height: 38px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.9rem;
    }
    .earn-icon { background: #dcfce7; color: #166534; }
    .spend-icon { background: #fee2e2; color: #991b1b; }

    /* SHOP CARDS */
    .shop-card {
        border-color: #e2e8f0 !important;
        transition: transform 0.2s;
    }
    .shop-card:hover { transform: translateY(-4px); border-color: #3b82f6 !important; }
    
    .type-label {
        font-size: 0.65rem; font-weight: 800; text-transform: uppercase;
        letter-spacing: 1px; color: #3b82f6; background: #eff6ff;
        padding: 4px 10px; border-radius: 6px;
    }

    .price-pill {
        background: #f8fafc; padding: 10px 15px; border-radius: 12px;
    }
    .price-value .amount { font-size: 1.5rem; font-weight: 800; color: #0f172a; }
    .price-value .pts { font-size: 0.75rem; font-weight: 700; color: #64748b; margin-left: 4px; }
    .owned-info { font-size: 0.75rem; font-weight: 600; color: #94a3b8; }

    .btn-buy-sleek {
        background: #0f172a; color: #fff; border-radius: 10px; border: none;
        transition: 0.2s;
    }
    .btn-buy-sleek:hover { background: #1e293b; color: #fff; }
    .btn-buy-sleek:disabled { background: #e2e8f0; color: #94a3b8; cursor: not-allowed; }

    /* INVENTORY */
    .inventory-mini-card { background: #fff; border-color: #f1f5f9 !important; }
    .token-icon {
        width: 44px; height: 44px; background: #eff6ff;
        border-radius: 10px; display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem;
    }

    /* UTILITIES */
    .border-bottom-subtle { border-bottom: 1px solid #f1f5f9; }
    .border-dashed { border: 2px dashed #e2e8f0 !important; }
    .bg-success-subtle { background-color: #ecfdf5 !important; }
    .transaction-icon-v2 {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
    }
    
    .earn-v2 { background-color: #f0fdf4; color: #16a34a; }
    .spend-v2 { background-color: #fef2f2; color: #dc2626; }

    .point-badge-v2 {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        line-height: 1;
    }
    
    .point-badge-v2.plus { color: #16a34a; }
    .point-badge-v2.minus { color: #dc2626; }
    
    .point-badge-v2 .pts-label {
        font-size: 0.65rem;
        font-weight: 800;
        letter-spacing: 1px;
        margin-top: 2px;
        opacity: 0.7;
    }

    .hover-bg-light:hover {
        background-color: #fafafa;
    }
    
    .tracking-tight { letter-spacing: -0.02em; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Persistent Tab State
    const activeTab = localStorage.getItem('dompet_siswa_tab');
    if (activeTab) {
        const tabEl = document.querySelector(`button[data-bs-target="${activeTab}"]`);
        if (tabEl) bootstrap.Tab.getOrCreateInstance(tabEl).show();
    }

    document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(btn => {
        btn.addEventListener('shown.bs.tab', e => {
            localStorage.setItem('dompet_siswa_tab', e.target.getAttribute('data-bs-target'));
        });
    });
});
</script>
@endsection