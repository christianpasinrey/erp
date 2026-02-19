<?php

declare(strict_types=1);

namespace App\Http\Requests\Landlord;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'plan' => ['required', 'in:starter,business,enterprise'],
            'max_users' => ['nullable', 'integer', 'min:1'],
            'max_companies' => ['required', 'integer', 'min:1'],
            'is_active' => ['required', 'boolean'],
            'trial_ends_at' => ['nullable', 'date'],
        ];
    }
}
