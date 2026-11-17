<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Flashcard extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'front_content',
        'back_content',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function flashcardProgresses(): HasMany
    {
        return $this->hasMany(FlashcardProgress::class);
    }

    public function progresses(): HasMany
    {
        return $this->flashcardProgresses();
    }
}
