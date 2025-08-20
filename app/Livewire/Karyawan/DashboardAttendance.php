<?php

namespace App\Livewire\Karyawan;

use App\Models\AttendanceRecord;
use Carbon\Carbon;
use Livewire\Component;

class DashboardAttendance extends Component
{
    public function render()
    {
        $user = auth()->user();
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        // Today's attendance status
        $todaysAttendance = [
            'check_in' => AttendanceRecord::where('user_id', $user->id)
                ->whereDate('created_at', $today)
                ->where('type', 'check_in')
                ->first(),
            'check_out' => AttendanceRecord::where('user_id', $user->id)
                ->whereDate('created_at', $today)
                ->where('type', 'check_out')
                ->first(),
        ];

        // Attendance statistics
        $stats = [
            'total_minggu_ini' => AttendanceRecord::where('user_id', $user->id)
                ->where('created_at', '>=', $thisWeek)
                ->where('type', 'check_in')
                ->count(),
            'total_bulan_ini' => AttendanceRecord::where('user_id', $user->id)
                ->where('created_at', '>=', $thisMonth)
                ->where('type', 'check_in')
                ->count(),
            'terlambat_bulan_ini' => 0, // Will calculate below
            'lembur_minggu_ini' => 0, // Will calculate below
        ];

        // Calculate late check-ins this month
        if ($user->attendance && $user->attendance->daily_schedules) {
            $lateCheckIns = AttendanceRecord::where('user_id', $user->id)
                ->where('created_at', '>=', $thisMonth)
                ->where('type', 'check_in')
                ->get()
                ->filter(function ($record) use ($user) {
                    if (!$record->check_in_time) return false;
                    
                    $recordDate = Carbon::parse($record->created_at);
                    $dayNumber = $recordDate->dayOfWeek === 0 ? 7 : $recordDate->dayOfWeek; // Convert Sunday from 0 to 7
                    
                    $expectedTime = $user->attendance->getClockInTime($dayNumber);
                    if (!$expectedTime) return false;
                    
                    $checkInTime = Carbon::parse($record->check_in_time);
                    $expectedCheckIn = Carbon::parse($expectedTime);

                    return $checkInTime->gt($expectedCheckIn);
                });
            $stats['terlambat_bulan_ini'] = $lateCheckIns->count();
        }

        // Calculate overtime this week
        if ($user->attendance && $user->attendance->daily_schedules) {
            $overtimeRecords = AttendanceRecord::where('user_id', $user->id)
                ->where('created_at', '>=', $thisWeek)
                ->where('type', 'check_out')
                ->get()
                ->filter(function ($record) use ($user) {
                    if (!$record->check_out_time) return false;
                    
                    $recordDate = Carbon::parse($record->created_at);
                    $dayNumber = $recordDate->dayOfWeek === 0 ? 7 : $recordDate->dayOfWeek; // Convert Sunday from 0 to 7
                    
                    $expectedTime = $user->attendance->getClockOutTime($dayNumber);
                    if (!$expectedTime) return false;
                    
                    $checkOutTime = Carbon::parse($record->check_out_time);
                    $expectedCheckOut = Carbon::parse($expectedTime);

                    return $checkOutTime->gt($expectedCheckOut);
                });
            $stats['lembur_minggu_ini'] = $overtimeRecords->count();
        }

        // Recent attendance records
        $recentAttendance = AttendanceRecord::where('user_id', $user->id)
            ->with(['location'])
            ->orderBy('created_at', 'desc')
            ->limit(7)
            ->get();

        // Check if late today
        $isLateToday = false;
        if ($todaysAttendance['check_in'] && $user->attendance && $user->attendance->daily_schedules) {
            $today = Carbon::today();
            $dayNumber = $today->dayOfWeek === 0 ? 7 : $today->dayOfWeek; // Convert Sunday from 0 to 7
            
            $expectedTime = $user->attendance->getClockInTime($dayNumber);
            if ($expectedTime && $todaysAttendance['check_in']->check_in_time) {
                $checkInTime = Carbon::parse($todaysAttendance['check_in']->check_in_time);
                $expectedCheckIn = Carbon::parse($expectedTime);
                $isLateToday = $checkInTime->gt($expectedCheckIn);
            }
        }

        // Determine next action
        $nextAction = null;
        if (! $todaysAttendance['check_in']) {
            $nextAction = [
                'type' => 'check_in',
                'label' => 'Absen Masuk',
                'icon' => 'arrow-right-on-rectangle',
                'color' => 'green',
            ];
        } elseif (! $todaysAttendance['check_out']) {
            $nextAction = [
                'type' => 'check_out',
                'label' => 'Absen Keluar',
                'icon' => 'arrow-left-on-rectangle',
                'color' => 'orange',
            ];
        }

        return view('livewire.karyawan.dashboard-attendance', [
            'todaysAttendance' => $todaysAttendance,
            'stats' => $stats,
            'recentAttendance' => $recentAttendance,
            'isLateToday' => $isLateToday,
            'nextAction' => $nextAction,
            'userAttendance' => $user->attendance,
        ]);
    }
}
