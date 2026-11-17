<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamAnswerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'exam_session_id' => $this->exam_session_id,
            'question_id' => $this->question_id,
            'question' => new QuestionResource($this->whenLoaded('question')),
            'selected_option_id' => $this->selected_option_id,
            'selected_option' => new QuestionOptionResource($this->whenLoaded('selectedOption')),
            'is_correct' => (bool) $this->is_correct,
        ];
    }
}