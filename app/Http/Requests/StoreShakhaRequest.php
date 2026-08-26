<?php

namespace App\Http\Requests;

use App\Models\Shakha;
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
        /** @var Shakha|null $shakha */
        $shakha = $this->route('shakha');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('shakhas')
                    ->where(fn ($query) => $query->where('area_id', $this->input('area_id')))
                    ->ignore($shakha?->id),
            ],
            'area_id' => ['required', 'exists:areas,id'],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('shakhas', 'code')->ignore($shakha?->id),
            ],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
            'opening_date' => ['required', 'date'],
            'focal_person_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
