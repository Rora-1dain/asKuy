<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'semester',
    ];

    protected function casts(): array
    {
        return [
            'semester' => 'integer',
        ];
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function practicePackages(): HasMany
    {
        return $this->hasMany(PracticePackage::class);
    }

    public function aiWeaknessMappings(): HasMany
    {
        return $this->hasMany(AiWeaknessMapping::class);
    }

    public function flashcards(): HasMany
    {
        return $this->hasMany(Flashcard::class);
    }

    public function chatSessions(): HasMany
    {
        return $this->hasMany(ChatSession::class);
    }

    public function gradeUploads(): HasMany
    {
        return $this->hasMany(GradeUpload::class);
    }
}
