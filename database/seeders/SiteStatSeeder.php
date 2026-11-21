<?php

namespace Database\Seeders;

use App\Models\SiteStat;
use Illuminate\Database\Seeder;

class SiteStatSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['icon' => '👥', 'value' => '10L+', 'label' => 'Active Players'],
            ['icon' => '💰', 'value' => '₹5 Cr+', 'label' => 'Prize Distributed'],
            ['icon' => '🏆', 'value' => '2,500+', 'label' => 'Contests Hosted'],
            ['icon' => '⚡', 'value' => '6', 'label' => 'Sports Covered'],
        ];

        foreach ($items as $i => $item) {
            SiteStat::updateOrCreate(
                ['label' => $item['label']],
                [
                    'icon' => $item['icon'],
                    'value' => $item['value'],
                    'sort_order' => $i + 1,
                    'is_active' => true,
                ]
            );
        }
    }
}
