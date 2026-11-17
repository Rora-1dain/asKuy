<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject_id' => ['sometimes', 'required', 'integer', 'exists:subjects,id'],
            'topic_name' => ['sometimes', 'required', 'string', 'max:255'],
            'difficulty' => ['sometimes', 'required', 'in:mudah,sedang,hots'],
            'question_text' => ['sometimes', 'required', 'string'],
            'explanation' => ['nullable', 'string'],
            'options' => ['sometimes', 'array', 'min:2'],
            'options.*.option_label' => ['required_with:options', 'string', 'max:5'],
            'options.*.option_text' => ['required_with:options', 'string'],
            'options.*.is_correct' => ['required_with:options', 'boolean'],
        ];
    }
}