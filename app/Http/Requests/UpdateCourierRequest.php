<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $courier = $this->route('courier');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'phone' => ['sometimes', 'required', 'string', 'max:30', Rule::unique('couriers', 'phone')->ignore($courier)],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('couriers', 'email')->ignore($courier)],
            'vehicle_type' => ['sometimes', 'required', 'string', 'max:50'],
            'license_plate' => ['sometimes', 'nullable', 'string', 'max:20', Rule::unique('couriers', 'license_plate')->ignore($courier)],
            'level' => ['sometimes', 'required', 'integer', 'between:1,5'],
            'is_active' => ['sometimes', 'boolean'],
            'registered_at' => ['sometimes', 'date'],
        ];
    }
}
