<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'location_id',
        'date',
        'time_in',
        'time_out',
        'lat_in',
        'long_in',
        'lat_out',
        'long_out',
        'photo_in',
        'photo_out',
        'status',
        'distance_meters',
        'auto_checkout',
    ];

    protected function casts(): array
    {
        return [
            'date'            => 'date:Y-m-d',
            'lat_in'          => 'float',
            'long_in'         => 'float',
            'lat_out'         => 'float',
            'long_out'        => 'float',
            'distance_meters' => 'float',
            'auto_checkout'   => 'boolean',
        ];
    }

    public function setDateAttribute($value): void
    {
        $this->attributes['date'] = $value instanceof \Carbon\CarbonInterface
            ? $value->format('Y-m-d')
            : substr((string) $value, 0, 10);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function locationTracks(): HasMany
    {
        return $this->hasMany(LocationTrack::class);
    }
}
