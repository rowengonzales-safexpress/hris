<?php

namespace Database\Factories\Core;

use App\Models\Core\CoreApp;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Core\CoreApp>
 */
class CoreAppFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CoreApp::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $codes = ['admin', 'tms', 'hris', 'frms', 'wms'];
        $code = fake()->randomElement($codes);
        
        return [
            'code' => $code,
            'name' => strtoupper($code),
            'description' => fake()->sentence(),
            'status' => 'A',
            'status_message' => 'Welcome',
            'route' => $code,
            'created_by' => 1,
        ];
    }
}