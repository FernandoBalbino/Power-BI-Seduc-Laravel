<?php

namespace Database\Factories;

use App\Enums\DashboardStatus;
use App\Models\Dashboard;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dashboard>
 */
class DashboardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sector = Sector::factory()->create();
        $user = User::factory()->create(['sector_id' => $sector->id]);

        return [
            'sector_id' => $sector->id,
            'user_id' => $user->id,
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'status' => DashboardStatus::Draft,
        ];
    }
}
