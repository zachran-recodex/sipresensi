<?php

namespace App\Console\Commands;

use App\Services\BiznetFaceService;
use Illuminate\Console\Command;

class CreateFaceGallery extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'face:create-gallery {gallery_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a face gallery in Biznet Face API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $galleryId = $this->argument('gallery_id') ?: config('services.biznet_face.gallery_id');

        $this->info("Creating face gallery: {$galleryId}");

        try {
            $service = app(BiznetFaceService::class);

            // First check existing galleries
            $this->info('Checking existing galleries...');
            $galleries = $service->getMyFaceGalleries();
            $this->table(['Gallery ID'], collect($galleries['risetai']['data'] ?? [])->map(fn ($g) => [$g['facegallery_id']]));

            // Create gallery if it doesn't exist
            $existingGalleries = collect($galleries['risetai']['data'] ?? [])->pluck('facegallery_id')->toArray();

            if (in_array($galleryId, $existingGalleries)) {
                $this->warn("Gallery '{$galleryId}' already exists!");

                return Command::SUCCESS;
            }

            $this->info("Creating gallery '{$galleryId}'...");
            $result = $service->createFaceGallery($galleryId);

            if ($result['risetai']['status'] === '200') {
                $this->info("✅ Gallery '{$galleryId}' created successfully!");
            } else {
                $this->error('Failed to create gallery: '.$result['risetai']['status_message']);

                return Command::FAILURE;
            }

        } catch (\Exception $e) {
            $this->error('Error: '.$e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
