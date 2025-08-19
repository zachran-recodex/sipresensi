<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FaceEnrollment extends Model
{
    protected $fillable = [
        'user_id',
        'biznet_user_id',
        'face_gallery_id',
        'enrolled_at',
        'enrollment_response',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
            'enrollment_response' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
