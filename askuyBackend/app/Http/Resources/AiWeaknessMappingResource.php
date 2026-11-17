<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AiWeaknessMappingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'subject_id' => $this->subject_id,
            'subject' => new SubjectResource($this->whenLoaded('subject')),
            'weak_topic' => $this->weak_topic,
            'mastery_percentage' => (float) $this->mastery_percentage,
            'status' => $this->status,
            'last_evaluated_at' => $this->last_evaluated_at?->toISOString(),
        ];
    }
}