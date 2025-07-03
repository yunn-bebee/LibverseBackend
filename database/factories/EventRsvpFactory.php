<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Event;
use App\Models\User;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventRsvp>
 */
class EventRsvpFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
   public function definition()
    {
        $attendanceTypes = ['In-person', 'Virtual'];
        $statuses = ['going', 'interested', 'not_going'];
        
        return [
            'user_id' => User::factory(),
            'event_id' => Event::factory(),
            'attendance_type' => $this->faker->randomElement($attendanceTypes),
            'status' => $this->faker->randomElement($statuses),
        ];
    }
}
