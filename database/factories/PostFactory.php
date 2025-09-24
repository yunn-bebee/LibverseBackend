<?php

namespace Database\Factories;
use App\Models\Book;
use App\Models\Thread;
use App\Models\User;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    public function definition()
    {
        return [
            'uuid' => $this->faker->uuid(),
            'thread_id' => Thread::factory(),
            'user_id' => User::factory(),
            'content' => $this->faker->paragraphs(2, true),
            'is_flagged' => false,
            'parent_post_id' => null,
            'book_id' => Book::factory(),
        ];
    }

    public function reply()
    {
        return $this->state([
            'parent_post_id' => Post::factory(),
        ]);
    }

    public function flagged()
    {
        return $this->state([
            'is_flagged' => true,
        ]);
    }
}
