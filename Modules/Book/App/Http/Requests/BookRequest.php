<?php
namespace Modules\Book\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $bookId = $this->route('book');

        return [
            'library_book_id' => 'nullable|string|max:50|unique:books,library_book_id,'.$bookId,
            'isbn' => 'nullable|string|max:20|unique:books,isbn,'.$bookId,
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'author' => 'required|string|max:100',
            'co_authors' => 'nullable|string|max:255',
            'publisher' => 'nullable|string|max:100',
            'publication_year' => 'nullable|integer|min:1900|max:'.date('Y'),
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'description' => 'nullable|string',
            'genres' => 'nullable|array',
        ];
    }
}
