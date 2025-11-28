<?php

namespace Modules\Post\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
class ReportPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'reason' => 'required|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'A report reason is required.',
            'reason.string' => 'The reason must be text.',
            'reason.max' => 'The reason may not be longer than 500 characters.',
        ];
    }
}
