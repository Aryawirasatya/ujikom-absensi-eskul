<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'item_id',
        'status',
        'used_at_attendance_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Item Marketplace
     * Ditambahkan withTrashed() agar jika item dihapus Admin, 
     * siswa tetap bisa melihat nama token di Inventory mereka.
     */
    public function item()
    {
        return $this->belongsTo(FlexibilityItem::class, 'item_id')->withTrashed();
    }

    /**
     * Relasi: Token ini dipakai untuk menutupi absen yang mana?
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'used_at_attendance_id');
    }
}