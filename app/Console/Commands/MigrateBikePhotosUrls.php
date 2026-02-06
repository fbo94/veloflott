<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateBikePhotosUrls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bikes:migrate-photos-urls';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix bike photo URLs by removing duplicate bikes/ path';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migration of bike photo URLs...');

        $bikes = DB::table('bikes')
            ->whereNotNull('photos')
            ->get();

        $updatedCount = 0;

        foreach ($bikes as $bike) {
            $photos = json_decode($bike->photos, true);

            if (!is_array($photos) || empty($photos)) {
                continue;
            }

            $hasChanges = false;
            $updatedPhotos = array_map(function ($photo) use (&$hasChanges) {
                // Fix URLs with duplicate bikes/bikes
                if (str_contains($photo, '/storage/bikes/bikes/')) {
                    $hasChanges = true;

                    return str_replace('/storage/bikes/bikes/', '/storage/bikes/', $photo);
                }

                return $photo;
            }, $photos);

            if ($hasChanges) {
                DB::table('bikes')
                    ->where('id', $bike->id)
                    ->update(['photos' => json_encode($updatedPhotos)]);

                $updatedCount++;
                $this->line("✓ Updated bike {$bike->internal_number} ({$bike->id})");
            }
        }

        $this->info("Migration completed! Updated {$updatedCount} bike(s).");

        return Command::SUCCESS;
    }
}
