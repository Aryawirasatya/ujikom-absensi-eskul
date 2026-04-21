@extends('layouts.app')

@section('content')
<div class="container py-4 py-md-5 d-flex justify-content-center align-items-center" style="min-height: 90vh;">

    <div class="card-master-wrapper  w-100 d-flex justify-content-center">
        
        <div class="animated-border-container">
            
            <div class="main-presence-card">
                
                <div class="card-header-visual"></div>

                <div class="card-content-area shadow-xl">
                    
                    <div class="profile-section">
                        <div class="profile-frame-container" id="btnBukaModal" data-bs-target="#photoModal">
                            @if($user->photo)
                                <img src="{{ asset('storage/students/'.$user->photo) }}" alt="Profile" class="profile-img-main">
                            @else
                                <div class="profile-img-main placeholder-bg">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                            @endif
                            <div class="zoom-badge"><i class="bi bi-search"></i></div>
                        </div>
                    </div>

                    <div class="student-info">
                        <h3 class="student-name">{{ $user->name }}</h3>
                        <div class="class-badge">
                            <i class="bi bi-mortarboard-fill"></i>
                            <span>Kelas {{ $academic->grade }} {{ $academic->class_label }}</span>
                        </div>
                    </div>

                    <div class="divider-container">
                        <span class="divider-text">Digital Identity</span>
                    </div>

                    <div class="qr-wrapper w-100 d-flex flex-column align-items-center" data-bs-toggle="modal" data-bs-target="#qrModal" style="cursor: pointer;">
                        <div class="qr-box">
                            {!! QrCode::size(180)
                                ->color(32, 32, 123)
                                ->margin(0)
                                ->generate($qrValue) !!}
                        </div>
                        <div class="qr-overlay-text">
                            <i class="bi bi-fullscreen me-1"></i> Perbesar QR
                        </div>
                    </div>
                    
                    <div class="card-footer-info">
                        <div class="clock-display">
                            <h2 id="clock">00:00:00</h2>
                            <p class="clock-label">Waktu Lokal Sekarang</p>
                        </div>

                        <div class="presence-status">
                            <div class="indicator-group">
                                <span class="dot"></span>
                                <span class="pulse"></span>
                            </div>
                            <span class="status-text">Sistem Absensi Aktif</span>
                        </div>
                    </div>

                </div> 
            </div>
        </div> 
    </div>
</div>

<div class="modal fade" id="photoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered px-3">
        <div class="modal-content border-0 bg-transparent text-center">
            <button type="button" class="btn-close btn-close-white ms-auto mb-2" data-bs-dismiss="modal"></button>
            <div class="modal-photo-wrapper">
                @if($user->photo)
                    <img src="{{ asset('storage/students/'.$user->photo) }}" class="modal-img">
                @else
                    <div class="modal-placeholder">
                        <i class="bi bi-person-circle"></i>
                        <p>Foto Tidak Tersedia</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="qrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered px-3">
        <div class="modal-content border-0 bg-transparent text-center">
            <button type="button" class="btn-close btn-close-white ms-auto mb-2" data-bs-dismiss="modal"></button>
            <div class="modal-qr-wrapper p-4 bg-white shadow-lg" style="border-radius: 30px; display: inline-block; max-width: 100%;">
                <div class="qr-responsive-container">
                     {!! QrCode::size(300)
                        ->color(32, 32, 123)
                        ->margin(1)
                        ->generate($qrValue) !!}
                </div>
                <h5 class="mt-3 fw-bold text-dark mb-0">Scan Me</h5>
                <small class="text-muted d-block text-truncate px-2">{{ $user->name }}</small>
            </div>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');

    :root {
        --primary-gradient: linear-gradient(135deg, #664ffe 0%, #20207b 100%);
        --card-bg: #ffffff;
        --text-dark: #1e293b;
        --text-muted: #64748b;
        --success: #10b981;
    }

    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background-color: #f8fafc;
        overflow-x: hidden;
    }

    .card-master-wrapper {
        border-radius: 28px;
        position: relative;
        padding: 0;
        background: transparent;
    }

    .animated-border-container {
        position: relative;
        width: 100%;
        max-width: 400px; /* Diubah dari width tetap */
        padding: 3px;
        border-radius: 28px;
        overflow: hidden;
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 0 auto;
    }

    .animated-border-container::before {
        content: '';
        position: absolute;
        width: 200%; /* Ditingkatkan untuk menutupi sudut saat rotasi */
        height: 200%;
        background: conic-gradient(transparent, #664ffe, #10b981, transparent 30%);
        animation: rotate 5s linear infinite;
        pointer-events: none;
    }

    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .main-presence-card {
        position: relative;
        z-index: 2;
        width: 100%;
        background: var(--card-bg);
        border-radius: 25px;
        overflow: hidden;
        box-shadow:#64748b;
    }

    .card-header-visual {
        height: 100px;
        background: var(--primary-gradient);
    }

    .card-content-area {
        padding: 0 1rem 1.5rem 1rem; /* Responsif padding */
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    @media (min-width: 768px) {
        .card-content-area {
            padding: 0 1.5rem 2rem 1.5rem;
        }
    }

    .profile-section {
        margin-top: -50px;
        margin-bottom: 1rem;
    }

    .profile-frame-container {
        position: relative;
        cursor: pointer;
        width: 100px;
        height: 100px;
        background: white;
        padding: 5px;
        border-radius: 30px;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    .profile-frame-container:hover { transform: scale(1.05); }

    .profile-img-main {
        width: 100%;
        height: 100%;
        border-radius: 25px;
        object-fit: cover;
    }

    .placeholder-bg {
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        font-size: 2.5rem;
        color: #cbd5e1;
    }

    .zoom-badge {
        position: absolute;
        bottom: 0;
        right: 0;
        background: #664ffe;
        color: white;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 3px solid white;
        font-size: 0.75rem;
    }

    .student-info {
        text-align: center;
        margin-bottom: 1.25rem;
        width: 100%;
    }

    .student-name {
        font-weight: 800;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
        font-size: 1.2rem; /* Diperkecil sedikit untuk mobile */
        word-wrap: break-word;
    }

    @media (min-width: 768px) {
        .student-name { font-size: 1.35rem; }
    }

    .class-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: #f0eeff;
        color: #664ffe;
        padding: 0.4rem 1rem;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.75rem;
    }

    .divider-container {
        width: 100%;
        display: flex;
        align-items: center;
        margin-bottom: 1.25rem;
    }

    .divider-container::before, .divider-container::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e2e8f0;
    }

    .divider-text {
        padding: 0 0.75rem;
        font-size: 0.6rem;
        font-weight: 800;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.15em;
    }

    .qr-wrapper {
        position: relative;
        background: white;
        padding: 1rem;
        border-radius: 20px;
        border: 1.5px solid #f1f5f9;
        margin-bottom: 1.25rem;
        transition: all 0.3s ease;
        max-width: 220px; /* Membatasi agar tidak terlalu lebar di mobile */
    }

    .qr-wrapper svg {
        max-width: 100%;
        height: auto !important; /* Membuat QR responsif */
    }

    .qr-wrapper:hover {
        transform: translateY(-5px);
        border-color: #664ffe;
        box-shadow: 0 10px 20px rgba(102, 79, 254, 0.1);
    }

    .qr-overlay-text {
        font-size: 0.65rem;
        font-weight: 700;
        color: #664ffe;
        margin-top: 0.5rem;
        text-align: center;
        opacity: 0.7;
    }

    .card-footer-info {
        width: 100%;
        text-align: center;
    }

    .clock-display {
        background: #f8fafc;
        padding: 0.75rem;
        border-radius: 18px;
        margin-bottom: 1rem;
    }

    #clock {
        font-weight: 800;
        font-size: 1.75rem;
        color: var(--text-dark);
        margin: 0;
        letter-spacing: 1px;
    }

    @media (min-width: 768px) {
        #clock { font-size: 2rem; letter-spacing: 2px; }
    }

    .clock-label {
        font-size: 0.6rem;
        color: var(--text-muted);
        text-transform: uppercase;
        font-weight: 700;
        margin: 0;
    }

    .presence-status {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
    }

    .indicator-group {
        position: relative;
        width: 10px;
        height: 10px;
    }

    .dot {
        position: absolute;
        width: 10px;
        height: 10px;
        background: var(--success);
        border-radius: 50%;
        z-index: 2;
        left: 0;
        top: 0;
    }

    .pulse {
        position: absolute;
        width: 10px;
        height: 10px;
        background: var(--success);
        border-radius: 50%;
        animation: pulse-ring 2s infinite;
        left: 0;
        top: 0;
    }

    @keyframes pulse-ring {
        0% { transform: scale(1); opacity: 0.8; }
        100% { transform: scale(4); opacity: 0; }
    }

    .status-text {
        font-size: 0.7rem;
        font-weight: 700;
        color: var(--text-muted);
    }

    /* MODAL STYLE */
    .modal-photo-wrapper {
        padding: 5px;
        background: white;
        border-radius: 30px;
        display: inline-block;
    }

    .modal-img {
        max-width: 100%;
        border-radius: 25px;
        max-height: 70vh;
        border: 4px solid white;
    }

    .qr-responsive-container svg {
        max-width: 100%;
        height: auto !important;
    }

    .shadow-xl {
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
    }

    .modal-placeholder {
        padding: 40px;
        background: white;
        border-radius: 25px;
        color: #cbd5e1;
    }
    
    .modal-placeholder i { font-size: 4rem; }
</style>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
    const clock = document.getElementById('clock');

    function updateClock() {
        const now = new Date();

        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');

        clock.textContent = `${hours}:${minutes}:${seconds}`;
    }

    updateClock();
    setInterval(updateClock, 1000);
});
document.addEventListener('DOMContentLoaded', function () {
    // Tombol Foto
    const btnFoto = document.getElementById('btnBukaModal');
    if (btnFoto) {
        btnFoto.addEventListener('click', function () {
            console.log("Tombol diklik!");
            const myModal = new bootstrap.Modal(document.getElementById('photoModal'));
            myModal.show();
        });
    }

    // Tombol QR
    const btnQR = document.querySelector('.qr-wrapper');
    if (btnQR) {
        btnQR.addEventListener('click', function () {
            const qrModal = new bootstrap.Modal(document.getElementById('qrModal'));
            qrModal.show();
        });
    }
});
</script>

@endpush