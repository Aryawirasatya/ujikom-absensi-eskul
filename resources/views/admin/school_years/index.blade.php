@extends('layouts.app')

@section('content')
<div class="container">

    <h4 class="mb-4">Manajemen Tahun Ajaran</h4>

    {{-- ALERT --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    {{-- MAIN CARD --}}
    <div class="card shadow-sm">

        {{-- CARD HEADER --}}
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Tahun Ajaran</h5>
                @if($currentYear)
                    <small class="text-muted">
                        Aktif saat ini:
                        <span class="fw-semibold text-success">
                            {{ $currentYear->name }}
                        </span>
                        · sejak {{ $currentYear->start_date->format('d M Y H:i') }}
                    </small>
                @else
                    <small class="text-danger">
                        Belum ada tahun ajaran aktif
                    </small>
                @endif
            </div>

            <button class="btn btn-danger"
                data-bs-toggle="modal"
                data-bs-target="#switchYearModal">
                <i class="bi bi-arrow-repeat me-1"></i>
                Ganti Tahun
            </button>
        </div>

        {{-- CARD BODY --}}
        <div class="card-body p-0">

            <table class="table table-bordered table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 20%">Tahun</th>
                        <th>Mulai</th>
                        <th>Selesai</th>
                        <th style="width: 15%">Status</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($history as $sy)
                    <tr class="{{ $sy->is_active ? 'table-success' : '' }}">
                        <td class="fw-semibold">
                            {{ $sy->name }}
                        </td>
                        <td>
                            {{ $sy->start_date?->format('d M Y H:i') ?? '-' }}
                        </td>
                        <td>
                            {{ $sy->end_date?->format('d M Y H:i') ?? '-' }}
                        </td>
                        <td>
                            @if($sy->is_active)
                                <span class="badge bg-success">AKTIF</span>
                            @else
                                <span class="badge bg-secondary">ARSIP</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- CARD FOOTER --}}
        <div class="card-footer bg-white">
            {{ $history->links() }}
        </div>
    </div>
</div>

{{-- MODAL GANTI TAHUN --}}
<div class="modal fade" id="switchYearModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <form method="POST"
              action="{{ route('admin.school-years.switch') }}"
              class="modal-content border-0 shadow">
            @csrf

            <div class="modal-header border-0">
                <div>
                    <h5 class="modal-title fw-semibold">
                        Ganti Tahun Ajaran
                    </h5>
                    <small class="text-muted">
                        Konfirmasi tindakan administratif sistem
                    </small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="bg-light rounded p-3 mb-3">
                    @if(!$currentYear)
                        <label class="form-label fw-semibold">
                            Tahun Awal Sistem
                        </label>
                        <input type="number"
                               name="start_year"
                               class="form-control"
                               placeholder="Contoh: 2024"
                               required>
                        <small class="text-muted">
                            Hanya diisi sekali saat sistem pertama kali digunakan.
                        </small>
                    @else
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small">Tahun Saat Ini</div>
                                <div class="fw-semibold">{{ $currentYear->name }}</div>
                            </div>

                            <i class="bi bi-arrow-right fs-5 text-muted"></i>

                            <div class="text-end">
                                <div class="text-muted small">Tahun Berikutnya</div>
                                <div class="fw-semibold text-danger">
                                    {{ $nextYearLabel }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Password Admin
                    </label>
                    <input type="password"
                           name="password"
                           class="form-control"
                           placeholder="Masukkan password admin"
                           required>
                </div>

                <div class="d-flex gap-2 p-3 rounded bg-warning-subtle">
                    <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                    <div class="small">
                        <strong>Tindakan permanen.</strong><br>
                        Pergantian tahun ajaran tidak dapat dibatalkan.
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0">
                <button type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">
                    Batal
                </button>
                <button type="submit"
                        class="btn btn-danger px-4">
                    Konfirmasi & Ganti Tahun
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
