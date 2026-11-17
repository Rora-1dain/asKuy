<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GeneratePracticePackageRequest extends FormRequest
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
            'total_questions' => ['required', 'integer', 'in:10,20,30'],
            'title' => ['nullable', 'string', 'max:255'],
        ];
    }
}