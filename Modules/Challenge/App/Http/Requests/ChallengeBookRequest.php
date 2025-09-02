<?php

namespace Modules\Challenge\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChallengeBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'book_id' => 'required|exists:books,id',
            'status' => 'required|in:planned,reading,completed',
        ];
    }
}
