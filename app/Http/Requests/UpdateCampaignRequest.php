<?php

namespace App\Http\Requests;

use App\Enums\ContentType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        $campaign = $this->route('campaign');

        return $this->user()?->can('update', $campaign) ?? false;
    }

    /**
     * @return array<string, list<string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'linkedin_account_id' => [
                'required',
                'integer',
                Rule::exists('linkedin_accounts', 'id')->where('user_id', $this->user()?->id),
            ],
            'timezone' => ['required', 'string', 'timezone:all'],
            'daily_post_time' => ['required', 'date_format:H:i'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'content_type' => ['required', Rule::enum(ContentType::class)],
            'topics' => ['required', 'array', 'min:1', 'max:20'],
            'topics.*' => ['required', 'string', 'max:80'],
        ];
    }
}
