<?php

namespace App\Http\Requests;

use App\Support\Divisions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $projectId = $this->route('project')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('project_locations', 'name')->where(
                    fn ($query) => $query->where('project_id', $projectId)
                ),
            ],
            'division' => ['required', 'string', Rule::in(Divisions::OPTIONS)],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }

    public function messages(): array
    {
        return [
            'division.required' => 'Select the division first, then enter the location.',
        ];
    }
}
