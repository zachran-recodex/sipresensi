<?php

namespace App\Livewire\Administrator;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;

class DashboardStats extends Component
{
    public function render()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        $stats = [
            'total_karyawan' => User::role('karyawan')->count(),
            'total_admin' => User::role('admin')->count(),
            'hadir_hari_ini' => AttendanceRecord::whereDate('recorded_at', $today)
                ->where('type', 'check_in')
                ->distinct('user_id')
                ->count(),
            'total_absensi_bulan_ini' => AttendanceRecord::where('recorded_at', '>=', $thisMonth)->count(),
        ];

        // Recent attendance records
        $recentAttendance = AttendanceRecord::with(['user', 'location'])
            ->orderBy('recorded_at', 'desc')
            ->limit(10)
            ->get();

        // Late check-ins today
        $lateCheckIns = AttendanceRecord::with(['user', 'user.attendance'])
            ->whereDate('recorded_at', $today)
            ->where('type', 'check_in')
            ->get()
            ->filter(function ($record) {
                $userAttendance = $record->user->attendance;
                if (! $userAttendance || ! $userAttendance->daily_schedules) {
                    return false;
                }

                $dayOfWeek = $record->recorded_at->dayOfWeek;
                $dayNumber = $dayOfWeek === 0 ? 7 : $dayOfWeek; // Convert Sunday from 0 to 7

                $daySchedule = $userAttendance->getDaySchedule($dayNumber);
                if (! $daySchedule) {
                    return false;
                }

                $checkInTime = $record->recorded_at->format('H:i');
                $expectedTime = $daySchedule['clock_in'];

                return $checkInTime > $expectedTime;
            });

        // Users who haven't checked in today
        $notCheckedIn = User::role('karyawan')
            ->whereDoesntHave('attendanceRecords', function ($query) use ($today) {
                $query->whereDate('recorded_at', $today)
                    ->where('type', 'check_in');
            })
            ->with('attendance')
            ->get();

        return view('livewire.administrator.dashboard-stats', [
            'stats' => $stats,
            'recentAttendance' => $recentAttendance,
            'lateCheckIns' => $lateCheckIns,
            'notCheckedIn' => $notCheckedIn,
        ]);
    }
}
