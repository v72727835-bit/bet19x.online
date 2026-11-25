<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Moves money from one player's wallet to another's.
 *
 * The sender pays from deposit balance first, then winnings. The receiver gets
 * it as deposit balance (playable; it becomes withdrawable only after it is
 * won), so transfers can't be used to skip the withdrawal rules.
 */
class WalletTransfer
{
    public const MIN = 10;

    /** Find a player by user ID, email or phone. */
    public static function findUser(?string $q): ?User
    {
        $q = trim((string) $q);
        if ($q === '') {
            return null;
        }
        if (ctype_digit(ltrim($q, '#')) && strlen(ltrim($q, '#')) < 9) {
            return User::find((int) ltrim($q, '#'));
        }

        return User::where('email', strtolower($q))->orWhere('email', $q)->orWhere('phone', $q)->first();
    }

    /** "Ravi Kumar Sharma" -> "Ravi K. S." so a lookup confirms the person without showing the full name. */
    public static function maskName(?string $name): string
    {
        $parts = preg_split('/\s+/', trim((string) $name)) ?: [];

        return collect($parts)->map(fn ($p, $i) => $i === 0 ? $p : mb_substr($p, 0, 1) . '.')->implode(' ');
    }

    /**
     * @param  string  $by  who started it, for the history text: 'player', 'admin Name', 'agent Name'
     * @return string reference id
     *
     * @throws \DomainException with a message safe to show
     */
    public static function send(User $from, User $to, float $amount, string $by = 'player', ?string $note = null): string
    {
        $amount = round($amount, 2);
        if ($amount < self::MIN) {
            throw new \DomainException('Kam se kam ₹' . self::MIN . ' transfer kar sakte hain.');
        }
        if ($from->id === $to->id) {
            throw new \DomainException('Khud ko transfer nahi kar sakte.');
        }
        if ($to->is_blocked) {
            throw new \DomainException('Jis user ko bhej rahe hain uska account block hai.');
        }

        if (! self::ready()) {
            throw new \DomainException('Transfer abhi chalu nahi hai: server par "php artisan migrate" chalana baaki hai.');
        }

        $ref = 'TRF-' . strtoupper(Str::random(10));
        $conn = (new Wallet)->getConnectionName();

        DB::connection($conn)->transaction(function () use ($from, $to, $amount, $by, $note, $ref) {
            foreach ([$from->id, $to->id] as $uid) {
                Wallet::firstOrCreate(['user_id' => $uid], ['deposit_balance' => 0, 'winnings_balance' => 0, 'bonus_balance' => 0]);
            }
            // lock both wallets in a fixed order so two opposite transfers can't deadlock
            $wallets = Wallet::whereIn('user_id', [$from->id, $to->id])->orderBy('id')->lockForUpdate()->get()->keyBy('user_id');
            $src = $wallets[$from->id];
            $dst = $wallets[$to->id];

            $dep = (float) $src->deposit_balance;
            $win = (float) $src->winnings_balance;
            if ($amount > round($dep + $win, 2) + 0.00001) {
                throw new \DomainException('Balance kam hai. ₹' . number_format($dep + $win, 2) . ' tak hi transfer ho sakta hai.');
            }

            $fromDep = round(min($dep, $amount), 2);
            $fromWin = round($amount - $fromDep, 2);
            $src->deposit_balance = round($dep - $fromDep, 2);
            $src->winnings_balance = round($win - $fromWin, 2);
            $src->save();

            $dst->deposit_balance = round((float) $dst->deposit_balance + $amount, 2);
            $dst->save();

            $suffix = ($by !== 'player' ? " (by {$by})" : '') . ($note ? ' — ' . Str::limit($note, 120) : '');
            WalletTransaction::create([
                'wallet_id' => $src->id, 'type' => 'transfer_out', 'amount' => $amount,
                'description' => "Transfer to {$to->name} (#{$to->id}){$suffix}", 'reference_id' => $ref,
            ]);
            WalletTransaction::create([
                'wallet_id' => $dst->id, 'type' => 'transfer_in', 'amount' => $amount,
                'description' => "Transfer from {$from->name} (#{$from->id}){$suffix}", 'reference_id' => $ref,
            ]);
        });

        return $ref;
    }

    /** False while wallet_transactions.type is still the old ENUM that can't store transfer types. */
    public static function ready(): bool
    {
        static $ready = null;
        if ($ready !== null) {
            return $ready;
        }
        try {
            $conn = (new WalletTransaction)->getConnectionName();
            $col = collect(\Illuminate\Support\Facades\Schema::connection($conn)->getColumns('wallet_transactions'))->firstWhere('name', 'type');
            $type = strtolower((string) ($col['type'] ?? ''));

            return $ready = ! str_starts_with($type, 'enum') || str_contains($type, 'transfer_out');
        } catch (\Throwable $e) {
            return $ready = true;
        }
    }
}
