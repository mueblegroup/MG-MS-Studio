<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        $user = $this->user();
        $isStudent = $user?->role === 'student';
        $isClientOwner = $user?->role === 'admin' && ! $user?->studio_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                Rule::unique(User::class)->ignore($user?->id),
            ],
            'phone_number' => ['required', 'string', 'max:40', 'regex:/^[0-9+()\-\s]{7,40}$/'],
            'organisation_name' => [$isClientOwner ? 'required' : 'nullable', 'string', 'max:255'],
            'job_title' => [$isClientOwner ? 'required' : 'nullable', 'string', 'max:255'],
            'country' => [$isClientOwner ? 'required' : 'nullable', 'string', 'max:100'],
            'date_of_birth' => [($isStudent || $isClientOwner) ? 'required' : 'nullable', 'date', 'before:today', 'after:1900-01-01'],
            'gender' => [($isClientOwner ? 'required' : 'nullable'), Rule::in(['female', 'male', 'non_binary', 'prefer_not_to_say', 'other'])],
            'address' => [($isStudent || $isClientOwner) ? 'required' : 'nullable', 'string', 'max:2000'],
            'city' => [$isClientOwner ? 'required' : 'nullable', 'string', 'max:120'],
            'state' => [$isClientOwner ? 'required' : 'nullable', 'string', 'max:120'],
            'postal_code' => [$isClientOwner ? 'required' : 'nullable', 'string', 'max:30'],
            'emergency_contact_name' => [$isStudent ? 'required' : 'nullable', 'string', 'max:255'],
            'emergency_contact_phone' => [$isStudent ? 'required' : 'nullable', 'string', 'max:40'],
        ];
    }
}
