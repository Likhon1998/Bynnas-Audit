<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShakhaRequest extends FormRequest
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
                Rule::unique('shakhas')->where(fn ($query) => $query->where('area_id', $this->input('area_id'))),
            ],
            'area_id' => ['required', 'exists:areas,id'],
            'code' => ['nullable', 'string', 'max:50', 'unique:shakhas,code'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }
}
