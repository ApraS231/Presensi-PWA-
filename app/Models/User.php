<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nik',
        'name',
        'email',
        'password',
        'role',
        'jabatan',
        'department',
        'no_telp',
        'avatar',
        'enrollment_status',
        'is_active',
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
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    /**
     * Relasi 1:1 ke FaceDescriptor
     */
    public function faceDescriptor(): HasOne
    {
        return $this->hasOne(FaceDescriptor::class);
    }

    /**
     * Relasi 1:N ke Catatan Presensi
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Relasi 1:N ke Pengajuan Cuti / Izin
     */
    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class, 'user_id');
    }

    /**
     * Relasi 1:N ke Izin yang disetujui / direview
     */
    public function approvedLeaves(): HasMany
    {
        return $this->hasMany(Leave::class, 'approved_by');
    }

    /**
     * Relasi 1:N ke Notifikasi User
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Relasi 1:N ke Rekaman Titik Jejak Lokasi (Tracking)
     */
    public function locationTracks(): HasMany
    {
        return $this->hasMany(LocationTrack::class);
    }
}
