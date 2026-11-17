<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FlashcardProgressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'flashcard_id' => $this->flashcard_id,
            'status' => $this->status,
            'last_reviewed_at' => $this->last_reviewed_at?->toISOString(),
            'flashcard' => new FlashcardResource($this->whenLoaded('flashcard')),
        ];
    }
}