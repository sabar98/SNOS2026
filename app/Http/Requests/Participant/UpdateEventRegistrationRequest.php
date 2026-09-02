<?php

namespace App\Http\Requests\Participant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEventRegistrationRequest extends FormRequest
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
            // Any existing category (active or not) is accepted here, since a
            // registration keeps its previously chosen category even if that
            // category is later deactivated from new selections.
            'participant_type' => ['required', Rule::exists('participant_categories', 'key')],
            'attendance_method' => ['required', 'in:luring,daring'],
            'article_scope' => ['nullable', 'string', 'max:255'],
            'institution' => ['required', 'string', 'max:255'],
            'special_needs' => ['nullable', 'string'],
            'join_gala_dinner' => ['boolean'],
            'join_wisata_sabang' => ['boolean'],
            'join_wisata_lokal' => ['boolean'],
        ];
    }
}
