<?php

namespace Database\Seeders;

use App\Enums\StreamerStatus;
use App\Models\Streamer;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Nuno Maduro',
            'email' => 'enunomaduro@gmail.com',
            'password' => bcrypt('enunomaduro@gmail.com'),
        ]);

        Streamer::create([
            'name' => 'Nuno Maduro',
            'twitch_id' => '139973107',
            'twitch_username' => 'enunomaduro',
            'status' => StreamerStatus::PendingApproval,
            'is_live' => false,
        ]);
    }
}
