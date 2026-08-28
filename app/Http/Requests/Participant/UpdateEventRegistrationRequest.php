<?php

namespace App\Http\Requests\Participant;

use Illuminate\Foundation\Http\FormRequest;

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
            'participant_type' => ['required', 'in:presenter_luring,presenter_daring,peserta_umum,peserta_mahasiswa'],
            'attendance_method' => ['required', 'in:luring,daring'],
            'article_scope' => ['nullable', 'string', 'max:255'],
            'institution' => ['required', 'string', 'max:255'],
            'special_needs' => ['nullable', 'string'],
            'join_gala_dinner' => ['boolean'],
        ];
    }
}
