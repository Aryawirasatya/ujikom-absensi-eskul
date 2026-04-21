@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="max-width:1100px;">

{{-- ALERTS --}}
@if($isCancelled)
<div class="alert alert-danger border-0 rounded-4 d-flex align-items-center gap-3 p-3 mb-4 shadow-sm">
    <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:42px;height:42px;">
        <i class="bi bi-exclamation-octagon-fill"></i>
    </div>
    <div><h6 class="mb-0 fw-bold">Kegiatan Diliburkan</h6>
    <small class="opacity-75">{{ $activity->cancel_reason ?? 'Tidak disebutkan' }}</small></div>
</div>
@endif
@if(session('success'))
<div class="alert alert-success border-0 rounded-4 d-flex align-items-center gap-3 p-3 mb-4 shadow-sm">
    <i class="bi bi-check-circle-fill fs-5 text-success flex-shrink-0"></i>
    <span>{{ session('success') }}</span>
</div>
@endif
@include('layouts.partials.eskul-nav')

{{-- ===== HEADER CARD ===== --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
    <div class="card-body p-4">

        {{-- Breadcrumb + Judul --}}
        <div class="row align-items-center mb-4 pb-3 border-bottom g-3">

    {{-- LEFT SIDE: Info Utama --}}
    <div class="col-lg-7">
        
        {{-- BREADCRUMB: Dibuat lebih subtle --}}

        {{-- TITLE: Ukuran lebih tegas --}}
        <h2 class="fw-extrabold text-dark mb-3 tracking-tight">
            {{ $activity->title }}
        </h2>

        {{-- META INFO: Menggunakan wrapper yang lebih rapi --}}
        <div class="d-flex flex-wrap align-items-center gap-2">
            
            {{-- MODE --}}
            <div class="d-flex align-items-center px-3 py-1 bg-light border rounded-pill shadow-sm">
                <i class="bi bi-pencil-square text-secondary me-2"></i>
                <span class="small fw-bold text-secondary">Manual</span>
            </div>

            {{-- TYPE --}}
            <div class="d-flex align-items-center px-3 py-1 bg-primary-subtle border border-primary-subtle rounded-pill shadow-sm">
                <i class="bi bi-tag-fill text-primary me-2"></i>
                <span class="small fw-bold text-primary">{{ strtoupper($activity->type) }}</span>
            </div>

            {{-- DATE: Diberi pemisah visual --}}
            <div class="ms-lg-2 ps-lg-2 border-start-lg text-muted small">
                <i class="bi bi-calendar3 me-1"></i>
                {{ \Carbon\Carbon::parse($activity->activity_date)->locale('id')->translatedFormat('l, d F Y') }}
                <div class="small text-muted mt-1">
                    <i class="bi bi-clock me-1"></i>
                    Jam Masuk: <strong>{{ \Carbon\Carbon::parse($activity->started_at)->format('H:i') }}</strong>
                </div>
            </div>

        </div>
    </div>


    {{-- RIGHT SIDE: Actions & Status --}}
    <div class="col-lg-5">
        <div class="d-flex flex-column align-items-lg-end gap-3">

            {{-- STATUS BADGE: Dibuat lebih menonjol sebagai indikator status --}}
            <div class="status-indicator">
                @if($activity->attendance_phase === 'checkin')
                    <div class="badge bg-success px-4 py-2 rounded-pill shadow-sm animate-pulse">
                        <i class="bi bi-record-circle-fill me-2"></i>SESI MASUK AKTIF
                    </div>
                @elseif($activity->attendance_phase === 'checkout')
                    <div class="badge bg-warning text-dark px-4 py-2 rounded-pill shadow-sm">
                        <i class="bi bi-record-circle-fill me-2"></i>SESI PULANG AKTIF
                    </div>
                @elseif($activity->attendance_phase === 'finished' || $isFinished)
                    <div class="badge bg-dark px-4 py-2 rounded-pill shadow-sm">
                        <i class="bi bi-check-circle-fill me-2"></i>SELESAI
                    </div>
                @endif
            </div>

            {{-- ACTION BUTTONS: Dikelompokkan berdasarkan fungsi --}}
            <div class="d-flex flex-wrap justify-content-lg-end gap-2 w-100">

                @if(!$isCancelled && !$isFinished)
                    
                    <div class="btn-group shadow-sm">
                        @if($activity->attendance_phase === 'not_started')
                            <button class="btn btn-primary px-4 fw-bold" onclick="confirmStartCheckin()">
                                <i class="bi bi-play-fill me-1"></i> Mulai Sesi
                            </button>
                        @elseif($activity->attendance_phase === 'checkin')
                            <button class="btn btn-warning px-4 fw-bold text-dark" onclick="confirmOpenCheckout()">
                                <i class="bi bi-arrow-right-circle-fill me-1"></i> Buka Pulang
                            </button>
                        @elseif($activity->attendance_phase === 'checkout')
                            <button class="btn btn-danger px-4 fw-bold" onclick="confirmFinishManual()">
                                <i class="bi bi-flag-fill me-1"></i> Selesaikan
                            </button>
                        @endif
                    </div>

                @elseif($isCancelled)
                    <button class="btn btn-outline-danger disabled px-4 fw-bold border-2 rounded-pill">
                        <i class="bi bi-x-circle-fill me-1"></i> DILIBURKAN
                    </button>
                @endif

            </div>

        </div>
    </div>

</div>



        {{-- STEPPER --}}
        @php
            $phases = ['not_started'=>['label'=>'Persiapan','icon'=>'bi-hourglass'],
                       'checkin'    =>['label'=>'Masuk',    'icon'=>'bi-door-open'],
                       'checkout'   =>['label'=>'Pulang',   'icon'=>'bi-door-closed'],
                       'finished'   =>['label'=>'Selesai',  'icon'=>'bi-check2-all']];
            $phaseKeys   = array_keys($phases);
            $currentIdx  = array_search($activity->attendance_phase, $phaseKeys);
        @endphp
        <div class="stepper d-flex align-items-center">
            @foreach($phases as $key => $info)
            @php
                $idx      = array_search($key, $phaseKeys);
                $isPassed = $currentIdx > $idx;
                $isCurr   = $currentIdx === $idx;
            @endphp
            <div class="stepper-item flex-fill text-center {{ $isCurr ? 'active' : '' }} {{ $isPassed ? 'passed' : '' }}">
                <div class="stepper-icon mx-auto mb-2">
                    @if($isPassed)<i class="bi bi-check-lg"></i>
                    @else<i class="bi {{ $info['icon'] }}"></i>
                    @endif
                </div>
                <div class="stepper-label">{{ $info['label'] }}</div>
            </div>
            @if(!$loop->last)
            <div class="stepper-line flex-fill {{ $isPassed ? 'passed' : '' }}"></div>
            @endif
            @endforeach
        </div>
    </div>
</div>


@php
    $summaryConfig = [
        'hadir' => ['color'=>'success','icon'=>'bi-check-circle-fill','label'=>'Hadir'],
        'izin'  => ['color'=>'info',   'icon'=>'bi-envelope-fill',    'label'=>'Izin'],
        'sakit' => ['color'=>'primary','icon'=>'bi-activity', 'label'=>'Sakit'],
        'alpha' => ['color'=>'danger', 'icon'=>'bi-x-circle-fill',    'label'=>'Alpha'],
    ];
    $totalAnggota  = $activeMembers->total();
    $sudahDiisi = ($summary['hadir'] ?? 0)
            + ($summary['izin']  ?? 0)
            + ($summary['sakit'] ?? 0)
            + ($summary['alpha'] ?? 0);
    $sudahCheckout = $attendances->whereNotNull('checkout_at')->count();
@endphp
 

<div class="row g-3 mb-4">
    @foreach($summaryConfig as $status => $cfg)
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-3 text-center">
                {{-- Bulatan Icon --}}
                <div class="bg-{{ $cfg['color'] }}-subtle text-{{ $cfg['color'] }} rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" 
                     style="width: 42px; height: 42px;">
                    <i class="bi {{ $cfg['icon'] }} fs-5"></i>
                </div>
                
                <h3 class="fw-bold mb-0 text-dark">{{ $summary[$status] }}</h3>
                <div class="text-muted fw-bold text-uppercase" style="font-size:.65rem; letter-spacing:1px;">
                    {{ $cfg['label'] }}
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Progress --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 p-4">
    @if($activity->attendance_phase === 'checkin')
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="fw-bold text-dark small"><i class="bi bi-door-open me-1 text-success"></i>Progress Check-in</span>
        <span class="badge bg-success-subtle text-success rounded-pill px-3">{{ $sudahDiisi }} / {{ $totalAnggota }}</span>
    </div>
    <div class="progress rounded-pill mb-2" style="height:8px;">
        <div class="progress-bar bg-success rounded-pill" style="width:{{ $totalAnggota > 0 ? round($sudahDiisi/$totalAnggota*100) : 0 }}%; transition:width .4s;"></div>
    </div>
    @if(($totalAnggota - $sudahDiisi) > 0)
    <small class="text-muted"><i class="bi bi-info-circle me-1"></i>{{ $totalAnggota - $sudahDiisi }} anggota belum diisi. Isi semua lalu klik <b>Buka Sesi Pulang</b>.</small>
    @else
    <small class="text-success fw-bold"><i class="bi bi-check-circle me-1"></i>Semua anggota sudah terisi! Anda bisa membuka sesi pulang.</small>
    @endif

    @elseif($activity->attendance_phase === 'checkout')
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="fw-bold text-dark small"><i class="bi bi-door-closed me-1 text-warning"></i>Progress Check-out</span>
        <span class="badge bg-warning-subtle text-warning rounded-pill px-3">{{ $sudahCheckout }} / {{ $summary['hadir'] }}</span>
    </div>
    <div class="progress rounded-pill mb-2" style="height:8px;">
        <div class="progress-bar bg-warning rounded-pill" style="width:{{ $summary['hadir'] > 0 ? round($sudahCheckout/$summary['hadir']*100) : 0 }}%; transition:width .4s;"></div>
    </div>
    @if($sudahCheckout < $summary['hadir'])
    <small class="text-muted"><i class="bi bi-info-circle me-1"></i>{{ $summary['hadir'] - $sudahCheckout }} anggota yang hadir belum checkout.</small>
    @else
    <small class="text-success fw-bold"><i class="bi bi-check-circle me-1"></i>Semua anggota sudah checkout! Anda bisa menyelesaikan absensi.</small>
    @endif
    @endif
</div>

{{-- ===== TABEL ===== --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
    <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-people-fill me-2 text-primary"></i>
                @if($activity->attendance_phase === 'checkin')
                    Daftar Kehadiran &mdash; <span class="text-success">Fase Masuk</span>
                @elseif($activity->attendance_phase === 'checkout')
                    Daftar Kehadiran &mdash; <span class="text-warning">Fase Pulang</span>
                @else
                    Rekap Kehadiran
                @endif
            </h5>
            <small class="text-muted">
                @if($activity->attendance_phase === 'checkin') Tandai status setiap anggota. Yang hadir dapat checkout nanti.
                @elseif($activity->attendance_phase === 'checkout') Klik tombol Checkout untuk anggota yang sudah pulang.
                @else Data absensi final — tidak dapat diubah.
                @endif
            </small>
        </div>

        {{-- Bulk buttons --}}
        @if(!$isCancelled && !$isFinished)
        @if($activity->attendance_phase === 'checkin')
        <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-sm btn-success fw-semibold rounded-pill px-3" onclick="bulkCheckin('hadir')">
                <i class="bi bi-check2-all me-1"></i>Semua Hadir</button>
            <button class="btn btn-sm btn-outline-info fw-semibold rounded-pill px-3" onclick="bulkCheckin('izin')">
                <i class="bi bi-envelope me-1"></i>Semua Izin</button>
            <button class="btn btn-sm btn-outline-danger fw-semibold rounded-pill px-3" onclick="bulkCheckin('alpha')">
                <i class="bi bi-x-circle me-1"></i>Semua Alpha</button>
        </div>
        @elseif($activity->attendance_phase === 'checkout')
        <button class="btn btn-sm btn-warning fw-semibold rounded-pill px-3" onclick="bulkCheckoutAll()">
            <i class="bi bi-box-arrow-right me-1"></i>Checkout Semua yang Hadir</button>
        @endif
        @endif
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4 py-3 border-0 th-label">Anggota</th>
                    <th class="py-3 border-0 text-center th-label">Status</th>
                    <th class="py-3 border-0 text-center th-label">Masuk</th>
                    <th class="py-3 border-0 text-center th-label">Pulang</th>
                    @if(!$isCancelled && !$isFinished)
                    <th class="py-3 pe-4 border-0 text-end th-label">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($activeMembers as $member)
                @php $att = $attendances[$member->user_id] ?? null; @endphp
                <tr id="row-{{ $member->user_id }}" class="{{ $att?->final_status === 'alpha' ? 'table-danger bg-opacity-25' : '' }}">
                    <td class="ps-4 py-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-circle bg-primary-subtle text-primary fw-bold flex-shrink-0">
                                {{ substr($member->user->name, 0, 1) }}
                            </div>
                            <div>
                                <div class="fw-bold text-dark">{{ $member->user->name }}</div>
                                <small class="text-muted">{{ $member->user->nisn ?? '-' }}</small>
                            </div>
                        </div>
                    </td>

                    <td class="text-center" id="badge-{{ $member->user_id }}">
                        @if($att?->final_status)
                        @php $fs = $att->final_status; @endphp
                        <span class="badge rounded-pill px-3 py-2 fw-bold
                            @if($fs=='hadir') bg-success
                            @elseif($fs=='izin') bg-info
                            @elseif($fs=='sakit') bg-primary
                            @elseif($fs=='alpha') bg-danger
                            @else bg-secondary @endif" style="font-size:.7rem;">
                            {{ strtoupper($fs) }}
                        </span>
                        @else
                        <span class="badge bg-light text-muted border rounded-pill px-3 py-2 fw-bold pending-badge" style="font-size:.7rem;">
                            PENDING
                        </span>
                        @endif
                    </td>

                    <td class="text-center" id="checkin-time-{{ $member->user_id }}">
                        @if($att?->checkin_at)
                        <span class="fw-bold text-dark">{{ $att->checkin_at->format('H:i') }}</span>
                        <div class="text-muted" style="font-size:.6rem;">{{ $att->checkin_at->format('d/m') }}</div>
                        @else
                        <span class="text-muted" style="opacity:.35;">--:--</span>
                        @endif
                    </td>

                    <td class="text-center" id="checkout-time-{{ $member->user_id }}">
                        @if($att?->checkout_at)
                        <span class="fw-bold text-dark">{{ $att->checkout_at->format('H:i') }}</span>
                        <div class="text-muted" style="font-size:.6rem;">{{ $att->checkout_at->format('d/m') }}</div>
                        @elseif($att?->final_status === 'hadir' && $activity->attendance_phase === 'checkout')
                        <span class="text-warning fw-bold small"><i class="bi bi-record-fill me-1"></i>Di Lokasi</span>
                        @else
                        <span class="text-muted" style="opacity:.35;">--:--</span>
                        @endif
                    </td>

                    @if(!$isCancelled && !$isFinished)
                    <td class="text-end pe-4">
                        @if($activity->attendance_phase === 'checkin')
                        <div class="d-flex justify-content-end gap-1" id="action-{{ $member->user_id }}">
                            @foreach([
                                'hadir' => ['success','check-circle-fill'],
                                'izin'  => ['info','clipboard-check-fill'],
                                'sakit' => ['primary','activity'],
                                'alpha' => ['danger','x-circle-fill']
                            ] as $s => $c)
                            <button title="{{ strtoupper($s) }}"
                                    onclick="setCheckin({{ $member->user_id }}, '{{ $s }}')"
                                    class="btn btn-sm fw-bold px-2 rounded-3 {{ ($att?->final_status==$s) ? 'btn-'.$c[0] : 'btn-outline-'.$c[0] }}"
                                    id="btn-{{ $member->user_id }}-{{ $s }}">
                                <i class="bi bi-{{ $c[1] }}"></i>
                                <span class="d-none d-md-inline ms-1">{{ ucfirst($s) }}</span>
                            </button>
                            @endforeach
                        </div>
                        @elseif($activity->attendance_phase === 'checkout')
                            @if($att?->final_status === 'hadir')
                                @if($att?->checkout_at)
                                <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-bold"
                                      style="font-size:.7rem;" id="checkout-btn-{{ $member->user_id }}">
                                    <i class="bi bi-check-circle me-1"></i>Sudah Checkout
                                </span>
                                @else
                                <button class="btn btn-sm btn-warning fw-semibold px-3 rounded-3"
                                        onclick="setCheckout({{ $member->user_id }})"
                                        id="checkout-btn-{{ $member->user_id }}">
                                    <i class="bi bi-box-arrow-right me-1"></i>Checkout
                                </button>
                                @endif
                            @else
                            <span class="text-muted fst-italic small">—</span>
                            @endif
                        @endif
                    </td>
                    @endif
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-5 text-muted fw-bold">Belum ada anggota terdaftar.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-center py-3 p-5">
     {{ $activeMembers->links() }}
    </div>
</div>

{{-- Hidden forms --}}
<form id="startCheckinForm"   method="POST" action="{{ route('pembina.activity.manual_start_checkin', [$eskul->id, $activity->id]) }}" class="d-none">@csrf</form>
<form id="openCheckoutForm"   method="POST" action="{{ route('pembina.activity.manual_open_checkout', [$eskul->id, $activity->id]) }}" class="d-none">@csrf</form>
<form id="finishManualForm"   method="POST" action="{{ route('pembina.activity.finish', [$eskul->id, $activity->id]) }}" class="d-none">@csrf</form>

</div>

{{-- ===== STYLES ===== --}}
<style>
body { background: #f0f4f8; font-family: 'Inter', sans-serif; }

/* Stepper */
.stepper { position: relative; }
.stepper-item { position: relative; }
.stepper-icon {
    width: 44px; height: 44px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; font-weight: 700;
    background: #e9ecef; color: #adb5bd;
    border: 3px solid #e9ecef;
    transition: all .3s;
}
.stepper-item.active .stepper-icon {
    background: #0d6efd; color: #fff; border-color: #0d6efd;
    box-shadow: 0 0 0 5px rgba(13,110,253,.15);
    transform: scale(1.1);
}
.stepper-item.passed .stepper-icon {
    background: #198754; color: #fff; border-color: #198754;
}
.stepper-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #adb5bd; margin-top: 4px; }
.stepper-item.active .stepper-label { color: #0d6efd; }
.stepper-item.passed .stepper-label { color: #198754; }
.stepper-line {
    height: 3px; background: #e9ecef; margin: 0 6px;
    align-self: flex-start; margin-top: 22px;
    flex-shrink: 1;
    transition: background .3s;
}
.stepper-line.passed { background: #198754; }

/* Summary cards */
.summary-card { transition: transform .2s; }
.summary-card:hover { transform: translateY(-3px); }
.summary-icon { width: 46px; height: 46px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }

/* Table */
.th-label { text-transform: uppercase; font-size: .68rem; letter-spacing: 1.5px; color: #6c757d; font-weight: 800; }
.avatar-circle { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }


    /* Tambahan CSS sedikit untuk feel yang lebih 'Pro' */
    .fw-extrabold { font-weight: 800; }
    .tracking-tight { letter-spacing: -0.025em; }
    .tracking-wider { letter-spacing: 0.05em; }
    .border-start-lg { border-left: 1px solid #dee2e6; }
    
    @media (max-width: 991px) {
        .border-start-lg { border-left: none; }
        .justify-content-lg-end { justify-content: flex-start !important; }
        .align-items-lg-end { align-items: flex-start !important; }
    }

    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: .7; }
    }

/* Pulse badge */
@keyframes pulseBadge { 0%,100% { opacity:1; } 50% { opacity:.6; } }
.phase-pulse { animation: pulseBadge 2s infinite; }
</style>

{{-- ===== SCRIPT ===== --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const CSRF            = "{{ csrf_token() }}";
const CHECKIN_URL     = "{{ route('pembina.activity.manual_checkin',    [$eskul->id, $activity->id]) }}";
const CHECKOUT_URL    = "{{ route('pembina.activity.manual_checkout_save', [$eskul->id, $activity->id]) }}";
const BULK_CI_URL     = "{{ route('pembina.activity.bulk_manual_checkin',  [$eskul->id, $activity->id]) }}";
const BULK_CO_URL     = "{{ route('pembina.activity.bulk_manual_checkout', [$eskul->id, $activity->id]) }}";

const STATUS_CLS = { hadir:'bg-success', izin:'bg-info', sakit:'bg-primary', alpha:'bg-danger' };
const BTN_COLOR  = { hadir:'success',    izin:'info',    sakit:'primary',     alpha:'danger'   };

/* ---- Mulai Sesi ---- */
function confirmStartCheckin() {
    Swal.fire({
        title: 'Mulai Sesi Masuk?',
        text: 'Anda akan membuka sesi check-in untuk kegiatan ini.',
        icon: 'question', showCancelButton: true,
        confirmButtonColor: '#0d6efd', cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-play-fill me-1"></i> Mulai Sekarang',
        cancelButtonText: 'Batal'
    }).then(r => { if (r.isConfirmed) document.getElementById('startCheckinForm').submit(); });
}

/* ---- Buka Sesi Pulang ---- */
function confirmOpenCheckout() {
    const pending = document.querySelectorAll('.pending-badge').length;
    Swal.fire({
        title: 'Buka Sesi Pulang?',
        html: pending > 0
            ? `<span class="text-danger fw-bold">${pending} anggota masih PENDING</span> dan akan otomatis jadi <b>ALPHA</b>.<br>Lanjutkan?`
            : 'Sesi masuk ditutup dan sesi pulang dibuka.',
        icon: pending > 0 ? 'warning' : 'question', showCancelButton: true,
        confirmButtonColor: '#f59e0b', cancelButtonColor: '#6c757d',
        confirmButtonText: 'Buka Sesi Pulang', cancelButtonText: 'Batal'
    }).then(r => { if (r.isConfirmed) document.getElementById('openCheckoutForm').submit(); });
}

/* ---- Selesaikan ---- */
function confirmFinishManual() {
    Swal.fire({
        title: 'Selesaikan Absensi?',
        text: 'Data akan dikunci dan tidak bisa diubah lagi.',
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#dc3545', cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Selesaikan!', cancelButtonText: 'Batal'
    }).then(r => { if (r.isConfirmed) document.getElementById('finishManualForm').submit(); });
}

/* ---- Checkin perorangan ---- */
function setCheckin(userId, status) {
    updateBadge(userId, status);
    updateBtns(userId, status);
    post(CHECKIN_URL, { user_id: userId, status })
        .then(d => {
            if (d.success && d.checkin_time)
                document.getElementById('checkin-time-'+userId).innerHTML =
                    `<span class="fw-bold text-dark">${d.checkin_time}</span>`;
            if (!d.success) Swal.fire('Gagal', d.message, 'error').then(()=>location.reload());
        }).catch(() => Swal.fire('Error','Gagal menyimpan.','error').then(()=>location.reload()));
}

/* ---- Checkout perorangan ---- */
function setCheckout(userId) {
    const btn = document.getElementById('checkout-btn-'+userId);
    if (btn) { btn.disabled=true; btn.innerHTML='<span class="spinner-border spinner-border-sm"></span>'; }
    post(CHECKOUT_URL, { user_id: userId })
        .then(d => {
            if (d.success) {
                document.getElementById('checkout-time-'+userId).innerHTML =
                    `<span class="fw-bold text-dark">${d.checkout_time}</span>`;
                const el = document.getElementById('checkout-btn-'+userId);
                if (el) el.outerHTML = `<span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-bold"
                    style="font-size:.7rem;" id="checkout-btn-${userId}">
                    <i class="bi bi-check-circle me-1"></i>Sudah Checkout</span>`;
            } else {
                Swal.fire('Gagal', d.message, 'error');
                if (btn) { btn.disabled=false; btn.innerHTML='<i class="bi bi-box-arrow-right me-1"></i>Checkout'; }
            }
        }).catch(()=>Swal.fire('Error','Gagal.','error').then(()=>location.reload()));
}
function bulkCheckin(status) {
    const lbl = { hadir:'Hadir', izin:'Izin', sakit:'Sakit', alpha:'Alpha' };
    Swal.fire({
        title: `Tandai Semua ${lbl[status]}?`,
        text: `Seluruh anggota eskul (termasuk di halaman lain) akan ditandai ${lbl[status]}.`,
        icon: 'warning', 
        showCancelButton: true,
        confirmButtonText: `Ya, Proses Semua`,
        cancelButtonText: 'Batal'
    }).then(r => {
        if (!r.isConfirmed) return;
        
        Swal.fire({ title:'Memproses...', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });
        
        // KIRIM HANYA STATUS KE CONTROLLER
        post(BULK_CI_URL, { status: status }) 
            .then(d => {
                if (d.success) {
                    Swal.fire({ 
                        icon:'success', 
                        title:'Berhasil', 
                        text: d.message,
                        timer: 1500
                    }).then(() => {
                        location.reload(); // WAJIB RELOAD untuk sinkronisasi pagination
                    });
                } else {
                    Swal.fire('Gagal', d.message, 'error');
                }
            }).catch(() => Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error'));
    });
}

/* ---- Bulk Checkout ---- */
function bulkCheckoutAll() {
    Swal.fire({
        title: 'Checkout Semua yang Hadir?',
        text: 'Waktu pulang semua anggota hadir akan dicatat sekarang.',
        icon: 'question', showCancelButton: true,
        confirmButtonColor: '#f59e0b', cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Checkout Semua', cancelButtonText: 'Batal'
    }).then(r => {
        if (!r.isConfirmed) return;
        Swal.fire({ title:'Memproses...', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });
        post(BULK_CO_URL, {})
            .then(d => {
                if (d.success)
                    Swal.fire({ icon:'success', title:'Berhasil', text:d.message, timer:1500, showConfirmButton:false })
                        .then(()=>location.reload());
                else Swal.fire('Gagal', d.message, 'error');
            }).catch(()=>Swal.fire('Error','Terjadi kesalahan.','error'));
    });
}

/* ---- Helpers ---- */
function post(url, body) {
    return fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN':CSRF, 'Content-Type':'application/json', 'Accept':'application/json' },
        body: JSON.stringify(body)
    }).then(r => r.json());
}
function updateBadge(id, status) {
    const c = document.getElementById('badge-'+id);
    if (c) c.innerHTML = `<span class="badge rounded-pill px-3 py-2 fw-bold ${STATUS_CLS[status]}" style="font-size:.7rem;">${status.toUpperCase()}</span>`;
}
function updateBtns(id, status) {
    ['hadir','izin','sakit','alpha'].forEach(s => {
        const b = document.getElementById(`btn-${id}-${s}`);
        if (!b) return;
        b.className = `btn btn-sm fw-bold px-2 rounded-3 ${s===status?'btn-'+BTN_COLOR[s]:'btn-outline-'+BTN_COLOR[s]}`;
    });
}
</script>
@endsection