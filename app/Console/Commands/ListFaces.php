<?php

namespace App\Console\Commands;

use App\Services\FaceRecognitionService;
use Illuminate\Console\Command;

class ListFaces extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'face:list-faces {gallery_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all faces in a gallery';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $galleryId = $this->argument('gallery_id') ?: config('services.biznet_face.gallery_id');

        $this->info("Listing faces in gallery: {$galleryId}");

        try {
            $service = app(FaceRecognitionService::class);
            $result = $service->listFaces($galleryId);

            if ($result['risetai']['status'] === '200') {
                $faces = $result['risetai']['data'] ?? [];

                if (empty($faces)) {
                    $this->warn('No faces found in gallery.');

                    return Command::SUCCESS;
                }

                $this->table(['User ID', 'User Name'],
                    collect($faces)->map(fn ($face) => [
                        $face['user_id'] ?? 'N/A',
                        $face['user_name'] ?? 'N/A',
                    ])
                );
            } else {
                $this->error('Failed to list faces: '.$result['risetai']['status_message']);

                return Command::FAILURE;
            }

        } catch (\Exception $e) {
            $this->error('Error: '.$e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
