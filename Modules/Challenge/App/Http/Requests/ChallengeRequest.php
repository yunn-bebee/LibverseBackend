<?php

namespace Modules\Challenge\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChallengeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'target_count' => 'required|integer|min:1',
            'badge_id' => 'required|exists:badges,id',
            'is_active' => 'boolean',
        ];



        return $rules;
    }
}
