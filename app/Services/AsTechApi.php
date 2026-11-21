<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class AsTechApi
{
    public function sign(string $body): string
    {
        return hash_hmac('sha256', $body, (string) config('astech.secret'));
    }

    public function verify(string $raw, string $sig): bool
    {
        return $sig !== '' && hash_equals($this->sign($raw), strtolower(trim($sig)));
    }

    public function call(string $path, array $payload = []): array
    {
        $raw = empty($payload) ? '{}' : json_encode($payload, JSON_UNESCAPED_SLASHES);
        $res = Http::timeout(30)->withHeaders([
            'X-Api-Key'   => (string) config('astech.key'),
            'X-Signature' => $this->sign($raw),
            'Origin'      => (string) config('astech.origin'),
        ])->withBody($raw, 'application/json')
          ->post(rtrim(config('astech.base_url'), '/') . $path);

        $json = $res->json();
        if (!is_array($json) || ($json['status'] ?? '') !== 'success') {
            throw new \RuntimeException(($json['error'] ?? '') ?: ('API error HTTP ' . $res->status()));
        }
        return $json['content'] ?? [];
    }

    public function games(): array
    {
        return $this->call('/api/public/v1/games')['games'] ?? [];
    }

    public function open(string $player, string $gameId): array
    {
        return $this->call('/api/public/v1/session/open', [
            'player_login' => $player,
            'game_id'      => $gameId,
            'currency'     => config('astech.currency'),
            'language'     => 'en',
            'exit_url'     => config('astech.exit_url'),
        ]);
    }
}
