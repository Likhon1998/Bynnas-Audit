<?php

namespace App\Http\Requests;

use App\Support\Divisions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_pksf' => $this->boolean('is_pksf'),
            'is_maternity' => $this->boolean('is_maternity'),
            'has_project_audit' => $this->boolean('has_project_audit'),
            'has_project_monitoring' => $this->boolean('has_project_monitoring'),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:projects,name'],
            'donor' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
            'is_pksf' => ['boolean'],
            'is_maternity' => ['boolean'],
            'has_project_audit' => ['boolean'],
            'has_project_monitoring' => ['boolean'],
            'locations' => ['nullable', 'array'],
            'locations.*.name' => ['required_with:locations', 'string', 'max:255'],
            'locations.*.division' => ['required_with:locations.*.name', 'string', Rule::in(Divisions::OPTIONS)],
            'locations.*.status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
        ];
    }

    public function messages(): array
    {
        return [
            'locations.*.name.required_with' => 'Each location needs a name.',
            'locations.*.division.required_with' => 'Select the division for each location.',
        ];
    }
}
