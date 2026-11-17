<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'education_level' => $this->education_level,
            'major' => $this->major,
            'study_goal' => $this->study_goal,
            'google_id' => $this->google_id,
            'avatar_url' => $this->avatar_url,
            'study_streak' => $this->study_streak,
            'last_study_date' => $this->last_study_date?->format('Y-m-d'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}