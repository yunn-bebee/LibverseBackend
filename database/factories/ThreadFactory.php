<?php

namespace Database\Factories;
use App\Models\Book;
use App\Models\Forum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ThreadFactory extends Factory
{
    public function definition()
    {
        $postTypes = ['discussion', 'announcement', 'question'];
        
        return [
            'forum_id' => Forum::factory(),
            'user_id' => User::factory(),
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraphs(3, true),
            'post_type' => $this->faker->randomElement($postTypes),
            'is_pinned' => false,
            'is_locked' => false,
            'book_id' => Book::factory(),
        ];
    }

    public function pinned()
    {
        return $this->state([
            'is_pinned' => true,
        ]);
    }

    public function locked()
    {
        return $this->state([
            'is_locked' => true,
        ]);
    }
}
