<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendSuperAdminChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isSuperAdmin();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'thread_uuid' => ['required', 'uuid'],
            'message' => ['required', 'string', 'min:2', 'max:1500'],
        ];
    }
}
