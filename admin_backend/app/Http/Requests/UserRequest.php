<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // only allow updates if the user is logged in
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Backpack passes the entry's id on update, null on create.
        $userId = $this->route('id');

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => $userId ? 'nullable|string|min:8' : 'required|string|min:8',
            'role' => ['required', Rule::in(['admin', 'responder'])],
            'barangay' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'name' => 'name',
            'email' => 'email address',
            'password' => 'password',
            'role' => 'role',
            'barangay' => 'barangay',
            'phone' => 'phone number',
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'role.in' => 'Role must be either Admin or Responder.',
            'email.unique' => 'This email is already used by another staff account.',
            'password.min' => 'Password must be at least 8 characters.',
        ];
    }
}