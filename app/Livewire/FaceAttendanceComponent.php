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
        }
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
        // Check if it's a valid work day
        $today = now()->dayOfWeekIso; // 1 = Monday, 7 = Sunday
        $attendance = $user->attendance;

        if ($attendance && ! in_array($today, $attendance->work_days)) {
            throw new \Exception('Hari ini bukan hari kerja Anda');
        }

        // Check time constraints if needed
        if ($attendance) {
            $currentTime = now()->format('H:i');
            $clockInTime = $attendance->getFormattedClockInTime();
            $clockOutTime = $attendance->getFormattedClockOutTime();

            if ($this->attendanceType === 'check_in' && $currentTime < $clockInTime) {
                throw new \Exception('Belum waktu untuk check in. Waktu check in: '.$clockInTime);
            }

            if ($this->attendanceType === 'check_out' && $currentTime < $clockOutTime) {
                // Allow early check out, just show warning in notes
            }
        }
    }

    protected function recordAttendance(User $user, array $faceApiResponse): void
    {
        DB::beginTransaction();

        try {
            $lastRecord = AttendanceRecord::where('user_id', $user->id)
                ->whereDate('recorded_at', today())
                ->orderBy('recorded_at', 'desc')
                ->first();

            if ($this->attendanceType === 'check_out' && ! $lastRecord) {
                throw new \Exception('Tidak dapat check out tanpa check in terlebih dahulu');
            }

            if ($this->attendanceType === 'check_in' && $lastRecord && $lastRecord->type === 'check_in') {
                throw new \Exception('Anda sudah melakukan check in hari ini');
            }

            AttendanceRecord::create([
                'user_id' => $user->id,
                'type' => $this->attendanceType,
                'recorded_at' => now(),
                'method' => 'face_recognition',
                'confidence_level' => $this->confidenceLevel,
                'mask_detected' => $this->maskDetected,
                'face_api_response' => $faceApiResponse,
                'location' => request()->ip(),
                'notes' => $this->maskDetected ? 'Mengenakan masker' : null,
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

        return "Absensi {$type} berhasil untuk {$user->name} pada {$time}";
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
    }

    public function render()
    {
        return view('livewire.face-attendance-component');
    }
}
