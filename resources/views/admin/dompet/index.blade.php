@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 px-lg-5">

    {{-- HEADER --}}
    <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
        <div>
            <h3 class="fw-bolder mb-1 text-dark tracking-tight">
                <i class="bi bi-wallet2 me-2 text-primary"></i>Dompet Integritas
            </h3>
            <p class="text-secondary mb-0">Manajemen aturan poin otomatis dan ekosistem marketplace.</p>
        </div>
    </div>

    {{-- NAV TABS (Modern Underline Style) --}}
    <ul class="nav nav-custom mb-5" id="dompetTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="leaderboard-tab" data-bs-toggle="tab" data-bs-target="#leaderboard" type="button" role="tab">
                <i class="bi bi-bar-chart me-2"></i>Leaderboard
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="rules-tab" data-bs-toggle="tab" data-bs-target="#rules" type="button" role="tab">
                <i class="bi bi-diagram-3 me-2"></i>Rule Engine
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="shop-tab" data-bs-toggle="tab" data-bs-target="#shop" type="button" role="tab">
                <i class="bi bi-shop-window me-2"></i>Marketplace
            </button>
        </li>
    </ul>

    <div class="tab-content" id="dompetTabsContent">

        {{-- ========================================================= --}}
        {{-- TAB: LEADERBOARD --}}
        {{-- ========================================================= --}}
        <div class="tab-pane fade show active" id="leaderboard" role="tabpanel">
            <div class="row g-5">

                {{-- TOP 10 --}}
                <div class="col-12 col-lg-6">
                    <div class="d-flex align-items-center mb-4">
                        <div class="icon-square bg-success-subtle text-success me-3">
                            <i class="bi bi-arrow-up-right"></i>
                        </div>
                        <h5 class="fw-bold mb-0">Siswa Teladan</h5>
                    </div>

                    <div class="card border rounded-4 shadow-none">
                        <div class="card-body p-0">
                            <table class="table table-borderless table-hover align-middle mb-0 custom-table">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Peringkat</th>
                                        <th class="text-end pe-4">Total Poin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topStudents as $i => $s)
                                        @php $points = $s->points ?? 0; @endphp
                                        <tr>
                                            <td class="ps-4 py-3">
                                                <div class="d-flex align-items-center">
                                                    <span class="rank-number me-3 text-secondary">{{ $i + 1 }}</span>
                                                    <span class="fw-semibold text-dark">{{ $s->name }}</span>
                                                </div>
                                            </td>
                                            <td class="text-end pe-4 py-3">
                                                <span class="fw-bold {{ $points >= 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ $points > 0 ? '+' : '' }}{{ number_format($points) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center py-5 text-secondary">
                                                <i class="bi bi-inbox fs-4 d-block mb-2"></i> Belum ada data
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- BOTTOM 10 --}}
                <div class="col-12 col-lg-6">
                    <div class="d-flex align-items-center mb-4">
                        <div class="icon-square bg-danger-subtle text-danger me-3">
                            <i class="bi bi-arrow-down-right"></i>
                        </div>
                        <h5 class="fw-bold mb-0">Perlu Perhatian</h5>
                    </div>

                    <div class="card border rounded-4 shadow-none">
                        <div class="card-body p-0">
                            <table class="table table-borderless table-hover align-middle mb-0 custom-table">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Siswa</th>
                                        <th class="text-end pe-4">Total Poin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($bottomStudents as $s)
                                        @php $points = $s->points ?? 0; @endphp
                                        <tr>
                                            <td class="ps-4 py-3 fw-medium text-dark">{{ $s->name }}</td>
                                            <td class="text-end pe-4 py-3">
                                                <span class="fw-bold {{ $points >= 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ $points > 0 ? '+' : '' }}{{ number_format($points) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center py-5 text-secondary">
                                                <i class="bi bi-inbox fs-4 d-block mb-2"></i> Belum ada data
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- TAB: RULE ENGINE --}}
        {{-- ========================================================= --}}
        <div class="tab-pane fade" id="rules" role="tabpanel">

            @php
                $fieldMap = [
                    'late_minutes' => 'Menit Terlambat',
                    'checkin_time' => 'Jam Datang',
                    'checkout_time' => 'Jam Pulang',
                    'final_status' => 'Status Absen'
                ];
                $operatorMap = [
                    '=' => '=', '<' => '<', '<=' => '≤', '>' => '>', '>=' => '≥', 'BETWEEN' => 'Diantara'
                ];
            @endphp

            {{-- BUILDER FORM --}}
             <div class="card border-0 rounded-4 mb-5 shadow-sm" style="background: #ffffff; ring: 1px solid rgba(0,0,0,0.05);">
                    <form action="{{ route('admin.dompet.rules.store') }}" method="POST" id="ruleForm">
                        @csrf
                        <div class="row g-4">
                            <div class="col-14">
                                            <div class="card-header bg-dark px-4 py-3">
                                                <h6 class="text-white mb-0 fw-bold small text-uppercase ls-1">Konfigurasi Aturan Baru</h6>
                                            </div>
                                            <div class="card-body p-4">
                                                <form action="{{ route('admin.dompet.rules.store') }}" method="POST">
                                                    @csrf
                                                    
                                                    <div class="mb-4">
                                                        <label class="form-label text-muted small fw-bolder tracking-wide">NAMA ATURAN</label>
                                                        <input type="text" name="rule_name" class="form-control minimal-input shadow-none" placeholder="Contoh: Bonus Kehadiran Pagi..." required>
                                                    </div>

                                                    <label class="form-label text-muted small fw-bolder tracking-wide text-uppercase">Logika Formula</label>
                                                    
                                                    <div class="d-flex flex-wrap flex-lg-nowrap align-items-center gap-2 p-3 rounded-4 bg-light border">
                                                        
                                                        <div class="d-flex align-items-center flex-shrink-0">
                                                            <span class="badge bg-white text-primary border border-primary-subtle px-3 py-2 rounded-3 fw-bold shadow-xs">JIKA</span>
                                                        </div>

                                                        <div class="d-flex align-items-center gap-2">
                                                            <select name="condition_field" id="fieldSelect" class="form-select border-0 bg-white shadow-sm fw-bold rounded-3 py-2" style="min-width: 160px; cursor: pointer;">
                                                                <option value="late_minutes">Menit Terlambat</option>
                                                                <option value="checkin_time">Jam Kedatangan</option>
                                                                <option value="checkout_time">Jam Pulang</option>
                                                                <option value="final_status">Status Absensi</option>
                                                            </select>

                                                            <select name="condition_operator" id="operatorSelect" class="form-select border-0 bg-white shadow-sm fw-bold text-primary rounded-3 py-2 text-center" style="width: 100px; cursor: pointer;">
                                                                <option value="=">=</option>
                                                                <option value="<">&lt;</option>
                                                                <option value="<=">&le;</option>
                                                                <option value=">">&gt;</option>
                                                                <option value=">=">&ge;</option>
                                                                <option value="BETWEEN">ANTARA</option>
                                                            </select>
                                                        </div>

                                                        <div class="d-flex align-items-center gap-2 flex-grow-1">
                                                            <div id="valueContainer" class="d-inline-block">
                                                                </div>
                                                            <input type="text" name="condition_value_2" id="valueInput2" class="form-control d-none border-0 bg-white fw-bold text-center shadow-sm rounded-3 py-2" style="width: 110px;" placeholder="Nilai ke-2(MENIT)">
                                                        </div>

                                                        <div class="d-flex align-items-center flex-shrink-0">
                                                            <span class="badge bg-white text-success border border-success-subtle px-3 py-2 rounded-3 fw-bold shadow-xs">MAKA</span>
                                                        </div>

                                                        <div class="d-flex align-items-center gap-2">
                                                            <div class="input-group bg-white rounded-3 shadow-sm overflow-hidden" style="width: 100px;">
                                                                <input type="number" name="point_modifier" class="form-control border-0 text-center fw-bolder fs-6" placeholder="+ / - PTS" required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 text-end pt-3">
                                                        <button type="submit" class="btn btn-dark px-5 py-3 fw-bold rounded-pill shadow transition-hover">
                                                            <i class="bi bi-check2-circle me-2"></i>Aktifkan Aturan Baru
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                            </div>
                        </div>
                    </form>
            </div>
            {{-- LIST RULES --}}
            <h5 class="fw-bold mb-4">Daftar Aturan Aktif</h5>
            <div class="card border shadow-none rounded-4">
                <div class="table-responsive">
                    <table class="table table-borderless table-hover align-middle mb-0 custom-table">
                        <thead class="table-light text-secondary small">
                            <tr>
                                <th class="ps-4 py-3">NAMA ATURAN</th>
                                <th class="py-3">KONDISI</th>
                                <th class="py-3 text-center">EFEK POIN</th>
                                <th class="text-end pe-4 py-3">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rules as $r)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <span class="fw-semibold text-dark d-block">{{ $r->rule_name }}</span>
                                    </td>
                                    <td class="py-3">
                                        <div class="d-inline-flex align-items-center bg-light rounded-pill px-3 py-1 text-secondary small">
                                            <span class="text-dark fw-medium">{{ $fieldMap[$r->condition_field] ?? $r->condition_field }}</span>
                                            <span class="mx-2">{{ $operatorMap[$r->condition_operator] ?? $r->condition_operator }}</span>
                                            <span class="text-dark fw-bold">{{ $r->condition_value }}</span>
                                            @if($r->condition_operator === 'BETWEEN')
                                                <span class="mx-1">&</span>
                                                <span class="text-dark fw-bold">{{ $r->condition_value_2 }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center py-3">
                                        <span class="fw-bold {{ $r->point_modifier >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $r->point_modifier > 0 ? '+' : '' }}{{ $r->point_modifier }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4 py-3">
                                        <form action="{{ route('admin.dompet.rules.destroy', $r->id) }}" method="POST" onsubmit="return confirm('Hapus aturan ini?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light text-secondary hover-danger border-0 rounded-circle" style="width: 32px; height: 32px;">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-4 d-block mb-2"></i> Belum ada aturan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- ========================================================= --}}
        {{-- TAB: MARKETPLACE --}}
        {{-- ========================================================= --}}
        <div class="tab-pane fade" id="shop" role="tabpanel">

            {{-- ADD ITEM FORM --}}
            <div class="card border-0 rounded-4 bg-white ring-1 shadow-sm mb-5 overflow-hidden">
            <div class="card-header bg-dark px-4 py-3 d-flex align-items-center justify-content-between">
                <h6 class="text-white mb-0 fw-bold small text-uppercase ls-1">Registrasi Item Toko Baru</h6>
                <i class="bi bi-cart-plus text-secondary"></i>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.dompet.items.store') }}" method="POST" id="marketplaceForm">
                    @csrf
                    
                    <div class="d-flex flex-wrap flex-lg-nowrap align-items-end gap-3 p-3 rounded-4 bg-light border">
                        
                        <div class="flex-grow-1" style="min-width: 200px;">
                            <label class="form-label text-muted small fw-bolder tracking-wide text-uppercase mb-2 ps-1">Identitas Produk</label>
                            <div class="input-group bg-white rounded-3 shadow-xs border-0 overflow-hidden">
                                <span class="input-group-text border-0 bg-white pe-0"><i class="bi bi-tag text-primary"></i></span>
                                <input type="text" name="item_name" class="form-control border-0 py-2 fw-bold text-dark shadow-none" placeholder="Nama voucher/item..." required>
                            </div>
                        </div>

                        <div style="min-width: 170px;">
                            <label class="form-label text-muted small fw-bolder tracking-wide text-uppercase mb-2 ps-1">Kategori</label>
                            <select name="token_type" id="tokenType" class="form-select border-0 bg-white shadow-xs fw-bold rounded-3 py-2 px-3 text-dark shadow-none" style="cursor: pointer;">
                                <option value="late_forgiveness">Bebas Telat</option>
                                <option value="free_alpha">Bebas Alpha</option>
                                <option value="forget_checkout">bebas Checkout</option>
                            </select>
                        </div>

                        <div id="effectWrapper" style="width: 110px;">
                            <label class="form-label text-muted small fw-bolder tracking-wide text-uppercase mb-2 ps-1">Value</label>
                            <div class="input-group bg-white rounded-3 shadow-xs border-0 overflow-hidden">
                                <input type="number" name="effect_value" id="effectValue" class="form-control border-0 py-2 text-center fw-bold text-primary shadow-none" value="15">
                                <span class="input-group-text border-0 bg-white ps-0 fw-bold small text-muted">M</span>
                            </div>
                        </div>

                        <div style="width: 150px;">
                            <label class="form-label text-success small fw-bolder tracking-wide text-uppercase mb-2 ps-1">Harga Beli</label>
                            <div class="input-group bg-white rounded-3 shadow-xs border-0 overflow-hidden border-start border-success border-3">
                                <input type="number" name="point_cost" class="form-control border-0 py-2 text-center fw-black text-success shadow-none" placeholder="0" required>
                                <span class="input-group-text border-0 bg-white ps-0 fw-bold small text-muted">PTS</span>
                            </div>
                        </div>

                        <div style="width: 100px;">
                            <label class="form-label text-muted small fw-bolder tracking-wide text-uppercase mb-2 ps-1">Limit</label>
                            <input type="number" name="stock_limit" class="form-control border-0 bg-white shadow-xs py-2 text-center fw-bold rounded-3 shadow-none" placeholder="∞">
                        </div>

                        <div class="ms-lg-auto">
                            <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-bold shadow-sm transition-all hover-scale d-flex align-items-center">
                                <i class="bi bi-plus-circle-fill me-2"></i>simpan
                            </button>
                        </div>

                    </div>
                    <div class="mt-2 ps-1">
                        <small class="text-muted"><i class="bi bi-info-circle me-1"></i> Gunakan "Value" untuk menentukan menit pada tipe Bebas Telat.</small>
                    </div>
                </form>
            </div>
        </div>

            {{-- PRODUCTS LIST --}}
            <h5 class="fw-bold mb-4">Etalase Toko</h5>
                <div class="row g-4">
         @forelse($items as $item)
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card h-100 border-0 rounded-4 shadow-sm marketplace-card transition-all bg-white">
            <div class="card-body p-3 d-flex flex-column">
                
                {{-- 1. Header: Kategori & Aksi --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge-type">
                        <i class="bi bi-tag-fill me-1"></i>
                        {{ str_replace('_', ' ', $item->token_type) }}
                    </span>
                    
                    <form action="{{ route('admin.dompet.items.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus item ini?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-trash" title="Hapus">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </form>
                </div>

                {{-- 2. Konten Utama: Nama & Harga --}}
                <div class="mb-3">
                    <h6 class="fw-bold text-dark mb-2 tracking-tight line-clamp-2" style="height: 2.5rem; line-height: 1.25;">
                        {{ $item->item_name }}
                    </h6>
                    <div class="d-flex align-items-end">
                        <span class="fs-3 fw-black text-primary tracking-tighter">{{ number_format($item->point_cost) }}</span>
                        <span class="ms-1 pb-1 text-muted fw-bold" style="font-size: 0.7rem;">PTS</span>
                    </div>
                </div>

                {{-- 3. Info Spesifikasi (Compact Footer) --}}
                <div class="mt-auto pt-3 border-top border-light">
                    <div class="row g-2">
                        {{-- Kolom Limit --}}
                        <div class="col-6">
                            <div class="spec-pill">
                                <i class="bi bi-shield-check text-secondary"></i>
                                <div class="ms-2">
                                    <small class="d-block text-muted text-uppercase fw-bold" style="font-size: 0.55rem;">Limit</small>
                                    <span class="small fw-black text-dark">{{ $item->stock_limit ? $item->stock_limit.'x' : '∞' }}</span>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Kolom Efek (Dinamis: Menit vs Unit) --}}
                        <div class="col-6">
                            <div class="spec-pill">
                                <i class="bi bi-lightning-charge text-warning"></i>
                                <div class="ms-2">
                                    <small class="d-block text-muted text-uppercase fw-bold" style="font-size: 0.55rem;">Efek</small>
                                    <span class="small fw-black text-dark">
                                        @if($item->token_type === 'late_forgiveness')
                                            {{ $item->effect_value }}m
                                        @else
                                            {{ $item->effect_value ?? 1 }}x
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@empty
    <div class="col-12">
        <div class="card border-0 rounded-4 bg-light py-5 text-center shadow-none">
            <div class="card-body">
                <i class="bi bi-layers text-secondary opacity-25 display-4 mb-3"></i>
                <h6 class="fw-bold text-dark">Gudang Kosong</h6>
                <p class="text-muted small px-5">Belum ada item marketplace yang dibuat untuk siswa.</p>
            </div>
        </div>
    </div>
@endforelse
        </div>
        </div>

    </div>
</div>

{{-- STYLES --}}
<style>
   @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

    :root {
        --bs-font-sans-serif: 'Inter', sans-serif;
    }

    body { background-color: #fbfbfb; }
    /* Tambahan agar input di valueContainer tetap konsisten sizenya */
    #valueContainer .form-control, 
    #valueContainer .form-select {
        border: 0;
        background: white;
        font-weight: 700;
        border-radius: 8px;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
        min-width: 120px;
    }
    
    .ls-1 { letter-spacing: 1px; }
    .ring-1 { box-shadow: 0 0 0 1px rgba(0,0,0,0.05); }
    .shadow-xs { box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .marketplace-card {
        border: 1px solid #f1f5f9 !important;
    }
    
    .marketplace-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important;
        border-color: #3b82f6 !important;
    }

    .badge-type {
        font-size: 0.6rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        background: #f8fafc;
        padding: 4px 10px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
    }

    .btn-trash {
        background: transparent;
        border: none;
        color: #cbd5e1;
        transition: 0.2s;
        padding: 0;
    }
    .btn-trash:hover { color: #ef4444; }

    .spec-pill {
        display: flex;
        align-items: center;
        background: #fff;
        border: 1px solid #f1f5f9;
        padding: 6px 8px;
        border-radius: 10px;
    }

    .fw-black { font-weight: 900; }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .minimal-input {
        background-color: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        transition: all 0.2s;
    }
    .minimal-input:focus {
        background-color: #fff;
        border-color: #0d6efd;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.05) !important;
    }

    .hover-scale:hover {
        transform: scale(1.02);
    }
    .tracking-tighter { letter-spacing: -0.05em; }
    .tracking-widest { letter-spacing: 0.1em; }
    .fw-black { font-weight: 800; }

    /* Inventory Card Enhancement */
    .inventory-card {
        border-color: #f0f0f0 !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .inventory-card:hover {
        border-color: #d1d1d1 !important;
        transform: translateY(-6px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.03) !important;
    }

    /* Category Dot Indicator */
    .category-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }

    /* Minimalist Delete Button */
    .btn-delete-circle {
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: transparent;
        border: 1px solid #eee;
        color: #aaa;
        transition: all 0.2s;
        padding: 0;
    }

    .btn-delete-circle:hover {
        background: #fff5f5;
        border-color: #feb2b2;
        color: #e53e3e;
    }

    /* Dashed Border for Specs Section */
    .border-top-dashed {
        border-top: 1px dashed #eee;
    }

    /* Empty State Icons */
    .icon-circle-bg {
        width: 60px;
        height: 60px;
        background: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 10px rgba(0,0,0,0.03);
    }

    .empty-state-container {
        border-width: 2px !important;
    }

    .bg-light-subtle {
        background-color: #fafafa !important;
    }

    /* Point Text Gradient or Flat Dark */
    .price-container .amount {
        color: #1a1a1a;
    }
        /* Typography & Layout */
    .tracking-tight { letter-spacing: -0.025em; }
    .ls-1 { letter-spacing: 0.05em; }
    .fw-black { font-weight: 800; }
    .ring-1 { box-shadow: 0 0 0 1px rgba(0,0,0,0.06); }
    .shadow-xs { box-shadow: 0 1px 3px rgba(0,0,0,0.05); }

    /* Custom Navigation */
    .nav-custom { display: flex; gap: 2.5rem; border-bottom: 1px solid #eee; }
    .nav-custom .nav-link { 
        padding: 1rem 0; color: #777; font-weight: 600; font-size: 0.9rem;
        background: none; border: none; border-bottom: 2px solid transparent; transition: 0.3s;
    }
    .nav-custom .nav-link.active { color: #0d6efd; border-bottom-color: #0d6efd; }

    /* Leaderboard Styles */
    .rank-badge {
        width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;
        border-radius: 8px; font-weight: 800; font-size: 0.85rem; background: #f1f5f9; color: #475569;
    }
    .rank-0 { background: #fef9c3; color: #854d0e; } /* Gold */
    .rank-1 { background: #f1f5f9; color: #475569; } /* Silver */
    .rank-2 { background: #ffedd5; color: #9a3412; } /* Bronze */
    
    .icon-square { width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; border-radius: 12px; font-size: 1.2rem; }

    /* Logic Builder */
    .logic-input { font-weight: 700; width: 130px !important; border-radius: 8px; text-align: center; height: 45px; }
    .logic-icon-box { width: 48px; height: 48px; background: #f8fafc; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; color: #0d6efd; border: 1px solid #e2e8f0; }
    .point-chip { padding: 0.5rem 1.2rem; border-radius: 50px; font-weight: 800; font-size: 0.95rem; min-width: 85px; text-align: center; }
    .point-chip.pos { background: #f0fdf4; color: #166534; }
    .point-chip.neg { background: #fef2f2; color: #991b1b; }
    .btn-icon-delete { color: #cbd5e1; transition: 0.2s; background: none; border: none; padding: 0; }
    .btn-icon-delete:hover { color: #ef4444; transform: scale(1.1); }

    /* Marketplace */
    .type-badge { font-size: 0.65rem; font-weight: 800; text-transform: uppercase; color: #64748b; background: #f1f5f9; padding: 0.35rem 0.8rem; border-radius: 6px; letter-spacing: 0.5px; }
    .price-amount { font-size: 2rem; font-weight: 900; letter-spacing: -1px; }
    .price-unit { font-size: 0.7rem; font-weight: 700; color: #94a3b8; margin-left: 2px; }
    .product-hover { transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
    .product-hover:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0,0,0,0.06) !important; }
    .btn-delete-link { background: none; border: none; color: #e2e8f0; font-size: 1.2rem; transition: 0.2s; }
    .btn-delete-link:hover { color: #f87171; }

    .form-control-lg, .form-select-lg {
        border-radius: 12px;
    }
    
    .shadow-xs {
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    /* Modernizing select arrow */
    .form-select {
        background-position: right 0.75rem center;
        background-size: 14px 10px;
    }

    .fw-black {
        font-weight: 800;
    }

    .italic {
        font-style: italic;
    }

    /* Subtle border-start on Logic Box */
    .logic-builder-container {
        border-left: 5px solid #0d6efd !important;
    }

    .transition-hover {
        transition: all 0.3s ease;
    }

    .transition-hover:hover {
        transform: scale(1.02);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    .fw-black { font-weight: 800; }
    .ls-1 { letter-spacing: 0.5px; }
    
    #marketplaceForm .form-control::placeholder {
        color: #cbd5e0;
        font-weight: 400;
        font-size: 0.85rem;
    }

    #marketplaceForm .input-group-text {
        font-size: 0.75rem;
    }

    .hover-scale {
        transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .hover-scale:hover {
        transform: scale(1.03);
    }

    /* Minimal shadow-xs untuk kedalaman input */
    .shadow-xs {
        box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
    }

    /* Warna border-start untuk Harga sebagai aksen visual */
    .border-success {
        border-color: #10b981 !important;
    }
</style>

{{-- SCRIPT --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Tab Persistence
    const triggerTabList = document.querySelectorAll('#dompetTabs button');
    triggerTabList.forEach(triggerEl => {
        const tabTrigger = new bootstrap.Tab(triggerEl);
        triggerEl.addEventListener('click', e => {
            e.preventDefault();
            tabTrigger.show();
            localStorage.setItem('active_dompet_tab', e.target.getAttribute('data-bs-target'));
        });
    });
    const savedTab = localStorage.getItem('active_dompet_tab');
    if (savedTab) {
        const t = document.querySelector(`[data-bs-target="${savedTab}"]`);
        if (t) bootstrap.Tab.getOrCreateInstance(t).show();
    }

    // 2. Logic UI Update
    const fS = document.getElementById('fieldSelect'), oS = document.getElementById('operatorSelect'), vC = document.getElementById('valueContainer'), vI2 = document.getElementById('valueInput2');
    function refreshUI() {
        const f = fS.value, o = oS.value;
        vC.innerHTML = ''; vI2.classList.add('d-none'); vI2.removeAttribute('required');
        const commonClass = "form-control logic-input border shadow-none";
        
        if (f === 'late_minutes') {
            vC.innerHTML = `<input type="number" name="condition_value" class="${commonClass}" placeholder="0" required>`;
        } else if (f.includes('time')) {
            vC.innerHTML = `<input type="time" name="condition_value" class="${commonClass}" style="width: 140px !important;" required>`;
        } else if (f === 'final_status') {
            vC.innerHTML = `<select name="condition_value" class="form-select logic-input border shadow-none" style="width: 140px !important;"><option value="hadir">HADIR</option><option value="telat">TELAT</option><option value="alpha">ALPHA</option><option value="izin">IZIN</option><option value="sakit">SAKIT</option></select>`;
            if(o !== '=') oS.value = '=';
            Array.from(oS.options).forEach(opt => opt.disabled = (opt.value !== '='));
        }
        if (f !== 'final_status') Array.from(oS.options).forEach(opt => opt.disabled = false);
        if (o === 'BETWEEN') { vI2.classList.remove('d-none'); vI2.setAttribute('required', 'required'); vI2.type = (f === 'late_minutes') ? 'number' : 'time'; }
    }
    fS.addEventListener('change', refreshUI); oS.addEventListener('change', refreshUI); refreshUI();

    // 3. Marketplace Effect Visibility
    const tT = document.getElementById('tokenType'), eW = document.getElementById('effectWrapper');
    tT.addEventListener('change', () => eW.style.display = (tT.value === 'late_forgiveness') ? 'block' : 'none');
    eW.style.display = (tT.value === 'late_forgiveness') ? 'block' : 'none';
});
</script>
@endsection