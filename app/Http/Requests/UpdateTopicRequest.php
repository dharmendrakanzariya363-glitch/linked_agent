<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTopicRequest extends FormRequest
{
    public function authorize(): bool
    {
        $topic = $this->route('topic');

        return $this->user()?->can('update', $topic) ?? false;
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:80'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
