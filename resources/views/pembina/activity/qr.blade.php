@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="max-width: 1200px;">

@php
    /**
     * STATE MANAGEMENT & CONSTANTS
     * Mengatur status sesi, pembatalan, dan fase akhir
     */
    $isSessionActive =
    $activeQrSession &&
    $activeQrSession->mode === $activity->attendance_phase &&
    $activeQrSession->opened_at &&
    $activeQrSession->expires_at &&
    now()->between(
        $activeQrSession->opened_at,
        $activeQrSession->expires_at
    );

$isCancelled = $activity->status === 'cancelled';
$isFinished = $activity->attendance_phase === 'finished';
$hasCheckinData = $attendances->count() > 0;
$firstCheckinSession = $activity->qrSessions
    ->where('mode','checkin')
    ->sortBy('opened_at')
    ->first();

    $lastCheckoutSession = $activity->qrSessions
        ->where('mode','checkout')
        ->sortByDesc('opened_at')
        ->first();

    $checkinOpenedAt = $firstCheckinSession?->opened_at;
    $checkoutClosedAt = $activity->ended_at ?? $lastCheckoutSession?->expires_at;
    $summary = [
        'hadir'  => ['count' => 0, 'color' => 'success', 'icon' => 'bi-check-circle'],
        'telat'  => ['count' => 0, 'color' => 'warning', 'icon' => 'bi-clock-history'],
        'izin'   => ['count' => 0, 'color' => 'info', 'icon' => 'bi-envelope'],
        'sakit'  => ['count' => 0, 'color' => 'primary', 'icon' => 'bi-activity'],
        'alpha'  => ['count' => 0, 'color' => 'danger', 'icon' => 'bi-x-circle'],
        'libur'  => ['count' => 0, 'color' => 'secondary', 'icon' => 'bi-calendar-x']
    ];

    foreach($attendances as $a){
        if($a->final_status === 'hadir' && $a->checkin_status === 'late'){
            $summary['telat']['count']++;
        } elseif($a->final_status && isset($summary[$a->final_status])){
            $summary[$a->final_status]['count']++;
        }

}

    // Definisi Step/Tahapan Kegiatan
    $phaseOrder = [
        'not_started' => 'Persiapan',
        'checkin'     => 'Masuk',
        'checkout'    => 'Pulang',
        'finished'    => 'Selesai'
    ];
    $start = \Carbon\Carbon::parse($activity->started_at);
    $end = $activity->ended_at 
    ? \Carbon\Carbon::parse($activity->ended_at)
    : null;

// Ambil langsung dari kolom checkin_open_at milik activity
$openTime = $activity->checkin_open_at 
    ? \Carbon\Carbon::parse($activity->checkin_open_at) 
    : null;

    $isBeforeOpenTime = $openTime ? now()->lt($openTime) : false; // Pakai waktu open, bukan waktu start
    $isAfterEnd = $end ? now()->gt($end) : false;
@endphp

{{-- ================= ALERT STATUS ================= --}}
@if($isCancelled)
<div class="alert alert-danger border-0 shadow-sm rounded-4 d-flex align-items-center p-3 mb-4">
    <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
        <i class="bi bi-exclamation-octagon-fill fs-4"></i>
    </div>
    <div>
        <h6 class="mb-0 fw-bold">Kegiatan Diliburkan / Dibatalkan</h6>
        <small class="opacity-75">Alasan: {{ $activity->cancel_reason ?? 'Tidak disebutkan' }}</small>
    </div>
</div>
@endif

@include('layouts.partials.eskul-nav')
{{-- ================= HEADER SECTION ================= --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
    <div class="card-body p-4">
        <div class="row align-items-center g-3">
            <div class="col-md-7">
                <h2 class="fw-bold mb-1 text-dark">{{ $activity->title }}</h2>
                <span class="badge bg-primary-subtle text-primary border-0 px-2 py-1">
                    <i class="bi bi-tag-fill me-1"></i>
                    {{ strtoupper($activity->type) }}
                </span>
                @if($activity->attendance_mode)
                <span class="badge {{ $activity->attendance_mode == 'qr' 
                        ? 'bg-primary-subtle text-primary' 
                        : 'bg-secondary-subtle text-secondary' }} 
                        border-0 px-2 py-1 ms-1">

                    <i class="bi {{ $activity->attendance_mode == 'qr' 
                        ? 'bi-qr-code' 
                        : 'bi-pencil-square' }} me-1"></i>

                    {{ strtoupper($activity->attendance_mode) }}
                </span>
                @endif
                <div class="mt-2">
                    <span class="badge bg-light text-dark border px-2 py-1">
                        <i class="bi bi-clock-fill me-1 text-warning"></i>
                        Jam Masuk:
                        <strong>{{ $start->format('H:i') }}</strong>
                    </span>

                    <span class="ms-2 badge bg-light text-muted border px-2 py-1">
                        <i class="bi bi-door-open me-1"></i>
                        Absen Dibuka:
                        <strong>
                            {{ $openTime ? $openTime->format('H:i') : '--:--' }}
                        </strong>
                    </span>
                    <span class="badge {{ $end ? 'bg-primary text-white' : 'bg-light text-muted' }} border px-2 py-1">
                        <i class="bi bi-patch-check-fill me-1 {{ $end ? 'text-' : '' }}"></i>
                        Jam Selesai: 
                        <strong>
                            {{ $end ? $end->format('H:i') : 'Belum Berakhir' }}
                        </strong>
                    </span>
                </div>
            </div>
            
            {{-- ACTION BUTTONS - DYNAMIC PER PHASE --}}
            <div class="col-md-5 d-flex justify-content-md-end gap-2 align-items-center">
                @if(!$isCancelled && !$isFinished)

                    {{-- ================= NOT STARTED ================= --}}
                    @if($activity->attendance_phase == 'not_started')

                        <button 
                            class="btn btn-primary px-4 py-2"
                            data-bs-toggle="modal"
                            data-bs-target="#checkinModal"
                            {{ $isBeforeOpenTime ? 'disabled' : '' }}
                        >
                            <i class="bi bi-play-fill me-1"></i>
                            {{ $isBeforeOpenTime 
                                ? 'Buka Pukul ' . ($openTime?->format('H:i') ?? '--:--') 
                                : 'Buka Check-in' 
                            }}
                        </button>

                    {{-- ================= CHECKIN ================= --}}
                    @elseif($activity->attendance_phase == 'checkin')

                        @if($isSessionActive)

                            <a href="{{ route('pembina.activity.qr.scan_view',[$eskul->id,$activity->id]) }}"
                               class="btn btn-success px-4 py-2">
                                <i class="bi bi-qr-code-scan me-2"></i>Mode Scanner
                            </a>

                        @elseif(!$hasCheckinData)

                            <button class="btn btn-outline-success"
                                data-bs-toggle="modal"
                                data-bs-target="#checkinModal">
                                Lanjut Scan
                            </button>

                        @else

                            <span class="text-muted small">Sesi sudah ditutup</span>

                        @endif

                        <button class="btn btn-warning"
                            data-bs-toggle="modal"
                            data-bs-target="#validationModal">
                            Validasi & Checkout
                        </button>

                    {{-- ================= CHECKOUT ================= --}}
                    @elseif($activity->attendance_phase == 'checkout')

                        @if($isSessionActive)

                            <a href="{{ route('pembina.activity.qr.scan_view',[$eskul->id,$activity->id]) }}"
                               class="btn btn-success px-4 py-2">
                                <i class="bi bi-qr-code-scan me-2"></i>Scan Pulang
                            </a>

                            <button class="btn btn-danger"
                                data-bs-toggle="modal"
                                data-bs-target="#closeCheckoutModal">
                                Akhiri Checkout
                            </button>

                        @elseif($activity->qrSessions->where('mode','checkout')->count() == 0)

                            <button class="btn btn-warning"
                                data-bs-toggle="modal"
                                data-bs-target="#checkoutModal">
                                Buka Sesi Pulang
                            </button>

                        @else

                            <span class="text-muted small">Sesi sudah ditutup</span>

                        @endif

                        @php
                            $hasCheckin = $activity->qrSessions->where('mode', 'checkin')->count() > 0;
                            $hasCheckout = $activity->qrSessions->where('mode', 'checkout')->count() > 0;

                            $isCheckoutClosed =
                                $activity->attendance_phase === 'checkout' &&
                                $activity->qrSessions->where('mode','checkout')->count() > 0 &&
                                !$isSessionActive;
                        @endphp

                        @if($hasCheckin && $hasCheckout && $isCheckoutClosed)
                            <button class="btn btn-danger px-4 py-2 shadow-sm fw-bold rounded-3"
                                onclick="confirmFinish()">
                                <i class="bi bi-flag-fill me-1"></i> Selesaikan
                            </button>
                        @else
                            <button class="btn btn-secondary px-4 py-2 shadow-sm fw-bold rounded-3"
                                onclick="Swal.fire('Checkout Belum Ditutup','Klik tombol AKHIRI CHECKOUT terlebih dahulu','warning')">
                                <i class="bi bi-lock-fill me-1"></i> Selesaikan
                            </button>
                        @endif

                    @endif

                @else
                    {{-- TAMPILAN JIKA SUDAH SELESAI ATAU DIBATALKAN --}}
                    <div class="bg-dark-subtle text-dark p-2 px-4 rounded-3 fw-bold border border-secondary-subtle">
                        <i class="bi bi-lock-fill me-2"></i>
                        {{ $isCancelled ? 'KEGIATAN DIBATALKAN' : 'AKTIVITAS SELESAI / TERKUNCI' }}
                    </div>
                @endif

                <form id="finishForm" method="POST" action="{{ route('pembina.activity.finish',[$eskul->id,$activity->id]) }}" class="d-none">
                    @csrf
                </form>
            </div>
        </div>
 
        <hr class="my-4 opacity-25">

        {{-- ALUR PROSES (STEPPER) --}}
        <div class="d-flex justify-content-between phase-container position-relative">
            @foreach($phaseOrder as $key => $label)
                @php
                    $isCurrent = $activity->attendance_phase == $key;
                    $isPassed = array_search($activity->attendance_phase, array_keys($phaseOrder)) > array_search($key, array_keys($phaseOrder));
                @endphp
                <div class="text-center phase-item {{ $isCurrent ? 'active' : '' }} {{ $isPassed ? 'passed' : '' }}">
                    <div class="phase-icon shadow-sm mb-2 mx-auto">
                        @if($isPassed)
                            <i class="bi bi-check-lg text-white"></i>
                        @else
                            <small class="fw-bold">{{ $loop->iteration }}</small>
                        @endif
                    </div>
                    <span class="small fw-bold d-block text-uppercase ls-1">{{ $label }}</span>
                </div>
            @endforeach
            <div class="phase-line"></div>
        </div>
    </div>
</div>

{{-- ================= RINGKASAN DATA (STATS) ================= --}}
<div class="row g-3 mb-4">
    @foreach($summary as $status => $data)
    <div class="col-lg-2 col-md-4 col-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden card-summary bg-white">
            <div class="card-body p-3 text-center position-relative">
                <div class="icon-shape bg-{{ $data['color'] }}-subtle text-{{ $data['color'] }} rounded-circle mb-2 mx-auto">
                    <i class="bi {{ $data['icon'] }}"></i>
                </div>
                <h4 class="fw-bold mb-0 text-dark">{{ $data['count'] }}</h4>
                <div class="text-muted small fw-bold text-uppercase" style="font-size: 0.65rem; letter-spacing: 1px;">{{ $status }}</div>
                <div class="status-indicator bg-{{ $data['color'] }}"></div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- ================= DAFTAR ANGGOTA ================= --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0 fw-bold text-dark">
                <i class="bi bi-people-fill me-2 text-primary"></i>Status Kehadiran Anggota
            </h5>
            <small class="text-muted">Kelola data presensi secara individu jika diperlukan</small>
        </div>
        <div class="badge bg-light text-primary border px-3 py-2 rounded-pill">
            Total: {{ $activeMembers->total() }} Anggota Terdaftar
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 custom-table">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4 py-3 sticky-col bg-light border-0">Anggota</th>
                    <th class="py-3 border-0 text-center">Jam Masuk</th>
                    <th class="py-3 border-0 text-center">Jam Pulang</th>
                    <th class="py-3 border-0 text-center">Status</th>
                    <th class="py-3 border-0 text-center">Metode</th>
                </tr>
            </thead>

            <tbody>
            @forelse($activeMembers as $member)
                @php $att = $attendances[$member->user_id] ?? null; @endphp
                <tr>
                    <td class="ps-4 sticky-col bg-white">
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle me-3 bg-primary-subtle text-primary fw-bold shadow-sm">
                                {{ substr($member->user->name, 0, 1) }}
                            </div>
                            <div>
                                <div class="fw-bold text-dark">{{ $member->user->name }}</div>
                                <div class="text-muted small">{{ $member->user->nisn }}</div>
                            </div>
                        </div>
                    </td>

                    <td class="text-center">
                        @if($att?->checkin_at)
                            <div class="fw-bold text-dark">{{ $att->checkin_at->format('H:i') }}</div>
                            
                            @if($att->checkin_status == 'late')
                                <span class="badge bg-danger-subtle text-danger px-2 py-0" style="font-size: 0.6rem;">
                                    TELAT {{ $att->late_minutes ?? 0 }} MNT
                                </span>
                            @else
                                <span class="badge bg-success-subtle text-success px-2 py-0" style="font-size: 0.6rem;">
                                    AWAL {{ $att->early_minutes ?? 0 }} MNT
                                </span>
                            @endif
                        @else
                            <span class="text-muted opacity-25 small italic">--:--</span>
                        @endif
                    </td>

                    <td class="text-center">
                        @if($att?->checkout_at)
                            <div class="fw-bold text-dark">{{ $att->checkout_at->format('H:i') }}</div>
                            <span class="badge bg-success-subtle text-success px-2 py-0" style="font-size: 0.6rem;">CHECKED OUT</span>
                        @elseif($att?->checkin_at && !$att->checkout_at)
                            <span class="text-warning small fw-bold">
                                <i class="bi bi-clock-history me-1"></i> BELUM CHECKOUT
                            </span>
                        @else
                            <span class="text-muted opacity-25 small">--:--</span>
                        @endif
                    </td>

                    <td class="text-center">
                        @if($att?->final_status == 'hadir')
                            <span class="badge {{ $att->checkin_status == 'late' ? 'bg-warning text-dark' : 'bg-success' }} fw-bold">
                                HADIR {{ $att->checkin_status == 'late' ? '(TELAT)' : '' }}
                            </span>
                        @elseif($att?->final_status == 'izin')
                            <span class="badge bg-info fw-bold">IZIN</span>
                        @elseif($att?->final_status == 'sakit')
                            <span class="badge bg-primary fw-bold">SAKIT</span>
                        @elseif($att?->final_status == 'alpha')
                            <span class="badge bg-danger fw-bold">ALPHA</span>
                        @elseif($att?->final_status == 'libur')
                            <span class="badge bg-secondary fw-bold">LIBUR</span>
                        @else
                            <span class="badge bg-light text-muted">PENDING</span>
                        @endif
                    </td>

                    <td class="text-center">
                        @if($att)
                            <span class="text-uppercase fw-bold d-block" style="font-size: 0.65rem; color: {{ $att->attendance_source == 'scan' ? '#198754' : '#0d6efd' }}">
                                <i class="bi {{ $att->attendance_source == 'scan' ? 'bi-qr-code' : ($att->attendance_source == 'manual' ? 'bi-pencil-square' : 'bi-cpu') }} me-1"></i>
                                {{ $att->attendance_source }}
                            </span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <img src="https://cdn-icons-png.flaticon.com/512/6134/6134065.png" width="80" class="opacity-25 mb-3">
                        <p class="text-muted fw-bold">Belum ada anggota yang terdaftar.</p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-3">
        {{ $activeMembers->links() }}
    </div>
</div>

</div>

 
{{-- 2. MODAL OPEN SESSION (CHECK-IN) --}}
<div class="modal fade" id="checkinModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('pembina.activity.session.open',[$eskul->id,$activity->id]) }}" class="modal-content border-0 shadow-lg rounded-4">
            @csrf
            <input type="hidden" name="mode" value="checkin">
            <div class="modal-header bg-primary text-white rounded-top-4 py-3">
                <h5 class="fw-bold mb-0 mx-auto">Konfigurasi Scan Masuk</h5>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <i class="bi bi-qr-code-scan text-primary" style="font-size: 3rem;"></i>
                    <p class="text-muted small px-3">Atur waktu pengerjaan presensi bagi siswa yang membawa perangkat.</p>
                </div>
               <div class="text-center">
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle me-1"></i>
                        Absensi mengikuti jadwal kegiatan.<br>
                        Siswa bisa mulai scan sesuai waktu yang telah ditentukan.
                    </div>
                </div>
            </div>
           <div class="modal-footer border-0">
                <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" {{ $isBeforeOpenTime ? 'disabled' : '' }}>
                    {{ $isBeforeOpenTime 
                        ? 'Buka Pukul ' . ($openTime?->format('H:i') ?? '--:--') 
                        : 'Mulai Sekarang' 
                    }}
                </button>
            </div>
        </form>
    </div>
</div>

{{-- 3. MODAL VALIDASI MASSAL (Jembatan Checkin -> Checkout) --}}
<div class="modal fade" id="validationModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-warning py-3 border-0">
                <div class="d-flex align-items-center">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                        <i class="bi bi-shield-check text-warning fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Validasi Sesi Masuk</h5>
                        <small class="text-dark opacity-75">Tentukan status siswa yang tidak melakukan scan</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body p-0"> {{-- P-0 agar list group nempel ke pinggir lebih rapi --}}
                <div class="p-4 pb-2">
                    <div class="alert alert-light border-0 shadow-sm d-flex align-items-center mb-4 rounded-3">
                        <i class="bi bi-info-circle-fill fs-5 me-3 text-warning"></i>
                        <small class="text-muted">Siswa yang dibiarkan (tidak dicentang/divalidasi) akan otomatis dianggap <b class="text-danger">ALPHA</b> saat sesi pulang dibuka.</small>
                    </div>

                    @if(count($belumAbsen) > 0)
                    <div class="d-flex justify-content-between align-items-center mb-3 px-2">
                        <h6 class="fw-bold mb-0 text-dark text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px;">
                            Daftar Belum Presensi ({{ count($belumAbsen) }})
                        </h6>
                        <div class="form-check">
                            <input class="form-check-input border-warning student-checkbox-all" type="checkbox" id="selectAllStudents">
                            <label class="form-check-label small fw-bold text-muted" for="selectAllStudents">Pilih Semua</label>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="list-group list-group-flush border-top border-bottom" style="max-height: 400px; overflow-y: auto;">
                    @forelse($belumAbsen as $ba)
                    <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 px-4 border-0">
                        <div class="d-flex align-items-center">
                            <div class="form-check me-3">
                                <input class="form-check-input student-checkbox border-secondary" type="checkbox" value="{{ $ba->user_id }}">
                            </div>
                            <div class="avatar-circle-sm me-3 bg-light text-secondary fw-bold small">
                                {{ substr($ba->user->name, 0, 1) }}
                            </div>
                            <div>
                                <span class="fw-bold d-block text-dark">{{ $ba->user->name }}</span>
                                <code class="small text-muted">{{ $ba->user->nisn }}</code>
                            </div>
                        </div>
                        <div class=" d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-info px-3 fw-bold" onclick="bulkUpdate('izin', [{{ $ba->user_id }}])">
                                <i class="bi bi-envelope-fill me-1"></i> IZIN
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary px-3 fw-bold text-white" onclick="bulkUpdate('sakit', [{{ $ba->user_id }}])">
                                <i class="bi bi-plus-circle-fill me-1"></i> SAKIT
                            </button>
                            <button 
                                type="button"  class="btn btn-sm btn-success fw-bold" onclick="bulkUpdate('hadir',[{{ $ba->user_id }}])">
                                <i class="bi bi-check-circle me-1"></i>    HADIR
                            </button>

                        </div>
                    </div>
                    @empty
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="bi bi-check2-all text-success" style="font-size: 3rem;"></i>
                        </div>
                        <h6 class="fw-bold">Luar Biasa!</h6>
                        <p class="text-muted small">Semua anggota sudah melakukan presensi masuk.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <div class="modal-footer border-0 p-4 bg-light shadow-inner">
            <div class="w-100 d-flex flex-wrap gap-2 align-items-center justify-content-between">
                <div class="d-flex gap-2">
                     <button
                        class="btn btn-success btn-sm fw-bold px-3 text-white rounded-pill shadow-sm"
                        onclick="triggerBulk('hadir')">
                        <i class="bi bi-check-circle me-1"></i>
                        HADIRKAN TERPILIH
                        </button>

                        <button
                       class="btn btn-info btn-sm fw-bold px-3 text-white rounded-pill shadow-sm"
                        onclick="triggerBulk('izin')">
                        <i class="bi bi-envelope-fill me-1"></i>
                        IZIN
                        </button>

                        <button
                      class="btn btn-secondary  btn-sm fw-bold px-3 text-white rounded-pill shadow-sm"
                        onclick="triggerBulk('sakit')">
                        <i class="bi bi-heart-pulse me-1"></i>
                        SAKIT
                        </button>
                </div>
                                    
                <form action="{{ route('pembina.activity.finalize_validation', [$eskul->id, $activity->id]) }}" method="POST">
                    @csrf
                 
                    <button type="submit" class="btn btn-warning fw-extrabold px-4 py-2 rounded-3 shadow border-bottom border-dark border-3" 
                            onclick="return confirm('Siswa yang tidak masuk daftar hadir/izin/sakit akan otomatis dianggap ALPHA. Lanjutkan ke Sesi Pulang?')">
                        FINALISASI & BUKA PULANG <i class="bi bi-arrow-right-circle-fill ms-2"></i>
                    </button>
                </form>
            </div>
        </div>
        </div>
    </div>
</div>

{{-- 4. MODAL OPEN SESSION (CHECK-OUT) --}}
<div class="modal fade" id="checkoutModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('pembina.activity.session.open',[$eskul->id,$activity->id]) }}" class="modal-content border-0 shadow-lg rounded-4">
            @csrf
            <input type="hidden" name="mode" value="checkout">
            <input type="hidden" name="late_tolerance_minutes" value="0">
            <div class="modal-header border-0 bg-warning text-dark py-3">
                <h5 class="fw-bold mb-0 mx-auto">Sesi Pulang (Scan Checkout)</h5>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px;">
                    <i class="bi bi-door-open-fill fs-1"></i>
                </div>
                <h6 class="fw-bold">Buka Pintu Keluar</h6>
                <p class="small text-muted mb-4">Siswa yang sudah hadir (Scan/Manual) wajib melakukan scan checkout untuk menutup kehadirannya.</p>
                
                <div class="alert alert-info small">
                    Sesi checkout mengikuti waktu kegiatan yang telah ditentukan.
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">Tutup</button>
                <button class="btn btn-warning px-4 fw-bold shadow">AKTIFKAN SEKARANG</button>
            </div>
        </form>
    </div>
</div>
{{-- 5. MODAL TUTUP CHECKOUT --}}
{{-- 5. MODAL TUTUP CHECKOUT --}}
<div class="modal fade" id="closeCheckoutModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">

            <div class="modal-header bg-danger py-3 border-0">
                <div class="d-flex align-items-center">

                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center me-3"
                         style="width:40px;height:40px;">
                        <i class="bi bi-door-closed text-danger fs-4"></i>
                    </div>

                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Tutup Sesi Checkout</h5>
                        <small class="text-dark opacity-75">
                            Tandai checkout manual untuk siswa yang belum scan
                        </small>
                    </div>

                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>


            <div class="modal-body p-0">

                <div class="p-4 pb-2">

                    <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4 rounded-3">

                        <i class="bi bi-exclamation-triangle-fill fs-5 me-3"></i>

                            <div>

                            <strong>Penting</strong>

                            <div class="small text-muted">

                            Siswa yang <b>tidak dicentang</b> akan dianggap
                            <b>TIDAK MELAKUKAN CHECKOUT</b> dan statusnya tetap aktif.

                            </div>

                        </div>

                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3 px-2">

                        <div class="d-flex justify-content-between align-items-center mb-3 px-2">

                        <h6 class="fw-bold mb-0 text-dark text-uppercase"
                        style="font-size:.75rem;letter-spacing:1px;">
                        Daftar Belum Checkout
                        </h6>

                        <div class="form-check">
                        <input
                        class="form-check-input border-danger"
                        type="checkbox"
                        id="selectAllCheckout">
                        <label class="form-check-label small fw-bold text-muted">
                        Pilih Semua
                        </label>
                        </div>

                        </div>

                    </div>

                </div>


                <div class="list-group list-group-flush border-top border-bottom"
                     style="max-height:400px;overflow-y:auto;">

                    @foreach($activeMembers as $member)

                        @php
                            $att = $attendances[$member->user_id] ?? null;
                        @endphp

                        @if($att && $att->checkin_at && !$att->checkout_at)

                        <div class="list-group-item d-flex justify-content-between align-items-center py-3 px-4 border-0">

                            <div class="d-flex align-items-center">

                                <div class="form-check me-3">
                                    <input
                                        class="form-check-input checkout-checkbox border-danger"
                                        type="checkbox"
                                        value="{{ $member->user_id }}"
                                    >
                                </div>

                                <div class="avatar-circle-sm me-3 bg-light text-secondary fw-bold small">
                                    {{ substr($member->user->name,0,1) }}
                                </div>

                                <div>
                                    <span class="fw-bold d-block text-dark">
                                        {{ $member->user->name }}
                                    </span>

                                    <code class="small text-muted">
                                        {{ $member->user->nisn }}
                                    </code>
                                </div>
                            </div>
                            <span class="badge bg-warning-subtle text-warning fw-bold">
                                BELUM CHECKOUT
                            </span>
                        </div>
                        @endif
                    @endforeach
                </div>

            </div>


            <div class="modal-footer border-0 p-4 bg-light shadow-inner">

                <div class="w-100 d-flex justify-content-between align-items-center">

                    <button
                        type="button"
                        class="btn btn-light fw-bold px-4"
                        data-bs-dismiss="modal"
                    >
                        Batal
                    </button>


                    <form
                        method="POST"
                        action="{{ route('pembina.activity.checkout.close',[$eskul->id,$activity->id]) }}"
                        id="closeCheckoutForm"
                    >

                        @csrf

                        <input
                            type="hidden"
                            name="manual_checkout_ids"
                            id="manualCheckoutIds"
                        >

                        <button class="btn btn-danger fw-extrabold px-4 py-2 rounded-3 shadow border-bottom border-dark border-3">

                            AKHIRI CHECKOUT

                            <i class="bi bi-door-closed-fill ms-2"></i>

                        </button>

                    </form>

                </div>

            </div>

        </div>
    </div>
</div>
{{-- ================= STYLING & SCRIPT ================= --}}

<style>
    @import url('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css');
    
    :root {
        --primary-soft: #eef2ff;
        --accent-blue: #0d6efd;
    }

    body { background-color: #f4f7fa; font-family: 'Inter', sans-serif; }
    
    /* Stepper UI */
    .phase-line { position: absolute; top: 18px; left: 10%; right: 10%; height: 3px; background: #e9ecef; z-index: 1; }
    .phase-item { width: 25%; color: #adb5bd; z-index: 2; position: relative; }
    .phase-item.active { color: var(--accent-blue); }
    .phase-item.passed { color: #198754; }
    .phase-icon { width: 40px; height: 40px; background: #fff; border: 3px solid #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; }
    .phase-item.active .phase-icon { border-color: var(--accent-blue); background: var(--accent-blue); color: white; transform: scale(1.1); box-shadow: 0 0 15px rgba(13, 110, 253, 0.3); }
    .phase-item.passed .phase-icon { border-color: #198754; background: #198754; color: white; }
    
    /* Card Styles */
    .card-summary { border-bottom: 4px solid transparent !important; transition: all 0.2s ease; }
    .card-summary:hover { transform: translateY(-3px); }
    .status-indicator { position: absolute; bottom: 0; left: 0; height: 4px; width: 100%; }
    .icon-shape { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; }
    
    /* Table Enhancements */
    .custom-table th { text-transform: uppercase; font-size: 0.7rem; letter-spacing: 1.5px; color: #6c757d; font-weight: 800; border-bottom: 1px solid #f0f0f0 !important; }
    .sticky-col { position: sticky; left: 0; z-index: 5; border-right: 1px solid #f8f9fa; }
    .avatar-circle { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
    
    .ls-1 { letter-spacing: 0.5px; }
    .italic { font-style: italic; }

    /* Animations */
    .animate-pulse { animation: pulse-red 2s infinite; }
    @keyframes pulse-red {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }

    .avatar-circle-sm {
    width: 35px;
    height: 35px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #eee;
}

.fw-extrabold { font-weight: 800; }

#validationModal .list-group-item:hover {
    background-color: #fff9ed; /* Highlight warna kuning lembut saat hover */
}

/* Custom Checkbox Color */
.student-checkbox:checked {
    background-color: #ffc107;
    border-color: #ffc107;
}
/* ===== Attendance Meta Info ===== */

/* ===== Attendance Meta Info ===== */

.attendance-meta{
display:flex;
align-items:center;
gap:10px;
font-size:.85rem;
font-weight:600;
margin-top:8px;
flex-wrap:wrap;
}

.meta-item{
display:flex;
align-items:center;
gap:6px;
padding:6px 10px;
border-radius:10px;
}

.meta-checkin{
background:#e8f7ee;
color:#198754;
border:1px solid #b6e3c7;
}

.meta-checkout{
background:#fdeaea;
color:#dc3545;
border:1px solid #f5c2c7;
}

.meta-divider{
color:#adb5bd;
font-weight:700;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    /**
     * Modal Helper for Manual Input
     */
    function openManualModal(userId, userName) {
        document.getElementById('manualUserId').value = userId;
        document.getElementById('manualTitle').innerHTML = `Set Status: <span class="text-primary">${userName}</span>`;
        new bootstrap.Modal(document.getElementById('manualModal')).show();
    }

    /**
     * Confirmation for Activity Finish
     */
    function confirmFinish() {
        Swal.fire({
            title: 'Selesaikan Kegiatan?',
            text: "Data absensi akan dikunci dan tidak dapat diubah lagi.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Selesaikan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('finishForm').submit();
            }
        });
    }

    /**
     * Bulk Update Function (AJAX to Controller)
     */
    function triggerBulk(status) {
        const checkboxes = document.querySelectorAll('.student-checkbox:checked');
        const ids = Array.from(checkboxes).map(cb => cb.value);
        
        if (ids.length === 0) {
            Swal.fire('Info', 'Pilih minimal satu siswa dahulu.', 'info');
            return;
        }
        bulkUpdate(status, ids);
    }

    function bulkUpdate(status, userIds) {
        Swal.fire({
            title: 'Proses...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        fetch("{{ route('pembina.activity.bulk_manual', [$eskul->id, $activity->id]) }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                user_ids: userIds,
                status: status
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                Swal.fire('Gagal', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
    const selectAllBtn = document.getElementById('selectAllStudents');
    if (selectAllBtn) {
        selectAllBtn.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = this.checked;
                // Memberi feedback visual pada baris yang terpilih
                const row = cb.closest('.list-group-item');
                if(this.checked) row.style.backgroundColor = "#fff9ed";
                else row.style.backgroundColor = "transparent";
            });
        });
    }

    // Feedback visual untuk checkbox individual
    const individualCheckboxes = document.querySelectorAll('.student-checkbox');
    individualCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            const row = this.closest('.list-group-item');
            if(this.checked) row.style.backgroundColor = "#fff9ed";
            else row.style.backgroundColor = "transparent";
            
            // Uncheck "Select All" jika ada satu yang tidak dicentang
            if(!this.checked && selectAllBtn) selectAllBtn.checked = false;
        });
    });
});



document
.getElementById('closeCheckoutForm')
?.addEventListener('submit', function (e){

e.preventDefault()

const checked = document.querySelectorAll('.checkout-checkbox:checked')
const ids = Array.from(checked).map(cb => cb.value)

Swal.fire({
icon: 'warning',
title: 'Akhiri sesi checkout?',
html: `
Siswa yang dicentang akan <b>checkout manual</b>.<br>
Siswa yang tidak dicentang akan dianggap <b>belum checkout</b>.
`,
showCancelButton: true,
confirmButtonText: 'Ya, Akhiri Checkout',
cancelButtonText: 'Batal'
}).then((result) => {

if(result.isConfirmed){

document.getElementById('manualCheckoutIds').value =
JSON.stringify(ids)

e.target.submit()

}

})

})

document.addEventListener("DOMContentLoaded", function(){

const selectAllCheckout = document.getElementById("selectAllCheckout")

if(selectAllCheckout){

selectAllCheckout.addEventListener("change", function(){

const checkboxes = document.querySelectorAll(".checkout-checkbox")

checkboxes.forEach(cb => {
cb.checked = this.checked
})

})

}

})
    
</script>

@endsection