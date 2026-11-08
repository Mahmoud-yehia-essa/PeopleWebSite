<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class MergeUsersCommand extends Command
{
    protected $signature = 'wiselook:merge-users {--hosting-file= : Path to hosting SQL dump}';
    protected $description = 'Smart merge of local users into hosting users with deduplication and ID mapping';

    public function handle()
    {
        $this->info("==========================================================");
        $this->info("WiseLook: Smart Users Merge & ID Mapping Generator");
        $this->info("==========================================================");

        $hostingFile = $this->option('hosting-file') ?: '/Users/mahmoudessa/Downloads/users (3).sql';

        if (!file_exists($hostingFile)) {
            $this->error("Hosting SQL file not found at: {$hostingFile}");
            return Command::FAILURE;
        }

        $this->info("1. Creating local mapping table `user_id_mappings` if not exists...");
        if (!Schema::hasTable('user_id_mappings')) {
            Schema::create('user_id_mappings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('local_user_id')->index();
                $table->unsignedBigInteger('hosting_user_id')->index();
                $table->string('action', 30); // MERGED_SKIPPED, INSERTED_AS_IS, ID_REASSIGNED
                $table->string('match_reason', 50); // email_match, phone_match, new_user, id_collision
                $table->string('email')->nullable();
                $table->string('phone_number')->nullable();
                $table->timestamps();
            });
            $this->info("Created table `user_id_mappings`.");
        } else {
            $this->warn("Table `user_id_mappings` already exists. Truncating for fresh run...");
            DB::table('user_id_mappings')->truncate();
        }

        $this->info("2. Parsing hosting users from {$hostingFile}...");
        $handle = fopen($hostingFile, 'r');
        if (!$handle) {
            $this->error("Cannot open {$hostingFile}");
            return Command::FAILURE;
        }

        $hostingIds = [];
        $hostingEmails = [];
        $hostingPhones = [];
        $dummyPhones = ["123456", "0123456789", "12345678", "00000000", "11111111", "123456789"];

        while (($line = fgets($handle)) !== false) {
            $trimmed = trim($line);
            if (preg_match("/^\(([0-9]+),/", $trimmed, $m)) {
                $id = (int)$m[1];
                $cols = str_getcsv(trim($trimmed, "(),; "), ",", "\x27");

                $email = isset($cols[1]) && $cols[1] !== "NULL" ? strtolower(trim($cols[1])) : null;
                $phone = isset($cols[7]) && $cols[7] !== "NULL" ? preg_replace("/[^0-9+]/", "", trim($cols[7])) : null;

                $hostingIds[$id] = true;
                if (!empty($email)) {
                    $hostingEmails[$email] = $id;
                }
                if (!empty($phone) && strlen($phone) >= 7 && !in_array($phone, $dummyPhones)) {
                    $hostingPhones[$phone] = $id;
                }
            }
        }
        fclose($handle);

        $this->info("Loaded from hosting: " . count($hostingIds) . " IDs, " . count($hostingEmails) . " Emails, " . count($hostingPhones) . " Phones.");

        // Find max allocated ID across local and hosting
        $maxLocalId = DB::table('users')->max('id') ?: 0;
        $maxHostingId = !empty($hostingIds) ? max(array_keys($hostingIds)) : 0;
        $nextReassignedId = max($maxLocalId, $maxHostingId) + 1;

        $this->info("Max Local ID: {$maxLocalId}, Max Hosting ID: {$maxHostingId}. Next available ID for collisions: {$nextReassignedId}");

        $outputSqlPath1 = base_path('safe_users_migration.sql');
        $outputSqlPath2 = '/Users/mahmoudessa/Downloads/safe_users_migration.sql';
        $mappingCsvPath = '/Users/mahmoudessa/Downloads/user_id_mappings.csv';

        $sqlFile = fopen($outputSqlPath1, 'w');
        $csvFile = fopen($mappingCsvPath, 'w');
        fputcsv($csvFile, ['local_user_id', 'hosting_user_id', 'action', 'match_reason', 'email', 'phone_number']);

        // Write SQL Header
        fwrite($sqlFile, "-- =============================================================\n");
        fwrite($sqlFile, "-- WiseLook: Safe Users Migration Script\n");
        fwrite($sqlFile, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
        fwrite($sqlFile, "-- =============================================================\n\n");
        fwrite($sqlFile, "SET NAMES utf8mb4;\n");
        fwrite($sqlFile, "SET FOREIGN_KEY_CHECKS = 0;\n");
        fwrite($sqlFile, "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n\n");

        $pdo = DB::connection()->getPdo();

        $totalLocal = DB::table('users')->count();
        $this->info("3. Processing {$totalLocal} local users...");

        $bar = $this->output->createProgressBar($totalLocal);
        $bar->start();

        $stats = [
            'total' => 0,
            'matched_email' => 0,
            'matched_phone' => 0,
            'inserted_as_is' => 0,
            'id_reassigned' => 0,
        ];

        $sqlChunkSize = 500;
        $sqlBatch = [];
        $mappingBatch = [];
        $maxInsertedId = $maxHostingId;

        DB::table('users')->orderBy('id')->chunk(2000, function ($users) use (
            &$stats, &$hostingIds, &$hostingEmails, &$hostingPhones, &$dummyPhones,
            &$nextReassignedId, &$sqlBatch, &$mappingBatch, &$maxInsertedId,
            $sqlFile, $csvFile, $pdo, $sqlChunkSize, $bar
        ) {
            foreach ($users as $u) {
                $stats['total']++;
                $bar->advance();

                $localId = (int)$u->id;
                $email = !empty($u->email) ? strtolower(trim($u->email)) : null;
                $phone = !empty($u->phone_number) ? preg_replace("/[^0-9+]/", "", trim($u->phone_number)) : null;

                $matchedHostingId = null;
                $matchReason = null;

                // 1. Check Email match
                if ($email && isset($hostingEmails[$email])) {
                    $matchedHostingId = $hostingEmails[$email];
                    $matchReason = 'email_match';
                    $stats['matched_email']++;
                }
                // 2. Check Phone match
                elseif ($phone && strlen($phone) >= 7 && !in_array($phone, $dummyPhones) && isset($hostingPhones[$phone])) {
                    $matchedHostingId = $hostingPhones[$phone];
                    $matchReason = 'phone_match';
                    $stats['matched_phone']++;
                }

                if ($matchedHostingId !== null) {
                    // DUPLICATE FOUND: Do NOT insert into hosting. Just record mapping!
                    $mappingBatch[] = [
                        'local_user_id' => $localId,
                        'hosting_user_id' => $matchedHostingId,
                        'action' => 'MERGED_SKIPPED',
                        'match_reason' => $matchReason,
                        'email' => $u->email,
                        'phone_number' => $u->phone_number,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    fputcsv($csvFile, [$localId, $matchedHostingId, 'MERGED_SKIPPED', $matchReason, $u->email, $u->phone_number]);
                    continue;
                }

                // 3. NEW USER: Needs insertion into hosting
                $targetId = $localId;
                $action = 'INSERTED_AS_IS';
                $matchReason = 'new_user';

                // Check for ID collision with hosting
                if (isset($hostingIds[$localId])) {
                    $targetId = $nextReassignedId++;
                    $action = 'ID_REASSIGNED';
                    $matchReason = 'id_collision';
                    $stats['id_reassigned']++;
                } else {
                    $stats['inserted_as_is']++;
                }

                if ($targetId > $maxInsertedId) {
                    $maxInsertedId = $targetId;
                }

                $mappingBatch[] = [
                    'local_user_id' => $localId,
                    'hosting_user_id' => $targetId,
                    'action' => $action,
                    'match_reason' => $matchReason,
                    'email' => $u->email,
                    'phone_number' => $u->phone_number,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                fputcsv($csvFile, [$localId, $targetId, $action, $matchReason, $u->email, $u->phone_number]);

                // Prepare SQL row
                $sqlBatch[] = self::formatUserRow($u, $targetId, $pdo);

                if (count($sqlBatch) >= $sqlChunkSize) {
                    self::writeSqlChunk($sqlFile, $sqlBatch);
                    $sqlBatch = [];
                }

                if (count($mappingBatch) >= 1000) {
                    DB::table('user_id_mappings')->insert($mappingBatch);
                    $mappingBatch = [];
                }
            }
        });

        // Flush remaining batches
        if (!empty($sqlBatch)) {
            self::writeSqlChunk($sqlFile, $sqlBatch);
            $sqlBatch = [];
        }
        if (!empty($mappingBatch)) {
            DB::table('user_id_mappings')->insert($mappingBatch);
            $mappingBatch = [];
        }

        $bar->finish();
        $this->newLine(2);

        // Footer of SQL
        $nextAutoIncrement = $maxInsertedId + 1;
        fwrite($sqlFile, "\n-- Restore checks and update AUTO_INCREMENT\n");
        fwrite($sqlFile, "SET FOREIGN_KEY_CHECKS = 1;\n");
        fwrite($sqlFile, "ALTER TABLE `users` AUTO_INCREMENT = {$nextAutoIncrement};\n");

        fclose($sqlFile);
        fclose($csvFile);

        // Copy SQL to Downloads folder too
        copy($outputSqlPath1, $outputSqlPath2);

        $this->info("==========================================================");
        $this->info("MIGRATION PROCESSING COMPLETE!");
        $this->info("==========================================================");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Local Users Processed', number_format($stats['total'])],
                ['Matched by Email (Skipped/Protected)', number_format($stats['matched_email'])],
                ['Matched by Phone (Skipped/Protected)', number_format($stats['matched_phone'])],
                ['Total Skipped (Already on Hosting)', number_format($stats['matched_email'] + $stats['matched_phone'])],
                ['New Users Inserted (Preserved ID)', number_format($stats['inserted_as_is'])],
                ['New Users with ID Collision (Reassigned)', number_format($stats['id_reassigned'])],
                ['Total New Users Added to SQL File', number_format($stats['inserted_as_is'] + $stats['id_reassigned'])],
                ['New Final AUTO_INCREMENT on Hosting', number_format($nextAutoIncrement)],
            ]
        );

        $this->info("Output files created successfully:");
        $this->line("1. SQL File (Project):   {$outputSqlPath1}");
        $this->line("2. SQL File (Downloads): {$outputSqlPath2}");
        $this->line("3. Mapping CSV:          {$mappingCsvPath}");
        $this->line("4. Local Database Table: `user_id_mappings` (" . DB::table('user_id_mappings')->count() . " rows)");

        return Command::SUCCESS;
    }

    private static function formatUserRow($u, int $targetId, \PDO $pdo): string
    {
        $fields = [
            $targetId,
            !empty($u->email) ? $pdo->quote($u->email) : "NULL",
            $u->google_id !== null ? $pdo->quote($u->google_id) : "NULL",
            $u->facebook_id !== null ? $pdo->quote($u->facebook_id) : "NULL",
            $u->apple_id !== null ? $pdo->quote($u->apple_id) : "NULL",
            $u->password !== null ? $pdo->quote($u->password) : "NULL",
            $u->password_hash !== null ? $pdo->quote($u->password_hash) : "NULL",
            $u->phone_number !== null ? $pdo->quote($u->phone_number) : "NULL",
            $u->country_code !== null ? $pdo->quote($u->country_code) : "NULL",
            $u->first_name !== null ? $pdo->quote($u->first_name) : $pdo->quote(""),
            $u->last_name !== null ? $pdo->quote($u->last_name) : $pdo->quote(""),
            $u->profile_picture !== null ? $pdo->quote($u->profile_picture) : "NULL",
            $u->cover_picture !== null ? $pdo->quote($u->cover_picture) : "NULL",
            $u->birth_date !== null ? $pdo->quote($u->birth_date) : "NULL",
            $u->gender !== null ? $pdo->quote($u->gender) : "NULL",
            $u->address !== null ? $pdo->quote($u->address) : "NULL",
            $u->bio !== null ? $pdo->quote($u->bio) : "NULL",
            (int)($u->post_count ?? 0),
            (int)($u->friend_count ?? 0),
            $u->reset_code !== null ? $pdo->quote($u->reset_code) : "NULL",
            $u->last_login !== null ? $pdo->quote($u->last_login) : "NULL",
            $u->created_at !== null ? $pdo->quote($u->created_at) : "NULL",
            $u->updated_at !== null ? $pdo->quote($u->updated_at) : "NULL",
            $u->deleted_at !== null ? $pdo->quote($u->deleted_at) : "NULL",
            (int)($u->is_active ?? 1),
            $u->token !== null ? $pdo->quote($u->token) : "NULL",
            $u->remember_token !== null ? $pdo->quote($u->remember_token) : "NULL",
            (int)($u->status ?? 1),
            (int)($u->is_verified ?? 0),
            $u->country_flag !== null ? $pdo->quote($u->country_flag) : "NULL",
            $u->role !== null ? $pdo->quote($u->role) : $pdo->quote("user"),
            $u->provider !== null ? $pdo->quote($u->provider) : "NULL",
            (int)($u->points ?? 0),
        ];

        return "(" . implode(", ", $fields) . ")";
    }

    private static function writeSqlChunk($file, array $rows): void
    {
        fwrite($file, "INSERT IGNORE INTO `users` (`id`, `email`, `google_id`, `facebook_id`, `apple_id`, `password`, `password_hash`, `phone_number`, `country_code`, `first_name`, `last_name`, `profile_picture`, `cover_picture`, `birth_date`, `gender`, `address`, `bio`, `post_count`, `friend_count`, `reset_code`, `last_login`, `created_at`, `updated_at`, `deleted_at`, `is_active`, `token`, `remember_token`, `status`, `is_verified`, `country_flag`, `role`, `provider`, `points`) VALUES\n");
        fwrite($file, implode(",\n", $rows) . ";\n\n");
    }
}

