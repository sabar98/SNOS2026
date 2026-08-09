<?php

namespace App\Http\Requests\Participant;

use App\Models\EventRegistration;
use Illuminate\Foundation\Http\FormRequest;

class StoreArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var EventRegistration $registration */
        $registration = $this->route('registration');

        return $this->user()->can('update', $registration)
            && ($registration->status === 'pembayaran_terverifikasi' || $registration->articles()->exists());
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'journal_id' => ['nullable', 'exists:journals,id'],
            'title' => ['required', 'string', 'max:255'],
            'abstract' => ['required', 'string'],
            'keywords' => ['required', 'string', 'max:255'],
            'field' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:doc,docx,pdf', 'max:10240'],
            'statement_letter' => ['required', 'file', 'mimes:doc,docx,pdf', 'max:10240'],
            'authors' => ['required', 'array', 'min:1'],
            'authors.*.name' => ['required', 'string', 'max:255'],
            'authors.*.email' => ['required', 'email', 'max:255'],
            'authors.*.affiliation' => ['nullable', 'string', 'max:255'],
            'authors.*.is_corresponding' => ['boolean'],
        ];
    }
}
