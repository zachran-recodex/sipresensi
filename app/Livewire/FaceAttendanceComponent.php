<?php

namespace App\Livewire;

use App\Models\AttendanceRecord;
use App\Models\User;
use App\Services\BiznetFaceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class FaceAttendanceComponent extends Component
{
    public $attendanceType = 'check_in';

    public $capturedImage = '';

    public $attendanceStatus = 'idle';

    public $errorMessage = '';

    public $successMessage = '';

    public $showCamera = false;

    public $identifiedUser = null;

    public $confidenceLevel = null;

    public $maskDetected = false;

    public $userLatitude = null;

    public $userLongitude = null;

    public $locationValidated = false;

    public $todaysAttendance = [];

    public $attendanceComplete = false;

    public $nextActionNeeded = null;

    public $isLateCheckIn = false;

    public $isEarlyCheckOut = false;

    protected $biznetFaceService;

    public function boot(): void
    {
        $this->biznetFaceService = app(BiznetFaceService::class);
    }

    public function mount($type = 'check_in'): void
    {
        $this->attendanceType = $type;

        // Debug authentication
        $user = auth()->user();
        $userId = $user ? $user->id : null;

        logger('FaceAttendance mount debug', [
            'auth_user' => $user,
            'auth_id' => auth()->id(),
            'userId' => $userId,
            'type' => $type,
        ]);

        if (! $userId) {
            session()->flash('error', 'Silahkan login terlebih dahulu untuk menggunakan fitur absensi.');
            $this->redirect(route('login'));

            return;
        }

        // Check if user has face enrollment
        $faceEnrollment = \App\Models\FaceEnrollment::where('user_id', $userId)
            ->where('is_active', true)
            ->first();

        logger('Face enrollment check', [
            'user_id' => $userId,
            'enrollment_found' => $faceEnrollment ? true : false,
            'enrollment_data' => $faceEnrollment,
        ]);

        if (! $faceEnrollment) {
            $this->attendanceStatus = 'not_enrolled';

            return;
        }

        // Check today's attendance records
        $this->checkTodaysAttendance($userId);
    }

    protected function checkTodaysAttendance($userId): void
    {
        $todaysRecords = AttendanceRecord::where('user_id', $userId)
            ->whereDate('recorded_at', today())
            ->orderBy('recorded_at')
            ->get();

        $this->todaysAttendance = [
            'check_in' => null,
            'check_out' => null,
            'total_records' => $todaysRecords->count(),
        ];

        foreach ($todaysRecords as $record) {
            if ($record->type === 'check_in' && ! $this->todaysAttendance['check_in']) {
                $this->todaysAttendance['check_in'] = $record;
            } elseif ($record->type === 'check_out' && ! $this->todaysAttendance['check_out']) {
                $this->todaysAttendance['check_out'] = $record;
            }
        }

        $this->determineAttendanceStatus();
        $this->checkAttendanceTimings();
    }

    protected function determineAttendanceStatus(): void
    {
        $hasCheckIn = $this->todaysAttendance['check_in'] !== null;
        $hasCheckOut = $this->todaysAttendance['check_out'] !== null;

        if ($hasCheckIn && $hasCheckOut) {
            // Complete attendance for today
            $this->attendanceComplete = true;
            $this->attendanceStatus = 'complete';
            $this->nextActionNeeded = null;
        } elseif ($hasCheckIn && ! $hasCheckOut) {
            // Only checked in, needs check out
            $this->attendanceComplete = false;
            $this->nextActionNeeded = 'check_out';

            if ($this->attendanceType === 'check_in') {
                $this->attendanceStatus = 'already_checked_in';
            }
        } elseif (! $hasCheckIn && $hasCheckOut) {
            // Unusual case: checked out without check in
            $this->attendanceComplete = false;
            $this->nextActionNeeded = 'check_in';

            if ($this->attendanceType === 'check_out') {
                $this->attendanceStatus = 'no_check_in';
            }
        } else {
            // No attendance yet today
            $this->attendanceComplete = false;
            $this->nextActionNeeded = 'check_in';
        }

        logger('Attendance status determined', [
            'user_id' => auth()->id(),
            'attendance_status' => $this->attendanceStatus,
            'attendance_complete' => $this->attendanceComplete,
            'next_action_needed' => $this->nextActionNeeded,
            'todays_attendance' => $this->todaysAttendance,
        ]);
    }

    protected function checkAttendanceTimings(): void
    {
        $user = auth()->user();
        $attendance = $user?->attendance;

        if (! $attendance) {
            return;
        }

        // Reset status
        $this->isLateCheckIn = false;
        $this->isEarlyCheckOut = false;

        // Check late check-in
        if ($this->todaysAttendance['check_in']) {
            $checkInTime = $this->todaysAttendance['check_in']->recorded_at;
            $expectedCheckInTime = $attendance->clock_in_time;

            // Compare time only (ignore date)
            $actualTime = $checkInTime->format('H:i:s');
            $expectedTime = $expectedCheckInTime->format('H:i:s');

            if ($actualTime > $expectedTime) {
                $this->isLateCheckIn = true;
            }
        }

        // Check early check-out
        if ($this->todaysAttendance['check_out']) {
            $checkOutTime = $this->todaysAttendance['check_out']->recorded_at;
            $expectedCheckOutTime = $attendance->clock_out_time;

            // Compare time only (ignore date)
            $actualTime = $checkOutTime->format('H:i:s');
            $expectedTime = $expectedCheckOutTime->format('H:i:s');

            if ($actualTime < $expectedTime) {
                $this->isEarlyCheckOut = true;
            }
        }

        logger('Attendance timing check', [
            'user_id' => auth()->id(),
            'is_late_check_in' => $this->isLateCheckIn,
            'is_early_check_out' => $this->isEarlyCheckOut,
            'check_in_actual' => $this->todaysAttendance['check_in']?->recorded_at?->format('H:i:s'),
            'check_in_expected' => $attendance->clock_in_time?->format('H:i:s'),
            'check_out_actual' => $this->todaysAttendance['check_out']?->recorded_at?->format('H:i:s'),
            'check_out_expected' => $attendance->clock_out_time?->format('H:i:s'),
        ]);
    }

    public function setUserLocation($latitude, $longitude): void
    {
        $this->userLatitude = $latitude;
        $this->userLongitude = $longitude;
        $this->locationValidated = false; // Reset validation status
    }

    public function startCamera(): void
    {
        $this->showCamera = true;
        $this->attendanceStatus = 'capturing';
        $this->errorMessage = '';
        $this->successMessage = '';
        $this->identifiedUser = null;
        $this->dispatch('attendance-camera-started');
    }

    public function captureImage($imageData): void
    {
        try {
            $this->capturedImage = $imageData;
            $this->attendanceStatus = 'captured';

            $this->dispatch('image-captured', imageData: $imageData);
        } catch (\Exception $e) {
            $this->errorMessage = 'Gagal menangkap gambar: '.$e->getMessage();
            $this->attendanceStatus = 'error';
        }
    }

    public function processAttendance(): void
    {
        try {
            $this->attendanceStatus = 'processing';
            $this->errorMessage = '';
            $this->successMessage = '';

            if (! $this->capturedImage) {
                throw new \Exception('Tidak ada gambar yang ditangkap');
            }

            if (! BiznetFaceService::validateBase64Image($this->capturedImage)) {
                throw new \Exception('Gambar tidak valid');
            }

            $response = $this->biznetFaceService->identifyFace($this->capturedImage);

            // Log response for debugging
            Log::info('Biznet Face API identify response', ['response' => $response]);

            // Check if response has the risetai structure
            $apiResponse = $response['risetai'] ?? $response;

            if (($apiResponse['status'] ?? $response['status'] ?? null) !== '200') {
                throw new \Exception($apiResponse['status_message'] ?? $response['status_message'] ?? 'Gagal mengidentifikasi wajah');
            }

            // The identify API returns data in 'return' array, not 'data'
            $returnData = $apiResponse['return'] ?? [];

            if (empty($returnData) || ! isset($returnData[0]['user_id'])) {
                throw new \Exception('Wajah tidak dikenali. Pastikan Anda sudah terdaftar.');
            }

            $responseData = $returnData[0]; // Get first (and should be only) result
            $biznetUserId = $responseData['user_id'];

            $faceEnrollment = \App\Models\FaceEnrollment::where('biznet_user_id', $biznetUserId)
                ->where('is_active', true)
                ->first();

            Log::info('Face enrollment lookup', [
                'biznet_user_id' => $biznetUserId,
                'enrollment_found' => $faceEnrollment ? true : false,
                'enrollment_data' => $faceEnrollment,
            ]);

            if (! $faceEnrollment) {
                throw new \Exception('Data enrollment tidak ditemukan');
            }

            $user = $faceEnrollment->user;

            if (! $user) {
                throw new \Exception('User tidak ditemukan');
            }

            Log::info('User identification for attendance', [
                'identified_user_id' => $user->id,
                'identified_user_name' => $user->name,
                'current_auth_user_id' => auth()->id(),
                'current_auth_user_name' => auth()->user()?->name,
            ]);

            $this->identifiedUser = $user;
            $this->confidenceLevel = $responseData['confidence_level'] ?? null;
            $this->maskDetected = ($responseData['masker'] ?? 'false') === 'true';

            $this->validateAttendancePermission($user);
            $this->validateAttendanceRules($user);

            $this->recordAttendance($user, $response);

            $this->attendanceStatus = 'success';
            $this->successMessage = $this->getSuccessMessage($user);

            // Refresh today's attendance status
            $this->checkTodaysAttendance($user->id);

        } catch (\Exception $e) {
            Log::error('Face attendance failed', [
                'type' => $this->attendanceType,
                'error' => $e->getMessage(),
            ]);

            $this->errorMessage = $e->getMessage();
            $this->attendanceStatus = 'error';
        }
    }

    protected function validateAttendancePermission(User $user): void
    {
        if (! auth()->check()) {
            throw new \Exception('Anda harus login terlebih dahulu');
        }

        $currentUser = auth()->user();

        // Allow self-attendance or admin/super admin can record for others
        if ($currentUser->id === $user->id) {
            // User recording their own attendance - always allowed
            return;
        }

        // Check if current user is admin/super admin
        if ($currentUser->hasAnyRole(['super admin', 'admin'])) {
            // Admin can record attendance for any user
            return;
        }

        // Otherwise, not allowed
        throw new \Exception('Anda hanya dapat mencatat absensi untuk diri sendiri. Wajah yang dikenali: '.$user->name);
    }

    protected function validateAttendanceRules(User $user): void
    {
        $attendance = $user->attendance;

        if (! $attendance) {
            throw new \Exception('Setting attendance untuk user ini belum dikonfigurasi');
        }

        // Check if it's a valid work day
        $today = now()->dayOfWeekIso; // 1 = Monday, 7 = Sunday

        if (! in_array($today, $attendance->work_days)) {
            throw new \Exception('Hari ini bukan hari kerja Anda');
        }

        // Check time constraints
        $currentTime = now()->format('H:i');
        $clockInTime = $attendance->getFormattedClockInTime();
        $clockOutTime = $attendance->getFormattedClockOutTime();

        if ($this->attendanceType === 'check_in' && $currentTime < $clockInTime) {
            throw new \Exception('Belum waktu untuk check in. Waktu check in: '.$clockInTime);
        }

        if ($this->attendanceType === 'check_out' && $currentTime < $clockOutTime) {
            // Allow early check out, just show warning in notes
        }

        // Validate location if location_id is set
        if ($attendance->location_id) {
            $this->validateLocation($attendance);
        }
    }

    protected function validateLocation($attendance): void
    {
        if (! $this->userLatitude || ! $this->userLongitude) {
            throw new \Exception('Lokasi tidak terdeteksi. Pastikan GPS aktif dan izinkan akses lokasi.');
        }

        $location = $attendance->location;

        if (! $location || ! $location->is_active) {
            throw new \Exception('Lokasi kerja tidak tersedia atau tidak aktif');
        }

        // Calculate distance using Haversine formula
        $distance = $this->calculateDistance(
            $this->userLatitude,
            $this->userLongitude,
            $location->latitude,
            $location->longitude
        );

        if ($distance > $location->radius_meters) {
            throw new \Exception(
                "Anda berada di luar area kerja. Jarak: {$distance}m dari {$location->name} (Max: {$location->radius_meters}m)"
            );
        }

        $this->locationValidated = true;

        Log::info('Location validation passed', [
            'user_id' => auth()->id(),
            'location' => $location->name,
            'user_coordinates' => [$this->userLatitude, $this->userLongitude],
            'location_coordinates' => [$location->latitude, $location->longitude],
            'distance_meters' => $distance,
            'radius_meters' => $location->radius_meters,
        ]);
    }

    protected function calculateDistance($lat1, $lon1, $lat2, $lon2): int
    {
        $earthRadiusKm = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $lat1 = deg2rad($lat1);
        $lat2 = deg2rad($lat2);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            sin($dLon / 2) * sin($dLon / 2) * cos($lat1) * cos($lat2);
        $c = 2 * asin(sqrt($a));

        $distanceKm = $earthRadiusKm * $c;

        return (int) ($distanceKm * 1000); // Convert to meters
    }

    protected function recordAttendance(User $user, array $faceApiResponse): void
    {
        DB::beginTransaction();

        try {
            // Get today's attendance records
            $todaysRecords = AttendanceRecord::where('user_id', $user->id)
                ->whereDate('recorded_at', today())
                ->orderBy('recorded_at')
                ->get();

            $todaysCheckIn = $todaysRecords->firstWhere('type', 'check_in');
            $todaysCheckOut = $todaysRecords->firstWhere('type', 'check_out');

            // Validation for check_in
            if ($this->attendanceType === 'check_in') {
                if ($todaysCheckIn) {
                    throw new \Exception('Anda sudah melakukan check in hari ini pada '.
                        $todaysCheckIn->recorded_at->format('H:i:s'));
                }
            }

            // Validation for check_out
            if ($this->attendanceType === 'check_out') {
                if (! $todaysCheckIn) {
                    throw new \Exception('Tidak dapat check out tanpa check in terlebih dahulu');
                }

                if ($todaysCheckOut) {
                    throw new \Exception('Anda sudah melakukan check out hari ini pada '.
                        $todaysCheckOut->recorded_at->format('H:i:s'));
                }
            }

            // Prepare location data
            $locationData = [
                'ip_address' => request()->ip(),
            ];

            if ($this->userLatitude && $this->userLongitude) {
                $locationData['coordinates'] = [
                    'latitude' => $this->userLatitude,
                    'longitude' => $this->userLongitude,
                ];
            }

            // Prepare notes
            $notes = [];
            if ($this->maskDetected) {
                $notes[] = 'Mengenakan masker';
            }
            if ($this->locationValidated && $user->attendance?->location) {
                $notes[] = 'Lokasi tervalidasi: '.$user->attendance->location->name;
            }

            AttendanceRecord::create([
                'user_id' => $user->id,
                'type' => $this->attendanceType,
                'recorded_at' => now(),
                'method' => 'face_recognition',
                'confidence_level' => $this->confidenceLevel,
                'mask_detected' => $this->maskDetected,
                'face_api_response' => $faceApiResponse,
                'location' => $locationData,
                'notes' => implode(', ', $notes) ?: null,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function getSuccessMessage(User $user): string
    {
        $type = $this->attendanceType === 'check_in' ? 'masuk' : 'keluar';
        $time = now()->format('H:i');

        $message = "Absensi {$type} berhasil untuk {$user->name} pada {$time}";

        // Add timing status
        if ($this->attendanceType === 'check_in' && $this->isLateCheckIn) {
            $expectedTime = $user->attendance?->getFormattedClockInTime();
            $message .= " (Terlambat - Jam kerja: {$expectedTime})";
        } elseif ($this->attendanceType === 'check_out' && $this->isEarlyCheckOut) {
            $expectedTime = $user->attendance?->getFormattedClockOutTime();
            $message .= " (Pulang cepat - Jam kerja: {$expectedTime})";
        }

        // Add next action hint
        if ($this->attendanceType === 'check_in') {
            $message .= '. Jangan lupa check out nanti!';
        } elseif ($this->attendanceType === 'check_out') {
            $message .= '. Sampai jumpa besok!';
        }

        return $message;
    }

    public function getTodaysAttendanceSummary(): array
    {
        $summary = [
            'has_check_in' => ! empty($this->todaysAttendance['check_in']),
            'has_check_out' => ! empty($this->todaysAttendance['check_out']),
            'is_complete' => $this->attendanceComplete,
            'next_action' => $this->nextActionNeeded,
        ];

        if ($summary['has_check_in']) {
            $summary['check_in_time'] = $this->todaysAttendance['check_in']->recorded_at->format('H:i:s');
        }

        if ($summary['has_check_out']) {
            $summary['check_out_time'] = $this->todaysAttendance['check_out']->recorded_at->format('H:i:s');
        }

        return $summary;
    }

    public function getCheckInTimeClass(): string
    {
        if ($this->isLateCheckIn) {
            return 'text-red-600 font-semibold';
        }

        return 'text-green-600';
    }

    public function getCheckOutTimeClass(): string
    {
        if ($this->isEarlyCheckOut) {
            return 'text-red-600 font-semibold';
        }

        return 'text-blue-600';
    }

    public function getCheckInStatusMessage(): string
    {
        if (! $this->todaysAttendance['check_in']) {
            return '';
        }

        if ($this->isLateCheckIn) {
            $user = auth()->user();
            $expectedTime = $user?->attendance?->getFormattedClockInTime();

            return " (Terlambat - Jam kerja: {$expectedTime})";
        }

        return ' (Tepat waktu)';
    }

    public function getCheckOutStatusMessage(): string
    {
        if (! $this->todaysAttendance['check_out']) {
            return '';
        }

        if ($this->isEarlyCheckOut) {
            $user = auth()->user();
            $expectedTime = $user?->attendance?->getFormattedClockOutTime();

            return " (Pulang cepat - Jam kerja: {$expectedTime})";
        }

        return ' (Sesuai waktu)';
    }

    public function retake(): void
    {
        $this->capturedImage = '';
        $this->attendanceStatus = 'capturing';
        $this->errorMessage = '';
        $this->dispatch('attendance-camera-started');
    }

    public function resetComponent(): void
    {
        $this->capturedImage = '';
        $this->attendanceStatus = 'idle';
        $this->errorMessage = '';
        $this->successMessage = '';
        $this->showCamera = false;
        $this->identifiedUser = null;
        $this->confidenceLevel = null;
        $this->maskDetected = false;

        // Refresh today's attendance status
        $user = auth()->user();
        if ($user) {
            $this->checkTodaysAttendance($user->id);
        }
    }

    public function render()
    {
        return view('livewire.face-attendance-component');
    }
}
