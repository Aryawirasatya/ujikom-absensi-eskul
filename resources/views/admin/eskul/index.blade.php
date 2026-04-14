@extends('layouts.app')

@section('content')

<div class="container-fluid py-4">

```
{{-- HEADER --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Data Eskul</h3>
        <div class="text-muted">
            Kelola ekstrakurikuler dan pembinanya
        </div>
    </div>

    <a href="{{ route('admin.eskul.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>
        Tambah Eskul
    </a>
</div>

{{-- TABLE --}}
<div class="card shadow-sm border-0">
    <div class="table-responsive">

        <table class="table table-hover align-middle mb-0">

            <thead class="table-light">
                <tr>
                    <th style="width:60px">#</th>
                    <th>Nama Eskul</th>
                    <th>Pembina</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>

            <tbody>

            @forelse ($eskuls as $eskul)

                <tr>

                    <td>{{ $loop->iteration }}</td>

                    <td class="fw-semibold">
                        {{ $eskul->name }}
                    </td>

                    <td>
                        {{ optional($eskul->primaryCoach?->user)->name ?? '-' }}
                    </td>

                    <td>
                        <span class="badge {{ $eskul->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $eskul->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>

                    <td class="text-end">

                        <div class="d-flex justify-content-end gap-2">

                            {{-- EDIT --}}
                            <a href="{{ route('admin.eskul.edit', $eskul->id) }}"
                               class="btn btn-sm btn-outline-primary">
                                Edit
                            </a>

                            {{-- TOGGLE --}}
                            <form action="{{ route('admin.eskul.toggle', $eskul->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Yakin ingin mengubah status eskul ini?')">

                                @csrf
                                @method('PATCH')

                                <button
                                    class="btn btn-sm {{ $eskul->is_active ? 'btn-warning' : 'btn-success' }}">

                                    {{ $eskul->is_active ? 'Nonaktifkan' : 'Aktifkan' }}

                                </button>

                            </form>

                        </div>

                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        Belum ada eskul
                    </td>
                </tr>

            @endforelse

            </tbody>

        </table>

    </div>
</div>
 
</div>
@endsection
