<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $subjectId = $this->route('subject') instanceof \App\Models\Subject
            ? $this->route('subject')->id
            : $this->route('subject');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('subjects', 'code')->ignore($subjectId)],
            'semester' => ['sometimes', 'required', 'integer', 'min:1', 'max:14'],
        ];
    }
}