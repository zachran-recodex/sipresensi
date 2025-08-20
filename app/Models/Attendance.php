<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    /** @use HasFactory<\Database\Factories\AttendanceFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'location_id',
        'daily_schedules',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'daily_schedules' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the attendance record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the location for the attendance record.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Check if the attendance record is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get work days as readable text.
     */
    public function getWorkDaysText(): string
    {
        $dayNames = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        if (! $this->daily_schedules) {
            return 'Tidak ada jadwal kerja';
        }

        $workDays = collect($this->daily_schedules)
            ->keys()
            ->map(fn ($day) => $dayNames[(int) $day] ?? $day)
            ->implode(', ');

        return $workDays ?: 'Tidak ada jadwal kerja';
    }

    /**
     * Check if a specific day is a work day.
     */
    public function isWorkDay(int $dayNumber): bool
    {
        return isset($this->daily_schedules[$dayNumber]);
    }

    /**
     * Get schedule for a specific day.
     */
    public function getDaySchedule(int $dayNumber): ?array
    {
        return $this->daily_schedules[$dayNumber] ?? null;
    }

    /**
     * Get formatted schedule for display.
     */
    public function getFormattedSchedule(): string
    {
        if (! $this->daily_schedules) {
            return 'Tidak ada jadwal';
        }

        $dayNames = [
            1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam',
            5 => 'Jum', 6 => 'Sab', 7 => 'Min',
        ];

        // Group days by same schedule time
        $groupedSchedules = [];
        foreach ($this->daily_schedules as $day => $schedule) {
            $timeKey = $schedule['clock_in'] . '-' . $schedule['clock_out'];
            $groupedSchedules[$timeKey][] = (int) $day;
        }

        $schedules = [];
        foreach ($groupedSchedules as $timeKey => $days) {
            [$clockIn, $clockOut] = explode('-', $timeKey);
            
            // Sort days for consistent display
            sort($days);
            
            if (count($days) == 1) {
                $dayName = $dayNames[$days[0]];
                $schedules[] = "{$dayName}: {$clockIn}-{$clockOut}";
            } else {
                // Group consecutive days
                $dayGroups = $this->groupConsecutiveDays($days, $dayNames);
                $schedules[] = implode(', ', $dayGroups) . ": {$clockIn}-{$clockOut}";
            }
        }

        return implode(' | ', $schedules);
    }

    /**
     * Group consecutive days for compact display.
     */
    private function groupConsecutiveDays(array $days, array $dayNames): array
    {
        $groups = [];
        $current = [$days[0]];
        
        for ($i = 1; $i < count($days); $i++) {
            if ($days[$i] == $days[$i-1] + 1) {
                $current[] = $days[$i];
            } else {
                $groups[] = $this->formatDayGroup($current, $dayNames);
                $current = [$days[$i]];
            }
        }
        
        $groups[] = $this->formatDayGroup($current, $dayNames);
        
        return $groups;
    }

    /**
     * Format a group of days (e.g., "Sen-Jum" or "Sen, Rab").
     */
    private function formatDayGroup(array $days, array $dayNames): string
    {
        if (count($days) == 1) {
            return $dayNames[$days[0]];
        } elseif (count($days) >= 3 && $days == range($days[0], end($days))) {
            // Consecutive days: Sen-Jum
            return $dayNames[$days[0]] . '-' . $dayNames[end($days)];
        } else {
            // Non-consecutive: Sen, Rab, Sab
            return implode(', ', array_map(fn($day) => $dayNames[$day], $days));
        }
    }

    /**
     * Get clock in time for a specific day.
     */
    public function getClockInTime(int $dayNumber): ?string
    {
        $schedule = $this->getDaySchedule($dayNumber);

        return $schedule ? $schedule['clock_in'] : null;
    }

    /**
     * Get clock out time for a specific day.
     */
    public function getClockOutTime(int $dayNumber): ?string
    {
        $schedule = $this->getDaySchedule($dayNumber);

        return $schedule ? $schedule['clock_out'] : null;
    }

    /**
     * Get default clock in time (for backward compatibility).
     */
    public function getFormattedClockInTime(): string
    {
        if (! $this->daily_schedules) {
            return '-';
        }

        // Get the first day's clock in time
        $firstDay = array_key_first($this->daily_schedules);

        return $this->daily_schedules[$firstDay]['clock_in'] ?? '-';
    }

    /**
     * Get default clock out time (for backward compatibility).
     */
    public function getFormattedClockOutTime(): string
    {
        if (! $this->daily_schedules) {
            return '-';
        }

        // Get the first day's clock out time
        $firstDay = array_key_first($this->daily_schedules);

        return $this->daily_schedules[$firstDay]['clock_out'] ?? '-';
    }

    /**
     * Scope a query to only include active attendance records.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive attendance records.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }
}
