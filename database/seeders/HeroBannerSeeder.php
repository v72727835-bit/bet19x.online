<?php

namespace Database\Seeders;

use App\Models\HeroBanner;
use Illuminate\Database\Seeder;

class HeroBannerSeeder extends Seeder
{
    public function run(): void
    {
        $dummyImages = [
            ['url' => 'https://picsum.photos/id/1011/900/400', 'title' => 'Build Your Dream XI'],
            ['url' => 'https://picsum.photos/id/1015/900/400', 'title' => "Join Today's Contests"],
            ['url' => 'https://picsum.photos/id/1021/900/400', 'title' => 'Win Big Every Match'],
            ['url' => 'https://picsum.photos/id/1043/900/400', 'title' => 'Live Contests Now Open'],
            ['url' => 'https://picsum.photos/id/1050/900/400', 'title' => 'Play. Compete. Win.'],
        ];

        foreach ($dummyImages as $i => $img) {
            HeroBanner::updateOrCreate(
                ['title' => $img['title']],
                ['image_path' => $img['url'], 'sort_order' => $i + 1, 'is_active' => true]
            );
        }
    }
}
