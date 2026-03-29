@extends('layouts.app')

@section('content')
<div class="container py-4">

    <div class="row justify-content-center">
        <div class="col-lg-6">

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">

                    <div class="mb-4 text-center">
                        <h4 class="fw-bold mb-1">Tambah Pembina</h4>
                        <p class="text-muted mb-0">
                            Buat akun pembina baru
                        </p>
                    </div>

                    <form action="{{ route('admin.pembina.store') }}" method="POST">
                        @csrf

                        {{-- ================= NAMA ================= --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Lengkap</label>

                            <input type="text"
                                   name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   placeholder="Contoh: Budi Santoso"
                                   value="{{ old('name') }}"
                                   required>

                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- ================= EMAIL ================= --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>

                            <input type="email"
                                   name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   placeholder="contoh: budi@gmail.com"
                                   value="{{ old('email') }}"
                                   required>

                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- ================= PASSWORD ================= --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Password</label>

                            <input type="password"
                                   name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Minimal 6 karakter"
                                   required>

                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- ================= BUTTON ================= --}}
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('admin.pembina.index') }}"
                               class="btn btn-outline-secondary px-4">
                                Kembali
                            </a>

                            <button type="submit" class="btn btn-primary px-4">
                                Simpan Pembina
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>

</div>
@endsection