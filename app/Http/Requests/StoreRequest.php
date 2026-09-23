<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Store;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('whatsapp_number'))) {
            $this->merge([
                'whatsapp_number' => preg_replace('/[\s\-().]/', '', $this->input('whatsapp_number')),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'whatsapp_number' => ['required', 'string', 'regex:/^\+?[0-9]{8,15}$/'],
            'currency' => ['required', 'string', 'max:8'],
            'status' => ['sometimes', Rule::in([Store::STATUS_ACTIVE, Store::STATUS_INACTIVE])],
        ];
    }

    public function messages(): array
    {
        return [
            'whatsapp_number.regex' => 'The WhatsApp number must be 8 to 15 digits, optionally starting with +.',
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if (! $this->hasFile('logo')) {
            unset($data['logo']);
        }

        return data_get($data, $key, $default);
    }
}
