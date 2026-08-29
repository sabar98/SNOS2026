<?php

namespace App\Http\Requests\Participant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'participant_type' => ['required', Rule::exists('participant_categories', 'key')->where('is_active', true)],
            'attendance_method' => ['required', 'in:luring,daring'],
            'article_scope' => ['nullable', 'string', 'max:255'],
            'institution' => ['required', 'string', 'max:255'],
            'special_needs' => ['nullable', 'string'],
            'join_gala_dinner' => ['boolean'],
            'bank_account_id' => ['required', 'exists:bank_accounts,id'],
            'terms_accepted' => ['accepted'],
        ];
    }
}
