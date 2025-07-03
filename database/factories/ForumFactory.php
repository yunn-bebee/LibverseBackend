<?php
namespace Database\Factories;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ForumFactory extends Factory
{
    public function definition()
    {
        $categories = ['Students', 'Professionals', 'EFL', 'BookClubs', 'Events'];
        
        return [
            'name' => $this->faker->words(3, true) . ' Forum',
            'slug' => $this->faker->slug,
            'description' => $this->faker->paragraph,
            'category' => $this->faker->randomElement($categories),
            'is_public' => true,
            'created_by' => User::factory(),
            'book_id' => Book::factory(),
        ];
    }
}