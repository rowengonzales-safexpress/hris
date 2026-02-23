<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\Core\User;
use App\Models\Core\CoreApp;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Notification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['info', 'success', 'warning', 'error'];

        return [
            'user_id' => User::factory(),
            'app_id' => CoreApp::factory(),
            'user_to' => null,
            'title' => fake()->sentence(4),
            'message' => fake()->paragraph(),
            'type' => fake()->randomElement($types),
            'is_read' => fake()->boolean(30), // 30% chance of being read
            'action_url' => fake()->optional()->url(),
            'data' => fake()->optional()->randomElement([
                ['key' => 'value'],
                ['action' => 'click', 'target' => 'button'],
                null
            ]),
        ];
    }

    /**
     * Indicate that the notification is unread.
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => false,
        ]);
    }

    /**
     * Indicate that the notification is read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => true,
        ]);
    }

    /**
     * Set the notification type.
     */
    public function type(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
        ]);
    }
}
