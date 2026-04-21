@extends('layouts.app')

@section('content')

<style>
body{
    background:#f4f6f9;
}

/* ================= SCANNER CARD ================= */
.scanner-card{
    background:#fff;
    border-radius:20px;
    box-shadow:0 8px 25px rgba(0,0,0,.06);
    overflow:hidden;
    border:1px solid #e9ecef;
}

#reader{
    border:none !important;
}

/* ================= OVERLAY ================= */
.qr-overlay{
    position:absolute;
    inset:0;
    display:flex;
    align-items:center;
    justify-content:center;
    pointer-events:none;
}

.scan-box{
    width:250px;
    height:250px;
    border:2px solid rgba(0,0,0,.1);
    border-radius:16px;
    position:relative;
}

.scan-line{
    position:absolute;
    width:100%;
    height:2px;
    background:#0d6efd;
    animation:scanMove 2s linear infinite;
}

@keyframes scanMove{
    0%{ top:0; }
    100%{ top:100%; }
}

/* ================= RESULT ================= */
/* ================= IMPROVED RESULT UI ================= */
.result-card {
    border-radius: 24px;
    border: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    min-height: 180px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

/* Warna Khusus Berdasarkan Status */
.res-on-time { background: #dcfce7; border: 3px solid #22c55e !important; color: #15803d; }
.res-late    { background: #fef9c3; border: 3px solid #eab308 !important; color: #854d0e; }
.res-token   { background: #e0f2fe; border: 3px solid #0ea5e9 !important; color: #0369a1; }
.res-checkout{ background: #eef2ff; border: 3px solid #6366f1 !important; color: #4338ca; }
.res-error   { background: #fee2e2; border: 3px solid #ef4444 !important; color: #991b1b; }
.res-idle    { background: #fff; border: 1px solid #e9ecef !important; }

.status-label {
    font-size: 0.75rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: 5px;
    opacity: 0.8;
}

.student-name {
    font-size: 1.5rem;
    font-weight: 800;
    line-height: 1.2;
    margin-bottom: 8px;
    color: #1e293b;
}

.student-info-pill {
    display: inline-flex;
    gap: 10px;
    background: rgba(0,0,0,0.05);
    padding: 4px 12px;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #334155;
}

.final-badge {
    margin-top: 15px;
    padding: 8px 24px;
    border-radius: 12px;
    font-weight: 900;
    font-size: 1.1rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

/* Animasi Pop & Flash */
@keyframes popIn {
    0% { transform: scale(0.9); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}
.animate-pop { animation: popIn 0.3s ease-out forwards; }
.flash-success { box-shadow: 0 0 50px rgba(25, 135, 84, 0.8); border: 4px solid #198754; }
.flash-error { box-shadow: 0 0 50px rgba(220, 53, 69, 0.8); border: 4px solid #dc3545; }
/* ================= TIMER UI BARU ================= */
.timer-state{
    font-size:1.2rem;
    font-weight:700;
}

.timer-countdown{
    font-size:2rem;
    font-weight:800;
    line-height:1.1;
}

.timer-sub{
    font-size:.85rem;
    color:#6c757d;
}

.main-wrapper{
    max-width:520px;
}

.camera-btn{
    width:42px;
    height:42px;
    display:flex;
    align-items:center;
    justify-content:center;
}
</style>

@php
    $start = \Carbon\Carbon::parse($activity->started_at);

    // ✅ FIX: pakai field baru (fixed time)
    $openTime = $activity->checkin_open_at
        ? \Carbon\Carbon::parse($activity->checkin_open_at)
        : $start; // fallback aman

    $expireTime = \Carbon\Carbon::parse($activeQrSession->expires_at);
@endphp

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12 main-wrapper">

            {{-- ================= HEADER ================= --}}
        <div class="d-flex justify-content-between align-items-center mb-3">

    {{-- Back Button --}}
    <a href="{{ route('pembina.activity.show', [$eskul->id, $activity->id]) }}"
       class="btn btn-light border rounded-circle shadow-sm camera-btn">
        <i class="bi bi-arrow-left"></i>
    </a>

    {{-- Title --}}
    <div class="text-center">
        <div class="fw-bold">Absensi QR Code</div>
        <small class="text-muted">
            Mode:
            @if($activeQrSession->mode == 'checkin')
                Check-in (Kedatangan)
            @else
                Check-out (Kepulangan)
            @endif
        </small>
    </div>

    {{-- Switch Camera --}}
    <button id="switchCamera"
            class="btn btn-light border rounded-circle shadow-sm camera-btn">
        <i class="bi bi-camera-video"></i>
    </button>

</div>

            {{-- ================= TIMER PANEL BARU ================= --}}
            <div class="card mb-3 shadow-sm border-0 rounded-4">
                <div class="card-body text-center">

                    <div id="timerState" class="timer-state text-success">
                        MODE HADIR
                    </div>
                    <div id="timerLabel" class="timer-sub mb-2">
                        Menghitung waktu...
                    </div>

                    <hr>

                    <div class="timer-sub">
                        Mulai Scan:
                        <strong>{{ $openTime->format('H:i') }}</strong>
                    </div>

                    @if($activeQrSession->mode === 'checkin')
                      <div class="timer-sub">
                            Jam Masuk:
                            <strong>{{ $start->format('H:i') }}</strong>
                        </div>

                        <div class="timer-sub">
                            Sesi Berakhir:
                            <strong>{{ $expireTime->format('H:i') }}</strong>
                        </div>
                    @else
                        <div class="timer-sub">
                            Berakhir:
                             <strong>{{ $expireTime->format('H:i') }}</strong>
                        </div>
                    @endif

                </div>
            </div>

            {{-- ================= SCANNER ================= --}}
            <div class="scanner-card position-relative">
                <div id="reader"></div>

                <div class="qr-overlay">
                    <div class="scan-box">
                        <div class="scan-line"></div>
                    </div>
                </div>
            </div>

            {{-- ================= RESULT ================= --}}
            <div id="resultBox"
                 class="card mt-3 result-card bg-white shadow-sm border">

                <div class="card-body text-center py-4">
                    <div id="resultName" class="result-name text-secondary">
                        Siap melakukan pemindaian
                    </div>

                    <div id="resultStatus"
                         class="result-status mt-2 text-muted">
                        Silakan arahkan QR ke dalam kotak
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>

<script>
const activityId = "{{ $activity->id }}";
const csrfToken = "{{ csrf_token() }}";

const mode = "{{ $activeQrSession->mode }}";

const openTime = {{ $openTime->timestamp * 1000 }};
const startTime = {{ $start->timestamp * 1000 }};
 const expireTime = {{ $expireTime->timestamp * 1000 }};


const timerState = document.getElementById('timerState');
const timerLabel = document.getElementById('timerLabel');

const resultBox = document.getElementById('resultBox');
const resultName = document.getElementById('resultName');
const resultStatus = document.getElementById('resultStatus');

let cameras = [];
let currentCameraIndex = 0;
let html5QrCode;
let isProcessing = false;
let isExpired = false;

/* ================= BEEP ================= */
const audioCtx = new (window.AudioContext || window.webkitAudioContext)();

function playBeep(freq,duration,volume=1){
    const osc = audioCtx.createOscillator();
    const gain = audioCtx.createGain();
    osc.type="square";
    osc.frequency.setValueAtTime(freq,audioCtx.currentTime);
    gain.gain.setValueAtTime(volume,audioCtx.currentTime);
    osc.connect(gain);
    gain.connect(audioCtx.destination);
    osc.start();
    osc.stop(audioCtx.currentTime+duration);
}

function beepSuccess(){ playBeep(1800,0.18,1); }
function beepError(){
    playBeep(500,0.25,1);
    setTimeout(()=>playBeep(500,0.25,1),250);
}
function beepEnd(){ playBeep(250,0.6,1); }

setInterval(function(){

    const now = new Date();
    const nowMs = now.getTime();

    const nowText = now.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });

    if(mode === 'checkin'){

        if(nowMs < openTime){

            timerState.innerText = "BELUM DIBUKA";
            timerState.className = "timer-state text-secondary";
            timerLabel.innerText =
                "Sekarang: " + nowText + " • Menunggu waktu buka";

        }else if(nowMs <= startTime){

            // ✅ BENAR: lebih awal = start - now
            const diff = Math.max(0, Math.round((startTime - nowMs) / 60000));

            timerState.innerText = "HADIR";
            timerState.className = "timer-state text-success";
            timerLabel.innerText =
                "Sekarang: " + nowText +
                (diff === 0
                    ? " • Tepat waktu"
                    : " • Lebih awal " + diff + " menit"
                );

        }else if(nowMs <= expireTime){

            // ✅ BENAR: telat = now - start
            const diff = Math.max(0, Math.round((nowMs - startTime) / 60000));

            timerState.innerText = "TELAT";
            timerState.className = "timer-state text-warning";
            timerLabel.innerText =
                "Sekarang: " + nowText +
                (diff === 0
                    ? " • Baru lewat waktu"
                    : " • Telat " + diff + " menit"
                );

        }else{

            endSession();
        }

    }else{

        if(nowMs > expireTime){
            endSession();
        }else{
            timerState.innerText = "CHECKOUT AKTIF";
            timerState.className = "timer-state text-primary";
            timerLabel.innerText =
                "Sekarang: " + nowText + " • Silakan checkout";
        }

    }

},1000);

function endSession(){
    if(isExpired) return;

    isExpired = true;
    timerState.innerText = "SESI BERAKHIR";
    timerState.className = "timer-state text-danger";
    timerLabel.innerText = "Waktu habis";

    if(html5QrCode){
        html5QrCode.stop();
    }

    beepEnd();
}

/* ================= RESULT ================= */
/* ================= RESULT ================= */
/* ================= RESULT ================= */
function showSuccess(data) {
    const scannerEl = document.querySelector('.scanner-card');
    scannerEl.classList.add('flash-success');
    
    let stateClass = "";
    let statusText = "";
    let badgeClass = "";
    let icon = "";

    if (mode === 'checkout') {
        stateClass = "res-checkout";
        statusText = "CHECKOUT BERHASIL";
        badgeClass = "bg-primary text-white";
        icon = '<i class="bi bi-door-closed-fill"></i>';
    } else {
        if (data.token_used) {
            stateClass = "res-token";
            statusText = "BEBAS TELAT (VOUCHER)";
            badgeClass = "bg-info text-white";
            icon = '<i class="bi bi-ticket-perforated-fill"></i>';
        } 
       // Di dalam fungsi showSuccess bagian checkin_status === 'LATE'
        else if (data.checkin_status === 'LATE' || parseFloat(data.late_minutes) > 0) {
            stateClass = "res-late";
            
            // ✅ FIX: Gunakan Math.max untuk mencegah angka minus
            const roundedLate = Math.max(0, Math.round(parseFloat(data.late_minutes)));
            
            statusText = `TERLAMBAT ${roundedLate} MENIT`;
            badgeClass = "bg-warning text-dark";
            icon = '<i class="bi bi-clock-fill"></i>';
        }
        else {
            stateClass = "res-on-time";
            statusText = "HADIR TEPAT WAKTU";
            badgeClass = "bg-success text-white";
            icon = '<i class="bi bi-check-circle-fill"></i>';
        }
    }

    // ... sisa kode render HTML tetap sama ...
    resultBox.className = `card mt-3 result-card animate-pop ${stateClass}`;
    resultBox.innerHTML = `
        <div class="card-body text-center py-4">
            <div class="status-label">Informasi Siswa</div>
            <div class="student-name">${data.name}</div>
            <div class="student-info-pill">
                <span>Kelas ${data.class || '-'}</span>
                <span style="opacity:0.3">|</span>
                <span>NISN ${data.nisn || '-'}</span>
            </div>
            <div>
                <div class="final-badge ${badgeClass}">
                    ${icon} ${statusText}
                </div>
            </div>
        </div>
    `;

    beepSuccess();
    setTimeout(() => scannerEl.classList.remove('flash-success'), 2000);
}
function showError(message) {
    const scannerEl = document.querySelector('.scanner-card');
    scannerEl.classList.add('flash-error');
    
    resultBox.className = "card mt-3 result-card animate-pop res-error";
    resultBox.innerHTML = `
        <div class="card-body text-center py-4">
            <div class="status-label text-danger">Terjadi Kendala</div>
            <div class="student-name text-danger" style="font-size:1.3rem">GAGAL MEMINDAI</div>
            <div class="final-badge bg-danger text-white">
                <i class="bi bi-exclamation-triangle-fill"></i> ${message}
            </div>
        </div>
    `;
    
    beepError();
    setTimeout(() => scannerEl.classList.remove('flash-error'), 2000);
}
/* ================= SCAN ================= */
async function onScanSuccess(decodedText){

    if(isProcessing || isExpired) return;
    isProcessing = true;

    if(Date.now() < openTime){
        showError("Belum waktunya absen");
        isProcessing = false;
        return;
    }

    if(Date.now() > expireTime){
        showError("Sesi sudah berakhir");
        isProcessing = false;
        return;
    }

    try{
        const response = await fetch("{{ route('pembina.qr.scan_process') }}",{
            method:"POST",
            headers:{
                "Content-Type":"application/json",
                "X-CSRF-TOKEN":csrfToken
            },
            body:JSON.stringify({
                activity_id:activityId,
                qr_data:decodedText
            })
        });

        const data = await response.json();

        data.success
            ? showSuccess(data)
            : showError(data.message);

    }catch(e){
        showError("Terjadi kesalahan pada sistem.");
    }

    setTimeout(() => {
        resultBox.className = "card mt-3 result-card res-idle shadow-sm";
        resultBox.innerHTML = `
            <div class="card-body text-center py-4">
                <div class="result-name text-secondary">
                    Siap melakukan pemindaian
                </div>
                <div class="result-status mt-2 text-muted">
                    Silakan arahkan QR ke dalam kotak
                </div>
            </div>
        `;
        isProcessing = false;
    }, 3300); // Reset setelah 3 detik
}
async function startScanner(cameraId){
    try{
        if(html5QrCode){
            await html5QrCode.stop();
        }

        html5QrCode = new Html5Qrcode("reader");

        await html5QrCode.start(
            cameraId,
            { fps: 10, qrbox: { width: 250, height: 250 } },
            onScanSuccess
        );

    }catch(e){
        console.error(e);
        showError("Gagal memulai kamera");
    }
}

async function initScanner(){
    try{
        cameras = await Html5Qrcode.getCameras();

        if(!cameras.length){
            showError("Kamera tidak ditemukan");
            return;
        }

        await startScanner(cameras[0].id);

    }catch(e){
        console.error(e);
        showError("Gagal mengakses kamera. Izinkan permission kamera.");
    }
}

document.getElementById("switchCamera")
.addEventListener("click", async function(){

    if(cameras.length < 2) return;

    currentCameraIndex =
        (currentCameraIndex + 1) % cameras.length;

    await startScanner(cameras[currentCameraIndex].id);
});

initScanner();
</script>

@endsection