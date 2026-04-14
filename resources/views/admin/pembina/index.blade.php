@extends('layouts.app')

@section('content')
<div class="container py-4">

    <h4 class="mb-3">Data Pembina</h4>

    <a href="{{ route('admin.pembina.create') }}"
       class="btn btn-primary mb-3">
        + Tambah Pembina
    </a>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th width="220">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($pembinas as $pembina)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $pembina->name }}</td>
                            <td>{{ $pembina->email }}</td>

                            <td>
                                <span class="badge {{ $pembina->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $pembina->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>

                            <td>

                                {{-- EDIT --}}
                                <button class="btn btn-sm btn-info btn-edit"
                                    data-id="{{ $pembina->id }}"
                                    data-name="{{ $pembina->name }}"
                                    data-email="{{ $pembina->email }}">
                                    Edit
                                </button>

                                {{-- TOGGLE --}}
                                <form action="{{ route('admin.pembina.toggle', $pembina->id) }}"
                                      method="POST"
                                      class="d-inline">
                                    @csrf
                                    @method('PATCH')

                                    <button class="btn btn-sm btn-warning">
                                        {{ $pembina->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">
                                Belum ada pembina
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>
    </div>

</div>


{{-- ================= EDIT MODAL ================= --}}
<div class="modal fade" id="editModal">
    <div class="modal-dialog">
        <form method="POST"
              id="editForm"
              class="modal-content">
            @csrf
            @method('PUT')

            <div class="modal-header">
                <h5 class="modal-title">Edit Pembina</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label>Nama</label>
                    <input name="name" id="editName" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" id="editEmail" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Password (Kosongkan jika tidak diubah)</label>
                    <input type="password" name="password" class="form-control">
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">
                    Batal
                </button>
                <button class="btn btn-primary">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

@endsection


@push('scripts')
<script>

document.querySelectorAll('.btn-edit').forEach(btn => {

    btn.addEventListener('click', () => {

        document.getElementById('editName').value = btn.dataset.name;
        document.getElementById('editEmail').value = btn.dataset.email;

        document.getElementById('editForm').action =
            `/admin/pembina/${btn.dataset.id}`;

        new bootstrap.Modal(
            document.getElementById('editModal')
        ).show();

    });

});

</script>
@endpush