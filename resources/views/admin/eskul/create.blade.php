@extends('layouts.app')

@section('content')

<div class="container">

```
<!-- Page Header -->
<div class="d-flex align-items-center mb-4">
    <div>
        <h3 class="mb-0">Tambah Ekstrakurikuler</h3>
        <small class="text-muted">
            Buat eskul baru dan tentukan pembina yang bertanggung jawab
        </small>
    </div>
</div>

<form action="{{ route('admin.eskul.store') }}" method="POST">
    @csrf

    <div class="card shadow-sm">

        <div class="card-body">

            <!-- INFORMASI ESKUL -->
            <h5 class="mb-3">Informasi Eskul</h5>

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label class="form-label fw-semibold">
                        Nama Eskul <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        name="name"
                        class="form-control"
                        placeholder="Contoh: Futsal, Pramuka, Paskibra"
                        required>

                </div>

                <div class="col-md-6 mb-3">

                    <label class="form-label fw-semibold">
                        Deskripsi
                    </label>

                    <textarea
                        name="description"
                        class="form-control"
                        rows="3"
                        placeholder="Deskripsi singkat eskul (opsional)"></textarea>

                </div>

            </div>

            <hr class="my-4">

            <!-- PEMBINA -->
            <h5 class="mb-3">Pembina Eskul</h5>

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label class="form-label fw-semibold">
                        Pembina <span class="text-danger">*</span>
                    </label>

                    <select
                        name="primary_coach"
                        class="form-select"
                        required>

                        <option value="">
                            -- Pilih Pembina --
                        </option>

                        @foreach ($pembinas as $pembina)

                            <option value="{{ $pembina->id }}">
                                {{ $pembina->name }}
                            </option>

                        @endforeach

                    </select>

                    <small class="text-muted">
                        Pembina ini akan bertanggung jawab atas kegiatan dan absensi eskul.
                    </small>

                </div>

            </div>

        </div>

        <!-- ACTION -->
        <div class="card-footer d-flex justify-content-between">

            <a href="{{ route('admin.eskul.index') }}"
               class="btn btn-light">
                Kembali
            </a>

            <button type="submit"
                    class="btn btn-primary px-4">
                Simpan Eskul
            </button>

        </div>

    </div>

</form>
```

</div>
@endsection
