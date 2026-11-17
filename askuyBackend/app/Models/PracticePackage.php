<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PracticePackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject_id',
        'title',
        'total_questions',
        'status',
        'pdf_path',
        'topic_name',
        'difficulty',
        'error_message',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'total_questions' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function examSessions(): HasMany
    {
        return $this->hasMany(ExamSession::class, 'package_id');
    }
}
