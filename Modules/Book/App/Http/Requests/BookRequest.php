<?php

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
        $bookUuid = $this->get('book') ?? null; // For update
        
        return [
            'library_book_id' => 'required|string|max:50|unique:books,library_book_id,'.$bookUuid.',uuid',
            'isbn' => 'required|string|unique:books,isbn,'.$bookUuid.',uuid',
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'cover_image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
            'description' => 'required|string',
            'publication_year' => 'required|integer|min:1900|max:' . date('Y'),
            'genres' => 'sometimes|array',
            'genres.*' => 'string|max:50',
            'verified' => 'sometimes|boolean',
        ];
    }
}