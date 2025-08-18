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
        'clock_in_time',
        'clock_out_time',
        'work_days',
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
            'clock_in_time' => 'datetime:H:i',
            'clock_out_time' => 'datetime:H:i',
            'work_days' => 'array',
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
     * Get formatted clock in time.
     */
    public function getFormattedClockInTime(): string
    {
        return $this->clock_in_time->format('H:i');
    }

    /**
     * Get formatted clock out time.
     */
    public function getFormattedClockOutTime(): string
    {
        return $this->clock_out_time->format('H:i');
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

        $selectedDays = collect($this->work_days)
            ->map(fn ($day) => $dayNames[$day] ?? $day)
            ->implode(', ');

        return $selectedDays ?: 'Tidak ada hari kerja';
    }

    /**
     * Check if a specific day is a work day.
     */
    public function isWorkDay(int $dayNumber): bool
    {
        return in_array($dayNumber, $this->work_days ?? []);
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
