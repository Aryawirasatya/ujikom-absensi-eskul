@extends('layouts.app')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

    .min-wrapper {
        font-family: 'Inter', sans-serif;
        color: #1a1a1a;
        padding-top: 2rem;
    }

    .min-header {
        margin-bottom: 3rem;
    }

    .min-title {
        font-weight: 700;
        font-size: 1.75rem;
        letter-spacing: -0.02em;
    }

    .min-card {
        background: #ffffff;
        border: 1px solid #eaecf0; /* Garis sangat tipis */
        border-radius: 12px;
        transition: all 0.2s ease;
        padding: 24px;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .min-card:hover {
        border-color: #d0d5dd;
        box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.03);
    }

    .category-tag {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #667085;
        margin-bottom: 12px;
        display: block;
    }

    .min-card-title {
        font-weight: 600;
        font-size: 1.25rem;
        margin-bottom: 8px;
        color: #101828;
    }

    .min-card-desc {
        font-size: 0.875rem;
        color: #475467;
        line-height: 1.5;
        margin-bottom: 20px;
        flex-grow: 1;
    }

    .info-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 16px;
        font-size: 0.875rem;
        color: #344054;
    }

    .info-row i {
        color: #98a2b3;
    }

    /* Progress Styling - Ultra Thin */
    .progress-label {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        font-weight: 500;
        margin-bottom: 6px;
    }

    .progress-thin {
        height: 4px;
        background-color: #f2f4f7;
        border-radius: 2px;
    }

    .progress-bar-minimal {
        background-color: #101828; /* Hitam pekat atau biru gelap */
        border-radius: 2px;
    }

    .btn-minimal {
        margin-top: 24px;
        padding: 10px;
        border: 1px solid #d0d5dd;
        background: white;
        border-radius: 8px;
        color: #344054;
        font-size: 0.875rem;
        font-weight: 600;
        text-align: center;
        text-decoration: none;
        transition: all 0.2s;
    }

    .btn-minimal:hover {
        background: #f9fafb;
        color: #101828;
    }

    .empty-state-minimal {
        text-align: center;
        padding: 6rem 0;
        border: 2px dashed #eaecf0;
        border-radius: 16px;
    }
</style>

<div class="container min-wrapper">
    
    <header class="min-header d-flex justify-content-between align-items-end">
        <div>
            <h1 class="min-title">Ekstrakurikuler</h1>
            <p class="text-muted m-0">Kamu terdaftar di {{ $memberships->count() }} kegiatan aktif.</p>
        </div>
    </header>

    <div class="row g-4">
        @forelse($memberships as $member)
            <div class="col-md-6 col-lg-4">
                <div class="min-card">
                    <span class="category-tag">Status • Aktif</span>
                    
                    <h2 class="min-card-title">{{ $member->extracurricular->name }}</h2>
                    
                    <p class="min-card-desc">
                        {{ Str::limit($member->extracurricular->description ?? 'Tidak ada deskripsi kegiatan untuk ekstrakurikuler ini.', 100) }}
                    </p>

                    <div class="info-row">
                        <i class="bi bi-person"></i>
                        <span>pembina: {{ optional($member->extracurricular->primaryCoach->user)->name ?? '-' }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="empty-state-minimal">
                    <i class="bi bi-folder2-open text-muted" style="font-size: 2rem;"></i>
                    <h5 class="mt-3 fw-semibold">Belum ada eskul</h5>
                    <p class="text-muted small">Hubungi admin untuk mendaftar ke ekstrakurikuler.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection