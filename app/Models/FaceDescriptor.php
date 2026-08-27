<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FaceDescriptor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'descriptor_data',
        'sample_photo',
    ];

    protected function casts(): array
    {
        return [
            'descriptor_data' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
