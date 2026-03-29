@extends('layouts.app')

@section('title', 'Pertanyaan Penilaian')

<style>
    /* ==== CSS MODERN & PROPORSIONAL ==== */
    
    .page-header {
        background: linear-gradient(135deg, #4e73df 0%, #36b9cc 100%);
        border-radius: 16px;
        padding: 30px 35px;
        color: #fff;
        margin-bottom: 30px;
        box-shadow: 0 8px 20px rgba(78, 115, 223, 0.2);
    }

    .page-header h4 {
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    .stat-card {
        border-radius: 16px;
        padding: 24px;
        background: #fff;
        border: 1px solid #f0f2f5;
        display: flex;
        align-items: center;
        gap: 18px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.06);
    }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .table-card {
        border-radius: 16px;
        background: #fff;
        border: 1px solid #f0f2f5;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        overflow: hidden;
    }

    .table {
        margin-bottom: 0;
    }

    .table thead th {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #6e7687;
        font-weight: 700;
        background: #f8fafc;
        padding: 16px 20px;
        border-bottom: 2px solid #edf2f7;
    }

    .table tbody td {
        padding: 16px 20px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }

    .table tbody tr:hover {
        background-color: #fbfbfc;
    }

    /* Styling khusus untuk baris header kategori */
    tr.bg-light.fw-bold td {
        background-color: #f8fafc !important;
        color: #334155;
        font-size: 0.95rem;
        border-left: 4px solid #4e73df;
    }

    .badge-soft {
        background: #4772ff;
        color: #4e73df;
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 0.75rem;
    }

    .btn-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        transition: all 0.2s ease;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .btn-icon:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    .action-group {
        display: flex;
        justify-content: center;
        gap: 8px;
    }

    /* Styling Modal biar nggak kaku */
    .modal-content {
        border-radius: 20px;
        border: none;
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }

    .modal-header {
        border-bottom: 1px solid #f1f5f9;
        padding: 20px 24px;
    }

    .modal-body {
        padding: 24px;
    }

    .modal-footer {
        border-top: 1px solid #f1f5f9;
        padding: 16px 24px;
        background: #fafbfc;
        border-bottom-left-radius: 20px;
        border-bottom-right-radius: 20px;
    }

    /* Input Forms dalam Modal */
    .form-control, .form-select {
        border-radius: 10px;
        padding: 12px 16px;
        border: 1px solid #cbd5e1;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
    }
</style>


@section('content')

<div class="container-fluid">

    {{-- ================= HEADER ================= --}}
    <div class="page-header shadow-sm justify-content-between">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

            <div>
                <h4 class="mb-1">Pertanyaan Penilaian</h4>

                <div class="small opacity-75">
                    Kelola indikator yang digunakan pembina untuk menilai siswa
                </div>
            </div>

            
        </div>
        
        <button
            class="btn btn-light fw-semibold px-4 mt-3 mt-md-0"
            data-bs-toggle="modal"
            data-bs-target="#modalTambah">
            <i class="fas fa-plus me-1"></i>
            Tambah Pertanyaan
        </button>
    </div>


    {{-- ================= STATS ================= --}}
    <div class="row g-3 mb-4">

        <div class="col-md-4">

            <div class="card stat-card shadow-sm">

                <div class="stat-icon bg-primary text-white">
                    <i class="fas fa-question"></i>
                </div>

                <div>
                    <div class="fw-bold fs-4">{{ $stats['total'] }}</div>
                    <div class="small text-muted">Total Pertanyaan</div>
                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="card stat-card shadow-sm">

                <div class="stat-icon bg-success text-white">
                    <i class="fas fa-check"></i>
                </div>

                <div>
                    <div class="fw-bold fs-4">{{ $stats['active'] }}</div>
                    <div class="small text-muted">Aktif</div>
                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="card stat-card shadow-sm">

                <div class="stat-icon bg-secondary text-white">
                    <i class="fas fa-ban"></i>
                </div>

                <div>
                    <div class="fw-bold fs-4">{{ $stats['inactive'] }}</div>
                    <div class="small text-muted">Nonaktif</div>
                </div>

            </div>

        </div>

    </div>



    {{-- ================= TABLE ================= --}}
    <div class="card table-card shadow-sm">

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table align-middle mb-0">

                    <thead>

                        <tr>
                            <th class="ps-4">#</th>
                            <th>Kategori</th>
                            <th>Pertanyaan</th>
                            <th>Status</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>

                    </thead>

                    <tbody>

@forelse($categories as $cat)
<tr class="bg-light fw-bold">
    <td colspan="5" class="ps-4 pe-4"> {{-- pe-4 supaya badge nggak nempel garis kanan --}}
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-layer-group text-primary me-2"></i>
                {{ $cat->name }}
            </div>
            
            <span class="badge badge-soft">
                {{ $cat->questions->count() }} pertanyaan
            </span>
        </div>
    </td>
</tr>
@forelse($cat->questions as $q)

<tr>

    <td class="ps-4 text-muted">
        •
    </td>

    <td></td>

    <td class="fw-semibold">
        {{ $q->question }}
    </td>

    <td>

        @if($q->is_active)

            <span class="badge bg-success py-2 px-3">
                Aktif
            </span>

        @else

            <span class="badge bg-secondary py-2 px-3">
                Nonaktif
            </span>

        @endif

    </td>


    <td class="text-center">

        <div class="action-group">

            {{-- EDIT --}}
            <button
                class="btn-icon bg-warning text-white"
                onclick="openEdit(
                    {{ $q->id }},
                    '{{ $q->question }}',
                    {{ $q->category_id }}
                )">

                <i class="fas fa-pen"></i>

            </button>


            {{-- TOGGLE --}}
            <form action="{{ route('admin.assessment-questions.toggle',$q) }}"
                  method="POST">

                @csrf
                @method('PATCH')

                <button class="btn-icon bg-info text-white">
                    <i class="fas fa-sync"></i>
                </button>

            </form>


            {{-- DELETE --}}
            <form action="{{ route('admin.assessment-questions.destroy',$q) }}"
                  method="POST"
                  onsubmit="return confirm('Hapus pertanyaan ini?')">

                @csrf
                @method('DELETE')

                <button class="btn-icon bg-danger text-white">
                    <i class="fas fa-trash"></i>
                </button>

            </form>

        </div>

    </td>

</tr>

@empty

<tr>
    <td colspan="5" class="text-center text-muted py-4">
        Belum ada pertanyaan pada kategori ini
    </td>
</tr>

@endforelse


@empty

<tr>
    <td colspan="5" class="text-center text-muted py-5">

        <i class="fas fa-inbox fa-3x mb-3 opacity-50"></i>

        <div>
            Belum ada kategori penilaian
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



{{-- ================= MODAL TAMBAH ================= --}}
<div class="modal fade" id="modalTambah">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form method="POST" action="{{ route('admin.assessment-questions.store') }}">

                @csrf

                <div class="modal-header">

                    <h5 class="modal-title fw-bold">
                        Tambah Pertanyaan
                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                </div>


                <div class="modal-body">

                    <div class="mb-3">

                        <label class="form-label fw-semibold">
                            Kategori
                        </label>

                        <select
                            name="category_id"
                            class="form-select"
                            required>

                            @foreach($categories as $cat)

                                <option value="{{ $cat->id }}">
                                    {{ $cat->name }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    <div class="mb-3">

                        <label class="form-label fw-semibold">
                            Pertanyaan
                        </label>

                        <input
                            type="text"
                            name="question"
                            class="form-control"
                            placeholder="Contoh: Siswa bekerja sama dengan baik"
                            required>

                    </div>

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal">

                        Batal

                    </button>


                    <button type="submit" class="btn btn-primary px-4">

                        <i class="fas fa-save me-1"></i>
                        Simpan

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>



{{-- ================= MODAL EDIT ================= --}}
<div class="modal fade" id="modalEdit">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <form method="POST" id="formEdit">

                @csrf
                @method('PUT')

                <div class="modal-header">

                    <h5 class="modal-title fw-bold">
                        Edit Pertanyaan
                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                </div>


                <div class="modal-body">

                    <div class="mb-3">

                        <label class="form-label fw-semibold">
                            Kategori
                        </label>

                        <select
                            name="category_id"
                            id="editCategory"
                            class="form-select">

                            @foreach($categories as $cat)

                                <option value="{{ $cat->id }}">
                                    {{ $cat->name }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    <div class="mb-3">

                        <label class="form-label fw-semibold">
                            Pertanyaan
                        </label>

                        <input
                            type="text"
                            name="question"
                            id="editQuestion"
                            class="form-control">

                    </div>

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal">

                        Batal

                    </button>


                    <button type="submit" class="btn btn-warning text-white px-4">

                        <i class="fas fa-save me-1"></i>
                        Update

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>



<script>

function openEdit(id, question, category)
{
    document.getElementById('formEdit').action =
        "/admin/assessment-questions/" + id;

    document.getElementById('editQuestion').value =
        question;

    document.getElementById('editCategory').value =
        category;

    new bootstrap.Modal(
        document.getElementById('modalEdit')
    ).show();
}

</script>

@endsection