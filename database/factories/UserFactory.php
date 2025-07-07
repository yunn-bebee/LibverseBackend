<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
     public function definition()
    {
        return [
            'member_id' => 'AYA-' . Str::random(6),
            'uuid' => Str::uuid(),
            'username' => $this->faker->unique()->userName,
            'email' => $this->faker->unique()->safeEmail,
            'password' => Hash::make('password'),
            'role' => $this->faker->randomElement(UserRole::cases())->value,
            'date_of_birth' => $this->faker->dateTimeBetween('-60 years', '-13 years')->format('Y-m-d'),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ];
    }

    public function admin()
    {
        return $this->state([
            'role' => UserRole::ADMIN->value,
            'member_id' => 'BCL-ADMIN-' . Str::random(4),
        ]);
    }

    public function moderator()
    {
        return $this->state([
            'role' => UserRole::MODERATOR->value,
            'member_id' => 'BCL-MOD-' . Str::random(4),
        ]);
    }

    public function member()
    {
        return $this->state([
            'role' => UserRole::MEMBER->value,
            'member_id' => 'BCL-MEM-' . Str::random(4),
        ]);
    }

    public function unverified()
    {
        return $this->state([
            'email_verified_at' => null,
        ]);
    }
}
