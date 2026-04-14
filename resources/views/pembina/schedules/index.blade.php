@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 px-lg-4">

    {{-- ================= HEADER ================= --}}
    @include('layouts.partials.eskul-nav')
    <div class="row align-items-center mb-5">
        <div class="col-md-8">

            <h2 class="fw-bold text-dark mb-1 tracking-tight">Kelola Jadwal Eskul</h2>
            <p class="text-muted mb-0">Atur hari pelaksanaan rutin untuk <span class="fw-semibold text-primary">{{ $eskul->name }}</span></p>
        </div>
        <div class="col-md-4 text-md-end mt-4 mt-md-0">
            <a href="{{ route('pembina.eskul.index') }}" class="btn btn-white btn-modern">
                <i class="bi bi-arrow-left me-2 text-muted"></i>Kembali
            </a>
        </div>
    </div>

    {{-- ================= STATS / ALERT ================= --}}
    @if (session('success') || $errors->any())
        <div class="row mb-4">
            <div class="col-12">
                @if ($errors->any())
                    <div class="alert alert-danger elegant-alert d-flex align-items-center mb-3" role="alert">
                        <div class="alert-icon bg-danger text-white me-3"><i class="fas fa-exclamation"></i></div>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach
                        </ul>
                    </div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success elegant-alert d-flex align-items-center mb-0" role="alert">
                        <div class="alert-icon bg-success text-white me-3"><i class="fas fa-check"></i></div>
                        <div class="fw-medium">{{ session('success') }}</div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="row g-4">
        {{-- ================= FORM TAMBAH (Kiri) ================= --}}
        <div class="col-xl-4 col-lg-5">
            <div class="card card-elegant h-100 border-0">
                <div class="card-body p-4 p-xl-5">
                    <div class="d-flex flex-column align-items-center text-center mb-4 pb-2">
                        <div class="icon-box bg-primary-subtle text-primary mb-3">
                            <i class="fas fa-calendar-alt fa-lg"></i>
                        </div>
                        <h5 class="fw-bold mb-1">Tambah Hari Latihan</h5>
                        <p class="text-muted small mb-0">Pilih hari untuk jadwal rutin eskul.</p>
                    </div>

                    <form method="POST" action="{{ route('pembina.schedules.store', $eskul->id) }}">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark mb-2">Pilih Hari</label>
                            <select name="day_of_week" class="form-select select-elegant shadow-none" required>
                                <option value="" disabled selected>-- Tentukan Hari --</option>
                                @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'] as $index => $day)
                                    <option value="{{ $index+1 }}">{{ $day }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button class="btn btn-primary btn-lg w-100 fw-bold btn-glow">
                            <i class="fas fa-plus me-2"></i>Tambahkan Jadwal
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ================= LIST JADWAL (Kanan) ================= --}}
        <div class="col-xl-8 col-lg-7">
            <div class="card card-elegant h-100 border-0">
                <div class="card-header bg-transparent border-0 pt-4 px-4 px-xl-5 pb-0">
                    <h5 class="fw-bold text-dark mb-0">Daftar Jadwal Aktif</h5>
                </div>
                
                <div class="card-body p-0 mt-3">
                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4 ps-xl-5">HARI PELAKSANAAN</th>
                                    <th>STATUS</th>
                                    <th class="text-end pe-4 pe-xl-5">TINDAKAN</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($schedules as $s)
                                    <tr>
                                        <td class="ps-4 ps-xl-5">
                                            <div class="d-flex align-items-center">
                                                <div class="day-avatar me-3 {{ $s->is_active ? 'bg-primary-subtle text-primary' : 'bg-light text-muted' }}">
                                                    {{ substr(['','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'][$s->day_of_week], 0, 1) }}
                                                </div>
                                                <span class="fw-bold text-dark fs-6">
                                                    {{ ['','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'][$s->day_of_week] }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            @if($s->is_active)
                                                <span class="badge badge-soft-success">
                                                    <span class="dot-indicator bg-success"></span> Aktif
                                                </span>
                                            @else
                                                <span class="badge badge-soft-secondary">
                                                    <span class="dot-indicator bg-secondary"></span> Nonaktif
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4 pe-xl-5">
                                            <div class="d-inline-flex gap-2">
                                               <form method="POST" action="{{ route('pembina.schedules.toggle', [$eskul->id, $s->id]) }}">
                                                    @csrf @method('PATCH')
                                                    <button class="btn btn-sm btn-action {{ $s->is_active ? 'text-warning bg-warning-subtle' : 'text-success bg-success-subtle' }}" data-bs-toggle="tooltip" title="{{ $s->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                        <i class="fas {{ $s->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                                    </button>
                                                </form>
                                                
                                                <form method="POST"
                                                    action="{{ route('pembina.schedules.destroy', [$eskul->id, $s->id]) }}"
                                                    onsubmit="return confirm('Yakin ingin menghapus jadwal ini?')">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button class="btn btn-sm btn-action text-danger bg-danger-subtle"
                                                            data-bs-toggle="tooltip"
                                                            title="Hapus Jadwal">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <div class="empty-state">
                                                <i class="fas fa-calendar-times fa-3x text-light mb-3"></i>
                                                <h6 class="fw-bold text-dark mb-1">Belum ada jadwal</h6>
                                                <p class="text-muted small mb-0">Silakan tambahkan hari latihan di panel sebelah kiri.</p>
                                            </div>
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



<style>
    /* Global Typography & Spacing */
    .tracking-tight { letter-spacing: -0.02em; }
    
    /* Cards */
    .card-elegant {
        border-radius: 20px;
        box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    /* Icons & Avatars */
    .icon-box {
        width: 64px;
        height: 64px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 18px;
    }
    .day-avatar {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-weight: 700;
        font-size: 1.1rem;
    }

    /* Forms */
    .select-elegant {
        border-radius: 12px;
        border: 2px solid #edf2f7;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        transition: border-color 0.2s;
    }
    .select-elegant:focus {
        border-color: #cbd5e1;
        box-shadow: none;
    }

    /* Buttons */
    .btn-modern {
        border-radius: 12px;
        padding: 0.6rem 1.25rem;
        font-weight: 600;
        transition: all 0.2s;
    }
    .btn-white { background: #fff; border: 1px solid #e2e8f0; color: #475569; }
    .btn-white:hover { background: #f8fafc; border-color: #cbd5e1; color: #1e293b; }
    .btn-glow { box-shadow: 0 8px 20px rgba(13, 110, 253, 0.2); border-radius: 12px; }
    
    .btn-action {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        border: none;
        transition: all 0.2s ease;
    }
    .btn-action:hover { transform: translateY(-2px); }

    .btn-soft-warning {
        background-color: #fffbeb;
        color: #d97706;
        border: 1px solid #fde68a;
        border-radius: 10px;
    }
    .btn-soft-warning:hover { background-color: #fef3c7; color: #b45309; }

    /* Tables */
    .table-custom th {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #94a3b8;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 1rem;
        font-weight: 700;
    }
    .table-custom td {
        padding: 1.25rem 0;
        vertical-align: middle;
        border-bottom: 1px solid #f8fafc;
    }
    .table-custom tbody tr:hover td { background-color: #f8fafc; }
    .table-custom tbody tr:last-child td { border-bottom: none; }

    /* Badges */
    .badge-soft-success {
        background-color: #ecfdf5;
        color: #059669;
        padding: 0.5rem 0.85rem;
        border-radius: 30px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
    .badge-soft-secondary {
        background-color: #f1f5f9;
        color: #64748b;
        padding: 0.5rem 0.85rem;
        border-radius: 30px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }
    .dot-indicator {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        display: inline-block;
    }

    /* Alerts */
    .elegant-alert {
        border-radius: 16px;
        padding: 1rem;
    }
    .alert-icon {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Utilities */
    .bg-primary-subtle { background-color: #eff6ff !important; }
    .bg-danger-subtle { background-color: #fef2f2 !important; }
    .bg-success-subtle { background-color: #f0fdf4 !important; }
    .bg-warning-subtle { background-color: #fffbeb !important; }
</style>
@endsection

@push('scripts')
<script>
    // Inisialisasi Tooltip Bootstrap (jika Anda menggunakan Bootstrap 5)
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })

</script>
@endpush