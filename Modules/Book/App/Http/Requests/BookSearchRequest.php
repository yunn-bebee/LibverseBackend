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
          
        ];
    }
}
