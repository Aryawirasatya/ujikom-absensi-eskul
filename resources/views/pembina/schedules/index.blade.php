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
            <div class="card card-elegant h-100 border-0 shadow-sm">
                <div class="card-body p-4 p-xl-5">
                    <div class="d-flex flex-column align-items-center text-center mb-4 pb-2">
                        <div class="icon-box bg-primary-subtle text-primary mb-3">
                            <i class="fas fa-calendar-alt fa-lg"></i>
                        </div>
                        <h5 class="fw-bold mb-1">Tambah Hari Latihan</h5>
                        <p class="text-muted small mb-0">Tentukan waktu rutin untuk agenda otomatis.</p>
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

                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark mb-2">Jam Masuk (Target)</label>
                            <input type="time" name="start_time" class="form-control select-elegant shadow-none" required>
                            <small class="text-muted d-block mt-1">Patokan keterlambatan siswa.</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark mb-2">
                                Jam Buka Absensi
                            </label>

                            <input type="time"
                                name="checkin_open_at"
                                class="form-control select-elegant shadow-none"
                                required>

                            <small class="text-muted d-block mt-1">
                                Jam siswa boleh mulai scan (contoh: 15:30)
                            </small>
                        </div>

                        <button class="btn btn-primary btn-lg w-100 fw-bold btn-glow mt-2">
                            <i class="fas fa-plus me-2"></i>Tambahkan Jadwal
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ================= LIST JADWAL (Kanan) ================= --}}
        <div class="col-xl-8 col-lg-7">
            <div class="card card-elegant h-100 border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-4 px-4 px-xl-5 pb-0">
                    <h5 class="fw-bold text-dark mb-0">Daftar Jadwal Rutin</h5>
                </div>
                
                <div class="card-body p-0 mt-3">
                    <div class="table-responsive">
                        <table class="table table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4 ps-xl-5">HARI</th>
                                    <th class="text-center">JAM MASUK</th>
                                    <th class="text-center">ABSEN DIBUKA</th>
                                    <th class="text-center">STATUS</th>
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
                                        <td class="text-center">
                                            <div class="fw-bold text-dark">
                                                <i class="far fa-clock me-1 text-primary"></i>
                                                {{ \Carbon\Carbon::parse($s->start_time)->format('H:i') }}
                                            </div>
                                        </td>
                                       <td class="text-center">
                                            <div class="fw-bold text-success">
                                                <i class="bi bi-door-open me-1"></i>

                                                @if($s->checkin_open_at)
                                                    {{ \Carbon\Carbon::parse($s->checkin_open_at)->format('H:i') }}
                                                @else
                                                    <span class="text-muted">--:--</span>
                                                @endif

                                            </div>
                                            <small class="text-muted">Mulai scan</small>
                                        </td>
                                        <td class="text-center">
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
                                                    <button class="btn btn-sm btn-action {{ $s->is_active ? 'text-warning bg-warning-subtle' : 'text-success bg-success-subtle' }}" 
                                                            data-bs-toggle="tooltip" 
                                                            title="{{ $s->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                        <i class="fas {{ $s->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                                    </button>
                                                </form>
                                                
                                                <form method="POST"
                                                    action="{{ route('pembina.schedules.destroy', [
                                                    'eskul' => $eskul->id,
                                                    'schedule' => $s->id
                                                ]) }}"
                                                    onsubmit="return confirm('Yakin ingin menghapus jadwal rutin ini?')">
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
                                        <td colspan="5" class="text-center py-5">
                                            <div class="empty-state">
                                                <i class="fas fa-calendar-times fa-3x text-light mb-3"></i>
                                                <h6 class="fw-bold text-dark mb-1">Belum ada jadwal rutin</h6>
                                                <p class="text-muted small mb-0">Silakan tentukan hari dan jam latihan eskul.</p>
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
        border-radius: 24px;
        box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05);
        transition: transform 0.2s ease;
    }
    
    /* Icons & Avatars */
    .icon-box {
        width: 64px;
        height: 64px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 20px;
    }
    .day-avatar {
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        font-weight: 800;
        font-size: 1.1rem;
    }

    /* Forms */
    .select-elegant {
        border-radius: 14px;
        border: 2px solid #f1f5f9;
        padding: 0.75rem 1.1rem;
        font-size: 0.95rem;
        background-color: #f8fafc;
        transition: all 0.2s ease;
    }
    .select-elegant:focus {
        border-color: #0d6efd;
        background-color: #fff;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.08);
    }
    .input-group-text {
        border-radius: 0 14px 14px 0;
    }

    /* Buttons */
    .btn-modern {
        border-radius: 14px;
        padding: 0.6rem 1.5rem;
        font-weight: 600;
        transition: all 0.2s;
    }
    .btn-white { background: #fff; border: 1px solid #e2e8f0; color: #475569; }
    .btn-white:hover { background: #f8fafc; border-color: #cbd5e1; color: #1e293b; }
    .btn-glow { 
        box-shadow: 0 8px 25px rgba(13, 110, 253, 0.2); 
        border-radius: 14px; 
    }
    
    .btn-action {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        border: none;
        transition: all 0.2s ease;
    }
    .btn-action:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }

    /* Tables */
    .table-custom th {
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: #94a3b8;
        border-bottom: 1px solid #f1f5f9;
        padding: 1.25rem 1rem;
        font-weight: 800;
    }
    .table-custom td {
        padding: 1.5rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f8fafc;
    }
    .table-custom tbody tr:hover td { background-color: #fcfdfe; }

    /* Badges */
    .badge-soft-success {
        background-color: #dcfce7;
        color: #15803d;
        padding: 0.6rem 1rem;
        border-radius: 30px;
        font-weight: 700;
        font-size: 0.75rem;
    }
    .badge-soft-secondary {
        background-color: #f1f5f9;
        color: #475569;
        padding: 0.6rem 1rem;
        border-radius: 30px;
        font-weight: 700;
        font-size: 0.75rem;
    }
    .dot-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 4px;
    }

    /* Alerts */
    .elegant-alert {
        border-radius: 20px;
        padding: 1.25rem;
        border: none;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .alert-icon {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
</style>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
@endpush