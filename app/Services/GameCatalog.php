<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/** Looks up game titles / providers for bet history rows (logs only store the game id). */
class GameCatalog
{
    public static function lookup(): array
    {
        try {
            $games = Cache::remember('astech_games', 1800, fn () => app(AsTechApi::class)->games());
        } catch (\Throwable $e) {
            return [];
        }

        $map = [];
        foreach ($games as $g) {
            if (! empty($g['gameId'])) {
                $map[$g['gameId']] = ['title' => $g['title'] ?? $g['gameId'], 'provider' => $g['provider'] ?? ''];
            }
        }

        return $map;
    }
}
