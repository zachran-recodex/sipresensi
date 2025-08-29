<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FaceRecognitionService
{
    protected string $apiUrl;

    protected string $token;

    protected string $faceGalleryId;

    public function __construct()
    {
        $this->apiUrl = config('services.biznet_face.api_url');
        $this->token = config('services.biznet_face.api_token');
        $this->faceGalleryId = config('services.biznet_face.gallery_id');
    }

    /**
     * Get API counters (remaining quota)
     */
    public function getCounters(): array
    {
        $response = Http::withHeaders([
            'Accesstoken' => $this->token,
        ])->get($this->apiUrl.'/client/get-counters', [
            'trx_id' => $this->generateTransactionId(),
        ]);

        return $this->handleResponse($response);
    }

    /**
     * Create face gallery
     */
    public function createFaceGallery(?string $galleryId = null): array
    {
        $galleryId = $galleryId ?: $this->faceGalleryId;

        $response = Http::withHeaders([
            'Accesstoken' => $this->token,
        ])->post($this->apiUrl.'/facegallery/create-facegallery', [
            'facegallery_id' => $galleryId,
            'trx_id' => $this->generateTransactionId(),
        ]);

        return $this->handleResponse($response);
    }

    /**
     * Get list of face galleries
     */
    public function getMyFaceGalleries(): array
    {
        $response = Http::withHeaders([
            'Accesstoken' => $this->token,
        ])->get($this->apiUrl.'/facegallery/my-facegalleries');

        return $this->handleResponse($response);
    }

    /**
     * Enroll a face to the gallery
     */
    public function enrollFace(string $userId, string $userName, string $base64Image, ?string $galleryId = null): array
    {
        $galleryId = $galleryId ?: $this->faceGalleryId;

        $response = Http::withHeaders([
            'Accesstoken' => $this->token,
        ])->post($this->apiUrl.'/facegallery/enroll-face', [
            'user_id' => $userId,
            'user_name' => $userName,
            'facegallery_id' => $galleryId,
            'image' => $base64Image,
            'trx_id' => $this->generateTransactionId(),
        ]);

        return $this->handleResponse($response);
    }

    /**
     * Verify a face with registered user (1:1 authentication)
     */
    public function verifyFace(string $userId, string $base64Image, ?string $galleryId = null): array
    {
        $galleryId = $galleryId ?: $this->faceGalleryId;

        $response = Http::withHeaders([
            'Accesstoken' => $this->token,
        ])->post($this->apiUrl.'/facegallery/verify-face', [
            'user_id' => $userId,
            'facegallery_id' => $galleryId,
            'image' => $base64Image,
            'trx_id' => $this->generateTransactionId(),
        ]);

        return $this->handleResponse($response);
    }

    /**
     * Identify a face from gallery (1:N authentication)
     */
    public function identifyFace(string $base64Image, ?string $galleryId = null): array
    {
        $galleryId = $galleryId ?: $this->faceGalleryId;

        $response = Http::withHeaders([
            'Accesstoken' => $this->token,
        ])->post($this->apiUrl.'/facegallery/identify-face', [
            'facegallery_id' => $galleryId,
            'image' => $base64Image,
            'trx_id' => $this->generateTransactionId(),
        ]);

        return $this->handleResponse($response);
    }

    /**
     * Compare two images without using database
     */
    public function compareImages(string $sourceImage, string $targetImage): array
    {
        $response = Http::withHeaders([
            'Accesstoken' => $this->token,
        ])->post($this->apiUrl.'/compare-images', [
            'source_image' => $sourceImage,
            'target_image' => $targetImage,
            'trx_id' => $this->generateTransactionId(),
        ]);

        return $this->handleResponse($response);
    }

    /**
     * List all faces in a gallery
     */
    public function listFaces(?string $galleryId = null): array
    {
        $galleryId = $galleryId ?: $this->faceGalleryId;

        $response = Http::withHeaders([
            'Accesstoken' => $this->token,
        ])->post($this->apiUrl.'/facegallery/list-faces', [
            'facegallery_id' => $galleryId,
            'trx_id' => $this->generateTransactionId(),
        ]);

        return $this->handleResponse($response);
    }

    /**
     * Delete a face from gallery
     */
    public function deleteFace(string $userId, ?string $galleryId = null): array
    {
        $galleryId = $galleryId ?: $this->faceGalleryId;

        $response = Http::withHeaders([
            'Accesstoken' => $this->token,
        ])->delete($this->apiUrl.'/facegallery/delete-face', [
            'user_id' => $userId,
            'facegallery_id' => $galleryId,
            'trx_id' => $this->generateTransactionId(),
        ]);

        return $this->handleResponse($response);
    }

    /**
     * Delete face gallery
     */
    public function deleteFaceGallery(?string $galleryId = null): array
    {
        $galleryId = $galleryId ?: $this->faceGalleryId;

        $response = Http::withHeaders([
            'Accesstoken' => $this->token,
        ])->delete($this->apiUrl.'/facegallery/delete-facegallery', [
            'facegallery_id' => $galleryId,
            'trx_id' => $this->generateTransactionId(),
        ]);

        return $this->handleResponse($response);
    }

    /**
     * Handle API response
     */
    protected function handleResponse($response): array
    {
        $data = $response->json();

        // Log the response for debugging
        Log::info('Biznet Face API Response', [
            'status_code' => $response->status(),
            'data' => $data,
        ]);

        if (! $response->successful()) {
            throw new \Exception('Biznet Face API Error: '.($data['status_message'] ?? 'Unknown error'));
        }

        return $data;
    }

    /**
     * Generate unique transaction ID
     */
    protected function generateTransactionId(): string
    {
        return 'sipresensi_'.Str::uuid()->toString();
    }

    /**
     * Convert file to base64
     */
    public static function fileToBase64(string $filePath): string
    {
        if (! file_exists($filePath)) {
            throw new \Exception('File not found: '.$filePath);
        }

        $imageData = file_get_contents($filePath);
        $base64 = base64_encode($imageData);

        return $base64;
    }

    /**
     * Convert uploaded file to base64
     */
    public static function uploadedFileToBase64($file): string
    {
        if (! $file || ! $file->isValid()) {
            throw new \Exception('Invalid file upload');
        }

        $imageData = file_get_contents($file->getRealPath());
        $base64 = base64_encode($imageData);

        return $base64;
    }

    /**
     * Validate base64 image with enhanced face detection checks
     */
    public static function validateBase64Image(string $base64, bool $requireFace = false): bool
    {
        // Remove data URL prefix if present
        if (str_contains($base64, 'data:image')) {
            $base64 = explode(',', $base64)[1];
        }

        $decoded = base64_decode($base64, true);

        if ($decoded === false) {
            return false;
        }

        // Check if it's a valid image
        $imageInfo = getimagesizefromstring($decoded);

        if ($imageInfo === false) {
            return false;
        }

        // Basic image quality checks
        $width = $imageInfo[0];
        $height = $imageInfo[1];

        // Stricter minimum resolution requirements for face recognition
        if ($width < 480 || $height < 480) {
            return false;
        }

        // Maximum file size (5MB when decoded)
        if (strlen($decoded) > 5 * 1024 * 1024) {
            return false;
        }

        // If face detection is required, perform additional checks
        if ($requireFace) {
            return self::basicFaceDetection($decoded);
        }

        return true;
    }

    /**
     * Enhanced face detection using image analysis
     */
    protected static function basicFaceDetection(string $imageData): bool
    {
        // Create image resource from string
        $image = imagecreatefromstring($imageData);

        if ($image === false) {
            return false;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        // Enhanced face detection - check multiple criteria
        $darkPixels = 0;
        $brightPixels = 0;
        $mediumPixels = 0;
        $totalPixels = 0;
        $skinTonePixels = 0;
        $contrastRegions = 0;

        // Sample pixels to check various characteristics
        for ($x = 0; $x < $width; $x += 8) {
            for ($y = 0; $y < $height; $y += 8) {
                $rgb = imagecolorat($image, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                $brightness = ($r + $g + $b) / 3;

                // Brightness categorization
                if ($brightness < 50) {
                    $darkPixels++;
                } elseif ($brightness > 200) {
                    $brightPixels++;
                } else {
                    $mediumPixels++;
                }

                // Skin tone detection (basic range)
                if ($r > 95 && $g > 40 && $b > 20 &&
                    $r > $g && $r > $b &&
                    $r - $g > 15 && $r - $b > 15) {
                    $skinTonePixels++;
                }

                // Check for contrast (indicating facial features)
                if ($x > 0 && $y > 0) {
                    $neighborRgb = imagecolorat($image, $x - 8, $y - 8);
                    $neighborR = ($neighborRgb >> 16) & 0xFF;
                    $neighborG = ($neighborRgb >> 8) & 0xFF;
                    $neighborB = $neighborRgb & 0xFF;
                    $neighborBrightness = ($neighborR + $neighborG + $neighborB) / 3;

                    if (abs($brightness - $neighborBrightness) > 30) {
                        $contrastRegions++;
                    }
                }

                $totalPixels++;
            }
        }

        imagedestroy($image);

        // Calculate ratios
        $darkRatio = $darkPixels / $totalPixels;
        $brightRatio = $brightPixels / $totalPixels;
        $mediumRatio = $mediumPixels / $totalPixels;
        $skinRatio = $skinTonePixels / $totalPixels;
        $contrastRatio = $contrastRegions / $totalPixels;

        // Enhanced strict validation criteria for better face detection
        // 1. Reject if too dark or too bright (stricter thresholds)
        if ($darkRatio > 0.4 || $brightRatio > 0.4) {
            return false;
        }

        // 2. Must have substantial amount of medium-brightness pixels (face area)
        if ($mediumRatio < 0.3) {
            return false;
        }

        // 3. Must have adequate skin-tone pixels (stricter skin detection)
        if ($skinRatio < 0.08) {
            return false;
        }

        // 4. Must have good contrast (indicating clear facial features)
        if ($contrastRatio < 0.15) {
            return false;
        }

        // 5. Additional validation - reject if lighting is too uniform (indicates poor image quality)
        $lightingVariance = ($darkRatio * $brightRatio * $mediumRatio);
        if ($lightingVariance < 0.01) {
            return false;
        }

        // 5. Check image dimensions ratio (faces are typically not too wide or tall)
        $aspectRatio = max($width, $height) / min($width, $height);
        if ($aspectRatio > 2.5) {
            return false;
        }

        return true;
    }
}
