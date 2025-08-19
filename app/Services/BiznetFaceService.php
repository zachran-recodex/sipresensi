<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BiznetFaceService
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
     * Validate base64 image
     */
    public static function validateBase64Image(string $base64): bool
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

        return $imageInfo !== false;
    }
}
