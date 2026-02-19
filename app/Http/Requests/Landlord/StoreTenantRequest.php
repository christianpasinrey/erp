<?php

declare(strict_types=1);

namespace App\Http\Requests\Landlord;

use Illuminate\Foundation\Http\FormRequest;

class StoreTenantRequest extends FormRequest
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
            'domain' => ['required', 'string', 'max:255', 'unique:domains,domain'],
            'plan' => ['required', 'in:starter,business,enterprise'],
            'max_users' => ['nullable', 'integer', 'min:1'],
            'max_companies' => ['required', 'integer', 'min:1'],
            'trial_ends_at' => ['nullable', 'date'],
        ];
    }
}
