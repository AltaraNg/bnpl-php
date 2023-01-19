<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property string full_name
 * @property string email
 * @property string phone_number
 * @property string marital_status
 * @property string address
 * @property string gender
 * @property Carbon date_of_birth
 */
class VendorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        if (request()->method() == 'POST') {
            return [
                'full_name' => ['required', 'string', 'max:200'],
                'email' => ['nullable', 'email', 'max:200', 'unique:users,email'],
                'phone_number' => ['required', 'string', 'min:11', 'max:200', 'unique:users,phone_number'],
                'address' => ['required', 'string', 'max:200'],
                'gender' => ['required', 'string', Rule::in(['male', 'female'])],
            ];
        }

        if (request()->method() == 'PATCH') {
            return [
                'full_name' => ['sometimes', 'string', 'max:200'],
                'email' => ['sometimes', 'email', 'max:200',  Rule::unique('users', 'email')->ignore($this->vendor)],
                'phone_number' => ['sometimes', 'string', 'min:11', 'max:200', Rule::unique('users', 'phone_number')->ignore($this->vendor)],
                'address' => ['sometimes', 'string', 'max:200'],
                'gender' => ['sometimes', 'string', Rule::in(['male', 'female'])],
            ];
        }
        return [];
    }
}
