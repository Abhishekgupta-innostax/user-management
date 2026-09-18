<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
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
        $guard = $this->is('api/*') ? 'sanctum' : 'web';

        return [
            'current_password' => ['required', "current_password:{$guard}"],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }
}
