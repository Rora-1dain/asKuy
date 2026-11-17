<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'topic_name' => ['required', 'string', 'max:255'],
            'difficulty' => ['required', 'in:mudah,sedang,hots'],
            'question_text' => ['required', 'string'],
            'explanation' => ['nullable', 'string'],
            'options' => ['required', 'array', 'min:2'],
            'options.*.option_label' => ['required', 'string', 'max:5'],
            'options.*.option_text' => ['required', 'string'],
            'options.*.is_correct' => ['required', 'boolean'],
        ];
    }
}