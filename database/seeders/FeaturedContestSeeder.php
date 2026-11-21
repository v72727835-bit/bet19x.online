<?php

namespace Database\Seeders;

use App\Models\FeaturedContest;
use Illuminate\Database\Seeder;

class FeaturedContestSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['url' => 'https://picsum.photos/id/1058/600/400', 'title' => 'Cricket Mega Contest', 'subtitle' => 'IND vs AUS · Build your XI', 'prize' => 'Prize Pool ₹10,00,000'],
            ['url' => 'https://picsum.photos/id/1074/600/400', 'title' => 'Football Grand League', 'subtitle' => 'Weekend Premier Fixtures', 'prize' => 'Prize Pool ₹5,00,000'],
            ['url' => 'https://picsum.photos/id/1080/600/400', 'title' => 'Kabaddi Champions Cup', 'subtitle' => 'Pro League Matchday', 'prize' => 'Prize Pool ₹2,00,000'],
            ['url' => 'https://picsum.photos/id/1082/600/400', 'title' => 'Basketball Showdown', 'subtitle' => 'Top 8 Teams This Week', 'prize' => 'Prize Pool ₹1,50,000'],
            ['url' => 'https://picsum.photos/id/1011/600/400', 'title' => 'Hockey Premier Clash', 'subtitle' => 'National League Round 3', 'prize' => 'Prize Pool ₹1,00,000'],
            ['url' => 'https://picsum.photos/id/1035/600/400', 'title' => 'Volleyball Smash Cup', 'subtitle' => 'Regional Finals', 'prize' => 'Prize Pool ₹75,000'],
            ['url' => 'https://picsum.photos/id/1041/600/400', 'title' => 'T20 Blitz Contest', 'subtitle' => 'Evening Match Special', 'prize' => 'Prize Pool ₹3,00,000'],
            ['url' => 'https://picsum.photos/id/1043/600/400', 'title' => 'Champions League XI', 'subtitle' => 'Group Stage Fixtures', 'prize' => 'Prize Pool ₹4,00,000'],
            ['url' => 'https://picsum.photos/id/1050/600/400', 'title' => 'Kabaddi Weekend Mega', 'subtitle' => 'Season Semi-Finals', 'prize' => 'Prize Pool ₹1,20,000'],
            ['url' => 'https://picsum.photos/id/1059/600/400', 'title' => 'Basketball 3x3 League', 'subtitle' => 'City Championship', 'prize' => 'Prize Pool ₹90,000'],
            ['url' => 'https://picsum.photos/id/1069/600/400', 'title' => 'Test Match Special', 'subtitle' => '5-Day Format Contest', 'prize' => 'Prize Pool ₹6,00,000'],
            ['url' => 'https://picsum.photos/id/1084/600/400', 'title' => 'Football Cup Finale', 'subtitle' => 'Knockout Round', 'prize' => 'Prize Pool ₹8,00,000'],
        ];

        foreach ($items as $i => $item) {
            FeaturedContest::updateOrCreate(
                ['title' => $item['title']],
                [
                    'image_path' => $item['url'],
                    'subtitle' => $item['subtitle'],
                    'prize_text' => $item['prize'],
                    'sort_order' => $i + 1,
                    'is_active' => true,
                ]
            );
        }
    }
}
