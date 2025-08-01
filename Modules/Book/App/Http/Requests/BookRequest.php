<?php
// Modules/Book/App/Http/Requests/BookRequest.php
namespace Modules\Book\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $bookUuid = $this->route('book') ?? null; // For update

        return [
            'library_book_id' => ['nullable', 'string', 'max:50', Rule::unique('books', 'library_book_id')->ignore($bookUuid, 'uuid')],
            'isbn' => ['nullable', 'string', 'max:20', Rule::unique('books', 'isbn')->ignore($bookUuid, 'uuid')],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'], // 2MB max
            'description' => ['nullable', 'string'],
            'publication_year' => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
            'genres' => ['nullable', 'array'],
            'genres.*' => ['string', 'max:50'],
            'verified' => ['sometimes', 'boolean'],
        ];
    }
}
