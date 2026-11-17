<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionOptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'question_id' => $this->question_id,
            'option_label' => $this->option_label,
            'option_text' => $this->option_text,
            'is_correct' => (bool) $this->is_correct,
        ];
    }
}