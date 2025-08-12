<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use Illuminate\Support\Str;

class BookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'library_book_id' => 'BCL-' . Str::random(10),
            'isbn' => $this->faker->isbn13,
            'title' => $this->faker->sentence(3),

            'author' => $this->faker->name,
             'cover_image' => 'covers/' . $this->faker->image('public/storage/covers', 300, 450, 'book', false),
            'description' => $this->faker->paragraphs(3, true),
            'genres' => json_encode($this->faker->randomElements([
                'Fiction', 'Non-Fiction', 'Science Fiction',
                'Fantasy', 'Mystery', 'Biography', 'History'
            ], $this->faker->numberBetween(1, 3))),
            'added_by' => User::factory(),
        ];
    }
}
