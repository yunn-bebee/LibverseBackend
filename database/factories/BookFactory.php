<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use Illuminate\Support\Str;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
         return [
            'library_book_id' => 'BCL-' . Str::random(10),
            'isbn' => $this->faker->isbn13,
            'title' => $this->faker->sentence(3),
            'author' => $this->faker->name,
            'cover_image' => 'covers/' . $this->faker->image('public/storage/covers', 300, 450, 'book', false),
            'description' => $this->faker->paragraphs(3, true),
            'added_by' => User::factory(),
            'verified' => $this->faker->boolean(90),
        ];
    }
}
