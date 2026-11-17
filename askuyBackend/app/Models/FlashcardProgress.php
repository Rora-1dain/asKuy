<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlashcardProgress extends Model
{
    use HasFactory;

    protected $table = 'flashcard_progress';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'flashcard_id',
        'status',
        'last_reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'last_reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function flashcard(): BelongsTo
    {
        return $this->belongsTo(Flashcard::class);
    }
}
