<?php

namespace App\Livewire;

use App\Models\FaceEnrollment;
use App\Services\BiznetFaceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class FaceEnrollmentComponent extends Component
{
    public $userId;

    public $userName;

    public $capturedImage = '';

    public $enrollmentStatus = 'idle';

    public $errorMessage = '';

    public $showCamera = false;

    protected $biznetFaceService;

    public function boot(): void
    {
        $this->biznetFaceService = app(BiznetFaceService::class);
    }

    public function mount($userId = null, $userName = null): void
    {
        // Debug authentication
        $user = auth()->user();

        // Log for debugging
        logger('FaceEnrollment mount debug', [
            'auth_user' => $user,
            'auth_id' => auth()->id(),
            'provided_userId' => $userId,
            'provided_userName' => $userName,
        ]);

        // Ensure userId is always an integer
        $this->userId = $userId ? (int) $userId : ($user ? $user->id : null);
        $this->userName = $userName ?? ($user ? $user->name : null);

        if (! $this->userId) {
            session()->flash('error', 'Silahkan login terlebih dahulu untuk mendaftarkan wajah.');
            $this->redirect(route('login'));

            return;
        }

        // Additional validation
        if (! is_numeric($this->userId)) {
            throw new \Exception('Invalid user ID: '.$this->userId);
        }

        $this->userId = (int) $this->userId;

        // Check if user already has face enrollment
        $existingEnrollment = \App\Models\FaceEnrollment::where('user_id', $this->userId)
            ->where('is_active', true)
            ->first();

        if ($existingEnrollment) {
            $this->enrollmentStatus = 'already_enrolled';
        }
    }

    public function startCamera(): void
    {
        $this->showCamera = true;
        $this->enrollmentStatus = 'capturing';
        $this->errorMessage = '';
        $this->dispatch('camera-started');
    }

    public function captureImage($imageData): void
    {
        try {
            if (! $imageData) {
                throw new \Exception('Data gambar kosong');
            }

            // Validate image quality
            if (! BiznetFaceService::validateBase64Image($imageData)) {
                throw new \Exception('Kualitas gambar tidak memenuhi syarat');
            }

            $this->capturedImage = $imageData;
            $this->enrollmentStatus = 'captured';

            $this->dispatch('image-captured', imageData: $imageData);
        } catch (\Exception $e) {
            $this->errorMessage = 'Gagal menangkap gambar: '.$e->getMessage();
            $this->enrollmentStatus = 'error';
        }
    }

    public function enrollFace(): void
    {
        try {
            $this->enrollmentStatus = 'enrolling';
            $this->errorMessage = '';

            if (! $this->capturedImage) {
                throw new \Exception('Tidak ada gambar yang ditangkap');
            }

            if (! BiznetFaceService::validateBase64Image($this->capturedImage)) {
                throw new \Exception('Gambar tidak valid');
            }

            $biznetUserId = 'user_'.$this->userId.'_'.time();

            DB::beginTransaction();

            $response = $this->biznetFaceService->enrollFace(
                $biznetUserId,
                $this->userName,
                $this->capturedImage
            );

            FaceEnrollment::create([
                'user_id' => $this->userId,
                'biznet_user_id' => $biznetUserId,
                'face_gallery_id' => config('services.biznet_face.gallery_id'),
                'enrolled_at' => now(),
                'enrollment_response' => $response,
                'is_active' => true,
            ]);

            DB::commit();

            $this->enrollmentStatus = 'success';
            $this->dispatch('face-enrolled');

            session()->flash('message', 'Wajah berhasil didaftarkan!');

            // Reset to already enrolled state
            $this->enrollmentStatus = 'already_enrolled';

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Face enrollment failed', [
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
            ]);

            $this->errorMessage = 'Gagal mendaftarkan wajah: '.$e->getMessage();
            $this->enrollmentStatus = 'error';
        }
    }

    public function retake(): void
    {
        $this->capturedImage = '';
        $this->enrollmentStatus = 'capturing';
        $this->errorMessage = '';
    }

    public function reenrollFace(): void
    {
        try {
            // Get existing active enrollments
            $existingEnrollments = \App\Models\FaceEnrollment::where('user_id', $this->userId)
                ->where('is_active', true)
                ->get();

            // Delete faces from Biznet API first
            foreach ($existingEnrollments as $enrollment) {
                try {
                    $this->biznetFaceService->deleteFace($enrollment->biznet_user_id);
                    Log::info('Deleted face from Biznet API', ['biznet_user_id' => $enrollment->biznet_user_id]);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete face from Biznet API', [
                        'biznet_user_id' => $enrollment->biznet_user_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Deactivate existing enrollments in database
            \App\Models\FaceEnrollment::where('user_id', $this->userId)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            // Reset to idle state for new enrollment
            $this->enrollmentStatus = 'idle';
            $this->capturedImage = '';
            $this->errorMessage = '';
            $this->showCamera = false;

            session()->flash('message', 'Data wajah lama telah dihapus. Silahkan daftar ulang.');

        } catch (\Exception $e) {
            Log::error('Error during reenroll face', [
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
            ]);

            $this->errorMessage = 'Gagal menghapus data wajah lama: '.$e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.face-enrollment-component');
    }
}
