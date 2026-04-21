@extends('layouts.app')

@section('content')

<div class="container-fluid py-4">

{{-- HEADER --}}
<div class="mb-4">
    <h3 class="fw-bold mb-1">Edit Eskul</h3>
    <div class="text-muted">
        Perbarui data eskul dan pembinanya
    </div>
</div>

<form method="POST" action="{{ route('admin.eskul.update', $eskul->id) }}">
    @csrf
    @method('PUT')

    <div class="card shadow-sm border-0">

        <div class="card-body">

            {{-- ================= INFORMASI ESKUL ================= --}}
            <h5 class="fw-semibold mb-3">Informasi Eskul</h5>

            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">
                        Nama Eskul <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        name="name"
                        class="form-control"
                        value="{{ old('name', $eskul->name) }}"
                        required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Deskripsi</label>

                    <textarea
                        name="description"
                        class="form-control"
                        rows="3">{{ old('description', $eskul->description) }}</textarea>
                </div>

            </div>

            <hr class="my-4">

            {{-- ================= PEMBINA ================= --}}
            <h5 class="fw-semibold mb-3">Pembina Eskul</h5>

            <div class="row g-3">

                <div class="col-md-6">

                    <label class="form-label">
                        Pembina <span class="text-danger">*</span>
                    </label>

                    <select
                        name="primary_coach"
                        class="form-select"
                        required>

                        <option value="">-- Pilih Pembina --</option>

                        @foreach ($pembinas as $pembina)

                            <option
                                value="{{ $pembina->id }}"
                                @selected(old('primary_coach', $primary?->user_id) == $pembina->id)>

                                {{ $pembina->name }}

                            </option>

                        @endforeach

                    </select>

                </div>

            </div>

        </div>

        {{-- ACTION --}}
        <div class="card-footer bg-white d-flex justify-content-between">

            <a href="{{ route('admin.eskul.index') }}" class="btn btn-light">
                Batal
            </a>

            <button type="submit" class="btn btn-primary px-4">
                Simpan Perubahan
            </button>

        </div>

    </div>
</form>
 
</div>
@endsection
