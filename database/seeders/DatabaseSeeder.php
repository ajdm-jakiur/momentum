<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Julian',
            'email' => 'juliyansmith44@gmail.com',
            'referral_code' => substr(str_replace(['+', '/', '='], '', base64_encode(random_bytes(24))), 0, 16),
        ]);

        $this->call([
            SectorSeeder::class,
            RoadmapSeeder::class,
        ]);
    }
}
