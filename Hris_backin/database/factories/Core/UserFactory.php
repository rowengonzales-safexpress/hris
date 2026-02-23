<?php

namespace Database\Factories\Core;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Core\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $lastname = fake()->lastName();
        $firstname = fake()->firstName();
        $userType = ['sysadmin','admin','user'];
        return [
            'name' => $firstname. ' '.$lastname,
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'last_name' => $lastname,
            'first_name' => $firstname,
            'password' => $firstname,
            'remember_token' => Str::random(10),
            'user_type' => $userType[array_rand($userType)]
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}