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

        // Generate daily schedules for work days
        $dailySchedules = [];
        foreach ($workDays as $day) {
            $clockIn = fake()->time('H:i', '08:00'); // Between 07:00-09:00
            $clockOut = fake()->time('H:i', '17:00'); // Between 16:00-18:00

            // Ensure clock out is after clock in
            $clockInTime = \Carbon\Carbon::createFromFormat('H:i', $clockIn);
            $clockOutTime = \Carbon\Carbon::createFromFormat('H:i', $clockOut);

            if ($clockOutTime <= $clockInTime) {
                $clockOutTime = $clockInTime->copy()->addHours(8); // Add 8 hours
                $clockOut = $clockOutTime->format('H:i');
            }

            $dailySchedules[$day] = [
                'clock_in' => $clockIn,
                'clock_out' => $clockOut,
            ];
        }

        return [
            'user_id' => User::factory(),
            'location_id' => Location::factory(),
            'daily_schedules' => $dailySchedules,
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
            'daily_schedules' => [
                1 => ['clock_in' => '09:00', 'clock_out' => '17:00'], // Monday
                2 => ['clock_in' => '09:00', 'clock_out' => '17:00'], // Tuesday
                3 => ['clock_in' => '09:00', 'clock_out' => '17:00'], // Wednesday
                4 => ['clock_in' => '09:00', 'clock_out' => '17:00'], // Thursday
                5 => ['clock_in' => '09:00', 'clock_out' => '17:00'], // Friday
            ],
        ]);
    }

    /**
     * Set work days including Saturday.
     */
    public function includingSaturday(): static
    {
        return $this->state(fn (array $attributes) => [
            'daily_schedules' => [
                1 => ['clock_in' => '09:00', 'clock_out' => '17:00'], // Monday
                2 => ['clock_in' => '09:00', 'clock_out' => '17:00'], // Tuesday
                3 => ['clock_in' => '09:00', 'clock_out' => '17:00'], // Wednesday
                4 => ['clock_in' => '09:00', 'clock_out' => '17:00'], // Thursday
                5 => ['clock_in' => '09:00', 'clock_out' => '17:00'], // Friday
                6 => ['clock_in' => '09:00', 'clock_out' => '17:00'], // Saturday
            ],
        ]);
    }

    /**
     * Set early shift (07:00-15:00).
     */
    public function earlyShift(): static
    {
        return $this->state(function (array $attributes) {
            $dailySchedules = [];

            // Apply early shift to all work days
            foreach ([1, 2, 3, 4, 5] as $day) {
                $dailySchedules[$day] = [
                    'clock_in' => '07:00',
                    'clock_out' => '15:00',
                ];
            }

            return ['daily_schedules' => $dailySchedules];
        });
    }

    /**
     * Set late shift (14:00-22:00).
     */
    public function lateShift(): static
    {
        return $this->state(function (array $attributes) {
            $dailySchedules = [];

            // Apply late shift to all work days
            foreach ([1, 2, 3, 4, 5] as $day) {
                $dailySchedules[$day] = [
                    'clock_in' => '14:00',
                    'clock_out' => '22:00',
                ];
            }

            return ['daily_schedules' => $dailySchedules];
        });
    }
}
