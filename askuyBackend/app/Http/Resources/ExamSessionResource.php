<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'package_id' => $this->package_id,
            'practice_package' => new PracticePackageResource($this->whenLoaded('practicePackage')),
            'score' => (float) $this->score,
            'accuracy_percentage' => (float) $this->accuracy_percentage,
            'duration_seconds' => (int) $this->duration_seconds,
            'grade_category' => $this->grade_category,
            'answers' => ExamAnswerResource::collection($this->whenLoaded('examAnswers')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}