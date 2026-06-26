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
            ['name' => 'Islam Studies With History', 'slug' => 'islam-studies-with-history', 'icon' => '🕌', 'color' => '#0d9488', 'sort_order' => 1],
            ['name' => 'Systems Engineering', 'slug' => 'systems-engineering', 'icon' => '🖥️', 'color' => '#e85d26', 'sort_order' => 2],
            ['name' => 'DSA', 'slug' => 'dsa', 'icon' => '🧮', 'color' => '#d64000', 'sort_order' => 3],
            ['name' => 'Dev Practice', 'slug' => 'dev-practice', 'icon' => '🛠️', 'color' => '#16a34a', 'sort_order' => 4],
            ['name' => 'Communication Skills', 'slug' => 'communication-skills', 'icon' => '💬', 'color' => '#7c3aed', 'sort_order' => 5],
        ];

        foreach ($sectors as $sector) {
            Sector::updateOrCreate(['slug' => $sector['slug']], $sector);
        }
    }
}
