<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PracticePackageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'subject_id' => $this->subject_id,
            'subject' => new SubjectResource($this->whenLoaded('subject')),
            'title' => $this->title,
            'topic_name' => $this->topic_name,
            'difficulty' => $this->difficulty,
            'total_questions' => $this->total_questions,
            'status' => $this->status,
            'pdf_path' => $this->pdf_path,
            'error_message' => $this->error_message,
            'completed_at' => $this->completed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}