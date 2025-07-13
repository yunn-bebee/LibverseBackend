<?php

namespace Modules\Book\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookSearchRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'search' => 'sometimes|string|max:255',
            'author' => 'sometimes|string|max:255',
            'min_year' => 'sometimes|integer|min:1900|max:' . date('Y'),
            'per_page' => 'sometimes|integer|min:1|max:100',
        ];
    }
}