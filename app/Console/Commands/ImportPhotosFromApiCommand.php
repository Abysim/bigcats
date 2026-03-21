<?php

namespace App\Console\Commands;

use App\Models\Photo;
use App\Models\Tag;
use Dotenv\Dotenv;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ImportPhotosFromApiCommand extends Command
{
    protected $signature = 'photos:import-from-api {--api-path=api : Path to API project relative to base_path}';
    protected $description = 'One-time import of published photos from API flickr_photos table';

    private const FLICKR_PHOTO_STATUS_PUBLISHED = 6;

    public function handle(): int
    {
        $apiPath = base_path($this->option('api-path'));
        $envFile = $apiPath . '/.env';

        if (!file_exists($envFile)) {
            $this->error("API .env file not found at: {$envFile}");
            $this->info('Use --api-path=../api for local development');

            return self::FAILURE;
        }

        $apiEnv = Dotenv::createArrayBacked($apiPath, '.env')->load();

        $requiredKeys = ['DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'];
        $missingKeys = array_diff($requiredKeys, array_keys($apiEnv));
        if (!empty($missingKeys)) {
            $this->error('Missing required keys in API .env: ' . implode(', ', $missingKeys));

            return self::FAILURE;
        }

        config(['database.connections.api' => [
            'driver' => 'mysql',
            'host' => $apiEnv['DB_HOST'] ?? '127.0.0.1',
            'port' => $apiEnv['DB_PORT'] ?? '3306',
            'database' => $apiEnv['DB_DATABASE'],
            'username' => $apiEnv['DB_USERNAME'],
            'password' => $apiEnv['DB_PASSWORD'],
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]]);

        try {
            $query = DB::connection('api')
                ->table('flickr_photos')
                ->where('status', self::FLICKR_PHOTO_STATUS_PUBLISHED)
                ->whereNotNull('thumbnail_url')
                ->whereNotNull('thumbnail_width')
                ->whereNotNull('thumbnail_height')
                ->whereNotNull('url');

            $total = $query->count();
        } catch (QueryException $e) {
            $this->error('Failed to connect to API database: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->info("Found {$total} published photos in API database.");

        if ($total === 0) {
            $this->info('Nothing to import.');

            return self::SUCCESS;
        }

        $existingLinks = Photo::pluck('flickr_link')->flip()->all();
        $tagMap = Tag::pluck('id', 'short_name');

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $stats = ['imported' => 0, 'skipped_duplicate' => 0];

        DB::transaction(function () use ($query, $existingLinks, $tagMap, $bar, &$stats) {
            foreach ($query->lazy() as $flickrPhoto) {
                $bar->advance();

                if (isset($existingLinks[$flickrPhoto->url])) {
                    $stats['skipped_duplicate']++;
                    continue;
                }

                $photo = Photo::create([
                    'name' => $flickrPhoto->publish_title ?? '',
                    'author_name' => $flickrPhoto->owner_realname ?: $flickrPhoto->owner_username ?: 'Unknown',
                    'flickr_link' => $flickrPhoto->url,
                    'thumbnail_url' => $flickrPhoto->thumbnail_url,
                    'thumbnail_width' => $flickrPhoto->thumbnail_width,
                    'thumbnail_height' => $flickrPhoto->thumbnail_height,
                    'is_published' => true,
                ]);

                if (!empty($flickrPhoto->publish_tags)) {
                    $tagNames = array_filter(array_map(
                        fn ($t) => preg_replace('/[^[:alnum:]]/u', '', $t),
                        explode(' ', $flickrPhoto->publish_tags),
                    ));
                    $tagIds = $tagMap->only($tagNames)->values()->all();
                    if (!empty($tagIds)) {
                        $photo->tags()->sync($tagIds);
                    }
                }

                $existingLinks[$flickrPhoto->url] = true;
                $stats['imported']++;
            }
        });

        $bar->finish();
        $this->newLine(2);

        $this->info('Import complete:');
        $this->table(
            ['Metric', 'Count'],
            collect($stats)->map(fn ($v, $k) => [str_replace('_', ' ', ucfirst($k)), $v])->values()->all(),
        );

        return self::SUCCESS;
    }
}
