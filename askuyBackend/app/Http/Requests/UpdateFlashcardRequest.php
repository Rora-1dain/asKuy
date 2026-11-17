<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFlashcardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject_id' => ['sometimes', 'required', 'integer', 'exists:subjects,id'],
            'front_content' => ['sometimes', 'required', 'string'],
            'back_content' => ['sometimes', 'required', 'string'],
        ];
    }
}