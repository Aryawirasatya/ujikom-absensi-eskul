@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 px-lg-4">

    {{-- Menu Navigasi Tabs (Jika ada partials/nav) --}}
    @include('layouts.partials.eskul-nav')

    {{-- ================= HEADER SECTION ================= --}}
    <div class="page-header d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1">Manajemen Keanggotaan</h3>
        </div>
    </div>

    {{-- ================= NOTIFIKASI / ALERT ================= --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm d-flex align-items-center rounded-4 py-3 mb-4">
            <div class="icon-circle bg-success text-white me-3 shadow-sm" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                <i class="fas fa-check small"></i>
            </div>
            <span class="fw-medium text-success">{{ session('success') }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center rounded-4 py-3 mb-4">
            <div class="icon-circle bg-danger text-white me-3 shadow-sm" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                <i class="fas fa-exclamation-triangle small"></i>
            </div>
            <span class="fw-medium text-danger">{{ $errors->first() }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- ================= FORM REGISTRASI (KIRI) ================= --}}
        <div class="col-xl-4 col-lg-5">
            <div class="card card-round border-0 shadow-sm h-100 overflow-hidden">
                <div class="card-header bg-primary p-4 border-0">
                    <div class="d-flex align-items-center">
                        <div class="icon-shape bg-white text-primary rounded-3 me-3" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div>
                            <h5 class="text-white fw-bold mb-0">Registrasi Baru</h5>
                            <small class="text-white-50">Tambahkan siswa ke eskul ini</small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4 p-xl-5">
                    <form method="POST" action="{{ route('pembina.members.store', $eskul->id) }}">
                        @csrf
                        <div class="form-group mb-4 p-0">
                            <label class="form-label fw-bold text-dark mb-2">Cari Nama Siswa / NISN</label>
                            <select name="user_id" class="form-select select2-searchable w-100" required></select>
                            <div class="mt-2 p-3 bg-primary-subtle rounded-3 border-start border-primary border-3">
                                <p class="small text-primary mb-0">
                                    <i class="fas fa-info-circle me-1"></i> Siswa yang sudah terdaftar aktif tidak akan muncul dalam pencarian.
                                </p>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg btn-round shadow py-3 fw-bold">
                                <i class="fas fa-plus-circle me-2"></i>Daftarkan Sekarang
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ================= TABEL ANGGOTA (KANAN) ================= --}}
        <div class="col-xl-8 col-lg-7">
            <div class="card card-round border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">Daftar Anggota Eskul</h5>
                        <p class="text-muted small">Total: <strong>{{ $members->count() }}</strong> Siswa Terdaftar</p>
                    </div>
                </div>

                <div class="card-body px-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 border-0">
                            <thead class="bg-light-subtle">
                                <tr>
                                    <th class="ps-4 text-muted small fw-bold text-uppercase py-3">Siswa</th>
                                    <th class="text-muted small fw-bold text-uppercase py-3">NISN</th>
                                    <th class="text-muted small fw-bold text-uppercase py-3">Kelas</th>
                                    <th class="text-end pe-4 text-muted small fw-bold text-uppercase py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($members as $member)
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-3">
                                                    <span class="avatar-title rounded-circle bg-primary-subtle text-primary fw-bold" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                        {{ strtoupper(substr($member->user->name, 0, 1)) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $member->user->name }}</div>
                                                    <div class="text-muted" style="font-size: 0.75rem;">ID: #{{ str_pad($member->user->id, 4, '0', STR_PAD_LEFT) }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <code class="text-primary fw-medium bg-primary-subtle px-2 py-1 rounded small">
                                                {{ $member->user->nisn ?? '-' }}
                                            </code>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 fw-bold">
                                                {{ optional($member->user->currentAcademic)->grade ?? '?' }}-{{ optional($member->user->currentAcademic)->class_label ?? '?' }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            @if($member->status === 'active')
                                                <form method="POST" action="{{ route('pembina.members.deactivate', [$eskul->id, $member->id]) }}" class="d-inline">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="btn btn-icon btn-link btn-danger btn-lg p-0" data-bs-toggle="tooltip" title="Nonaktifkan Anggota">
                                                        <i class="fas fa-user-minus"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('pembina.members.activate', [$eskul->id, $member->id]) }}" class="d-inline">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="btn btn-icon btn-link btn-success btn-lg p-0" data-bs-toggle="tooltip" title="Aktifkan Kembali">
                                                        <i class="fas fa-user-check"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="80" class="opacity-25 mb-3">
                                            <h6 class="fw-bold text-muted">Daftar Keanggotaan Kosong</h6>
                                            <p class="small text-muted">Belum ada siswa yang bergabung di eskul ini.</p>
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
</div>

{{-- ================= EXTRA CSS ================= --}}
<style>
    /* Kaiadmin Layout Tweaks */
    .card-round { border-radius: 1.2rem !important; }
    .btn-round { border-radius: 100px !important; }
    .bg-primary-subtle { background-color: #e8efff !important; }
    .bg-secondary-subtle { background-color: #f0f3f6 !important; }
    .text-primary { color: #5867dd !important; }
    .bg-primary { background-color: #5867dd !important; }
    
    .table thead th {
        background-color: #f8fafc;
        border-top: none !important;
        border-bottom: 1px solid #f1f5f9 !important;
    }
    .table tbody tr td { border-bottom: 1px solid #f8fafc; }
    .table tbody tr:hover td { background-color: #fbfcfe; }

    /* Custom Breadcrumbs style for Kaiadmin */
    .breadcrumbs { display: flex; align-items: center; list-style: none; }
    .breadcrumbs li a { color: #8d9498; text-decoration: none; font-size: 0.9rem; }
    .breadcrumbs .separator { margin: 0 10px; }
    .breadcrumbs .nav-item.active a { color: #5867dd; font-weight: 600; }

    /* Select2 Kaiadmin Skin */
    .select2-container--default .select2-selection--single {
        height: 50px !important;
        border: 1px solid #ebedf2 !important;
        border-radius: 0.5rem !important;
        padding: 10px 12px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 48px !important;
    }
    .select2-dropdown {
        border: 1px solid #ebedf2 !important;
        box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important;
        border-radius: 0.5rem !important;
    }
</style>
@endsection

@push('scripts')
{{-- Load Select2 CSS via CDN if not in app.css --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Inisialisasi Select2 dengan AJAX
    $('.select2-searchable').select2({
        placeholder: "🔍 Ketik Nama atau NISN Siswa...",
        minimumInputLength: 2,
        allowClear: true,
        ajax: {
            url: "{{ route('pembina.members.search', $eskul->id) }}",
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return { q: params.term };
            },
            processResults: function(data) {
                return {
                    results: data.map(function(item) {
                        return {
                            id: item.id,
                            text: item.text + " | NISN: " + (item.nisn ?? '-') + " | Kelas: " + (item.kelas ?? '-')
                        };
                    })
                };
            },
            cache: true
        }
    });

    // Inisialisasi Tooltip Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});
</script>
@endpush