<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'package_id' => ['nullable', 'integer', 'exists:practice_packages,id'],
            'duration_seconds' => ['required', 'integer', 'min:0'],
            'answers' => ['required', 'array', 'min:1'],
            'answers.*.question_id' => ['required', 'integer', 'exists:questions,id'],
            'answers.*.selected_option_id' => ['nullable', 'integer', 'exists:question_options,id'],
        ];
    }
}