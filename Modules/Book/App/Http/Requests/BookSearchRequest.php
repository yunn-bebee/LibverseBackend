<?php
// Modules/Book/App/Http/Requests/BookSearchRequest.php
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
            'search' => [ 'string', 'min:3', 'max:255'],
            'author' => ['nullable', 'string', 'max:255'],
            'min_year' => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
