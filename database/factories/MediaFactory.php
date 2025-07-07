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
   // database/factories/MediaFactory.php
public function definition()
{
    $fileTypes = ['image', 'video', 'document'];
    $type = $this->faker->randomElement($fileTypes);
    
    // Get random sample file
    $samples = [
        'image' => ['sample1.jpg', 'sample2.jpg', 'sample3.jpg'],
        'video' => ['sample1.mp4', 'sample2.mp4', 'sample3.mp4'],
        'document' => ['sample1.pdf', 'sample2.pdf', 'sample3.pdf'],
    ];
    
    $fileName = $samples[$type][array_rand($samples[$type])];
    
    $thumbnail = null;
    if ($type === 'video') {
        $thumbnail = 'thumbnails/thumbnail_' . $fileName . '.jpg';
    }
    
    return [
        'post_id' => Post::factory(),
        'user_id' => User::factory(),
        'file_url' => 'media_samples/' . $fileName,
        'file_type' => $type,
        'thumbnail_url' => $thumbnail,
        'caption' => $this->faker->sentence,
    ];
}
}
