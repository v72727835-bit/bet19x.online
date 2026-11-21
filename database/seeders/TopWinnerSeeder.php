<?php

namespace Database\Seeders;

use App\Models\TopWinner;
use Illuminate\Database\Seeder;

class TopWinnerSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Rohit Sharma K.', 'contest' => 'Cricket Mega Contest', 'amount' => 250000],
            ['name' => 'Ankita Verma', 'contest' => 'Football Grand League', 'amount' => 120000],
            ['name' => 'Suresh Yadav', 'contest' => 'Kabaddi Champions Cup', 'amount' => 85000],
            ['name' => 'Priya Nair', 'contest' => 'Basketball Showdown', 'amount' => 60000],
            ['name' => 'Vikram Singh', 'contest' => 'T20 Blitz Contest', 'amount' => 45000],
            ['name' => 'Meera Joshi', 'contest' => 'Hockey Premier Clash', 'amount' => 32000],
            ['name' => 'Arjun Patel', 'contest' => 'Volleyball Smash Cup', 'amount' => 21000],
            ['name' => 'Kavita Reddy', 'contest' => 'Champions League XI', 'amount' => 18500],
        ];

        foreach ($items as $i => $item) {
            TopWinner::updateOrCreate(
                ['name' => $item['name'], 'contest_name' => $item['contest']],
                [
                    'amount_won' => $item['amount'],
                    'sort_order' => $i + 1,
                    'is_active' => true,
                ]
            );
        }
    }
}
