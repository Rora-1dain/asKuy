<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FlashcardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Mendapatkan progress untuk user saat ini jika di-load
        $userProgress = $this->relationLoaded('progresses')
            ? $this->progresses->first()
            : null;

        return [
            'id' => $this->id,
            'subject_id' => $this->subject_id,
            'subject' => new SubjectResource($this->whenLoaded('subject')),
            'front_content' => $this->front_content,
            'back_content' => $this->back_content,
            'status' => $userProgress ? $userProgress->status : 'not_memorized',
            'last_reviewed_at' => $userProgress?->last_reviewed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}