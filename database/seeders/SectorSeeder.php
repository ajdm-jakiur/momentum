<?php

namespace Database\Seeders;

use App\Models\Sector;
use Illuminate\Database\Seeder;

class SectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sectors = [
            ['name' => 'Systems Engineering', 'slug' => 'systems-engineering', 'icon' => '🖥️', 'color' => '#e85d26', 'sort_order' => 1],
            ['name' => 'DSA', 'slug' => 'dsa', 'icon' => '🧮', 'color' => '#d64000', 'sort_order' => 2],
            ['name' => 'Dev Practice', 'slug' => 'dev-practice', 'icon' => '🛠️', 'color' => '#16a34a', 'sort_order' => 3],
            ['name' => 'Communication Skills', 'slug' => 'communication-skills', 'icon' => '💬', 'color' => '#7c3aed', 'sort_order' => 4],
        ];

        foreach ($sectors as $sector) {
            Sector::updateOrCreate(['slug' => $sector['slug']], $sector);
        }
    }
}
