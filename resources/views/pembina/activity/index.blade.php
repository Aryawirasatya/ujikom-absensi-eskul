@extends('layouts.app')

@section('content')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        --danger-gradient: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);
        --info-gradient: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%);
        --transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    body { background-color: #f4f7fa; font-family: 'Inter', sans-serif; color: #2d3436; }
    .dashboard-container { max-width: 1200px; margin: auto; padding-top: 2rem; padding-bottom: 5rem; }
    .page-header {
        background: white; padding: 2.5rem; border-radius: 28px;
        box-shadow: 0 15px 45px rgba(0,0,0,0.05); margin-bottom: 2.5rem;
        border: 1px solid rgba(0,0,0,0.03); position: relative; overflow: hidden;
    }
    .page-header::before {
        content:''; position:absolute; top:0; left:0; width:100%; height:5px;
        background: var(--primary-gradient);
    }
    .filter-wrapper {
        background: white; padding: 18px 30px; border-radius: 20px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.03); display: flex; align-items: center;
        gap: 20px; margin-bottom: 2.5rem; border: 1px solid #edf2f7;
    }
    .filter-label { font-weight:800; color:#64748b; font-size:.85rem; text-transform:uppercase; letter-spacing:1px; }
    .custom-nav-pills { background:#f1f5f9; padding:7px; border-radius:16px; display:inline-flex; }
    .custom-nav-pills .nav-link { border-radius:12px; padding:12px 28px; font-weight:700; color:#94a3b8; transition:var(--transition-smooth); border:none; }
    .custom-nav-pills .nav-link.active { background:white; color:#0d6efd; box-shadow:0 10px 20px rgba(13,110,253,.15); }
    .card-session {
        border:none; border-radius:24px; transition:var(--transition-smooth);
        background:white; margin-bottom:1.8rem; position:relative; border:1px solid transparent;
    }
    .card-session:hover { transform:translateY(-10px); box-shadow:0 25px 50px rgba(0,0,0,0.07) !important; border-color:#e2e8f0; }
    .status-sidebar { width:10px; position:absolute; left:0; top:25px; bottom:25px; border-radius:0 8px 8px 0; }
    .bg-status-active    { background: var(--primary-gradient); }
    .bg-status-cancelled { background: var(--danger-gradient); }
    .bg-status-scheduled { background: var(--info-gradient); }
    .bg-status-finished  { background: #1e293b; }
    .selection-card {
        border:2px solid #f8fafc; border-radius:24px; padding:35px 25px;
        transition:var(--transition-smooth); cursor:pointer; text-align:center;
        height:100%; position:relative; background:#fdfdfd;
    }
    .selection-card:hover { border-color:#0d6efd; background:#f0f7ff; transform:scale(1.03); }
    .selection-card i { font-size:3rem; margin-bottom:20px; color:#0d6efd; }
    .selection-card.manual i { color:#64748b; }
    .selection-card.manual:hover i { color:#0d6efd; }
    .badge-soft-success { background:#dcfce7; color:#166534; }
    .badge-soft-info    { background:#e0f2fe; color:#075985; }
    .badge-soft-warning { background:#fef3c7; color:#92400e; }
    .badge-soft-danger  { background:#fee2e2; color:#991b1b; }
    .badge-soft-dark    { background:#f1f5f9; color:#1e293b; }
    .animate-pulse-custom { animation: pulse-soft 2.5s infinite; }
    @keyframes pulse-soft { 0%,100%{transform:scale(1);opacity:1} 50%{transform:scale(1.08);opacity:.9} }
    .grayscale { filter:grayscale(1); }
</style>

<div class="container dashboard-container">
    @include('layouts.partials.eskul-nav')
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center">
        <div class="header-content">
            <h1 class="h2 fw-bolder mb-0 text-dark">Monitor Absensi: <span class="text-primary">{{ $eskul->name }}</span></h1>
            <p class="text-muted small mb-0 mt-2">
                <i class="fas fa-fingerprint me-2 text-primary"></i>
                Pusat kendali kehadiran anggota ekstrakurikuler secara real-time.
            </p>
        </div>
        <div class="header-action mt-3 mt-lg-0">
            <button class="btn btn-primary shadow-lg px-5 py-3 fw-bold rounded-pill"
                    data-bs-toggle="modal" data-bs-target="#nonRoutineModal">
                <i class="fas fa-plus-circle me-2"></i>Buat Agenda Khusus
            </button>
        </div>
    </div>

    <div class="filter-wrapper d-flex flex-wrap justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <span class="filter-label"><i class="fas fa-sliders-h me-2"></i>Filter Tipe:</span>
            <select class="form-select form-select-sm border-0 bg-light fw-bold rounded-pill px-4"
                    id="typeFilter" style="width:220px;height:45px;cursor:pointer;">
                <option value="all">Semua Tipe Aktivitas</option>
                <option value="routine">Jadwal Rutin Mingguan</option>
                <option value="non_routine">Agenda Khusus / Rapat</option>
            </select>
        </div>
        <ul class="nav custom-nav-pills shadow-sm" id="pills-tab" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="pills-today-tab" data-bs-toggle="pill"
                        data-bs-target="#pills-today" type="button" role="tab">
                    <i class="bi bi-calendar-check me-2"></i>Hari Ini
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="pills-upcoming-tab" data-bs-toggle="pill"
                        data-bs-target="#pills-upcoming" type="button" role="tab">
                    <i class="fas fa-calendar-alt me-2"></i>Mendatang
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="pills-history-tab" data-bs-toggle="pill"
                        data-bs-target="#pills-history" type="button" role="tab">
                    <i class="fas fa-history me-2"></i>Riwayat Lampau
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content" id="pills-tabContent">

        {{-- ===== TAB HARI INI ===== --}}
        <div class="tab-pane fade show active" id="pills-today" role="tabpanel">
        @php
            $todayActivities = $activities->filter(
                fn($act) => \Carbon\Carbon::parse($act->activity_date)->isToday()
            );
        @endphp
        <div id="activityContainerToday">
        @forelse($todayActivities as $act)

        <div class="card card-session shadow-sm activity-item {{ $act->status=='cancelled' ? 'opacity-75' : '' }}"
             data-type="{{ $act->type }}">

            @php
                $sidebarClass = 'bg-status-active';
                if($act->status == 'cancelled') $sidebarClass = 'bg-status-cancelled';
                elseif($act->attendance_phase == 'finished') $sidebarClass = 'bg-status-finished';
            @endphp
            <div class="status-sidebar {{ $sidebarClass }}"></div>

            <div class="card-body p-4 ms-3">
                <div class="row align-items-center">

                    {{-- INFO --}}
                   <div class="col-xl-5 col-lg-5 mb-3 mb-lg-0">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="fas fa-clock text-primary small"></i>
                            <span class="fw-bold text-secondary small">
                                {{ \Carbon\Carbon::parse($act->activity_date)->locale('id')->translatedFormat('l, d F Y') }}
                            </span>
                        </div>
                        <h4 class="fw-bold text-dark mb-0">{{ $act->title }}</h4>
                        
                        <div class="mt-1 mb-2">
                            <small class="text-muted">
                                <i class="bi bi-door-open me-1"></i> Buka: <strong>{{ \Carbon\Carbon::parse($act->checkin_open_at)->format('H:i') }}</strong>
                                <span class="mx-2">|</span>
                                <i class="bi bi-box-arrow-in-right me-1"></i> Mulai: <strong>{{ \Carbon\Carbon::parse($act->started_at)->format('H:i') }}</strong>
                            </small>
                        </div>
                        <span class="badge {{ $act->type=='routine' ? 'badge-soft-success' : 'badge-soft-info' }} px-3 py-2 rounded-pill fw-bold text-uppercase" style="font-size:.7rem;">
                            <i class="fas {{ $act->type=='routine' ? 'fa-sync' : 'fa-star' }} me-1"></i>
                            {{ $act->type=='routine' ? 'Rutin' : 'agenda Khusus' }}
                        </span>
                    </div>

                    {{-- STATUS --}}
                    <div class="col-xl-3 col-lg-3 text-center border-start border-end py-2">
                        <label class="d-block small fw-bold text-uppercase text-muted mb-3">Status Saat Ini</label>

                        @if($act->status == 'cancelled')
                            <span class="badge bg-danger rounded-pill px-4 py-2 fw-bold shadow-sm">DILIBURKAN</span>
                        @elseif($act->attendance_phase == 'finished')
                            <span class="badge bg-dark rounded-pill px-4 py-2 fw-bold text-white shadow-sm">SELESAI</span>
                        @elseif(in_array($act->attendance_phase, ['checkin','checkout']))
                            <div class="animate-pulse-custom">
                                <span class="badge bg-success text-white px-4 py-2 rounded-pill fw-bold shadow">ABSENSI BERJALAN</span>
                            </div>
                        @else
                            <span class="badge bg-warning text-dark px-4 py-2 rounded-pill fw-bold shadow-sm">SIAP DIMULAI</span>
                        @endif
                    </div>

                    {{-- ACTION --}}
                    <div class="col-xl-4 col-lg-4 text-lg-end mt-4 mt-lg-0">
                        <div class="d-flex flex-column gap-2 ps-lg-4">

                           @if($act->status == 'cancelled' || $act->attendance_phase == 'finished')
                            <a href="{{ route('pembina.activity.show', [$eskul->id, $act->id]) }}"
                            class="btn btn-light border-2 px-5 py-3 rounded-pill fw-bold shadow-sm">
                                <i class="fas fa-file-alt me-2"></i>Lihat Rekapitulasi
                            </a>

                            @elseif(in_array($act->attendance_phase, ['checkin','checkout']))
                                @if($act->attendance_mode === 'manual')
                                    <a href="{{ route('pembina.activity.manual_page', [$eskul->id, $act->id]) }}"
                                    class="btn btn-success btn-lg shadow-sm rounded-pill fw-bold">
                                        <i class="fas fa-pencil-alt me-2"></i>Lanjutkan Manual
                                        <i class="fas fa-chevron-right ms-2"></i>
                                    </a>
                                @else
                                    <a href="{{ route('pembina.activity.show', [$eskul->id, $act->id]) }}"
                                    class="btn btn-success btn-lg shadow-sm rounded-pill fw-bold">
                                        <i class="fas fa-qrcode me-2"></i>Lanjutkan Absensi
                                        <i class="fas fa-chevron-right ms-2"></i>
                                    </a>
                                @endif
                                <button onclick="alertForbiddenCancel()"
                                        class="btn btn-outline-danger btn-sm border-0 fw-bold mt-1">
                                    <i class="fas fa-calendar-times me-2"></i>Liburkan Sesi Ini
                                </button>

                            {{-- ✅ KONDISI BARU: mode sudah dipilih tapi phase belum berubah (user back via browser) --}}
                            @elseif(!is_null($act->attendance_mode))
                                @if($act->attendance_mode === 'manual')
                                    <a href="{{ route('pembina.activity.manual_page', [$eskul->id, $act->id]) }}"
                                    class="btn btn-primary btn-lg shadow-sm rounded-pill fw-bold">
                                        <i class="fas fa-pencil-alt me-2"></i>Buka Absensi Manual
                                        <i class="fas fa-chevron-right ms-2"></i>
                                    </a>
                                @else
                                    <a href="{{ route('pembina.activity.show', [$eskul->id, $act->id]) }}"
                                    class="btn btn-primary btn-lg shadow-sm rounded-pill fw-bold">
                                        <i class="fas fa-qrcode me-2"></i>Buka Absensi QR
                                        <i class="fas fa-chevron-right ms-2"></i>
                                    </a>
                                @endif
                                <button onclick="confirmCancel('{{ $act->id }}','{{ $act->title }}')"
                                        class="btn btn-outline-danger btn-sm border-0 fw-bold mt-1">
                                    <i class="fas fa-calendar-times me-2"></i>Liburkan Sesi Ini
                                </button>

                            @else
                                {{-- Mode belum dipilih sama sekali --}}
                                <button onclick='openSelectionModal({{ $act->id }}, @json($act->title))'
                                        class="btn btn-primary btn-lg shadow-sm rounded-pill fw-bold">
                                    Mulai Absensi
                                    <i class="fas fa-play ms-2"></i>
                                </button>
                                <button onclick="confirmCancel('{{ $act->id }}','{{ $act->title }}')"
                                        class="btn btn-outline-danger btn-sm border-0 fw-bold mt-1">
                                    <i class="fas fa-calendar-times me-2"></i>Liburkan Sesi Ini
                                </button>
                            @endif

                        </div>

                        <form id="cancel-form-{{ $act->id }}"
                              action="{{ route('pembina.activity.cancel', [$eskul->id, $act->id]) }}"
                              method="POST" style="display:none;">
                            @csrf
                            <input type="hidden" name="reason" id="reason-field-{{ $act->id }}">
                        </form>
                    </div>

                </div>
            </div>
        </div>

        @empty
        <div class="text-center py-5 bg-white rounded-5">
            <img src="https://cdn-icons-png.flaticon.com/512/6134/6134065.png"
                 width="100" class="mb-4 opacity-25 grayscale">
            <h5 class="text-muted fw-bold">Tidak ada jadwal aktivitas untuk hari ini.</h5>
        </div>
        @endforelse
        </div>
        </div>

        {{-- ===== TAB MENDATANG ===== --}}
        <div class="tab-pane fade" id="pills-upcoming" role="tabpanel">
            @php
                $upcomingActivities = $activities->filter(
                    fn($act) => \Carbon\Carbon::parse($act->activity_date)->isAfter(\Carbon\Carbon::today())
                );
            @endphp
            <div id="activityContainerUpcoming">
                @forelse($upcomingActivities as $act)
                <div class="card card-session shadow-sm activity-item" data-type="{{ $act->type }}">
                    <div class="status-sidebar bg-status-scheduled"></div>
                    <div class="card-body p-4 ms-3">
                        <div class="row align-items-center">
                           <div class="col-md-6">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <i class="fas fa-calendar-plus text-info small"></i>
                                    <span class="fw-bold text-info small">
                                        {{ \Carbon\Carbon::parse($act->activity_date)->locale('id')->translatedFormat('l, d F Y') }}
                                    </span>
                                </div>
                                <h4 class="fw-bold text-dark mb-0">{{ $act->title }}</h4>
                                
                                <div class="mt-1">
                                    <span class="badge bg-light text-dark border-0">
                                        <i class="bi bi-clock me-1"></i> Mulai Pukul: {{ \Carbon\Carbon::parse($act->started_at)->format('H:i') }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <span class="badge badge-soft-warning px-4 py-2 rounded-pill fw-bold">DIJADWALKAN</span>
                            </div>
                            <div class="col-md-3 text-end">
                                <button class="btn btn-light rounded-pill px-4 fw-bold disabled border">Belum Aktif</button>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5 bg-white rounded-5">
                    <p class="text-muted mb-0 fw-bold">Belum ada agenda masa depan yang tercatat.</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- ===== TAB RIWAYAT ===== --}}
        <div class="tab-pane fade" id="pills-history" role="tabpanel">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-4 border-0 text-secondary small fw-bold">TANGGAL & HARI</th>
                                <th class="py-4 border-0 text-secondary small fw-bold">JUDUL KEGIATAN</th>
                                <th class="py-4 border-0 text-secondary small fw-bold text-center">TIPE</th>
                                <th class="py-4 border-0 text-secondary small fw-bold text-center">MODE</th>
                                <th class="py-4 border-0 text-secondary small fw-bold text-center">STATUS AKHIR</th>
                                <th class="text-end pe-4 py-4 border-0 text-secondary small fw-bold">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activities->filter(fn($act) => \Carbon\Carbon::parse($act->activity_date)->isBefore(\Carbon\Carbon::today())) as $act)
                            <tr class="activity-item" data-type="{{ $act->type }}">
                                <td class="ps-4 py-3">
                                    <div class="fw-bold text-dark">{{ \Carbon\Carbon::parse($act->activity_date)->locale('id')->translatedFormat('d M Y') }}</div>
                                    <small class="text-muted fw-medium">{{ \Carbon\Carbon::parse($act->activity_date)->locale('id')->translatedFormat('l') }}</small>
                                </td>
                                <td><span class="fw-bold text-dark">{{ $act->title }}</span></td>
                                <td class="text-center">
                                    <span class="badge {{ $act->type=='routine' ? 'badge-soft-success' : 'badge-soft-info' }} rounded-pill">
                                        {{ $act->type=='routine' ? 'Rutin' : 'Khusus' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($act->attendance_mode)
                                    <span class="badge {{ $act->attendance_mode=='qr' ? 'bg-primary-subtle text-primary' : 'bg-secondary-subtle text-secondary' }} rounded-pill px-2"
                                          style="font-size:.65rem;">
                                        {{ strtoupper($act->attendance_mode) }}
                                    </span>
                                    @else
                                    <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($act->status == 'cancelled')
                                        <span class="badge badge-soft-danger px-3 py-2 rounded-pill fw-bold">Diliburkan</span>
                                    @else
                                        <span class="badge badge-soft-dark px-3 py-2 rounded-pill fw-bold">Selesai</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('pembina.activity.show', [$eskul->id, $act->id]) }}"
                                       class="btn btn-sm btn-outline-primary rounded-pill px-4 fw-bold">
                                        Detail <i class="fas fa-external-link-alt ms-1"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ===== MODAL PILIH MODE ===== --}}
<div class="modal fade" id="selectionModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-2xl rounded-5 overflow-hidden">
            <div class="modal-header border-0 p-5 pb-0">
                <div>
                    <h3 class="fw-bolder mb-1">Konfigurasi Metode Absensi</h3>
                    <p class="text-muted mb-0" id="modalActivityTitleDisplay"></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-5">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="selection-card" onclick="goToActivity('qr')">
                            <div class="icon-box mb-3"><i class="fas fa-qrcode"></i></div>
                            <h5 class="fw-bold text-dark">Sistem QR Code</h5>
                            <p class="small text-muted mb-3">Siswa melakukan scan mandiri menggunakan perangkat masing-masing.</p>
                            <span class="badge bg-primary px-4 py-2 rounded-pill shadow-sm">Efisien & Cepat</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="selection-card manual" onclick="goToActivity('manual')">
                            <div class="icon-box mb-3"><i class="bi bi-person-check"></i></div>
                            <h5 class="fw-bold text-dark">Presensi Manual</h5>
                            <p class="small text-muted mb-3">Pembina menandai daftar hadir siswa satu-persatu melalui dashboard.</p>
                            <span class="badge bg-light text-dark border px-4 py-2 rounded-pill shadow-sm">Kontrol Penuh</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-5 pt-0">
                <p class="small text-center w-100 text-muted">
                    <i class="fas fa-lock me-1"></i>
                    Mode yang dipilih akan dikunci dan tidak dapat diubah kembali.
                </p>
            </div>
        </div>
    </div>
</div>

{{--
    Form tersembunyi untuk submit pilihan mode.
    Menggunakan url() helper bukan route() dengan placeholder
    agar tidak ada masalah URL-encoding di JavaScript.
--}}
<form id="chooseModeForm" method="POST" action="">
    @csrf
    <input type="hidden" name="mode" id="chooseModeInput" value="">
</form>

<div class="modal fade" id="nonRoutineModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('pembina.activity.store_non_routine', $eskul->id) }}"
              method="POST" class="modal-content border-0 shadow-lg rounded-5">
            @csrf
            <div class="modal-header bg-primary text-white border-0 py-4 px-5">
                <h5 class="modal-title fw-bold">Entri Agenda Khusus Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body p-5">
                {{-- Nama Agenda --}}
                <div class="mb-4">
                    <label class="form-label fw-bold text-dark">Nama Agenda / Kegiatan</label>
                    <input type="text" name="title" value="{{ old('title') }}" 
                           class="form-control border-0 bg-light p-3 rounded-4 @error('title') is-invalid @enderror" 
                           placeholder="Contoh: Rapat Koordinasi Lomba" required>
                </div>

                {{-- Tanggal --}}
                <div class="mb-4">
                    <label class="form-label fw-bold text-dark">Tanggal Pelaksanaan</label>
                    <input type="date" name="activity_date" value="{{ old('activity_date', date('Y-m-d')) }}" 
                           class="form-control border-0 bg-light p-3 rounded-4 @error('activity_date') is-invalid @enderror" required>
                </div>

                <div class="row">
                    {{-- Jam Mulai --}}
                    <div class="col-md-6 mb-4">
                        <label class="form-label fw-bold text-dark">Jam Mulai</label>
                        <input type="time" name="start_time" value="{{ old('start_time') }}"
                               class="form-control border-0 bg-light p-3 rounded-4 @error('start_time') is-invalid @enderror" required>
                    </div>

                    {{-- Jam Buka Absensi --}}
                    <div class="col-md-6 mb-4">
                        <label class="form-label fw-bold text-dark">Jam Buka Absensi</label>
                        <input type="time" name="checkin_open_time" value="{{ old('checkin_open_time') }}"
                               class="form-control border-0 bg-light p-3 rounded-4 @error('checkin_open_time') is-invalid @enderror" required>
                    </div>
                </div>
                
                {{-- Tampilkan Error Spesifik di Bawah Input Jam --}}
                @error('checkin_open_time')
                    <div class="alert alert-danger border-0 small rounded-3 py-2">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $message }}
                    </div>
                @enderror

                <div class="mb-0">
                    <label class="form-label fw-bold text-dark">Deskripsi (Opsional)</label>
                    <textarea name="description" class="form-control border-0 bg-light p-3 rounded-4"
                              rows="3" placeholder="Tuliskan detail singkat...">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="modal-footer border-0 px-5 pb-5">
                <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow">
                    Simpan & Terbitkan Agenda
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let currentActivityId = null;

/*
 * BASE_URL: dibangun dari url() helper Blade — statis, tidak ada placeholder.
 * Format: /pembina/eskul/{eskulId}/activity
 * JS tinggal tambahkan /{activityId}/choose-mode di belakang.
 *
 * KENAPA BUKAN route() dengan placeholder?
 * route('...', [id, ':someId']) akan URL-encode ':someId' jadi '%3AsomeId'
 * sehingga Laravel tidak bisa match routenya.
 */
const BASE_ACTIVITY_URL = "{{ url('pembina/eskul/' . $eskul->id . '/activity') }}";

/* ---- Filter ---- */
document.addEventListener('DOMContentLoaded', function () {
    const typeFilter = document.getElementById('typeFilter');
    if (typeFilter) {
        typeFilter.addEventListener('change', function () {
            const val = this.value;
            document.querySelectorAll('.activity-item').forEach(item => {
                item.style.display = (val === 'all' || item.getAttribute('data-type') === val) ? '' : 'none';
            });
        });
    }

    const tooltips = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltips.map(el => new bootstrap.Tooltip(el));
});

/* ---- Buka Modal Pilih Mode ---- */
function openSelectionModal(id, title) {
    currentActivityId = parseInt(id);
    const el = document.getElementById('modalActivityTitleDisplay');
    if (el) el.innerText = 'Kegiatan: ' + title;
    new bootstrap.Modal(document.getElementById('selectionModal')).show();
}

/* ---- Submit Pilihan Mode ---- */
function goToActivity(mode) {
    if (!currentActivityId) return;

    const form = document.getElementById('chooseModeForm');
    form.action = BASE_ACTIVITY_URL + '/' + currentActivityId + '/choose-mode';
    document.getElementById('chooseModeInput').value = mode;
    form.submit();
}

/* ---- Liburkan (jika sesi sudah berjalan) ---- */
function alertForbiddenCancel() {
    Swal.fire({
        title: 'Tidak Diizinkan',
        text: 'Sesi absensi sudah berjalan. Tidak bisa diliburkan.',
        icon: 'error',
        confirmButtonColor: '#0d6efd'
    });
}

/* ---- Konfirmasi Liburkan ---- */
function confirmCancel(id, title) {
    Swal.fire({
        title: 'Liburkan Sesi?',
        text: `Anda akan meliburkan kegiatan "${title}".`,
        input: 'textarea',
        inputPlaceholder: 'Masukkan alasan pembatalan...',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Liburkan',
        cancelButtonText: 'Batal',
        preConfirm: value => {
            if (!value) { Swal.showValidationMessage('Alasan wajib diisi!'); return false; }
            return value;
        }
    }).then(result => {
        if (result.isConfirmed) {
            const reasonField = document.getElementById('reason-field-' + id);
            if (reasonField) {
                reasonField.value = result.value;
                document.getElementById('cancel-form-' + id).submit();
            }
        }
    });
}
</script>
@endpush
@endsection