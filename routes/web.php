<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AssessmentCategoryController;
use App\Http\Controllers\Admin\AssessmentReportController;


use App\Http\Controllers\Siswa\AssessmentController as SiswaAssessmentController;
/*
|--------------------------------------------------------------------------
| ADMIN CONTROLLERS
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Admin\{
    SchoolYearActionController,
    StudentController,
    PembinaController,
    ExtracurricularController,
    ReportController,
    AssessmentQuestionController,
    DompetAdminController,
    ScheduleExceptionController as AdminScheduleExceptionController
};

/*
|--------------------------------------------------------------------------
| PEMBINA CONTROLLERS
|--------------------------------------------------------------------------
*/
use App\Http\Controllers\Pembina\{
    ActivityController,
    QrScanController,
    AttendanceController,
    EskulController,
    MemberController,
    ScheduleController,
    AssessmentController as PembinaAssessmentController,
    ScheduleExceptionController as PembinaScheduleExceptionController
};
use App\Http\Controllers\Siswa\{
    StudentQrController,
    StudentEskulController,
    StudentAttendanceController,
    DompetSiswaController
    


};
/*
|--------------------------------------------------------------------------
| ROOT
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect()->route('login'));

/*
|--------------------------------------------------------------------------
| AUTH (SEMUA ROLE)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'check.user.active'])->group(function () {
    Route::get('/change-password', [ProfileController::class, 'showChangePasswordForm'])->name('password.change.form');
    Route::post('/change-password', [ProfileController::class, 'updatePassword'])->name('password.change.update');
});
Route::middleware(['auth','check.user.active','force.student.password'])
->group(function () {

    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});

/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'check.user.active', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Students
        Route::resource('students', StudentController::class)->except(['show']);
        Route::post('students/import', [StudentController::class, 'import'])->name('students.import');
        Route::post('students/import/photos', [StudentController::class, 'importPhotos'])->name('students.import.photos');

        // Eskul
        Route::resource('eskul', ExtracurricularController::class)->except(['show', 'destroy']);
        Route::patch('eskul/{eskul}/toggle', [ExtracurricularController::class, 'toggle'])->name('eskul.toggle');
        Route::patch('eskul/{eskul}/assessment-visibility', [AssessmentReportController::class, 'toggleVisibility'])->name('eskul.assessment-visibility');

        // Pembina
        Route::resource('pembina', PembinaController::class);
        Route::patch('pembina/{id}/toggle', [PembinaController::class, 'toggle'])->name('pembina.toggle');

        // School Years
        Route::get('/school-years', [SchoolYearActionController::class, 'index'])->name('school-years.index');
        Route::post('/school-years/switch', [SchoolYearActionController::class, 'switch'])->name('school-years.switch');

        // Laporan Kehadiran
        Route::prefix('laporan')->name('laporan.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/export-excel', [ReportController::class, 'exportExcelGlobal'])->name('export-excel');
            Route::get('/export-pdf', [ReportController::class, 'exportPdfGlobal'])->name('export-pdf');
            Route::get('/per-pembina', [ReportController::class, 'perPembina'])->name('per-pembina');
            Route::get('/per-pembina/export-excel', [ReportController::class, 'exportExcelPembina'])->name('per-pembina.export-excel');
            Route::get('/per-pembina/export-pdf', [ReportController::class, 'exportPdfPembina'])->name('per-pembina.export-pdf');
            Route::get('/per-siswa', [ReportController::class, 'perSiswa'])->name('per-siswa');
            Route::get('/per-siswa/export-excel', [ReportController::class, 'exportExcelSiswa'])->name('per-siswa.export-excel');
            Route::get('/per-siswa/export-pdf', [ReportController::class, 'exportPdfSiswa'])->name('per-siswa.export-pdf');
            Route::get('/per-eskul/{eskul}', [ReportController::class, 'perEskul'])->name('per-eskul');
            Route::get('/per-eskul/{eskul}/export-excel', [ReportController::class, 'exportExcelEskul'])->name('per-eskul.export-excel');
            Route::get('/per-eskul/{eskul}/export-pdf', [ReportController::class, 'exportPdfEskul'])->name('per-eskul.export-pdf');
        });

        // Kategori Penilaian
        Route::prefix('assessment-categories')->name('assessment-categories.')->group(function () {
            Route::get('/', [AssessmentCategoryController::class, 'index'])->name('index');
            Route::post('/', [AssessmentCategoryController::class, 'store'])->name('store');
            Route::put('/{category}', [AssessmentCategoryController::class, 'update'])->name('update');
            Route::patch('/{category}/toggle', [AssessmentCategoryController::class, 'toggleActive'])->name('toggle');
            Route::delete('/{category}', [AssessmentCategoryController::class, 'destroy'])->name('destroy');
        });
           Route::get('assessment-questions', 
        [AssessmentQuestionController::class,'index']
            )->name('assessment-questions.index');

            Route::post('assessment-questions', 
                [AssessmentQuestionController::class,'store']
            )->name('assessment-questions.store');

            Route::put('assessment-questions/{assessmentQuestion}', 
                [AssessmentQuestionController::class,'update']
            )->name('assessment-questions.update');

            Route::patch('assessment-questions/{assessmentQuestion}/toggle', 
                [AssessmentQuestionController::class,'toggle']
            )->name('assessment-questions.toggle');

            Route::delete('assessment-questions/{assessmentQuestion}', 
                [AssessmentQuestionController::class,'destroy']
            )->name('assessment-questions.destroy');
        // Laporan Penilaian Sikap
        Route::prefix('penilaian')->name('penilaian.')->group(function () {
            Route::get('/', [AssessmentReportController::class, 'index'])->name('index');
            Route::get('/eskul/{eskul}', [AssessmentReportController::class, 'perEskul'])->name('per-eskul');
            Route::get('/siswa/{user}', [AssessmentReportController::class, 'perSiswa'])->name('per-siswa');
        });


        Route::get('/dompet', [DompetAdminController::class, 'index'])->name('dompet.index');
    
        // Kelola Aturan (Rule Engine)
        Route::post('/dompet/rules', [DompetAdminController::class, 'storeRule'])->name('dompet.rules.store');
        Route::delete('/dompet/rules/{rule}', [DompetAdminController::class, 'destroyRule'])->name('dompet.rules.destroy');
        
        // Kelola Katalog (Marketplace)
        Route::post('/dompet/items', [DompetAdminController::class, 'storeItem'])->name('dompet.items.store');
        Route::delete('/dompet/items/{item}', [DompetAdminController::class, 'destroyItem'])->name('dompet.items.destroy'); 
        // RULE
Route::put('/dompet/rules/{id}', [DompetAdminController::class, 'updateRule'])
    ->name('dompet.rules.update');

// ITEM
Route::put('/dompet/items/{id}', [DompetAdminController::class, 'updateItem'])
    ->name('dompet.items.update');
    });

/*
|--------------------------------------------------------------------------
| PEMBINA
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'check.user.active', 'role:pembina'])
    ->prefix('pembina')
    ->name('pembina.')
    ->group(function () {

        // Dashboard eskul
        Route::get('/eskul', [EskulController::class, 'index'])->name('eskul.index');

        // Laporan kehadiran (lintas eskul)
        Route::get('/laporan', [\App\Http\Controllers\Pembina\ReportController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/{eskul}', [\App\Http\Controllers\Pembina\ReportController::class, 'show'])->name('laporan.show');

        // QR scan global
        Route::post('/qr-scan/process', [QrScanController::class, 'scan'])->name('qr.scan_process');
        Route::get('/penilaian/laporan-index', [PembinaAssessmentController::class, 'indexLaporan'])->name('penilaian.laporan_index');
        // Per-eskul routes
        Route::prefix('eskul/{eskul}')->group(function () {

            // Members
            Route::get('/members', [MemberController::class, 'index'])->name('members.index');
            Route::get('/members/search', [MemberController::class, 'searchStudents'])->name('members.search');
            Route::post('/members', [MemberController::class, 'store'])->name('members.store');
            Route::delete('/members/{member}', [MemberController::class, 'destroy'])->name('members.destroy');
            Route::patch('/members/{member}/activate', [MemberController::class, 'activate'])->name('members.activate');
            Route::patch('/members/{member}/deactivate', [MemberController::class, 'deactivate'])->name('members.deactivate');

            // Schedules
            Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
            Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
            Route::patch('/schedules/{schedule}/toggle', [ScheduleController::class, 'toggle'])->name('schedules.toggle');
            Route::delete('/schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');

            // Laporan per-eskul
            Route::prefix('laporan')->name('laporan.')->group(function () {
                Route::get('/{user}/detail', [\App\Http\Controllers\Pembina\ReportController::class, 'detailSiswa'])->name('detail-siswa');
                Route::get('/export-excel', [\App\Http\Controllers\Pembina\ReportController::class, 'exportExcel'])->name('export-excel');
                Route::get('/export-pdf', [\App\Http\Controllers\Pembina\ReportController::class, 'exportPdf'])->name('export-pdf');
                Route::get('/{user}/export-pdf', [\App\Http\Controllers\Pembina\ReportController::class, 'exportPdfSiswa'])->name('detail-siswa.export-pdf');
            });

            // ===================================================
            // PENILAIAN SIKAP — urutan penting, spesifik dulu
            // ===================================================
            Route::get('/penilaian/laporan', [PembinaAssessmentController::class, 'laporan'])->name('penilaian.laporan');
            Route::get('/penilaian/siswa/{user}', [PembinaAssessmentController::class, 'showSiswa'])->name('penilaian.siswa');
            Route::get('/penilaian', [PembinaAssessmentController::class, 'index'])->name('penilaian.index');
            Route::post('/penilaian', [PembinaAssessmentController::class, 'store'])->name('penilaian.store');
            Route::post('/penilaian/tutup', [PembinaAssessmentController::class, 'tutupPeriode'])->name('penilaian.tutup');
            Route::post(
            'period/{period}/close',
            [PembinaAssessmentController::class,'closePeriod']
            )->name('period.close');
            Route::post('period/create', [PembinaAssessmentController::class, 'createPeriod'])
    ->name('period.create');
            Route::post(
            'period/{period}/open',
            [PembinaAssessmentController::class,'reopenPeriod']
            )->name('period.open');
            // Activity / Attendance
            Route::get('/attendance', [ActivityController::class, 'index'])->name('activity.index');
            Route::post('/attendance/non-routine', [ActivityController::class, 'storeNonRoutine'])->name('activity.store_non_routine');

            Route::prefix('activity/{activity}')->name('activity.')->group(function () {
                Route::get('/', [ActivityController::class, 'show'])->name('show');
                Route::post('/finish', [AttendanceController::class, 'finishActivity'])->name('finish');
                Route::post('/cancel', [ActivityController::class, 'cancel'])->name('cancel');
                Route::post('/finalize-validation', [AttendanceController::class, 'finalizeValidation'])->name('finalize_validation');
                Route::post('/bulk-manual', [AttendanceController::class, 'bulkMarkManual'])->name('bulk_manual');
                Route::post('/attendance/manual', [AttendanceController::class, 'markManual'])->name('manual');
                Route::post('/choose-mode', [AttendanceController::class, 'chooseMode'])->name('choose_mode');
                Route::get('/manual', [AttendanceController::class, 'manualPage'])->name('manual_page');
                Route::post('/manual-checkin', [AttendanceController::class, 'manualCheckin'])->name('manual_checkin');
                Route::post('/bulk-manual-checkin', [AttendanceController::class, 'bulkManualCheckin'])->name('bulk_manual_checkin');
                Route::post('/manual-open-checkout', [AttendanceController::class, 'manualOpenCheckout'])->name('manual_open_checkout');
                Route::post('/manual-checkout-save', [AttendanceController::class, 'manualCheckout'])->name('manual_checkout_save');
                Route::post('/bulk-manual-checkout', [AttendanceController::class, 'bulkManualCheckout'])->name('bulk_manual_checkout');
                Route::post('/manual-start-checkin', [AttendanceController::class, 'manualStartCheckin'])->name('manual_start_checkin');
                Route::post('/checkout/close', [AttendanceController::class, 'closeCheckout'])->name('checkout.close');
                Route::get('/scan', [QrScanController::class, 'scanView'])->name('qr.scan_view');
                Route::prefix('session')->name('session.')->group(function () {
                    Route::post('/open', [QrScanController::class, 'openSession'])->name('open');
                    Route::post('/close', [QrScanController::class, 'closeSession'])->name('close');
                });
            });
        });
    });

/*
|--------------------------------------------------------------------------
| SISWA
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'check.user.active', 'role:siswa'])
    ->prefix('siswa')
    ->name('siswa.')
    ->group(function () {

        Route::get('/qr', [StudentQrController::class, 'show'])->name('qr.show');
        Route::get('/eskul', [StudentEskulController::class, 'index'])->name('eskul.index');
        Route::get('/attendance', [StudentAttendanceController::class, 'index'])->name('attendance.index');

        Route::prefix('kehadiran')->name('kehadiran.')->group(function () {
            Route::get('/', [StudentAttendanceController::class, 'index'])->name('index');
            Route::get('/export-excel', [StudentAttendanceController::class, 'exportExcel'])->name('export-excel');
            Route::get('/export-pdf', [StudentAttendanceController::class, 'exportPdf'])->name('export-pdf');
        });

        // Penilaian — terpisah dari kehadiran
        Route::get('/penilaian', [SiswaAssessmentController::class, 'index'])->name('penilaian.index');
        Route::get('/dompet', [DompetSiswaController::class, 'index'])->name('dompet.index');
    Route::post('/dompet/buy', [DompetSiswaController::class, 'buyToken'])->name('dompet.buy');


Route::post('/dompet/deactivate/{id}', [DompetSiswaController::class, 'deactivateToken'])
    ->name('dompet.deactivate');
    });

require __DIR__.'/auth.php';