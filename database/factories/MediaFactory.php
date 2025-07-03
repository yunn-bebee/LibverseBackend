<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Post;
use App\Models\User;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Media>
 */
class MediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $fileTypes = ['image', 'video', 'document'];
        $type = $this->faker->randomElement($fileTypes);
        
        $thumbnail = null;
        if ($type === 'video') {
            $thumbnail = 'thumbnails/' . $this->faker->image('public/storage/thumbnails', 320, 180, null, false);
        }
        
        return [
            'post_id' => Post::factory(),
            'user_id' => User::factory(),
            'file_url' => 'media/' . $this->faker->file(
                storage_path('app/public/media_samples'), 
                storage_path('app/public/media'),
                false
            ),
            'file_type' => $type,
            'thumbnail_url' => $thumbnail,
            'caption' => $this->faker->sentence,
        ];
    }
}
