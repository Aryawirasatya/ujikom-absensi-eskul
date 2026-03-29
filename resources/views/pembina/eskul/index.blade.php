@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="mb-4">
        <h4 class="fw-bold mb-1">Manajemen Ekstrakurikuler</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 p-0 bg-transparent">
                <li class="breadcrumb-item text-muted">Pembina</li>
                <li class="breadcrumb-item active fw-medium">Dashboard Eskul</li>
            </ol>
        </nav>
    </div>

    <div class="row g-4">
        @forelse ($eskuls as $eskul)
        <div class="col-xl-4 col-md-6">
            <div class="card card-eskul border-0 shadow-sm h-100 overflow-hidden">

                <div class="role-indicator {{ $eskul->primaryCoach?->user_id === auth()->id() ? 'bg-primary' : 'bg-info' }}"></div>

                <div class="card-body p-4 d-flex flex-column">

                    {{-- Header --}}
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-eskul rounded-3 d-flex align-items-center justify-content-center">
                                <span class="fw-bold text-primary fs-5">{{ substr($eskul->name, 0, 1) }}</span>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">{{ $eskul->name }}</h5>
                                @if($eskul->is_active)
                                    <small class="text-success d-flex align-items-center gap-1">
                                        <span class="status-dot bg-success"></span> Aktif
                                    </small>
                                @else
                                    <small class="text-muted d-flex align-items-center gap-1">
                                        <span class="status-dot bg-secondary"></span> Nonaktif
                                    </small>
                                @endif
                            </div>
                        </div>
                        <span class="badge {{ $eskul->primaryCoach?->user_id === auth()->id() ? 'badge-primary' : 'badge-info' }} px-2 py-1">
                            {{ $eskul->primaryCoach?->user_id === auth()->id() ? 'Utama' : 'Asisten' }}
                        </span>
                    </div>

                    {{-- Deskripsi --}}
                    <p class="text-muted small mb-4 flex-grow-1 line-clamp-2">
                        {{ $eskul->description ?? 'Tidak ada deskripsi tersedia untuk ekstrakurikuler ini.' }}
                    </p>

                    {{-- Stats --}}
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="stat-box rounded-3 text-center py-2 px-1">
                                <div class="fw-bold text-dark h6 mb-0">{{ $eskul->members()->where('status','active')->count() }}</div>
                                <div class="stat-label">Anggota</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-box rounded-3 text-center py-2 px-1">
                                <div class="fw-bold text-dark h6 mb-0">{{ $eskul->schedules()->where('is_active', 1)->count() }}</div>
                                <div class="stat-label">Jadwal</div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-2 opacity-10">

                    {{-- ============ ACTION BUTTONS ============ --}}
                    <div class="d-grid gap-2">

                        {{-- Baris 1: Anggota | Jadwal | Penilaian --}}
                        <div class="row g-2">
                            <div class="col-4">
                                <a href="{{ route('pembina.members.index', $eskul->id) }}"
                                   class="btn btn-light border btn-sm w-100 d-flex flex-column align-items-center py-2 gap-1 action-tile">
                                    <i class="bi bi-people text-primary" style="font-size:1rem"></i>
                                    <span class="tile-label">Anggota</span>
                                </a>
                            </div>
                            <div class="col-4">
                                <a href="{{ route('pembina.schedules.index', $eskul->id) }}"
                                   class="btn btn-light border btn-sm w-100 d-flex flex-column align-items-center py-2 gap-1 action-tile">
                                    <i class="bi bi-calendar3 text-primary" style="font-size:1rem"></i>
                                    <span class="tile-label">Jadwal</span>
                                </a>
                            </div>
                            <div class="col-4">
                                {{-- ★ PENILAIAN — sejajar Anggota & Jadwal ★ --}}
                                <a href="{{ route('pembina.penilaian.index', $eskul->id) }}"
                                   class="btn btn-light border border-warning btn-sm w-100 d-flex flex-column align-items-center py-2 gap-1 action-tile action-tile-warn">
                                    <i class="fas fa-star text-warning" style="font-size:1rem"></i>
                                    <span class="tile-label text-warning-emphasis">Penilaian</span>
                                </a>
                            </div>
                        </div>

                        {{-- Baris 2: Presensi (full width, CTA utama) --}}
                        <a href="{{ route('pembina.activity.index', $eskul->id) }}"
                           class="btn btn-primary btn-sm py-2 fw-semibold">
                            <i class="bi bi-qr-code-scan me-2"></i>Buka Presensi
                        </a>

                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm text-center py-5">
                <div class="card-body">
                    <i class="fas fa-school fa-3x text-muted opacity-25 mb-3 d-block"></i>
                    <h5 class="fw-bold">Belum Ada Eskul</h5>
                    <p class="text-muted mx-auto" style="max-width:400px">
                        Sistem belum mendeteksi Anda sebagai pembina. Silakan hubungi admin sekolah.
                    </p>
                </div>
            </div>
        </div>
        @endforelse
    </div>

</div>

<style>
.card-eskul {
    border-radius: 16px;
    transition: transform .25s cubic-bezier(.175,.885,.32,1.275), box-shadow .25s;
}
.card-eskul:hover { transform: translateY(-6px); box-shadow: 0 16px 40px rgba(0,0,0,.1) !important; }
.role-indicator { height: 4px; }
.avatar-eskul { width:46px; height:46px; background:#f0f3ff; flex-shrink:0; }
.status-dot { height:7px; width:7px; border-radius:50%; display:inline-block; }
.badge-primary { background:#e8efff; color:#4e73df; }
.badge-info    { background:#e1f5fe; color:#0097a7; }
.stat-box { background:#f8f9fc; }
.stat-label { font-size:.62rem; text-transform:uppercase; letter-spacing:.5px; color:#6c757d; }
.action-tile { border-radius:10px !important; transition: all .18s; text-decoration:none; }
.action-tile:hover { background:#f0f3ff !important; border-color:#4e73df !important; }
.action-tile-warn:hover { background:#fff8e6 !important; }
.tile-label { font-size:.68rem; font-weight:600; color:#555; }
.line-clamp-2 { display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
.btn-primary { background:#4e73df !important; border-color:#4e73df !important; }
.breadcrumb-item+.breadcrumb-item::before { content:"›"; font-size:1.2rem; vertical-align:middle; line-height:0; }
</style>
@endsection
