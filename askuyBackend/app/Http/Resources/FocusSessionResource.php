<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FocusSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'duration_minutes' => $this->duration_minutes,
            'ambient_sound_used' => $this->ambient_sound_used,
            'session_type' => $this->session_type,
            'stress_level' => $this->stress_level,
            'completed_at' => $this->completed_at?->toISOString(),
        ];
    }
}