<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AiEditPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        $post = $this->route('post');

        return $this->user()?->can('update', $post) ?? false;
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'instruction' => ['required', 'string', 'min:3', 'max:'.config('content.max_prompt_length')],
        ];
    }
}
