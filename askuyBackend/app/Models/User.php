<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'education_level',
        'major',
        'study_goal',
        'google_id',
        'avatar_url',
        'study_streak',
        'last_study_date',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'study_streak' => 'integer',
            'last_study_date' => 'date',
        ];
    }

    public function practicePackages(): HasMany
    {
        return $this->hasMany(PracticePackage::class);
    }

    public function examSessions(): HasMany
    {
        return $this->hasMany(ExamSession::class);
    }

    public function aiWeaknessMappings(): HasMany
    {
        return $this->hasMany(AiWeaknessMapping::class);
    }

    public function focusSessions(): HasMany
    {
        return $this->hasMany(FocusSession::class);
    }

    public function chatSessions(): HasMany
    {
        return $this->hasMany(ChatSession::class);
    }

    public function gradeUploads(): HasMany
    {
        return $this->hasMany(GradeUpload::class);
    }

    public function flashcardProgresses(): HasMany
    {
        return $this->hasMany(FlashcardProgress::class);
    }
}