<div class="sidebar" data-background-color="dark">
    <div class="sidebar-logo">
        <div class="logo-header" data-background-color="dark">
            <a href="{{ route('dashboard') }}" class="logo d-flex align-items-center gap-2">
                <img src="{{ asset('assets/img/logo.png') }}" alt="Logo" height="90" width="200">
            </a>

            <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                    <i class="gg-menu-right"></i>
                </button>
                <button class="btn btn-toggle sidenav-toggler">
                    <i class="gg-menu-left"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <ul class="nav nav-secondary">

                <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}">
                        <i class="fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                @role('admin')
                    <li class="nav-section">
                        <span class="sidebar-mini-icon">
                            <i class="fa fa-database"></i>
                        </span>
                        <h4 class="text-section">Master Data</h4>
                    </li>

                    <li class="nav-item {{ request()->routeIs('admin.school-years.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.school-years.index') }}">
                            <i class="fas fa-calendar-alt"></i>
                            <p>Tahun Ajaran</p>
                        </a>
                    </li>

                    <li class="nav-item {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.students.index') }}">
                            <i class="bi bi-people"></i>
                            <p>Siswa</p>
                        </a>
                    </li>

                    <li class="nav-item {{ request()->routeIs('admin.pembina.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.pembina.index') }}">
                            <i class="bi bi-person-badge"></i>
                            <p>Pembina</p>
                        </a>
                    </li>

                    <li class="nav-item {{ request()->routeIs('admin.eskul.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.eskul.index') }}">
                            <i class="fas fa-futbol"></i>
                            <p>Eskul</p>
                        </a>
                    </li>

                    <li class="nav-item {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.laporan.index') }}">
                            <i class="bi bi-bar-chart"></i>
                            <p>Laporan Absensi</p>
                        </a>
                    </li>

                    <li class="nav-item {{ request()->routeIs('admin.assessment-categories.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.assessment-categories.index') }}">
                            <i class="fas fa-tags"></i>
                            <p>Kategori Indikator</p>
                        </a>
                    </li>

                    <li class="nav-item {{ request()->routeIs('admin.assessment-questions.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.assessment-questions.index') }}">
                            <i class="fas fa-clipboard-list"></i>
                            <p>Pertanyaan Indikator</p>
                        </a>
                    </li>

                    <li class="nav-item {{ request()->routeIs('admin.penilaian.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.penilaian.index') }}">
                            <i class="fas fa-file-signature"></i>
                            <p>Laporan Penilaian</p>
                        </a>
                    </li>
                @endrole

               @role('pembina')
    <li class="nav-section">
        <span class="sidebar-mini-icon"><i class="fa fa-tasks"></i></span>
        <h4 class="text-section">Menu Pembina</h4>
    </li>

    {{-- Menu Manajemen --}}
    <li class="nav-item {{ request()->routeIs('pembina.eskul.index') ? 'active' : '' }}">
        <a href="{{ route('pembina.eskul.index') }}">
            <i class="fas fa-futbol"></i>
            <p>Eskul yang dikelola</p>
        </a>
    </li>

    {{-- Menu Laporan Absensi --}}
    <li class="nav-item {{ request()->routeIs('pembina.laporan.*') ? 'active' : '' }}">
        <a href="{{ route('pembina.laporan.index') }}">
            <i class="bi bi-graph-up"></i>
            <p>Laporan Absensi</p>
        </a>
    </li>

    {{-- Menu Laporan Penilaian (GERBANG UTAMA) --}}
    <li class="nav-item {{ request()->routeIs('pembina.penilaian.*') ? 'active' : '' }}">
        <a href="{{ route('pembina.penilaian.laporan_index') }}">
            <i class="fas fa-clipboard-check"></i>
            <p>Laporan Penilaian</p>
        </a>
    </li>
@endrole

                @role('siswa')
                    <li class="nav-section">
                        <span class="sidebar-mini-icon">
                            <i class="fa fa-user-graduate"></i>
                        </span>
                        <h4 class="text-section">Siswa</h4>
                    </li>

                    <li class="nav-item {{ request()->routeIs('siswa.qr.*') ? 'active' : '' }}">
                        <a href="{{ route('siswa.qr.show') }}">
                            <i class="fas fa-qrcode"></i>
                            <p>QR Saya</p>
                        </a>
                    </li>

                    <li class="nav-item {{ request()->routeIs('siswa.eskul.*') ? 'active' : '' }}">
                        <a href="{{ route('siswa.eskul.index') }}">
                            <i class="fas fa-futbol"></i>
                            <p>Eskul Saya</p>
                        </a>
                    </li>

                    <li class="nav-item {{ request()->routeIs('siswa.attendance.index') ? 'active' : '' }}">
                        <a href="{{ route('siswa.attendance.index') }}">
                            <i class="fas fa-history"></i>
                            <p>Riwayat Absensi</p>
                        </a>
                    </li>

                    <li class="nav-item {{ request()->routeIs('siswa.penilaian.*') ? 'active' : '' }}">
                        <a href="{{ route('siswa.penilaian.index') }}">
                            <i class="fas fa-chart-line"></i>
                            <p>Rapor Sikap Saya</p>
                        </a>
                    </li>
                @endrole

            </ul>
        </div>
    </div>
</div>

<style>
    .nav-toggle .btn-toggle {
        position: relative;
    }
    .nav-toggle .btn-toggle::after {
        content: '';
        position: absolute;
        top: -10px;
        bottom: -10px;
        left: -10px;
        right: -10px;
    }
</style>