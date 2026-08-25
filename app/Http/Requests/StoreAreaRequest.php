<?php

namespace App\Http\Requests;

use App\Support\Divisions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('areas')->where(fn ($query) => $query->where('division', $this->input('division'))),
            ],
            'division' => ['required', 'string', Rule::in(Divisions::OPTIONS)],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }
}
