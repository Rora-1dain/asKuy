<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'package_id',
        'score',
        'accuracy_percentage',
        'duration_seconds',
        'grade_category',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'accuracy_percentage' => 'decimal:2',
            'duration_seconds' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function practicePackage(): BelongsTo
    {
        return $this->belongsTo(PracticePackage::class, 'package_id');
    }

    public function examAnswers(): HasMany
    {
        return $this->hasMany(ExamAnswer::class, 'exam_session_id');
    }

    public function answers(): HasMany
    {
        return $this->examAnswers();
    }
}
