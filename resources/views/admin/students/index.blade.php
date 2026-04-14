@extends('layouts.app')
<style>
    /* Container Utama */
    .pagination-wrapper .pagination {
        display: flex;
        padding-left: 0;
        list-style: none;
        margin-bottom: 0;
        gap: 5px; /* Jarak antar tombol */
    }

    /* Hilangkan border bawaan Laravel/Bootstrap */
    .pagination-wrapper .page-item .page-link {
        border: none !important;
        background-color: #f1f3f9;
        color: #6c757d;
        padding: 8px 16px;
        font-size: 0.875rem;
        font-weight: 600;
        border-radius: 10px !important; /* Membuat tombol lebih kotak membulat modern */
        transition: all 0.2s ease-in-out;
        box-shadow: none;
    }

    /* Efek Hover */
    .pagination-wrapper .page-item .page-link:hover {
        background-color: #e8ebf3;
        color: #5867dd;
        transform: translateY(-2px);
    }

    /* Halaman Aktif */
    .pagination-wrapper .page-item.active .page-link {
        background-color: #5867dd !important; /* Biru Ungu Kaiadmin */
        color: white !important;
        box-shadow: 0 4px 12px rgba(88, 103, 221, 0.35) !important;
    }

    /* Ikon panah (Previous/Next) agar ukurannya pas */
    .pagination-wrapper .page-item .page-link svg {
        width: 18px;
        height: 18px;
    }

    /* Menyembunyikan teks "Next" dan "Previous" pada versi mobile jika perlu */
    @media (max-width: 576px) {
        .pagination-wrapper .page-link {
            padding: 8px 12px;
            font-size: 0.75rem;
        }
    }
</style>
@section('content')
<div class="container-fluid py-4">

    {{-- ================= HEADER ================= --}}
    <div class="row mb-4 align-items-end">
        <div class="col-md-6">
            <h3 class="fw-bold mb-1">Data Siswa</h3>
            <div class="text-muted">
                @if($year)
                    Tahun Ajaran Aktif: <strong>{{ $year->name }}</strong>
                @else
                    <span class="text-danger">Belum ada tahun ajaran aktif</span>
                @endif
                @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
                @endif

                
            </div>
        </div>
        

        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <button class="btn btn-outline-success me-2"
                    data-bs-toggle="collapse"
                    data-bs-target="#importBox">
                Import Excel
            </button>

            <button class="btn btn-outline-dark me-2"
                    data-bs-toggle="collapse"
                    data-bs-target="#importPhotoBox">
                Import Foto (ZIP)
            </button>

            <button class="btn btn-primary"
                    data-bs-toggle="collapse"
                    data-bs-target="#manualBox">
                Tambah Siswa
            </button>
        </div>
    </div>

    {{-- ================= IMPORT EXCEL ================= --}}
    <div class="collapse mb-4" id="importBox">
        <div class="card shadow-sm border-0">
            <div class="card-body">

                <form method="POST"
                      action="{{ route('admin.students.import') }}"
                      enctype="multipart/form-data">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-8">
                            <input type="file" name="file" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-success w-100">Import</button>
                        </div>
                    </div>

                    <small class="text-muted mt-2 d-block">
                        Format: nama | gender | nisn | grade | kelas
                    </small>
                </form>

            </div>
        </div>
    </div>

    {{-- ================= IMPORT FOTO MASSAL ================= --}}
    <div class="collapse mb-4" id="importPhotoBox">
        <div class="card shadow-sm border-0">
            <div class="card-body">

                <form method="POST"
                      action="{{ route('admin.students.import.photos') }}"
                      enctype="multipart/form-data">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-8">
                            <input type="file"
                                   name="zip_file"
                                   accept=".zip"
                                   class="form-control"
                                   required>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-dark w-100">
                                Import Foto ZIP
                            </button>
                        </div>
                    </div>

                    <small class="text-muted mt-2 d-block">
                        ZIP tanpa folder. Nama file harus = NISN (contoh: 123456789.jpg)
                    </small>
                </form>

            </div>
        </div>
    </div>

    {{-- ================= TAMBAH MANUAL ================= --}}
    <div class="collapse mb-4" id="manualBox">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-semibold">
                Tambah Siswa Manual
            </div>

            <form method="POST"
                  action="{{ route('admin.students.store') }}"
                  enctype="multipart/form-data"
                  class="card-body">
                @csrf

                <div class="row g-3">

                    <div class="col-md-3">
                        <input name="name" class="form-control" placeholder="Nama" required>
                    </div>

                    <div class="col-md-2">
                        <input name="nisn" class="form-control" placeholder="NISN" required>
                    </div>

                    <div class="col-md-2">
                        <select name="gender" class="form-select">
                            <option value="L">L</option>
                            <option value="P">P</option>
                        </select>
                    </div>

                    <div class="col-md-1">
                        <select name="grade" class="form-select">
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <input name="class_label" class="form-control" placeholder="Kelas">
                    </div>

                    <div class="col-md-2">
                        <input type="file" name="photo" class="form-control">
                    </div>

                </div>

                <div class="text-end mt-3">
                    <button class="btn btn-primary">Simpan</button>
                </div>

            </form>
        </div>
    </div>

    {{-- ================= TABLE ================= --}}
    <div class="card shadow-sm border-0">

        <div class="card-header bg-white d-flex justify-content-between">
            <span class="fw-semibold">Daftar Siswa</span>

            <input type="text"
                   id="studentSearch"
                   class="form-control form-control-sm w-25"
                   placeholder="Cari nama / NISN / kelas">
        </div>

        <div class="table-responsive" style="max-height:70vh;">
            <table class="table table-hover align-middle mb-0" id="studentTable">

                <thead class="table-light sticky-top">
                    <tr>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>NISN</th>
                        <th>JK</th>
                        <th>Kelas</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                @foreach ($students as $s)
                    <tr>

                        <td>
                            @if($s->user && $s->user->photo)
                                <img src="{{ asset('storage/students/'.$s->user->photo) }}"
                                     width="45"
                                     height="45"
                                     style="object-fit:cover;border-radius:50%;">
                            @else
                                <div style="width:45px;height:45px;border-radius:50%;background:#ccc;display:flex;align-items:center;justify-content:center;font-weight:bold;">
                                    {{ strtoupper(substr($s->user->name ?? '?',0,1)) }}
                                </div>
                            @endif
                        </td>

                        <td class="fw-semibold">{{ $s->user->name ?? '-' }}</td>
                        <td>{{ $s->user->nisn ?? '-' }}</td>
                        <td>{{ $s->user->gender ?? '-' }}</td>
                        <td>{{ $s->grade }} {{ $s->class_label }}</td>

                        <td>
                            <span class="badge bg-{{ $s->academic_status === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($s->academic_status) }}
                            </span>
                        </td>

                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary btn-edit"
                                data-bs-toggle="modal"
                                data-bs-target="#editModal"
                                data-id="{{ $s->id }}"
                                data-name="{{ $s->user->name }}"
                                data-gender="{{ $s->user->gender }}"
                                data-class="{{ $s->class_label }}">
                                Edit
                            </button>

                            @if($s->academic_status === 'active')
                                <button class="btn btn-sm btn-outline-danger btn-deactivate"
                                    data-id="{{ $s->id }}"
                                    data-name="{{ $s->user->name }}">
                                    Nonaktif
                                </button>
                            @endif
                        </td>

                    </tr>
                @endforeach
                </tbody>

            </table>
        </div>
        <div class="card-footer bg-white border-top py-3">
    <div class="d-flex flex-column flex-md-row justify-content-center align-items-center">
        <div class="pagination-wrapper">
            {{ $students->onEachSide(1)->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
    </div>

</div>
<!-- EDIT MODAL -->
<div class="modal fade" id="editModal" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">

<form method="POST" id="editForm">
@csrf
@method('PUT')

<div class="modal-header">
<h5 class="modal-title">Edit Siswa</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<div class="mb-3">
<label>Nama</label>
<input type="text" name="name" id="editName" class="form-control">
</div>

<div class="mb-3">
<label>Gender</label>
<select name="gender" id="editGender" class="form-select">
<option value="L">L</option>
<option value="P">P</option>
</select>
</div>

<div class="mb-3">
<label>Kelas</label>
<input type="text" name="class_label" id="editClass" class="form-control">
</div>

</div>

<div class="modal-footer">
<button class="btn btn-primary">Update</button>
</div>

</form>

</div>
</div>
</div>
@endsection

@push('scripts')
<script>

document.getElementById('studentSearch').addEventListener('keyup', e => {
    const q = e.target.value.toLowerCase();
    document.querySelectorAll('#studentTable tbody tr').forEach(tr => {
        tr.style.display = tr.innerText.toLowerCase().includes(q) ? '' : 'none';
    });
});

document.querySelectorAll('.btn-edit').forEach(btn => {

    btn.addEventListener('click', function(){

        const id = this.dataset.id
        const name = this.dataset.name
        const gender = this.dataset.gender
        const kelas = this.dataset.class

        document.getElementById('editName').value = name
        document.getElementById('editGender').value = gender
        document.getElementById('editClass').value = kelas

        document.getElementById('editForm').action = `/admin/students/${id}`

    })

})
</script>

@endpush
