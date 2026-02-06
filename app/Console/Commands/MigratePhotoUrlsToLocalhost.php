<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigratePhotoUrlsToLocalhost extends Command
{
    protected $signature = 'bikes:migrate-photos-to-localhost';

    protected $description = 'Migrate bike photo URLs from veloflott.localhost to localhost';

    public function handle(): int
    {
        $this->info('Starting migration of bike photo URLs...');

        $bikes = DB::table('bikes')
            ->whereNotNull('photos')
            ->get();

        $totalBikes = $bikes->count();
        $updatedBikes = 0;

        foreach ($bikes as $bike) {
            $photos = json_decode($bike->photos, true);

            if (!is_array($photos) || empty($photos)) {
                continue;
            }

            $hasChanges = false;
            $updatedPhotos = array_map(function ($photo) use (&$hasChanges) {
                if (str_contains($photo, 'https://veloflott.localhost/storage/')) {
                    $hasChanges = true;

                    return str_replace('https://veloflott.localhost/storage/', 'http://localhost/storage/', $photo);
                }

                return $photo;
            }, $photos);

            if ($hasChanges) {
                DB::table('bikes')
                    ->where('id', $bike->id)
                    ->update([
                        'photos' => json_encode($updatedPhotos),
                        'updated_at' => now(),
                    ]);

                $updatedBikes++;
                $this->info("Updated bike {$bike->id}");
            }
        }

        $this->info('Migration completed!');
        $this->info("Total bikes: {$totalBikes}");
        $this->info("Updated bikes: {$updatedBikes}");

        return Command::SUCCESS;
    }
}
