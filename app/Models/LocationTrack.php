<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocationTrack extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'date',
        'latitude',
        'longitude',
        'accuracy',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'date'        => 'date:Y-m-d',
            'latitude'    => 'float',
            'longitude'   => 'float',
            'accuracy'    => 'float',
            'recorded_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }
}
