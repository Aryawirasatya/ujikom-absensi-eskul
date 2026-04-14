@extends('layouts.app')

@section('title', 'Kategori Penilaian Sikap')

<style>
/* ===== PAGE HEADER ===== */
.page-hero {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    border-radius: 18px;
    padding: 28px 32px;
    color: #fff;
    position: relative;
    overflow: hidden;
    margin-bottom: 28px;
}
.page-hero::after {
    content: '';
    position: absolute; top: -60px; right: -60px;
    width: 220px; height: 220px;
    background: rgba(255,255,255,.06); border-radius: 50%;
    pointer-events: none;
}
.page-hero::before {
    content: '';
    position: absolute; bottom: -30px; right: 140px;
    width: 100px; height: 100px;
    background: rgba(255,255,255,.04); border-radius: 50%;
    pointer-events: none;
}

/* ===== STAT CARDS ===== */
.stat-card {
    border-radius: 14px;
    border: none;
    padding: 20px 22px;
    display: flex;
    align-items: center;
    gap: 14px;
}
.stat-icon {
    width: 48px; height: 48px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; flex-shrink: 0;
}

/* ===== TABLE ===== */
.table-card { border-radius: 16px; overflow: hidden; border: none; }
.table thead th {
    font-size: .72rem; text-transform: uppercase;
    letter-spacing: .7px; color: #8a94a6; font-weight: 700;
    background: #f8fafc; border-bottom: 2px solid #eef0f4;
    padding: 14px 16px;
}
.table tbody tr { transition: background .15s; }
.table tbody tr:hover { background: #f8fafc; }
.table tbody td { padding: 14px 16px; vertical-align: middle; border-color: #f0f2f5; }

/* ===== BADGES ===== */
.pill-visible   { background: #e8f5fe; color: #0c7abf; font-size: .74rem; padding: 4px 10px; border-radius: 20px; font-weight: 600; }
.pill-hidden    { background: #f0f0f0; color: #6c757d; font-size: .74rem; padding: 4px 10px; border-radius: 20px; font-weight: 600; }
.pill-active    { background: #e6f9f0; color: #1a8a55; font-size: .74rem; padding: 4px 10px; border-radius: 20px; font-weight: 600; }
.pill-inactive  { background: #f5f5f5; color: #888; font-size: .74rem; padding: 4px 10px; border-radius: 20px; font-weight: 600; }

/* ===== STAR DEMO ===== */
.star-demo { color: #f6c23e; letter-spacing: 1px; font-size: .9rem; }

/* ===== ACTION BUTTONS ===== */
.btn-act {
    width: 32px; height: 32px; border-radius: 8px;
    display: inline-flex; align-items: center; justify-content: center;
    border: none; transition: all .15s; cursor: pointer; font-size: .75rem;
}
.btn-act:hover { transform: scale(1.1); }
.btn-act-edit   { background: #e8efff; color: #4e73df; }
.btn-act-edit:hover   { background: #4e73df; color: #fff; }
.btn-act-toggle { background: #fff3e0; color: #e67e22; }
.btn-act-toggle:hover { background: #e67e22; color: #fff; }
.btn-act-on     { background: #e6f9f0; color: #1a8a55; }
.btn-act-on:hover     { background: #1a8a55; color: #fff; }
.btn-act-del    { background: #fdecea; color: #e74c3c; }
.btn-act-del:hover    { background: #e74c3c; color: #fff; }

/* ===== MODAL ===== */
.modal-content { border-radius: 18px; border: none; }
.modal-header  { border-bottom: 1px solid #f0f2f5; padding: 22px 28px 18px; }
.modal-body    { padding: 20px 28px; }
.modal-footer  { border-top: 1px solid #f0f2f5; padding: 16px 28px 22px; }
.form-control, .form-select {
    border: 1.5px solid #e0e4ec; border-radius: 10px; font-size: .9rem;
    transition: border-color .2s, box-shadow .2s;
}
.form-control:focus, .form-select:focus {
    border-color: #4e73df; box-shadow: 0 0 0 3px rgba(78,115,223,.1);
}

/* ===== EMPTY STATE ===== */
.empty-state { padding: 60px 20px; text-align: center; }
.empty-icon { font-size: 3rem; margin-bottom: 12px; display: block; }
</style>

@section('content')
<div class="container-fluid">

    {{-- Page Hero --}}
    <div class="page-hero">
        <div class="position-relative" style="z-index:1">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold mb-1">Kategori Penilaian Sikap</h4>
                    <p class="mb-0 opacity-75 small">
                        Kelola indikator evaluasi yang digunakan pembina saat menilai anggota eskul.
                    </p>
                </div>
                <button class="btn btn-light fw-semibold px-4" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    <i class="fas fa-plus me-2"></i>Tambah Indikator
                </button>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="alert border-0 shadow-sm alert-dismissible fade show mb-4"
         style="background:#e6f9f0; color:#1a5c3a; border-radius:12px">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert border-0 shadow-sm alert-dismissible fade show mb-4"
         style="background:#fdecea; color:#7b2020; border-radius:12px">
        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card stat-card shadow-sm">
                <div class="stat-icon" style="background:#e8efff">
                    <i class="fas fa-layer-group text-primary"></i>
                </div>
                <div>
                    <div class="h4 fw-bold mb-0 text-dark">{{ $stats['total'] }}</div>
                    <div class="text-muted small">Total Indikator</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card shadow-sm">
                <div class="stat-icon" style="background:#e6f9f0">
                    <i class="fas fa-toggle-on" style="color:#1a8a55"></i>
                </div>
                <div>
                    <div class="h4 fw-bold mb-0" style="color:#1a8a55">{{ $stats['active'] }}</div>
                    <div class="text-muted small">Aktif</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card shadow-sm">
                <div class="stat-icon" style="background:#f5f5f5">
                    <i class="fas fa-toggle-off text-muted"></i>
                </div>
                <div>
                    <div class="h4 fw-bold mb-0 text-muted">{{ $stats['inactive'] }}</div>
                    <div class="text-muted small">Nonaktif</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card table-card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4" style="width:44px">#</th>
                            <th>Nama Indikator</th>
                            <th>Deskripsi</th>
                            <th class="text-center">Skala</th>
                            <th class="text-center">Tampil ke Siswa</th>
                            <th class="text-center">Status</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $i => $cat)
                        <tr class="{{ !$cat->is_active ? 'opacity-50' : '' }}">
                            <td class="ps-4 text-muted small fw-semibold">{{ $i + 1 }}</td>
                            <td>
                                <span class="fw-semibold text-dark" style="font-size:.9rem">{{ $cat->name }}</span>
                            </td>
                            <td>
                                <span class="text-muted small">{{ Str::limit($cat->description, 60) ?: '—' }}</span>
                            </td>
                            <td class="text-center">
                                <div class="star-demo">★★★★★</div>
                                <div class="text-muted" style="font-size:.68rem">1 – 5</div>
                            </td>
                            <td class="text-center">
                                @if($cat->show_to_student)
                                    <span class="pill-visible"><i class="fas fa-eye fa-xs me-1"></i>Terlihat</span>
                                @else
                                    <span class="pill-hidden"><i class="fas fa-eye-slash fa-xs me-1"></i>Tersembunyi</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($cat->is_active)
                                    <span class="pill-active"><i class="fas fa-circle fa-xs me-1"></i>Aktif</span>
                                @else
                                    <span class="pill-inactive"><i class="far fa-circle fa-xs me-1"></i>Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex gap-1 justify-content-center">
                                    {{-- Edit --}}
                                    <button class="btn-act btn-act-edit" title="Edit"
                                        onclick="openEditModal({{ $cat->id }}, @js($cat->name), @js($cat->description ?? ''), {{ $cat->show_to_student ? 'true' : 'false' }})">
                                        <i class="fas fa-pen"></i>
                                    </button>

                                    {{-- Toggle Aktif --}}
                                    <form action="{{ route('admin.assessment-categories.toggle', $cat) }}" method="POST" class="d-inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" title="{{ $cat->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                            class="btn-act {{ $cat->is_active ? 'btn-act-toggle' : 'btn-act-on' }}">
                                            <i class="fas {{ $cat->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                        </button>
                                    </form>

                                    {{-- Hapus --}}
                                    <form action="{{ route('admin.assessment-categories.destroy', $cat) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Hapus indikator \'{{ $cat->name }}\'?\n\nIndikator yang sudah punya data penilaian tidak bisa dihapus.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="Hapus" class="btn-act btn-act-del">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <span class="empty-icon">📋</span>
                                    <h6 class="fw-semibold text-dark mb-1">Belum ada indikator penilaian</h6>
                                    <p class="text-muted small mb-3">Mulai tambahkan indikator untuk menilai sikap anggota eskul.</p>
                                    <button class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#modalTambah">
                                        <i class="fas fa-plus me-2"></i>Tambah Sekarang
                                    </button>
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

{{-- ===== MODAL TAMBAH ===== --}}
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <form action="{{ route('admin.assessment-categories.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <span class="me-2" style="font-size:1.1rem">✨</span>Tambah Indikator
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">
                            Nama Indikator <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" class="form-control" required maxlength="100"
                            placeholder="cth. Disiplin, Kerja Sama, Kreativitas...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">
                            Deskripsi <span class="text-muted fw-normal">(opsional)</span>
                        </label>
                        <textarea name="description" class="form-control" rows="2" maxlength="500"
                            placeholder="Penjelasan singkat tentang indikator ini..."></textarea>
                    </div>
                    <div class="p-3 rounded-3" style="background:#f8fafc; border:1.5px solid #eef0f4">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" name="show_to_student"
                                id="addShowStudent" value="1" checked>
                            <label class="form-check-label" for="addShowStudent">
                                <span class="fw-semibold small text-dark">Tampilkan ke Siswa</span>
                                <div class="text-muted" style="font-size:.76rem">
                                    Siswa bisa melihat skor indikator ini di rapor mereka
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer gap-2">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===== MODAL EDIT ===== --}}
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <form id="formEdit" method="POST">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <span class="me-2" style="font-size:1.1rem">✏️</span>Edit Indikator
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">
                            Nama Indikator <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" id="editName" class="form-control" required maxlength="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark">Deskripsi</label>
                        <textarea name="description" id="editDesc" class="form-control" rows="2" maxlength="500"></textarea>
                    </div>
                    <div class="p-3 rounded-3" style="background:#f8fafc; border:1.5px solid #eef0f4">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" name="show_to_student"
                                id="editShowStudent" value="1">
                            <label class="form-check-label" for="editShowStudent">
                                <span class="fw-semibold small text-dark">Tampilkan ke Siswa</span>
                                <div class="text-muted" style="font-size:.76rem">
                                    Siswa bisa melihat skor indikator ini di rapor mereka
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer gap-2">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-white px-4">
                        <i class="fas fa-save me-1"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openEditModal(id, name, desc, showToStudent) {
    document.getElementById('formEdit').action = `/admin/assessment-categories/${id}`;
    document.getElementById('editName').value = name;
    document.getElementById('editDesc').value = desc;
    document.getElementById('editShowStudent').checked = showToStudent;
    new bootstrap.Modal(document.getElementById('modalEdit')).show();
}
</script>
@endpush
