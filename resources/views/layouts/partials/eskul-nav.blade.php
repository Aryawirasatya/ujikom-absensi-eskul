<div class="eskul-nav mb-3">

    <div class="d-flex flex-wrap align-items-center gap-3">

        {{-- NAMA ESKUL --}}
        <div class="eskul-title">
            <i class="fas fa-futbol text-primary me-1"></i>
            {{ $eskul->name }}
        </div>

        {{-- NAV MENU --}}
        <div class="eskul-tabs">

            <a href="{{ route('pembina.members.index',$eskul->id) }}"
               class="eskul-tab {{ request()->routeIs('pembina.members.*') ? 'active' : '' }}">
                Anggota
            </a>

            <a href="{{ route('pembina.schedules.index',$eskul->id) }}"
               class="eskul-tab {{ request()->routeIs('pembina.schedules.*') ? 'active' : '' }}">
                Jadwal
            </a>

            <a href="{{ route('pembina.activity.index',$eskul->id) }}"
               class="eskul-tab {{ request()->routeIs('pembina.activity.*') ? 'active' : '' }}">
                absensi
            </a>

        </div>

        {{-- BACK --}}
        <a href="{{ route('pembina.eskul.index') }}"
           class="eskul-back ms-auto">
            <i class="bi bi-arrow-left"></i>
        </a>

    </div>

</div>

<style>
    .eskul-nav{
background:white;
padding:10px 14px;
border-radius:12px;
border:1px solid #eef2f7;
}

.eskul-title{
font-weight:700;
font-size:14px;
color:#1e293b;
display:flex;
align-items:center;
}

.eskul-tabs{
display:flex;
gap:6px;
flex-wrap:wrap;
}

.eskul-tab{
font-size:13px;
padding:6px 12px;
border-radius:8px;
text-decoration:none;
color:#64748b;
font-weight:600;
transition:.15s;
}

.eskul-tab:hover{
background:#f1f5f9;
color:#0d6efd;
}

.eskul-tab.active{
background:#0d6efd;
color:white;
}

.eskul-back{
width:30px;
height:30px;
display:flex;
align-items:center;
justify-content:center;
border-radius:8px;
background:#f8fafc;
color:#64748b;
text-decoration:none;
transition:.15s;
}

.eskul-back:hover{
background:#e2e8f0;
color:#0f172a;
}
</style>