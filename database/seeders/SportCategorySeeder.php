<?php

namespace Database\Seeders;

use App\Models\SportCategory;
use Illuminate\Database\Seeder;

class SportCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'All', 'icon' => '🔥'],
            ['name' => 'Cricket', 'icon' => '🏏'],
            ['name' => 'Football', 'icon' => '⚽'],
            ['name' => 'Kabaddi', 'icon' => '🤼'],
            ['name' => 'Basketball', 'icon' => '🏀'],
            ['name' => 'Hockey', 'icon' => '🏑'],
            ['name' => 'Volleyball', 'icon' => '🏐'],
        ];

        foreach ($categories as $i => $c) {
            SportCategory::updateOrCreate(
                ['name' => $c['name']],
                ['icon' => $c['icon'], 'sort_order' => $i + 1, 'is_active' => true]
            );
        }
    }
}
