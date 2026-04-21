<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_year_id',
        'extracurricular_id',
        'schedule_id',
        'session_owner_id',
        'type',
        'title',
        'description',
        'activity_date',
        'status',
        'attendance_phase',
        'attendance_mode', 
        'started_at',
        'ended_at',
        'created_by',
        'checkin_open_at',
        'cancel_reason',  
        'cancelled_at',  // Tambahkan ini jika belum ada
    ];

    protected $casts = [
        'activity_date'   => 'date',
        'started_at'      => 'datetime',
        'checkin_open_at' => 'datetime',
        'ended_at'        => 'datetime',
        'cancelled_at'    => 'datetime',
    ];

    // Relasi ke Tahun Ajaran
    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }
    
    // Relasi ke Ekstrakurikuler
    public function extracurricular()
    {
        return $this->belongsTo(Extracurricular::class);
    }

    // Relasi ke Jadwal Master (Hanya untuk tipe routine)
    public function schedule()
    {
        return $this->belongsTo(ExtracurricularSchedule::class, 'schedule_id');
    }

    // Relasi ke Daftar Kehadiran Siswa
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    // Relasi ke Sesi QR yang aktif
    public function qrSessions()
    {
        return $this->hasMany(ActivityQrSession::class, 'activity_id');
    }

    // Relasi ke Pembina (Owner Sesi)
    public function owner()
    {
        return $this->belongsTo(User::class, 'session_owner_id');
    }
}