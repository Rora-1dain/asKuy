<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FocusSession extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'duration_minutes',
        'ambient_sound_used',
        'session_type',
        'stress_level',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'duration_minutes' => 'integer',
            'stress_level' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
