<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Attendance::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate random work days (Monday to Friday, sometimes including Saturday)
        $workDays = collect([1, 2, 3, 4, 5]); // Monday to Friday
        if (fake()->boolean(20)) { // 20% chance to include Saturday
            $workDays->push(6);
        }

        return [
            'user_id' => User::factory(),
            'location_id' => Location::factory(),
            'clock_in_time' => fake()->time('H:i', '08:00'), // Between 07:00-09:00
            'clock_out_time' => fake()->time('H:i', '17:00'), // Between 16:00-18:00
            'work_days' => $workDays->toArray(),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the attendance record is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Set specific work days (Monday to Friday).
     */
    public function weekdays(): static
    {
        return $this->state(fn (array $attributes) => [
            'work_days' => [1, 2, 3, 4, 5], // Monday to Friday
        ]);
    }

    /**
     * Set work days including Saturday.
     */
    public function includingSaturday(): static
    {
        return $this->state(fn (array $attributes) => [
            'work_days' => [1, 2, 3, 4, 5, 6], // Monday to Saturday
        ]);
    }

    /**
     * Set early shift (07:00-15:00).
     */
    public function earlyShift(): static
    {
        return $this->state(fn (array $attributes) => [
            'clock_in_time' => '07:00',
            'clock_out_time' => '15:00',
        ]);
    }

    /**
     * Set late shift (14:00-22:00).
     */
    public function lateShift(): static
    {
        return $this->state(fn (array $attributes) => [
            'clock_in_time' => '14:00',
            'clock_out_time' => '22:00',
        ]);
    }
}
