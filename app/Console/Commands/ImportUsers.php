<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

#[Signature('app:import-users')]
#[Description('Import users from public/users_with_data.sql and hash passwords')]
class ImportUsers extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sqlPath = public_path('users_with_data.sql');

        if (!file_exists($sqlPath)) {
            $this->error("SQL file not found at: {$sqlPath}");
            return Command::FAILURE;
        }

        $this->info("Reading SQL file from public/users_with_data.sql...");
        $sqlContent = file_get_contents($sqlPath);

        $tempTableName = 'users_import_temp';
        $this->info("Dropping temporary table {$tempTableName} if it exists...");
        Schema::dropIfExists($tempTableName);

        $this->info("Preparing SQL script for temporary table {$tempTableName}...");
        // Replace `users` with temporary table name in SQL definitions and inserts
        $sqlContent = preg_replace('/CREATE TABLE `users`/', "CREATE TABLE `{$tempTableName}`", $sqlContent);
        $sqlContent = preg_replace('/INSERT INTO `users`/', "INSERT INTO `{$tempTableName}`", $sqlContent);
        $sqlContent = preg_replace('/ALTER TABLE `users`/', "ALTER TABLE `{$tempTableName}`", $sqlContent);

        $this->info("Creating and populating temporary table {$tempTableName}...");
        DB::unprepared($sqlContent);

        if (!Schema::hasTable($tempTableName)) {
            $this->error("Failed to create temporary table {$tempTableName}");
            return Command::FAILURE;
        }

        $tempUsers = DB::table($tempTableName)->get();
        $totalCount = $tempUsers->count();
        $this->info("Found {$totalCount} users in temporary table.");

        $this->warn("Truncating active users table...");
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info("Importing users and hashing plain text passwords...");
        $bar = $this->output->createProgressBar($totalCount);
        $bar->start();

        foreach ($tempUsers as $tempUser) {
            // Hash the plain text password
            $hashedPassword = Hash::make($tempUser->password);

            // Insert into active users table with mapped values and requested defaults
            DB::table('users')->insert([
                'id' => $tempUser->id,
                'email' => $tempUser->email,
                'password' => $hashedPassword,
                'password_hash' => $tempUser->password_hash,
                'phone_number' => $tempUser->phone_number,
                'first_name' => $tempUser->first_name,
                'last_name' => $tempUser->last_name,
                'profile_picture' => $tempUser->profile_picture,
                'cover_picture' => $tempUser->cover_picture,
                'birth_date' => $tempUser->birth_date,
                'gender' => $tempUser->gender,
                'address' => $tempUser->address,
                'bio' => $tempUser->bio,
                'post_count' => $tempUser->post_count,
                'friend_count' => $tempUser->friend_count,
                'reset_code' => $tempUser->reset_code,
                'last_login' => $tempUser->last_login,
                'created_at' => $tempUser->created_at,
                'updated_at' => $tempUser->updated_at,
                'deleted_at' => $tempUser->deleted_at,
                'is_active' => $tempUser->is_active,
                'token' => $tempUser->token,
                'status' => $tempUser->status,
                'is_verified' => $tempUser->is_verified,
                'country_flag' => null,
                'role' => 'user',
                'provider' => null,
                'points' => 0,
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info("Dropping temporary table {$tempTableName}...");
        Schema::dropIfExists($tempTableName);

        $this->info("User import completed successfully! Imported {$totalCount} users.");
        return Command::SUCCESS;
    }
}
