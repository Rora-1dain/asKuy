<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GradeUploadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'subject_id' => $this->subject_id,
            'subject' => new SubjectResource($this->whenLoaded('subject')),
            'original_name' => $this->original_name,
            'file_path' => $this->file_path,
            'file_type' => $this->file_type,
            'status' => $this->status,
            'analysis_result' => $this->analysis_result,
            'error_message' => $this->error_message,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}