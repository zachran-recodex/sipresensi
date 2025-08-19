<?php

namespace App\Console\Commands;

use App\Models\FaceEnrollment;
use App\Services\FaceRecognitionService;
use Illuminate\Console\Command;

class CleanFaceData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'face:clean-data {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean all face enrollment data from both database and Biznet API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (! $this->option('force')) {
            if (! $this->confirm('This will delete ALL face enrollment data. Are you sure?')) {
                $this->info('Operation cancelled.');

                return Command::SUCCESS;
            }
        }

        try {
            $service = app(FaceRecognitionService::class);

            // Get all face enrollments
            $enrollments = FaceEnrollment::all();

            $this->info('Found '.$enrollments->count().' face enrollments to clean.');

            $deletedFromApi = 0;
            $failedFromApi = 0;

            // Delete from Biznet API first
            foreach ($enrollments as $enrollment) {
                try {
                    $service->deleteFace($enrollment->biznet_user_id);
                    $this->line("✅ Deleted {$enrollment->biznet_user_id} from API");
                    $deletedFromApi++;
                } catch (\Exception $e) {
                    $this->line("❌ Failed to delete {$enrollment->biznet_user_id}: {$e->getMessage()}");
                    $failedFromApi++;
                }
            }

            // Delete from database
            $deletedFromDb = FaceEnrollment::count();
            FaceEnrollment::truncate();

            $this->info("✅ Cleaned {$deletedFromDb} records from database");
            $this->info("✅ Deleted {$deletedFromApi} faces from Biznet API");

            if ($failedFromApi > 0) {
                $this->warn("⚠️  {$failedFromApi} faces failed to delete from API");
            }

            $this->info('Face data cleanup completed!');

        } catch (\Exception $e) {
            $this->error('Error: '.$e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
