<?php

namespace Modules\Post\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class MediaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:jpg,png,mp4,pdf|max:20480', // 20MB max
            'caption' => 'nullable|string|max:255',
            'thumbnail_url' => 'nullable|url',
        ];
    }

    public function authorize(): bool
    {
        return Auth::check();
    }
}
