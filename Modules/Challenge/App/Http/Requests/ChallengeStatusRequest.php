<?php

namespace Modules\Challenge\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChallengeStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:planned,reading,completed',
            'rating' => 'nullable|integer|between:1,5',
            'review' => 'nullable|string|max:1000',
        ];
    }
}
