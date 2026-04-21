<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointLedger extends Model
{
    use HasFactory;

    protected $fillable = [
    'user_id',
    'attendance_id',  
    'transaction_type',
    'amount',
    'current_balance',
    'description',
];

    // Relasi: Buku besar ini milik siapa?
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function attendance()
{
    return $this->belongsTo(Attendance::class);
}
}