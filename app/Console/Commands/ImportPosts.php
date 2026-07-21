<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

#[Signature('app:import-posts')]
#[Description('Import posts from public/posts_with_data.sql')]
class ImportPosts extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sqlPath = public_path('posts_with_data.sql');

        if (!file_exists($sqlPath)) {
            $this->error("SQL file not found at: {$sqlPath}");
            return Command::FAILURE;
        }

        $tempTableName = 'posts_import_temp';
        $this->info("Dropping temporary table {$tempTableName} if it exists...");
        Schema::dropIfExists($tempTableName);

        // 1. Create temp table structure by copying posts table and dropping wise_rating
        $this->info("Creating temporary table structure {$tempTableName}...");
        DB::statement("CREATE TABLE {$tempTableName} LIKE posts;");
        DB::statement("ALTER TABLE {$tempTableName} DROP COLUMN wise_rating;");

        // 2. Read SQL file line by line and insert in chunks of 100
        $this->info("Reading SQL file and inserting in chunks...");
        $handle = fopen($sqlPath, 'r');
        if (!$handle) {
            $this->error("Failed to open SQL file.");
            return Command::FAILURE;
        }

        $chunkSize = 100;
        $rows = [];
        $totalInserted = 0;

        // Since we don't know the total lines in advance, we will count lines starting with '(' first to show a progress bar
        $this->info("Counting total rows to import...");
        $totalRows = 0;
        while (($line = fgets($handle)) !== false) {
            if (str_starts_with(trim($line), '(')) {
                $totalRows++;
            }
        }
        rewind($handle);

        $this->info("Importing {$totalRows} posts to temporary table...");
        $bar = $this->output->createProgressBar($totalRows);
        $bar->start();

        while (($line = fgets($handle)) !== false) {
            $trimmed = trim($line);
            if (str_starts_with($trimmed, '(')) {
                // Trim trailing comma or semicolon
                $rowVal = rtrim($trimmed, ',;');
                $rows[] = $rowVal;

                if (count($rows) >= $chunkSize) {
                    $valuesSql = implode(",\n", $rows);
                    DB::unprepared("INSERT INTO {$tempTableName} (`id`, `user_id`, `content`, `image`, `video`, `privacy_level_id`, `like_count`, `comment_count`, `share_count`, `is_active`, `parent_id`, `post_type_id`, `created_at`, `updated_at`, `deleted_at`) VALUES {$valuesSql}");
                    $totalInserted += count($rows);
                    $bar->advance(count($rows));
                    $rows = [];
                }
            }
        }

        // Insert remaining rows
        if (count($rows) > 0) {
            $valuesSql = implode(",\n", $rows);
            DB::unprepared("INSERT INTO {$tempTableName} (`id`, `user_id`, `content`, `image`, `video`, `privacy_level_id`, `like_count`, `comment_count`, `share_count`, `is_active`, `parent_id`, `post_type_id`, `created_at`, `updated_at`, `deleted_at`) VALUES {$valuesSql}");
            $totalInserted += count($rows);
            $bar->advance(count($rows));
        }
        fclose($handle);
        $bar->finish();
        $this->newLine();

        $this->info("Successfully populated temporary table with {$totalInserted} rows.");

        // 3. Truncate posts
        $this->warn("Truncating active posts table...");
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('posts')->truncate();

        // 4. Copy to active posts table setting wise_rating to NULL
        $this->info("Copying posts to active table...");
        DB::unprepared("INSERT INTO posts (`id`, `user_id`, `content`, `image`, `video`, `privacy_level_id`, `like_count`, `comment_count`, `share_count`, `is_active`, `parent_id`, `post_type_id`, `wise_rating`, `created_at`, `updated_at`, `deleted_at`)
                        SELECT `id`, `user_id`, `content`, `image`, `video`, `privacy_level_id`, `like_count`, `comment_count`, `share_count`, `is_active`, `parent_id`, `post_type_id`, NULL, `created_at`, `updated_at`, `deleted_at` FROM {$tempTableName}");

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 5. Clean up
        $this->info("Dropping temporary table {$tempTableName}...");
        Schema::dropIfExists($tempTableName);

        $this->info("Posts import completed successfully! Imported {$totalInserted} posts.");
        return Command::SUCCESS;
    }
}
