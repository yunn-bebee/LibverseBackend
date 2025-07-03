<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Forum;
use App\Models\User;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $eventTypes = ['Workshop', 'AuthorTalk', 'BookClub', 'LanguageSession'];
        $locationTypes = ['physical', 'virtual', 'hybrid'];
        
        return [
            'title' => $this->faker->words(4, true) . ' Event',
            'slug' => $this->faker->slug,
            'description' => $this->faker->paragraphs(3, true),
            'event_type' => $this->faker->randomElement($eventTypes),
            'start_time' => $this->faker->dateTimeBetween('+1 week', '+1 month'),
            'end_time' => $this->faker->dateTimeBetween('+1 month', '+3 months'),
            'location_type' => $this->faker->randomElement($locationTypes),
            'physical_address' => $this->faker->address,
            'zoom_link' => $this->faker->url,
            'max_attendees' => $this->faker->numberBetween(10, 100),
            'cover_image' => 'events/' . $this->faker->image('public/storage/events', 800, 600, 'event', false),
            'created_by' => User::factory(),
            'forum_id' => Forum::factory(),
        ];
    }
}
