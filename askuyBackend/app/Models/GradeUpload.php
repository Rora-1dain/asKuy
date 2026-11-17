<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject_id',
        'original_name',
        'file_path',
        'file_type',
        'status',
        'analysis_result',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'analysis_result' => 'array',
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
}
