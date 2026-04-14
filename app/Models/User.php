<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\StudentAcademic;

use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    

    use HasRoles;
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'nisn',
        'email',
        'password',
        'is_active',
        'gender',
        'photo',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function extracurricularCoaches()
{
    return $this->hasMany(
        \App\Models\ExtracurricularCoach::class,
        'user_id'
    );
}
    public function academics()
    {
        return $this->hasMany(StudentAcademic::class);
    }

    public function currentAcademic()
    {
        return $this->hasOne(StudentAcademic::class)
            ->whereHas('schoolYear', fn ($q) => $q->where('is_active', true));
    }
    public function extracurricularMembers()
{
    return $this->hasMany(
        \App\Models\ExtracurricularMember::class
    );
}

public function getPhotoUrlAttribute()
{
    if ($this->photo) {
        return asset('storage/students/' . $this->photo);
    }
    return null;

}
public function attendances()
{
    return $this->hasMany(\App\Models\Attendance::class);
}

}
